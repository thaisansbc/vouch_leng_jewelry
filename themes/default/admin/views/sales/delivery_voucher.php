<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
        @media print {
            body {
                zoom: 90%;
            }
            @page {
                margin: 0mm 0mm 0mm 6.3mm;
            }
            .namebiller{
                font-size:13px !important;
                font-size:9px;
                font-weight:bold;
            }
            .v_biller{
                margin-top:10px !important;
                margin-top:-1px !important;
            }
            .modal-content { 
                page-break-after: auto; 
            }
            #recompense{
            margin-left:-10px !important;

            }
            #recompense h3{
                font-size:9px !important;
            }
        
            #recompense p{
                font-size:8px;
                margin-top:-6px;
            }
            .modal-content{
                width:80mm;
                height:100mm;
                margin-top:-22px;
                overflow: hidden;
            }
            .main_title{
                font-size:10px !important;
            }
            #table{
                width: 100%;
                height: auto;
                font-size:8px;
            }
            #table tr td{
                padding:3px 5px;
                font-size:10px;
                border:1px solid black !important;
            }
            #logo{
                width:67px !important;
                height:60px !important;
                margin-left:15px;
                /* margin-left:30px !important; */
            }
            #logo img{
                width:100% !important; 
                height:100% !important;
                margin-top:1px;
                object-fit: contain !important;
            }

            .text{
                font-size:8px;
                margin-right:19px;
            }
            .info{
                font-size:9px !important;
                margin-top:10px !important;
            }
            .main_title{
                font-size:15px;
            }
        }
    </style>
    <style>

        /* .modal-body {
            padding: 30px;
        } */
   
        .row {
            margin-right: -5px;
            /* margin-left: -5px; */
        }
        .sty tr td{
            padding:7px;
            border:1px solid black;
        }
        .sty{
            width: 100%; 
            height: auto;
        }
        .namebiller{
            font-size:20px;
        }
        .v_biller{
            margin-top:-20px;
        }
        .logo{
            width:220px;
            /* height:110px; */
            margin-left:30px;
        }
        .logo img{
             width:80%; 
             /* height:100%; */
             margin-top:-20px;
             object-fit: contain;
        }
        .main_title p{
            margin-top:-32px;
            margin-left:-35px;
        }
        .info{
            font-size:15px;
            margin-top:15px;
        }
        .main_title{
                font-size:16px;
            }      
    </style>
