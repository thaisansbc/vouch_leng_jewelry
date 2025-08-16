<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('reports/profit_loss_table'); ?>';
    });
</script>
<style>
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
    table tr td.purchase_tax,
    table tr td.salary_tax {
        font-weight: bold;
        font-size: 14px;
        padding: 0 60px;
    }
    .returns_header {
        font-weight: bold;
        font-size: 18px;
    }
    table tr td.expenses,
    table tr td.payroll,
    table tr td.expenses_budget,
    table tr td.purchases,
    table tr td.returns,
    table tr td.cost_of_good,
    table tr td.sales {
        font-weight: bold;
        font-size: 16px;
        padding: 0 37px;
    }
    .total_expense_background,
    .total_payroll_background,
    .total_return_background,
    .gross_profit_net_sales_background {
        background-color: #1D8471;
        color: #ffffff;
        font-weight: bold;
    }
    .profit_loss_purchase,
    .profit_loss,
    .total_expenses,
    .total_payroll,
    .expenses_header,
    .payroll_header,
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
    .text-bar, #image {
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
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-university tip" data-placement="left" title="<?= lang("billers") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('reports/profit_loss_table') ?>"><i class="fa fa-university"></i> <?= lang('billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
                                foreach ($billers as $biller) {
                                    echo '<li><a href="' . admin_url('reports/profit_loss_table/'.$start.'/'.$end.'/' . $biller->id) . '"><i class="fa fa-university"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                }
                            } elseif ($billers && $this->session->userdata('biller_id')) {
                                $biller_id = explode(',', $this->session->userdata('biller_id'));
                                foreach ($billers as $biller) {
                                    foreach ($biller_id as $key => $value) {
                                        if ($biller->id == $value) {
                                            echo '<li><a href="' . admin_url('reports/profit_loss_table/'.$start.'/'.$end.'/' . $biller->id) . '"><i class="fa fa-university"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                        }
                                    }
                                }
                            }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('view_pl_report'); ?></p>
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-8">

                        <table class="table">
                           
                            <?php 
                            $bnetsale           = $btotal_sales->total_amount - 0 - (-$bgetReturnOnSales->total_amount);
                            $bpayments_received = $btotal_payment_received->total_amount;
                            $bcost_of_good      = $btotal_sales_costing->cost;
                            $bnetpurchase       = $btotal_purchases->total_amount;

                            $bnetstore_sale     = $btotal_store_sales->total_amount;
                            $bnetstore_purchase = $btotal_store_purchases->total_amount;
                            $bnetstore_sale_purchase = $bnetstore_sale - $bnetstore_purchase;
                            //------------close-----------

                            $netsale           = $total_sales->total_amount - 0 - (-$getReturnOnSales->total_amount);
                            $payments_received = $total_payment_received->total_amount;
                            $cost_of_good      = $total_sales_costing->cost;
                            $netpurchase       = $total_purchases->total_amount;

                            $netstore_sale     = $total_store_sales->total_amount;
                            $netstore_purchase = $total_store_purchases->total_amount;
                            $netstore_sale_purchase = $netstore_sale - $netstore_purchase;

                            if ($this->Settings->profit_loss_method) {
                                $case_in_out = $netsale - $total_return_sales->total_amount - $netpurchase - $total_expenses->total_amount + $netstore_sale_purchase;
                                $profit_loss = $netsale - $cost_of_good - $total_return_sales->total_amount - $total_expenses->total_amount + $netstore_sale_purchase;

                                $bprofit_loss = $bnetsale - $bcost_of_good - $btotal_return_sales->total_amount - $btotal_expenses->total_amount + $bnetstore_sale_purchase;
                            }else{
                                $case_in_out = $payments_received - $total_return_sales->total_amount - $netpurchase - $total_expenses->total_amount + $netstore_sale_purchase;
                                $profit_loss = $payments_received - $cost_of_good - $total_return_sales->total_amount - $total_expenses->total_amount + $netstore_sale_purchase;

                                $bprofit_loss = $bpayments_received - $bcost_of_good - $btotal_return_sales->total_amount - $btotal_expenses->total_amount + $bnetstore_sale_purchase;
                            }
                            ?>
                            <tbody>
                                <tr class="gross_profit_net_sales_background">
                                    <td class="begining"><?= lang('profit_loss_beginning_of_period'); ?></td>
                                    <td class="total_amount text-right">
                                        <?php echo $this->bpas->formatMoney($bprofit_loss) ?></td>
                                </tr>

                                <tr>
                                    <td class="revenues_header"><?= lang('revenues'); ?></td>
                                    <td id="revenues"></td>
                                </tr>
                                <?php if ($this->Settings->profit_loss_method) { ?>
                                <tr>
                                    <td class="sales"><?= lang('sales'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_sales->sTotal_amount) ?></td>
                                </tr>
                                <tr>
                                    <td class="sale_tax"><?= lang('discount'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_sales->total_discount) ?></td>
                                </tr>
                                <tr>
                                    <td class="sale_tax"><?= lang('sale_tax'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_sales->tax) ?></td>
                                </tr>
                                <tr>
                                    <td class="sale_tax"><?= lang('shipping'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_sales->shipping) ?></td>
                                </tr>
                                <tr>
                                    <td class="sale_tax"><?= lang('sale_return'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($getReturnOnSales->total_amount) ?></td>
                                </tr>
                                

                                <tr class="gross_profit_net_sales_background">
                                    <td class="gross_profit_net_sales"><?= lang('gross_profit_net_sales'); ?></td>
                                    <td class="total_amount text-right">
                                        <?php echo $this->bpas->formatMoney($netsale) ?></td>
                                </tr>
                                <?php } else { ?>
                                <tr>
                                    <td class="sale_tax"><?= lang('payments_received'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_payment_received->total_amount) ?></td>
                                </tr>
                                <?php } ?>
                                <tr class="gross_profit_net_sales_background">
                                    <td class="gross_profit_net_sales"><?= lang('cost_of_good'); ?></td>
                                    <td class="total_amount text-right">
                                        <?php echo $this->bpas->formatMoney($cost_of_good) ?></td>
                                </tr>
                                <tr>
                                    <td class="returns_header"><?= lang('returns'); ?></td>
                                    
                                </tr>
                                <tr>
                                    <td class="returns"><?= lang('returns'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_return_sales->stotal_amount) ?></td>
                                </tr>
                                <tr>
                                    <td class="return_tax"><?= lang('discount'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_return_sales->total_discount) ?></td>
                                </tr>
                                <tr>
                                    <td class="return_tax"><?= lang('return_tax'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_return_sales->tax) ?></td>
                                </tr>
                                <tr>
                                    <td class="return_tax"><?= lang('shipping'); ?></td>
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_return_sales->shipping) ?></td>
                                </tr>
                                <tr class="total_return_background">
                                    <td class="total_return"><?= lang('total_return'); ?></td>                         
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_return_sales->total_amount) ?></td>
                                </tr>
                                <tr>
                                    <td class="expenses_header"><?= lang('purchases'); ?></td>
                                </tr>
                                <tr>
                                    <td class="purchases"><?= lang('purchases'); ?></td>                           
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_purchases->subtotal_amount) ?></td>
                                </tr>
                                <tr>
                                    <td class="purchase_tax"><?= lang('discount'); ?></td>
                                    <td class="text-right"><?= $this->bpas->formatMoney($total_purchases->total_discount)?></td>
                                </tr>
                                <tr>
                                    <td class="purchase_tax"><?= lang('purchase_tax'); ?></td>
                                    <td class="text-right"><?= $this->bpas->formatMoney($total_purchases->tax)?></td>
                                </tr>
                                <tr>
                                    <td class="purchase_tax"><?= lang('shipping'); ?></td>
                                    <td class="text-right"><?= $this->bpas->formatMoney($total_purchases->shipping)?></td>
                                </tr>
                                <tr class="total_expense_background">
                                    <td class="total_expenses"><?= lang('total_purchases'); ?></td>
                                    <td class="total_amount text-right">
                                        <?php echo $this->bpas->formatMoney($netpurchase) ?>
                                    </td>
                                </tr>
                                <?php if($Settings->store_sales) { ?>
                                <?php 
                                    $subtotal_amount_store_sp = (!empty($total_store_sales) ? $total_store_sales->sTotal_amount : 0) - (!empty($total_store_purchases) ? $total_store_purchases->sTotal_amount : 0);
                                    $total_discount_store_sp  = (!empty($total_store_sales) ? $total_store_sales->total_discount : 0) - (!empty($total_store_purchases) ? $total_store_purchases->total_discount : 0);
                                    $tax_store_sp             = (!empty($total_store_sales) ? $total_store_sales->tax : 0) - (!empty($total_store_purchases) ? $total_store_purchases->tax : 0);
                                    $shipping_store_sp        = (!empty($total_store_sales) ? $total_store_sales->shipping : 0) - (!empty($total_store_purchases) ? $total_store_purchases->shipping : 0);
                                ?>
                                <tr>
                                    <td class="expenses_header"><?= lang('store_sales'); ?></td>
                                </tr>
                                <tr>
                                    <td class="purchases"><?= lang('store_sales'); ?></td>                           
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($subtotal_amount_store_sp) ?></td>
                                </tr>
                                <tr>
                                    <td class="purchase_tax"><?= lang('discount'); ?></td>
                                    <td class="text-right"><?= $this->bpas->formatMoney($total_discount_store_sp)?></td>
                                </tr>
                                <tr>
                                    <td class="purchase_tax"><?= lang('purchase_tax'); ?></td>
                                    <td class="text-right"><?= $this->bpas->formatMoney($tax_store_sp)?></td>
                                </tr>
                                <tr>
                                    <td class="purchase_tax"><?= lang('shipping'); ?></td>
                                    <td class="text-right"><?= $this->bpas->formatMoney($shipping_store_sp)?></td>
                                </tr>
                                <tr class="total_expense_background">
                                    <td class="total_expenses"><?= lang('total_store_sales'); ?></td>
                                    <td class="total_amount text-right">
                                        <?php echo $this->bpas->formatMoney($netstore_sale_purchase) ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td class="expenses_header"><?= lang('expenses'); ?></td>
                                </tr>
                                <tr class="hide">
                                    <td class="expenses_budget"><?= lang('expenses_budget'); ?></td>                           
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_expenses_budget->total_amount) ?></td>
                                </tr>
                                <?php
                                if($totalexbycategories){
                                    foreach ($totalexbycategories as $excategory) {
                                    ?>
                                    <tr>
                                        <td class="purchases"><?= lang($excategory->name); ?></td>
                                        <td class="text-right"><?= $this->bpas->formatMoney($excategory->total_amount)?></td>
                                    </tr>
                                    <?php
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="payroll"><?= lang('payroll'); ?></td>                           
                                    <td class="total_amount text-right"><?= $this->bpas->formatMoney($total_payroll->total_amount) ?></td>
                                </tr>
                                <tr class="total_expense_background">
                                    <td class="total_expenses"><?= lang('total_expenses'); ?></td>
                                    <td class="total_amount text-right">
                                        <?= $this->bpas->formatMoney($total_expenses->total_amount) ?>
                                    </td>
                                </tr>
                                <tr class="profit_loss_purchase_background hide">
                                    <td class="profit_loss_purchase"><?= lang('case_in_out'); ?> <?= $Settings->store_sales ? lang('case_in_out_info_store') : lang('case_in_out_info'); ?></td>
                                    <td class="total_amount text-right"><?php echo $this->bpas->formatMoney($case_in_out) ?></td>
                                </tr>
                                <tr class="profit_loss_background">
                                    <td class="profit_loss"><?= lang('profit_loss'); ?> <?= $Settings->store_sales ? lang('profit_loss_info_store') : lang('profit_loss_info'); ?></td>       
                                    <td class="total_amount text-right"><?php echo $this->bpas->formatMoney($profit_loss) ?></td>
                                </tr>
                                <tr class="gross_profit_net_sales_background">
                                    <td class="begining"><?= lang('profit_loss_end_of_period'); ?></td>
                                    <td class="total_amount text-right">
                                        <?php echo $this->bpas->formatMoney($bprofit_loss+$profit_loss) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-2"></div>
                </div>

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
