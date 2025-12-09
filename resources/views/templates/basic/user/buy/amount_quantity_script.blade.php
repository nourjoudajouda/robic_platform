
@push('script')
    <script>
        (function($) {
            "use strict";
            let price = 0;
            let gram = 0;
            let amount = 0;

            $('select[name="category_id"]').on('change', function() {
                price = $(this).find('option:selected').data('price');
                $('.currentPrice').text(price);
                if (amount > 0) {
                    gram = amount / price;
                    $('[name="gram"]').val(gram.toFixed(4));
                }
            }).trigger('change');

            $('[name="amount"]').on('keyup', function() {
                amount = $(this).val() * 1;
                gram = amount / price;
                $('[name="gram"]').val(gram.toFixed(4));      
                handleSubmitButton();
            });

            $('[name="gram"]').on('keyup', function() {
                let gram = $(this).val();
                let amount = gram * price;
                $('[name="amount"]').val(amount.toFixed(2));
                handleSubmitButton();
            });

            let minAmount = {{ $chargeLimit->min_amount }};
            let maxAmount = {{ $chargeLimit->max_amount }};

            $('[name="amount"], [name="gram"]').on('focusout', function() {                
                let amount = $('[name="amount"]').val() * 1;
                if (amount <= 0) {
                    $('[name="amount"]').val('');
                    $('[name="gram"]').val('');
                    return false;
                }

                if (amount < minAmount) {
                    notify('error', `Minimum amount is ${minAmount}`);
                    return false;
                }
                if (amount > maxAmount) {
                    notify('error', `Maximum amount is ${maxAmount}`);
                    return false;
                }

                amount = amount.toFixed(2);
                gram = amount / price;
                $('[name="amount"]').val(amount);
                $('[name="gram"]').val(gram.toFixed(4));
            });

            function handleSubmitButton(){
                if(minAmount <= amount && maxAmount >= amount){
                    $('button[type="submit"]').attr('disabled', false);
                }else{
                    $('button[type="submit"]').attr('disabled', true);
                }
            }


        })(jQuery);
    </script>
@endpush