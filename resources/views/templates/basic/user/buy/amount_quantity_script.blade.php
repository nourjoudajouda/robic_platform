
@push('script')
    <script>
        (function($) {
            "use strict";
            // sell_price = سعر الوحدة الواحدة من batch_sell_order
            let pricePerItem = {{ $cheapestSellOrder->sell_price ?? $selectedBatch->sell_price }};
            let quantity = 0; // الكمية بالوحدة
            let amount = 0;

            // تحديث السعر عند تغيير المنتج
            $('select[name="batch_id"]').on('change', function() {
                pricePerItem = $(this).find('option:selected').data('price');
                $('.currentPrice').text(pricePerItem);
                $('#selected_batch_id').val($(this).val());
                if (amount > 0) {
                    // المبلغ ÷ سعر الوحدة = الكمية بالوحدات
                    quantity = amount / pricePerItem;
                    $('[name="quantity"]').val(quantity.toFixed(4));
                }
            });

            // تحديث الكمية عند تغيير المبلغ
            $('[name="amount"]').on('keyup', function() {
                amount = $(this).val() * 1;
                if (pricePerItem > 0) {
                    // المبلغ ÷ سعر الوحدة = الكمية بالوحدات
                    quantity = amount / pricePerItem;
                    $('[name="quantity"]').val(quantity.toFixed(4));
                }
                handleSubmitButton();
            });

            // تحديث المبلغ عند تغيير الكمية
            $('[name="quantity"]').on('keyup', function() {
                quantity = $(this).val() * 1; // الكمية بالوحدات
                if (pricePerItem > 0) {
                    // الكمية بالوحدات × سعر الوحدة = المبلغ
                    amount = quantity * pricePerItem;
                    $('[name="amount"]').val(amount.toFixed(2));
                }
                handleSubmitButton();
            });

            let minAmount = {{ $chargeLimit->min_amount }};
            let maxAmount = {{ $chargeLimit->max_amount }};

            $('[name="amount"], [name="quantity"]').on('focusout', function() {                
                let amount = $('[name="amount"]').val() * 1;
                if (amount <= 0) {
                    $('[name="amount"]').val('');
                    $('[name="quantity"]').val('');
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
                // المبلغ ÷ سعر الوحدة = الكمية بالوحدات
                quantity = amount / pricePerItem;
                $('[name="amount"]').val(amount);
                $('[name="quantity"]').val(quantity.toFixed(4));
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