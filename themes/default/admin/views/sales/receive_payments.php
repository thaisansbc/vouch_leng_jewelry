<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#RPData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('sales/getReceivePayments/'.($biller ? $biller->id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "receive_payment_link";
				var action = $('td:eq(12)', nRow);
				if(aData[10] == 'pending'){
					action.find('.verify_receive_payment').remove();
					action.find('.approve_receive_payment').remove();
					action.find('.unapprove_receive_payment').remove();
				}
				if(aData[10] == 'checked'){
					action.find('.check_receive_payment').remove();
					action.find('.verify_receive_payment').remove();
					action.find('.unapprove_receive_payment').remove();
				}
				if(aData[10] == 'approved'){
					action.find('.check_receive_payment').remove();
					action.find('.approve_receive_payment').remove();
				}
				if(aData[10] == 'verified'){
					action.find('.check_receive_payment').remove();
					action.find('.approve_receive_payment').remove();
					action.find('.verify_receive_payment').remove();
				}
				
				if(aData[10] != 'pending'){
					action.find('.edit_receive_payment').remove();
					action.find('.delete_receive_payment').remove();
				}
				
				return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
               var total = 0;
                for (var i = 0; i < aaData.length; i++) {
					total += parseFloat(aaData[aiDisplay[i]][5]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[5].innerHTML = currencyFormat(parseFloat(total));
            },
			'bStateSave': true,
			'fnStateSave': function (oSettings, oData) {
				localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
			},
			'fnStateLoad': function (oSettings) {
				var data = localStorage.getItem('DataTables_' + window.location.pathname);
				return JSON.parse(data);
			},
			"search": {
				"caseInsensitive": false
			},
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, {"mRender": fsd}, {"mRender": fsd}, {"mRender": currencyFormat}, null,null,null,null, {"mRender": row_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('from_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('to_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('checked_by');?>]", filter_type: "text", data: []},	
			{column_number: 8, filter_default_label: "[<?=lang('approved_by');?>]", filter_type: "text", data: []},	
			{column_number: 9, filter_default_label: "[<?=lang('verified_by');?>]", filter_type: "text", data: []},	
			{column_number: 10, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},	
        ], "footer");
    });
</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('sales/receive_payment_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
	  <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('receive_payments').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('sales/add_receive_payment') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_receive_payment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
						<li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("approve_receive_payment") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='approve'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left"><i class="fa fa-check"></i> <?= lang('approve_receive_payment') ?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_receive_payments") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_receive_payments') ?>
                            </a>
                        </li>
                    </ul>
                </li>
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= admin_url('sales/receive_payments') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . admin_url('sales/receive_payments/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
							}
							?>
						</ul>
					</li>
				<?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="RPData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("reference_no"); ?></th>
								<th><?= lang("from_date"); ?></th>
								<th><?= lang("to_date"); ?></th>
								<th><?= lang("amount"); ?></th>
								<th><?= lang("created_by"); ?></th>
								<th><?= lang("checked_by"); ?></th>
								<th><?= lang("approved_by"); ?></th>
								<th><?= lang("verified_by"); ?></th>
								<th><?= lang("status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>							
								<th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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