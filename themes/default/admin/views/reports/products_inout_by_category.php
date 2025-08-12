<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;

        }

        .form_org {
            display: none !important;
        }

        .print_form {
            text-align: left !important;
        }

        .print_forms {
            display: block !important;
        }

        .date2 {
            display: block !important;
        }
    }
</style>
<?php
$v = '';

if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= '&category=' . $this->input->post('category');
}
if ($this->input->post('brand')) {
    $v .= '&brand=' . $this->input->post('brand');
}
if ($this->input->post('subcategory')) {
    $v .= '&subcategory=' . $this->input->post('subcategory');
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
if ($this->input->post('cf1')) {
    $v .= '&cf1=' . $this->input->post('cf1');
}
if ($this->input->post('cf2')) {
    $v .= '&cf2=' . $this->input->post('cf2');
}
if ($this->input->post('cf3')) {
    $v .= '&cf3=' . $this->input->post('cf3');
}
if ($this->input->post('cf4')) {
    $v .= '&cf4=' . $this->input->post('cf4');
}
if ($this->input->post('cf5')) {
    $v .= '&cf5=' . $this->input->post('cf5');
}
if ($this->input->post('cf6')) {
    $v .= '&cf6=' . $this->input->post('cf6');
}
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#form').hide();
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });
    });
