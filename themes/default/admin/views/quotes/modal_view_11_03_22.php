<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style type="text/css">
    .table_pro {
        width: 100%;
    }
    .table_pro tr > th {
        text-align: center !important;
        font-size: 12px;
        padding: 5px;
    }
    .table_pro tr > th, .table_pro tr > td {
        border: 1px solid #000 !important;
    }
    .table_pro tr > td {
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
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 well">
                        <div class="col-sm-3 col-xs-3">
                            <?php if ($logo) { ?>
                            <div style="margin-left: -20px;">
                                <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>" width="200">
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-sm-6 col-xs-6">
                            <div class="text-center" style="line-height: normal;">
                                <h1 style="margin: 0;">SBC Cambodia</h1>
                                <h2 style="margin: 0 0 5px;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                                <?php
                                    echo $biller->address . ' ' . $biller->postal_code . ' ' . $biller->state . '<br>' . $biller->country;
                                    echo '<p style="margin-top: -3px;">' . 'ទូរស័ព្ទលេខ (' . lang('tel') . '): ' . $biller->phone . '</p>';
                                    echo '<p style="margin-top: -17px;">' . 'សារអេឡិចត្រូនិច (' . lang('email') . '): ' . $biller->email . '</p>';
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3 col-xs-3 text-right order_barcodes">
                            <?= $this->bpas->qrcode('link', urlencode(admin_url('quotes/view/' . $inv->id)), 2); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-xs-12 text-center" style="margin-top: -20px !important;">
                        <h3 style="font-family: 'Khmer OS Muol Light';display: none;">វិក្កយបត្រ</h3>
                        <h2>QUOTATION</h2>
                    </div>
                </div>

                <div class="row">
                    <?php if ($Settings->invoice_view == 1) { ?>
                    <div class="col-xs-12 text-center">
                        <h1><?= lang('tax_invoice'); ?></h1>
                    </div>
                    <?php } ?>

                    <div class="col-xs-7 col-ms-7">
                        <table style="font-size: 12px;">
                            <tr>
                                <td style="width: 10%;">ឈ្មោះក្រុមហ៊ុន</br>Company Name</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 30%;">
                                    <h2 style="margin-top:10px;"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h2>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 10%;">អាសយដ្ឋាន</br><?= lang('address'); ?></td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 30%;">
                                    <?php echo $customer->address . ', ' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . ', ' . $customer->country;?>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 10%;">ទូរស័ព្ទលេខ (<?= lang('tel'); ?>)</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 30%;"><?= $customer->phone ?></td>
                            </tr>
                            <tr>
                                <td style="width: 10%;">អុីម៉ែល (<?= lang('email'); ?>)</td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 30%;"><?= $customer->email ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-5 col-ms-5">
                        <table style="font-size: 12px;">
                            <tr>
                                <td style="width: 20%;">កាលបរិច្ឆេទ<br><?= lang('date'); ?></td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 25%;"><?= $this->bpas->hrld($inv->date); ?></td>
                            </tr>
                            <tr>
                                <td style="width: 20%;">លេខរៀងវិក្កយបត្រ<br><?= lang('ref'); ?></td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 25%;"><?= $inv->reference_no; ?></td>
                            </tr>
                            <?php if($inv->valid_day){?>
                            <tr>
                                <td style="width: 20%;">ថ្ងៃផុតកំណត់<br><?= lang('valid_day'); ?></td>
                                <td style="width: 5%;">:</td>
                                <td style="width: 25%;"><?= $inv->valid_day; ?></td>
                            </tr>
                            <?php }?>
                        </table>
                    </div>
                </div>

                <?php $col = $Settings->indian_gst ? 5 : 4;
                if ($Settings->product_discount && $inv->product_discount != 0) {
                    $col++;
                }
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    $col++;
                }
                if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 2;
                } elseif ($Settings->product_discount && $inv->product_discount != 0){
                    $tcol = $col - 1;
                } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 1;
                } else {
                    $tcol = $col;
                } ?>

                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <table class="table_pro" style="margin-top: 10px;">
                            <div class="thead" style="font-size: 11px;">
                                <tr style="color: #FFF !important; background-color: #444 !important;">
                                    <th>ល.រ<br><?= strtoupper(lang('no.')) ?></th>
                                    <th style="min-width:40px; width: 40px; text-align: center;">រូបភាព<br><?= strtoupper(lang('image')) ?></th>
                                    <th>បរិយាយមុខទំនិញ<br><?= strtoupper(lang('description')) ?></th>
                                    <?php if ($Settings->indian_gst) { ?>
                                        <th><?= lang('hsn_code'); ?></th>
                                    <?php } ?>
                                    <th>ខ្នាត<br><?= strtoupper(lang('unit')) ?></th>
                                    <th>ចំនួន<br><?= strtoupper(lang('quantity')) ?></th>
                                    <th>តម្លៃ<br><?= strtoupper(lang('unit_price')) ?></th>
                                    <?php if ($Settings->product_discount) {
                                        echo '<th>' . 'បញ្ចុះតម្លៃ <br>' . strtoupper(lang('discount')) . '</th>';
                                    } 
                                    if ($Settings->tax1) {
                                        echo '<th>' . 'ពន្ធទំនិញ <br>' . strtoupper(lang('tax')) . '</th>';
                                    } ?>
                                    <th>តម្លៃសរុបតាមមុខទំនិញ<br><?= strtoupper(lang('subtotal')) ?></th>
                                </tr>
                            </div>
                            <div class="tbody">
                                <?php  $detault_currency= $Settings->default_currency =="USD" ? "$" : "៛";  ?>
                                <?php $no = 1; $erow = 1; $totalRow = 0;
                     
                                    foreach ($rows as $row) {
                                        $free = lang('free');
                                        $product_unit = '';
                                        $total = 0;
                                        $product_name_setting;
                                        $product_name_setting = $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');?>

                                    <tr>
                                        <td style="vertical-align: middle; text-align: center"><?php echo $no ?></td>
                                        <td style="vertical-align: middle;">
                                            <div class="text-center">
                                                <a href="<?= site_url('assets/uploads/') . $row->image ?>" data-toggle="lightbox">
                                                    <img src="<?= site_url('assets/uploads/'). $row->image;?>" alt="" style="width=40px;height:40px;padding:3px;" />
                                                </a>
                                            </div>
                                            <!-- <div class="text-center"><a href="' +
                                            site.url +
                                            'assets/uploads/' +
                                            image_link +
                                            '" data-toggle="lightbox"><img src="' +
                                            site.url +
                                            'assets/uploads/thumbs/' +
                                            image_link +
                                            '" alt="" style="width:30px; height:30px;" /></a></div> -->
                                        </td>
                                        <td style="vertical-align: middle; padding-left: 10px;">
                                            <?=$row->product_name;?>
                                        </td>
                                        <td style="vertical-align: middle; text-align: center">
                                            <?= $row->product_unit_code; ?>
                                        </td>
                                        <td style="vertical-align: middle; text-align: center;">
                                            <?= $this->bpas->formatQuantity($row->unit_quantity) ?>
                                        </td>
                                        <td style="vertical-align: middle; text-align: center">
                                            <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                            <?= $detault_currency.$this->bpas->formatMoney($row->unit_price); ?>
                                        </td>
                                        <?php if ($Settings->product_discount) {
                                            echo '<td style="vertical-align: middle; text-align: center">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $detault_currency.$this->bpas->formatMoney($row->item_discount) . '</td>';
                                        }
                                        if ($Settings->tax1) {
                                            echo '<td style="vertical-align: middle; text-align: center">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . $detault_currency.$this->bpas->formatMoney($row->item_tax) . '</td>';
                                        } ?>
                                        <td style="vertical-align: middle; text-align: right;width: 80px;">
                                            <?= $detault_currency.$this->bpas->formatMoney($row->subtotal); ?>
                                        </td>
                                    </tr>

                                <?php $no++; $erow++; $totalRow++;
                                    if ($totalRow % 25 == 0) {
                                        echo '<tr class="pageBreak"></tr>';
                                    }
                                } ?>
                                <?php
                                    if($erow < 11){
                                        $k=11 - $erow;
                                        for($j=1; $j<=$k; $j++) {
                                            echo '<tr>
                                                    <td height="34px" style="text-align: center; vertical-align: middle">'.$no.'</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>';
                                                    if($Settings->product_discount){
                                                        echo '<td></td>';
                                                    }
                                                    if ($Settings->tax1) {
                                                        echo '<td></td>';   
                                                    }
                                            echo '</tr>';
                                            $no++;
                                        }
                                    }
                                ?>
                                <?php
                                    
                                    $row = 2;
                                    $col =4;
                                
                                    if ($inv->grand_total != $inv->total) {
                                        $row++;
                                    }
                                    if ($inv->order_discount != 0) {
                                        $row++;
                                        $col = 3;
                                    }
                                    if ($inv->shipping != 0) {
                                        $row++;
                                        $col = 3;
                                    }
                                    if ($inv->order_tax != 0) {
                                        $row++;
                                        $col = 3;
                                    }
                                ?>
                            </div>
                            <div class="tfoot">
                                <?php if ($inv->grand_total != $inv->total) { ?>
                                <tr>
                                    <td rowspan = "<?= $row; ?>" colspan="4" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;">
                                        <?php if (!empty($inv->invoice_footer)) { ?>
                                            <p style="font-size:14px !important;"><strong><u>Note: </u></strong></p>
                                            <p style="margin-top:-5px !important; line-height: 2"><?= $inv->invoice_footer ?></p>
                                        <?php } ?>
                                    </td>
                                    <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">សរុប​ / <?= strtoupper(lang('total')) ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <td align="right" style="padding-right: 10px;">$<?=$this->bpas->formatMoney($inv->total); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($Settings->product_discount && $inv->product_discount != 0) { ?>
                                <tr>
                                    <td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                                    <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">បញ្ចុះតម្លៃលើការបញ្ជាទិញ / <?= strtoupper(lang('order_discount')) ?></td>
                                    <td align="right" style="padding-right: 10px;"><?php echo $this->bpas->formatMoney($inv->product_discount); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($Settings->tax1 && $inv->product_tax > 0) { ?>
                                <tr>
                                    <td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>
                                    <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">ពន្ធអាករ / <?= strtoupper(lang('tax')) ?></td>
                                    <td align="right" style="padding-right: 10px;">$<?= $this->bpas->formatMoney($inv->product_tax) ?></td>
                                </tr>
                                <?php } ?>
                         
                                <?php if ($inv->order_discount != 0) {
                                    echo '<tr>' . 
                                        '<td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' . 
                                        '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px;">' . 
                                            lang('order_discount') . ' (' . $default_currency->code . ')</td>' . 
                                        '<td style="text-align: right; padding-right: 10px;">' . 
                                            ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($inv->order_discount) . 
                                        '</td>' . 
                                    '</tr>';
                                } ?>
                                <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                    echo '<tr>' . 
                                        '<td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' .
                                        '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px;">' . 
                                            lang('order_tax') . ' (' . $default_currency->code . ')' . 
                                        '</td>' . 
                                        '<td style="text-align: right; padding-right: 10px;">' . 
                                            $this->bpas->formatMoney($inv->order_tax) . 
                                        '</td>' . 
                                    '</tr>';
                                } ?>
                                <?php if ($inv->shipping != 0) {
                                    echo '<tr>' . 
                                        '<td colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;"></td>' .
                                        '<td colspan="' . $col . '" style="text-align: right; padding-right: 10px;">' . 
                                            lang('shipping') . ' (' . $default_currency->code . ')' . 
                                        '</td>' . 
                                        '<td style="text-align: right; padding-right: 10px;">' . 
                                            $this->bpas->formatMoney($inv->shipping) . 
                                        '</td>' . 
                                    '</tr>';
                                } ?>
                                <tr>
                                    <td rowspan="<?= $row; ?>" colspan="3" style="border-left: 1px solid #FFF !important; border-bottom: 1px solid #FFF !important;">
                                        <?php if (!empty($inv->invoice_footer)) { ?>
                                            <p><strong><u>Note: </u></strong></p>
                                            <p><?= $inv->invoice_footer ?></p>
                                        <?php } ?>
                                    </td>
                                  
                                    <td colspan="<?= $col; ?>" style="text-align: right; font-weight: bold; padding-right: 10px;">តម្លៃសរុបរួម​​ / <?= strtoupper(lang('total_amount')) ?>
                                        (<?= $default_currency->code; ?>)
                                    </td>
                                    <td align="right" style="padding-right: 10px;"><?= $detault_currency.$this->bpas->formatMoney($inv->grand_total); ?></td>
                                </tr>
                            </div>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                    <?php if ($inv->note || $inv->note != '') { ?>
                        <div class="well well-sm" style="margin-top: 20px;">
                            <p class="bold"><?= lang('note'); ?>:</p>
                            <?= $this->bpas->decode_html($inv->note); ?>
                        </div>
                    <?php } ?>
                    </div>
                    <div class="col-xs-4 pull-right text-center">
                        <hr class="signature" style="border-top: 2px solid black; margin: 25px;">
                        <p style="margin-top: -20px;">ហត្ថលេខា និង ឈ្មោះអ្នករៀបចំ<br>Prepared's Signature & Name</p>
                    </div>
                </div>
                <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, null, $inv->product_tax) : ''; ?>
                <br><br>
                <div class="row" >
                    <?php if (!$Supplier || !$Customer) {
                            ?>
                <div class="buttons">
                    <?php if ($inv->attachment) {
                                ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php
                            } ?>
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_sale') ?>">
                                <i class="fa fa-heart"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_sale') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_purchase') ?>">
                                <i class="fa fa-star"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_purchase') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('quotes/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('quotes/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete') ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php
                        } ?>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.tip').tooltip();
        });
    </script>