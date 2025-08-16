<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        oTable = $('#customerRewardProductTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getRewards/customer/product') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, {"mRender": formatQuantity2}, {"mRender": currencyFormatLeft}, null, {"mRender": formatQuantity2}, {"bSortable": false}]
        });
    });
</script>
<script>
    $(document).ready(function() {
        oTable = $('#customerRewardMoneyTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getRewards/customer/money') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, null, {"mRender": formatQuantity2}, {"mRender": currencyFormatLeft}, {"mRender": currencyFormatLeft}, {"bSortable": false}]
        });
    });
</script>
<script>
    $(document).ready(function() {
        oTable = $('#supplierRewardProductTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getRewards/supplier/product') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, {"mRender": formatQuantity2}, {"mRender": currencyFormatLeft}, null, {"mRender": formatQuantity2}, {"bSortable": false}]
        });
    });
</script>
<script>
    $(document).ready(function() {
        oTable = $('#supplierRewardMoneyTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getRewards/supplier/money') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, null, {"mRender": formatQuantity2}, {"mRender": currencyFormatLeft}, {"mRender": currencyFormatLeft}, {"bSortable": false}]
        });
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-th-list"></i><?= lang('rewards'); ?></h2>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <ul id="myTab" class="nav nav-tabs">
                    <li class="bold"><a href="#tab_customer_reward" class="tab-grey"><?= lang('customer_reward') ?></a></li>
                    <li class="bold"><a href="#tab_supplier_reward" class="tab-grey"><?= lang('supplier_reward') ?></a></li>
                </ul>
                <div class="tab-content">
                    <div id="tab_customer_reward" class="tab-pane fade">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-xs-10">
                                    <h3 style="font-weight: bold; padding-top: 10px;"><?= lang('rewards_product'); ?></h3>
                                </div>
                                <div class="col-xs-2">
                                    <div class="box-header">
                                        <div class="box-icon">
                                            <ul class="btn-tasks">
                                                <li class="dropdown">
                                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                                        <li>
                                                            <a href="<?php echo admin_url('system_settings/add_reward/customer/product'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                                <i class="fa fa-plus"></i> <?= lang('add_reward') ?>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" id="xls_customer_reward_product" class="tip">
                                                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="customerRewardProductTable" class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 10px; width: 10px; text-align: center;">
                                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                                </th>
                                                <th style="width: 300px !important;"><?= lang('exchange_product'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('exchange_quantity'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('amount'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('receive_product'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('receive_quantity'); ?></th>
                                                <th style="width: 100px;"><?= lang('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="10" class="dataTables_empty">
                                                    <?= lang('loading_data_from_server') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-xs-10">
                                    <h3 style="font-weight: bold; padding-top: 10px;"><?= lang('rewards_money'); ?></h3>
                                </div>
                                <div class="col-xs-2">
                                    <div class="box-header">
                                        <div class="box-icon">
                                            <ul class="btn-tasks">
                                                <li class="dropdown">
                                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                                        <li>
                                                            <a href="<?php echo admin_url('system_settings/add_reward/customer/money'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                                <i class="fa fa-plus"></i> <?= lang('add_reward') ?>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" id="xls_customer_reward_money" class="tip">
                                                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="customerRewardMoneyTable" class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 10px; width: 10px; text-align: center;">
                                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                                </th>
                                                <th style="width: 350px !important;"><?= lang('exchange_product'); ?></th>
                                                <th style="width: 350px !important;"><?= lang('exchange_quantity'); ?></th>
                                                <th style="width: 350px !important;"><?= lang('amount'); ?></th>
                                                <th style="width: 350px !important;"><?= lang('interest'); ?></th>
                                                <th style="width: 100px;"><?= lang('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="10" class="dataTables_empty">
                                                    <?= lang('loading_data_from_server') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab_supplier_reward" class="tab-pane fade">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-xs-10">
                                    <h3 style="font-weight: bold; padding-top: 10px;"><?= lang('rewards_product'); ?></h3>
                                </div>
                                <div class="col-xs-2">
                                    <div class="box-header">
                                        <div class="box-icon">
                                            <ul class="btn-tasks">
                                                <li class="dropdown">
                                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                                        <li>
                                                            <a href="<?php echo admin_url('system_settings/add_reward/supplier/product'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                                <i class="fa fa-plus"></i> <?= lang('add_reward') ?>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" id="xls_supplier_reward_product" class="tip">
                                                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="supplierRewardProductTable" class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 10px; width: 10px; text-align: center;">
                                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                                </th>
                                                <th style="width: 300px !important;"><?= lang('exchange_product'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('exchange_quantity'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('amount'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('receive_product'); ?></th>
                                                <th style="width: 300px !important;"><?= lang('receive_quantity'); ?></th>
                                                <th style="width: 100px;"><?= lang('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="10" class="dataTables_empty">
                                                    <?= lang('loading_data_from_server') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-xs-10">
                                    <h3 style="font-weight: bold; padding-top: 10px;"><?= lang('rewards_money'); ?></h3>
                                </div>
                                <div class="col-xs-2">
                                    <div class="box-header">
                                        <div class="box-icon">
                                            <ul class="btn-tasks">
                                                <li class="dropdown">
                                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                                        <li>
                                                            <a href="<?php echo admin_url('system_settings/add_reward/supplier/money'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                                <i class="fa fa-plus"></i> <?= lang('add_reward') ?>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" id="xls_supplier_reward_money" class="tip">
                                                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="supplierRewardMoneyTable" class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 10px; width: 10px; text-align: center;">
                                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                                </th>
                                                <th style="width: 350px !important;"><?= lang('exchange_product'); ?></th>
                                                <th style="width: 350px !important;"><?= lang('exchange_quantity'); ?></th>
                                                <th style="width: 350px !important;"><?= lang('amount'); ?></th>
                                                <th style="width: 350px !important;"><?= lang('interest'); ?></th>
                                                <th style="width: 100px;"><?= lang('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="10" class="dataTables_empty">
                                                    <?= lang('loading_data_from_server') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function() {
        $('#xls_customer_reward_product').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('system_settings/rewards_export_excel/customer/product'); ?>";
            return false;
        });
        $('#xls_customer_reward_money').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('system_settings/rewards_export_excel/customer/money'); ?>";
            return false;
        });
        $('#xls_supplier_reward_product').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('system_settings/rewards_export_excel/supplier/product'); ?>";
            return false;
        });
        $('#xls_supplier_reward_money').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('system_settings/rewards_export_excel/supplier/money'); ?>";
            return false;
        });
    });
</script>