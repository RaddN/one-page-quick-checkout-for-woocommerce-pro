<?php
if (!defined('ABSPATH')) {
    exit;
}
class onepaqucpro_License_Manager
{
    private $api_url = 'https://plugincy.com/';
    private $item_id = 4042;
    private $cache_key = 'onepaquc_license_cache';
    private $status_cache_key = 'onepaquc_status_cache';
    private $version_cache_key = 'RMENUPRO_VERSION_cache';

    public function __construct()
    {
        add_action('admin_init', array($this, 'handle_license_actions'));
        add_action('wp_ajax_onepaquc_check_updates', array($this, 'ajax_check_updates'));



        // Enqueue background check script for both admin and frontend (all users)
        $enqueue_bg_check = function () {
            static $is_enqueued = false;

            if ($is_enqueued) {
                return; // Exit if already enqueued
            }

            $is_enqueued = true; // Mark as enqueued
?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof ajaxurl !== 'undefined') {
                        var xhr = new XMLHttpRequest();
                        var params = new URLSearchParams();
                        params.append('action', 'onepaquc_background_license_check');
                        params.append('nonce', '<?php echo esc_js(wp_create_nonce('onepaquc_background_check')); ?>');
                        xhr.open('POST', ajaxurl, true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send(params.toString());
                    }
                });
            </script>
            <?php
        };
        add_action('admin_enqueue_scripts', $enqueue_bg_check);
        add_action('wp_footer', $enqueue_bg_check);

        // Make ajaxurl available on frontend for non-logged-in users
        add_action('wp_enqueue_scripts', function () {
            if (!is_admin()) {
                wp_enqueue_script('jquery');
            ?>
                <script type="text/javascript">
                    var ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
                </script>
        <?php
            }
        });

        add_action('wp_ajax_onepaquc_background_license_check', function () {
            check_ajax_referer('onepaquc_background_check', 'nonce');
            $this->background_license_check();
            $this->background_update_check();
            wp_send_json_success('Checked');
        });
        add_action('wp_ajax_nopriv_onepaquc_background_license_check', function () {
            check_ajax_referer('onepaquc_background_check', 'nonce');
            $this->background_license_check();
            $this->background_update_check();
            wp_send_json_success('Checked');
        });

        add_action('wp_ajax_onepaquc_remove_license', function () {
            check_ajax_referer('onepaquc_remove_license_nonce', 'nonce');
            delete_option('onepaquc_license_key');
            delete_option('onepaquc_license_status');
            delete_transient('onepaquc_license_deactivated_remotely');
            wp_cache_delete('onepaquc_license_cache');
            wp_cache_delete('onepaquc_status_cache');
            wp_cache_delete('RMENUPRO_VERSION_cache');
            delete_transient('onepaquc_license_cache');
            delete_transient('onepaquc_status_cache');
            delete_transient('RMENUPRO_VERSION_cache');
            wp_send_json_success();
        });
    }



    public function background_license_check()
    {
        $license_key = get_option('onepaquc_license_key', '');
        $license_status = get_option('onepaquc_license_status', '');

        if (empty($license_key)) {
            return;
        }



        $license_data = $this->check_license_status($license_key);

        if ($license_data && $license_data->license !== 'valid') {
            $this->handle_remote_deactivation($license_data);
        } elseif ($license_data && $license_data->license === 'valid' && $license_status === 'expired') {
            update_option('onepaquc_license_status', 'valid');
            delete_transient('onepaquc_license_deactivated_remotely');
        }

        if ($license_data) {
            wp_cache_set($this->cache_key, $license_data, '', 3600);
            set_transient($this->cache_key, $license_data, 7200);
        }
    }

    public function background_update_check()
    {
        if ($this->is_license_valid_cached()) {
            wp_cache_delete($this->version_cache_key);
            delete_transient($this->version_cache_key);

            $update_info = $this->check_for_updates();

            if ($update_info) {
                wp_send_json_success(array(
                    'update_available' => true,
                    'new_version' => $update_info->new_version,
                    'current_version' => defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0'
                ));
            } else {
                wp_send_json_success(array(
                    'update_available' => false
                ));
            }
        }
    }

    private function handle_remote_deactivation($license_data)
    {
        if ($license_data->license === 'expired') {
            update_option('onepaquc_license_status', 'expired');
            return;
        }

        update_option('onepaquc_license_status', $license_data->license);
        set_transient('onepaquc_license_deactivated_remotely', $license_data->license, WEEK_IN_SECONDS);
        $this->clear_all_cache();
    }

    public function clear_all_cache()
    {
        wp_cache_delete($this->cache_key);
        wp_cache_delete($this->status_cache_key);
        wp_cache_delete($this->version_cache_key);
        delete_transient($this->cache_key);
        delete_transient($this->status_cache_key);
        delete_transient($this->version_cache_key);
        delete_transient('onepaquc_last_update_check');
    }

    public function is_license_valid_cached()
    {
        $cached_status = wp_cache_get($this->status_cache_key);
        if ($cached_status !== false) {
            return $cached_status === 'valid';
        }

        $cached_status = get_transient($this->status_cache_key);
        if ($cached_status !== false) {
            wp_cache_set($this->status_cache_key, $cached_status, '', 300);
            return $cached_status === 'valid';
        }

        $license_status = get_option('onepaquc_license_status', '');
        $is_valid = $license_status === 'valid';

        wp_cache_set($this->status_cache_key, $license_status, '', 300);
        set_transient($this->status_cache_key, $license_status, 1800);

        return $is_valid;
    }

    public function is_license_valid()
    {
        return $this->is_license_valid_cached();
    }

    public function can_use_premium_features()
    {
        $license_status = get_option('onepaquc_license_status', '');
        return $license_status === 'valid' && !$this->is_license_expired_cached();
    }

    private function is_license_expired_cached()
    {
        $cached_details = wp_cache_get($this->cache_key);
        if ($cached_details !== false) {
            return $this->check_expiry_from_details($cached_details);
        }

        $cached_details = get_transient($this->cache_key);
        if ($cached_details !== false) {
            wp_cache_set($this->cache_key, $cached_details, '', 3600);
            return $this->check_expiry_from_details($cached_details);
        }

        $license_details = $this->get_license_details();
        if ($license_details) {
            wp_cache_set($this->cache_key, $license_details, '', 3600);
            set_transient($this->cache_key, $license_details, 7200);
            return $this->check_expiry_from_details($license_details);
        }

        return false;
    }

    private function check_expiry_from_details($license_details)
    {
        if (!$license_details || !$license_details->success || empty($license_details->expires)) {
            return false;
        }

        if (strtolower($license_details->expires) === 'lifetime') {
            return false;
        }

        $expires_date = new DateTime($license_details->expires);
        $current_date = new DateTime();
        return $expires_date < $current_date;
    }

    public function get_license_details()
    {
        $license_key = get_option('onepaquc_license_key', '');
        if (empty($license_key)) {
            return false;
        }

        $cached_details = wp_cache_get($this->cache_key);
        if ($cached_details !== false) {
            return $cached_details;
        }

        $cached_details = get_transient($this->cache_key);
        if ($cached_details !== false) {
            wp_cache_set($this->cache_key, $cached_details, '', 3600);
            return $cached_details;
        }

        $license_data = $this->check_license_status($license_key);
        if ($license_data) {
            wp_cache_set($this->cache_key, $license_data, '', 3600);
            set_transient($this->cache_key, $license_data, 7200);
        }

        return $license_data;
    }

    public function is_license_expiring_soon()
    {
        $license_details = $this->get_license_details();
        if (!$license_details || !$license_details->success || empty($license_details->expires)) {
            return false;
        }

        if (strtolower($license_details->expires) === 'lifetime') {
            return false;
        }

        $expires_date = new DateTime($license_details->expires);
        $current_date = new DateTime();
        $days_until_expiry = $current_date->diff($expires_date)->days;

        return $expires_date > $current_date && $days_until_expiry <= 30;
    }

    public function handle_license_actions()
    {

        if (!isset($_POST['onepaquc_license_action']) || !isset($_POST['onepaquc_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['onepaquc_license_nonce'])), 'onepaquc_license_nonce')) {
            return;
        }

        $license_key = sanitize_text_field(wp_unslash($_POST['onepaquc_license_key']));
        $action = sanitize_text_field(wp_unslash($_POST['onepaquc_license_action']));

        if (empty($license_key)) {
            add_settings_error('onepaquc_license', 'empty_license', 'Please enter a valid license key.', 'error');
            return;
        }

        if ($action === 'activate') {
            $this->activate_license($license_key);
        } elseif ($action === 'deactivate') {
            $this->deactivate_license($license_key);
        }
    }

    private function activate_license($license_key)
    {
        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0')
        ));

        if (is_wp_error($response)) {
            add_settings_error('onepaquc_license', 'api_error', 'Unable to connect to licensing server. Please try again later.', 'error');
            return;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        if ($license_data->license == 'valid') {
            update_option('onepaquc_license_key', $license_key);
            update_option('onepaquc_license_status', 'valid');
            delete_transient('onepaquc_license_deactivated_remotely');
            $this->clear_all_cache();
            add_settings_error('onepaquc_license', 'license_activated', 'License activated successfully!', 'updated');
        } else {
            delete_option('onepaquc_license_status');
            $error_message = $this->get_license_error_message($license_data);
            add_settings_error('onepaquc_license', 'activation_failed', $error_message, 'error');
        }
    }

    private function deactivate_license($license_key)
    {
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0')
        ));

        if (is_wp_error($response)) {
            add_settings_error('onepaquc_license', 'api_error', 'Unable to connect to licensing server. Please try again later.', 'error');
            return;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        if ($license_data->license == 'deactivated') {
            delete_option('onepaquc_license_key');
            delete_option('onepaquc_license_status');
            $this->clear_all_cache();
            add_settings_error('onepaquc_license', 'license_deactivated', 'License deactivated successfully!', 'updated');
        } else {
            add_settings_error('onepaquc_license', 'deactivation_failed', 'Failed to deactivate license. Please try again.', 'error');
        }
    }

    public function check_license_status($license_key)
    {
        $api_params = array(
            'edd_action' => 'check_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0')
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    public function get_version_info($license_key)
    {
        $cached_version = wp_cache_get($this->version_cache_key);
        if ($cached_version !== false) {
            return $cached_version;
        }

        $cached_version = get_transient($this->version_cache_key);
        if ($cached_version !== false) {
            wp_cache_set($this->version_cache_key, $cached_version, '', 3600);
            return $cached_version;
        }

        $api_params = array(
            'edd_action' => 'get_version',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0')
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $version_info = json_decode(wp_remote_retrieve_body($response));

        if ($version_info) {
            wp_cache_set($this->version_cache_key, $version_info, '', 3600);
            set_transient($this->version_cache_key, $version_info, 7200);
        }

        return $version_info;
    }

    public function check_for_updates()
    {
        $license_key = get_option('onepaquc_license_key', '');
        $license_status = get_option('onepaquc_license_status', '');

        if (empty($license_key) || ($license_status !== 'valid' && $license_status !== 'expired')) {
            return false;
        }

        $version_info = $this->get_version_info($license_key);
        if (!$version_info || !isset($version_info->new_version)) {
            return false;
        }

        $current_version = defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0';
        if (version_compare($current_version, $version_info->new_version, '<')) {
            return $version_info;
        }

        return false;
    }

    public function ajax_check_updates()
    {
        check_ajax_referer('onepaquc_check_updates', 'nonce');

        wp_cache_delete($this->version_cache_key);
        delete_transient($this->version_cache_key);

        $update_info = $this->check_for_updates();

        if ($update_info) {
            wp_send_json_success(array(
                'update_available' => true,
                'new_version' => $update_info->new_version,
                'current_version' => defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0'
            ));
        } else {
            wp_send_json_success(array(
                'update_available' => false
            ));
        }
    }

    private function get_license_error_message($license_data)
    {
        switch ($license_data->license) {
            case 'expired':
                return 'Your license key has expired. Please renew your license.';
            case 'disabled':
            case 'revoked':
                return 'Your license key has been disabled.';
            case 'missing':
                return 'Invalid license key. Please check your license key and try again.';
            case 'invalid':
            case 'site_inactive':
                return 'Your license is not active for this URL.';
            case 'item_name_mismatch':
                return 'This license key does not belong to this product.';
            case 'no_activations_left':
                return 'Your license key has reached its activation limit.';
            default:
                return 'An error occurred, please try again.';
        }
    }

    public function show_license_notices()
    {

        $remote_deactivation_status = get_transient('onepaquc_license_deactivated_remotely');
        if ($remote_deactivation_status) {
            $message = $this->get_remote_deactivation_message($remote_deactivation_status);
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>License Alert:</strong> ' . esc_html($message) . '</p>';
            echo '<p><a href="' . admin_url('admin.php?page=your-license-page') . '">Manage License</a> | ';
            echo '<a href="https://plugincy.com/support" target="_blank">Contact Support</a></p>';
            echo '</div>';
        }

        $this->show_expiry_notice();

        if ($this->is_license_valid_cached()) {
            $update_info = $this->check_for_updates();
            if ($update_info) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p><strong>Dynamic AJAX Product Filters Pro Update Available!</strong></p>';
                echo '<p>Version ' . esc_html($update_info->new_version) . ' is now available. ';
                echo '<a href="' . admin_url('plugins.php') . '?force_check_updates=1">Update now</a> to get the latest features and improvements.</p>';
                echo '</div>';
            }
        }
    }

    private function show_expiry_notice()
    {
        $license_key = get_option('onepaquc_license_key', '');
        $license_status = get_option('onepaquc_license_status', '');

        if (empty($license_key) || ($license_status !== 'valid' && $license_status !== 'expired')) {
            return;
        }

        $license_details = $this->get_license_details();
        if (!$license_details || !$license_details->success || empty($license_details->expires)) {
            return;
        }

        if (strtolower($license_details->expires) === 'lifetime') {
            return;
        }

        $expires_date = new DateTime($license_details->expires);
        $current_date = new DateTime();
        $days_until_expiry = $current_date->diff($expires_date)->days;
        $is_expired = $expires_date < $current_date;

        if ($is_expired) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>License Expired:</strong> Your Dynamic AJAX Product Filters Pro license expired on ' . esc_html($expires_date->format('M j, Y')) . '.</p>';
            echo '<p><a href="https://plugincy.com/checkout/?edd_license_key=' . esc_attr($license_key) . '" target="_blank" class="button button-primary">Renew License</a> ';
            echo '<a href="https://plugincy.com/support" target="_blank">Contact Support</a></p>';
            echo '</div>';
        } elseif ($days_until_expiry <= 30) {
            $notice_class = $days_until_expiry <= 7 ? 'notice-error' : 'notice-warning';
            echo '<div class="notice ' . $notice_class . ' is-dismissible">';
            echo '<p><strong>License Expiring Soon:</strong> Your license will expire in ' . $days_until_expiry . ' days (' . esc_html($expires_date->format('M j, Y')) . ').</p>';
            echo '<p><a href="https://plugincy.com/checkout/?edd_license_key=' . esc_attr($license_key) . '" target="_blank" class="button button-primary">Renew Now</a> ';
            echo '<a href="https://plugincy.com/my-account" target="_blank">Manage License</a></p>';
            echo '</div>';
        }
    }

    private function get_remote_deactivation_message($status)
    {
        switch ($status) {
            case 'expired':
                return 'Your license has expired and has been deactivated. Please renew to continue using premium features.';
            case 'disabled':
            case 'revoked':
                return 'Your license has been disabled. Please contact support for assistance.';
            case 'site_inactive':
                return 'Your license is no longer active for this website. Please reactivate or contact support.';
            case 'no_activations_left':
                return 'Your license has exceeded its activation limit and has been deactivated.';
            default:
                return 'Your license has been deactivated remotely. Please check your license status.';
        }
    }

    public function render_license_form()
    {
        $license_key = get_option('onepaquc_license_key', '');
        $license_status = get_option('onepaquc_license_status', '');
        $is_valid = $this->is_license_valid_cached();
        $is_expired = $this->is_license_expired_cached();
        ?>
        <div class="wrap" style="max-width: 100%;">
            <div class="col-md-6">
                <div class="plugincy_sec_head">
                    <span class="plugincy_sec_icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <svg fill="#fff" width="16" height="16" viewBox="0 0 0.48 0.48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.276.293.195.374H.142v.053H.089V.48H0V.391L.187.204A.2.2 0 0 1 .178.151a.151.151 0 1 1 .097.141zM.427.107A.053.053 0 1 0 .374.16.053.053 0 0 0 .427.107" />
                        </svg>
                    </span>
                    <span>
                        <span style="color: #1d2327; font-size: 1.3em; font-weight: 600;">License Settings</span>
                        <p>Manage your license key, check status, and update to the latest version</p>
                    </span>
                </div>
            </div>
            <div class="row" style="gap: 20px;">
                <div class="col-md-6" style="<?php echo !$is_valid ? 'flex: 0 0 100%; max-width: 99%;':'';?>">

                    <div style="gap: 5px;align-items: center;display: flex;">
                        <div style=" background: #eee; padding: 5px; border-radius: 5px; "><span class="dashicons dashicons-lock"></span></div>
                        <h3 for="onepaquc_license_key" style="font-size: 16px;margin: 0;"><?php echo esc_html__('License Key', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                    </div>
                    <form method="post" action="">
                        <?php wp_nonce_field('onepaquc_license_nonce', 'onepaquc_license_nonce'); ?>
                        <div>
                            <div style="gap: 5px;align-items: center;display: flex;margin-top: 1rem;">
                                <span class="dashicons dashicons-shield" style="color: #0000ff;"></span>
                                <label for="onepaquc_license_key" style="font-size: 16px;margin: 0;"><?php echo esc_html__('Your License Key', 'one-page-quick-checkout-for-woocommerce-pro'); ?></label>
                            </div>
                            <div style="position: relative;">
                                <input type="text" style="width: 100%;border: 1px solid #eee;padding: 6px 0 6px 15px;" id="onepaquc_license_key" name="onepaquc_license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text" placeholder="Enter your license key" />
                                <div style="vertical-align: middle;margin-left: 8px;position: absolute;right: 0;top: 0;background: #dcffdc;height: 100%;display: flex;align-items: center;justify-content: center;padding: 0 20px;border-radius: 0 2px 2px 0;cursor:pointer;gap: 2px;">
                                    <?php if ($is_valid && !$is_expired): ?>
                                        <span class="dashicons dashicons-yes-alt" style="color: green; margin-top: 3px;"></span>
                                        <span style="color: green; font-weight: bold;">Active</span>
                                    <?php elseif ($is_expired): ?>
                                        <span class="dashicons dashicons-no-alt" style="color: #dc3545; margin-top: 3px;"></span>
                                        <span style="color: #dc3545; font-weight: bold; background: #f8d7da; border-radius: 3px; padding: 2px 8px;">Expired</span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-dismiss" style="color: red; margin-top: 3px;"></span>
                                        <span style="color: red;">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($is_valid && !$is_expired): ?>
                            <?php $version_info = $this->get_version_info($license_key); ?>
                            <?php if ($version_info && isset($version_info->new_version)):
                                $current_version = defined('RMENUPRO_VERSION') ? RMENUPRO_VERSION : '1.0.0'; ?>
                                <div style="margin-top: 15px;padding: 15px;background: #fbfbfb;border-radius: 6px;">
                                    <strong style="display: flex; align-items: center; gap: 8px;">
                                        <div style="background: #dce6ff;padding: 5px;border-radius: 5px;">
                                            <span class="dashicons dashicons-info" style="color: #2560e8; font-size: 18px;"></span>
                                        </div>
                                        Version Information:
                                    </strong>
                                    <div style="margin-top: 8px;">
                                        <span style="display: flex;align-items: center;gap: 6px;justify-content: space-between;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="background: #6c757d0a;padding: 5px;border-radius: 5px;">
                                                    <span class="dashicons dashicons-admin-plugins" style="color: #6c757d; font-size: 16px;"></span>
                                                </div>
                                                Current Version:
                                            </div><?php echo esc_html($current_version); ?>
                                        </span><br>
                                        <span style="display: flex;align-items: center;gap: 6px;justify-content: space-between;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="background: #fff2eaff;padding: 5px;border-radius: 5px;">
                                                    <span class="dashicons dashicons-update" style="color: #e75404; font-size: 16px;"></span>
                                                </div>
                                                Latest Version:
                                            </div> <?php echo esc_html($version_info->new_version); ?>
                                        </span>
                                        <?php if (version_compare($current_version, $version_info->new_version, '<')): ?>
                                            <br>
                                            <span style="color: #d63384; font-weight: bold; display: flex; align-items: center; gap: 6px; justify-content: space-between;">
                                                <div><span class="dashicons dashicons-warning" style="color: #d63384; font-size: 16px;"></span>
                                                    Update Available!</div> <a href="<?php echo admin_url('plugins.php'); ?>">Update Now</a>
                                            </span>
                                        <?php else: ?>
                                            <br>
                                            <span style="color: #198754; font-weight: bold; display: flex; align-items: center; gap: 6px; justify-content: space-between;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="background: #e8fff4;padding: 5px;border-radius: 5px;">
                                                        <span class="dashicons dashicons-yes" style="color: #198754; font-size: 16px;"></span>
                                                    </div>
                                                    Status
                                                </div>
                                                <div style="font-size: 14px;">
                                                    <span class="dashicons dashicons-yes-alt" style="color: green;margin-top: 3px;font-size: 14px;"></span>
                                                    Up to date
                                                </div>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="hidden" name="onepaquc_license_action" value="deactivate" />
                            <?php if ($is_expired): echo '<a href="https://plugincy.com/checkout/?edd_license_key=' . esc_attr($license_key) . '" target="_blank" class="button button-primary">Renew License</a>';
                            endif; ?>
                            <button type="submit" onclick="return confirm('Are you sure you want to deactivate your license?')" class="button button-primary" style="background: #eef1f6;color:#24262a;display: inline-flex;align-items: center;gap: 8px;margin-top: 15px;padding: 6px 20px;border: 1px solid #e3e3e3;">
                                <svg width="20" height="20" viewBox="0 0 0.8 0.8" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M.564.135a.031.031 0 0 0-.03.055.281.281 0 1 1-.27.001L.265.19A.031.031 0 0 0 .25.131a.02.02 0 0 0-.015.004.344.344 0 1 0 .331.001zM.399.382A.03.03 0 0 0 .43.351V.05a.031.031 0 0 0-.063 0v.301c0 .017.014.031.031.031" />
                                </svg>
                                <?php echo esc_html__('Deactivate License', 'one-page-quick-checkout-for-woocommerce-pro-pro'); ?>
                            </button>
                            <button type="submit" onclick="checkForUpdates()" id="check-updates-btn" class="button button-primary" style="background: #2560e8; display: inline-flex; align-items: center; gap: 8px; margin-top: 15px; padding: 6px 20px;">
                                <svg fill="#fff" height="20" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12.8 12.8" xml:space="preserve">
                                    <path d="M8.145 0A4.656 4.656 0 0 0 3.49 4.655c0 .838.225 1.62.61 2.3l-4.1 4.1.583 1.163 1.162.582 4.1-4.1c.678.388 1.463.61 2.3.61a4.656 4.656 0 0 0 0-9.31m0 7.855a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4" />
                                </svg>
                                <?php echo esc_html__('Check for Updates', 'one-page-quick-checkout-for-woocommerce-pro-pro'); ?>
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="onepaquc_license_action" value="activate" />
                            <button type="submit" class="button button-primary" style="background: #2560e8; display: flex; align-items: center; gap: 8px; margin-top: 15px; padding: 6px 20px;">
                                <svg width="18" height="18" viewBox="0 0 0.54 0.54" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M.27.045a.02.02 0 0 1 .022.022v.238L.344.254a.022.022 0 0 1 .032.032l-.09.09a.022.022 0 0 1-.032 0l-.09-.09A.022.022 0 1 1 .196.254l.051.052V.068A.022.022 0 0 1 .27.045M.113.383a.02.02 0 0 1 .022.022V.45h.27V.405a.022.022 0 1 1 .045 0V.45a.045.045 0 0 1-.045.045h-.27A.045.045 0 0 1 .09.45V.405A.022.022 0 0 1 .113.383" fill="#fff"></path>
                                </svg>
                                Activate License &amp; Install Pro
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                <?php if ($is_valid && !$is_expired): ?>
                    <div class="col-md-6 plugincy-dapfforwc-card" style="box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="background: #f2fcf4;padding: 5px;border-radius: 5px;">
                                    <span class="dashicons dashicons-info-outline" style="color: #238b4e;"></span>
                                </div>
                                <p>License Status</p>
                            </div>
                            <div>
                                <div style="font-size: 14px;background: #e2fbec;color: green;padding: 5px 10px 2px;border-radius: 10px;border: 1px solid #ccfbdf;">
                                    <span class="dashicons dashicons-yes-alt" style="color: green;margin-top: 3px;font-size: 14px;"></span>
                                    Licensed
                                </div>
                            </div>
                        </div>
                        <?php if ($is_valid): ?>
                            <?php if ($is_expired): ?>
                                <span class="license-status license-status-invalid">
                                    <strong>✗ License Expired</strong> - Please renew your license to continue using premium features
                                </span>
                            <?php else: ?>
                                <span class="license-status license-status-valid" style="display: flex;font-size: 14px;background: #e2fbec;color: green;border-radius: 10px;border: 1px solid #ccfbdf;padding: 20px;align-items: center;gap: 10px;">
                                    <span class="dashicons dashicons-yes-alt" style="color: green;margin-top: 3px;font-size: 14px;"></span>
                                    <div style="display: flex; flex-direction: column;"><strong>Premium License Active</strong> All premium features are available and active on your site</div>
                                </span>
                            <?php endif; ?>
                            <?php $license_details = $this->get_license_details(); ?>
                            <?php if ($license_details && $license_details->success): ?>
                                <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;">
                                    <strong style="display: flex; align-items: center; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="background: #e6edff;padding: 5px;border-radius: 5px;">
                                                <span class="dashicons dashicons-id" style="color: #2560e8; font-size: 18px;"></span>
                                            </div>
                                            License Details:
                                        </div>
                                    </strong>
                                    <div style=" display: flex; flex-direction: column; gap: 10px; padding-top: 10px; ">
                                        <div style=" display: flex; justify-content: space-between; ">
                                            <span style="display: flex; align-items: center; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="background: #ffece1ff;padding: 5px;border-radius: 5px;">
                                                        <span class="dashicons dashicons-admin-users" style="color: #e75404; font-size: 16px;"></span>
                                                    </div>
                                                    <strong>License Holder:</strong>
                                                </div>
                                            </span>
                                            <span style="color: #495057;"><?php echo esc_html($license_details->customer_name); ?></span>
                                        </div>
                                        <div style=" display: flex; justify-content: space-between; ">
                                            <span style="display: flex; align-items: center; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="background: #e0fff1;padding: 5px;border-radius: 5px;">
                                                        <span class="dashicons dashicons-calendar-alt" style="color: #198754; font-size: 16px;"></span>
                                                    </div>
                                                    <strong>Expires:</strong>
                                                </div>
                                            </span>
                                            <?php if (strtolower($license_details->expires) === 'lifetime'): ?>
                                                <span style="color: #198754; font-weight: bold;">
                                                    <span class="dashicons dashicons-infinity" style="font-size: 16px; vertical-align: middle;"></span>
                                                    Lifetime
                                                </span>
                                                <?php else:
                                                $expires_date = new DateTime($license_details->expires);
                                                $current_date = new DateTime();
                                                $days_until_expiry = $current_date->diff($expires_date)->days;
                                                $is_expired = $expires_date < $current_date;
                                                if ($is_expired): ?>
                                                    <span style="color: #dc3545; font-weight: bold;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <div style="background: #e0fff1;padding: 5px;border-radius: 5px;">
                                                                <span class="dashicons dashicons-no-alt" style="font-size: 16px; vertical-align: middle;"></span>
                                                            </div>
                                                            Expired on <?php echo esc_html($expires_date->format('M j, Y')); ?>
                                                        </div>
                                                    </span>
                                                <?php elseif ($days_until_expiry <= 30): ?>
                                                    <span style="color: #fd7e14; font-weight: bold;">
                                                        <span class="dashicons dashicons-warning" style="font-size: 16px; vertical-align: middle;"></span>
                                                        <?php echo esc_html($expires_date->format('M j, Y')); ?>
                                                        <small>(<?php echo $days_until_expiry; ?> days left)</small>
                                                        <?php echo '<a href="https://plugincy.com/checkout/?edd_license_key=' . esc_attr($license_key) . '" target="_blank">Renew License</a>'; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: #198754;">
                                                        <span class="dashicons dashicons-yes" style="font-size: 16px; vertical-align: middle;"></span>
                                                        <?php echo esc_html($expires_date->format('M j, Y')); ?>
                                                    </span>
                                            <?php endif;
                                            endif; ?>
                                        </div>
                                        <div style=" display: flex; justify-content: space-between; ">
                                            <span style="display: flex; align-items: center; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="background: #e4f9ffff;padding: 5px;border-radius: 5px;">
                                                        <span class="dashicons dashicons-networking" style="color: #1b85a5; font-size: 16px;"></span>
                                                    </div>
                                                    <strong>Site Usage:</strong>
                                                </div>
                                            </span>
                                            <div>
                                                <span style="color: #495057;">
                                                    <?php echo esc_html($license_details->site_count); ?> of <?php echo esc_html($license_details->license_limit); ?> sites used
                                                </span>
                                                <?php if ($license_details->site_count >= $license_details->license_limit): ?>
                                                    <small style="color: #dc3545;">
                                                        <span class="dashicons dashicons-no" style="font-size: 14px; vertical-align: middle;"></span>
                                                        (Limit reached)
                                                    </small>
                                                <?php else: ?>
                                                    <small style="color: #198754;">
                                                        <span class="dashicons dashicons-plus" style="font-size: 14px; vertical-align: middle;"></span>
                                                        (<?php echo esc_html($license_details->activations_left); ?> activations left)
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="license-status license-status-invalid">
                                <strong>✗ Unlicensed</strong> - Please activate your license to access premium features
                            </span>
                            <?php if (!empty($license_status) && $license_status !== 'valid'): ?>
                                <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                                    <strong>Status:</strong> <?php echo esc_html(ucfirst(str_replace('_', ' ', $license_status))); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($license_status === 'site_inactive'): ?>
                                <button type="button" class="button button-secondary" id="onepaquc-remove-license-btn" style="margin-left:10px;">
                                    Remove License
                                </button>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var removeBtn = document.getElementById('onepaquc-remove-license-btn');
                                        if (removeBtn) {
                                            removeBtn.addEventListener('click', function(e) {
                                                e.preventDefault();
                                                if (confirm('Are you sure you want to remove the license from this site?')) {
                                                    removeBtn.textContent = 'Removing...';
                                                    removeBtn.disabled = true;
                                                    var xhr = new XMLHttpRequest();
                                                    xhr.open('POST', ajaxurl, true);
                                                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                                    xhr.onreadystatechange = function() {
                                                        if (xhr.readyState === 4) {
                                                            if (xhr.status === 200) {
                                                                removeBtn.textContent = 'Success';
                                                                removeBtn.style.backgroundColor = '#28a745';
                                                                removeBtn.style.borderColor = '#28a745';
                                                                setTimeout(function() {
                                                                    removeBtn.style.transition = 'opacity 0.5s';
                                                                    removeBtn.style.opacity = '0';
                                                                    setTimeout(function() {
                                                                        removeBtn.style.display = 'none';
                                                                    }, 500);
                                                                }, 800);
                                                                var licenseInput = document.getElementById('onepaquc_license_key');
                                                                if (licenseInput) {
                                                                    licenseInput.value = '';
                                                                }
                                                            } else {
                                                                removeBtn.textContent = 'Remove License';
                                                                removeBtn.disabled = false;
                                                                alert('Failed to remove license. Please try again.');
                                                            }
                                                        }
                                                    };
                                                    xhr.send('action=onepaquc_remove_license&nonce=<?php echo wp_create_nonce('onepaquc_remove_license_nonce'); ?>');
                                                }
                                            });
                                        }
                                    });
                                </script>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <p class="support-links">
                <span style="margin-right: 16px;">
                    <a href="https://plugincy.com/support" target="_blank" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-sos" style="font-size: 16px; vertical-align: middle; margin-bottom: -6px;"></span>
                        <?php echo esc_html__('Contact Support', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                    </a>
                </span>
                <span>
                    <a href="https://plugincy.com/my-account" target="_blank" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-admin-network" style="font-size: 16px; vertical-align: middle; margin-bottom: -6px;"></span>
                        <?php echo esc_html__('Manage Your Licenses', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                    </a>
                </span>
            </p>

            <style>
                .license-status {
                    padding: 8px 12px;
                    border-radius: 4px;
                    display: inline-block;
                    font-size: 14px;
                }

                .license-status-valid {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .license-status-invalid {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
            </style>

            <script>
                function checkForUpdates() {
                    var btn = document.getElementById('check-updates-btn');
                    var originalText = btn.textContent;
                    btn.textContent = 'Checking...';
                    btn.disabled = true;

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            btn.textContent = originalText;
                            btn.disabled = false;

                            if (xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    if (response.data.update_available) {
                                        alert('Update available! Version ' + response.data.new_version + ' is ready to install.');
                                        location.reload();
                                    } else {
                                        alert('You have the latest version installed.');
                                    }
                                } else {
                                    alert('Error checking for updates: ' + response.data);
                                }
                            } else {
                                alert('Error checking for updates. Please try again.');
                            }
                        }
                    };

                    xhr.send('action=onepaquc_check_updates&nonce=' + '<?php echo wp_create_nonce("onepaquc_check_updates"); ?>');
                }
            </script>
        </div>
<?php
    }
}

global $onepaqucpro_License_Manager;

$onepaqucpro_License_Manager = new onepaqucpro_License_Manager();

function onepaqucpro_is_license_valid()
{
    global $onepaqucpro_License_Manager;
    return $onepaqucpro_License_Manager->is_license_valid_cached();
}

function onepaqucpro_premium_feature()
{
    global $onepaqucpro_License_Manager;

    if (!$onepaqucpro_License_Manager->can_use_premium_features()) {
        if (get_option('onepaquc_license_status') === 'expired') {
            return false;
        }
        return false;
    }
    return true;
}
