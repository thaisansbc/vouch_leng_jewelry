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
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("payments_received_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
            <center><div style="font-size: 18px;font-weight: bold;"><?= lang('payments_received_report'); ?></div></center>
            <?php if(isset($start_date)) { ?>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date); ?></div></center>
            <?php } elseif (isset($start)) { ?>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start).' To '.$this->bpas->fldc($end); ?></div></center>
            <?php } ?>
            <br>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('date'); ?></th>
                        <th><?= lang('payment_ref'); ?></th>
                        <th><?= lang('customer'); ?></th>
                        <th><?= lang('paid_by'); ?></th>
                        <th><?= lang('total_pay'); ?></th>
                        <th><?= lang('penalty'); ?></th>
                        <th><?= lang('discount'); ?></th>
                        <th><?= lang('grand_total'); ?></th>
                        <th><?= lang('type'); ?></th>
                    </tr>
                    <?php 
                    if (!empty($rows)) {
                        $i = 1;
                        $total_pay   = 0;
                        $penalty     = 0;
                        $discount    = 0;
                        $grand_total = 0;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $this->bpas->hrld($row->date); ?> </td>
                                <td><?= $row->payment_ref; ?></td> 
                                <td><?= $row->customer; ?></td>                           
                                <td><?= lang($row->paid_by); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->total_pay); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->penalty); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->discount); ?></td>
                                <td><?= $this->bpas->formatDecimal($row->grand_total); ?></td>
                                <td><?= $row->type; ?></td>
                            </tr>
                        <?php 
                            $total_pay   += $row->total_pay;
                            $penalty     += $row->penalty;
                            $discount    += $row->discount;
                            $grand_total += $row->grand_total;
                            $i++;
                        }
                    }?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-right"><strong><?= lang('total') ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($total_pay); ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($penalty); ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($discount); ?></strong></td>
                            <td><strong><?= $this->bpas->formatDecimal($grand_total); ?></strong></td>
                            <td></td>
                        </tr>
                </table>
            </div>         
            </div>
        </div>
    </div>
</div>