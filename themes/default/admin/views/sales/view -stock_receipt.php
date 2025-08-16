<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang('sale_no') . ' ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>">
                        </i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) {
    ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php
} ?>
                        <li>
                            <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="sledit">
                                <i class="fa fa-edit"></i> <?= lang('edit_sale') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/payments/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-dollar"></i> <?= lang('add_payment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <?php if (!$inv->sale_id) {
        ?>
                        <li>
                            <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-truck"></i> <?= lang('add_delivery') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/return_sale/' . $inv->id) ?>">
                                <i class="fa fa-angle-double-left"></i> <?= lang('return_sale') ?>
                            </a>
                        </li>
                        <?php
    } ?>
                    </ul>
                </li>
            </ul>
        </div>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:10px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($inv->return_sale_ref) && $inv->return_id) {
        echo '<div class="alert alert-info no-print"><p>' . lang('sale_is_returned') . ': ' . $inv->return_sale_ref;
        echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
        echo '</p></div>';
    } ?>
                <div class="col-xs-12">
                    <img src="<?= admin_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
                <div class="text-center"><h1><?php
                    if ($inv->sale_status=='returned') {
                        $status = 'RETRUN';
                    }else{ $status = 'RECEIPT'; }
                    echo lang('STOCK').' '.$status; 
                ?></h1></div>
                <div class="well well-sm" style="display: none;">

                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-user padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h2>
                            <?= $customer->company              && $customer->company != '-' ? '' : 'Attn: ' . $customer->name ?>

                            <?php
                            echo $customer->address . '<br>' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br>' . $customer->country;

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
                        <div class="clearfix"></div>
                    </div>

                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                            <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                            <?php
                            echo $biller->address . '<br>' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br>' . $biller->country;

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
                        <div class="clearfix"></div>

                    </div>

                    <div class="col-xs-4">
                        <div class="col-xs-2"><i class="fa fa-3x fa-building-o padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $Settings->site_name; ?></h2>
                            <?= $warehouse->name ?>

                            <?php
                            echo $warehouse->address . '<br>';
                            echo($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <?php if ($Settings->invoice_view == 1) {
                                ?>
                    <div class="col-xs-12 text-center">
                        <h1><?= lang('tax_invoice'); ?></h1>
                    </div>
                <?php
                            } ?>
                <div class="clearfix"></div>
                <div class="col-xs-7 pull-right" style="display: none;">
                    <div class="col-xs-12 text-right order_barcodes">
                        <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                        <?= $this->bpas->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-5">
                    <div class="col-xs-2"><i class="fa fa-3x fa-file-text-o padding010 text-muted"></i></div>
                    <div class="col-xs-10">
                        <h2 class=""><?= lang('ref'); ?>: <?= $inv->reference_no; ?></h2>
                        <?php if (!empty($inv->return_sale_ref)) {
                                echo '<p>' . lang('return_ref') . ': ' . $inv->return_sale_ref;
                                if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '</p>';
                                }
                            } ?>

                        <p style="font-weight:bold;"><?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?></p>

                        <p style="font-weight:bold;"><?= lang('sale_status'); ?>: <?= lang($inv->sale_status); ?></p>

                        <p style="font-weight:bold;"><?= lang('payment_status'); ?>
                            : <?= lang($inv->payment_status); ?></p>

                        <?php if ($inv->payment_status != 'paid') {
                                echo '<p>' . lang('due_date') . ': ' . $this->bpas->hrsd($inv->due_date) . '</p>';
                            } ?>

                        <p>&nbsp;</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-4 pull-right">
                    <div class=""><label>RECEIVED FROM :</label>.............................</div>
                    <div class=""><label>CATEGORY :</label>........................................</div>
                    <div class=""><?php if ($inv->sale_status == 'returned') {
                        echo "<label>LOCATION :</label>.........................................";
                    } ?></div>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped print-table order-table">

                        <thead>
                        <tr>
                            <th><?= lang('no.'); ?></th>
                            <th> <?= lang('code'); ?></th>
                            <th><?= lang('description'); ?></th>
                            <?php if ($Settings->indian_gst) {
                                ?>
                                <th><?= lang('hsn_code'); ?></th>
                            <?php
                            } ?>
                            <th><?= lang('UOM'); ?></th>
                            <th><?= lang('quantity'); ?></th>
                            <?php
                            if ($Settings->product_serial) {
                                echo '<th style="text-align:center; vertical-align:middle;">' . lang('serial_no') . '</th>';
                            }
                            if($Settings->disable_price == 'no'){
                                echo '<th style="padding-right:20px;">'.lang('unit_price').'</th>';
                            }
                            ?>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('tax') . '</th>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('discount') . '</th>';
                            }
                            if($Settings->disable_price == 'no'){
                                echo '<th style="padding-right:20px;">'.lang('subtotal').'</th>';
                            }
                            ?>
                            
                        </tr>

                        </thead>

                        <tbody>

                        <?php $r = 1;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="text-align: center;width: 150px;">
                                    <?= $row->product_code; ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                </td>
                                <?php if ($Settings->indian_gst) {
                                ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                <?php
                            } ?>
                                <td style="width: 80px;text-align: center;"> <?= ($inv->sale_status == 'returned' ? $row->base_unit_code : $row->product_unit_code); ?></td>
                                <td style="width: 100px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <?php
                                if ($Settings->product_serial) {
                                    echo '<td>' . $row->serial_no . '</td>';
                                }
                                ?>
                                <?php if($Settings->disable_price == 'no'){ ?>
                                <td style="text-align:right; width:120px; padding-right:10px;">
                                    <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                    <?= $this->bpas->formatMoney($row->unit_price); ?>
                                </td>
                                <?php }
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <?php if($Settings->disable_price == 'no'){ ?>
                                <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                <?php } ?>
                            </tr>
                            <?php
                            $r++;
                        endforeach;

                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                            foreach ($return_rows as $row):
                                ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code ?>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) {
                                    ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php
                                } ?>
                                    <td style="width: 100px; text-align:center; vertical-align:middle;">
                                        <?= $row->base_unit_code; ?></td>
                                    <td style="width: 100px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->quantity); ?></td>
                                    <?php
                                    if ($Settings->product_serial) {
                                        echo '<td>' . $row->serial_no . '</td>';
                                    } ?>
                                   
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                            } ?>
                                    
                                </tr>
                                <?php
                                $r++;
                            endforeach;
                        }

                        ?>
                        </tbody>
                        <tfoot>
                        <?php
                        $col = $Settings->indian_gst ? 7 : 6;
                        if ($Settings->product_serial) {
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
                        <?php if ($inv->grand_total != $inv->total) {
                            ?>
                            <tr>
                                <td colspan="<?= $tcol; ?>"
                                    style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                                }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                            } ?>
                                <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php
                        } ?>
                        <?php
                        if ($return_sale) {
                       //     echo '<tr><td colspan="' . ($col-2) . '" style="text-align:right; padding-right:10px;;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                        }
                        ?>

          

                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping - ($return_sale && $return_sale->shipping ? $return_sale->shipping : 0)) . '</td></tr>';
                        }
                        ?>
                        <?php if($Settings->disable_price == 'no'){ ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                        </tr>
                        <?php } ?>
                        </tfoot>
                    </table>
                    <div class="clearfix"></div>
                </div>
                <div style="padding-top: 20px;">
                <?php if($inv->sale_status == 'returned'){ ?>
                    <div class="row">
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
                <?php }else{ ?>
                    <div class="row">
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
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Stock Received by:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Quality checked by:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div style="border: 1px solid black;padding: 80px 10px 10px 10px;">
                                <p style="border-bottom: 1px dotted;"></p>
                                <div>
                                    <div>Procurement:</div>
                                    <div>Name:</div>
                                    <div>Date:</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
                <div class="row">
                    <div class="col-xs-6">
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

                        <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) {
                            ?>
                        <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                            <div class="well well-sm">
                                <?=
                                '<p>' . lang('this_sale') . ': ' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                                . '<br>' .
                                lang('total') . ' ' . lang('award_points') . ': ' . $customer->award_points . '</p>'; ?>
                            </div>
                        </div>
                        <?php
                        } ?>
                    </div>

                    <div class="col-xs-6" style="display: none;">
                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
                        <div class="well well-sm">
                            <p><?= lang('created_by'); ?>: <?= $inv->created_by ? $created_by->first_name . ' ' . $created_by->last_name : $customer->name; ?> </p>
                            <p><?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?></p>
                            <?php if ($inv->updated_by) {
                            ?>
                                <p><?= lang('updated_by'); ?>
                                    : <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?></p>
                                <p><?= lang('update_at'); ?>: <?= $this->bpas->hrld($inv->updated_at); ?></p>
                            <?php
                        } ?>
                        </div>
                    </div>
                </div>

                <?php if ($inv->payment_status != 'paid') {
                            ?>
                    <div style="display: none;" id="payment_buttons" class="row text-center padding10 no-print">

                        <?php if ($paypal->active == '1' && $inv->grand_total != '0.00') {
                                if (trim(strtolower($customer->country)) == $biller->country) {
                                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                                } else {
                                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                                } ?>
                            <div class="col-xs-6 text-center">
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                    <input type="hidden" name="cmd" value="_xclick">
                                    <input type="hidden" name="business" value="<?= $paypal->account_email; ?>">
                                    <input type="hidden" name="item_name" value="<?= $inv->reference_no; ?>">
                                    <input type="hidden" name="item_number" value="<?= $inv->id; ?>">
                                    <input type="hidden" name="image_url"
                                           value="<?= admin_url() . 'assets/uploads/logos/' . $Settings->logo; ?>">
                                    <input type="hidden" name="amount"
                                           value="<?= ($inv->grand_total - $inv->paid) + $paypal_fee; ?>">
                                    <input type="hidden" name="no_shipping" value="1">
                                    <input type="hidden" name="no_note" value="1">
                                    <input type="hidden" name="currency_code" value="<?= $default_currency->code; ?>">
                                    <input type="hidden" name="bn" value="FC-BuyNow">
                                    <input type="hidden" name="rm" value="2">
                                    <input type="hidden" name="return"
                                           value="<?= admin_url('sales/view/' . $inv->id); ?>">
                                    <input type="hidden" name="cancel_return"
                                           value="<?= admin_url('sales/view/' . $inv->id); ?>">
                                    <input type="hidden" name="notify_url"
                                           value="<?= admin_url('payments/paypalipn'); ?>"/>
                                    <input type="hidden" name="custom"
                                           value="<?= $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee; ?>">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-block"><i
                                            class="fa fa-money"></i> <?= lang('pay_by_paypal') ?></button>
                                </form>
                            </div>
                        <?php
                            } ?>


                        <?php if ($skrill->active == '1' && $inv->grand_total != '0.00') {
                                if (trim(strtolower($customer->country)) == $biller->country) {
                                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                                } else {
                                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                                } ?>
                            <div class="col-xs-6 text-center">
                                <form action="https://www.moneybookers.com/app/payment.pl" method="post">
                                    <input type="hidden" name="pay_to_email" value="<?= $skrill->account_email; ?>">
                                    <input type="hidden" name="status_url"
                                           value="<?= admin_url('payments/skrillipn'); ?>">
                                    <input type="hidden" name="cancel_url"
                                           value="<?= admin_url('sales/view/' . $inv->id); ?>">
                                    <input type="hidden" name="return_url"
                                           value="<?= admin_url('sales/view/' . $inv->id); ?>">
                                    <input type="hidden" name="language" value="EN">
                                    <input type="hidden" name="ondemand_note" value="<?= $inv->reference_no; ?>">
                                    <input type="hidden" name="merchant_fields" value="item_name,item_number">
                                    <input type="hidden" name="item_name" value="<?= $inv->reference_no; ?>">
                                    <input type="hidden" name="item_number" value="<?= $inv->id; ?>">
                                    <input type="hidden" name="amount"
                                           value="<?= ($inv->grand_total - $inv->paid) + $skrill_fee; ?>">
                                    <input type="hidden" name="currency" value="<?= $default_currency->code; ?>">
                                    <input type="hidden" name="detail1_description" value="<?= $inv->reference_no; ?>">
                                    <input type="hidden" name="detail1_text"
                                           value="Payment for the sale invoice <?= $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->bpas->formatMoney($inv->grand_total + $skrill_fee); ?>">
                                    <input type="hidden" name="logo_url"
                                           value="<?= admin_url() . 'assets/uploads/logos/' . $Settings->logo; ?>">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-block"><i
                                            class="fa fa-money"></i> <?= lang('pay_by_skrill') ?></button>
                                </form>
                            </div>
                        <?php
                            } ?>
                        <div class="clearfix"></div>
                    </div>
                <?php
                        } ?>
                <?php if ($payments) {
                            ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-condensed print-table">
                                    <thead>
                                    <tr>
                                        <th><?= lang('date') ?></th>
                                        <th><?= lang('payment_reference') ?></th>
                                        <th><?= lang('paid_by') ?></th>
                                        <th><?= lang('amount') ?></th>
                                        <th><?= lang('created_by') ?></th>
                                        <th><?= lang('type') ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($payments as $payment) {
                                ?>
                                        <tr <?= $payment->type == 'returned' ? 'class="warning"' : ''; ?>>
                                            <td><?= $this->bpas->hrld($payment->date) ?></td>
                                            <td><?= $payment->reference_no; ?></td>
                                            <td><?= lang($payment->paid_by);
                                if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                    echo ' (' . $payment->cc_no . ')';
                                } elseif ($payment->paid_by == 'Cheque') {
                                    echo ' (' . $payment->cheque_no . ')';
                                } ?></td>
                                            <td><?= $this->bpas->formatMoney($payment->amount); ?></td>
                                            <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                            <td><?= lang($payment->type); ?></td>
                                        </tr>
                                    <?php
                            } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php
                        } ?>
            </div>
        </div>
        <?php if (!$Supplier || !$Customer) {
                            ?>
            <div class="buttons" style="display: none;">
                <div class="btn-group btn-group-justified">
                    <?php if ($inv->attachment) {
                                ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php
                            } ?>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/payments/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <?php if (!$inv->sale_id) {
                                ?>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_delivery') ?>">
                            <i class="fa fa-truck"></i> <span class="hidden-sm hidden-xs"><?= lang('add_delivery') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning tip sledit" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo"
                            title="<b><?= $this->lang->line('delete_sale') ?></b>"
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                            data-html="true" data-placement="top"><i class="fa fa-trash-o"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                    <?php
                            } ?>
                    <!--<div class="btn-group"><a href="<?= admin_url('sales/excel/' . $inv->id) ?>" class="tip btn btn-primary"  title="<?= lang('download_excel') ?>"><i class="fa fa-download"></i> <?= lang('excel') ?></a></div>-->
                </div>
            </div>
        <?php
                        } ?>
    </div>
</div>
