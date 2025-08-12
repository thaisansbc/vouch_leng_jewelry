<?php //$this->bpas->print_arrays($discount['discount']) 
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>View A5</title>
	<link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/custome.css" rel="stylesheet">
</head>
<style>
	body {
		font-size: 14px !important;
	}

	.container {
		width: 29.7cm;
		margin: 20px auto;
		/*padding: 10px;*/
		box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
	}

	@media print {
		.customer_label {
			padding-left: 0 !important;
		}

		.invoice_label {
			padding-left: 0 !important;
		}

		#footer hr p {
			font-size: 1px !important;
			position: absolute !important;
			bottom: 0 !important;
			/*margin-top: -30px !important;*/
		}
        @page {
            /* dimensions for the whole page */
            size: A5;
            
            margin: 0;
        }

		/* .row table tr td {
			font-size: 10px !important;
		} */

		/*.row table tr th {
			font-size: 8px !important;
		}*/
		/* .table thead>tr>th,
		.table tbody>tr>th,
		.table tfoot>tr>th {
			background-color: #444 !important;
			color: #FFF !important;
		} */

		footer {
			page-break-after: always;
		}

		.row .col-xs-7 table tr td,
		.col-sm-5 table tr td {
			font-size: 10px !important;
		}

		#note {
			max-width: 95% !important;
			margin: 0 auto !important;
			border-radius: 5px 5px 5px 5px !important;
			margin-left: 26px !important;
		}
	}


	.company_addr h3:first-child {
		font-family: Khmer OS Muol !important;
		//padding-left: 12% !important;
	}

	.company_addr h3:nth-child(2) {
		margin-top: -2px !important;
		//padding-left: 130px !important;
		font-size: 26px !important;
		font-weight: bold;
	}

	.company_addr h3:last-child {
		margin-top: -2px !important;
		//padding-left: 100px !important;
	}

	.company_addr p {
		font-size: 12px !important;
		margin-top: -10px !important;
		padding-left: 20px !important;
	}

	.inv h4:first-child {
		font-family: Khmer OS Muol !important;
		font-size: 14px !important;
	}

	.inv h4:last-child {
		margin-top: -5px !important;
		font-size: 14px !important;
	}

	button {
		border-radius: 0 !important;
	}
</style>

