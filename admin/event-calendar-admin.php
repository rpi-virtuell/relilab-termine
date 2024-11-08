<?php

class EventCalendarAdmin
{


    // Initialize hooks and actions
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_options_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    // Add options page to the WordPress admin

    static function get_termin_selected_post_type()
    {
        return get_option('termin_selected_post_type');
    }

    // Register settings for the options page

    static function get_termin_start_date_field()
    {
        $field = acf_get_field(get_option('termin_start_date_field'));
        if (is_array($field)) {
            return $field['name'];
        } else {
            return $field;
        }
    }

    // Render the HTML for the options page

    static function get_termin_end_date_field()
    {
        $field = acf_get_field(get_option('termin_end_date_field'));
        if (is_array($field)) {
            return $field['name'];
        } else {
            return $field;
        }
    }

    public function add_options_page()
    {
        add_options_page(
            'Event Calendar Settings',       // Page title
            'Event Calendar Settings',       // Menu title
            'manage_options',                // Capability
            'event-calendar-settings',       // Menu slug
            [$this, 'render_settings_page']  // Callback function
        );
    }

    public function register_settings()
    {
        register_setting('event_calendar_group', 'termin_selected_post_type');
        register_setting('event_calendar_group', 'termin_start_date_field');
        register_setting('event_calendar_group', 'termin_end_date_field');
    }

    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $post_types = get_post_types(['public' => true], 'objects');
        $termin_selected_post_type = get_option('termin_selected_post_type');
        $termin_start_date_field = get_option('termin_start_date_field');
        $termin_end_date_field = get_option('termin_end_date_field');

        // Retrieve ACF fields if ACF plugin is active
        $acf_fields = [];
        if (function_exists('acf_get_field_groups')) {
            $field_groups = acf_get_field_groups();

            foreach ($field_groups as $group) {
                $fields = acf_get_fields($group['key']);
                foreach ($fields as $field) {
                    $acf_fields[$field['key']] = $field['label'];
                }
            }
        }

        ?>
        <div class="wrap">
            <h1>Event Calendar Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('event_calendar_group');
                do_settings_sections('event-calendar-settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Post Type</th>
                        <td>
                            <select name="termin_selected_post_type">
                                <?php foreach ($post_types as $post_type) : ?>
                                    <option value="<?php echo esc_attr($post_type->name); ?>" <?php selected($termin_selected_post_type, $post_type->name); ?>>
                                        <?php echo esc_html($post_type->label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Start Date Field</th>
                        <td>
                            <select name="termin_start_date_field">
                                <?php foreach ($acf_fields as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($termin_start_date_field, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">End Date Field</th>
                        <td>
                            <select name="termin_end_date_field">
                                <?php foreach ($acf_fields as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($termin_end_date_field, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

}


