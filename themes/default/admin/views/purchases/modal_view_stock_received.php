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
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                        <p class="bold">
                            <?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?><br>
                            <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                            <?php if (!empty($inv->return_purchase_ref)) {
                                echo lang('return_ref') . ': ' . $inv->return_purchase_ref;
                                if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '<br>';
                                }
                            } ?>
                            <?= lang('status'); ?>: <?= lang($inv->status); ?><br>
                            <?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?>
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
                    <?php echo $this->lang->line('to'); ?>:
                    <h2 style="margin-top:10px;"><?= $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name; ?></h2>
                    <?= $supplier->company && $supplier->company != '-' ? '' : 'Attn: ' . $supplier->name ?>
                    <?php
                    echo $supplier->address . '<br />' . $supplier->city . ' ' . $supplier->postal_code . ' ' . $supplier->state . '<br />' . $supplier->country;
                    echo '<p>';
                    if ($supplier->vat_no != '-' && $supplier->vat_no != '') {
                        echo '<br>' . lang('vat_no') . ': ' . $supplier->vat_no;
                    }
                    if ($supplier->gst_no != '-' && $supplier->gst_no != '') {
                        echo '<br>' . lang('gst_no') . ': ' . $supplier->gst_no;
                    }
                    if ($supplier->cf1 != '-' && $supplier->cf1 != '') {
                        echo '<br>' . lang('scf1') . ': ' . $supplier->cf1;
                    }
                    if ($supplier->cf2 != '-' && $supplier->cf2 != '') {
                        echo '<br>' . lang('scf2') . ': ' . $supplier->cf2;
                    }
                    if ($supplier->cf3 != '-' && $supplier->cf3 != '') {
                        echo '<br>' . lang('scf3') . ': ' . $supplier->cf3;
                    }
                    if ($supplier->cf4 != '-' && $supplier->cf4 != '') {
                        echo '<br>' . lang('scf4') . ': ' . $supplier->cf4;
                    }
                    if ($supplier->cf5 != '-' && $supplier->cf5 != '') {
                        echo '<br>' . lang('scf5') . ': ' . $supplier->cf5;
                    }
                    if ($supplier->cf6 != '-' && $supplier->cf6 != '') {
                        echo '<br>' . lang('scf6') . ': ' . $supplier->cf6;
                    }
                    echo '</p>';
                    echo lang('tel') . ': ' . $supplier->phone . '<br />' . lang('email') . ': ' . $supplier->email;
                    ?>
                </div>
                <div class="col-xs-6">
                    <?php echo $this->lang->line('from'); ?>:<br />
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
                            <?php if ($Settings->indian_gst) { ?>
                                <th><?= lang('hsn_code'); ?></th>
                            <?php } ?>
                            <th style="width: 30%;"><?= lang('quantity'); ?></th>
                            <th><?= lang('unit'); ?></th>
                            <?php
                            if ($inv->status == 'partial') {
                                echo '<th>' . lang('received') . '</th>';
                            } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $r     = 1;
                        $tax_summary = [];
                        foreach ($rows as $row) :
                        ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                </td>
                                <?php if ($Settings->indian_gst) {
                                ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                <?php
                                } ?>
                                <td style="width: 80px; text-align:left; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <td style="width: 80px; text-align:left; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->bpas->formatQuantity($row->quantity_received) . ' ' . $row->product_unit_code . '</td>';
                                } ?>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                            foreach ($return_rows as $row) :
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) {
                                    ?>
                                        <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php
                                    } ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->bpas->formatQuantity($row->quantity_received) . ' ' . $row->product_unit_code . '</td>';
                                    } ?>
                                </tr>
                        <?php
                            $r++;
                            endforeach;
                        } ?>
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