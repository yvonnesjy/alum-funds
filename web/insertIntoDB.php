<?php
include 'utils.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('stripe/init.php');

$curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
\Stripe\ApiRequestor::setHttpClient($curl);

extract(parse_url(getenv('DATABASE_URL')));
$pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=".ltrim($path, '/'));

if (isset($_POST['amount'])) {

    \Stripe\Stripe::setApiKey("sk_test_YRQsjn2dGpluxMPWQKBlKxPR");

    // Get the credit card details submitted by the form
    $token = $_POST['stripeToken'];
    $amount = $_POST['amount'] * 100;

    // Create a charge: this will charge the user's card
    try {
        $charge = \Stripe\Charge::create(array(
        "amount" => $amount, // Amount in cents
        "currency" => "usd",
        "source" => $token,
        "description" => "Example charge"
        ));

        $query = "select * from donations";
        $result = pg_query($pg_conn, $query);

        $id = pg_num_rows($result);
        $anonymous = 0;
        if (isset($_POST['anonymous'])) {
            $anonymous = 1;
        }
        $query = "insert into donations values ('".date("Y-m-d")."', ".$_POST['amount'].", '".$_POST['name']."', '".$_POST['story']."', ".$id.", '".$_POST['purpose']."', ".$anonymous.")";
        pg_query($conn, $query);
    } catch(\Stripe\Error\Card $e) {
      // The card has been declined
    }
    redirect("views/index.twig");
}
?>