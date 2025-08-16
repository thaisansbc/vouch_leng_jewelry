<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = '';

if ($this->input->post('category')) {
    $v .= '&category=' . $this->input->post('category');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
?>
<script src="<?= $assets; ?>js/hc/highcharts.js"></script>
<script type="text/javascript">
    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
            };
        });
        $('#chart').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {text: ''},
            credits: {enabled: false},
            tooltip: {
                formatter: function () {
                    return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                },
                followPointer: true,
                useHTML: true,
                borderWidth: 0,
                shadow: false,
                valueDecimals: site.settings.decimals,
                style: {fontSize: '14px', padding: '0', color: '#000000'}
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return '<h3 style="margin:-15px 0 0 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y) + '</b></h3>';
                        },
                        useHTML: true
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line('stock_value'); ?>',
                data: [
                    ['<?php echo $this->lang->line('stock_value_by_price'); ?>', <?php echo $stock->stock_by_price; ?>],
                    ['<?php echo $this->lang->line('stock_value_by_cost'); ?>', <?php echo $stock->stock_by_cost; ?>],
                    ['<?php echo $this->lang->line('profit_estimate'); ?>', <?php echo($stock->stock_by_price - $stock->stock_by_cost); ?>],
                ]

            }]
        });

    });
</script>
 <ul id="myTab" class="nav nav-tabs">
    <li class=""><a href="#graph" class="tab-grey"><?= lang('chart') ?></a></li>
    <li class=""><a href="#stock_value" class="tab-grey"><?= lang('stock_value_detail') ?></a></li>
</ul>
<div class="tab-content">
    <div id="graph" class="tab-pane fade in">
        <div class="box" style="margin-top: 15px;">
            <div class="box-header">
                <h2 class="blue"><i
                        class="fa-fw fa fa-bar-chart-o"></i><?= lang('warehouse_stock') . ' (' . ($warehouse ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <?php if (!empty($warehouses) && ($Owner || $Admin)) {
                            ?>
                            <li class="dropdown">
                                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
                                        class="icon fa fa-building-o tip" data-placement="left"
                                        title="<?= lang('warehouses') ?>"></i></a>
                                <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                    aria-labelledby="dLabel">
                                    <li><a href="<?= admin_url('reports/warehouse_stock') ?>"><i
                                                class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                                    <li class="divider"></li>
                                    <?php
                                    foreach ($warehouses as $warehouse) {
                                        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/warehouse_stock/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                        <p class="introtext"><?php echo lang('warehouse_stock_heading'); ?></p>
                        <?php if ($totals) {
                            ?>

                            <div class="small-box padding1010 col-sm-6 bblue">
                                <div class="inner clearfix">
                                    <a>
                                        <h3><?= $this->bpas->formatQuantity($totals->total_items) ?></h3>

                                        <p><?= lang('total_items') ?></p>
                                    </a>
                                </div>
                            </div>

                            <div class="small-box padding1010 col-sm-6 bdarkGreen">
                                <div class="inner clearfix">
                                    <a>
                                        <h3><?= $this->bpas->formatQuantity($totals->total_quantity) ?></h3>

                                        <p><?= lang('total_quantity') ?></p>
                                    </a>
                                </div>
                            </div>
                            <div class="clearfix" style="margin-top:20px;"></div>
                        <?php
                         } ?>
                        <div id="chart" style="width:100%; height:450px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="stock_value" class="tab-pane fade">
        <script>
            $(document).ready(function () {
                oTable = $('#PrRData').dataTable({
                    "aaSorting": [[0, "asc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/getCategoriesStock/?v=1' . $v) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },
                    "aoColumns": [null, null, {"mRender": decimalFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}],
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var pQty = 0, sQty = 0, pAmt = 0, sAmt = 0, pl = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            pQty += parseFloat(aaData[aiDisplay[i]][2]);
            
                            pAmt += parseFloat(aaData[aiDisplay[i]][3]);
                            sAmt += parseFloat(aaData[aiDisplay[i]][4]);
                            pl += parseFloat(aaData[aiDisplay[i]][5]);
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        nCells[2].innerHTML = decimalFormat(parseFloat(pQty));
                    
                        nCells[3].innerHTML = currencyFormat(parseFloat(pAmt));
                        nCells[4].innerHTML = currencyFormat(parseFloat(sAmt));
                        nCells[5].innerHTML = currencyFormat(parseFloat(pl));
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('category_code');?>]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('category_name');?>]", filter_type: "text", data: []},
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
        <div class="box">
            <div class="box-header">
                <h2 class="blue">
                    <i class="fa-fw fa fa-folder-open"></i><?= lang('categories_report'); ?> <?php
                    if ($this->input->post('start_date')) {
                        echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                    }
                    ?>
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

                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="form">

                            <?php echo admin_form_open('reports/categories'); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('category', 'category') ?>
                                        <?php
                                        $cat[''] = '';
                                        foreach ($categories as $category) {
                                            $cat[$category->id] = $category->name;
                                        }
                                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang('select') . ' ' . lang('category') . '" style="width:100%"')
                                        ?>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                        <?php
                                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                        ?>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('start_date', 'start_date'); ?>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('end_date', 'end_date'); ?>
                                        <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                    class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>

                        </div>

                        <div class="clearfix"></div>

                        <div class="table-responsive">
                            <table id="PrRData"
                                   class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                                   style="margin-bottom:5px;">
                                <thead>
                                <tr class="active">
                                    <th><?= lang('category_code'); ?></th>
                                    <th><?= lang('category_name'); ?></th>
                                    <th><?= lang('purchased'); ?></th>
     
                                    <th><?= lang('purchased_amount'); ?></th>
                                    <th><?= lang('sold_amount'); ?></th>
                                    <th><?= lang('profit_loss'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th><?= lang('purchased'); ?></th>
              
                                    <th><?= lang('purchased_amount'); ?></th>
                                    <th><?= lang('sold_amount'); ?></th>
                                    <th><?= lang('profit_loss'); ?></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
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
            window.location.href = "<?=admin_url('reports/getCategoriesReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getCategoriesReport/0/xls/?v=1' . $v)?>";
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