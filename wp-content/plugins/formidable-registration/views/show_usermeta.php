
<table class="form-table">
<tbody>
<?php

foreach($meta_keys as $meta_key => $field_id){ 
    if(empty($profileuser->{$meta_key}))
        continue;
?>
<tr>
	<th><label><?php echo ucwords($meta_key) ?></label></th>
	<td><?php 
	    $meta_val = $profileuser->{$meta_key}; //maybe_unserialize($profileuser->{$meta_key});
	    $field = $frm_field->getOne($field_id);
	    echo FrmProEntryMetaHelper::display_value($meta_val, $field, array('type' => $field->type, 'truncate' => false)); 
	    
	    unset($field);
	    unset($meta_key);
	    unset($field_id); 
	    ?>
	</td>
</tr>
<?php
}

?>

</tbody>
</table>
