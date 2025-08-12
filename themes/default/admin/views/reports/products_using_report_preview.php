<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css" media="all">
    table {
        font-size: 13px !important;
    }
    @media print {
        table {
            font-size: 13px !important;
        }
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("products_using_report"); ?></h2>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <center><div style="font-size: 18px;font-weight: bold;"><?= lang('products_using_report'); ?></div></center>
                <center><div style="font-size: 16px;"><?= $this->bpas->fldc($start_date).' To '.$this->bpas->fldc($end_date);?></div></center><br>
                <div class="table-responsive" style="table-layout: fixed;">
                    <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                        <tr>
                            <th><?= lang('no'); ?></th>
                            <th style="width: 10% !important;"><?= lang('product_code'); ?></th>
                            <th style="width: 15% !important;"><?= lang('product_name'); ?></th>
                            <th><?= lang('beginning'); ?></th>
                            <th><?= lang('purchased'); ?></th>
                            <th><?= lang('using_stock'); ?></th>
                            <th><?= lang('return_stock'); ?></th>
                            <th><?= lang('stock_in_hand'); ?></th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <table width="100%" border="1">
                                    <tr>
                                        <td style="width: 33%; text-align: center;">New</td>
                                        <td style="width: 33%; text-align: center;">Old</td>
                                        <td style="width: 33%; text-align: center;">Broken</td>
                                    </tr>
                                </table>
                            </th>
                            <th>
                                <table width="100%" border="1">
                                    <tr>
                                        <td style="width: 33%; text-align: center;">New</td>
                                        <td style="width: 33%; text-align: center;">Old</td>
                                        <td style="width: 33%; text-align: center;">Broken</td>
                                    </tr>
                                </table>
                            </th>
                            <th>
                                <table width="100%" border="1">
                                    <tr>
                                        <td style="width: 33%; text-align: center;">New</td>
                                        <td style="width: 33%; text-align: center;">Old</td>
                                        <td style="width: 33%; text-align: center;">Broken</td>
                                    </tr>
                                </table>
                            </th>
                            <th>
                                <table width="100%" border="1">
                                    <tr>
                                        <td style="width: 33%; text-align: center;">New</td>
                                        <td style="width: 33%; text-align: center;">Old</td>
                                        <td style="width: 33%; text-align: center;">Broken</td>
                                    </tr>
                                </table>
                            </th>
                            <th>
                                <table width="100%" border="1">
                                    <tr>
                                        <td style="width: 33%; text-align: center;">New</td>
                                        <td style="width: 33%; text-align: center;">Old</td>
                                        <td style="width: 33%; text-align: center;">Broken</td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <?php 
                        if (!empty($rows)) {
                            $i=1;
                            foreach ($rows as $row) { ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $row->code; ?></td> 
                                    <td><?= $row->name; ?></td> 
                                    <?php if (!empty($row->variant)) { ?>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->n_BeginningQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->o_BeginningQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->b_BeginningQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->n_PurchasedQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->o_PurchasedQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->b_PurchasedQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->n_UsingStockQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->o_UsingStockQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->b_UsingStockQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->n_ReturnStockQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->o_ReturnStockQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->b_ReturnStockQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->n_BalanceQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->o_BalanceQty); ?></td>
                                                    <td style="width: 33%; padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->b_BalanceQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                    <?php } else { ?>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->BeginningQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->PurchasedQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->UsingStockQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->ReturnStockQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: right;">
                                            <table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">
                                                <tr>
                                                    <td style="padding-right: 5px; text-align: right;"><?= $this->bpas->formatDecimal($row->BalanceQty); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                    <?php } ?>    
                                </tr>
                            <?php $i++; }
                        }?>
                    </table>
                </div>         
            </div>
        </div>
    </div>
</div>