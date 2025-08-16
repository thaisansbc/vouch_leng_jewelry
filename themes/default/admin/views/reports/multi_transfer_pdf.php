<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .qrimg {
        width: 60px;
        margin-right: 20px;
    }
    .bcimg {
        width: 80px;
    }

    .tbTransfer {
        width: 100%;
        margin-top: 30px;
    }
    .tbTransfer tr > th {
        text-align: center !important;
        font-size: 12px;
        padding: 5px;
    }
    .tbTransfer tr > th, .tbTransfer tr > td {
        border: 1px solid #000 !important;
    }
    .tbTransfer tr > td {
        height: 30px;
    }

    .divTable {
        display: table;
        width: auto;
    }
    .divRow {
       display: table-row;
       font-size: 12px;
       width: auto;
       margin-bottom: 5px;
    }   
    .divCell {
        float: left;
        display :table-column;
        width: 35%;
    }
    .divCell_ {
        float: left;
        display :table-column;
        width: 8%;
    }
</style>
        <div class="row">
            <div class="col-xs-12">
                <?php if ($logo) { ?>
                    <img style="margin-left: 35%; width: 35%; margin-bottom: 40px;" src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
                <?php } ?>
            </div>
            <div class="row bold" style="margin-bottom: 20px;">
                <div style="width: 75%; float: left;">
                    <div class="divTable">
                        <div class="divRow">
                            <div class="divCell">កាលបរិច្ឆេទ​ / <?= lang('date'); ?></div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $this->bpas->hrld($transfer->date); ?></div>
                        </div>
                        <div class="divRow">
                            <div class="divCell">លេខរៀងវិក្កយបត្រ / <?= lang('ref'); ?></div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $transfer->reference_no; ?></div>
                        </div>
                        <div class="divRow">
                            <div class="divCell">បង្កើតដោយ / <?= lang('created_by'); ?></div>
                            <div class="divCell_">:</div>
                            <div class="divCell"><?= $created_by->first_name . ' ' . $created_by->last_name; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xs-3 pull-right text-right order_barcodes" style="float: right;">
                    <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($transfer->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $transfer->reference_no; ?>" class="bcimg" />
                    <?= $this->bpas->qrcode('link', urlencode(admin_url('transfers/multi_transfer_pdf/' . $transfer->id)), 2); ?>
                </div>
                <div class="clearfix"></div>
            </div>  
            <div class="clearfix"></div>

            <?php $total_amount = 0; ?>
            <div class="table-responsive">
                <table class="tbTransfer">
                    <thead class="thead">
                        <tr style="color: #FFF !important; background-color: #555 !important;">
                            <th style="text-align:center; vertical-align:middle;"><?= lang('no.'); ?></th>
                            <th style="vertical-align:middle;"><?= lang('ref'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('paid_by'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('charge_amount'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('transfer_amount'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('amount'); ?></th>
                        </tr>
                    </thead>
                    <div class="tbody">
                        <?php $r = 1;
                        foreach ($rows as $row): ?>
                        <tr>
                            <td style="text-align: center; width: 25px;"><?= $r; ?></td>
                            <td style="text-align: left; padding-left: 10px;"><?= $row->reference_no ?></td>
                            <td style="text-align: left; padding-left: 10px;"><?= $row->paid_by ?></td>
                            <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 10px;"><?= $this->bpas->formatMoney($row->charge_amount); ?></td>
                            <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 10px;"><?= $this->bpas->formatMoney($row->transfer_amount); ?></td>
                            <td style="width: 18%; text-align: right; vertical-align: middle; padding-right: 10px;"><?= $this->bpas->formatMoney($row->amount); ?></td>
                        </tr>
                        <?php 
                            $r++;
                            $total_amount += $this->bpas->formatMoney($row->amount);
                        endforeach; ?>
                    </div>
                    <div class="tfoot">
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold; padding-right: 10px;">
                                <?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align: right; font-weight: bold; padding-right: 10px;">
                                <?= $this->bpas->formatMoney($total_amount) ?>
                            </td>
                        </tr>
                    </div>
                </table>
            </div>
            <div class="clearfix"></div>
            <div class="row"​>
                <div class="col-xs-12">
                    <?php if ($transfer->note || $transfer->note != '') { ?>
                    <div class="well well-sm">
                        <p class="bold"><?= lang('note'); ?>:</p>
                        <div><?= $this->bpas->decode_html($transfer->note); ?></div>
                    </div>
                    <?php } ?>
                </div>
                <div class="col-xs-4 pull-left text-center" style="margin-top: 20%;">
                    <hr class="signature" style="border-top: 2px solid black; margin: 25px;">
                    <p style="margin-top: -20px;">ហត្ថលេខា និង ឈ្មោះអ្នករៀបចំ<br>Prepared's Signature & Name</p>
                </div>
                <div class="col-xs-4 pull-right text-center" style="margin-top: 20%;">
                    <hr class="signature" style="border-top: 2px solid black; margin: 25px;">
                    <p style="margin-top: -20px;">ហត្ថលេខា និង ឈ្មោះអ្នកទទួល<br>Received's signature and Name</p>
                </div>
            </div>
        </div>
    