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

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$query = "select * from sisters";
$app->get('/db/', function() use($app) {
    $st = $app['pdo']->prepare($query);
    $st->execute();
    
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $app['monolog']->addDebug('Row ' . $row['first'].$row['sister'].$row['number']);
        echo $row['first'].$row['sister'].$row['number'];
    }
});

$app->run();
?>