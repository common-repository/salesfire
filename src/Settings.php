<?php

/**
 * Salesfire Settings
 *
 * @package     Salesfire_Settings
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Salesfire_Settings
{
    public function init()
    {
        $this->register_settings();
    }

    public function init_page()
    {
        add_options_page(
            'Salesfire Settings',
            'Salesfire',
            'manage_options',
            'salesfire',
            array ($this, 'render_settings_page')
        );
    }

    public function register_settings()
    {
        register_setting( 'salesfire', 'salesfire_tracking', 'sanitize_text_field' );
        register_setting( 'salesfire', 'salesfire_uuid', 'sanitize_text_field' );

        add_settings_section(
            'salesfire',
            '',
            '',
            'salesfire'
        );

        add_settings_field(
            'salesfire_tracking',
            'Tracking Enabled',
            array( $this, 'render_checkbox_field_html' ),
            'salesfire',
            'salesfire'
        );

        add_settings_field(
            'salesfire_uuid',
            'Site UUID',
            array( $this, 'render_text_field_html' ),
            'salesfire',
            'salesfire'
        );
    }

    public function render_settings_page()
    {

        ?>

        <h2>Salesfire Settings</h2>

        <form action="options.php" method="post">
            <?php
            settings_fields( 'salesfire' );
            do_settings_sections( 'salesfire' );
            ?>
            <input
              type="submit"
              name="submit"
              class="button button-primary"
              value="<?php esc_attr_e( 'Save' ); ?>"
            />
        </form>

        <?php
    }

    public function render_text_field_html()
    {
        $text = get_option( 'salesfire_uuid' );

        printf(
            '<input type="text" id="salesfire_uuid" name="salesfire_uuid" value="%s" style="max-width:300px;width:100%%;" />',
            esc_attr( $text )
        );
    }

    public function render_checkbox_field_html()
    {
        $text = get_option( 'salesfire_tracking' );

        ?>
            <label>
                <input type="checkbox" id="salesfire_tracking" name="salesfire_tracking" value="1" <?php checked($text, '1') ?> /> Yes
            </label>
        <?php
    }
}
