<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
		oTable = $('#CsData').dataTable({
			"aaSorting": [[1, "desc"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= admin_url('pos/getCustomerStocks/') ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			"aoColumns": [{"mRender" : checkbox, "bSortable" : false},{"mRender" : fld, "sClass":"center"}, {"sClass":"center"}, {"sClass":"left"}, {"sClass":"left"}, null, {"mRender" : fsd, "sClass":"center"}, {"sClass":"center"}, {"mRender": row_status}, {"bSortable": false}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {              
				nRow.id = aData[0];
                nRow.className = "customer_stock_link";

				var action = $('td:eq(9)', nRow);
				if(aData[8] == 'returned'){
					action.find('.cs-cancel').remove();
				}
				if(aData[8] == 'returned' || aData[8] == 'completed'){
					action.find('.cs-transfer').remove();
					action.find('.cs-return').remove();
					action.find('.cs-edit').remove();
					action.find('.cs-delete').remove();
				}else{
					action.find('.cs-cancel').remove();
				}
				
				if(aData[8] == 'expired'){
					action.find('.cs-transfer').remove();
					action.find('.cs-return').remove();
					action.find('.cs-edit').remove();
					action.find('.cs-delete').remove();
					action.find('.cs-cancel').remove();
				}
				
                return nRow;
            }
		}).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('expiry');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
		
		if (localStorage.getItem('remove_csls')) {
            if (localStorage.getItem('csitems')) {
                localStorage.removeItem('csitems');
            }
            if (localStorage.getItem('csref')) {
                localStorage.removeItem('csref');
            }
			if (localStorage.getItem('csexpiry')) {
                localStorage.removeItem('csexpiry');
            }
			if (localStorage.getItem('cscustomer')) {
                localStorage.removeItem('cscustomer');
            }
            if (localStorage.getItem('cswarehouse')) {
                localStorage.removeItem('cswarehouse');
            }
            if (localStorage.getItem('csnote')) {
                localStorage.removeItem('csnote');
            }
            if (localStorage.getItem('csdate')) {
                localStorage.removeItem('csdate');
            }
            localStorage.removeItem('remove_csls');
        }

        <?php if ($this->session->userdata('remove_csls')) { ?>
            if (localStorage.getItem('csitems')) {
                localStorage.removeItem('csitems');
            }
            if (localStorage.getItem('csref')) {
                localStorage.removeItem('csref');
            }
			if (localStorage.getItem('csexpiry')) {
                localStorage.removeItem('csexpiry');
            }
			if (localStorage.getItem('cscustomer')) {
                localStorage.removeItem('cscustomer');
            }
            if (localStorage.getItem('cswarehouse')) {
                localStorage.removeItem('cswarehouse');
            }
            if (localStorage.getItem('csnote')) {
                localStorage.removeItem('csnote');
            }
            if (localStorage.getItem('csdate')) {
                localStorage.removeItem('csdate');
            }
        <?php $this->bpas->unset_data('remove_csls');}
        ?>
		
    });
</script>
<?php if ($Owner || $Admin) {
    echo admin_form_open('pos/customer_stock_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customer_stocks'); ?></h2>
	<div class="box-icon">
		<ul class="btn-tasks">
			<li class="dropdown">
				<a data-toggle="dropdown" class="dropdown-toggle" href="#">
					<i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
				</a>
				<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
					<li>
						<a href="<?=admin_url('pos/add_customer_stock')?>">
							<i class="fa fa-plus-circle"></i> <?=lang('add_customer_stock')?>
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
						title="<b><?=lang("delete_customer_stocks")?></b>"
						data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
						data-html="true" data-placement="left">
						<i class="fa fa-trash-o"></i> <?=lang('delete_customer_stocks')?>
						</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
	
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="CsData" cellpadding="0" cellspacing="0" border="0"
						   class="table table-bordered">
						<thead>
						<tr class="primary">
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
							<th class="col-xs-2"><?= lang("date"); ?></th>
							<th class="col-xs-2"><?= lang("reference"); ?></th>
							<th class="col-xs-2"><?= lang("customer"); ?></th>
							<th class="col-xs-2"><?= lang("phone"); ?></th>
							<th class="col-xs-4"><?= lang("description"); ?></th>
							<th class="col-xs-4"><?= lang("expiry"); ?></th>
							<th class="col-xs-3"><?= lang("created_by"); ?></th>
							<th class="col-xs-3"><?= lang("status"); ?></th>
							<th style="width:85px;"><?= lang("actions"); ?></th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
							</tr>
                        </tfoot>
					</table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>