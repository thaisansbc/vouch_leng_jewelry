<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
	#dtFilter-filter--Repayment_table-1, 
	#dtFilter-filter--Repayment_table-2, 
	#dtFilter-filter--Repayment_table-11, 
	#dtFilter-filter--Repayment_table-12, 
	#dtFilter-filter--Repayment_table-13 { text-align: center !important; }
</style>
<ul id="myTab" class="nav nav-tabs">
	<?php if($Admin || $Owner || $GP['installments-payments']){ ?>
		<li class=""><a href="#repayments" class="tab-grey"><?= lang('repayments') ?></a></li>
	<?php } ?>
	<li class=""><a href="#transactions" class="tab-grey"><?= lang('transactions') ?></a></li>
	<?php if($Admin || $Owner || $GP['installments-edit']){ ?>
	<li class=""><a href="#assignations" class="tab-grey"><?= lang('assignations') ?></a></li>
	<?php } ?>
</ul>
<div class="tab-content">
    <div id="repayments" class="tab-pane fade in">
		<script>
			$(document).ready(function () {
				oTable = $('#Repayment_table').dataTable({
					"aaSorting": [[1, "asc"]],
					"aLengthMenu": [[-1], ["<?= lang('all') ?>"]],
					"iDisplayLength": -1,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= admin_url('installments/getRepayments/?id='.$id) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [
						{"bSortable": false,"mRender": checkbox},
						{"sClass":"center", "bSortable": false}, 
						{"mRender":fsd, "sClass":"center"},
						{"mRender":currencyFormat},
						{"mRender":currencyFormat}, 
						{"mRender":currencyFormat}, 
						{"mRender":currencyFormat},
						{"mRender":currencyFormat},
						{"mRender":currencyFormat},
						{"mRender":currencyFormat},
						{"mRender":currencyFormat},
						{"mRender":row_status},
						{"mRender":row_status},				
						{"bSortable": false <?php if($installment->status  == 'inactive'){ echo ', "bVisible":false '; } ?>}],
					'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						nRow.id = aData[0];
						var action = $('td:eq(13)', nRow);
						if(aData[12] == 'pending') {
							action.find(".view-payment").remove();
						}
						if(aData[12] == 'payoff') {
							action.find(".add-payment").remove();
							action.find(".view-payment").remove();
						}
						if(aData[12] == 'paid') {
							nRow.className = "installment_payment_link";
							action.find(".add-payment").remove();
						}
						if(aData[12] == 'partial') {
							nRow.className = "installment_payment_link";
						}
						return nRow;
					},
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
						var payment = 0, 
							interest = 0, 
							principal = 0, 
							payment_paid = 0,
							interest_paid = 0, 
							principal_paid = 0, 
							penalty_paid = 0;
						
						for (var i = 0; i < aaData.length; i++) {
							payment += parseFloat(aaData[aiDisplay[i]][3]);
							interest += parseFloat(aaData[aiDisplay[i]][4]);
							principal += parseFloat(aaData[aiDisplay[i]][5]);
							payment_paid += parseFloat(aaData[aiDisplay[i]][7]);
							interest_paid += parseFloat(aaData[aiDisplay[i]][8]);
							principal_paid += parseFloat(aaData[aiDisplay[i]][9]);
							penalty_paid += parseFloat(aaData[aiDisplay[i]][10]);
						}
						var nCells = nRow.getElementsByTagName('th');
						nCells[3].innerHTML = currencyFormat(parseFloat(payment));
						nCells[4].innerHTML = currencyFormat(parseFloat(interest));
						nCells[5].innerHTML = currencyFormat(parseFloat(principal));
						nCells[7].innerHTML = currencyFormat(parseFloat(payment_paid));
						nCells[8].innerHTML = currencyFormat(parseFloat(interest_paid));
						nCells[9].innerHTML = currencyFormat(parseFloat(principal_paid));
						nCells[10].innerHTML = currencyFormat(parseFloat(penalty_paid));
						
						$("#payment_v").text(formatMoney(parseFloat(payment)));
						$("#payment_p").text(formatMoney(parseFloat(payment_paid)));
						$("#payment_b").text(formatMoney(parseFloat(payment - payment_paid)));
						
						$("#interest_v").text(formatMoney(parseFloat(interest)));
						$("#interest_p").text(formatMoney(parseFloat(interest_paid)));
						$("#interest_b").text(formatMoney(parseFloat(interest - interest_paid)));
						
						$("#principal_v").text(formatMoney(parseFloat(principal)));
						$("#principal_p").text(formatMoney(parseFloat(principal_paid)));
						$("#principal_b").text(formatMoney(parseFloat(principal - principal_paid)));
					}
				}).fnSetFilteringDelay().dtFilter([
					{column_number: 1, filter_default_label: "[#]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('deadline');?>]", filter_type: "text", data: []},
					{column_number: 11, filter_default_label: "[<?=lang('overdue');?>]", filter_type: "text", data: []},
					{column_number: 12, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
					{column_number: 13, filter_default_label: "[<?=lang('action');?>]", filter_type: "text", data: []},
				], "footer");
			});
		</script>
		<?php if ($Owner || $Admin || $GP['bulk_actions']) {
			echo admin_form_open('installments/repayment_actions', 'id="action-form"');
		} ?>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-usd"></i>
					<?= lang('repayments'); ?> ( <?= $installment->customer ?> #<?= $installment->reference_no ?> )
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
							</a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							   <li>
									<li id="payment_box">
										<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment">
											<i class="fa fa-money"></i> <?=lang('add_payment')?>
										</a>
									</li>
									<li>
										<a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
											<?= lang('export_to_excel') ?>
										</a>
									</li>
							   </li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('list_results'); ?></p>
						<div class="table-responsive">
							<table id="Repayment_table" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped table-condensed">
								<thead>
									<tr class="active">
										<th style="min-width:30px; width: 30px; text-align: center;">
											<input class="checkbox checkft" type="checkbox" name="check"/>
										</th>
										<th width='100'>#</th>
										<th width='180'><?= lang("deadline") ?></th>
										<th width='180'><?= lang("payment") ?></th>
										<th width='180'><?= lang("interest") ?></th>
										<th width='180'><?= lang("principal") ?></th>
										<th width='180'><?= lang("balance") ?></th>
										<th width='150'><?= lang("payment") ?><br/><small>( <?= lang('paid') ?> )</small></th>
										<th width='150'><?= lang("interest") ?><br/><small>( <?= lang('paid') ?> )</small></th>
										<th width='150'><?= lang("principal") ?><br/><small>( <?= lang('paid') ?> )</small></th>
										<th width='150'><?= lang("penalty") ?><br/><small>( <?= lang('paid') ?> )</small></th>
										<th width='100'><?= lang("overdue") ?></th>
										<th width='150'><?= lang("status") ?></th>
										<th width='5%'><?= lang("action") ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
									</tr>
								</tbody>
								<tfoot class="dtFilter">
									<th style="min-width:30px; width: 30px; text-align: center;">
										<input class="checkbox checkft" type="checkbox" name="check"/>
									</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tfoot>
								<tfoot>
									<tr>
										<td colspan="5" class="bold" style="font-size:13px; text-align:center;">
											<?= lang("payment") ?> : 
											<span id="payment_v"></span> - 
											<span id="payment_p"></span> = 
											<span id="payment_b" style="color:red;"></span>
										</td>
										<td colspan="4" class="bold" style="font-size:13px; text-align:center;">
											<?= lang("interest") ?> :
											<span id="interest_v"></span> - 
											<span id="interest_p"></span> = 
											<span id="interest_b" style="color:red;"></span>
										</td>
										<td colspan="5" class="bold" style="font-size:13px; text-align:center;">
											<?= lang("principal") ?> : 
											<span id="principal_v"></span> - 
											<span id="principal_p"></span> = 
											<span id="principal_b" style="color:red;"></span>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php if ($Owner || $GP['bulk_actions']) {?>
			<div style="display: none;">
				<input type="hidden" name="form_action" value="" id="form_action"/>
				<?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
			</div>
			<?=form_close()?>
		<?php } ?>
	</div>
	<div id="transactions" class="tab-pane fade in">
		<script>
			$(document).ready(function () {
				oTable = $('#Transaction_table').dataTable({
					"aaSorting": [[9, "asc"]],
					"aLengthMenu": [[-1], ["<?= lang('all') ?>"]],
					"iDisplayLength": -1,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= admin_url('installments/getTransactions/?id='.$id) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [
						{"sClass":"center", "bSortable": false}, 
						{"mRender":fld, "sClass":"center"},
						null,
						null,
						{"mRender": currencyFormat },
						{"mRender": currencyFormat }, 
						{"mRender": currencyFormat },
						{"mRender": currencyFormat },
						null,
						{"sClass":"center"},
						{"mRender":row_status},],
					'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						nRow.id = aData[0];
						return nRow;
					},
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
						var payment = 0, interest = 0, principal = 0, penalty = 0;
						for (var i = 0; i < aaData.length; i++) {
							 payment += parseFloat(aaData[aiDisplay[i]][4]);
							 interest += parseFloat(aaData[aiDisplay[i]][5]);
							 principal += parseFloat(aaData[aiDisplay[i]][6]);
							 penalty += parseFloat(aaData[aiDisplay[i]][7]);
						}
						var nCells = nRow.getElementsByTagName('th');
						nCells[4].innerHTML = currencyFormat(parseFloat(payment));
						nCells[5].innerHTML = currencyFormat(parseFloat(interest));
						nCells[6].innerHTML = currencyFormat(parseFloat(principal));
						nCells[7].innerHTML = currencyFormat(parseFloat(penalty));
					}
				}).fnSetFilteringDelay().dtFilter([
					{column_number: 1, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
					{column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
					{column_number: 8, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
					{column_number: 9, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
					{column_number: 10, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
				], "footer");
			});
		</script>
		
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-star-o"></i>
					<?= lang('transactions'); ?> ( <?= $installment->customer ?> #<?= $installment->reference_no ?> )
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
							</a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							   <li>
									<li>
										<a href="#" id="xls2" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
											<?= lang('export_to_excel') ?>
										</a>
									</li>
							   </li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('list_results'); ?></p>
						<div class="table-responsive">
							<table  id="Transaction_table" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped table-condensed">
								<thead>
								<tr class="active">
									<th style="min-width:30px; width: 30px; text-align: center;">#</th>
									<th width='150'><?= lang("date") ?></th>
									<th width='150'><?= lang("reference_no") ?></th>
									<th width='150'><?= lang("customer") ?></th>
									<th width='150'><?= lang("payment") ?><br/><small>( <?= lang('paid') ?> )</small></th>
									<th width='150'><?= lang("interest") ?><br/><small>( <?= lang('paid') ?> )</small></th>
									<th width='150'><?= lang("principal") ?><br/><small>( <?= lang('paid') ?> )</small></th>
									<th width='150'><?= lang("penalty") ?><br/><small>( <?= lang('paid') ?> )</small></th>
									<th width='100'><?= lang("created_by") ?></th>
									<th style="width:30px;"><?= lang("paid_by") ?></th>
									<th style="width:30px;"><?= lang("type") ?></th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
								</tr>
								</tbody>
								<tfoot class="dtFilter">
									<th style="min-width:30px; width: 30px; text-align: center;">#</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tfoot>
								<tfoot></tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="assignations" class="tab-pane fade in">
		<script>
			$(document).ready(function () {
				oTable = $('#Assignation_table').dataTable({
					"aaSorting": [[0, "desc"]],
					"aLengthMenu": [[-1], ["<?= lang('all') ?>"]],
					"iDisplayLength": -1,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= admin_url('installments/getAssignations/?id='.$id) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [
						{"sClass":"center", "mRender" : checkbox},
						{"mRender":fsd, "sClass":"center"},
						null,
						null,
						null,
						null,
						null],
					'fnRowCallback': function (nRow, aData, iDisplayIndex) {
						var oSettings = oTable.fnSettings();
						nRow.id = aData[0];
						return nRow;
					},
				}).fnSetFilteringDelay().dtFilter([
					{column_number: 1, filter_default_label: "[<?=lang('assigned_date');?>]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('old_customer');?>]", filter_type: "text", data: []},
					{column_number: 3, filter_default_label: "[<?=lang('new_customer');?>]", filter_type: "text", data: []},
					{column_number: 4, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
					{column_number: 5, filter_default_label: "[<?=lang('assigned_by');?>]", filter_type: "text", data: []},
				], "footer");
				
			});
		</script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-star-o"></i>
					<?= lang('assignations'); ?> ( <?= $installment->customer ?> #<?= $installment->reference_no ?> )
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
							</a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							   <li>
									<li>
										<a href="<?= admin_url('installments/add_assignation/'.$installment->id); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-plus-circle"></i> 
											<?= lang('add_assignation') ?>
										</a>
									</li>
									<li>
										<a href="#" id="xls3" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
											<?= lang('export_to_excel') ?>
										</a>
									</li>
							   </li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('list_results'); ?></p>
						<div class="table-responsive">
							<table  id="Assignation_table" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped table-condensed">
								<thead>
								<tr class="active">
									<th style="width: 20px !important; text-align: center;">
										<input class="checkbox checkft" type="checkbox" name="check"/>
									</th>
									<th width='200'><?= lang("assigned_date") ?></th>
									<th width='200'><?= lang("old_customer") ?></th>
									<th width='200'><?= lang("new_customer") ?></th>
									<th width='250'><?= lang("description") ?></th>
									<th width='150'><?= lang("assigned_by") ?></th>
									<th style="width:20px;"><?= lang("actions") ?></th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
								</tr>
								</tbody>
								<tfoot class="dtFilter">
									<th style="min-width:30px; width: 30px; text-align: center;">
										<input class="checkbox checkft" type="checkbox" name="check"/>
									</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tfoot>
								<tfoot></tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('#pdf2').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=admin_url('installments/transaction_actions/'.$id.'/1/0')?>";
			return false;
		});
		$('#xls2').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=admin_url('installments/transaction_actions/'.$id.'/0/1')?>";
			return false;
		});
		$('#pdf3').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=admin_url('installments/assignation_actions/'.$id.'/1/0')?>";
			return false;
		});
		$('#xls3').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=admin_url('installments/assignation_actions/'.$id.'/0/1')?>";
			return false;
		});
		$('#multi_payment').live('click',function(){
			var installment_id = '';
			var intRegex = /^\d+$/;
			var i = 0;
			$('.input-xs').each(function(){
				if ($(this).is(':checked') && intRegex.test($(this).val())) {
					if(i==0){
						installment_id += $(this).val();
						i=1;
					}else{
						installment_id += "InstallmentID"+$(this).val();
					}
				}
			});
			if(installment_id==''){
				alert("<?= lang('no_sale_selected') ?>")
				return false;
			}else{
				var link = '<?= anchor('admin/installments/add_multi_payment/#######', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal" class="multi_payment"')?>';
				var add_payment_link = link.replace("#######", installment_id);
				$("#payment_box").html(add_payment_link);
				$('.multi_payment').click();
				$("#payment_box").html('<a href="javascript:void(0)" id="multi_payment" data-action="multi_payment"><i class="fa fa-money"></i> <?=lang('add_payment')?></a>');		
				return false;
			}
		});
	})
</script>