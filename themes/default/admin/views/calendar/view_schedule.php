<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<head>
    <meta charset="utf-8">
    <style>
        .Header{
            margin-top:5px;
            background-color:#4682B4;
            padding: 4px 6px;
            font-weight:bold;
            color:#ffffffff;
            border-radius:5px;
            font-size: 16px;
            position:absolute;
        }
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
        .qrimg { width: 50px !important; }
        
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
        .combo_height{
            height: 30px;
        }
        .combo_line{
            border-bottom: 1px solid #000000;
        }
    </style>
</head>
<body>
<div class="modal-dialog modal-lg no-modal-header" style="font-size: 11px; margin-top: 10px !important;">
    <div class="modal-content">    
        <div class="modal-body"  style="margin-top: -10px;">
            <div class="Header no-print"><?= lang('view_schedule')?></div>
            <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;">
                <i class="fa fa-2x">&times;</i>
            </button>
        </div>
        <div class="modal-body" style="margin-top: 20px;margin-bottom: 20px;"> 
       
        <div class="row">
            <div class="col-sm-6">
                  <div class="col-sm-12">
                    <h2>Schedule Information</h2>
                  </div>
                  <div class="col-sm-12"><span style="font-size:16px;"><?= $schedule->title?></span>
            <?php 
           
                if($schedule->status=='pending'){
                    ?>
                    <span style="font-size:14px;background-color:#FFA500;color:#ffffff;padding:4px 6px;border-radius:5px;font-weight:bold; text-transform: uppercase;"><?= $schedule->status?></span>
                    <?php
                 }
                 if($schedule->status=='expired'){
                    ?>
                    <span style="font-size:14px;background-color:#FF0000;color:#ffffff;padding:4px 6px;border-radius:5px;font-weight:bold; text-transform: uppercase;"><?= $schedule->status?></span>
                    <?php
                 }
           ?>
         </div>
                  <div class="col-sm-12" style="padding:10px 0px;">
                    <div class="col-xs-5">
                    <?php if(!empty($schedule->photo)){
                            ?><img id="pr-image" src="<?= base_url() ?>assets/uploads/thumbs/<?= $schedule->photo ?>" alt="<?= $schedule->photo ?>" class="img-responsive img-thumbnail"/><?php
                    }else{
                        ?><img id="pr-image" src="<?= base_url() ?>assets/uploads/thumbs/no_image.png" class="img-responsive img-thumbnail"/><?php
                    }
                    ?>
                   </div>
                  </div>
            </div>
            <div class="col-sm-6">
                  <div class="col-sm-12">
                    <h2>Schedule Date</h2>
                  </div>
                  <div class="col-sm-12">
                      <span style="font-size:14px;">Date :</span> <span style="font-size:14px;padding:0px 4px"> <?=$this->bpas->hrld($schedule->start) ?> <?=date('A', strtotime($this->bpas->hrld($schedule->start)))  ?> <i class="fa-solid fa-arrow-right"></i> <?=$this->bpas->hrld($schedule->end) ?> <?=date('A', strtotime($this->bpas->hrld($schedule->end)))  ?>
                      </span>
                    </div>
                    <div class="col-sm-12">
                      <span style="font-size:14px;">Created date : </span> <span style="font-size:14px;padding:0px 4px"> <?=$this->bpas->hrld($schedule->created_date) ?> <?=date('A', strtotime($this->bpas->hrld($schedule->created_date)))  ?>
                      </span>
                    </div>
                   
            </div>
        </div>
        <div class="row">
        <div class="col-sm-12">
              <div class="drop-box" style="width:100%;border:1px solid #C0C0C0;border-radius:5px;padding:4px 5px; font-size:14px;">
              Event Description
               <div class="show-data"  style="width:100%;border-top:1px solid #C0C0C0; font-size:14px;padding:10px 22px;">
                     <?= $this->bpas->decode_html($schedule->description);?>
               </div>
            
              </div>
              
        </div>
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
        .modal-dialog{
            <?= ($print==1) ? 'display:none !important;' : ''; ?>
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
<script type="text/javascript">

    $(document).ready(function() {
        $('.show-data').hide();
        $('.drop-box').click(function(){
            $('.show-data').slideToggle("slow");
        });



        $('.show-data1').hide();
        $('.drop-box1').click(function(){
            $('.show-data1').slideToggle("slow");
        });
        // $('.tip').tooltip();            
    });
</script>   