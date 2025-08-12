<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($Owner || $Admin) {
    ?>
    <ul id="myTab" class="nav nav-tabs">
        <li class=""><a href="#details" class="tab-grey"><?= lang('product_details') ?></a></li>
        <li class=""><a href="#chart" class="tab-grey"><?= lang('chart') ?></a></li>
        <li class=""><a href="#sales" class="tab-grey"><?= lang('sales') ?></a></li>
        <li class=""><a href="#quotes" class="tab-grey"><?= lang('quotes') ?></a></li>
        <?php if ($product->type == 'standard') {
        ?>
        <li class=""><a href="#purchases" class="tab-grey"><?= lang('purchases') ?></a></li>
        <li class=""><a href="#transfers" class="tab-grey"><?= lang('transfers') ?></a></li>
        <li class=""><a href="#damages" class="tab-grey"><?= lang('quantity_adjustments') ?></a></li>
        <?php } ?>
        <li class=""><a href="#using" class="tab-grey"><?= lang('stock_using') ?></a></li>
    </ul>

<div class="tab-content">
    <div id="details" class="tab-pane fade in">
        <?php } ?>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-file-text-o nb"></i> <?= $product->name . (SHOP && $product->hide != 1 ? ' (' . lang('shop_views') . ': ' . $product->views . ')' : ''); ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                <li>
                                    <a href="<?= admin_url('products/edit/' . $product->id) ?>">
                                        <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= admin_url('products/print_barcodes/' . $product->id) ?>">
                                        <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= admin_url('products/pdf/' . $product->id) ?>">
                                        <i class="fa fa-download"></i> <?= lang('pdf') ?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= lang('delete_product') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('products/delete/' . $product->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('product_details'); ?></p>

                        <div class="row">
                            <div class="col-sm-5">
                                <img src="<?= base_url() ?>assets/uploads/<?= $product->image ?>"
                                     alt="<?= $product->name ?>" class="img-responsive img-thumbnail"/>

                                <div id="multiimages" class="padding10">
                                    <?php if (!empty($images)) {
                                        echo '<a class="img-thumbnail" data-toggle="lightbox" data-gallery="multiimages" data-parent="#multiimages" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                                        foreach ($images as $ph) {
                                            echo '<div class="gallery-image"><a class="img-thumbnail" data-toggle="lightbox" data-gallery="multiimages" data-parent="#multiimages" href="' . base_url() . 'assets/uploads/' . $ph->photo . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $ph->photo . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                                            if ($Owner || $Admin || $GP['products-edit']) {
                                                echo '<a href="#" class="delimg" data-item-id="' . $ph->id . '"><i class="fa fa-times"></i></a>';
                                            }
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-striped dfTable table-right-left">
                                        <tbody>
                                        <tr>
                                            <td colspan="2" style="background-color:#FFF;"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:30%;"><?= lang('barcode_qrcode'); ?></td>
                                            <td style="width:70%;">
                                            <img src="<?= admin_url('misc/barcode/' . $product->code . '/' . $product->barcode_symbology . '/74/0'); ?>" alt="<?= $product->code; ?>" class="bcimg" />
                                                <?= $this->bpas->qrcode('link', urlencode(admin_url('products/view/' . $product->id)), 2); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('type'); ?></td>
                                            <td><?php echo lang($product->type); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('name'); ?></td>
                                            <td><?php echo $product->name; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('code'); ?></td>
                                            <td><?php echo $product->code; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('brand'); ?></td>
                                            <td><?= $brand ? $brand->name : ''; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('serial_no'); ?></td>
                                            <td><?= $product->serial_no; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('max_serial'); ?></td>
                                            <td><?= $product->max_serial; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('category'); ?></td>
                                            <td><?php echo $category->name; ?></td>
                                        </tr>
                                        <?php if ($product->subcategory_id) {
                                        ?>
                                            <tr>
                                                <td><?= lang('subcategory'); ?></td>
                                                <td><?php echo $subcategory->name; ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><?= lang('unit'); ?></td>
                                            <td><?= $unit ? $unit->name . ' (' . $unit->code . ')' : ''; ?></td>
                                        </tr>
                                        <?php if ($Owner || $Admin) {
                                        echo '<tr><td>' . lang('cost') . '</td><td>' . $this->bpas->formatMoney($product->cost) . '</td></tr>';
                                        echo '<tr><td>' . lang('price') . '</td><td>' . $this->bpas->formatMoney($product->price) . '</td></tr>';
                                        if ($product->promotion) {
                                            echo '<tr><td>' . lang('promotion') . '</td><td>' . $this->bpas->formatMoney($product->promo_price) . ' (' . $this->bpas->hrsd($product->start_date) . ' - ' . $this->bpas->hrsd($product->end_date) . ')</td></tr>';
                                        }
                                    } else {
                                        if ($GP['products-cost']) {
                                            echo '<tr><td>' . lang('cost') . '</td><td>' . $this->bpas->formatMoney($product->cost) . '</td></tr>';
                                        }
                                        if ($GP['products-price']) {
                                            echo '<tr><td>' . lang('price') . '</td><td>' . $this->bpas->formatMoney($product->price) . '</td></tr>';
                                            if ($product->promotion) {
                                                echo '<tr><td>' . lang('promotion') . '</td><td>' . $this->bpas->formatMoney($product->promo_price) . ' (' . $this->bpas->hrsd($product->start_date) . ' - ' . $this->bpas->hrsd($product->start_date) . ')</td></tr>';
                                            }
                                        }
                                    } ?>
                                        <?php if ($product->tax_rate) { ?>
                                            <tr>
                                                <td><?= lang('tax_rate'); ?></td>
                                                <td><?php echo $tax_rate->name; ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('tax_method'); ?></td>
                                                <td><?php echo $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($product->alert_quantity != 0) { ?>
                                            <tr>
                                                <td><?= lang('alert_quantity'); ?></td>
                                                <td><?php echo $this->bpas->formatQuantity($product->alert_quantity); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($variants) { ?>
                                            <tr>
                                                <td><?= lang('product_variants'); ?></td>
                                                <td><?php foreach ($variants as $variant) {
                                                echo '<span class="label label-primary">' . $variant->name . '</span> ';
                                            } ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) {
                                            ?>
                                            <h3 class="bold"><?= lang('custom_fields') ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('custom_field') ?></th>
                                                        <th><?= lang('value') ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    if ($product->cf1) {
                                                        echo '<tr><td>' . lang('pcf1') . '</td><td>' . $product->cf1 . '</td></tr>';
                                                    }
                                            if ($product->cf2) {
                                                echo '<tr><td>' . lang('pcf2') . '</td><td>' . $product->cf2 . '</td></tr>';
                                            }
                                            if ($product->cf3) {
                                                echo '<tr><td>' . lang('pcf3') . '</td><td>' . $product->cf3 . '</td></tr>';
                                            }
                                            if ($product->cf4) {
                                                echo '<tr><td>' . lang('pcf4') . '</td><td>' . $product->cf4 . '</td></tr>';
                                            }
                                            if ($product->cf5) {
                                                echo '<tr><td>' . lang('pcf5') . '</td><td>' . $product->cf5 . '</td></tr>';
                                            }
                                            if ($product->cf6) {
                                                echo '<tr><td>' . lang('pcf6') . '</td><td>' . $product->cf6 . '</td></tr>';
                                            } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                        <?php if ((!$Supplier || !$Customer) && !empty($warehouses) && $product->type == 'standard') { ?>
                                            <h3 class="bold"><?= lang('warehouse_quantity') ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('warehouse_name') ?></th>
                                                        <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($warehouses as $warehouse) {
                                                if ($warehouse->quantity != 0) {
                                                    echo '<tr><td>' . $warehouse->name . ' (' . $warehouse->code . ')</td><td><strong>' . $this->bpas->formatQuantity($warehouse->quantity) . '</strong>' . ($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') . '</td></tr>';
                                                }
                                            } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-sm-7">
                                        <?php if ($product->type == 'combo') { ?>
                                            <h3 class="bold"><?= lang('combo_items') ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('product_name') ?></th>
                                                        <th><?= lang('quantity') ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php if ($combo_items) {
                                                        foreach ($combo_items as $combo_item) {
                                                        echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->bpas->formatQuantity($combo_item->qty) . '</td></tr>';
                                                        }
                                                    } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                        <?php if (!empty($options) && $product->type == 'standard') { ?>
                                            <h3 class="bold"><?= lang('product_variants_quantity'); ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('warehouse_name') ?></th>
                                                        <th><?= lang('product_variant'); ?></th>
                                                        <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                                        <?php if ($Owner || $Admin) {
                                                echo '<th>' . lang('cost') . '</th>';
                                                echo '<th>' . lang('price') . '</th>';
                                            } ?>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    foreach ($options as $option) {
                                                        if ($option->wh_qty != 0) {
                                                            echo '<tr><td>' . $option->wh_name . '</td><td>' . $option->name . '</td><td class="text-center">' . $this->bpas->formatQuantity($option->wh_qty) . '</td>';
                                                            if ($Owner || $Admin && (!$Customer || $GP['products-cost'])) {
                                                                echo '<td class="text-right">' . $this->bpas->formatMoney($option->cost) . '</td><td class="text-right">' . $this->bpas->formatMoney($option->price) . '</td>';
                                                            }
                                                            echo '</tr>';
                                                        }
                                                    } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                    <?php if($addon_items){ ?>
                                            <h3 class="bold"><?= lang('addOn_items') ?></h3>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 33%"><?= lang('product_name') ?></th>
                                                        <th><?= lang('description') ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($addon_items as $addon_item) {
                                                        echo '<tr>
                                                                <td style="width: 33%">' . $addon_item->name . ' (' . $addon_item->code . ')</td>
                                                                <td>' . $addon_item->description . '</td>
                                                            </tr>';
                                                    } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">

                                <?= $product->details ? '<div class="panel panel-success"><div class="panel-heading">' . lang('product_details_for_invoice') . '</div><div class="panel-body">' . $product->details . '</div></div>' : ''; ?>
                                <?= $product->product_details ? '<div class="panel panel-primary"><div class="panel-heading">' . lang('product_details') . '</div><div class="panel-body">' . $product->product_details . '</div></div>' : ''; ?>

                            </div>
                        </div>

                        <?php if (!$Supplier || !$Customer) {
                                            ?>
                        <div class="buttons">
                            <div class="btn-group btn-group-justified">
                                <div class="btn-group">
                                    <a href="<?= admin_url('products/print_barcodes/' . $product->id) ?>" class="tip btn btn-primary" title="<?= lang('print_barcode_label') ?>">
                                        <i class="fa fa-print"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('print_barcode_label') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="<?= admin_url('products/pdf/' . $product->id) ?>" class="tip btn btn-primary" title="<?= lang('pdf') ?>">
                                        <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="<?= admin_url('products/edit/' . $product->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit_product') ?>">
                                        <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="#" class="tip btn btn-danger bpo" title="<b><?= lang('delete_product') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('products/delete/' . $product->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="top">
                                        <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.tip').tooltip();
            });
        </script>
        <?php } ?>

        <?php if ($Owner || $Admin) { ?>
    </div>
    <div id="chart" class="tab-pane fade">
        <script src="<?= $assets; ?>js/hc/highcharts.js"></script>
        <script type="text/javascript">
            $(function () {
                Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
                    return {
                        radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                        stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
                    };
                });
                <?php if ($sold) {
                                                ?>
                var sold_chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'soldchart',
                        type: 'line',
                        width: <?= $purchased ? "($('#details').width()-160)/2" : "$('#details').width()-100"; ?>
                    },
                    credits: {enabled: false},
                    title: {text: ''},
                    xAxis: {
                        categories: [<?php
                    foreach ($sold as $r) {
                        $month = explode('-', $r->month);
                        echo "'" . lang('cal_' . strtolower($month[1])) . ' ' . $month[0] . "', ";
                    } ?>]
                    },
                    yAxis: {min: 0, title: ""},
                    legend: {enabled: false},
                    tooltip: {
                        shared: true,
                        followPointer: true,
                        formatter: function () {
                            var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;min-width:150px;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                            $.each(this.points, function () {
                                if (this.series.name == '<?= lang('amount'); ?>') {
                                    s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                    currencyFormat(this.y) + '</b></td></tr>';
                                } else {
                                    s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                    formatQuantity(this.y) + '</b></td></tr>';
                                }
                            });
                            s += '</table></div>';
                            return s;
                        },
                        useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                        style: {fontSize: '14px', padding: '0', color: '#000000'}
                    },
                    series: [{
                        type: 'spline',
                        name: '<?= lang('sold'); ?>',
                        data: [<?php
                        foreach ($sold as $r) {
                            $month = explode('-', $r->month);
                            echo "['" . lang('cal_' . strtolower($month[1])) . ' ' . $month[0] . "', " . $r->sold . '],';
                            // echo "['".lang('cal_'.strtolower($r->month))."', ".$r->sold."],";
                        } ?>]
                    }, {
                        type: 'spline',
                        name: '<?= lang('amount'); ?>',
                        data: [<?php
                        foreach ($sold as $r) {
                            $month = explode('-', $r->month);
                            echo "['" . lang('cal_' . strtolower($month[1])) . ' ' . $month[0] . "', " . $r->amount . '],';
                            // echo "['".lang('cal_'.strtolower($r->month))."', ".$r->amount."],";
                        } ?>]
                    }]
                });
                $(window).resize(function () {
                    sold_chart.setSize($('#soldchart').width(), 450);
                });
                <?php
                                            }
                                            if ($purchased) {
                                                ?>
                var purchased_chart = new Highcharts.Chart({
                    chart: {renderTo: 'purchasedchart', type: 'line', width: ($('#details').width() - 160) / 2},
                    credits: {enabled: false},
                    title: {text: ''},
                    xAxis: {
                        categories: [<?php
        foreach ($purchased as $r) {
            $month = explode('-', $r->month);
            echo "'" . lang('cal_' . strtolower($month[1])) . ' ' . $month[0] . "', ";
        } ?>]
                    },
                    yAxis: {min: 0, title: ""},
                    legend: {enabled: false},
                    tooltip: {
                        shared: true,
                        followPointer: true,
                        formatter: function () {
                            var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;min-width:150px;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                            $.each(this.points, function () {
                                if (this.series.name == '<?= lang('amount'); ?>') {
                                    s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                    currencyFormat(this.y) + '</b></td></tr>';
                                } else {
                                    s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                    formatQuantity(this.y) + '</b></td></tr>';
                                }
                            });
                            s += '</table></div>';
                            return s;
                        },
                        useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                        style: {fontSize: '14px', padding: '0', color: '#000000'}
                    },
                    series: [{
                        type: 'spline',
                        name: '<?= lang('purchased'); ?>',
                        data: [<?php
            foreach ($purchased as $r) {
                $month = explode('-', $r->month);
                echo "['" . lang('cal_' . strtolower($month[1])) . ' ' . $month[0] . "', " . $r->purchased . '],';
                // echo "['".lang('cal_'.strtolower($r->month))."', ".$r->purchased."],";
            } ?>]
                    }, {
                        type: 'spline',
                        name: '<?= lang('amount'); ?>',
                        data: [<?php
            foreach ($purchased as $r) {
                $month = explode('-', $r->month);
                echo "['" . lang('cal_' . strtolower($month[1])) . ' ' . $month[0] . "', " . $r->amount . '],';
                // echo "['".lang('cal_'.strtolower($r->month))."', ".$r->amount."],";
            } ?>]
                    }]
                });
                $(window).resize(function () {
                    purchased_chart.setSize($('#purchasedchart').width(), 450);
                });
                <?php
                                            } ?>

            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o nb"></i><?= lang('chart'); ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-sm-<?= $purchased ? '6' : '12'; ?>">
                                <div class="box" style="border-top: 1px solid #dbdee0;">
                                    <div class="box-header">
                                        <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('sold'); ?>
                                        </h2>
                                    </div>
                                    <div class="box-content">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div id="soldchart" style="width:100%; height:450px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($purchased) {
                                                ?>
                                <div class="col-sm-6">
                                    <div class="box" style="border-top: 1px solid #dbdee0;">
                                        <div class="box-header">
                                            <h2 class="blue"><i
                                                    class="fa-fw fa fa-bar-chart-o"></i><?= lang('purchased'); ?></h2>
                                        </div>
                                        <div class="box-content">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div id="purchasedchart" style="width:100%; height:450px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="sales" class="tab-pane fade">
        <?php $warehouse_id = null; ?>
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#SlRData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/getSalesReport/?v=1&product=' . $product->id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        nRow.id = aData[14];
                        nRow.className = (aData[8] > 0) ? "invoice_link2" : "invoice_link2 warning";
                        return nRow;
                        },
                        "aoColumns": [
                            {"mRender": fld}, null, null, null, null, null, null, null, 
                            {"bSearchable": false, "mRender": pqFormat},
                            {"mRender": currencyFormat}, 
                            {"mRender": currencyFormat}, 
                            {"mRender": currencyFormat}, 
                            {"mRender": currencyFormat}, 
                            {"mRender": row_status}],
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var rgtotal = 0, gtotal = 0, paid = 0, balance = 0,customer_total = 0,qty = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            qty += QtyFormat(aaData[aiDisplay[i]][8]);
                            rgtotal += parseFloat(aaData[aiDisplay[i]][9]);
                            gtotal += parseFloat(aaData[aiDisplay[i]][10]);
                            paid += parseFloat(aaData[aiDisplay[i]][11]);
                            balance += parseFloat(aaData[aiDisplay[i]][12]);
                            if(aaData[aiDisplay[i]][7] != null){
                                customer_total += parseFloat(aaData[aiDisplay[i]][7]);    
                            }
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        nCells[7].innerHTML = formatQuantity(parseFloat(customer_total));
                        nCells[8].innerHTML = formatQuantity(qty);
                        nCells[9].innerHTML = currencyFormat(parseFloat(rgtotal));
                        nCells[10].innerHTML = currencyFormat(parseFloat(gtotal));
                        nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                        nCells[12].innerHTML = currencyFormat(parseFloat(balance));
                    }
                    }).fnSetFilteringDelay().dtFilter([
                        {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                        {column_number: 1, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
                        {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
                        {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
                        {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
                        {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
                        {column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
                        {column_number: 7, filter_default_label: "[<?=lang('customer').' (QTY)';?>]", filter_type: "text", data: []},
                        {column_number: 13, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
                    ], "footer");
                });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= $product->name . ' ' . lang('sales'); ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip image" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="SlRData" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"><?= lang('date'); ?></th>
                                        <th style="width: 50px;"><?= lang('project'); ?></th>
                                        <th><?= lang('reference_no'); ?></th>
                                        <th style="width: 50px;"><?= lang('biller'); ?></th>
                                        <th style="width: 50px;"><?= lang('customer'); ?></th>
                                        <th style="width: 40px;"><?= lang('phone'); ?></th>
                                        <th style="width: 20px;"><?= lang('address'); ?></th>
                                        <th><?= lang('customer').' (Qty)'; ?></th>
                                        <th><?= lang('product_qty'); ?></th>
                                        <th><?= lang('real_grand_total'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th><?= lang('payment_status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="10"
                                        class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="width: 50px;"></th>
                                        <th style="width: 50px;"></th>
                                        <th></th>
                                        <th style="width: 50px;"></th>
                                        <th style="width: 50px;"></th>
                                        <th style="width: 40px;"></th>
                                        <th style="width: 20px;"></th>
                                        <th><?= lang('customer_total'); ?></th>
                                        <th><?= lang('product_qty'); ?></th>
                                        <th><?= lang('real_grand_total'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="quotes" class="tab-pane fade">
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#QuRData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/getQuotesReport/?v=1&product=' . $product->id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[7];
                        nRow.className = "quote_link2";
                        return nRow;
                    },
                    "aoColumns": [{"mRender": fld}, null, null, null, {
                        "bSearchable": false,
                        "mRender": pqFormat
                    }, {"mRender": currencyFormat}, {"mRender": row_status}],
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var qty = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            qty += QtyFormat(aaData[aiDisplay[i]][4]);
                        }
                        var nCells = nRow.getElementsByTagName('th')
                        nCells[4].innerHTML = formatQuantity(qty);
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('customer'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('grand_total'); ?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?=lang('status'); ?>]", filter_type: "text", data: []},
                ], "footer");
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?= $product->name . ' ' . lang('quotes'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="QuRData" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('reference_no'); ?></th>
                                    <th><?= lang('biller'); ?></th>
                                    <th><?= lang('customer'); ?></th>
                                    <th><?= lang('product_qty'); ?></th>
                                    <th><?= lang('grand_total'); ?></th>
                                    <th><?= lang('status'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="7"
                                        class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang('product_qty'); ?></th>
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
    </div>
    <div id="purchases" class="tab-pane fade">
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#PoRData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/getPurchasesReport/?v=1&product=' . $product->id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        nRow.id = aData[10];
                        nRow.className = (aData[6] > 0) ? "purchase_link2" : "purchase_link2 warning";
                        return nRow;
                    },
                    "aoColumns": [{"mRender": fld}, null, null, null, null, {
                        "bSearchable": false,
                        "mRender": pqFormat
                    }, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var gtotal = 0, paid = 0, balance = 0,qty=0;
                        for (var i = 0; i < aaData.length; i++) {
                            qty += QtyFormat(aaData[aiDisplay[i]][5]);
                            gtotal += parseFloat(aaData[aiDisplay[i]][6]);
                            paid += parseFloat(aaData[aiDisplay[i]][7]);
                            balance += parseFloat(aaData[aiDisplay[i]][8]);
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        nCells[5].innerHTML = formatQuantity(parseFloat(qty));
                        nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                        nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                        nCells[8].innerHTML = currencyFormat(parseFloat(balance));
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('project'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('warehouse'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('supplier'); ?>]", filter_type: "text", data: []},
                    {column_number: 9, filter_default_label: "[<?=lang('status'); ?>]", filter_type: "text", data: []},
                ], "footer");
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-star nb"></i><?= $product->name . ' ' . lang('purchases'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="pdf2" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls2" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image2" class="tip image" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="PoRData" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('project'); ?></th>
                                    <th><?= lang('reference_no'); ?></th>
                                    <th><?= lang('warehouse'); ?></th>
                                    <th><?= lang('supplier'); ?></th>
                                    <th><?= lang('product_qty'); ?></th>
                                    <th><?= lang('grand_total'); ?></th>
                                    <th><?= lang('paid'); ?></th>
                                    <th><?= lang('balance'); ?></th>
                                    <th><?= lang('status'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="10"
                                        class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang('product_qty'); ?></th>
                                    <th><?= lang('grand_total'); ?></th>
                                    <th><?= lang('paid'); ?></th>
                                    <th><?= lang('balance'); ?></th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="transfers" class="tab-pane fade">
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#TrRData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/getTransfersReport/?v=1&product=' . $product->id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[7];
                        nRow.className = "transfer_link2";
                        return nRow;
                    },
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var gtotal = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            gtotal += parseFloat(aaData[aiDisplay[i]][5]);;
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        nCells[5].innerHTML = currencyFormat(formatMoney(gtotal));
                    },
                    "aoColumns": [{"mRender": fld}, null, {
                        "bSearchable": false,
                        "mRender": pqFormat
                    }, null, null, {"mRender": currencyFormat}, {"mRender": row_status}],

                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var qty = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            qty += QtyFormat(aaData[aiDisplay[i]][2]);
                        }
                        var nCells = nRow.getElementsByTagName('th')
                        nCells[2].innerHTML = formatQuantity(qty);
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('warehouse') . ' (' . lang('from') . ')'; ?>]",filter_type: "text", data: [] },
                    {column_number: 4, filter_default_label: "[<?=lang('warehouse') . ' (' . lang('to') . ')'; ?>]",filter_type: "text", data: [] },
                    {column_number: 5, filter_default_label: "[<?=lang('grand_total'); ?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?=lang('status'); ?>]", filter_type: "text", data: []},
                ], "footer");
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-star-o nb"></i><?= $product->name . ' ' . lang('transfers'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown"><a href="#" id="pdf3" class="tip" title="<?= lang('download_pdf') ?>"><i
                                    class="icon fa fa-file-pdf-o"></i></a></li>
                        <li class="dropdown"><a href="#" id="xls3" class="tip" title="<?= lang('download_xls') ?>"><i
                                    class="icon fa fa-file-excel-o"></i></a></li>
                        <li class="dropdown"><a href="#" id="image3" class="tip image"
                                                title="<?= lang('save_image') ?>"><i
                                    class="icon fa fa-file-picture-o"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="TrRData" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('reference_no'); ?></th>
                                    <th><?= lang('product_qty'); ?></th>
                                    <th><?= lang('warehouse') . ' (' . lang('from') . ')'; ?></th>
                                    <th><?= lang('warehouse') . ' (' . lang('to') . ')'; ?></th>
                                    <th><?= lang('grand_total'); ?></th>
                                    <th><?= lang('status'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="7"
                                        class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th><?= lang('product_qty'); ?></th>
                                    <th></th>
                                    <th></th>
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
    </div>
    <div id="damages" class="tab-pane fade">
        <script>
            $(document).ready(function () {
                oTable = $('#dmpData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/getAdjustmentReport/?v=1&product=' . $product->id); ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },
                    "aoColumns": [{"mRender": fld}, null, null, null, {"mRender": decode_html}, {"bSortable": false, "mRender": pqFormat}],

                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        nRow.id = aData[6];
                        nRow.className = "adjustment_link2";
                        return nRow;
                    },
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var qty = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            qty += QtyFormat(aaData[aiDisplay[i]][5]);
                        }
                        var nCells = nRow.getElementsByTagName('th')
                        nCells[5].innerHTML = formatQuantity(qty);
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('warehouse'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('created_by'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang(' note'); ?>]", filter_type: "text", data: []},
                ], "footer");
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('adjustments_report'); ?><?php
                    if ($this->input->post('start_date')) {
                        echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                    } ?>
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
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                                <thead>
                                <tr>
                                    <th class="col-xs-2"><?= lang('date'); ?></th>
                                    <th class="col-xs-2"><?= lang('reference_no'); ?></th>
                                    <th class="col-xs-2"><?= lang('warehouse'); ?></th>
                                    <th class="col-xs-1"><?= lang('created_by'); ?></th>
                                    <th><?= lang('note'); ?></th>
                                    <th class="col-xs-2"><?= lang('products'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th><th></th><th></th><th></th><th></th>
                                    <th><?= lang('products'); ?></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="using" class="tab-pane fade">
        <?php 
            $v='';
            if ($this->input->post('start_date')) {
                $v .= "&start_date=" . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= "&end_date=" . $this->input->post('end_date');
            }
            if ($this->input->post('referno')) {
                $v .= "&referno=" . $this->input->post('referno');
            }
            if ($this->input->post('empno')) {
                $v .= "&empno=" . $this->input->post('empno');
            }
            if ($this->input->post('plan')) {
                $v .= "&plan=" . $this->input->post('plan');
            }
            $v .= "&product=".$product->id;
        ?>
        <script>
            $(document).ready(function () {
                function row_statusX(x) {
                    if(x == null) {
                        return '';
                    } else if(x == 'return' || x == 'book' || x == 'free') {
                        return '<div class="text-center"><span class="label label-warning">'+lang[x]+'</span></div>';
                    } else if(x == 'use' || x == 'paid' || x == 'sent' || x == 'received') {
                        return '<div class="text-center"><span class="label label-success">'+lang[x]+'</span></div>';
                    } else if(x == 'partial' || x == 'partial_payment' || x == 'transferring' || x == 'ordered'  || x == 'busy'  || x == 'processing') {
                        return '<div class="text-center"><span class="label label-info">'+lang[x]+'</span></div>';
                    } else if(x == 'due' || x == 'returned') {
                        return '<div class="text-center"><span class="label label-danger">'+lang[x]+'</span></div>';
                    } else {
                        return '<div class="text-center"><span class="label label-default">'+lang[x]+'</span></div>';
                    }
                }
                $('#UnitTable').dataTable({
                    "aaSorting": [[7, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('products/get_using_stock').'/?v=1'.$v ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },

                    'fnRowCallback': function (nRow, aaData, iDisplayIndex) {
                            var action = $('td:eq(10)', nRow);
                            var returned = aaData[9];
                            if (returned=="return") {
                                action.find('.add_return').remove();
                                action.find('.edit_using').remove();
                            } else {
                                action.find('.edit_return').remove();
                            }
                        return nRow;
                    },
                      "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                {"mRender": fld},
                null,
                null,
                null,
                null,
                null,
                {"sClass":"center"},
                null,
                {"mRender":row_statusX},
                {"bSortable": false, "sClass":"center"}],
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var gtotal = 0, paid = 0, balance = 0, status = ' ';
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 1, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('reference_no');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('Project');?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('home_type');?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?=lang('Warehouse');?>]", filter_type: "text", data: []},
                    {column_number: 7, filter_default_label: "[<?=lang('Employee');?>]", filter_type: "text", data: []},
                    {column_number: 8, filter_default_label: "[<?=lang('Description');?>]", filter_type: "text", data: []},
                    {column_number: 9, filter_default_label: "[<?=lang('Status');?>]", filter_type: "text", data: []},
                ], "footer");
            });
            $(document).ready(function () {
                $('#form_using').hide();
                $('.toggle_down_using').click(function () {
                    $("#form_using").slideDown();
                    return false;
                });
                $('.toggle_up_using').click(function () {
                    $("#form_using").slideUp();
                    return false;
                });
                $("#product").autocomplete({
                    source: '<?= admin_url('reports/suggestions'); ?>',
                    select: function (event, ui) {
                        $('#product_id').val(ui.item.id);
                    },
                    minLength: 1,
                    autoFocus: false,
                    delay: 300,
                });
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue">
                    <i class="fa-fw fa fa-barcode"></i>
                    <?= lang('list_using_stock'); ?>
                    (
                        <?php
                            if (count($warehouses) > 1) {
                                echo lang('all_warehouses');
                            } else {
                                foreach ($warehouses as $ware) {
                                    echo $ware->name;
                                }
                            }
                        ?>
                    )
                </h2>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="toggle_up_using tip" title="<?= lang('hide_form') ?>">
                                <i class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="toggle_down_using tip" title="<?= lang('show_form') ?>">
                                <i class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="excel" data-action="export_excel" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang('actions')?>"></i></a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                <li>
                                    <a href="<?=admin_url('products/add_using_stock')?>">
                                        <i class="fa fa-plus-circle"></i> <?=lang('add_stock_using')?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?=lang('delete_using_stock')?></b>"
                                        data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                        data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?=lang('delete_using_stock')?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div style="display: none;">
                <input type="hidden" name="form_action" value="" id="form_action"/>
                <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
            </div>
            <?= form_close()?> 
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?=lang('list_results');?></p>
                        <div id="form_using">
                           <?php echo admin_form_open("products/view_enter_using_stock"); ?>            
                            <div class="box-content">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <?= lang("start_date", "start_date"); ?>
                                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control tip date" id="start_date"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <?= lang("end_date", "end_date"); ?>
                                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>             
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                         <div class="form-group">
                                                <label class="control-label" for="user"><?= lang("reference_no"); ?></label>
                                                <?php
                                                $ust = array(""=>"ALL");
                                                foreach ($enter_using_stock as $es) {
                                                    $ust[$es->reference_no] = $es->reference_no;
                                                }
                                                echo form_dropdown('referno', $ust, (isset($_POST['referno']) ? $_POST['referno'] : ""), 'class="form-control" id="referno2" ');
                                                ?>
                                        </div>      
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                                <label class="control-label" for="user"><?= lang("Employee_No"); ?></label>
                                                <?php
                                                $emps = array(""=>"ALL");
                                                foreach ($empno as $es) {
                                                    $emps[$es->username] = $es->username;
                                                }
                                                echo form_dropdown('empno', $emps, (isset($_POST['empno']) ? $_POST['empno'] : ''), 'class="form-control" id="empno2" ');
                                                ?>
                                        </div>      
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                                <label class="control-label" for="user"><?= lang("home_type"); ?></label>
                                                <?php
                                                $pl = array(""=>"ALL");
                                                if (!empty($plans)) {
                                                    foreach ($plans as $plan) {
                                                        $pl[$plan->id] = $plan->title;
                                                    }
                                                }
                                                echo form_dropdown('plan', $pl, (isset($_POST['plan']) ? $_POST['plan'] : ''), 'class="form-control" id="empno2" ');
                                                ?>
                                        </div>      
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary input-xs">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table id="UnitTable" class="table table-condensed table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkth" type="checkbox" name="check"/>
                                        </th>
                                        <th><?php echo $this->lang->line("date"); ?></th>  
                                        <th><?php echo $this->lang->line("reference_no"); ?></th>
                                        <th><?php echo $this->lang->line("biller"); ?></th>
                                        <th><?php echo $this->lang->line("home_type"); ?></th>
                                        <th><?php echo $this->lang->line("address"); ?></th>
                                        <th><?php echo $this->lang->line("warehouse"); ?></th>
                                        <th><?php echo $this->lang->line("employee"); ?></th>
                                        <th><?php echo $this->lang->line("description"); ?></th>
                                        <th><?php echo $this->lang->line("status");?></th>
                                        <th style="width:100px;"><?= lang("actions"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="9" class="dataTables_empty">
                                            <?= lang('loading_data_from_server') ?>
                                        </td>
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
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th><?=lang('action');?></th>
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
                window.location.href = "<?=admin_url('reports/getSalesReport/pdf/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#xls').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getSalesReport/0/xls/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#pdf1').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getQuotesReport/pdf/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#xls1').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getQuotesReport/0/xls/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#pdf2').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getPurchasesReport/pdf/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#xls2').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getPurchasesReport/0/xls/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#pdf3').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getTransfersReport/pdf/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#xls3').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getTransfersReport/0/xls/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#pdf4').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('products/getadjustments/pdf/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#xls4').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('products/getadjustments/0/xls/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#pdf5').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getAdjustmentReport/pdf/?v=1' . $v)?>";
                return false;
            });
            $('#xls5').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getAdjustmentReport/0/xls/?v=1' . $v)?>";
                return false;
            });
            $('.image').click(function (event) {
                var box = $(this).closest('.box');
                event.preventDefault();
                html2canvas(box, {
                    onrendered: function (canvas) {
                        openImg(canvas.toDataURL());
                    }
                });
                return false;
            });
        });
    </script>
<?php
} ?>
