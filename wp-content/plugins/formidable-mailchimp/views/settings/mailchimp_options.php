<?php if(version_compare($frm_version, '1.6', '<')){ ?>
<!-- 1.5.5 and below -->
<div class="theme-group clearfix">
	<div class="theme-group-header state-default">
		<span class="icon icon-triangle-1-e"><?php _e('Collapse', 'formidable') ?></span>
		<a href="#"><?php _e('MailChimp', 'formidable') ?></a>
	</div><!-- /theme group Error -->
	<div class="theme-group-content corner-bottom clearfix">
        <div class="clearfix">
<!-- end 1.5.5 --> 
<?php } 

if(version_compare($frm_version, '1.07.01', '<=')){ ?>           
<div id="mailchimp_settings" class="mailchimp_settings tabs-panel" style="display:none;">
<?php } ?>
<table class="form-table">
    <tbody>
    <tr>
    <td><label for="mailchimp"><input type="checkbox" name="options[mailchimp]" id="mailchimp" value="1" <?php checked($values['mailchimp'], 1); ?> /> <?php _e('Add users who submit this form to a Mailchimp mailing list', 'formidable') ?></label></td>
    </tr>
<?php
        if(!empty($values['mlcmp_list'])){
            $hide_mailchimp = ($values['mailchimp']) ? '' : 'style="display:none;"';
            foreach((array)$values['mlcmp_list'] as $list_id => $list_options){
                if(!is_array($list_options))
                    continue;

                $list_fields = $api->listMergeVars($list_id);
                $groups = $api->listInterestGroupings($list_id);
                include(FRM_MLCMP_PATH .'/views/settings/_list_options.php');
                unset($list_fields);
            }
        }
        
        ?>
    </tbody>
</table>
<p id="mlcmp_add_button" class="hide_mailchimp" style="margin-left:10px;<?php echo $values['mailchimp'] ? '' : 'display:none;'; ?>">
    <a href="javascript:void(0)" class="button-secondary frm_mlcmp_add_list">+ <?php _e('Add List', 'formidable') ?></a></p>
</p>

<?php if(version_compare($frm_version, '1.07.01', '<=')){ ?>
</div>
<?php 
wp_localize_script('formidable', 'frm_js', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'images_url' => FRM_URL .'/images',
    'loading' => __('Loading&hellip;')
));
}

if(version_compare($frm_version, '1.6', '<')){ ?>
<!-- 1.5.5 and below -->
        </div>
    </div>
</div>
<!-- end 1.5.5 -->
<?php } ?>
<style type="text/css">
.themeRoller .mailchimp_settings{color:#333;display:block !important;}
.frm_left_label{clear:both;float:left;width:170px;}
.mlchp_list > td{border-top:1px solid #DFDFDF;}
table .mlchp_list:nth-child(2) > td{border:none;}
</style>

<script type="text/javascript">
jQuery(document).ready(function($){
frm_form_id=<?php echo $values['id'] ?>;
$('#mailchimp_settings').on('change', 'select[name="mlcmp_list[]"]', frmMlcmpFields);
$('#mailchimp_settings').on('click', '.frm_mlcmp_remove', frmMlcmpRemoveList);
$('.frm_mlcmp_add_list').click(frmMlcmpAddList);
$('input#mailchimp').click(function(){
    frm_show_div('hide_mailchimp',this.checked,1,'.');
    if(this.checked) frmMlcmpAddList();
    else $('.frm_mlcmp_remove').click();
});
$('#mailchimp_settings').on('click', '.frm_mlcmp_remove_tag', function(){
    if(jQuery('.frm_mlcmp_logic_row').length==1){
        var c=',.frm_logic_label';
    }else{
        var c='';
    }
    $('#'+$(this).closest('.frm_mlcmp_logic_row').attr('id')+c).fadeOut(1000, function(){
        $(this).closest('.frm_mlcmp_logic_row').replaceWith('');
    });
});
});

function frmMlcmpFields(){
    var id=jQuery(this).val();
    var htmlid=jQuery(this).attr('id').replace('select_list_', '');
    var div=jQuery(this).closest('.mlchp_list').find('.frm_mlcmp_fields');
    div.empty().append('<img class="frm_mlcmp_loading_field" src="'+ frm_js.images_url +'/wpspin_light.gif" alt="'+ frm_js.loading +'" style="display:none;"/>');
    jQuery('.frm_mlcmp_loading_field').fadeIn('slow');
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_match_fields&form_id="+frm_form_id+"&list_id="+id,
        success:function(html){jQuery('.frm_mlcmp_loading_field').replaceWith(html).fadeIn('slow');}
    });
}

function frmMlcmpAddList(){
    var len=jQuery('.mlchp_list').length+1;
    jQuery('#mailchimp_settings .form-table tbody').append('<tr class="frm_mlcmp_loading_list"><td><img src="'+ frm_js.images_url +'/wpspin_light.gif" alt="'+ frm_js.loading +'" style="display:none;"/></td></tr>');
    jQuery('.frm_mlcmp_loading_list img').fadeIn('slow');
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_add_list&list_id="+len,
        success:function(html){jQuery('.frm_mlcmp_loading_list').replaceWith(html);jQuery('.mailchimp_settings').fadeIn('slow');}
    });
}

function frmMlcmpRemoveList(){
    var id=jQuery(this).attr('id').replace('remove_list_', '');
    jQuery('.mlchp_list_'+id+',#frm_mlcmp_fields_'+id+',.frm_mlcmp_fields_'+id).fadeOut(1000, function(){
        jQuery('.mlchp_list_'+id+',#frm_mlcmp_fields_'+id+',.frm_mlcmp_fields_'+id).replaceWith('');
    });
}

function frmMlcmpAddLogicRow(id){
if(jQuery('#frm_logic_row_'+id+' .frm_mlcmp_logic_row').length)
	var len=1+parseInt(jQuery('#frm_logic_row_'+id+' .frm_mlcmp_logic_row:last').attr('id').replace('frm_logic_'+id+'_', ''));
else var len=0;
jQuery.ajax({
    type:"POST",url:ajaxurl,
    data:"action=frm_mlcmp_add_logic_row&form_id="+frm_form_id+"&list_id="+id+"&meta_name="+len,
    success:function(html){jQuery('.frm_logic_label').show();jQuery('#frm_logic_row_'+id).append(html);}
});
}

function frmMlcmpGetFieldValues(field_id,list_id,row){ 
    if(field_id){
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_get_field_values&form_id="+frm_form_id+"&list_id="+list_id+"&field_id="+field_id,
        success:function(msg){jQuery('#frm_show_selected_values_'+list_id+'_'+row).html(msg);} 
    });
    }
}

function frmMlcmpGetFieldGrpValues(field_id,list_id,grp){ 
    if(field_id){
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_mlcmp_get_field_values&form_id="+frm_form_id+"&list_id="+list_id+"&field_id="+field_id,
        success:function(msg){jQuery('.frm_show_selected_values_'+list_id+'_'+grp).html(msg);} 
    });
    }
}
</script>