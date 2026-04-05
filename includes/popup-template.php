<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function onepaqucpro_is_rendering_popup_checkout()
{
  return !empty($GLOBALS['onepaqucpro_popup_checkout_context_active']);
}

function onepaqucpro_render_popup_checkout_context_marker()
{
  if (!onepaqucpro_is_rendering_popup_checkout() && (!function_exists('onepaqucpro_request_has_popup_checkout_context') || !onepaqucpro_request_has_popup_checkout_context())) {
    return;
  }

  echo '<input type="hidden" name="onepaqucpro_popup_checkout_context" value="1" />';
}
add_action('woocommerce_review_order_after_submit', 'onepaqucpro_render_popup_checkout_context_marker', 5);

function onepaqucpro_rmenupro_checkout($isonepagewidget = false)
{
  $context_key = 'onepaqucpro_popup_checkout_context_active';
  $had_context = array_key_exists($context_key, $GLOBALS);
  $previous_context = $had_context ? $GLOBALS[$context_key] : null;

  if (!$isonepagewidget) {
    $GLOBALS[$context_key] = true;
  }
?>
  <div class="popup-content">
    <?php if (!$isonepagewidget) { ?>
      <div style=" display: flex; justify-content: space-between; ">
        <h2><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></h2>
        <div class="close_button" onclick="closeCheckoutPopup()">
        </div>
      </div>

    <?php } ?>

    <div class="popup-message"></div>
    <div id="checkout-form"><?php echo do_shortcode('[woocommerce_checkout]'); ?></div>
    <?php
    if (!$isonepagewidget) {
      if ($had_context) {
        $GLOBALS[$context_key] = $previous_context;
      } else {
        unset($GLOBALS[$context_key]);
      }
    }
    ?>
    <?php if (get_option("hide_product")) { ?>
      <div class="cart-subtotal">
        <span><?php echo get_option("txt_total") ? esc_attr(get_option("txt_total", 'Subtotal: ')) : "Subtotal: "; ?> <?php echo wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
      </div><?php } ?>
  </div>
<?php
}
