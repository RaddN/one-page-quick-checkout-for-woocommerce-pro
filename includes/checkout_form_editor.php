<style>
    .plugincy_container .checkout-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .plugincy_container .checkout-form {
        padding-top: 10px;
    }

    .plugincy_container .order-review {
        background: #f8f9fa;
        padding: 30px;
        border-left: 1px solid #ddd;
    }

    .plugincy_container .form-section {
        margin-bottom: 30px;
    }

    .plugincy_container .section-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0px;
        color: #333;
        position: relative;
    }

    .plugincy_container .editable-field {
        position: relative;
        margin-bottom: 20px;
        border: 2px solid transparent;
        border-radius: 4px;
        padding: 5px;
        transition: all 0.3s ease;
    }

    .plugincy_container .editable-field:hover {
        border-color: #0073aa;
        background: #f8f9ff;
    }

    .plugincy_container .field-controls {
        position: absolute;
        top: -10px;
        right: -10px;
        display: none;
        gap: 5px;
        z-index: 10;
    }

    .plugincy_container .editable-field:hover>.field-controls {
        display: flex;
    }

    .plugincy_container .control-btn {
        width: 24px;
        height: 24px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .plugincy_container .edit-btn {
        background: #0073aa;
        color: white;
    }

    .plugincy_container .hide-btn {
        background: #dc3545;
        color: white;
    }

    .plugincy_container .show-btn {
        background: #28a745;
        color: white;
    }

    .plugincy_container .form-row {
        display: grid;
        gap: 15px;
        margin-bottom: 20px;
    }

    .plugincy_container .form-row.row-2 {
        grid-template-columns: 1fr 1fr;
    }

    .plugincy_container .form-field,
    .plugincy_modal .form-field {
        display: flex;
        flex-direction: column;
    }

    .plugincy_container .form-field label,
    .plugincy_modal .form-field label {
        margin-bottom: 5px;
        font-weight: 500;
        color: #555;
    }

    .plugincy_container .form-field input,
    .plugincy_container .form-field select,
    .plugincy_container .form-field textarea,
    .plugincy_modal .form-field input,
    .plugincy_modal .form-field select,
    .plugincy_modal .form-field textarea {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .plugincy_container .form-field input:focus,
    .plugincy_container .form-field select:focus,
    .plugincy_container .form-field textarea:focus {
        outline: none;
        border-color: #0073aa;
        box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
    }

    .plugincy_container .required {
        color: #dc3545;
    }

    .plugincy_container .plugincy-hidden-field {
        opacity: 0.5;
    }

    .plugincy_container .coupon-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 4px;
    }

    .plugincy_container .order-summary {
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .plugincy_container .order-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .plugincy_container .order-item:last-child {
        border-bottom: none;
    }

    .plugincy_container .order-total {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 16px;
    }

    .plugincy_container .payment-methods {
        margin-top: 20px;
    }

    .plugincy_container .payment-method {
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .plugincy_container .payment-method label {
        display: block;
        padding: 15px;
        cursor: pointer;
        font-weight: 500;
    }

    .plugincy_container .place-order-btn {
        width: 100%;
        background: #0073aa;
        color: white;
        border: none;
        padding: 15px;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 20px;
    }

    .plugincy_container .place-order-btn:hover {
        background: #005a87;
    }

    /* Modal Styles */
    .plugincy_modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .plugincy_modal .modal-content {
        background: white;
        max-width: 500px;
        margin: 10% auto;
        padding: 30px;
        border-radius: 8px;
        position: relative;
    }

    .plugincy_modal .modal-header {
        margin-bottom: 20px;
    }

    .plugincy_modal .modal-header h3 {
        margin-bottom: 10px;
        color: #333;
    }

    .plugincy_modal .close-btn {
        position: absolute;
        top: 15px;
        right: 20px;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }

    .plugincy_modal .modal-form {
        display: grid;
        gap: 15px;
    }

    .plugincy_modal .modal-form .form-field {
        display: grid;
        grid-template-columns: 120px 1fr;
        align-items: center;
        gap: 15px;
    }

    .plugincy_modal .modal-form label {
        font-weight: 500;
        color: #555;
    }

    .plugincy_modal .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .plugincy_modal .btn,
    .plugincy_container .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }

    .plugincy_modal .btn-primary,
    .plugincy_container .btn-primary {
        background: #0073aa;
        color: white;
    }

    .plugincy_modal .btn-secondary,
    .plugincy_container .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .plugincy_modal .toggle-switch,
    .plugincy_container .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .plugincy_modal .toggle-switch input,
    .plugincy_container .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .plugincy_modal .toggle-slider,
    .plugincy_container .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .plugincy_modal .toggle-slider:before,
    .plugincy_container .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .plugincy_modal input:checked+.toggle-slider,
    .plugincy_container input:checked+.toggle-slider {
        background-color: #0073aa;
    }

    .plugincy_modal input:checked+.toggle-slider:before,
    .plugincy_container input:checked+.toggle-slider:before {
        transform: translateX(26px);
    }

    .shipping_disabled,.payments_disabled {
        opacity: 0.5 !important;
        position: relative;
        overflow: hidden;
    }

    .shipping_disabled:hover:before, 
    .payments_disabled:hover:before 
    {
        content: "Shipping is not configured";
        position: absolute;
        background: red;
        z-index: 9999;
        height: 100%;
        width: 100%;
        left: 0;
        top: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #fff;
    }
    .payments_disabled:hover:before{
        content: "Payment method is not configured";
    }

    @media (max-width: 768px) {

        .plugincy_modal .checkout-container,
        .plugincy_container .checkout-container {
            grid-template-columns: 1fr;
        }

        .plugincy_modal .form-row.row-2,
        .plugincy_container .form-row.row-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php
$default_config = array(
        'coupon-section' => array(
            'visible' => true,
            'title' => 'Have a coupon?',
            'description' => 'If you have a coupon code, please apply it below.',
            'placeholder' => 'Coupon code',
            'button' => 'Apply Coupon'
        ),
        'billing-title' => array(
            'visible' => true,
            'text' => 'Billing details'
        ),
        'first-name' => array(
            'visible' => true,
            'label' => 'First name',
            'placeholder' => 'Enter your first name',
            'required' => true
        ),
        'last-name' => array(
            'visible' => true,
            'label' => 'Last name',
            'placeholder' => 'Enter your last name',
            'required' => true
        ),
        'email' => array(
            'visible' => true,
            'label' => 'Email address',
            'placeholder' => 'Enter your email address',
            'required' => true
        ),
        'phone' => array(
            'visible' => true,
            'label' => 'Phone number',
            'placeholder' => 'Enter your phone number',
            'required' => false
        ),
        'country' => array(
            'visible' => true,
            'label' => 'Country / Region',
            'required' => true
        ),
        'address' => array(
            'visible' => true,
            'label' => 'Street address',
            'placeholder' => 'House number and street name',
            'required' => true
        ),
        'address2' => array(
            'visible' => true,
            'placeholder' => 'Apartment, suite, unit, etc. (optional)'
        ),
        'city' => array(
            'visible' => true,
            'label' => 'Town / City',
            'placeholder' => 'Enter your city',
            'required' => true
        ),
        'state' => array(
            'visible' => true,
            'label' => 'State / District',
            'placeholder' => 'Enter your state',
            'required' => true
        ),
        'postcode' => array(
            'visible' => true,
            'label' => 'Postcode / ZIP',
            'placeholder' => 'Enter your postcode',
            'required' => true
        ),
        'company' => array(
            'visible' => true,
            'label' => 'Company name',
            'placeholder' => 'Enter your company name',
            'required' => false
        ),
        'ship-to-different' => array(
            'visible' => true,
            'label' => 'Ship to a different address?'
        ),
        'shipping-first-name' => array(
            'visible' => true,
            'label' => 'First name',
            'placeholder' => 'Enter shipping first name',
            'required' => true
        ),
        'shipping-last-name' => array(
            'visible' => true,
            'label' => 'Last name',
            'placeholder' => 'Enter your shipping last name',
            'required' => true
        ),
        'shipping-country' => array(
            'visible' => true,
            'label' => 'Country / Region',
            'required' => true
        ),
        'shipping-address' => array(
            'visible' => true,
            'label' => 'Street address',
            'placeholder' => 'House number and street name',
            'required' => true
        ),
        'shipping-address2' => array(
            'visible' => true,
            'placeholder' => 'Apartment, suite, unit, etc. (optional)'
        ),
        'shipping-city' => array(
            'visible' => true,
            'label' => 'Town / City',
            'placeholder' => 'Enter shipping city',
            'required' => true
        ),
        'shipping-state' => array(
            'visible' => true,
            'label' => 'State / District',
            'placeholder' => 'Enter shipping state',
            'required' => true
        ),
        'shipping-postcode' => array(
            'visible' => true,
            'label' => 'Postcode / ZIP',
            'placeholder' => 'Enter shipping postcode',
            'required' => true
        ),
        'shipping-company' => array(
            'visible' => true,
            'label' => 'Company name',
            'placeholder' => 'Enter shipping company name',
            'required' => false
        ),
        'additional-title' => array(
            'visible' => true,
            'text' => 'Additional information'
        ),
        'order-notes' => array(
            'visible' => true,
            'label' => 'Order notes',
            'placeholder' => 'Notes about your order, e.g. special notes for delivery.',
            'required' => false
        ),
        'order-review-title' => array(
            'visible' => true,
            'text' => 'Your order'
        ),
        'order-summary' => array(
            'visible' => true
        ),
        'product-header' => array(
            'visible' => true,
            'text' => 'Product'
        ),
        'subtotal-header' => array(
            'visible' => true,
            'text' => 'Subtotal'
        ),
        'order-item' => array(
            'visible' => true,
            'text' => 'Sample Product × 1'
        ),
        'order-item-price' => array(
            'visible' => true,
            'text' => '$29.99'
        ),
        'subtotal2' => array(
            'visible' => true
        ),
        'subtotal-price' => array(
            'visible' => true,
            'text' => '$29.99'
        ),
        'shipping' => array(
            'visible' => true,
            'text' => 'Shipping'
        ),
        'shipping-price' => array(
            'visible' => true,
            'text' => '$5.00'
        ),
        'total-header' => array(
            'visible' => true,
            'text' => 'Total'
        ),
        'total-price' => array(
            'visible' => true,
            'text' => '$34.99'
        ),
        'payment-methods' => array(
            'visible' => true
        ),
        'privacy-policy' => array(
            'visible' => true,
            'text' => 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.'
        ),
        'place-order' => array(
            'visible' => true,
            'text' => 'Place order'
        )
    );
$checkout_form_setup = get_option("checkout_form_setup", json_encode($default_config));
$dataObject = json_decode($checkout_form_setup);
if (class_exists('WC_Shipping_Zones')) {
    $shipping_zones = WC_Shipping_Zones::get_zones();
}
if ( class_exists( 'WC_Payment_Gateways' ) ) {
    $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
}
?>
<div class="plugincy_container <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : ''; ?>">

    <div class="checkout-container">
        <div class="checkout-form">
            <!-- Coupon Section -->
            <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'coupon-section'}->{'visible'}) && !$dataObject->{'coupon-section'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="coupon-section">
                <div class="field-controls">
                    <button class="control-btn edit-btn" onclick="editField('coupon-section')" title="Edit">✎</button>
                    <button class="control-btn <?php echo (isset($dataObject->{'coupon-section'}->{'visible'}) && !$dataObject->{'coupon-section'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('coupon-section')" title="<?php echo (isset($dataObject->{'coupon-section'}->{'visible'}) && !$dataObject->{'coupon-section'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                        <?php echo (isset($dataObject->{'coupon-section'}->{'visible'}) && !$dataObject->{'coupon-section'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                    </button>
                </div>
                <div class="coupon-section">
                    <h4 style=" display: inline; " id="coupon-title"><?php echo isset($dataObject->{'coupon-section'}->{'title'}) ? $dataObject->{'coupon-section'}->{'title'} : 'Have a coupon?'; ?></h4>
                    <a id="coupon-description"><?php echo isset($dataObject->{'coupon-section'}->{'description'}) ? $dataObject->{'coupon-section'}->{'description'} : 'If you have a coupon code, please apply it below.'; ?></a>
                    <div class="form-row" style="margin-top: 15px;">
                        <div class="form-field">
                            <input type="text" placeholder="<?php echo isset($dataObject->{'coupon-section'}->{'placeholder'}) ? $dataObject->{'coupon-section'}->{'placeholder'} : 'Coupon code'; ?>" id="coupon-input">
                        </div>
                        <button class="btn btn-primary" id="apply-coupon-btn"><?php echo isset($dataObject->{'coupon-section'}->{'button'}) ? $dataObject->{'coupon-section'}->{'button'} : 'Apply Coupon'; ?></button>
                    </div>
                </div>
            </div>

            <!-- Billing Details -->
            <div class="form-section">
                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'billing-title'}->{'visible'}) && !$dataObject->{'billing-title'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="billing-title">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('billing-title')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'billing-title'}->{'visible'}) && !$dataObject->{'billing-title'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('billing-title')" title="<?php echo (isset($dataObject->{'billing-title'}->{'visible'}) && !$dataObject->{'billing-title'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'billing-title'}->{'visible'}) && !$dataObject->{'billing-title'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <h2 class="section-title" id="billing-title"><?php echo isset($dataObject->{'billing-title'}->{'text'}) ? $dataObject->{'billing-title'}->{'text'} : 'Billing details'; ?></h2>
                </div>

                <div class="form-row row-2">
                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'first-name'}->{'visible'}) && !$dataObject->{'first-name'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="first-name">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('first-name')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'first-name'}->{'visible'}) && !$dataObject->{'first-name'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('first-name')" title="<?php echo (isset($dataObject->{'first-name'}->{'visible'}) && !$dataObject->{'first-name'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'first-name'}->{'visible'}) && !$dataObject->{'first-name'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="first-name-input">
                                <?php echo isset($dataObject->{'first-name'}->{'label'}) ? $dataObject->{'first-name'}->{'label'} : 'First name'; ?>
                                <?php if (isset($dataObject->{'first-name'}->{'required'}) && $dataObject->{'first-name'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="first-name-input" placeholder="<?php echo isset($dataObject->{'first-name'}->{'placeholder'}) ? $dataObject->{'first-name'}->{'placeholder'} : 'Enter your first name'; ?>">
                        </div>
                    </div>

                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'last-name'}->{'visible'}) && !$dataObject->{'last-name'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="last-name">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('last-name')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'last-name'}->{'visible'}) && !$dataObject->{'last-name'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('last-name')" title="<?php echo (isset($dataObject->{'last-name'}->{'visible'}) && !$dataObject->{'last-name'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'last-name'}->{'visible'}) && !$dataObject->{'last-name'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="last-name-input">
                                <?php echo isset($dataObject->{'last-name'}->{'label'}) ? $dataObject->{'last-name'}->{'label'} : 'last name'; ?>
                                <?php if (isset($dataObject->{'last-name'}->{'required'}) && $dataObject->{'last-name'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="last-name-input" placeholder="<?php echo isset($dataObject->{'last-name'}->{'placeholder'}) ? $dataObject->{'last-name'}->{'placeholder'} : 'Enter your last name'; ?>">
                        </div>
                    </div>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'email'}->{'visible'}) && !$dataObject->{'email'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="email">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('email')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'email'}->{'visible'}) && !$dataObject->{'email'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('email')" title="<?php echo (isset($dataObject->{'email'}->{'visible'}) && !$dataObject->{'email'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'email'}->{'visible'}) && !$dataObject->{'email'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="email-input">
                            <?php echo isset($dataObject->{'email'}->{'label'}) ? $dataObject->{'email'}->{'label'} : 'email address'; ?>
                            <?php if (isset($dataObject->{'email'}->{'required'}) && $dataObject->{'email'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="email-input" placeholder="<?php echo isset($dataObject->{'email'}->{'placeholder'}) ? $dataObject->{'email'}->{'placeholder'} : 'Enter your email address'; ?>">
                    </div>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'phone'}->{'visible'}) && !$dataObject->{'phone'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="phone">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('phone')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'phone'}->{'visible'}) && !$dataObject->{'phone'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('phone')" title="<?php echo (isset($dataObject->{'phone'}->{'visible'}) && !$dataObject->{'phone'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'phone'}->{'visible'}) && !$dataObject->{'phone'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="phone-input">
                            <?php echo isset($dataObject->{'phone'}->{'label'}) ? $dataObject->{'phone'}->{'label'} : 'phone number'; ?>
                            <?php if (isset($dataObject->{'phone'}->{'required'}) && $dataObject->{'phone'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="phone-input" placeholder="<?php echo isset($dataObject->{'phone'}->{'placeholder'}) ? $dataObject->{'phone'}->{'placeholder'} : 'Enter your phone number'; ?>">
                    </div>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'country'}->{'visible'}) && !$dataObject->{'country'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="country">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('country')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'country'}->{'visible'}) && !$dataObject->{'country'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('country')" title="<?php echo (isset($dataObject->{'country'}->{'visible'}) && !$dataObject->{'country'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'country'}->{'visible'}) && !$dataObject->{'country'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="country-input">
                            <?php echo isset($dataObject->{'country'}->{'label'}) ? $dataObject->{'country'}->{'label'} : 'Country / Region'; ?>
                            <?php if (isset($dataObject->{'country'}->{'required'}) && $dataObject->{'country'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <select id="country-input">
                            <option value="">Select a country</option>
                            <option value="BD">Bangladesh</option>
                            <option value="US">United States</option>
                            <option value="UK">United Kingdom</option>
                            <option value="CA">Canada</option>
                        </select>
                    </div>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'address'}->{'visible'}) && !$dataObject->{'address'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="address">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('address')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'address'}->{'visible'}) && !$dataObject->{'address'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('address')" title="<?php echo (isset($dataObject->{'address'}->{'visible'}) && !$dataObject->{'address'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'address'}->{'visible'}) && !$dataObject->{'address'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="address-input">
                            <?php echo isset($dataObject->{'address'}->{'label'}) ? $dataObject->{'address'}->{'label'} : 'Street address'; ?>
                            <?php if (isset($dataObject->{'address'}->{'required'}) && $dataObject->{'address'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="address-input" placeholder="<?php echo isset($dataObject->{'address'}->{'placeholder'}) ? $dataObject->{'address'}->{'placeholder'} : 'House number and street name'; ?>">
                    </div>
                </div>
                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'address2'}->{'visible'}) && !$dataObject->{'address2'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="address2">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('address2')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'address2'}->{'visible'}) && !$dataObject->{'address2'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('address2')" title="<?php echo (isset($dataObject->{'address2'}->{'visible'}) && !$dataObject->{'address2'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'address2'}->{'visible'}) && !$dataObject->{'address2'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <input type="text" id="address2-input" placeholder="<?php echo isset($dataObject->{'address2'}->{'placeholder'}) ? $dataObject->{'address2'}->{'placeholder'} : 'Apartment, suite, unit, etc. (optional)'; ?>">
                    </div>
                </div>

                <div class="form-row row-2">
                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'city'}->{'visible'}) && !$dataObject->{'city'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="city">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('city')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'city'}->{'visible'}) && !$dataObject->{'city'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('city')" title="<?php echo (isset($dataObject->{'city'}->{'visible'}) && !$dataObject->{'city'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'city'}->{'visible'}) && !$dataObject->{'city'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="city-input">
                                <?php echo isset($dataObject->{'city'}->{'label'}) ? $dataObject->{'city'}->{'label'} : 'Town / City'; ?>
                                <?php if (isset($dataObject->{'city'}->{'required'}) && $dataObject->{'city'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="city-input" placeholder="<?php echo isset($dataObject->{'city'}->{'placeholder'}) ? $dataObject->{'city'}->{'placeholder'} : 'Enter your city'; ?>">
                        </div>
                    </div>

                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'state'}->{'visible'}) && !$dataObject->{'state'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="state">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('state')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'state'}->{'visible'}) && !$dataObject->{'state'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('state')" title="<?php echo (isset($dataObject->{'state'}->{'visible'}) && !$dataObject->{'state'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'state'}->{'visible'}) && !$dataObject->{'state'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="state-input">
                                <?php echo isset($dataObject->{'state'}->{'label'}) ? $dataObject->{'state'}->{'label'} : 'State / District'; ?>
                                <?php if (isset($dataObject->{'state'}->{'required'}) && $dataObject->{'state'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="state-input" placeholder="<?php echo isset($dataObject->{'state'}->{'placeholder'}) ? $dataObject->{'state'}->{'placeholder'} : 'Enter your state'; ?>">
                        </div>
                    </div>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'postcode'}->{'visible'}) && !$dataObject->{'postcode'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="postcode">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('postcode')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'postcode'}->{'visible'}) && !$dataObject->{'postcode'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('postcode')" title="<?php echo (isset($dataObject->{'postcode'}->{'visible'}) && !$dataObject->{'postcode'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'postcode'}->{'visible'}) && !$dataObject->{'postcode'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="postcode-input">
                            <?php echo isset($dataObject->{'postcode'}->{'label'}) ? $dataObject->{'postcode'}->{'label'} : 'Postcode / ZIP'; ?>
                            <?php if (isset($dataObject->{'postcode'}->{'required'}) && $dataObject->{'postcode'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="postcode-input" placeholder="<?php echo isset($dataObject->{'postcode'}->{'placeholder'}) ? $dataObject->{'postcode'}->{'placeholder'} : 'Enter your postcode'; ?>">
                    </div>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'company'}->{'visible'}) && !$dataObject->{'company'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="company">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('company')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'company'}->{'visible'}) && !$dataObject->{'company'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('company')" title="<?php echo (isset($dataObject->{'company'}->{'visible'}) && !$dataObject->{'company'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'company'}->{'visible'}) && !$dataObject->{'company'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="company-input">
                            <?php echo isset($dataObject->{'company'}->{'label'}) ? $dataObject->{'company'}->{'label'} : 'Company name (optional)'; ?>
                            <?php if (isset($dataObject->{'company'}->{'required'}) && $dataObject->{'company'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="company-input" placeholder="<?php echo isset($dataObject->{'company'}->{'placeholder'}) ? $dataObject->{'company'}->{'placeholder'} : 'Enter your company name'; ?>">
                    </div>
                </div>
            </div>

            <!-- shipping Details -->
            <div class="form-section <?php echo isset($shipping_zones) && empty($shipping_zones) ? 'shipping_disabled' : ''; ?>">
                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'ship-to-different'}->{'visible'}) && !$dataObject->{'ship-to-different'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="ship-to-different">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('ship-to-different')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'ship-to-different'}->{'visible'}) && !$dataObject->{'ship-to-different'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('ship-to-different')" title="<?php echo (isset($dataObject->{'ship-to-different'}->{'visible'}) && !$dataObject->{'ship-to-different'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'ship-to-different'}->{'visible'}) && !$dataObject->{'ship-to-different'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label>
                            <input type="checkbox" id="ship-to-different-checkbox" onchange="toggleShippingFields()">
                            <span id="ship-to-different-text"><?php echo isset($dataObject->{'ship-to-different'}->{'label'}) ? $dataObject->{'ship-to-different'}->{'label'} : 'Ship to a different address?'; ?></span>
                        </label>
                    </div>
                </div>

                <div id="shipping-fields" style="display: none;">
                    <div class="form-row row-2">
                        <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-first-name'}->{'visible'}) && !$dataObject->{'shipping-first-name'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-first-name">
                            <div class="field-controls">
                                <button class="control-btn edit-btn" onclick="editField('shipping-first-name')" title="Edit">✎</button>
                                <button class="control-btn <?php echo (isset($dataObject->{'shipping-first-name'}->{'visible'}) && !$dataObject->{'shipping-first-name'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-first-name')" title="<?php echo (isset($dataObject->{'shipping-first-name'}->{'visible'}) && !$dataObject->{'shipping-first-name'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                    <?php echo (isset($dataObject->{'shipping-first-name'}->{'visible'}) && !$dataObject->{'shipping-first-name'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                                </button>
                            </div>
                            <div class="form-field">
                                <label for="shipping-first-name-input">
                                    <?php echo isset($dataObject->{'shipping-first-name'}->{'label'}) ? $dataObject->{'shipping-first-name'}->{'label'} : 'First name'; ?>
                                    <?php if (isset($dataObject->{'shipping-first-name'}->{'required'}) && $dataObject->{'shipping-first-name'}->{'required'}) : ?>
                                        <span class="required">*</span>
                                    <?php else : ?>
                                        <span>(optional)</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="shipping-first-name-input" placeholder="<?php echo isset($dataObject->{'shipping-first-name'}->{'placeholder'}) ? $dataObject->{'shipping-first-name'}->{'placeholder'} : 'Enter shipping first name'; ?>">
                            </div>
                        </div>

                        <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-last-name'}->{'visible'}) && !$dataObject->{'shipping-last-name'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-last-name">
                            <div class="field-controls">
                                <button class="control-btn edit-btn" onclick="editField('shipping-last-name')" title="Edit">✎</button>
                                <button class="control-btn <?php echo (isset($dataObject->{'shipping-last-name'}->{'visible'}) && !$dataObject->{'shipping-last-name'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-last-name')" title="<?php echo (isset($dataObject->{'shipping-last-name'}->{'visible'}) && !$dataObject->{'shipping-last-name'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                    <?php echo (isset($dataObject->{'shipping-last-name'}->{'visible'}) && !$dataObject->{'shipping-last-name'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                                </button>
                            </div>
                            <div class="form-field">
                                <label for="shipping-last-name-input">
                                    <?php echo isset($dataObject->{'shipping-last-name'}->{'label'}) ? $dataObject->{'shipping-last-name'}->{'label'} : 'last name'; ?>
                                    <?php if (isset($dataObject->{'shipping-last-name'}->{'required'}) && $dataObject->{'shipping-last-name'}->{'required'}) : ?>
                                        <span class="required">*</span>
                                    <?php else : ?>
                                        <span>(optional)</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="shipping-last-name-input" placeholder="<?php echo isset($dataObject->{'shipping-last-name'}->{'placeholder'}) ? $dataObject->{'shipping-last-name'}->{'placeholder'} : 'Enter your shipping last name'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-country'}->{'visible'}) && !$dataObject->{'shipping-country'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-country">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('shipping-country')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping-country'}->{'visible'}) && !$dataObject->{'shipping-country'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-country')" title="<?php echo (isset($dataObject->{'shipping-country'}->{'visible'}) && !$dataObject->{'shipping-country'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping-country'}->{'visible'}) && !$dataObject->{'shipping-country'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="shipping-country-input">
                                <?php echo isset($dataObject->{'shipping-country'}->{'label'}) ? $dataObject->{'shipping-country'}->{'label'} : 'shipping-country / Region'; ?>
                                <?php if (isset($dataObject->{'shipping-country'}->{'required'}) && $dataObject->{'shipping-country'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <select id="shipping-country-input">
                                <option value="">Select a shipping-country</option>
                                <option value="BD">Bangladesh</option>
                                <option value="US">United States</option>
                                <option value="UK">United Kingdom</option>
                                <option value="CA">Canada</option>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-address'}->{'visible'}) && !$dataObject->{'shipping-address'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-address">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('shipping-address')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping-address'}->{'visible'}) && !$dataObject->{'shipping-address'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-address')" title="<?php echo (isset($dataObject->{'shipping-address'}->{'visible'}) && !$dataObject->{'shipping-address'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping-address'}->{'visible'}) && !$dataObject->{'shipping-address'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="shipping-address-input">
                                <?php echo isset($dataObject->{'shipping-address'}->{'label'}) ? $dataObject->{'shipping-address'}->{'label'} : 'Street shipping-address'; ?>
                                <?php if (isset($dataObject->{'shipping-address'}->{'required'}) && $dataObject->{'shipping-address'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="shipping-address-input" placeholder="<?php echo isset($dataObject->{'shipping-address'}->{'placeholder'}) ? $dataObject->{'shipping-address'}->{'placeholder'} : 'House number and street name'; ?>">
                        </div>
                    </div>
                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-address2'}->{'visible'}) && !$dataObject->{'shipping-address2'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-address2">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('shipping-address2')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping-address2'}->{'visible'}) && !$dataObject->{'shipping-address2'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-address2')" title="<?php echo (isset($dataObject->{'shipping-address2'}->{'visible'}) && !$dataObject->{'shipping-address2'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping-address2'}->{'visible'}) && !$dataObject->{'shipping-address2'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <input type="text" id="shipping-address2-input" placeholder="<?php echo isset($dataObject->{'shipping-address2'}->{'placeholder'}) ? $dataObject->{'shipping-address2'}->{'placeholder'} : 'Apartment, suite, unit, etc. (optional)'; ?>">
                        </div>
                    </div>

                    <div class="form-row row-2">
                        <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-city'}->{'visible'}) && !$dataObject->{'shipping-city'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-city">
                            <div class="field-controls">
                                <button class="control-btn edit-btn" onclick="editField('shipping-city')" title="Edit">✎</button>
                                <button class="control-btn <?php echo (isset($dataObject->{'shipping-city'}->{'visible'}) && !$dataObject->{'shipping-city'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-city')" title="<?php echo (isset($dataObject->{'shipping-city'}->{'visible'}) && !$dataObject->{'shipping-city'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                    <?php echo (isset($dataObject->{'shipping-city'}->{'visible'}) && !$dataObject->{'shipping-city'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                                </button>
                            </div>
                            <div class="form-field">
                                <label for="shipping-city-input">
                                    <?php echo isset($dataObject->{'shipping-city'}->{'label'}) ? $dataObject->{'shipping-city'}->{'label'} : 'Town / shipping-city'; ?>
                                    <?php if (isset($dataObject->{'shipping-city'}->{'required'}) && $dataObject->{'shipping-city'}->{'required'}) : ?>
                                        <span class="required">*</span>
                                    <?php else : ?>
                                        <span>(optional)</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="shipping-city-input" placeholder="<?php echo isset($dataObject->{'shipping-city'}->{'placeholder'}) ? $dataObject->{'shipping-city'}->{'placeholder'} : 'Enter shipping city'; ?>">
                            </div>
                        </div>

                        <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-state'}->{'visible'}) && !$dataObject->{'shipping-state'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-state">
                            <div class="field-controls">
                                <button class="control-btn edit-btn" onclick="editField('shipping-state')" title="Edit">✎</button>
                                <button class="control-btn <?php echo (isset($dataObject->{'shipping-state'}->{'visible'}) && !$dataObject->{'shipping-state'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-state')" title="<?php echo (isset($dataObject->{'shipping-state'}->{'visible'}) && !$dataObject->{'shipping-state'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                    <?php echo (isset($dataObject->{'shipping-state'}->{'visible'}) && !$dataObject->{'shipping-state'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                                </button>
                            </div>
                            <div class="form-field">
                                <label for="shipping-state-input">
                                    <?php echo isset($dataObject->{'shipping-state'}->{'label'}) ? $dataObject->{'shipping-state'}->{'label'} : 'shipping-state / District'; ?>
                                    <?php if (isset($dataObject->{'shipping-state'}->{'required'}) && $dataObject->{'shipping-state'}->{'required'}) : ?>
                                        <span class="required">*</span>
                                    <?php else : ?>
                                        <span>(optional)</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="shipping-state-input" placeholder="<?php echo isset($dataObject->{'shipping-state'}->{'placeholder'}) ? $dataObject->{'shipping-state'}->{'placeholder'} : 'Enter shipping state'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-postcode'}->{'visible'}) && !$dataObject->{'shipping-postcode'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-postcode">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('shipping-postcode')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping-postcode'}->{'visible'}) && !$dataObject->{'shipping-postcode'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-postcode')" title="<?php echo (isset($dataObject->{'shipping-postcode'}->{'visible'}) && !$dataObject->{'shipping-postcode'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping-postcode'}->{'visible'}) && !$dataObject->{'shipping-postcode'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="shipping-postcode-input">
                                <?php echo isset($dataObject->{'shipping-postcode'}->{'label'}) ? $dataObject->{'shipping-postcode'}->{'label'} : 'shipping-postcode / ZIP'; ?>
                                <?php if (isset($dataObject->{'shipping-postcode'}->{'required'}) && $dataObject->{'shipping-postcode'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="shipping-postcode-input" placeholder="<?php echo isset($dataObject->{'shipping-postcode'}->{'placeholder'}) ? $dataObject->{'shipping-postcode'}->{'placeholder'} : 'Enter  shipping postcode'; ?>">
                        </div>
                    </div>

                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-company'}->{'visible'}) && !$dataObject->{'shipping-company'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-company">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('shipping-company')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping-company'}->{'visible'}) && !$dataObject->{'shipping-company'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-company')" title="<?php echo (isset($dataObject->{'shipping-company'}->{'visible'}) && !$dataObject->{'shipping-company'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping-company'}->{'visible'}) && !$dataObject->{'shipping-company'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <div class="form-field">
                            <label for="shipping-company-input">
                                <?php echo isset($dataObject->{'shipping-company'}->{'label'}) ? $dataObject->{'shipping-company'}->{'label'} : 'shipping-company name (optional)'; ?>
                                <?php if (isset($dataObject->{'shipping-company'}->{'required'}) && $dataObject->{'shipping-company'}->{'required'}) : ?>
                                    <span class="required">*</span>
                                <?php else : ?>
                                    <span>(optional)</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="shipping-company-input" placeholder="<?php echo isset($dataObject->{'shipping-company'}->{'placeholder'}) ? $dataObject->{'shipping-company'}->{'placeholder'} : 'Enter shipping company name'; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-section">
                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'additional-title'}->{'visible'}) && !$dataObject->{'additional-title'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="additional-title">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('additional-title')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'additional-title'}->{'visible'}) && !$dataObject->{'additional-title'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('additional-title')" title="<?php echo (isset($dataObject->{'additional-title'}->{'visible'}) && !$dataObject->{'additional-title'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'additional-title'}->{'visible'}) && !$dataObject->{'additional-title'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <h2 class="section-title" id="additional-title"><?php echo isset($dataObject->{'additional-title'}->{'text'}) ? $dataObject->{'additional-title'}->{'text'} : 'Additional information'; ?></h2>
                </div>

                <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'order-notes'}->{'visible'}) && !$dataObject->{'order-notes'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="order-notes">
                    <div class="field-controls">
                        <button class="control-btn edit-btn" onclick="editField('order-notes')" title="Edit">✎</button>
                        <button class="control-btn <?php echo (isset($dataObject->{'order-notes'}->{'visible'}) && !$dataObject->{'order-notes'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('order-notes')" title="<?php echo (isset($dataObject->{'order-notes'}->{'visible'}) && !$dataObject->{'order-notes'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                            <?php echo (isset($dataObject->{'order-notes'}->{'visible'}) && !$dataObject->{'order-notes'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                        </button>
                    </div>
                    <div class="form-field">
                        <label for="order-notes-input">
                            <?php echo isset($dataObject->{'order-notes'}->{'label'}) ? $dataObject->{'order-notes'}->{'label'} : 'Order notes (optional)'; ?>
                            <?php if (isset($dataObject->{'order-notes'}->{'required'}) && $dataObject->{'order-notes'}->{'required'}) : ?>
                                <span class="required">*</span>
                            <?php else : ?>
                                <span>(optional)</span>
                            <?php endif; ?>
                        </label>
                        <textarea id="notes-input" rows="4"
                            placeholder="<?php echo isset($dataObject->{'order-notes'}->{'placeholder'}) ? $dataObject->{'order-notes'}->{'placeholder'} : 'Notes about your order, e.g. special notes for delivery.'; ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-review">
            <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'order-review-title'}->{'visible'}) && !$dataObject->{'order-review-title'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="order-review-title">
                <div class="field-controls">
                    <button class="control-btn edit-btn" onclick="editField('order-review-title')" title="Edit">✎</button>
                    <button class="control-btn <?php echo (isset($dataObject->{'order-review-title'}->{'visible'}) && !$dataObject->{'order-review-title'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('order-review-title')" title="<?php echo (isset($dataObject->{'order-review-title'}->{'visible'}) && !$dataObject->{'order-review-title'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                        <?php echo (isset($dataObject->{'order-review-title'}->{'visible'}) && !$dataObject->{'order-review-title'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                    </button>
                </div>
                <h2 class="section-title" id="order-review-title"><?php echo isset($dataObject->{'order-review-title'}->{'text'}) ? $dataObject->{'order-review-title'}->{'text'} : 'Your order'; ?></h2>
            </div>

            <div class="order-summary <?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'order-summary'}->{'visible'}) && !$dataObject->{'order-summary'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="order-summary">
                <div class="field-controls">
                    <button class="control-btn <?php echo (isset($dataObject->{'order-summary'}->{'visible'}) && !$dataObject->{'order-summary'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('order-summary')" title="<?php echo (isset($dataObject->{'order-summary'}->{'visible'}) && !$dataObject->{'order-summary'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                        <?php echo (isset($dataObject->{'order-summary'}->{'visible'}) && !$dataObject->{'order-summary'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                    </button>
                </div>
                <div class="order-item">
                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'product-header'}->{'visible'}) && !$dataObject->{'product-header'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="product-header">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('product-header')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'product-header'}->{'visible'}) && !$dataObject->{'product-header'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('product-header')" title="<?php echo (isset($dataObject->{'product-header'}->{'visible'}) && !$dataObject->{'product-header'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'product-header'}->{'visible'}) && !$dataObject->{'product-header'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="product-header-text"><?php echo isset($dataObject->{'product-header'}->{'text'}) ? $dataObject->{'product-header'}->{'text'} : 'Product'; ?></strong></span>
                    </div>
                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'subtotal-header'}->{'visible'}) && !$dataObject->{'subtotal-header'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="subtotal-header">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('subtotal-header')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'subtotal-header'}->{'visible'}) && !$dataObject->{'subtotal-header'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('subtotal-header')" title="<?php echo (isset($dataObject->{'subtotal-header'}->{'visible'}) && !$dataObject->{'subtotal-header'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'subtotal-header'}->{'visible'}) && !$dataObject->{'subtotal-header'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="subtotal-header-text"><?php echo isset($dataObject->{'subtotal-header'}->{'text'}) ? $dataObject->{'subtotal-header'}->{'text'} : 'Subtotal'; ?></strong></span>
                    </div>
                </div>
                <div class="order-item">
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'order-item'}->{'visible'}) && !$dataObject->{'order-item'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="order-item">
                        <div class="field-controls">
                            <button class="control-btn <?php echo (isset($dataObject->{'order-item'}->{'visible'}) && !$dataObject->{'order-item'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('order-item')" title="<?php echo (isset($dataObject->{'order-item'}->{'visible'}) && !$dataObject->{'order-item'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'order-item'}->{'visible'}) && !$dataObject->{'order-item'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="order-item-text"><?php echo isset($dataObject->{'order-item'}->{'text'}) ? $dataObject->{'order-item'}->{'text'} : 'Sample Product × 1'; ?></strong></span>
                    </div>
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'order-item-price'}->{'visible'}) && !$dataObject->{'order-item-price'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="order-item-price">
                        <div class="field-controls">
                            <button class="control-btn <?php echo (isset($dataObject->{'order-item-price'}->{'visible'}) && !$dataObject->{'order-item-price'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('order-item-price')" title="<?php echo (isset($dataObject->{'order-item-price'}->{'visible'}) && !$dataObject->{'order-item-price'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'order-item-price'}->{'visible'}) && !$dataObject->{'order-item-price'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="order-item-price-text"><?php echo isset($dataObject->{'order-item-price'}->{'text'}) ? $dataObject->{'order-item-price'}->{'text'} : '$29.99'; ?></strong></span>
                    </div>
                </div>
                <div class="order-item">
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'subtotal2'}->{'visible'}) && !$dataObject->{'subtotal2'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="subtotal2">
                        <div class="field-controls">
                            <button class="control-btn <?php echo (isset($dataObject->{'subtotal2'}->{'visible'}) && !$dataObject->{'subtotal2'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('subtotal2')" title="<?php echo (isset($dataObject->{'subtotal2'}->{'visible'}) && !$dataObject->{'subtotal2'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'subtotal2'}->{'visible'}) && !$dataObject->{'subtotal2'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="subtotal2-text"><?php echo isset($dataObject->{'subtotal-header'}->{'text'}) ? $dataObject->{'subtotal-header'}->{'text'} : 'Subtotal'; ?></strong></span>
                    </div>
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'subtotal-price'}->{'visible'}) && !$dataObject->{'subtotal-price'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="subtotal-price">
                        <div class="field-controls">
                            <button class="control-btn <?php echo (isset($dataObject->{'subtotal-price'}->{'visible'}) && !$dataObject->{'subtotal-price'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('subtotal-price')" title="<?php echo (isset($dataObject->{'subtotal-price'}->{'visible'}) && !$dataObject->{'subtotal-price'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'subtotal-price'}->{'visible'}) && !$dataObject->{'subtotal-price'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="subtotal-price-text"><?php echo isset($dataObject->{'subtotal-price'}->{'text'}) ? $dataObject->{'subtotal-price'}->{'text'} : '$29.99'; ?></strong></span>
                    </div>
                </div>
                <div class="order-item <?php echo isset($shipping_zones) && empty($shipping_zones) ? 'shipping_disabled' : ''; ?>">
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping'}->{'visible'}) && !$dataObject->{'shipping'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('shipping')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping'}->{'visible'}) && !$dataObject->{'shipping'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping')" title="<?php echo (isset($dataObject->{'shipping'}->{'visible'}) && !$dataObject->{'shipping'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping'}->{'visible'}) && !$dataObject->{'shipping'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="shipping-text"><?php echo isset($dataObject->{'shipping'}->{'text'}) ? $dataObject->{'shipping'}->{'text'} : 'Shipping'; ?></strong></span>
                    </div>
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'shipping-price'}->{'visible'}) && !$dataObject->{'shipping-price'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="shipping-price">
                        <div class="field-controls">
                            <button class="control-btn <?php echo (isset($dataObject->{'shipping-price'}->{'visible'}) && !$dataObject->{'shipping-price'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('shipping-price')" title="<?php echo (isset($dataObject->{'shipping-price'}->{'visible'}) && !$dataObject->{'shipping-price'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'shipping-price'}->{'visible'}) && !$dataObject->{'shipping-price'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="shipping-price-text"><?php echo isset($dataObject->{'shipping-price'}->{'text'}) ? $dataObject->{'shipping-price'}->{'text'} : '$29.99'; ?></strong></span>
                    </div>
                </div>
                <div class="order-item order-total">
                    <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'total-header'}->{'visible'}) && !$dataObject->{'total-header'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="total-header">
                        <div class="field-controls">
                            <button class="control-btn edit-btn" onclick="editField('total-header')" title="Edit">✎</button>
                            <button class="control-btn <?php echo (isset($dataObject->{'total-header'}->{'visible'}) && !$dataObject->{'total-header'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('total-header')" title="<?php echo (isset($dataObject->{'total-header'}->{'visible'}) && !$dataObject->{'total-header'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'total-header'}->{'visible'}) && !$dataObject->{'total-header'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="total-header-text"><?php echo isset($dataObject->{'total-header'}->{'text'}) ? $dataObject->{'total-header'}->{'text'} : 'Total'; ?></strong></span>
                    </div>
                    <div style="margin-bottom:0px;" class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'total-price'}->{'visible'}) && !$dataObject->{'total-price'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="total-price">
                        <div class="field-controls">
                            <button class="control-btn <?php echo (isset($dataObject->{'total-price'}->{'visible'}) && !$dataObject->{'total-price'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('total-price')" title="<?php echo (isset($dataObject->{'total-price'}->{'visible'}) && !$dataObject->{'total-price'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                                <?php echo (isset($dataObject->{'total-price'}->{'visible'}) && !$dataObject->{'total-price'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                            </button>
                        </div>
                        <span><strong id="total-price-text"><?php echo isset($dataObject->{'total-price'}->{'text'}) ? $dataObject->{'total-price'}->{'text'} : '$29.99'; ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'payment-methods'}->{'visible'}) && !$dataObject->{'payment-methods'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?> <?php echo isset($payment_gateways) && empty($payment_gateways) ? 'payments_disabled' : ''; ?>" data-field="payment-methods">
                <div class="field-controls">
                    <button class="control-btn <?php echo (isset($dataObject->{'payment-methods'}->{'visible'}) && !$dataObject->{'payment-methods'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('payment-methods')" title="<?php echo (isset($dataObject->{'payment-methods'}->{'visible'}) && !$dataObject->{'payment-methods'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                        <?php echo (isset($dataObject->{'payment-methods'}->{'visible'}) && !$dataObject->{'payment-methods'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                    </button>
                </div>
                <div class="payment-methods">
                    <?php if (!empty($payment_gateways)) : ?>
                        <?php foreach ($payment_gateways as $gateway) : ?>
                            <div class="payment-method">
                                <label>
                                    <input type="radio" name="payment" value="<?php echo esc_attr($gateway->id); ?>" <?php echo $gateway === reset($payment_gateways) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($gateway->get_title()); ?>
                                </label>
                                <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
                                    <div class="payment-box" style="display:none;">
                                        <?php $gateway->payment_fields(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div style="color: #dc3545; padding: 10px;">No payment methods are available.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'privacy-policy'}->{'visible'}) && !$dataObject->{'privacy-policy'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="privacy-policy">
                <div class="field-controls">
                    <!-- <button class="control-btn edit-btn" onclick="editField('privacy-policy')" title="Edit">✎</button> -->
                    <button class="control-btn <?php echo (isset($dataObject->{'privacy-policy'}->{'visible'}) && !$dataObject->{'privacy-policy'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('privacy-policy')" title="<?php echo (isset($dataObject->{'privacy-policy'}->{'visible'}) && !$dataObject->{'privacy-policy'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                        <?php echo (isset($dataObject->{'privacy-policy'}->{'visible'}) && !$dataObject->{'privacy-policy'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                    </button>
                </div>
                <div style="font-size: 12px; color: #666; margin: 15px 0;" id="privacy-text">
                    <?php echo isset($dataObject->{'privacy-policy'}->{'text'}) ? $dataObject->{'privacy-policy'}->{'text'} :
                        'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.'; ?>
                </div>
            </div>

            <div class="<?php echo !onepaqucpro_premium_feature() ? 'pro-only' : 'editable-field'; ?><?php echo (isset($dataObject->{'place-order'}->{'visible'}) && !$dataObject->{'place-order'}->{'visible'}) ? ' plugincy-hidden-field' : ''; ?>" data-field="place-order">
                <div class="field-controls">
                    <button class="control-btn edit-btn" onclick="editField('place-order')" title="Edit">✎</button>
                    <button class="control-btn <?php echo (isset($dataObject->{'place-order'}->{'visible'}) && !$dataObject->{'place-order'}->{'visible'}) ? 'show-btn' : 'hide-btn'; ?>" onclick="toggleField('place-order')" title="<?php echo (isset($dataObject->{'place-order'}->{'visible'}) && !$dataObject->{'place-order'}->{'visible'}) ? 'Show' : 'Hide'; ?>">
                        <?php echo (isset($dataObject->{'place-order'}->{'visible'}) && !$dataObject->{'place-order'}->{'visible'}) ? '👁‍🗨' : '👁'; ?>
                    </button>
                </div>
                <button class="place-order-btn" id="place-order-btn"><?php echo isset($dataObject->{'place-order'}->{'text'}) ? $dataObject->{'place-order'}->{'text'} : 'Place order'; ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="plugincy_modal" id="editModal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h3 id="modalTitle">Edit Field</h3>
            <p id="modalDescription">Customize the field properties below.</p>
        </div>
        <div class="modal-form" id="modalForm">
            <!-- Dynamic form fields will be inserted here -->
        </div>
        <div class="modal-buttons">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveFieldChanges()">Save Changes</button>
        </div>
    </div>
</div>

<script>
    let currentEditingField = null;
    let fieldSettings = {};

    // Initialize field settings
    function initializeFieldSettings() {
        const fields = document.querySelectorAll('.editable-field');
        fields.forEach(field => {
            const fieldId = field.dataset.field;
            fieldSettings[fieldId] = {
                visible: true,
                label: '',
                placeholder: '',
                required: false,
                text: ''
            };
        });
    }

    // Edit field function
    function editField(fieldId) {
        currentEditingField = fieldId;
        const modal = document.getElementById('editModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('modalForm');

        modalTitle.textContent = `Edit ${fieldId.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}`;

        // Generate form based on field type
        const formHTML = generateFieldForm(fieldId);
        modalForm.innerHTML = formHTML;

        // Populate current values
        populateCurrentValues(fieldId);

        modal.style.display = 'block';
    }

    // Generate form based on field type
    function generateFieldForm(fieldId) {
        let formHTML = '';

        // Common fields
        formHTML += `
                <div class="form-field">
                    <label>Visible:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="field-visible" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            `;

        // Field-specific forms
        switch (fieldId) {
            case 'billing-title':
            case 'additional-title':
            case 'order-review-title':
                formHTML += `
                        <div class="form-field">
                            <label>Title Text:</label>
                            <input type="text" id="field-text" placeholder="Enter title text">
                        </div>
                    `;
                break;

            case 'coupon-section':
                formHTML += `
                        <div class="form-field">
                            <label>Title:</label>
                            <input type="text" id="field-title" placeholder="Have a coupon?">
                        </div>
                        <div class="form-field">
                            <label>Description:</label>
                            <input type="text" id="field-description" placeholder="If you have a coupon code...">
                        </div>
                        <div class="form-field">
                            <label>Placeholder:</label>
                            <input type="text" id="field-placeholder" placeholder="Enter placeholder text">
                        </div>
                        <div class="form-field">
                            <label>Button Text:</label>
                            <input type="text" id="field-button" placeholder="Apply Coupon">
                        </div>
                    `;
                break;

            case 'place-order':
                formHTML += `
                        <div class="form-field">
                            <label>Button Text:</label>
                            <input type="text" id="field-text" placeholder="Place order">
                        </div>
                    `;
                break;

            case 'privacy-policy':
                formHTML += `
                        <div class="form-field">
                            <label>Privacy Text:</label>
                            <?php
                            $my_content = '';
                            wp_editor($my_content, 'field-text', array(
                                'textarea_rows' => 10,
                                'editor_type'   => 'tinymce',
                                'media_buttons' => false,
                                // 'quicktags'     => false,
                                'tinymce'       => array(
                                    'toolbar1' => 'formatselect,bold,italic,underline,link,unlink,bullist,numlist,blockquote,undo,redo,removeformat',
                                    'toolbar2' => '',
                                ),
                                'default_editor' => 'tinymce',
                            ));
                            ?>
                        </div>
                    `;
                break;

            case 'ship-to-different':
                formHTML += `
                <div class="form-field">
                    <label>Checkbox Text:</label>
                    <input type="text" id="field-text" placeholder="Ship to a different address?">
                </div>
            `;
                break;

            case 'product-header':
            case 'subtotal-header':
            case 'total-header':
            case 'shipping':
                formHTML += `
                <div class="form-field">
                    <label>Header Text:</label>
                    <input type="text" id="field-text" placeholder="Enter header text">
                </div>
            `;
                break;

            case 'shipping-first-name':
            case 'shipping-last-name':
            case 'shipping-country':
            case 'shipping-address':
            case 'shipping-city':
            case 'shipping-state':
            case 'shipping-postcode':
            case 'shipping-company':
                // Standard form field handling
                formHTML += `
                <div class="form-field">
                    <label>Label:</label>
                    <input type="text" id="field-label" placeholder="Enter field label">
                </div>
                <div class="form-field">
                    <label>Placeholder:</label>
                    <input type="text" id="field-placeholder" placeholder="Enter placeholder text">
                </div>
                <div class="form-field">
                    <label>Required:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="field-required">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            `;
                break;

            default:
                // Form input fields
                formHTML += `
                        <div class="form-field">
                            <label>Label:</label>
                            <input type="text" id="field-label" placeholder="Enter field label">
                        </div>
                        <div class="form-field">
                            <label>Placeholder:</label>
                            <input type="text" id="field-placeholder" placeholder="Enter placeholder text">
                        </div>
                        <div class="form-field">
                            <label>Required:</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="field-required">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    `;
                break;
        }

        return formHTML;
    }

    // Populate current values in modal
    function populateCurrentValues(fieldId) {
        const field = document.querySelector(`[data-field="${fieldId}"]`);
        const visibleCheckbox = document.getElementById('field-visible');

        // Set visibility
        visibleCheckbox.checked = !field.classList.contains('plugincy-hidden-field');

        // Set field-specific values
        switch (fieldId) {
            case 'billing-title':
                document.getElementById('field-text').value = document.getElementById('billing-title').textContent;
                break;
            case 'additional-title':
                document.getElementById('field-text').value = document.getElementById('additional-title').textContent;
                break;
            case 'order-review-title':
                document.getElementById('field-text').value = document.getElementById('order-review-title').textContent;
                break;
            case 'coupon-section':
                document.getElementById('field-title').value = document.getElementById('coupon-title').textContent;
                document.getElementById('field-description').value = document.getElementById('coupon-description').textContent;
                document.getElementById('field-button').value = document.getElementById('apply-coupon-btn').textContent;
                const couponinput = field.querySelector('input, select, textarea');
                if (couponinput && document.getElementById('field-placeholder')) {
                    document.getElementById('field-placeholder').value = couponinput.placeholder || '';
                }
                break;
            case 'place-order':
                document.getElementById('field-text').value = document.getElementById('place-order-btn').textContent;
                break;
            case 'privacy-policy':
                // Set content in wp_editor using tinyMCE
                setTimeout(() => {
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('field-text')) {
                        tinyMCE.get('field-text').setContent(document.getElementById('privacy-text').innerHTML);
                    } else {
                        document.getElementById('field-text').value = document.getElementById('privacy-text').innerHTML;
                    }
                }, 100);
                break;
            case 'ship-to-different':
                document.getElementById('field-text').value = document.getElementById('ship-to-different-text').textContent;
                break;
            case 'product-header':
                document.getElementById('field-text').value = document.getElementById('product-header-text').textContent;
                break;
            case 'subtotal-header':
                document.getElementById('field-text').value = document.getElementById('subtotal-header-text').textContent;
                break;
            case 'total-header':
                document.getElementById('field-text').value = document.getElementById('total-header-text').textContent;
                break;
            case 'shipping':
                document.getElementById('field-text').value = document.getElementById('shipping-text').textContent;
                break;
            default:
                // Form fields
                const label = field.querySelector('label');
                const input = field.querySelector('input, select, textarea');

                if (label && document.getElementById('field-label')) {
                    let labelText = label.textContent.replace(/\s*\*\s*$/, '').replace(/\(optional\)/i, '').replace(/\n/g, '').trim();
                    document.getElementById('field-label').value = labelText;
                }
                if (input && document.getElementById('field-placeholder')) {
                    document.getElementById('field-placeholder').value = input.placeholder || '';
                }
                if (document.getElementById('field-required')) {
                    document.getElementById('field-required').checked = label && label.innerHTML.includes('*');
                }
                break;
        }
    }

    // Save field changes
    function saveFieldChanges() {
        const fieldId = currentEditingField;
        const field = document.querySelector(`[data-field="${fieldId}"]`);
        const visibleCheckbox = document.getElementById('field-visible');

        // Update visibility
        if (visibleCheckbox.checked) {
            field.classList.remove('plugincy-hidden-field');
            const hideBtn = field.querySelector('.hide-btn');
            if (hideBtn) {
                hideBtn.innerHTML = '👁';
                hideBtn.title = 'Hide';
                hideBtn.className = 'control-btn hide-btn';
            }
        } else {
            field.classList.add('plugincy-hidden-field');
            const hideBtn = field.querySelector('.hide-btn');
            if (hideBtn) {
                hideBtn.innerHTML = '👁‍🗨';
                hideBtn.title = 'Show';
                hideBtn.className = 'control-btn show-btn';
            }
        }

        // Update field-specific values
        switch (fieldId) {
            case 'billing-title':
                document.getElementById('billing-title').textContent = document.getElementById('field-text').value;
                break;
            case 'additional-title':
                document.getElementById('additional-title').textContent = document.getElementById('field-text').value;
                break;
            case 'order-review-title':
                document.getElementById('order-review-title').textContent = document.getElementById('field-text').value;
                break;
            case 'coupon-section':
                document.getElementById('coupon-title').textContent = document.getElementById('field-title').value;
                document.getElementById('coupon-description').textContent = document.getElementById('field-description').value;
                document.getElementById('apply-coupon-btn').textContent = document.getElementById('field-button').value;
                const couponinput = field.querySelector('input, select, textarea');
                if (couponinput && document.getElementById('field-placeholder')) {
                    couponinput.placeholder = document.getElementById('field-placeholder').value;
                }
                break;
            case 'place-order':
                document.getElementById('place-order-btn').textContent = document.getElementById('field-text').value;
                break;
            case 'privacy-policy':
                // Get content from wp_editor using tinyMCE
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('field-text')) {
                    document.getElementById('privacy-text').innerHTML = tinyMCE.get('field-text').getContent();
                } else {
                    document.getElementById('privacy-text').innerHTML = document.getElementById('field-text').value;
                }
                break;
            case 'ship-to-different':
                document.getElementById('ship-to-different-text').textContent = document.getElementById('field-text').value;
                break;
            case 'product-header':
                document.getElementById('product-header-text').textContent = document.getElementById('field-text').value;
                break;
            case 'subtotal-header':
                document.getElementById('subtotal-header-text').textContent = document.getElementById('field-text').value;
                document.getElementById('subtotal2-text').textContent = document.getElementById('field-text').value;
                break;
            case 'total-header':
                document.getElementById('total-header-text').textContent = document.getElementById('field-text').value;
                break;
            case 'shipping':
                document.getElementById('shipping-text').textContent = document.getElementById('field-text').value;
                break;
            default:
                // Form fields
                const label = field.querySelector('label');
                const input = field.querySelector('input, select, textarea');

                if (label && document.getElementById('field-label')) {
                    const labelText = document.getElementById('field-label').value;
                    const isRequired = document.getElementById('field-required').checked;
                    label.innerHTML = labelText + (isRequired ? ' <span class="required">*</span>' : ' (optional)');
                }
                if (input && document.getElementById('field-placeholder')) {
                    input.placeholder = document.getElementById('field-placeholder').value;
                }
                break;
        }

        closeModal();
        saveChanges();
    }

    // Toggle field visibility
    function toggleField(fieldId) {
        const field = document.querySelector(`[data-field="${fieldId}"]`);
        const hideBtn = field.querySelector('.hide-btn, .show-btn');

        if (field.classList.contains('plugincy-hidden-field')) {
            field.classList.remove('plugincy-hidden-field');
            hideBtn.innerHTML = '👁';
            hideBtn.title = 'Hide';
            hideBtn.className = 'control-btn hide-btn';
        } else {
            field.classList.add('plugincy-hidden-field');
            hideBtn.innerHTML = '👁‍🗨';
            hideBtn.title = 'Show';
            hideBtn.className = 'control-btn show-btn';
        }
        saveChanges();
    }

    function toggleShippingFields() {
        const checkbox = document.getElementById('ship-to-different-checkbox');
        const shippingFields = document.getElementById('shipping-fields');

        if (checkbox.checked) {
            shippingFields.style.display = 'block';
        } else {
            shippingFields.style.display = 'none';
        }
        saveChanges();
    }


    // Close modal
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
        currentEditingField = null;
    }

    // Save all changes
    function saveChanges() {
        const formData = {};

        // Collect all field data
        document.querySelectorAll('.editable-field').forEach(field => {
            const fieldId = field.dataset.field;
            const isVisible = !field.classList.contains('plugincy-hidden-field');

            formData[fieldId] = {
                visible: isVisible
            };

            // Collect field-specific data
            switch (fieldId) {
                case 'billing-title':
                    formData[fieldId].text = document.getElementById('billing-title').textContent;
                    break;
                case 'additional-title':
                    formData[fieldId].text = document.getElementById('additional-title').textContent;
                    break;
                case 'order-review-title':
                    formData[fieldId].text = document.getElementById('order-review-title').textContent;
                    break;
                case 'coupon-section':
                    formData[fieldId].title = document.getElementById('coupon-title').textContent;
                    formData[fieldId].description = document.getElementById('coupon-description').textContent;
                    formData[fieldId].button = document.getElementById('apply-coupon-btn').textContent;
                    const couponinput = field.querySelector('input, select, textarea');
                    if (couponinput) {
                        formData[fieldId].placeholder = couponinput.placeholder;
                    }
                    break;
                case 'place-order':
                    formData[fieldId].text = document.getElementById('place-order-btn').textContent;
                    break;
                case 'privacy-policy':
                    formData[fieldId].text = document.getElementById('privacy-text').innerHTML;
                    break;
                case 'product-header':
                    formData[fieldId].text = document.getElementById('product-header-text').textContent;
                    break;
                case 'subtotal-header':
                    formData[fieldId].text = document.getElementById('subtotal-header-text').textContent;
                    break;
                case 'total-header':
                    formData[fieldId].text = document.getElementById('total-header-text').textContent;
                    break;
                case 'shipping':
                    formData[fieldId].text = document.getElementById('shipping-text').textContent;
                    break;
                default:
                    // Form fields
                    const label = field.querySelector('label');
                    const input = field.querySelector('input, select, textarea');

                    if (label) {
                        // Remove trailing * and (optional) from label
                        let labelText = label.textContent.replace(/\s*\*\s*$/, '').replace(/\(optional\)/i, '').replace(/\n/g, '').trim();
                        formData[fieldId].label = labelText;
                        formData[fieldId].required = label.innerHTML.includes('*');
                    }
                    if (input) {
                        formData[fieldId].placeholder = input.placeholder;
                    }
                    break;
            }
        });

        <?php if(onepaqucpro_premium_feature()){?>

        const checkoutSetupInput = document.getElementById('checkout_setup');
        if (checkoutSetupInput) {
            checkoutSetupInput.value = JSON.stringify(formData);
        }
        <?php } ?>

        // You can also generate the PHP array format for easy copy-paste

    }

    // Initialize the application
    function initApp() {
        initializeFieldSettings();

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveChanges();
            }
        });
    }

    // Preset configurations
    function loadPreset(presetName) {
        const presets = {
            minimal: {
                'coupon-section': {
                    visible: false
                },
                'company': {
                    visible: false
                },
                'state': {
                    visible: false
                },
                'additional-title': {
                    visible: false
                },
                'order-notes': {
                    visible: false
                }
            },
            full: {
                // All fields visible - default state
            },
            basic: {
                'coupon-section': {
                    visible: false
                },
                'company': {
                    visible: false
                },
                'additional-title': {
                    visible: false
                },
                'order-notes': {
                    visible: false
                },
                'privacy-policy': {
                    visible: false
                }
            }
        };

        if (presets[presetName]) {
            Object.keys(presets[presetName]).forEach(fieldId => {
                const field = document.querySelector(`[data-field="${fieldId}"]`);
                const settings = presets[presetName][fieldId];

                if (!settings.visible) {
                    field.classList.add('plugincy-hidden-field');
                    const hideBtn = field.querySelector('.hide-btn');
                    if (hideBtn) {
                        hideBtn.innerHTML = '👁‍🗨';
                        hideBtn.title = 'Show';
                        hideBtn.className = 'control-btn show-btn';
                    }
                }
            });
        }
    }

    // Reset to defaults
    function resetToDefaults() {
        if (confirm('Are you sure you want to reset all fields to default values?')) {
            location.reload();
        }
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initApp();
    });
</script>