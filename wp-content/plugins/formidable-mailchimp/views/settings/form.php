<?php if(version_compare($frm_version, '1.07.01', '<=')){ ?>
<div class="mailchimp_settings tabs-panel" style="display:none;">
<?php } ?>
    <table class="form-table">
        <tr class="form-field" valign="top">
            <td width="200px"><label><?php _e('API Key', 'formidable') ?></label></td>
        	<td>
                <input type="text" name="frm_api_key" id="frm_api_key" value="<?php echo $frm_mlcmp_settings->api_key ?>" class="frm_long_input" />
        	</td>
        </tr>
        
    </table>
<?php if(version_compare($frm_version, '1.07.01', '<=')){ ?>    
</div>
<?php } ?>