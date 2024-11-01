<?php
/**
 * Plugin Name: Sparklab Content Wingman
 * Plugin URI: https://sparklab.ai/wp-content-wingman/
 * Description: This WordPress plugin enables bloggers to create unique content easily with OpenAI. It provides a simple and intuitive interface that allows you to quickly generate content from a set of keywords or topics.
 * Version: 1.0
 * Author: the Sparklab Team
 *
 * @package sparklab-content-wingman
 */

defined( 'ABSPATH' ) || exit;


/**
 * register styles.
 */
function spcwm_register_block(): void
{

    if ( ! function_exists( 'register_block_type' ) ) {
        // Gutenberg is not active.
        return;
    }

    // __DIR__ is the current directory where block.json file is stored.
    register_block_type( __DIR__ );

    wp_enqueue_style( 'sparklab-content-wingman', plugin_dir_url(__FILE__) . 'style.css' );

}
add_action( 'init', 'spcwm_register_block' );
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */

/**
 * custom option and settings
 */
function spcwm_settings_init() {
    // Register a new setting for "sparklab" page.
    register_setting( 'spcwm', 'spcwm_options' );

    // Register a new section in the "sparklab" page.
    add_settings_section(
        'spcwm_section_developers',
        __( 'Unlock the power of our plugin with an API key!.', 'spcwm' ), 'spcwm_section_developers_callback',
        'spcwm'
    );

    // Register a new field in the "spcwm_section_developers" section, inside the "sparklab" page.
    add_settings_field(
        'spcwm_field_api_key', // As of WP 4.6 this value is used only internally.
        __( 'API Key', 'spcwm' ),
        'spcwm_field_api_key_cb',
        'spcwm',
        'spcwm_section_developers',
        array(
            'api_key'         => 'spcwm_field_api_key',
            'class'             => 'spcwm_row',
            'spcwm_custom_data' => 'custom',
        )
    );
}

/**
 * Register our spcwm_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'spcwm_settings_init' );


/**
 * Custom option and settings:
 *  - callback functions
 */


/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function spcwm_section_developers_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'If you need help with anything, please feel free to reach out to us', 'spcwm' ); ?> <a href="https://sparklab.ai/contact/">here</a></p>
    <?php
}

/**
 * @param $args
 * @return void
 */
function spcwm_field_api_key_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'spcwm_options' );
    ?>
    <input type="text" value="<?php echo esc_attr($options['spcwm_field_api_key']);?>" name="spcwm_options[<?php echo esc_attr( $args['api_key'] ); ?>]">
    <p class="description">
        <?php esc_html_e( '', 'spcwm' ); ?>
    </p>
    <?php
}

/**
 * Add the top level menu page.
 */
function spcwm_options_page() {
    add_menu_page(
        'SparkLab - Settings',
        'SparkLab',
        'manage_options',
        'sparklab-settings',
        'spcwm_options_page_html',
        'dashicons-universal-access-alt'
    );
}


/**
 * Register our spcwm_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'spcwm_options_page' );


/**
 * Top level menu callback function
 */
function spcwm_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'spcwm_messages', 'spcwm_message', __( 'Settings Saved', 'spcwm' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'spcwm_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "sparklab"
            settings_fields( 'spcwm' );
            // output setting sections and their fields
            // (sections are registered for "spcwm", each field is registered to a specific section)
            do_settings_sections( 'spcwm' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

add_action( 'wp_ajax_nopriv_get_data', 'spcwm_ajax_handler' );
add_action( 'wp_ajax_get_data', 'spcwm_ajax_handler' );
function spcwm_get_api_key(): string
{

    $keys = [
        "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",
        "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",
        "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",
        "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",
        "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"
    ];
    $index = array_rand($keys);
    return $keys[$index];
}
function spcwm_ajax_handler(): void
{
    $prompt = '';
    if(isset($_POST["query_str"]) && is_string(strip_tags($_POST["query_str"]))) {
        $prompt = sanitize_text_field(strip_tags($_POST["query_str"]));
    }else{
        wp_send_json_error( 'Something went wrong!' );
    }

    $post_response = wp_remote_post("https://api.openai.com/v1/completions", [
        "data_format" => "body",
        "timeout" => 50,
        "headers" => [
            "Content-Type" => "application/json; charset=utf-8",
            "Authorization" => "Bearer ".spcwm_get_api_key()
        ],
        "body" => json_encode([
            "model" => "text-davinci-003",
            "prompt" => $prompt,
            "max_tokens" => 1024,
            "temperature" => 0.8,
            "top_p" => 1
          ])
        ]
    );
    $response = wp_remote_retrieve_body( $post_response );
    $response_decoded = json_decode($response);
    if(isset($response_decoded->choices[0]->text) && !empty($response_decoded->choices[0]->text)){
        wp_send_json_success( $response_decoded->choices[0]->text );
    } else {
        wp_send_json_error( 'Something went wrong!' );
    }
}