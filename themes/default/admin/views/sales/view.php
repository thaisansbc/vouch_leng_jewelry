<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.sledit', function(e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?= lang('you_will_loss_sale_data') ?>", function(result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });
</script>
<style type="text/css" media="all">
    table {
        font-size: 11px !important;
    }

    @media print {
        table {
            font-size: 11px !important;
        }
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("sale_no") . ' ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>">
                        </i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>
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
                                <i class="fa fa-envelope"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <?php if (!$inv->sale_id) { ?>
                            <li>
                                <a href="<?= admin_url('deliveries/add/0/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                    <i class="fa fa-truck"></i> <?= lang('add_delivery') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= admin_url('sales/return_sale/' . $inv->id) ?>">
                                    <i class="fa fa-angle-double-left"></i> <?= lang('return_sale') ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <table class="" border="0" cellspacing="0" style="width:100%;" id="tb_outter">
                    <thead>
                        <tr>
                            <td>
                                <div class="col-xs-2">
                                    <?php if ($logo) { ?>
                                        <div><img style="width: 180px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$biller->logo; ?>" ></div>
                                    <?php } ?>                                
                                </div>
                                <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                                    <h2 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $biller->cf1; ?></h2>
                                    <h2 style="font-weight: bold; font-family: 'FontAwesome';"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                                    <div style="font-size:14px; font-weight: bold; line-height: 110%; text-align: center;">
                                        <?php
                                            echo '<p style="letter-spacing: 3px;">' . $biller->cf3 . '</p>';
                                            echo '<p>' . $biller->cf2 . '</p>';
                                            if($biller->address){
                                                echo '<p>' . $biller->address . '' . $biller->postal_code . '' . $biller->city . ' ' . $biller->country . '</p>';
                                            }
                                            if($biller->phone){
                                                echo '<p>Tel: ' . $biller->phone . '</p>';
                                            }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-xs-2 text-right order_barcodes" style="margin-top: 15px;">
                                    <!-- <?= $this->bpas->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?> -->
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-xs-6" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div>
                                <div class="col-xs-3 text-center" style="font-size: 20px; line-height: 55%; font-family: KhmerOS_muollight !important; font-weight: bold; padding: 0;">
                                    <p>វិក្កយបត្រ / <span style="margin-bottom: 0px;"><?= strtoupper('invoice') ?></span></p>
                                </div>
                                <div class="col-xs-3" style="border-bottom: 2px solid #2E86C1; text-align: center; margin-bottom: 10px;"></div> <!-- #5DADE2 -->
                            </td>
                        </tr>
                        <tr style="font-size: 11px;">
                            <td>
                                <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 49%; float: left; font-weight: bold; margin-bottom: 5px !important;margin-right:2%;">
                                    <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 40%; margin-bottom: -5px; font-style: italic !important;">ព័ត៍មានអតិថិជន</caption>
                                    <tr>
                                        <td style="width: 35%; padding-left: 5px;">អតិថិជន / <?= lang('customer'); ?></td>
                                        <td style="width: 1%;">:</td>
                                        <td style="width: 30%;"><b><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 5px;">ទូរស័ព្ទលេខ / Tel</td>
                                        <td>:</td>
                                        <td><?= $customer->phone ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 5px; vertical-align: top;">អាសយដ្ឋាន /<?= lang('address'); ?></td>
                                        <td style="vertical-align: top;">:</td>
                                        <td style="padding-bottom: 3px;"><?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ', ' . $customer->country; ?></td>
                                    </tr>
                                    <?php if($inv->payment_term) {?>
                                    <tr>
                                        <td style="padding-left: 5px;">Payment Term</td>
                                        <td>:</td>
                                        <td><?= $inv->payment_term ?> Day</td>
                                    </tr>
                                    <?php }?>
                                </table>
                      
                                <table style="border-radius: 10px; border: 2px solid #2E86C1; border-collapse: separate !important; width: 49%; font-weight: bold;">
                                    <caption style="display: block; position: relative; bottom: 6px; background-color: white !important; margin-left: 10px; width: 40%; margin-bottom: -5px; font-style: italic !important;">ឯកសារយោង</caption>
                                    <tr>
                                        <td style="width: 25%; padding-left: 5px;">វិក្កយបត្រ / Invoice NO</td>
                                        <td style="width: 1%;">:</td>
                                        <td style="width: 30%;"><?= $inv->reference_no; ?></td>
                                    </tr>
                                     <?php if (!empty($inv->return_sale_ref)) {
                                    ?>
                                    <tr>
                                        <td style="width: 25%; padding-left: 5px;"><?= lang("return_ref") ; ?></td>
                                        <td style="width: 1%;">:</td>
                                        <td style="width: 30%;"><?= $inv->return_sale_ref; ?>
                                            <?php 
                                            if ($inv->return_id) {
                                                echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    } ?>
                                    <tr>
                                        <td style="padding-left: 5px;">កាលបរិច្ឆាទ / Date</td>
                                        <td>:</td>
                                        <td><?= $this->bpas->hrsd($inv->date); ?></td>
                                    </tr>
                                    <tr class="hide">
                                        <td style="padding-left: 5px;">អ្នកគិតលុយ / Cashier</td>
                                        <td>:</td>
                                        <td><?php echo $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                                    </tr>
                                    <?php if($sold_by){ ?>
                                    <tr>
                                        <td style="padding-left: 5px;">អ្នកលក់ / Sale Man</td>
                                        <td>:</td>
                                        <td><?php echo $sold_by->first_name . ' ' . $sold_by->last_name; ?></td>
                                    </tr>
                                     <?php }?>
                                     <?php if($inv->po_number){ ?>
                                    <tr>
                                        <td style="padding-left: 5px;">លេខកម្មង់ / PO</td>
                                        <td>:</td>
                                        <td><?php echo $inv->po_number; ?></td>
                                    </tr>
                                     <?php }?>
                                </table>
                            </td>
                        </tr>
                        <!-- <tr><td>&nbsp;</td></tr> -->
                    </thead>
                </table>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table class="border1">
                            <tr>
                                <th>ល.រ​ <br><?= lang("no"); ?></th>
                                <th>បរិយាយមុខទំនិញ <br> <?= lang("description"); ?> (<?= lang("code"); ?>)</th>
                                <?php if ($Settings->indian_gst) { ?>
                                    <th><?= lang("hsn_code"); ?></th>
                                <?php } ?>
                                <th>បរិមាណ <br><?= lang("quantity"); ?></th>
                                <th>ថ្លៃឯកតា <br><?= lang("unit"); ?></th>
                                <?php
                                if ($Settings->product_serial) {
                                    echo '<th style="text-align:center; vertical-align:middle;">' . lang("serial_no") . '</th>';
                                }
                                ?>
                                <?php
                                if ($Settings->warranty) {
                                    echo '<th style="text-align:center; vertical-align:middle;">' . lang("warranty_expired") . '</th>';
                                }
                                ?>
                                <th style="padding-right:20px;"><?= lang("unit_price"); ?></th>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                                }
                                ?>
                                <th style="padding-right:20px;">តម្លៃសរុប <br><?= lang("subtotal"); ?></th>
                            </tr>
                            <?php $r = 1;
                            $currency_rate_usd = 1;
                            $currency_rate_kh=$inv->currency_rate_kh;
                            $currency_rate_bat=$inv->currency_rate_bat;

                            $total_downpayment;
                            $usd = " $";
                            $khm = " ៛";
                            $bat = " ฿";
                            foreach ($rows as $row) :
                            ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) { ?>
                                        <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php } ?>
                                    <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                    <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->product_unit_name; ?></td>
                                    <?php
                                    if ($Settings->product_serial) {
                                        echo '<td>' . $row->serial_no . '</td>';
                                    }
                                    if ($Settings->warranty) {
                                        $date = $inv->date;
                                        $warranty =date('Y-m-d', strtotime($date. ' + '.$row->warranty.' days'));
                                        $check_warranty = date('Y-m-d') > $warranty ? lang('expired') : $this->bpas->hrsd($warranty);                                        
                                        echo '<td>' .$check_warranty. '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:80px; padding-right:10px;"><?= $this->bpas->formatMoney($row->real_unit_price); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:70px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
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
                                            <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        </td>
                                        <?php if ($Settings->indian_gst) { ?>
                                            <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                        <?php } ?>
                                        <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->quantity); ?></td>
                                        <td style="width: 50px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                        <?php
                                        if ($Settings->product_serial) {
                                            echo '<td>' . $row->serial_no . '</td>';
                                        }
                                        if ($Settings->warranty) {
                                            echo '<td>' . $row->warranty . '</td>';
                                        }
                                        ?>
                                        <td style="text-align:right; width:80px; padding-right:10px;"><?= $this->bpas->formatMoney($row->real_unit_price); ?></td>
                                        <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            echo '<td style="width: 60px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                        }
                                        ?>
                                        <td style="text-align:right; width:70px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                    </tr>
                            <?php
                                    $r++;
                                endforeach;
                            }

                            ?>
                            <?php
                            $col = $Settings->indian_gst ? 6 : 5;
                            if ($Settings->product_serial) {
                                $col++;
                            }
                            if ($Settings->warranty) {
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
                                    <td colspan="<?= $tcol; ?>" style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                                </tr>
                            <?php } ?>


                             <?php if($down_payment && $issue_inv){
                                foreach($down_payment as $downpayment) { 
                                    $total_downpayment = $downpayment->amount; ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right; "><?= $downpayment->description; ?></td>
                                        <td style="text-align:right; "><?= $this->bpas->formatMoney($total_downpayment ? $total_downpayment : ''); ?></td>
                                    </tr>
                            <?php
                                }
                            } 
                            ?>
                            
                            <?php
                                if ($return_sale) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale->grand_total) . '</td></tr>';
                                }
                                if ($inv->surcharge != 0) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                                }
                            ?>

                            <?php if ($inv->order_discount != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                            }
                            ?>

                            <?php if ($issue_inv && $down_payment) { 
                                    $vat_dpm = $inv->order_tax * 100 / $inv->total ;
                                    $percent_dpm=  $vat_dpm *  $total_downpayment / 100;
                                ?>
                                <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("VAT") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($percent_dpm) . '</td></tr>';
                                    }
                                 ?>
                            <?php }else{ ?>

                                <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                                }
                                ?>
                            <?php } ?>

                            <!-- <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                            }
                            ?> -->

                            <?php if ($inv->shipping != 0) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                            }
                            ?>

                            <?php if ($issue_inv && $down_payment) { ?> 
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($percent_dpm + $total_downpayment ); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                        (<?= $KHM->code; ?>)
                                    </td>
                                    <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($percent_dpm + $total_downpayment *  ($currency_rate_kh)); ?></td>
                                </tr>
                            <?php }else{?>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                        (<?= $KHM->code; ?>)
                                    </td>
                                    <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) *  ($currency_rate_kh) : $inv->grand_total *  ($currency_rate_kh)); ?></td>
                                </tr>
                            <?php } ?>

                            <!-- <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                            </tr>
                           
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                    (<?= $KHM->code; ?>)
                                </td>
                                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale * ($currency_rate_kh) ? ($inv->grand_total + $return_sale->grand_total) *  ($currency_rate_kh) : $inv->grand_total *  ($currency_rate_kh)); ?></td>
                            </tr> -->

                            <?php if ($inv->paid > 0) { ?>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                            </tr>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                            </tr>
                            <?php 
                                if(empty($issue_inv)){ ?>
                                    <tr>
                                        <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("overdue"); ?>
                                            (<?= $usd; ?>)
                                        </td>
                                        <td style="text-align:right; font-weight:bold;">
                                            <?= (($TotalSalesDue->total_amount - $TotalSalesDue->paid) > 0) ? $this->bpas->formatMoney($TotalSalesDue->total_amount - $TotalSalesDue->paid) : '0.00' ?>
                                        </td>
                                    </tr> <?php 
                                } ?>
                            <?php } ?>

                          
                            <!-- <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                            </tr>
                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                            </tr>

                            <tr>
                                <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("overdue"); ?>
                                    (<?= $usd; ?>)
                                </td>
                                <td style="text-align:right; font-weight:bold;">
                                    <?= (($TotalSalesDue->total_amount - $TotalSalesDue->paid) > 0) ? $this->bpas->formatMoney($TotalSalesDue->total_amount - $TotalSalesDue->paid) : '0.00' ?>

                                </td>
                            </tr> -->

                    </table>
                </div>

                <div class="row">
                    <div class="col-xs-6 pull-right">
                        <div class="well-sm">
                            <p>
                                <?= lang("seller"); ?>:<br>
                                <?= lang("date"); ?>: ............./............./...................
                            </p>
                        </div>
                    </div>
                    <div class="col-xs-6 pull-right">
                        <div class="well-sm">
                            <p>
                                <?= lang("buyer"); ?>:<br>
                                <?= lang("date"); ?>: ............./............./...................
                            </p>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <?php
                        if($issue_inv && $get_all_down_payments) { ?> 
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>
                                <?php foreach($get_all_down_payments as $downpayment) { ?>
                                    <div> <?= $downpayment->description ?> </div>
                                <?php } ?>
                            </div>
                        <?php } elseif ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                            </div>
                        <?php
                        }

                        if ($inv->staff_note || $inv->staff_note != "") { ?>
                            <div class="well well-sm staff_note">
                                <p class="bold"><?= lang("staff_note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->staff_note); ?></div>
                            </div>
                        <?php } ?>

                        <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
                            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                                <div class="well well-sm">
                                    <?=
                                        '<p>' . lang('this_sale') . ': ' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                                            . '<br>' .
                                            lang('total') . ' ' . lang('award_points') . ': ' . $customer->award_points . '</p>'; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-xs-6" style="display: none;">
                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
                        <div class="well well-sm">
                            <p><?= lang("created_by"); ?>
                                : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>

                            <p><?= lang("date"); ?>: <?= $this->bpas->hrld($inv->date); ?></p>
                            <?php if ($inv->updated_by) { ?>
                                <p><?= lang("updated_by"); ?>
                                    : <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></p>
                                <p><?= lang("update_at"); ?>: <?= $this->bpas->hrld($inv->updated_at); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if ($payments) { ?>
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
                                        <?php foreach ($payments as $payment) { ?>
                                            <tr <?= $payment->type == 'returned' ? 'class="warning"' : ''; ?>>
                                                <td><?= $this->bpas->hrld($payment->date) ?></td>
                                                <td><?= $payment->reference_no; ?></td>
                                                <td><?= lang($payment->paid_by);
                                                    if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                                        echo ' (' . $payment->cc_no . ')';
                                                    } elseif ($payment->paid_by == 'Cheque') {
                                                        echo ' (' . $payment->cheque_no . ')';
                                                    }
                                                    ?></td>
                                                <td><?= $this->bpas->formatMoney($payment->amount); ?></td>
                                                <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                                <td><?= lang($payment->type); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <?php if ($inv->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <div class="btn-group">
                        <a href="<?= admin_url('sales/view_a5/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_a5') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_a5') ?></span>
                        </a>
                    </div>

                    <?php if ($issue_inv) { ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/add_downpayment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_down_payments') ?>">
                                <i class="fa fa-money" style="color:orange"></i> <span class="hidden-sm hidden-xs"><?= lang('add_down_payments') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/view_down_payments/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_down_payments') ?>">
                                <i class="fa fa-money" style="color:orange"></i> <span class="hidden-sm hidden-xs"><?= lang('view_down_payments') ?></span>
                            </a>
                        </div>
                    <?php } ?>

                    <div class="btn-group hide">
                        <a href="<?= admin_url('sales/view_Invoce_downpayment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_down_payment') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_down_payment') ?></span>
                        </a>
                    </div>

                    <div class="btn-group">
                        <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>">
                            <i class="fa fa-money" style="color:yellow"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                        </a>
                    </div>

                    <div class="btn-group">
                        <a href="<?= admin_url('sales/payments/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                            <i class="fa fa-money"style="color:yellow"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                        </a>
                    </div>

                    <div class="btn-group">
                        <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <?php if (!$inv->sale_id) { ?>

                        <div class="btn-group">
                            <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning tip sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_sale") ?></b>" data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" data-html="true" data-placement="top"><i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <!--<div class="btn-group"><a href="<?= admin_url('sales/excel/' . $inv->id) ?>" class="tip btn btn-primary"  title="<?= lang('download_excel') ?>"><i class="fa fa-download"></i> <?= lang('excel') ?></a></div>-->
                </div>
            </div>
        <?php } ?>
    </div>
</div>