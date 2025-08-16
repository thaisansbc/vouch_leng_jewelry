<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
		oTable = $('#fuel_sale').dataTable({
			"aaSorting": [[0, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= admin_url('sales/getFuelSales/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0)); ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			"aoColumns": [
			{"mRender" : checkbox},
			{"mRender" : fld,"sClass":"text-center"},
			null,
			null,
			{"sClass":"text-center"},
			{"mRender":formatQuantity},
			{"mRender":formatQuantity},
			{"mRender":formatQuantity},
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			{"mRender":row_status},
			{"bSortable": false,"mRender": attachment},
			{"bSortable": false}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "fuel_sale_link";
				var action = $('td:eq(16)', nRow);
				if(aData[14]=='completed'){
					action.find('.fuel-add_sale, .fuel-delete, .fuel-edit').remove();
				}
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var cus_qty = 0, using_qty = 0 ,quantity=0, total_sales=0, credit_amount=0, bank_amount = 0, cash_change=0, cash_submit=0, different = 0;
				for (var i = 0; i < aaData.length; i++) {
					cus_qty += parseFloat(aaData[aiDisplay[i]][5]);
					using_qty += parseFloat(aaData[aiDisplay[i]][6]);
					quantity += parseFloat(aaData[aiDisplay[i]][7]);
					total_sales += parseFloat(aaData[aiDisplay[i]][8]);
					cash_change += parseFloat(aaData[aiDisplay[i]][9]);
					cash_submit += parseFloat(aaData[aiDisplay[i]][10]);
					credit_amount += parseFloat(aaData[aiDisplay[i]][11]);
					bank_amount += parseFloat(aaData[aiDisplay[i]][12]);
					different += parseFloat(aaData[aiDisplay[i]][13]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[5].innerHTML = formatQuantity(parseFloat(cus_qty));
				nCells[6].innerHTML = formatQuantity(parseFloat(using_qty));
				nCells[7].innerHTML = formatQuantity(parseFloat(quantity));
				nCells[8].innerHTML = currencyFormat(parseFloat(total_sales));
				nCells[9].innerHTML = currencyFormat(parseFloat(cash_change));
				nCells[10].innerHTML = currencyFormat(parseFloat(cash_submit));
				nCells[11].innerHTML = currencyFormat(parseFloat(credit_amount));
				nCells[12].innerHTML = currencyFormat(parseFloat(bank_amount));
				nCells[13].innerHTML = currencyFormat(parseFloat(different));
			},
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('saleman');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('time');?>]", filter_type: "text", data: []},
			{column_number: 14, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
		
		if (localStorage.getItem('remove_shls')) {
			 if (localStorage.getItem('shitems')) {
				localStorage.removeItem('shitems');
			}
			if (localStorage.getItem('shref')) {
				localStorage.removeItem('shref');
			}
			if (localStorage.getItem('shsaleman')) {
                localStorage.removeItem('shsaleman');
            }
			if (localStorage.getItem('shwarehouse')) {
				localStorage.removeItem('shwarehouse');
			}
			if (localStorage.getItem('shuser')) {
				localStorage.removeItem('shuser');
			}
			if (localStorage.getItem('shdate')) {
				localStorage.removeItem('shdate');
			}
			if (localStorage.getItem('shnote')) {
				localStorage.removeItem('shnote');
			}
			if (localStorage.getItem('shtime')) {
				localStorage.removeItem('shtime');
			}
			localStorage.removeItem('remove_shls');
		}
		<?php 
		if ($this->session->userdata('remove_shls')) { ?>
            if (localStorage.getItem('shitems')) {
				localStorage.removeItem('shitems');
			}
			if (localStorage.getItem('shref')) {
				localStorage.removeItem('shref');
			}
			if (localStorage.getItem('shsaleman')) {
                localStorage.removeItem('shsaleman');
            }
			if (localStorage.getItem('shwarehouse')) {
				localStorage.removeItem('shwarehouse');
			}
			if (localStorage.getItem('shuser')) {
				localStorage.removeItem('shuser');
			}
			if (localStorage.getItem('shdate')) {
				localStorage.removeItem('shdate');
			}
			if (localStorage.getItem('shnote')) {
				localStorage.removeItem('shnote');
			}
			if (localStorage.getItem('shtime')) {
				localStorage.removeItem('shtime');
			}
        <?php $this->bpas->unset_data('remove_shls'); } ?>
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('sales/fuel_sale_actions', 'id="action-form"');
} ?>
<div class="box">

    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('fuel_sales').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
		<div class="box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#">
						<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
					</a>
					<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li>
							<a href="<?= admin_url('sales/add_fuel_sale')?>">
								<i class="fa fa-plus-circle"></i> <?=lang('add_fuel_sale')?>
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
							title="<b><?=lang("delete_fuel_sales")?></b>"
							data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
							data-html="true" data-placement="left">
							<i class="fa fa-trash-o"></i> <?=lang('delete_fuel_sales')?>
							</a>
						</li>
					</ul>
				</li>
				
				<?php if (!empty($warehouses) && $this->config->item('one_warehouse')==false)  { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('sales/fuel_sales/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('sales/fuel_sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
				
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= admin_url('sales/fuel_sales/') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . admin_url('sales/fuel_sales/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
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
                    <table id="fuel_sale" cellpadding="0" cellspacing="0" border="0"
						   class="table table-bordered">
						<thead>
						<tr class="primary">
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
							<th><?= lang("date"); ?></th>
							<th><?= lang("reference_no"); ?></th>
							<th><?= lang("saleman"); ?></th>
							<th><?= lang("time"); ?></th>
							<th><?= lang("customer_qty"); ?></th>
							<th><?= lang("using_qty"); ?></th>
							<th><?= lang("fuel_qty"); ?></th>
							<th><?= lang("fuel_amount"); ?></th>
							<th><?= lang("cash_change"); ?></th>
							<th><?= lang("cash_submit"); ?></th>
							<th><?= lang("credit_amount"); ?></th>
							<th><?= lang("bank_amount"); ?></th>
							<th><?= lang("different"); ?></th>
							<th><?= lang("status"); ?></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							<th><?= lang("actions"); ?></th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="15" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
						</tbody>
						<tfoot>
							<tr class="active">
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
								<th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:30px;"><?= lang("actions"); ?></th>
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