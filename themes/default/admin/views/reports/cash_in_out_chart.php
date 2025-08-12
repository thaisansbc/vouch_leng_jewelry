<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
    $netsale = $total_sales->total_amount - 0 - (-$getReturnOnSales->total_amount);
    $payments_received = $total_payment_received->total_amount;
    $netpurchase = $total_purchases->total_amount;
    if ($this->Settings->profit_loss_method) {
        $cost_of_good = $total_sales_costing->cost;
        $case_in_out = $netsale - $total_return_sales->total_amount - $netpurchase - $total_expenses->total_amount - $total_expenses_budget->total_amount;
        $profit_loss = $netsale - $cost_of_good - $total_return_sales->total_amount - $total_expenses->total_amount - $total_expenses_budget->total_amount;
    } else {
        $cost_of_good = $total_sales_costing_by_payment->cost;
        $case_in_out = $payments_received - $total_return_sales->total_amount -$netpurchase - $total_expenses->total_amount - $total_expenses_budget->total_amount;
        $profit_loss = $payments_received - $cost_of_good - $total_return_sales->total_amount - $total_expenses->total_amount - $total_expenses_budget->total_amount;
    }
?>
<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('reports/cash_in_out_chart'); ?>';
    });
</script>
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
                    if(this.key == 'Case In/Out') {
                        if (this.point.positive) {
                            return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                        } else {
                            return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y * (-1)) + '</strong> (' + formatNumber(this.percentage) + '%)';
                        }
                    } else {
                        return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                    }
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
                            if(this.key == 'Case In/Out') {
                                if (this.point.positive) {
                                    return '<h3 style="margin:-15px 0 10px 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y) + '</b></h3>';    
                                } else {
                                    return '<h3 style="margin:-15px 0 10px 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y * (-1)) + '</b></h3>';    
                                }
                            } else {
                                return '<h3 style="margin:-15px 0 10px 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y) + '</b></h3>';
                            }
                        },
                        useHTML: true
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line('cash_in/out_chart'); ?>',
                data: [
                    [
                        <?php if ($this->Settings->profit_loss_method) { ?>
                            '<?php echo $this->lang->line('gross_profit_net_sales'); ?>', <?php echo $netsale ? $netsale : 0; ?>
                        <?php } else { ?>
                            '<?php echo $this->lang->line('sales_by_payments_received'); ?>', <?php echo $total_payment_received->total_amount ? $total_payment_received->total_amount : 0; ?>
                        <?php } ?>
                    ],
                    ['<?php echo $this->lang->line('returns'); ?>', <?php echo $total_return_sales->total_amount ? $total_return_sales->total_amount : 0; ?>],
                    ['<?php echo $this->lang->line('cost_of_good'); ?>', <?php echo $netpurchase ? $netpurchase : 0; ?>],
                    ['<?php echo $this->lang->line('expenses'); ?>', <?php echo ($total_expenses->total_amount ? $total_expenses->total_amount : 0) + ($total_expenses_budget->total_amount ? $total_expenses_budget->total_amount : 0); ?>],
                    {name: '<?php echo $this->lang->line('case_in_out'); ?>', y:<?php echo $case_in_out ? abs($case_in_out) : 0; ?>, positive: <?php echo (($case_in_out < 0) ? 'false' : 'true'); ?>}
                ]
            }]
        });
    });
</script>
<?php if ($Owner || $Admin) { ?>
    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('cash_in/out_chart'); ?></h2>
            <div class="box-icon">
                <div class="form-group choose-date hidden-xs">
                    <div class="controls">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text"
                                value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                                id="daterange" class="form-control">
                            <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <?php if (!empty($warehouses) && ($Owner || $Admin)) { ?>
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                <li><a href="<?= admin_url('reports/top10_profit_chart') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                                <li class="divider"></li>
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/cash_in_out_chart/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                    <div id="chart" style="width:100%; height:450px;"></div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>