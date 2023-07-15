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

?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-success mb-2">Vennligst betal for å bekrefte din ordre</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 order-md-0 mb-2">
            <div class="cart-totals w-100">
                <h2 class="title">Totalt i handlekurven</h2>
                <ul>
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
        <div class="col-md-4 text-center order-md-1 mb-2">
            <div class="center-block">
                <!-- Additional code for payment options can be added here -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card mb-1">
                <div class="card-body">
                    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="custom_payment_form">
                        <input type="hidden" name="payment_method" value="quickpay">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <button type="submit" name="complete-order" id="complete-order"
                            class="btn btn-outline btn-block btn-lg">Proceed To Quickpay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
// Include the necessary theme files
get_footer();
?>