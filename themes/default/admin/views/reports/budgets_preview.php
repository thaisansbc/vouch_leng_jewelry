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
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("expenses_budget_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="font-size: 18px;font-weight: bold;"><?= lang('expenses_budget_report'); ?></div></center>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th class="col-xs-2"><?= lang('date'); ?></th>
                        <th class="col-xs-1"><?= lang('reference'); ?></th>
                        <th class="col-xs-2"><?= lang('created_by'); ?></th>
                        <th class="col-xs-1"><?= lang('title'); ?></th>
                        <th class="col-xs-2" style="text-align: right !important;"><?= lang('amount'); ?></th>
                        <th class="col-xs-2" style="text-align: right !important;"><?= lang('expenses'); ?></th>
                        <th class="col-xs-2"><?= lang('note'); ?></th>
                    </tr>
                    <?php 
                    if (!empty($rows)) {
                        $total_amount = 0;
                        $total_expense = 0;
                        $i=1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $this->bpas->hrld($row->date); ?> </td>
                                <td><?= $row->reference; ?></td> 
                                <td><?= $row->user; ?></td> 
                                <td><?= $row->title; ?></td>                           
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->amount); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->expenses); ?></td>
                                <td><?= $row->note; ?></td>
                            </tr>
                            <?php 
                            $total_amount  += $row->amount;
                            $total_expense += $row->expenses;
                            $i++;
                        }
                    } ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="text-align: right;"><strong><?= lang('total') ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_amount); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total_expense); ?></strong></td>
                            <td></td>
                        </tr>
                </table>
            </div>         
            </div>
        </div>
    </div>
</div>