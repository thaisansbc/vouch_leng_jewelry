<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('receipt_of_payments'); ?></h4>
        </div>
        <style type="text/css">
        body{
                font-family: 'Roboto','khmer os battambang', sans-serif;
                    font-size: 12px;
        }
        @media print {
            font-family: 'Roboto','khmer os battambang', sans-serif;
                font-size: 12px;
        }
        </style>
        <div class="modal-body" id="myfrm">
            <div class="table-responsive">
                <table width="100%">
                    <tr>
                        <td style="width: 8%;">
                            <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" 
                            style="width: 150px;margin-top:-40px; ">
                        </td>
                        <td style="width: 60%; text-align: center;">
                            <div>
                                <div style="text-align: center;">
                                    <h1 style="font-family:'Khmer OS Muol Light'; font-size:20px; font-weight: bold; margin-bottom: -10px;"><?= $biller->cf1;?></h1>
                                    <hr style="width:70%; height:1px; background-color:black; display: block;">
                                    <div style="line-height: normal; margin-top: -5px;">
                                        <span style="font-size: 18px; letter-spacing: 3px;"><?= $biller->company;?> </span><br>
                                        <span style="font-family:'Khmer OS Muol Light'; font-weight: normal; font-size: 16px;"><?= $biller->invoice_footer; ?></span><br><br>
                                        <span style="font-family:'Khmer OS Muol Light'; font-weight: bold; font-size: 16px;">បង្កាន់ដៃទទួលប្រាក់ </span><br>
                                        <span style="font-weight: bold; font-size: 16px;">RECEIPT</span>
                                    </div>
                                </div>
                            </div>            
                        </td>
                        <td style="width: 15%;"></td>
                    </tr>
                </table>
            </div>
            <br>
            <table width="100%">
                <tr style="font-weight: bold;">
                    <td width="50%" style="font-family:'khmer os battambang';"> <p> <?= lang('name'); ?> : <?= ($customer->company != "-" ? $customer->company : $customer->name); ?></p></td>
                    
                    <td width="50%" style="font-family:'khmer os battambang';padding-left: 100px;"><p><?= "Invoice : " . $inv->reference_no; ?></p></td>
                </tr>
                <tr>
                    <td width="50%" style="font-family:'khmer os battambang';"> <p><?= lang('tel'); ?> : <?= $customer->phone; ?></p></td>
                    
                    <td  width="50%" style="font-family:'khmer os battambang';padding-left: 100px;"​> <p><?= lang('ref'); ?> : <?= $inv->reference_no; ?></p></td>
                </tr>
                
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td width="50%" style="font-family:'khmer os battambang';"> <p>ចំនួនប្រាក់បានទទួល / Amount Received</p></td>
                    <td width="50%"> : $ <?= $payment->amount; ?></td>
                </tr>
                <tr>
                    <td width="50%" style="font-family:'khmer os battambang';"> <p>ប្រាក់ខ្វះ (Balance due)</p>
                    <td width="50%"> : $ <?= $inv->grand_total - $inv->paid ?></td>
                </tr>
                
  
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td width="50%">
                    <span style="font-family:'khmer os battambang';"​><?= 'អ្នកបង់ប្រាក់ /'. lang("paid_by"); ?></span><br><br><br>
                        ..............................
                    </td>
                    <td width="50%">
                        Date : <?=date("d/m/Y", strtotime($inv->date)); ?><br>
                        <span style="font-family:'khmer os battambang';"​>អ្នកទូទាត់ / Cashier: <?= $created_by->first_name . ' ' . $created_by->last_name; ?></span><br><br><br>
                        ..............................
                    </td>
                </tr>
            </table>
            <div style="height:2px; border-top:2px solid #000000;margin-bottom: 10px;margin-bottom: 20px;">&nbsp;</div>
            <div style="text-align: center; margin-top: -12px; line-height: 70%; font-size: 11px;">
                <p><?php echo $warehouse->address; ?></p>
                <p>Tel: <?php echo $warehouse->phone; ?>, Email: <?php echo $warehouse->email; ?></p>
            </div>
        </div>
        
        <div class="model-footer no-printo">
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin: 15px;" onclick="myPrint('myfrm')">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<script>
    function myPrint(myfrm) {
        var printdata = document.getElementById(myfrm);
            newwin = window.open("");
            newwin.document.write(printdata.outerHTML);
            newwin.print();
            newwin.close();
        }
</script>