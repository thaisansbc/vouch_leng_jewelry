<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, shipping = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
	var concretes = '<?= ($this->config->item("concretes") ? true : false) ?>';
    $(document).ready(function () {
		<?php if (isset($inv) && $inv) { ?>
        localStorage.setItem('rebiller', '<?=$inv->biller_id?>');
        localStorage.setItem('resupplier', '<?=$inv->supplier_id?>');
        localStorage.setItem('reref', '<?=$inv->reference_no?>');
        localStorage.setItem('rewarehouse', '<?=$inv->warehouse_id?>');
        localStorage.setItem('restatus', '<?=$inv->status?>');
        localStorage.setItem('renote', '<?= str_replace(array("\r", "\n"), "", $this->bpas->decode_html($inv->note)); ?>');
        localStorage.setItem('rediscount', '<?=$inv->order_discount_id?>');
        localStorage.setItem('retax2', '<?=$inv->order_tax_id?>');
        localStorage.setItem('reshipping', '<?=$inv->shipping?>');
        localStorage.setItem('reitems', JSON.stringify(<?=$inv_items;?>));
        <?php } ?>
        <?php if($this->input->get('resupplier')) { ?>
        if (!localStorage.getItem('reitems')) {
            localStorage.setItem('resupplier', <?=$this->input->get('resupplier');?>);
        }
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
        if (!localStorage.getItem('redate')) {
            $("#redate").datetimepicker({
                <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
                fontAwesome: true,
                language: 'bpas',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#redate', function (e) {
            localStorage.setItem('redate', $(this).val());
        });
        if (redate = localStorage.getItem('redate')) {
            $('#redate').val(redate);
        }
        <?php } ?>
        $(document).on('change', '#rebiller', function (e) {
            localStorage.setItem('rebiller', $(this).val());
        });
        if (rebiller = localStorage.getItem('rebiller')) {
            $('#rebiller').val(rebiller);
        }
        if (!localStorage.getItem('retax2')) {
            localStorage.setItem('retax2', <?=$Settings->default_tax_rate2;?>);
        }
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_stock_received'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("purchases/add_receive/".(isset($purchase_id) ? $purchase_id : ''), $attrib);
				echo form_hidden('purchase_id', (isset($purchase_id) ? $purchase_id : ''));
				echo form_hidden('purchase_order_id', (isset($purchase_order_id) ? $purchase_order_id : ''));
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "date"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="redate" required="required"'); ?>
                                </div>
                            </div>
						<?php } ?>
						<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "reref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="reref"'); ?>
                            </div>
                        </div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "rewarehouse"); ?>
								<?php
								foreach ($warehouses as $warehouse) {
									$wh[$warehouse->id] = $warehouse->name;
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="rewarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
						<?php if($this->config->item("po_receive_item")){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("po_reference", "po_reference"); ?>
									<?php
									$po_opts[""] =  lang('select_po') ;
									if (isset($purchase_orders) && $purchase_orders) {
										foreach ($purchase_orders as $purchase_order) {
											$po_opts[$purchase_order->id] = $purchase_order->reference_no;
										}
									}
									echo form_dropdown('po_reference', $po_opts, (isset($purchase_order_id) ? $purchase_order_id: ''), 'id="po_reference" class="form-control input-tip select" style="width:100%;" ');
									?>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("pu_reference", "pu_reference"); ?>
								<?php
								$pu_opts[""] =  lang('select') . ' ' . lang('purchase');
								if($purchases){
									foreach ($purchases as $purchase) {
										$pu_opts[$purchase->id] = $purchase->reference_no;
									}
								}
								echo form_dropdown('pu_reference', $pu_opts, (isset($purchase_id) ? $purchase_id: ''), 'id="pu_reference" class="form-control input-tip select" style="width:100%;" ');
								?>
							</div>
						</div>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("si_reference", "posiref"); ?>
                                <?php echo form_input('si_reference_no', (isset($_POST['si_reference_no']) ? $_POST['si_reference_no'] : ((isset($inv) && isset($inv->si_reference_no)) ? $inv->si_reference_no : '')), 'class="form-control input-tip" id="posiref"'); ?>
                            </div>
                        </div>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("dn_reference", "dn_reference"); ?>
                                <?php echo form_input('dn_reference', (isset($_POST['dn_reference']) ? $_POST['dn_reference'] : ''), 'class="form-control input-tip" id="dn_reference"'); ?>
                            </div>
                        </div>
						<?php if($this->config->item("concretes")){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("truck", "truck"); ?>
									<?php echo form_input('truck', (isset($_POST['truck']) ? $_POST['truck'] : ''), 'class="form-control input-tip" id="truck"'); ?>
								</div>
							</div>
						<?php } ?>
						<?= form_hidden('re_reference_no', (isset($_POST['re_reference_no']) ? $_POST['re_reference_no'] : ((isset($inv) && $inv) ? $inv->reference_no : '')), 'class="form-control tip" id="re_reference_no" readonly="true"'); ?>
						<?php echo form_hidden('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ((isset($supplier) && $supplier) ? $supplier->id: '')), 'class="form-control" id="resupplier" required="required" '); ?>
						<div class="col-md-4">
							<div class="form-group">
								<?php
									$us1 = array(lang('select').' '.lang('user'));
									foreach($users as $user){
										$us1[$user->id] = $user->first_name . " " . $user->last_name;
									}
									echo lang("receive_by", "receive_by");
									echo form_dropdown("receive_by", $us1, (isset($_POST['receive_by']) ? $_POST['receive_by'] : $this->session->userdata('user_id'))," class='form-control' id='receive_by' required='required'" );
								?>
							</div>
						</div>
                        <div class="col-md-12" id="sticker"></div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="reTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
											<tr>
												<th class="col-md-6"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
												<?php if ($Settings->product_expiry) {
													echo '<th class="col-md-1">' . $this->lang->line("expiry_date") . '</th>';
												} ?>
												<th class="col-md-2" style="text-align: center !important;"><?= lang("quantity"); ?></th>
												<?php if ($Settings->product_serial) { ?>
													<th class="col-md-2"><?= lang("serial_no"); ?></th>
												<?php } ?>
												<th class="col-md-2" style="text-align: center !important;"><?= lang("receive_quantity"); ?></th>
                                                <?php if ($Settings->using_weight) { ?>
                                                    <th class="col-md-2"><?= lang("weight"); ?></th>
                                                <?php } ?>
												<?php if ($this->config->item("concretes")) { ?> 
													<th class="col-md-2"><?= lang("sup_qty"); ?></th>
												<?php } ?>
												<th style="width: 30px !important; text-align: center !important;">
													<?= lang('actions'); ?>
												</th>
											</tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
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
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="denote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("address", "address"); ?>
									<?php echo form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : ((isset($supplier) && $supplier) ? $supplier->address : '')), 'class="form-control" id="address"'); ?>
								</div>
							</div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group"><?php echo form_submit('add_receive', $this->lang->line("submit"), 'id="add_receive" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
						
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
				<?php if ($suspend_option != 0) { ?>
					<div class="form-group">
						<?= lang('tags', 'tags'); ?>
						<?php
						$tags1 = array(0 => lang('no'));
						foreach ($tags as $tag) {
							$tags1[$tag->name] = $tag->name;
						} ?>
						<?= form_dropdown('tags', $tags1, '', 'class="form-control" id="tags" style="width:100%;"'); ?>
					</div>
					<script type="text/javascript">
						$ (function() {
							$("#tags").on("change",function() {
								var tags = $(this).val();
								$("#icomment").val(tags);
							});
						});
					</script>
				<?php } ?>
				<div class="form-group">
					<?= lang('note', 'note'); ?>
					<?= form_textarea('comment', '', 'class="form-control skip" id="icomment" style="height:80px;"'); ?>
				</div>
                <div class="form-group hidden">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php $opts = array(0 => lang('no'), 1 => lang('yes')); ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
				<div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true"><i class="fa fa-2x">&times;</i></span>
					<span class="sr-only"><?=lang('close');?></span>
				</button>
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
		$('#pu_reference').on('change',function(){
			var pu_reference = $(this).val();
			location.replace(site.base_url+"purchases/add_receive/"+pu_reference);
		});
		$('#po_reference').on('change',function(){
			var po_reference = $(this).val();
			location.replace(site.base_url+"purchases/add_receive/"+po_reference+"/1");
		});
	});
</script>

