<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_nozzle_start_no'). " (" . $tank->name . ")"; ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("system_settings/add_nozzle_start_no/".$id, $attrib); ?>
		<div class="modal-body">
			<p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<?= lang("product", "product"); ?>
						<?php
						$pr[''] = lang('select').' '.lang('product');
						foreach ($products as $product) {
							$pr[$product->id] = $product->name;
						}
						echo form_dropdown('product', $pr, '', 'class="form-control tip select" id="product" style="width:100%;" required="required"');
						?>
					</div>
					<div class="form-group">
						<?= lang("nozzle_no", "nozzle_no"); ?>
						<?php echo form_input('nozzle_no', '', 'class="form-control" id="nozzle_no" required="required"'); ?>
					</div>
					<div class="form-group">
						<?= lang("nozzle_start_no", "nozzle_start_no"); ?>
						<?php echo form_input('nozzle_start_no', '', 'class="form-control" id="nozzle_start_no" required="required" '); ?>
					</div>
					<div class="form-group">
						<label class="control-label" for="saleman"><?= lang("saleman"); ?></label>
						<?php
						if($salemans){
							foreach($salemans as $saleman){
								$opt_salemans[$saleman->id] = $saleman->first_name;
							}
						}
						echo form_dropdown('saleman[]', $opt_salemans, (isset($_POST['saleman']) ? $_POST['saleman'] : ''), 'class="form-control saleman" id="saleman" multiple style="height:100px" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"');
						?>
					</div>
				</div>	
			</div>
		</div>
		<div class="modal-footer">
			<?php echo form_submit('add_nozzle_start_no', lang('add_nozzle_start_no'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>