<?php
include 'utils.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('stripe/init.php');

$curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
\Stripe\ApiRequestor::setHttpClient($curl);

$conn = mysqli_connect($server, $username, $password, $database) or die("Unable to connect");

if (isset($_POST['name'])) {
    $query = "select * from donations";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));

    $id = mysqli_num_rows($result);
    $anonymous = 0;
    if (isset($_POST['anonymous'])) {
        $anonymous = 1;
    }
    $query = "insert into donations values ('".date("Y-m-d")."', ".$_POST['amount'].", '".$_POST['name']."', '".$_POST['story']."', ".$id.", '".$_POST['purpose']."', ".$anonymous.")";
    mysqli_query($conn, $query) or die(mysqli_error($conn));

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
    } catch(\Stripe\Error\Card $e) {
      // The card has been declined
    }
    redirect("index.php");
    // echo "<script>var amount = ".$_POST['amount']."</script>";
}
?>