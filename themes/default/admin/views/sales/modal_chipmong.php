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
            font-size: 11px;
            padding: 5px;
        }
        .table_pro tr > th, .table_pro tr > td {
            border: 1px solid #000 !important;
            font-size: 11px;
        }
        .header_th td{
            font-size: 12px;
            font-family: 'Khmer Mool1';
        }
        .table_top tr > th, .table_top tr > td {
            border: 1px solid #000 !important;
            font-size: 11px;
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
                font-size: 10px;
            }
            .modal-body{
                margin-right: 20px;
            }
            .note_ { border: 1px solid black !important; }
            thead { display: table-header-group; }
        }

        @font-face {
            font-family: 'KhmerOS_muollight';
            src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
        }
    </style>
</head>
<body>
    <div class="modal-dialog modal-lg no-modal-header" style="font-size: 11px; margin-top: -15px !important;">
        <div class="modal-content">    
            <div class="modal-body">
                <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;"><i class="fa fa-2x">&times;</i></button>
                <?php if ($chipmong->push == 0) { ?>
                <div class="btn-group">
                    <a href="#" id="push" class="tip btn btn-info">
                        <i class="fa fa-upload"></i> Push
                    </a>
                </div>
                <?php } ?>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();"><i class="fa fa-print"></i> <?= lang('print'); ?></button>
                <table class="table table-bordered table-hover table-striped dataTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th><?= lang('date');?></th>
                            <th><?= lang('biller');?></th>
                            <th><?= lang('reference');?></th>
                            <th><?= lang('grand_total');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i=1; 
                        foreach($rows as $row){ ?>
                        <tr>
                            <td><?=$i;?></td>
                            <td><?= $row->date;?></td>
                            <td><?= $row->biller;?></td>
                            <td><?= $row->reference_no;?></td>
                            <td><?= $row->grand_total;?></td>
                        </tr>
                        <?php
                        $i++;
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
 <div class="alert alert-success" role="alert">
    <strong>Successful push data to chipmong.</strong>
</div>
<div class="alert alert-danger" role="alert">
    <strong>Push data to chipmong fail!</strong>
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
        $('.alert-success').alert('close');
        $('.alert-danger').alert('close');
        $("#push").click(function(){
            $.ajax({
                url: site.base_url + "chipmong/insert/<?= $id ?>",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    window.location.replace(site.base_url + 'chipmong');
                }, error: function(jqXHR, textStatus, errorThrown){
                    // console.log("Error!: " + textStatus);
                }, complete: function(xhr, statusText){
                    // console.log(xhr.status + " " + statusText);
                }
            });
            return false;
        });          
    });
</script>   