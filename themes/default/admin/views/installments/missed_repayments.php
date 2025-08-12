<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#LTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('installments/getMissedRepayments') ?>',
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
				{"mRender":fsd}, 
				{"sClass":"left"}, 
				{"sClass":"left"}, 
				{"mRender":currencyFormat}, 
				{"mRender":currencyFormat}, 
				{"mRender":currencyFormat}, 
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":currencyFormat},
				{"mRender":row_status},
				{"mRender":row_status, "bSortable": false}, 				
				{"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                var action = $('td:eq(15)', nRow);
				if(aData[14] == 'paid'){
					nRow.className = "success";
				}
				if(aData[14] == 'partial'){
					nRow.className = "warning";
				}
				if(aData[14] == 'payoff'){
					nRow.className = "danger";
					action.find('.add-payment').remove();
				}
				if(aData[14] != 'payoff'){
					action.find('.delete-installment_item').remove();
				}
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                 var principal = 0,
					 interest = 0, 
					 payment = 0, 
					 principal_paid = 0, 
					 interest_paid =0, 
					 payment_paid = 0, 
					 penalty_paid = 0;
					 
                for (var i = 0; i < aaData.length; i++) {
                    payment += parseFloat(aaData[aiDisplay[i]][5]);
					interest += parseFloat(aaData[aiDisplay[i]][6]);
					principal += parseFloat(aaData[aiDisplay[i]][7]);
					
					payment_paid += parseFloat(aaData[aiDisplay[i]][9]);
					interest_paid += parseFloat(aaData[aiDisplay[i]][10]);
					principal_paid += parseFloat(aaData[aiDisplay[i]][11]);
					penalty_paid += parseFloat(aaData[aiDisplay[i]][12]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(parseFloat(payment));
				nCells[6].innerHTML = currencyFormat(parseFloat(interest));
				nCells[7].innerHTML = currencyFormat(parseFloat(principal));
				nCells[9].innerHTML = currencyFormat(parseFloat(payment_paid));
				nCells[10].innerHTML = currencyFormat(parseFloat(interest_paid));
				nCells[11].innerHTML = currencyFormat(parseFloat(principal_paid));
				nCells[12].innerHTML = currencyFormat(parseFloat(penalty_paid));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[#]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('deadline');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 13, filter_default_label: "[<?=lang('overdue');?>]", filter_type: "text", data: []},
			{column_number: 14, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
			{column_number: 15, filter_default_label: "[<?=lang('action');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
	echo admin_form_open('installments/missed_repayment_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('missed_repayments'); ?></h2>
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
                    <table id="LTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped table-condensed">
                        <thead>
	                        <tr class="active">
	                            <th style="min-width:30px; width: 30px; text-align: center;">
	                                <input class="checkbox checkft" type="checkbox" name="check"/>
	                            </th>
								<th width='100'>#</th>
								<th width='180'><?= lang("deadline") ?></th>
								<th width='180'><?= lang("reference_no") ?></th>
								<th width='180'><?= lang("customer") ?></th>
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
	                            <td colspan="15" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
	                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<th>&nbsp;</th>
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
							<th></th>
							<th></th>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>
<script type="text/javascript">
	$(function(){
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