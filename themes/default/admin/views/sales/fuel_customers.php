<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
		oTable = $('#FSData').dataTable({
			"aaSorting": [[0, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= admin_url('sales/getFuelCustomers/'.($warehouse ? $warehouse->id : 0).'/'.($biller ? $biller->id : 0)); ?>',
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
				{"mRender":currencyFormat},
				{"mRender": row_status},
				{"bSortable": false,"mRender": attachment},
				{"bSortable": false}
			],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "fuel_customer_link";
				
				var action = $('td:eq(9)', nRow);
				if(aData[7]!='pending'){
					action.find('.delete_fuel_customer').remove();
				}
				
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var quantity=0, grand_total=0;
				for (var i = 0; i < aaData.length; i++) {
					quantity += parseFloat(aaData[aiDisplay[i]][5]);
					grand_total += parseFloat(aaData[aiDisplay[i]][6]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[5].innerHTML = formatQuantity(parseFloat(quantity));
				nCells[6].innerHTML = currencyFormat(parseFloat(grand_total));
			},
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('salesman');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
		
		if (localStorage.getItem('remove_fcls')) {
			if (localStorage.getItem('fcitems')) {
				localStorage.removeItem('fcitems');
			}
			if (localStorage.getItem('fcdate')) {
				localStorage.removeItem('fcdate');
			}
			if (localStorage.getItem('fcreference')) {
				localStorage.removeItem('fcreference');
			}
			if (localStorage.getItem('fcsalesman')) {
				localStorage.removeItem('fcsalesman');
			}
			if (localStorage.getItem('fcwarehouse')) {
				localStorage.removeItem('fcwarehouse');
			}
			if (localStorage.getItem('fcnote')) {
				localStorage.removeItem('fcnote');
			}
			if (localStorage.getItem('fctime')) {
				localStorage.removeItem('fctime');
			}
			if (localStorage.getItem('fccustomer')) {
				localStorage.removeItem('fccustomer');
			}
			localStorage.removeItem('remove_fcls');
		}
		<?php 
		if ($this->session->userdata('remove_fcls')) { ?>
            if (localStorage.getItem('fcitems')) {
				localStorage.removeItem('fcitems');
			}
			if (localStorage.getItem('fcdate')) {
				localStorage.removeItem('fcdate');
			}
			if (localStorage.getItem('fcreference')) {
				localStorage.removeItem('fcreference');
			}
			if (localStorage.getItem('fcsalesman')) {
				localStorage.removeItem('fcsalesman');
			}
			if (localStorage.getItem('fccustomer')) {
				localStorage.removeItem('fccustomer');
			}
			if (localStorage.getItem('fcwarehouse')) {
				localStorage.removeItem('fcwarehouse');
			}
			if (localStorage.getItem('fcnote')) {
				localStorage.removeItem('fcnote');
			}
			if (localStorage.getItem('fctime')) {
				localStorage.removeItem('fctime');
			}
        <?php $this->bpas->unset_data('remove_fcls'); } ?>
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('sales/fuel_customer_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
	<h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('fuel_customers').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
	<div class="box-icon">
		<ul class="btn-tasks">
			<li class="dropdown">
				<a data-toggle="dropdown" class="dropdown-toggle" href="#">
					<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
				</a>
				<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
					<li>
						<a href="<?=admin_url('sales/add_fuel_customer')?>">
							<i class="fa fa-plus-circle"></i> <?=lang('add_fuel_customer')?>
						</a>
					</li>
					<li><a href="#" id="create_sale" data-action="create_sale"><i class="fa fa-file-excel-o"></i> <?= lang('create_sale') ?></a></li>
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
                        <li><a href="<?= admin_url('sales/fuel_customers/') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('sales/fuel_customers/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            <?php } ?>
			
			<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li><a href="<?= admin_url('sales/fuel_customers/') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
						<li class="divider"></li>
						<?php
						foreach ($billers as $biller) {
							echo '<li><a href="' . admin_url('sales/fuel_customers/null/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
						}
						?>
					</ul>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="FSData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
						<thead>
						<tr class="primary">
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
							<th><?= lang("date"); ?></th>
							<th><?= lang("reference"); ?></th>
							<th><?= lang("customer"); ?></th>
							<th><?= lang("salesman"); ?></th>
							<th><?= lang("quantity"); ?></th>
							<th><?= lang("grand_total"); ?></th>
							<th><?= lang("status"); ?></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							<th style="width:30px;"><?= lang("actions"); ?></th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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


<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		$(document).on('click', '#create_sale', function(e) {
			e.preventDefault();
			$('#form_action').val($(this).attr('data-action'));
			$('#action-form-submit').click();
		});
	});
</script>
