<script type="text/javascript">
	var count = 1;
    $(document).ready(function () {
		<?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
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
        $('#customer').val(<?= $this->input->post('customer') ?>);
        <?php } ?>
		if (localStorage.getItem('usitems')) {
			localStorage.removeItem('usitems');
		}
		if (localStorage.getItem('finishitems')) {
			localStorage.removeItem('finishitems');
		}
		if (localStorage.getItem('from_location')) {
			localStorage.removeItem('from_location');
		}
		if (localStorage.getItem('authorize_id')) {
			localStorage.removeItem('authorize_id');
		}
		if (localStorage.getItem('employee_id')) {
			localStorage.removeItem('employee_id');
		}
		if (localStorage.getItem('shop')) {
			localStorage.removeItem('shop');
		}
		if (localStorage.getItem('account')) {
			localStorage.removeItem('account');
		}
		$("#add_item").autocomplete({
            source: function (request, response) {
				$.ajax({
					type: 'get',
					url: '<?= admin_url('products/suggestionsStock'); ?>',
					dataType: "json",
					data: {
						term: request.term,
						warehouse_id: $("#from_location").val(),
						plan: $("#plan").val()
					},
					success: function (data) {
						response(data);
					}
				});
            },
			minLength: 1,
            autoFocus: false,
            delay: 200,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_using_stock_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
		$('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });
		$("#add_finish_item").autocomplete({
			source: function (request, response) {
				$.ajax({
					type: 'get',
					url: '<?= admin_url('products/suggestionsStock'); ?>',
					dataType: "json",
					data: {
						term: request.term,
						warehouse_id: $("#from_location").val(),
						plan: $("#plan").val()
					},
					success: function (data) {
						response(data);
					}
				});
			},
			minLength: 1,
			autoFocus: false,
			delay: 200,
			response: function (event, ui) {
				if ($(this).val().length >= 16 && ui.content[0].id == 0) {
					bootbox.alert('<?= lang('no_match_found') ?>', function () {
						$('#add_finish_item').focus();
					});
					$(this).val('');
				}
				else if (ui.content.length == 1 && ui.content[0].id != 0) {
					ui.item = ui.content[0];
					$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
					$(this).autocomplete('close');
					$(this).removeClass('ui-autocomplete-loading');
				}
				else if (ui.content.length == 1 && ui.content[0].id == 0) {
					bootbox.alert('<?= lang('no_match_found') ?>', function () {
						$('#add_finish_item').focus();
					});
				}
			},
			select: function (event, ui) {
				event.preventDefault();
				if (ui.item.id !== 0) {
					var row = add_using_stock_finish_item(ui.item);
					if (row)
						$(this).val('');
				} else {
					bootbox.alert('<?= lang('no_match_found') ?>');
				}
			}
		});
		$('#add_finish_item').bind('keypress', function (e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				$(this).autocomplete("search");
			}
		});
		$("#date").datetimepicker({
			format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
		}).datetimepicker('update', '<?= $using_stock->date; ?>');
		$("#address").select2("destroy").empty().attr("placeholder", "<?= lang('select_plan_to_load') ?>").select2({
            placeholder: "<?= lang('select_plan_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_plan_to_load') ?>'}
            ]
        });
		$('#plan').change(function () {
			var v = $(this).val();
            $('#modal-loading').show();
			if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= admin_url('products/getAddress') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#address").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_plan_to_load') ?>",
                                data: scdata
                            });
                        }else{
							$("#address").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_plan_to_load') ?>",
                                data: 'not found'
                            });
						}
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#address").select2("destroy").empty().attr("placeholder", "<?= lang('select_plan_to_load') ?>").select2({
                    placeholder: "<?= lang('select_plan_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_plan_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
		});
		$('#plan').trigger('change');
		$("#return_reference_no").attr('readonly', true);
		$("#ref_st").on('ifChanged', function() {
		  if ($(this).is(':checked')) {
			$("#return_reference_no").prop('readonly', false);
			$("#return_reference_no").val("");
		  }else{
			$("#return_reference_no").prop('readonly', true);
			var temp = $("#temp_reference_no_return").val();
			$("#return_reference_no").val(temp);
			
		  }
		});	
		
    });
	<?php if($items){?>
		// localStorage.setItem('usitems', '<?= $items; ?>');
		if(!localStorage.getItem('usitems')){
			localStorage.setItem('usitems', '<?= $items; ?>');
		}
		if(!localStorage.getItem('finishitems')){
			localStorage.setItem('finishitems', '<?= $items_finish; ?>');
		}
		localStorage.setItem('from_location', '<?= isset($where)?$where:null; ?>');
		localStorage.setItem('authorize_id', '<?= $using_stock->authorize_id; ?>');
		localStorage.setItem('employee_id', '<?= $using_stock->employee_id; ?>');
		localStorage.setItem('shop', '<?= $using_stock->shop; ?>');
		localStorage.setItem('account', '<?= $using_stock->account; ?>');
		localStorage.setItem('plan', '<?= $using_stock->plan_id; ?>');
	<?php } ?>
