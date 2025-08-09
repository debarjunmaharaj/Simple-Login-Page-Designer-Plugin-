<?php
/**
 *Hi welcome to todays project on how to build basic plugin for login page

 * Plugin Name:       Simple Login Designer
 * Description:       A very simple plugin to customize the WordPress login page.
                      Changes the login button to orange and lets you add a custom logo.
 * Version:           1.0
 * Author:            Debarjun Chakraborty
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// 1. Add the settings page to the admin menu
function sld_add_admin_menu() {
    add_options_page(
        'Simple Login Designer',        // Page Title
        'Simple Login Designer',        // Menu Title
        'manage_options',               // Capability
        'simple_login_designer',        // Menu Slug
        'sld_options_page_html'         // Function to display the page
    );
}
add_action('admin_menu', 'sld_add_admin_menu');


// 2. Register the settings we want to save
function sld_settings_init() {
    register_setting('sld_settings_group', 'sld_logo_url');

    add_settings_section(
        'sld_main_section',             // ID
        'Login Page Customizations',    // Title
        null,                           // Callback
        'simple_login_designer'         // Page
    );

    add_settings_field(
        'sld_logo_url',                 // ID
        'Custom Logo',                  // Title
        'sld_logo_field_html',          // Callback to render the field
        'simple_login_designer',        // Page
        'sld_main_section'              // Section
    );
}
add_action('admin_init', 'sld_settings_init');


// 3. HTML for the settings page and the logo upload field
function sld_options_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('sld_settings_group');
            do_settings_sections('simple_login_designer');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function sld_logo_field_html() {
    // Enqueue WordPress media scripts
    wp_enqueue_media();
    
    $logo_url = get_option('sld_logo_url');
    ?>
    <input type="text" name="sld_logo_url" id="sld_logo_url" value="<?php echo esc_url($logo_url); ?>" class="regular-text">
    <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">
    <p class="description">Upload or choose a logo from your media library.</p>
    
    <!-- Simple preview -->
    <div id="logo-preview-wrapper" style="margin-top:10px;">
        <img id="logo-preview" src="<?php echo esc_url($logo_url); ?>" style="max-height: 80px; <?php echo empty($logo_url) ? 'display:none;' : ''; ?>">
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($){
        $('#upload-btn').click(function(e) {
            e.preventDefault();
            var image = wp.media({ 
                title: 'Upload Logo',
                multiple: false
            }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                $('#sld_logo_url').val(image_url);
                $('#logo-preview').attr('src', image_url).show();
            });
        });
    });
    </script>
    <?php
}


// 4. Apply the custom styles and logo to the actual login page
function sld_apply_custom_styles() {
    $logo_url = get_option('sld_logo_url');
    ?>
    <style type="text/css">
        /* Change the login button to orange */
        .wp-core-ui .button-primary {
            background: #FFA500 !important; /* Orange */
            border-color: #D98C00 !important; /* Darker orange for border */
            box-shadow: 0px 1px 0px #D98C00 !important;
            text-shadow: 0 -1px 1px #D98C00, 1px 0 1px #D98C00, 0 1px 1px #D98C00, -1px 0 1px #D98C00 !important;
        }

        /* Style for the custom logo */
        <?php if (!empty($logo_url)) : ?>
            #login h1 a, .login h1 a {
                background-image: url(<?php echo esc_url($logo_url); ?>);
                height: 80px; /* You can adjust this height */
                width: 100%;
                background-size: contain;
                background-repeat: no-repeat;
                padding-bottom: 20px;
            }
        <?php endif; ?>
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'sld_apply_custom_styles');


// 5. Change the logo link URL from wordpress.org to your site's home page
function sld_custom_login_url() {
    return home_url('/');
}
add_filter('login_headerurl', 'sld_custom_login_url');


// 6. Change the logo link title attribute
function sld_custom_login_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'sld_custom_login_title');
