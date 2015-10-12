<?php
/*
Plugin Name: Formidable Registration
Plugin URI: http://formidablepro.com/knowledgebase/formidable-registration/
Description: Register users through a Formidable form
Author: Strategy11
Author URI: http://strategy11.com
Version: 1.08
Text Domain: frmreg
*/

include_once(dirname( __FILE__ ) .'/FrmRegAppController.php');
$obj = new FrmRegAppController();

include_once(dirname( __FILE__ ) .'/FrmRegAppHelper.php');
$obj = new FrmRegAppHelper();

// Register Widgets
if(class_exists('WP_Widget')){
    require_once(dirname( __FILE__ ) .'/widgets/FrmRegLogin.php');
    add_action('widgets_init', create_function('', 'return register_widget("FrmRegLogin");'));
}


add_action('admin_init', 'frm_reg_include_updater', 1);
function frm_reg_include_updater(){
    include_once(dirname( __FILE__ ) .'/FrmRegUpdate.php');
    $frm_reg_update = new FrmRegUpdate();
}

