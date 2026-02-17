<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

class onepaqucpro_helper
{
    public function tooltip($content)
    { ?>
        <span class="tooltip">
            <span class="question-mark">?</span>
            <span class="tooltip-text"><?php echo wp_kses_post($content); ?></span>
        </span>
    <?php
    }

    public function sec_head($headtag, $class, $icon, $title, $tooltip = '', $description = '')
    {
        global $onepaquc_onepaquc_onepaqucpro_allowed_tags;

        echo '<' . esc_html($headtag) . ' class="' . esc_html($class) . '">';

        // Check if icon is not empty
        if (!empty($icon)) {
            echo '<span class="plugincy_sec_icon">' . wp_kses($icon, $onepaquc_onepaquc_onepaqucpro_allowed_tags) . '</span>';
        }

        echo '<span class="plugincy_sec_title">' . esc_html($title);

        // Add description if it exists
        if (!empty($description)) {
            echo '<p style="margin: 0; margin-top:4px; font-weight:400;">' . esc_html($description) . '</p>';
        }

        echo '</span>';

        // Add tooltip if it exists
        if (!empty($tooltip)) {
            $this->tooltip($tooltip);
        }

        echo '</' . esc_html($headtag) . '>';
    }

    public function switcher($name, $default = 1, $notice = "")
    { ?>
        <label class="switch">
            <input data-notice="<?php echo esc_html($notice); ?>" type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked(1, get_option($name, $default), true); ?> />
            <span class="slider round"></span>
        </label>
<?php
    }
}
