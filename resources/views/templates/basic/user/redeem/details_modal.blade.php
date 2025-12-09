<div id="redeemDetailsModal" class="modal custom--modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Redeem Gold')</h5>
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
                let orderDetails = $(this).data('order_details');
                let html = '';
                orderDetails.forEach(item => {
                    html += `<span>${item.text}</span></br>`;
                });
                modal.find('.orderDetails').html(html);

                let pickupPoint = $(this).data('pickup_point');
                if (pickupPoint) {
                    modal.find('.deliveryPoint').text(`@lang('Pickup Point')`);
                    modal.find('.deliveryDetails').text(pickupPoint.address);
                } else {
                    let address = $(this).data('address');
                    modal.find('.deliveryPoint').text(`@lang('Home Delivery')`);
                    modal.find('.deliveryDetails').html(address);
                }

                modal.modal('show');
            });

        })(jQuery);
    </script>
@endpush
