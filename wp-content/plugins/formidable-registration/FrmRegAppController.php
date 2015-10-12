<?php
 
class FrmRegAppController{
    function FrmRegAppController(){
        add_action('plugins_loaded', 'FrmRegAppController::load_lang');
        add_filter('frm_add_form_settings_section', 'FrmRegAppController::add_registration_options');
        add_action('wp_ajax_frm_add_usermeta_row', 'FrmRegAppController::_usermeta_row');
        add_filter('frm_setup_new_form_vars', 'FrmRegAppController::setup_new_vars');
        add_filter('frm_setup_edit_form_vars', 'FrmRegAppController::setup_edit_vars');
        add_filter('frm_form_options_before_update', 'FrmRegAppController::update_options', 15, 2);
        
        add_action('frm_entry_form', 'FrmRegAppController::hidden_form_fields');
        add_filter('frm_validate_field_entry', 'FrmRegAppController::validate', 20, 2);
        add_action('frm_after_create_entry', 'FrmRegAppController::create_user', 30, 2);
        
        add_action('frm_after_update_entry', 'FrmRegAppController::update_user', 25, 2);
        add_filter('frm_setup_edit_fields_vars', 'FrmRegAppController::check_updated_user_meta', 10, 3); 
        
        add_action('show_user_profile', 'FrmRegAppController::show_usermeta', 200);
        add_action('edit_user_profile', 'FrmRegAppController::show_usermeta', 200);
        
        add_shortcode('frm-login', 'FrmRegAppController::login_form');
        
        add_action('wp_ajax_nopriv_frm_registration_signon', 'FrmRegAppController::signon');
    }
    
    public static function path(){
        return dirname( __FILE__ );
    }
    
    public static function load_lang(){
        load_plugin_textdomain('frmreg', false, 'formidable-registration/languages/' );
    }
    
    public static function add_registration_options($sections){
        $sections['registration'] = array('class' => 'FrmRegAppController', 'function' => 'registration_options');
        return $sections;
    }
    
    public static function registration_options($values){
        if(!class_exists('FrmProFieldsHelper'))
            return;
            
        $frm_field = new FrmField();
        if(isset($values['id']))
            $fields = $frm_field->getAll("fi.form_id='$values[id]' and fi.type not in ('divider', 'html', 'break', 'captcha', 'rte')", ' ORDER BY field_order');
        $echo = true;
        
        if(method_exists('FrmAppHelper', 'plugin_version'))
            $frm_version = FrmAppHelper::plugin_version();
        else
            global $frm_version; //version fallback < v1.07.02
        
        include(self::path() .'/views/registration_options.php');
    }
    
    public static function _usermeta_row($meta_name=false, $field_id=''){
        if(!$meta_name and isset($_POST['meta_name']))
            $meta_name = $_POST['meta_name'];
        
        if(isset($_POST['form_id'])){
            $frm_field = new FrmField();
            $fields = $frm_field->getAll("fi.form_id='$_POST[form_id]' and fi.type not in ('divider', 'html', 'break', 'captcha')", ' ORDER BY field_order');
        }
        $echo = false;   
        include(self::path() .'/views/_usermeta_row.php');
        die();
    }
    
