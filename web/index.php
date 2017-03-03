<?php
include 'utils.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

extract(parse_url(getenv('DATABASE_URL')));
$pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=".ltrim($path, '/'));
// $app->register(new Herrera\Pdo\PdoServiceProvider(),
//   array(
//     'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/'),
//     'pdo.host' => $dbopts["host"],
//     'pdo.port' => $dbopts["port"],
//     'pdo.username' => $dbopts["user"],
//     'pdo.password' => $dbopts["pass"]
//   )
// );

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$query = "select date, first, sister, last, story, id, anonymous
        from donations join sisters
        on donations.number = sisters.number
        order by id desc";
$result = pg_query($pg_conn, $query);
$stories = array();
if (pg_num_rows($result)) {
  while ($row = pg_fetch_row($result)) {
    // $app['monolog']->addDebug('Row ' . $row[0].$row[5]);
    $stories[] = array('date' => $row[0],
                      'first' => $row[1], 
                      'sister' => $row[2],
                      'last' => $row[3],
                      'story' => $row[4],
                      'id' => $row[5],
                      'anonymous' => $row[6]);
  }
}

$query = "select sisters.class, first, sister, last, number
          from classes join sisters
          on classes.name = sisters.class";
$result = pg_query($pg_conn, $query);
$sisters = array();
if (pg_num_rows($result)) {
  while ($row = pg_fetch_row($result)) {
    // $app['monolog']->addDebug('Row ' . $row[0].' '.$row[1]);
    $sisters[] = array('class' => $row[0],
                      'first' => $row[1], 
                      'sister' => $row[2],
                      'last' => $row[3],
                      'number' => $row[4]);
  }
}

$files = glob("images/pic-"."*.jpg");
if ($files != false) {
    $num_images = count($files);
}

$app['stories'] = $stories;
$app['sisters'] = $sisters;
$app['num_images'] = $num_images;

$app->get('/', function() use($app) {
    // $st = $app['pdo']->prepare($query);
    // $st->execute();

    // $stories = array();
    // while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    //     $app['monolog']->addDebug('Row ' . $row['story'].$row['id']);
    //     $stories[] = $row;
    // }

  return $app['twig']->render('index.twig', array(
    'stories' => $stories,
    'sisters' => $sisters,
    'num_images' => $num_images
  ));
});

// $app->get('/', function() use($app) {
//     // $st = $app['pdo']->prepare($query);
//     // $st->execute();
    
//     // $sisters = array();
//     // while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
//     //     $app['monolog']->addDebug('Row ' . $row['first'].$row['number']);
//     //     $sisters[] = $row;
//     // }

//   return $app['twig']->render('index.twig', array(
//     'sisters' => $sisters
//   ));
// });

// $app->get('/', function() use($app) {
//   return $app['twig']->render('index.twig', array(
//     'num_images' => $num_images
//   ));
// });

$app->run();
?>