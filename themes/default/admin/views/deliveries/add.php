<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<script type="text/javascript">
    var count = 1, an = 1;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error   = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
		<?php if (isset($inv)) { ?>
			localStorage.setItem('decustomer', '<?=$inv->customer_id?>');
			localStorage.setItem('debiller', '<?=$inv->biller_id?>');
			localStorage.setItem('dewarehouse', '<?= $inv->warehouse_id ?>');
			localStorage.setItem('dereceived_phone', '<?= $inv->shipping_request_phone ?>');
			localStorage.setItem('denote', '<?= str_replace(array("\r", "\n"), "", $this->bpas->decode_html($inv->shipping_request_note)); ?>');
			localStorage.setItem('deaddress', '<?= str_replace(array("\r", "\n"), "", $this->bpas->decode_html($inv->shipping_request_address)); ?>');
			localStorage.setItem('dediscount', '<?=$inv->order_discount_id?>');
			localStorage.setItem('deshipping', '<?=$inv->shipping?>');
			localStorage.setItem('slpayment_term', '<?=$inv->payment_term?>')
            localStorage.setItem('deitems', JSON.stringify(<?=$inv_items;?>));
		<?php } 
        if($this->input->get('customer')) { ?>
            if (!localStorage.getItem('deitems')) {
                localStorage.setItem('decustomer', <?=$this->input->get('customer');?>);
            }
        <?php } ?>
        var sale_date = new Date('<?= (isset($inv) && $inv) ? $inv->date : '' ?>');
        $("#dedate").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'bpas',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 1,
            forceParse: 0,
            startDate: sale_date
        });
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('dedate')) {
                setDate(new Date());
            }
            var old_date = $('#dedate').val();
            $(document).on('change', '#dedate', function(e) {
                localStorage.setItem('dedate', $(this).val());
            });
            if (dedate = localStorage.getItem('dedate')) {
                $('#dedate').val(dedate);
            }
        <?php } ?>
        function setDate(date_set) {
            $("#dedate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'bpas',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 1,
                forceParse: 0,
                startDate: sale_date
            }).datetimepicker('update', date_set);
        }
        $(document).on('change', '#debiller', function (e) {
            localStorage.setItem('debiller', $(this).val());
        });
        if (debiller = localStorage.getItem('debiller')) {
            $('#debiller').val(debiller);
        }
        if (!localStorage.getItem('detax2')) {
            localStorage.setItem('detax2', <?=$Settings->default_tax_rate2;?>);
        }
        if (localStorage.getItem('remove_dols') || ($('#si_reference').val() == '' && $('#so_reference').val() == '')) {
            if (localStorage.getItem('deitems')) {
                localStorage.removeItem('deitems');
            }
            if (localStorage.getItem('dediscount')) {
                localStorage.removeItem('dediscount');
            }
            if (localStorage.getItem('detax2')) {
                localStorage.removeItem('detax2');
            }
            if (localStorage.getItem('deshipping')) {
                localStorage.removeItem('deshipping');
            }
            if (localStorage.getItem('deref')) {
                localStorage.removeItem('deref');
            }
            if (localStorage.getItem('dewarehouse')) {
                localStorage.removeItem('dewarehouse');
            }
            if (localStorage.getItem('denote')) {
                localStorage.removeItem('denote');
            }
            if (localStorage.getItem('deinnote')) {
                localStorage.removeItem('deinnote');
            }
            if (localStorage.getItem('deaddress')) {
                localStorage.removeItem('deaddress');
            }
            if (localStorage.getItem('dereceived_phone')) {
                localStorage.removeItem('dereceived_phone');
            }
            if (localStorage.getItem('decustomer')) {
                localStorage.removeItem('decustomer');
            }
            if (localStorage.getItem('decurrency')) {
                localStorage.removeItem('decurrency');
            }
            if (localStorage.getItem('dedate')) {
                localStorage.removeItem('dedate');
            }
            if (localStorage.getItem('debiller')) {
                localStorage.removeItem('debiller');
            }
            // $('#modal-loading').show();
            // location.reload();
        }
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_delivery'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("deliveries/add", $attrib);
				echo form_hidden('sale_id', (isset($sale_id) ? $sale_id : ''));
				echo form_hidden('sale_order_id',(isset($sale_order_id) ? $sale_order_id : ''));
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
						<?php } echo form_hidden('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ((isset($inv) && $inv) ? $inv->reference_no : '')), 'class="form-control input-tip" id="deref"'); ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("reference_no", "do_reference_no"); ?>
								<?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $do_reference_no), 'class="form-control tip" id="do_reference_no"'); ?>
							</div>
						</div>	
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("so_reference", "so_reference"); ?>
								<?php
								$so_opts[""] =  lang('select_so') ;
								if($saleorders){
									foreach ($saleorders as $saleorder) {
										$so_opts[$saleorder->id] = $saleorder->reference_no;
									}
								}
								echo form_dropdown('so_reference', $so_opts, (isset($sale_order_id) ? $sale_order_id: ''), 'id="so_reference" class="form-control input-tip select" style="width:100%;" ');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("si_reference", "si_reference"); ?>
								<?php
								$sa_opts[""] =  lang('select_si') ;
								if ($sales) {
									foreach ($sales as $sale) {
										$sa_opts[$sale->id] = $sale->reference_no;
									}
								}
								echo form_dropdown('si_reference', $sa_opts, (!empty($sale_id) ? $sale_id : ''), 'id="si_reference" class="form-control input-tip select" style="width:100%;" ');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("customer", "customer"); ?>
								<?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ((isset($customer) && $customer) ? $customer->name : '')), 'class="form-control" id="decustomer" readonly redeired="redeired" '); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?php
									$users_detail = array();
									foreach($drivers as $user){
										$users_detail[$user->id] = $user->name;
									}
									echo lang("delivered_by", "delivered_by");
									echo form_dropdown("delivered_by", $users_detail, (isset($_POST['delivered_by']) ? $_POST['delivered_by'] : '')," class='form-control' id='delivered_by' " );
								?>
							</div>
						</div>
                        <div class="col-md-4">
							<div class="form-group">
								<?= lang("send_number", "send_number"); ?>
								<?= form_input('send_number', (isset($_POST['send_number']) ? $_POST['send_number'] : ''), 'class="form-control " id="send_number"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("Received_Number", "received_by"); ?>
								<?= form_input('received_by', (isset($_POST['received_by']) ? $_POST['received_by'] : $sale->shipping_request_phone), 'class="form-control" id="dereceived_phone"'); ?>
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
                                                <th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                                <?php if ($Settings->show_qoh == 1) { ?>
                                                    <th class="col-md-1"><?= lang("qoh"); ?></th>
                                                <?php } if($Settings->product_expiry == '1') { ?>
                                                    <th class="col-md-1"><?= lang("expiry_date"); ?></th>
                                                <?php } ?>
                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <th class="col-md-2"><?= lang("delivered_quantity"); ?></th>
                                                <th class="col-md-2"><?= lang("balance"); ?></th>
                                                <th class="col-md-1"><?= lang("delivery_quantity"); ?></th>
                                                <th class="col-md-2"><?= lang("unit"); ?></th>
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
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						<div class="clearfix"></div>
                        <div class="row" id="bt">
                            <div class="col-sm-6">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "denote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $sale->shipping_request_note), 'class="form-control" id="denote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
							<div class="col-sm-6">
						        <div class="form-group">
									<?= lang("Address_Receiver", "address"); ?>
									<?php
										$address = '';
										if (isset($customer) && $customer) {
											$address = $customer->address . " " . $customer->city . " " . $customer->state . " " . $customer->postal_code . " " . $customer->country . "<br>Tel: " . $customer->phone;
										}
									?>
									<?php echo form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : $sale->shipping_request_address), 'class="form-control" id="deaddress" required="required"'); ?>
								</div>
							</div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group"><?php echo form_submit('add_delivery', $this->lang->line("submit"), 'id="add_delivery" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
<script type="text/javascript">
	$(document).ready(function () {
		$('#so_reference').on('change',function(){
			var so_reference = $(this).val();
			location.replace(site.base_url+"deliveries/add/"+so_reference);
		});
		$('#si_reference').on('change',function(){
			var si_reference = $(this).val();
			location.replace(site.base_url+"deliveries/add/0/"+si_reference);
		});
	});
</script>