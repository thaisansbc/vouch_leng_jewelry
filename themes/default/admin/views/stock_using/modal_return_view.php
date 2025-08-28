<?php defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    html,
    body {
        height: 100%;
        background: #FFF;
    }

    body:before,
    body:after {
        display: none !important;
    }

    .table th {
        text-align: center;
        padding: 5px;
    }

    .table td {
        padding: 4px;
    }

    hr {
        border-color: #333;
        width: 100px;
        margin-top: 70px;
    }
</style>
<div class='modal-dialog modal-lg'>
    <div class='modal-content'>
        <div class='modal-header'>
            <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>
                <i class='fa fa-2x'>&times;
                </i>
            </button>
            <button type='button' class='btn btn-xs btn-default no-print pull-right' style='margin-right:15px;'
                onclick='window.print();'>
                <i class='fa fa-print'></i>
                <?= lang('print');
                ?>
            </button>
            <h4 class='modal-title' id='myModalLabel'>
                <?= $product->name . (SHOP && $product->hide != 1 ? ' (' . lang('shop_views') . ': ' .
                    $product->views . ')' : '');
                ?>
            </h4>
        </div>
        <div class='modal-body'>
            <div class='print_rec' id='wrap' style='width: 90%; margin: 40px auto;'>
                <div class='row'>
                    <div class='col-lg-12'>
                        <div class='col-sm-12 col-xs-12 '>
                            <center>
                                <h1 style='font-family:Time New Roman;font-size:25px;'>
                                    <?= lang('using_stock_return_form') ?>
                                </h1>
                            </center>
                        </div>
                    </div>

                    <div class='row padding10'>
                        <div class='col-lg-4 col-sm-4 col-xs-4' style='float: left;font-size:13px;'>
                            <b>
                                <p style='font-size: 17px;'>
                                    <?= lang('information');
                                    ?>
                                </p>
                            </b>
                            <table>
                                <tr>
                                    <td>Project</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td style='font-size:12px !important;'><b>
                                            <?= $biller->company;
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>Authorize Name</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= isset($authorize->username) ? $authorize->username : '';
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>Employee Name</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $using_stock->first_name . ' ' . $using_stock->last_name;
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>Project Plan</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $using_stock->title;
                                            ?>
                                        </b></td>
                                </tr>
                            </table>
                        </div>
                        <div class='col-lg-3 col-sm-3 col-xs-3' style='text-align:center;margin-top:-20px'>
                        </div>
                        <div class='col-lg-5 col-sm-5 col-xs-5' style='float: right;font-size:13px'>
                            <b>
                                <p style='font-size: 17px;'>
                                    <?= lang('reference');
                                    ?>
                                </p>
                            </b>
                            <table>
                                <tr>
                                    <td>Reference No</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $using_stock->reference_no;
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>From Using Reference</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $using_stock->using_reference_no;
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>Date</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $this->bpas->hrsd($using_stock->date);
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>Warehouse</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $using_stock->name;
                                            ?>
                                        </b></td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>&nbsp;
                                        &nbsp;
                                        :&nbsp;
                                        &nbsp;
                                    </td>
                                    <td><b>
                                            <?= $using_stock->address;
                                            ?>
                                        </b></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class='clearfix'></div>
                    <div class='row padding10' style='display:none'>
                        <div class='col-xs-6' style='float: left;'>

                        </div>
                        <div class='col-xs-5' style='float: right;'>

                        </div>
                    </div>

                    <div class='clearfix'></div>

                    <div class='-table-responsive'>
                        <table class='table table-bordered table-hover table-striped' style='width: 100%;'>
                            <thead style='font-size: 13px;'>
                                <tr>
                                    <th>
                                        <?= lang('no');
                                        ?>
                                    </th>
                                    <th>
                                        <?= lang('product_code');
                                        ?>
                                    </th>
                                    <th>
                                        <?= lang('product_name');
                                        ?>
                                    </th>
                                    <?php if ($Settings->product_expiry) {
                                    ?>
                                        <th>
                                            <?= lang('expiry_date');
                                            ?>
                                        </th>
                                    <?php }
                                    ?>
                                    <th>
                                        <?= lang('description');
                                        ?>
                                    </th>
                                    <th>
                                        <?= lang('unit');
                                        ?>
                                    </th>
                                    <th>
                                        <?= lang('quantity');
                                        ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody style='font-size: 13px;'>
                                <?php
                                $i = 1;
                                $total = 0;
                                foreach ($stock_item as $si) {
                                ?>
                                    <tr>
                                        <td style='text-align:center;'>
                                            <?= $i;
                                            ?>
                                        </td>
                                        <td style='text-align:center;'>
                                            <?= $si->code ?>
                                        </td>
                                       <td>
                                        <?= $si->product_name; ?>
                                        <small style="color:blue;">
                                            <?php 
                                            if ($si->combo_product) {
                                                $combo_products = json_decode($si->combo_product);
                                                foreach ($combo_products as $combo_product) {
                                                    echo '<br>' . $combo_product->name . ' (' . $combo_product->code . ')<br>';

                                                    if (!empty($combo_product->wax_setting_qty)) {
                                                        echo 'Step 1: ' . $this->bpas->formatQuantity($combo_product->wax_setting_qty) . ' > ';
                                                    }
                                                    if (!empty($combo_product->casting_qty)) {
                                                        echo 'Step 2: ' . $this->bpas->formatQuantity($combo_product->casting_qty) . ' > ';
                                                    }
                                                    if (!empty($combo_product->filing_pre_polishing_qty)) {
                                                        echo 'Step 3: ' . $this->bpas->formatQuantity($combo_product->filing_pre_polishing_qty) . ' > ';
                                                    }
                                                    if (!empty($combo_product->stone_setting_qty)) {
                                                        echo 'Step 4: ' . $this->bpas->formatQuantity($combo_product->stone_setting_qty) . '  ';
                                                    }
                                                    if (!empty($combo_product->final_polishing_qty)) {
                                                        echo 'Step 5: ' . $this->bpas->formatQuantity($combo_product->final_polishing_qty) . ' > ';
                                                    }
                                                    if (!empty($combo_product->quality_inspection_qty)) {
                                                        echo 'Step 6: ' . $this->bpas->formatQuantity($combo_product->quality_inspection_qty) . ' > ';
                                                    }
                                                    if (!empty($combo_product->packaging_qty)) {
                                                        echo 'Step 7: ' . $this->bpas->formatQuantity($combo_product->packaging_qty);
                                                    }
                                                }
                                            }
                                            ?>
                                        </small>
                                    </td>
                                        <?php if ($Settings->product_expiry) {
                                        ?>
                                            <td class='text-center'>
                                                <?= $this->bpas->hrsd($si->expiry);
                                                ?>
                                            </td>
                                        <?php }
                                        ?>
                                        <td class='text-center'>
                                            <?= $si->description; ?>
                                        </td>
                                        <td style='text-align:center;'>
                                            <?= $si->unit_name;
                                            ?>
                                        </td>
                                        <td style='text-align:center;'>
                                            <?= $this->bpas->formatQuantity($si->qty_by_unit);
                                            ?>
                                        </td>
                                    </tr>

                                <?php
                                    $total += $si->qty_by_unit;
                                    $i++;
                                }
                                ?>
                                <?php
                                echo '
                                        <tr>
                                            <td colspan=' . '6' . '>
                                                <p><b>Note:</b>' . '&nbsp;&nbsp;' . strip_tags($using_stock->note) . '</p>
                                            </td>
                                        <tr>
                                    ';
                                ?>
                                </tfoot>
                        </table>
                    </div>
                    <div class='row'>
                        <div class='col-lg-4 col-sm-4 col-xs-4  pull-left' style='text-align:center'>
                            <hr />
                            <p><b>
                                    <?= lang('Signature Stock Manager');
                                    ?>
                                </b></p>
                        </div>
                        <div class='col-lg-4 col-sm-4 col-xs-4 ' style='text-align:center'>
                            <hr />
                            <p><b>
                                    <?= lang('Stock');
                                    ?>
                                </b></p>
                        </div>
                        <div class='col-lg-4 col-sm-4 col-xs-4  pull-left' style='text-align:center'>
                            <hr />
                            <p><b>
                                    <?= lang('Receiver');
                                    ?>
                                </b></p>
                        </div>

                    </div>
                </div>
                <div class='col-md-12'>

                </div>
            </div>
        </div>
    </div>
</div>
</div>