<?php
include 'utils.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
  array(
    'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/'),
    'pdo.host' => $dbopts["host"],
    'pdo.port' => $dbopts["port"],
    'pdo.username' => $dbopts["user"],
    'pdo.password' => $dbopts["pass"]
  )
);

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
$app->get('/db/', function() use($app) {
    $st = $app['pdo']->prepare($query);
    $st->execute();
    
    $stories = array();
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $app['monolog']->addDebug('Row ' . $row['story'].$row['id']);
        $stories[] = $row;
    }

  return $app['twig']->render('index.twig', array(
    'stories' => $stories
  ));
});

$query = "select sisters.class, first, sister, last, number
          from classes join sisters
          on classes.name = sisters.class";
$app->get('/db/', function() use($app) {
    $st = $app['pdo']->prepare($query);
    $st->execute();
    
    $sisters = array();
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $app['monolog']->addDebug('Row ' . $row['first'].$row['number']);
        $sisters[] = $row;
    }

  return $app['twig']->render('index.twig', array(
    'sisters' => $sisters
  ));
});

$files = glob("images/pic-"."*.jpg");
if ($files != false) {
    $num_images = count($files);
}
$app->get('/', function() use($app) {
  return $app['twig']->render('index.twig', array(
    'num_images' => $num_images
  ));
});

$app->run();
?>