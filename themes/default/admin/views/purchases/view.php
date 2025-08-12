<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
@media print {
    .no-print{
        display:none !important;
    }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang('purchase_no') . '. ' . $inv->id; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?= admin_url('purchases/payments/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('purchases/add_payment/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('add_payment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>">
                                <i class="fa fa-edit"></i> <?= lang('edit_purchase') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('purchases/email/' . $inv->id) ?>">
                                <i class="fa fa-envelope"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('purchases/excel_export/' . $inv->id) ?>">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="well">
                    <?php if ($warehouse->logo ) {
                        $path   = base_url() . 'assets/uploads/' . $warehouse->logo;
                    ?>
                    <div><img src="<?= $path; ?>" alt="<?=$warehouse->name; ?>" style="max-height: 80px;"></div>
                    <?php } ?>
               </div>
                <!-- <?php if (!empty($inv->return_purchase_ref) && $inv->return_id) {
                echo '<div class="alert alert-info no-print"><p>' . lang('purchase_is_returned') . ': ' . $inv->return_purchase_ref;
                echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                echo '</p></div>';
                } ?>
                <div class="clearfix"></div>
                <div class="print-only col-xs-12">
                    <img src="<?= admin_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                         alt="<?= $Settings->site_name; ?>">
                </div> -->
                <div class="well well-sm">
                    <div class="col-xs-4 border-right">
                        <div class="col-xs-12">
                            <div>   <strong><?php echo $this->lang->line("name"); ?> : </strong> 
                                <?= $supplier->company ? $supplier->company : $supplier->company; ?>
                            </div>
                            <div> <strong><?php echo $this->lang->line("Attn"); ?> : </strong> 
                                <?= $supplier->name ? $supplier->name : $supplier->name; ?>
                            </div>
                            <div> <strong><?php echo $this->lang->line("address"); ?> : </strong> 
                                <?= $supplier->address; ?>
                            </div>
                            <div> <strong><?php echo $this->lang->line("city"); ?> : </strong> 
                                <?= $supplier->city; ?>
                            </div>
                            <?php
                            echo '<strong>'.lang("phone") . ": </strong>" . $supplier->phone . "<br />";
                            // echo '<strong>'.lang("email") . ": </strong>" . $supplier->email;
                            // echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br />" . $supplier->country;
                            echo "<p>";
                            if ($supplier->vat_no != "-" && $supplier->vat_no != "") {
                                echo "<br>" . lang("vat_no") . ": " . $supplier->vat_no;
                            }
                            if ($supplier->cf1 != "-" && $supplier->cf1 != "") {
                                echo "<br>" . lang("scf1") . ": " . $supplier->cf1;
                            }
                            if ($supplier->cf2 != "-" && $supplier->cf2 != "") {
                                echo "<br>" . lang("scf2") . ": " . $supplier->cf2;
                            }
                            if ($supplier->cf3 != "-" && $supplier->cf3 != "") {
                                echo "<br>" . lang("scf3") . ": " . $supplier->cf3;
                            }
                            if ($supplier->cf4 != "-" && $supplier->cf4 != "") {
                                echo "<br>" . lang("scf4") . ": " . $supplier->cf4;
                            }
                            if ($supplier->cf5 != "-" && $supplier->cf5 != "") {
                                echo "<br>" . lang("scf5") . ": " . $supplier->cf5;
                            }
                            if ($supplier->cf6 != "-" && $supplier->cf6 != "") {
                                echo "<br>" . lang("scf6") . ": " . $supplier->cf6;
                            }
                            echo "</p>";
                            // echo lang("tel") . ": " . $supplier->phone . "<br />" . lang("email") . ": " . $supplier->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-xs-4 border-right">
                        <div><strong><?php echo $this->lang->line("purchase_no"); ?> : </strong> 
                            <?= $inv->reference_no; ?>
                        </div>
                        <div><strong><?php echo $this->lang->line("date"); ?> : </strong> 
                            <?= $inv->date; ?>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="col-xs-12">
                            <div><strong><?php echo $this->lang->line("name"); ?> : </strong> 
                                <?= $warehouse->name; ?>
                            </div>
                            <div><strong><?php echo $this->lang->line("Attn"); ?> : </strong> 
                                <!-- <?= $warehouse->atten_name; ?> -->
                            </div>
                            <div><strong><?php echo $this->lang->line("address"); ?> : </strong> 
                                <?php echo strip_tags($warehouse->address); ?>
                            </div>
                            <div><strong><?php echo $this->lang->line("city"); ?> : </strong> 
                                    
                            </div>
                            <div><strong><?php echo $this->lang->line("phone"); ?> : </strong> 
                                <?= $warehouse->phone; ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!-- <div class="well well-sm">
                    <div class="col-xs-4 border-right">
                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name; ?></h2>
                            <?= $supplier->company              && $supplier->company != '-' ? '' : 'Attn: ' . $supplier->name ?>
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
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-xs-4">
                        <div class="col-xs-2"><i class="fa fa-3x fa-truck padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $Settings->site_name; ?></h2>
                            <?= $warehouse->name ?>
                            <?php
                            echo $warehouse->address . '<br>';
                            echo($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-xs-4 border-left">
                        <div class="col-xs-2"><i class="fa fa-3x fa-file-text-o padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= lang('ref'); ?>: <?= $inv->reference_no; ?></h2>
                            <?php if (!empty($inv->return_purchase_ref)) {
                                echo '<p>' . lang('return_ref') . ': ' . $inv->return_purchase_ref;
                                if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '</p>';
                                }
                            } ?>
                            <p style="font-weight:bold;"><?= lang('date'); ?>: <?= $this->bpas->hrld($inv->date); ?></p>
                            <p style="font-weight:bold;"><?= lang('status'); ?>: <?= lang($inv->status); ?></p>
                            <p style="font-weight:bold;"><?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?></p>
                        </div>
                        <div class="col-xs-12 order_barcodes">
                            <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                            <?= $this->bpas->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div> -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped print-table order-table">
                        <thead>
                            <tr>
                                <th><?= lang("item"); ?></th>
                                <th><?= lang("code"); ?></th>
                                <th><?= lang("description"); ?></th>
                                <th><?= lang("qty"); ?></th>
                                <th><?= lang("unit"); ?></th>
                                <!-- <th><?= lang("color"); ?></th>
                                <th><?= lang("size"); ?></th> -->
                                <?php if ($inv->status == 'partial') {
                                        echo '<th>'.lang("received").'</th>';
                                } ?>
                                <th style="padding-right:20px;"><?= lang("unit_price"); ?></th>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                                } ?>
                                <th style="padding-right:20px;"><?= lang("total_usd"); ?></th>
                                <?php if($this->Admin || $this->Owner){ ?>
                                <th style="" class="no-print"><?= lang("actions"); ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $r = 1; 
                        foreach ($rows as $row): ?>
                            <?php 
                                $unit = $this->site->getUnitByID($row->product_unit_id);
                                $quantity_received = $this->bpas->formatDecimal($this->site->baseToUnitQty($row->quantity_received, $unit)); 
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle; width: 180px;">
                                    <?= $row->product_code; ?>
                        
                                </td>
                                <td style="vertical-align:middle; width: 180px;">
                                     <?= $row->product_name; ?>
                                                <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?> <?= $row->status_change != null ? '<br>' . $row->status_change : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    <!-- color  -->
                                     <?php
                                    $data= explode("|",$row->variant);
                                    if($row->variant){
                                        
                                        echo $data[0];
                                        if(isset($data[1])){
                                        //  echo $data[1];
                                        }
                                    }
                                    //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                    ?>
                                    <!-- size -->
                                    <?php if(isset($data[1])){ echo $data[1];}
                                        if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?>
                                </td>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                </td>
                                <!--  <td style="vertical-align:middle;">
                                     <?php
                                     $edit_expiry = anchor('admin/purchases/edit_expiry/'.$row->id.'/'.$row->purchase_id, '<i class="fa fa-edit"></i> ', 'data-toggle="modal" title="'.lang('edit').'" data-target="#myModal"');
                                    $data= explode("|",$row->variant);
                                    if($row->variant){
                                        echo $data[0];
                                        if(isset($data[1])){
                                        //  echo $data[1];
                                        }
                                    }
                                    //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                    ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?php if(isset($data[1])){ echo $data[1];}
                                    if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?>
                                </td> -->  
                                <?php if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($quantity_received) .'</td>';
                                } ?>
                                <td style="text-align:right; width:120px; padding-right:10px;">
                                    <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_cost) . '</del>' : ''; ?>
                                    <?= $this->bpas->formatMoney($row->unit_cost); ?>
                                </td>
                                <?php if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                               <?php if($this->Admin || $this->Owner){?>
                                <td style="text-align:center; width:50px;" class="no-print"><?= $edit_expiry;?></td>
                                <?php } ?>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                            foreach ($return_rows as $row): ?>
                            <tr class="warning">
                             <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle; width: 180px;">
                                    <?= $row->product_code; ?>
                        
                                </td>
                                <td style="vertical-align:middle; width: 180px;">
                                     <?= $row->product_name; ?>
                                                <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?><?= $row->status_change ? '<br>' . $row->status_change : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    <!-- color  -->
                                     <?php
                                    $data= explode("|",$row->variant);
                                    if($row->variant){
                                        
                                        echo $data[0];
                                        if(isset($data[1])){
                                        //  echo $data[1];
                                        }
                                        
                                    }
                                    //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                    ?>
                                    <!-- size -->
                                    <?php if(isset($data[1])){ echo $data[1];}
                                    if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?>
                                </td>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                </td>
                                <!--  <td style="vertical-align:middle;">
                                     <?php
                                     $edit_expiry = anchor('admin/purchases/edit_expiry/'.$row->id.'/'.$row->purchase_id, '<i class="fa fa-edit"></i> ', 'data-toggle="modal" title="'.lang('edit').'" data-target="#myModal"');
                                    $data= explode("|",$row->variant);
                                    if($row->variant){
                                        
                                        echo $data[0];
                                        if(isset($data[1])){
                                        //  echo $data[1];
                                        }
                                    }
                                    //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                    ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?php if(isset($data[1])){ echo $data[1];}
                                    if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?>
                                </td> -->  
                                <?php if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                } ?>
                                <td style="text-align:right; width:120px; padding-right:10px;">
                                    <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->bpas->formatMoney($row->real_unit_cost) . '</del>' : ''; ?>
                                    <?= $this->bpas->formatMoney($row->unit_cost); ?>
                                </td>
                                <?php if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                <?php if ($this->Admin || $this->Owner) { ?>
                                <td style="text-align:center; width:50px;" class="no-print"><?= $edit_expiry;?></td>
                                <?php } ?>
                            </tr>
                                <tr class="warning hide">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) { ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                    <?php } ?>
                                    <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_code; ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:120px;">' . $this->bpas->formatQuantity($row->quantity_received) . ' ' . $row->product_unit_code . '</td>';
                                    } ?>
                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->unit_cost); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->bpas->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_discount) . '</td>';
                                    } ?>
                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                </tr>
                                <?php
                                $r++;
                            endforeach;
                        } ?>
                        </tbody>
                        <tfoot>
                        <?php
                        $col = $Settings->indian_gst ? 7 : 6;
                        if ($inv->status == 'partial') {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                        }
                        if (($Settings->product_discount && $inv->product_discount != 0) && ($Settings->tax1 && $inv->product_tax > 0)) {
                            $tcol = $col - 2;
                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                            $tcol = $col - 1;
                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 1;
                        } else {
                            $tcol = $col;
                        } ?>
                        <?php if ($inv->grand_total != $inv->total) { ?>
                            <tr>
                                <td colspan="<?= $tcol; ?>"
                                    style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="text-align:right;">' . $this->bpas->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; padding-right:10px;"><?= $this->bpas->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($return_purchase) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                        } ?>
                        <?php if ($Settings->indian_gst) {
                            if ($inv->cgst > 0) {
                                $cgst = $return_purchase ? $inv->cgst + $return_purchase->cgst : $inv->cgst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($cgst) : $cgst) . '</td></tr>';
                            }
                            if ($inv->sgst > 0) {
                                $sgst = $return_purchase ? $inv->sgst + $return_purchase->sgst : $inv->sgst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($sgst) : $sgst) . '</td></tr>';
                            }
                            if ($inv->igst > 0) {
                                $igst = $return_purchase ? $inv->igst + $return_purchase->igst : $inv->igst;
                                echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->bpas->formatMoney($igst) : $igst) . '</td></tr>';
                            }
                        } ?>
                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                        } ?>
                        <?php if ($Settings->tax2) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                        } ?>
                        <?php 
                        if ($inv->shipping != 0) {
                            if ($this->Settings->avc_costing) {
                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' .lang('cost') .''. lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->bpas->formatMoney($inv->shipping) . '</td></tr>';
                            }
                        } ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr style="display: none;">
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->bpas->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row">
                        <div class="col-xs-3">
                            <table class="table">
                                <tr>
                                    <th style="text-align: center;" height="30">Payment Details</th>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="well-sm">
                                        <input type="checkbox" name="ch1" value="check"> Check<br>
                                        <input type="checkbox" name="ch1" value="cash"> Cash<br>
                                        <input type="checkbox" name="ch1" value="transfer"> Telegraphic Transfer<br>
                                        <h6>Name:    .......................................................................</h6>
                                        <h6>CC#:     ..........................................................................</h6>
                                        <h6>Exp Date: ................/................../............................</h6>
                                        </div>
                                    </td>
                                </tr>
                            </table><br/>
                            <table class="table">
                                <tr>
                                    <th style="text-align: center;" height="30">Shipping Date</th>
                                </tr>
                                <tr>
                                    <td>
                                     <div class="well-sm">
                                        <!-- <hr>
                                        <h6>Shipping Date</h6> -->
                                        <br/>
                                        <p><?= lang("date"); ?>: ................/................../........................</p><br/>
                                        </div>
                                    </td>
                                </tr>
                            </table><br/>
                    </div>
                    <div class="col-xs-5">
                    <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                        <?php if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-xs-4">
                            <div class="well-sm">
                                <hr>
                                <p><strong><?= lang('representative'); ?></strong></p> 
                            </div><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                            <div class="col-xs-8">
                               <strong><?= lang('managing_director'); ?></strong>
                            </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($payments)) { ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th><?= lang('date') ?></th>
                                    <th><?= lang('payment_reference') ?></th>
                                    <th><?= lang('paid_by') ?></th>
                                    <th><?= lang('amount') ?></th>
                                    <th><?= lang('created_by') ?></th>
                                    <th><?= lang('type') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($payments as $payment) { ?>
                                <tr>
                                    <td><?= $this->bpas->hrld($payment->date) ?></td>
                                    <td><?= $payment->reference_no; ?></td>
                                    <td><?= $payment->paid_by; ?></td>
                                    <td><?= $payment->amount; ?></td>
                                    <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                    <td><?= $payment->type; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <?php if ($inv->attachment) { ?>
                    <div class="btn-group">
                        <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                            <i class="fa fa-chain"></i> <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                        </a>
                    </div>
                <?php } ?>
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/payments/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/add_payment/' . $inv->id) ?>" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>" data-target="#myModal" data-toggle="modal">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete_purchase') ?></b>"
                           data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                           data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>