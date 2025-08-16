<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
	var count = 1, an = 1;
	$(document).ready(function () {
		
		if (localStorage.getItem('remove_csls')) {
			if (localStorage.getItem('csitems')) {
				localStorage.removeItem('csitems');
			}
			if (localStorage.getItem('csref')) {
				localStorage.removeItem('csref');
			}
			if (localStorage.getItem('csexpiry')) {
                localStorage.removeItem('csexpiry');
            }
			if (localStorage.getItem('cscustomer')) {
                localStorage.removeItem('cscustomer');
            }
			if (localStorage.getItem('cswarehouse')) {
				localStorage.removeItem('cswarehouse');
			}
			if (localStorage.getItem('csnote')) {
				localStorage.removeItem('csnote');
			}
			if (localStorage.getItem('csdate')) {
				localStorage.removeItem('csdate');
			}
			localStorage.removeItem('remove_csls');
		}
		
		<?php if ($Owner || $Admin || $GP['sales-date']) { ?>
			if (!localStorage.getItem('csdate')) {
				$("#csdate").datetimepicker({
					format: site.dateFormats.js_ldate,
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
			$(document).on('change', '#csdate', function (e) {
				localStorage.setItem('csdate', $(this).val());
			});
			if (csdate = localStorage.getItem('csdate')) {
				$('#csdate').val(csdate);
			}
		<?php } ?>
		
		$("#add_item_cs").autocomplete({
			source: function (request, response) {
				if (!$('#cscustomer').val()) {
					$('#add_item').val('').removeClass('ui-autocomplete-loading');
					bootbox.alert('<?=lang('select_above');?>');
					$('#add_item').focus();
					return false;
				} 
				$.ajax({
					type: 'get',
					url: '<?= admin_url('sales/suggestions'); ?>',
					dataType: "json",
					data: {
						term: request.term,
						warehouse_id: $("#cswarehouse").val(),
						customer_id: $("#cscustomer").val(),						
					},
					success: function (data) {
						$(this).removeClass('ui-autocomplete-loading');
						response(data);
					}
				});
			},
			minLength: 1,
			autoFocus: false,
			delay: 250,
			response: function (event, ui) {
			   if (ui.content.length == 1 && ui.content[0].id != 0) {
					ui.item = ui.content[0];
					$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
					$(this).autocomplete('close');
					$(this).removeClass('ui-autocomplete-loading');
				}
			},
			select: function (event, ui) {
				event.preventDefault();
				if (ui.item.id !== 0) {
					var row = add_cs_item(ui.item);
					if (row)
						$(this).val('');
				} else {
					bootbox.alert('<?= lang('no_match_found') ?>');
				}
			}
		});
		
		
		$(document).on('change', '#csnote', function (e) {
            localStorage.setItem('csnote', $(this).val());
        });
        if (csnote = localStorage.getItem('csnote')) {
            $('#csnote').val(csnote);
        }
		
		$(document).on('change', '#csbiller', function (e) {
            localStorage.setItem('csbiller', $(this).val());
        });
        if (csbiller = localStorage.getItem('csbiller')) {
            $('#csbiller').val(csbiller);
        }

	});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_customer_stock'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<p class="introtext"><?= lang('enter_info'); ?></p>
			<?php 
				$attrib = array('data-toggle' => 'validator', 'role' => 'form');
				echo admin_form_open_multipart("pos/add_customer_stock/", $attrib); 
				?>
				<div class="row">
				
					<div class="col-lg-12">

						<?php if ($Owner || $Admin || $GP['sales-date']) { ?>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("date", "csdate"); ?>
									<?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="csdate" required="required"'); ?>
								</div>
							</div>
						<?php } ?>
						
						<div class="col-sm-4">
							<div class="form-group">
								<?= lang("reference_no", "csreference_no"); ?>
								<?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no" '); ?>
							</div>
						</div>
						
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("biller", "csbiller"); ?>
									<?php
									$bl[""] = "";
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
									}
									echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="csbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
									?>
								</div>
							</div>
						<?php } else {
							$biller_input = array(
								'type' => 'hidden',
								'name' => 'biller',
								'id' => 'csbiller',
								'value' => $this->session->userdata('biller_id'),
							);

							echo form_input($biller_input);
						} ?>
						
						<div class="clearfix"></div>
						<div class="col-md-12">
							<div class="panel panel-warning">
								<div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
								<div class="panel-body" style="padding: 5px;">
								
									<?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("warehouse", "cswarehouse"); ?>
												<?php
												$wh[''] = '';
												foreach ($warehouses as $warehouse) {
													$wh[$warehouse->id] = $warehouse->name;
												}
												echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="cswarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;"');
												?>
											</div>
										</div>
									<?php } else {
										$warehouse_input = array(
											'type' => 'hidden',
											'name' => 'warehouse',
											'id' => 'cswarehouse',
											'value' => $this->session->userdata('warehouse_id'),
											);

										echo form_input($warehouse_input);
									} ?>
									
									<div class="col-md-4">
										<div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="cscustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php if ($Owner || $Admin) { ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= site_url('pos/add_customer'); ?>" id="add-customer"class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
									</div>
														
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("expiry", "csexpiry"); ?>
											<?php 
												$time = strtotime(date("Y-m-d"));
												$final = date("d/m/Y", strtotime("+1 month", $time));
											?>
											<?= form_input('expiry_date', (isset($_POST['expiry_date']) ? $_POST['expiry_date'] : $final), 'class="form-control date" id="csexpiry" autocomplete="off" required="required"'); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-md-12" id="sticker">
							<div class="well well-sm">
								<div class="form-group" style="margin-bottom:0;">
									<div class="input-group">
										<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
											<i class="fa fa-2x fa-barcode addIcon"></i></a>
										</div>
										<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item_cs" placeholder="' . lang("add_product_to_order") . '"'); ?>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
						
						<div class="col-md-12">
							<div class="control-group table-group">
								<label class="table-label"><?= lang("order_items"); ?> *</label>
								<div class="controls table-controls">
									<table id="csTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
										<thead>
											<tr>
												<th style="width:205px"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
												<th style="width:105px"><?= lang('variant') ?></th>
												<th style="width:105px"><?= lang('unit') ?></th>
												<th style="width:105px"><?= lang('quantity') ?></th>
												<?php
												if ($Settings->product_serial) {
													echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
												}
												?>
												<th style="width: 15px !important; text-align: center;">
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
						
						<div class="clearfix"></div>
						
						<div class="col-md-12">
							<div class="form-group">
								<?= lang("note", "note"); ?>
								<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="csnote"'); ?>
							</div>
						</div>
						
						<div class="col-md-12">
                            <div class="fprom-group"><?php echo form_submit('add_customer_stock', lang("submit"), 'id="add_customer_stock" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
					
						<div class="clearfix"></div>
						
					</div>
				
				</div>
				
				<?php echo form_close(); ?>
				
			</div>
        </div>
    </div>
</div>

<script type="text/javascript" charset="UTF-8">
	$(function () {
		$('#cscustomer').val(localStorage.getItem('poscustomer')).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
				$.ajax({
					type: "get", async: false,
					url: "<?=admin_url('customers/getCustomer')?>/" + $(element).val(),
					dataType: "json",
					success: function (data) {
						callback(data[0]);						
					}
				});
			},
			ajax: {
				url: site.base_url + "customers/suggestions",
				dataType: 'json',
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10
					};
				},
				results: function (data, page) {
					if (data.results != null) {
						return {results: data.results};
					} else {
						return {results: [{id: '', text: 'No Match Found'}]};
					}
				}
			}
		});
	});
</script>