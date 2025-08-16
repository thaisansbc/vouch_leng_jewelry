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
        #content{
            width:7.5cm;
            height:10cm;
            border:1px solid grey;
        }
        #logo{
            width:90px;
            height:50px;
            float: left;
        }
        #logo img{
            width:100%;
            height:100%;
            object-fit: contain;
        }
        #com_name{
            text-align:center;
        }
        #com_name h2{
            font-size:12px;
            /* padding-right:30px; */
            /* float:right; */
            margin-top:15px;
            font-weight:bold;
          }
        #info{
            margin-left:-100px;
            margin-top:10px;
        }
        #info ul li{
            font-size:9px;
        }
        #bar_code p{
        font-size:7.1px;
        float: left;
        margin-left:10px;
        }
        #well{
            margin-top:-15px;
        }
        #table{
            width:100%;
            height:auto;
        }
    
        #table tr td{
            padding:4px 3px;
            font-weight: bold;
            border:1px solid black !important;
            font-size: 9px;
        }
        #recompense{
            margin-top:-20px;
        }
        #recompense h4{
            font-size:9px;
        }
        #recompense p{
            font-size:8px;
            margin-top:-6px;
        }
        .top{
            margin-top:-8px;
        }
        .well11{
            padding:0 10px;
        }
        .qrimg{
            width:50px !important;
            object-fit: contain !important;
            margin-left:25px !important;
            padding-bottom:5px !important;
        }
    }
</style>

<style>
    .btn-print{
        width:100%;
        padding:5px;
        color:white;
        margin:15px 0;
        cursor:pointer;
    }
    .border{
        width:15cm;
        height:15cm;
        background-color:#B1C2E0;
        margin:auto;
    }
    .btn-print:hover{
        opacity:0.9;
    }
    .content{
        width:15cm;
        height:17cm;
        border:1px solid grey;
        margin:auto;
    }
    .logo{
        width:180px;
        height:90px;
    }
    .logo img{
        width:100%;
        height:100%;
        object-fit: contain;
    }
    .com_name{
        /* background-color:red; */
        text-align:center;
    }
    .com_name h2{
        font-size:22px;
        /* margin-left:25px; */
        margin-top:35px;
        font-weight:bold;
    }
    .table tr td{
        padding:5px 7px;
        color:black;
    }   
    .pic_barcode img{
        width:50px !important;
        height: auto !important;
        padding-bottom:5px !important;
        object-fit: contain !important;
        margin-left:30px !important;
    }
    .bar_code p{
        margin-left:-28px;
    }
   
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= lang('print_sticker_delivery') ?></h2>
    </div>
    <div  class="container">
         <button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print btn-print"><i class="icon fa fa-print"></i>Print</button>
    </div>
 <?php foreach($deliveries as $delivery) { ?>
    <div class="content" id="content">
        <div class="row">
            <div class="col-sm-4">
                <div class="logo" id="logo">
                    <img src="<?= site_url() . 'assets/uploads/logos/' . $delivery['biller']->logo; ?> " alt="<?= $delivery['biller']->company && $delivery['biller']->company != '-' ? $delivery['biller']->company : $delivery['biller']->name; ?>">
                </div>
            </div>
            <div class="col-sm-8">
                <div class="com_name" id="com_name">
                     <h2><?= $delivery['biller']->company && $delivery['biller']->company != '-' ? $delivery['biller']->company : $delivery['biller']->name; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="well11" id="well">
            <div class="row bold">
                <div class="col-xs-6 order_barcodes info" id="info">
                        <?php 
                            $shipping= $delivery['sales']->shipping;
                            $grand_total=$delivery['sales']->grand_total;
                            $total= $grand_total-$shipping ;
                        ?>
                        <ul style="list-style-type:none;">
                            <li>លេដកូដផ្ញើរ :  <?= $delivery['delivery']->do_reference_no; ?> </li>
                            <li>តម្លៃអីវ៉ាន : <?= number_format($total,2) ?> $</li>
                            <li>ចំនួន : <?= $delivery['sales']->total_items; ?> </li>
                            <li>ថ្ងៃ ខែ ផ្ញើរ : <?= $this->bpas->hrld($delivery['delivery']->date); ?></li>
                        </ul>
                </div>
                <div class="col-xs-6 order_barcodes bar_code" id="bar_code">
                        <div class="pic_barcode" id="pic_barcode"><?= $this->bpas->qrcode_note('link', urlencode(admin_url('sales/view/' . $delivery['inv']->id)), 2); ?></div>
                        <p>ស្កេននៅទីនេះដើម្បីស្វែងរកអីវ៉ាន</p>
                </div>
            </div>
            <div>
                <table  class=" table-bordered table-hover table-striped print table" id="table" >
                    <tr>
                        <td>ប្រភេទអីវ៉ាន</td>
                        <td colspan="3">មិនធានាបែកបាក់ចំនួន : </td>
                    </tr>
                    <tr>
                        <td>លេខអ្នកផ្ញើរ</td>
                        <td><?php echo $delivery['customer']->phone;?></td>
                        <td>លេខអ្នកទទួល</td>
                        <td><?php echo $delivery['delivery']->phone;?></td>
                    </tr>
                    <tr>
                        <td>ផ្ញើរពីសាខា</td>
                        <td><?php echo $delivery['delivery']->address?></td>
                        <td>ទិសដៅ</td>
                        <td><?php echo $delivery['delivery']->note?></td>
                    </tr>
                    <tr>
                        <td>តម្លៃផ្ញើរ</td>
                        <td><?php echo $shipping;?> $</td>
                        <td>តម្លៃដឹកដល់ផ្ទះ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>សរុបត្រូវបង់ប្រាក់</td>
                        <td colspan="3"><?= number_format(($total+$shipping) ,2);?> $</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="top"></div>
        <div>
            <div class="col-xs-12 bold" id="recompense">
                    <h4 style="font-weight: bold"><u>លក្ខខណ្ខធ្វើសំណង៖</u></h4>
                    <p>- អីវ៉ានដែលងាយខូចគុណភាព ក្រុមហុនមិនទទួលខុសត្រូវឡើយ ។</p>
                    <p>- ករណិបាត់រឺខូចខាត់ក្រុមហុននឹងសង់ 20 ដងនៃតម្លៃផ្ញើរ តែមិនលើសពិចំនួនអីវ៉ានឡើយ។</p>
                    <p>- Facebook SBCsolution </p>
                    <p>Tel: 01205124</p>
            </div>
        </div>
        <p style="page-break-after: always;">&nbsp;</p>
    </div>
<?php } ?>

    <div  class="container">
             <button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print btn-print"><i class="icon fa fa-print"></i>Print</button>
    </div>
</div>
