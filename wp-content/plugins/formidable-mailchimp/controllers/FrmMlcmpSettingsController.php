<?php

class FrmMlcmpSettingsController{
    function FrmMlcmpSettingsController(){
        add_action('frm_add_settings_section', 'FrmMlcmpSettingsController::add_settings_section');
        add_action('frm_add_form_settings_section', 'FrmMlcmpSettingsController::add_mailchimp_options', 10);
        add_action('frm_add_form_option_section', 'FrmMlcmpSettingsController::mailchimp_options');
        add_action('wp_ajax_frm_mlcmp_add_list', 'FrmMlcmpSettingsController::add_list');
        add_action('wp_ajax_frm_mlcmp_match_fields', 'FrmMlcmpSettingsController::match_fields');
        add_action('wp_ajax_frm_mlcmp_add_logic_row', 'FrmMlcmpSettingsController::add_logic_row');
        add_action('wp_ajax_frm_mlcmp_get_field_values', 'FrmMlcmpSettingsController::get_field_values');
        
        add_filter('frm_setup_new_form_vars', 'FrmMlcmpSettingsController::setup_new_vars');
        add_filter('frm_setup_edit_form_vars', 'FrmMlcmpSettingsController::setup_edit_vars');
        add_filter('frm_form_options_before_update', 'FrmMlcmpSettingsController::update_options', 15, 2);
    }

    public static function add_settings_section($sections){
        $sections['mailchimp'] = array('class' => 'FrmMlcmpSettingsController', 'function' => 'route');
        return $sections;
    }
    
    public static function add_mailchimp_options($sections){
        $sections['mailchimp'] = array('class' => 'FrmMlcmpSettingsController', 'function' => 'mailchimp_options');
        return $sections;
    }
    
