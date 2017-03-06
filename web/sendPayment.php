<?php
include 'utils.php';
include 'index.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('stripe/init.php');

$curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
\Stripe\ApiRequestor::setHttpClient($curl);

if (isset($_POST)) {

    \Stripe\Stripe::setApiKey("sk_test_YRQsjn2dGpluxMPWQKBlKxPR");

    // Get the credit card details submitted by the form
    $token = $_POST['stripeToken'];
    $amount = $_POST['amount'] * 100;
    $description = "Donation made by ".$_POST['name'];

    // Create a charge: this will charge the user's card
    try {
        $charge = \Stripe\Charge::create(array(
        "amount" => $amount, // Amount in cents
        "currency" => "usd",
        "source" => $token,
        "description" => $description
        ));
        
        updateDB();

        global $app;
        $app['name'] = explode(" ", $_POST['name'])[1];
        redirect("views/success.twig");
    } catch(\Stripe\Error\Card $e) {
      // The card has been declined
    }
    redirect("index.php");
}

function updateDB() {
    $user = 'sbyqoykaxdtcbd';
    $dbname = 'ddc85v730ush9e';
    $pass = 'ca188649905b508b4bd725a75e646f2a3b9a49a72c745740bf6007e99c7f14bb';
    $host = 'ec2-107-22-244-62.compute-1.amazonaws.com';
    $port = '5432';
    $path = 'postgres://sbyqoykaxdtcbd:ca188649905b508b4bd725a75e646f2a3b9a49a72c745740bf6007e99c7f14bb@ec2-107-22-244-62.compute-1.amazonaws.com:5432/ddc85v730ush9e';

    // extract(parse_url(getenv('DATABASE_URL')));
    $pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=$dbname");
    // $pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=".ltrim($path, '/'));

    $query = "select * from donations";
    $result = pg_query($pg_conn, $query);

    $id = pg_num_rows($result);
    $anonymous = 'false';
    if (isset($_POST['anonymous'])) {
        $anonymous = 'true';
    }

    $number = explode(" ", $_POST['name'])[0];
    $query = "insert into donations values (".$id.", '".date("Y-m-d")."', ".$_POST['amount'].", '".$number."', '".$_POST['story']."', '".$_POST['purpose']."', ".$anonymous.")";
    pg_query($pg_conn, $query);
}
?>