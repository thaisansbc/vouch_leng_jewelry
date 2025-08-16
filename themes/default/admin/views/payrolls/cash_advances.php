<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#cash_advanceData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('payrolls/getCashAdvances/'.($biller ? $biller->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
				var action = $('td:eq(11)', nRow);
				if(aData[8] == 'pending'){
					action.find('.add_payback').remove();
					action.find('.unapprove_cash_advance').remove();
				}else if(aData[8] == 'approved'){
					action.find('.approve_cash_advance').remove();
					action.find('.reject_cash_advance').remove();
					action.find('.edit_cash_advance').remove();
					action.find('.delete_cash_advance').remove();
				}else if(aData[8] == 'rejected'){
					action.find('.add_payback').remove();
					action.find('.reject_cash_advance').remove();
					action.find('.unapprove_cash_advance').remove();
					action.find('.edit_cash_advance').remove();
					action.find('.delete_cash_advance').remove();
				}
				if(aData[9] == 'pending'){
					action.find('.view_payback').remove();
				}else if(aData[9] == 'paid'){
					action.find('.add_payback').remove();
					action.find('.unapprove_cash_advance').remove();
				}else{
					action.find('.unapprove_cash_advance').remove();
				}
				
                nRow.id = aData[0];
                nRow.className = "cash_advance_link";
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total_amount = 0; total_paid = 0, total_balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					total_amount += parseFloat(aaData[aiDisplay[i]][4]);
                    total_paid += parseFloat(aaData[aiDisplay[i]][5]);
					total_balance += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[4].innerHTML = currencyFormat(total_amount);
                nCells[5].innerHTML = currencyFormat(total_paid);
				nCells[6].innerHTML = currencyFormat(total_balance);
            },
            "aoColumns": [{"mRender": checkbox},{"mRender": fld}, null, null,  {"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": decode_html},{"mRender": row_status},{"mRender": pay_status},{"bSortable": false,"mRender": attachment},  {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('requested_by');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
    });

</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('payrolls/cash_advance_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('cash_advances').' ('.($biller ? $biller->name : lang('all_billers')).')' ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('payrolls/add_cash_advance') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_cash_advance') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_cash_advances") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_cash_advances') ?>
                            </a>
                        </li>
                    </ul>
                </li>
				
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= admin_url('payrolls/cash_advances') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . admin_url('payrolls/cash_advances/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                    <table id="cash_advanceData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>						
								<th><?= lang("date"); ?></th>
								<th><?= lang("reference_no"); ?></th>
								<th><?= lang("requested_by"); ?></th>
								<th><?= lang("amount"); ?></th>
								<th><?= lang("paid"); ?></th>
								<th><?= lang("balance"); ?></th>
								<th><?= lang("note"); ?></th>
								<th style="width:60px"><?= lang("status"); ?></th>
								<th style="width:60px"><?= lang("payment_status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:10%; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="12" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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