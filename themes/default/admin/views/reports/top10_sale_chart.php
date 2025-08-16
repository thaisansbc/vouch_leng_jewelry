<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
                    },
                     animation: {
                        duration: 2000
                    }, 
                    innerSize: 200,
                    depth: 20
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line('top10_sales'); ?>',
                data: [
                    <?php 
                    if($stocks){
						foreach($stocks as $stock){
					?>
							['<?php echo $stock->product_name.' ('.$stock->product_code.')'; ?>', <?php echo $stock->quantity; ?>],
					<?php
						}
                    }else { ?>
                        ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 0; ?>],
                    <?php }
					?>
                ]

            }]
        });

    });
</script>

<?php if ($Owner || $Admin) {
    ?>
    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i
                    class="fa-fw fa fa-bar-chart-o"></i><?= lang('top10_sale') . ' (' . ($warehouse ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
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
                                <li><a href="<?= admin_url('reports/top10_sale_chart') ?>"><i
                                            class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                                <li class="divider"></li>
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/top10_sale_chart/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } ?>
                            </ul>
                        </li>
                    <?php
    } ?>
                </ul>
            </div>
        </div>
        	<?php	
            $total_amount = 0 ;
            foreach($stocks as $stock){
				$total_amount += $stock->quantity;
            } 
            ?>
        <div  class="box-content">
            <div class="row">
                <div  class="col-lg-12">
                    <p class="introtext"><?php echo lang('top10_sale_chart_heading'); ?></p>
                    <?php if ($totals) {
                        ?>
                        
                        <div class="small-box padding1010 col-sm-6 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $this->bpas->formatQuantity(sizeof($stocks)) ?></h3>
                                    <p><?= lang('total_items') ?></p>
                                </a>
                            </div>
                        </div>

                        <div  class="small-box padding1010 col-sm-6 bdarkGreen">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $this->bpas->formatQuantity($total_amount) ?></h3>
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
<?php
} ?>
