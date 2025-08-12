<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    .box-content table{
        font-family: Verdana,'Khmer OS Battambang';
        font-size: 12px;
    }
    @media print {
        font-family: Verdana,'Khmer OS Battambang';
        font-size: 12px;
    }
    table td, table th{
        padding: 5px;
    }
    table th{
        background: #cccccc;
    }
    table ul{
        padding-left: 30px;
    }
</style>
<div class="box">
    <div class="box-header">
        
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) {
                            ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php
                        } ?>
                        <li>
                            <a href="<?= admin_url('quotes/edit/' . $inv->id) ?>">
                                <i class="fa fa-edit"></i> <?= lang('edit_quote') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/add/' . $inv->id) ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('create_invoice') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('quotes/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('quotes/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <!--<li><a href="<?= admin_url('quotes/excel/' . $inv->id) ?>"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>-->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content" style="padding-left: 10px;">
        <div class="row">
            <div class="col-lg-12">
                <table width="100%">
                    <tr>
                        <td style="vertical-align: top;">
                            <img width="180" src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                        </td>
                        <td style="vertical-align: top;">
                       
                                <h1 style="margin-top:0 !important;color: #3F708A"><?= $biller->company;?> <br></h1>
                                Phone: <?= $biller->phone;?> <br>
                                Email: <?= $biller->email;?> <br>
                                Address: <?= $this->bpas->remove_tags($biller->address);?> 
                         
                        </td>
                        <td><div style="font-weight:bold;font-size: 24px;color:#3F708A;text-align: right;">Quotation</div></td>
                    </tr>
                </table>
                <hr style="border-bottom: 2px solid #3F708A;">
                <table width="100%">
                    <tr>
                       <td width="50%">
                            <table>
                                <tr>
                                    <td style="text-align:left;font-weight: bold;">Client</td>
                                    <td>: <?= ($customer->company !='-')? $customer->company:$customer->name ;?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left;font-weight: bold;">Attn.</td>
                                    <td>: <?= $customer->name;?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left;font-weight: bold;">Telephone</td>
                                    <td>: <?= $customer->phone;?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:left;font-weight: bold;">Address</td>
                                    <td>: <?= $customer->address;?></td>
                                </tr>
                                
                            </table>
                        </td>
                        <td width="50%">
                            <table style="float:right;">
                                <tr>
                                    <td style="text-align:right;font-weight: bold;">Date</td>
                                    <td style="border:1px solid black;"><?= $this->bpas->hrld($inv->date); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;font-weight: bold;">No.</td>
                                    <td style="border:1px solid black;"><?= $inv->reference_no;?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;font-weight: bold;">Q.Validity</td>
                                    <td style="border:1px solid black;"><?= $this->bpas->hrsd($inv->valid_day); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;font-weight: bold;">Service</td>
                                    <td style="border:1px solid black;"><?= $inv->service; ?></td>
                                </tr>
                            </table>
                       </td>
                    </tr>
                </table>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table style="width: 99%;border: 1px solid #000000;padding: 5px;" border="1">
                        <thead>
                        <tr>
                            <th bgcolor="#cccccc;"><?= lang('no.'); ?></th>
                            <th bgcolor="#cccccc;"><?= lang('description'); ?></th>
                            <th bgcolor="#cccccc;" style="padding-right:20px;"><?= lang('price'); ?></th>
                            <th bgcolor="#cccccc;" class="" width="30"><?= lang('Qty'); ?></th>
                            
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('tax') . '</th>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('discount') . '</th>';
                            }
                            ?>
                            <th bgcolor="#cccccc;" style="padding-right:20px;"><?= lang('total'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $r = 1;
                        foreach ($rows as $row):
                        $product = $this->site->getProductByID($row->product_id);

                        ?>
                            <tr>
                                <td style="text-align:center; width:30px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <strong><?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?></strong> <strong><?= $row->unit_price > 0 ? '('.$this->bpas->formatMoney($row->unit_price).')' : ''; ?></strong>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <strong><?= $row->details ? '<br>' . $row->details : ''; ?></strong>
                                    <div style="padding-left:10px;">
                                        <?php if($row->image !="no_image.png"){?>
                                        <div style="float: left;width: 105px;">
                                            <img src="<?= site_url('assets/uploads/'). $row->image;?>" alt="" style="width=80px;height:80px;padding:3px;float: left;" />
                                        </div>
                                        <?php }?>
                                        <div style="float: left;">
                                            <?= $product->product_details; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:right; width:60px;vertical-align:middle;">
                                    <?= $this->bpas->formatMoney($row->unit_price); ?></td>
                                <td style="width: 60px; text-align:center; vertical-align:middle;">
                                    <?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:80px; padding-right:10px; vertical-align:middle;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        ?>
                        </tbody>
                        <tfoot>
                        <?php
                        $col = $Settings->indian_gst ? 5 : 4;
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 2;
                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                            $tcol = $col - 1;
                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 1;
                        } else {
                            $tcol = $col;
                        }
                        $rspan =2;
                        if ($Settings->tax2 && $inv->order_tax != 0) {
                            $trow = $rspan + 1;
                        }else{
                            $trow = $rspan;
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $tcol-2; ?>" rowspan="<?= $trow;?>">
                                <?= $this->bpas->decode_html($inv->note);?>
                            </td>
                            <td style="text-align:right; padding-right:10px;" colspan="2"><?= lang('total'); ?>
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->bpas->formatMoney($inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->bpas->formatMoney($inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($inv->total + $inv->product_tax); ?></td>
                        </tr>
              
                        <?php
                        if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($inv->order_discount) . '</td></tr>';
                        }
                        if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr>
                                <td style="text-align:right; padding-right:10px;;" colspan="2">'.lang('VAT').'</td>
                                <td style="text-align:right; padding-right:10px;">'.$this->bpas->formatMoney($inv->order_tax) . '</td></tr>';
                        }
                        if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;" colspan="2"><?= lang('grand_total'); ?>
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($inv->grand_total); ?></td>
                        </tr>

                        </tfoot>
                    </table>
                </div>

                <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, null, $inv->product_tax) : ''; ?>
                <div class="row">
                    <div style="padding-left:10px;padding-top: 10px;">
                        <div style="font-weight: bold;text-decoration: underline;">Payment Term:</div>
                        <div style="padding-left:10px;">
                            <?= $this->bpas->decode_html($inv->payment_note); ?>
                        </div>
                    </div>
                </div>
                <div class="row" style="font-size: 12px;">
                    <div class="col-xs-4 pull-left text-left" style="padding-left:10px;">
                        <p style="margin-top: 2px;">Authorized by:</p><br><br>
                        <div class="signature" style="border-top: 2px solid black;display: block; ">
                            Mr. SEANG Kimpheng <br>
                            T:  Co-Founder/ Project Coordinator <br>
                            M: (+855) 016 78 78 75 / 095 78 78 65 <br>
                            E: seangkimpheng@sbcsolution.biz <br>
                                seangkimpheng@gmail.com
                        </div>

                    </div>
                    <div class="col-xs-4">&nbsp;</div>
                    <div class="col-xs-4 pull-left text-center">
                        
                        <p style="margin-top: 2px;">អ្នកទិញ / Buyer Signature</p><br><br>
                        <div class="signature" style="border-top: 2px solid; black;display: block;">
                        Customer’s Stamp or Signature
                        </div>
                    </div>

                </div>
            </div>
        </div>
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
                        <a href="<?= admin_url('sales/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_invoice') ?>">
                            <i class="fa fa-plus-circle"></i> <span class="hidden-sm hidden-xs"><?= lang('create_invoice') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('quotes/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('quotes/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-info tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete_quote') ?></b>"
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                            data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php
                        } ?>
    </div>
</div>
