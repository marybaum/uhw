<?php

class FrmMlcmpSettings{
    var $api_key;
    var $forms;

    function FrmMlcmpSettings(){
        $this->set_default_options();
    }
    
    function default_options(){
        return array(
            'api_key'       => '',
            'email_type'    => 'html' //html, text, or mobile
        );
    }
    
    function set_default_options(){
        $settings = $this->default_options();
        
        foreach($settings as $setting => $default){
            if(!isset($this->{$setting}))
                $this->{$setting} = $default;
        }
    }
    
    function update($params){
        $settings = $this->default_options();
        
        foreach($settings as $setting => $default){
            if(isset($params['frm_'. $setting]))
                $this->{$setting} = $params['frm_'. $setting];
        }
    }

    function store(){
        // Save the posted value in the database
        update_option( 'frm_mlcmp_options', $this);
    }
    

}