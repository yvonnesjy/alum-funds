<?php
include 'utils.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('stripe/init.php');

if (isset($_POST)) {

    $curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
    \Stripe\ApiRequestor::setHttpClient($curl);
    \Stripe\Stripe::setApiKey("sk_test_YRQsjn2dGpluxMPWQKBlKxPR");

    // Create a charge: this will charge the user's card
    try {
        $charge = \Stripe\Charge::create(array(
        "amount" => $_POST['amount'] * 100, // Amount in cents
        "currency" => "usd",
        "source" => $_POST['stripeToken'],
        "description" => "Donation to Delta Phi Lambda",
        "receipt_email" => $_POST['email']
        ));
        
        updateDB();
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

    $number = explode("_", $_POST['name'])[0];
    $query = "insert into donations values (".$id.", '".date("Y-m-d")."', ".$_POST['amount'].", '".$number."', '".$_POST['story']."', '".$_POST['purpose']."', ".$anonymous.", '".$_POST['email']."')";
    pg_query($pg_conn, $query);
}
?>