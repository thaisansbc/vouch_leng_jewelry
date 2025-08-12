<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    var oTable;
    $(document).ready(function () {
        function count_type(x) {
            if (x == 'full') {
                return '<div class="text-center"><label class="label label-success"><?= lang('full'); ?></label></div>';
            } else if (x == 'partial') {
                return '<div class="text-center"><label class="label label-primary"><?= lang('partial'); ?></label></div>';
            } else {
                return x;
            }
        }
        function status(x) {
            if (x == '0') {
                return '<div class="text-center"><label class="label label-success"><?= lang('Draft'); ?></label></div>';
            } else if (x == '1') {
                return '<div class="text-center"><label class="label label-primary"><?= lang('Complete'); ?></label></div>';
            } else {
                return x;
            }
        }
        oTable = $('#STData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getCounts' . ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0]; nRow.className = "count_link"; return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": count_type}, {"mRender": status}, null, null, {"bSortable": false, "mRender": attachment2}, {"bSortable": false, "mRender": attachment}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('brands');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('categories');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('products/count_actions' . ($warehouse_id ? '/' . $warehouse_id : ''), 'id="action-form"');
} ?>
<div class="breadcrumb-header">
    <?php $wh_title = ($warehouse_id ? $warehouse->name : ((isset($user_warehouse) && !empty($user_warehouse)) ? $user_warehouse->name : lang('all_warehouses'))); ?>
    <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('stock_counts') . ' (' . $wh_title . ')'; ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a href="<?= admin_url('products/count_stock') ?>" class="tip" data-placement="top" title="<?= lang('count_stock') ?>">
                    <i class="icon fa fa-plus tip"></i>
                </a>
            </li>
            <?php if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('products/stock_counts') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('products/stock_counts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
            <?php } elseif (!empty($warehouses)){ ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('products/stock_counts') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        $warehouse_id = explode(',', $this->session->userdata('warehouse_id'));
                        foreach ($warehouses as $warehouse) {
                            foreach ($warehouse_id as $key => $value) {
                                if ($warehouse->id==$value) {
                                    echo '<li><a href="' . admin_url('products/stock_counts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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

                <div class="table-responsive">
                    <table id="STData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?= lang('date') ?></th>
                            <th class="col-xs-2"><?= lang('reference') ?></th>
                            <th class="col-xs-2"><?= lang('warehouse') ?></th>
                            <th class="col-xs-1"><?= lang('type') ?></th>
                            <th class="col-xs-1"><?= lang('status') ?></th>
                            <th class="col-xs-2"><?= lang('brands') ?></th>
                            <th class="col-xs-2"><?= lang('categories') ?></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-file-o"></i></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-chain"></i></th>
                            <th style="max-width:65px; text-align:center;"><?= lang('actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-file-o"></i></th>
                            <th style="max-width:30px; text-align:center;"><i class="fa fa-chain"></i></th>
                            <th style="width:65px; text-align:center;"><?= lang('actions') ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {
                                ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php
                            } ?>
