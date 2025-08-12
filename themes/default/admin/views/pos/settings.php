<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cogs"></i><?= lang('pos_settings'); ?></h2>
        <?php if (isset($pos->purchase_code) && !empty($pos->purchase_code) && $pos->purchase_code != 'purchase_code') {
        ?>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <!-- <li class="dropdown"><a href="<?= admin_url('pos/updates') ?>" class="toggle_down"><i
                    class="icon fa fa-upload"></i><span class="padding-right-10"><?= lang('updates'); ?></span></a>
                </li> -->
                </ul>
            </div>
        <?php
        } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos_setting'];
                echo admin_form_open('pos/settings', $attrib);
                ?>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('pos_config') ?></legend>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('pro_limit', 'limit'); ?>
                            <?= form_input('pro_limit', $pos->pro_limit, 'class="form-control" id="limit" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('delete_code', 'pin_code'); ?>
                            <?= form_password('pin_code', $pos->pin_code, 'class="form-control" pattern="[0-9]{4,8}"id="pin_code"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('default_category', 'default_category'); ?>
                            <?php
                            $ct[''] = lang('select') . ' ' . lang('default_category');
                            foreach ($categories as $catrgory) {
                                $ct[$catrgory->id] = $catrgory->name;
                            }
                            echo form_dropdown('category', $ct, $pos->default_category, 'class="form-control" id="default_category" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('default_biller', 'default_biller'); ?>
                            <?php
                            $bl[0] = '';
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            if (isset($_POST['biller'])) {
                                $biller = $_POST['biller'];
                            } else {
                                $biller = '';
                            }
                            echo form_dropdown('biller', $bl, $pos->default_biller, 'class="form-control" id="default_biller" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="warehouse"><?= lang('default_warehouse'); ?></label>

                            <div class="controls"> 
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                }
                                echo form_dropdown('warehouse', $wh, $pos->default_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('default_customer', 'customer1'); ?>
                            <?= form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $pos->default_customer), 'id="customer1" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control" style="width:100%;"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('display_time', 'display_time'); ?>
                            <?php
                            $yn = ['1' => lang('yes'), '0' => lang('no')];
                            echo form_dropdown('display_time', $yn, $pos->display_time, 'class="form-control" id="display_time" required="required"');
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('onscreen_keyboard', 'keyboard'); ?>
                            <?php
                            echo form_dropdown('keyboard', $yn, $pos->keyboard, 'class="form-control" id="keyboard" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('product_button_color', 'product_button_color'); ?>
                            <?php $col = ['default' => lang('default'), 'primary' => lang('primary'), 'info' => lang('info'), 'warning' => lang('warning'), 'danger' => lang('danger')];
                            echo form_dropdown('product_button_color', $col, $pos->product_button_color, 'class="form-control" id="product_button_color" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('tooltips', 'tooltips'); ?>
                            <?php
                            echo form_dropdown('tooltips', $yn, $pos->tooltips, 'class="form-control" id="tooltips" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('rounding', 'rounding'); ?>
                            <?php
                            $rnd = ['0' => lang('disable'), '1' => lang('to_nearest_005'), '2' => lang('to_nearest_050'), '3' => lang('to_nearest_number'), '4' => lang('to_next_number')];
                            echo form_dropdown('rounding', $rnd, $pos->rounding, 'class="form-control" id="rounding" required="required"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('item_order', 'item_order'); ?>
                            <?php $oopts = [0 => lang('default'), 1 => lang('category')]; ?>
                            <?= form_dropdown('item_order', $oopts, $pos->item_order, 'class="form-control" id="item_order" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('after_sale_page', 'after_sale_page'); ?>
                            <?php $popts = [0 => lang('receipt'), 1 => lang('pos')]; ?>
                            <?= form_dropdown('after_sale_page', $popts, $pos->after_sale_page, 'class="form-control" id="after_sale_page" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('display_customer_details', 'customer_details'); ?>
                            <?php $popts = [0 => lang('no'), 1 => lang('yes')]; ?>
                            <?= form_dropdown('customer_details', $popts, $pos->customer_details, 'class="form-control" id="customer_details" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('pos_type', 'pos_type'); ?>
                            <?php $postype = ['pos' => lang('pos'), 'table' => lang('table'), 'room' => lang('room')]; ?>
                            <?= form_dropdown('pos_type', $postype, $pos->pos_type, 'class="form-control" id="pos_type" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('separate', 'separate'); ?>
                            <?php $separate = [0 => lang('no'), 1 => lang('yes')]; ?>
                            <?= form_dropdown('separate', $separate, $pos->separate, 'class="form-control" id="separate" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('show_category', 'show_category'); ?>
                            <?php
                               $get_fields_categories = $this->site->getcustomfield('Show Category');
                                        $field_categories = [''];
                                        if (!empty($get_fields_categories)) {
                                            foreach ($get_fields_categories as $field_id) {
                                                $field_categories[$field_id->name] = $field_id->name;
                                            }
                                        }
                                        ?>
                        <?= form_dropdown('show_category', $field_categories, $pos->show_category, 'class="form-control" id="show_category"'); ?>
                          <!--   <?php $show_category = [0 => lang('1'), 1 => lang('2'), 2 => lang('3'), 3 => lang('4'), 4 => lang('5'), 5 => lang('6'), 6 => lang('7'), 7 => lang('8'), 8 => lang('9'), 9 => lang('10'), 10 => lang('11'), 11 => lang('12'), 12 => lang('13'), 13 => lang('14'), 14 => lang('15')]; ?>
                            <?= form_dropdown('show_category', $show_category, $pos->show_category, 'class="form-control" id="show_category"'); ?> -->
                        </div>
                    </div>
                <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('password/reason', 'password/reason'); ?>
                            <?php $pass_reason = [0 => lang('pin_code'), 1 => lang('reason')]; ?>
                            <?= form_dropdown('password_reason', $pass_reason, $pos->password_reason, 'class="form-control" id="password_reason"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('sale_due', 'sale_due'); ?>
                            <?php $sale_due = [0 => lang('no'), 1 => lang('yes')]; ?>
                            <?= form_dropdown('sale_due', $sale_due, $pos->sale_due, 'class="form-control" id="sale_due" required="required"'); ?>
                        </div>
                    </div>
                     <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('show_categories', 'show_categories'); ?>
                            <?php $show_categories = [0 => lang('show_top'), 1 => lang('show_right')]; ?>
                            <?= form_dropdown('show_categories', $show_categories, $pos->show_categories, 'class="form-control" id="show_categories"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                        <?= lang('item_qty', 'item_qty'); ?>
                        <?php $show_item = [1 => lang('all'), 0 => lang('in_stock')]; ?>
                        <?= form_dropdown('show_item', $show_item, $pos->show_item, 'class="form-control" id="show_item"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                        <?= lang('show_quantity', 'show_quantity'); ?>
                        <?php $show_qty = [1 => lang('qty_show'), 0 => lang('qty_hide')]; ?>
                        <?= form_dropdown('show_qty', $show_qty, $pos->show_qty, 'class="form-control" id="show_qty"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('member_card', 'member_card'); ?>
                            <?php $member_card = [0 => lang('no'), 1 => lang('yes')]; ?>
                            <?= form_dropdown('member_card', $member_card, $pos->member_card, 'class="form-control" id="member_card" required="required"'); ?>
                        </div>
                    </div> 
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('coupon_card', 'coupon_card'); ?>
                            <?php $coupon_card = [0 => lang('no'), 1 => lang('yes')]; ?>
                            <?= form_dropdown('coupon_card', $coupon_card, $pos->coupon_card, 'class="form-control" id="coupon_card" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('show_close_register_products', 'close_register_products'); ?>
                            <?php $popts = [0 => lang('no'), 1 => lang('yes')]; ?>
                            <?= form_dropdown('show_close_register_products', $popts, $pos->show_close_register_products, 'class="form-control" id="customer_details" required="required"'); ?>
                        </div>
                    </div>
                </fieldset>
              
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('pos_printing') ?></legend>

                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('printing', 'remote_printing'); ?>
                            <?php
                            $opts = [0 => lang('local_install'), 1 => lang('web_browser_print'), 3 => lang('php_pos_print_app')];
                            ?>
                            <?= form_dropdown('remote_printing', $opts, $pos->remote_printing, 'class="form-control select2" id="remote_printing" style="width:100%;" required="required"'); ?>
                            <span class="help-block"><?= lang('print_recommandations'); ?></span>
                            <span class="help-block"><?= lang('download') . ': <a href="https://github.com/Tecdiary/ppp" target="_blank">PHP Pos Print Server</a>'; ?></span>
                            <?php if (DEMO) {
                            ?>
                                <span class="help-block">On demo, you can test web printing only.</span>
                            <?php
                            } ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="printers">

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('auto_print', 'auto_print'); ?> <strong>*</strong>
                                <?= form_dropdown('auto_print', $yn, $pos->auto_print, 'class="form-control select2" id="auto_print" style="width:100%;"'); ?>
                            </div>
                        </div>

                        <div class="ppp">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('use_local_printers', 'local_printers'); ?>
                                    <?= form_dropdown('local_printers', $yn, set_value('local_printers', $pos->local_printers), 'class="form-control tip" id="local_printers"  required="required"'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="lp">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('receipt_printer', 'receipt_printer'); ?> <strong>*</strong>
                                    <?php
                                    $printer_opts = [];
                                    if (!empty($printers)) {
                                        foreach ($printers as $printer) {
                                            $printer_opts[$printer->id] = $printer->title;
                                        }
                                    }
                                    ?>
                                    <?= form_dropdown('receipt_printer', $printer_opts, $pos->printer, 'class="form-control select2" id="receipt_printer" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('order_printers', 'order_printers'); ?> <strong>*</strong>
                                    <?= form_dropdown('order_printers[]', $printer_opts, '', 'multiple class="form-control select2" id="order_printers" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('cash_drawer_codes', 'cash_drawer_codes'); ?>
                                    <?= form_input('cash_drawer_codes', $pos->cash_drawer_codes, 'class="form-control" id="cash_drawer_codes" placeholder="\x1C"'); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="form-group">
                        <?= lang('print_product', 'print_product'); ?>
                        <?php
                            $mbiller_id = explode(',', $pos->print_product);
                            // $st[''] = lang('select') . ' ' . lang('default_stocktype');
                            foreach ($stocktype as $stocktypes) 
                            {
                                $st[$stocktypes->id] = ucfirst($stocktypes->name);
                            }
                            echo form_dropdown('print_product[]', $st, $mbiller_id, 'id="print_product" class="form-control select"  multiple="multiple"');
                        ?>
                    </div>



                </fieldset>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('custom_fileds') ?></legend>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_title1', 'tcf1'); ?>
                            <?= form_input('cf_title1', $pos->cf_title1, 'class="form-control tip" id="tcf1"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_value1', 'vcf1'); ?>
                            <?= form_input('cf_value1', $pos->cf_value1, 'class="form-control tip" id="vcf1"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_title2', 'tcf2'); ?>
                            <?= form_input('cf_title2', $pos->cf_title2, 'class="form-control tip" id="tcf2"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cf_value2', 'vcf2'); ?>
                            <?= form_input('cf_value2', $pos->cf_value2, 'class="form-control tip" id="vcf2"'); ?>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('shortcuts') ?></legend>
                    <p><?= lang('shortcut_heading') ?></p>

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('focus_add_item', 'focus_add_item'); ?>
                            <?= form_input('focus_add_item', $pos->focus_add_item, 'class="form-control tip" id="focus_add_item"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('add_manual_product', 'add_manual_product'); ?>
                            <?= form_input('add_manual_product', $pos->add_manual_product, 'class="form-control tip" id="add_manual_product"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('customer_selection', 'customer_selection'); ?>
                            <?= form_input('customer_selection', $pos->customer_selection, 'class="form-control tip" id="customer_selection"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('add_customer', 'add_customer'); ?>
                            <?= form_input('add_customer', $pos->add_customer, 'class="form-control tip" id="add_customer"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('toggle_category_slider', 'toggle_category_slider'); ?>
                            <?= form_input('toggle_category_slider', $pos->toggle_category_slider, 'class="form-control tip" id="toggle_category_slider"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('toggle_subcategory_slider', 'toggle_subcategory_slider'); ?>
                            <?= form_input('toggle_subcategory_slider', $pos->toggle_subcategory_slider, 'class="form-control tip" id="toggle_subcategory_slider"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('toggle_brands_slider', 'toggle_brands_slider'); ?>
                            <?= form_input('toggle_brands_slider', $pos->toggle_brands_slider, 'class="form-control tip" id="toggle_brands_slider"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('cancel_sale', 'cancel_sale'); ?>
                            <?= form_input('cancel_sale', $pos->cancel_sale, 'class="form-control tip" id="cancel_sale"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('suspend_sale', 'suspend_sale'); ?>
                            <?= form_input('suspend_sale', $pos->suspend_sale, 'class="form-control tip" id="suspend_sale"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('print_items_list', 'print_items_list'); ?>
                            <?= form_input('print_items_list', $pos->print_items_list, 'class="form-control tip" id="print_items_list"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('finalize_sale', 'finalize_sale'); ?>
                            <?= form_input('finalize_sale', $pos->finalize_sale, 'class="form-control tip" id="finalize_sale"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('today_sale', 'today_sale'); ?>
                            <?= form_input('today_sale', $pos->today_sale, 'class="form-control tip" id="today_sale"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('open_hold_bills', 'open_hold_bills'); ?>
                            <?= form_input('open_hold_bills', $pos->open_hold_bills, 'class="form-control tip" id="open_hold_bills"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('close_register', 'close_register'); ?>
                            <?= form_input('close_register', $pos->close_register, 'class="form-control tip" id="close_register"'); ?>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('payment_gateways') ?></legend>
                    <?php
                    if ($paypal_balance) {
                        if (!isset($paypal_balance['error'])) {
                            echo '<div class="alert alert-success"><button data-dismiss="alert" class="close" type="button">×</button><strong>' . lang('paypal_balance') . '</strong><p>';
                            $blns = sizeof($paypal_balance['amount']);
                            $r    = 1;
                            foreach ($paypal_balance['amount'] as $balance) {
                                echo lang('balance') . ': ' . $balance['L_AMT'] . ' (' . $balance['L_CURRENCYCODE'] . ')';
                                if ($blns != $r) {
                                    echo ', ';
                                }
                                $r++;
                            }
                            echo '</p></div>';
                        } else {
                            echo '<div class="alert alert-danger"><button data-dismiss="alert" class="close" type="button">×</button><p>';
                            foreach ($paypal_balance['message'] as $msg) {
                                echo $msg['L_SHORTMESSAGE'] . ' (' . $msg['L_ERRORCODE'] . '): ' . $msg['L_LONGMESSAGE'] . '<br>';
                            }
                            echo '</p></div>';
                        }
                    }
                    ?>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('paypal_pro', 'paypal_pro'); ?>
                            <?= form_dropdown('paypal_pro', $yn, $pos->paypal_pro, 'class="form-control" id="paypal_pro" required="required"'); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div id="paypal_pro_con">
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <?= lang('APIUsername', 'APIUsername'); ?>
                                <?= form_input('APIUsername', $APIUsername, 'class="form-control tip" id="APIUsername"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                <?= lang('APIPassword', 'APIPassword'); ?>
                                <?= form_input('APIPassword', $APIPassword, 'class="form-control tip" id="APIPassword"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <?= lang('APISignature', 'APISignature'); ?>
                                <?= form_input('APISignature', $APISignature, 'class="form-control tip" id="APISignature"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <?php
                    if ($stripe_balance) {
                        echo '<div class="alert alert-success"><button data-dismiss="alert" class="close" type="button">×</button><strong>' . lang('stripe_balance') . '</strong>';
                        echo '<p>' . lang('pending_amount') . ': ' . $stripe_balance['pending_amount'] . ' (' . $stripe_balance['pending_currency'] . ')';
                        echo ', ' . lang('available_amount') . ': ' . $stripe_balance['available_amount'] . ' (' . $stripe_balance['available_currency'] . ')</p>';
                        echo '</div>';
                    }
                    ?>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('stripe', 'stripe'); ?>
                            <?= form_dropdown('stripe', $yn, $pos->stripe, 'class="form-control" id="stripe" required="required"'); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div id="stripe_con">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <?= lang('stripe_secret_key', 'stripe_secret_key'); ?>
                                <?= form_input('stripe_secret_key', $stripe_secret_key, 'class="form-control tip" id="stripe_secret_key"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <?= lang('stripe_publishable_key', 'stripe_publishable_key'); ?>
                                <?= form_input('stripe_publishable_key', $stripe_publishable_key, 'class="form-control tip" id="stripe_publishable_key"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('authorize', 'authorize'); ?>
                            <?= form_dropdown('authorize', $yn, $pos->authorize, 'class="form-control" id="authorize" required="required"'); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div id="authorize_con">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <?= lang('api_login_id', 'api_login_id'); ?>
                                <?= form_input('api_login_id', $api_login_id, 'class="form-control tip" id="api_login_id"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <?= lang('api_transaction_key', 'api_transaction_key'); ?>
                                <?= form_input('api_transaction_key', $api_transaction_key, 'class="form-control tip" id="api_transaction_key"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </fieldset>

                <?= form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary"'); ?>

                <?= form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(e) {
        $("#order_printers").select2().select2('val', <?= $pos->order_printers; ?>);
        if ($('#remote_printing').val() == 1) {
            $('.printers').slideUp();
        } else if ($('#remote_printing').val() == 0) {
            $('.printers').slideDown();
            $('.ppp').slideUp();
            $('.lp').slideDown();
        } else {
            $('.printers').slideDown();
            $('.ppp').slideDown();
            if ($('#local_printers').val() == 1) {
                $('.lp').slideUp();
            } else {
                $('.lp').slideDown();
            }
        }
        $('#remote_printing').change(function() {
            if ($(this).val() == 1) {
                $('.printers').slideUp();
            } else if ($(this).val() == 0) {
                $('.printers').slideDown();
                $('.ppp').slideUp();
                $('.lp').slideDown();
            } else {
                $('.printers').slideDown();
                $('.ppp').slideDown();
                if ($('#local_printers').val() == 1) {
                    $('.lp').slideUp();
                } else {
                    $('.lp').slideDown();
                }
            }
        });
        $('#local_printers').change(function() {
            if ($(this).val() == 1) {
                $('.lp').slideUp();
            } else {
                $('.lp').slideDown();
            }
        });


        $('#pos_setting').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            },
            excluded: [':disabled']
        });
        $('select.select').select2({
            minimumResultsForSearch: 7
        });
        $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });

        $('#customer1').val('<?= $pos->default_customer; ?>').select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function(element, callback) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: site.base_url + "customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function(data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return {
                            results: data.results
                        };
                    } else {
                        return {
                            results: [{
                                id: '',
                                text: 'No Match Found'
                            }]
                        };
                    }
                }
            }
        });

        $('#paypal_pro').change(function() {
            var pp = $(this).val();
            if (pp == 1) {
                $('#paypal_pro_con').slideDown();
            } else {
                $('#paypal_pro_con').slideUp();
            }
        });
        $('#stripe').change(function() {
            var st = $(this).val();
            if (st == 1) {
                $('#stripe_con').slideDown();
            } else {
                $('#stripe_con').slideUp();
            }
        });
        $('#authorize').change(function() {
            var st = $(this).val();
            if (st == 1) {
                $('#authorize_con').slideDown();
            } else {
                $('#authorize_con').slideUp();
            }
        });
        var st = '<?= $pos->stripe ?>';
        var pp = '<?= $pos->paypal_pro ?>';
        var az = '<?= $pos->authorize ?>';
        if (st == 1) {
            $('#stripe_con').slideDown();
        } else {
            $('#stripe_con').slideUp();
        }
        if (pp == 1) {
            $('#paypal_pro_con').slideDown();
        } else {
            $('#paypal_pro_con').slideUp();
        }
        if (st == 1) {
            $('#authorize_con').slideDown();
        } else {
            $('#authorize_con').slideUp();
        }

    });
</script>