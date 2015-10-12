<div id="frm_logic_<?php echo $list_id .'_'. $meta_name ?>" class="frm_mlcmp_logic_row">
<span><a href="javascript:void(0)" class="frm_mlcmp_remove_tag"> X </a></span>
&nbsp;
<select name="options[mlcmp_list][<?php echo $list_id ?>][hide_field][]" onchange="frmMlcmpGetFieldValues(this.value,'<?php echo $list_id ?>',<?php echo $meta_name ?>)">
    <option value="">- <?php _e('Select Field', 'formidable') ?> -</option>
    <?php foreach ($form_fields as $ff){ 
        if(!in_array($ff->type, array('select','radio','checkbox','10radio','scale')))
            continue;
        $selected = ($ff->id == $hide_field) ?' selected="selected"':''; ?>
    <option value="<?php echo $ff->id ?>"<?php echo $selected ?>><?php echo FrmAppHelper::truncate($ff->name, 30); ?></option>
    <?php } ?>
</select>
<?php _e('is', 'formidable'); 

if(!isset($list_options['hide_field_cond']))
    $list_options['hide_field_cond'] = array($meta_name => '==');

if(!isset($list_options['hide_field_cond'][$meta_name]))
    $list_options['hide_field_cond'][$meta_name] = '==';   
?>

<select name="options[mlcmp_list][<?php echo $list_id ?>][hide_field_cond][]">
    <option value="==" <?php selected($list_options['hide_field_cond'][$meta_name], '==') ?>><?php _e('equal to', 'formidable') ?></option>
    <option value="!=" <?php selected($list_options['hide_field_cond'][$meta_name], '!=') ?>><?php _e('NOT equal to', 'formidable') ?> &nbsp;</option>
    <option value=">" <?php selected($list_options['hide_field_cond'][$meta_name], '>') ?>><?php _e('greater than', 'formidable') ?></option>
    <option value="<" <?php selected($list_options['hide_field_cond'][$meta_name], '<') ?>><?php _e('less than', 'formidable') ?></option>
</select>

<span id="frm_show_selected_values_<?php echo $list_id ?>_<?php echo $meta_name ?>" class="no_taglist">
    <?php if ($hide_field and is_numeric($hide_field)){
        global $frm_field, $frm_entry_meta;
        $current_field_id = $list_id;
        $new_field = $frm_field->getOne($hide_field);
        if($new_field)
            $new_field->field_options = maybe_unserialize($new_field->field_options);

        include('_field_values.php');
    } ?>
</span>
</div>