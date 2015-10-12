<?php
if(!isset($list_options['optin']))
    $list_options['optin'] = 0;
?>

<?php foreach($list_fields as $list_field){ ?>
<p><label class="frm_left_label"><?php echo $list_field['name']; ?> 
    <?php if($list_field['req']) { ?><span class="frm_required">*</span><?php } ?>
    </label>
    
    <select name="options[mlcmp_list][<?php echo $list_id ?>][fields][<?php echo $list_field['tag'] ?>]">
        <option value="">- <?php _e('Select Field', 'formidable') ?> -</option>
        <?php foreach($form_fields as $form_field){ 
                if($list_field['field_type'] == 'email' and !in_array($form_field->type, array('email', 'hidden', 'user_id')))
                    continue;
                $selected = (isset($list_options['fields'][$list_field['tag'] ]) and $list_options['fields'][$list_field['tag']] == $form_field->id) ? ' selected="selected"' : '';
            ?>
        <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo stripslashes($form_field->name) ?></option>
        <?php } ?>
    </select>
</p>
<?php } ?>
<?php 
if($groups){
foreach($groups as $group){ 
    if(!isset($group['id']))
        continue;
?>
<div><label class="frm_left_label"><?php echo esc_html($group['name']); ?></label>
    <select name="options[mlcmp_list][<?php echo $list_id ?>][groups][<?php echo $group['id'] ?>][id]" onchange="frmMlcmpGetFieldGrpValues(this.value,'<?php echo $list_id ?>','<?php echo $group['id'] ?>')">
            <option value="">- <?php _e('Select Field', 'formidable') ?> -</option>
            <?php foreach($form_fields as $form_field){ 
                if(!in_array($form_field->type, array('hidden', 'select', 'radio', 'checkbox', 'data')))
                    continue;
                if((isset($list_options['groups'][$group['id']]) and $list_options['groups'][$group['id']]['id'] == $form_field->id)){
                    global $frm_field;
                    $selected = ' selected="selected"';
                    $new_field = $form_field;
                }else{
                    $selected = '';
                }
            ?>
            <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo stripslashes($form_field->name) ?></option>
            <?php } ?>
    </select>
        <div class="frm_indent_opt">
        <?php foreach($group['groups'] as $g){ ?>
            <div><label class="frm_left_label clear" style="width:145px"><?php echo esc_html($g['name']) ?></label>
            <p class="frm_show_selected_values_<?php echo $list_id .'_'. $group['id']; ?>" class="no_taglist">
                <?php if (isset($new_field)){
                    $new_field->field_options = maybe_unserialize($new_field->field_options);

                    include('_field_values.php');
                }else{ ?>
<select style="visibility:hidden;"><option value=""> </option></select>
<?php    
                } ?>
            </p>
            </div>
        <?php 
                unset($g);
            } 
        if(isset($new_field))
            unset($new_field);
        ?>
        <div class="clear"></div>
        </div>
</div>
<?php }
} ?>

<p><label class="frm_left_label"><?php _e('Opt In', 'formidable') ?></label>
    <select name="options[mlcmp_list][<?php echo $list_id ?>][optin]" id="mlcmp_optin_<?php echo $list_id ?>">
        <option value="0"><?php _e('Single', 'formidable') ?></option>
        <option value="1" <?php selected($list_options['optin'], 1); ?>><?php _e('Double', 'formidable') ?></option>
    </select> 
</p>

<div><label class="frm_logic_label" <?php if(!isset($list_options['hide_field']) or empty($list_options['hide_field'])){ echo 'style="display:none;"'; } ?>><?php _e('Conditional Logic', 'formidable') ?></label>
    <div class="frm_logic_rows tagchecklist">
        <div id="frm_logic_row_<?php echo $list_id ?>">
<?php 
if(isset($list_options['hide_field']) and !empty($list_options['hide_field'])){
    foreach((array)$list_options['hide_field'] as $meta_name => $hide_field){
        include(FRM_MLCMP_PATH .'/views/settings/_logic_row.php');
    }
}
?>
        </div>
    </div>
    <p><a class="button" href="javascript:frmMlcmpAddLogicRow('<?php echo $list_id ?>');">+ <?php _e('Add Conditional Logic', 'formidable') ?></a></p>
</div>