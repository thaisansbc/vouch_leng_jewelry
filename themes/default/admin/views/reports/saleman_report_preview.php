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
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("salemans_detail_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="font-size: 18px;font-weight: bold;"><?= lang('salemans_detail_report'); ?></div></center>
                <center><div style="font-size: 16px; margin-top: 10px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
                <?php if(isset($zone)){ ?>
                    <center><div style="font-size: 16px; margin-top: -8px;"><?= lang('zone') . ' : ' . $zone->zone_name; ?></div></center><br>
                <?php } ?>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th style="width: 50px;"><?php echo lang('date'); ?></th>
                        <th style="width: 50px;"><?php echo lang('project'); ?></th>
                        <th style="width: 30px;"><?php echo lang('reference_no'); ?></th>
                        <th style="width: 120px;"><?php echo lang('biller'); ?></th>
                        <th><?php echo lang('saleman'); ?></th>
                        <th style="width: 70px;"><?php echo lang('customer'); ?></th>
                        <th><?php echo lang('zone'); ?></th>
                        <th style="width: 20px;"><?php echo lang('sale'); ?></th>
                        <th><?php echo lang('product') . ' (Qty)';  ?></th>
                        <th style="width: 110px; text-align: right !important;"><?php echo lang('award_points'); ?></th>
                        <th style="width: 50px; text-align: right !important;"><?php echo lang('commission'); ?></th>
                        <th style="text-align: right !important;"><?php echo lang('grand_total'); ?></th>
                        <th style="text-align: right !important;"><?php echo lang('paid'); ?></th>
                        <th style="text-align: right !important;"><?php echo lang('balance'); ?></th>
                        <th style="text-align: center !important;"><?php echo lang('payment_status'); ?></th>
                    </tr>
                    <?php 
                    if (!empty($rows)) {
                        $i = 1;
                        $ap_total = 0; $cms = 0; $sQty = 0; $total = 0; $paid = 0; $balance = 0; $totalreal = 0;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= $this->bpas->hrld($row->date); ?> </td>
                                <td><?= $row->project_name; ?></td> 
                                <td><?= $row->reference_no; ?></td> 
                                <td><?= $row->biller; ?></td> 
                                <td><?= $row->saleman_by; ?></td> 
                                <td><?= $row->customer; ?></td> 
                                <td><?= $row->zone_id; ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->total_items); ?></td>
                                <td><?= $row->iname; ?></td> 
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->saleman_award_points); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->commission); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->grand_total); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->paid); ?></td>
                                <td style="text-align: right;"><?= $this->bpas->formatDecimal($row->grand_total - $row->paid); ?></td>
                                <td style="text-align: center !important;"><?= lang($row->payment_status); ?></td>
                            </tr>
                            <?php 

                            $sQty      += $row->total_items;
                            $ap_total  += $row->saleman_award_points;
                            $cms       += $row->commission;
                            $total     += $row->grand_total;
                            $paid      += $row->paid;
                            $balance   += ($row->grand_total - $row->paid);
                            $i++;
                        }
                    }?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="text-align: right;"><strong><?= lang('total') ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($sQty); ?></strong></td>
                            <td>&nbsp;</td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($ap_total); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($cms); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($total); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($paid); ?></strong></td>
                            <td style="text-align: right;"><strong><?= $this->bpas->formatDecimal($balance); ?></strong></td>
                            <td></td>
                        </tr>
                </table>
            </div>         
            </div>
        </div>
    </div>
</div>