<?php defined('BASEPATH') or exit('No direct script access allowed'); 
 
    // var_dump($stocks);
    // exit();
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
               tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
             xAxis: {
        categories: [
            <?php foreach($stocks as $stock){ ?>
            ['<?php echo $stock->product_name.'('.$stock->product_code.')'; ?>'],
            <?php }?>
        ],
        crosshair: true
    },
            series: [ {
         type: 'column',
        name: 'Cost Amount',
        data: [<?php foreach($stocks as $stock){ ?>
            [<?php echo $stock->cost_amount; ?>],
            <?php }?>]

    }, {
         type: 'column',
        name: 'Price Amount',
        data: [<?php foreach($stocks as $stock){ ?>
            [<?php echo $stock->price_amount; ?>],
            <?php }?>]

    }, {
        type: 'column',
        name: 'Profit Amount',
        data: [<?php foreach($stocks as $stock){ ?>
            [<?php echo $stock->profit; ?>],
            <?php }?>]

    }]
            // series: [
           
            //     { 
            //         type: 'column',
            //         name: '<?php echo 'profit'; ?>',
            //         data: [ <?php foreach($stocks as $stock){ ?>
            //                 ['<?php echo $stock->product_name.'('.$stock->product_code.')'; ?>',<?php echo $stock->profit; ?>],
            //                 <?php }?>
            //                 ]
            //     }
            
                   
            // ]
      
        });

    });
     $(function () {
        // Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
        //     return {
        //         radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
        //         stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
        //     };
        // });
        $('#chart2').highcharts({
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
               tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
             xAxis: {
        categories: [
            <?php foreach($stocks as $stock){ ?>
            ['<?php echo $stock->product_name.'('.$stock->product_code.')'; ?>'],
            <?php }?>
        ],
        crosshair: true
    },
            series: [ {
        name: 'Cost Amount',
        data: [<?php foreach($stocks as $stock){ ?>
            [<?php echo $stock->cost_amount; ?>],
            <?php }?>]

    }, {
        name: 'Price Amount',
        data: [<?php foreach($stocks as $stock){ ?>
            [<?php echo $stock->price_amount; ?>],
            <?php }?>]

    }, {
        name: 'Profit Amount',
        data: [<?php foreach($stocks as $stock){ ?>
            [<?php echo $stock->profit; ?>],
            <?php }?>]

    }]
            // series: [
           
            //     { 
            //         type: 'column',
            //         name: '<?php echo 'profit'; ?>',
            //         data: [ <?php foreach($stocks as $stock){ ?>
            //                 ['<?php echo $stock->product_name.'('.$stock->product_code.')'; ?>',<?php echo $stock->profit; ?>],
            //                 <?php }?>
            //                 ]
            //     }
            
                   
            // ]
      
        });

    });
</script>
<!-- Highcharts.chart('container', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Monthly Average Rainfall'
    },
    subtitle: {
        text: 'Source: WorldClimate.com'
    },
    xAxis: {
        categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ],
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Rainfall (mm)'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [{
        name: 'Tokyo',
        data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]

    }, {
        name: 'New York',
        data: [83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3]

    }, {
        name: 'London',
        data: [48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2]

    }, {
        name: 'Berlin',
        data: [42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]

    }]
}); -->
 <!-- series: [
            <?php foreach($stocks as $stock): ?>
                { 
                    type: 'column',
                    name: '<?php echo $stock->product_name.' ('.$stock->product_code.')'; ?>',
                    data: [<?php echo $stock->quantity; ?>,
                            <?php echo $stock->cost_amount; ?>,
                            <?php echo $stock->price_amount; ?>,
                            <?php echo $stock->profit; ?>]
                },
            <?php endforeach;?>
                   
            ] -->
 <!-- series: [{
        name: 'John',
        data: [5, 3, 4, 7, 2]
    }, {
        name: 'Jane',
        data: [2, -2, -3, 2, 1]
    }, {
        name: 'Joe',
        data: [3, 4, 4, -2, 5]
    }] -->
<?php if ($Owner || $Admin) {
    ?>
    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i
                    class="fa-fw fa fa-bar-chart-o"></i><?= lang('top10_profit') . ' (' . ($warehouse ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
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
                                <li><a href="<?= admin_url('reports/top10_profit_chart') ?>"><i
                                            class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                                <li class="divider"></li>
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/top10_profit_chart/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                    <p class="introtext"><?php echo lang('top10_profit_chart_heading'); ?></p>
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
                      <div id="chart2" style="width:100%; height:450px;"></div>
                </div>
            </div>
        </div>
    </div>
<?php
} ?>
