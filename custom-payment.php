<?php
/*
 * Template Name: custom-payment.php
 * Description: This is a custom template for Payquick orders.
 */

// Include the necessary theme files
get_header();

?>
<?php
$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
$order = wc_get_order($order_id);
if ($order) {
    $redirect = get_post_meta($order_id, '_elavon_payment_link', true);
    $order_key = $order->get_order_key();
    $thank_you_url = add_query_arg(
        array(
            'key' => $order_key
        ),
        wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url())
    );
    $status = $order->get_status();
} else {
    exit();
}



?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-success mb-2 payment-title">Vennligst betal for å bekrefte din ordre</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 order-md-0 mb-2" style="padding: 0;">
            <div class="cart-totals w-100">
                <h2 class="title">Totalt i handlekurven</h2>
                <ul class="cart-infos">
                    <li><span>Subtotal</span><span>
                            <?php echo wc_price($order->get_subtotal()); ?>
                        </span></li>
                    <li><span>Tax</span><span>
                            <?php echo wc_price($order->get_total_tax()); ?>
                        </span></li>
                    <li>
                        <span>Shipping</span>
                        <span>
                            <?php echo wc_price($order->get_shipping_total()); ?>
                        </span>
                    </li>
                    <li><span>Total</span><span>
                            <?php echo wc_price($order->get_total()); ?>
                        </span></li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 text-center order-md-1 mb-2" style="padding: 0;margin:0 auto">
            <div class="center-block">
                <!-- Additional code for payment options can be added here -->
                <div id="qrcode"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card mb-1">
                <div class="card-body">

                    <a href="<?php echo $redirect; ?>" id="complete-order" class="btn btn-outline  btn-lg"
                        style="display:block;text-align:center">Proceed To Payment</a>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>



    let orderStatus = "<?php echo esc_js($status); ?>";
    let thankYouUrl = "<?php echo esc_url($thank_you_url); ?>";

    if (orderStatus === 'processing') {
        window.location.href = thankYouUrl;
    } else {
        window.setTimeout(function () {

            location.reload();
        }, 10000);
    }
    var dynamicURL = `<?php echo home_url($_SERVER['REQUEST_URI']) ?>`;
    var qrCode = new QRCode(document.getElementById("qrcode"), {
        text: dynamicURL,
    });
    qrCode.makeCode(dynamicURL);

</script>

<?php
// Include the necessary theme files
get_footer();
?>