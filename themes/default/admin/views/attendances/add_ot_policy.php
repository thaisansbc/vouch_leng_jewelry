<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_ot_policy'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("attendances/add_ot_policy"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
				<?php echo lang('ot_policy', 'ot_policy'); ?> *
				<div class="controls">
					<input type="text" class="form-control" name="ot_policy" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('time_in', 'time_in'); ?> *
				<div class="controls">
					<input type="text" class="form-control timepicker" name="time_in" required="required"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('start_in', 'start_in'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="start_in" required="required"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('end_in', 'end_in'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="end_in" required="required"/>
						</div>
					</div>
                </div>
            </div>
			
			<div class="clearfix"><hr></div>

			<div class="form-group">
				<?php echo lang('time_out', 'time_out'); ?> *
				<div class="controls">
					<input type="text" class="form-control timepicker" name="time_out" required="required"/>
				</div>
			</div>
			
			<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('start_out', 'start_out'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="start_out" required="required"/>
						</div>
					</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php echo lang('end_out', 'end_out'); ?> *
						<div class="controls">
							<input type="text" class="form-control timepicker" name="end_out" required="required"/>
						</div>
					</div>
                </div>
            </div>
			
			<div class="form-group">
				<?php echo lang('type', 'type'); ?> *
				<div class="controls">
					<?php
						$ot_type["normal"] = lang('normal');
						$ot_type["holiday"] = lang('holiday');
						$ot_type["weekend"] = lang('weekend');
						echo form_dropdown('type', $ot_type, '', 'class="form-control" id="type" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("type") . '"');
					?>
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
		
		var old_rate_percent;
		$(document).on("focus", '.rate_percent', function () {
			old_rate_percent = $(this).val();
		}).on("change", '.rate_percent', function () {
			var new_rate_percent = $(this).val();
			if (!is_numeric(new_rate_percent) || new_rate_percent < 0 ) {
				$(this).val(old_rate_percent);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});
		
		var old_minimum_min;
		$(document).on("focus", '.minimum_min', function () {
			old_minimum_min = $(this).val();
		}).on("change", '.minimum_min', function () {
			var new_minimum_min = $(this).val();
			if (!is_numeric(new_minimum_min) || new_minimum_min < 0 ) {
				$(this).val(old_minimum_min);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});
	});
</script>