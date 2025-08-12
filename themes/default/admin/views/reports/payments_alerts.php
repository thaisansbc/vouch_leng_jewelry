<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        oTable = $('#PQData').dataTable({
            "aaSorting": [
                [1, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getPaymentsAlerts' ) ?>',
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
            "aoColumns": [null, null, null,null,null 
                ],
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 0,
                filter_default_label: "[<?= lang('ដំណាក់កាល'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 1,
                filter_default_label: "[<?= lang('reference_no'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('ទឹកប្រាក់ត្រូវបង់'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('កាលបរិច្ឆេទបង់ប្រាក់'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('ផ្សេងៗ'); ?>]",
                filter_type: "text",
                data: []
            },
          
        ], "footer");
    });
</script>
<style>
    #dtFilter-filter--PQData-3, #dtFilter-filter--PQData-4 {
        text-align: center;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <?php 
                if($warehouse_id){
                    $str = "";
                    foreach ($warehouse as $key => $value) {
                        $str .= $key != count($warehouse) - 1  ? $value->name . ", " : $value->name; 
                    }
                }
            ?>
            <i class="fa-fw fa fa-calendar-o"></i><?= lang('payments_alerts') ; ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks hide">
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i>
                        </a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li>
                                <a href="<?= admin_url('reports/quantity_alerts') ?>">
                                    <i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/quantity_alerts/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } ?>
                        </ul>
                    </li>
                <?php
                } ?>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip hide" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="PQData" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-hover table-striped dfTable reports-table">
                        <thead>
                            <tr class="active">
                                <th style="min-width:250px; width: 250px; text-align: center;"><?php echo $this->lang->line('ដំណាក់កាល'); ?>
                                </th><th><?php echo $this->lang->line('reference_no'); ?></th>
                                <th><?php echo $this->lang->line('ទឹកប្រាក់ត្រូវបង់'); ?></th>
                                <th style="text-align: center !important;"><?php echo $this->lang->line('កាលបរិច្ឆេទបង់ប្រាក់'); ?></th>
                                <th style="text-align: center !important;"><?php echo $this->lang->line('ផ្សេងៗ'); ?></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                
                                <th style="min-width:40px; width: 40px; text-align: center;"></th>
                                <th></th>
                                <th style="text-align: center !important; width: 20%; "></th>
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#pdf').click(function(event) {
            event.preventDefault();
            // window.location.href = "<?= admin_url('reports/getQuantityAlerts/' . ($warehouse_id ? $warehouse_id : '0') . '/pdf') ?>";
            window.location.href = "<?= admin_url('reports/getQuantityAlerts/' . ($warehouse_id ? str_replace(",", "-", $warehouse_id) : '0') . '/pdf') ?>";
            return false;
        });
        $('#xls').click(function(event) {
            event.preventDefault();
            // window.location.href = "<?= admin_url('reports/getQuantityAlerts/' . ($warehouse_id ? $warehouse_id : '0') . '/0/xls') ?>";
            window.location.href = "<?= admin_url('reports/getQuantityAlerts/' . ($warehouse_id ? str_replace(",", "-", $warehouse_id) : '0') . '/0/xls') ?>";
            return false;
        });
        $('#image').click(function(event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function(canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>