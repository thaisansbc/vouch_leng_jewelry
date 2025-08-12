<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('group_permissions'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('set_permissions'); ?></p>

                <?php if (!empty($p)) {
    if ($p->group_id != 1) {
        echo admin_form_open('system_settings/permissions/' . $id); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped reports-table">

                                <thead>
                                <tr>
                                    <th colspan="8"
                                        class="text-center"><?php echo $group->description . ' ( ' . $group->name . ' ) ' . $this->lang->line('group_permissions'); ?></th>
                                </tr>
                                <tr>
                                    <th rowspan="4" class="text-center"><?= lang('module_name'); ?>
                                    </th>
                                    <th colspan="5" class="text-center"><?= lang('permissions'); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang('view'); ?></th>
                                    <th class="text-center"><?= lang('add'); ?></th>
                                    <th class="text-center"><?= lang('edit'); ?></th>
                                    <th class="text-center"><?= lang('delete'); ?></th>
                                    <th class="text-center"><?= lang('import'); ?></th>
                                    <th class="text-center"><?= lang('export'); ?></th>
                                    <th class="text-center"><?= lang('misc'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?= lang('products'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-index" <?php echo $p->{'products-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-add" <?php echo $p->{'products-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-edit" <?php echo $p->{'products-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-delete" <?php echo $p->{'products-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="products-import" <?php echo $p->{'products-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="products-export" <?php echo $p->{'products-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-cost" class="checkbox" name="products-cost" <?php echo $p->{'products-cost'} ? 'checked' : ''; ?>>
                                            <label for="products-cost" class="padding05"><?= lang('product_cost') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-price" class="checkbox" name="products-price" <?php echo $p->{'products-price'} ? 'checked' : ''; ?>>
                                            <label for="products-price" class="padding05"><?= lang('product_price') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-adjustments" class="checkbox" name="products-adjustments" <?php echo $p->{'products-adjustments'} ? 'checked' : ''; ?>>
                                            <label for="products-adjustments" class="padding05"><?= lang('adjustments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-barcode" class="checkbox" name="products-barcode" <?php echo $p->{'products-barcode'} ? 'checked' : ''; ?>>
                                            <label for="products-barcode" class="padding05"><?= lang('print_barcodes') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-stock_count" class="checkbox" name="products-stock_count" <?php echo $p->{'products-stock_count'} ? 'checked' : ''; ?>>
                                            <label for="products-stock_count" class="padding05"><?= lang('stock_counts') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <?php if($Settings->module_asset){?>
                                <tr>
                                    <td><?= lang("assets"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-index" <?php echo $p->{'employees-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-add" <?php echo $p->{'employees-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-edit" <?php echo $p->{'employees-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-delete" <?php echo $p->{'employees-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-import" <?php echo $p->{'employees-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-export" <?php echo $p->{'employees-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td><?= lang('sales_order'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales_order-index" <?php echo $p->{'sales_order-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales_order-add" <?php echo $p->{'sales_order-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales_order-edit" <?php echo $p->{'sales_order-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales_order-delete" <?php echo $p->{'sales_order-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sale_order-import" <?php echo $p->{'sale_order-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sale_order-export" <?php echo $p->{'sale_order-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                         <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales_order-approved" class="checkbox" name="sales_order-approved" <?php echo $p->{'sales_order-approved'} ? "checked" : ''; ?>>
                                            <label for="sales_order-approved" class="padding05"><?= lang('approved') ?></label>
                                        </span>
                                       
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales_order-rejected" class="checkbox" name="sales_order-rejected" <?php echo $p->{'sales_order-rejected'} ? "checked" : ''; ?>>
                                            <label for="sales_order-rejected" class="padding05"><?= lang('rejected') ?></label>
                                        </span>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang('sales'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-index" <?php echo $p->{'sales-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add" <?php echo $p->{'sales-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit" <?php echo $p->{'sales-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete" <?php echo $p->{'sales-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sales-import" <?php echo $p->{'sales-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sales-export" <?php echo $p->{'sales-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email" <?php echo $p->{'sales-email'} ? 'checked' : ''; ?>>
                                            <label for="sales-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf" <?php echo $p->{'sales-pdf'} ? 'checked' : ''; ?>>
                                            <label for="sales-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <?php if (POS) { ?>
                                            <input type="checkbox" value="1" id="pos-index" class="checkbox" name="pos-index" <?php echo $p->{'pos-index'} ? 'checked' : ''; ?>>
                                            <label for="pos-index" class="padding05"><?= lang('pos') ?></label>
                                            <?php
                                            } ?>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-payments" class="checkbox" name="sales-payments" <?php echo $p->{'sales-payments'} ? 'checked' : ''; ?>>
                                            <label for="sales-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-return_sales" class="checkbox" name="sales-return_sales" <?php echo $p->{'sales-return_sales'} ? 'checked' : ''; ?>>
                                            <label for="sales-return_sales" class="padding05"><?= lang('return_sales') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><?= lang('deliveries'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-deliveries" <?php echo $p->{'sales-deliveries'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add_delivery" <?php echo $p->{'sales-add_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit_delivery" <?php echo $p->{'sales-edit_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete_delivery" <?php echo $p->{'sales-delete_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sales-import_delivery" <?php echo $p->{'sales-import_delivery'} ? "checked" : ''; ?>>
                                    </td>
                                     <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sales-export_delivery" <?php echo $p->{'sales-export_delivery'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf_delivery" <?php echo $p->{'sales-pdf_delivery'} ? 'checked' : ''; ?>>
                                            <label for="sales-pdf_delivery" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang('gift_cards'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-gift_cards" <?php echo $p->{'sales-gift_cards'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add_gift_card" <?php echo $p->{'sales-add_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit_gift_card" <?php echo $p->{'sales-edit_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete_gift_card" <?php echo $p->{'sales-delete_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sales-import_gift_card" <?php echo $p->{'sales-import_gift_card'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="sales-export_gift_card" <?php echo $p->{'sales-export_gift_card'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>

                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('quotes'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-index" <?php echo $p->{'quotes-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-add" <?php echo $p->{'quotes-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-edit" <?php echo $p->{'quotes-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-delete" <?php echo $p->{'quotes-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="quotes-import" <?php echo $p->{'quotes-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="quotes-export" <?php echo $p->{'quotes-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="quotes-email" class="checkbox" name="quotes-email" <?php echo $p->{'quotes-email'} ? 'checked' : ''; ?>>
                                            <label for="quotes-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="quotes-pdf" class="checkbox" name="quotes-pdf" <?php echo $p->{'quotes-pdf'} ? 'checked' : ''; ?>>
                                            <label for="quotes-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('purchases'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-index" <?php echo $p->{'purchases-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-add" <?php echo $p->{'purchases-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-edit" <?php echo $p->{'purchases-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-delete" <?php echo $p->{'purchases-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="purchases-import" <?php echo $p->{'purchases-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="purchases-export" <?php echo $p->{'purchases-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-email" class="checkbox" name="purchases-email" <?php echo $p->{'purchases-email'} ? 'checked' : ''; ?>>
                                            <label for="purchases-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-pdf" class="checkbox" name="purchases-pdf" <?php echo $p->{'purchases-pdf'} ? 'checked' : ''; ?>>
                                            <label for="purchases-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-payments" class="checkbox" name="purchases-payments" <?php echo $p->{'purchases-payments'} ? 'checked' : ''; ?>>
                                            <label for="purchases-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-expenses_budget" class="checkbox" name="purchases-expenses_budget" <?php echo $p->{'purchases-expenses_budget'} ? 'checked' : ''; ?>>
                                            <label for="purchases-expenses_budget" class="padding05"><?= lang('expenses_budget') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-expenses" class="checkbox" name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? 'checked' : ''; ?>>
                                            <label for="purchases-expenses" class="padding05"><?= lang('expenses') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-budgets" class="checkbox" name="purchases-budgets" <?php echo $p->{'purchases-budgets'} ? 'checked' : ''; ?>>
                                            <label for="purchases-budgets" class="padding05"><?= lang('budgets') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-return_purchases" class="checkbox" name="purchases-return_purchases" <?php echo $p->{'purchases-return_purchases'} ? 'checked' : ''; ?>>
                                            <label for="purchases-return_purchases" class="padding05"><?= lang('return_purchases') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang("purchases_request"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_request-index" <?php echo $p->{'purchases_request-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_request-add" <?php echo $p->{'purchases_request-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_request-edit" <?php echo $p->{'purchases_request-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_request-delete" <?php echo $p->{'purchases_request-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="purchase_request-import" <?php echo $p->{'purchase_request-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="purchase_request-export" <?php echo $p->{'purchase_request-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases_request-approved" class="checkbox" name="purchases_request-approved" <?php echo $p->{'purchases_request-approved'} ? "checked" : ''; ?>>
                                            <label for="purchases_request-approved" class="padding05"><?= lang('approved') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases_request-rejected" class="checkbox" name="purchases_request-rejected" <?php echo $p->{'purchases_request-rejected'} ? "checked" : ''; ?>>
                                            <label for="purchases_request-rejected" class="padding05"><?= lang('rejected') ?></label>
                                        </span>
                                        <span style="display:inline-block; display: none;">
                                            <input type="checkbox" value="1" id="purchases_request-email" class="checkbox" name="purchases_request-email" <?php echo $p->{'purchases_request-email'} ? "checked" : ''; ?>>
                                            <label for="purchases_request-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;display: none;">
                                            <input type="checkbox" value="1" id="purchases_request-pdf" class="checkbox" name="purchases_request-pdf" <?php echo $p->{'purchases_request-pdf'} ? "checked" : ''; ?>>
                                            <label for="purchases_request-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang("purchases_order"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_order-index" <?php echo $p->{'purchases_order-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_order-add" <?php echo $p->{'purchases_order-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_order-edit" <?php echo $p->{'purchases_order-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases_order-delete" <?php echo $p->{'purchases_order-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="purchases_order-import" <?php echo $p->{'purchases_order-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="purchases_order-export" <?php echo $p->{'purchases_order-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases_order-approved" class="checkbox" name="purchases_order-approved" <?php echo $p->{'purchases_order-approved'} ? "checked" : ''; ?>>
                                            <label for="purchases_order-approved" class="padding05"><?= lang('approved') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases_order-rejected" class="checkbox" name="purchases_order-rejected" <?php echo $p->{'purchases_order-rejected'} ? "checked" : ''; ?>>
                                            <label for="purchases_order-rejected" class="padding05"><?= lang('rejected') ?></label>
                                        </span>
                                        <span style="display:inline-block;display: none;">
                                            <input type="checkbox" value="1" id="purchases_order-email" class="checkbox" name="purchases_order-email" <?php echo $p->{'purchases_order-email'} ? "checked" : ''; ?>>
                                            <label for="purchases_order-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;display: none;">
                                            <input type="checkbox" value="1" id="purchases_order-pdf" class="checkbox" name="purchases_order-pdf" <?php echo $p->{'purchases_order-pdf'} ? "checked" : ''; ?>>
                                            <label for="purchases_order-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang('transfers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-index" <?php echo $p->{'transfers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-add" <?php echo $p->{'transfers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-edit" <?php echo $p->{'transfers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-delete" <?php echo $p->{'transfers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="transfers-import" <?php echo $p->{'transfers-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="transfers-export" <?php echo $p->{'transfers-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-email" class="checkbox" name="transfers-email" <?php echo $p->{'transfers-email'} ? 'checked' : ''; ?>>
                                            <label for="transfers-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-pdf" class="checkbox" name="transfers-pdf" <?php echo $p->{'transfers-pdf'} ? 'checked' : ''; ?>>
                                            <label for="transfers-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-approved" class="checkbox" name="transfers-approved" <?php echo $p->{'transfers-approved'} ? 'checked' : ''; ?>>
                                            <label for="transfers-approved" class="padding05"><?= lang('approved') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('returns'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-index" <?php echo $p->{'returns-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-add" <?php echo $p->{'returns-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-edit" <?php echo $p->{'returns-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-delete" <?php echo $p->{'returns-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="returns-import" <?php echo $p->{'returns-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="returns-export" <?php echo $p->{'returns-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-email" class="checkbox" name="returns-email" <?php echo $p->{'returns-email'} ? 'checked' : ''; ?>>
                                            <label for="returns-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-pdf" class="checkbox" name="returns-pdf" <?php echo $p->{'returns-pdf'} ? 'checked' : ''; ?>>
                                            <label for="returns-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <?php if($Settings->project){?>
                                <tr>
                                    <td><?= lang('project'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-index" <?php echo $p->{'projects-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-add" <?php echo $p->{'projects-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-edit" <?php echo $p->{'projects-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-delete" <?php echo $p->{'projects-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="projects-import" <?php echo $p->{'projects-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="projects-export" <?php echo $p->{'projects-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang('task'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-index" <?php echo $p->{'projects-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-add" <?php echo $p->{'projects-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-edit" <?php echo $p->{'projects-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="projects-delete" <?php echo $p->{'projects-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="projects-import" <?php echo $p->{'projects-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="projects-export" <?php echo $p->{'projects-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if (POS) { ?>
                                <tr>
                                    <td><?= lang("room_table"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="room-index" <?php echo $p->{'room-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="room-add" <?php echo $p->{'room-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="room-edit" <?php echo $p->{'room-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="room-delete" <?php echo $p->{'room-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="room-import" <?php echo $p->{'room-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="room-export" <?php echo $p->{'room-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td><?= lang('customers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-index" <?php echo $p->{'customers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-add" <?php echo $p->{'customers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-edit" <?php echo $p->{'customers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-delete" <?php echo $p->{'customers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="customers-import" <?php echo $p->{'customers-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="customers-export" <?php echo $p->{'customers-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-deposits" class="checkbox" name="customers-deposits" <?php echo $p->{'customers-deposits'} ? 'checked' : ''; ?>>
                                            <label for="customers-deposits" class="padding05"><?= lang('deposits') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-delete_deposit" class="checkbox" name="customers-delete_deposit" <?php echo $p->{'customers-delete_deposit'} ? 'checked' : ''; ?>>
                                            <label for="customers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('suppliers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-index" <?php echo $p->{'suppliers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-add" <?php echo $p->{'suppliers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-edit" <?php echo $p->{'suppliers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-delete" <?php echo $p->{'suppliers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="suppliers-import" <?php echo $p->{'suppliers-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="suppliers-export" <?php echo $p->{'suppliers-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="suppliers-deposits" class="checkbox" name="suppliers-deposits" <?php echo $p->{'suppliers-deposits'} ? 'checked' : ''; ?>>
                                            <label for="suppliers-deposits" class="padding05"><?= lang('deposits') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="suppliers-delete_deposit" class="checkbox" name="suppliers-delete_deposit" <?php echo $p->{'suppliers-delete_deposit'} ? 'checked' : ''; ?>>
                                            <label for="suppliers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                
                                <tr>
                                    <td><?= lang("drivers"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="drivers-index" <?php echo $p->{'drivers-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="drivers-add" <?php echo $p->{'drivers-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="drivers-edit" <?php echo $p->{'drivers-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="drivers-delete" <?php echo $p->{'drivers-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="drivers-import" <?php echo $p->{'drivers-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="drivers-export" <?php echo $p->{'drivers-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                
                                <tr>
                                    <td><?= lang("users"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="users-index" <?php echo $p->{'users-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="users-add" <?php echo $p->{'users-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="users-edit" <?php echo $p->{'users-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="users-delete" <?php echo $p->{'users-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="users-import" <?php echo $p->{'users-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="users-export" <?php echo $p->{'users-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><?= lang("----"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-index" <?php echo $p->{'employees-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-add" <?php echo $p->{'employees-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-edit" <?php echo $p->{'employees-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-delete" <?php echo $p->{'employees-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-import" <?php echo $p->{'employees-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-export" <?php echo $p->{'employees-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                <?php if($Settings->module_property){?>
                                <tr>
                                    <td><?= lang("property"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-index" <?php echo $p->{'employees-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-add" <?php echo $p->{'employees-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-edit" <?php echo $p->{'employees-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-delete" <?php echo $p->{'employees-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-import" <?php echo $p->{'employees-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-export" <?php echo $p->{'employees-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><?= lang("leasing"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-index" <?php echo $p->{'employees-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-add" <?php echo $p->{'employees-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-edit" <?php echo $p->{'employees-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-delete" <?php echo $p->{'employees-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-import" <?php echo $p->{'employees-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-export" <?php echo $p->{'employees-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td><?= lang("calendar"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-index" <?php echo $p->{'employees-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-add" <?php echo $p->{'employees-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-edit" <?php echo $p->{'employees-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-delete" <?php echo $p->{'employees-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-import" <?php echo $p->{'employees-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="employees-export" <?php echo $p->{'employees-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td></td>
                                </tr>

                                <?php if($Settings->module_account){?>
                                <tr>
                                    <td><?= lang("accounting"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-index" <?php echo $p->{'accounts-index'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-add" <?php echo $p->{'accounts-add'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-edit" <?php echo $p->{'accounts-edit'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-delete" <?php echo $p->{'accounts-delete'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center hide">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-import" <?php echo $p->{'accounts-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center hide">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-export" <?php echo $p->{'accounts-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-import" <?php echo $p->{'accounts-import'} ? "checked" : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox"
                                               name="accounts-export" <?php echo $p->{'accounts-export'} ? "checked" : ''; ?>>
                                    </td>
                                    <td>
                                       <div class="container-fluid">
                                         <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-list_receivable" class="checkbox"
                                            name="account-list_receivable" <?php echo $p->{'account-list_receivable'} ? "checked" : ''; ?>><label
                                            for="account-list_receivable" class="padding05"><?= lang('account-list_receivable') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-list_ar_aging" class="checkbox"
                                            name="account-list_ar_aging" <?php echo $p->{'account-list_ar_aging'} ? "checked" : ''; ?>><label
                                            for="account-list_ar_aging" class="padding05"><?= lang('account-list_ar_aging') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-ar_by_customer" class="checkbox"
                                            name="account-ar_by_customer" <?php echo $p->{'account-ar_by_customer'} ? "checked" : ''; ?>><label
                                            for="account-ar_by_customer" class="padding05"><?= lang('account-ar_by_customer') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-bill_receipt" class="checkbox"
                                            name="account-bill_receipt" <?php echo $p->{'account-bill_receipt'} ? "checked" : ''; ?>><label
                                            for="account-bill_receipt" class="padding05"><?= lang('account-bill_receipt') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-list_payable" class="checkbox"
                                            name="account-list_payable" <?php echo $p->{'account-list_payable'} ? "checked" : ''; ?>><label
                                            for="account-list_payable" class="padding05"><?= lang('account-list_payable') ?></label>
                                        </div>
                                        <div class="col-md-6">  
                                            <input type="checkbox" value="1" id="account-list_ap_aging" class="checkbox"
                                               name="account-list_ap_aging" <?php echo $p->{'account-list_ap_aging'} ? "checked" : ''; ?>><label
                                            for="account-list_ap_aging" class="padding05"><?= lang('account-list_ap_aging') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-ap_by_supplier" class="checkbox"
                                            name="account-ap_by_supplier" <?php echo $p->{'account-ap_by_supplier'} ? "checked" : ''; ?>><label
                                            for="account-ap_by_supplier" class="padding05"><?= lang('account-ap_by_supplier') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-bill_payable" class="checkbox"
                                            name="account-bill_payable" <?php echo $p->{'account-bill_payable'} ? "checked" : ''; ?>><label
                                            for="account-bill_payable" class="padding05"><?= lang('account-bill_payable') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-list_ac_head" class="checkbox"
                                            name="account-list_ac_head" <?php echo $p->{'account-list_ac_head'} ? "checked" : ''; ?>><label
                                            for="account-list_ac_head" class="padding05"><?= lang('account-list_ac_head') ?></label>
                                        </div>
                                        <div class="col-md-6">                                     
                                            <input type="checkbox" value="1" id="account-add_ac_head" class="checkbox"
                                               name="account-add_ac_head" <?php echo $p->{'account-add_ac_head'} ? "checked" : ''; ?>><label
                                            for="account-add_ac_head" class="padding05"><?= lang('account-add_ac_head') ?></label>
                                        </div>
                                        <div class="col-md-6">                                    
                                            <input type="checkbox" value="1" id="account-list_customer_deposit" class="checkbox"
                                               name="account-list_customer_deposit" <?php echo $p->{'account-list_customer_deposit'} ? "checked" : ''; ?>><label
                                            for="account-list_customer_deposit" class="padding05"><?= lang('account-list_customer_deposit') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-add_customer_deposit" class="checkbox"
                                            name="account-add_customer_deposit" <?php echo $p->{'account-add_customer_deposit'} ? "checked" : ''; ?>><label
                                            for="account-add_customer_deposit" class="padding05"><?= lang('account-add_customer_deposit') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-list_supplier_deposit" class="checkbox"
                                            name="account-list_supplier_deposit" <?php echo $p->{'account-list_supplier_deposit'} ? "checked" : ''; ?>><label
                                            for="account-list_supplier_deposit" class="padding05"><?= lang('account-list_supplier_deposit') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account-add_supplier_deposit" class="checkbox"
                                            name="account-add_supplier_deposit" <?php echo $p->{'account-add_supplier_deposit'} ? "checked" : ''; ?>><label
                                            for="account-add_supplier_deposit" class="padding05"><?= lang('account-add_supplier_deposit') ?></label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="checkbox" value="1" id="account_setting" class="checkbox"
                                            name="account_setting" <?php echo $p->{'account_setting'} ? "checked" : ''; ?>><label
                                            for="account_setting" class="padding05"><?= lang('account_setting') ?></label>
                                        </div>  
                                      </div>                                        
                                    </td>
                                </tr>
                                <?php }?>

                                </tbody>
                            </table>
                        </div>



                        <div class="table-responsive">
                            <table cellpadding="0" cellspacing="0" border="0"
                                   class="table table-bordered table-hover table-striped" style="margin-bottom: 5px;">
                                <thead>
                                <tr>
                                    <th colspan="2">
                                        <input type="checkbox" value="1" class="checkbox" id="reports-index" name="reports-index" <?php echo $p->{'reports-index'} ? "checked" : ''; ?>>
                                        <label for="sale_report-index" class="padding05">
                                            <?= lang('reports') ?>
                                        </label>
                                    </th>
                                </tr>
                               <tr>
                                    <td><?= lang('reports'); ?></td>

                                    <td colspan="5">
                                        <div class="col-md-6">
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="product_quantity_alerts" name="reports-quantity_alerts" <?php echo $p->{'reports-quantity_alerts'} ? 'checked' : ''; ?>>
                                                <label for="product_quantity_alerts" class="padding05"><?= lang('product_quantity_alerts') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="Product_expiry_alerts" name="reports-expiry_alerts" <?php echo $p->{'reports-expiry_alerts'} ? 'checked' : ''; ?>>
                                                <label for="Product_expiry_alerts" class="padding05"><?= lang('product_expiry_alerts') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="products"
                                                name="reports-products" <?php echo $p->{'reports-products'} ? 'checked' : ''; ?>><label for="products" class="padding05"><?= lang('products') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="daily_sales" name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? 'checked' : ''; ?>>
                                                <label for="daily_sales" class="padding05"><?= lang('daily_sales') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="monthly_sales" name="reports-monthly_sales" <?php echo $p->{'reports-monthly_sales'} ? 'checked' : ''; ?>>
                                                <label for="monthly_sales" class="padding05"><?= lang('monthly_sales') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="sale_targets" name="reports-sale_targets" <?php echo $p->{'reports-sale_targets'} ? 'checked' : ''; ?>>
                                                <label for="sale_targets" class="padding05"><?= lang('sale_targets') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="sales" name="reports-sales" <?php echo $p->{'reports-sales'} ? 'checked' : ''; ?>>
                                                <label for="sales" class="padding05"><?= lang('sales') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="payments" name="reports-payments" <?php echo $p->{'reports-payments'} ? 'checked' : ''; ?>>
                                                <label for="payments" class="padding05"><?= lang('payments') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="tax" name="reports-tax" <?php echo $p->{'reports-tax'} ? 'checked' : ''; ?>>
                                                <label for="tax" class="padding05"><?= lang('tax_report') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="expenses" name="reports-expenses" <?php echo $p->{'reports-expenses'} ? 'checked' : ''; ?>>
                                                <label for="expenses" class="padding05"><?= lang('expenses') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="budgets" name="reports-budgets" <?php echo $p->{'reports-budgets'} ? 'checked' : ''; ?>>
                                                <label for="budgets" class="padding05"><?= lang('budgets') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="expenses_budget" name="reports-expenses_budget" <?php echo $p->{'reports-expenses_budget'} ? 'checked' : ''; ?>>
                                                <label for="expenses_budget" class="padding05"><?= lang('expenses_budget') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="daily_purchases" name="reports-daily_purchases" <?php echo $p->{'reports-daily_purchases'} ? 'checked' : ''; ?>>
                                                <label for="daily_purchases" class="padding05"><?= lang('daily_purchases') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="monthly_purchases" name="reports-monthly_purchases" <?php echo $p->{'reports-monthly_purchases'} ? 'checked' : ''; ?>>
                                                <label for="monthly_purchases" class="padding05"><?= lang('monthly_purchases') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="purchases" name="reports-purchases" <?php echo $p->{'reports-purchases'} ? 'checked' : ''; ?>>
                                                <label for="purchases" class="padding05"><?= lang('purchases') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="customers" name="reports-customers" <?php echo $p->{'reports-customers'} ? 'checked' : ''; ?>>
                                                <label for="customers" class="padding05"><?= lang('customers') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="salemans" name="reports-salemans" <?php echo $p->{'reports-salemans'} ? 'checked' : ''; ?>>
                                                <label for="salemans" class="padding05"><?= lang('salemans') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="commission" name="reports-commission" <?php echo $p->{'reports-commission'} ? 'checked' : ''; ?>>
                                                <label for="commission" class="padding05"><?= lang('commission') ?></label>
                                            </span></br>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="suppliers" name="reports-suppliers" <?php echo $p->{'reports-suppliers'} ? 'checked' : ''; ?>>
                                                <label for="suppliers" class="padding05"><?= lang('suppliers') ?></label>
                                            </span>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="products_in_out_category" name="reports-stock_in_out" <?php echo $p->{'reports-stock_in_out'} ? 'checked' : ''; ?>>
                                                <label for="suppliers" class="padding05"><?= lang('products_in_out_category') ?></label>
                                            </span>
                                        </div>
                                        <div class="col-md-6"> 
                                            <div class="col-md-8" style="border-bottom: 2px solid #DDDDDD">                                         
                                                <input type="checkbox" value="1" class="checkbox" id="account_report-index"
                                                    name="account_report-index" <?php echo $p->{'account_report-index'}? "checked" : '';?>>
                                                <label for="account_report-index" class="padding05"><?= lang('account_report-index') ?></label>
                                            </div><br/>
                                            <div class="col-md-12">
                                                <input type="checkbox" value="1" class="checkbox" id="account_report-ledger"
                                                    name="account_report-ledger" <?php echo $p->{'account_report-ledger'}? "checked" : '';?>>
                                                    <label for="account_report-ledger" class="padding05"><?= lang('account_report-ledger') ?></label><br/>
                                                <input type="checkbox" value="1" class="checkbox" id="account_report-trail_balance"
                                                    name="account_report-trail_balance" <?php echo $p->{'account_report-trail_balance'}? "checked" : '';?>>
                                                    <label for="account_report-trail_balance" class="padding05"><?= lang('account_report-trail_balance') ?></label><br/>
                                                <input type="checkbox" value="1" class="checkbox" id="balance_sheet"
                                                    name="account_report-balance_sheet" <?php echo $p->{'account_report-balance_sheet'}? "checked" : '';?>>
                                                    <label for="account_report-balance_sheet" class="padding05"><?= lang('account_report-balance_sheet') ?></label><br/>      
                                                <input type="checkbox" value="1" class="checkbox" id="account_report-income_statement"
                                                    name="account_report-income_statement" <?php echo $p->{'account_report-income_statement'}? "checked" : '';?>>
                                                    <label for="account_report-income_statement" class="padding05"><?= lang('account_report-income_statement') ?></label><br/>


                                                <input type="checkbox" value="1" class="checkbox" id="account_report-payment"
                                                    name="account_report-payment" <?php echo $p->{'account_report-payment'}? "checked" : '';?>>
                                                    <label for="account_report-payment" class="padding05"><?= lang('account_report-payment') ?></label><br/>
                                                    
                                                <input type="checkbox" value="1" class="checkbox" id="account_report-income_statement_detail"
                                                    name="account_report-income_statement_detail" <?php echo $p->{'account_report-income_statement_detail'}? "checked" : '';?>>
                                                    <label for="account_report-income_statement_detail" class="padding05"><?= lang('account_report-income_statement_detail') ?></label><br/>
                                                <input type="checkbox" value="1" class="checkbox" id="account_report-cash_book"
                                                    name="account_report-cash_book" <?php echo $p->{'account_report-cash_book'}? "checked" : '';?>>
                                                    <label for="account_report-cash_book" class="padding05"><?= lang('account_report-cash_book') ?></label><br/>
                                                  
                                            </div>
                                        </div>
                                    </td>

                                </tr>

                                <tr>
                                    <td><?= lang('misc'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="bulk_actions" <?php echo $p->bulk_actions ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('bulk_actions') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                            name="edit_price" <?php echo $p->edit_price ? 'checked' : ''; ?>>
                                            <label for="edit_price" class="padding05"><?= lang('edit_price_on_sale') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?=lang('update')?></button>
                        </div>
                        <?php echo form_close();
    } else {
        echo $this->lang->line('group_x_allowed');
    }
} else {
    echo $this->lang->line('group_x_allowed');
} ?>


            </div>
        </div>
    </div>
</div>
