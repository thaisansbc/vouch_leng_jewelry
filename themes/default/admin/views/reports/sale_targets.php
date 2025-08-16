<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
if ($this->input->post('biller')) {
    $v .= '&biller=' . $this->input->post('biller');
}
if ($this->input->post('saleman')) {
    $v .= '&saleman=' . $this->input->post('saleman');
}
if ($this->input->post('description')) {
    $v .= '&description=' . $this->input->post('description');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
if ($this->input->post('zone')) {
    $v .= '&zone=' . $this->input->post('zone');
}
?>

<script>
    $(document).ready(function () {
        var zones;
        $.ajax({
            url: site.base_url + 'system_settings/getZones_ajax',
            dataType: "json",
            success: function (data) {
                zones = data;
            },
            error: function (xhr, error) {
                console.debug(xhr); 
                console.debug(error);
            }
        });
        function percentage(x) {
            x = parseFloat(x).toFixed(2);
            if(x > 100) { x = 100; }
            return '<div class="progress" style="background-color: #ddd;"><div class="progress-bar" role="progressbar" aria-valuenow="' + x + '" aria-valuemin="0" aria-valuemax="100" style="width: ' + x + '%; color: black;">' + x + '%</div></div>';
        }
        function sale_target_status(x){
            if(x == 1){
                return '<div class="text-center"><span class="sale_target_status label label-success" style="padding: 3.5px;">Complete</span></div>';
            } else {
                return '<div class="text-center"><span class="sale_target_status label label-warning" style="padding: 3.5px;">Not Complete</span></div>';
            }
        }

        oTable = $('#EXPData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getSaleTargetsReport/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                null, null, 
                null, null,
                { "fnRender": function (o) {
                    if(o.aData[4] != null){
                        var zns = [];
                        var multi_zones_by_id = o.aData[4].split(",");
                        multi_zones_by_id.forEach((element, index, array) => {
                            zns[index] = zones.find(x => x.id === element).zone_name;
                        }); 
                        var x = "";
                        zns.forEach((element, index) => {
                            var randomColor = Math.floor(Math.random() * 16777215).toString(16);
                            while(randomColor.length < 6) { randomColor = "0" + randomColor; }
                            x += "<span class='label' style='font-size: 12px; padding: 7px; margin: 5px; display: inline-block; color: white; background-color: #" + randomColor + ";'>" + element + "</span>";
                        });
                        return $(this).innerHTML = "<div style='overflow-wrap: anywhere;'>" + x + "</div>";
                    } else {
                        return null;
                    }
                }},
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, null,
                {"mRender": percentage} , {"mRender": sale_target_status}
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var target = 0, sales = 0;
                for (var i = 0; i < aaData.length; i++) {
                    target += parseFloat(aaData[aiDisplay[i]][5]);
                    sales += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(target);
                nCells[6].innerHTML = currencyFormat(sales);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('start_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('end_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('saleman');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('zone');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<style>
    .table td:nth-child(9) {
        text-align: center !important;
    }
    .table td:nth-child(10) {
        text-align: center !important;
    }
    #dtFilter-filter--EXPData-9 {
        text-align: center !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('sale_targets_report'); ?></h2>
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
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
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
                <div id="form">
                    <?php echo admin_form_open('reports/sale_targets'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control date" id="start_date" autocomplete=off'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control date" id="end_date" autocomplete=off'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang('branch'); ?></label>
                                <?php
                                $bl[''] = lang('select') . ' ' . lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="saleman"><?= lang('saleman'); ?></label>
                                <?php
                                $sm[''] = lang('select') . ' ' . lang('saleman');
                                foreach ($salemans as $saleman) {
                                    $sm[$saleman->id] = $saleman->first_name . ' ' . $saleman->last_name;
                                }
                                echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ''), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('saleman') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="zone"><?= lang('zone'); ?></label>
                                <?php
                                $zns[''] = lang('select') . ' ' . lang('zone');
                                if($zones){
                                    foreach ($zones as $zone) {
                                        $zns[$zone->p_id] = $zone->p_name && $zone->p_name != '-' ? $zone->p_name : $zone->p_name;
                                        if($zone->c_id != null){
                                            $child_zones_id = explode("___", $zone->c_id);
                                            $child_zones_name = explode("___", $zone->c_name);

                                            foreach ($child_zones_id as $key => $value) {
                                                $zns[$value] = "&emsp;" . $child_zones_name[$key];
                                            }
                                        }
                                    }
                                }
                                echo form_dropdown('zone', $zns, (isset($_POST['zone']) ? $_POST['zone'] : ''), 'class="form-control" id="zone" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('zone') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('description', 'description'); ?>
                                <?php echo form_input('description', (isset($_POST['description']) ? $_POST['description'] : ''), 'class="form-control" id="description"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> 
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="EXPData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang('start_date'); ?></th>
                                <th><?= lang('end_date'); ?></th>
                                <th><?= lang('biller'); ?></th>
                                <th><?= lang('saleman'); ?></th>
                                <th style="width: 20%;"><?= lang('zone'); ?></th>
                                <th style="text-align: right !important;"><?= lang('target'); ?></th>
                                <th style="text-align: right !important;"><?= lang('total_sales'); ?></th>
                                <th><?= lang('description'); ?></th>
                                <th><?= lang('progress'); ?></th>
                                <th style="text-align: center !important;"><?= lang('status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th><?= lang('progress'); ?></th><th style="text-align: center !important;"><?= lang('status'); ?></th>
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
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSaleTargetsReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSaleTargetsReport/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSaleTargetsReport/0/0/preview/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>