<div class="modal-dialog modal-lg no-modal-header print top">
    <div class="modal-content">
        <div class="modal-body print">
            <div class="border_tage" style="margin: 0px; padding: 5px; margin-left: -15px; 
                margin-right: -5px; margin-top: -15px; margin-bottom: -5px">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>

                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>

                <div class="clearfix"></div> 
                <div class="row">
                    <?php if ($logo) { ?>
                        <div style="font-size:15px;" class="text-center col-xs-5">
                            <div class="logo" id="logo">
                                    <img src="<?= site_url() . 'assets/uploads/logos/' . $biller->logo; ?> "
                                    alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-xs-7 v_biller">
                        <h2 class="namebiller"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    </div>
                </div>

                <?php 
                    $time        = $delivery->time ? $delivery->time : '';
                    $tim         = $delivery->time_out_id;
                    $date        = date("d-m-Y", strtotime($delivery->date));
                    $shipping    = $sales->shipping;
                    $grand_total = $sales->grand_total;
                    $total       = $grand_total-$shipping;
                ?>
            
                <div class="row bold">
                    <div class="col-xs-4 order_barcodes info">
                        <ul style="list-style-type:none;">
                            <li>Time : <?= $time ?> </li>
                            <li>Date : <?= $date ?> </li>
                        </ul>
                    </div>
                    <div class="col-xs-4 order_barcodes text-center" style="margin-top:-10px !important;">
                        <ul style="list-style-type:none;">
                            <li style="font-family:Khmer OS Muol Light;" class="main_title">បណ្ណ័ផ្ញើអីវ៉ាន់</li>
                            <!-- <li style="font-size:9px !important;">Goods Delivery Voucher</li> -->
                        </ul>
                    </div>
            
                    <div class="col-xs-4 order_barcodes info text-right">
                        <ul style="list-style-type:none;">
                            <li>DRef: <?= $delivery->do_reference_no ?> </li>
                            <li>SRef: <?= $delivery->sale_reference_no ?> </li>
                        </ul>
                    </div>
                </div>
            
                <div class="table-responsive">
                    <table  class=" table-bordered table-hover table-striped print sty" id="table">
                            <tr>
                                <td style='font-weight: bold;font-size:15px;'>ផ្ញើ:&nbsp;&nbsp;<?= $delivery->sender; ?></td>
                                <td style='font-weight: bold;font-size:15px;'>ទទួល:&nbsp;&nbsp;<?= $delivery->received_by; ?></td> 
                            </tr>
                            <tr>
                                <td style="font-size:13px">អីវ៉ាន់:&nbsp;&nbsp;
                                    <?php   
                                        foreach($products as $product){
                                            echo $product->product_name.' '. (int)($product->quantity) .' '.$product->unit_name.'<br>';
                                        }
                                    ?>
                                </td>
                                <td style="font-size:13px" >ទៅ:&nbsp;&nbsp;<?= strip_tags($delivery->to); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;font-size:13px;">តម្លៃអីវ៉ាន់:&nbsp;&nbsp; <span>$<?= number_format(($sales->total_price) ,2);?></span></td>
                                <td style="font-weight: bold;font-size:13px;">តម្លៃដឹក:&nbsp;&nbsp;<span>៛<?= $shipping ;?></span></td>
                            </tr>
                            <tr>
                                <?php 
                                    $paid = ($sales->payment_status == "paid" ) ? "<i style='font-size:20px;'class='fa fa-check-circle' aria-hidden='true'></i>" : ''; 
                                    $notpaid = ($sales->payment_status != "paid" ) ? "<i style='font-size:20px;'class='fa fa-check-circle' aria-hidden='true'></i>" : ''; 
                                echo "<td><h6 style='font-weight: bold'> $notpaid &nbsp; មិនទាន់បង់ប្រាក់</h6></td>
                                <td><h6 style='font-weight: bold'>  $paid &nbsp; បង់ប្រាក់រួច</h6></td>";
                                ?>
                            </tr>
                    </table>
                </div>
                <div  class="row recompense " id="recompense">
                    <div class="col-xs-12">
                            <h3><u>ចំណាំ៖</u></h3>
                            <p>- ក្នុងករណីបាត់បង់ ក្រុមហ៊ុននឹងសង់ចំនួន 20 ដងនៃតម្លៃអីវ៉ាន់ដែលបានផ្ញើ</p>
                            <p>- ទទួលបញ្ញើដែលមានតម្លៃមិនលើសពី 250$ ផុតកំណត់ 3 ថ្ងៃមិនទទួលខុសត្រូវ មិនទទួលអីវ៉ាន់តាមផ្លូវ</p>
                            <p>- អ្នកមកទទួលយកអីវ៉ាន់សូមភ្ចាប់មកជាមួយលេខទូរសព្ទ័ដែលមាននៅក្នុងបណ្ណ័​បញ្ញើ</p>
                            <p>- ក្រុមហ៊ុនមិនទទួលអីវ៉ាន់់ខុសច្បាប់មានដូចជា ៖ សត្វព្រៃ ខ្នុរ ធុរេន គ្រឿងផ្ទុះ គ្រឿងញៀន អីវ៉ាន់គេចពន្ធ នឹងរបស់គ្រោះថ្នាក់ផ្សេងៗ(អ្នកដែលល្មើសបម្រាមត្រូវទទួលខុសត្រូវចំពោះមុខច្បាប់)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


