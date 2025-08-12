<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_bank_account'); ?></h4>
		
        </div>
		<?php
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		 	echo admin_form_open_multipart("hr/add_bank_account/".$employee_id, $attrib); 
		?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="row">

				<div class="col-lg-12">	

					<div class="form-group">
						<?php echo lang('account', 'account'); ?>
						<div class="controls">
							<input type="text" id="account" required name="account" class="form-control"/>
						</div>
					</div>

					<div class="form-group">
						<?php echo lang('account_no', 'account_no'); ?>
						<div class="controls">
							<input type="text" id="account_no" required name="account_no" class="form-control"/>
						</div>
					</div>

					<div class="form-group">
						<?php echo lang('account_name', 'account_name'); ?>
						<div class="controls">
							<input type="text" id="account_name" name="account_name" class="form-control"/>
						</div>
					</div>	

					<div class="form-group">
						<?php echo lang('account_type', 'account_type'); ?>
						<div class="controls">
							<input type="text" id="account_type" name="account_type" class="form-control"/>
						</div>
					</div>

					<div class="form-group">
						<?php echo lang('date_open', 'date_open'); ?>
						<div class="controls">
							<input type="text" id="date_open" name="date_open" class="form-control date"/>
						</div>
					</div>

					<div class="form-group">
						<?php echo lang('date_issue', 'date_issue'); ?>
						<div class="controls">
							<input type="text" id="date_issue" name="date_issue" class="form-control date"/>
						</div>
					</div>

					<div class="form-group">
						<?= lang("currency", "currency"); ?>
						<?php
						$cur[""] = "";
						foreach ($currencies as $currency) {
							$cur[$currency->code] = $currency->name;
						}
						echo form_dropdown('currency', $cur, (isset($_POST['currency']) ? $_POST['currency'] : ''), 'id="currency" data-placeholder="' . lang("select") . ' ' . lang("currency") . '" required="required" class="form-control input-tip select" style="width:100%;"');
						?>
					</div>
					
					<div class="form-group">
						<?php echo lang('description', 'description'); ?>
						<div class="controls">
							<textarea id="b_description" name="b_description" class="form-control"></textarea>
						</div>
					</div>
					
				</div>
			</div>
        </div>
		
        <div class="modal-footer">
            <?php echo form_submit('add_bank_account', lang('add_bank_account'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>