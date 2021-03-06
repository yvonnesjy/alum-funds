<?php
session_start();
include 'utils.php';

require_once('stripe/init.php');

if (isset($_POST)) {

    $curl = new \Stripe\HttpClient\CurlClient(array(CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1));
    \Stripe\ApiRequestor::setHttpClient($curl);
    \Stripe\Stripe::setApiKey("sk_live_hWSqktaYp7Lm7rpdjbOjCR6s");

    // Create a charge: this will charge the user's card
    try {
        $charge = \Stripe\Charge::create(array(
        "amount" => $_POST['amount'] * 100, // Amount in cents
        "currency" => "usd",
        "source" => $_POST['stripeToken'],
        "description" => "Donation",
        "receipt_email" => $_POST['email']
        ));
        
        updateDB();

        $_SESSION['suc_msg'] = explode("_", $_POST['name'])[1];
    } catch(\Stripe\Error\Card $e) {
        // Since it's a decline, \Stripe\Error\Card will be caught
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $_SESSION['err_msg'] = $err['message'];
    } catch (\Stripe\Error\RateLimit $e) {
        // Too many requests made to the API too quickly
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $_SESSION['err_msg'] = $err['message'];
    } catch (\Stripe\Error\InvalidRequest $e) {
        // Invalid parameters were supplied to Stripe's API
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $_SESSION['err_msg'] = $err['message'];
    } catch (\Stripe\Error\Authentication $e) {
        // Authentication with Stripe's API failed
        // (maybe you changed API keys recently)
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $_SESSION['err_msg'] = $err['message'];
    } catch (\Stripe\Error\ApiConnection $e) {
        // Network communication with Stripe failed
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $_SESSION['err_msg'] = $err['message'];
    } catch (\Stripe\Error\Base $e) {
        // Display a very generic error to the user, and maybe send
        // yourself an email
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $_SESSION['err_msg'] = $err['message'];
    } catch (Exception $e) {
        // Something else happened, completely unrelated to Stripe
        $_SESSION['err_msg'] = "An error occurred. Please try again.\n If this keeps happenning, email Yvonne(stonfish@hotmail.com).";
    }

    if (isset($_SESSION['err_msg'])) {
        $_SESSION['post'] = $_POST;
    }

    redirect("index.php");
}

function updateDB() {
    extract(parse_url(getenv('DATABASE_URL')));
    $pg_conn = pg_connect("user=$user password=$pass host=$host port=$port dbname=".ltrim($path, '/'));

    $query = "select * from donations";
    $result = pg_query($pg_conn, $query);

    $id = pg_num_rows($result);
    $anonymous = 'false';
    if (isset($_POST['anonymous'])) {
        $anonymous = 'true';
    }

    $number = explode("_", $_POST['name'])[0];
    $query = "insert into donations values (".$id.", '".date("Y-m-d")."', ".$_POST['amount'].", '".$number."', '".$_POST['story']."', '".$_POST['purpose']."', ".$anonymous.")";
    pg_query($pg_conn, $query);
}
?>