    public static function match_fields(){
        $form_id = (isset($_POST['form_id'])) ? $_POST['form_id'] : false;
        $list_id = (isset($_POST['list_id'])) ? $_POST['list_id'] : false;
        if(!(int)$form_id or !$list_id)
            die;
            
        global $frm_mlcmp_settings;
        
        if(!class_exists('FRM_MCAPI'))
            require_once(FRM_MLCMP_PATH . '/MCAPI.class.php');
        
        $frm_field = new FrmField();
        $api = new FRM_MCAPI($frm_mlcmp_settings->api_key);
        $list_fields = $api->listMergeVars($list_id);
        $form_fields = $frm_field->getAll("fi.form_id=". (int)$form_id ." and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');
        $groups = $api->listInterestGroupings($list_id);
        $hide_mailchimp = '';

        include(FRM_MLCMP_PATH .'/views/settings/_match_fields.php');
        die();
    }
    
    public static function mailchimp_options($values){
        global $frm_mlcmp_settings;
        
        if(!class_exists('FRM_MCAPI'))
            require_once(FRM_MLCMP_PATH . '/MCAPI.class.php');
        
        $frm_field = new FrmField();  
        $api = new FRM_MCAPI($frm_mlcmp_settings->api_key);
        $lists = $api->lists();
        
        if(!empty($values['mlcmp_list']))
            $form_fields = $frm_field->getAll("fi.form_id='". (int)$values['id'] ."' and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');
         
        if(method_exists('FrmAppHelper', 'plugin_version'))
            $frm_version = FrmAppHelper::plugin_version();
        else
            global $frm_version;
        
        include_once(FRM_MLCMP_PATH .'/views/settings/mailchimp_options.php');
    }
    
    public static function add_list($list_id=false, $active=true){
        global $frm_mlcmp_settings;
        
        $hide_mailchimp = $active ? '' : 'style="display:none;"';
        $die = ($list_id) ? false : true;
        if(!$list_id and isset($_POST) and isset($_POST['list_id']))
            $list_id = $_POST['list_id'];
           
        if(!class_exists('FRM_MCAPI'))
            require_once(FRM_MLCMP_PATH . '/MCAPI.class.php');
            
        $api = new FRM_MCAPI($frm_mlcmp_settings->api_key);
        
        $lists = $api->lists();
            
        $list_options = array('optin' => 0);
        
        include(FRM_MLCMP_PATH .'/views/settings/_list_options.php');
        
        if($die)
            die();
    }
    
    public static function add_logic_row(){
        if(!isset($_POST) or !isset($_POST['list_id']))
            die();
            
        global $frm_field;
            
        $list_id = $_POST['list_id'];
        $form_id = (int)$_POST['form_id'];
        $meta_name = $_POST['meta_name'];
        $hide_field = '';
        
        $form_fields = $frm_field->getAll("fi.form_id = ". (int)$form_id ." and (type in ('select','radio','checkbox','10radio','scale','data') or (type = 'data' and (field_options LIKE '\"data_type\";s:6:\"select\"%' OR field_options LIKE '%\"data_type\";s:5:\"radio\"%' OR field_options LIKE '%\"data_type\";s:8:\"checkbox\"%') ))", "field_order");

        $frmdb = new FrmDb();
        $form_options = $frmdb->get_var($frmdb->forms, array('id' => $form_id), 'options');
        $form_options = maybe_unserialize($form_options);
        if(isset($form_options['mlcmp_list'][$list_id]))
            $list_options = $form_options['mlcmp_list'][$list_id];
        else
            $list_options = array('hide_field' => array(), 'hide_field_cond' => array(), 'hide_opt' => array());
        
        if(!isset($list_options['hide_field_cond'][$meta_name]))
            $list_options['hide_field_cond'][$meta_name] = '==';
            
        include(FRM_MLCMP_PATH .'/views/settings/_logic_row.php');
        
        die();
    }
    
    public static function get_field_values(){
        global $frm_field, $frm_entry_meta;
        
        $list_id = $meta_name = $_POST['list_id'];
        $form_id = (int)$_POST['form_id'];
        
        $new_field = $frm_field->getOne($_POST['field_id']);
        
        $frmdb = new FrmDb();
        $form_options = $frmdb->get_var($frmdb->forms, array('id' => $form_id), 'options');
        $form_options = maybe_unserialize($form_options);
        if(isset($form_options['mlcmp_list'][$list_id]))
            $list_options = $form_options['mlcmp_list'][$list_id];
        else
            $list_options = array('hide_field' => array(), 'hide_field_cond' => array(), 'hide_opt' => array());
            
        require(FRM_MLCMP_PATH .'/views/settings/_field_values.php');
        die();
    }
    
    public static function setup_new_vars($values){
        $defaults = FrmMlcmpAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            $values[$opt] = FrmAppHelper::get_param($opt, $default);
            unset($default);
            unset($opt);
        }
        return $values;
    }
    
    public static function setup_edit_vars($values){
        $defaults = FrmMlcmpAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            if (!isset($values[$opt]))
                $values[$opt] = ($_POST and isset($_POST['options'][$opt])) ? $_POST['options'][$opt] : $default;
            unset($default);
            unset($opt);
        }
        
        if(isset($_POST) and isset($_POST['options']['mlcmp_list']))
            $values['mlcmp_list'] = $_POST['options']['mlcmp_list'];

        return $values;
    }
    
    public static function update_options($options, $values){
        $defaults = FrmMlcmpAppHelper::get_default_options();
        
        foreach($defaults as $opt => $default){
            $options[$opt] = (isset($values['options'][$opt])) ? $values['options'][$opt] : $default;
            unset($default);
            unset($opt);
        }

        unset($defaults);
        
        return $options;
    }
    
    public static function display_form(){
        global $frm_mlcmp_settings;
        
        if(method_exists('FrmAppHelper', 'plugin_version'))
            $frm_version = FrmAppHelper::plugin_version();
        else
            global $frm_version;

        require_once(FRM_MLCMP_PATH . '/views/settings/form.php');
    }

    public static function process_form(){
        global $frm_mlcmp_settings;
        
        if(method_exists('FrmAppHelper', 'plugin_version'))
            $frm_version = FrmAppHelper::plugin_version();
        else
            global $frm_version;

        //$errors = $frm_mlcmp_settings->validate($_POST,array());
        $errors = array();
        
        $frm_mlcmp_settings->update($_POST);

        if( empty($errors) ){
            $frm_mlcmp_settings->store();
            $message = __('Settings Saved', 'formidable');
        }

        require_once(FRM_MLCMP_PATH . '/views/settings/form.php');
    }

    public static function route(){
        $action = FrmAppHelper::get_param('action');
        if($action == 'process-form')
            return FrmMlcmpSettingsController::process_form();
        else
            return FrmMlcmpSettingsController::display_form();
    }
}