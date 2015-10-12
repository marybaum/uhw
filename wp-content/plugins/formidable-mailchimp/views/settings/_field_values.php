<?php
if(!$new_field)
    return;
    
if ($new_field->type == 'data'){

    if (isset($new_field->field_options['form_select']) && is_numeric($new_field->field_options['form_select']))
        $new_entries = $frm_entry_meta->getAll("it.field_id=".$new_field->field_options['form_select']);
        
    $new_field->options = array();
    if (isset($new_entries) && !empty($new_entries)){
        foreach ($new_entries as $ent)
            $new_field->options[$ent->item_id] = $ent->meta_value;
    }
}else if(isset($new_field->field_options['post_field']) and $new_field->field_options['post_field'] == 'post_status'){
    $new_field->options = FrmProFieldsHelper::get_status_options($new_field);
}else{
    $new_field->options = stripslashes_deep(maybe_unserialize($new_field->options));
}

$field_id = (isset($g) and isset($group)) ? "options[mlcmp_list][{$list_id}][groups][{$group['id']}][{$g['name']}]" : "options[mlcmp_list][{$list_id}][hide_opt]";
$field_name = (isset($g) and isset($group)) ? $field_id : $field_id .'[]';

if(isset($new_field->field_options['post_field']) and $new_field->field_options['post_field'] == 'post_category'){
    $new_field = (array)$new_field;
    if(isset($list_options['groups'][$group['id']])){
        $new_field['value'] = (isset($field) and isset($list_options['groups'][$group['id']][$g['name']])) ? $list_options['groups'][$group['id']][$g['name']] : '';
    }else{
        $new_field['value'] = (isset($field) and isset($list_options['hide_opt'][$meta_name])) ? $list_options['hide_opt'][$meta_name] : '';
    }
    $new_field['exclude_cat'] = (isset($new_field->field_options['exclude_cat'])) ? $new_field->field_options['exclude_cat'] : '';
    echo FrmFieldsHelper::dropdown_categories(array('name' => $field_name, 'id' => $field_id, 'field' => $new_field) );
}else{ 
?>
<select name="<?php echo esc_attr($field_name) ?>">
    <option value="">- <?php _e('Select', 'formidable'); ?> -</option>
    <?php 
    if($new_field->options){ 
        $temp_field = (array)$new_field;
        foreach($new_field->field_options as $k => $o){
            $temp_field[$k] = $o;
            unset($k);
            unset($o);
        }

        foreach ($new_field->options as $opt_key => $opt){
        $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $temp_field); //use VALUE instead of LABEL
        $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $temp_field);
        
    if(isset($group) and isset($g) and isset($list_options['groups'][$group['id']])){
        $selected = (isset($list_options) && isset($list_options['groups'][$group['id']][$g['name']]) && (($new_field->type == 'data' && $list_options['groups'][$group['id']][$g['name']] == $opt_key) || $list_options['groups'][$group['id']][$g['name']] == $field_val)) ? ' selected="selected"' : '';
    }else{
        $selected = (isset($list_options) && isset($list_options['hide_opt'][$meta_name]) && (($new_field->type == 'data' && $list_options['hide_opt'][$meta_name] == $opt_key) || $list_options['hide_opt'][$meta_name] == $field_val)) ? ' selected="selected"' : '';
    } ?>
    <option value="<?php echo ($new_field->type == 'data') ? $opt_key : stripslashes(esc_html($field_val)); ?>"<?php echo $selected; ?>><?php echo FrmAppHelper::truncate($opt, 25); ?></option>
    <?php
        unset($opt_key);
        unset($opt);
        } 
    } ?>
</select>
<?php 
} ?>