</script>
  
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products_report'); ?> 
            <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?></h2>

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

                    <?php echo admin_form_open('reports/stock_in_out'); ?>
                    <div class="row">
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang('product', 'suggest_product'); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : '' ?>" id="report_product_id" />
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('category', 'category') ?>
                                <?php
                                $cat[''] = lang('select') . ' ' . lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang('select') . ' ' . lang('category') . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4 hide">
                            <div class="form-group">
                                <?= lang('subcategory', 'subcategory') ?>
                                <div class="controls" id="subcat_data"> 
                                    <?php
                                    echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang('select_category_to_load') . '"');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang('brand', 'brand') ?>
                                <?php
                                $bt[''] = lang('select') . ' ' . lang('brand');
                                foreach ($brands as $brand) {
                                    $bt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang('select') . ' ' . lang('brand') . '" style="width:100%"')
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
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <span style="display:none" class="date2">Date: <?= date("Y-m-d"); ?> - <?= date("h:i:sa"); ?></span>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin:10px 15px 15px 15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <?php if($settings->default_currency != "KHM"){ $currency = "$"; ?>
                <div class="table-responsive print_forms" style="display:none;">
                    <table style="width:100%">
                        <thead>
                            <tr class="active">
                                <th class="print_form"><?= lang('product_name'); ?></th>
                                <th class="print_form"><?= lang('qty'); ?></th>
                                <th class="print_form"><?= lang('total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                                    $totalsqty =[];
                                    $totalsSale = [];
                                    
                            foreach ($getcategoryInOut as $key1 => $category) {
                            ?>
                                <tr class="border-0">
                                    <th colspan="3" class="border-bottom"><?= $i ?>. <?= $category->category ?></th>
                                </tr>
                                <?php
                                $totalqty = 0;
                                $totalSale = 0;
                                $getProductsInOut = $this->reports_model->getProductsInOut($category->category_id, $get_warehouse, $start_date, $end_date);
                                $j = 1;
                                foreach ($getProductsInOut as $key => $row) {
                                ?>
                                    <tr>
                                        <!-- <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. ' . $row->code . '-' . $row->name ?></td> -->
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. '. $row->name ?></td>
                                        <td><?= $this->bpas->formatQuantity($row->soldQty); ?></td>
                                        <td><?= $currency; ?><?= $this->bpas->formatMoney($row->totalSale); ?></td>
                                    </tr>
                                <?php
                                    $j++;
                                    $totalqty += $row->soldQty;
                                    $totalSale += $row->totalSale;
                                }
                                ?>
                                <tr class="active">
                                    <th class="text-right"><?= lang('subtotal'); ?>:</th>
                                    <th class="">
                                       <?= $this->bpas->formatQuantity($totalqty); ?></th>
                                    <th class=""><?= $currency; ?><?= $this->bpas->formatMoney($totalSale); ?></th>
                                </tr>
                                <?php $totalsqtys[] = $this->bpas->formatQuantity($totalqty); ?>
                                <?php $totalsSales += $totalSale; ?>
                            <?php
                                $i++;
                            }
                           $getSalesDiscount = $this->reports_model->getSaleDiscount($category->category_id, $get_warehouse, $start_date, $end_date);
                           $sumTotalSales = array_sum($totalsSales);
                            ?>

                            <tr class="active">
                                <th class="text-right "><?= lang('total'); ?>:&nbsp;&nbsp;</th>
                                <th colspan="1" rowspan="3"><?php echo array_sum($totalsqtys); ?></th>
                                <th><?= $currency; ?><?php echo $sumTotalSales; ?></th>
                            </tr>
                             <tr class="active">
                                    <th class="text-right"><?= lang('discount'); ?>:</th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($getSalesDiscount->order_discount); ?></th>
                            </tr>
                            <tr class="active">
                                    <th class="text-right"><?= lang('grand_total'); ?>:</th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($sumTotalSales -$getSalesDiscount->order_discount); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive form_org">
                    <table class="table table-striped table-bordered table-condensed reports-table" style="margin-bottom:5px;">
                        <thead>
                            <tr class="active">
                                <th class="print_form"><?= lang('product_name'); ?></th>
                                <th class="print_form"><?= lang('qty'); ?></th>
                                <th class="print_form"><?= lang('total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $i = 1;
                            $totalsqty = [];
                            $totalsSale = 0;
                        if($getcategoryInOut){
                            foreach ($getcategoryInOut as $key1 => $category) {
                            ?>
                                <tr>
                                    <th colspan="3"><?= $i ?>. <?= $category->category ?></th>
                                </tr>
                                <?php
                                
                                $totalqty = 0;
                                $totalSale = 0;
                                $getProductsInOut = $this->reports_model->getProductsInOut($category->category_id, $get_warehouse, $start_date, $end_date);
                                $j = 1;
                                foreach ($getProductsInOut as $key => $row) {

                                ?>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. ' . $row->code . '-' . $row->name ?></td>
                                        <td><?= $this->bpas->formatQuantity(($row->soldQty)) ?></td>
                                        <td>$<?= $this->bpas->formatMoney($row->totalSale) ?></td>
                                    </tr>
                                <?php
                                    $j++;
                                    $totalqty += $row->soldQty;
                                    $totalSale += $row->totalSale;
                                }
                                ?>
                                <tr class="active">
                                    <th class="text-right"><?= lang('subtotal'); ?>:</th>
                                    <th><?= $this->bpas->formatQuantity($totalqty); ?></th>
                                    <th>$<?= $this->bpas->formatQuantity($totalSale); ?></th>
                                </tr>
                                <?php $totalsqty[] = $this->bpas->formatQuantity($totalqty); ?>
                                <?php $totalsSale += $totalSale; ?>
                            <?php
                                $i++;
                            }
                        }
                           $getSalesDiscount = $this->reports_model->getSaleDiscount($category->category_id, $get_warehouse, $start_date, $end_date);
                           $sumTotalSales = array_sum($totalsSales);
                            ?>

                            <tr class="active">
                                <th class="text-right "><?= lang('total'); ?>:&nbsp;&nbsp;</th>
                                <th colspan="1" rowspan="3"><?php echo array_sum($totalsqtys); ?></th>
                                <th><?= $currency; ?><?php echo $sumTotalSales; ?></th>
                            </tr>
                             <tr class="active">
                                    <th class="text-right"><?= lang('discount'); ?>:</th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($getSalesDiscount->order_discount); ?></th>
                            </tr>
                            <tr class="active">
                                    <th class="text-right"><?= lang('grand_total'); ?>:</th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($sumTotalSales -$getSalesDiscount->order_discount); ?></th>
                            </tr>

                        </tbody>
                    </table>
                </div>
            <?php }else{  $currency = "៛"; ?>
             <div class="table-responsive print_forms" style="display:none;">
                    <table style="width:100%">
                        <thead>
                            <tr class="active">
                                <th class="print_form"><?= lang('product_name'); ?></th>
                                <th class="print_form"><?= lang('qty'); ?></th>
                                <th class="print_form"><?= lang('total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $i = 1;
                                    $totalsqty =[];
                                    $totalsSale = [];
                            foreach ($getcategoryInOut as $key1 => $category) {
                            ?>
                                <tr class="border-0">
                                    <th colspan="3" class="border-bottom"><?= $i ?>. <?= $category->category ?></th>
                                </tr>
                                <?php
                                $totalqty = 0;
                                $totalSale = 0;
                                $getProductsInOut = $this->reports_model->getProductsInOut($category->category_id, $get_warehouse, $start_date, $end_date);
                                $j = 1;

                                foreach ($getProductsInOut as $key => $row) {

                                ?>
                                    <tr>
                                        <!-- <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. ' . $row->code . '-' . $row->name ?></td> -->
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. '. $row->name ?></td>
                                        <td><?= $this->bpas->formatQuantity($row->soldQty); ?></td>
                                        <td><?= $currency; ?><?= $this->bpas->formatMoney($row->totalSale); ?></td>
                                    </tr>
                                <?php
                                    $j++;
                                    $totalqty += $row->soldQty;
                                    $totalSale += $row->totalSale;
                                }
                                ?>
                                <tr class="active">
                                    <th class="text-right"><?= lang('subtotal'); ?>:</th>
                                    <th class="">
                                       <?= $this->bpas->formatQuantity($totalqty); ?></th>
                                    <th class=""><?= $currency; ?><?= $this->bpas->formatMoney($totalSale); ?></th>
                                </tr>
                                <?php $totalsqtys[] = $this->bpas->formatQuantity($totalqty); ?>
                                <?php $totalsSales[] = $totalSale; ?>
                            <?php
                                $i++;
                            }

                           $getSalesDiscount = $this->reports_model->getSaleDiscount($category->category_id, $get_warehouse, $start_date, $end_date);
                           $sumTotalSales = array_sum($totalsSales);
                            ?>

                            <tr class="active">
                                <th class="text-right "><?= lang('total'); ?>:&nbsp;&nbsp;</th>
                                <th colspan="1" rowspan="3"><?php echo array_sum($totalsqtys); ?></th>
                                <th><?= $currency; ?><?php echo $sumTotalSales; ?></th>
                            </tr>
                             <tr class="active">
                                    <th class="text-right"><?= lang('discount'); ?>:</th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($getSalesDiscount->order_discount); ?></th>
                            </tr>
                            <tr class="active">
                                    <th class="text-right"><?= lang('grand_total'); ?>:</th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($sumTotalSales -               $getSalesDiscount->order_discount); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive form_org">
                    <table class="table table-striped table-bordered table-condensed reports-table" style="margin-bottom:5px;">
                        <thead>
                            <tr class="active">
                                <th class="print_form"><?= lang('product_name'); ?></th>
                                <th class="print_form"><?= lang('qty'); ?></th>
                                <th class="print_form"><?= lang('total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $i = 1;
                            $totalsqty = [];
                            $totalsSale = 0;
                            // var_dump($getcategoryInOut);
                        if($getcategoryInOut){
                            foreach ($getcategoryInOut as $key1 => $category) {
                            ?>
                                <tr>
                                    <th colspan="3"><?= $i ?>. <?= $category->category ?></th>
                                </tr>
                                <?php
                                
                                $totalqty = 0;
                                $totalDiscount = 0;
                                $totalSale = 0;
                                $getProductsInOut = $this->reports_model->getProductsInOut($category->category_id, $get_warehouse, $start_date, $end_date);
                                $j = 1;
                                // var_dump($getProductsInOut);
                                foreach ($getProductsInOut as $key => $row) {
                                ?>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $j . '. ' . $row->code . '-' . $row->name ?></td>
                                        <td><?= $this->bpas->formatQuantity(($row->soldQty)) ?></td>
                                        <td><?= $currency; ?><?= $this->bpas->formatMoney($row->totalSale) ?></td>
                                    </tr>
                                <?php
                                    $j++;
                                    $totalqty += $row->soldQty;
                                    $totalSale += $row->totalSale;
                                }
                                ?>
                                <tr class="active">
                                    <th class="text-right"><?= lang('subtotal'); ?>:</th>
                                    <th><?= $this->bpas->formatQuantity($totalqty); ?></th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($totalSale); ?></th>
                                </tr>
                                
                                <?php $totalsqty[] = $this->bpas->formatQuantity($totalqty); ?>
                                <?php $totalsSale += $totalSale; ?>
                            <?php
                                $i++;
                            }
                        }
                         $getSalesDiscount = $this->reports_model->getSaleDiscount($category->category_id, $get_warehouse, $start_date, $end_date);
                            ?>
                            <tr class="active">
                                <th class="text-right"><?= lang('total'); ?></th>
                                <th colspan="1" rowspan="3"><?php echo array_sum($totalsqty); ?></th>
                                <th><?= $currency; ?><?php echo $totalsSale; ?></th>
                            </tr>
                            <tr class="active">
                                    <th class="text-right"><?= lang('discount'); ?></th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity($getSalesDiscount->order_discount); ?></th>
                            </tr>
                            <tr class="active">
                                    <th class="text-right"><?= lang('grand_total'); ?></th>
                                    <th><?= $currency; ?><?= $this->bpas->formatQuantity(($totalsSale-$getSalesDiscount->order_discount)); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
        <?php } ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#pdf').click(function(event) {
            event.preventDefault();
            window.location.href = "<?= admin_url('reports/getProductsReport/pdf/?v=1' . $v) ?>";
            return false;
        });
        $('#xls').click(function(event) {
            event.preventDefault();
            window.location.href = "<?= admin_url('reports/getProductsReport/0/xls/?v=1' . $v) ?>";
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