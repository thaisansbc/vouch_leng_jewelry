<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('Add consumption'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("consumption/add", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
				<div class="row">
					<div class="col-sm-6">
						<?= lang('Color', 'Color'); ?>
						<?= form_input('stylecolor', set_value('stylecolor'), 'class="form-control tip" id="Color" required="required"'); ?>
					</div>
					<div class="col-sm-6">
						<?= lang('Size', 'size'); ?>
						<select name="size" class="form-control">
							<option value="S">S</option>
							<option value="M">M</option>
							<option value="L">L</option>
							<option value="X">X</option>
							<option value="XL">XL</option>
						</select>
						<?php// = form_input('size', set_value('size'), 'class="form-control tip" id="size" required="required"'); ?>
					</div>
				</div>
            </div>
			<div class="form-group">
				<div class="row">
					<div class="col-sm-6">
						<?= lang('Consumption', 'Consumption'); ?>
						<?= form_input('consumption', set_value('consumption'), 'class="form-control tip" id="Consumption" required="required"'); ?>
					</div>
					<div class="col-sm-6">
						<?= lang('Makeup', 'Makeup'); ?>
						<?= form_input('makeup', set_value('makeup'), 'class="form-control tip" '); ?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-sm-3">
						<?= lang('Color Code'); ?>
					</div>
					<div class="col-sm-3">
						<?= lang('QTY Shirt'); ?>
					</div>
					<div class="col-sm-4">
						<?= lang('Cloth Color'); ?>
					</div>
					<div class="col-sm-2">
						<div class="btn btn-primary addButton"><i
						class="fa fa-plus"></i> <?=lang('add')?></div>
					</div>
				</div>
			</div>
			<div class="form-group" id="append_color">
				<div class="row" style="margin-bottom: 10px;">
					<div class="col-sm-3">
						<?= form_input('colorcode[]', set_value('colorcode'), 'class="form-control tip" '); ?>
					</div>
					<div class="col-sm-3">
						<?= form_input('qtyshirt[]', set_value('qtyshirt'), 'class="form-control tip"'); ?>
					</div>
					<div class="col-sm-4">
						<select name="clothcolor[]" class="form-control" id="pcc_type_1">
							<option value="Puma Red">Puma Red</option>
							<option value="Electic Blue">Electic Blue</option>
							<option value="Black">Black</option>
							<option value="White">White</option>
							<option value="Pepper Green">Pepper Green</option>
							<option value="Peacoat">Peacoat</option>
							<option value="Cyber Yellow">Cyber Yellow</option>
						</select>
						<?php // = form_input('clothcolor[]', set_value('clothcolor'), 'class="form-control tip" '); ?>
					</div>
					<div class="col-sm-2">
						
					</div>
				</div>
			</div>
			<div id="multi_append_color"></div>			
        </div>
        <div class="modal-footer">
            <?= form_submit('add_color', lang('add_room'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
<script>
$(document).on('click', '.addButton', function () {
		$('#pcc_type_1').select2('destroy');
		var phtml = $('#append_color').html();
			update_html = phtml.replace(/_1/g, '_');
		$('#multi_append_color').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' + update_html);

});

$(document).on('click', '.close-payment', function () {
	$(this).next().remove();
	$(this).remove();
	pa--;
});
</script>