<?php

/*
 * Plugin Name: RT Contributors
 * Plugin URI: #
 * Description: Displays contributors on post pages.
 * Version: 1.0.0
 * Author: Shweta Danej
 * Author URI: #
 * Text Domain: rtc
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    return;
}

/**
 * Plugin file path
 *
 * @since 1.0.0
 * @var string RTC_FILE
 */
define('RTC_FILE', __FILE__);
/**
 * Plugin directory path
 *
 * @since 1.0.0
 * @var string RTC_DIR
 */
define('RTC_DIR', plugin_dir_path(__FILE__));
/**
 * Plugin directory url
 *
 * @var string RTC_URL
 * @since 1.0.0
 */
define('RTC_URL', plugin_dir_url(__FILE__));
/**
 * Plugin classes directory path
 *
 * @var string RTC_CLASSES
 * @since 1.0.0
 */
define('RTC_CLASSES', RTC_DIR . 'classes/');
/**
 * Plugin template directory
 *
 * @var string RTC_TEMPLATE
 * @since 1.0.0
 */
define('RTC_TEMPLATE', RTC_DIR . 'templates/');
/**
 * Plugin name
 *
 * @var string RTC_NAME
 * @since 1.0.0
 */
define('RTC_NAME', 'RT Contributors');

add_action('plugins_loaded', 'rtc_init');

if (!function_exists('rtc_init')) {

    /**
     * Initialization.
     * 
     * @since 1.0.0
     */
    function rtc_init() {
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'rtc');
        unload_textdomain('rtc');
        load_textdomain('rtc', RTC_DIR . 'languages/' . "rtc-" . $locale . '.mo');
        load_plugin_textdomain('rtc', false, RTC_DIR . 'languages');
        require_once( RTC_CLASSES . 'class.rtc_main.php' );
    }
}