<body>
	<br>
	<div class="container" style="width: 821px;margin: 0 auto;">
		<div class="col-xs-12" style="width: 794px;">
			<div class="row" style="margin-top: 20px !important;">
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;" id="logo">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" height="50px">
                </div>
            <?php } ?>
            <!-- <div class="col-xs-12 text-center">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
            </div> -->
            <div class="well well-sm" id="a5" style="height: 80px;">
                <div class="row bold">
                    <div class="col-xs-7">
                        <p class="bold">
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?><span>,&nbsp;&nbsp;</span>
                            <?= lang('sale_status'); ?>: <?= lang($inv->sale_status); ?><span><br></span>
                            <?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?><span>,&nbsp;&nbsp;</span>
                            <?php if ($inv->payment_status != 'paid') {
                            echo '' . lang('due_date') . ': ' . $this->bpas->hrsd($inv->due_date);
                            } ?><span><br></span>
                            <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                            <?php if (!empty($inv->return_sale_ref)) {
                            echo lang('return_ref') . ': ' . $inv->return_sale_ref;
                            if ($inv->return_id) {
                                echo ' <a href="' . admin_url('sales/view_a5/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                            } else {
                                echo '<br>';
                            }
                            } ?>
                           
                            
                        </p>
                    </div>
                    <div class="col-xs-5 text-right order_barcodes" id="order_barcodes">
                        <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" height="50px"/>
                        <?= $this->bpas->qrcode_a5('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                    </div>
                    
                </div>
                
            </div>
            <div class="clearfix"></div>
            <div class="row" style="margin-bottom:1px;">

                <!-- <?php if ($Settings->invoice_view == 1) {
                ?>
                    <div class="col-xs-12 text-center">
                        <h1><?= lang('tax_invoice'); ?></h1>
                    </div>
                <?php
                } ?> -->

                <div class="col-xs-6" id="to" style="margin-top:1px;">
                    <?php echo $this->lang->line('to'); ?>:<br/>
                    
                    <h6 style="margin-top:1px;"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h6>
                    <?= $customer->company                              && $customer->company != '-' ? '' : 'Attn: ' . $customer->name ?>

                    <?php
                    echo $customer->address . ' ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ' ' . $customer->country;

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

                <div class="col-xs-6" id="info" style="margin-top:1px;">
                    <?php echo $this->lang->line('from'); ?>:
                    
                    <h6 style="margin-top: 1px;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h6>
                    <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                    <?php
                    echo $biller->address . ' ' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . ' ' . $biller->country;

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

            <div class="clearfix"></div>
            <div class="table-responsive">
                <table class="table table-hover table-striped print-table order-table" >

                    <thead>

                    <tr>
                        <th style="text-align: center;">ល.រ​ <br><?= lang('no.'); ?></th>
                        <th style="text-align: center;">បរិយាយមុខទំនិញ <br><?= lang('description'); ?></th>
                        <?php if ($Settings->indian_gst) {
                        ?>
                            <th style="text-align: center;"><?= lang('hsn_code'); ?></th>
                        <?php
                    } ?>
                        <th style="text-align: center;">បរិមាណ <br><?= lang('quantity'); ?></th>
                        <th style="text-align: center;">ថ្លៃឯកតា <br><?= lang('unit_price'); ?></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang('tax') . '</th>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th>' .'បញ្ចុះតម្លៃ <br>'. lang('discount') . '</th>';
                        }
                        ?>
                        <th style="text-align: center;">តម្លៃសរុប <br><?= lang('subtotal'); ?></th>
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
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . ($inv->sale_status == 'returned' ? $row->base_unit_code : $row->product_unit_name); ?></td>
                            <td style="text-align:center; width:100px;">
                                <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                <?= $this->bpas->formatMoney($row->unit_price); ?>
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:center; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:center; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:center; width:120px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
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
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->quantity) . ' ' . $row->base_unit_code; ?></td>
                                <td style="text-align:center; width:100px;"><?= $this->bpas->formatMoney($row->unit_price); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:center; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style="width: 100px; text-align:center; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                        } ?>
                                <td style="text-align:center; width:120px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <?php if ($inv->grand_total != $inv->total) {
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:center; padding-right:10px;"><?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:center;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                            }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style="text-align:center;">' . $this->bpas->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                        } ?>
                            <td style="text-align:center; "><?= $this->bpas->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                        </tr>
                    <?php
                    } ?>
                    <?php
                    if ($return_sale) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:center;">' . $this->bpas->formatMoney($return_sale->grand_total) . '</td></tr>';
                    }
                    if ($inv->surcharge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:center;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                    }
                    ?>

                    <?php if ($Settings->indian_gst) {
                        if ($inv->cgst > 0) {
                            $cgst = $return_sale ? $inv->cgst + $return_sale->cgst : $inv->cgst;
                            echo '<tr><td colspan="' . $col . '" class="text-center">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-center">' . ($Settings->format_gst ? $this->bpas->formatMoney($cgst) : $cgst) . '</td></tr>';
                        }
                        if ($inv->sgst > 0) {
                            $sgst = $return_sale ? $inv->sgst + $return_sale->sgst : $inv->sgst;
                            echo '<tr><td colspan="' . $col . '" class="text-center">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-center">' . ($Settings->format_gst ? $this->bpas->formatMoney($sgst) : $sgst) . '</td></tr>';
                        }
                        if ($inv->igst > 0) {
                            $igst = $return_sale ? $inv->igst + $return_sale->igst : $inv->igst;
                            echo '<tr><td colspan="' . $col . '" class="text-center">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-center">' . ($Settings->format_gst ? $this->bpas->formatMoney($igst) : $igst) . '</td></tr>';
                        }
                    } ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:center;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:center;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:center;">' . $this->bpas->formatMoney($inv->shipping - ($return_sale && $return_sale->shipping ? $return_sale->shipping : 0)) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;">តម្លៃសរុបរួម​​ <?= lang('total_amount'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:center; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;">បានបង់ប្រាក់ចំនួន​ <?= lang('paid'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:center; font-weight:bold;"><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;">ចំនួនទឹកប្រាក់ <?= lang('balance'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:center; font-weight:bold;"><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                    </tr>

                    </tfoot>
                </table>
            </div>

            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>

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

                <div class="col-xs-4 pull-left"  style="margin-top: 50px; ">
                    <hr class="signature" style="border-top: 1px solid black;">
                    <p>ហត្ថលេខា​ និងឈ្មោះអតិជន<br>Customer's signature and Name</p>
                    <p></p> 
                </div>
                <div></div>
                <div class="col-xs-4 pull-right" style="margin-top: 50px; ">
                    <hr class="signature" style="border-top: 1px solid black;">
                    <p>ហត្ថលេខា​ និងឈ្មោះអ្នកលក់<br>Seller's signature and Name</p>
                
                </div>

                <div class="col-xs-5 pull-right" style="display: none;">
                    <div class="well well-sm">
                        <p>
                            <?= lang('created_by'); ?>: <?= $inv->created_by ? $created_by->first_name . ' ' . $created_by->last_name : $customer->name; ?> <br>
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
			<footer></footer>
			<br>
		</div>
	</div>
	<br>
	<div style="width: 821px;margin: 0 auto;">
		<a class="btn btn-warning no-print" href="<?= site_url('admin/sales'); ?>">
			<i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
		</a>
	</div>
	<br>
</body>

</html>