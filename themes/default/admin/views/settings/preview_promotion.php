<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css" media="all">
    body{
        -webkit-print-color-adjust:exact;
        }
    @font-face {
        font-family: 'KhmerOS_muollight';
        src: url('<?= $assets ?>fonts/KhmerOS_muollight.ttf') format('truetype');
    }
    table {
        font-size: 12px !important;
    }
    @media print {
        table {
            font-size: 12px !important;
        }
    }
    @page { margin-top: 0; }
    
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("Promotion Detail By Date"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content" style="padding-top: 5px; padding-left: 5px;">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="padding-top: 15px; font-size: 25px;color:#FF3E96!important;font-family:KhmerOS_muollight !important;">
                  <?= isset($biller->name) ? $biller->name : ''; ?> </div></center>
                  <center><div style="font-size: 16px;font-weight: bold; color:#FF3E96 !important;">
                    <?= lang('លំអិតនៃការបញ្ចុះតម្លៃតាមកាលបរិច្ឆេទ'); ?></div></center>
                <center><div style="font-size: 16px;font-weight: bold;color:#FF3E96 !important;">
                    <?= lang('Promotion Detail By Date'); ?></div></center>
                    <br>
                <table  class="table table-responsive table-hover">
                    <thead>
                        <tr>  
                            <th style="background-color:#3F708A !important;"><?= lang('No'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('product_code'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('products_name'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('category'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('quantity'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('price'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('discount'); ?></th>
                            <th style="background-color:#3F708A !important;"><?= lang('amount'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            if (!empty($rows)) {
                                $i = 1;
                                foreach ($rows as $row) { 
                                    $new_string =  mb_strimwidth($row->pname, 0, 45, ".....");  
                                    $amount = explode("__",$row->amount);
                                    // var_dump($this->bpas->formatDecimal(($amount[0] * floatval($amount[1]))/100));exit;
                                    $total_amount = !empty($row->amount) ? ( $this->bpas->formatDecimal($amount[0]) - $this->bpas->formatDecimal(($amount[0] * floatval($amount[1]))/100)) : "0.00";
                                    ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= $row->pcode ?></td>
                                        <td><?= $new_string  ?></td>
                                        <td><?= $row->category_name  ?></td>
                                        <td><?= !empty($row->pqty) ? $this->bpas->formatDecimal($row->pqty) : "0.00"; ?></td>
                                        <td>$<?= !empty($row->pprice) ? $this->bpas->formatDecimal($row->pprice) : "0.00"; ?></td>
                                        <td><?= $row->discount; ?></td>
                                        <td>$<?= $this->bpas->formatDecimal($total_amount); ?></td>
                                    </tr>
                                <?php 
                
                                $i++;
                                }
                            }?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<footer style="display:none;"><div id="pageFooter">Page </div></footer>