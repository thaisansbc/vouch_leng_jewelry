
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('product')) {
		$v .= "&product=" . $this->input->post('product');
	}
    if ($this->input->post('category')) {
		$v .= "&category=" . $this->input->post('category');
	}
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: right;
    }
    <?php if ($Owner || $Admin || $this->session->userdata('show_cost')) {
    ?>
    #PRData td:nth-child(9) {
        text-align: right;
    }
    <?php
} if ($Owner || $Admin || $this->session->userdata('show_price')) {
        ?>
    #PRData td:nth-child(8) {
        text-align: right;
    }
    <?php
    } ?>
</style>
<script>
    var oTable;
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('assets/get_evaluation/?v=1' . $v . ($warehouse_id ? '/' . $warehouse_id : '') . ($supplier ? '?supplier=' . $supplier->id : '')) ?>',
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
                nRow.className = "asset_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, 
				{"bSortable": false,"mRender": img_hl},
				{"mRender": fsd},				
				null, 
				null, 
				null, 
				null, 
				{"mRender": currencyFormat},
				null,
				null,
				{"bSortable": false}
            ]/*,
			"fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var cost = 0;var price = 0;
                for (var i = 0; i < aaData.length; i++) {
                    cost += parseFloat(aaData[aiDisplay[i]][6]);
					price += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(cost);
				nCells[8].innerHTML = currencyFormat(price);
            }*/
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 2, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
        ], "footer");

    });
	
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i><?= lang('evaluation') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')' . ($supplier ? ' (' . lang('supplier') . ': ' . ($supplier->first_name && $supplier->last_name != '-' ? $supplier->first_name : $supplier->usernames) . ')' : ''); ?>
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">  
                <?php if (!empty($warehouses)) {
                ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('reports/evaluation') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('reports/evaluation/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php
            } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
				<div id="form">
                    <?php echo admin_form_open("reports/evaluation"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                
                                $pr[0] = $this->lang->line("all");;
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code ;
                                }
                                echo form_dropdown('product', $pr, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[0] = $this->lang->line("all");
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="project"><?= lang("biller"); ?></label>
                                <?php
                                if ($Owner || $Admin) {
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company.'/'.$biller->name : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                } else {
                                    $user_pro[""] = "";
                                    // foreach ($user_billers as $user_biller) {
                                    $user_pro[$user_billers->id] = $user_billers->company.'/'.$user_billers->name;
                                    // }
                                    echo form_dropdown('biller', $user_pro, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_product', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                    <?php if ($Owner || $GP['bulk_actions']) {
                        echo admin_form_open('assets/actions' . ($warehouse_id ? '/' . $warehouse_id : ''), 'id="action-form"');
                    } ?>
                </div>
                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line('image'); ?></th>
                            <th><?= lang('date') ?></th>
							<th><?= lang('code') ?></th>
                            <th><?= lang('name') ?></th>
                            <th><?= lang('biller') ?></th>
                            <th><?= lang('category') ?></th>
                            <th><?= lang('cost') ?></th>
							<th><?= lang('current_price') ?></th>
                            <th><?= lang('created_by')?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang('actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line('image'); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:65px; text-align:center;"><?= lang('actions') ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>

<script>
	$('#form').hide();
	$('.toggle_down').click(function() {
		$("#form").slideDown();
		return false;
	});
	$('.toggle_up').click(function() {
		$("#form").slideUp();
		return false;
	});
</script>