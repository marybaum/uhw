<tr class="hide_mailchimp mlchp_list mlchp_list_<?php echo $list_id ?>" <?php echo $hide_mailchimp; ?>>
    <td>
    <a class="frm_mlcmp_remove alignright frm_email_actions feature-filter" id="remove_list_<?php echo $list_id ?>" href="javascript:void(0)"><img src="<?php echo FRM_URL ?>/images/trash.png" alt="<?php _e('Remove', 'formidable') ?>" title="<?php _e('Remove', 'formidable') ?>" /></a>
    <p>
        <?php if($lists){ ?>
        <select name="mlcmp_list[]" id="select_list_<?php echo $list_id ?>">
            <option value="">- <?php _e('Select List to Subscribe', 'formidable') ?> -</option>
            <?php foreach($lists['data'] as $list){ ?>
            <option value="<?php echo $list['id'] ?>" <?php selected($list_id, $list['id']) ?>><?php echo $list['name'] ?></option>
            <?php } ?>
        </select>
        <?php }else{
            _e('No MailChimp mailing lists found', 'formidable');
        } ?>
    </p>
<div class="frm_mlcmp_fields frm_indent_opt">
<?php if(isset($list_fields) and $list_fields){
    include(FRM_MLCMP_PATH .'/views/settings/_match_fields.php');
} ?>
</div>
</td>
</tr>
