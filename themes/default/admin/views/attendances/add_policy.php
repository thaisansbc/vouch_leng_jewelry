<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_policy'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("attendances/add_policy"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
				<?php echo lang('policy', 'policy'); ?> *
				<div class="controls">
					<input type="text" class="form-control" name="policy" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('time_in_one', 'time_in_one'); ?> *
				<div class="controls">
					<input type="text" class="form-control timepicker" name="time_in_one" required="required"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('start_in_one', 'start_in_one'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="start_in_one" required="required"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('end_in_one', 'end_in_one'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="end_in_one" required="required"/>
						</div>
					</div>
                </div>
            </div>
			
			<div class="clearfix"><hr></div>
			
			<div class="form-group">
				<?php echo lang('time_out_one', 'time_out_one'); ?> *
				<div class="controls">
					<input type="text" class="form-control timepicker" name="time_out_one" required="required"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('start_out_one', 'start_out_one'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="start_out_one" required="required"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('end_out_one', 'end_out_one'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="end_out_one" required="required"/>
						</div>
					</div>
                </div>
            </div>
			<div class="clearfix"><hr></div>
			
			<div class="form-group">
				<?php echo lang('time_in_two', 'time_in_two'); ?>
				<div class="controls">
					<input type="text" class="form-control timepicker" name="time_in_two"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('start_in_two', 'start_in_two'); ?>
						<div class="controls">
							<input type="text" class="form-control timepicker" name="start_in_two"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('end_in_two', 'end_in_two'); ?>
						<div class="controls">
							<input type="text" class="form-control timepicker" name="end_in_two"/>
						</div>
					</div>
                </div>
            </div>
			<div class="clearfix"><hr></div>
			
			<div class="form-group">
				<?php echo lang('time_out_two', 'time_out_two'); ?>
				<div class="controls">
					<input type="text" class="form-control timepicker" name="time_out_two"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('start_out_two', 'start_out_two'); ?>
						<div class="controls">
							<input type="text" class="form-control timepicker" name="start_out_two"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('end_out_two', 'end_out_two'); ?>
						<div class="controls">
							<input type="text" class="form-control timepicker" name="end_out_two"/>
						</div>
					</div>
                </div>
            </div>
			
			<div class="form-group">
				<?php echo lang('minimum_min', 'minimum_min'); ?> *
				<div class="controls">
					<input type="text" class="form-control minimum_min" name="minimum_min" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('round_min', 'round_min'); ?> *
				<div class="controls">
					<input type="text" class="form-control round_min" name="round_min" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('monthly_working_day', 'monthly_working_day'); ?> *
				<div class="controls">
					<input type="text" value="" class="form-control" name="monthly_working_day" required="required"/>
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
			format: 'hh:ii',
			fontAwesome: true,
			autoclose: 1,
			startView: 0,
			todayBtn: 1
		});	
	});
</script>