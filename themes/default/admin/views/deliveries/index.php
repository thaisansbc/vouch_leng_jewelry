<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
$v = "";
if($this->input->get("status")){
	$v .= "&status=". $this->input->get("status");
}
?>
<script>
    $(document).ready(function () {
		<?php if($this->session->userdata('remove_dols')) { ?>
			if (localStorage.getItem('decustomer')) {
				localStorage.removeItem('decustomer');
			}
			if (localStorage.getItem('debiller')) {
				localStorage.removeItem('debiller');
			}
			if (localStorage.getItem('dewarehouse')) {
				localStorage.removeItem('dewarehouse');
			}
			if (localStorage.getItem('denote')) {
				localStorage.removeItem('denote');
			}
			if (localStorage.getItem('deaddress')) {
				localStorage.removeItem('deaddress');
			}
			if (localStorage.getItem('dereceived_phone')) {
				localStorage.removeItem('dereceived_phone');
			}
			if (localStorage.getItem('dediscount')) {
				localStorage.removeItem('dediscount');
			}
			if (localStorage.getItem('deshipping')) {
				localStorage.removeItem('deshipping');
			}
			if (localStorage.getItem('deitems')) {
				localStorage.removeItem('deitems');
			}
			<?php $this->bpas->unset_data('remove_dols'); ?>
		<?php } ?>
        var dss = <?= json_encode(array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'))); ?>;
        function ds(x) {
            if (x == 'delivered') {
                return '<div class="text-center"><span class="label label-success">'+(dss[x] ? lang[dss[x]] : lang[x])+'</span></div>';
            } else if (x == 'completed') {
                return '<div class="text-center"><span class="label label-success">'+(dss[x] ? lang[dss[x]] : lang[x])+'</span></div>';
            } else if (x == 'delivering') {
                return '<div class="text-center"><span class="label label-primary">'+(dss[x] ? lang[dss[x]] : lang[x])+'</span></div>';
            } else if (x == 'packing') {
                return '<div class="text-center"><span class="label label-warning">'+(dss[x] ? lang[dss[x]] : lang[x])+'</span></div>';
            }else if (x == 'pending') {
                return '<div class="text-center"><span class="label label-warning">'+(dss[x] ? lang[dss[x]] : lang[x])+'</span></div>';
            }
            return x;
            return (x != null) ? (dss[x] ? dss[x] : x) : x;
        }
        oTable = $('#DOData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('deliveries/getDeliveries/'.($biller ? $biller->id : '').'/'.("?v=1".$v));?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                	nRow.id   = aData[0];
				var action = $('td:eq(10)', nRow);
				if(aData[8] == 'completed'){
					action.find('.create_sale').remove();        
					action.find('.delete_delivery').remove();        
				}
                nRow.className = "delivery_link";
                return nRow;
            },
            "aoColumns": [
            	{"mRender": checkbox}, 
            	{"mRender": fld}, 
            	null, 
            	null, 
            	null, 
            	null, 
            	{"sClass":"center"}, 
            	{"mRender": ds},
            	null, 
            	{"bSortable": false,"mRender": attachment}, 
            	{"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('do_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('so_reference_no');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<style type="text/css">
	#dtFilter-filter-wrapper--DOData-6 { width: 200px !important; }
</style>
<?php if ($Owner || $GP['bulk_actions']) { echo admin_form_open('deliveries/delivery_actions', 'id="action-form"'); } ?>
<div class="breadcrumb-header">
	<?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
	<h2 class="blue"><i class="fa-fw fa fa-truck"></i><?= lang('deliveries') . ' (' . $biller_title . ')'; ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
					<li>
						<a href="<?= admin_url('deliveries/add') ?>">
							<i class="fa fa-plus-circle"></i> <?=lang('add_delivery')?>
						</a>
					</li>
					<li><a href="#" id="create_sale" data-action="create_sale"><i class="fa fa-file-excel-o"></i> <?= lang('create_sale') ?></a></li>
                    <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_deliveriesaaaa") ?></b>" 
                            data-content="<p><?= lang('r_u_sure') ?></p>
                            	<button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                            data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_deliveries') ?>
                        </a>
                    </li>
                </ul>
            </li>
			<?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li><a href="<?= admin_url('deliveries/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
						<li class="divider"></li>
						<?php
						foreach ($billers as $biller) {
							echo '<li><a href="' . admin_url('deliveries/index/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
						}
						?>
					</ul>
				</li>
			<?php } elseif (!empty($billers)) { ?>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li><a href="<?= admin_url('deliveries/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
						<li class="divider"></li>
						<?php
						$biller_id_ = $count_billers;
                        foreach ($billers as $biller) {
                            foreach ($biller_id_ as $key => $value) {
                                if ($biller->id == $value) {
									echo '<li><a href="' . admin_url('deliveries/index/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
						        }
                            }
                        } ?>
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
					<table id="DOData" class="table table-bordered table-hover table-striped table-condensed">
						<thead>
							<tr>
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("do_reference_no"); ?></th>
								<th><?= lang("sale_reference_no"); ?></th>
								<th><?= lang("so_reference_no"); ?></th>
								<th style="width: 100px"><?= lang("customer"); ?></th>
								<th><?= lang("delivered_by"); ?></th>
								<th><?= lang("status"); ?></th>
								<th style="width: 100px !important;"><?= lang("payment_status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
						</tbody>
						<tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
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
        <?= form_submit('perform_action', 'perform_action', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $(document).on('click', '#delete', function(e) {
                e.preventDefault();
                $('#form_action').val($(this).attr('data-action'));
                // $('#action-form').submit();
                $('#action-form-submit').click();
            });
			$(document).on('click', '#create_sale', function(e) {
                e.preventDefault();
                $('#form_action').val($(this).attr('data-action'));
                $('#action-form-submit').click();
            });
        });
    </script>
<?php } ?>