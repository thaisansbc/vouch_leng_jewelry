<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?= lang('view_a5') ?></title>
	<link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/custome.css" rel="stylesheet">
</head>
<style>
    @media print {
        .table-bordered th, 
        .table-bordered td{
            border: 1px solid #282b2e;
        }
        table th{
            background: #428bca;
        }
        @page {
            size: A5;
            margin: 0;
        }
    }
    .container {
		width: 29.7cm;
		margin: 20px auto;
		box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
	}
    table {
        width: 100%;
    }
    table th{
        background: #428bca;
    }
    #printable table th, #printable table td {
        padding: 5px;
        border: 1px solid #282b2e;
    }
    .approve_form {
        width: 32%;
        border: 1px solid #000000;
        float: left; 
        margin-right: 1.33%;
        padding: 5px;
        margin-bottom: 5px;
    }
</style>

<div class="box">
    <div class="container" style="width: 821px; margin: 10px auto;">
        <div class="col-xs-12" style="width: 794px;">
            <div class="row" style="margin-top: 20px !important;">
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:10px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <div class="well-sm">
                    <?php
                    if ($Settings->logo){
                        $path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                        $type   = pathinfo($path, PATHINFO_EXTENSION);
                        $data   = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                        <div>
                            <img src="<?= $path; ?>" alt="<?= $Settings->site_name; ?>"  style="max-height: 80px;">
                        </div>
                    <?php
                    } ?>
                    <h3 align="center"><strong style="margin-top: -20px; display: block;">PROJECT PLAN</strong></h3>
                </div>
                <div class="well-sm" style="border: 1px solid #282b2e; margin-bottom: 10px;">
                    <div class="col-xs-6 border-right">
                        <table>
                            <tr>
                                <td style="width: 55%;"><strong><?php echo $this->lang->line("title"); ?></strong></td>
                                <td style="width: 4%;">:</td>
                                <td><?= $inv->title; ?></td>
                            </tr>
                            <tr>
                                <td><strong><strong><?php echo $this->lang->line("reference_no"); ?></strong></td>
                                <td>:</td>
                                <td><?= $inv->reference_no; ?></td>
                            </tr>
                            <tr>
                                <td><strong><strong><?php echo $this->lang->line("date"); ?></strong></td>
                                <td>:</td>
                                <td><?= $inv->date; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php echo $this->lang->line("attachment_detal"); ?></strong></td>
                                <td>:</td>
                                <td>
                                    <span style="border: 1px solid #282b2e; width: 10px;height: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;</span> Yes 
                                    <span style="border: 1px solid #282b2e; width: 10px;height: 10px; margin-left: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;</span> No
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-6">
                        <div class="col-xs-12">
							<!-- <div>	<strong><?php echo $this->lang->line("date_purchase"); ?> : </strong> 
									<?= $this->bpas->hrld($inv->date); ?>
							</div> -->
							<div>	<strong><?php echo $this->lang->line("status"); ?> : </strong> 
									<?= lang($inv->status); ?>
							</div>
                            <?php
							if($inv->status =="approved"){
								echo '
								<div>	<strong>'.lang("approved_date").' : </strong> 
										'.$inv->approved_date.'
								</div>';
							}
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="table-responsive" id="printable">
                    <table class="print_table table-hover table-striped print-table order-table">
                        <thead>
                            <tr>
                                <th><?= lang("no"); ?></th>
                                <th><?= lang("expected_date"); ?></th>
                                <th><?= lang("description"); ?></th>
                                <th><?= lang("budget_code"); ?></th>
                                <th><?= lang("reason_for_request"); ?></th>
                                <?php if ($Settings->indian_gst) { ?>
                                    <th><?= lang("hsn_code"); ?></th>
                                <?php } ?>
                                <th style="text-align: center;"><?= lang("quantity"); ?></th>
                                <!-- <th><?= lang("unit"); ?></th> -->
                                <?php
                                    if ($inv->status == 'partial') {
                                        echo '<th>'.lang("received").'</th>';
                                    }
                                ?>
                                <th style="text-align: right;"><?= lang("unit_price"); ?></th>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                                }
                                ?>
                                <th style="text-align: right;"><?= lang("subtotal"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $r = 1;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;"><?= $inv->date; ?></td>
                                <td style="vertical-align:middle; width: 180px;">
                                    <?= $row->product_name; ?>
									<?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    <?php
                                        $data = explode("|",$row->variant);
                                        if($row->variant){
                                            echo $data[0];
                                            if(isset($data[1])){
                                            //  echo $data[1];
                                            }
                                        }
                                    ?>
                                    <?php 
                                        if(isset($data[1])){ echo $data[1]; }
                                        if(isset($data[2])){ echo ' | '.$data[2]; }
                                    ?>
								</td>
                                <td style="vertical-align:middle;"></td>
                                <td style="vertical-align:middle;"></td>
                                <?php if ($Settings->indian_gst) { ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                <?php } ?>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <!-- <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                } ?> -->
                                <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                            endforeach;
                        ?>
                        </tbody>
                        <tr>
                        <?php
                        $col = $Settings->indian_gst ? 8 : 7;
                        if ($inv->status == 'partial') {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                        }
                        if (($Settings->product_discount  && $inv->product_discount != 0) && ($Settings->tax1 && $inv->product_tax > 0)) {
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
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php } ?>
                        <?php
                        if ($return_purchase) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->tax2) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; font-weight:bold; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("balance"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold; padding-right: 10px;"><?= $this->bpas->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
                        </tr>
                        </tr>
                    </table>
                </div>
                <div class="row">
                    <div class="col-xs-4" style="margin-top: 15px;">
                        <p><?= lang("procurment"); ?></p>
                        <p><?= lang("aapproved_by"); ?>
                            <?php 
                                if($approves){
                                    echo " ". ucfirst($approves->first_name) . " " . ucfirst($approves->last_name);
                                }
                            ?>        
                        </p>
                    </div>
                    <div class="col-xs-8" style="margin-top: 15px;">
                        <p>
                            <span style="width: 10px;height: 10px; border: 1px solid #282b2e;">&nbsp;&nbsp;&nbsp;&nbsp;</span> Yes
                            <span style="width: 10px;height: 10px; border: 1px solid #282b2e; margin-left: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;</span> No
                        </p>
                        <p style="margin-bottom: 20%;">
                            <span style="width: 10px;height: 10px; border: 1px solid #282b2e;">&nbsp;&nbsp;&nbsp;&nbsp;</span> MD
                            <span style="width: 10px;height: 10px; border: 1px solid #282b2e; margin-left: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;</span> Manager Head ot Department
                        </p>
                    </div>
                </div>
                <div class="row" style="padding-bottom: 20px;">
                    <div class="col-xs-12">
                        <!-- <div class="col-xs-4">
                        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                            <?php if ($inv->note || $inv->note != "") { ?>
                                <div class="well well-sm">
                                    <p class="bold"><?= lang("note"); ?>:</p>

                                    <div><?= $this->bpas->decode_html($inv->note); ?></div>
                                </div>
                            <?php } ?>
                        </div> -->
                        <!-- <div class="col-xs-4 text-center">
                            <div class="well-sm">
                            <hr>
                            <p><?= lang("authorized"); ?></p>
                            <p><?= lang("date"); ?>: ............/............/............</p>
                            </div>
                        </div> -->
                        <div class="approve_form">
                            <?= lang("head_of_department"); ?><br/><br/>
                            <?= lang("signatur"); ?><br/>
                            <?= lang("head_name"); ?><br/>
                            <?= lang("head_date"); ?>
                        </div>
                        <div class="approve_form">
                            <?= lang("manager_of_department"); ?><br/><br/>
                            <?= lang("signatur"); ?><br/>
                            <?= lang("head_name"); ?><br/>
                            <?= lang("head_date"); ?>
                        </div>
                        <div class="approve_form">
                            <?= lang("requested_by"); ?><br/><br/>
                            <?= lang("signatur"); ?><br/>
                            <?= lang("head_name"); ?><br/>
                            <?= lang("head_date"); ?>
                        </div>
                        <div class="approve_form">
                            <?= lang("manager_director"); ?>
                            <h6>&nbsp;</h6>
                            <br/><br/>
                            <?= lang("signatur"); ?><br/>
                            <?= lang("head_name"); ?><br/>
                            <?= lang("head_date"); ?>
                        </div>
                        <div class="approve_form">
                            <?= lang("acknowledged_by"); ?>
                            <h6>(head of Finance)</h6>
                            <br/><br/>
                            <?= lang("signatur"); ?><br/>
                            <?= lang("head_name"); ?><br/>
                            <?= lang("head_date"); ?>
                        </div>
                        <div class="approve_form">
                            <?= lang("acknowledged_by"); ?>
                            <h6>(head of Finance)</h6>
                            <br/><br/>
                            <?= lang("signatur"); ?><br/>
                            <?= lang("head_name"); ?><br/>
                            <?= lang("head_date"); ?>
                        </div>
                        <div class="clearfix"></div>
                        </div>
                    </div>
                <div class="row">
                    <!-- <div class="col-xs-4">
                    <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                        <?php if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>
                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                    </div> -->
                    <!-- <div class="col-xs-4 text-center">
                        <div class="well-sm">
                        <hr>
                        <p><?= lang("authorized"); ?></p>
                        <p><?= lang("date"); ?>: ............/............/............</p>
                        </div>
                    </div> -->
                </div> 
            </div>
        </div>
    </div>
    <div style="width: 821px; margin: 10px auto;">
		<a class="btn btn-warning no-print" href="<?= site_url('admin/projects/plans'); ?>">
			<i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
		</a>
	</div>
</div>