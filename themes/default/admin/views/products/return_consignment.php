<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('return_consignment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-to-form');
                echo admin_form_open_multipart("products/return_consignment/" . $consignment->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['charge_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "usdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i')), 'class="form-control input-tip datetime" id="usdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "ref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="ref" '); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label>
                                <div class="controls table-controls">
                                    <table id="toTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
	                                        <tr>
	                                            <th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>                                           
	                                            <?php if ($Settings->product_expiry) {
	                                                echo '<th>' . $this->lang->line("expiry_date") . '</th>';
	                                            } ?>
												<?php if ($Settings->product_serial) {
	                                                echo '<th>' . lang("serial_no") . '</th>';
	                                            } ?>
												<th><?= lang("unit_price"); ?></th>
												<th><?= lang("consignment_quantity"); ?></th>
												<th><?= lang("sale_quantity"); ?></th>
												<th><?= lang("returned_quantity"); ?></th>
												<th><?= lang("balance"); ?></th>
												<th><?= lang("return"); ?></th>
												<th><?= lang("unit"); ?></th>
	                                        </tr>
                                        </thead>
                                        <tbody>
											<?php
												$tbody = '';
												if ($consignment_items) {
													foreach ($consignment_items as $consignment_item) {
														$product = $this->site->getProductByID($consignment_item->product_id);
														$units = $this->site->getUnitbyProduct($product->id,$product->unit);
														$select_unit = '<select class="unit form-control text-center" name="unit[]">';
														if ($units) {
															foreach ($units as $unit) {
																$select_unit .= '<option '.($unit->id == $product->unit ? 'selected' : '').' unit_qty = "'.$unit->operation_value.'" class="text-center" value="'.$unit->id.'">'.$unit->name.'</option>';
															}
														}
														$select_unit .= '</select>';
														$consignment_item->sale_qty = ($consignment_item->sale_qty == '' ? 0 : $consignment_item->sale_qty);
														$consignment_item->return_qty = ($consignment_item->return_qty == '' ? 0 : $consignment_item->return_qty);
														$balance_qty = $consignment_item->quantity - $consignment_item->sale_qty - $consignment_item->return_qty;
														$tbody .='<tr>
																	<td>
																		<input name="consignment_item_id[]" type="hidden" value="'.$consignment_item->id.'"/>
																		<input name="product_id[]" type="hidden" value="'.$consignment_item->product_id.'"/>
														 				<input name="product_code[]" type="hidden" value="'.$consignment_item->product_code.'"/>
																		<input name="product_name[]" type="hidden" value="'.$consignment_item->product_name.'"/>
																		<input name="product_type[]" type="hidden" value="'.$consignment_item->product_type.'"/>
																		<input name="option_id[]" type="hidden" value="'.$consignment_item->option_id.'"/>
																		<input name="product_expiry[]" type="hidden" value="'.$consignment_item->expiry.'"/>
														 				<input name="product_serial[]" type="hidden" value="'.$consignment_item->serial_no.'"/>
														 				<input class="balance_qty" name="balance_qty[]" type="hidden" value="'.$balance_qty.'"/>
														 				<input class="product_unit_id" name="product_unit_id[]" type="hidden" value="'.$product->unit.'"/>
																		<input class="product_cost" name="product_cost[]" type="hidden" value="'.$product->cost.'"/>
																		<input class="real_unit_price" name="real_unit_price[]" type="hidden" value="'.$consignment_item->real_unit_price.'"/>
														 				'.$consignment_item->product_code.' - '.$consignment_item->product_name.'</td>
																		'.($Settings->product_expiry ? '<td class="text-center">'.$this->bpas->hrsd($consignment_item->expiry).'</td>' : '' ).'
														 				'.($Settings->product_serial ? '<td class="text-center">'.$consignment_item->serial_no.'</td>' : '' ).'
														 				<td class="text-right">'.$this->bpas->formatMoney($consignment_item->unit_price).'</td>
																		<td class="text-right">'.$this->bpas->convertQty($consignment_item->product_id,$consignment_item->quantity).'</td>
																		<td class="text-right">'.$this->bpas->convertQty($consignment_item->product_id,$consignment_item->sale_qty).'</td>
														 				<td class="text-right">'.$this->bpas->convertQty($consignment_item->product_id,$consignment_item->return_qty).'</td>
																		<td class="text-right">'.$this->bpas->convertQty($consignment_item->product_id,$balance_qty).'</td>
														 				<td class="text-right"><input name="return_quantity[]" class="form-control text-center return_quantity" type="text"/></td>
														 				<td class="text-right">'.$select_unit.'</td>
														 			</tr>';

													}
												}
												echo $tbody;
											?>
										</tbody>
										<input type="hidden" name="total_return" class="total_return" value = "0" />
										<input type="hidden" name="consignment" class="consignment" value = "<?= $consignment->id ?>" />
                                    </table>
                                </div>
                            </div>
                            <div class="from-group">
                                <?= lang("note", "note"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'id="note" class="form-control" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                            <div
                                class="from-group"><?php echo form_submit('return_consignment', $this->lang->line("submit"), 'id="return_consignment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            </div>
                        </div>

                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
	function total_return() {
		var total_return = 0;
		$('.return_quantity').each(function() {
			var parent = $(this).parent().parent();
			var new_return_quantity = $(this).val() - 0;
			var unit_qty = parent.find('.unit').find(":selected").attr('unit_qty') - 0;
			total_return += new_return_quantity * unit_qty;
		});
		$('.total_return').val(total_return);
	}
    $(document).on("change", '.unit', function () {
		var parent = $(this).parent().parent();
		var product_unit_id = parent.find('.product_unit_id').val();
		var quantity = parent.find('.balance_qty').val() - 0;
		var new_return_quantity = parent.find('.return_quantity').val() - 0;
		var unit_qty = parent.find('.unit').find(":selected").attr('unit_qty') - 0;
		new_return_quantity = new_return_quantity * unit_qty;
        if (!is_numeric(new_return_quantity) || new_return_quantity < 0 || new_return_quantity > quantity) {
			$(this).select2('val',product_unit_id);
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
			parent.find('.product_unit_id').val($(this).val());
		}
		total_return();
    });
	var old_return_quantity;
    $(document).on("focus", '.return_quantity', function () {
        old_return_quantity = $(this).val();
    }).on("change", '.return_quantity', function () {
		var parent = $(this).parent().parent();
		var quantity = parent.find('.balance_qty').val() - 0;
		var new_return_quantity = $(this).val() - 0;
		var unit_qty = parent.find('.unit').find(":selected").attr('unit_qty') - 0;
		new_return_quantity = new_return_quantity * unit_qty;
        if (!is_numeric(new_return_quantity) || new_return_quantity < 0 || new_return_quantity > quantity) {
            $(this).val(old_return_quantity);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		total_return();
    });
</script>