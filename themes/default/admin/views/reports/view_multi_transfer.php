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
        width: 98%;
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
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="text-center" style="margin-bottom: 35px;">
                <img style="width: 30%; margin-left: 12%;" src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>" >
            </div>
            <div class="row bold" style="margin-bottom: 20px;">
                <div class="col-xs-6">
                    <table style="font-size: 12px; line-height: normal;">
                        <tr>
                            <td style="width: 28%;">កាលបរិច្ឆេទ​ / <?= lang('date'); ?></td>
                            <td style="width: 3%;">:</td>
                            <td style="width: 30%;"><?= $this->bpas->hrld($transfer->date); ?></td>
                        </tr>
                        <tr>
                            <td style="width: 28%;">លេខរៀងវិក្កយបត្រ / <?= lang('ref'); ?></td>
                            <td style="width: 3%;">:</td>
                            <td style="width: 30%;"><?= $transfer->reference_no; ?></td>
                        </tr>
                        <tr>
                            <td style="width: 28%;">បង្កើតដោយ / <?= lang('created_by'); ?></td>
                            <td style="width: 3%;">:</td>
                            <td style="width: 30%;"><?= $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-6 pull-right text-right order_barcodes">
                    <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($transfer->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $transfer->reference_no; ?>" class="bcimg" />
                    <?= $this->bpas->qrcode('link', urlencode(admin_url('reports/view_multi_transfer/' . $transfer->id)), 2); ?>
                </div>
                <div class="clearfix"></div>
            </div>  
            <div class="clearfix"></div>

            <?php $total_amount = 0; ?>
            <div class="table-responsive">
                <table class="tbTransfer">
                    <div class="thead">
                        <tr style="color: #FFF !important; background-color: #555 !important;">
                            <th style="text-align:center; vertical-align:middle;"><?= lang('no.'); ?></th>
                            <th style="vertical-align:middle;"><?= lang('ref'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('paid_by'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('charge_amount'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('transfer_amount'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('amount'); ?></th>
                        </tr>
                    </div>
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
    </div>
</div>