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
            <?php if ($logo) {
    ?>
                <div class="text-left" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                         alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php
} ?>
            <h1 class="text-center"><?= ($inv->type =='returned') ? lang('stock_return'):lang('keep') ; ?></h1>
            <div class="well well-sm">
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4">
                        <p class="bold">
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?><br>
                            <?= lang('type'); ?>: <?= lang('return_sale'); ?><br>
                            <?= lang('ref'); ?>: <?= $inv->reference_no; ?>
                        </p>
                    </div>
                    <div class="col-xs-4">
                        <?php echo $this->lang->line('to'); ?>:
                        <span style="margin-top:10px;"><?= $customer->company ? $customer->company : $customer->name; ?></span>
                        <?= $customer->company ? '' : 'Attn: ' . $customer->name ?>

                        <?php
                        echo $customer->address . '<br>' . $customer->city . ' ' . 
                            $customer->postal_code . ' ' . $customer->state . ' ' . 
                            $customer->country;

                        echo '<p>';

                        if ($customer->vat_no != '-' && $customer->vat_no != '') {
                            echo '<br>' . lang('vat_no') . ': ' . $customer->vat_no;
                        }
                        if ($customer->gst_no != '-' && $customer->gst_no != '') {
                            echo '<br>' . lang('gst_no') . ': ' . $customer->gst_no;
                        }
                        if ($customer->cf1 != '-' && $customer->cf1 != '') {
                            echo '<br>' . lang('ccf1') . ': ' . $customer->cf1;
                        }
                        if ($customer->cf2 != '-' && $customer->cf2 != '') {
                            echo '<br>' . lang('ccf2') . ': ' . $customer->cf2;
                        }
                        if ($customer->cf3 != '-' && $customer->cf3 != '') {
                            echo '<br>' . lang('ccf3') . ': ' . $customer->cf3;
                        }
                        if ($customer->cf4 != '-' && $customer->cf4 != '') {
                            echo '<br>' . lang('ccf4') . ': ' . $customer->cf4;
                        }
                        if ($customer->cf5 != '-' && $customer->cf5 != '') {
                            echo '<br>' . lang('ccf5') . ': ' . $customer->cf5;
                        }
                        if ($customer->cf6 != '-' && $customer->cf6 != '') {
                            echo '<br>' . lang('ccf6') . ': ' . $customer->cf6;
                        }

                        echo '</p>';
                        echo lang('tel') . ': ' . $customer->phone . '<br>' . lang('email') . ': ' . $customer->email;
                        ?>
                    </div>

                    <div class="col-xs-4">
                        <?php echo $this->lang->line('from'); ?>:
                        <span style="margin-top:10px;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></span>
                        <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                        <?php
                        echo $biller->address . '<br>' . $biller->city . ' ' . $biller->postal_code . ' ' . 
                                $biller->state . ' ' . $biller->country;

                        echo '<p>';

                        if ($biller->vat_no != '-' && $biller->vat_no != '') {
                            echo '<br>' . lang('vat_no') . ': ' . $biller->vat_no;
                        }
                        if ($biller->gst_no != '-' && $biller->gst_no != '') {
                            echo '<br>' . lang('gst_no') . ': ' . $biller->gst_no;
                        }
                        if ($biller->cf1 != '-' && $biller->cf1 != '') {
                            echo '<br>' . lang('bcf1') . ': ' . $biller->cf1;
                        }
                        if ($biller->cf2 != '-' && $biller->cf2 != '') {
                            echo '<br>' . lang('bcf2') . ': ' . $biller->cf2;
                        }
                        if ($biller->cf3 != '-' && $biller->cf3 != '') {
                            echo '<br>' . lang('bcf3') . ': ' . $biller->cf3;
                        }
                        if ($biller->cf4 != '-' && $biller->cf4 != '') {
                            echo '<br>' . lang('bcf4') . ': ' . $biller->cf4;
                        }
                        if ($biller->cf5 != '-' && $biller->cf5 != '') {
                            echo '<br>' . lang('bcf5') . ': ' . $biller->cf5;
                        }
                        if ($biller->cf6 != '-' && $biller->cf6 != '') {
                            echo '<br>' . lang('bcf6') . ': ' . $biller->cf6;
                        }

                        echo '</p>';
                        echo lang('tel') . ': ' . $biller->phone . '<br>' . lang('email') . ': ' . $biller->email;
                        ?>
                    </div>

                </div>
            </div>
            <div class="table-responsive">
                <table class="print_table table-hover table-striped print-table order-table">

                    <thead>

                    <tr>
                        <th><?= lang('no.'); ?></th>
                        <th><?= lang('description'); ?></th>
                        <?php if ($Settings->indian_gst) {
                        ?>
                            <th><?= lang('hsn_code'); ?></th>
                        <?php
                    } ?>
                        <th><?= lang('quantity'); ?></th>
                        <th><?= lang('unit_price'); ?></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang('tax') . '</th>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th>' . lang('discount') . '</th>';
                        }
                        ?>
                        <th><?= lang('subtotal'); ?></th>
                    </tr>

                    </thead>

                    <tbody>

                    <?php $r = 1;
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                                <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                            </td>
                            <?php if ($Settings->indian_gst) {
                        ?>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                            <?php
                    } ?>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_code; ?></td>
                            <td style="text-align:right; width:100px;"><?= $this->bpas->formatMoney($row->unit_price); ?></td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:120px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    ?>
                    </tbody>
                    <tfoot>
                    <?php
                    $col = $Settings->indian_gst ? 5 : 4;
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
                    <?php if ($inv->grand_total != $inv->total) {
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->bpas->formatMoney($inv->product_tax) . '</td>';
                            }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style="text-align:right;">' . $this->bpas->formatMoney($inv->product_discount) . '</td>';
                        } ?>
                            <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney(($inv->total + $inv->product_tax)); ?></td>
                        </tr>
                    <?php
                    } ?>
                    <?php
                    if ($inv->surcharge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                    }
                    ?>
                    <?php
                    if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>

                    <?php if ($Settings->indian_gst) {
                        if ($inv->cgst > 0) {
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($inv->cgst) : $inv->cgst) . '</td></tr>';
                        }
                        if ($inv->sgst > 0) {
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($inv->sgst) : $inv->sgst) . '</td></tr>';
                        }
                        if ($inv->igst > 0) {
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($inv->igst) : $inv->igst) . '</td></tr>';
                        }
                    } ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($inv->grand_total); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($inv->paid)); ?></td>
                    </tr>

                    </tfoot>
                </table>
            </div>

            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, null, ($inv->product_tax)) : ''; ?>
             <div class="row" style="padding-top: 20px;">
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Approved by:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Delivered by:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Received by:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Acknowledged by:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        if ($inv->staff_note || $inv->staff_note != '') {
                            ?>
                            <div class="well well-sm staff_note">
                                <p class="bold"><?= lang('staff_note'); ?>:</p>
                                <div><?= $this->bpas->decode_html($inv->staff_note); ?></div>
                            </div>
                        <?php
                        } ?>
                </div>

                <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) {
                            ?>
                <div class="col-xs-5 pull-left">
                    <div class="well well-sm">
                        <?=
                        '<p>' . lang('this_sale') . ': ' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                        . '<br>' .
                        lang('total') . ' ' . lang('award_points') . ': ' . $customer->award_points . '</p>'; ?>
                    </div>
                </div>
                <?php
                        } ?>

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
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
