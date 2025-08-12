<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#LTable').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('installments/getInstallments/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0)) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
				{"mRender": checkbox},
				{"sClass":"center", "mRender" : fld}, 
				{"sClass":"left"}, 
				{"sClass":"left"},
				{"sClass":"left"},
				{"sClass":"left"}, 
				{"sClass":"center", "mRender" : currencyFormat}, 
				{"sClass":"center", "mRender" : currencyFormat},  
				{"sClass":"center", "mRender" : currencyFormat},
				{"sClass":"center", "mRender" : currencyFormat},
				{"sClass":"center", "mRender" : currencyFormat},
				{"sClass":"center", "mRender" : fsd}, 
				{"sClass":"center", "mRender" : row_status}, 
				{"bSortable": false}],
				'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
				nRow.className = "installment_schedule_link";
				var action = $('td:eq(13)', nRow);
                var checkbox = $('td:eq(0)', nRow);
				if(aData[12] == 'completed' || aData[12] == 'payoff' || aData[12] == 'returned'){
					action.find('.active-installment').remove();
					action.find('.payoff-installment').remove();
					action.find('.edit_installment').remove();
					if(aData[12] != 'returned'){
						action.find('.inactive-installment').remove();
					}
				}
				if(aData[12] == 'inactive' || aData[12] == 'voiced'){
					action.find('.inactive-installment').remove();
					action.find('.edit_installment').remove();
					action.find('.payoff-installment').remove();
					action.find('.assign_installment').remove();
				}else{
					action.find('.active-installment').remove();
				}
                return nRow;
            }
		,"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var amount = 0, deposit = 0, principal = 0, interest = 0, payment = 0;
                for (var i = 0; i < aaData.length; i++) {
					amount += parseFloat(aaData[aiDisplay[i]][7]);
					deposit += parseFloat(aaData[aiDisplay[i]][8]);
                    principal += parseFloat(aaData[aiDisplay[i]][9]);
					interest += parseFloat(aaData[aiDisplay[i]][10]);
					payment += parseFloat(aaData[aiDisplay[i]][11]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[7].innerHTML = currencyFormat(parseFloat(amount));
				nCells[8].innerHTML = currencyFormat(parseFloat(deposit));
                nCells[9].innerHTML = currencyFormat(parseFloat(principal));
				nCells[10].innerHTML = currencyFormat(parseFloat(interest));
				nCells[11].innerHTML = currencyFormat(parseFloat(payment));
            }
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 1, filter_default_label: "[<?= lang('date') ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('sale_ref') ?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?= lang('reference_no') ?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?= lang('customer') ?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?= lang('phone') ?>]", filter_type: "text", data: []},
			{column_number: 11, filter_default_label:"[<?= lang('first_payment_date') ?>]", filter_type: "text", data: []},
			{column_number: 12, filter_default_label:"[<?= lang('status') ?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php 
	if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('installments/installment_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('installments'); ?></h2>
        <div class="box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
				<a data-toggle="dropdown" class="dropdown-toggle" href="#">
					<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
				</a>
					<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li>
							<a href="<?= admin_url("installments/add") ?>">
								<i class="fa fa fa-plus-circle"></i> <?=lang('add_installment')?>
							</a>
						</li>
						<li>
							<a href="#" id="excel" data-action="export_excel">
								<i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
							</a>
						</li>
						
						<li class="divider"></li>
						<li>
							<a href="#" class="bpo"
							title="<b><?=lang("delete_installments")?></b>"
							data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
							data-html="true" data-placement="left">
							<i class="fa fa-trash-o"></i> <?=lang('delete_installments')?>
							</a>
						</li>
					</ul>
				</li>
				<?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= admin_url('installments/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($warehouses as $warehouse) {
								echo '<li><a href="' . admin_url('installments/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
							}
							?>
						</ul>
					</li>
				<?php } ?>
				
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= admin_url('installments/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . admin_url('installments/index/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                    <table id="LTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
							<th><?= lang("date") ?></th>
							<th><?= lang("sale_ref") ?></th>
							<th><?= lang("reference_no") ?></th>
							<th><?= lang("customer") ?></th>
							<th><?= lang("phone") ?></th>
							<th><?= lang("amount") ?></th>
							<th><?= lang("deposit") ?></th>
							<th><?= lang("principal") ?></th>
							<th><?= lang("interest") ?></th>
							<th><?= lang("payment") ?></th>
							<th><?= lang("first_payment_date") ?></th>
							<th><?= lang("status") ?></th>
							<th><?= lang("action") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="14" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
							<th><?= lang("action") ?></th>
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
<?php }
?>
