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
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= $group->description . ' ( ' . $group->name . ' ) ' . lang('group_permissions'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('set_permissions'); ?></p>
                <ul id="myTab" class="nav nav-tabs">
                    <li class=""><a href="#default" class="tab-grey"><?= lang('default') ?></a></li>
                    <?php if($Settings->module_inventory){ ?>
                    <li class=""><a href="#product" class="tab-grey"><?= lang('products') ?></a></li>
                    <?php }if ($Settings->module_asset) { ?>
                    <li class=""><a href="#asset" class="tab-grey"><?= lang('assets') ?></a></li>
                    <?php }if ($Settings->module_purchase) { ?>
                    <li class=""><a href="#purchase" class="tab-grey"><?= lang('purchases') ?></a></li>
                    <?php }if ($Settings->module_sale || POS) { ?>
                    <li class=""><a href="#sale" class="tab-grey"><?= lang('sales') ?></a></li>
                    <?php }if ($Settings->module_school) { ?>
                    <li class=""><a href="#school" class="tab-grey"><?= lang('school') ?></a></li>
                    <?php }if($Settings->accounting){ ?>
                    <li class=""><a href="#accounting" class="tab-grey"><?= lang('accounting') ?></a></li>
                    <?php }if ($Settings->module_hr) { ?>
                    <li class=""><a href="#hr" class="tab-grey"><?= lang('HR') ?></a></li>
                    <?php }if ($Settings->project) { ?>
                    <li class=""><a href="#project" class="tab-grey"><?= lang('project') ?></a></li>
                    <?php }if ($Settings->module_property){ ?>
                    <li class=""><a href="#property" class="tab-grey"><?= lang('property') ?></a></li>
                    <?php } if($Settings->module_installment || $Settings->module_loan){ ?>
                    <li class=""><a href="#installment" class="tab-grey"><?= lang('installment').'-'.lang('loans') ?></a></li>
                    <?php } if ($Settings->module_clinic) { ?>
                    <li class=""><a href="#clinic" class="tab-grey"><?= lang('clinic') ?></a></li>
                    <?php } if ($Settings->module_gym) { ?>
                    <li class=""><a href="#gym" class="tab-grey"><?= lang('gym') ?></a></li>
                    <?php }
                    ?>
                </ul>
            <?php if (!empty($p)) {
                if ($p->group_id != 1) {
                    echo admin_form_open('system_settings/permissions/' . $id); ?>
                    <div class="tab-content">
                        <div id="default" class="tab-pane fade in">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                    <?php if($this->config->item('master_data')==true){?>
                                    <tr>
                                        <td><?= lang('store_sales'); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales-index" <?php echo $p->{'store_sales-index'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales-add" <?php echo $p->{'store_sales-add'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales-edit" <?php echo $p->{'store_sales-edit'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales-delete" <?php echo $p->{'store_sales-delete'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales-import" <?php echo $p->{'store_sales-import'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales-export" <?php echo $p->{'store_sales-export'} ? "checked" : ''; ?>>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang('store_sales_order'); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales_order-index" <?php echo $p->{'store_sales_order-index'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales_order-add" <?php echo $p->{'store_sales_order-add'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales_order-edit" <?php echo $p->{'store_sales_order-edit'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales_order-delete" <?php echo $p->{'store_sales_order-delete'} ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales_order-import" <?php echo $p->{'store_sales_order-import'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="store_sales_order-export" <?php echo $p->{'store_sales_order-export'} ? "checked" : ''; ?>>
                                        </td>
                                    </tr>
                                    <?php }?>
                                    <tr>
                                        <td><?= lang("expenses"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="expenses-index" <?php echo $p->{'expenses-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="expenses-add" <?php echo $p->{'expenses-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="expenses-edit" <?php echo $p->{'expenses-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="expenses-delete" <?php echo $p->{'expenses-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>
                                            <input type="checkbox" value="1" id="expenses-date" class="checkbox" name="expenses-date" <?php echo $p->{'expenses-date'} ? "checked" : ''; ?>>
                                            <label for="expenses-date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <?php 
                                    if($Settings->module_tax){?>
                                    <tr>
                                        <td><?= lang("taxs"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="taxs-index" <?php echo $p->{'taxs-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="taxs-add_tax" <?php echo $p->{'taxs-add_tax'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="taxs-edit_tax" <?php echo $p->{'taxs-edit_tax'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="taxs-delete_tax" <?php echo $p->{'taxs-delete_tax'} ? "checked" : ''; ?>>
                                        </td>
                                        <td></td><td></td>
                                        <td>
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" id="view_tax" class="checkbox" name="view_tax" <?php echo $p->{'sales-view_sale_declare'} ? 'checked' : ''; ?>>
                                                <label for="view_tax" class="padding05"><?= lang('view_sale_declare') ?></label>
                                            </span>
                                            <input type="checkbox" value="1" id="taxs-purchases_report" class="checkbox" name="taxs-purchases_report" <?php echo $p->{'taxs-purchases_report'} ? "checked" : ''; ?>>
                                            <label for="taxs-purchases_report" class="padding05"><?= lang('purchases_report') ?></label>
                                            <input type="checkbox" value="1" id="taxs-sales_report" class="checkbox" name="taxs-sales_report" <?php echo $p->{'taxs-sales_report'} ? "checked" : ''; ?>>
                                            <label for="taxs-sales_report" class="padding05"><?= lang('sales_report') ?></label>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <?php if($this->Settings->module_concrete){ ?>
                                    <tr>
                                        <td><?= lang("delivery"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-deliveries" <?php echo $p->{'concretes-deliveries'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-add_delivery" <?php echo $p->{'concretes-add_delivery'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-edit_delivery" <?php echo $p->{'concretes-edit_delivery'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-delete_delivery" <?php echo $p->{'concretes-delete_delivery'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td>
                                            <input type="checkbox" value="1" id="concretes-deliveries-date" class="checkbox" name="concretes-deliveries-date" <?php echo $p->{'concretes-deliveries-date'} ? "checked" : ''; ?>>
                                            <label for="concretes-deliveries-date" class="padding05"><?= lang('date') ?></label>
                                            <input type="checkbox" value="1" id="concretes-skip-so" class="checkbox" name="concretes-skip-so" <?php echo $p->{'concretes-skip-so'} ? "checked" : ''; ?>>
                                            <label for="concretes-skip-so" class="padding05"><?= lang('skip_so') ?></label>
                                            <input type="checkbox" value="1" id="concretes-drivers" class="checkbox" name="concretes-drivers" <?php echo $p->{'concretes-drivers'} ? "checked" : ''; ?>>
                                            <label for="concretes-drivers" class="padding05"><?= lang('driver') ?></label>
                                            <input type="checkbox" value="1" id="concretes-trucks" class="checkbox" name="concretes-trucks" <?php echo $p->{'concretes-trucks'} ? "checked" : ''; ?>>
                                            <label for="concretes-trucks" class="padding05"><?= lang('truck') ?></label><br>
                                            <input type="checkbox" value="1" id="concretes-slumps" class="checkbox" name="concretes-slumps" <?php echo $p->{'concretes-slumps'} ? "checked" : ''; ?>>
                                            <label for="concretes-slumps" class="padding05"><?= lang('slump') ?></label>
                                            <input type="checkbox" value="1" id="concretes-casting_types" class="checkbox" name="concretes-casting_types" <?php echo $p->{'concretes-casting_types'} ? "checked" : ''; ?>>
                                            <label for="concretes-casting_types" class="padding05"><?= lang('casting_type') ?></label>
                                            <input type="checkbox" value="1" id="concretes-mission_types" class="checkbox" name="concretes-mission_types" <?php echo $p->{'concretes-mission_types'} ? "checked" : ''; ?>>
                                            <label for="concretes-mission_types" class="padding05"><?= lang('mission_type') ?></label>
                                            <input type="checkbox" value="1" id="concretes-officers" class="checkbox" name="concretes-officers" <?php echo $p->{'concretes-officers'} ? "checked" : ''; ?>>
                                            <label for="concretes-officers" class="padding05"><?= lang('officer') ?></label><br>
                                            <input type="checkbox" value="1" id="concretes-groups" class="checkbox" name="concretes-groups" <?php echo $p->{'concretes-groups'} ? "checked" : ''; ?>>
                                            <label for="concretes-groups" class="padding05"><?= lang('groups') ?></label>
                                            <input type="checkbox" value="1" id="concretes-deliveries_report" class="checkbox" name="concretes-deliveries_report" <?php echo $p->{'concretes-deliveries_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-deliveries_report" class="padding05"><?= lang('deliveries_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-daily_deliveries" class="checkbox" name="concretes-daily_deliveries" <?php echo $p->{'concretes-daily_deliveries'} ? "checked" : ''; ?>>
                                            <label for="concretes-daily_deliveries" class="padding05"><?= lang('daily_deliveries') ?></label><br>
                                            <input type="checkbox" value="1" id="concretes-daily_stock_outs" class="checkbox" name="concretes-daily_stock_outs" <?php echo $p->{'concretes-daily_stock_outs'} ? "checked" : ''; ?>>
                                            <label for="concretes-daily_stock_outs" class="padding05"><?= lang('daily_stock_outs') ?></label>
                                            <input type="checkbox" value="1" id="concretes-daily_stock_ins" class="checkbox" name="concretes-daily_stock_ins" <?php echo $p->{'concretes-daily_stock_ins'} ? "checked" : ''; ?>>
                                            <label for="concretes-daily_stock_ins" class="padding05"><?= lang('daily_stock_ins') ?></label>
                                            <input type="checkbox" value="1" id="concretes-inventory_in_outs" class="checkbox" name="concretes-inventory_in_outs" <?php echo $p->{'concretes-inventory_in_outs'} ? "checked" : ''; ?>>
                                            <label for="concretes-inventory_in_outs" class="padding05"><?= lang('inventory_in_outs') ?></label>
                                        </td>
                                    </tr>
                                    <?php if($Settings->moving_waitings){ ?>
                                        <tr>
                                            <td><?= lang("moving_waiting"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-moving_waitings" <?php echo $p->{'concretes-moving_waitings'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-add_moving_waiting" <?php echo $p->{'concretes-add_moving_waiting'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-edit_moving_waiting" <?php echo $p->{'concretes-edit_moving_waiting'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-delete_moving_waiting" <?php echo $p->{'concretes-delete_moving_waiting'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">&nbsp;</td>
                                            <td class="text-center">&nbsp;</td>
                                            <td>
                                                <input type="checkbox" value="1" id="concretes-moving_waitings-date" class="checkbox" name="concretes-moving_waitings-date" <?php echo $p->{'concretes-moving_waitings-date'} ? "checked" : ''; ?>>
                                                <label for="concretes-moving_waitings-date" class="padding05"><?= lang('date') ?></label>
                                                <input type="checkbox" value="1" id="concretes-moving_waitings_report" class="checkbox" name="concretes-moving_waitings_report" <?php echo $p->{'concretes-moving_waitings_report'} ? "checked" : ''; ?>>
                                                <label for="concretes-moving_waitings_report" class="padding05"><?= lang('moving_waitings_report') ?></label>
                                            </td>
                                        </tr>
                                    <?php } if($Settings->missions){ ?>
                                        <tr>
                                            <td><?= lang("mission"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-missions" <?php echo $p->{'concretes-missions'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-add_mission" <?php echo $p->{'concretes-add_mission'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-edit_mission" <?php echo $p->{'concretes-edit_mission'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-delete_mission" <?php echo $p->{'concretes-delete_mission'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">&nbsp;</td>
                                            <td class="text-center">&nbsp;</td>
                                            <td>
                                                <input type="checkbox" value="1" id="concretes-missions-date" class="checkbox" name="concretes-missions-date" <?php echo $p->{'concretes-missions-date'} ? "checked" : ''; ?>>
                                                <label for="concretes-missions-date" class="padding05"><?= lang('date') ?></label>
                                                <input type="checkbox" value="1" id="concretes-missions_report" class="checkbox" name="concretes-missions_report" <?php echo $p->{'concretes-missions_report'} ? "checked" : ''; ?>>
                                                <label for="concretes-missions_report" class="padding05"><?= lang('missions_report') ?></label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td><?= lang("fuel"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-fuels" <?php echo $p->{'concretes-fuels'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-add_fuel" <?php echo $p->{'concretes-add_fuel'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-edit_fuel" <?php echo $p->{'concretes-edit_fuel'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-delete_fuel" <?php echo $p->{'concretes-delete_fuel'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td>
                                            <input type="checkbox" value="1" id="concretes-fuels-date" class="checkbox" name="concretes-fuels-date" <?php echo $p->{'concretes-fuels-date'} ? "checked" : ''; ?>>
                                            <label for="concretes-fuels-date" class="padding05"><?= lang('date') ?></label>
                                            <input type="checkbox" value="1" id="concretes-fuels_report" class="checkbox" name="concretes-fuels_report" <?php echo $p->{'concretes-fuels_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-fuels_report" class="padding05"><?= lang('fuels_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-fuel_summaries_report" class="checkbox" name="concretes-fuel_summaries_report" <?php echo $p->{'concretes-fuel_summaries_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-fuel_summaries_report" class="padding05"><?= lang('fuel_summaries_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-fuel_details_report" class="checkbox" name="concretes-fuel_details_report" <?php echo $p->{'concretes-fuel_details_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-fuel_details_report" class="padding05"><?= lang('fuel_details_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-fuel_by_customer_report" class="checkbox" name="concretes-fuel_by_customer_report" <?php echo $p->{'concretes-fuel_by_customer_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-fuel_by_customer_report" class="padding05"><?= lang('fuel_by_customer_report') ?></label>
                                        </td>
                                    </tr>   
                                    <?php if($Settings->fuel_expenses){ ?>
                                        <tr>
                                            <td><?= lang("fuel_expense"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-fuel_expenses" <?php echo $p->{'concretes-fuel_expenses'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-add_fuel_expense" <?php echo $p->{'concretes-add_fuel_expense'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-edit_fuel_expense" <?php echo $p->{'concretes-edit_fuel_expense'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-delete_fuel_expense" <?php echo $p->{'concretes-delete_fuel_expense'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">&nbsp;</td>
                                            <td class="text-center">&nbsp;</td>
                                            <td>
                                                <input type="checkbox" value="1" id="concretes-fuel_expenses-date" class="checkbox" name="concretes-fuel_expenses-date" <?php echo $p->{'concretes-fuel_expenses-date'} ? "checked" : ''; ?>>
                                                <label for="concretes-fuel_expenses-date" class="padding05"><?= lang('date') ?></label>
                                                <input type="checkbox" value="1" id="concretes-fuel_expense_payments" class="checkbox" name="concretes-fuel_expense_payments" <?php echo $p->{'concretes-fuel_expense_payments'} ? "checked" : ''; ?>>
                                                <label for="concretes-fuel_expense_payments" class="padding05"><?= lang('payments') ?></label>
                                                <input type="checkbox" value="1" id="concretes-fuel_expenses_report" class="checkbox" name="concretes-fuel_expenses_report" <?php echo $p->{'concretes-fuel_expenses_report'} ? "checked" : ''; ?>>
                                                <label for="concretes-fuel_expenses_report" class="padding05"><?= lang('fuel_expenses_report') ?></label>
                                                <input type="checkbox" value="1" id="concretes-fuel_expense_details_report" class="checkbox" name="concretes-fuel_expense_details_report" <?php echo $p->{'concretes-fuel_expense_details_report'} ? "checked" : ''; ?>>
                                                <label for="concretes-fuel_expense_details_report" class="padding05"><?= lang('fuel_expense_details_report') ?></label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td><?= lang("sale"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-sales" <?php echo $p->{'concretes-sales'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-add_sale" <?php echo $p->{'concretes-add_sale'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-edit_sale" <?php echo $p->{'concretes-edit_sale'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-delete_sale" <?php echo $p->{'concretes-delete_sale'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td>
                                            <input type="checkbox" value="1" id="concretes-sales-date" class="checkbox" name="concretes-sales-date" <?php echo $p->{'concretes-sales-date'} ? "checked" : ''; ?>>
                                            <label for="concretes-sales-date" class="padding05"><?= lang('date') ?></label>
                                            <input type="checkbox" value="1" id="concretes-sales_report" class="checkbox" name="concretes-sales_report" <?php echo $p->{'concretes-sales_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-sales_report" class="padding05"><?= lang('sales_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-sale_details_report" class="checkbox" name="concretes-sale_details_report" <?php echo $p->{'concretes-sale_details_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-sale_details_report" class="padding05"><?= lang('sale_details_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-product_sales_report" class="checkbox" name="concretes-product_sales_report" <?php echo $p->{'concretes-product_sales_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-product_sales_report" class="padding05"><?= lang('product_sales_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-product_customers_report" class="checkbox" name="concretes-product_customers_report" <?php echo $p->{'concretes-product_customers_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-product_customers_report" class="padding05"><?= lang('product_customers_report') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("adjustment"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-adjustments" <?php echo $p->{'concretes-adjustments'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-add_adjustment" <?php echo $p->{'concretes-add_adjustment'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-edit_adjustment" <?php echo $p->{'concretes-edit_adjustment'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-delete_adjustment" <?php echo $p->{'concretes-delete_adjustment'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td>
                                            <input type="checkbox" value="1" id="concretes-approve_adjustment" class="checkbox" name="concretes-approve_adjustment" <?php echo $p->{'concretes-approve_adjustment'} ? "checked" : ''; ?>>
                                            <label for="concretes-approve_adjustment" class="padding05"><?= lang('approve_adjustment') ?></label>
                                            <input type="checkbox" value="1" id="concretes-adjustments_report" class="checkbox" name="concretes-adjustments_report" <?php echo $p->{'concretes-adjustments_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-adjustments_report" class="padding05"><?= lang('adjustments_report') ?></label>
                                        </td>
                                    </tr>
                                    <?php if($Settings->errors){ ?>
                                        <tr>
                                            <td><?= lang("error"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-errors" <?php echo $p->{'concretes-errors'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-add_error" <?php echo $p->{'concretes-add_error'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-edit_error" <?php echo $p->{'concretes-edit_error'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-delete_error" <?php echo $p->{'concretes-delete_error'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">&nbsp;</td>
                                            <td class="text-center">&nbsp;</td>
                                            <td>
                                                <input type="checkbox" value="1" id="concretes-errors-date" class="checkbox" name="concretes-errors-date" <?php echo $p->{'concretes-errors-date'} ? "checked" : ''; ?>>
                                                <label for="concretes-errors-date" class="padding05"><?= lang('date') ?></label>
                                                <input type="checkbox" value="1" id="concretes-daily_errors" class="checkbox" name="concretes-daily_errors" <?php echo $p->{'concretes-daily_errors'} ? "checked" : ''; ?>>
                                                <label for="concretes-daily_errors" class="padding05"><?= lang('daily_errors') ?></label>
                                                <input type="checkbox" value="1" id="concretes-daily_error_materials" class="checkbox" name="concretes-daily_error_materials" <?php echo $p->{'concretes-daily_error_materials'} ? "checked" : ''; ?>>
                                                <label for="concretes-daily_error_materials" class="padding05"><?= lang('daily_error_materials') ?></label>
                                            </td>
                                        </tr>
                                    <?php } if($Settings->absents){ ?>
                                        <tr>
                                            <td><?= lang("absent"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-absents" <?php echo $p->{'concretes-absents'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-add_absent" <?php echo $p->{'concretes-add_absent'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-edit_absent" <?php echo $p->{'concretes-edit_absent'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="concretes-delete_absent" <?php echo $p->{'concretes-delete_absent'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">&nbsp;</td>
                                            <td class="text-center">&nbsp;</td>
                                            <td>
                                                <input type="checkbox" value="1" id="concretes-absents-date" class="checkbox" name="concretes-absents-date" <?php echo $p->{'concretes-absents-date'} ? "checked" : ''; ?>>
                                                <label for="concretes-absents-date" class="padding05"><?= lang('date') ?></label>
                                                <input type="checkbox" value="1" id="concretes-absents_report" class="checkbox" name="concretes-absents_report" <?php echo $p->{'concretes-absents_report'} ? "checked" : ''; ?>>
                                                <label for="concretes-absents_report" class="padding05"><?= lang('absents_report') ?></label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td><?= lang("commission"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-commissions" <?php echo $p->{'concretes-commissions'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-add_commission" <?php echo $p->{'concretes-add_commission'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-edit_commission" <?php echo $p->{'concretes-edit_commission'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="concretes-delete_commission" <?php echo $p->{'concretes-delete_commission'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">&nbsp;</td>
                                        <td class="text-center">&nbsp;</td>
                                        <td>
                                            <input type="checkbox" value="1" id="concretes-commissions-date" class="checkbox" name="concretes-commissions-date" <?php echo $p->{'concretes-commissions-date'} ? "checked" : ''; ?>>
                                            <label for="concretes-commissions-date" class="padding05"><?= lang('date') ?></label>
                                            <input type="checkbox" value="1" id="concretes-commission_payments" class="checkbox" name="concretes-commission_payments" <?php echo $p->{'concretes-commission_payments'} ? "checked" : ''; ?>>
                                            <label for="concretes-commission_payments" class="padding05"><?= lang('payments') ?></label>
                                            <input type="checkbox" value="1" id="concretes-commissions_report" class="checkbox" name="concretes-commissions_report" <?php echo $p->{'concretes-commissions_report'} ? "checked" : ''; ?>>
                                            <label for="concretes-commissions_report" class="padding05"><?= lang('commissions_report') ?></label>
                                            <input type="checkbox" value="1" id="concretes-truck_commissions" class="checkbox" name="concretes-truck_commissions" <?php echo $p->{'concretes-truck_commissions'} ? "checked" : ''; ?>>
                                            <label for="concretes-truck_commissions" class="padding05"><?= lang('truck_commissions') ?></label>
                                            <input type="checkbox" value="1" id="concretes-pump_commissions" class="checkbox" name="concretes-pump_commissions" <?php echo $p->{'concretes-pump_commissions'} ? "checked" : ''; ?>>
                                            <label for="concretes-pump_commissions" class="padding05"><?= lang('pump_commissions') ?></label>
                                            <input type="checkbox" value="1" id="concretes-officer_commissions" class="checkbox" name="concretes-officer_commissions" <?php echo $p->{'concretes-officer_commissions'} ? "checked" : ''; ?>>
                                            <label for="concretes-officer_commissions" class="padding05"><?= lang('officer_commissions') ?></label>
                                        </td>
                                    </tr>
                                    <?php }?>
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
                                        <td><?= lang("calendar"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="calendar-index" <?php echo $p->{'calendar-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="calendar-add" <?php echo $p->{'calendar-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="calendar-edit" <?php echo $p->{'calendar-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="calendar-delete" <?php echo $p->{'calendar-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="calendar-import" <?php echo $p->{'calendar-import'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="calendar-export" <?php echo $p->{'calendar-export'} ? "checked" : ''; ?>>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("settings"); ?></td>
                                        <td colspan="7">
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="settings" name="settings" <?php echo $p->{'settings'} ? "checked" : ''; ?>>
                                                <label for="settings" class="padding05"><?= lang('settings') ?></label>
                                            </span>
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="system_settings-index" name="system_settings-index" <?php echo $p->{'system_settings-index'} ? "checked" : ''; ?>>
                                                <label for="system_settings-index" class="padding05"><?= lang('system_settings') ?></label>
                                            </span>
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="pos-settings" name="pos-settings" <?php echo $p->{'pos-settings'} ? "checked" : ''; ?>>
                                                <label for="pos-settings" class="padding05"><?= lang('pos_settings') ?></label>
                                            </span>
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="system_settings-change_logo" name="system_settings-change_logo" <?php echo $p->{'system_settings-change_logo'} ? "checked" : ''; ?>>
                                                <label for="system_settings-change_logo" class="padding05"><?= lang('change_logo') ?></label>
                                            </span>
                                            <?php if($Settings->project == 1){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="projects-index" name="projects-index" <?php echo $p->{'projects-index'} ? "checked" : ''; ?>>
                                                    <label for="projects-index" class="padding05"><?= lang('projects') ?></label>
                                                </span><br>
                                            <?php } if($Settings->module_inventory) { ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-warehouses" name="system_settings-warehouses" <?php echo $p->{'system_settings-warehouses'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-warehouses" class="padding05"><?= lang('warehouses') ?></label>
                                                </span>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-categories" name="system_settings-categories" <?php echo $p->{'system_settings-categories'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-categories" class="padding05"><?= lang('categories') ?></label>
                                                </span>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-units" name="system_settings-units" <?php echo $p->{'system_settings-units'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-units" class="padding05"><?= lang('units') ?></label>
                                                </span>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-brands" name="system_settings-brands" <?php echo $p->{'system_settings-brands'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-brands" class="padding05"><?= lang('brands') ?></label>
                                                </span>
                                                <?php if($this->config->item('repair')==true){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-models" name="system_settings-models" <?php echo $p->{'system_settings-models'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-models" class="padding05"><?= lang('models') ?></label>
                                                </span>
                                                <?php } ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-variants" name="system_settings-variants" <?php echo $p->{'system_settings-variants'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-variants" class="padding05"><?= lang('variants') ?></label>
                                                </span>
                                                <?php if($this->config->item('convert')){ ?>
                                                    <span style="inline-block">
                                                        <input type="checkbox" value="1" class="checkbox" id="system_settings-boms" name="system_settings-boms" <?php echo $p->{'system_settings-boms'} ? "checked" : ''; ?>>
                                                        <label for="system_settings-boms" class="padding05"><?= lang('bom') ?></label>
                                                    </span><br>
                                                <?php } ?>
                                            <?php } ?>
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="system_settings-zones" name="system_settings-zones" <?php echo $p->{'system_settings-zones'} ? "checked" : ''; ?>>
                                                <label for="system_settings-zones" class="padding05"><?= lang('zones') ?></label>
                                            </span><br>
                                            <?php if($Settings->module_purchase){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-expense_categories" name="system_settings-expense_categories" <?php echo $p->{'system_settings-expense_categories'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-expense_categories" class="padding05"><?= lang('expense_categories') ?></label>
                                                </span>
                                            <?php } if($this->pos_settings->pos_type =="table"){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-tables" name="system_settings-tables" <?php echo $p->{'system_settings-tables'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-tables" class="padding05"><?= lang('tables') ?></label>
                                                </span>
                                            <?php } if ($Settings->module_sale) { ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-customer_groups" name="system_settings-customer_groups" <?php echo $p->{'system_settings-customer_groups'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-customer_groups" class="padding05"><?= lang('customer_groups') ?></label>
                                                </span>
                                                <span style="inline-block" class="hide">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-customer_price" name="system_settings-customer_price" <?php echo $p->{'system_settings-customer_price'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-customer_price" class="padding05"><?= lang('customer_price') ?></label>
                                                </span>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-price_groups" name="system_settings-price_groups" <?php echo $p->{'system_settings-price_groups'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-price_groups" class="padding05"><?= lang('price_groups') ?></label>
                                                </span>
                                                <?php if ($Settings->reward_exchange) { ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-rewards" name="system_settings-rewards" <?php echo $p->{'system_settings-rewards'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-rewards" class="padding05"><?= lang('rewards') ?></label>
                                                </span>
                                                <?php } ?>
                                                <br>
                                                <?php if($this->config->item('product_promotions')) { ?>
                                                    <span style="inline-block">
                                                        <input type="checkbox" value="1" class="checkbox" id="system_settings-product_promotions" name="system_settings-product_promotions" <?php echo $p->{'system_settings-product_promotions'} ? "checked" : ''; ?>>
                                                        <label for="system_settings-product_promotions" class="padding05"><?= lang('product_promotions') ?></label>
                                                    </span>
                                                <?php } ?>
                                            <?php } if($Settings->module_sale || $Settings->module_purchase){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-payment_term" name="system_settings-payment_term" <?php echo $p->{'system_settings-payment_term'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-payment_term" class="padding05"><?= lang('payment_term') ?></label>
                                                </span>
                                            <?php } if($this->config->item('saleman_commission')){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-sale_targets" name="system_settings-sale_targets" <?php echo $p->{'system_settings-sale_targets'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-sale_targets" class="padding05"><?= lang('saleman_targets') ?></label>
                                                </span>
                                            <?php } if($Settings->module_sale || $Settings->module_purchase){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-currencies" name="system_settings-currencies" <?php echo $p->{'system_settings-currencies'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-currencies" class="padding05"><?= lang('currencies') ?></label>
                                                </span>
                                            <?php } if($Settings->module_sale || $Settings->module_purchase){ ?>
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="system_settings-tax_rates" name="system_settings-tax_rates" <?php echo $p->{'system_settings-tax_rates'} ? "checked" : ''; ?>>
                                                <label for="system_settings-tax_rates" class="padding05"><?= lang('tax_rates') ?></label>
                                            </span>
                                            <?php } if($Settings->module_sale){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-email_templates" name="system_settings-email_templates" <?php echo $p->{'system_settings-email_templates'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-email_templates" class="padding05"><?= lang('email_templates') ?></label>
                                                </span>
                                                <span style="inline-block" class="hide">
                                                    <input type="checkbox" value="1" class="checkbox" id="pos-printers" name="pos-printers" <?php echo $p->{'pos-printers'} ? "checked" : ''; ?>>
                                                    <label for="pos-printers" class="padding05"><?= lang('printers') ?></label>
                                                </span>
                                            <?php } ?>
                                            <span style="inline-block" class="hide">
                                                <input type="checkbox" value="1" class="checkbox" id="system_settings-cash_account" name="system_settings-cash_account" <?php echo $p->{'system_settings-cash_account'} ? "checked" : ''; ?>>
                                                <label for="system_settings-cash_account" class="padding05"><?= lang('cash_account') ?></label>
                                            </span>
                                            <?php  if($this->config->item("send_telegram")){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-telegrams" name="system_settings-telegrams" <?php echo $p->{'system_settings-telegrams'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-telegrams" class="padding05"><?= lang('telegrams') ?></label>
                                                </span>
                                            <?php } ?>
                                            <span style="inline-block">
                                                <input type="checkbox" value="1" class="checkbox" id="system_settings-user_groups" name="system_settings-user_groups" <?php echo $p->{'system_settings-user_groups'} ? "checked" : ''; ?>>
                                                <label for="system_settings-user_groups" class="padding05"><?= lang('group_permissions') ?></label>
                                            </span>
                                            <?php  if($this->config->item('backup')){ ?>
                                                <span style="inline-block">
                                                    <input type="checkbox" value="1" class="checkbox" id="system_settings-backups" name="system_settings-backups" <?php echo $p->{'system_settings-backups'} ? "checked" : ''; ?>>
                                                    <label for="system_settings-backups" class="padding05"><?= lang('backups') ?></label>
                                            <?php } ?>
                                        </td>
                                    </tr>
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
                                    <?php if($this->config->item('master_data')==true){?>
                                   <tr>
                                        <td><?= lang('reports'); ?></td>
                                        <td colspan="5">
                                            <div class="col-md-6">
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="store_sales" name="reports-store_sales" <?php echo $p->{'reports-store_sales'} ? 'checked' : ''; ?>>
                                                    <label for="store_sales" class="padding05"><?= lang('store_sales') ?></label>
                                                </span></br>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php }?>
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
                                            <span style="display:inline-block;">
                                                <input type="checkbox" value="1" class="checkbox" id="change_date"
                                                name="change_date" <?php echo $p->change_date ? 'checked' : ''; ?>>
                                                <label for="change_date" class="padding05"><?= lang('change_date') ?></label>
                                            </span>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="product" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                    <?php if ($Settings->module_inventory) { ?>
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
                                                <input type="checkbox" value="1" class="checkbox" name="products-import" <?php echo $p->{'products-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="products-export" <?php echo $p->{'products-export'} ? "checked" : ''; ?>>
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
                                                    <input type="checkbox" value="1" id="products-update_cost_and_price" class="checkbox" name="products-update_cost_and_price" <?php echo $p->{'products-update_cost_and_price'} ? 'checked' : ''; ?>>
                                                    <label for="products-update_cost_and_price" class="padding05"><?= lang('product_update_cost_and_price') ?></label>
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
                                        <?php  if ($Settings->stock_received) { ?>
                                        <tr>
                                            <td><?= lang('stock_received'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="stock_received-index" <?php echo $p->{'stock_received-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="stock_received-add" <?php echo $p->{'stock_received-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="stock_received-edit" <?php echo $p->{'stock_received-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="stock_received-delete" <?php echo $p->{'stock_received-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="stock_received-import" <?php echo $p->{'stock_received-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="stock_received-export" <?php echo $p->{'stock_received-export'} ? "checked" : ''; ?>>
                                            </td>
                                        </tr>
                                        <?php }?>
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
                                                <input type="checkbox" value="1" class="checkbox" name="transfers-import" <?php echo $p->{'transfers-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="transfers-export" <?php echo $p->{'transfers-export'} ? "checked" : ''; ?>>
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
                                        <?php if ($Settings->reward_exchange) { ?>
                                        <tr>
                                            <td><?= lang('reward_exchange'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="reward_exchange-index" <?php echo $p->{'reward_exchange-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="reward_exchange-add" <?php echo $p->{'reward_exchange-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="reward_exchange-edit" <?php echo $p->{'reward_exchange-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="reward_exchange-delete" <?php echo $p->{'reward_exchange-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="reward_exchange-import" <?php echo $p->{'reward_exchange-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="reward_exchange-export" <?php echo $p->{'reward_exchange-export'} ? "checked" : ''; ?>>
                                            </td>
                                        </tr>
                                        <?php }?>
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
                                                    <input type="checkbox" value="1" class="checkbox" id="products" name="reports-products" <?php echo $p->{'reports-products'} ? 'checked' : ''; ?>><label for="products" class="padding05"><?= lang('products') ?></label>
                                                </span></br>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="stock_received" name="reports-stock_received" <?php echo $p->{'reports-stock_received'} ? 'checked' : ''; ?>><label for="stock_received" class="padding05"><?= lang('stock_received') ?></label>
                                                </span></br>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="products_in_out_category" name="reports-stock_in_out" <?php echo $p->{'reports-stock_in_out'} ? 'checked' : ''; ?>>
                                                    <label for="suppliers" class="padding05"><?= lang('products_in_out_category') ?></label>
                                                </span></br>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="reward_exchange" name="reports-reward_exchange" <?php echo $p->{'reports-reward_exchange'} ? 'checked' : ''; ?>>
                                                    <label for="reward_exchange" class="padding05"><?= lang('reward_exchange') ?></label>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="asset" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                    <?php if($Settings->module_asset){?>
                                        <tr>
                                            <td><?= lang("assets"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="assets-index" <?php echo $p->{'assets-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="assets-add" <?php echo $p->{'assets-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="assets-edit" <?php echo $p->{'assets-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="assets-delete" <?php echo $p->{'assets-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="assets-import" <?php echo $p->{'assets-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="assets-export" <?php echo $p->{'assets-export'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="assets-expenses" class="checkbox" name="assets-expenses" <?php echo $p->{'assets-expenses'} ? 'checked' : ''; ?>>
                                                    <label for="assets-expenses" class="padding05"><?= lang('expenses') ?></label>
                                                </span>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="assets-depreciation" class="checkbox" name="assets-depreciation" <?php echo $p->{'assets-depreciation'} ? 'checked' : ''; ?>>
                                                    <label for="assets-depreciation" class="padding05"><?= lang('depreciation') ?></label>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php }?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="purchase" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">

                                    <thead>
                                   
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                        <?php if($Settings->module_purchase){ ?>
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
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="suppliers" name="reports-suppliers" <?php echo $p->{'reports-suppliers'} ? 'checked' : ''; ?>>
                                                    <label for="suppliers" class="padding05"><?= lang('suppliers') ?></label>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if ($Settings->purchase_request) { ?>
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
                                                <span style="display:inline-block; display: none;">
                                                    <input type="checkbox" value="1" id="purchases_request-email" class="checkbox" name="purchases_request-email" <?php echo $p->{'purchases_request-email'} ? "checked" : ''; ?>>
                                                    <label for="purchases_request-email" class="padding05"><?= lang('email') ?></label>
                                                </span>
                                                <span style="display:inline-block;display: none;">
                                                    <input type="checkbox" value="1" id="purchases_request-pdf" class="checkbox" name="purchases_request-pdf" <?php echo $p->{'purchases_request-pdf'} ? "checked" : ''; ?>>
                                                    <label for="purchases_request-pdf" class="padding05"><?= lang('pdf') ?></label>
                                                </span>
                                                <?php
                                                if($Settings->multi_level){
                                                ?>
                                                <table width="100%">
                                                    <tr>
                                                        <td width="120">
                                                            <span style="display:inline-block;">
                                                                <input type="checkbox" value="1" id="purchases_request-approved" class="checkbox" name="purchases_request-approved" <?php echo $p->{'purchases_request-approved'} ? "checked" : ''; ?>>
                                                                <label for="purchases_request-approved" class="padding05"><?= lang('approved') ?></label>
                                                            </span>
                                                        </td>
                                                        <td width="120">
                                                            <span style="display:inline-block;">
                                                                <input type="checkbox" value="1" id="purchases_request-rejected" class="checkbox" name="purchases_request-rejected" <?php echo $p->{'purchases_request-rejected'} ? "checked" : ''; ?>>
                                                                <label for="purchases_request-rejected" class="padding05"><?= lang('rejected') ?></label>
                                                            </span>
                                                        </td>
                                                 
                                                    </tr>
                                                </table>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php }if ($Settings->purchase_order) { ?>
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
                                        <?php }?>
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
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="sale" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">

                                    <thead>
                                   
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                    <?php 
                                    if ($Settings->module_sale || POS) { ?>
                                        <?php if ($Settings->sale_order) { ?>
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
                                                <?php
                                                if($Settings->multi_level){
                                                ?>    
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="sales_order-approved" class="checkbox" name="sales_order-approved" <?php echo $p->{'sales_order-approved'} ? "checked" : ''; ?>>
                                                    <label for="sales_order-approved" class="padding05"><?= lang('approved') ?></label>
                                                </span>
                                            
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="sales_order-rejected" class="checkbox" name="sales_order-rejected" <?php echo $p->{'sales_order-rejected'} ? "checked" : ''; ?>>
                                                    <label for="sales_order-rejected" class="padding05"><?= lang('rejected') ?></label>
                                                </span>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php }?>
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
                                                    <?php if (POS) { ?>
                                                    <input type="checkbox" value="1" id="close_table" class="checkbox" name="close_table" <?php echo $p->close_table ? 'checked' : ''; ?>>
                                                    <label for="close_table" class="padding05"><?= lang('close_table') ?></label>
                                                    <?php
                                                    } ?>
                                                </span>
                                                 <span style="display:inline-block;">
                                                    <?php if (POS) { ?>
                                                    <input type="checkbox" value="1" id="remove_item" class="checkbox" name="remove_item" <?php echo $p->remove_item ? 'checked' : ''; ?>>
                                                    <label for="remove_item" class="padding05"><?= lang('remove_item') ?></label>
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
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="products-making" class="checkbox" name="products-making" <?php echo $p->{'products-making'} ? 'checked' : ''; ?>>
                                                    <label for="products-making" class="padding05"><?= lang('view_producing') ?></label>
                                                </span>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="sales-credit_note" class="checkbox" name="sales-credit_note" <?php echo $p->{'sales-credit_note'} ? 'checked' : ''; ?>>
                                                    <label for="sales-credit_note" class="padding05"><?= lang('credit_note') ?></label>
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
                                        <?php if ($Settings->delivery) {?>
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
                                        <?php } if($Settings->quotation) { ?>
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
                                    <?php }
                                    }
                                    ?>
                                    <?php if (POS && $this->pos_settings->pos_type !="pos") { ?>
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
                                        <?php } if ($this->Settings->driver) {?>
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

                                        <?php if($this->Settings->module_fuel){ ?>
                                            <tr>
                                                <td><?= lang("fuel_sale"); ?></td>
                                                <td class="text-center">
                                                    <input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-index" <?php echo $p->{'sales-fuel_sale-index'} ? "checked" : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-add" <?php echo $p->{'sales-fuel_sale-add'} ? "checked" : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-edit" <?php echo $p->{'sales-fuel_sale-edit'} ? "checked" : ''; ?>>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" value="1" class="checkbox" name="sales-fuel_sale-delete" <?php echo $p->{'sales-fuel_sale-delete'} ? "checked" : ''; ?>>
                                                </td>
                                                <td>
                                                    <input type="checkbox" value="1" id="sales-fuel_sale-date" class="checkbox" name="sales-fuel_sale-date" <?php echo $p->{'sales-fuel_sale-date'} ? "checked" : ''; ?>>
                                                    <label for="sales-fuel_sale-date" class="padding05"><?= lang('date') ?></label>
                                                </td>
                                            </tr>
                                        <?php } ?>
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
                                                    <input type="checkbox" value="1" class="checkbox" id="daily_sales" name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? 'checked' : ''; ?>>
                                                    <label for="daily_sales" class="padding05"><?= lang('daily_sales') ?></label>
                                                </span></br>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="monthly_sales" name="reports-monthly_sales" <?php echo $p->{'reports-monthly_sales'} ? 'checked' : ''; ?>>
                                                    <label for="monthly_sales" class="padding05"><?= lang('monthly_sales') ?></label>
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
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="gym" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                            <td><?= lang('schedules'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schedules-index" <?php echo $p->{'schedules-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <!-- <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="customers-add" <?php echo $p->{'customers-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="customers-edit" <?php echo $p->{'customers-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="customers-delete" <?php echo $p->{'customers-delete'} ? 'checked' : ''; ?>>
                                            </td> -->
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('trainee'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainees-index" <?php echo $p->{'trainees-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainees-add" <?php echo $p->{'trainees-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainees-edit" <?php echo $p->{'trainees-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainees-delete" <?php echo $p->{'trainees-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('trainer'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainers-index" <?php echo $p->{'trainers-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainers-add" <?php echo $p->{'trainers-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainers-edit" <?php echo $p->{'trainers-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="trainers-delete" <?php echo $p->{'trainers-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('class'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="class-index" <?php echo $p->{'class-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="class-add" <?php echo $p->{'class-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="class-edit" <?php echo $p->{'class-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="class-delete" <?php echo $p->{'class-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">     
                                            </td>
                                            <td class="text-center">
                                            </td>
                                            <td>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="class-time_tables" class="checkbox" name="class-time_tables" <?php echo $p->{'class-time_tables'} ? 'checked' : ''; ?>>
                                                    <label for="class-time_tables" class="padding05"><?= lang('time_tables') ?></label>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('activity'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="activitys-index" <?php echo $p->{'activitys-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="activitys-add" <?php echo $p->{'activitys-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="activitys-edit" <?php echo $p->{'activitys-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="activitys-delete" <?php echo $p->{'activitys-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('membership'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="memberships-index" <?php echo $p->{'memberships-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="memberships-add" <?php echo $p->{'memberships-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="memberships-edit" <?php echo $p->{'memberships-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="memberships-delete" <?php echo $p->{'memberships-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('level'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="levels-index" <?php echo $p->{'levels-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="levels-add" <?php echo $p->{'levels-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="levels-edit" <?php echo $p->{'levels-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="levels-delete" <?php echo $p->{'levels-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('categories'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="category-index" <?php echo $p->{'category-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="category-add" <?php echo $p->{'category-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="category-edit" <?php echo $p->{'category-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="category-delete" <?php echo $p->{'category-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('workouts'); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="workouts-index" <?php echo $p->{'workouts-index'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="workouts-add" <?php echo $p->{'workouts-add'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="workouts-edit" <?php echo $p->{'workouts-edit'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="workouts-delete" <?php echo $p->{'workouts-delete'} ? 'checked' : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td class="text-center">
                                                
                                            </td>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive">
                                <table cellpadding="0" cellspacing="0" border="0"
                                       class="table table-bordered table-hover table-striped" style="margin-bottom: 5px;">
                                    <thead>
                                    <tr>
                                        <th colspan="2">
                                           
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
                                                    <input type="checkbox" value="1" class="checkbox" id="daily_sales" name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? 'checked' : ''; ?>>
                                                    <label for="daily_sales" class="padding05"><?= lang('schedule') ?></label>
                                                </span></br>
                                  
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="clinic" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                            <td><?= lang('patience'); ?></td>
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
                                    <?php 
                                    if ($Settings->module_clinic) { ?>
                                        <tr>
                                            <td><?= lang('treatments'); ?></td>
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
                                                <?php
                                                if($Settings->multi_level){
                                                ?>    
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="sales_order-approved" class="checkbox" name="sales_order-approved" <?php echo $p->{'sales_order-approved'} ? "checked" : ''; ?>>
                                                    <label for="sales_order-approved" class="padding05"><?= lang('approved') ?></label>
                                                </span>
                                            
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="sales_order-rejected" class="checkbox" name="sales_order-rejected" <?php echo $p->{'sales_order-rejected'} ? "checked" : ''; ?>>
                                                    <label for="sales_order-rejected" class="padding05"><?= lang('rejected') ?></label>
                                                </span>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('invoice'); ?></td>
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
                                                    <?php if (POS) { ?>
                                                    <input type="checkbox" value="1" id="close_table" class="checkbox" name="close_table" <?php echo $p->close_table ? 'checked' : ''; ?>>
                                                    <label for="close_table" class="padding05"><?= lang('close_table') ?></label>
                                                    <?php
                                                    } ?>
                                                </span>
                                                 <span style="display:inline-block;">
                                                    <?php if (POS) { ?>
                                                    <input type="checkbox" value="1" id="remove_item" class="checkbox" name="remove_item" <?php echo $p->remove_item ? 'checked' : ''; ?>>
                                                    <label for="remove_item" class="padding05"><?= lang('remove_item') ?></label>
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
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="products-making" class="checkbox" name="products-making" <?php echo $p->{'products-making'} ? 'checked' : ''; ?>>
                                                    <label for="products-making" class="padding05"><?= lang('view_producing') ?></label>
                                                </span>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" id="sales-credit_note" class="checkbox" name="sales-credit_note" <?php echo $p->{'sales-credit_note'} ? 'checked' : ''; ?>>
                                                    <label for="sales-credit_note" class="padding05"><?= lang('credit_note') ?></label>
                                                </span>
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
                                                    <input type="checkbox" value="1" class="checkbox" id="daily_sales" name="reports-daily_sales" <?php echo $p->{'reports-daily_sales'} ? 'checked' : ''; ?>>
                                                    <label for="daily_sales" class="padding05"><?= lang('daily_sales') ?></label>
                                                </span></br>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="monthly_sales" name="reports-monthly_sales" <?php echo $p->{'reports-monthly_sales'} ? 'checked' : ''; ?>>
                                                    <label for="monthly_sales" class="padding05"><?= lang('monthly_sales') ?></label>
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
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="school" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">

                                    <thead>
                                   
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="5" class="text-center"><?= lang('permissions'); ?></th>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><?= lang('view'); ?></th>
                                        <th class="text-center"><?= lang('add'); ?></th>
                                        <th class="text-center"><?= lang('edit'); ?></th>
                                        <th class="text-center"><?= lang('delete'); ?></th>
                                        <th class="text-center"><?= lang('misc'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= lang("students"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-index" <?php echo $p->{'schools-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-add" <?php echo $p->{'schools-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-edit" <?php echo $p->{'schools-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-delete" <?php echo $p->{'schools-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-student_report" class="checkbox" name="schools-student_report" <?php echo $p->{'schools-student_report'} ? "checked" : ''; ?>>
                                                <label for="schools-student_report" class="padding05"><?= lang('student_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-programs" class="checkbox" name="schools-programs" <?php echo $p->{'schools-programs'} ? "checked" : ''; ?>>
                                                <label for="schools-programs" class="padding05"><?= lang('programs') ?></label>
                                                <input type="checkbox" value="1" id="schools-sections" class="checkbox" name="schools-sections" <?php echo $p->{'schools-sections'} ? "checked" : ''; ?>>
                                                <label for="schools-sections" class="padding05"><?= lang('sections') ?></label>
                                                <input type="checkbox" value="1" id="schools-skills" class="checkbox" name="schools-skills" <?php echo $p->{'schools-skills'} ? "checked" : ''; ?>>
                                                <label for="schools-skills" class="padding05"><?= lang('skills') ?></label>
                                                <input type="checkbox" value="1" id="schools-grades" class="checkbox" name="schools-grades" <?php echo $p->{'schools-grades'} ? "checked" : ''; ?>>
                                                <label for="schools-grades" class="padding05"><?= lang('grades') ?></label>
                                                <input type="checkbox" value="1" id="schools-rooms" class="checkbox" name="schools-rooms" <?php echo $p->{'schools-rooms'} ? "checked" : ''; ?>>
                                                <label for="schools-rooms" class="padding05"><?= lang('rooms') ?></label>
                                                <input type="checkbox" value="1" id="schools-classes" class="checkbox" name="schools-classes" <?php echo $p->{'schools-classes'} ? "checked" : ''; ?>>
                                                <label for="schools-classes" class="padding05"><?= lang('classes') ?></label>
                                                <input type="checkbox" value="1" id="schools-time_tables" class="checkbox" name="schools-time_tables" <?php echo $p->{'schools-time_tables'} ? "checked" : ''; ?>>
                                                <label for="schools-time_tables" class="padding05"><?= lang('time_tables') ?></label>
                                                <input type="checkbox" value="1" id="schools-class_years" class="checkbox" name="schools-class_years" <?php echo $p->{'schools-class_years'} ? "checked" : ''; ?>>
                                                <label for="schools-class_years" class="padding05"><?= lang('class_years') ?></label>
                                                <input type="checkbox" value="1" id="schools-credit_scores" class="checkbox" name="schools-credit_scores" <?php echo $p->{'schools-credit_scores'} ? "checked" : ''; ?>>
                                                <label for="schools-credit_scores" class="padding05"><?= lang('credit_scores') ?></label>
                                                <input type="checkbox" value="1" id="schools-bank_info" class="checkbox" name="schools-bank_info" <?php echo $p->{'schools-bank_info'} ? "checked" : ''; ?>>
                                                <label for="schools-bank_info" class="padding05"><?= lang('bank_info') ?></label>
                                                <input type="checkbox" value="1" id="schools-black_lists" class="checkbox" name="schools-black_lists" <?php echo $p->{'schools-black_lists'} ? "checked" : ''; ?>>
                                                <label for="schools-black_lists" class="padding05"><?= lang('black_lists') ?></label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang("sales"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-sales" <?php echo $p->{'schools-sales'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-add_sale" <?php echo $p->{'schools-add_sale'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-edit_sale" <?php echo $p->{'schools-edit_sale'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-delete_sale" <?php echo $p->{'schools-delete_sale'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-number_of_student_report" class="checkbox" name="schools-number_of_student_report" <?php echo $p->{'schools-number_of_student_report'} ? "checked" : ''; ?>>
                                                <label for="schools-number_of_student_report" class="padding05"><?= lang('number_of_student_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-enrollment_by_grade_report" class="checkbox" name="schools-enrollment_by_grade_report" <?php echo $p->{'schools-enrollment_by_grade_report'} ? "checked" : ''; ?>>
                                                <label for="schools-enrollment_by_grade_report" class="padding05"><?= lang('enrollment_by_grade_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-monthly_enrollment_report" class="checkbox" name="schools-monthly_enrollment_report" <?php echo $p->{'schools-monthly_enrollment_report'} ? "checked" : ''; ?>>
                                                <label for="schools-monthly_enrollment_report" class="padding05"><?= lang('monthly_enrollment_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-yearly_enrollment_report" class="checkbox" name="schools-yearly_enrollment_report" <?php echo $p->{'schools-yearly_enrollment_report'} ? "checked" : ''; ?>>
                                                <label for="schools-yearly_enrollment_report" class="padding05"><?= lang('yearly_enrollment_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-monthly_tuition_fee_report" class="checkbox" name="schools-monthly_tuition_fee_report" <?php echo $p->{'schools-monthly_tuition_fee_report'} ? "checked" : ''; ?>>
                                                <label for="schools-monthly_tuition_fee_report" class="padding05"><?= lang('monthly_tuition_fee_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-monthly_payment_report" class="checkbox" name="schools-monthly_payment_report" <?php echo $p->{'schools-monthly_payment_report'} ? "checked" : ''; ?>>
                                                <label for="schools-monthly_payment_report" class="padding05"><?= lang('monthly_payment_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-student_fee_report" class="checkbox" name="schools-student_fee_report" <?php echo $p->{'schools-student_fee_report'} ? "checked" : ''; ?>>
                                                <label for="schools-student_fee_report" class="padding05"><?= lang('student_fee_report') ?></label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang("teachers"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teachers" <?php echo $p->{'schools-teachers'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teachers-add" <?php echo $p->{'schools-teachers-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teachers-edit" <?php echo $p->{'schools-teachers-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teachers-delete" <?php echo $p->{'schools-teachers-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-teacher_report" class="checkbox" name="schools-teacher_report" <?php echo $p->{'schools-teacher_report'} ? "checked" : ''; ?>>
                                                <label for="schools-teacher_report" class="padding05"><?= lang('teacher_report') ?></label>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?= lang("attendances"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-attendances" <?php echo $p->{'schools-attendances'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-attendances-add" <?php echo $p->{'schools-attendances-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-attendances-edit" <?php echo $p->{'schools-attendances-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-attendances-delete" <?php echo $p->{'schools-attendances-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-attendance_report" class="checkbox" name="schools-attendance_report" <?php echo $p->{'schools-attendance_report'} ? "checked" : ''; ?>>
                                                <label for="schools-attendance_report" class="padding05"><?= lang('attendance_report') ?></label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang("teacher_attendances"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances" <?php echo $p->{'schools-teacher_attendances'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances-add" <?php echo $p->{'schools-teacher_attendances-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances-edit" <?php echo $p->{'schools-teacher_attendances-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-teacher_attendances-delete" <?php echo $p->{'schools-teacher_attendances-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-teacher_attendance_report" class="checkbox" name="schools-teacher_attendance_report" <?php echo $p->{'schools-teacher_attendance_report'} ? "checked" : ''; ?>>
                                                <label for="schools-teacher_attendance_report" class="padding05"><?= lang('teacher_attendance_report') ?></label>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><?= lang("examinations"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-examinations" <?php echo $p->{'schools-examinations'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-examinations-add" <?php echo $p->{'schools-examinations-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-examinations-edit" <?php echo $p->{'schools-examinations-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-examinations-delete" <?php echo $p->{'schools-examinations-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            
                                            <td>
                                                <input type="checkbox" value="1" id="schools-study_info_report" class="checkbox" name="schools-study_info_report" <?php echo $p->{'schools-study_info_report'} ? "checked" : ''; ?>>
                                                <label for="schools-study_info_report" class="padding05"><?= lang('study_info_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-examanition_report" class="checkbox" name="schools-examanition_report" <?php echo $p->{'schools-examanition_report'} ? "checked" : ''; ?>>
                                                <label for="schools-examanition_report" class="padding05"><?= lang('examanition_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-monthly_class_result_report" class="checkbox" name="schools-monthly_class_result_report" <?php echo $p->{'schools-monthly_class_result_report'} ? "checked" : ''; ?>>
                                                <label for="schools-monthly_class_result_report" class="padding05"><?= lang('monthly_class_result_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-monthly_top_five_report" class="checkbox" name="schools-monthly_top_five_report" <?php echo $p->{'schools-monthly_top_five_report'} ? "checked" : ''; ?>>
                                                <label for="schools-monthly_top_five_report" class="padding05"><?= lang('monthly_top_five_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-section_by_month_report" class="checkbox" name="schools-section_by_month_report" <?php echo $p->{'schools-section_by_month_report'} ? "checked" : ''; ?>>
                                                <label for="schools-section_by_month_report" class="padding05"><?= lang('section_by_month_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-sectionly_class_result_report" class="checkbox" name="schools-sectionly_class_result_report" <?php echo $p->{'schools-sectionly_class_result_report'} ? "checked" : ''; ?>>
                                                <label for="schools-sectionly_class_result_report" class="padding05"><?= lang('sectionly_class_result_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-class_result_report" class="checkbox" name="schools-class_result_report" <?php echo $p->{'schools-class_result_report'} ? "checked" : ''; ?>>
                                                <label for="schools-class_result_report" class="padding05"><?= lang('class_result_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-yearly_class_result_report" class="checkbox" name="schools-yearly_class_result_report" <?php echo $p->{'schools-yearly_class_result_report'} ? "checked" : ''; ?>>
                                                <label for="schools-yearly_class_result_report" class="padding05"><?= lang('yearly_class_result_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-yearly_top_five_report" class="checkbox" name="schools-yearly_top_five_report" <?php echo $p->{'schools-yearly_top_five_report'} ? "checked" : ''; ?>>
                                                <label for="schools-yearly_top_five_report" class="padding05"><?= lang('yearly_top_five_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-yearly_subject_result_report" class="checkbox" name="schools-yearly_subject_result_report" <?php echo $p->{'schools-yearly_subject_result_report'} ? "checked" : ''; ?>>
                                                <label for="schools-yearly_subject_result_report" class="padding05"><?= lang('yearly_subject_result_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-sectionly_subject_result_report" class="checkbox" name="schools-sectionly_subject_result_report" <?php echo $p->{'schools-sectionly_subject_result_report'} ? "checked" : ''; ?>>
                                                <label for="schools-sectionly_subject_result_report" class="padding05"><?= lang('sectionly_subject_result_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-result_by_student_form" class="checkbox" name="schools-result_by_student_form" <?php echo $p->{'schools-result_by_student_form'} ? "checked" : ''; ?>>
                                                <label for="schools-result_by_student_form" class="padding05"><?= lang('result_by_student_form') ?></label>
                                                <input type="checkbox" value="1" id="schools-monthly_top_five_form" class="checkbox" name="schools-monthly_top_five_form" <?php echo $p->{'schools-monthly_top_five_form'} ? "checked" : ''; ?>>
                                                <label for="schools-monthly_top_five_form" class="padding05"><?= lang('monthly_top_five_form') ?></label>
                                                <input type="checkbox" value="1" id="schools-yearly_top_five_form" class="checkbox" name="schools-yearly_top_five_form" <?php echo $p->{'schools-yearly_top_five_form'} ? "checked" : ''; ?>>
                                                <label for="schools-yearly_top_five_form" class="padding05"><?= lang('yearly_top_five_form') ?></label>
                                                <input type="checkbox" value="1" id="schools-best_student_by_grade_report" class="checkbox" name="schools-best_student_by_grade_report" <?php echo $p->{'schools-best_student_by_grade_report'} ? "checked" : ''; ?>>
                                                <label for="schools-best_student_by_grade_report" class="padding05"><?= lang('best_student_by_grade_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-failure_student_by_year_report" class="checkbox" name="schools-failure_student_by_year_report" <?php echo $p->{'schools-failure_student_by_year_report'} ? "checked" : ''; ?>>
                                                <label for="schools-failure_student_by_year_report" class="padding05"><?= lang('failure_student_by_year_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-overview_chart" class="checkbox" name="schools-overview_chart" <?php echo $p->{'schools-overview_chart'} ? "checked" : ''; ?>>
                                                <label for="schools-overview_chart" class="padding05"><?= lang('overview_chart') ?></label>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?= lang("tickets"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-tickets" <?php echo $p->{'schools-tickets'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-add_ticket" <?php echo $p->{'schools-add_ticket'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-edit_ticket" <?php echo $p->{'schools-edit_ticket'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-delete_ticket" <?php echo $p->{'schools-delete_ticket'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-feedback_questions" class="checkbox" name="schools-feedback_questions" <?php echo $p->{'schools-feedback_questions'} ? "checked" : ''; ?>>
                                                <label for="schools-feedback_questions" class="padding05"><?= lang('feedback_questions') ?></label>
                                                <input type="checkbox" value="1" id="schools-assign_ticket" class="checkbox" name="schools-assign_ticket" <?php echo $p->{'schools-assign_ticket'} ? "checked" : ''; ?>>
                                                <label for="schools-assign_ticket" class="padding05"><?= lang('assign_ticket') ?></label>
                                                <input type="checkbox" value="1" id="schools-ticket-solution" class="checkbox" name="schools-ticket-solution" <?php echo $p->{'schools-ticket-solution'} ? "checked" : ''; ?>>
                                                <label for="schools-ticket-solution" class="padding05"><?= lang('ticket_solution') ?></label>
                                                <input type="checkbox" value="1" id="schools-response_ticket" class="checkbox" name="schools-response_ticket" <?php echo $p->{'schools-response_ticket'} ? "checked" : ''; ?>>
                                                <label for="schools-response_ticket" class="padding05"><?= lang('response_ticket') ?></label>
                                                <input type="checkbox" value="1" id="schools-ticket_report" class="checkbox" name="schools-ticket_report" <?php echo $p->{'schools-ticket_report'} ? "checked" : ''; ?>>
                                                <label for="schools-ticket_report" class="padding05"><?= lang('ticket_report') ?></label>
                                                
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><?= lang("waitings"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-waitings" <?php echo $p->{'schools-waitings'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-add_waiting" <?php echo $p->{'schools-add_waiting'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-edit_waiting" <?php echo $p->{'schools-edit_waiting'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-delete_waiting" <?php echo $p->{'schools-delete_waiting'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-waiting_report" class="checkbox" name="schools-waiting_report" <?php echo $p->{'schools-waiting_report'} ? "checked" : ''; ?>>
                                                <label for="schools-waiting_report" class="padding05"><?= lang('waiting_report') ?></label>
                                                
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><?= lang("testings"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-testings" <?php echo $p->{'schools-testings'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-add_testing" <?php echo $p->{'schools-add_testing'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-edit_testing" <?php echo $p->{'schools-edit_testing'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-delete_testing" <?php echo $p->{'schools-delete_testing'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-testing_groups" class="checkbox" name="schools-testing_groups" <?php echo $p->{'schools-testing_groups'} ? "checked" : ''; ?>>
                                                <label for="schools-testing_groups" class="padding05"><?= lang('testing_groups') ?></label>
                                                <input type="checkbox" value="1" id="schools-testing_results" class="checkbox" name="schools-testing_results" <?php echo $p->{'schools-testing_results'} ? "checked" : ''; ?>>
                                                <label for="schools-testing_results" class="padding05"><?= lang('testing_results') ?></label>
                                                <input type="checkbox" value="1" id="schools-update_result" class="checkbox" name="schools-update_result" <?php echo $p->{'schools-update_result'} ? "checked" : ''; ?>>
                                                <label for="schools-update_result" class="padding05"><?= lang('update_result') ?></label>
                                                <input type="checkbox" value="1" id="schools-testing_report" class="checkbox" name="schools-testing_report" <?php echo $p->{'schools-testing_report'} ? "checked" : ''; ?>>
                                                <label for="schools-testing_report" class="padding05"><?= lang('testing_report') ?></label>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><?= lang("student_statuses"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-student_statuses" <?php echo $p->{'schools-student_statuses'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-add_student_status" <?php echo $p->{'schools-add_student_status'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-edit_student_status" <?php echo $p->{'schools-edit_student_status'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="schools-delete_student_status" <?php echo $p->{'schools-delete_student_status'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="schools-add_reenrollment" class="checkbox" name="schools-add_reenrollment" <?php echo $p->{'schools-add_reenrollment'} ? "checked" : ''; ?>>
                                                <label for="schools-add_reenrollment" class="padding05"><?= lang('add_reenrollment') ?></label>
                                                <input type="checkbox" value="1" id="schools-set_student_status_review" class="checkbox" name="schools-set_student_status_review" <?php echo $p->{'schools-set_student_status_review'} ? "checked" : ''; ?>>
                                                <label for="schools-set_student_status_review" class="padding05"><?= lang('set_student_status_review') ?></label>
                                                <input type="checkbox" value="1" id="schools-student_status_report" class="checkbox" name="schools-student_status_report" <?php echo $p->{'schools-student_status_report'} ? "checked" : ''; ?>>
                                                <label for="schools-student_status_report" class="padding05"><?= lang('student_status_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-suspension_report" class="checkbox" name="schools-suspension_report" <?php echo $p->{'schools-suspension_report'} ? "checked" : ''; ?>>
                                                <label for="schools-suspension_report" class="padding05"><?= lang('suspension_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-dropping_out_report" class="checkbox" name="schools-dropping_out_report" <?php echo $p->{'schools-dropping_out_report'} ? "checked" : ''; ?>>
                                                <label for="schools-dropping_out_report" class="padding05"><?= lang('dropping_out_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-reconfirmation_report" class="checkbox" name="schools-reconfirmation_report" <?php echo $p->{'schools-reconfirmation_report'} ? "checked" : ''; ?>>
                                                <label for="schools-reconfirmation_report" class="padding05"><?= lang('reconfirmation_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-black_list_report" class="checkbox" name="schools-black_list_report" <?php echo $p->{'schools-black_list_report'} ? "checked" : ''; ?>>
                                                <label for="schools-black_list_report" class="padding05"><?= lang('black_list_report') ?></label>
                                                <input type="checkbox" value="1" id="schools-graduation_report" class="checkbox" name="schools-graduation_report" <?php echo $p->{'schools-graduation_report'} ? "checked" : ''; ?>>
                                                <label for="schools-graduation_report" class="padding05"><?= lang('graduation_report') ?></label>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="hr" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">

                                    <thead>
                                   
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="5" class="text-center"><?= lang('permissions'); ?></th>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><?= lang('view'); ?></th>
                                        <th class="text-center"><?= lang('add'); ?></th>
                                        <th class="text-center"><?= lang('edit'); ?></th>
                                        <th class="text-center"><?= lang('delete'); ?></th>
                                        <th class="text-center"><?= lang('misc'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($Settings->module_hr){ ?>
                                    <tr>
                                        <td><?= lang("candidate"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-index" <?php echo $p->{'hr-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add" <?php echo $p->{'hr-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit" <?php echo $p->{'hr-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete" <?php echo $p->{'hr-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("shortlist"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-index" <?php echo $p->{'hr-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add" <?php echo $p->{'hr-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit" <?php echo $p->{'hr-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete" <?php echo $p->{'hr-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("interview"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-index" <?php echo $p->{'hr-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add" <?php echo $p->{'hr-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit" <?php echo $p->{'hr-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete" <?php echo $p->{'hr-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("training"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-index" <?php echo $p->{'hr-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add" <?php echo $p->{'hr-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit" <?php echo $p->{'hr-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete" <?php echo $p->{'hr-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="hr-positions" class="checkbox" name="hr-positions" <?php echo $p->{'hr-positions'} ? "checked" : ''; ?>>
                                            <label for="hr-positions" class="padding05"><?= lang('trainee') ?></label>
                                            <input type="checkbox" value="1" id="hr-positions" class="checkbox" name="hr-positions" <?php echo $p->{'hr-positions'} ? "checked" : ''; ?>>
                                            <label for="hr-positions" class="padding05"><?= lang('trainer') ?></label>
                                            <input type="checkbox" value="1" id="hr-positions" class="checkbox" name="hr-positions" <?php echo $p->{'hr-positions'} ? "checked" : ''; ?>>
                                            <label for="hr-positions" class="padding05"><?= lang('training_type') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("employee"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-index" <?php echo $p->{'hr-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add" <?php echo $p->{'hr-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit" <?php echo $p->{'hr-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete" <?php echo $p->{'hr-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="hr-positions" class="checkbox" name="hr-positions" <?php echo $p->{'hr-positions'} ? "checked" : ''; ?>>
                                            <label for="hr-positions" class="padding05"><?= lang('positions') ?></label>
                                            <input type="checkbox" value="1" id="hr-departments" class="checkbox" name="hr-departments" <?php echo $p->{'hr-departments'} ? "checked" : ''; ?>>
                                            <label for="hr-departments" class="padding05"><?= lang('departments') ?></label>
                                            <input type="checkbox" value="1" id="hr-groups" class="checkbox" name="hr-groups" <?php echo $p->{'hr-groups'} ? "checked" : ''; ?>>
                                            <label for="hr-groups" class="padding05"><?= lang('hr_groups') ?></label>
                                            <input type="checkbox" value="1" id="hr-employee_types" class="checkbox" name="hr-employee_types" <?php echo $p->{'hr-employee_types'} ? "checked" : ''; ?>>
                                            <label for="hr-employee_types" class="padding05"><?= lang('employee_types') ?></label>
                                            <input type="checkbox" value="1" id="hr-employees_relationships" class="checkbox" name="hr-employees_relationships" <?php echo $p->{'hr-employees_relationships'} ? "checked" : ''; ?>>
                                            <label for="hr-employees_relationships" class="padding05"><?= lang('employees_relationships') ?></label>
                                            <input type="checkbox" value="1" id="hr-tax_conditions" class="checkbox" name="hr-tax_conditions" <?php echo $p->{'hr-tax_conditions'} ? "checked" : ''; ?>>
                                            <label for="hr-tax_conditions" class="padding05"><?= lang('tax_conditions') ?></label>
                                            <input type="checkbox" value="1" id="hr-leave_types" class="checkbox" name="hr-leave_types" <?php echo $p->{'hr-leave_types'} ? "checked" : ''; ?>>
                                            <label for="hr-leave_types" class="padding05"><?= lang('leave_types') ?></label>
                                            <input type="checkbox" value="1" id="hr-employees_report" class="checkbox" name="hr-employees_report" <?php echo $p->{'hr-employees_report'} ? "checked" : ''; ?>>
                                            <label for="hr-employees_report" class="padding05"><?= lang('employees_report') ?></label>
                                            <input type="checkbox" value="1" id="hr-banks_report" class="checkbox" name="hr-banks_report" <?php echo $p->{'hr-banks_report'} ? "checked" : ''; ?>>
                                            <label for="hr-banks_report" class="padding05"><?= lang('banks_report') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("kpi"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-kpi_index" <?php echo $p->{'hr-kpi_index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-kpi_add" <?php echo $p->{'hr-kpi_add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-kpi_edit" <?php echo $p->{'hr-kpi_edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-kpi_delete" <?php echo $p->{'hr-kpi_delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="hr-kpi_types" class="checkbox" name="hr-kpi_types" <?php echo $p->{'hr-kpi_types'} ? "checked" : ''; ?>>
                                            <label for="hr-kpi_types" class="padding05"><?= lang('kpi_types') ?></label>
                                            <input type="checkbox" value="1" id="hr-kpi_report" class="checkbox" name="hr-kpi_report" <?php echo $p->{'hr-kpi_report'} ? "checked" : ''; ?>>
                                            <label for="hr-kpi_report" class="padding05"><?= lang('kpi_report') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("id_card"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-id_cards" <?php echo $p->{'hr-id_cards'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add_id_card" <?php echo $p->{'hr-add_id_card'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit_id_card" <?php echo $p->{'hr-edit_id_card'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete_id_card" <?php echo $p->{'hr-delete_id_card'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="hr-sample_id_cards" class="checkbox" name="hr-sample_id_cards" <?php echo $p->{'hr-sample_id_cards'} ? "checked" : ''; ?>>
                                            <label for="hr-sample_id_cards" class="padding05"><?= lang('sample_id_cards') ?></label>
                                            <input type="checkbox" value="1" id="hr-approve_id_card" class="checkbox" name="hr-approve_id_card" <?php echo $p->{'hr-approve_id_card'} ? "checked" : ''; ?>>
                                            <label for="hr-approve_id_card" class="padding05"><?= lang('approve_id_card') ?></label>
                                            <input type="checkbox" value="1" id="hr-id_cards_report" class="checkbox" name="hr-id_cards_report" <?php echo $p->{'hr-id_cards_report'} ? "checked" : ''; ?>>
                                            <label for="hr-id_cards_report" class="padding05"><?= lang('id_cards_report') ?></label>
                                            <input type="checkbox" value="1" id="hr-id_cards_date" class="checkbox" name="hr-id_cards_date" <?php echo $p->{'hr-id_cards_date'} ? "checked" : ''; ?>>
                                            <label for="hr-id_cards_date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("salary_review"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-salary_reviews" <?php echo $p->{'hr-salary_reviews'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-add_salary_review" <?php echo $p->{'hr-add_salary_review'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-edit_salary_review" <?php echo $p->{'hr-edit_salary_review'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="hr-delete_salary_review" <?php echo $p->{'hr-delete_salary_review'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="hr-approve_salary_review" class="checkbox" name="hr-approve_salary_review" <?php echo $p->{'hr-approve_salary_review'} ? "checked" : ''; ?>>
                                            <label for="hr-approve_salary_review" class="padding05"><?= lang('approve_salary_review') ?></label>
                                            <input type="checkbox" value="1" id="hr-salary_reviews_report" class="checkbox" name="hr-salary_reviews_report" <?php echo $p->{'hr-salary_reviews_report'} ? "checked" : ''; ?>>
                                            <label for="hr-salary_reviews_report" class="padding05"><?= lang('salary_reviews_report') ?></label>
                                            <input type="checkbox" value="1" id="hr-salary_reviews_date" class="checkbox" name="hr-salary_reviews_date" <?php echo $p->{'hr-salary_reviews_date'} ? "checked" : ''; ?>>
                                            <label for="hr-salary_reviews_date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <?php } if($Settings->attendance){ ?>       
                                    <tr>
                                        <td><?= lang("attendances"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendances-check_in_outs" <?php echo $p->{'attendances-check_in_outs'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendances-add_check_in_out" <?php echo $p->{'attendances-add_check_in_out'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendances-edit_check_in_out" <?php echo $p->{'attendances-edit_check_in_out'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="attendances-delete_check_in_out" <?php echo $p->{'attendances-delete_check_in_out'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="attendances-generate_attendances" class="checkbox" name="attendances-generate_attendances" <?php echo $p->{'attendances-generate_attendances'} ? "checked" : ''; ?>>
                                            <label for="attendances-generate_attendances" class="padding05"><?= lang('generate_attendances') ?></label>
                                            <input type="checkbox" value="1" id="attendances-take_leaves" class="checkbox" name="attendances-take_leaves" <?php echo $p->{'attendances-take_leaves'} ? "checked" : ''; ?>>
                                            <label for="attendances-take_leaves" class="padding05"><?= lang('take_leaves') ?></label>
                                            <input type="checkbox" value="1" id="attendances-approve_take_leave" class="checkbox" name="attendances-approve_take_leave" <?php echo $p->{'attendances-approve_take_leave'} ? "checked" : ''; ?>>
                                            <label for="attendances-approve_take_leave" class="padding05"><?= lang('approve_take_leave') ?></label>
                                            <input type="checkbox" value="1" id="attendances-approve_attendances" class="checkbox" name="attendances-approve_attendances" <?php echo $p->{'attendances-approve_attendances'} ? "checked" : ''; ?>>
                                            <label for="attendances-approve_attendances" class="padding05"><?= lang('approve_attendances') ?></label>
                                            <input type="checkbox" value="1" id="attendances-cancel_attendances" class="checkbox" name="attendances-cancel_attendances" <?php echo $p->{'attendances-cancel_attendances'} ? "checked" : ''; ?>>
                                            <label for="attendances-cancel_attendances" class="padding05"><?= lang('cancel_attendances') ?></label>
                                            <input type="checkbox" value="1" id="attendances-approve_ot" class="checkbox" name="attendances-approve_ot" <?php echo $p->{'attendances-approve_ot'} ? "checked" : ''; ?>>
                                            <label for="attendances-approve_ot" class="padding05"><?= lang('approve_ot') ?></label>
                                            <input type="checkbox" value="1" id="attendances-policies" class="checkbox" name="attendances-policies" <?php echo $p->{'attendances-policies'} ? "checked" : ''; ?>>
                                            <label for="attendances-policies" class="padding05"><?= lang('policies') ?></label>
                                            <input type="checkbox" value="1" id="attendances-ot_policies" class="checkbox" name="attendances-ot_policies" <?php echo $p->{'attendances-ot_policies'} ? "checked" : ''; ?>>
                                            <label for="attendances-ot_policies" class="padding05"><?= lang('ot_policies') ?></label>
                                            <input type="checkbox" value="1" id="attendances-list_devices" class="checkbox" name="attendances-list_devices" <?php echo $p->{'attendances-list_devices'} ? "checked" : ''; ?>>
                                            <label for="attendances-list_devices" class="padding05"><?= lang('devices') ?></label>
                                            
                                            <input type="checkbox" value="1" id="attendances-check_in_out_report" class="checkbox" name="attendances-check_in_out_report" <?php echo $p->{'attendances-check_in_out_report'} ? "checked" : ''; ?>>
                                            <label for="attendances-check_in_out_report" class="padding05"><?= lang('check_in_out_report') ?></label>
                                            <input type="checkbox" value="1" id="attendances-daily_attendance_report" class="checkbox" name="attendances-daily_attendance_report" <?php echo $p->{'attendances-daily_attendance_report'} ? "checked" : ''; ?>>
                                            <label for="attendances-daily_attendance_report" class="padding05"><?= lang('daily_attendance_report') ?></label>
                                            <input type="checkbox" value="1" id="attendances-montly_attendance_report" class="checkbox" name="attendances-montly_attendance_report" <?php echo $p->{'attendances-montly_attendance_report'} ? "checked" : ''; ?>>
                                            <label for="attendances-montly_attendance_report" class="padding05"><?= lang('montly_attendance_report') ?></label>
                                            <input type="checkbox" value="1" id="attendances-attendance_department_report" class="checkbox" name="attendances-attendance_department_report" <?php echo $p->{'attendances-attendance_department_report'} ? "checked" : ''; ?>>
                                            <label for="attendances-attendance_department_report" class="padding05"><?= lang('attendance_department_report') ?></label>
                                            <input type="checkbox" value="1" id="attendances-employee_leave_report" class="checkbox" name="attendances-employee_leave_report" <?php echo $p->{'attendances-employee_leave_report'} ? "checked" : ''; ?>>
                                            <label for="attendances-employee_leave_report" class="padding05"><?= lang('employee_leave_report') ?></label>
                                            <input type="checkbox" value="1" id="attendances-date" class="checkbox" name="attendances-date" <?php echo $p->{'attendances-date'} ? "checked" : ''; ?>>
                                            <label for="attendances-date" class="padding05"><?= lang('date') ?></label>
                                            
                                        </td>
                                    </tr>
                                    <?php } if($Settings->payroll){ ?>    
                                    <tr>
                                        <td><?= lang("cash_advances"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-cash_advances" <?php echo $p->{'payrolls-cash_advances'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-add_cash_advance" <?php echo $p->{'payrolls-add_cash_advance'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-edit_cash_advance" <?php echo $p->{'payrolls-edit_cash_advance'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-delete_cash_advance" <?php echo $p->{'payrolls-delete_cash_advance'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="payrolls-approve_cash_advance" class="checkbox" name="payrolls-approve_cash_advance" <?php echo $p->{'payrolls-approve_cash_advance'} ? "checked" : ''; ?>>
                                            <label for="payrolls-approve_cash_advance" class="padding05"><?= lang('approve_cash_advance') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-payback" class="checkbox" name="payrolls-payback" <?php echo $p->{'payrolls-payback'} ? "checked" : ''; ?>>
                                            <label for="payrolls-payback" class="padding05"><?= lang('payback') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-cash_advances_report" class="checkbox" name="payrolls-cash_advances_report" <?php echo $p->{'payrolls-cash_advances_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-cash_advances_report" class="padding05"><?= lang('cash_advances_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-cash_advances_date" class="checkbox" name="payrolls-cash_advances_date" <?php echo $p->{'payrolls-cash_advances_date'} ? "checked" : ''; ?>>
                                            <label for="payrolls-cash_advances_date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("benefits"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-benefits" <?php echo $p->{'payrolls-benefits'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-add_benefit" <?php echo $p->{'payrolls-add_benefit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-edit_benefit" <?php echo $p->{'payrolls-edit_benefit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-delete_benefit" <?php echo $p->{'payrolls-delete_benefit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="payrolls-additions" class="checkbox" name="payrolls-additions" <?php echo $p->{'payrolls-additions'} ? "checked" : ''; ?>>
                                            <label for="payrolls-additions" class="padding05"><?= lang('additions') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-deductions" class="checkbox" name="payrolls-deductions" <?php echo $p->{'payrolls-deductions'} ? "checked" : ''; ?>>
                                            <label for="payrolls-deductions" class="padding05"><?= lang('deductions') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-approve_benefit" class="checkbox" name="payrolls-approve_benefit" <?php echo $p->{'payrolls-approve_benefit'} ? "checked" : ''; ?>>
                                            <label for="payrolls-approve_benefit" class="padding05"><?= lang('approve_benefit') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-benefits_report" class="checkbox" name="payrolls-benefits_report" <?php echo $p->{'payrolls-benefits_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-benefits_report" class="padding05"><?= lang('benefits_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-benefit_details_report" class="checkbox" name="payrolls-benefit_details_report" <?php echo $p->{'payrolls-benefit_details_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-benefit_details_report" class="padding05"><?= lang('benefit_details_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-benefits_date" class="checkbox" name="payrolls-benefits_date" <?php echo $p->{'payrolls-benefits_date'} ? "checked" : ''; ?>>
                                            <label for="payrolls-benefits_date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("salaries"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-salaries" <?php echo $p->{'payrolls-salaries'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-add_salary" <?php echo $p->{'payrolls-add_salary'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-edit_salary" <?php echo $p->{'payrolls-edit_salary'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-delete_salary" <?php echo $p->{'payrolls-delete_salary'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="payrolls-approve_salary" class="checkbox" name="payrolls-approve_salary" <?php echo $p->{'payrolls-approve_salary'} ? "checked" : ''; ?>>
                                            <label for="payrolls-approve_salary" class="padding05"><?= lang('approve_salary') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-salaries_report" class="checkbox" name="payrolls-salaries_report" <?php echo $p->{'payrolls-salaries_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-salaries_report" class="padding05"><?= lang('salaries_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-salary_details_report" class="checkbox" name="payrolls-salary_details_report" <?php echo $p->{'payrolls-salary_details_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-salary_details_report" class="padding05"><?= lang('salary_details_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-salary_banks_report" class="checkbox" name="payrolls-salary_banks_report" <?php echo $p->{'payrolls-salary_banks_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-salary_banks_report" class="padding05"><?= lang('salary_banks_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-payslips_report" class="checkbox" name="payrolls-payslips_report" <?php echo $p->{'payrolls-payslips_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-payslips_report" class="padding05"><?= lang('payslips_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-salaries_date" class="checkbox" name="payrolls-salaries_date" <?php echo $p->{'payrolls-salaries_date'} ? "checked" : ''; ?>>
                                            <label for="payrolls-salaries_date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("payments"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-payments" <?php echo $p->{'payrolls-payments'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-add_payment" <?php echo $p->{'payrolls-add_payment'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-edit_payment" <?php echo $p->{'payrolls-edit_payment'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox" name="payrolls-delete_payment" <?php echo $p->{'payrolls-delete_payment'} ? "checked" : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="checkbox" value="1" id="payrolls-payments_report" class="checkbox" name="payrolls-payments_report" <?php echo $p->{'payrolls-payments_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-payments_report" class="padding05"><?= lang('payments_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-payment_details_report" class="checkbox" name="payrolls-payment_details_report" <?php echo $p->{'payrolls-payment_details_report'} ? "checked" : ''; ?>>
                                            <label for="payrolls-payment_details_report" class="padding05"><?= lang('payment_details_report') ?></label>
                                            <input type="checkbox" value="1" id="payrolls-payments_date" class="checkbox" name="payrolls-payments_date" <?php echo $p->{'payrolls-payments_date'} ? "checked" : ''; ?>>
                                            <label for="payrolls-payments_date" class="padding05"><?= lang('date') ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang("payroll"); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="payroll-index" <?php echo $p->{'payroll-index'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="payroll-add" <?php echo $p->{'payroll-add'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="payroll-edit" <?php echo $p->{'payroll-edit'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="payroll-delete" <?php echo $p->{'payroll-delete'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="payroll-import" <?php echo $p->{'payroll-import'} ? "checked" : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" value="1" class="checkbox"
                                                   name="payroll-export" <?php echo $p->{'payroll-export'} ? "checked" : ''; ?>>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <?php } ?>
                                     </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="accounting" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                                <?php if($Settings->module_account) { ?>
                                                <div class="col-md-6">
                                                    <input type="checkbox" value="1" id="account-bill_receipt" class="checkbox"
                                                    name="account-bill_receipt" <?php echo $p->{'account-bill_receipt'} ? "checked" : ''; ?>><label
                                                    for="account-bill_receipt" class="padding05"><?= lang('account-bill_receipt') ?></label>
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
                                                    <input type="checkbox" value="1" id="bank_reconcile" class="checkbox"
                                                    name="bank_reconcile" <?php echo $p->{'bank_reconcile'} ? "checked" : ''; ?>><label
                                                    for="bank_reconcile" class="padding05"><?= lang('bank_reconcile') ?></label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="checkbox" value="1" id="account_setting" class="checkbox"
                                                    name="account_setting" <?php echo $p->{'account_setting'} ? "checked" : ''; ?>><label
                                                    for="account_setting" class="padding05"><?= lang('account_setting') ?></label>
                                                </div>
                                                <?php } ?> 
                                              </div>                                        
                                            </td>
                                        </tr>
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
                                                    <input type="checkbox" value="1" class="checkbox" id="profit_loss" name="reports-profit_loss" <?php echo $p->{'reports-profit_loss'} ? 'checked' : ''; ?>>
                                                    <label for="profit_loss" class="padding05">
                                                        <?= lang('profit_loss') ?></label>
                                                </span></br>
                                                <?php if($Settings->module_account){?>
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
                                                        
                                                    <input type="checkbox" value="1" class="checkbox" id="reports-cashflow"
                                                        name="reports-cashflow" <?php echo $p->{'reports-cashflow'}? "checked" : '';?>>
                                                        <label for="reports-cashflow" class="padding05"><?= lang('reports-cashflow') ?></label><br/>
                                                      
                                                </div>
                                                <?php }?>
                                            </div>
                                        </td>

                                    </tr>

                                   
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div id="project" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="property" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
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
                                    <?php 
                                        if($Settings->module_property){?>
                                        <tr>
                                            <td><?= lang("property"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="property-index" <?php echo $p->{'property-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="property-add" <?php echo $p->{'property-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="property-edit" <?php echo $p->{'property-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="property-delete" <?php echo $p->{'property-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="property-import" <?php echo $p->{'property-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="property-export" <?php echo $p->{'property-export'} ? "checked" : ''; ?>>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang("loan"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="loan-index" <?php echo $p->{'loan-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="loan-add" <?php echo $p->{'loan-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="loan-edit" <?php echo $p->{'loan-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="loan-delete" <?php echo $p->{'loan-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="loan-import" <?php echo $p->{'loan-import'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox"
                                                       name="loan-export" <?php echo $p->{'loan-export'} ? "checked" : ''; ?>>
                                            </td>
                                            <td></td>
                                        </tr>
                                    <?php }?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="installment" class="tab-pane fade">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped reports-table">
                                    <thead>
                                    <tr>
                                        <th rowspan="4" style="width: 12%;" class="text-center"><?= lang('module_name'); ?></th>
                                        <th colspan="7" class="text-center"><?= lang('permissions'); ?></th>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><?= lang('view'); ?></th>
                                        <th class="text-center"><?= lang('add'); ?></th>
                                        <th class="text-center"><?= lang('edit'); ?></th>
                                        <th class="text-center"><?= lang('delete'); ?></th>
                                        <th class="text-center"><?= lang('misc'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if($Settings->module_installment){ ?>
                                        <tr>
                                            <td><?= lang("installments"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="installments-index" <?php echo $p->{'installments-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="installments-add" <?php echo $p->{'installments-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="installments-edit" <?php echo $p->{'installments-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="installments-delete" <?php echo $p->{'installments-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="installments-payments" class="checkbox" name="installments-payments" <?php echo $p->{'installments-payments'} ? "checked" : ''; ?>>
                                                <label for="installments-payments" class="padding05"><?= lang('payments') ?></label>
                                                
                                                <input type="checkbox" value="1" id="installments-payoff" class="checkbox" name="installments-payoff" <?php echo $p->{'installments-payoff'} ? "checked" : ''; ?>>
                                                <label for="installments-payoff" class="padding05"><?= lang('payoff') ?></label>
                                                
                                                <input type="checkbox" value="1" id="installments-inactive" class="checkbox" name="installments-inactive" <?php echo $p->{'installments-inactive'} ? "checked" : ''; ?>>
                                                <label for="installments-inactive" class="padding05"><?= lang('inactive') ?></label>
                                                <input type="checkbox" value="1" id="installments-penalty" class="checkbox" name="installments-penalty" <?php echo $p->{'installments-penalty'} ? "checked" : ''; ?>>
                                                <label for="installments-penalty" class="padding05"><?= lang('penalty') ?></label>
                                                <input type="checkbox" value="1" id="installments-date" class="checkbox" name="installments-date" <?php echo $p->{'installments-date'} ? "checked" : ''; ?>>
                                                <label for="installments-date" class="padding05"><?= lang('date') ?></label>
                                                
                                            </td>
                                        </tr>
                                    <?php } if($Settings->module_loan){ ?>
                                        <tr>
                                            <td><?= lang("loans"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="loans-index" <?php echo $p->{'loans-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                n/a
                                            </td>
                                            <td class="text-center">
                                                n/a
                                            </td>
                                            <td class="text-center">
                                                n/a
                                            </td>
                                            <td>
                                                
                                                <input type="checkbox" value="1" id="loans-schedule-add" class="checkbox" name="loans-schedule-add" <?php echo $p->{'loans-schedule-add'} ? "checked" : ''; ?>>
                                                <label for="loans-schedule-add" class="padding05"><?= lang('add_schedule') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-schedule-edit" class="checkbox" name="loans-schedule-edit" <?php echo $p->{'loans-schedule-edit'} ? "checked" : ''; ?>>
                                                <label for="loans-schedule-edit" class="padding05"><?= lang('edit_schedule') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-date" class="checkbox" name="loans-date" <?php echo $p->{'loans-date'} ? "checked" : ''; ?>>
                                                <label for="loans-date" class="padding05"><?= lang('date') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-payment-schedule" class="checkbox" name="loans-payment-schedule" <?php echo $p->{'loans-payment-schedule'} ? "checked" : ''; ?>>
                                                <label for="loans-payment-schedule" class="padding05"><?= lang('payment_schedule') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-payments" class="checkbox" name="loans-payments" <?php echo $p->{'loans-payments'} ? "checked" : ''; ?>>
                                                <label for="loans-payments" class="padding05"><?= lang('payments') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-payoff" class="checkbox" name="loans-payoff" <?php echo $p->{'loans-payoff'} ? "checked" : ''; ?>>
                                                <label for="loans-payoff" class="padding05"><?= lang('payoff') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-borrowers" class="checkbox" name="loans-borrowers" <?php echo $p->{'loans-borrowers'} ? "checked" : ''; ?>>
                                                <label for="loans-borrowers" class="padding05"><?= lang('borrowers') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-borrower_types" class="checkbox" name="loans-borrower_types" <?php echo $p->{'loans-borrower_types'} ? "checked" : ''; ?>>
                                                <label for="loans-borrower_types" class="padding05"><?= lang('borrower_types') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-loan_products" class="checkbox" name="loans-loan_products" <?php echo $p->{'loans-loan_products'} ? "checked" : ''; ?>>
                                                <label for="loans-loan_products" class="padding05"><?= lang('loan_products') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-collaterals" class="checkbox" name="loans-collaterals" <?php echo $p->{'loans-loan_products'} ? "checked" : ''; ?>>
                                                <label for="loans-collaterals" class="padding05"><?= lang('collaterals') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-guarantors" class="checkbox" name="loans-guarantors" <?php echo $p->{'loans-guarantors'} ? "checked" : ''; ?>>
                                                <label for="loans-guarantors" class="padding05"><?= lang('guarantors') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-charges" class="checkbox" name="loans-charges" <?php echo $p->{'loans-charges'} ? "checked" : ''; ?>>
                                                <label for="loans-charges" class="padding05"><?= lang('charges') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-working_status" class="checkbox" name="loans-working_status" <?php echo $p->{'loans-working_status'} ? "checked" : ''; ?>>
                                                <label for="loans-working_status" class="padding05"><?= lang('working_status') ?></label>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang("loan_applications"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="loans-applications-index" <?php echo $p->{'loans-applications-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="loans-applications-add" <?php echo $p->{'loans-applications-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="loans-applications-edit" <?php echo $p->{'loans-applications-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="loans-applications-delete" <?php echo $p->{'loans-applications-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                
                                                <input type="checkbox" value="1" id="loans-applications-approve" class="checkbox" name="loans-applications-approve" <?php echo $p->{'loans-applications-approve'} ? "checked" : ''; ?>>
                                                <label for="loans-applications-approve" class="padding05"><?= lang('approve_application') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-applications-decline" class="checkbox" name="loans-applications-decline" <?php echo $p->{'loans-applications-decline'} ? "checked" : ''; ?>>
                                                <label for="loans-applications-decline" class="padding05"><?= lang('decline_application') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-applications-disburse" class="checkbox" name="loans-applications-disburse" <?php echo $p->{'loans-applications-disburse'} ? "checked" : ''; ?>>
                                                <label for="loans-applications-disburse" class="padding05"><?= lang('add_disbursement') ?></label>
                                                
                                                <input type="checkbox" value="1" id="loans-applications-date" class="checkbox" name="loans-applications-date" <?php echo $p->{'loans-applications-date'} ? "checked" : ''; ?>>
                                                <label for="loans-applications-date" class="padding05"><?= lang('date') ?></label>
                                                
                                            </td>
                                        </tr>
                                    <?php } if($Settings->module_save){?>
                                        <tr>
                                            <td><?= lang("savings"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="savings-index" <?php echo $p->{'savings-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="savings-add" <?php echo $p->{'savings-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="savings-edit" <?php echo $p->{'savings-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="savings-delete" <?php echo $p->{'savings-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="savings-add_deposit" class="checkbox" name="savings-add_deposit" <?php echo $p->{'savings-add_deposit'} ? "checked" : ''; ?>>
                                                <label for="savings-add_deposit" class="padding05"><?= lang('add_deposit') ?></label>
                                                
                                                <input type="checkbox" value="1" id="savings-add_withdraw" class="checkbox" name="savings-add_withdraw" <?php echo $p->{'savings-add_withdraw'} ? "checked" : ''; ?>>
                                                <label for="savings-add_withdraw" class="padding05"><?= lang('add_withdraw') ?></label>
                                                
                                                <input type="checkbox" value="1" id="savings-add_transfer" class="checkbox" name="savings-add_transfer" <?php echo $p->{'savings-add_transfer'} ? "checked" : ''; ?>>
                                                <label for="savings-add_transfer" class="padding05"><?= lang('add_transfer') ?></label>
                                                
                                                <input type="checkbox" value="1" id="savings-saving_products" class="checkbox" name="savings-saving_products" <?php echo $p->{'savings-saving_products'} ? "checked" : ''; ?>>
                                                <label for="savings-saving_products" class="padding05"><?= lang('saving_products') ?></label>
                                                
                                                <input type="checkbox" value="1" id="savings-date" class="checkbox" name="savings-date" <?php echo $p->{'savings-date'} ? "checked" : ''; ?>>
                                                <label for="savings-date" class="padding05"><?= lang('date') ?></label>
                                                
                                            </td>
                                        </tr>
                                    <?php } if($Settings->module_pawn){ ?>
                                        <tr>
                                            <td><?= lang("pawns"); ?></td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="pawns-index" <?php echo $p->{'pawns-index'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="pawns-add" <?php echo $p->{'pawns-add'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="pawns-edit" <?php echo $p->{'pawns-edit'} ? "checked" : ''; ?>>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" value="1" class="checkbox" name="pawns-delete" <?php echo $p->{'pawns-delete'} ? "checked" : ''; ?>>
                                            </td>
                                            <td>
                                                <input type="checkbox" value="1" id="pawns-returns" class="checkbox" name="pawns-returns" <?php echo $p->{'pawns-returns'} ? "checked" : ''; ?>>
                                                <label for="pawns-returns" class="padding05"><?= lang('returns') ?></label>
                                                <input type="checkbox" value="1" id="pawns-purchases" class="checkbox" name="pawns-purchases" <?php echo $p->{'pawns-purchases'} ? "checked" : ''; ?>>
                                                <label for="pawns-purchases" class="padding05"><?= lang('purchases') ?></label>
                                                <input type="checkbox" value="1" id="pawns-closes" class="checkbox" name="pawns-closes" <?php echo $p->{'pawns-closes'} ? "checked" : ''; ?>>
                                                <label for="pawns-closes" class="padding05"><?= lang('closes') ?></label>
                                                <input type="checkbox" value="1" id="pawns-products" class="checkbox" name="pawns-products" <?php echo $p->{'pawns-products'} ? "checked" : ''; ?>>
                                                <label for="pawns-products" class="padding05"><?= lang('products') ?></label>
                                                <input type="checkbox" value="1" id="pawns-payments" class="checkbox" name="pawns-payments" <?php echo $p->{'pawns-payments'} ? "checked" : ''; ?>>
                                                <label for="pawns-payments" class="padding05"><?= lang('payments') ?></label>
                                                <input type="checkbox" value="1" id="pawns-date" class="checkbox" name="pawns-date" <?php echo $p->{'pawns-date'} ? "checked" : ''; ?>>
                                                <label for="pawns-date" class="padding05"><?= lang('date') ?></label>
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
                                        <td><?= lang('reports'); ?></td>
                                        <td colspan="5">
                                            <div class="col-md-6">
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="installments" name="reports-installments" <?php echo $p->{'reports-installments'} ? 'checked' : ''; ?>>
                                                    <label for="installments" class="padding05"><?= lang('installments') ?></label>
                                                </span></br>
                                                <span style="display:inline-block;">
                                                    <input type="checkbox" value="1" class="checkbox" id="installment_payments" name="reports-installment_payments" <?php echo $p->{'reports-installment_payments'} ? 'checked' : ''; ?>>
                                                    <label for="installment_payments" class="padding05"><?= lang('installment_payments') ?></label>
                                                </span></br>
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
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
