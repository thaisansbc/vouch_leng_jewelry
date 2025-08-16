<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        .table-bordered th,
        .table-bordered td{
            border: 1px solid #282b2e;
        }
        table th{
            background: #428bca;
        }
        .logo_left img{
            margin-top:-110px;
        }
        .img{
        margin-top:-100px;
	    }
    }
    table{
        width: 100%;
    }
    .no-border td{
        border: none !important;
        padding: 1px;
    }
    table th{
        background: #428bca;
    }
    #printable table th,#printable table td{
        padding: 5px;
        border: 1px solid #282b2e;
    }
    .page_:not(:first-of-type) {
                page-break-before: always !important;
                margin-top: 40px !important;
                display: flex;
            }
    .page_:first-of-type {
        page-break-before: avoid !important;
        display: flex;
    } 
    .approve_form{
        width: 100%;
        margin-right: 1%;
        padding: 5px;
        margin-bottom: 5px;
    }
    @font-face {
            font-family: 'KhmerOS_muollight';
            src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
        }

</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("purchase_no") . '. ' . $inv->id; ?></h2>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                        </a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <?php if ($inv->attachment) { ?>
                                <li><a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                        <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="<?= admin_url('purchases/payments/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                    <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= admin_url('purchases/add_payment/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                    <i class="fa fa-money"></i> <?= lang('add_payment') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>">
                                    <i class="fa fa-edit"></i> <?= lang('edit_purchase') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= admin_url('purchases/email/' . $inv->id) ?>">
                                    <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                                </a>
                            </li>
                            <!--
                            <li>
                                <a href="<?= admin_url('purchases_request/pdf/' . $inv->id) ?>">
                                    <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                                </a>
                            </li>-->
                            <li class="hide">
                                <?php // admin_url('purchases_request/excel_export/' . $inv->id) ?>
                                <a href="#" id="xls">
                                    <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                                </a>
                            </li>
                            <li>
                                
                                <a href="<?= admin_url('purchases_request/excel_export/' . $inv->id) ?>" id="xls">
                                    <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                                </a>
                            </li>
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
                <div class="col-lg-6 text-left">
                    <img width="140" src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                             alt="<?= $Settings->site_name; ?>">
                </div>
                <div class="col-lg-6 text-right">
                    <img  width="140" src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo2; ?>"
                                alt="<?= $Settings->site_name; ?>">
                </div>
            </div>
            <div class="col-lg-12">
                <div class="text-center" style="margin-top:-30px;">
                    <h4 align="center" style="font-family:'KhmerOS_muollight';"><strong>ទម្រង់បែបបទស្នើរសុំការចំណាយ</strong></h4>
                    <h5 align="center" style="font-family:'KhmerOS_muollight';"><strong>មូលធន/ប្រតិបត្តិការ </strong></h5>
                </div>
                <div class="well-sm" style="border: 1px solid #282b2e;  font-family:khmer os siemreap;font-size:11px;">
                    <table width="100%">
                        <tr>
                            <td width="55%">
                                <table>
                                    <tr>
                                        <td width="150"><strong><?php echo $this->lang->line("នាយកដ្ឋានស្នើ៖"); ?> </strong> </td>
                                        <td>: <?= $warehouse->name; ?></td>
                                    </tr>
                                    <tr>
                                        <td width="150"><strong><?php echo $this->lang->line("លេខយោង៖"); ?> </strong> </td>
                                        <td>: <?= $inv->reference_no; ?></td>
                                    </tr>
                                    <tr>
                                        <td width="230"><strong><?php echo $this->lang->line("ឯកសារភ្ជាប់លំអិត៖"); ?> </strong> </td>
                                        <td><img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">មាន</span>
                                                 &nbsp; &nbsp; <img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">មិនមាន</span></td>
                                    </tr>
                                </table>
                            </td> 
                            <td width="35%">
                                <table>
                                    <tr class="hide">
                                        <td width="150"><strong><?php echo $this->lang->line("Reason_for_request"); ?> </strong> :
                                        <?=  $this->site->getProjectByID($inv->project_id)->project_name;?> 
                                            <?=  $this->site->getProjectByID($inv->project_id)->description;?></td>
                                    </tr>
                                    <tr class="hide">
                                        <td width="150"><strong><?php echo $this->lang->line("date"); ?> </strong> </td>
                                        <td>: <?php $this->bpas->hrld($inv->date); ?></td>
                                    </tr>
                                  
                                </table>
                            </td> 
                        </tr>
                    </table>
                </div>
        
                <div class="table-responsive" id="printable" style="font-size: 11px;font-family:khmer os siemreap;">
                    <table class="print_table table-bordered table-hover table-striped print-table order-table">
                            <thead>
                                <tr>
                                    <th width="20px"><?= lang("ល.រ"); ?></th>
                                    <th style="text-align:center;"><?= lang("បរិយាយ"); ?></th>
                                    <th style="text-align:center;"><?= lang("មូលហេតុស្នើសុំ"); ?></th>
                                    <th style="text-align:center;"><?= lang("លេខកូដថវិកាយោង "); ?></th>
                                    <th style="text-align:center;"><?= lang("កាលបរិច្ឆេទប៉ាន់ស្មាន"); ?></th>
                                    <?php if ($Settings->indian_gst) { ?>
                                        <th><?= lang("លេខកូដថវិកាយោង "); ?></th>
                                    <?php } ?>
                                    <th style="text-align:center;"><?= lang("ចំនួន"); ?></th>
                                    <?php
                                        if ($inv->status == 'partial') {
                                            echo '<th>'.lang("received").'</th>';
                                        }
                                    ?>
                                    <th style="padding-right:5px;text-align:center;"><?= lang("ឯកតា "); ?></th>
                                    <th style="padding-right:5px;text-align:center;"><?= lang("តម្លៃឯកតា "); ?></th>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<th style="padding-right:5px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                                    }
                                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                        echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                                    }
                                    ?>
                                    <th style="padding-right:5px;text-align:center;"><?= lang("ចំណាយប៉ាន់ស្មាន"); ?></th>
                                </tr>
                            </thead>

                            <tbody style="font-size:10px;">
                                <?php $r = 1;
                                $erow = 1; 
                                $no = 1;
                                $totalRow = 0;
                            if($rows){
                                foreach ($rows as $row):
                                    ?>
                                        <tr>
                                            <td style="text-align:center; width:20px; vertical-align:middle;"><?= $r; ?></td>
                                    
                                            <td style="vertical-align:middle;width: 700px;">
                                                <?= $row->product_name; ?>
                                                            <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                                <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                                                <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                                <!-- color -->
                                                <?php
                                                $data= explode("|",$row->variant);
                                                if($row->variant){
                                                    echo $data[0];
                                                    if(isset($data[1])){
                                                    //  echo $data[1];
                                                    }
                                                }
                                                //  ($row->variant ? ' (' . $row->variant . ')' : '');
                                                ?>
                                                <!-- size -->
                                                <?php if(isset($data[1])){ echo $data[1];}
                                                if(isset($data[2])){ echo ' | '.$data[2];}
                                                ?>
                                            </td>
                                            
                                            <td style="vertical-align:middle;width: 500px;"> <?= $row->description; ?></td>

                                            <td style="vertical-align:middle;width: 100px;"></td>
                                            
                                            <td style="vertical-align:middle; width: 100px">
                                                <?= $this->bpas->hrsd($inv->date); ?>
                                            </td>

                                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                            <?php
                                            if ($inv->status == 'partial') {
                                                echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                            }
                                            ?> 
                                            <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                            <?php
                                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                            }
                                            if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                            }
                                            ?>
                                            <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                        </tr>

                                    <?php
                                        $r++;
                                        $no++; 
                                        $erow ++; 
                                        $totalRow++;       
                                        endforeach;
                                    }    
                                    ?>
                                    
                                 <?php
                                    if($erow > 11){
                                        $k= 21 - $erow;
                                        for($j=1; $j<=$k; $j++) {
                                            echo '<tr>
                                                    <td height="34px" style="text-align: center; vertical-align: middle">'.$no.'</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>';
                                                    if($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                                        echo '<td></td>';
                                                    }
                                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                                        echo '<td></td>';   
                                                    }
                                            echo '</tr>';
                                            $no++;
                                        }
                                    }elseif($erow < 5 ){
                                        $k= 7 - $erow;
                                        for($j=1; $j<=$k; $j++) {
                                            echo '<tr>
                                                    <td height="34px" style="text-align: center; vertical-align: middle">'.$no.'</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>';
                                                    if($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                                        echo '<td></td>';
                                                    }
                                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                                        echo '<td></td>';   
                                                    }
                                            echo '</tr>';
                                            $no++;
                                    }
                                }
                                ?>
                            </tbody>
                   

                            <tr>    
                                <?php
                                $col = $Settings->indian_gst ? 9 : 8;
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
                                        <td colspan="<?= $tcol; ?>"
                                            style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                            (<?= $default_currency->code; ?>)
                                        </td>
                                        <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                        }
                                        ?>
                                        <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                                    </tr>
                                <?php } ?>
                                <?php
                                if ($return_purchase) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                                }
                                if ($inv->surcharge != 0) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                                }
                                ?>

                                <?php  if ($inv->order_discount != 0) {
                                    echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                                }
                                ?>
                
                                <tr>
                                    <td colspan="<?= $col; ?>"
                                        style="text-align:right; font-weight:bold;"><?= lang("សរុប :"); ?>
                                        (<?= $default_currency->code; ?>) :
                                    </td>
                                    <td style="text-align:right; padding-right:5px; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
                                </tr>
                            </tr>
                    </table>
               </div>

                <?php 
          
                    if ($no > 11) {
                        echo '<div class="page_"></div>';
                    }
                
                ?>
                
               <div width="100%">
                    <div style="height:10px;">&nbsp;</div>
                        <table width="100%" style="font-family:khmer os siemreap;font-size:10.5px;">
                            <tr>
                                <td width="30%"><?= lang("នាយកដ្ឋានលទ្ធកម្ម៖"); ?></td>
                                <td>
                                    <img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">មាន</span>
                                    &nbsp; &nbsp; <img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">មិនមាន</span>
                                </td>
                            </tr>
                            <tr>
                                <td width="30%"><?= lang("អនុម័តដោយ ៖"); ?></td>
                                <td>
                                    <img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">នាយកប្រតិបត្តិ</span>
                                    &nbsp; &nbsp; <img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">អគ្គនាយកប្រតិបត្តិ</span>
                                    &nbsp; &nbsp; <img style="width:17px" src="https://img.icons8.com/ios-filled/50/000000/unchecked-checkbox.png"/> <span style="font-family:khmer os siemreap;font-size: 10px;font-weight: bold;">ប្រធានផ្នែក/ប្រធាននាយកដ្ឋាន</span>
                                </td>
                                </td>
                            </tr>
                        </table>
                    <br>
                    <div style="width: 100%;font-size:10.5px;margin-top:-10px;">
                        <table width="100%" style="font-family:khmer os siemreap;">
                            <?php 
                            foreach($PersonApproved as $key => $val){
                                if($val){
                            ?>
                           <!-- ---------- -->
                            <?php
                                }
                            }

                            foreach ($approved__ as $key__ => $value__)
                                {
                                    $key2 = $key__;
                                }
                            ?>
                            <tr>
                            <?php
                            //if($approved_){ 
                                
                            	foreach ($approved as $key => $value) {

                                    //if($value != "" && $value != null && is_numeric($value) && $key != 'id' && $key != 'purchase_request_id'){
                                        $user = $this->site->getUser($value);?>
                                        <?php foreach($approved_ as $key_l => $value_l){
                                               $ml = explode('_by', $key);
                                               $nl = $ml[0].'_status';

                                               if($key_l == $nl){
                                                    $style = ($value_l != 'approved') ? 'hide':"";
                                                }
                                            } ?>
                                        	<td style="border: 1px solid #000000;" class="<?= $style; ?>">
                                        	    <div class="approve_form">
                                        	        <?= lang($key); ?><br><br><br>
                                        	        <?= lang("ហត្ថលេខា ៖ "); ?>
                                        	        <?php if($user->signature){ ?>  
                                        	            <img alt="" src="<?= base_url() ?>assets/uploads/avatars/<?= $user->signature ?>" class="" style="width:100px;height:70px;">
                                        	        <?php } ?> 
                                        	        <br>
                                        	        <?= lang("ឈ្មោះ ៖ "); ?><?= $user->first_name . ' ' . $user->last_name; ?><br/>
                                        	        <?php foreach($approved_ as $key_ => $value_){
                                        	           $m = explode('_by', $key);
                                        	           $n = $m[0].'_date';
                                        	           if($key_ == $n){ ?>
                                        	               <?= lang("កាលបរិច្ឆេទ ៖ "); ?><?= $value_; ?>
                                        	           <?php 
                                        	        } 
                                        	      } ?>
                                        	    </div>
                                        	</td>
                                    <?php
                                    //}
                                }
                            //} ?>
                            </tr>
                               <!--  <td width="33%" style="border: 1px solid #000000;">
                                    <div class="approve_form">
                                        <?= lang("ប្រធានផ្នែក"); ?><br/><br/><br><br><br>
                                        <?= lang("ហត្ថលេខា ៖"); ?><br/>
                                        <?= lang("ឈ្មោះ ៖"); ?><br/>
                                        <?= lang("កាលបរិច្ឆេទ ៖"); ?>
                                    </div>
                                </td>
                                <td colspan="2" width="33%" style="border: 1px solid #000000;">
                                    <div class="approve_form">
                                        <?= lang("អ្នកស្នើសុំ"); ?><br/><br/><br><br><br>
                                        <?= lang("ហត្ថលេខា ៖"); ?><br/>
                                        <?= lang("ឈ្មោះ ៖"); ?><br/>
                                        <?= lang("កាលបរិច្ឆេទ ៖"); ?>
                                    </div>
                                </td> 
                            <tr>
                                <td width="33%" style="border: 1px solid #000000;">
                                    <div class="approve_form">
                                        <?= lang("អនុប្រធាននាយកប្រតិបត្តិ"); ?>
                                        <h6>&nbsp;</h6>
                                        <br/><br/>
                                        <?= lang("ហត្ថលេខា ៖ "); ?><br/>
                                        <?= lang("ឈ្មោះ៖"); ?><br/>
                                        <?= lang("កាលបរិច្ឆេទ ៖ "); ?>
                                    </div>
                                </td>
                                <td width="33%" style="border: 1px solid #000000;">
                                    <div class="approve_form">
                                        <?= lang("ទទួលស្គាល់ដោយ"); ?>
                                        <h6 style="font-family:khmer os siemreap;font-size:10px;">(ប្រធាននាយកដ្ឋានហិរញ្ញវត្ថុ)</h6>
                                        <br/><br/>
                                        <?= lang("ហត្ថលេខា ៖ "); ?><br/>
                                        <?= lang("ឈ្មោះ៖"); ?><br/>
                                        <?= lang("កាលបរិច្ឆេទ ៖ "); ?>
                                    </div>
                                </td>
                                <td colspan="2" width="33%" style="border: 1px solid #000000;">
                                    <div class="approve_form">
                                        <?= lang("ទទួលស្គាល់ដោយ "); ?>
                                        <h6 style="font-family:khmer os siemreap;font-size:10px;">(ប្រធាននាយកដ្ឋានរដ្ឋបាលនិងធនធានមនុស្ស)</h6>
                                        <br/><br/>
                                        <?= lang("ហត្ថលេខា ៖ "); ?><br/>
                                        <?= lang("ឈ្មោះ៖"); ?><br/>
                                        <?= lang("កាលបរិច្ឆេទ ៖ "); ?>
                                    </div>
                                </td>
                            </tr> -->
                        </table>
                        <table width="100%" style="border: 1px solid #000000;font-family:khmer os siemreap" class="text-center" > 
                            <tr>
                                <td style="padding-bottom:120px !important;padding:10px 0">នាយកប្រតិបត្តិ</td>
                            </tr>
                        </table>
                        <table class="no-print hide">
                            <tr>
                                <td class="2">
                                    <img width="280" src="<?= base_url() . 'assets/uploads/form/head1.png'; ?>"></td>
                                <td class="2">
                                    <img width="280" src="<?= base_url() . 'assets/uploads/form/head2.png'; ?>"></td>
                                <td class="2">
                                    <img width="280" src="<?= base_url() . 'assets/uploads/form/head3.png'; ?>"></td>
                            </tr>
                        </table>
                        <br><br>
                        <table class="no-print hide">
                            <tr>
                                <td class="2">
                                    <img width="280" src="<?= base_url() . 'assets/uploads/form/head4.png'; ?>"></td>
                                <td class="2">
                                    <img width="280" src="<?= base_url() . 'assets/uploads/form/head5.png'; ?>"></td>
                                <td class="2">
                                    <img width="280" src="<?= base_url() . 'assets/uploads/form/head6.png'; ?>"></td>
                            </tr>
                        </table>
                        <div class="clearfix"></div>
                    </div> 
                     <div class="clearfix"></div>         
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#xls").click(function(e) {
      var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8">'+
        '<style> #printable table th, #printable table td{ border:1px solid #000000; }table { white-space:wrap;} table th, table td{ font-size:10px !important; } .approve_form{width: 32%;border: 1px solid #000000;float: left; margin-right: 1%;padding: 5px;margin-bottom: 5px;} .checkbox{ width: 10px;height: 10px;border: 1px solid #282b2e;}</style>' + $('.box-content').html());
      this.href = result;
      this.download = "Purchases Request.xls";
      return true;      
    });
</script>
