<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WooCommerce GetNet Redirect
 * Plugin URI:        -
 * Description:       -
 * Version:           1.0.5
 * Author:            -
 * Author URI:        -
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc_getnet_redirect
 * Domain Path:       /languages
 */

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

require_once __DIR__ . '/vendor/autoload.php';

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Checks that woocommerce is installed
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    error_log('Woocommerce is not active so the woo-cdp plugin is not running correctly');
    return ;
}

// Setup constants
define('PLUGIN_FILE', __FILE__);

// Starts the Kernel of the plugin
use App\Kernel;
Kernel::init();
