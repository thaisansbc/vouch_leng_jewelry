<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, shipping = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
		<?php if ($inv) { ?>
        localStorage.setItem('dedate', '<?= date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')], strtotime($inv->date))?>');
        localStorage.setItem('decustomer', '<?=$inv->customer_id?>');
        localStorage.setItem('debiller', '<?=$inv->biller_id?>');
        //localStorage.setItem('deref', '<?=$inv->reference_no?>');
        localStorage.setItem('dewarehouse', '<?=$inv->warehouse_id?>');

        localStorage.setItem('denote', '<?= str_replace(array("\r", "\n"), "", $this->bpas->decode_html($inv->note)); ?>');
        localStorage.setItem('dediscount', '<?=$inv->order_discount_id?>');
        localStorage.setItem('detax2', '<?=$inv->order_tax_id?>');
        localStorage.setItem('deshipping', '<?=$inv->shipping?>');
		localStorage.setItem('slpayment_term', '<?=$inv->payment_term?>')
        localStorage.setItem('deitems', JSON.stringify(<?=$inv_items;?>));
        <?php } ?>
        <?php if($this->input->get('customer')) { ?>
        if (!localStorage.getItem('deitems')) {
            localStorage.setItem('decustomer', <?=$this->input->get('customer');?>);
        }
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
        if (!localStorage.getItem('dedate')) {
            $("#dedate").datetimepicker({
                <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
                fontAwesome: true,
                language: 'bms',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#dedate', function (e) {
            localStorage.setItem('dedate', $(this).val());
        });
        if (dedate = localStorage.getItem('dedate')) {
            $('#dedate').val(dedate);
        }
        <?php } ?>
        $(document).on('change', '#debiller', function (e) {
            localStorage.setItem('debiller', $(this).val());
        });
        if (debiller = localStorage.getItem('debiller')) {
            $('#debiller').val(debiller);
        }
        if (!localStorage.getItem('detax2')) {
            localStorage.setItem('detax2', <?=$Settings->default_tax_rate2;?>);
        }        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_delivery'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("deliveries/edit/".$id, $attrib);
				echo form_hidden('delivery_id', $id);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "date"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="dedate" redeired="redeired"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "deref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $inv->reference_no), 'class="form-control input-tip" id="deref"'); ?>
                            </div>
                        </div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("do_reference_no", "do_reference_no"); ?>
								<?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $do_reference_no), 'class="form-control tip" id="do_reference_no"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("customer", "customer"); ?>
								<?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $customer->name), 'class="form-control" id="decustomer" redeired="redeired" '); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?php
									$users_detail = array();
									foreach($allUsers as $user){
										$users_detail[$user->id] = $user->first_name . " " . $user->last_name;
									}
									echo lang("delivered_by", "delivered_by");
									echo form_dropdown("delivered_by", $users_detail, (isset($_POST['delivered_by']) ? $_POST['delivered_by'] : $inv->delivered_by)," class='form-control' id='delivered_by' " );
								?>
							</div>
						</div>
						<div class="col-md-4 hidden">
							<div class="form-group">
								<?= lang("received_by", "received_by"); ?>
								<?= form_input('received_by', (isset($_POST['received_by']) ? $_POST['received_by'] : ''), 'class="form-control " id="received_by"'); ?>
							</div>
						</div>
                        <div class="col-md-12" id="sticker"></div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="deTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                        <tr>
                                            <th class="col-md-6"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                            <th class="col-md-3"><?= lang("quantity"); ?></th>
											<th class="col-md-3"><?= lang('delivered_quantity') ?></th>
                                            <th style="width: 30px !important; text-align: center;">
												<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
											</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="total_items" value="" id="total_items" redeired="redeired"/>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						<div class="clearfix"></div>
                        <div class="row" id="bt">
                            <div class="col-sm-6">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "denote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $inv->note), 'class="form-control" id="denote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("address", "address"); ?>
									<?php echo form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : $inv->address), 'class="form-control" id="address" required="required"'); ?>
								</div>
							</div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group"><?php echo form_submit('edit_delivery', $this->lang->line("submit"), 'id="edit_delivery" class="btn btn-primary" style="pediting: 6px 15px; margin:15px 0;"'); ?>
                            <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

