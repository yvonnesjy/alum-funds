<?php
session_start();
include 'utils.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../vendor/autoload.php');

global $app;
$app = new Silex\Application();
$app['debug'] = true;

$user = 'sbyqoykaxdtcbd';
$dbname = 'ddc85v730ush9e';
$pass = 'ca188649905b508b4bd725a75e646f2a3b9a49a72c745740bf6007e99c7f14bb';
$host = 'ec2-107-22-244-62.compute-1.amazonaws.com';
$port = '5432';
$path = 'postgres://sbyqoykaxdtcbd:ca188649905b508b4bd725a75e646f2a3b9a49a72c745740bf6007e99c7f14bb@ec2-107-22-244-62.compute-1.amazonaws.com:5432/ddc85v730ush9e';

// extract(parse_url(getenv('DATABASE_URL')));
$pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=$dbname");
// $pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=".ltrim($path, '/'));

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
    $app['monolog']->addDebug('Row ' . $row[5]." ".$row[6]);
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
          on classes.name = sisters.class
          order by number";
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
$app['suc_msg'] = "";
$app['err_msg'] = "";
$app['post'] = array();
if (isset($_SESSION['suc_msg'])) {
  $app['suc_msg'] = $_SESSION['suc_msg'];
  unset($_SESSION['suc_msg']);
}
if (isset($_SESSION['err_msg'])) {
  $app['err_msg'] = $_SESSION['err_msg'];
  $app['post'] = $_SESSION['post'];
  unset($_SESSION['err_msg']);
  unset($_SESSION['post']);
}

$app->run();
?>