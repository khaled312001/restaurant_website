<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Pay With Midtrans') }}</title>
</head>

<body>
    <button hidden class="btn btn-primary" id="pay-button">{{ __('Pay Now') }}</button>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    @if ($is_production == 1)
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
        </script>
    @endif
    <script>
        var success_url = "{{ $notifyURL }}";
        var cancel_url = "{{ $cancelUrl }}";
        const payButton = document.querySelector('#pay-button');
        payButton.addEventListener('click', function(e) {
            e.preventDefault();

            snap.pay('{{ $snapToken }}', {
                // Optional
                onSuccess: function(result) {

                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    let orderId = result.order_id;
                    // console.log(orderId);
                    window.location.href = success_url + '?order_id=' + orderId;
                    // console.log(result)
                },
                // Optional
                onPending: function(result) {
                    window.location.href = cancel_url;
                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    // console.log(result)
                },
                // Optional
                onError: function(result) {
                    window.location.href = cancel_url;
                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    // console.log(result)
                }
            });
        });
    </script>

    <script>
        window.onload = function() {
            // Triggering click event on the button with ID 'pay-button'
            var payButton = document.getElementById('pay-button');
            if (payButton) {
                payButton.click();
            }
        };
    </script>

</body>

</html>
