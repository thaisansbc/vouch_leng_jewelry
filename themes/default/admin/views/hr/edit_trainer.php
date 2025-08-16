<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_trainer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("hr/edit_trainer/".$officer->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name',$officer->full_name, 'class="form-control" id="name" required="required"'); ?>
            </div>
	
			<div class="form-group">
                <label class="control-label" for="name_kh"><?php echo $this->lang->line("name_kh"); ?></label>
                <?php echo form_input('name_kh',$officer->full_name_kh, 'class="form-control" id="name_kh"'); ?>
            </div>
			
            
			<div class="form-group">
                <label class="control-label" for="phone"><?php echo $this->lang->line("phone"); ?></label>
                <?php echo form_input('phone', $officer->phone, 'class="form-control" id="phone"'); ?>
            </div>
	
            <div class="form-group">
                <?= lang('gender', 'gender'); ?>
                <?php
                $cgs = array('Male' => lang('Male') , 'Female'=> lang('Female'));
                echo form_dropdown('gender', $cgs, $officer->gender, 'class="form-control select" id="cf4" style="width:100%;"');
                ?>
            </div>


			<div class="form-group">
				<?= lang("attachment", "attachment") ?>
				<input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachment" data-show-upload="false"
					   data-show-preview="false" class="form-control file">
			</div>
			<div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("address"); ?></label>
                <?php echo form_textarea('address', $officer->address, 'class="form-control" id="address"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="note"><?php echo $this->lang->line("note"); ?></label>
                <?php echo form_textarea('note', $officer->note, 'class="form-control" id="note"'); ?>
            </div>
			
			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_officer', lang('edit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$('.timepicker').datetimepicker({
			format: 'hh:ii:ss',
			fontAwesome: true,
			autoclose: 1,
			startView: 0,
			todayBtn: 1
		});	
		$("#status").live("change",function(){
			if($(this).val() == 'inactive'){
				$(".box_end_date").slideDown();
			}else{
				$(".box_end_date").slideUp();
			}
		});
	});
</script>
