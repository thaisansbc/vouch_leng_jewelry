<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
        @media print {
  
            #logo {
                display: none;
                
            }
            #printsmall{
                font-size: 10px; 
                margin-left: 0;
                /*margin-right: 0;
                margin-top: 0;*/
                font-weight: bold;
            }


             /*.border_tage {
                margin-left: -15px;
                margin-right: -15px;
                margin-top: -15px;
            } */
        }
    </style>
<div class="modal-dialog modal-lg no-modal-header print" id="printsmall">
    <div class="modal-content">
        <div class="modal-body print">
            <div class="border_tage" style="margin: 0px; border: 1px solid black; padding: 5px; margin-left: -15px;
                margin-right: -5px; margin-top: -15px; margin-bottom: -5px">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <div class="clearfix"></div>
                <!-- <?php if ($logo) { ?>
                    <div class="text-center" style="margin-bottom:20px;" id="logo">
                        <img src="<?= admin_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                             alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                    </div>
                <?php } ?> -->

                <div class="well well-sm" style="margin-bottom: 0px; display: none;">
                    <div class="row bold">
                        <div class="col-xs-6 text-right order_barcodes " >
                            <img src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" height='35px' style="float: left;"/>
                            
                        </div>
                        <div class="col-xs-6 text-right order_barcodes" >
                           <?= $this->bpas->qrcode_note('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
                        </div>
                        <!-- <div class="clearfix"></div> -->
                    </div>
                    <!-- <div class="clearfix"></div> -->
                </div>
      
                <div class="row" style="margin-top: 0px;">
                    <div class="col-xs-6">
                        <?= $delivery->sale_reference_no; ?> 
                    </div>
                    <div class="col-xs-6">
                        <?= $this->bpas->hrld($delivery->date); ?>
                    </div>
                    <div class="col-xs-6">
                        <!-- <p>អ្នកទទួល: <?= $delivery->received_by; ?></p> -->
                        អ្នកទទួល: <?= $delivery->customer; ?>
                    </div>
                    <div class="col-xs-6" style="margin-bottom: 0px;">
                        <?= $customer->phone; ?>
                    </div>
                    <div class="col-xs-12">
                        <?= html_entity_decode($delivery->address); ?>
                    </div>
                </div>
               
                <div class="table-responsive" >
                    <table class=" table-bordered table-hover table-striped print" style="width: 100%; height: auto;">
                        <!-- <thead>
                            <tr>
                                <th style="text-align:center; vertical-align:middle;"><?= lang('no'); ?></th>
                                <th style="vertical-align:middle;"><?= lang('description'); ?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= lang('quantity'); ?></th>
                            </tr>
                        </thead> -->

                        <tbody>

                        <?php $r = 1;
                        foreach ($rows as $row): ?>
                            <tr>
                                <!-- <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td> -->
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
                                    if ($row->details) {
                                        echo '<br><strong>' . lang('product_details') . '</strong> ' .
                                        html_entity_decode($row->details);
                                    }
                                    ?>
                                </td>
                                <td style="width: 30px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix" style="margin-top: 10px;"> </div>
                <div class="table-responsive">
                    <table class="table">

                        <tbody>
                            <tr>
                                <td width="50%">ទឹកប្រាក់ត្រូវប្រមូល៖ </td>
                                <td width="50%">$<?= $this->bpas->formatMoney($inv->grand_total); ?></td>
                            </tr>
                            <tr>
                                <td width="50%">ចំណាំ៖ </td>
                                <td width="50%"><?= $this->bpas->decode_html($delivery->note); ?></td>
                            </tr>
                        </tbody>

                    </table>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <p><?= $delivery->name; ?></p>
                    </div>
                    <div class="col-xs-6">
                        <p><?= $user->first_name . ' ' . $user->last_name; ?> </p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>


