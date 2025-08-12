<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    @media print { 
        .no-print{ display:none !important; } 
        #myModal .modal-content {
            display: none !important;
        }
        .modal-body {
            padding-left: 0px;
            padding-right: 20px;
        }
        @page {
            margin: 0;
            size: A4;
        }
    }
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();"><i class="fa fa-print"></i> <?= lang('print'); ?></button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_stock_received') . ' (' . lang('purcahse') . ' ' . lang('reference') . ': ' . $inv->reference_no . ')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-3">
                    <img style="margin-top: 20px;" src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
                </div>
                <div class="col-xs-6 text-center">
                     <h1><?= $this->Settings->site_name;?></h1>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center"><p style="font-weight: bold; font-family: 'Time New Roman'; font-size: 18px;"><?= strtoupper('Stock Receipt'); ?></p></div>
            </div>
            <div class="row">
                <div class="col-xs-7">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 25%;"><?php echo $this->lang->line("received_from"); ?></td>
                            <td>:</td>
                            <td><?= $warehouse->name; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $this->lang->line("stock_in_by"); ?></td>
                            <td>:</td>
                            <td><?= $stock_in_by->first_name . ' ' . $stock_in_by->last_name; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $this->lang->line("phone"); ?></td>
                            <td>:</td>
                            <td><?= $warehouse->phone; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-5">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 45%;"><?php echo $this->lang->line("date"); ?></td>
                            <td style="width: 5%;">:</td>
                            <td><?= $this->bpas->hrld($stock_in->date); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $this->lang->line("purchase_ref"); ?></td>
                            <td>:</td>
                            <td><?= $inv->reference_no; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $this->lang->line("stock_in_ref"); ?></td>
                            <td>:</td>
                            <td><?= $stock_in->reference_no; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">                    
                <div class="col-xs-12">
                    <div class="table"><!-- responsive -->
                        <table class="table table-hover table-bordered table-striped" style="margin-top: 20px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center !important;"><?= lang("No"); ?></th>
                                    <th><?= lang("code"); ?></th>
                                    <th><?= lang("description"); ?></th>
                                    <th style="text-align: center !important;"><?= lang("unit"); ?></th>
                                    <th style="text-align: center !important;"><?= lang("qty"); ?></th>
                                    <th style="padding-right:20px;"><?= lang("serial"); ?></th>
                                    <th style="padding-right:20px;"><?= lang("max_serial"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $r = 1; 
                            foreach ($stock_in_items as $row): ?>
                                <?php 
                                    $unit = $this->site->getUnitByID($row->product_unit_id);
                                    $stock_received_qty = $this->bpas->formatDecimal($this->site->baseToUnitQty($row->stock_received_qty, $unit)); 
                                ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle; width: 120px;"><?= $row->product_code; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_name; ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?> <?= $row->status_change != null ? '<br>' . $row->status_change : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                        <!-- color -->
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
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($stock_received_qty); ?></td>
                                    <td style="text-align:right; width:100px; padding-right:10px;"></td>
                                    <td style="text-align:right; width:100px; padding-right:10px;"></td>
                                </tr>
                                <?php
                                $r++;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-3">
                    <table class="table">
                        <tr>
                            <th style="text-align: center;border: none;" height="30"></th>
                        </tr>
                        <tr>
                            <td>
                                <h6>Acknowledged by: ..................................................</h6>
                                <h6>Name: ......................................</h6>
                                <h6>Date: ........../.........../................</h6>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-3">
                    <table class="table">
                        <tr>
                            <th style="text-align: center;border: none;" height="30"></th>
                        </tr>
                        <tr>
                            <td>
                                <h6>Stock Received by: ..................................................</h6>
                                <h6>Name: ......................................</h6>
                                <h6>Date: ........../.........../................</h6>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-3">
                    <table class="table">
                        <tr>
                            <th style="text-align: center;border: none;" height="30"></th>
                        </tr>
                        <tr>
                            <td>
                                <h6>Quality Checked by: ..................................................</h6>
                                <h6>Name: ......................................</h6>
                                <h6>Date: ........../.........../................</h6>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-3">
                    <table class="table">
                        <tr>
                            <th style="text-align: center;border: none;" height="30"></th>
                        </tr>
                        <tr>
                            <td>
                                <h6>Procurement: ..................................................</h6>
                                <h6>Name: ......................................</h6>
                                <h6>Date: ........../.........../................</h6>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-5 no-print">
                    <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                    <?php if ($stock_in->note || $stock_in->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>
                            <div><?= $this->bpas->decode_html($stock_in->note); ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>