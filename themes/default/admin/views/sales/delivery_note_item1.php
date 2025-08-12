<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
        @media print {
            @page {
                margin: 0;
            }
            #printsmall{
                font-size: 10px; 
                margin-left: 0;
                font-weight: bold;
            }
            .modal-content { 
                page-break-after: auto; 
            }
        .modal-content{
            width:8cm;
            height:10cm;
            margin-top:-32px;
            overflow: hidden;
        }
        #table{
            width: 96%;
            height: auto;
            font-size:8px;
        }
        #table tr td{
            padding:3px 5px;
            font-size:9px;
            border:1px solid black !important;
        }
        #logo{
            width:68px !important;
            height:60px !important;
            margin-left:15px;
            /* margin-left:30px !important; */
        }
        #logo img{
             width:100% !important; 
             height:100% !important;
             object-fit: contain !important;
        }
        #comp_n h1{
            font-size:14px;
            font-weight:bold;
            margin-left:50px;
            padding-top:15px;
        }
        #recompense h3{
            font-size:10px;
        }
        #recompense p{
            font-size:8.95px;
            margin-top:-6px;
        }
        .text{
            font-size:8px;
            margin-right:19px;
        }
        .info{
            font-size:8.95px;
            margin-top:-40px;
        }
        .barcod{
            margin-top:-40px;
        }
    }
    </style>
    <style>
        .sty tr td{
            padding:7px;
            border:1px solid black;
        }
        .sty{
            width: 100%; 
            height: auto;
        }
        .qrimg{
            width:50px;
            height:50px;
            margin:5px 0;
            object-fit: contain;
        }
        .logo{
            width:220px;
            height:110px;
            margin-left:50px;
        }
        .logo img{
             width:100%; 
             height:100%;
             object-fit: contain;
        }
        .recompense h3{
            font-size:15px;
        }
        .well11{
            padding:0 5px;
        }
      
    </style>
<div class="modal-dialog modal-lg no-modal-header print top" id="printsmall">
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
                <br>
                    <?php if ($logo) { ?>
                        <div style="font-size:15px;" class="text-center">
                            <div class="logo" id="logo">
                                <img src="<?= site_url() . 'assets/uploads/logos/' . $biller->logo; ?> "
                                    alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                            </div>
                                <div style="margin-top:-80px;" id="comp_n">
                                    <h1><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h1>
                                </div>
                        </div>
                    <?php } ?>
                            
                    <div  class="well11 " style="margin-top:40px; display: block;">
                        <div class="row bold">
                            <div class="col-xs-6 order_barcodes info">
                                    <?php 
                                        $shipping= $sales->shipping;
                                        $grand_total=$sales->grand_total;
                                        $total= $grand_total-$shipping ;
                                    ?>
                            
                                    <ul style="list-style-type:none;">
                                        <li>លេដកូដផ្ញើ : <?= $delivery->do_reference_no; ?> </li>
                                        <li>ថ្ងៃ ខែ ផ្ញើ : <?= $this->bpas->hrld($delivery->date); ?> </li>
                                    </ul>
                                
                            </div>
              
                            <!-- <div class="barcod"> -->
                                <!-- <div style="padding-right:35px;" class="col-xs-6 text-right order_barcodes barcod">
                                    <?= $this->bpas->qrcode_note('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                                </div>
                                <p style="float:right;" class="text">ស្តេននៅទីនេះដើម្បីស្វែងរកអីវ៉ាន</p> -->
                            <!-- </div> -->
                            
                        </div>
                        <?php 
                            $data1=html_entity_decode($delivery->address);
                            $data2=str_ireplace('<p>',' ',$data1);
                            $data3=str_ireplace('</p>',' ',$data2); 
                        ?>
                        <div class="table-responsive">
                            <table  class=" table-bordered table-hover table-striped print sty" id="table">
                                    <tr>
                                        <td>លេខផ្ញើ</td>
                                        <td><?php echo $delivery->send_number;?></td>
                                        <td>លេខទទួល</td>
                                        <td><?= !empty($delivery->received_by) ? $delivery->received_by : "N/A" ?></td>
                                    </tr>
                                    <tr>
                                        <td>ផ្ញើពី</td>
                                        <td><?php echo  $biller->company ?></td>
                                        <td>ទៅកាន់</td>
                                        <td><?php echo ($data3)?></td>
                                    </tr>
                                    <tr>
                                        <td>តម្លៃអីវ៉ាន</td>
                                        <td>$<?php echo $this->bpas->formatMoney($total) ?></td>
                                        <td>ចំនួន</td>
                                        <td><?php echo $sales->total_items; ?> Items</td>
                                    </tr>
                                    <tr>
                                        <td>តម្លៃផ្ញើ</td>
                                        <td colspan="3">$<?php echo $this->bpas->formatMoney($shipping);?></td>
                                    </tr>
                                    <tr>
                                        <td>តម្លៃសរុប</td>
                                        <td colspan="3"> $<?= $this->bpas->formatMoney($total+$shipping);?></td>
                                    </tr>
                                    <tr>
                                        <?php 
                                            $paid = ($sales->payment_status == "paid" ) ? "<i style='font-size:15px;'class='fa fa-check-circle' aria-hidden='true'></i>" : ''; 
                                            $notpaid = ($sales->payment_status != "paid" ) ? "<i style='font-size:15px;'class='fa fa-check-circle' aria-hidden='true'></i>" : ''; 
                                        echo "<td colspan='2' style='font-weight: bold'> $notpaid &nbsp; មិនទាន់បង់ប្រាក់</td>
                                              <td colspan='2' style='font-weight: bold'> $paid &nbsp; បង់ប្រាក់រួច </td>";
                                        ?>
                                    </tr>
                            </table>
                        </div>
                
                        <div style="padding:20px 0px;" class="row recompense" id="recompense">
                            <div class="col-xs-12">
                                    <h3><u>ចំណាំ៖</u></h3>
                                    <p>- សូមពិនិត្យមើលអីវ៉ាន់ មុនទទួលយក</p>
                                    <p>- ករណីខុសទំហំ ឫ ពណ៍អាចដូរវិញបានក្នុងកំឡុងពេល 3 ថ្ងៃ</p>
                                    <p>- ករណីផ្លាស់ប្តូរអីវ៉ាន់ ត្រូវចំណាយថ្លៃដឹកដោយខ្លួនឯង</p>
                                    <p>- Facebook SBCsolution </p>
                                    <p>Tel: 01205124</p>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>


