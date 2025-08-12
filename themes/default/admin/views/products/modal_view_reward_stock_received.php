<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
            </div>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                        <p class="bold">
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?><br>
                            <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                        </p>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                        <?= $this->bpas->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <?php echo $this->lang->line($reward_exchange->category); ?>:
                    <h2 style="margin-top:10px;"><?= $company->company && $company->company != '-' ? $company->company : $company->name; ?></h2>
                    <?= $company->company && $company->company != '-' ? '' : 'Attn: ' . $company->name ?>
                    <?php
                    echo $company->address . '<br />' . $company->city . ' ' . $company->postal_code . ' ' . $company->state . '<br />' . $company->country;
                    echo lang('tel') . ': ' . $company->phone . '<br />' . lang('email') . ': ' . $company->email;
                    ?>
                </div>
                <div class="col-xs-6">
                    <?php echo $this->lang->line('biller'); ?>:<br />
                    <h2 style="margin-top:10px;">
                        <?php if ($biller) { ?>
                            <?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>
                        <?php } else { ?>
                            <?= $Settings->site_name; ?>
                        <?php } ?>
                    </h2>
                    <?= $warehouse->name ?>
                    <?php
                        echo $warehouse->address;
                        echo ($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                    ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped print-table order-table">
                    <thead>
                        <tr>
                            <th><?= lang('no.'); ?></th>
                            <th><?= lang('description'); ?></th>
                            <th><?= lang('quantity'); ?></th>
                            <th><?= lang('unit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $r     = 1;
                        foreach ($rows as $row) { ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                </td>
                                <td style="width: 80px; text-align:left; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->quantity); ?></td>
                                <td style="width: 80px; text-align:left; vertical-align:middle;"><?= $row->unit_name; ?></td>
                            </tr>
                            <?php $r++; } ?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?php if ($inv->note || $inv->note != '') { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('note'); ?>:</p>
                            <div><?= $this->bpas->decode_html($inv->note); ?></div>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang('created_by'); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?>
                        </p>
                        <?php if ($inv->updated_by) { ?>
                            <p>
                                <?= lang('updated_by'); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?><br>
                                <?= lang('update_at'); ?>: <?= $this->bpas->hrld($inv->updated_at); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
    });
</script>