</script>
<?php echo admin_form_open("products/return_using_stock/". $id); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
			<i class="fa fa-reply"></i><?= lang('return_stock_using'); ?> 
		</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<input type="hidden"  name="stock_id"  id="stock_id" value="<?=$using_stock->id?>" />
				<div class="clearfix"></div>
				<div class="row">
					<div class="col-md-4">
						<?php if ($Owner || $Admin || $Settings->allow_change_date == 1) { ?>
							<div class="form-group">
								<?= lang('date', 'date'); ?>
								<?= form_input('date', $this->bpas->hrld($using_stock->date), 'class="form-control tip datetime" required id="date" autocomplete=off'); ?>
							</div>
						<?php } ?>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang('reference_no', 'reference_no'); ?>
							<?= form_input('reference_no', $using_stock->reference_no, 'class="form-control tip"  required  id="reference_no" style="pointer-events:none;"'); ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang('return_reference_no', 'reference_no'); ?>
							<div class="input-group">  
								<?= form_input('return_reference_no', isset($ref_return)?$ref_return:'', 'class="form-control tip" id="return_reference_no"'); ?>
								<input type="hidden"  name="temp_reference_no_return"  id="temp_reference_no_return" value="<?= $ref_return ?>" />
								<input type="hidden"  name="ref_prefix"  id="ref_prefix" value="esr" />
								<div class="input-group-addon no-print" style="padding: 2px 5px;background-color:white;">
									<input type="checkbox" name="ref_status" id="ref_st" value="1" style="margin-top:3px;">
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang('biller', 'biller'); ?>
							<?php
							 	foreach ($biller as $bl) {
                                    $billers[$bl->id] = $bl->company;
                                }
                            	echo form_dropdown('biller', $billers, $using_stock->biller_id, 'class="form-control" required id="biller" placeholder="' . lang("select") . ' ' . lang("biller") . '" style="width:100%;pointer-events:none;"')
                            ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group all">
                            <?= lang("warehouse", "from_location") ?>
                            <?php
								$wh[""]="";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->code .'-'. $warehouse->name;
                                }
								echo form_dropdown('from_location', $wh, $using_stock->warehouse_id, 'class="form-control"   required  id="from_location" placeholder="' . lang("select") . ' ' . lang("location") . '" style="width:100%;pointer-events:none;"')
                            ?>
                        </div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang('authorize_by', 'authorize_by'); ?>
							<?php
                                foreach ($authorize_by as $au) {
                                    $authorize[$au->id] = $au->username;
                                }
								echo form_dropdown('authorize_id', $authorize, $using_stock->authorize_id, 'class="form-control"  required  id="authorize_id" placeholder="' . lang("select") . ' ' . lang("authorize_id") . '" style="width:100%"')
                            ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang('employee', 'employee'); ?>
							<?php
                                foreach ($employees as $epm) {
                                    $em[$epm->id] = $epm->fullname;
                                }
                            	echo form_dropdown('employee_id', $em, $using_stock->employee_id, 'class="form-control"    id="employee_id" placeholder="' . lang("select") . ' ' . lang("employee") . '" style="width:100%"')
                            ?>
						</div>
					</div>
					<?php if ($this->Settings->project) { ?>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang('plan', 'plan'); ?>
							<?php
								$pl[""] = "";
								foreach ($plan as $pplan) {
                                    $pl[$pplan->id] = $pplan->title;
                                }
								echo form_dropdown('plan', $pl, $using_stock->plan_id, 'class="form-control"  id="plan" placeholder="' . lang("select") . ' ' . lang("plan") . '" style="width:100%; pointer-events:none;"')
                            ?>
						</div>
					</div>
					<?php } ?>
					<div class="col-md-4 hide">
						<div class="form-group all">
                            <?= lang("address", "address") ?>
                            <?php echo form_input('address', $using_stock->address_id, 'class="form-control" id="address"  placeholder="' . lang("select_plan_to_load") . '"'); ?>
                        </div>
					</div>
					<div class="col-md-4 hide">
						<div class="form-group">
							<?= lang('customer', 'customer'); ?>
							<?php echo form_input('customer', '', 'id="customer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" class="form-control input-tip" style="min-width:100%;"'); ?>
						</div>
					</div>
					<div class="col-md-4">			
						<div class="form-group hide">
							<?= lang('account', 'account'); ?>
							<?php
								$gl[""] = "";
                                foreach ($accounting as $acc) {
                                    $gl[$acc->accountcode] = $acc->accountcode.' - '.$acc->accountname;
                                }
                            	echo form_dropdown('account', $gl, $using_stock->account, 'class="form-control"    id="account" placeholder="' . lang("select") . ' ' . lang("account") . '" style="width:100%"')
                            ?>
						</div>
					</div>
				</div>	
				<div class="row">
					<div class="col-md-12 pr_form" id="sticker">
						<div class="well well-sm">
							<div class="form-group" style="margin-bottom:0;">
								<div class="input-group wide-tip">
									<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
									<i class="fa fa-2x fa-barcode addIcon"></i></div>
									<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 pr_form">
						<div class="table-responsive">
							<table id="UsData" class="table table-bordered table-hover table-striped table-condensed reports-table">
								<thead>
									<tr>
										<th style="width:30% !important;">
											<span><?= lang("product"); ?></span>
										</th>
										<?php if ($Settings->product_expiry) {
                                            echo '<th style="width:14% !important;">' . $this->lang->line("expiry_date") . '</th>';
                                        } ?>
										<th style="width:10% !important;"><?= lang("description"); ?></th>
										<th style="width:8% !important;"><?= lang("QOH"); ?></th>
										<th style="width:8% !important;"><?= lang("qty_use"); ?></th>
										<th style="width:10% !important;"><?= lang("unit_variant"); ?></th>
										<th style="width:10% !important;"><?= lang('project_qty'); ?></th>
										<th style="width:2% !important;"><i class="fa fa-trash-o" aria-hidden="true"></i></th>
									</tr>
								</thead>
								<tbody class="tbody"></tbody>
							</table>
						</div>
					</div>
				</div>
				 <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_finish_item', '', 'class="form-control input-lg" id="add_finish_item" placeholder="' . lang("add_finished_good_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
						
						<div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("finished_good"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="PFinish" class="table table-bordered table-hover table-striped table-condensed reports-table">
										<thead>
											<tr>
												<th style="width:30% !important;"><span><?= lang("product"); ?></span></th>
												<?php if ($Settings->product_expiry) {
													echo '<th style="width:14% !important;">' . $this->lang->line("expiry_date") . '</th>';
												} ?>
												<th style="width:10% !important;"><?= lang("description"); ?></th>
												<th style="width:8% !important;"><?= lang("QOH"); ?></th>
												<th style="width:8% !important;"><?= lang("qty_use"); ?></th>
												<th style="width:10% !important;"><?= lang("unit_variant"); ?></th>
												<th style="width:10% !important;"><?= lang('project_qty'); ?></th>
												<th style="width:2% !important;"><i class="fa fa-trash-o" aria-hidden="true"></i></th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
                                </div>
                            </div>
                        </div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group all">
							<?= lang("note", "note") ?>
							<?= form_textarea('note',$using_stock->note, 'class="form-control" id="note"'); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="fprom-group">
							<input type="hidden"  name="total_item_cost" required id="total_item_cost" class=" form-control total_item_cost" value="">
							<input type="hidden" value="" name="store_del_pro_id" id="store_del_pro_id"/>
							<?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary" style="display:none;" id="btn_submit"'); ?>
							<button type="button" name="submit_report" class="btn btn-primary" id="btn_using"><?= lang('submit') ?></button>
							<button type="button" name="convert_items" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>
            </div>
        </div>
    </div>
	   <div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
        <div class="modal-dialog" style="width:70%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                        <i class="fa fa-2x">&times;</i></span>
                        <span class="sr-only"><?=lang('close');?></span>
                    </button>
                    <h4 class="modal-title" id="comboModalLabel"></h4>
                </div>
                <div class="modal-body" style="margin-top:-15px !important;">
                    <label class="table-label"><?= lang("combo_products"); ?></label>
                    <table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                        <input type="hidden" id="row_id" value="" />
                        <thead>
                            <tr>
                                <th width="10px"><?= lang('type'); ?></th>
                                <th width="200px"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                <?php if ($Settings->qty_operation) { ?>
                                    <th><?= lang('width') ?></th>
                                    <th><?= lang('height') ?></th>
                                <?php } ?>
                                <th><i class="fas fa-gem"></i><br><?= lang('wax_setting') ?></th>
                                <th><i class="fas fa-fire"></i><br><?= lang('casting') ?></th>
                                <th><i class="fas fa-tools"></i><br><?= lang('filing_pre_polishing') ?></th>
                                <th><i class="fas fa-gem"></i><br><?= lang('stone_setting') ?></th>
                                <th><i class="fas fa-star"></i><br><?= lang('final_polishing') ?></th>
                                <th><i class="fas fa-search"></i><br><?= lang('quality_inspection') ?></th>
                                <th><i class="fas fa-box"></i><br><?= lang('packaging') ?></th>
                                <th><?= lang('price') ?></th>
                                <th width="3%">
                                    <a id="add_comboProduct" class="btn btn-sm btn-primary hide"><i class="fa fa-plus"></i></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
                </div>
            </div>
        </div>
    </div>
	<?php
		$units[""] = "";
		foreach ($all_unit as $getunits) {
			$units[$getunits->id] = $getunits->name;
		}
		$dropdown = form_dropdown("purchase_type", $units, '', 'id="purchase_type"  class="form-control input-tip select" style="width:100%;"');
	?>
</div>
<?php $unit_option ='';
foreach ($all_unit as $getunits) {
	$unit_option.= '<option value='.$getunits->id.'>'.$getunits->name.'</option>';
} ?>
<script>
	$(document).ready(function () {
		$(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
            $(this).autocomplete({
                source: '<?= admin_url('products/using_suggestions/'.$id); ?>',
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
                        var parent = $(this).parent().parent();
                        parent.find(".combo_product_id").val(ui.item.id);
                        parent.find(".combo_name").val(ui.item.name);
                        parent.find(".combo_code").val(ui.item.code);
                        parent.find(".combo_price").val(formatDecimal(ui.item.price));
                        parent.find(".combo_qty").val(formatDecimal(1));
                        if (site.settings.qty_operation == 1) {
                            parent.find(".combo_width").val(formatDecimal(1));
                            parent.find(".combo_height").val(formatDecimal(1));
                        }
                        $(this).val(ui.item.label);
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });
	});
	</script>