<?php
/*
Plugin Name: MLM plugins
Version: 1.0.0
Plugin URI: https://buildsalon.co.jp/
Author: BuildSalon & Rinshen
Description: 
Text Domain: wp_buildsalon_mlm_affipoint
Domain Path: /
*/

/*History*/
/*
1.0.0 Create new
1.0.1 Adding a membership
1.0.2 Fix issue
*/

//Direct access to this file is not permitted
if (!defined('ABSPATH')){
    exit("Do not access this file directly.");
}

define('BS_MLM_AFFIPOINT_VER', '1.0.2');
define('BS_MLM_AFFIPOINT_PATH', dirname(__FILE__) . '/');
define('BS_MLM_AFFIPOINT_URL', plugins_url('', __FILE__));
define('BS_MLM_AFFIPOINT_DIRNAME', dirname(plugin_basename(__FILE__)));
define('BS_MLM_AFFIPOINT_TEMPLATE_PATH', 'bs-mlm-affipoint');

// permission define
define('BS_MLM_AFFIPOINT_MANAGEMENT_PERMISSION', 'manage_options');

include_once('classes/class_mlm_affipoint_main.php');
$bs_affiliate_main_obj = BSMlmAffiPointMain::get_instance(); // regist hook function

function bs_mlm_affipoint_activate() {
	BSMlmAffiPointMain::get_instance()->plugin_installer();
    if ( !wp_next_scheduled( 'schedule_generate_point_monthly' )) {
        error_log("mlm-schedule_generate_point_monthly-scheduled", 0);
        wp_schedule_event( strtotime( '01:00:00' ), 'daily', 'schedule_generate_point_monthly' );
    }
}
function bs_mlm_affipoint_deactivate() {
    wp_clear_scheduled_hook('schedule_generate_point_monthly');
}
register_activation_hook(__FILE__, 'bs_mlm_affipoint_activate');
register_deactivation_hook(__FILE__, 'bs_mlm_affipoint_deactivate');

include_once('classes/class_mlm_affipoint_admin_tree.php');
add_action( 'schedule_generate_point_monthly', 'BSMlmAffiPointAdminCalculate::schedule_generate_point_monthly_function' );

