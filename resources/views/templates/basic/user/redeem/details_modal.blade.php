<div id="redeemDetailsModal" class="modal custom--modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Shipping Details')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form method="POST">
                @csrf
                <div class="modal-body">
                    <h6 class="mb-1">@lang('Order Details')</h6>
                    <div class="orderDetails"></div>
                    <hr>
                    <h6 class="mb-1">@lang('Delivery') - <span class="deliveryPoint"></span></h6>
                    <div class="deliveryDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </form>
        </div>
    </div>
</div>



@push('script')
    <script>
        (function($) {
            "use strict";

            let modal = $('#redeemDetailsModal');

            $('.detailsBtn').on('click', function() {
                let productName = $(this).data('product_name');
                let quantity = $(this).data('quantity');
                let unit = $(this).data('unit');
                let shippingCost = $(this).data('shipping_cost');
                let deliveryType = $(this).data('delivery_type');
                let deliveryAddress = $(this).data('delivery_address');
                let shippingMethod = $(this).data('shipping_method');
                let distance = $(this).data('distance');
                
                let html = `
                    <div class="mb-3">
                        <strong>@lang('Product'):</strong> ${productName}<br>
                        <strong>@lang('Quantity'):</strong> ${quantity} ${unit}<br>
                        <strong>@lang('Shipping Cost'):</strong> ${shippingCost}
                    </div>
                `;
                
                if (deliveryType === 'shipping' && shippingMethod !== 'N/A') {
                    html += `
                        <div class="mb-3">
                            <strong>@lang('Shipping Method'):</strong> ${shippingMethod}<br>
                            <strong>@lang('Distance'):</strong> ${distance} @lang('km')
                        </div>
                    `;
                }
                
                modal.find('.orderDetails').html(html);

                if (deliveryType === 'pickup') {
                    modal.find('.deliveryPoint').text(`@lang('Pickup from Warehouse')`);
                } else {
                    modal.find('.deliveryPoint').text(`@lang('Home Delivery')`);
                }
                modal.find('.deliveryDetails').html(deliveryAddress);

                modal.modal('show');
            });

        })(jQuery);
    </script>
@endpush
