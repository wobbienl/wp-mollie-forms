<?php

/*
Plugin Name: Mollie Forms
Description: Create registration forms with payment methods of Mollie. One-time and recurring payments are possible.
Version: 2.7.5
Author: Wobbie.nl
Author URI: https://wobbie.nl
Text Domain: mollie-forms
License: GPL-2.0+
*/

if (!defined('ABSPATH')) {
    die('Please do not load this file directly!');
}

// include files
require_once 'classes/Exception.php';
require_once 'classes/MollieApi.php';
require_once 'classes/Helpers.php';
require_once 'classes/MollieForms.php';
require_once 'classes/FormBuilder.php';
require_once 'classes/Form.php';
require_once 'classes/Webhook.php';
require_once 'classes/Migrator.php';

// start the plugin
$plugin = new \MollieForms\MollieForms();
$plugin->setPluginFile(__FILE__);

// include admin files
if (is_admin()) {
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    require_once 'classes/RegistrationsTable.php';
    require_once 'classes/Admin.php';
    $admin = new \MollieForms\Admin($plugin);
}

// init the migrator
$migrator = new \MollieForms\Migrator($plugin);
$form     = new \MollieForms\Form($plugin);
$webhook  = new \MollieForms\Webhook($plugin);

if (get_option('mollie-forms_version') != $plugin->getVersion()) {
    $migrator->runMigration();
}

register_activation_hook(__FILE__, [$migrator, 'runMigration']);

load_plugin_textdomain('mollie-forms');
