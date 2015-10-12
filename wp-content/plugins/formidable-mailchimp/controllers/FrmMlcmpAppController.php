<?php

class FrmMlcmpAppController{
    function FrmMlcmpAppController(){
        add_action('frm_entry_form', 'FrmMlcmpAppController::hidden_form_fields', 10, 2);
        add_action('frm_after_create_entry', 'FrmMlcmpAppController::send_to_mailchimp', 25, 2);
        add_action('frm_after_update_entry', 'FrmMlcmpAppController::send_to_mailchimp', 25, 2);
    }
    
    public static function hidden_form_fields($form, $form_action){
        $form->options = maybe_unserialize($form->options);
        if(!isset($form->options['mailchimp']) or !$form->options['mailchimp'] or !isset($form->options['mlcmp_list']) or !is_array($form->options['mlcmp_list']))
            return;
            
        echo '<input type="hidden" name="frm_mailchimp" value="1"/>'."\n";
        
        if($form_action != 'update')
            return;
        
        global $frm_vars, $frm_editing_entry, $frm_entry_meta;
        $list = reset($form->options['mlcmp_list']);
        $field_id = $list['fields']['EMAIL'];
        $edit_id = (is_array($frm_vars) and isset($frm_vars['editing_entry'])) ? $frm_vars['editing_entry'] : $frm_editing_entry;
        $email = $frm_entry_meta->get_entry_meta((int)$frm_editing_entry, $field_id);
        echo '<input type="hidden" name="frm_mailchimp_email" value="'. esc_attr($email) .'"/>'."\n";
    }
    
