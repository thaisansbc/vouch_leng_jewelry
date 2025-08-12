<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        /*#myModal .modal-content {
            display: none !important;
        }*/

        .printfield {
            display: none !important;
        }

        #POSData {
            display: none !important;
        }

        .date1 {
            display: none !important;
        }

        .date2 {
            display: block !important;
        }

        /* .dtFilter {
            display: block !important;
        } */

        .table-responsive {
            display: block !important;
        }

        /* td .sorting_1 {
            display: compact !important;
        }*/

    }
</style>
<?php
$v = "";

?>

<div class="breadcrumb-header">

    <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('pos_sales');?></h2>
    </h2>

   
    </div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
          
              
       
            <div class="table-responsive">
                <table id="POSData" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check" />
                            </th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('reference_no'); ?></th>
                            <th><?= lang('biller'); ?></th>
                            <th><?= lang('customer'); ?></th>
                            <th><?= lang('driver'); ?></th>
                            
                            <th class="printfield" style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        echo $datas;
                        ?>
                    </tbody>
                </table>
                <div class="row"> 
                    <div class="col-md-6 text-left">
                        <?php //echo $showing;?>
                    </div>
                    <div class="col-md-6  text-right">
                        <div class="dataTables_paginate paging_bootstrap">
                            <?= $pagination; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {
?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php
} ?>
<script>
    
    $(document).ready(function() {
        function balance(x) {
            if (!x) {
                return 0.00;
            }
            var b = x.split('__');
            var total = formatNumber(parseFloat(b[0]));
            var rounding = formatNumber(parseFloat(b[1]));
            var paid = formatNumber(parseFloat(b[2]));
            // alert(total + rounding - paid);
            return currencyFormat(total + rounding - paid);
        }
        $(document).on('click', '#view_multi_invoices', function(e) {
                e.preventDefault();
                var arrItems = [];
                var k = 0;
                $('.checkbox').each(function(i){
                    if($(this).is(":checked")){
                        if(this.value != "" && this.value != "on" && this.value != "null"){
                            arrItems[k] = $(this).val();
                            k++;
                        } 
                    }
                });
                window.location.replace('<?= site_url("admin/pos/view_multi_invoices");?>?data=' + arrItems + '');
        });
        
    });
</script>