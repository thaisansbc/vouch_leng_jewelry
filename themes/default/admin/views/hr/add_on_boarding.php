<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_on_boarding'); ?></h4>
        </div>
		<?php 
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo admin_form_open_multipart("hr/add_on_boarding/".$employee_id, $attrib); 
		?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-lg-12">	
					<div class="form-group">
						<?php echo lang('joining_date', 'joining_date'); ?>
						<div class="controls">
							<input type="text" id="joining_date" name="joining_date" class="form-control date" required />
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('probation_periods', 'probation_periods'); ?>(<?php echo lang('month'); ?>)
						<div class="controls">
							<input type="text" name="probation_periods" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('probation_end_date', 'probation_end_date'); ?>
						<div class="controls">
							<input type="text" name="probation_end_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
            			<?php echo lang('received_company_asset', 'assets'); ?>
                        <div class="row">
                            <div class="col-xs-6 col-sm-2">
                                <input type="radio" class="checkbox type" value="1" name="asset" <?= $this->input->post('asset')==1 ? 'checked="checked"' : ''; ?> required="required">
                                <label for="full" class="padding05">
                                    <?= lang('yes'); ?>
                                </label>
                            </div>
                            <div class="col-xs-6 col-sm-2">
                                <input type="radio" class="checkbox type" value="0" name="asset" <?= $this->input->post('asset')==0 ? 'checked="checked"' : ''; ?>>
                                <label for="partial" class="padding05">
                                    <?= lang('no'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
						<?php echo lang('attachment', 'attachment'); ?>
						<div class="controls">
							<input type="file" id="attachment" name="attachment" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('description', 'description'); ?>
						<div class="controls">
							<textarea id="description" name="description" class="form-control"></textarea>
						</div>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_working_history', lang('add_working_history'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>