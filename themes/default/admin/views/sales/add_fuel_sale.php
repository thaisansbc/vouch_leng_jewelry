<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
	var count = 1, an = 1, total = 0, edit_price = 0;
	<?php if($Admin || $Owner || $GP['edit_price']){ ?>
		edit_price = 1;
	<?php } ?>
	$(document).ready(function () {
		 <?php if($this->session->userdata('remove_shls')) { ?>
			if (localStorage.getItem('shitems')) {
				localStorage.removeItem('shitems');
			}
			if (localStorage.getItem('shref')) {
				localStorage.removeItem('shref');
			}
			if (localStorage.getItem('shdate')) {
				localStorage.removeItem('shdate');
			}
			if (localStorage.getItem('shsaleman')) {
				localStorage.removeItem('shsaleman');
			}
			if (localStorage.getItem('shwarehouse')) {
				localStorage.removeItem('shwarehouse');
			}
			if (localStorage.getItem('shnote')) {
				localStorage.removeItem('shnote');
			}
			if (localStorage.getItem('shtime')) {
				localStorage.removeItem('shtime');
			}
			if (localStorage.getItem('shkh_rate')) {
				localStorage.removeItem('shkh_rate');
			}
        <?php $this->bpas->unset_data('remove_shls'); } ?>
		
		<?php if (isset($fuel_items) && $fuel_items!==false) { ?>
			localStorage.setItem('shitems', JSON.stringify(<?=$fuel_items;?>));
		<?php }else{ ?>
			localStorage.removeItem('shitems');
			localStorage.removeItem('shref');
			localStorage.removeItem('shdate');
			localStorage.removeItem('shsaleman');
			localStorage.removeItem('shwarehouse');
			localStorage.removeItem('shnote');
			localStorage.removeItem('shtime');
			localStorage.removeItem('shkh_rate');
		<?php } ?>
		
		$(document).on('change', '#shsaleman', function (e) {
            localStorage.setItem('shsaleman', $(this).val());
        });
        if (shsaleman = localStorage.getItem('shsaleman')) {
			$('#shsaleman').select2("val", shsaleman);
        }
		$(document).on('change', '#shwarehouse', function (e) {
            localStorage.setItem('shwarehouse', $(this).val());
        });
        if (shwarehouse = localStorage.getItem('shwarehouse')) {
			$('#shwarehouse').select2("val", shwarehouse);
        }
        $(document).on('change', '#shtime', function (e) {
            localStorage.setItem('shtime', $(this).val());
        });
        if (shtime = localStorage.getItem('shtime')) {
			$('#shtime').select2("val", shtime);
        }
		$(document).on('change', '#shkh_rate', function (e) {
            localStorage.setItem('shkh_rate', $(this).val());
        });
        if (shkh_rate = localStorage.getItem('shkh_rate')) {
			$('#shkh_rate').val();
        }
		ItemnTotals();
		$("#add_item").autocomplete({
            source: function (request, response) {
				if (!$('#shsaleman').val() || !$('#shwarehouse').val()) {
					bootbox.alert('<?=lang('select_above');?>');
					$('#shsaleman').focus();
					return false;
				}
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('sales/suggesion_fuel_sale'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
						salesman_id: $("#shsaleman").val(),
						warehouse_id: $("#shwarehouse").val()
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
                    var row = add_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
		
	});
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_fuel_sale'); ?></h2>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("sales/add_fuel_sale", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['sales-fuel_sale-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "shdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="shdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "soref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="shref"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "shbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="shbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'shbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						
						<?php if($Settings->project == 1){ ?>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
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
								<div class="col-md-4">
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
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("warehouse", "shwarehouse"); ?>
								<?php
								if($warehouses){
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name;
									}
								}
								echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="shwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("kh_rate", "shkh_rate"); ?>
								<?php echo form_input('kh_rate', (isset($_POST['kh_rate']) ? $_POST['kh_rate'] : $kh_rate), 'class="form-control input-tip" id="shkh_rate"'); ?>
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<?php if ($this->Settings->sale_man) { ?>
										<div class="col-md-4">
											<div class="form-group">
											<?= lang("saleman", "shsaleman"); ?>
											<?php 
												$opsalemans[""] = lang('select').' '.lang('saleman');
												foreach($salemans as $saleman){
													$opsalemans[$saleman->id] = $saleman->first_name .' '.$saleman->last_name;
												}
											?>
											<?= form_dropdown('saleman_id', $opsalemans, (isset($_GET['saleman_id'])?$_GET['saleman_id']:0), ' id="shsaleman" class="form-control" required="required"'); ?>
											</div>
										</div>
									<?php } ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("time", "shtime"); ?>
											<?php
											$optt[""] = "";
											if($times){
												foreach ($times as $time) {
													$optt[$time->id] = $time->open_time.' - '.$time->close_time;
												}
											}
											echo form_dropdown('time_id', $optt, (isset($_POST['time_id']) ? $_POST['time_id'] : $fuel_time), 'id="shtime" required data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("time") . '" class="form-control input-tip select" style="width:100%;"');
											?>
										</div>
									</div>
                                </div>
                            </div>
                        </div>
						<div class="col-md-12" id="sticker">
							<div class="well well-sm">
								<div class="form-group" style="margin-bottom:0;">
									<div class="input-group wide-tip">
										<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
											<i class="fa fa-2x fa-barcode addIcon"></i></a></div>
										<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_tank_to_order") . '"'); ?>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
                        <div class="clearfix"></div>
						<div class="col-sm-12">
							<div class="control-group table-group">
                                <label class="table-label"><?= lang("nozzle_no"); ?> *</label>
								<div class="controls table-controls">
									<table id="glTable" class="table table-bordered table-striped" style="margin-top:10px;">
										<thead>
											<tr>
												<th><?= lang("tank") ?></th>
												<th><?= lang("nozzle_no") ?></th>
												<th><?= lang("start_no") ?></th>
												<th><?= lang("end_no") ?></th>
												<th><?= lang("unit_price") ?></th>
												<th><?= lang("using_qty") ?></th>
												<th><?= lang("customer_qty") ?></th>
												<th><?= lang("customer_amount") ?></th>
												<th><?= lang("quantity") ?></th>
												<th><?= lang("subtotal") ?></th>
												<th><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
										<tbody></tbody>
										<tfoot></tfoot>
									</table>
								</div>
							</div>
						</div>
						<input type="hidden" name="total_items" value="" id="total_items" required="required"/>
						<div class="row" id="bt">
                            <div class="col-md-12">
								<div class="col-md-6">
									<div class="form-group">
										<?= lang("document", "document") ?>
										<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false"
											   data-show-preview="false" class="form-control file">
									</div>
									<div class="form-group">
                                        <?= lang("note", "shnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="shnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
								</div>
								<div class="col-md-6">
									<?= lang("credit_amount", "credit_amount"); ?>
									<div class="form-group">
									<table class="count-money-table" style="width:100%">
										<td class="text-center">$</td>
										<td><input value="0" name="credit_amount_usd" class="form-control credit_amount_usd text-right"/></td>
										<td class="text-center">៛</td>
										<td><input value="0" name="credit_amount_khr" class="form-control credit_amount_khr text-right"/></td>
									</table>
									</div>
								</div>
								<div class="col-md-6">
									<?= lang("bank_amount", "bank_amount"); ?>
									<div class="form-group">
									<table class="count-money-table" style="width:100%">
										<td class="text-center">$</td>
										<td><input value="0" name="bank_amount_usd" class="form-control bank_amount_usd text-right"/></td>
										<td class="text-center">៛</td>
										<td><input value="0" name="bank_amount_khr" class="form-control bank_amount_khr text-right"/></td>
									</table>
									</div>
								</div>
								<div class="col-md-6">
									<label class="table-label"><?= lang("cash_change"); ?></label>
									<div class="form-group">
										<table class="count-money-table" style="width:100%">
											<?php
												$moneys_USD = array("100","50","20","10","5","1","-1","-2","-3","-4");
												$moneys_KHR = array("100000","50000","20000","10000","15000","5000","2000","1000","500","100");
												$smoney_changes = array();
												if($money_changes){
													foreach($money_changes as $money_change){
														$smoney_changes[$money_change->code] = $money_change->amount;
													}
												}
												if($moneys_KHR){
													$change_USD = isset($smoney_changes['USD']) && $smoney_changes['USD'] ? $smoney_changes['USD'] : 0 ;
													$change_KHR = isset($smoney_changes['KHR']) && $smoney_changes['KHR'] ? $smoney_changes['KHR'] : 0 ;
													echo '<tr>';
														if($moneys_USD){
															echo '
																<td class="text-center">$</td>
																<td>
																	<input name="change_usd" class="form-control change-money-usd text-right" autocomplete="off" value="'.$change_USD.'" placeholder="'.lang("USD").'" />
																</td>';
														}
														if($moneys_KHR){
															echo '
																<td class="text-center">៛</td>
																<td>
																	<input name="change_kh" class="form-control change-money-kh text-right" autocomplete="off" value="'.$change_KHR.'" placeholder="'.lang("KHR").'" />
																</td>';
														}
													echo '</tr>';
												}
											?>
										</table>
									</div>
								</div>
								<div class="col-md-6">
									<label class="table-label"><?= lang("cash_submit"); ?> * </label>
									<table style="width:100%" class="count-money-table">
										<?php
											if($moneys_KHR && $moneys_USD){
												foreach($moneys_KHR as $k=> $money){
													echo '<tr>';
														if($moneys_USD){
															if($moneys_USD[$k] <= 0){
																echo '<td style="width:120px;"><button style="width:100%;" class="text-right" value="'.$moneys_USD[$k].'" onClick="return false;">'.$this->bpas->formatDecimal(0).'</button></td>';
															}else{
																echo '<td style="width:120px;"><button style="width:100%;" class="btn-money-usd text-right" value="'.$moneys_USD[$k].'" onClick="return false;">'.$this->bpas->formatDecimal($moneys_USD[$k]).'</button></td>';
															}
															echo '<td style="width:120px;"><input type="number" min=0 name="count-money-usd['.$moneys_USD[$k].']" value="0" class="count-money-usd input-sm form-control" /></td>';
															echo '<td style="width:120px;"><button style="width:100%;" class="val-money-usd text-right" onClick="return false;">0</button></td>';
														}
														if($moneys_KHR){
															echo '<td style="width:120px;"><button style="width:100%;" class="btn-money-kh text-right" value="'.$money.'" onClick="return false;">'.$this->bpas->formatDecimal($money,-1).'</button></td>';
															echo '<td style="width:120px;"><input type="number" min=0 name="count-money-kh['.$money.']" value="0" class="count-money-kh input-sm form-control" /></td>';
															echo '<td style="width:120px;"><button style="width:100%;" class="val-money-kh text-right" onClick="return false;">0</button></td>';
														}
													echo '</tr>';
												}
												echo '<tr>';
													if($moneys_USD){
														echo '<td class="text-right">$</td>';
														echo '<td colspan="2" style="font-size:18px;"><input type="text" name="total_USD" id="total_USD" class="form-control cash_submit_usd" value="'.$this->bpas->formatDecimal(0).'" style="text-align:right;"/></td>';
													}
													if($moneys_KHR){
														echo '<td class="text-right">៛</td>';
														echo '<td colspan="2" style="font-size:18px;"><input type="text" name="total_KHR" id="total_KHR" class="form-control cash_submit_khr" value="'.$this->bpas->formatDecimal(0).'" style="text-align:right;"/></td>';
													}
												echo '</tr>';
											}
										?>
									</table>
								</div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('add_fuel_sale', $this->lang->line("submit"), 'id="add_fuel_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
							</div>
						</div>
                </div>
				<div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> : <span class="totals_val" id="titems">0</span></td>
							<td class="text-right"><?= lang('total') ?> : <span class="totals_val" id="total">0.00</span></td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
	.count-money-table td{
		padding:2px;
	}
	.count-money-table button{
		padding:3px;
	}
</style>
<script type="text/javascript">
	$(function(){
		
		$("#shbiller").change(biller); biller();
		function biller(){
			var biller = $("#shbiller").val();
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

		//========USD=========//
		
		$(".btn-money-usd").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-usd").val()-0;
			if(count >= 0){
				row.find(".count-money-usd").val(count+1);
				$(".count-money-usd").change();
			}
		});
		$(".val-money-usd").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-usd").val()-0;
			if(count > 0){
				row.find(".count-money-usd").val(count-1);
				$(".count-money-usd").change();
			}
		});
		$(".count-money-usd").on("change",function(){
			var row = $(this).closest("tr");
			var money = row.find(".btn-money-usd").attr("value")-0;
			var count = row.find(".count-money-usd").val()-0;
				row.find(".val-money-usd").text(formatDecimal(money * count));
				cal_total();
		});
		
		//========KH=========//

		$(".btn-money-kh").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-kh").val()-0;
			if(count >= 0){
				row.find(".count-money-kh").val(count+1);
				$(".count-money-kh").change();
			}
		});
		$(".val-money-kh").on("click",function(){
			var row = $(this).closest("tr");
			var count = row.find(".count-money-kh").val()-0;
			if(count > 0){
				row.find(".count-money-kh").val(count-1);
				$(".count-money-kh").change();
			}
		});
		$(".count-money-kh").on("change",function(){
			var row = $(this).closest("tr");
			var money = row.find(".btn-money-kh").attr("value")-0;
			var count = row.find(".count-money-kh").val()-0;
				row.find(".val-money-kh").text(formatDecimal(money * count));
				cal_total();
		});

		//========Calc=========//

		function cal_total(){
			var total = 0;
			$(".count-money-usd").each(function(){
				var row = $(this).closest("tr");
				var money_usd = row.find(".val-money-usd").text()-0;
				total += money_usd;
			});
			$("#total_USD").val(total);
			
			var total_kh = 0;
			$(".count-money-kh").each(function(){
				var row = $(this).closest("tr");
				var money_kh = row.find(".val-money-kh").text()-0;
				total_kh += money_kh;
			});
			$("#total_KHR").val(total_kh);
		}
		
		$("#shsaleman").on("change",function(){
			var saleman = $(this).val();
			if(saleman){
				window.location = "<?= admin_url("sales/add_fuel_sale/?saleman_id=") ?>" + saleman;
			}else{
				window.location = "<?= admin_url("sales/add_fuel_sale") ?>";
			}
			return false;
		});
		
		$("#shdate").datetimepicker({
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
	});
</script>