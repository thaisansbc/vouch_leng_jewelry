<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('reports/profit_loss'); ?>';
    });
</script>
<style>
@media print {
    .fa {
        color: #EEE;
        display: none;
    }
    .small-box {
        border: 1px solid #CCC;
    }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('profit_loss'); ?></h2>

        <div class="box-icon">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text"
                               value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                               id="daterange" class="form-control">
                        <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('view_pl_report'); ?></p>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="col-sm-4">
                            <div class="small-box padding1010 borange">
                                <h4 class="bold"><?= lang('purchases') ?></h4>
                                <i class="icon fa fa-star"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_purchases->total_amount) ?></h3>

                                <p class="bold"><?= $total_purchases->total . ' ' . lang('purchases') ?> </p>

                                <p><?= $this->bpas->formatMoney($total_purchases->paid) . ' ' . lang('paid') ?>
                                    & <?= $this->bpas->formatMoney($total_purchases->tax) . ' ' . lang('tax') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bdarkGreen">
                                <h4 class="bold"><?= lang('sales') ?></h4>
                                <i class="icon fa fa-heart"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_sales->sTotal_amount) ?></h3>

                                <p class="bold"><?= $total_sales->total . ' ' . lang('sales') ?> </p>

                                <p><?= $this->bpas->formatMoney($total_sales->paid) . ' ' . lang('paid') ?>
                                    & <?= $this->bpas->formatMoney($total_sales->tax) . ' ' . lang('tax') ?> </p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bdarkGreen">
                                <h4 class="bold"><?= lang('discount') ?></h4>
                                <i class="icon fa fa-heart"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_discounts->total_amount) ?></h3>

                                <p class="bold"><?= $total_sales->total . ' ' . lang('sales') ?> </p>
                                <p>&nbsp;</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bred">
                                <h4 class="bold"><?= lang('returns') ?></h4>
                                <i class="icon fa fa-random"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_return_sales->total_amount) ?></h3>

                                <p class="bold"><?= $total_return_sales->total . ' ' . lang('returns') ?> </p>

                                <p><?= $this->bpas->formatMoney($total_return_sales->tax) . ' ' . lang('tax') ?> </p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bdarkGreen">
                                <h4 class="bold"><?= lang('payments_received') ?></h4>
                                <i class="icon fa fa-usd"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_received->total_amount) ?></h3>

                                <p class="bold"><?= $total_received->total . ' ' . lang('received') ?> </p>

                                <p><?= $this->bpas->formatMoney($total_received_cash->total_amount) . ' ' . lang('cash') ?>
                                    , <?= $this->bpas->formatMoney($total_received_cc->total_amount) . ' ' . lang('CC') ?>
                                    , <?= $this->bpas->formatMoney($total_received_cheque->total_amount) . ' ' . lang('cheque') ?>
                                    , <?= $this->bpas->formatMoney($total_received_ppp->total_amount) . ' ' . lang('paypal_pro') ?>
                                    , <?= $this->bpas->formatMoney($total_received_stripe->total_amount) . ' ' . lang('stripe') ?> </p>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="small-box padding1010 borange">
                                <h4 class="bold"><?= lang('payments_sent') ?></h4>
                                <i class="icon fa fa-usd"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_paid->total_amount) ?></h3>

                                <p><?= $total_paid->total . ' ' . lang('sent') ?></p>

                                <p>&nbsp;</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bpurple">
                                <h4 class="bold"><?= lang('expenses') ?></h4>
                                <i class="icon fa fa-usd"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_expenses->total_amount) ?></h3>

                                <p class="bold"><?= $total_expenses->total . ' ' . lang('expenses') ?></p>

                                <p>&nbsp;</p>
                            </div>
                        </div>
                    </div>
                </div>
             
                <div class="row hide">
                    <div class="col-sm-12">
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bred">
                                <h4 class="bold"><?= lang('profit_loss') ?></h4>
                                <i class="icon fa fa-money"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_sales->total_amount - $total_purchases->total_amount) ?></h3>

                                <p><?= $this->bpas->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                                    - <?= $this->bpas->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="small-box padding1010 bpink">
                                <h4 class="bold"><?= lang('profit_loss') ?></h4>
                                <i class="icon fa fa-money"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_sales->total_amount - $total_purchases->total_amount - $total_sales->tax) ?></h3>

                                <p><?= $this->bpas->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                                    - <?= $this->bpas->formatMoney($total_sales->tax) . ' ' . lang('tax') ?>
                                    - <?= $this->bpas->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?> </p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="small-box padding1010 bblue">
                                <h4 class="bold"><?= lang('profit_loss') ?></h4>
                                <i class="icon fa fa-money"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney(($total_sales->total_amount - $total_sales->tax) - ($total_purchases->total_amount - $total_purchases->tax)) ?></h3>

                                <p>(<?= $this->bpas->formatMoney($total_sales->total_amount) . ' ' . lang('sales') ?>
                                    - <?= $this->bpas->formatMoney($total_sales->tax) . ' ' . lang('tax') ?>) -
                                    (<?= $this->bpas->formatMoney($total_purchases->total_amount) . ' ' . lang('purchases') ?>
                                    - <?= $this->bpas->formatMoney($total_purchases->tax) . ' ' . lang('tax') ?>)</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row hide">
                    <div class="col-sm-12">
                        <div class="col-sm-12">
                            <div class="small-box padding1010 bmGreen">
                                <h4 class="bold"><?= lang('payments') ?></h4>
                                <i class="icon fa fa-pie-chart"></i>

                                <h3 class="bold"><?= $this->bpas->formatMoney($total_received->total_amount - (0 - $total_returned->total_amount) - $total_paid->total_amount - $total_expenses->total_amount - ($total_return_sales ? $total_return_sales->total_amount : 0)) ?></h3>

                                <p class="bold"><?= $this->bpas->formatMoney($total_received->total_amount) . ' ' . lang('received') ?>
                                    - <?= $this->bpas->formatMoney(0 - $total_returned->total_amount) . ' ' . lang('refund') ?>
                                    - <?= $this->bpas->formatMoney($total_paid->total_amount) . ' ' . lang('sent') ?>
                                    - <?= $this->bpas->formatMoney($total_expenses->total_amount) . ' ' . lang('expenses') ?>
                                    - <?= $this->bpas->formatMoney($total_return_sales->total_amount) . ' ' . lang('returns') ?>
                                    </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="col-sm-12">
                            <div class="small-box padding1010 bmGreen">
                                <h4 class="bold"><?= lang('profit_loss') ?></h4>
                                <i class="icon fa fa-pie-chart"></i>

                                <h3 class="bold">
                                    <?= $this->bpas->formatMoney($total_sales->sTotal_amount - (0 - $total_returned->total_amount) - $total_paid->total_amount - $total_expenses->total_amount - ($total_return_sales ? $total_return_sales->total_amount : 0) - $product_cost - ($total_discounts?$total_discounts->total_amount:0)) ?></h3>

                                <p class="bold"><?= $this->bpas->formatMoney($total_sales->sTotal_amount) . ' ' . lang('sales') ?>
                                    - <?= $this->bpas->formatMoney(0 - $total_returned->total_amount) . ' ' . lang('refund') ?>
                                    - <?= $this->bpas->formatMoney($total_paid->total_amount) . ' ' . lang('sent') ?>
                                    - <?= $this->bpas->formatMoney($total_expenses->total_amount) . ' ' . lang('expenses') ?>
                                    - <?= $this->bpas->formatMoney($total_return_sales->total_amount) . ' ' . lang('returns') ?>
                                    - <?= $this->bpas->formatMoney($product_cost) . ' ' . lang('cost') ?>
                                    - <?= $this->bpas->formatMoney($total_discounts->total_amount) . ' ' . lang('discount') ?>
                                    </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!empty($warehouses_report)) {
                    foreach ($warehouses_report as $warehouse_report) { ?>
                    <div class="col-sm-4">
                        <div class="small-box padding1010 bblue">
                            <h4 class="bold"><?= $warehouse_report['warehouse']->name.' ('.$warehouse_report['warehouse']->code.')'; ?></h4>
                            <i class="icon fa fa-money"></i>
                            <h3 class="bold">
                                <?= $this->bpas->formatMoney($warehouse_report['total_sales']->sTotal_amount)?></h3>
                            <p>
                            <?= $this->bpas->formatMoney($warehouse_report['total_sales']->sTotal_amount) . ' ' . lang('sales'); ?>
                                - <?= $this->bpas->formatMoney($warehouse_report['total_sales']->tax) . ' ' . lang('tax') ?>
                                = <?= $this->bpas->formatMoney($warehouse_report['total_sales']->sTotal_amount-$warehouse_report['total_sales']->tax).' '.lang('net_sales'); ?>
                                </p>
                            <p>
                            <?= lang('sales'); ?>
                            </p>
                            <hr style="border-color: rgba(255, 255, 255, 0.4);">
                            <h3 class="bold">
                                <?= '<h3 class="bold">'.$this->bpas->formatMoney($warehouse_report['total_discounts']).'</h3>'; ?>
                                <p>
                                <?= lang('discount'); ?>
                                </p>
                                <hr style="border-color: rgba(255, 255, 255, 0.4);">
                                <?= '<h3 class="bold">'.$this->bpas->formatMoney($warehouse_report['total_cost']).'</h3>'; ?>
                                <p>
                                <?= lang('cost'); ?>
                                </p>
                                <hr style="border-color: rgba(255, 255, 255, 0.4);">
                                <?= '<h3 class="bold">'.$this->bpas->formatMoney($warehouse_report['total_expenses']->total_amount).'</h3>'; ?>
                                <p>
                                <?= $warehouse_report['total_expenses']->total.' '.lang('expenses'); ?>
                                </p>
                              
                                
                                <hr style="border-color: rgba(255, 255, 255, 0.4);">
                                <?= '<h3 class="bold">'.$this->bpas->formatMoney(($warehouse_report['total_sales']->total_amount
                                    - $warehouse_report['total_sales']->tax)
                                    - $warehouse_report['total_cost'] 
                                    - $warehouse_report['total_expenses']->total_amount
                                    - ($warehouse_report['total_returns']->total_amount-$warehouse_report['total_returns']->tax)
                                ).'</h3>'; ?>
                                <p>
                                <?= lang('sales').' & '.lang('refund').' - '.lang('cost').' - '. lang('expenses').' - '. lang('returns'); ?>
                                </p>

                        </div>
                    </div>
                <?php }
                
                }?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/profit_loss_pdf')?>/" + encodeURIComponent('<?=$start?>') + "/" + encodeURIComponent('<?=$end?>');
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>
