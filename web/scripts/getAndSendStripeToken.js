Stripe.setPublishableKey('pk_test_DuyFhe6ZKiJbjFgdJ57dpOFd');

$(function() {
    $('#form').validator({

        // the delay in milliseoncds
        delay: 500,

        // allows html inside the error messages
        html: false,

        // disable submit button if there's invalid form
        disable: true,

        // <a href="http://www.jqueryscript.net/tags.php?/Scroll/">Scroll</a> to and focus the first field with an error upon validation of the form.
        focus: true,

        // define your custom validation rules
        custom: {
            notEquals: function($el) {
                var matchValue = $el.data("notEquals");
                if ($el.val() === matchValue) {
                    return false;
                }
                return true;
            }
        },

        // default errof messages
        errors: {
        },

        // feedback CSS classes
        feedback: {
            success: 'glyphicon-ok',
            error: 'glyphicon-remove'
        }

    }).on('submit', function(e) {
        if (!e.isDefaultPrevented()) {
            var $form = $('#form');
            // Disable the submit button to prevent repeated clicks:
            $form.find('.submit').prop('disabled', true);

            // Request a token from Stripe:
            Stripe.card.createToken($form, stripeResponseHandler);

            // Prevent the form from being submitted:
        }
        return false;
    });
});

function stripeResponseHandler(status, response) {
    // Grab the form:
    var $form = $('#form');

    if (response.error) { // Problem!
        $form.find('legend').text("Here's to the memories we made...");

        // Show the errors on the form:
        if (isMobile()) {
            alert(response.error.message);
        } else {
            $form.find('#payment-errors').text(response.error.message);
        }
        
        $form.find('.submit').prop('disabled', false); // Re-enable submission

    } else { // Token was created!

        // Get the token ID:
        var token = response.id;

        // Insert the token ID into the form so it gets submitted to the server:
        $form.append($('<input type="hidden" name="stripeToken">').val(token));

        // Submit the form:
        $form.get(0).submit();
        $form.find('.submit').prop('disabled', false);
    }
}