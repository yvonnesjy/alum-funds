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
    'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/').';host='.$dbopts["host"],
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

$app->get('/db/', function() use($app) {
  $st = $app['pdo']->prepare('SELECT name FROM test_table');
  $st->execute();

  $names = array();
  $query = "select * from sisters";
  $app->get('/db/', function() use($app) {
      $st = $app['pdo']->prepare($query);
      $st->execute();
      
      while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
          $app['monolog']->addDebug('Row ' . $row['first'].$row['sister'].$row['number']);
          $names[] = $row;
      }
  });

  return $app['twig']->render('database.twig', array(
    'names' => $names
  ));
});

$app->run();
?>