    public static function setup_new_vars($values){
        $defaults = FrmRegAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            $values[$opt] = FrmAppHelper::get_param($opt, $default);
            unset($default);
            unset($opt);
        }
        return $values;
    }
    
    public static function setup_edit_vars($values){   
        $defaults = FrmRegAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            if (!isset($values[$opt]))
                $values[$opt] = ($_POST and isset($_POST['options'][$opt])) ? $_POST['options'][$opt] : $default;
            unset($default);
            unset($opt);
        }
        
        return $values;
    }
    
    public static function update_options($options, $values){
        $defaults = FrmRegAppHelper::get_default_options();
        unset($defaults['reg_usermeta']);
        
        foreach($defaults as $opt => $default){
            $options[$opt] = (isset($values['options'][$opt])) ? $values['options'][$opt] : $default;
            unset($default);
            unset($opt);
        }
        
        $options['reg_usermeta'] = array();
        if(isset($values['options']['reg_usermeta']) and isset($values['options']['reg_usermeta']['meta_name'])){
            foreach($values['options']['reg_usermeta']['meta_name'] as $meta_key => $meta_value){
                if(!empty($meta_value) and !empty($values['options']['reg_usermeta']['field_id'][$meta_key]))
                    $options['reg_usermeta'][$meta_value] = $values['options']['reg_usermeta']['field_id'][$meta_key];
            }
        }

        unset($defaults);
        
        //Make sure the form includes a User ID field for correct editing
        if ($options['registration']){
            $frm_field = new FrmField();
            $form_id = $values['id'];
            $user_field = $frm_field->getAll(array('fi.form_id' => $form_id, 'type' => 'user_id'));
            if (!$user_field){
                $new_values = FrmFieldsHelper::setup_new_vars('user_id', $form_id);
                $new_values['name'] = __('User ID', 'frmreg');
                $frm_field->create($new_values);
                unset($new_values);
            }
            unset($user_field);
        }
        
        return $options;
    }
    
    public static function hidden_form_fields($form){
        if(isset($form->options['registration']) and $form->options['registration']){
            if(isset($form->options['reg_username']) and $form->options['reg_username'])
                echo '<input type="hidden" name="frm_register[username]" value="'. $form->options['reg_username'] .'"/>'."\n";
            
            echo '<input type="hidden" name="frm_register[email]" value="'. $form->options['reg_email'] .'"/>'."\n";
        }
    }
    
    public static function validate($errors, $field){
        if(isset($_POST) and isset($_POST['frm_register'])){
            $value = $_POST['item_meta'][$field->id];
            if(!empty($value) and 
                ($field->type == 'password' or 
                    (isset($_POST['frm_register']['username']) and (int)$field->id == $_POST['frm_register']['username']) or
                    (isset($_POST['frm_register']['email']) and ((int)$field->id == $_POST['frm_register']['email']))
                )){
                
                global $user_ID;
                $required_role = apply_filters('frmreg_required_role', 'administrator');
                if($user_ID and (!is_admin() or defined('DOING_AJAX')) and !current_user_can($required_role))
                    return $errors; //don't check if user is logged in because a new user won't be created anyway
                
                $posted_id = isset($_POST['frm_user_id']) ? $_POST['frm_user_id'] : 0;
                if(!$posted_id and !isset($_POST['frm_user_id'])){
                    global $wpdb;
                    $user_id_field = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_fields WHERE type=%s AND form_id=%d", 'user_id', $field->form_id));
                    if($user_id_field)
                        $posted_id = $_POST['frm_user_id'] = $_POST['item_meta'][$user_id_field];
                }
                
                if($posted_id){
                    $user_data = get_userdata($posted_id);
                    $old_username = $user_data->user_login;
                    $old_email = $user_data->user_email;
                }else if($user_ID){
                    $user_data = get_userdata($user_ID);
                    $old_username = $user_data->user_login;
                    $old_email = $user_data->user_email;
                }else{
                    $old_username = $old_email = false;
                }
                
                if(isset($_POST['frm_register']['username']) and (int)$field->id == $_POST['frm_register']['username']){
                    //if there is a username field in the form
                    $username = $_POST['item_meta'][$_POST['frm_register']['username']];
                    
                    if((($old_username and strtolower($username) != strtolower($old_username)) or !$old_username) and FrmRegAppHelper::username_exists($username))
                        $errors['field'. $field->id] = __('This username is already registered.', 'frmreg');

                }
                
                if(isset($_POST['frm_register']['email']) and ((int)$field->id == $_POST['frm_register']['email'])){
                    if(!function_exists('email_exists'))
                        require_once(ABSPATH . WPINC . '/registration.php');

                    //check if email has already been used
                    if((($old_email and $value != $old_email) or !$old_email) and email_exists($value) )
                		$errors['field'. $field->id] = __('This email address is already registered.', 'frmreg');
                }

            }else if($field->type == 'password' and isset($errors['field'. $field->id]) and empty($_POST['item_meta'][$field->id])){
                //Don't require password if updating
                if((isset($_POST['frm_action']) and $_POST['frm_action'] == 'update') or (isset($_POST['action']) and $_POST['action'] == 'update')) //if updating
                    unset($errors['field'. $field->id]); //remove the error message
            }
        }
    
        return $errors;
    }
    
    public static function create_user($entry_id, $form_id){ //TODO: add wp_noonce
        if (isset($_POST) and isset($_POST['frm_register'])){
            global $user_ID;
            if($user_ID){
                $required_role = apply_filters('frmreg_required_role', 'administrator');
                if((is_admin() and !defined('DOING_AJAX')) or current_user_can($required_role)){
                    //don't require the user to edit their own record
                    $frm_entry = new FrmEntry();
                    $entry = $frm_entry->getOne($entry_id);
                    unset($frm_entry);
                    
                    $e = isset($_POST['frm_register']['email']) ? $_POST['item_meta'][$_POST['frm_register']['email']] : false;
                    if($entry and ($user_ID == $entry->user_id or ($e and email_exists($e)))){
                        //allow admin users to update their profile when creating an entry
                        self::update_user($entry_id, $form_id);
                        return;
                    }
                }else{
                    //if user is already logged-in, then update the user
                    self::update_user($entry_id, $form_id);
                    return;
                }
                unset($required_role);
            } 
            
            $frm_form = new FrmForm();
            $form = $frm_form->getOne($form_id);
            unset($frm_form);

            if(!isset($form->options['registration']) or 
                !$form->options['registration'] or 
                !isset($form->options['reg_email']) or 
                !isset($_POST['item_meta'][$form->options['reg_email']]))
                return;
  
            
            $user_meta = self::_get_usermeta($form->options);

            if(!isset($user_meta['user_pass']) or empty($user_meta['user_pass']))
                $user_meta['user_pass'] = wp_generate_password();
            
            if(empty($form->options['reg_username'])){
                //if the username will be generated from the email
                $parts = explode("@", $user_meta['user_email']);
                $user_meta['user_login'] = $parts[0];
            }else if($form->options['reg_username'] == '-1'){    
                //if the username will be generated from the full email
                $user_meta['user_login'] = $user_meta['user_email'];
            }else{
                $user_meta['user_login'] = $_POST['item_meta'][$form->options['reg_username']];
            }
            $user_meta['user_login'] = FrmRegAppHelper::generate_unique_username($user_meta['user_login']);
            
            if(isset($form->options['reg_display_name']) and !empty($form->options['reg_display_name']))
                $user_meta['display_name'] = self::_generate_display_name($form->options);

            $new_role = (isset($form->options['reg_role'])) ? $form->options['reg_role'] : 'subscriber';
            $user_meta['role'] = apply_filters('frmreg_new_role', $new_role, array('form' => $form));
            unset($new_role);
            
            if(!function_exists('username_exists'))
                require_once(ABSPATH . WPINC . '/registration.php');
                
            $user_meta = apply_filters('frmreg_user_data', $user_meta, array('action' => 'create', 'form' => $form));
            
            $user_id = wp_insert_user($user_meta);
            if(is_wp_error($user_id)){
                wp_die($user_id->get_error_message());
                return;
            }
            
            $user_id = (int)$user_id;
            if($user_id){
                global $wpdb;
                // set user id field
                $wpdb->update( $wpdb->prefix .'frm_items', array('user_id' => $user_id, 'updated_by' => $user_id), array('id' => $entry_id) );
                wp_cache_delete($entry_id, 'frm_entry');
                
                $_POST['frm_user_id'] = $user_id;
                $user_field = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_fields WHERE type=%s AND form_id=%d", 'user_id', $form_id));
                
                $frm_entry_meta = new FrmEntryMeta();
                $frm_entry_meta->delete_entry_meta($entry_id, $user_field);
                $frm_entry_meta->add_entry_meta($entry_id, $user_field, '', $user_id);
                
                //remove password from database
                if(isset($form->options['reg_password']) and !empty($form->options['reg_password']))
                    $frm_entry_meta->delete_entry_meta($entry_id, (int)$form->options['reg_password']);
                
                unset($frm_entry_meta);
                
                //Update usermeta
                self::update_usermeta($form->options, $user_id);
                
                // send new user notifications
                wp_new_user_notification($user_id, ''); // sending a blank password only sends notification to admin
                self::new_user_notification($user_id, $user_meta['user_pass'], $form, $entry_id);
                
                //log user in
                if(!isset($form->options['login']) or $form->options['login'])
                    self::auto_login($user_meta['user_login'], $user_meta['user_pass']);
            }
            
        }
    }
    
    private static function new_user_notification( $user_id, $plaintext_pass, $form, $entry_id ){			
		$user = new WP_User( $user_id );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );
        $form->options = maybe_unserialize($form->options);

        //Add a filter so the email notification can be stopped
		if( apply_filters( 'frm_send_new_user_notification', true, $form, $entry_id ) ){ 
		    if( function_exists( 'is_multisite' ) && is_multisite() ){
    			$blogname = $GLOBALS['current_site']->site_name;
    		}else{
    			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
    			// we want to reverse this for the plain text arena of emails.
    			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    		}
		    
		    if(!isset($form->options['reg_email_msg']) or empty($form->options['reg_email_msg'])){
		        $message  = sprintf(__('Username: %s', 'frmreg'), $user_login) . "\r\n";
        	    $message .= sprintf(__('Password: %s', 'frmreg'), $plaintext_pass) . "\r\n";
        	    $message .= wp_login_url() . "\r\n";
    	    }else{
    	        $message = str_replace('[password]', $plaintext_pass, $form->options['reg_email_msg'] );
    	        $message = str_replace('[username]', $user_login, $message );
    	        $message = str_replace('[sitename]', $blogname, $message );
    	        $message = apply_filters('frm_content', $message, $form, $entry_id);
    	    }
            $message = apply_filters( 'frm_new_user_notification_message', $message, $plaintext_pass, $user_id );
            
            if(!isset($form->options['reg_email_subject']) or empty($form->options['reg_email_subject'])){
        	    $title = sprintf( __( '[%s] Your username and password', 'frmreg' ), $blogname);
        	}else{
        	    $title = str_replace('[sitename]', $blogname, $form->options['reg_email_subject'] );
        	    $title = apply_filters('frm_content', $title, $form, $entry_id);
        	}
			$title = apply_filters( 'frm_new_user_notification_title', $title, $user_id );

			wp_mail( $user_email, $title, $message );
		}
	}
    
    public static function auto_login($log, $pwd, $id=false){
        if(is_user_logged_in())
            return;
        
        if(method_exists('FrmAppHelper', 'plugin_version'))
            $frm_version = FrmAppHelper::plugin_version();
        else
            global $frm_version;
        
        //TODO: check if ajax submit is enabled
        if(version_compare( $frm_version, '1.06.03', '>')){
            $_POST['log'] = $log;
            $_POST['pwd'] = $pwd;
            if($id)
                $_POST['frm_id'] = $id;
                
            self::signon();
            return;
        }
        
        if(isset($_POST['frm_skip_cookie']))
            return;
?>
<script type="text/javascript">
jQuery(document).ready(function($){
jQuery.ajax({type:"POST",url:"<?php echo admin_url('admin-ajax.php'); ?>",
data:"action=frm_registration_signon&log=<?php echo $log ?>&pwd=<?php echo $pwd ?><?php if($id) echo '&frm_id='. $id ?>"
});
});    
</script>
<?php
    }
    
    public static function signon(){
        if(is_user_logged_in())
            return;
        
        if(isset($_POST['frm_id'])){
            wp_clear_auth_cookie();
			wp_set_auth_cookie($_POST['frm_id']);
        }else{
            $log = wp_signon();  
        }                
        //wp_set_current_user($user_id, $user_meta['user_login']);
    }
    
    public static function update_user($entry_id, $form_id){
        if(!(int)$form_id) return;
        
        $frm_form = new FrmForm();
        $form = $frm_form->getOne($form_id);
        unset($frm_form);
        
        if(!isset($form->options['registration']) or !$form->options['registration'])
            return;
        
        global $user_ID;
        $posted_id = isset($_POST['frm_user_id']) ? $_POST['frm_user_id'] : $user_ID;
        
        if((int)$posted_id){
            $user_obj = get_userdata( $posted_id );
            $user_meta = $user_obj->to_array();
            if(function_exists('_get_additional_user_keys')){
                foreach ( _get_additional_user_keys( $user_obj ) as $key ){
                    $user_meta[$key] = get_user_meta( (int)$posted_id, $key, true );
                    unset($key);
                }
            }else{
                //set profile checkboxes to current values
                foreach(array('rich_editing', 'admin_color', 'show_admin_bar_front', 'use_ssl', 'first_name', 'last_name') as $m){
                    if(!isset($user_meta[$m]))
                        $user_meta[$m] = get_user_meta($user_meta['ID'], $m, true);
                    unset($m);
                }
            }
        }else{
            $user_meta = array();
        }
        
        if(isset($user_meta['user_pass']))
            unset($user_meta['user_pass']);
        
        $user_meta = self::_get_usermeta($form->options, $user_meta);
        
        if($posted_id){
            $required_role = apply_filters('frmreg_required_role', 'administrator');
            if($user_ID and ($posted_id != $user_ID)){
                if((is_admin() and !defined('DOING_AJAX')) or current_user_can($required_role) ){
                    //allow editing if allowed or editing from the admin
                }else{
                    return; //make sure this record is updated by the owner or from the admin
                }
            }
            $user_meta['ID'] = $posted_id;
            unset($required_role);
        }
        
        if(empty($form->options['reg_username'])){
            $user_data = get_userdata($posted_id);
            $user_meta['user_login'] = $user_data->user_login;
        }else if($form->options['reg_username'] == '-1' and isset($user_meta['user_email'])){
            $user_meta['user_login'] = $user_meta['user_email'];
        }else{
            $user_meta['user_login'] = $_POST['item_meta'][$form->options['reg_username']];
        }
        
        if(isset($form->options['reg_display_name']) and !empty($form->options['reg_display_name']))
            $user_meta['display_name'] = self::_generate_display_name($form->options);
        
        if(isset($user_meta['ID']) and isset($user_meta['user_pass']))
            $user_meta['user_pass'] = wp_hash_password($user_meta['user_pass']);
           
        if(!function_exists('username_exists'))
            require_once(ABSPATH . WPINC . '/registration.php');
            
        $user_meta = apply_filters('frmreg_user_data', $user_meta, array('action' => 'update', 'form' => $form));
        
        if(!$user_meta)
            return;
        
        $user_id = wp_insert_user($user_meta);
        
        if($user_id and is_numeric($user_id)){
            self::update_usermeta($form->options, $user_id);
            
            //remove password from database
            if(isset($user_meta['user_pass']) and isset($form->options['reg_password']) and !empty($form->options['reg_password'])){
                $frm_entry_meta = new FrmEntryMeta();
                $frm_entry_meta->delete_entry_meta($entry_id, (int)$form->options['reg_password']);
                unset($frm_entry_meta);
                
                $current_user = wp_get_current_user();
                if((!is_admin() or defined('DOING_AJAX')) and isset($user_meta['user_pass']) and $current_user->ID == $user_id){
                    if(!isset($user_meta['user_login']))
                        $user_meta['user_login'] = $current_user->user_login;
                    self::auto_login($user_meta['user_login'], $user_meta['user_pass'], $current_user->ID);
                }
            }
        }
    }
    
    public static function check_updated_user_meta($values, $field, $entry_id=false){
        global $user_ID;
        
        if(in_array($field->type, array('data', 'checkbox')))
            return $values;
        
        $frm_form = new FrmForm();
        $form = $frm_form->getOne($field->form_id);
        unset($frm_form);

        if(!isset($form->options['registration']) or empty($form->options['registration']))
            return $values;
        
        if(!isset($form->options['reg_usermeta']) or empty($form->options['reg_usermeta']))
            $form->options['reg_usermeta'] = array();
        
        $form->options['reg_usermeta']['username'] = $form->options['reg_username'];
        $form->options['reg_usermeta']['user_email'] = $form->options['reg_email'];
        if($form->options['reg_username'] == '-1')
            $form->options['reg_username'] = $form->options['reg_email'];
            
        $form->options['reg_usermeta']['first_name'] = $form->options['reg_first_name'];
        $form->options['reg_usermeta']['last_name'] = $form->options['reg_last_name'];
        
        if(isset($form->options['reg_display_name']) and is_numeric($form->options['reg_display_name']))
            $form->options['reg_usermeta']['display_name'] = $form->options['reg_display_name'];
        
        $user_meta = array_search($field->id, $form->options['reg_usermeta']);
        if($user_meta){
            $frm_entry = new FrmEntry();
            $entry = $frm_entry->getOne($entry_id);
            unset($frm_entry);
            
            if(!$entry or !$entry->user_id)
                return $values;
                
            $user_data = get_userdata($entry->user_id);
            if(!isset($_POST['form_id']) and !isset($_POST['item_meta']) and !isset($_POST['item_meta'][$field->id])){
                $new_value = isset($user_data->{$user_meta}) ? $user_data->{$user_meta} : FrmRegAppHelper::get_user_meta($user_ID, $user_meta);
            
                if($new_value)
                    $values['value'] = $new_value;
                unset($new_value);
            }
        }
        
        return $values;
    }
    
    public static function show_usermeta(){
        global $profileuser, $wpdb;

        $meta_keys = array();
        $form_options = $wpdb->get_col("SELECT options FROM {$wpdb->prefix}frm_forms WHERE is_template=0 AND status='published'");
        foreach($form_options as $opts){
            $opts = maybe_unserialize($opts);
            if(!isset($opts['reg_usermeta']) or empty($opts['reg_usermeta']))
                continue;
            
            foreach($opts['reg_usermeta'] as $meta_key => $field_id){
                if($meta_key != 'user_url')
                    $meta_keys[$meta_key] = $field_id;
            }
        }
        
        if(!empty($meta_keys)){
            $frm_field = new FrmField();
            include(self::path() .'/views/show_usermeta.php');
        }
    }
    
    private static function update_usermeta($form_options, $user_ID){
        if(isset($form_options['reg_usermeta']) and !empty($form_options['reg_usermeta'])){
            foreach($form_options['reg_usermeta'] as $meta_key => $field_id){
                $meta_val = (isset($_POST['item_meta'][$field_id])) ? $_POST['item_meta'][$field_id] : '';
                if($meta_key == 'user_url'){
                    wp_update_user(array('ID' => $user_ID, 'user_url' => $meta_val));
                }else{
                    if(function_exists('update_user_meta'))
                        update_user_meta($user_ID, $meta_key, $meta_val);
                    else
                        update_usermeta($user_ID, $meta_key, $meta_val);
                }
            }
        }
    }
    
    private static function _get_usermeta($form_options, $user_meta = array()){
        if(isset($form_options['reg_email']) and isset($_POST['item_meta'][$form_options['reg_email']]))
            $user_meta['user_email'] = sanitize_text_field( $_POST['item_meta'][$form_options['reg_email']] );

        if(is_numeric($form_options['reg_password']) and !empty($_POST['item_meta'][$form_options['reg_password']]))
            $user_meta['user_pass'] = $_POST['item_meta'][$form_options['reg_password']];
           
        foreach(array('first_name', 'last_name') as $user_field){
            if(is_numeric($form_options['reg_'. $user_field]) and !empty($_POST['item_meta'][$form_options['reg_'. $user_field]]))
                $user_meta[$user_field] = $_POST['item_meta'][$form_options['reg_'. $user_field]];
        }
        
        // Other cols in wp_users: 'user_url', 'display_name', 'description'
        
        return $user_meta;
    }
    
    private static function _generate_display_name($opts){
        if(isset($opts['reg_display_name']) and !empty($opts['reg_display_name'])){
            if(is_numeric($opts['reg_display_name']))
                $display_name = $_POST['item_meta'][$opts['reg_display_name']];
            else if($opts['reg_display_name'] == 'display_firstlast' and is_numeric($opts['reg_first_name']) and is_numeric($opts['reg_last_name']))
                $display_name = $_POST['item_meta'][$opts['reg_first_name']] . ' '. $_POST['item_meta'][$opts['reg_last_name']];
            else if($opts['reg_display_name'] == 'display_lastfirst' and is_numeric($opts['reg_first_name']) and is_numeric($opts['reg_last_name']))
                $display_name = $_POST['item_meta'][$opts['reg_last_name']] . ' '. $_POST['item_meta'][$opts['reg_first_name']];
        }
        
        return $display_name;
    }
    
    public static function login_form($atts){
        if(is_user_logged_in()) //don't show the login form if user is already logged in
            return '<a href="'. wp_logout_url( get_permalink() ) .'" class="frm_logout_link" >Logout</a>';
            
        $defaults = array(
            'form_id' => 'loginform', 'label_username' => __( 'Username' ),
            'label_password' => __( 'Password' ), 'label_remember' => __( 'Remember Me' ),
            'label_log_in' => __( 'Login' ), 'id_username' => 'user_login',
            'id_password' => 'user_pass', 'id_remember' => 'rememberme',
            'id_submit' => 'wp-submit', 'remember' => true,
            'value_username' => NULL, 'value_remember' => false,
            'slide' => false, 'style' => true, 'layout' => 'v',
            'redirect' => site_url( $_SERVER['REQUEST_URI'] )
        );
        
        if(isset($atts['slide']) and $atts['slide']){
            $defaults['form_id'] = 'frm-loginform';
            $defaults['label_username'] = $defaults['label_password'] = '';
            $defaults['remember'] = false;
            $defaults['layout'] = 'h';
        }
        
        $atts = shortcode_atts($defaults, $atts);
        
        global $frm_vars;
        if(!is_array($frm_vars))
            $frm_vars = array();
        
        if(!isset($frm_vars['reg_login_ids']) or !is_array($frm_vars['reg_login_ids']))
            $frm_vars['reg_login_ids'] = array();
        
        if(in_array($atts['form_id'], $frm_vars['reg_login_ids']))
            $atts['form_id'] .= '1';
        $frm_vars['reg_login_ids'][] = $atts['form_id'];
        
        $content = '';

        if($atts['slide'] and $atts['style']){
            $content .= '<style type="text/css">';
            if($atts['layout'] == 'h'){
                $content .= '#'. $atts['form_id'].' p{float:left;margin:1px 1px 0;padding:0;}.frm-open-login{float:left;margin-right:15px;}#'. $atts['form_id'].' input[type="text"], #'. $atts['form_id'].' input[type="password"]{width:120px;}';
            }else{
                $content .= '#'. $atts['form_id'].' input[type="text"], #'. $atts['form_id'].' input[type="password"]{width:auto;}';
            }
            $content .= '#'. $atts['form_id'].'{display:none;}#'. $atts['form_id'].' input{padding:1px 5px 2px;vertical-align:top;font-size:13px;} .frm-open-login a{text-decoration:none;font-size:12px;}
</style>'."\n";
        }else if($atts['style']){
            $content .= '<style type="text/css">#'. $atts['form_id'].' input[type="text"], #'. $atts['form_id'].' input[type="password"]';
            if($atts['layout'] == 'h'){
                $content .= '{width:120px;}#'. $atts['form_id'].' p{float:left;margin:1px 1px 0;padding:0;}';
            }else{
                $content .= '{width:auto;}';
            }
            $content .= '</style>'."\n";
        }
        
        if($atts['slide'])
            $content .= '<span class="frm-open-login"><a href="#">'. $atts['label_log_in'] .' &rarr;</a></span>';
            
        $atts['echo'] = false;
        if($atts['style'])
            $content .= '<div class="with_frm_style"><div class="frm_form_fields submit">'."\n";
        
        $content .= wp_login_form( $atts );
        
        if($atts['style'])
            $content .= '</div></div>';
        
        if($atts['slide']){
            $content .= '<div style="clear:both"></div>'."\n";
            $content .= '<script type="text/javascript">
            jQuery(document).ready(function($){ $(".frm-open-login a").click(function(){$("#'. $atts['form_id'] .'").toggle(600);return false;});});
            </script>'."\n";
        }
        return $content;
    }

}

?>