<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('reports/yearly_profit_loss'); ?>';

        /* Date Time Picker */
        $('#datetimepicker').datetimepicker({
            timepicker: false
        });
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

    table thead tr th {
        font-size: 16px;
    }

    table tr td.sale_tax, 
    table tr td.discount,
    table tr td.sale_return,
    table tr td.return_tax,
    table tr td.purchase_tax {
        font-weight: bold;
        font-size: 14px;
        padding: 0 60px;
    }
    .returns_header {
        font-weight: bold;
        font-size: 18px;
    }
    table tr td.expenses,
    table tr td.purchases,
    table tr td.returns,
    table tr td.cost_of_good,
    table tr td.sales {
        font-weight: bold;
        font-size: 16px;
        padding: 0 37px;
    }
    .total_expense_background,
    .total_return_background,
    .gross_profit_net_sales_background {
        background-color: #1D8471;
        color: #ffffff;
        font-weight: bold;
    }
    .profit_loss_purchase,
    .profit_loss,
    .total_expenses,
    .expenses_header,
    .total_return,
    .gross_profit_net_sales,
    .revenues_header,
    .total_amount {
        font-weight: bold;
        font-size: 16px;
    }
    .profit_loss_purchase_background,
    .profit_loss_background {
        background-color: #3F89C8;
        color: #ffffff;
        font-weight: bold;
    }
    .text-bar,
    #image {
        color: #1D8471;
    }
    #revenues {
        margin-left: 100px;
    }
    .introtext {
        color: #1D8471;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="text-bar"><i class="fa-fw fa fa-bars"></i><?= lang('yearly_profit_loss'); ?></h2>
        <div class="box-icon">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text"
                               value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                               id="datetimepicker" class="form-control">
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
                <p class="introtext"><?= lang('view_ypl_report'); ?></p>

                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="table-header"><?= lang('january'); ?></th>
                            <th class="table-header"><?= lang('february'); ?></th>
                            <th class="table-header"><?= lang('march'); ?></th>
                            <th class="table-header"><?= lang('april'); ?></th>
                            <th class="table-header"><?= lang('may'); ?></th>
                            <th class="table-header"><?= lang('june'); ?></th>
                            <th class="table-header"><?= lang('july'); ?></th>
                            <th class="table-header"><?= lang('august'); ?></th>
                            <th class="table-header"><?= lang('september'); ?></th>
                            <th class="table-header"><?= lang('october'); ?></th>
                            <th class="table-header"><?= lang('november'); ?></th>
                            <th class="table-header"><?= lang('december'); ?></th>
                            <th class="table-header"><?= lang('total_amount'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="revenues_header"><?= lang('revenues'); ?></td>
                            <td id="revenues"></td>
                        </tr>
                        <tr>
                            <td class="sales"><?= lang('sales'); ?></td>
                            <td><?= $this->bpas->formatMoney($total_sales->total_amount) ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney($total_sales->total_amount) ?></td>
                        </tr>
                        <tr>
                            <td class="sale_tax"><?= lang('sale_tax'); ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr>
                            <td class="sale_return"><?= lang('sale_return'); ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr>
                            <td class="discount"><?= lang('discount'); ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr>
                            <td class="cost_of_good"><?= lang('cost_of_good'); ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr class="gross_profit_net_sales_background">
                            <td class="gross_profit_net_sales"><?= lang('gross_profit_net_sales'); ?></td>
                            <td class="gross_profit_net_sales"><?= $this->bpas->formatMoney(($warehouse_report['total_sales']->total_amount) - ($warehouse_report['total_purchases']->total_amount) - ($warehouse_report['total_returns']->total_amount)) ?></td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="gross_profit_net_sales">0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney(($warehouse_report['total_sales']->total_amount) - ($warehouse_report['total_purchases']->total_amount) - ($warehouse_report['total_returns']->total_amount)) ?></td>
                        </tr>
                        <tr>
                            <td class="returns_header"><?= lang('returns'); ?></td>
                            
                        </tr>
                        <tr>
                            <td class="returns"><?= lang('returns'); ?></td>
                            <td><?= $this->bpas->formatMoney($total_return_sales->total_amount) ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney($total_return_sales->total_amount) ?></td>
                        </tr>
                        <tr>
                            <td class="return_tax"><?= lang('return_tax'); ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr class="total_return_background">
                            <td class="total_return"><?= lang('total_return'); ?></td>
                            <td class="total_return"><?= $this->bpas->formatMoney($total_return_sales->total_amount) ?></td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_return">0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney($total_return_sales->total_amount) ?></td>
                        </tr>
                        <tr>
                            <td class="expenses_header"><?= lang('expenses'); ?></td>
                        </tr>
                        <tr>
                            <td class="purchases"><?= lang('purchases'); ?></td>
                            <td><?= $this->bpas->formatMoney($total_purchases->total_amount) ?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney($total_purchases->total_amount) ?></td>
                        </tr>
                        <tr>
                            <td class="purchase_tax"><?= lang('purchase_tax'); ?></td>
                            <td><?= $this->bpas->formatMoney($total_purchases->paid)  ?>-<?= $this->bpas->formatMoney($total_purchases->tax)?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr>
                            <td class="expenses"><?= lang('expenses'); ?></td>
                            <td><?= $this->bpas->formatMoney($total_expenses->total_amount)?></td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td>0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney($total_expenses->total_amount) ?></td>
                        </tr>
                        <tr class="total_expense_background">
                            <td class="total_expenses"><?= lang('total_expenses'); ?></td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_expenses">0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr class="profit_loss_purchase_background">
                            <td class="profit_loss_purchase"><?= lang('case_in_out'); ?></td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="profit_loss_purchase">0.00</td>
                            <td class="total_amount">0.00</td>
                        </tr>
                        <tr class="profit_loss_background">
                            <td class="profit_loss"><?= lang('profit_loss'); ?></td>
                            <td class="profit_loss"><?= $this->bpas->formatMoney($total_sales->total_amount - $total_purchases->total_amount) ?></td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="profit_loss">0.00</td>
                            <td class="total_amount"><?= $this->bpas->formatMoney($total_sales->total_amount - $total_purchases->total_amount) ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/jquery.js"></script>
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
