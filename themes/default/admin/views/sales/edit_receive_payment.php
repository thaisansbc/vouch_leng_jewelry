<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_receive_payment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("sales/edit_receive_payment/".$receive_payment->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<?php if ($Owner || $Admin || $GP['sales-receive_payments-date']) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("date", "rpdate"); ?>
									<?php echo form_input('date', $this->bpas->hrld($receive_payment->date), 'class="form-control input-tip datetime" id="rpdate" required="required"'); ?>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "rpref"); ?>
                                <?php echo form_input('reference_no', $receive_payment->reference_no, 'class="form-control input-tip" id="rpref"'); ?>
                            </div>
                        </div>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "rpbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, $receive_payment->biller_id, 'id="rpbiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'rpbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						<div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-2">
										<div class="form-group">
											<?= lang("from_date", "rpfrom_date"); ?>
											<?php echo form_input('from_date', $this->bpas->hrsd($receive_payment->from_date), 'class="form-control input-tip datetime" id="rpfrom_date" required="required"'); ?>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<?= lang("to_date", "rpto_date"); ?>
											<?php echo form_input('to_date', $this->bpas->hrsd($receive_payment->to_date), 'class="form-control input-tip datetime" id="rpto_date" required="required"'); ?>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<?= lang("type", "rtype"); ?>
											<?php
												$opt_type[""] = lang("select")." ".lang("type");
												$opt_type["sale"] = lang("sale");
												$opt_type["pos"] = lang("pos");
												echo form_dropdown('type', $opt_type, $receive_payment->type, 'id="rtype" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("type") . '"  class="form-control input-tip select"');
											?>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<?= lang("received_by", "rpreceived_by"); ?>
											<?php
												$ct_opt[""] = lang("select")." ".lang("received_by");
												if($users){
													foreach($users as $received_by){
														$ct_opt[$received_by->id] = $received_by->last_name." ".$received_by->first_name;
													}
												}
												echo form_dropdown('received_by', $ct_opt, $receive_payment->received_by, 'id="rpreceived_by" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("received_by") . '"  class="form-control input-tip select"');
											?>
										</div>
									</div>
						
									<div class="col-sm-2">
										<div class="form-group">
											<?= lang("paid_by", "rppaid_by"); ?>
											<?php
												$paid_by_opt = false;
												if($cash_accounts){
													foreach($cash_accounts as $cash_account){
														$paid_by_opt[$cash_account->id] = $cash_account->name;
													}
												}
												echo form_dropdown('paid_by[]', $paid_by_opt, ($receive_payment->paid_by ? json_decode($receive_payment->paid_by) : ""), 'id="rppaid_by" multiple class="form-control input-tip select"');
											?>
										</div>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="conTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
										<thead>
											<tr>
												<th><?= lang("received_by") ?></th>
												<th><?= lang("date") ?></th>
												<th><?= lang("sale_ref") ?></th>
												<th><?= lang("payment_ref") ?></th>
												<th><?= lang("customer") ?></th>
												<th><?= lang("paid_by") ?></th>
												<th><?= lang("amount") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataDel">
											<?php
												$tbody = "";
												if($receive_payment_items){
													foreach($receive_payment_items as $row){
														$tbody.="<tr class='payment_link_last' id='".$row->payment_id."'>
																	<td class='text-left'><input name='payment_id[]' class='payment_id'  value='".$row->payment_id."' type='hidden'/><input name='payment_created_by[]'  value='".$row->payment_created_by."' type='hidden'/>".$row->payment_created_by."</td>
																	<td class='text-center'><input name='payment_date[]' type='hidden' value='".$row->payment_date."'/>".$this->bpas->hrld($row->payment_date)."</td>
																	<td class='text-center'><input name='sale_ref[]' type='hidden' value='".$row->sale_ref."'/>".$row->sale_ref."</td>
																	<td class='text-center'><input name='payment_ref[]' type='hidden' value='".$row->payment_ref."'/>".$row->payment_ref."</td>
																	<td class='text-center'><input name='customer[]' type='hidden' value='".$row->customer."'/>".$row->customer."</td>
																	<td class='text-center'><input name='payment_paid_by[]' type='hidden' value='".$row->payment_paid_by."'/>".$row->payment_paid_by."</td>
																	<td class='text-right'><input name='payment_amount[]' type='hidden' class='amount' value='".$row->payment_amount."'/>".$this->bpas->formatMoney($row->payment_amount)."</td>
																	<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>
																</tr>";
													}
												}
												echo $tbody;
											?>
										</tbody>
                                        <tfoot>
											<tr>
												<th class="text-right" colspan="6"><?= lang("total") ?></th>
												<th class="text-right" id="total"></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						<div class="clearfix"></div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="form-group">
									<?= lang("note", "rpnote"); ?>
									<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="rpnote" style="margin-top: 10px; height: 100px;"'); ?>
								</div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('edit_receive_payment', $this->lang->line("submit"), 'id="edit_receive_payment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
		<div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
			<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
				<tr class="warning">
					<td><?= lang('items') ?> : <span class="totals_val pull" id="titems">0</span></td>
					<td><?= lang('total') ?> : <span class="totals_val pull" id="ttotal">0.00</span></td>
				</tr>
			</table>
		</div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$(document).on("change", "#rpbiller, #rpreceived_by, #rpfrom_date, #rpto_date, #rppaid_by, #rtype", function () {	
			getConSales();
		});
		function getConSales(){
			var biller_id = $("#rpbiller").val();
			var from_date = $("#rpfrom_date").val();
			var to_date = $("#rpto_date").val();
			var received_by = $("#rpreceived_by").val();
			var paid_by = $("#rppaid_by").val();
			var type = $("#rtype").val();
			if(biller_id && from_date && to_date){
				$.ajax({
					type: "get", 
					async: true,
					url: site.base_url + "sales/get_sale_payments/",
					data : { 
							biller_id : biller_id,
							from_date : from_date,
							to_date : to_date,
							received_by : received_by,
							paid_by : paid_by,
							type : type,
							receive_id : <?= $receive_payment->id ?>
					},
					dataType: "json",
					success: function (data) {
						var dataDel = "";
						if (data != false) {
							$.each(data, function () {
								dataDel += "<tr class='payment_link_last' id='"+this.id+"'>";
									dataDel += "<td class='text-left'><input name='payment_id[]' class='payment_id'  value='"+this.id+"' type='hidden'/><input name='payment_created_by[]'  value='"+this.created_by+"' type='hidden'/>"+this.created_by+"</td>";
									dataDel += "<td class='text-center'><input name='payment_date[]' type='hidden' value='"+this.date+"'/>"+fld(this.date)+"</td>";
									dataDel += "<td class='text-center'><input name='sale_ref[]' type='hidden' value='"+this.sale_ref+"'/>"+this.sale_ref+"</td>";
									dataDel += "<td class='text-center'><input name='payment_ref[]' type='hidden' value='"+this.payment_ref+"'/>"+this.payment_ref+"</td>";
									dataDel += "<td class='text-left'><input name='customer[]' type='hidden' value='"+this.customer+"'/>"+this.customer+"</td>";
									dataDel += "<td class='text-left'><input name='payment_paid_by[]' type='hidden' value='"+this.paid_by+"'/>"+this.paid_by+"</td>";
									dataDel += "<td class='text-right'><input name='payment_amount[]' value='"+this.amount+"' type='hidden' class='amount'/>"+formatMoney(this.amount)+"</td>";
									dataDel += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";
								dataDel += "</tr>";
							});
						}
						$("#dataDel").html(dataDel);
						loadItems();
					}
				});
			}else{
				$("#dataDel").html("");
				loadItems();
			}
		}
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
			loadItems();
		});
		loadItems();
		function loadItems(){
			var total = 0;
			var total_amount = 0;
			$(".payment_id").each(function(){
				var row = $(this).closest('tr');
				var amount = row.find(".amount").val() - 0;
				total_amount += amount;
				total++;
			});
			$('#ttotal').text(formatMoney(total_amount));
			$('#titems').text(total);
			$('#total').text(formatMoney(total_amount));
		}

	});
</script>