<?php
/*
Plugin Name: Formidable to MailChimp
Description: Save and update MailChimp contacts from your Formidable forms
Version: 1.01
Plugin URI: http://formidablepro.com/
Author URI: http://strategy11.com
Author: Strategy11
*/

define('FRM_MLCMP_PATH', dirname( __FILE__ ));

/***** SETUP SETTINGS OBJECT *****/
require_once(FRM_MLCMP_PATH .'/models/FrmMlcmpSettings.php');

global $frm_mlcmp_settings;

$frm_mlcmp_settings = get_option('frm_mlcmp_options');

if(!is_object($frm_mlcmp_settings)){
    if($frm_mlcmp_settings){
        $frm_mlcmp_settings = unserialize(serialize($frm_mlcmp_settings));
    }else{
        $frm_mlcmp_settings = new FrmMlcmpSettings();
        update_option('frm_mlcmp_options', $frm_mlcmp_settings);
    }
}

$frm_mlcmp_settings->set_default_options(); // Sets defaults for unset options

//Controllers
require_once(FRM_MLCMP_PATH .'/controllers/FrmMlcmpAppController.php');
require_once(FRM_MLCMP_PATH .'/controllers/FrmMlcmpSettingsController.php');

$obj = new FrmMlcmpAppController();
$obj = new FrmMlcmpSettingsController();

include_once(FRM_MLCMP_PATH .'/helpers/FrmMlcmpAppHelper.php');
$obj = new FrmMlcmpAppHelper();


add_action('admin_init', 'frm_mlcmp_include_updater', 1);
function frm_mlcmp_include_updater(){
    include_once(FRM_MLCMP_PATH .'/models/FrmMlcmpUpdate.php');
    $frm_mlcmp_update = new FrmMlcmpUpdate();
}
