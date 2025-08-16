<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php 
$v = '';
if (isset($alert_id)) {
    $v .= "&a=" . $alert_id;
}
?>

<script>
    $(document).ready(function () {
        oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getStockReceived' . ($warehouse_id ? '/' . $warehouse_id : '') . '?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false,"mRender": checkbox}, 
                {"mRender": fld}, null,null,null,null,  
                {"mRender": row_status},
                {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "stock_received_link";
                var action = $('td:eq(8)', nRow);
                if(aData[6] == "received"){
                    action.find('.add_stock').remove();
                }
                return nRow;
            }
        
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('po_ref');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('po_ref');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('purchase_status');?>]", filter_type: "text", data: []},
        ], "footer");

        <?php if ($this->session->userdata('remove_pols')) {
    ?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('poextras')) {
            localStorage.removeItem('poextras');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        <?php $this->bpas->unset_data('remove_pols');
        }
        ?>
    });

</script>
<style type="text/css">
    body {
        position: static !important;
        overflow-y: auto !important;
    }
</style>
<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('products/stock_received_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <?php $wh_title = ($warehouse_id ? $warehouse->name : ((isset($user_warehouse) && !empty($user_warehouse)) ? $user_warehouse->name : lang('all_warehouses'))); ?>
        <h2 class="blue"><i class="fa-fw fa fa-star"></i><?=lang('stock_received') . ' (' . $wh_title . ')';?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang('actions')?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (($this->Owner || $this->Admin) || !$this->session->userdata('warehouse_id')) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('products/stock_received') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('products/stock_received/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($warehouses)){ ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('products/stock_received') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $warehouse_id = explode(',', $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                foreach ($warehouse_id as $key => $value) {
                                    if ($warehouse->id==$value) {
                                        echo '<li><a href="' . admin_url('products/stock_received/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                }
                                
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?=lang('list_results');?></p>
                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('project'); ?></th>
                            <th><?= lang('reference'); ?></th>
                            <th><?= lang('po_ref'); ?></th>
                            <th><?= lang('pr_ref'); ?></th>
                            <th><?= lang('purchase_status'); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px;"><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
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
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
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
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>