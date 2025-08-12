<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_device'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("attendances/add_device", $attrib); ?>
        <div class="modal-body" >
            <p><?= lang('enter_info'); ?></p>
				
            <div class="form-group">
                <?= lang('device_name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('ip_address', 'ip_address'); ?>
                <?= form_input('ip_address','' , 'class="form-control" onkeypress="validate(event)" id="ip_address" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('port', 'port'); ?>
                <?= form_input('port', '', 'class="form-control" id="port" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('description', 'description'); ?>
                <?= form_input('description', '', 'class="form-control" id="description" '); ?>
            </div>
			
			<div class="form-group">
				<?php 
					echo lang('auto_clear_att', 'clear');
					$cl_op[0] = lang('no');
					$cl_op[1] = lang('yes');
					echo form_dropdown('clear', $cl_op,'', 'class="form-control" id="clear" '); 
				?>
			</div>
			<div class="form-group clear_att">
				<?= lang('maximum_att_log_clear', 'maximum_att_log'); ?>
                <?= form_input('maximum_att_log', '', 'class="form-control" id="maximum_att_log"'); ?>
			</div>
		
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_device', lang('add_device'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script>
	$(document).ready(function () {
		$('.clear_att').hide();
		$('#clear').live('change',function(){
			var clear = $(this).val();
			if(clear==1){
				$('.clear_att').slideDown();
			}else{
				$('.clear_att').slideUp();
			}
		});
		
		$(document).on("focus", '#maximum_att_log', function () {
			old_maximum = $(this).val();
		}).on("change", '#maximum_att_log', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val())) {
				$(this).val(old_maximum);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});
	});
</script>