<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="row">
                <div class="col-xs-4">
                    <?php if ($logo) { ?>
                        <div class="text-left">
                            <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" width="180" alt="<?= $Settings->site_name; ?>">
                        </div>
                    <?php } ?>                           
                </div>
                <div class="col-xs-5 text-center">
                    <h2 style="margin-top:10px;">
                        <?php if ($biller) { ?>
                            <?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>
                        <?php } else { ?>
                            <?= $Settings->site_name; ?>
                        <?php } ?>
                    </h2>
                    <?= $warehouse->name ?>
                    <?php
                        echo $warehouse->address;
                        echo ($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                    ?>
                </div>
                <div class="col-xs-3 text-right">
                    <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/32/0/1'); ?>" alt="<?= $inv->reference_no; ?>" />
                        <?php // $this->bpas->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2); ?>
                </div>
            </div>
            <br><br>
            <tr>
                <td>
                    <div class="col-xs-5" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div>
                    <div class="col-xs-2 text-center" style="font-size: 20px; line-height: 55%; font-family: KhmerOS_muollight !important; font-weight: bold; padding: 0;">
                        <p><?= lang('purchase'); ?></p>
                    </div>
                    <div class="col-xs-5" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div> <!-- #5DADE2 -->
                </td>
            </tr>
            <br><br>
            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 100%; float: left; margin-right: 2%; font-weight: bold; margin-bottom: 5px !important;">
                        <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 85%; margin-bottom: -5px; font-style: italic !important;">ព័ត៍មានអ្នកផ្គត់ផ្គង់</caption>
                        <tr>
                            <td style="width: 30%; padding-left: 5px;">អ្នកផ្គត់ផ្គង់ / <?= lang('supplier'); ?></td>
                            <td style="width: 1%;">:</td>
                            <td style="width: 30%;"><b><?= $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name; ?></b></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 5px;">ទូរស័ព្ទលេខ / Tel</td>
                            <td>:</td>
                            <td><?= $supplier->phone  ?></td>
                        </tr>
                        <?php if ($supplier->vat_no != '-' && $supplier->vat_no != '') {?>
                        <tr>
                            <td style="padding-left: 5px;"><?= lang('vat_no') ?></td>
                            <td>:</td>
                            <td><?= $supplier->vat_no  ?></td>
                        </tr>
                        <?php }?>
                        <tr>
                            <td style="padding-left: 5px; vertical-align: top;">អាសយដ្ឋាន / <?= lang('address'); ?></td>
                            <td style="vertical-align: top;">:</td>
                            <td style="padding-bottom: 3px;"><?php  echo $supplier->address . ' ' . $supplier->city . ' ' . $supplier->postal_code . ' ' . $supplier->state . ' ' . $supplier->country; ?></td>
                        </tr>
                    </table>

                </div>
                <div class="col-xs-6">
                    <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; font-weight: bold;">
                        <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 65%; margin-bottom: -5px; font-style: italic !important;">ឯកសារយោង</caption>
                        <tr>
                            <td style="width: 25%; padding-left: 5px;">វិក្កយបត្រ / Invoice NO</td>
                            <td style="width: 1%;">:</td>
                            <td style="width: 30%;"><?= $inv->reference_no; ?>
                                <?php if (!empty($inv->return_purchase_ref)) {
                                echo lang('return_ref') . ': ' . $inv->return_purchase_ref;
                                if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '<br>';
                                }
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 5px;">កាលបរិច្ឆាទ / Date</td>
                            <td>:</td>
                            <td><?= $this->bpas->hrld($inv->date); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 5px;"><?= lang('status'); ?></td>
                            <td>:</td>
                            <td><?= lang($inv->status); ?></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 5px;"><?= lang('payment_status'); ?></td>
                            <td>:</td>
                            <td><?= lang($inv->payment_status); ?></td>
                        </tr>
                    </table>
                    
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered print-table order-table">
                    <thead>
                        <tr>
                            <th><?= lang('no.'); ?></th>
                            <th><?= lang('description'); ?></th>
                            <?php if ($Settings->indian_gst) { ?>
                                <th><?= lang('hsn_code'); ?></th>
                            <?php } ?>
                            <th><?= lang('quantity'); ?></th>
                            <th><?= lang('unit'); ?></th>
                            <?php
                            if ($inv->status == 'partial') {
                                echo '<th>' . lang('received') . '</th>';
                            } ?>
                            <?php if ($Settings->default_currency == "USD") { ?>
                                <!-- <th><?= lang('unit_cost'); ?></th> -->
                                <th><?= lang('unit_cost'); ?>($)</th>
                            <?php } ?>
                            <?php if ($Settings->default_currency == "KHM") { ?>
                                <th><?= lang('unit_cost'); ?>(៛)</th>
                            <?php } ?>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th>' . lang('tax') . '</th>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th>' . lang('discount') . '</th>';
                            } ?>
                            <th><?= lang('subtotal'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $r     = 1;
                        $tax_summary = [];
                        foreach ($rows as $row) :
                        ?>
                        <?php 
                            $unit = $this->site->getUnitByID($row->product_unit_id);
                            $quantity_received = $this->bpas->formatDecimal($this->site->baseToUnitQty($row->quantity_received, $unit)); 
                        ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                </td>
                                <?php if ($Settings->indian_gst) { ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                <?php } ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->bpas->formatQuantity($quantity_received) . '</td>';
                                } ?>
                                <?php if ($Settings->default_currency == "USD") { ?>
                                    <!-- <td style="text-align:right; width:100px;">
                                        <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_cost) . '</del>' : ''; ?>
                                        <?php if ($row->other_cost == 0) { ?>
                                            <?= $this->bpas->formatMoney($row->unit_cost); ?>
                                            (<?= $row->symbol ?>)
                                        <?php } else { ?>
                                            <?= $this->bpas->formatMoney($row->other_cost); ?>
                                            (<?= $row->symbol ?>)
                                        <?php } ?>
                                    </td> -->
                                    <td style="text-align:right; width:100px;">
                                        <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_cost) . '</del>' : ''; ?>
                                        <?= $this->bpas->formatMoney($row->unit_cost); ?>
                                        ($)
                                    </td>
                                <?php } ?>
                                <?php if ($Settings->default_currency == "KHM") { ?>
                                    <td style="text-align:right; width:100px;">
                                        <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_cost) . '</del>' : ''; ?>
                                        <?= $this->bpas->formatMoney($row->unit_cost); ?>
                                        (៛)
                                    </td>
                                <?php } ?>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; width:120px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                            foreach ($return_rows as $row) :
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) {
                                    ?>
                                        <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php
                                    } ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->bpas->formatQuantity($row->quantity_received) . ' ' . $row->product_unit_code . '</td>';
                                    } ?>
                                    <td style="text-align:right; width:100px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    } ?>
                                    <td style="text-align:right; width:120px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                </tr>
                        <?php
                                $r++;
                            endforeach;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                        $col = $Settings->indian_gst ? 6 : 5;
                        if ($inv->status == 'partial') {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 2;
                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                            $tcol = $col - 1;
                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 1;
                        } else {
                            $tcol = $col;
                        }
                        ?>
                        <?php if ($inv->grand_total != $inv->total) { ?>
                            <tr>
                                <td colspan="<?= $tcol; ?>" style="text-align: right;"><?= lang('total'); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td class="text-right">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td class="text-right">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                } ?>
                                <td style="text-align: right;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php } ?>
                        <?php
                        if ($return_purchase) {
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                        } ?>
                        <?php if ($Settings->indian_gst) {
                            if ($inv->cgst > 0) {
                                $cgst = $return_purchase ? $inv->cgst + $return_purchase->cgst : $inv->cgst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($cgst) : $cgst) . '</td></tr>';
                            }
                            if ($inv->sgst > 0) {
                                $sgst = $return_purchase ? $inv->sgst + $return_purchase->sgst : $inv->sgst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($sgst) : $sgst) . '</td></tr>';
                            }
                            if ($inv->igst > 0) {
                                $igst = $return_purchase ? $inv->igst + $return_purchase->igst : $inv->igst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($igst) : $igst) . '</td></tr>';
                            }
                        } ?>

                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '"  style="text-align: right;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td  style="text-align: right;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->bpas->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->shipping != 0) {
                            if ($this->Settings->avc_costing) {
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cost') . ' ' . lang('shipping') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                            }
                        }
                        ?>
                        <!-- <?php if ($Settings->default_currency == "KHM") {
                            $col = $col - 1; ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right;"><?= lang('total_amount'); ?>
                                            (USD)
                                        </td>
                                        <td style="text-align:right;">
                                            <?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?>
                                        </td>
                                    </tr>

                        <?php 
                        }  ?> -->
                        <?php if ($Settings->default_currency == "USD") { ?>
                            <?php foreach ($currencys as $currency) { ?>
                                <?php if ($currency->code == "USD" && $sumUSD > 0) { ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right;"><?= lang('total_amount'); ?>
                                            (USD)
                                        </td>
                                        <td style="text-align:right;">
                                            <?= $this->bpas->formatMoney($sumUSD) ?>
                                        </td>
                                    </tr><?php } ?>
                                <?php if ($currency->code == "KHM" && $sumKHM > 0) { ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right;"><?= lang('total_amount'); ?>
                                            (KHM)
                                        </td>
                                        <td style="text-align:right;">
                                            <?= $this->bpas->formatMoney($sumKHM) ?>
                                        </td>
                                    </tr><?php } ?>
                                <?php if ($currency->code == "Euro" && $sumEuro > 0) { ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right;"><?= lang('total_amount'); ?>
                                            (Euro)
                                        </td>
                                        <td style="text-align:right;">
                                            <?= $this->bpas->formatMoney($sumEuro) ?>
                                        </td>
                                    </tr><?php } ?>
                                <?php if ($currency->code == "BAT" && $sumBAT > 0) { ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right;"><?= lang('total_amount'); ?>
                                            (BAT)
                                        </td>
                                        <td style="text-align:right;">
                                            <?= $this->bpas->formatMoney($sumBAT) ?>
                                        </td>
                                    </tr><?php } ?>
                                <?php if ($currency->code == "Yuan" && $sumYuan > 0) { ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right;"><?= lang('total_amount'); ?>
                                            (Yuan)
                                        </td>
                                        <td style="text-align:right;">
                                            <?= $this->bpas->formatMoney($sumYuan) ?>
                                        </td>
                                    </tr><?php } ?>
                        <?php
                            }
                        } ?>
                        <!-- <?php foreach ($rows as $row) { ?>
                            <?php if ($row->currency == "USD") { ?>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                        (<?= $row->currency; ?>)
                                    </td>
                                    <td style="text-align:right; font-weight:bold;">
                                        <?= ($sumUSD) ?>
                                    </td>
                                </tr><?php } ?>
                            <?php if ($row->currency == "KHM") { ?>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                        (<?= $row->currency; ?>)
                                    </td>
                                    <td style="text-align:right; font-weight:bold;">
                                        <?= ($sumKHM) ?>
                                    </td>
                                </tr><?php } ?>
                            <?php if ($row->currency == "Euro") { ?>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                        (<?= $row->currency; ?>)
                                    </td>
                                    <td style="text-align:right; font-weight:bold;">
                                        <?= ($sumEuro) ?>
                                    </td>
                                </tr><?php } ?>
                            <?php if ($row->currency == "BAT") { ?>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                        (<?= $row->currency; ?>)
                                    </td>
                                    <td style="text-align:right; font-weight:bold;">
                                        <?= ($sumBAT) ?>
                                    </td>
                                </tr><?php } ?>
                            <?php if ($row->currency == "Yuan") { ?>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                        (<?= $row->currency; ?>)
                                    </td>
                                    <td style="text-align:right; font-weight:bold;">
                                        <?= ($sumYuan) ?>
                                    </td>
                                </tr><?php } ?>
                        <?php
                                } ?> -->
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('grand_total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
                        </tr>

                    </tfoot>
                </table>
            </div>
            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax + $return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php
                    if ($inv->note || $inv->note != '') {
                    ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('note'); ?>:</p>
                            <div><?= $this->bpas->decode_html($inv->note); ?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <!--   <div class="col-xs-6 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang('created_by'); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?>
                        </p>
                        <?php if ($inv->updated_by) {
                        ?>
                        <p>
                            <?= lang('updated_by'); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?><br>
                            <?= lang('update_at'); ?>: <?= $this->bpas->hrld($inv->updated_at); ?>
                        </p>
                        <?php
                        } ?>
                    </div>
                </div> -->
                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang('created_by'); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?>
                        </p>
                        <?php if ($inv->updated_by) {
                        ?>
                            <p>
                                <?= lang('updated_by'); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?><br>
                                <?= lang('update_at'); ?>: <?= $this->bpas->hrld($inv->updated_at); ?>
                            </p>
                        <?php
                        } ?>
                    </div>
                </div>
            </div>
            <?php if (!$Supplier || !$Customer) { ?>
                <div class="buttons">
                    <?php if ($inv->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/add_payment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
                                <i class="fa fa-dollar"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete') ?></b>" data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php
            } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
    });
</script>