<?php
include 'utils.php';

require('../vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$files = glob("images/pic-"."*.jpg");
if ($files != false) {
    $num_images = count($files);
}

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

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

$query = "select date, first, sister, last, story, id, anonymous
        from donations join sisters
        on donations.number = sisters.number
        order by id desc";
$app->get('/db/', function() use($app) {
    $st = $app['pdo']->prepare($query);
    $st->execute();

    // $names = array();
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $app['monolog']->addDebug('Row '.$row['first'].$row['sister'].$row['number']);
    }
});

$commentStyle = "";
while ($row = mysqli_fetch_array($result)) {
    $name = "Anonymous";
    if ($row['anonymous'] != 1) {
        $name = $row['first']." \"".$row['sister']."\" ".$row['last'];
    }

    $commentStyle = $commentStyle.".comment-".$row['id'].", ";
    echo "  <style type='text/css'>
                .comment-".$row['id']." { background-image: url('../images/pic-".($row['id'] % $num_images).".jpg'); }
            </style>";
    echo "  <div class='comment-".$row['id']."'>
                <div class='content'>
                    <div class='background'>";
    echo "              <div class='story-display'><p style='color: #897C7B;'>".$row['story']."</p><br><p style='color: #895753;'>-".$name." ".$row['date']."</p></div>";
    echo "          </div>
                </div>
            </div>";
}
$commentStyle = substr($commentStyle, 0, strlen($commentStyle) - 2);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scal=1">
    <title>Thanks, DPhiL</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Shadows+Into+Light" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Reenie+Beanie" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div id="left-panel" class="col-md-8">
                <div class="cover" align="center">
                    <div class="content">
                        <div class="background">
                            <img src="images/logo.png" class="img-responsive logo" alt="DPhiL Logo">
                            <h1 class="site-name">Thanks, DPhiL!<3</h1>
                        </div>
                    </div>
                </div>

            <style type='text/css'><?php echo $commentStyle; ?>
                {
                    height: 100%;
                    width: 100%;
                    display: table;
                    background-attachment: fixed;
                    background-position: center;
                    background-repeat: no-repeat;
                    background-size: cover;
                }
            </style>

            </div>
            <div id="right-panel" class="col-md-4">
                <form id="form" name="form" action="insertIntoDB.php" onsubmit="return validateForm()" method="POST">
                    <span class="payment-errors"></span>
                    <legend>GIVE US MONEY</legend>
                    <div class="form-row">
                        <label for="name">
                            <span>I am</span>
                            <select id="name" name="name" required="true">
                                <option value="default" disabled>--</option>
                                <option disabled>- Charter -</option>
                                <?php
                                $query = "select sisters.class, first, sister, last, number
                                        from classes join sisters
                                        on classes.name = sisters.class";
                                $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
                                $class = "Charter";
                                while ($row = mysqli_fetch_array($result)) {
                                    if ($class != $row['class']) {
                                        $class = $row['class'];
                                        echo "<option disabled> </option>";
                                        echo "<option disabled>- ".$class." -</option>";
                                    }
                                    $number = $row['number'];
                                    if ($number < 10) {
                                        $number = "0".$number;
                                    }
                                    $name = "#".$number." ".$row['first']." \"".$row['sister']."\" ".$row['last'];
                                    echo "<option value='".$number."'>".$name."</option>";
                                }
                                ?>
                            </select>
                        </label>
                    </div>

                    <div class="form-row">
                        <label for="email">
                            <span>Email</span>
                            <input type="email" id="email" name="email" required="true">
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label for="amount">
                            <span>Amount</span>
                            <input type="number" id="amount" name="amount" required="true">
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label for="purpose">
                            <span>Purpose</span>
                            <input type="text" id="purpose" name="purpose">
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <label for="story">
                            <span>What's your story with DPhiL?</span>
                            <textarea id="story" name="story" rows="6" required="true"></textarea>
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>Card Number</span>
                            <input type="text" size="20" maxlength="20" data-stripe="number" required="true">
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>Expiration (MM/YY)</span>
                            <input class="exp" type="text" size="2" maxlength="2" data-stripe="exp_month" required="true">
                            <span> / </span>
                            <input class="exp" type="text" size="2" maxlength="2" data-stripe="exp_year" required="true">
                        </label>
                    </div>

                    <div class="form-row">
                        <label>
                            <span>CVC</span>
                            <input type="text" size="4" maxlength="4" data-stripe="cvc" required="true">
                        </label>
                        &emsp;
                        <label>
                            <span>Billing Zip</span>
                            <input type="text" size="6" maxlength="6" data-stripe="address_zip" required="true">
                        </label>
                    </div>

                    <div class="form-row, anonymous-checkbox">
                        <input type="checkbox" name="anonymous" value="anonymous">
                        <label for="anonymous" class="anonymous-label">Post my story anonymously</label>
                    </div>

                    <button type="submit" class="submit" id="custom-button">I'm ready to donate!</button>
                </form>
            </div>
        </div>
    </div>

    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        function validateForm() {
            var name = document.forms['form']['name'].value;
            if (name != "default") {
                alert("Name must be filled out");
                return false;
            }

            var email = document.forms['form']['email'].value;
            if !(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email.value)) {
                alert("You have entered an invalid email address!")
                return false;
            }
        }
    </script>
    <script type="text/javascript">
        Stripe.setPublishableKey('pk_test_DuyFhe6ZKiJbjFgdJ57dpOFd');

        $(function() {
            var $form = $('#form');
            $form.submit(function(event) {
                // Disable the submit button to prevent repeated clicks:
                $form.find('.submit').prop('disabled', true);

                // Request a token from Stripe:
                Stripe.card.createToken($form, stripeResponseHandler);

            // Prevent the form from being submitted:
                return false;
            });
        });

        function stripeResponseHandler(status, response) {
            // Grab the form:
            var $form = $('#form');

            if (response.error) { // Problem!

                // Show the errors on the form:
                $form.find('.payment-errors').text(response.error.message);
                $form.find('.submit').prop('disabled', false); // Re-enable submission

            } else { // Token was created!

                // Get the token ID:
                var token = response.id;

                // Insert the token ID into the form so it gets submitted to the server:
                $form.append($('<input type="hidden" name="stripeToken">').val(token));

                // Submit the form:
                $form.get(0).submit();
            }
        };
    </script>
</body>
</html>