<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
		<?php if ($consignment) { ?>
			localStorage.setItem('csmdate', '<?= date($dateFormats[($Settings->date_with_time == 0 ? 'php_sdate' : 'php_ldate')], strtotime($consignment->date))?>');
			localStorage.setItem('csmcustomer', '<?=$consignment->customer_id?>');
			localStorage.setItem('csmbiller', '<?=$consignment->biller_id?>');
			localStorage.setItem('csmref', '<?=$consignment->reference_no?>');
			localStorage.setItem('csmwarehouse', '<?=$consignment->warehouse_id?>');
			localStorage.setItem('csmvalid_day', '<?=$consignment->valid_day?>');
			localStorage.setItem('csmnote', '<?= str_replace(array("\r", "\n"), "", $this->bpas->decode_html($consignment->note)); ?>');
			localStorage.setItem('csmitems', JSON.stringify(<?=$consingment_items;?>));
        <?php } if($this->input->get('customer')) { ?>
        if (!localStorage.getItem('csmitems')) {
            localStorage.setItem('csmcustomer', <?=$this->input->get('customer');?>);
        }
		<?php } if ($Owner || $Admin || $GP['change_date']) { ?>
			if (!localStorage.getItem('csmdate')) {
				$("#csmdate").datetimepicker({
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
			$(document).on('change', '#csmdate', function (e) {
				localStorage.setItem('csmdate', $(this).val());
			});
			if (csmdate = localStorage.getItem('csmdate')) {
				$('#csmdate').val(csmdate);
			}
        <?php } ?>
        $(document).on('change', '#csmbiller', function (e) {
            localStorage.setItem('csmbiller', $(this).val());
        });
        if (csmbiller = localStorage.getItem('csmbiller')) {
            $('#csmbiller').val(csmbiller);
        }
		if (csmvalid_day = localStorage.getItem('csmvalid_day')) {
            $('#csmvalid_day').val(csmvalid_day);
        }
		$('#csmvalid_day').focus(function () {
			old_valid_day = $(this).val();
		}).change(function () {
			if (!is_numeric($(this).val())) {
				$(this).val(old_valid_day);
				bootbox.alert(lang.unexpected_value);
				return;
			}else{
				localStorage.setItem('csmvalid_day', $(this).val());
			}
		});
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#csmcustomer').val()) {
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
                        warehouse_id: $("#csmwarehouse").val(),
                        customer_id: $("#csmcustomer").val()
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
					var product_type = ui.item.row.type;
					if (product_type == 'digital') {
						$.ajax({
							type: 'get',
							url: '<?= admin_url('sales/suggestionsDigital'); ?>',
							dataType: "json",
							data: {
								term : ui.item.item_id,
								warehouse_id: $("#csmwarehouse").val(),
								customer_id: $("#csmcustomer").val(),
							},
							success: function (result) {
								$.each( result, function(key, value) {
									var row = add_invoice_item(value);
									if (row)
										$(this).val('');
								});
							}
						});
						$(this).val('');
					} else {
                    	var row = add_invoice_item(ui.item);
                    	if (row) $(this).val('');
					}
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
		$(window).bind('beforeunload', function (e) {
            localStorage.setItem('remove_csmls', true);
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_consignment').click(function () {
            $(window).unbind('beforeunload');            
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_consignment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("products/edit_consignment/".$consignment->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "csmdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="csmdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "csmref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="csmref"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "csmbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="csmbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'csmbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("customer", "csmcustomer"); ?>
                                            <div class="input-group">
                                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="csmcustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"'); ?>
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
                                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= admin_url('customers/add'); ?>" id="add-customer"class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("warehouse", "csmwarehouse"); ?>
											<?php
											foreach ($warehouses as $warehouse) {
												$wh[$warehouse->id] = $warehouse->name;
											}
											echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="csmwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
											?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("valid_day", "csmvalid_day"); ?>
											<?php echo form_input('valid_day', '', 'class="form-control input-tip" id="csmvalid_day"'); ?>
										</div>
									</div>
									<div class="clearfix"></div>
									<?php if($Settings->project == 1){ ?>
										<?php if ($Owner || $Admin) { ?>
											<div class="col-md-4">
												<div class="form-group" style="margin-bottom: 13px;">
													<?= lang("project", "project"); ?>
													<div class="input-group">
														<div class="no-project">
															<?php
															$pj[''] = '';
															if(isset($projects) && $projects){
																foreach ($projects as $project) {
																	$pj[$project->id] = $project->name;
																}
															}
															echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
															?>
														</div>
														<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
															<a href="<?= admin_url('system_settings/projects'); ?>" class="external" target="_blank">
																<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
															</a>
														</div>
														<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
															<a href="<?= admin_url('system_settings/add_project'); ?>" class="external" data-toggle="modal" data-target="#myModal">
																<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
															</a>
														</div>
													</div>
												</div>
											</div>
										<?php } else { ?>
											<div class="col-md-4" style="margin-bottom: 13px;">
												<div class="form-group">
													<?= lang("project", "project"); ?>
													<div class="no-project">
														<?php
														$pj[''] = ''; 
														if(isset($user) && isset($projects) && $projects){
															$right_project = json_decode($user->project_ids);
															if($right_project){
																foreach ($projects as $project) {
																	if(in_array($project->id, $right_project)){
																		$pj[$project->id] = $project->name;
																	}
																}
															}
														}
														echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
														?>
													</div>
												</div>
											</div>
										<?php } ?>
									<?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                        </div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>"><i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="csmTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
											<tr>
												<th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
												<?php if($Settings->show_qoh == 1) { ?>	
													<th class="col-md-1"><?= lang("qoh"); ?></th>	
												<?php } if ($Settings->product_expiry) {
													echo '<th class="col-md-1">' . $this->lang->line("expiry_date") . '</th>';
												} if ($Settings->product_serial) {
													echo '<th class="col-md-1">' . lang("serial_no") . '</th>';
												}
												?>
												<th class="col-md-1"><?= lang("net_unit_price"); ?></th>
												<th class="col-md-1"><?= lang("quantity"); ?></th>
												<?php if ($Settings->show_unit == 1) { ?>	
													<th class="col-md-1"><?= lang("unit"); ?></th>	
												<?php } ?>
												<th><?= lang("subtotal"); ?> (<spanclass="currency"><?= $default_currency->code ?></span>)</th >
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
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
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "csmnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="csmnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group"><?php echo form_submit('edit_consignment', $this->lang->line("submit"), 'id="edit_consignment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> : <span class="totals_val pull" id="titems">0</span></td>
                            <td><?= lang('total') ?> : <span class="totals_val pull" id="total">0.00</span></td>
                            <td><?= lang('grand_total') ?> : <span class="totals_val pull" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
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
                    <?php if ($Settings->product_serial) { ?>
						<div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <div id="pserials-div"></div>
								<input type="hidden" class="form-control" id="pserial">
								<input type="hidden" class="form-control" id="pscost">
                            </div>
                        </div>
                    <?php } ?>
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
					<?php if($Settings->attributes==1){ ?>
						<div class="form-group">
							<label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
							<div class="col-sm-8">
								<div id="poptions-div"></div>
							</div>
						</div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
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
<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
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
				<?php if($suspend_option != 0){ ?>
					<div class="form-group">
						<?= lang('tags', 'tags'); ?>
						<?php
						$tags1 = array(0 => lang('no'));
						foreach($tags as $tag){
							$tags1[$tag->name] = $tag->name;
						}
						?>
						<?= form_dropdown('tags', $tags1, '', 'class="form-control" id="tags" style="width:100%;"'); ?>
					</div>
					<script type="text/javascript">
						$(function(){
							$("#tags").on("change",function(){
								var tags = $(this).val();
								$("#icomment").val(tags);
							});
						});
					</script>
				<?php } ?>
                <div class="form-group">
                    <?= lang('comment', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control skip" id="icomment" style="height:80px;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
			
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
		$("#csmbiller").change(biller); biller();
		function biller(){
			var biller = $("#csmbiller").val();
			var project = 0;
			$.ajax({
				url : "<?= admin_url("sales/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
	});
</script>	