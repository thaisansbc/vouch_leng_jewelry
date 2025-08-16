<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php if ($sales[0]['modal']) { ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content"><div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
    <?php
} else {
    $rec_cur= $inv->currency =="usd" ? "$" : "៛";
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
                font-size: 11px !important;
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
            th span .text1{
                text-align: left;
            }
            th span .text2{
                text-align: right;
            }
            @media print {
                table{
                    font-size: 11px !important;
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
                    <?php echo  !empty($sales[0]['biller']->logo) ? '<img src="'.base_url('assets/uploads/logos/'.$sales[0]['biller']->logo).'" alt="">' : ''; ?>
                    <h3 style="text-transform:uppercase;"><?= $sales[0]['biller']->company != '-' ? $sales['biller']->company : $sales['biller']->name;?></h3>
                </div>
                <?php

                if ($Settings->invoice_view == 1 || $Settings->indian_gst) {
                    ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('tax_invoice');?></h4>
                    </div>
                    <?php
                }else{
                     ?>
                    <div class="col-sm-12 text-center">
                        <h4 style="font-weight:bold;"><?=lang('receipt');?></h4>
                    </div>
                    <?php
                }
                ?>
                <div style="clear:both;"></div>
                <table class="table table-condensed">
                    <thead style="border:1px solid #adabab;background:#f8f8f8;">
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </thead>
                    <tbody>
                        <?php
                $grand_total_all=0;
                foreach($sales as $sale){
                    
                    $detault_currency= $Settings->default_currency =="USD" ? "$" : "៛";
                    $payments_=$sale['payments'][0];
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
                    if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                        echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
                    }
                    if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                        echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
                    }
                    echo '</p>';
    
                        $r = 1; $category = 0;
                        $tax_summary = array();
                        $defaultGrandTotal = 0;
                        echo '<tr> <td colspan="100%" style="border-top:none;"> <b> - Inv N<sup>o</sup> : '.$sale['inv']->reference_no.'</b></td></tr>';
                        foreach ($sale['rows'] as $row) {
                            if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                $category = $row->category_id;
                                echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                            }
                            $defaultPrice = $this->site->getProductDefaultPrice($row->product_id);
                            $defaultGrandTotal = $defaultGrandTotal + ($row->real_unit_price * $row->unit_quantity);
                            if($row->product_code != "Time"){
                          
                            echo '<tr>
                                    <td class="border-bottom-">'.product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') .'</td>';
                                        if($row->product_code == "Time"){
                                            $hour =$row->unit_quantity;
                                            $seconds = $hour * 3600;
                                            $H = floor($seconds / 3600);
                                            $i = ($seconds / 60) % 60;
                                            $s = $seconds % 60;
                                            $time = sprintf("%02dh %02dm", $H, $i);
                                            echo '<td class="border-bottom">'.$time.'</td>';

                                        }else{
                                            echo '<td class="border-bottom-">('.$row->product_unit_code.')'.$this->bpas->formatQuantity($row->unit_quantity).'</td>';
                                        }
                            if($defaultPrice->price !== $row->unit_price){
                                echo '
                                        <td class="border-bottom-"><del>'.$row->currency.$this->bpas->formatMoney($defaultPrice->price) . '</del> ' . $row->currency . $this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td>
                                        <td class=" border-bottom text-right">'.$row->currency.$this->bpas->formatMoney($row->subtotal) . '</td>
                                    </tr>';
                            }else{
                                echo '
                                    <td class="border-bottom-">'. $row->currency . $this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td>
                                    <td class="text-right">'.$row->currency.$this->bpas->formatMoney($row->subtotal) . '</td>
                                </tr>';
                            }
                                if (!empty($row->second_name)) {
                                    echo '<tr><td colspan="2" class="no-border">'.$row->second_name.'</td></tr>';
                                }
                            }
                            $r++;
                        }
                        if ($sale['return_rows']) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($sale['return_rows'] as $row) {
                                if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                                    $category = $row->category_id;
                                    echo '<tr><td colspan="100%" class="no-border"><strong>'.$row->category_name.'</strong></td></tr>';
                                }
                                echo '<tr>
                                        <td class="no-border">'.$r.'</td>';
                                echo '<td class="no-border">'. product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td>';
                                echo '<td class="no-border border-bottom">' . $this->bpas->formatQuantity($row->unit_quantity).'</td>';
                                echo '<td class="no-border border-bottom">' . $this->bpas->formatQuantity($row->unit_quantity) . ' x '.$this->bpas->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($Settings->indian_gst ? $row->tax : $row->tax_code).')</small> '.$this->bpas->formatMoney($row->item_tax).' ('.lang('hsn_code').': '.$row->hsn_code.')' : '').'</td><td class="no-border border-bottom text-right">' . $this->bpas->formatMoney($row->subtotal) . '</td></tr>';
                                $r++;
                            }
                        } 
                        $grand_total_all +=(($sale['inv']->total + $sale['inv']->product_tax)+($sale['return_sale']->total + $sale['return_sale']->product_tax));
                        ?>
                            
                             <tr>
                                <td colspan="2" class="text-right"><?php echo "សរុប";?>/<?=lang("total");?></td>
                                <th colspan="2" ><span class="pull-left">:</span>
                                <span class="pull-right"><?= $detault_currency.$this->bpas->formatMoney($sale['return_sale'] ? (($sale['inv']->total + $sale['inv']->product_tax)+($sale['return_sale']->total + $sale['return_sale']->product_tax)) : ($sale['inv']->total + $sale['inv']->product_tax));?></span></td>
                            </tr>
                        <?php
                    }
                        ?>
                    </tbody>

                    <tfoot>
                        <?php
                        if ($pos_settings->rounding || $inv->rounding != 0) {
                            ?>
                            <tr>
                                <td><?=lang("rounding");?></td>
                                <th class="text-right"><?= $this->bpas->formatMoney($inv->rounding);?></td>
                            </tr>
                            <tr>
                                <td><?=lang("grand_total");?></td>
                                <th class="text-right"><?=$this->bpas->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));?></td>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <?php
                            if($Settings->default_currency =='USD'){
                                $default_grand_total_lang = "សរុបចុងក្រោយ".'/'.lang("grand_total");
                                $grand_total_lang_kh = "សរុបចុងក្រោយជារៀល".'/'.lang("grand_total_kh");
                            }else{
                                $default_grand_total_lang = "សរុបចុងក្រោយជារៀល".'/'.lang("grand_total_kh");
                                $grand_total_lang_kh = "សរុបចុងក្រោយ".'/'.lang("grand_total");
                            }
                            ?>
                            <tr style="border:1px solid #adabab;">
                                <th colspan="2" style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;text-align: right;">
                                    <?= $default_grand_total_lang;?></td>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</td>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= $detault_currency.$this->bpas->formatMoney($return_sale ? $grand_total_all : $grand_total_all);?></td>
                            </tr>
                            <tr style="border:1px solid #adabab;">
                                <th colspan="2" class="text-right" style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">
                                    <?php echo $grand_total_lang_kh;?></td>
                                <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</td>
                                <?php 

                                if($Settings->default_currency =='USD'){
                                    $gTotalkh = $this->bpas->formatDecimal($return_sale ? ($grand_total_all * $currency_rate_kh) :($grand_total_all * $currency_rate_kh));
                                    if($gTotalkh % 100 >= 50):
                                    ?>
                                    <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= $khm. number_format(ceil($gTotalkh/100)*100);?></td>
                                    <?php
                                    else:
                                    ?>
                                    <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= $khm. number_format(ceil($gTotalkh/100)*100);?></td>
                                <?php 
                                    endif;
                                }else{
                                    $gTotalud = $return_sale ? $grand_total_all : $grand_total_all;
                                    ?>
                                    <th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right">
                                        <?= $usd. $this->bpas->formatMoney($gTotalud/$currency_rate_kh);?></td>
                                  
                                    <?php
                                }
                                ?>
                            </tr>
                            <tr><th colspan="2" class="text-right " style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">អត្រាប្តូរប្រាក់/<?= lang('rate');?></th><th style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;">:</th>
                            	<th colspan="2" style="border-top:1px solid #adabab;border-bottom:1px solid #adabab;" class="text-right"><?= lang('1$') ?>=៛<?= $this->bpas->formatMoney($currency_rate_kh); ?></th></tr>
                            <?php
                        }
                        ?>
                    </tfoot>
                </table>
                <?= $this->Settings->developed_by ? '<div class="text-center"><font style="font-size:11px;">Solutions provided by <a href="http://sbcsolution.biz/">SBC Solutions</a></font></div>' : ''; ?>
            </div>
            
            <div class="order_barcodes text-center">
                <?php echo $this->Settings->developed_by ? $this->bpas->qrcode('link', urlencode('http://sbcsolution.biz/'), 2) : ''; ?>
            </div>
            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="text-transform:uppercase;" class="no-print">
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

            if ($sales[0]['modal']) {
                ?>
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <?php
                        if ($sales[0]['pos']->remote_printing == 1) {
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
                    if ($sales[0]['pos']->remote_printing == 1) {
                        echo '<button onclick="window.print();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                    } else {
                        echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">'.lang("open_cash_drawer").'</button>';
                    }
                    ?>
                </span>
                <span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?php
                    if ($pos_settings->pos_type =="pos") {
                        echo admin_url('pos');
                    }else{
                        echo admin_url('table');
                    }?>"><?= lang("back_to_pos"); ?></a>
                </span>
                 <span class="pull-left col-xs-12"><a class="btn btn-block btn-info" href="<?php echo admin_url('pos/sales')?>" id="sales"><?= lang("sales"); ?></a></span>
                <?php
            }
            if ($sales[0]['pos']->remote_printing == 1) {
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
    if( ! $sale[0]['modal']) {
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
    <?php
    if($sales[0]['modal']) {
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
