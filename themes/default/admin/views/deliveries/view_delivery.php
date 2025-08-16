<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();"><i class="fa fa-print"></i> <?= lang('print'); ?></button>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td colspan="4">
                                <table width="100%">
                                    <tr>
                                        <td class="text_center" style="width:20%">
                                            <?php
                                                if($biller->logo){
                                                    echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
                                                }
                                            ?>
                                        </td>
                                        <td class="text_center" style="width:60%">
                                            <div style="font-size:15px"><b><?= $biller->name ?></b></div>
                                            <div><?= $biller->address.$biller->city ?></div>
                                            <div><?= lang('tel').' : '. $biller->phone ?></div> 
                                            <div><?= lang('email').' : '. $biller->email ?></div>   
                                        </td>
                                        <td class="text_center" style="width:20%">
                                            &nbsp;
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="border:none;">
                                <table width="100%">
                                    <tr>
                                        <td valign="bottom" style="width:40%"><hr class="hr_title"></td>
                                        <td class="text_center" style="width:20%"><span style="font-size:18px"><b><i><?= lang('delivery_note') ?></i></b></span></td>
                                        <td valign="bottom" style="width:40%"><hr class="hr_title"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <table width="100%">
                                    <tr>
                                        <td>
                                            <fieldset>
                                                <legend style="font-size:16px"><b><i><?= lang('customer') ?></i></b></legend>
                                                <table>
                                                    <tr>
                                                        <td><?= lang('customer'); ?></td>
                                                        <td>: <?= $delivery->customer; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('delivered_by'); ?></td>
                                                        <td>: <?= !empty($delivered_by) ? $delivered_by->name : ''; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('received_by'); ?></td>
                                                        <td>: <?= lang($delivery->received_by); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('address'); ?></td>
                                                        <td>: <?= $delivery->address; ?></td>
                                                    </tr>
                                                    <?php if ($delivery->note) { ?>
                                                    <tr>
                                                        <td><?= lang('note'); ?></td>
                                                        <td>: <?= $this->bpas->decode_html($delivery->note); ?></td>
                                                    </tr>
                                                    <?php } ?>
                                                </table>
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset style="margin-left:5px !important;">
                                                <legend style="font-size:16px"><b><i><?= lang('reference') ?></i></b></legend>
                                                <table>
                                                    <tr>
                                                        <td><?= lang('ref') ?></td>
                                                        <td style="text-align:left"> : <b><?= $delivery->do_reference_no ?></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('date') ?></td>
                                                        <td style="text-align:left"> : <?= $this->bpas->hrsd($delivery->date) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('sale_reference_no'); ?></td>
                                                        <td>: <?= $delivery->sale_reference_no; ?></td> 
                                                    </tr>
                                                    <tr><td>&nbsp;</td><td></td></tr>
                                                    <tr><td>&nbsp;</td><td></td></tr>
                                                    <tr><td>&nbsp;</td><td></td></tr>
                                                </table>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('no'); ?></th>
                            <th style="vertical-align:middle;"><?= lang('description'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('quantity'); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang('remark'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $r = 1;
                    foreach ($rows as $row): ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
                                if ($row->details) {
                                    echo '<br><strong>' . lang('product_details') . '</strong> ' .
                                    html_entity_decode($row->details);
                                } ?>
                            </td>
                            <td style="width: 150px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . $row->unit_name; ?></td>
                            <td style="width: 150px; text-align:center; vertical-align:middle;"></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="clearfix"></div>
            <?php if ($delivery->status == 'delivered') { ?>
            <div class="row">
                <div class="col-xs-4">
                    <p><?= lang('prepared_by'); ?>:<br> <?= $user->first_name . ' ' . $user->last_name; ?> </p>
                </div>
                <div class="col-xs-4">
                    <p><?= lang('delivered_by'); ?>:<br> <?= !empty($delivered_by) ? $delivered_by->name : ''; ?></p>
                </div>
                <div class="col-xs-4">
                    <p><?= lang('received_by'); ?>:<br> <?= $delivery->received_by; ?></p>
                </div>
            </div>
            <?php } else { ?>
            <div class="row">
                <div class="col-xs-4">
                    <p style="height:80px;"><?= lang('prepared_by'); ?>
                        : <?= $user->first_name . ' ' . $user->last_name; ?> </p>
                    <hr>
                    <p><?= lang('stamp_sign'); ?></p>
                </div>
                <div class="col-xs-4">
                    <p style="height:80px;"><?= lang('delivered_by'); ?>: </p>
                    <hr>
                    <p><?= lang('stamp_sign'); ?></p>
                </div>
                <div class="col-xs-4">
                    <p style="height:80px;"><?= lang('received_by'); ?>: </p>
                    <hr>
                    <p><?= lang('stamp_sign'); ?></p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<style>
    @media print{
        .no-print{
            display:none !important;
        }
        .tr_print{
            display:table-row !important;
        }
        .modal-dialog{
            <?= $hide_print ?>
        }
        .bg-text{
            display:block !important;
        }
        @page{
            margin: 5mm; 
        }
        body {
            -webkit-print-color-adjust: exact !important;  
            color-adjust: exact !important;         
        }
    }
    .tr_print{
        display:none;
    }
    #tbody .td_print{
        border:none !important;
        border-left:1px solid black !important;
        border-right:1px solid black !important;
        border-bottom:1px solid black !important;
    }
    .hr_title{
        border:2px solid #6c757d !important;
        margin-bottom:3px !important;
        margin-top:3px !important;
    }
    .table_item th{
        border:1px solid black !important;
        background-color : #dddddd !important;
        text-align:center !important;
        line-height:30px !important;
    }
    .table_item td{

        line-height:5px !important;
    }
    .footer_des[rowspan] {
      vertical-align: top !important;
      text-align: left !important;
      border:0px !important;
    }
    .text_center{
        text-align:center !important;
    }
    .text_left{
        text-align:left !important;
        padding-left:3px !important;
    }
    .text_right{
        text-align:right !important;
        padding-right:3px !important;
    }
    fieldset{
        -moz-border-radius: 9px !important;
        -webkit-border-radius: 15px !important;
        border-radius:9px !important;
        border:2px solid #6c757d !important;
        min-height:2px !important;
        margin-bottom : 2>px !important;
        padding-left : 2px !important;
    }
    legend{
        width: initial !important;
        margin-bottom: initial !important;
        border: initial !important;
    }
    .modal table{
        width:100% !important;
        font-size:12px !important;
        border-collapse: collapse !important;
    }
    .bg-text{
        opacity: 0.1;
        color:lightblack;
        font-size:100px;
        position:absolute;
        transform:rotate(300deg);
        -webkit-transform:rotate(300deg);
        display:none;
    }
</style>