    public static function send_to_mailchimp($entry_id, $form_id){
        if(!isset($_POST) or !isset($_POST['frm_mailchimp']))
            return;
        
        global $frm_mlcmp_settings;
        
        $frmdb = new FrmDb();
        $form_options = $frmdb->get_var($frmdb->forms, array('id' => $form_id), 'options');
        $form_options = maybe_unserialize($form_options);
        if(!isset($form_options['mailchimp']) or !$form_options['mailchimp'])
            return;
        
        if(!class_exists('FRM_MCAPI'))
            require_once(FRM_MLCMP_PATH . '/MCAPI.class.php');
        
        // grab an API Key from http://admin.mailchimp.com/account/api/    
        $api = new FRM_MCAPI($frm_mlcmp_settings->api_key);
        
        foreach($form_options['mlcmp_list'] as $list_id => $list_options){
            //check conditions
            $subscribe = true;
            if(isset($list_options['hide_field']) and is_array($list_options['hide_field'])){
                //for now we are assuming that if all conditions are met, then the user will be subscribed
                foreach($list_options['hide_field'] as $hide_key => $hide_field){
                    if(!$subscribe)
                        continue;
                        
                    $observed_value = (isset($_POST['item_meta'][$hide_field])) ? $_POST['item_meta'][$hide_field] : '';
                    
                    if ($observed_value == ''){
                        $subscribe = false;
                    }else if(class_exists('FrmProFieldsHelper')){
                        $subscribe = FrmProFieldsHelper::value_meets_condition($observed_value, $list_options['hide_field_cond'][$hide_key], $list_options['hide_opt'][$hide_key]);
                    }
                }
            }

            if(!$subscribe) //don't subscribe if conditional logic is not met
                continue;
            
            $list_fields = $api->listMergeVars($list_id);
            
            $vars = array();
            foreach($list_options['fields'] as $field_tag => $field_id){
                $vars[$field_tag] = (isset($_POST['item_meta'][$field_id])) ? $_POST['item_meta'][$field_id] : '';
                if(is_numeric($vars[$field_tag])){
                    $field = FrmField::getOne($field_id);
                    if($field->type == 'user_id'){
                        $user_data = get_userdata($vars[$field_tag]);
                        if($field_tag == 'EMAIL')
                            $vars[$field_tag] = $user_data->user_email;
                        else if($field_tag == 'FNAME')
                            $vars[$field_tag] = $user_data->first_name;
                        else if($field_tag == 'LNAME')
                            $vars[$field_tag] = $user_data->last_name;
                        else
                            $vars[$field_tag] = $user_info->user_login;
                    }else{
                        $vars[$field_tag] = FrmProEntryMetaHelper::display_value($vars[$field_tag], $field, array('type' => $field->type, 'truncate' => false, 'entry_id' => $entry_id)); 
                    }
                }else{
                    global $frmpro_settings;
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', trim($vars[$field_tag])))
                	    $vars[$field_tag] = FrmProAppHelper::convert_date($vars[$field_tag], $frmpro_settings->date_format, 'Y-m-d');
                	$list_field = false;
                	foreach($list_fields as $lf){
                	    if($lf['tag'] == $field_tag){
                	        $list_field = $lf;
                	        continue;
                	    }
                	    unset($lf);
                	}
                	
                	if($list_field){
                	    if(isset($list_field['dateformat'])){
                	        $list_field['dateformat'] = str_replace('YYYY', 'Y', str_replace('DD', 'd', str_replace('MM', 'm', $list_field['dateformat'])));
                	        $vars[$field_tag] = date($list_field['dateformat'], strtotime($vars[$field_tag]));
                	    }
                	}
                	
                }
                
                if(is_array($vars[$field_tag]))
                    $vars[$field_tag] = implode(', ', $vars[$field_tag]);
            }

            unset($list_fields);
            
            if(isset($list_options['groups'])){
                $vars['GROUPINGS'] = array();
                foreach($list_options['groups'] as $g_id => $group){
                    $selected_grp = (isset($_POST['item_meta'][$group['id']])) ? $_POST['item_meta'][$group['id']] : '';
                    if(empty($selected_grp))
                        continue;
                        
                    if(is_array($selected_grp)){
                        $grps = '';
                        foreach($selected_grp as $sel_g){
                            $sel_g = array_search($sel_g, $group);
                            $grps .= str_replace(',', '\,', $sel_g).',';
                        }
                    }else{
                        $selected_grp = array_search($selected_grp, $group);
                        $grps = str_replace(',', '\,', $selected_grp);
                    }
                    unset($selected_grp);
                    
                    $vars['GROUPINGS'][] = array('id' => $g_id, 'groups' => $grps);
                    unset($g_id);
                    unset($group);
                }
                if(empty($vars['GROUPINGS']))
                    unset($vars['GROUPINGS']);
            }
            
            if(!isset($vars['EMAIL'])) //no email address is mapped
                return;
            
        	$email_type = $frm_mlcmp_settings->email_type;
        	$replace_interests = true; 
            $double_optin = (isset($list_options['optin'])) ? $list_options['optin'] : false;
            $send_welcome = false;
            
            if(isset($_POST['frm_mailchimp_email']) and is_email($_POST['frm_mailchimp_email'])){ //we are editing the entry
                $update_existing = true;
                $email_field = $_POST['frm_mailchimp_email'];
            }else{
                $update_existing = false;
                $email_field = $vars['EMAIL'];
            }
            
            $update_existing = apply_filters('frm_mlcmp_update_existing', $update_existing, compact('list_id', 'email_field', 'vars', 'email_type', 'double_optin', 'replace_interests', 'send_welcome'));
            $send_welcome = apply_filters('frm_mlcmp_send_welcome', $send_welcome, compact('list_id', 'email_field', 'vars', 'email_type', 'double_optin', 'update_existing', 'replace_interests'));
            
            $api->listSubscribe($list_id, $email_field, $vars, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome);
            
            if ($api->errorCode){
            error_log("Unable to load listSubscribe()!\n".
                "\tCode=".$api->errorCode."\n".
                "\tMsg=".$api->errorMessage."\n"
            );
            }
        }
    }

}