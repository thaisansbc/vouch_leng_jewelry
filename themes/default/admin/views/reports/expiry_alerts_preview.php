<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css" media="all">
    table {
        font-size: 13px !important;
    }
    @media print {
        table {
            font-size: 13px !important;
        }
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("product_expiry_alerts_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="font-size: 18px;font-weight: bold;"><?= lang('product_expiry_alerts_report'); ?></div></center>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?php echo $this->lang->line('product_code'); ?></th>
                        <th><?php echo $this->lang->line('product_name'); ?></th>
                        <th style="text-align: right;"><?php echo $this->lang->line('quantity'); ?></th>
                        <th><?php echo $this->lang->line('warehouse'); ?></th>
                        <th><?php echo $this->lang->line('expiry_date'); ?></th>
                    </tr>
                    <?php 
                    if (!empty($rows)) {
                        $total = 0;
                        $i=1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $row->product_code; ?></td> 
                                <td><?= $row->product_name; ?></td> 
                                <td style="text-align: right;"><?= $row->quantity_balance; ?></td>                           
                                <td><?= $row->name; ?></td>
                                <td><?= $row->expiry; ?></td>
                            </tr>
                        <?php 
                        $total += $row->quantity_balance;
                        $i++;
                        }
                    }?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="text-align: right;"><strong><?= lang('total') ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total); ?></strong></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                </table>
            </div>         
            </div>
        </div>
    </div>
</div>