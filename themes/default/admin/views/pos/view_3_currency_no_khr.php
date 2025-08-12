<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php if ($modal) { ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content"><div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
    <?php
} else {
	$rec_cur= $inv->currency =="usd" ? "USD" : "៛";
    ?>
<!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?=$page_title . " " . lang("no") . " " . $inv->id;?></title>
        <base href="<?=base_url()?>"/>
        <meta http-equiv="cache-control" content="max-age=0"/>
        <meta http-equiv="cache-control" content="no-cache"/>
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
        <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
        <style type="text/css" media="all">
            body { color: #000;}
            #wrapper { max-width: 480px; margin: 0 auto; padding-top: 10px; }
            .btn { border-radius: 0; margin-bottom: 5px; }
            .bootbox .modal-footer { border-top: 0; text-align: center; }
            h3 { margin: 5px 0; }
            .order_barcodes img { float: none !important; margin-top: 5px; }
			table{
				font-size: 13px !important;
			}
			table .border_top{
				border-top:none;
			}
			table .border_bottom{
				border-bottom:none;
			}
			table .border_left{
				border-left:none;
			}
			table .border_right{
				border-right:none;
			}
            @media print {
				table{
					font-size: 13px !important;
				}

				table .border_left{
					border-left:none;
				}
				table .border_right{
					border-right:none;
				}
                .no-print { display: none; }
                #wrapper { max-width: 480px; width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #ddd !important; }
                table tfoot { display: table-row-group; }
            }
        </style>
    </head>

    <body>
        <?php
    } ?>
    <div id="wrapper">
        <div id="receiptData">
            <div class="no-print">
                <?php
                if ($message) {
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?=is_array($message) ? print_r($message, true) : $message;?>
                    </div>
                    <?php
                } ?>
            </div>
            <div id="receipt-data">
                <div class="text-center">
                    <?php echo  !empty($biller->logo) ? '<img width="130px" src="'.base_url('assets/uploads/logos/'.$biller->logo).'" alt="">' : ''; ?>
                    <h3><?=$biller->company != '-' ? $biller->company : $biller->name;?></h3>
                    <?php
					$payments_=$payments[0];
					if(isset($payments_)){
						$paid_amount= explode(',',$payments_->paid_amount);
						$paid_amount_usd=$paid_amount[0];
						$paid_amount_kh=$paid_amount[1];
						$paid_amount_bat=$paid_amount[2];
						$currency_rate= explode(',',$payments_->currency_rate);
						$currency_rate_usd=$currency_rate[0];
						$currency_rate_kh=$currency_rate[1];
						$currency_rate_bat=$currency_rate[2];
					}else{
						$paid_amount_usd='';
						$paid_amount_kh='';
						$paid_amount_bat='';
						$currency_rate_usd='';
						$currency_rate_kh='';
						$currency_rate_bat='';
					}
					$usd=" $";
					$khm=" ៛";
					$bat=" B";
				   echo "<p>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .
                    "<br>" . lang("tel") . ": " . $biller->phone;
                    // comment or remove these extra info if you don't need
                    if (!empty($biller->cf1) && $biller->cf1 != "-") {
                        echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                    }
                    if (!empty($biller->cf2) && $biller->cf2 != "-") {
                        echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                    }
                    if (!empty($biller->cf3) && $biller->cf3 != "-") {
                        echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                    }
                    if (!empty($biller->cf4) && $biller->cf4 != "-") {
                        echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                    }
                    if (!empty($biller->cf5) && $biller->cf5 != "-") {
                        echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                    }
                    if (!empty($biller->cf6) && $biller->cf6 != "-") {
                        echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                    }
                    // end of the customer fields
                    echo "<br>";
                    if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                        echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
                    }
                    if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                        echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
                    }
                    echo '</p>';
                    ?>
                </div>
                <?php
			
                if ($Settings->invoice_view == 1 || $Settings->indian_gst) {
                    ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('tax_invoice');?></h4>
                    </div>
                    <?php
                }
                ?>
				<table width="100%">
					<tr>
						<td width="15%"><?php echo lang("customer");?></td>
						<td width="30%"><?php echo ': '.($customer->company && $customer->company != '-' ? $customer->company : $customer->name);?></td>
						<td width="15%">Invoice N<sup>0</sup></td>
						<td width="40%">
						<?php echo ': '.$inv->reference_no;
						 if (!empty($inv->return_sale_ref)) {
							echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
							if ($inv->return_id) {
								echo ' <a data-target="#myModal2" data-toggle="modal" href="'.admin_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
							} else {
								echo '</p>';
							}
						}
						?>
						</td>
					</tr>
					<tr style="border-bottom:1px dashed #cccccc;">
						<td width="15%">Cashier</td>
						<td width="30%"><?php echo ': '.$created_by->first_name." ".$created_by->last_name;?></td>
						<td width="15%">Date</td>
						<td width="40%"><?php echo ': '.$this->bpas->hrld($inv->date);?></td>
					</tr>
			
					<?php                  
					if ($pos_settings->customer_details) {
						echo "<tr>";
							echo "<td>";
								echo lang("address");
							echo "</td>";
							echo "<td colspan='3'>";
								echo ': '.$customer->address." ".$customer->city ." ".$customer->state." ".$customer->country ."<br>";
							echo "</td>";
						echo "</tr>";
					}
					?>
				</table>
                <div style="clear:both;"></div>
                <table class="table table-condensed">
                    <thead style="border:1px solid #adabab;background:#f8f8f8;">
						<th>N<sup>0</sup></th>
						<th>Description</th>
						<th>Qty</th>
						<th>Price</th>
						<th>Amount</th>
					</thead>
					<tbody>
                        <?php
                        $r = 1; $category = 0;
                        $tax_summary = array();
						$defaultGrandTotal = 0;
                        foreach ($rows as $row) {
                            if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                $category = $row->category_id;
                                echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                            }
							$defaultPrice = $this->site->getProductDefaultPrice($row->product_id);
							$defaultGrandTotal = $defaultGrandTotal + ($defaultPrice->price * $row->unit_quantity);
                            echo '<tr>
									<td>' . $r . '</td>
									<td>'.product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '</td>';
										if($row->product_code == "Time"){
											$hour =$row->unit_quantity;
											$seconds = $hour * 3600;
											$H = floor($seconds / 3600);
											$i = ($seconds / 60) % 60;
											$s = $seconds % 60;
											$time = sprintf("%02dh %02dm", $H, $i);
											echo '<td border-bottom">'.$time.'</td>';
											
										}else{
											echo '<td border-bottom">'.$this->bpas->formatQuantity($row->unit_quantity).'</td>';
										}
							/*if($defaultPrice->price !== $row->unit_price){
								echo '		
										<td border-bottom"><del>'.$usd.$this->bpas->formatMoney($defaultPrice->price) . '</del> ' . $usd . $this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td>
										<td class="no-border border-bottom text-right">'.$usd.$this->bpas->formatMoney($row->subtotal) . '</td>
									</tr>';
							}else{*/
								echo '		
									<td border-bottom">'. $usd . $this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td>
									<td class="no-border border-bottom text-right">'.$usd.$this->bpas->formatMoney($row->subtotal) . '</td>
								</tr>';
							//}
								if (!empty($row->second_name)) {
									echo '<tr><td colspan="2" class="no-border">'.$row->second_name.'</td></tr>';
								}
							

                            $r++;
                        }
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row) {
                                if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                    $category = $row->category_id;
                                    echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                                }
                                echo '<tr><td colspan="2" class="no-border">#' . $r . ': &nbsp;&nbsp;' . product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td></tr>';
                                echo '<tr><td class="no-border border-bottom">' . $this->bpas->formatQuantity($row->unit_quantity) . ' x '.$this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td><td class="no-border border-bottom text-right">' . $this->bpas->formatMoney($row->subtotal) . '</td></tr>';
								$r++;
                            }
                        }

                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
							<th><?php echo "ប្រាក់សរុប";?></th>
                            <th><?=lang("total");?></th>
                            <th>:</th>
                            <th class="text-right">
							<?= $usd.$this->bpas->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));?></th>
                        </tr>
						
                        <?php
                        if ($inv->order_tax != 0) {
                            echo '<tr><th>' . lang("tax") . '</th><th class="text-right">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
                        }
                        if ($inv->order_discount != 0) {
                            echo '<tr>
									<th></th>
									<th>បញ្ចុះតំលៃ</th>
									<th>' . lang("order_discount") . '</th>
									<th>:</th>
									<th class="text-right">' . $this->bpas->formatMoney($inv->order_discount) . '</th></tr>';
                        }
						/*if($customer_group->percent){
							echo '<tr>
									<th></th>
									<th>បញ្ចុះតំលៃ</th>
									<th>' . lang("order_discount") . '</th>
									<th>:</th>
									<th class="text-right">' . (-1 * $customer_group->percent) . '%' . '</th></tr>';
							 //echo (-1 * $customer_group->percent) . '%';
						}*/
                        if ($inv->shipping != 0) {
                            echo '<tr><th>' . lang("shipping") . '</th><th class="text-right">' . $this->bpas->formatMoney($inv->shipping) . '</th></tr>';
                        }
                        if ($return_sale) {
                            if ($return_sale->surcharge != 0) {
                                echo '<tr><th>' . lang("order_discount") . '</th><th class="text-right">' . $this->bpas->formatMoney($return_sale->surcharge) . '</th></tr>';
                            }
                        }
                        if ($Settings->indian_gst) {
                            if ($inv->cgst > 0) {
                                $cgst = $return_sale ? $inv->cgst + $return_sale->cgst : $inv->cgst;
                                echo '<tr><td>' . lang('cgst') .'</td><td class="text-right">' . ( $Settings->format_gst ? $this->bpas->formatMoney($cgst) : $cgst) . '</td></tr>';
                            }
                            if ($inv->sgst > 0) {
                                $sgst = $return_sale ? $inv->sgst + $return_sale->sgst : $inv->sgst;
                                echo '<tr><td>' . lang('sgst') .'</td><td class="text-right">' . ( $Settings->format_gst ? $this->bpas->formatMoney($sgst) : $sgst) . '</td></tr>';
                            }
                            if ($inv->igst > 0) {
                                $igst = $return_sale ? $inv->igst + $return_sale->igst : $inv->igst;
                                echo '<tr><td>' . lang('igst') .'</td><td class="text-right">' . ( $Settings->format_gst ? $this->bpas->formatMoney($igst) : $igst) . '</td></tr>';
                            }
                        }
                        if ($pos_settings->rounding || $inv->rounding != 0) {
                            ?>
                            <tr>
                                <th><?=lang("rounding");?></th>
                                <th class="text-right"><?= $this->bpas->formatMoney($inv->rounding);?></th>
                            </tr>
                            <tr>
                                <th><?=lang("grand_total");?></th>
                                <th class="text-right"><?=$this->bpas->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));?></th>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <tr style="border:1px solid #adabab;">
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;"></th>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;"><?php echo "ប្រាក់សរុបចុងក្រោយ";?></th>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;"><?=lang("grand_total");?></th>
								<th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</th>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?=$usd.$this->bpas->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);?></th>
                            </tr>
                            <?php
                        }
						$totalSaved = $defaultGrandTotal - ($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);
						if($totalSaved > 0):
						?>
							<tr style="border:1px solid #adabab;">
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;"></th>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;"><?php echo "សរុបការបញ្ចុះតំលៃ";?></th>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;"><?=lang("Total Saved");?></th>
								<th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</th>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?=$usd.$this->bpas->formatMoney($totalSaved);?></th>
                            </tr>
						<?php
						endif;
                        if ($inv->paid < ($inv->grand_total + $inv->rounding)) {
                           $original_paid_amount = $this->bpas->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
							if($original_paid_amount == 0){
							?>
								<tr>
									<th class="border_top border_left"></th>
									<th class="border_top"><?php echo "ប្រាក់បង់";?></th>
									<th width="110px" class="border_top"><?=lang("paid_amount").$usd;?></th>
									<th class="border_top">:</th>
									<th class="border_top border_right text-right">
									<?php //= $usd.$this->bpas->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);?>
									<?= $usd.$original_paid_amount;?>
									</th>
								</tr>
								<?php
							}else{
								if($paid_amount_usd){
								?>
								<tr>
									<th class="border_top border_left"></th>
									<th class="border_top"><?php echo "ប្រាក់បង់";?></th>
									<th width="110px" class="border_top"><?=lang("paid_amount").$usd;?></th>
									<th class="border_top">:</th>
									<th class="border_top border_right text-right">
									<?php //= $usd.$this->bpas->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);?>
									<?= $usd.$paid_amount_usd;?>
									</th>
								</tr>
								<?php
								}
								if($paid_amount_kh){
									/// $this->bpas->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
								?>
								 <tr>
									<th class="border_left"></th>
									<th><?php echo ($paid_amount_usd)? "" : "ប្រាក់បង់";?></th>
									<th width="110px" ><?=lang("paid_amount").$khm;?></th>
									<th>:</th>
									<th class="border_right text-right">
									<?= $khm.$this->bpas->formatMoney($paid_amount_kh);?>
									</th>
								</tr>
								<?php
								}
								if($paid_amount_bat){
									// $this->bpas->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
								?>
								 <tr>
									<th class="border_top border_left"></th>
									<th class="border_top"><?php echo ($paid_amount_usd)? "" : "ប្រាក់បង់";?></th>
									<th class="border_top"><?=lang("paid_amount").$bat;?></th>
									<th class="border_top">:</th>
									<th class="border_top border_right text-right">
									<?= $bat.$this->bpas->formatMoney($paid_amount_bat);?>
									</th>
								</tr>
								<?php
								}
							}
								$origi_due_amount=$this->bpas->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));
							if($original_paid_amount == 0){
								?>
								<tr>
									<th class="border_left"></th>
									<th><?php echo "ប្រាក់ជំពាក់​";?></th>
									<th><?=lang("due_amount").$usd;?></th>
									<th>:</th>
									<th class="border_right text-right">
										<?= $usd.$origi_due_amount;?>
									</th>
								</tr>
								<?php
							}else{
								if($paid_amount_usd){
								?>
								<tr>
									<th class="border_left"></th>
									<th><?php echo "ប្រាក់ជំពាក់​";?></th>
									<th><?=lang("due_amount").$usd;?></th>
									<th>:</th>
									<th class="border_right text-right">
										<?= $usd.$origi_due_amount;?>
									</th>
								</tr>
								<?php
								}
								if($paid_amount_kh){
									$due_kh=$origi_due_amount;
								?>
								<tr>
									<th class="border_left"></th>
									<th><?php echo ($paid_amount_usd)? "" : "ប្រាក់ជំពាក់";?></th>
									<th><?=lang("due_amount").$khm;?></th>
									<th>:</th>
									<th class="border_right text-right">
										<?= $khm.$this->bpas->formatMoney($due_kh*$currency_rate_kh);?>
									</th>
								</tr>
								<?php
								}
								if($paid_amount_bat){
									$due_kh=$this->bpas->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));
								?>
								<tr>
									<th class="border_left"></th>
									<th><?php echo ($paid_amount_usd)? "" : "ប្រាក់ជំពាក់";?></th>
									<th><?=lang("due_amount").$bat;?></th>
									<th>:</th>
									<th class="border_right text-right">
										<?= $bat.$this->bpas->formatMoney($due_kh*$currency_rate_bat);?>
									</th>
								</tr>
								<?php
								}
							}
                        }
						if ($payments) {
							foreach ($payments as $payment) {
								if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
									if($paid_amount_usd){
										$amount_usd=$this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '');
										//var_dump($amount_usd);
										echo '<tr>';
										echo '<th class="border_top border_left"></th>';
										echo '<td class="border_top">' . "ប្រាក់ទទួលជាដុល្លា".'</td>';
										echo '<td class="border_top">' . lang("amount").'('.$usd.')</td>';
										echo '<td class="border_top">:</td>';
										//	echo '<td class="text-right">'.$usd.$this->bpas->formatMoney($payment->pos_paid == 0 ? ($payment->amount / $inv->other_cur_paid_rate) : ($payment->pos_paid /$inv->other_cur_paid_rate) ) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
											echo '<td class="border_top border_right text-right">'.$amount_usd. '</td>';
										echo '</tr>';
									}
									if($paid_amount_kh){
										$amount_kh=$this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '');
										echo '<tr>';
											echo '<th class="border_top border_left"></th>';
											echo '<td class="border_top">' . "ប្រាក់ទទួលជារៀល".'</td>';
											echo '<td class="border_top">' . lang("amount").'('.$khm.')</td>';
											echo '<td class="border_top">:</td>';
												echo '<td class="border_top border_right text-right">'.$khm.$this->bpas->formatMoney($paid_amount_kh).'</td>';
										echo '</tr>';
									}
									if($paid_amount_bat){
										$amount_bat=$this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '');
										echo '<tr>';
											echo '<th class="border_top border_left"></th>';
											echo '<td class="border_top">' . "ប្រាក់ទទួលជាបាត".'</td>';
											echo '<td class="border_top">' . lang("amount").'('.$bat.')</td>';
											echo '<td class="border_top">:</td>';
												echo '<td class="border_top border_right text-right">'.$bat.$this->bpas->formatMoney($amount_bat * $currency_rate_bat).'</td>';
										echo '</tr>';
									}
									if($paid_amount_usd){
										echo '<tr>';
											echo '<th class="border_bottom border_left"></th>';
											echo '<td class="border_bottom">' ."ប្រាក់អាប់".'</td>';
											echo '<td class="border_bottom">' .lang("change").$usd.'</td>';
											echo '<td class="border_bottom">:</td>';
											echo '<td class="border_bottom border_right text-right">'.$usd.$this->bpas->formatMoney($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0) . '</td>';
										echo '</tr>';
									}elseif($paid_amount_kh){
										$change_kh=$this->bpas->formatMoney($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0);
										echo '<tr>';
											echo '<th class="border_bottom border_left"></th>';
											echo '<td class="border_bottom">' ."ប្រាក់អាប់".'</td>';
											echo '<td class="border_bottom">' .lang("change").$khm.'</td>';
											echo '<td class="border_bottom">:</td>';
											echo '<td class="border_bottom border_right text-right">'.$khm.$this->bpas->formatMoney($change_kh * $currency_rate_kh) . '</td>';
										echo '</tr>';
									}else{
										$change_bat=$this->bpas->formatMoney($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0);
										echo '<tr>';
											echo '<th class="border_bottom border_left"></th>';
											echo '<td class="border_bottom">' ."ប្រាក់អាប់".'</td>';
											echo '<td class="border_bottom">' .lang("change").$bat.'</td>';
											echo '<td class="border_bottom">:</td>';
											echo '<td class="border_bottom border_right text-right">'.$bat.$this->bpas->formatMoney($change_bat * $currency_rate_bat).'</th>';
										echo '</tr>';
									}
								}
								
							}
					
						}?>
                    </tfoot>
                </table>
                <?php
                if ($payments) {
                    echo '<table class="table table-striped table-condensed"><tbody>';
                    foreach ($payments as $payment) {
                        echo '<tr>';
                     /*   if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0) . '</td>';
                        }*/
						if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . $this->bpas->formatMoney($this->bpas->getCardBalance($payment->cc_no)) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }

                if ($return_payments) {
                    echo '<strong>'.lang('return_payments').'</strong><table class="table table-striped table-condensed"><tbody>';
                    foreach ($return_payments as $payment) {
                        $payment->amount = (0-$payment->amount);
                        echo '<tr>';
                        if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->bpas->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("no") . ': ' . $payment->cc_no . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo '<td>' . lang("balance") . ': ' . $this->bpas->formatMoney($this->bpas->getCardBalance($payment->cc_no)) . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->bpas->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                ?>

                <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax)) : ''; ?>

                <?= $customer->award_points != 0 && $Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                .'<br>'.
                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>' : ''; ?>
                <?= $inv->note ? '<p class="text-center">' . $this->bpas->decode_html($inv->note) . '</p>' : ''; ?>
                <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->bpas->encode_html($inv->staff_note) . '</p>' : ''; ?>
                <?= $biller->invoice_footer ? '<div class="text-center">'.$this->bpas->decode_html($biller->invoice_footer).'</div>' : ''; ?>
            </div>
            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="ext-transform:uppercase;" class="no-print">
            <hr>
            <?php
            if ($message) {
                ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?=is_array($message) ? print_r($message, true) : $message;?>
                </div>
                <?php
            } ?>
            <?php
            if ($modal) {
                ?>
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <?php
                        if ($pos->remote_printing == 1) {
                            echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        } else {
                            echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        }

                        ?>
                    </div>
                    <div class="btn-group" role="group">
                        <a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <span class="pull-right col-xs-12">
                    <?php
                    if ($pos->remote_printing == 1) {
                        echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                    } else {
                        echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">'.lang("open_cash_drawer").'</button>';
                    }
                    ?>
                </span>
                <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                <span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?php
					if ($pos_settings->pos_type =="pos") {
						echo admin_url('pos');
					}else{
						echo admin_url('table');
					}?>"><?= lang("back_to_pos"); ?></a>
                </span>
                <?php
            }
            if ($pos->remote_printing == 1) {
                ?>
                <div style="clear:both;"></div>
                <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                    <p style="font-weight:bold;">
                        Please don't forget to disble the header and footer in browser print settings.
                    </p>
                    <p style="text-transform: capitalize;">
                        <strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp; Header/Footer Make all --blank--
                    </p>
                    <p style="text-transform: capitalize;">
                        <strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer in Option &amp; Set Margins to None
                    </p>
                </div>
                <?php
            } ?>
            <div style="clear:both;"></div>
        </div>
    </div>

    <?php
    if( ! $modal) {
        ?>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
        <?php
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#email').click(function () {
                bootbox.prompt({
                    title: "<?= lang("email_address"); ?>",
                    inputType: 'email',
                    value: "<?= $customer->email; ?>",
                    callback: function (email) {
                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?= admin_url('pos/email_receipt') ?>",
                                data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                                dataType: "json",
                                success: function (data) {
                                    bootbox.alert({message: data.msg, size: 'small'});
                                },
                                error: function () {
                                    bootbox.alert({message: '<?= lang('ajax_request_failed'); ?>', size: 'small'});
                                    return false;
                                }
                            });
                        }
                    }
                });
                return false;
            });
        });

        <?php
        if ($pos_settings->remote_printing == 1) {
            ?>
            $(window).load(function () {
                window.print();
                return false;
            });
            <?php
        }
        ?>

    </script>
    <?php /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */ ?>
    <?php include 'remote_printing.php'; ?>
    <?php
    if($modal) {
        ?>
    </div>
</div>
</div>
<?php
} else {
    ?>
</body>
</html>
<?php
}
?>
