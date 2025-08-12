<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-2">&nbsp;</div>
                    <div class="col-sm-8">
                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-shopping-cart margin-right-sm"></i> <?= lang('payments'); ?>
                            </div>
                            <div class="panel-body">
                                                <div class="checkbox">
                                                <label style="display: inline-block; width: auto;">
                                                        <input type="radio" name="currency" value="usd" checked>
                                                        <span>
                                                            <i class="margin-right-md"></i> $<?= lang('USD') ?>
                                                        </span>
                                                    </label>
                                                    <label style="display: inline-block; width: auto;">
                                                        <input type="radio" name="currency" value="riel">
                                                        <span>
                                                            <i class="margin-right-md"></i> ៛<?= lang('Riel') ?>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="form-group all">
                                                    <?= lang('price', 'price') ?>
                                                    <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] :''), 'class="form-control tip" required="required"') ?>
                                                </div>
                                                <div class="form-group all">
                                                    <?= lang('tip', 'tip') ?>
                                                    <?= form_input('tip', (isset($_POST['tip']) ? $_POST['tip'] :''), 'class="form-control"') ?>
                                                </div>
                                                <hr>
                                                <h5 class="hide"><strong><?= lang('payment_method'); ?></strong></h5>
                                                <div class="checkbox bg">
                                                    <label style="display: inline-block; width: auto;">
                                                        <input type="radio" name="payment_method" value="aba" required="required">
                                                        <span>
                                                            <img width="50" src="<?= base_url('assets/images/payments/aba.png'); ?>" alt=""> <?= lang('ABA_Pay') ?>
                                                        </span>
                                                    </label>
                                                    <label style="display: inline-block; width: auto;">
                                                        <input type="radio" name="payment_method" value="aba" required="required">
                                                        <span>
                                                            <img width="50" src="<?= base_url('assets/images/payments/Acleda.png'); ?>" alt=""> <?= lang('ACLEDA') ?>
                                                        </span>
                                                    </label>
                                                    <label style="display: inline-block; width: auto;">
                                                        <input type="radio" name="payment_method" value="aba" required="required">
                                                        <span>
                                                            <img width="50" src="<?= base_url('assets/images/payments/PiPay.png'); ?>" alt=""> <?= lang('PiPay') ?>
                                                        </span>
                                                    </label>
                                                    <label style="display: inline-block; width: auto;">
                                                        <input type="radio" name="payment_method" value="aba" required="required">
                                                        <span>
                                                            <img width="50" src="<?= base_url('assets/images/payments/wing.png'); ?>" alt=""> <?= lang('WING') ?>
                                                        </span>
                                                    </label>
                                                </div>
                                                <hr>
                                                <div class="form-group hide">
                                                    <?= lang('comment_any', 'comment'); ?>
                                                    <?= form_textarea('comment', set_value('comment'), 'class="form-control" id="comment" style="height:100px;"'); ?>
                                                </div>
                                                <?php
                                               
                                                    echo form_submit('add_order', lang('continue'), 'class="btn btn-theme"');
                                              
                                               
                                                echo form_close();
                                          
                                            ?>
                                       
                      

                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4 hide">
                        <div id="sticky-con" class="margin-top-lg">
                            <div class="panel panel-default">
                                <div class="panel-heading text-bold">
                                    <i class="fa fa-shopping-cart margin-right-sm"></i> <?= lang('totals'); ?>
                                </div>
                                <div class="panel-body">
                                    <?php
                                    $total     = $this->bpas->convertMoney($this->cart->total(), false, false);
                                    $shipping  = $this->bpas->convertMoney($this->cart->shipping(), false, false);
                                    $order_tax = $this->bpas->convertMoney($this->cart->order_tax(), false, false);
                                    ?>
                                    <table class="table table-striped table-borderless cart-totals margin-bottom-no">
                                        <tr>
                                            <td><?= lang('total_w_o_tax'); ?></td>
                                            <td class="text-right"><?= $this->bpas->convertMoney($this->cart->total() - $this->cart->total_item_tax()); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('product_tax'); ?></td>
                                            <td class="text-right"><?= $this->bpas->convertMoney($this->cart->total_item_tax()); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('total'); ?></td>
                                            <td class="text-right"><?= $this->bpas->formatMoney($total, $selected_currency->symbol); ?></td>
                                        </tr>
                                        <?php if ($Settings->tax2 !== false) {
                                        echo '<tr><td>' . lang('order_tax') . '</td><td class="text-right">' . $this->bpas->formatMoney($order_tax, $selected_currency->symbol) . '</td></tr>';
                                    } ?>
                                        <tr>
                                            <td><?= lang('shipping'); ?> *</td>
                                            <td class="text-right"><?= $this->bpas->formatMoney($shipping, $selected_currency->symbol); ?></td>
                                        </tr>
                                        <tr><td colspan="2"></td></tr>
                                        <tr class="active text-bold">
                                            <td><?= lang('grand_total'); ?></td>
                                            <td class="text-right"><?= $this->bpas->formatMoney(($this->bpas->formatDecimal($total) + $this->bpas->formatDecimal($order_tax) + $this->bpas->formatDecimal($shipping)), $selected_currency->symbol); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>
