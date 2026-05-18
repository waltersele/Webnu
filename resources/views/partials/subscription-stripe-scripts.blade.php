<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    var stripe = Stripe('{{ config('services.stripe.key') }}');
    var elements = stripe.elements();
    var cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
            }
        }
    });
    cardElement.mount('#card-element');

    cardElement.on('change', function (event) {
        var displayError = document.getElementById('card-errors');
        if (displayError) {
            displayError.textContent = event.error ? event.error.message : '';
        }
    });

    var subscriptionForm = document.getElementById('{{ $formId ?? 'subscription-form' }}');
    if (!subscriptionForm) {
        return;
    }

    subscriptionForm.addEventListener('submit', function (event) {
        event.preventDefault();

        var privacyCheck = document.getElementById('privacy-check');
        var privacyError = document.getElementById('privacy-check-not-checked');

        if (privacyCheck && !privacyCheck.checked) {
            if (privacyError) {
                privacyError.style.display = 'block';
            }
            return;
        }

        if (privacyError) {
            privacyError.style.display = 'none';
        }

        var submitButton = subscriptionForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        }).then(function (result) {
            if (result.error) {
                var cardErrors = document.getElementById('card-errors');
                if (cardErrors) {
                    cardErrors.textContent = result.error.message;
                }
                if (submitButton) {
                    submitButton.disabled = false;
                }
                return;
            }

            document.getElementById('payment_method').value = result.paymentMethod.id;
            subscriptionForm.submit();
        });
    });
})();
</script>
