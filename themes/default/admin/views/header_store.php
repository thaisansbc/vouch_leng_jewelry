<style type="text/css">
    #sidebar_menu > ul > li.mm_reports > ul > li > a {
        color: #2a495a;
        background-color: #8abad2;
    }
    #sidebar_menu > ul > li.mm_reports > ul > li > ul > li > a {
        color: #2a495a;
        background-color: #cddee6;   
    }
    .set_color {
        background-color: #a3d0e5 !important;
    }
</style>

    <div class="container" id="container">
        <div class="row" id="main-con">
        <table class="lt"><tr><td class="sidebar-con">
            <div id="sidebar-left">
                <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                    <ul class="nav main-menu">
                        <div class="text-center" style="border-bottom:1px solid #cccccc;padding-bottom:5px; ">
                            <a href="<?= admin_url() ?>">
                            <?php if ($Settings->logo) {
                                echo '<img width="150" height="80" src="' . base_url('assets/uploads/logos/' . $Settings->logo) . '" alt="' . $Settings->site_name . '" style="margin-bottom:0px;" />';
                            } ?>
                                <li class="mm_welcome">
                                    <span class="title"><?= $Settings->site_name; ?></span>                           
                                </li>
                            </a>
                        </div>
                        <?php
                        if ($Owner || $Admin || $Store) {
                            if($Settings->module_inventory){ ?>
                            <li class="mm_products">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-barcode"></i>
                                    <span class="text"> <?= lang('inventory'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="products_index">
                                        <a class="submenu" href="<?= admin_url('products'); ?>">
                                            <i class="fa fa-barcode"></i>
                                            <span class="text"> <?= lang('list_products'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_add">
                                        <a class="submenu" href="<?= admin_url('products/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_product'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_import_excel">
                                        <a class="submenu" href="<?= admin_url('products/import_excel'); ?>">
                                            <i class="fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_products'); ?> Excel</span>
                                        </a>
                                    </li>
                                    <li id="products_import_excel">
                                        <a class="submenu" href="<?= admin_url('products/import_products_cost_and_price_excel'); ?>">
                                            <i class="fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_products_cost_and_price_excel'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_print_barcodes">
                                        <a class="submenu" href="<?= admin_url('products/print_barcodes'); ?>">
                                            <i class="fa fa-tags"></i>
                                            <span class="text"> <?= lang('print_barcode_label'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_stock_counts">
                                        <a class="submenu" href="<?= admin_url('products/stock_counts'); ?>">
                                            <i class="fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('stock_counts'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_count_stock">
                                        <a class="submenu" href="<?= admin_url('products/count_stock'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('count_stock'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_quantity_adjustments">
                                        <a class="submenu" href="<?= admin_url('products/quantity_adjustments'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('quantity_adjustments'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_add_adjustment">
                                        <a class="submenu" href="<?= admin_url('products/add_adjustment'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('add_adjustment'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($Settings->stock_received) { ?>
                                    <li id="products_stock_received">
                                        <a class="submenu" href="<?= admin_url('products/stock_received'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('stock_received'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="transfers_index">
                                        <a class="submenu" href="<?= admin_url('transfers'); ?>">
                                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="transfers_add">
                                        <a class="submenu" href="<?= admin_url('transfers/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                   
                       <?php } 
                            if($Settings->module_purchase){ ?>
                            <li class="mm_purchases">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-star"></i>
                                    <span class="text"> <?= lang('purchases'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if($Settings->multi_level){ ?>
                                    <li id="purchases_request_index">
                                        <a class="submenu" href="<?= admin_url('purchases_request'); ?>">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('list_purchase_request'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_request_add">
                                        <a class="submenu" href="<?= admin_url('purchases_request/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase_request'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_purchase_order">
                                        <a class="submenu" href="<?= admin_url('purchases_order'); ?>">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('list_purchase_order'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_purchase_order">
                                        <a class="submenu" href="<?= admin_url('purchases_order/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="purchases_index">
                                        <a class="submenu" href="<?= admin_url('purchases'); ?>">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('list_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add">
                                        <a class="submenu" href="<?= admin_url('purchases/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_purchase_by_csv">
                                        <a class="submenu" href="<?= admin_url('purchases/purchase_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('purchase_by_excel'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_expenses">
                                        <a class="submenu" href="<?= admin_url('purchases/expenses'); ?>">
                                            <i class="fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_expenses'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_expense">
                                        <a class="submenu" href="<?= admin_url('purchases/add_expense'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_expense'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_expense_by_csv">
                                        <a class="submenu" href="<?= admin_url('purchases/expense_by_csv'); ?>">
                                            <i class="fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_expense'); ?></span>
                                        </a>
                                    </li>
                                    <?php if($Settings->multi_level){ ?>
                                    <li id="purchases_expenses_budget">
                                        <a class="submenu" href="<?= admin_url('purchases/expenses_budget'); ?>">
                                            <i class="fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_expenses_budget'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_expense_budget">
                                        <a class="submenu" href="<?= admin_url('purchases/add_expense_budget'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_expense_budget'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_budgets">
                                        <a class="submenu" href="<?= admin_url('purchases/budgets'); ?>">
                                            <i class="fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_budgets'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_budget">
                                        <a class="submenu" href="<?= admin_url('purchases/add_budget'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_budget'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>
                                </ul>
                            </li>
                        <?php } 
                            if($Settings->module_sale){ ?>
                            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-heart"></i>
                                    <span class="text"> <?= lang('sales'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                         
                                    <li id="sales_index">
                                        <a class="submenu" href="<?= admin_url('sales'); ?>">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('list_sales'); ?></span>
                                        </a>
                                    </li>
                                
                                    <li id="sales_add">
                                        <a class="submenu" href="<?= admin_url('sales/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale'); ?></span>
                                        </a>
                                    </li>
                   
                               
                                    <li id="sales_sale_by_csv">
                                        <a class="submenu" href="<?= admin_url('sales/sale_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale_by_csv'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_deliveries">
                                        <a class="submenu" href="<?= admin_url('sales/deliveries'); ?>">
                                            <i class="fa fa-truck"></i>
                                            <span class="text"> <?= lang('deliveries'); ?></span>
                                        </a>
                                    </li>
                               
                                    <li id="sales_gift_cards">
                                        <a class="submenu" href="<?= admin_url('sales/gift_cards'); ?>">
                                            <i class="fa fa-gift"></i>
                                            <span class="text"> <?= lang('list_gift_cards'); ?></span>
                                        </a>
                                    </li>
                   
                                    <li id="returns_index">
                                        <a class="submenu" href="<?= admin_url('returns'); ?>">
                                            <i class="fa fa-random"></i><span class="text"> <?= lang('list_returns'); ?></span>
                                        </a>
                                    </li>
                                    <li id="returns_add">
                                        <a class="submenu" href="<?= admin_url('returns/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_return'); ?></span>
                                        </a>
                                    </li>
                                    <?php if($Settings->multi_level){ ?>
                                    <li id="returns_keep">
                                        <a class="submenu" href="<?= admin_url('returns/keep'); ?>">
                                            <i class="fa fa-random"></i><span class="text"> <?= lang('keep_list'); ?></span>
                                        </a>
                                    </li>
                                    <li id="returns_addkeep">
                                        <a class="submenu" href="<?= admin_url('returns/add_keep'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_keep'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php if (POS) { ?>
                            <li class="mm_pos">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-heart"></i>
                                    <span class="text"> <?= lang('point_of_sale'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    
                                    
                                    <li id="pos_sales">
                                        <a class="submenu" href="<?= admin_url('pos/sales'); ?>">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('pos_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="pos_index">
                                        <a class="submenu" href="<?= admin_url('pos'); ?>">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('pos'); ?></span>
                                        </a>
                                    </li>    
                                 
                              
                                    <?php  
                                    if($this->pos_settings->pos_type =="table" || $this->pos_settings->pos_type =="room"){ ?>
                                    <li id="suspended_note">
                                        <a class="submenu" href="<?= admin_url('suspended_note'); ?>">
                                            <i class="fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('Table|Rooms'); ?></span>
                                        </a>
                                    </li>
                                    
                                    <?php } ?>
                                
                                </ul>
                            </li>
                            <?php }  
                            
                         }
                        ?>
                            <?php if ($Settings->module_sale && $Settings->store_sales) { ?>
                            <li class="mm_store_sales">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-heart"></i>
                                    <span class="text"> <?= lang('store_sales'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_store'); ?>">
                                            <i class="fa fa-heart-o"></i>
                                            <span class="text"> <?= lang('store_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_store/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_store_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_order_store'); ?>">
                                            <i class="fa fa-heart-o"></i>
                                            <span class="text"> <?= lang('store_sales_order'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_order_store/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_store_sales_order'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php } ?>
                    
                            <li class="mm_auth mm_suppliers mm_billers">
                                <a class="dropmenu" href="#">
                                <i class="fa fa-users"></i>
                                <span class="text"> <?= lang('people'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                <?php if ($Owner || $Store) { ?>
                                    <li id="auth_users">
                                        <a class="submenu" href="<?= admin_url('users'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_users'); ?></span>
                                        </a>
                                    </li>
                                    <li id="auth_create_user">
                                        <a class="submenu" href="<?= admin_url('users/create_user'); ?>">
                                            <i class="fa fa-user-plus"></i><span class="text"> <?= lang('new_user'); ?></span>
                                        </a>
                                    </li>
                                    
                                    <li id="billers_index">
                                        <a class="submenu" href="<?= admin_url('billers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_billers'); ?></span>
                                        </a>
                                    </li>
                    
                                    <li id="suppliers_index">
                                        <a class="submenu" href="<?= admin_url('suppliers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="suppliers_index">
                                        <a class="submenu" href="<?= admin_url('suppliers/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                                        </a>
                                    </li>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('customers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('customers/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                                        </a>
                                    </li>
                                    <li id="drivers_index">
                                        <a class="submenu" href="<?= admin_url('drivers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_drivers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="drivers_index">
                                        <a class="submenu" href="<?= admin_url('drivers/create_driver'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_driver'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_notifications">
                                <a class="submenu" href="<?= admin_url('notifications'); ?>">
                                    <i class="fa fa-info-circle"></i><span class="text"> <?= lang('notifications'); ?></span>
                                </a>
                            </li>
                            <li class="mm_calendar">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-calendar"></i>
                                    <span class="text"><?= lang('events'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="purchases_expenses">
                                        <a class="submenu" href="<?= admin_url('calendar/events'); ?>">
                                            <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('list_event'); ?></span>
                                        </a>
                                    </li>
                                    
                                    <li id="purchases_add_expense">
                                        <a class="submenu" href="<?= admin_url('calendar/add_event_to_dos'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_event'); ?></span>
                                        </a>
                                    </li>
                                    <li class="mm_calendar">
                                        <a class="submenu" href="<?= admin_url('calendar'); ?>">
                                            <i class="fa fa-calendar"></i><span class="text"> <?= lang('calendar'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_expenses">
                                        <a class="submenu" href="<?= admin_url('calendar/holidays'); ?>">
                                            <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('holidays'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php if ($Owner) { ?>
                                <li class="mm_system_settings <?= strtolower($this->router->fetch_method()) == 'sales' ? '' : 'mm_pos' ?>">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-cog"></i><span class="text"> <?= lang('settings'); ?> </span><span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <li id="system_settings_index">
                                            <a href="<?= admin_url('system_settings') ?>">
                                                <i class="fa fa-cogs"></i><span class="text"> <?= lang('system_settings'); ?></span>
                                            </a>
                                        </li>
                                        <?php if (POS) { ?>
                                        <li id="pos_settings">
                                            <a href="<?= admin_url('pos/settings') ?>">
                                                <i class="fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
                                            </a>
                                        </li>

                                        <li id="promos_index hide" style="display: none;">
                                            <a href="<?= admin_url('promos') ?>">
                                                <i class="fa fa-cogs"></i><span class="text"> <?= lang('promos'); ?></span>
                                            </a>
                                        </li>
                                        
                                        <li id="pos_printers">
                                            <a href="<?= admin_url('pos/printers') ?>">
                                                <i class="fa fa-print"></i><span class="text"> <?= lang('list_printers'); ?></span>
                                            </a>
                                        </li>
                                        <li id="pos_add_printer">
                                            <a href="<?= admin_url('pos/add_printer') ?>">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_printer'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <li id="system_settings_products_alert">
                                            <a href="<?= admin_url('system_settings/products_alert') ?>">
                                                <i class="fa fa-exclamation-circle"></i><span class="text"> <?= lang('products_alert'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_change_logo">
                                            <a href="<?= admin_url('system_settings/change_logo') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-upload"></i><span class="text"> <?= lang('change_logo'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_currencies">
                                            <a href="<?= admin_url('system_settings/currencies') ?>">
                                                <i class="fa fa-money"></i><span class="text"> <?= lang('currencies'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_customer_groups">
                                            <a href="<?= admin_url('system_settings/customer_groups') ?>">
                                                <i class="fa fa-chain"></i><span class="text"> <?= lang('customer_groups'); ?></span>
                                            </a>
                                        </li>
                                         <li id="system_settings_price_groups">
                                            <a href="<?= admin_url('system_settings/price_groups') ?>">
                                                <i class="fa fa-dollar"></i><span class="text"> <?= lang('price_groups'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_expense_categories">
                                            <a href="<?= admin_url('system_settings/expense_categories') ?>">
                                                <i class="fa fa-folder-open"></i><span class="text"> <?= lang('expense_categories'); ?></span>
                                            </a>
                                        </li>
                                        <?php if(!$Settings->module_property){?>
                                        <li id="system_settings_categories">
                                            <a href="<?= admin_url('system_settings/categories') ?>">
                                                <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_units">
                                            <a href="<?= admin_url('system_settings/units') ?>">
                                                <i class="fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_brands">
                                            <a href="<?= admin_url('system_settings/brands') ?>">
                                                <i class="fa fa-th-list"></i><span class="text"> <?= lang('brands'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_variants">
                                            <a href="<?= admin_url('system_settings/variants') ?>">
                                                <i class="fa fa-tags"></i><span class="text"> <?= lang('variants'); ?></span>
                                            </a>
                                        </li>
                                        <li id="promos_index">
                                            <a href="<?= admin_url('system_settings/promotion') ?>">
                                                <i class="fa fa-chain"></i><span class="text"> <?= lang('promotion'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_commission_product">
                                            <a href="<?= admin_url('system_settings/commission_product') ?>">
                                                <i class="fa fa-dollar"></i><span class="text"> <?= lang('commission_product'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_multi_buy_groups">
                                            <a href="<?= admin_url('system_settings/multi_buy_groups') ?>">
                                                <i class="fa fa-dollar"></i><span class="text"> <?= lang('multi_buys'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_sale_targets">
                                            <a href="<?= admin_url('system_settings/sale_targets') ?>">
                                                <i class="fa fa-bullseye"></i><span class="text"> <?= lang('sale_targets'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_zones">
                                            <a href="<?= admin_url('system_settings/zones') ?>">
                                                <i class="fa fa-wrench"></i><span class="text"> <?= lang('zones'); ?></span>
                                            </a>
                                        </li>
                                        
                                        <li id="system_settings_floors">
                                            <a href="<?= admin_url('system_settings/floors') ?>">
                                                <i class="fa fa-th"></i><span class="text"> <?= lang('floors'); ?></span>
                                            </a>
                                        </li>
                                        
                                        <li id="system_settings_variants">
                                            <a href="<?= admin_url('system_settings/options') ?>">
                                                <i class="fa fa-tags"></i><span class="text"> <?= lang('options'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_payment_term">
                                            <a href="<?= admin_url('system_settings/payment_term') ?>">
                                                <i class="fa fa-money"></i><span class="text"> <?= lang('payment_term'); ?></span>
                                            </a>
                                        </li>
                                        <?php }?>

                                        <li id="system_settings_tax_rates">
                                            <a href="<?= admin_url('system_settings/tax_rates') ?>">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_warehouses">
                                            <a href="<?= admin_url('system_settings/warehouses') ?>">
                                                <i class="fa fa-building-o"></i><span class="text"> <?= lang('warehouses'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_email_templates">
                                            <a href="<?= admin_url('system_settings/email_templates') ?>">
                                                <i class="fa fa-envelope"></i><span class="text"> <?= lang('email_templates'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_user_groups">
                                            <a href="<?= admin_url('system_settings/user_groups') ?>">
                                                <i class="fa fa-key"></i><span class="text"> <?= lang('group_permissions'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_backups">
                                            <a href="<?= admin_url('system_settings/backups') ?>">
                                                <i class="fa fa-database"></i><span class="text"> <?= lang('backups'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_audit_trail">
                                            <a href="<?= admin_url('system_settings/audit_trail') ?>">
                                                <i class="fa fa-pencil"></i><span class="text"> <?= lang('audit_trail'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_custom_field">
                                            <a href="<?= admin_url('system_settings/custom_field') ?>">
                                                <i class="fa fa-key"></i><span class="text"> <?= lang('custom_field'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_change_logo">
                                            <a href="<?= admin_url('system_settings/language') ?>">
                                                <i class="fa fa-upload"></i><span class="text"> <?= lang('change_language'); ?></span>
                                            </a>
                                        </li>
                                        <?php if ($Owner || $Admin || $GP['system_settings-telegrams']){ ?>     
                                            <li id="system_settings_telegrams">
                                                <a href="<?= admin_url('system_settings/telegrams') ?>">
                                                    <i class="fa fa-key"></i><span class="text"> <?= lang('telegrams'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if(DEMO){ ?>
                                        <li id="reset">
                                            <a href="<?= admin_url('reset/demo') ?>">
                                                <i class="fa fa-eraser"></i><span class="text"> <?= lang('reset_data'); ?></span>
                                            </a>
                                        <?php } ?>
                                        </li>
                                        <!-- <li id="system_settings_updates">
                                            <a href="<?= admin_url('system_settings/updates') ?>">
                                                <i class="fa fa-upload"></i><span class="text"> <?= lang('updates'); ?></span>
                                            </a>
                                        </li> -->
                                    </ul>
                                </li>
                            <?php } ?>
                            <li class="mm_reports">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-bar-chart-o"></i>
                                    <span class="text"> <?= lang('reports'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul class="sub-menu">
                                    <?php if($Settings->module_inventory){ ?>
                                    <li class="sub_mm_reports_inventory">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-barcode"></i>
                                            <span class="text"> <?= lang('inventory'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_index">
                                                <a href="<?= admin_url('reports') ?>">
                                                    <i class="fa fa-bars"></i><span class="text"> <?= lang('overview_chart'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_warehouse_stock">
                                                <a href="<?= admin_url('reports/warehouse_stock') ?>">
                                                    <i class="fa fa-cubes"></i><span class="text"> <?= lang('stock_value'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_category_stock">
                                                <a href="<?= admin_url('reports/category_stock') ?>">
                                                    <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('category_stock_chart'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_cash_chart">
                                                <a href="<?= admin_url('reports/cash_chart') ?>">
                                                    <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('cash_analysis_chart'); ?></span>
                                                </a>
                                            </li> 
                                            <li id="reports_quantity_alerts">
                                                <a href="<?= admin_url('reports/quantity_alerts') ?>">
                                                    <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_products">
                                                <a href="<?= admin_url('reports/products') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('products_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if ($Settings->stock_received) { ?>
                                            <li id="reports_products">
                                                <a href="<?= admin_url('reports/stock_received') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('stock_received_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li id="reports_products">
                                                <a href="<?= admin_url('reports/stock_in_out') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('products_in_out_category'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_products">
                                                <a href="<?= admin_url('reports/inventory_inout') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('products_in_out'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_costs">
                                                <a href="<?= admin_url('reports/cost_report') ?>">
                                                    <i class="fa fa-dollar"></i><span class="text"> <?= lang('cost_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_adjustments">
                                                <a href="<?= admin_url('reports/adjustments') ?>">
                                                    <i class="fa fa-filter"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_warehouse_products">
                                                <a href="<?= admin_url('reports/warehouse_products') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('warehouse_products'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_customer_sale_top">
                                                <a href="<?= admin_url('reports/report_sale_top') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('product_sale_top'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_transfers_report">
                                                <a href="<?= admin_url('reports/transfers_report') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('transfers_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_list_using_stock_report">
                                                <a href="<?= admin_url('reports/list_using_stock_report') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('list_using_stock_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_products_using_report">
                                                <a href="<?= admin_url('reports/products_using_report') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('products_using_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_consignment_report">
                                                <a href="<?= admin_url('reports/consignment') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('consignment'); ?></span>
                                                </a>
                                            </li>
                                            <?php if ($Settings->product_expiry) { ?>
                                            <li id="reports_expiry_alerts">
                                                <a href="<?= admin_url('reports/expiry_alerts') ?>">
                                                    <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li id="reports_price_groups">
                                                <a href="<?= admin_url('reports/price_groups') ?>">
                                                    <i class="fa fa-folder-open"></i><span class="text"> <?= lang('price_groups_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_categories">
                                                <a href="<?= admin_url('reports/categories') ?>">
                                                    <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_brands">
                                                <a href="<?= admin_url('reports/brands') ?>">
                                                    <i class="fa fa-cubes"></i><span class="text"> <?= lang('brands_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_purchase){ ?>
                                    <li class="sub_mm_reports_purchases">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('purchases'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_daily_purchases">
                                                <a href="<?= admin_url('reports/daily_purchases') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_monthly_purchases">
                                                <a href="<?= admin_url('reports/monthly_purchases') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_purchases">
                                                <a href="<?= admin_url('reports/purchases') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_expenses">
                                                <a href="<?= admin_url('reports/expenses') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('expenses_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_budgets">
                                                <a href="<?= admin_url('reports/budgets') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('budgets_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_expenses_budget">
                                                <a href="<?= admin_url('reports/expenses_budget') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('expenses_budget_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                        
                                    <?php if($Settings->module_sale){ ?>
                                    <li class="sub_mm_reports_sales">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('sales'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if (POS) { ?>
                                            <li id="reports_register">
                                                <a href="<?= admin_url('reports/register') ?>">
                                                    <i class="fa fa-th-large"></i><span class="text"> <?= lang('register_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_bill_sales">
                                                <a href="<?= admin_url('reports/audit_bill') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('bill_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_suspend_report" <?=($this->uri->segment(2) === 'suspends' ? 'class="active"' : '')?> >
                                                <a href="<?= admin_url('reports/suspends') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('suspend_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li id="reports_best_sellers">
                                                <a href="<?= admin_url('reports/best_sellers') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('best_sellers'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_daily_sales">
                                                <a href="<?= admin_url('reports/daily_sales') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_sales'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_monthly_sales">
                                                <a href="<?= admin_url('reports/monthly_sales') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_sales">
                                                <a href="<?= admin_url('reports/sales') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_sales_detail">
                                                <a href="<?= admin_url('reports/sales_detail') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sales_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_sales_discount">
                                                <a href="<?= admin_url('reports/sales_discount') ?>">
                                                    <i class="fa fa-gift"></i><span class="text"> <?= lang('sales_discount_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_sale_targets">
                                                <a href="<?= admin_url('reports/sale_targets') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sale_targets_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if($Settings->project){ ?>
                                            <li id="reports_project">
                                                <a href="<?= admin_url('reports/products_project') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('projects_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                             <li id="reports_commission">
                                                <a href="<?= admin_url('reports/commission') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('commission_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_sale && $Settings->store_sales){ ?>
                                    <li class="sub_mm_reports_store_sales">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('store_sales'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_register">
                                                <a href="<?= admin_url('reports/store_sales') ?>">
                                                    <i class="fa fa-th-large"></i><span class="text"> <?= lang('store_sales_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_manufacturing){ ?>
                                    <li class="sub_mm_reports_manufacturing">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-barcode"></i>
                                            <span class="text"> <?= lang('manufacturing'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_project">
                                                <a href="<?= admin_url('reports/workorder_reports') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('workorder_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_convert_reports"  class="hide">
                                                <a href="<?= admin_url('reports/convert_reports') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('convert_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_loan){ ?>
                                    <li class="sub_mm_reports_loan sub_mm_reports_pawn">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-money"></i>
                                            <span class="text"><?= lang('loans'); ?> & <?= lang('pawns'); ?>  </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if(($Owner || $Admin || $GP['reports-loans']) && $this->config->item('loan')){ ?>
                                                <li id="reports_loans">
                                                    <a href="<?= admin_url('reports/loans') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loans_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(($Owner || $Admin || $GP['reports-loan_collection']) && $this->config->item('loan')){ ?>
                                                <li id="reports_loan_collection">
                                                    <a href="<?= admin_url('reports/loan_collection') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loan_collection_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(($Owner || $Admin || $GP['reports-loan_collectable']) && $this->config->item('loan')){ ?>
                                                <li id="reports_loan_collectable">
                                                    <a href="<?= admin_url('reports/loan_collectable') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loan_collectable_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(($Owner || $Admin || $GP['reports-loan_disbursement']) && $this->config->item('loan')){ ?>
                                                <li id="reports_loan_disbursement">
                                                    <a href="<?= admin_url('reports/loan_disbursement') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loan_disbursement_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } 
                                            if(($Owner || $Admin || $GP['reports-pawns']) && $this->config->item('pawn')){ ?>
                                                <li id="reports_pawns">
                                                    <a href="<?= admin_url('reports/pawns') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('pawns_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                         <?php if(($Owner || $Admin)){ ?>
                                                <li id="reports_print_history">
                                                    <a href="<?= admin_url('reports/print_history') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('print_history_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_property){ ?>
                                    <li class="sub_mm_reports_property">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-barcode"></i>
                                            <span class="text"> <?= lang('property'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            
                                            <li id="reports_booking">
                                                <a href="<?= admin_url('reports/leasing_commission') ?>">
                                                    <i class="fa fa-filter"></i><span class="text"> <?= lang('commission_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_booking">
                                                <a href="<?= admin_url('reports/booking') ?>">
                                                    <i class="fa fa-filter"></i><span class="text"> <?= lang('booking_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_blocking">
                                                <a href="<?= admin_url('reports/blocking') ?>">
                                                    <i class="fa fa-filter"></i><span class="text"> <?= lang('blocking_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_loans">
                                                <a href="<?= admin_url('reports/leasing') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('loans_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments">
                                                <a href="<?= admin_url('reports/payments') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_profit_loss_table">
                                                <a href="<?= admin_url('reports/profit_loss_table') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('profit_and_loss_table'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    
                                    <li class="sub_mm_reports_people">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-users"></i>
                                            <span class="text"> <?= lang('people_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_supplier_report">
                                                <a href="<?= admin_url('reports/suppliers') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_customer_report">
                                                <a href="<?= admin_url('reports/customers') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_saleman">
                                                <a href="<?= admin_url('reports/saleman') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('saleman_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_saleman_detail">
                                                <a href="<?= admin_url('reports/saleman_report') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('saleman_detail_report'); ?></span>
                                                </a>
                                            </li>
                                           
                                            <li id="reports_staff_report">
                                                <a href="<?= admin_url('reports/users') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('staff_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li> 
                                    <?php if($Settings->module_hr){ ?>
                                    <li class="sub_mm_reports_hr">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-users"></i>
                                            <span class="text"> <?= lang('hr_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php 
                                             if($Owner || $Admin || $GP['hr-employees_report']) { ?>
                                                <li id="hr_employees_report">
                                                    <a class="submenu" href="<?= admin_url('hr/employees_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('employees_report'); ?></span>
                                                    </a>
                                                </li>       
                                            <?php } if($Owner || $Admin || $GP['hr-banks_report']) { ?>
                                                <li id="hr_banks_report">
                                                    <a class="submenu" href="<?= admin_url('hr/banks_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('banks_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['hr-kpi_report']) { ?>
                                                <li id="hr_kpi_report">
                                                    <a class="submenu" href="<?= admin_url('hr/kpi_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('kpi_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['hr-id_cards_report']) { ?>
                                                <li id="hr_id_cards_report">
                                                    <a class="submenu" href="<?= admin_url('hr/id_cards_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('id_cards_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['hr-salary_reviews_report']) { ?>
                                                <li id="hr_salary_reviews_report">
                                                    <a class="submenu" href="<?= admin_url('hr/salary_reviews_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('salary_reviews_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } ?>

                                        </ul>
                                    </li> 
                                    <?php } ?>
                                    <?php if($Settings->attendance){ ?>
                                    <li class="sub_mm_reports_attendance">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-users"></i>
                                            <span class="text"> <?= lang('attendance_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php 
                                              if($Owner || $Admin || $GP['attendances-check_in_out_report']){ ?>
                                                <li id="attendances_check_in_out_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/check_in_out_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('check_in_out_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-daily_attendance_report']){ ?>
                                                <li id="attendances_daily_attendance_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/daily_attendance_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('daily_attendance_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-montly_attendance_report']){ ?>
                                                <li id="attendances_montly_attendance_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/montly_attendance_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> 
                                                            <?= lang('montly_attendance_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-attendance_department_report']){ ?>
                                                <li id="attendances_attendance_department_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/attendance_department_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('attendance_department_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-employee_leave_report']){ ?>
                                                <li id="attendances_employee_leave_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/employee_leave_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('employee_leave_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="attendances_employee_leave_by_year_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/employee_leave_by_year_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('employee_leave_by_year_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } ?>

                                        </ul>
                                    </li> 
                                    <?php } ?>
                                    <?php if($Settings->payroll){ ?>
                                    <li class="sub_mm_reports_payroll">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-users"></i>
                                            <span class="text"> <?= lang('payroll_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php 
                                            if($Owner || $Admin || $GP['payrolls-cash_advances_report']) { ?>
                                                <li id="payrolls_cash_advances_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/cash_advances_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('cash_advances_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-benefits_report']) { ?>
                                                <li id="payrolls_benefits_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/benefits_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('benefits_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-benefit_details_report']) { ?>
                                                <li id="payrolls_benefit_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/benefit_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('benefit_details_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
                                                <li id="payrolls_salaries_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salaries_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('salaries_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_details_report']) { ?>
                                                <li id="payrolls_salary_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salary_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('salary_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_banks_report']) { ?>
                                                <li id="payrolls_salary_banks_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salary_banks_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('salary_banks_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
                                                <li id="payrolls_salaries_13_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salaries_13_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('salaries_13_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_details_report']) { ?>
                                                <li id="payrolls_salary_13_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salary_13_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('salary_13_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-payslips_report']) { ?>
                                                <li id="payrolls_payslips_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/payslips_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('payslips_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-payments_report']) { ?>
                                                <li id="payrolls_payments_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/payments_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-payment_details_report']) { ?>
                                                <li id="payrolls_payment_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/payment_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('payment_details_report'); ?></span>
                                                    </a>
                                                </li>
                                        <?php } ?>

                                        </ul>
                                    </li> 
                                    <?php } ?>
                                    <!-----account---------->
                                     <?php if($Settings->accounting){ ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-book"></i>
                                            <span class="text"> <?= lang('ACCOUNTS'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_tax">
                                                <a href="<?= admin_url('reports/tax') ?>">
                                                    <i class="fa fa-area-chart"></i><span class="text"> <?= lang('tax_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments">
                                                <a href="<?= admin_url('reports/payments') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments_received">
                                                <a href="<?= admin_url('reports/payments_received') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_received_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments_voucher">
                                                <a href="<?= admin_url('reports/payments_voucher') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_voucher_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_profit_loss" class="hide">
                                                <a href="<?= admin_url('reports/profit_loss') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('profit_and_loss'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_yearly_profit_loss" class="hide">
                                                <a href="<?= admin_url('reports/yearly_profit_loss') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> Yearly Profit/or Loss</span>
                                                </a>
                                            </li>
                                            <li id="reports_profit_loss_table">
                                                <a href="<?= admin_url('reports/profit_loss_table') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('profit_and_loss_table'); ?></span>
                                                </a>
                                            </li>
                                            <?php if($Settings->module_account){ ?>
                                           
                                            <li id="reports_payments">
                                                <a href="<?= admin_url('reports/payments_report') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('tansfer_payment'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments">
                                                <a href="<?= admin_url('account/tansfer_payment_report') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('tansfer_payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_ledger">
                                                <a href="<?= admin_url('reports/ledger') ?>">
                                                    <i class="fa fa-book"></i><span class="text"> <?= lang('ledger'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_trial_balance">
                                                <a href="<?= admin_url('reports/trial_balance') ?>">
                                                    <i class="fa fa-bars"></i><span class="text"> <?= lang('trial_balance'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_income_statement">
                                                <a href="<?= admin_url('reports/income_statement') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('income_statement'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_balance_sheet">
                                                <a href="<?= admin_url('reports/balance_sheet') ?>">
                                                    <i class="fa fa-balance-scale"></i><span class="text"> <?= lang('balance_sheet'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_balance_sheet">
                                                <a href="<?= admin_url('reports/balance_sheet_by_month') ?>">
                                                    <i class="fa fa-balance-scale"></i><span class="text"> <?= lang('balance_sheet_by_month'); ?></span>
                                                </a>
                                            </li>                                                            
                                            <li id="reports_cashflow">
                                                <a href="<?= admin_url('reports/cashflow') ?>">
                                                    <i class="fa fa-book"></i><span class="text"> <?= lang('cashflow_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_cash_book_report" <?=($this->uri->segment(2) === 'cash_books' ? 'class="active"' : '')?> >
                                                <a href="<?= admin_url('reports/cash_books') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('cash_book'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_ledger">
                                                <a href="<?= admin_url('reports/bank_reconcile') ?>">
                                                    <i class="fa fa-book"></i><span class="text"> <?= lang('bank_reconciliation'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_ledger">
                                                <a href="<?= admin_url('reports/reconcile_report') ?>">
                                                    <i class="fa fa-book"></i><span class="text"> <?= lang('bank_reconciliation_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <!-----account---------->
                                    <?php if($this->site->module('loan')){ ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-book"></i>
                                            <span class="text"> <?= lang('loans_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php
                                            if($Owner || $Admin || $GP['reports-loans']){ ?>
                                                <li id="reports_loans">
                                                    <a href="<?= admin_url('reports/loans') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loans_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['reports-loan_collection']){ ?>
                                                <li id="reports_loan_collection">
                                                    <a href="<?= admin_url('reports/loan_collection') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loan_collection_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['reports-loan_disbursement']){ ?>
                                                <li id="reports_loan_disbursement">
                                                    <a href="<?= admin_url('reports/loan_disbursement') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('loan_disbursement_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                            
                                </ul>
                            </li>
                            <?php 
                            if (SHOP) {
                            if ($Owner && file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'Shop.php')) { ?>
                            <li class="mm_shop_settings mm_api_settings">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-shopping-cart"></i><span class="text"> <?= lang('front_end'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="shop_settings_index">
                                        <a href="<?= admin_url('shop_settings') ?>">
                                            <i class="fa fa-cog"></i><span class="text"> <?= lang('shop_settings'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_slider">
                                        <a href="<?= admin_url('shop_settings/slider') ?>">
                                            <i class="fa fa-file"></i><span class="text"> <?= lang('slider_settings'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($Settings->apis) { ?>
                                    <li id="api_settings_index">
                                        <a href="<?= admin_url('api_settings') ?>">
                                            <i class="fa fa-key"></i><span class="text"> <?= lang('api_keys'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="shop_settings_pages">
                                        <a href="<?= admin_url('shop_settings/pages') ?>">
                                            <i class="fa fa-file"></i><span class="text"> <?= lang('list_pages'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_pages">
                                        <a href="<?= admin_url('shop_settings/add_page') ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_page'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_sms_settings">
                                        <a href="<?= admin_url('shop_settings/sms_settings') ?>">
                                            <i class="fa fa-cogs"></i><span class="text"> <?= lang('sms_settings'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_send_sms">
                                        <a href="<?= admin_url('shop_settings/send_sms') ?>">
                                            <i class="fa fa-send"></i><span class="text"> <?= lang('send_sms'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_sms_log">
                                        <a href="<?= admin_url('shop_settings/sms_log') ?>">
                                            <i class="fa fa-file-text-o"></i><span class="text"> <?= lang('sms_log'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php } } ?>
                        <?php } else { // not owner and not admin ?>
                            <?php if ($GP['products-index'] || $GP['products-add'] || $GP['products-barcode'] || $GP['products-adjustments'] || $GP['products-stock_count'] || $GP['stock_received-index'] || $GP['products-update_cost_and_price']) { ?>
                            <li class="mm_products">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-barcode"></i>
                                    <span class="text"> <?= lang('products'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="products_index">
                                        <a class="submenu" href="<?= admin_url('products'); ?>">
                                            <i class="fa fa-barcode"></i><span class="text"> <?= lang('list_products'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($GP['products-add']) { ?>
                                    <li id="products_add">
                                        <a class="submenu" href="<?= admin_url('products/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_product'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-update_cost_and_price']) { ?>
                                    <li id="products_import_excel">
                                        <a class="submenu" href="<?= admin_url('products/import_products_cost_and_price_excel'); ?>">
                                            <i class="fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_products_cost_and_price_excel'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-barcode']) { ?>
                                    <li id="products_sheet">
                                        <a class="submenu" href="<?= admin_url('products/print_barcodes'); ?>">
                                            <i class="fa fa-tags"></i><span class="text"> <?= lang('print_barcode_label'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-adjustments']) { ?>
                                    <li id="products_quantity_adjustments">
                                        <a class="submenu" href="<?= admin_url('products/quantity_adjustments'); ?>">
                                            <i class="fa fa-filter"></i><span class="text"> <?= lang('quantity_adjustments'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_add_adjustment">
                                        <a class="submenu" href="<?= admin_url('products/add_adjustment'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('add_adjustment'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-stock_count']) { ?>
                                    <li id="products_stock_counts">
                                        <a class="submenu" href="<?= admin_url('products/stock_counts'); ?>">
                                            <i class="fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('stock_counts'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_count_stock">
                                        <a class="submenu" href="<?= admin_url('products/count_stock'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('count_stock'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['transfers-index'] || $GP['transfers-add']) { ?>             
                                        <li id="transfers_index">
                                            <a class="submenu" href="<?= admin_url('transfers'); ?>">
                                                <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                                            </a>
                                        </li>
                                        <?php if ($GP['transfers-add']) { ?>
                                        <li id="transfers_add">
                                            <a class="submenu" href="<?= admin_url('transfers/add'); ?>">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($Settings->stock_received) { ?>
                                        <?php if ($GP['stock_received-index']) { ?>
                                        <li id="products_stock_received">
                                            <a class="submenu" href="<?= admin_url('products/stock_received'); ?>">
                                                <i class="fa fa-filter"></i>
                                                <span class="text"> <?= lang('stock_received'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if ($GP['sales-index'] || $GP['sales-add'] || $GP['sales-deliveries'] || $GP['sales-gift_cards'] || $GP['sales_order-index']) { ?>
                            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-heart"></i>
                                    <span class="text"> <?= lang('sales'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($GP['sales_order-index']) { ?>
                                    <li id="sales_order_index">
                                        <a class="submenu" href="<?= admin_url('sales_order'); ?>">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('list_sales_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['sales_order-add']) { ?>
                                    <li id="sales_order_add">
                                        <a class="submenu" href="<?= admin_url('sales_order/add'); ?>">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('add_sales_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['sales-index']) { ?>
                                    <li id="sales_index">
                                        <a class="submenu" href="<?= admin_url('sales'); ?>">
                                            <i class="fa fa-heart"></i><span class="text"> <?= lang('list_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if (POS && $GP['pos-index']) { ?>
                                    <li id="pos_sales">
                                        <a class="submenu" href="<?= admin_url('pos/sales'); ?>">
                                            <i class="fa fa-heart"></i><span class="text"> <?= lang('pos_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['sales-add']) { ?>
                                    <li id="sales_add">
                                        <a class="submenu" href="<?= admin_url('sales/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_sale'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['sales-deliveries']) { ?>
                                    <li id="sales_deliveries">
                                        <a class="submenu" href="<?= admin_url('sales/deliveries'); ?>">
                                            <i class="fa fa-truck"></i><span class="text"> <?= lang('deliveries'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['sales-gift_cards']) { ?>
                                    <li id="sales_gift_cards">
                                        <a class="submenu" href="<?= admin_url('sales/gift_cards'); ?>">
                                            <i class="fa fa-gift"></i><span class="text"> <?= lang('gift_cards'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['returns-index'] || $GP['returns-add']) { ?>
                                    <li id="returns_index">
                                        <a class="submenu" href="<?= admin_url('returns'); ?>">
                                            <i class="fa fa-random"></i><span class="text"> <?= lang('list_returns'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($GP['returns-add']) { ?>
                                    <li id="returns_add">
                                        <a class="submenu" href="<?= admin_url('returns/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_return'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if ($GP['quotes-index'] || $GP['quotes-add']) { ?>
                            <li class="mm_quotes">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-heart-o"></i>
                                    <span class="text"> <?= lang('quotes'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="sales_index">
                                        <a class="submenu" href="<?= admin_url('quotes'); ?>">
                                            <i class="fa fa-heart-o"></i><span class="text"> <?= lang('list_quotes'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($GP['quotes-add']) { ?>
                                    <li id="sales_add">
                                        <a class="submenu" href="<?= admin_url('quotes/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_quote'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if ($GP['purchases-index'] || 
                                $GP['purchases-add'] ||
                                $GP['purchases_request-index'] || 
                                $GP['purchases_request-add'] || 
                                $GP['purchases_order-index'] || 
                                $GP['purchases_order-add'] || 
                                $GP['purchases-expenses'] ||
                                $GP['purchases-expenses_budget']
                            ) { ?>
                            <li class="mm_purchases">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-star"></i>
                                    <span class="text"> <?= lang('purchases'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Settings->multi_level) { ?>
                                        <?php if ($GP['purchases_request-index']) { ?>
                                        <li id="purchases_request_index">
                                            <a class="submenu" href="<?= admin_url('purchases_request'); ?>">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('list_purchase_request'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['purchases_request-add']) { ?>
                                        <li id="purchases_request_add">
                                            <a class="submenu" href="<?= admin_url('purchases_request/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_purchase_request'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['purchases_order-index']) { ?>
                                        <li id="purchases_purchase_order">
                                            <a class="submenu" href="<?= admin_url('purchases_order'); ?>">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('list_purchase_order'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['purchases_order-add']) { ?>
                                        <li id="purchases_add_purchase_order">
                                            <a class="submenu" href="<?= admin_url('purchases_order/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_purchase_order'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($GP['purchases-index']) { ?>
                                    <li id="purchases_index">
                                        <a class="submenu" href="<?= admin_url('purchases'); ?>">
                                            <i class="fa fa-star"></i><span class="text"> <?= lang('list_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['purchases-add']) { ?>
                                    <li id="purchases_add">
                                        <a class="submenu" href="<?= admin_url('purchases/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_purchase'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['purchases-import']) { ?>
                                    <li id="purchases_purchase_by_csv">
                                        <a class="submenu" href="<?= admin_url('purchases/purchase_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('purchase_by_excel'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Settings->multi_level) { ?>
                                        <?php if ($GP['purchases-expenses_budget']) { ?>
                                        <li id="purchases_expenses_budget">
                                            <a class="submenu" href="<?= admin_url('purchases/expenses_budget'); ?>">
                                                <i class="fa fa-dollar"></i>
                                                <span class="text"> <?= lang('list_expenses_budget'); ?></span>
                                            </a>
                                        </li>
                                        <li id="purchases_add_expense_budget">
                                            <a class="submenu" href="<?= admin_url('purchases/add_expense_budget'); ?>" 
                                                data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_expense_budget'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($GP['purchases-expenses']) { ?>
                                    <li id="purchases_expenses">
                                        <a class="submenu" href="<?= admin_url('purchases/expenses'); ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('list_expenses'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_expense">
                                        <a class="submenu" href="<?= admin_url('purchases/add_expense'); ?>"
                                            data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_expense'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_expense_by_csv">
                                        <a class="submenu" href="<?= admin_url('purchases/expense_by_csv'); ?>">
                                            <i class="fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_expense'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Settings->multi_level) { ?>
                                        <?php if ($GP['purchases-budgets']) { ?>
                                        <li id="purchases_budgets">
                                            <a class="submenu" href="<?= admin_url('purchases/budgets'); ?>">
                                                <i class="fa fa-dollar"></i>
                                                <span class="text"> <?= lang('list_budgets'); ?></span>
                                            </a>
                                        </li>
                                        <li id="purchases_add_budget">
                                            <a class="submenu" href="<?= admin_url('purchases/add_budget'); ?>" 
                                                data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_budget'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if ($Settings->store_sales) { ?>
                                <?php if ($GP['store_sales-index'] || $GP['store_sales-add'] || $GP['store_sales_order-index'] || $GP['store_sales_order-add']) { ?>
                                <li class="mm_store_sales">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-heart"></i>
                                        <span class="text"> <?= lang('store_sales'); ?>
                                        </span> <span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <?php if ($GP['store_sales-index']) { ?>
                                        <li id="quotes_index">
                                            <a class="submenu" href="<?= admin_url('sales_store'); ?>">
                                                <i class="fa fa-heart-o"></i>
                                                <span class="text"> <?= lang('store_sales'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['store_sales-add']) { ?>
                                        <li id="quotes_index">
                                            <a class="submenu" href="<?= admin_url('sales_store/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_store_sales'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['store_sales_order-index']) { ?>
                                        <li id="quotes_index">
                                            <a class="submenu" href="<?= admin_url('sales_order_store'); ?>">
                                                <i class="fa fa-heart-o"></i>
                                                <span class="text"> <?= lang('store_sales_order'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['store_sales_order-add']) { ?>
                                        <li id="quotes_index">
                                            <a class="submenu" href="<?= admin_url('sales_order_store/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_store_sales_order'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($GP['accounts-index'] || $GP['account-list_receivable'] || $GP['account-list_ar_aging'] || $GP['account-ar_by_customer'] || $GP['account-bill_receipt'] || $GP['account-list_payable'] || $GP['account-list_ap_aging'] || $GP['account-ap_by_supplier'] || $GP['account-bill_payable'] || $GP['account-list_ac_head'] || $GP['account-list_customer_deposit'] || $GP['account-list_supplier_deposit'] || $GP['account_setting']) { ?>                                         
                                <li class="mm_account">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-book"></i>
                                        <span class="text"> <?= lang('manage_accounts') ?></span>
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <?php if ($GP['accounts-index']) { ?>
                                            <li id="account_listjournal">
                                                <a class="submenu" href="<?= admin_url('account/listJournal'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_journal'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['accounts-add']) { ?>
                                            <li id="account_add_journal">
                                                <a class="submenu" href="<?= admin_url('account/add_journal'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                    <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_journal'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        
                                        <?php if ($GP['account-list_receivable']) { ?>
                                            <li id="account_list_ac_recevable">
                                                <a class="submenu" href="<?= admin_url('account/list_ac_recevable'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_ac_receivable'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-list_ar_aging']) { ?>
                                            <li id="account_list_ar_aging">
                                                <a class="submenu" href="<?= admin_url('account/list_ar_aging'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_ar_aging'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-ar_by_customer']) { ?>
                                        <li id="account_ar_by_customer">
                                            <a class="submenu" href="<?= admin_url('account/ar_by_customer'); ?>">
                                                <i class="fa fa-list"></i><span class="text"> <?= lang('ar_by_customer'); ?></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <?php if ($GP['account-bill_receipt']) { ?>
                                            <li id="account_billreceipt">
                                                <a href="<?= admin_url('account/billReceipt') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('bill_receipt'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-list_payable']) { ?>
                                            <li id="account_list_ac_payable">
                                                <a class="submenu" href="<?= admin_url('account/list_ac_payable'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('account_payable_list'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-list_ap_aging']) { ?>
                                            <li id="account_list_ap_aging">
                                                <a class="submenu" href="<?= admin_url('account/list_ap_aging'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_ap_aging'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-ap_by_supplier']) { ?>
                                            <li id="account_ap_by_supplier">
                                                <a class="submenu" href="<?= admin_url('account/ap_by_supplier'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('ap_by_supplier'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-bill_payable']) { ?>
                                            <li id="account_billpayable">
                                                <a href="<?= admin_url('account/billPayable') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('bill_payable'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        
                                        <?php if ($GP['account-list_ac_head']) { ?>
                                            <li id="account_index">
                                                <a class="submenu" href="<?= admin_url('account'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_ac_head'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-add_ac_head']) { ?>
                                            <li id="account_add">
                                                <a class="submenu" href="<?= admin_url('account/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                    <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_ac_head'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <!-- <?php if ($GP['account-list_customer_deposit']) { ?>
                                            <li id="account_deposits">
                                                <a class="submenu" href="<?= admin_url('account/deposits'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_customer_deposit'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-add_customer_deposit']) { ?>
                                            <li id="account_deposits">
                                                <a class="submenu" href="<?= admin_url('quotes/add_deposit'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" id="add">
                                                    <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer_deposit'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-list_supplier_deposit']) { ?>
                                            <li id="account_deposits">
                                                <a class="submenu" href="<?= admin_url('suppliers/deposits'); ?>">
                                                    <i class="fa fa-list"></i><span class="text"> <?= lang('list_supplier_deposit'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account-add_supplier_deposit']) { ?>
                                            <li id="account_deposits">
                                                <a class="submenu" href="<?= admin_url('suppliers/add_deposit'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" id="add">
                                                    <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier_deposit'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?> -->
                                        <?php if ($GP['bank_reconcile']) { ?>
                                            <li id="account_settings">
                                                <a href="<?= admin_url('account/bank_reconcile') ?>">
                                                    <i class="fa fa-cog"></i><span class="text"> <?= lang('bank_reconcile'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($GP['account_setting']) { ?>
                                            <li id="account_settings">
                                                <a href="<?= admin_url('account/settings') ?>">
                                                    <i class="fa fa-cog"></i><span class="text"> <?= lang('account_settings'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if($GP['reports-index']){ ?>
                            <li class="mm_auth mm_customers mm_suppliers mm_billers">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-users"></i>
                                    <span class="text"> <?= lang('people'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($GP['customers-index']) { ?>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('customers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['customers-add']) { ?>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('customers/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['suppliers-index']) { ?>
                                    <li id="suppliers_index">
                                        <a class="submenu" href="<?= admin_url('suppliers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['suppliers-add']) { ?>
                                    <li id="suppliers_index">
                                        <a class="submenu" href="<?= admin_url('suppliers/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if ($GP['reports-index']) { ?>
                            <li class="mm_reports">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-bar-chart-o"></i>
                                    <span class="text"> <?= lang('reports'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul class="sub-menu">
                                    <?php if ($GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts'] || $GP['reports-products'] || $GP['reports-stock_in_out'] || $GP['reports-stock_received']) { ?>
                                    <li class="sub_mm_reports_inventory">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-barcode"></i>
                                            <span class="text"> <?= lang('inventory'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($GP['reports-quantity_alerts']) { ?>
                                            <li id="reports_quantity_alerts">
                                                <a href="<?= admin_url('reports/quantity_alerts') ?>">
                                                    <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-expiry_alerts']) { ?>
                                                <?php if ($Settings->product_expiry) { ?>
                                                <li id="reports_expiry_alerts">
                                                    <a href="<?= admin_url('reports/expiry_alerts') ?>">
                                                        <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($GP['reports-products']) { ?>
                                            <li id="reports_products">
                                                <a href="<?= admin_url('reports/products') ?>">
                                                    <i class="fa fa-filter"></i><span class="text"> <?= lang('products_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_adjustments">
                                                <a href="<?= admin_url('reports/adjustments') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_categories">
                                                <a href="<?= admin_url('reports/categories') ?>">
                                                    <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_brands">
                                                <a href="<?= admin_url('reports/brands') ?>">
                                                    <i class="fa fa-cubes"></i><span class="text"> <?= lang('brands_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-stock_in_out']) { ?>
                                            <li id="reports_products">
                                                <a href="<?= admin_url('reports/stock_in_out') ?>">
                                                    <i class="fa fa-barcode"></i><span class="text"> <?= lang('products_in_out_category'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($Settings->stock_received) { ?>
                                                <?php if ($GP['reports-stock_received']) { ?>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/stock_received') ?>">
                                                        <i class="fa fa-barcode"></i><span class="text"> <?= lang('stock_received_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['reports-daily_sales'] || $GP['reports-monthly_sales'] || $GP['reports-sale_targets'] || $GP['reports-sales']) { ?>
                                    <li class="sub_mm_reports_sales">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('sales'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($GP['reports-daily_sales']) { ?>
                                            <li id="reports_daily_sales">
                                                <a href="<?= admin_url('reports/daily_sales') ?>">
                                                    <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_sales'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-monthly_sales']) { ?>
                                            <li id="reports_monthly_sales">
                                                <a href="<?= admin_url('reports/monthly_sales') ?>">
                                                    <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-sale_targets']) { ?>
                                            <li id="reports_sale_targets">
                                                <a href="<?= admin_url('reports/sale_targets') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sale_targets_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-sales']) { ?>
                                            <li id="reports_sales">
                                                <a href="<?= admin_url('reports/sales') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_sales_detail">
                                                <a href="<?= admin_url('reports/sales_detail') ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sales_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['reports-daily_purchases'] || $GP['reports-monthly_purchases'] || $GP['reports-purchases'] || $GP['reports-expenses'] || $GP['reports-budgets'] || $GP['reports-expenses_budget']) { ?>
                                    <li class="sub_mm_reports_purchases">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('purchases'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($GP['reports-daily_purchases']) { ?>
                                            <li id="reports_daily_purchases">
                                                <a href="<?= admin_url('reports/daily_purchases') ?>">
                                                    <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-monthly_purchases']) { ?>
                                            <li id="reports_monthly_purchases">
                                                <a href="<?= admin_url('reports/monthly_purchases') ?>">
                                                    <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-purchases']) { ?>
                                            <li id="reports_purchases">
                                                <a href="<?= admin_url('reports/purchases') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-expenses']) { ?>
                                            <li id="reports_expenses">
                                                <a href="<?= admin_url('reports/expenses') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('expenses_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-budgets']) { ?>
                                            <li id="reports_budgets">
                                                <a href="<?= admin_url('reports/budgets') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('budgets_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-expenses_budget']) { ?>
                                            <li id="reports_expenses_budget">
                                                <a href="<?= admin_url('reports/expenses_budget') ?>">
                                                    <i class="fa fa-star"></i><span class="text"> <?= lang('expenses_budget_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Settings->store_sales) { ?>
                                        <?php if ($GP['reports-store_sales']) { ?>
                                        <li class="sub_mm_reports_store_sales">
                                            <a class="dropmenu sub_dropmenu" href="#">
                                                <i class="fa fa-heart"></i>
                                                <span class="text"> <?= lang('store_sales'); ?> </span>
                                                <span class="chevron closed blue-color"></span>
                                            </a>
                                            <ul class="sub-sub-menu">
                                                <li id="reports_register">
                                                    <a href="<?= admin_url('reports/store_sales') ?>">
                                                        <i class="fa fa-th-large"></i><span class="text"> <?= lang('store_sales_report'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($GP['reports-suppliers'] || $GP['reports-customers'] || $GP['reports-salemans'] || $GP['reports-commission']) { ?>
                                    <li class="sub_mm_reports_hr">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-users"></i>
                                            <span class="text"> <?= lang('hr'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($GP['reports-suppliers']) { ?>
                                            <li id="reports_supplier_report">
                                                <a href="<?= admin_url('reports/suppliers') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-customers']) { ?>
                                            <li id="reports_customer_report">
                                                <a href="<?= admin_url('reports/customers') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?> 
                                            <?php if ($GP['reports-salemans']) { ?>
                                            <li id="reports_saleman_detail">
                                                <a href="<?= admin_url('reports/saleman_report') ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('saleman_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-commission']) { ?>
                                            <li id="reports_commission">
                                                <a href="<?= admin_url('reports/commission') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('commission_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Settings->store_sales) { ?>
                                        <?php if ($GP['reports-store_sales']) { ?>
                                        <li class="sub_mm_reports_store_sales">
                                            <a class="dropmenu sub_dropmenu" href="#">
                                                <i class="fa fa-heart"></i>
                                                <span class="text"> <?= lang('store_sales'); ?> </span>
                                                <span class="chevron closed blue-color"></span>
                                            </a>
                                            <ul class="sub-sub-menu">
                                                <li id="reports_register">
                                                    <a href="<?= admin_url('reports/store_sales') ?>">
                                                        <i class="fa fa-th-large"></i><span class="text"> <?= lang('store_sales_report'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($Settings->accounting || $GP['reports-payments'] || $GP['reports-tax'] || $GP['account_report-index']) { ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa fa-book"></i>
                                            <span class="text"> <?= lang('ACCOUNTS'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">    
                                            <?php if ($GP['reports-tax']) { ?>
                                            <li id="reports_tax">
                                                <a href="<?= admin_url('reports/tax') ?>">
                                                    <i class="fa fa-area-chart"></i><span class="text"> <?= lang('tax_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($GP['reports-payments']) { ?>
                                            <li id="reports_payments">
                                                <a href="<?= admin_url('reports/payments') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments_received">
                                                <a href="<?= admin_url('reports/payments_received') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_received_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_payments_voucher">
                                                <a href="<?= admin_url('reports/payments_voucher') ?>">
                                                    <i class="fa fa-money"></i><span class="text"> <?= lang('payments_voucher_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?> 
                                            <?php if($GP['account_report-index']){ ?>
                                                <?php if($GP['account_report-ledger']){ ?>
                                                <li id="reports_ledger">
                                                    <a href="<?= admin_url('reports/ledger') ?>">
                                                        <i class="fa fa-book"></i><span class="text"> <?= lang('ledger'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <?php if($GP['account_report-trail_balance']){ ?>
                                                <li id="reports_trial_balance">
                                                    <a href="<?= admin_url('reports/trial_balance') ?>">
                                                        <i class="fa fa-bars"></i><span class="text"> <?= lang('trial_balance'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <?php if($GP['account_report-income_statement']){ ?>
                                                <li id="reports_income_statement">
                                                    <a href="<?= admin_url('reports/income_statement') ?>">
                                                        <i class="fa fa-money"></i><span class="text"> <?= lang('income_statement'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <?php if($GP['account_report-balance_sheet']){ ?>
                                                <li id="reports_balance_sheet">
                                                    <a href="<?= admin_url('reports/balance_sheet') ?>">
                                                        <i class="fa fa-balance-scale"></i><span class="text"> <?= lang('balance_sheet'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <?php if($GP['account_report-cash_book']){ ?>
                                                <li id="reports_cash_book_report" <?=($this->uri->segment(2) === 'cash_books' ? 'class="active"' : '')?> >
                                                    <a href="<?= admin_url('reports/cash_books') ?>">
                                                        <i class="fa fa-money"></i><span class="text"> <?= lang('cash_book'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
                <a href="#" id="main-menu-act" class="full visible-md visible-lg">
                    <i class="fa fa-angle-double-left"></i>
                </a>
            </div>
            </td><td class="content-con">
            <div id="content">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ul class="breadcrumb">
                            <?php 
                            foreach ($bc as $b) {
                                if ($b['link'] === '#') {
                                    echo '<li class="active">' . $b['page'] . '</li>';
                                } else {
                                    echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                                }
                            } ?>
                            <li class="right_log hidden-xs">
                                <?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ': ' . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . ' ' . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . ' )</span>' ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $message; ?>
                            </div>
                        <?php } ?>
                        <?php if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $error; ?>
                            </div>
                        <?php } ?>
                        <?php if ($warning) { ?>
                            <div class="alert alert-warning">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $warning; ?>
                            </div>
                        <?php } ?>
                        <?php
                        if ($info) {
                            foreach ($info as $n) {
                                if (!$this->session->userdata('hidden' . $n->id)) { ?>
                                    <div class="alert alert-info">
                                        <a href="#" id="<?= $n->id ?>" class="close hideComment external"
                                            data-dismiss="alert">&times;</a>
                                        <?= $n->comment; ?>
                                    </div>
                                <?php
                                }
                            }
                        } ?>
                        <div class="alerts-con"></div>

<script type="text/javascript">
    $(document).ready(function(){
        $(".sub-menu li").on("click", function(){
            $(".sub-menu li").removeClass("active");
            $(this).addClass("active");
        });
        $(".sub-sub-menu li").on("click", function(){
            $(".sub-sub-menu li").removeClass("active");
            $(this).addClass("active");
        });
        $(".sub-sub-menu li a").on("click", function(){
            $(this).addClass("set_color");
        });
    });
</script>
