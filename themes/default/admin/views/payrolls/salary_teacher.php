<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#salaryData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('payrolls/getTeacherSalaries/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
				var action = $('td:eq(18)', nRow);
				if(aData[12] == 'pending'){
					action.find('.unapprove_salary').remove();
					action.find('.add_payment').remove();
				}else if(aData[12] == 'approved'){
					action.find('.approve_salary').remove();
					action.find('.edit_salary').remove();
					action.find('.delete_salary').remove();
				}
				if(aData[13] != 'pending'){
					action.find('.unapprove_salary').remove();
				}
				if(aData[13] == 'paid'){
					action.find('.add_payment').remove();
				}
				
                nRow.id = aData[0];
                nRow.className = "salary_teacher_link";
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total_gross_salary = 0; total_overtime = 0, total_addition = 0, total_cash_advanced = 0, total_tax_payment = 0, total_net_salary = 0, total_net_pay = 0,total_tax_paid = 0, total_salary_paid = 0, total_paid = 0, total_balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					total_gross_salary += parseFloat(aaData[aiDisplay[i]][4]);
     
					total_tax_payment += parseFloat(aaData[aiDisplay[i]][5]);
					total_net_salary += parseFloat(aaData[aiDisplay[i]][6]);
					total_net_pay += parseFloat(aaData[aiDisplay[i]][7]);
					total_tax_paid += parseFloat(aaData[aiDisplay[i]][8]);
					total_salary_paid += parseFloat(aaData[aiDisplay[i]][9]);
					total_paid += parseFloat(aaData[aiDisplay[i]][10]);
					total_balance += parseFloat(aaData[aiDisplay[i]][11]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[4].innerHTML = currencyFormat(total_gross_salary);
				nCells[5].innerHTML = currencyFormat(total_tax_payment);
				nCells[6].innerHTML = currencyFormat(total_net_salary);
				nCells[7].innerHTML = currencyFormat(total_net_pay);
				nCells[8].innerHTML = currencyFormat(total_tax_paid);
				nCells[9].innerHTML = currencyFormat(total_salary_paid);
				nCells[10].innerHTML = currencyFormat(total_paid);
				nCells[11].innerHTML = currencyFormat(total_balance);
            },
            "aoColumns": [{"mRender": checkbox},{"mRender": fld},
             null,null, 
            {"mRender": currencyFormat},
            {"mRender": currencyFormat},
            {"mRender": currencyFormat},
            {"mRender": currencyFormat},
            {"mRender": currencyFormat},
            {"mRender": currencyFormat}, 
            {"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": row_status},{"mRender": pay_status},{"bSortable": false,"mRender": attachment},  {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('month');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 12, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
			{column_number: 13, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
    });

</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('payrolls/salary_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('salaries_teacher').' ('.($biller ? $biller->name : lang('all_billers')).')' ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('payrolls/add_salary_teacher') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_salary') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_salaries") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_salaries') ?>
                            </a>
                        </li>
                    </ul>
                </li>
				
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= admin_url('payrolls/') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . admin_url('payrolls/index/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
							}
							?>
						</ul>
					</liv>
				<?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="salaryData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("month"); ?></th>
								<th><?= lang("created_by"); ?></th>
								<th><?= lang("gross_salary"); ?></th>
							
								<th><?= lang("tax_payment"); ?></th>
								<th><?= lang("net_salary"); ?></th>
								<th><?= lang("net_pay"); ?></th>
								<th><?= lang("tax_paid"); ?></th>
								<th><?= lang("salary_paid"); ?></th>
								<th><?= lang("total_paid"); ?></th>
								<th><?= lang("balance"); ?></th>
								<th><?= lang("status"); ?></th>
								<th><?= lang("payment_status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="16" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>