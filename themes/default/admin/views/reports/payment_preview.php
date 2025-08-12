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
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("payments_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="font-size: 18px;font-weight: bold;"><?= lang('payments_report'); ?></div></center>
                <center><div style="font-size: 16px;">
                    <?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('date'); ?></th>
                        <th><?= lang('payment_ref'); ?></th>
                        <th><?= lang('sale_ref'); ?></th>
                        <th><?= lang('customer'); ?></th>
                        <th><?= lang('paid_by'); ?></th>
                        <th><?= lang('amount'); ?></th>
                        <th><?= lang('status'); ?></th>
                        <th><?= lang('type'); ?></th>
                    </tr>
                    
                    <?php 
                    if (!empty($rows)) {
                        $total = 0;
                        $total_penalty = 0;
                        $tamountpaid = 0;
                        $i=1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $this->bpas->hrld($row->date); ?> </td>
                                <td><?= $row->reference_no; ?></td> 
                                <td><?= $row->sale_ref; ?></td> 
                                <td><?= $row->customer; ?></td>                           
                                <td><?= $row->paid_by; ?></td>
                                <td><?= $this->bpas->formatDecimal($row->amount); ?></td>
                                <!-- <td><?= $this->bpas->formatDecimal($row->penalty); ?></td> -->
                                <td><?= lang($row->penalty); ?></td>
                                <td><?= $row->type; ?></td>
                            </tr>
                        <?php 
                        $total += $row->amount;
                        // $total_penalty += $row->penalty;
                        $i++;
                        }
                    }?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-right"><strong><?= lang('total') ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($total); ?></strong></td>
                            <td class="text-right"><strong><?= lang('status') ?></strong></td>
                            <!-- <td><strong><?= $this->bpas->formatDecimal($total_penalty); ?></strong></td> -->
                            <td></td>
                        </tr>
                </table>
            </div>         
            </div>
        </div>
    </div>
</div>