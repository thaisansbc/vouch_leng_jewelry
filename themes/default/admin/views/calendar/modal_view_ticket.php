<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<head>
    <meta charset="utf-8">
    <style>
        .container {
            width: 29.7cm;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }
        .table_pro {
            width: 100%;
        }
        .table_pro tr > th {
            text-align: center !important;
            font-size: 12px;
            padding: 5px;
        }
        .table_pro tr > th, .table_pro tr > td {
            border: 1px solid #000 !important;
            font-size: 12px;
        }
        .header_th td{
            font-size: 12px;
            font-family: 'Khmer Mool1';
        }
        .table_top tr > th, .table_top tr > td {
            border: 1px solid #000 !important;
            font-size: 12px;
            text-align: center;
        }
        .well { padding-bottom: 0px; }
        .qrimg { width: 100px !important; }
        .bcimg{
            width: 150px !important; 
        }
        /*.bcimg{
            width: 150px !important; 
            -moz-transform: rotate(90deg);
            -webkit-transform: rotate(90deg);
            -o-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            transform: rotate(90deg);
        }*/
        @media print {
            .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
                border-top: 1px solid #000000 !important;
            }
            @page {
                margin: 0.15in 0 1.68in 0;
            }
            .header_th td{
                font-size: 12px;
            }
            .modal-body{
                margin-right: 20px;
            }
            thead { display: table-header-group; }
            .note_ { border: 1px solid black !important; }

            /* .modal-dialog { margin-left: 10px; width: 100% !important; } */
            .modal-content { page-break-after: auto; }
            ::-webkit-scrollbar {
                display: none; 
            }
            body {
                scrollbar-width: none; 
            }
        } 
        @font-face {
            font-family: 'KhmerOS_muollight';
            src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
        }
    </style>
</head>
<body>
<div class="modal-dialog modal-lg no-modal-header" style="font-size: 11px; margin-top: -15px !important;">
    <div class="modal-content" style="background: gray;">    
        <div class="modal-body">
            <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" id="image">
                <i class="fa-regular fa fa-upload"></i> <?= lang('download'); ?>
            </button>
            <table border="0" cellspacing="0" style="width:600;height: 200px;" class="download">
                <td style="width:500px;border-right:2px dashed #fff;">
                   <div style="width:100%;height: 20px;background: #3F708A ;color: #fff;text-align: center; -webkit-border-top-left-radius: 10px;-moz-border-radius-topleft: 10px;border-top-left-radius: 10px; ">
                       <?= $this->Settings->site_name?>
                   </div>
                   <div style="width:100%;height: 160px;background: #fff;padding: 10px;">
                        <div style="font-size:24px">
                             <?= $event->title ?>
                        </div>
                        <table width="width:100%">
                            <td width="40%">
                                <?= $this->bpas->qrcode('link', urlencode(admin_url('products/view/'.$ticket->code)), 3); ?>
                                <div><?= $ticket->code;?></div>
                            </td>
                            <td style="padding-left: 10px;color:#3F708A;font-size:13px;vertical-align:top">
                                <div>Name: <?php if(!empty($customer)){echo $customer->name;} ?></div>
                                <div>Phone: <?php if(!empty($customer)){echo $customer->phone;} ?></div>
                                <div>Date: <?= date("d-m-Y", strtotime($schedule->start)) ?></div>
                                <div>Time: <?= date("h:i A", strtotime($schedule->start))?> - <?= date("h:i A", strtotime($schedule->end)) ?></div>

                            </td>
                        </table>
                        

                   </div>
                   <div style="width:100%;height: 20px;background: #3F708A;color: #fff;-webkit-border-bottom-left-radius: 10px;-moz-border-radius-bottomleft: 10px;border-bottom-left-radius: 10px;">
                       
                   </div>
                </td>
                <td style="width:80px;">
                    <div style="width:100%;height: 20px;background: #3F708A;color: #fff;padding-left: 10px;-webkit-border-top-right-radius: 10px;-moz-border-radius-topright: 10px;border-top-right-radius: 10px;">
                       &nbsp;
                    </div>
                   <div style="width:100%;height: 160px;padding: 10px;background: #fff;">
                        <br><br>
                        <div class="text-center">Date: <?= date("d-m-Y", strtotime($schedule->start)) ?></div>
                        <div class="text-center">Time: <?= date("h:i A", strtotime($schedule->start))?> - <?= date("h:i A", strtotime($schedule->end)) ?></div>
                        <img src="<?= admin_url('misc/barcode/'.$ticket->code.'/code128/24/0'); ?>" class="bcimg" />
                        <div  class="text-center"><?= $ticket->code;?></div>
                   </div>
                   <div style="width:100%;height: 20px;background: #3F708A;color: #fff;
                   -webkit-border-bottom-right-radius: 10px;-moz-border-radius-bottomright: 10px;border-bottom-right-radius: 10px;">
                       &nbsp;
                   </div>
                </td>
            </table>
       
        </div>
    </div>
</div>
</body>
<style>
    @media print{
        .no-print{
            display:none !important;
        }
        .tr_print{
            display:table-row !important;
        }
        .bg-text{
            display:block !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
    }
    .bg-text{
        opacity: 0.1;
        color:lightblack;
        font-size:60px;
        position:absolute;
        transform:rotate(300deg);
        -webkit-transform:rotate(300deg);
        display:none;
    }
</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.download'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>  