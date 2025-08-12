<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_roster_code'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("attendances/add_roster_code"); ?>
        
		<div class="modal-body">
            <div class="form-group">
				<?php echo lang('code', 'code'); ?> *
				<div class="controls">
					<input type="text" value="<?= $policy->code ?>" class="form-control" name="code" required="required"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('from_time', 'from_time'); ?> *
						<div class="controls">
							<input type="text" value="<?= $policy->from_time ?>" class="form-control timepicker" name="from_time" required="required"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('to_time', 'to_time'); ?> *
						<div class="controls">
							<input type="text" value="<?= $policy->to_time ?>" class="form-control timepicker" name="to_time" required="required"/>
						</div>
					</div>
                </div>
            </div>
			<div class="form-group">
				<?php echo lang('hour', 'hour'); ?> *
				<div class="controls">
					<input type="text" value="<?= $policy->hour ?>" class="form-control" name="hour" required="required"/>
				</div>
			</div>
			<div class="form-group">
                <?php echo lang('note', 'note'); ?>
                <div class="controls">
                    <textarea name="note" class="form-control"></textarea>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
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
	});
</script>