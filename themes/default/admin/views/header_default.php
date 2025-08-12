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
        <table class="lt"><tr><td class="sidebar-con <?= $page_view?'hide':'';?>">
            <div id="sidebar-left">
                <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                    <ul class="nav main-menu">
                        <div class="text-center" style="border-bottom:1px solid #cccccc;padding-bottom:5px; ">
                            <a href="<?= admin_url() ?>">
                            <?php if ($Settings->logo) {
                                echo '<img width="150" height="80" src="'.base_url('assets/uploads/logos/' . $Settings->logo).'" alt="' . $Settings->site_name . '" style="margin-bottom:0px;" id="logo" />';
                                echo '<img width="30" height="30" src="'.base_url().'sbc_favicon.ico" alt="' . $Settings->site_name . '" style="display:none;" id="mini_logo" />';
                            } ?>
                            </a>
                        </div>
                        <?php
                        $session_module = $this->session->userdata('module');
                        if ((($this->Settings->ui == 'full')? ($session_module =='clinic' && $Settings->module_clinic) : $Settings->module_clinic)
                            && ($Owner || $Admin || $GP['customers-index']||$GP['customers-add'])) { ?>
                            <?php if ($Owner||$Admin||$GP['customers-index']) { ?>
                            <li class="mm_clinic-index">
                                <a class="submenu" href="<?= admin_url('clinic'); ?>">
                                    <i class="fa-regular fa fa-users"></i>
                                    <span class="text"> <?= lang('list_patient'); ?>
                                </a>
                            </li>
                            <?php } ?>
                            <li class="mm_clinic-consultation">
                                <a class="submenu" href="<?= admin_url('clinic/consultation'); ?>">
                                    <i class="fa-regular fa-user-doctor-message"></i>
                                    <span class="text"> <?= lang('consultation'); ?>
                                </a>
                            </li>
                            <li class="mm_clinic-opd">
                                <a class="submenu" href="<?= admin_url('clinic/opd'); ?>">
                                    <i class="fa-regular fa-syringe"></i>
                                    <span class="text"> <?= lang('opd'); ?></span>
                                </a>
                                
                            </li>
                            <li class="mm_clinic">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-bed-pulse"></i>
                                    <span class="text"> <?= lang('ipd'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if(($Owner||$Admin||$GP['sales_order-index'])) { ?>
                                    <li id="clinic_ipd_treatment">
                                        <a class="submenu" href="<?= admin_url('clinic/ipd'); ?>">
                                            <i class="fa-regular fa-syringe"></i>
                                            <span class="text"> <?= lang('ipd_patient'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="clinic_progress_note">
                                        <a class="submenu" href="<?= admin_url('clinic/progress_note'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('progress_note'); ?></span>
                                        </a>
                                    </li>
                                    <li id="clinic_medication_dose">
                                        <a class="submenu" href="<?= admin_url('clinic/medication_dose'); ?>">
                                            <i class="fa-regular fa-pills"></i>
                                            <span class="text"> <?= lang('medication_dose'); ?></span>
                                        </a>
                                    </li>
                                    <li id="clinic_operations">
                                        <a class="submenu" href="<?= admin_url('clinic/operations'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('operations'); ?></span>
                                        </a>
                                    </li>
                                    <li id="clinic_pathology">
                                        <a class="submenu" href="<?= admin_url('clinic/pathology'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('pathology'); ?></span>
                                        </a>
                                    </li>
                                    <li id="clinic_operation_categories">
                                        <a class="submenu" href="<?= admin_url('clinic/operation_categories'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('operation_categories'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="mm_clinic">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-users"></i>
                                    <span class="text"> <?= lang('sales'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="clinic_prescription">
                                        <a class="submenu" href="<?= admin_url('clinic/prescription'); ?>">
                                            <i class="fa-sharp fa-regular fa-file-prescription"></i>
                                            <span class="text"> <?= lang('prescription'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($Owner || $Admin ||$GP['sales-index']) { ?>
                                    <li id="clinic_sales">
                                        <a class="submenu" href="<?= admin_url('clinic/sales'); ?>">
                                            <i class="fa-regular fa-file-invoice-dollar"></i>
                                            <span class="text"> <?= lang('invoice'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_clinic">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('birth_death'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner||$Admin||$GP['customers-index']) { ?>
                                    <li id="clinic_birth">
                                        <a class="submenu" href="<?= admin_url('clinic/birth'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('birth_record'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner||$Admin||$GP['customers-index']) { ?>
                                    <li id="clinic_death">
                                        <a class="submenu" href="<?= admin_url('clinic/death'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('death_record'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_clinic">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-capsules"></i>
                                    <span class="text"> <?= lang('phamacy'); ?></span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin ||$GP['products-index']) { ?>
                                    <li id="clinic_medicines">
                                        <a class="submenu" href="<?= admin_url('clinic/medicines'); ?>">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('medicines'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['products-add']) { ?>
                                    <li id="clinic_add_medicine">
                                        <a class="submenu" href="<?= admin_url('clinic/add_medicine'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_medicine'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_table">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-bed"></i>
                                    <span class="text"> <?= lang('bed'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="table_suspend_note">
                                        <a class="submenu" href="<?= admin_url('table/suspend_note'); ?>">
                                            <i class="fa-regular fa-bed-pulse"></i>
                                            <span class="text"> <?= lang('bed'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($Settings->module_clinic) { ?>
                                    <li id="table_assign" class="hide">
                                        <a class="submenu" href="<?= admin_url('table/assign'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('assign'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php }
                        if ((($this->Settings->ui == 'full')? ($Settings->module_inventory && $session_module =='inventory') : $Settings->module_inventory)
                            && ($Owner || $Admin || $GP['products-index'] || $GP['products-add'] || $GP['products-barcode'] || $GP['products-adjustments'] || $GP['products-stock_count'] || $GP['stock_received-index'] || $GP['products-update_cost_and_price'] || $GP['reward_exchange-index'])) { ?>
                            <li class="mm_products">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-barcode"></i>
                                    <span class="text"> <?= lang('inventory'); ?></span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin ||$GP['products-index']) { ?>
                                    <li id="products_index">
                                        <a class="submenu" href="<?= admin_url('products'); ?>">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('list_products'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['products-add']) { ?>
                                    <li id="products_add">
                                        <a class="submenu" href="<?= admin_url('products/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_product'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_import_excel">
                                        <a class="submenu" href="<?= admin_url('products/import_excel'); ?>">
                                            <i class="fa-regular fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_products'); ?> Excel</span>
                                        </a>
                                    </li>
                                    <li id="products_import_products_cost_and_price_excel" class="hide">
                                        <a class="submenu" href="<?= admin_url('products/import_products_cost_and_price_excel'); ?>">
                                            <i class="fa-regular fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_products_cost_and_price_excel'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['products-barcode']) { ?>
                                    <li id="products_print_barcodes">
                                        <a class="submenu" href="<?= admin_url('products/print_barcodes'); ?>">
                                            <i class="fa-regular fa fa-tags"></i><span class="text"> <?= lang('print_barcode_label'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['products-stock_count']) { ?>
                                    <li id="products_stock_counts">
                                        <a class="submenu" href="<?= admin_url('products/stock_counts'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('stock_counts'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['products-adjustments']) { ?>
                                    <li id="products_quantity_adjustments">
                                        <a class="submenu" href="<?= admin_url('products/quantity_adjustments'); ?>">
                                            <i class="fa-regular fa fa-filter"></i><span class="text"> <?= lang('quantity_adjustments'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->reward_exchange && ($Owner || $Admin || $GP['reward_exchange-index'])) { ?>
                                    <li id="products_add_count_ring">
                                        <a class="submenu" href="<?= admin_url('products/add_count_ring/customer'); ?>">
                                            <i class="fa-regular fa-right-left"></i><span class="text"> <?= lang('add_count_ring'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_customer_rewards_exchange">
                                        <a class="submenu" href="<?= admin_url('products/rewards_exchange/customer'); ?>">
                                            <i class="fa-regular fa-right-left"></i><span class="text"> <?= lang('customer_rewards_exchange'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_supplier_rewards_exchange">
                                        <a class="submenu" href="<?= admin_url('products/rewards_exchange/supplier'); ?>">
                                            <i class="fa-regular fa-right-left"></i><span class="text"> <?= lang('supplier_rewards_exchange'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->stock_using && ($Owner || $Admin || $GP['products-using_stocks'])) { ?>
                                    <li id="products_using_stock" class="sub_navigation">
                                        <a class="submenu" href="<?= admin_url('products/using_stock'); ?>">
                                            <i class="fa-regular fa fa-filter"></i>
                                            <span class="text"> <?= lang('list_stock_using'); ?></span>
                                        </a>
                                    </li>
                                    <!-- $Settings->stock_received && $Settings->reward_exchange -->
                                    <?php } if (($Settings->reward_exchange) && ($Owner || $Admin ||$GP['stock_received-index'])) { ?>
                                    <li id="products_rewards_stock_received">
                                        <a class="submenu" href="<?= admin_url('products/rewards_stock_received'); ?>">
                                            <i class="fa-regular fa fa-filter"></i>
                                            <span class="text"> <?= lang('rewards_stock_received'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->stock_received && ($Owner || $Admin ||$GP['stock_received-index'])) { ?>
                                    <li id="products_stock_received">
                                        <a class="submenu" href="<?= admin_url('purchases/receives'); ?>">
                                            <i class="fa-regular fa fa-filter"></i>
                                            <span class="text"> <?= lang('stock_received'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->multi_warehouse && ($Owner || $Admin ||$GP['transfers-index'] || $GP['transfers-add'])) { ?>              
                                    <li id="transfers_index">
                                        <a class="submenu" href="<?= admin_url('transfers'); ?>">
                                            <i class="fa-regular fa-right-left"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->sale_consignment &&($Owner || $Admin || $GP['products-consignments'])) { ?>     
                                        <li id="products_consignments">
                                            <a class="submenu" href="<?= admin_url('products/consignments'); ?>">
                                                <i class="fa fa-list-ol"></i>
                                                <span class="text"> <?= lang('consignments'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if ($Owner || $Admin || $GP['products-saleman_stock']) { ?>     
                                        <li id="products_saleman_stock" class="hide">
                                            <a class="submenu" href="<?= admin_url('products/saleman_stock'); ?>">
                                                <i class="fa fa-list-ol"></i>
                                                <span class="text"> <?= lang('saleman_stock'); ?></span>
                                            </a>
                                        </li>
                                    <?php }?>
                                </ul>
                            </li>
                        <?php }
                        if((($this->Settings->ui == 'full')? ($session_module =='asset' && $Settings->module_asset) : $Settings->module_asset) && ($Owner||$Admin||$GP['assets-index'] || $GP['assets-add']|| $GP['assets-expenses']||$GP['assets-depreciation']||$GP['products-using_stocks']||$GP['products-using_stocks-add'])){ ?>
                            <li class="mm_asset" id="mm_asset">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-barcode fa_default"></i>
                                    <span class="text"> <?= lang('assets'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if($Owner||$Admin||$GP['assets-index']){?>
                                    <li id="assets_index">
                                        <a class="submenu" href="<?= admin_url('assets'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('list_assets'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['assets-add']){?>
                                    <li id="assets_add">
                                        <a class="submenu" href="<?= admin_url('assets/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_assets'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['assets-expenses']){?>
                                    <li id="assets_expenses">
                                        <a class="submenu" href="<?= admin_url('assets/expenses'); ?>">
                                            <i class="fa-regular fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['assets-depreciation']){?>
                                    <li id="purchases_add_expense">
                                        <a class="submenu" href="<?= admin_url('purchases/add_asset_expense'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase'); ?></span>
                                        </a>
                                    </li>
                                    <li id="assets_depreciation">
                                        <a class="submenu" href="<?= admin_url('assets/depreciation'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('depreciation'); ?></span>
                                        </a>
                                    </li>
                                    <li id="assets_add_issues">
                                          <a class="submenu" href="<?= admin_url('assets/evaluation'); ?>">
                                              <i class="fa-regular fa fa-plus-circle"></i>
                                              <span class="text"> <?= lang('evaluation'); ?></span>
                                          </a>
                                      </li>
                                    <?php } if($Owner||$Admin||$GP['products-using_stocks']){?>
                                    <li id="using_stock" class="sub_navigation">
                                        <a class="submenu" href="<?= admin_url('products/using_stock'); ?>">
                                            <i class="fa-regular fa fa-filter"></i>
                                            <span class="text"> <?= lang('list_stock_using'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['products-using_stocks-add']){?>
                                    <li id="add_stock_using" class="sub_navigation">
                                        <a class="submenu" href="<?= admin_url('products/add_using_stock'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_stock_using'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } 
                        if ($Settings->module_rental && ($Owner || $Admin ||$GP['leasing-assets']||$GP['leasing-index']||$GP['leasing-assets'])) {
                        ?>
                        <li class="mm_leasing">
                                <a class="dropmenu" href="#">
                                    <i class="fa-sharp fa-regular fa-car"></i>
                                    <span class="text"> <?= lang('vehicles'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin ||$GP['leasing-assets']) { ?>
                                    <li id="leasing_assets">
                                        <a class="submenu" href="<?= admin_url('leasing/assets'); ?>">
                                            <i class="fa-sharp fa-regular fa-car-side"></i>
                                            <span class="text"> <?= lang('list_assets'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['leasing-index']) { ?>
                                    <li id="leasing_index">
                                        <a class="submenu" href="<?= admin_url('leasing'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('leases'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['leasing-index']) { ?>
                                    <li id="leasing_blacklists">
                                        <a class="submenu" href="<?= admin_url('leasing/blacklists'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('blacklist'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['leasing-index']) { ?>
                                    <li id="leasing_end_leasing">
                                        <a class="submenu" href="<?= admin_url('leasing/end_leasing'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('end_leasing'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php
                        }
                        if($Settings->module_crm && ($Owner||$Admin||$GP['reports-leads_report'])){ ?>
                                <li class="mm_customers mm_leads">
                                    <a class="dropmenu" href="#">
                                        <i class="fa-regular fa fa-users"></i>
                                        <span class="text"> <?= lang('leads'); ?> </span>
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <?php if ($Owner || $Admin ||$GP['leads-groups']) { ?>
                                        <li id="leads_groups">
                                            <a class="submenu" href="<?= admin_url('leads/groups'); ?>">
                                                <i class="fa-regular fa fa-th"></i><span class="text"> <?= lang('groups'); ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Owner || $Admin ||$GP['leads-index']) { ?>
                                        <li id="leads_index">
                                            <a class="submenu" href="<?= admin_url('leads'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('leads'); ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Owner || $Admin ||$GP['leads-add']) { ?>
                                        <li id="leads_add">
                                            <a class="submenu" href="<?= admin_url('leads/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_lead'); ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Owner || $Admin ||$GP['leads-pipeline']) { ?>
                                        <li id="leads_pipeline">
                                            <a class="submenu" href="<?= admin_url('leads/pipeline'); ?>">
                                                <i class="fa-regular fa fa-pie-chart"></i><span class="text"> <?= lang('pipeline'); ?></span>
                                            </a>
                                        </li>
                                        <?php }?>
                                    </ul>
                                </li>
                                <li class="sub_mm_reports_crm">
                                    <a class="dropmenu sub_dropmenu" href="#">
                                        <i class="fa-regular fa fa-users"></i>
                                        <span class="text"> <?= lang('crm'); ?> </span>
                                        <span class="chevron closed blue-color"></span>
                                    </a>
                                    <ul class="sub-sub-menu">
                                        <?php if ($Owner||$Admin||$GP['reports-leads_report']) { ?>
                                        <li id="reports_customer_report">
                                            <a href="<?= admin_url('reports/leads_report') ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('leads_report'); ?></span>
                                            </a>
                                        </li>
                                        <li id="leads_report" class="hide">
                                            <a class="submenu" href="#">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sale_forecasting'); ?></span>
                                            </a>
                                        </li>
                                        <?php }?>
                                    </ul>
                                </li>
                        <?php          
                        } if((($this->Settings->ui == 'full')? ($session_module =='property' && $Settings->module_property) : $Settings->module_property) && ($Owner || $Admin ||$GP['products-index']||$GP['products-add'])){ ?>
                            <li class="mm_property">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-barcode fa_default"></i>
                                    <span class="text"> <?= lang('properties'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin ||$GP['sales-index']) { ?>
                                    <li id="property_sales">
                                        <a class="submenu" href="<?= admin_url('property/sales'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('list_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Owner || $Admin ||$GP['sales-add']) { ?>
                                    <li id="property_add_sale">
                                        <a class="submenu" href="<?= admin_url('property/add_sale'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="products_brands">
                                        <a href="<?= admin_url('system_settings/brands') ?>">
                                            <i class="fa-regular fa fa-th-list"></i><span class="text"> <?= lang('block'); ?></span>
                                        </a>
                                    </li>
                                    <li id="property_index">
                                        <a class="submenu" href="<?= admin_url('property'); ?>">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('list_property'); ?></span>
                                        </a>
                                    </li>
                                    <li id="property_add">
                                        <a class="submenu" href="<?= admin_url('property/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_property'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_categories">
                                        <a href="<?= admin_url('system_settings/categories') ?>">
                                            <i class="fa-regular fa fa-folder-open"></i><span class="text"> <?= lang('property_type'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_units">
                                        <a href="<?= admin_url('system_settings/units') ?>">
                                            <i class="fa-regular fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
                                        </a>
                                    </li>
                                    <li id="property_import_excel">
                                        <a class="submenu" href="<?= admin_url('property/import_excel'); ?>">
                                            <i class="fa-regular fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_property'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="mm_commission">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-envelope-open-dollar"></i>
                                    <span class="text"> <?= lang('commission'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>    
                                    <li id="loans_list">
                                        <a class="submenu" href="<?= admin_url('commission/commissions_payable'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('commission'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php }
                        if ((($this->Settings->ui == 'full')? ($session_module =='procurement' && $Settings->module_purchase) : $Settings->module_purchase) && ($Owner||$Admin||$GP['purchases-index'] || $GP['purchases-add'] || $GP['purchases_request-index'] || $GP['purchases_request-add'] || $GP['purchases_order-index'] || $GP['purchases_order-add'])) { ?>
                            <li class="mm_purchases">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-money-bill-1"></i>
                                    <span class="text"> <?= lang('purchases'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Settings->purchase_request && ($Owner||$Admin||$GP['purchases_request-index'])) { ?>
                                    <li id="purchases_request_index">
                                        <a class="submenu" href="<?= admin_url('purchases_request'); ?>">
                                            <i class="fa-regular fa fa-star"></i>
                                            <span class="text"> <?= lang('list_purchase_request'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->purchase_order && ($Owner||$Admin||$GP['purchases_order-index'])) { ?>
                                    <li id="purchases_purchase_order">
                                        <a class="submenu" href="<?= admin_url('purchases_order'); ?>">
                                            <i class="fa-regular fa fa-star"></i>
                                            <span class="text"> <?= lang('list_purchase_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner||$Admin||$GP['purchases-index']) { ?>
                                    <li id="purchases_index">
                                        <a class="submenu" href="<?= admin_url('purchases'); ?>">
                                            <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('list_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner||$Admin||$GP['purchases-import']) { ?>
                                    <li id="purchases_purchase_by_csv">
                                        <a class="submenu" href="<?= admin_url('purchases/purchase_by_csv'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('purchase_by_excel'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin || $GP['purchases-payments_requested']) { ?>
                                        <li id="account_ap_requested" class="hide">
                                            <a class="submenu" href="<?= admin_url('account/ap_requested'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('ap_requested'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['purchases-return_purchases']){ ?>
                                  
                                        <li id="purchases_purchase_return" class="hide">
                                            <a class="submenu" href="<?= admin_url('purchases/purchase_return'); ?>">
                                                <i class="fa-regular fa fa-list"></i>
                                                <span class="text"> <?= lang('purchase_returns'); ?></span>
                                            </a>
                                        </li>

                                    <?php }?>  
                                </ul>
                            </li>
                        <?php }
                        
                        if ((($this->Settings->ui == 'full')? ($session_module =='sales' && $Settings->module_sale) : $Settings->module_sale) && ($Owner || $Admin ||$GP['sales_order-index']||$GP['sales-index'] || $GP['sales-add'] || $GP['sales-deliveries'] || $GP['sales-gift_cards'] || $GP['sales-view_sale_declare'])) { 
                            ?>
                            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-money-check-dollar"></i>
                                    <span class="text"> <?= lang('sales'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php 

                                     if ($Settings->module_rental && ($Owner || $Admin ||$GP['leasing-generate_invoice_index'])) { ?>
                                    <li id="leasing_list_generate_invoice">
                                        <a class="submenu" href="<?= admin_url('leasing/list_generate_invoice'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> 
                                                <?= lang('list_generate_invoice'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_rental && ($Owner || $Admin ||$GP['leasing-generate_invoice_add'])) { ?>
                                    <li id="leasing_generate_invoice">
                                        <a class="submenu" href="<?= admin_url('leasing/generate_invoice'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('generate_invoice'); ?></span>
                                        </a>
                                    </li>
                                    <li id="leasing_sales">
                                        <a class="submenu" href="<?= admin_url('leasing/sales'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('invoice'); ?></span>
                                        </a>
                                    </li>
                                    <?php }

                                    if($Settings->monthly_auto_invoice && ($Owner||$Admin||$GP['sales-generate'])) { ?>
                                    <li id="sales_order_index">
                                        <a class="submenu" href="<?= admin_url('sales/generate'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('monthly_auto_invoice'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Settings->quotation && ($Owner||$Admin||$GP['quotes-index'])) { ?>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('quotes'); ?>">
                                            <i class="fa-regular fa-file-lines"></i>
                                            <span class="text"> <?= lang('quotes'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_compare" class="hide">
                                        <a class="submenu" href="<?= admin_url('quotes/comparison'); ?>">
                                            <i class="fa-regular fa-file-lines"></i>
                                            <span class="text"> <?= lang('comparison'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Settings->sale_order && ($Owner||$Admin||$GP['sales_order-index'])) { ?>
                                    <li id="sales_order_index">
                                        <a class="submenu" href="<?= admin_url('sales_order'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('list_sales_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin ||$GP['sales-index']) { ?>
                                    <li id="sales_index">
                                        <a class="submenu" href="<?= admin_url('sales'); ?>">
                                            <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('list_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_tax && ($Owner || $Admin || $GP['sales-view_sale_declare'])) { ?>
                                    <li id="sales_taxs">
                                        <a class="submenu" href="<?= admin_url('sales/taxs'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('list_invoices'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_rental && ($Owner || $Admin ||$GP['rental'])) { ?>
                                    <li id="rental_index">
                                        <a class="submenu" href="<?= admin_url('rental'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('rental'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner||$Admin||$GP['sales-add']) { ?>
                                    <!-- <li id="sales_sale_by_csv">
                                        <a class="submenu" href="<?= admin_url('sales/sale_by_csv'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale_by_csv'); ?></span>
                                        </a>
                                    </li> -->
                                    <?php } if ($Settings->delivery && $Owner || $Admin || $GP['sales-deliveries']) { ?>
                                    <li id="sales_shipping_request">
                                        <a class="submenu" href="<?= admin_url('deliveries/shipping_request'); ?>">
                                            <i class="fa-regular fa fa-truck"></i>
                                            <span class="text"> <?= lang('shipping_request'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_deliveries">
                                        <a class="submenu" href="<?= admin_url('deliveries'); ?>">
                                            <i class="fa-regular fa fa-truck"></i>
                                            <span class="text"> <?= lang('delivery_note'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="returns_index">
                                        <a class="submenu" href="<?= admin_url('sales/sale_returns'); ?>">
                                            <i class="fa-regular fa fa-random"></i><span class="text"> <?= lang('list_returns'); ?></span>
                                        </a>
                                    </li>
                                    <?php if($Settings->maintenance){?>
                                    <li id="sales_maintenance">
                                        <a class="submenu" href="<?= admin_url('sales/maintenance'); ?>">
                                            <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('maintenance'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if(POS){ ?>
                                    <!-- <li id="sales_kitchen">
                                        <a class="submenu" href="<?= admin_url('pos/making'); ?>">
                                            <i class="fa-regular fa fa-list"></i>
                                            <span class="text"> <?= lang('view_producing'); ?></span>
                                        </a>
                                    </li> -->
                                    <?php } if (($Owner||$Admin|| $GP['products-consignments']) && $Settings->sale_consignment && $this->config->item('consignments')) { ?>       
                                        <li id="products_consignments">
                                            <a class="submenu" href="<?= admin_url('sales/consignments'); ?>">
                                                <i class="fa-regular fa fa-list-ol"></i>
                                                <span class="text"> <?= lang('consignments'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if (($Owner || $Admin) && $this->Settings->module_fuel) { ?>
                                        <li id="sales_fuel_customers">
                                            <a class="submenu" href="<?= admin_url('sales/fuel_customers'); ?>">
                                                <i class="fa-solid fa-gas-pump"></i>
                                                <span class="text"> <?= lang('fuel_customers'); ?></span>
                                            </a>
                                        </li>
                                        <li id="sales_fuel_sales">
                                            <a class="submenu" href="<?= admin_url('sales/fuel_sales'); ?>">
                                                <i class="fa-solid fa-gas-pump"></i>
                                                <span class="text"> <?= lang('fuel_sales'); ?></span>
                                            </a>
                                        </li>
                                    <?php }  if (($Owner || $Admin || $GP['sales-index']) && $this->Settings->module_concrete) { ?>
                                            <li id="sales_sale_concretes">
                                                <a class="submenu" href="<?= admin_url('sales/sale_concretes'); ?>">
                                                    <i class="fa fa-heart"></i><span class="text"> <?= lang('sale_concretes'); ?></span>
                                                </a>
                                            </li>
                                        <?php } if (($Owner || $Admin || $GP['sales-receive_payments'])) { ?>
                                        <!-- <li id="sales_receive_payments">
                                            <a class="submenu" href="<?= admin_url('sales/receive_payments'); ?>">
                                                <i class="fa fa-gift"></i><span class="text"> <?= lang('receive_payments'); ?></span>
                                            </a>
                                        </li> -->
                                    <?php }if($Owner||$Admin||$GP['sales-depo_research']) { ?>
                                    <li id="sales_depo_research" class="hide">
                                        <a class="submenu" href="<?= admin_url('sales/depo_research'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('depo_research'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>

                                </ul>
                            </li>
                        <?php 
                        }
                        if (
                            (
                            ($this->Settings->ui == 'full')? (POS && $session_module =='pos') : POS
                            )
                             && $Owner || $Admin ||$GP['sales_order-index']||$GP['sales-index'] || $GP['sales-add'] || $GP['sales-deliveries'] || $GP['sales-gift_cards']) { ?>
                            <li class="mm_pos">
                                <a class="dropmenu" href="#">
                                    <i class="fa-sharp fa-regular fa-cash-register"></i>
                                    <span class="text"> <?= lang('point_of_sale'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if (POS && ($Owner||$Admin|| $GP['pos-index'])) { ?>
                                    <li id="pos_sales">
                                        <a class="submenu" href="<?= admin_url('pos/sales'); ?>">
                                            <i class="fa-regular fa-face-tongue-money"></i><span class="text"> <?= lang('pos_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="pos_index">
                                        <a class="submenu" href="<?= admin_url('pos'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('pos'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if($this->pos_settings->member_card && ($Owner||$Admin||$GP['sales-gift_cards'])){ ?>
                                    <li id="sales_gift_cards">
                                        <a class="submenu" href="<?= admin_url('sales/gift_cards'); ?>">
                                            <i class="fa-regular fa fa-gift"></i><span class="text"> <?= lang('gift_cards'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_member_cards">
                                        <a class="submenu" href="<?= admin_url('member_cards'); ?>">
                                            <i class="fa-regular fa fa-gift"></i>
                                            <span class="text"> <?= lang('member_cards'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_import_excel" class="hide">
                                        <a class="submenu" href="<?= admin_url('member_cards/import_excel'); ?>">
                                            <i class="fa-regular fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_member_card'); ?> Excel</span>
                                        </a>
                                    </li>
                                    <li id="member_cards_coupon">
                                        <a href="<?= admin_url('member_cards/coupon') ?>">
                                            <i class="fa-regular fa fa-chain"></i><span class="text"> <?= lang('coupon'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($this->pos_settings->pos_type !="pos" && ($Owner || $Admin || $GP['sales-customer_stocks'])) { ?>
                                        <li id="pos_customer_stocks">
                                            <a class="submenu" href="<?= admin_url('pos/customer_stocks'); ?>">
                                                <i class="fa-sharp fa-regular fa-container-storage"></i><span class="text"> <?= lang('customer_stocks'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php 
                        }
                        if((($this->Settings->ui == 'full')? ($Settings->module_loan && $session_module =='loan') : $Settings->module_loan)
                            && ($Owner || $Admin || $GP['loans-index'] || $GP['loans-loan_products'] || $GP['loans-index'] || $GP['loans-payments'])){
                            ?>
                            <li class="mm_loans <?= strtolower($this->router->fetch_method()) == 'loans' ? 'mm_loans' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-star"></i>
                                    <span class="text"> <?= lang('loan'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>    
                                <?php if ($Owner || $Admin || $GP['loans-applications-index']) { ?>
                                <li id="loans_applications">
                                    <a class="submenu" href="<?= admin_url('loans/applications'); ?>">
                                        <i class="fa-regular fa fa-heart"></i>
                                        <span class="text"> <?= lang('applications');?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['loans-index']) { ?>
                                <li id="loans_index">
                                    <a class="submenu" href="<?= admin_url('loans'); ?>">
                                        <i class="fa-regular fa fa-heart"></i>
                                        <span class="text"> <?= lang('loans');?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['loans-payments']) { ?>
                                <li id="loans_missed_repayments">
                                    <a class="submenu" href="<?= admin_url('loans/missed_repayments'); ?>">
                                        <i class="fa-regular fa fa-usd"></i>
                                        <span class="text"> <?= lang('missed_repayments');?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['loans-payments']) { ?>
                                <!-- <li id="loans_loan_collectable">
                                    <a class="submenu" href="<?= admin_url('loans/loan_collectable'); ?>">
                                        <i class="fa-regular fa fa-usd"></i>
                                        <span class="text"> <?= lang('loan_collectables');?></span>
                                    </a>
                                </li> -->
                                <?php } if ($Owner || $Admin || $GP['loans-borrowers']) { ?>
                                <li id="loans_borrowers">
                                    <a class="submenu" href="<?= admin_url('loans/borrowers'); ?>">
                                        <i class="fa-regular fa fa-star"></i>
                                        <span class="text"> <?= lang('borrowers');?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['loans-loan_products']) { ?>
                                <li id="loans_loan_products">
                                    <a class="submenu" href="<?= admin_url('loans/loan_products'); ?>">
                                        <i class="fa-regular fa fa-heart"></i>
                                        <span class="text"> <?= lang('loan_products');?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['loans-charges']) { ?>
                                <li id="loans_charges">
                                    <a class="submenu" href="<?= admin_url('loans/charges'); ?>">
                                        <i class="fa-regular fa fa-heart"></i>
                                        <span class="text"> <?= lang('charges');?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['loans-index']) { ?>
                                <li id="loans_calculator">
                                    <a class="submenu" href="<?= admin_url('loans/calculator'); ?>">
                                        <i class="fa-regular fa fa-calculator"></i>
                                        <span class="text"> <?= lang('calculator');?></span>
                                    </a>
                                </li>
                                <?php } ?>
                                </ul>
                            </li>
                        <?php 
                        }
                        if((($this->Settings->ui == 'full')? ($Settings->module_pawn && $session_module =='pawn') : $Settings->module_pawn)
                            && ($Owner || $Admin|| $GP['pawns-index']|| $GP['pawns-returns']|| $GP['pawns-purchases']|| $GP['pawns-products'])){ ?>
                            <li class="mm_pawns">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-star"></i>
                                    <span class="text"> <?= lang('pawn'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                <?php if ($Owner || $Admin || $GP['pawns-index']) { ?>
                                <li id="pawns_index">
                                    <a class="submenu" href="<?= admin_url('pawns'); ?>">
                                        <i class="fa-regular fa fa-heart"></i>
                                        <span class="text"> <?= lang('pawns'); ?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['pawns-returns']) { ?>
                                <li id="pawns_returns">
                                    <a class="submenu" href="<?= admin_url('pawns/returns'); ?>">
                                        <i class="fa-regular fa fa-heart"></i>
                                        <span class="text"> <?= lang('pawn_returns'); ?></span>
                                    </a>
                                </li>
                                <?php } if ($Owner || $Admin || $GP['pawns-purchases']) { ?>
                                    <li id="pawns_purchase">
                                        <a class="submenu" href="<?= admin_url('pawns/purchase'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('pawn_purchases'); ?></span>
                                        </a>
                                    </li>
                                <?php } if ($Owner || $Admin || $GP['pawns-products']) { ?>
                                    <li id="pawns_products">
                                        <a class="submenu" href="<?= admin_url('pawns/products'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('pawn_products'); ?></span>
                                        </a>
                                    </li>
                                <?php } ?>  
                                </ul>
                            </li>
                        <?php 
                        }
                        if((($this->Settings->ui == 'full')? ($Settings->module_save && $session_module =='saving') : $Settings->module_save)
                            && ($Owner||$Admin||$GP['savings-index'])){ ?>
                            <li class="mm_savings">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-star"></i>
                                    <span class="text"> <?= lang('saving'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin || $GP['savings-index']) { ?>
                                    <li id="savings_index">
                                        <a class="submenu" href="<?= admin_url('savings'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('savings');?></span>
                                        </a>
                                    </li>
                                    <li id="savings_saving_products">
                                        <a class="submenu" href="<?= admin_url('savings/saving_products'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('saving_products');?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                    
                                </ul>
                            </li>
                        <?php 
                        }
                        if((($this->Settings->ui == 'full')? ($Settings->module_installment && $session_module =='installment') : $Settings->module_installment)
                            && ($Owner || $Admin ||$GP['installments-index']|| $GP['installments-payments']|| $GP['installments-penalty'])){ ?>
                            <li class="mm_installments">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-star"></i>
                                    <span class="text"> <?= lang('installment'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                <?php if ($Owner || $Admin || $GP['installments-index']) { ?>
                                    <li id="installments_index">
                                        <a class="submenu" href="<?= admin_url('installments'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('installments');?></span>
                                        </a>
                                    </li>
                                <?php } if ($Owner || $Admin || $GP['installments-payments']) { ?>
                                    <li id="installments_missed_repayments">
                                        <a class="submenu" href="<?= admin_url('installments/missed_repayments'); ?>">
                                            <i class="fa-regular fa fa-usd"></i>
                                            <span class="text"> <?= lang('missed_repayments');?></span>
                                        </a>
                                    </li>
                                <?php } if ($Owner || $Admin || $GP['installments-penalty']) { ?>
                                    <li id="installments_missed_repayments">
                                        <a class="submenu" href="<?= admin_url('installments/penalty'); ?>">
                                            <i class="fa-regular fa fa-usd"></i>
                                            <span class="text"> <?= lang('penalty');?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </li>
                        <?php }

                        if ((($this->Settings->ui == 'full') ? ($Settings->module_hotel_apartment && $session_module =='hotel_apartment') : $Settings->module_hotel_apartment)
                            && ($Owner || $Admin ||$GP['sales_order-index'])) { ?>
                            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-hotel"></i>
                                    <span class="text"> <?= lang('hotel'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>                                    
                                    <li id="sales_add">
                                        <a class="submenu" href="<?= admin_url('room/daily_room'); ?>">
                                            <i class="fa-regular fa-calendar-days"></i>
                                            <span class="text"> <?= lang('room_calendar'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_add" class="hide">
                                        <a class="submenu" href="<?= admin_url('room'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('room'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_add">
                                        <a class="submenu" href="<?= admin_url('room/checkin'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('check_in'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_reservation">
                                        <a class="submenu" href="<?= admin_url('room/reservation'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('reservation'); ?></span>
                                        </a>
                                    </li>
                                    <li id="suspended_note">
                                        <a class="submenu" href="<?= admin_url('table/suspend_note'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('Table|Rooms'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="mm_rooms">
                                <a class="dropmenu" href="#">
                                    <i class="fa fa-braille" aria-hidden="true"></i>
                                    <span class="text"> <?= lang('room'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>     
                                    <li id="suspended_note">
                                        <a class="submenu" href="<?= admin_url('table/suspend_note'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('Rooms'); ?></span>
                                        </a>
                                    </li>     
                                    <li id="sales_reservation">
                                        <a class="submenu" href="<?= admin_url('room/reservation'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('reservations'); ?></span>
                                        </a>
                                    </li>                          
                                    <li id="sales_add">
                                        <a class="submenu" href="<?= admin_url('room/checkin'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('check-in/check-out'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php }
                        if ((($this->Settings->ui == 'full') ? ($Settings->module_express && $session_module =='express') : $Settings->module_express)
                            && ($Owner || $Admin)) { ?>
                            <li class="mm_tickets">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-heart"></i>
                                    <span class="text"> <?= lang('express'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="tickets_index">
                                        <a class="submenu" href="<?= admin_url('tickets'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('seats'); ?></span>
                                        </a>
                                    </li>
                                    <li id="tickets_list_ticket">
                                        <a class="submenu" href="<?= admin_url('tickets/list_ticket'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('booking_tickets'); ?></span>
                                        </a>
                                    </li>    
                                </ul>
                            </li>
                        <?php 
                        } if($this->Settings->module_concrete && ($Owner || $Admin || $GP['concretes-groups'] || $GP['concretes-absents'] || $GP['concretes-officer_commissions'] || $GP['concretes-commissions'] || $GP['concretes-fuel_expenses'] || $GP['concretes-missions'] || $GP['concretes-moving_waitings'] || $GP['concretes-mission_types'] || $GP['concretes-daily_errors'] || $GP['concretes-daily_error_materials'] || $GP['concretes-errors'] || $GP['concretes-adjustments'] || $GP['concretes-daily_stock_ins'] || $GP['concretes-inventory_in_outs'] || $GP['concretes-daily_stock_outs']  || $GP['concretes-officers'] || $GP['concretes-pump_commissions'] || $GP['concretes-truck_commissions'] || $GP['concretes-fuels'] || $GP['concretes-sales'] || $GP['concretes-daily_deliveries'] || $GP['concretes-casting_types'] || $GP['concretes-slumps'] || $GP['concretes-deliveries'] || $GP['concretes-drivers'] || $GP['concretes-trucks'])){ ?>
                        <li class="mm_concretes">
                            <a class="dropmenu" href="#">
                                <i class="fa fa-users"></i>
                                <span class="text"> <?= lang('concrete'); ?> </span> 
                                <span class="chevron closed"></span>
                            </a>
                            <ul>
                            <?php if($Owner || $Admin || $GP['concretes-deliveries']) { ?>
                                <li id="concretes_index">
                                    <a class="submenu" href="<?= admin_url('concretes'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('deliveries'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Settings->moving_waitings && ($Owner || $Admin || $GP['concretes-moving_waitings'])) { ?>
                                <li id="concretes_moving_waitings">
                                    <a class="submenu" href="<?= admin_url('concretes/moving_waitings'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('moving_waitings'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Settings->missions && ($Owner || $Admin || $GP['concretes-missions'])) { ?>
                                <li id="concretes_missions">
                                    <a class="submenu" href="<?= admin_url('concretes/missions'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('missions'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['concretes-fuels']) { ?>
                                <li id="concretes_fuels">
                                    <a class="submenu" href="<?= admin_url('concretes/fuels'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuels'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Settings->fuel_expenses && ($Owner || $Admin || $GP['concretes-fuel_expenses'])) { ?>
                                <li id="concretes_fuel_expenses">
                                    <a class="submenu" href="<?= admin_url('concretes/fuel_expenses'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuel_expenses'); ?></span>
                                    </a>
                                </li>       
                            <?php } if($Owner || $Admin || $GP['concretes-sales']) { ?>
                                <li id="concretes_sales">
                                    <a class="submenu" href="<?= admin_url('concretes/sales'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('sales'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['concretes-adjustments']) { ?>
                                <li id="concretes_adjustments">
                                    <a class="submenu" href="<?= admin_url('concretes/adjustments'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('adjustments'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Settings->errors && ($Owner || $Admin || $GP['concretes-errors'])) { ?>
                                <li id="concretes_errors">
                                    <a class="submenu" href="<?= admin_url('concretes/errors'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('errors'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Settings->absents && ($Owner || $Admin || $GP['concretes-absents'])) { ?>
                                <li id="concretes_absents">
                                    <a class="submenu" href="<?= admin_url('concretes/absents'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('absents'); ?></span>
                                    </a>
                                </li>
                            <?php } if($this->config->item('concrete_commission') && $Owner || $Admin || $GP['concretes-commissions']) { ?>
                                <li id="concretes_commissions">
                                    <a class="submenu" href="<?= admin_url('concretes/commissions'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('commissions'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['concretes-trucks']) { ?>
                                <li id="concretes_trucks">
                                    <a class="submenu" href="<?= admin_url('concretes/trucks'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('trucks'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['concretes-slumps']) { ?>
                                <li id="concretes_slumps">
                                    <a class="submenu" href="<?= admin_url('concretes/slumps'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('slumps'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['concretes-casting_types']) { ?>
                                <li id="concretes_casting_types">
                                    <a class="submenu" href="<?= admin_url('concretes/casting_types'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('casting_types'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Settings->missions && ($Owner || $Admin || $GP['concretes-mission_types'])) { ?>
                                <li id="concretes_mission_types">
                                    <a class="submenu" href="<?= admin_url('concretes/mission_types'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('mission_types'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['concretes-officers']) { ?>
                                <li id="concretes_officers">
                                    <a class="submenu" href="<?= admin_url('concretes/officers'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('officers'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['concretes-groups']) { ?>
                                <li id="concretes_groups">
                                    <a class="submenu" href="<?= admin_url('concretes/groups'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('groups'); ?></span>
                                    </a>
                                </li>
                            <?php } ?>
                            </ul>
                        </li>   
                        <?php } if($this->Settings->module_truckings && ($Owner || $Admin || $GP['truckings-cash_advance_by_driver_report'] || $GP['truckings-cash_advance_summary_report'] || $GP['truckings-cash_advance_report'] || $GP['truckings-trucking_expense_report'] || $GP['truckings-trucking_report'] || $GP['sales-index'] || $GP['truckings-cash_advances'] || $GP['truckings-index'] || $GP['truckings-dry_ports'] || $GP['truckings-factories'] || $GP['truckings-containers'] || $GP['truckings-drivers'] || $GP['truckings-trucks'])){ ?>
                        <li class="mm_truckings">
                            <a class="dropmenu" href="#">
                                <i class="fa fa-truck"></i>
                                <span class="text"> <?= lang('trucking'); ?> </span> 
                                <span class="chevron closed"></span>
                            </a>
                            <ul>
                            <?php if($Owner || $Admin || $GP['truckings-index']) { ?>
                                <li id="truckings_index">
                                    <a class="submenu" href="<?= admin_url('truckings'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('truckings'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-cash_advances']) { ?>
                                <li id="truckings_cash_advances">
                                    <a class="submenu" href="<?= admin_url('truckings/cash_advances'); ?>">
                                        <i class="fa fa-money"></i><span class="text"> <?= lang('cash_advances'); ?></span>
                                    </a>
                                </li>
                                <li id="truckings_cash_advance_reconciliations">
                                    <a class="submenu" href="<?= admin_url('truckings/cash_advance_reconciliations'); ?>">
                                        <i class="fa fa-money"></i><span class="text"> <?= lang('cash_advance_reconciliations'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['sales-index']) { ?>
                                <li id="truckings_sales">
                                    <a class="submenu" href="<?= admin_url('truckings/sales'); ?>">
                                        <i class="fa fa-money"></i><span class="text"> <?= lang('sales'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-drivers']) { ?>
                                <li id="truckings_drivers">
                                    <a class="submenu" href="<?= admin_url('truckings/drivers'); ?>">
                                        <i class="fa fa-users"></i><span class="text"> <?= lang('drivers'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-trucks']) { ?>
                                <li id="truckings_trucks">
                                    <a class="submenu" href="<?= admin_url('truckings/trucks'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('trucks'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-containers']) { ?>
                                <li id="truckings_containers">
                                    <a class="submenu" href="<?= admin_url('truckings/containers'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('containers'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-factories']) { ?>
                                <li id="truckings_factories">
                                    <a class="submenu" href="<?= admin_url('truckings/factories'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('factories'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-dry_ports']) { ?>
                                <li id="truckings_dry_ports">
                                    <a class="submenu" href="<?= admin_url('truckings/dry_ports'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('dry_ports'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['truckings-trucking_report']) { ?>
                                <li id="truckings_trucking_report">
                                    <a class="submenu" href="<?= admin_url('truckings/trucking_report'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('trucking_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['truckings-trucking_expense_report']) { ?>
                                <li id="truckings_trucking_expense_report">
                                    <a class="submenu" href="<?= admin_url('truckings/trucking_expense_report'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('trucking_expense_report'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-cash_advance_report']) { ?>
                                <li id="truckings_cash_advance_report">
                                    <a class="submenu" href="<?= admin_url('truckings/cash_advance_report'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('cash_advance_report'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-cash_advance_summary_report']) { ?>
                                <li id="truckings_cash_advance_summary_report">
                                    <a class="submenu" href="<?= admin_url('truckings/cash_advance_summary_report'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('cash_advance_summary_report'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['truckings-cash_advance_by_driver_report']) { ?>
                                <li id="truckings_cash_advance_by_driver_report">
                                    <a class="submenu" href="<?= admin_url('truckings/cash_advance_by_driver_report'); ?>">
                                        <i class="fa fa-truck"></i><span class="text"> <?= lang('cash_advance_by_driver_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } ?>
                            </ul>
                        </li>
                        
                    <?php } if($this->Settings->module_clearance && ($Owner || $Admin || $GP['reports-sales'] || $GP['clearances-sale_statement_report'] || $GP['clearances-containers'] || $GP['sales-index'] || $GP['clearances-expense_payment_report'] || $GP['clearances-income_by_booking_report'] || $GP['clearances-clearance_report'] || $GP['clearances-expense_by_booking_report'] || $GP['clearances-dry_ports'] || $GP['clearances-trucking_report'] || $GP['clearances-expense_payments'] || $GP['clearances-plan_report'] || $GP['clearances-booking_report'] || $GP['clearances-truckings'] || $GP['clearances-bookings'] || $GP['clearances-countries'] || $GP['clearances-ports'] || $GP['clearances-index'] || $GP['clearances-lines'])){ ?>
                        <li class="mm_clearances">
                            <a class="dropmenu" href="#">
                                <i class="fa fa-book"></i>
                                <span class="text"> <?= lang('clearance'); ?> </span> 
                                <span class="chevron closed"></span>
                            </a>
                            <ul>
                            <?php if($Owner || $Admin || $GP['clearances-bookings']) { ?>
                                <li id="clearances_bookings">
                                    <a class="submenu" href="<?= admin_url('clearances/bookings'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('bookings'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-truckings']) { ?>
                                <li id="clearances_truckings">
                                    <a class="submenu" href="<?= admin_url('clearances/truckings'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('truckings'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-index']) { ?>
                                <li id="clearances_index">
                                    <a class="submenu" href="<?= admin_url('clearances'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('clearances'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['sales-index']) { ?>
                                <li id="clearances_sales">
                                    <a class="submenu" href="<?= admin_url('clearances/sales'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('sales'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-expense_payments']) { ?>
                                <li id="clearances_expense_payments">
                                    <a class="submenu" href="<?= admin_url('clearances/expense_payments'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('expense_payments'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-lines']) { ?>
                                <li id="clearances_lines">
                                    <a class="submenu" href="<?= admin_url('clearances/lines'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('lines'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-vessels']) { ?>
                                <li id="clearances_vessels">
                                    <a class="submenu" href="<?= admin_url('clearances/vessels'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('vessels'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-ports']) { ?>
                                <li id="clearances_ports">
                                    <a class="submenu" href="<?= admin_url('clearances/ports'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('ports'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-countries']) { ?>
                                <li id="clearances_countries">
                                    <a class="submenu" href="<?= admin_url('clearances/countries'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('countries'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-dry_ports']) { ?>
                                <li id="clearances_dry_ports">
                                    <a class="submenu" href="<?= admin_url('clearances/dry_ports'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('dry_ports'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-containers']) { ?>
                                <li id="clearances_containers">
                                    <a class="submenu" href="<?= admin_url('clearances/containers'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('containers'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-plan_report']) { ?>
                                <li id="clearances_plan_report">
                                    <a class="submenu" href="<?= admin_url('clearances/plan_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('plan_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-booking_report']) { ?>
                                <li id="clearances_booking_report">
                                    <a class="submenu" href="<?= admin_url('clearances/booking_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('booking_report'); ?></span>
                                    </a>
                                </li>   
                                <li id="clearances_booking_detail_report">
                                    <a class="submenu" href="<?= admin_url('clearances/booking_detail_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('booking_detail_report'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-trucking_report']) { ?>
                                <li id="clearances_trucking_report">
                                    <a class="submenu" href="<?= admin_url('clearances/trucking_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('trucking_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-clearance_report']) { ?>
                                <li id="clearances_clearance_report">
                                    <a class="submenu" href="<?= admin_url('clearances/clearance_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('clearance_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['reports-sales']) { ?>
                                <li id="clearances_sale_report">
                                    <a class="submenu" href="<?= admin_url('clearances/sale_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('sale_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-expense_by_booking_report']) { ?>
                                <li id="clearances_expense_by_booking_report">
                                    <a class="submenu" href="<?= admin_url('clearances/expense_by_booking_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('expense_by_booking_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['clearances-income_by_booking_report']) { ?>
                                <li id="clearances_income_by_booking_report">
                                    <a class="submenu" href="<?= admin_url('clearances/income_by_booking_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('income_by_booking_report'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-expense_payment_report']) { ?>
                                <li id="clearances_expense_payment_report">
                                    <a class="submenu" href="<?= admin_url('clearances/expense_payment_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('expense_payment_report'); ?></span>
                                    </a>
                                </li>
                                <li id="clearances_expense_by_product_report">
                                    <a class="submenu" href="<?= admin_url('clearances/expense_by_product_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('expense_by_product_report'); ?></span>
                                    </a>
                                </li>
                            <?php } if($Owner || $Admin || $GP['clearances-sale_statement_report']) { ?>
                                <li id="clearances_sale_statement_report">
                                    <a class="submenu" href="<?= admin_url('clearances/sale_statement_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('sale_statement_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } if($Owner || $Admin || $GP['reports-sales']) { ?>
                                <li id="clearances_sale_form_report">
                                    <a class="submenu" href="<?= admin_url('clearances/sale_form_report'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('sale_form_report'); ?></span>
                                    </a>
                                </li>   
                            <?php } ?>
                            </ul>
                        </li>   
                        
                        <?php } 
                        if ($Settings->module_sale && $Settings->store_sales && ($Owner||$Admin||$GP['store_sales-index'] || $GP['store_sales-add'] || $GP['store_sales_order-index'] || $GP['store_sales_order-add'])) { 
                            ?>
                            <li class="mm_store_sales">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-heart"></i>
                                    <span class="text"> <?= lang('store_sales'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner||$Admin||$GP['store_sales-index']) { ?>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_store'); ?>">
                                            <i class="fa-regular fa fa-heart-o"></i>
                                            <span class="text"> <?= lang('store_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Owner||$Admin||$GP['store_sales-add']) { ?>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_store/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_store_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Owner||$Admin||$GP['store_sales_order-index']) { ?>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_order_store'); ?>">
                                            <i class="fa-regular fa fa-heart-o"></i>
                                            <span class="text"> <?= lang('store_sales_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Owner||$Admin||$GP['store_sales_order-add']) { ?>
                                    <li id="quotes_index">
                                        <a class="submenu" href="<?= admin_url('sales_order_store/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_store_sales_order'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php 
                        } 
                        if ($Settings->module_gym) { ?>
                            <li class="mm_gym mm_customers mm_member_cards">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('gym'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul> 
                                    <li id="sales_member_cards">
                                        <a class="submenu" href="<?= admin_url('member_cards/pos'); ?>">
                                            <i class="fa-regular fa fa-gift"></i>
                                            <span class="text"> <?= lang('pos'); ?></span>
                                        </a>
                                    </li>            
                                    <li id="gym_schedules">
                                        <a class="submenu" href="<?= admin_url('gym/schedules'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('schedule'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_index">
                                        <a class="submenu" href="<?= admin_url('gym/trainees'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('trainees'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_sales">
                                        <a class="submenu" href="<?= admin_url('gym/sales'); ?>">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('list_sales'); ?></span>
                                        </a>
                                    </li>
                                 
                                    <li id="gym_add_sale">
                                        <a class="submenu" href="<?= admin_url('gym/add_sale'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_classes">
                                        <a class="submenu" href="<?= admin_url('gym/classes'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('classes'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_trainers">
                                        <a class="submenu" href="<?= admin_url('gym/trainers'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('trainers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_level">
                                        <a class="submenu" href="<?= admin_url('gym/level'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('level'); ?></span>
                                        </a>
                                    </li>
                                    <li id="customers_membership">
                                        <a href="<?= admin_url('customers/membership') ?>">
                                            <i class="fa-regular fa fa-chain"></i><span class="text"> <?= lang('membership'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_member_cards">
                                        <a class="submenu" href="<?= admin_url('gym/membercards'); ?>">
                                            <i class="fa-regular fa fa-gift"></i>
                                            <span class="text"> <?= lang('member_cards'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_activity">
                                        <a class="submenu" href="<?= admin_url('gym/activity'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('activity'); ?></span>
                                        </a>
                                    </li>
                                    <li id="gym_categories">
                                        <a class="submenu" href="<?= admin_url('gym/categories'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('categories'); ?></span>
                                        </a>
                                    </li> 
                                    <li id="gym_workouts">
                                        <a class="submenu" href="<?= admin_url('gym/workouts'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('workouts'); ?></span>
                                        </a>
                                    </li> 

                                    <?php 
                                     if($Owner || $Admin || $GP['member_cards-attendances']) { ?>
                                        <li id="gym_arrival_membership">
                                            <a class="submenu" href="<?= admin_url('member_cards/arrival_membership'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('arrival_membership'); ?></span>
                                            </a>
                                        </li>
                                        <li id="gym_attendances">
                                            <a class="submenu" href="<?= admin_url('gym/attendances'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('attendances'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-teacher_attendances']) { ?>
                                        <li id="gym_teacher_attendances" class="hide">
                                            <a class="submenu" href="<?= admin_url('schools/teacher_attendances'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('teacher_attendances'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } 
                                    ?>
                                    
                              
                                </ul>
                            </li>
                        <?php 
                        } 
                        if(
                            (
                            ($this->Settings->ui == 'full')? ($Settings->module_school && $session_module =='school') : $Settings->module_school
                            )

                            && ($Owner || $Admin || $GP['schools-index'])){

                        ?>
                            <li class="mm_auth">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('student_information'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="schools_admission">
                                        <a class="submenu" href="<?= admin_url('schools/admissions'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_admission'); ?></span>
                                        </a>
                                    </li>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('schools/add_student'); ?>" >
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_admission'); ?></span>
                                        </a>
                                    </li>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('schools/assign_student'); ?>" >
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('assgin_students'); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                    if($Owner || $Admin || $GP['schools-student_statuses']) { ?>
                                        <li id="schools_student_statuses">
                                            <a class="submenu" href="<?= admin_url('schools/add_student_status'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('add_student_status'); ?></span>
                                            </a>
                                        </li>
                                        <li id="schools_student_statuses">
                                            <a class="submenu" href="<?= admin_url('schools/student_statuses'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('student_statuses'); ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    if($Owner || $Admin || $GP['schools-index']) { ?>
                                        <li id="schools_index">
                                            <a class="submenu" href="<?= admin_url('schools'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('students'); ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    if($Owner || $Admin || $GP['schools-index']) { ?>
                                        <li id="schools_index">
                                            <a class="submenu" href="<?= admin_url('schools/students_scholarship'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('students_scholarship'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_auth">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('academic'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                   <?php
                                   if($Owner || $Admin || $GP['schools-class_timetable']) { ?>
                                    <li id="schools_class_timetable">
                                        <a class="submenu" href="<?= admin_url('schools/class_timetable'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('class_timetable'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['schools-teacher_time_table']) { ?>
                                    <li id="schools_assign_class_teacher">
                                        <a class="submenu" href="<?= admin_url('schools/teacher_time_table'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('teacher_time_table'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['schools-assign_class_teacher']) { ?>
                                    <li id="schools_assign_class_teacher">
                                        <a class="submenu" href="<?= admin_url('schools/assign_class_teacher'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('assign_class_teacher'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['schools-programs']) { ?>    
                                    <li id="schools_programs">
                                        <a class="submenu" href="<?= admin_url('schools/programs'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('programs'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['schools-colleges']) { ?>    
                                    <li id="schools_colleges">
                                        <a class="submenu" href="<?= admin_url('schools/colleges'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('colleges'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['schools-skills']) { ?>  
                                        <li id="schools_skills">
                                            <a class="submenu" href="<?= admin_url('schools/skills'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('skills'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } if($Owner || $Admin || $GP['schools-grades']) { ?>  
                                        <li id="schools_grades">
                                            <a class="submenu" href="<?= admin_url('schools/grades'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('grades'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-subjects']) { ?>    
                                        <li id="schools_subjects">
                                            <a class="submenu" href="<?= admin_url('schools/subjects'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('subjects'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-sections']) { ?>    
                                        <li id="schools_sections">
                                            <a class="submenu" href="<?= admin_url('schools/sections'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sections'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-rooms']) { ?>   
                                        <li id="schools_rooms">
                                            <a class="submenu" href="<?= admin_url('schools/rooms'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('rooms'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-classes']) { ?> 
                                        <li id="schools_classes">
                                            <a class="submenu" href="<?= admin_url('schools/classes'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('classes'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-credit_scores']) { ?>   
                                        <li id="schools_credit_scores">
                                            <a class="submenu" href="<?= admin_url('schools/credit_scores'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('credit_scores'); ?></span>
                                            </a>
                                        </li>
                                   <?php }  
                                   if($Owner || $Admin || $GP['schools-feedback_questions']) { ?>  
                                        <li id="schools_feedback_questions">
                                            <a class="submenu" href="<?= admin_url('schools/feedback_questions'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('feedback_questions'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-testing_groups']) { ?>  
                                        <li id="schools_testing_groups">
                                            <a class="submenu" href="<?= admin_url('schools/testing_groups'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_groups'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-testing_results']) { ?> 
                                        <li id="schools_testing_results">
                                            <a class="submenu" href="<?= admin_url('schools/testing_results'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_results'); ?></span>
                                            </a>
                                        </li>   
                                    <!-- <?php } if($Owner || $Admin || $GP['schools-black_lists']) { ?> 
                                        <li id="schools_black_lists">
                                            <a class="submenu" href="<?= admin_url('schools/black_lists'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('black_lists'); ?></span>
                                            </a>
                                        </li>  -->  
                                    <?php } if($Owner || $Admin || $GP['schools-tickets']) { ?>
                                        <li id="schools_tickets">
                                            <a class="submenu" href="<?= admin_url('schools/tickets'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('tickets'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-waitings']) { ?>
                                        <li id="schools_waitings">
                                            <a class="submenu" href="<?= admin_url('schools/waitings'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('waitings'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-testings']) { ?>
                                        <li id="schools_testings">
                                            <a class="submenu" href="<?= admin_url('schools/testings'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testings'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-document_forms']) { ?>
                                        <li id="schools_document_list">
                                            <a class="submenu" href="<?= admin_url('schools/document_lists'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('document_lists'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-document_forms']) { ?>
                                        <li id="schools_document_forms">
                                            <a class="submenu" href="<?= admin_url('schools/document_forms'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('document_forms'); ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                   ?>
                                   <li id="schools_document_forms">
                                        <a class="submenu" href="<?= admin_url('schools/add_forms'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('Add_form'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="mm_account">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('fees_collection'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php 
                                    if($Owner || $Admin || $GP['schools-sales']) { ?>
                                        <li id="schools_sales">
                                            <a class="submenu" href="<?= admin_url('schools/sales'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sales'); ?></span>
                                            </a>
                                        </li>
                                        <li id="schools_sales">
                                            <a class="submenu" href="<?= admin_url('schools/other_income'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('other_income'); ?></span>
                                            </a>
                                        </li>
                                        <li id="schools_sale_by_excel">
                                            <a class="submenu" href="<?= admin_url('schools/sale_by_excel'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('import_sale_by_excel'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-fees_master']) { ?>   
                                        <li id="schools_credit_scores">
                                            <a class="submenu" href="<?= admin_url('schools/fees_master'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fees_master'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-fees_type']) { ?>  
                                        <li id="schools_fees_type">
                                            <a class="submenu" href="<?= admin_url('schools/fees_type'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fees_type'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } if($Owner || $Admin || $GP['schools-scholarships']) { ?>   
                                        <li id="schools_credit_scores">
                                            <a class="submenu" href="<?= admin_url('schools/scholarships'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('scholarships'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_examinations">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('examinations'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if($Owner || $Admin || $GP['schools-examinations']) { ?>        
                                        <!-- <li id="schools_examinations">
                                            <a class="submenu" href="<?= admin_url('schools/examinations'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('examinations'); ?></span>
                                            </a>
                                        </li>
                                        <li id="schools_examination_details">
                                            <a class="submenu" href="<?= admin_url('schools/examination_details'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('examination_details'); ?></span>
                                            </a>
                                        </li> -->
                                        <li id="schools_examinations">
                                            <a class="submenu" href="<?= admin_url('schools/exams'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('examinations'); ?></span>
                                            </a>
                                        </li>
                                        <li id="schools_examinations">
                                            <a class="submenu" href="<?= admin_url('schools/credit_score_percentage'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('credit_score_percentage'); ?></span>
                                            </a>
                                        </li>
                                        <li id="schools_examinations">
                                            <a class="submenu" href="<?= admin_url('schools/grade_point_average'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('grade_point_average'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="mm_attendances">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-clock"></i>
                                    <span class="text"> <?= lang('attendances'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php 
                                     if($Owner || $Admin || $GP['schools-attendances']) { ?>
                                        <li id="schools_attendances">
                                            <a class="submenu" href="<?= admin_url('schools/attendances'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('attendances'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['schools-teacher_attendances']) { ?>
                                        <li id="schools_teacher_attendances">
                                            <a class="submenu" href="<?= admin_url('schools/teacher_attendances'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('teacher_attendances'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } 
                                    ?>
                                    
                                </ul>
                            </li>
                            <li class="mm_course mm_lesson">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('course'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="schools_course">
                                        <a class="submenu" href="<?= admin_url('schools/course'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('course'); ?></span>
                                        </a>
                                    </li>
                                    <li id="schools_lesson">
                                        <a class="submenu" href="<?= admin_url('schools/lesson'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('lesson'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="mm_library" id="mm_library">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-book fa_default"></i>
                                    <span class="text"> <?= lang('library'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if($Owner||$Admin||$GP['assets-index']){?>
                                    <li id="library_index">
                                        <a class="submenu" href="<?= admin_url('library'); ?>">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('list_books'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['assets-add']){?>
                                    <li id="library_add">
                                        <a class="submenu" href="<?= admin_url('library/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_book'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['products-using_stocks']){?>
                                    <li id="library_borrow" class="sub_navigation">
                                        <a class="submenu" href="<?= admin_url('library/borrow'); ?>">
                                            <i class="fa-regular fa fa-filter"></i>
                                            <span class="text"> <?= lang('list_borrow'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['products-using_stocks-add']){?>
                                    <li id="library_add_borrow" class="sub_navigation">
                                        <a class="submenu" href="<?= admin_url('library/add_borrow'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_borrow'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>
                                </ul>
                            </li>
                            <li class="mm_drivers" id="mm_drivers">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-truck fa_default"></i>
                                    <span class="text"> <?= lang('transport'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php 
                                    if ($Owner||$Admin||$GP['drivers-index']) { ?>
                                    <li id="drivers_index">
                                        <a class="submenu" href="<?= admin_url('drivers'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_drivers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['drivers-add']) { ?>
                                    <li id="drivers_create_driver">
                                        <a class="submenu" href="<?= admin_url('drivers/create_driver'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_driver'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['drivers-route']){?>
                                    <li id="drivers_route">
                                        <a class="submenu" href="<?= admin_url('drivers/route'); ?>">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('route'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner||$Admin||$GP['drivers-vehicles']){?>
                                    <li id="drivers_vehicles">
                                        <a class="submenu" href="<?= admin_url('drivers/vehicles'); ?>">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('vehicles'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>
                                </ul>
                            </li>
                            <li class="mm_certificate">
                                <a class="dropmenu" href="#">
                                    <i class="fa-sharp fa-regular fa-graduation-cap"></i>
                                    <span class="text"> <?= lang('certificate'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php 
                                     if($Owner || $Admin || $GP['hr-id_cards']){ ?>
                                        <li id="hr_id_cards">
                                            <a class="submenu" href="<?= admin_url('hr/id_cards'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('id_cards'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-sample_id_cards']) { ?>
                                        <li id="hr_sample_id_cards">
                                            <a class="submenu" href="<?= admin_url('hr/sample_id_cards'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sample'); ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    ?>
                                    
                                </ul>
                            </li>
                        <?php  
                        } 
                        if((($this->Settings->ui == 'full')? ($Settings->project && $session_module =='project') : $Settings->project)

                            && ($Owner || $Admin)){ ?>
                            <li class="mm_projects">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-diagram-project"></i>
                                    <span class="text"> <?= lang('project'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="projects_index">
                                        <a class="submenu" href="<?= admin_url('projects'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('project'); ?></span>
                                        </a>
                                    </li>
                                    <li id="projects_add">
                                        <a class="submenu" href="<?= admin_url('projects/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_project'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_table">
                                        <a class="submenu" href="<?= admin_url('projects/tasks'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('tasks'); ?></span>
                                        </a>
                                    </li>
                                    <li id="projects_plans">
                                        <a class="submenu" href="<?= admin_url('projects/plans'); ?>">
                                            <i class="fa-regular fa fa-tags"></i><span class="text"> <?= lang('list_plan'); ?></span>
                                        </a>
                                    </li>
                                    <li id="projects_plan_add">
                                        <a class="submenu" href="<?= admin_url('projects/add_plan'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_plan'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php 
                        } 
                        if((($this->Settings->ui == 'full')? ($Settings->module_manufacturing && $session_module =='manufaturing') : $Settings->module_manufacturing)
                            && ($Owner || $Admin)){ ?>
                            <li class="mm_workorder">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-merge"></i>
                                    <span class="text"> <?= lang('workorder'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="products_table">
                                        <a class="submenu" href="<?= admin_url('workorder'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('workorder'); ?></span>
                                        </a>
                                    </li>                                   
                                    <li id="products_table">
                                        <a class="submenu" href="<?= admin_url('workorder/boms'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('bom'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php 
                        } 
                        if((($this->Settings->ui == 'full')? ($session_module =='module_exchange' && $Settings->module_exchange) : $Settings->module_exchange) && ($Owner||$Admin)) { 
                            ?>
                            <li class="mm_account">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-book"></i>
                                    <span class="text"> <?= lang('money_change') ?></span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner||$Admin||$GP['accounts-index']) { ?>
                                        <li id="money_index">
                                            <a class="submenu" href="<?= admin_url('money'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('list_exchange_money'); ?></span>
                                            </a>
                                        </li>
                                        <li id="account_add_exchange">
                                            <a class="submenu" href="<?= admin_url('money/add_exchange'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('add_exchange'); ?></span>
                                            </a>
                                        </li>
                                        <li id="money_exchange_rate">
                                            <a class="submenu" href="<?= admin_url('money/exchange_rate'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('exchange_rate'); ?></span>
                                            </a>
                                        </li>
                                        <li id="money_index">
                                            <a class="submenu" href="<?= admin_url('money/transfer_companies'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('transfer_companies'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php
                        }
                        if((($this->Settings->ui == 'full')? ($session_module =='accounting' && ($Settings->module_account)) : ($Settings->module_account)) && ($Owner||$Admin||$GP['accounts-index'] || $GP['account-list_receivable'] || $GP['account-list_ar_aging'] || $GP['account-ar_by_customer'] || $GP['account-bill_receipt'] || $GP['account-list_payable'] || $GP['account-list_ap_aging'] || $GP['account-ap_by_supplier'] || $GP['account-bill_payable'] || $GP['account-list_ac_head'] || $GP['account-list_customer_deposit'] || $GP['account-list_supplier_deposit'] || $GP['account_setting'])) {
                            ?>
                            <li class="mm_account">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-book"></i>
                                    <span class="text"> <?= lang('manage_accounts') ?></span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner||$Admin||$GP['accounts-index']) { ?>
                                        <li id="account_listjournal">
                                            <a class="submenu" href="<?= admin_url('account/listJournal'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('list_journal'); ?></span>
                                            </a>
                                        </li>
                                        <li id="account_transactions">
                                            <a class="submenu" href="<?= admin_url('account/transactions'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('transactions'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if ($Settings->module_account && ($Owner||$Admin||$GP['account-bill_receipt'])) { ?>
                                            <li id="account_billreceipt" class="hide">
                                                <a href="<?= admin_url('account/billReceipt') ?>">
                                                    <i class="fa-regular fa fa-money"></i><span class="text"> <?= lang('bill_receipt'); ?></span>
                                                </a>
                                            </li>
                                        <?php } if ($Settings->module_account && ($Owner||$Admin||$GP['account-bill_payable'])) { ?>
                                            <li id="account_billpayable" class="hide">
                                                <a href="<?= admin_url('account/billPayable') ?>">
                                                    <i class="fa-regular fa fa-money"></i><span class="text"> <?= lang('bill_payable'); ?></span>
                                                </a>
                                            </li>
                                        <?php } if ($Settings->module_account && ($Owner||$Admin||$GP['account-list_ac_head'])) { ?>
                                        <li id="account_index">
                                            <a class="submenu" href="<?= admin_url('account/sections'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('chart_account_section'); ?></span>
                                            </a>
                                        </li>
                                        <li id="account_index">
                                            <a class="submenu" href="<?= admin_url('account'); ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('list_ac_head'); ?></span>
                                            </a>
                                        </li>
                                        
                                        <?php } if ($Settings->module_account && ($Owner||$Admin||$GP['bank_reconcile'])) { ?>
                                            <li id="account_settings">
                                                <a href="<?= admin_url('account/bank_reconcile') ?>">
                                                    <i class="fa-regular fa fa-cog"></i><span class="text"> <?= lang('bank_reconcile'); ?></span>
                                                </a>
                                            </li>
                                        <?php } if ($Settings->module_account && ($Owner||$Admin||$GP['account_setting'])) { ?>
                                            <li id="account_settings">
                                                <a href="<?= admin_url('account/settings') ?>">
                                                    <i class="fa-regular fa fa-cog"></i><span class="text"> <?= lang('account_settings'); ?></span>
                                                </a>
                                            </li>
                                        <?php } if ($Settings->module_account && $Owner || $Admin || $GP['sales-deliveries']) { ?>
                                        <li id="account_credit_note">
                                            <a class="submenu" href="<?= admin_url('account/credit_note'); ?>">
                                                <i class="fa-regular fa fa-truck"></i>
                                                <span class="text"><?= lang('credit_note'); ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Settings->module_account && $Owner||$Admin||$GP['purchases-debit_note']) { ?>
                                        <li id="account_debit_note">
                                            <a class="submenu" href="<?= admin_url('account/debit_note'); ?>">
                                                <i class="fa-regular fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('debit_note'); ?></span>
                                            </a>
                                        </li>
                                        <?php }?>
                                        <li id="account_tansfer_payment" class="hide">
                                            <a href="<?= admin_url('account/tansfer_payment') ?>">
                                                <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('tansfer_payment'); ?></span>
                                            </a>
                                        </li>
                                        <li id="sales_customer_balance" class="hide">
                                            <a class="submenu" href="<?= admin_url('sales/customer_balance'); ?>">
                                                <i class="fa-regular fa fa-money"></i>
                                                <span class="text"> <?= lang('customer_balance'); ?></span>
                                            </a>
                                        </li>
                                        <li id="sales_customer_opening_balance" class="hide">
                                            <a class="submenu" href="<?= admin_url('sales/customer_opening_balance'); ?>">
                                                <i class="fa-regular fa fa-money"></i>
                                                <span class="text"> <?= lang('opening_ar'); ?></span>
                                            </a>
                                        </li>
                                        <li id="purchases_supplier_balance" class="hide">
                                            <a class="submenu" href="<?= admin_url('purchases/supplier_balance'); ?>">
                                                <i class="fa-regular fa fa-money"></i>
                                                <span class="text"> <?= lang('supplier_balance'); ?></span>
                                            </a>
                                        </li>
                                        <li id="purchases_supplier_opening_balance" class="hide">
                                            <a class="submenu" href="<?= admin_url('purchases/supplier_opening_balance'); ?>">
                                                <i class="fa-regular fa fa-money"></i>
                                                <span class="text"> <?= lang('opening_ap'); ?></span>
                                            </a>
                                        </li>
                                   
                                </ul> 
                            </li>
                        <?php }
                        if((($this->Settings->ui == 'full')? ($session_module =='tax' && $Settings->module_tax) : $Settings->module_tax)
                            && ($Owner || $Admin || $GP['taxs-index'] || $GP['taxs-purchases_report']|| $GP['taxs-sales_report'])){ ?>
                            <li class="mm_taxs <?= strtolower($this->router->fetch_method()) == 'taxs' ? 'mm_tax' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa-sharp fa-regular fa-magnifying-glass-dollar"></i>
                                    <span class="text"> <?= lang('tax'); ?> 
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                <?php if ($Owner || $Admin || $GP['taxs-index']) { ?>
                                    <li id="taxs_index">
                                        <a class="submenu" href="<?= admin_url('taxs'); ?>">
                                            <i class="fa-sharp fa-regular fa-hand-holding-dollar"></i>
                                            <span class="text"> <?= lang('taxs');?></span>
                                        </a>
                                    </li>
                                <?php } if ($Owner || $Admin || $GP['taxs-purchases_report']) { ?>
                                    <li id="taxs_purchases_report">
                                        <a class="submenu" href="<?= admin_url('taxs/purchases'); ?>">
                                            <i class="fa-regular fa-money-bill-1"></i>
                                            <span class="text"> <?= lang('purchases');?></span>
                                        </a>
                                    </li>
                                <?php } if ($Owner || $Admin || $GP['taxs-sales_report']) { ?>
                                    <li id="taxs_sales_report">
                                        <a class="submenu" href="<?= admin_url('taxs/sales'); ?>">
                                            <i class="fa-regular fa-money-check-dollar"></i>
                                            <span class="text"> <?= lang('sales');?></span>
                                        </a>
                                    </li>   
                                <?php } ?>
                                </ul>
                            </li>
                        <?php } 
                        if (
                            (
                            ($this->Settings->ui == 'full')? ($Settings->module_hr && $session_module =='hr') : $Settings->module_hr
                            )
                             && ($Owner || $Admin || $GP['hr-salary_reviews_report'] || $GP['hr-salary_reviews'] || $GP['hr-kpi_add'] || $GP['hr-id_cards'] || $GP['hr-id_cards_report'] || $GP['hr-sample_id_cards'] || $GP['hr-id_cards'] || $GP['hr-kpi_report'] || $GP['hr-employees_report'] || $GP['hr-banks_report'] || $GP['hr-kpi_index'] || $GP['hr-kpi_types'] || $GP['hr-index'] || $GP['hr-departments'] || $GP['hr-positions'] || $GP['hr-groups'] || $GP['hr-employee_types'] || $GP['hr-employees_relationships'] || $GP['hr-tax_conditions'] || $GP['hr-leave_categories'] || $GP['hr-leave_types'])) { ?>
                                <li class="mm_hr">
                                    <a class="dropmenu" href="#">
                                        <i class="fa-regular fa fa-users"></i>
                                        <span class="text"> <?= lang('hr'); ?> </span> 
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>            
                                    <?php if($Owner || $Admin || $GP['hr-index']){ ?>
                                        <li id="hr_index">
                                            <a class="submenu" href="<?= admin_url('hr'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('employees'); ?></span>
                                            </a>
                                        </li>

                                    <?php } if($Owner || $Admin || $GP['hr-positions']) { ?>
                                        <li id="hr_positions">
                                            <a class="submenu" href="<?= admin_url('hr/positions'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('positions'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-departments']) { ?>
                                        <li id="hr_departments">
                                            <a class="submenu" href="<?= admin_url('hr/departments'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('departments'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-groups']) { ?>
                                        <li id="hr_groups">
                                            <a class="submenu" href="<?= admin_url('hr/groups'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('groups'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-employee_types']) { ?>
                                        <li id="hr_employee_types">
                                            <a class="submenu" href="<?= admin_url('hr/employee_types'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('employee_types'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-employees_relationships']) { ?>
                                        <li id="hr_employees_relationships">
                                            <a class="submenu" href="<?= admin_url('hr/employees_relationships'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('employees_relationships'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-tax_conditions']) { ?>
                                        <li id="hr_tax_conditions">
                                            <a class="submenu" href="<?= admin_url('hr/tax_conditions'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('tax_conditions'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-leave_categories']) { ?>
                                        <li id="hr_leave_categories">
                                            <a class="submenu" href="<?= admin_url('hr/leave_categories'); ?>">
                                                <i class="fa fa-users"></i><span class="text"> <?= lang('leave_categories'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-leave_types']) { ?>
                                        <li id="hr_leave_types">
                                            <a class="submenu" href="<?= admin_url('hr/leave_types'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('leave_types'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-id_cards']){ ?>
                                        <li id="hr_id_cards">
                                            <a class="submenu" href="<?= admin_url('hr/id_cards'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('id_cards'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-sample_id_cards']) { ?>
                                        <li id="hr_sample_id_cards">
                                            <a class="submenu" href="<?= admin_url('hr/sample_id_cards'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sample'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-salary_reviews']){ ?>
                                        <li id="hr_salary_reviews" class="hide">
                                            <a class="submenu" href="<?= admin_url('hr/salary_reviews'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salary_reviews'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } if($Owner || $Admin || $GP['hr-awards']){ ?>
                                        <li id="hr_awards" class="hide">
                                            <a class="submenu" href="<?= admin_url('hr/awards'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('awards'); ?></span>
                                            </a>
                                        </li>  
                                    <?php } if($Owner || $Admin || $GP['hr-resignation']){ ?>
                                        <li id="hr_resignation">
                                            <a class="submenu" href="<?= admin_url('hr/resignation'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('resignation'); ?></span>
                                            </a>
                                        </li>  
                                    <?php } if($Owner || $Admin || $GP['hr-warning']){ ?>
                                        <li id="hr_warning">
                                            <a class="submenu" href="<?= admin_url('hr/warning'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('warning'); ?></span>
                                            </a>
                                        </li>  
                                    <?php } if($Owner || $Admin || $GP['hr-organization_chart']){ ?>
                                        <li id="hr_organization_chart">
                                            <a class="submenu" href="<?= admin_url('hr/organization_chart'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('organization_chart'); ?></span>
                                            </a>
                                        </li> 
                                    <?php } if($Owner || $Admin || $GP['hr-index']){ ?>
                                        <li id="hr_expired_document">
                                            <a class="submenu" href="<?= admin_url('hr/expired_document'); ?>">
                                                <i class="fa fa-exclamation-triangle"></i><span class="text"> <?= lang('expired_document'); ?></span>
                                            </a>
                                        </li> 
                                        <li id="hr_alert_birthday">
                                            <a class="submenu" href="<?= admin_url('hr/alert_birthday'); ?>">
                                                <i class="fa fa-exclamation-triangle"></i><span class="text"> <?= lang('alert_birthday'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-transfers']){ ?>
                                        <li id="hr_transfers">
                                            <a class="submenu" href="<?= admin_url('hr/transfers'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('transfers'); ?></span>
                                            </a>
                                        </li> 
                                    <?php } if($Owner || $Admin || $GP['hr-promotions']){ ?>
                                        <li id="hr_promotions">
                                            <a class="submenu" href="<?= admin_url('hr/promotions'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('promotions'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-travel']){ ?>
                                        <li id="hr_travels">
                                            <a class="submenu" href="<?= admin_url('hr/travels'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('travels'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['hr-complaints']){ ?>
                                        <li class="hide">
                                            <a class="submenu" href="<?= admin_url('hr/complaints'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('complaints'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
              
                         
                                    </ul>
                                </li>
                     
                        <?php 
                        }
                        if(
                            (
                            ($this->Settings->ui == 'full')? ($Settings->attendance && $session_module =='attendance') : $Settings->attendance
                            )
                             && ($Owner || $Admin || $GP['attendances-check_in_outs'] || $GP['attendances-add_check_in_out'] || $GP['attendances-edit_check_in_out'] || $GP['attendances-delete_check_in_out'] || $GP['attendances-generate_attendances'] || $GP['attendances-take_leaves'] || $GP['attendances-approve_attendances'] || $GP['attendances-cancel_attendances'] || $GP['attendances-approve_ot']|| $GP['attendances-policies'] || $GP['attendances-ot_policies'] || $GP['attendances-list_devices'] || $GP['attendances-check_in_out_report'] || $GP['attendances-daily_attendance_report'] || $GP['attendances-montly_attendance_report'] || $GP['attendances-attendance_department_report'] || $GP['attendances-employee_leave_report'])){ ?>
                            <li class="mm_attendances">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-clock"></i>
                                    <span class="text"> <?= lang('attendance'); ?> </span> 
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                <?php if($Owner || $Admin || $GP['attendances-generate_attendances']){ ?>
                                    <li id="attendances_index">
                                        <a class="submenu" href="<?= admin_url('attendances'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('generate_attendances'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-check_in_outs']){ ?>
                                    <li id="attendances_check_in_outs">
                                        <a class="submenu" href="<?= admin_url('attendances/check_in_outs'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('check_in_outs'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-take_leaves']){ ?>
                                
                                    <li id="attendances_take_leaves">
                                        <a class="submenu" href="<?= admin_url('attendances/take_leaves'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('take_leaves'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-day_off']){ ?>
                                    <li id="attendances_day_off">
                                        <a class="submenu" href="<?= admin_url('attendances/day_off'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('day_off'); ?></span>
                                        </a>
                                    </li>
                                <?php }if($Owner || $Admin || $GP['attendances-approve_attendances']){ ?>
                                
                                    <li id="attendances_approve_attendances">
                                        <a class="submenu" href="<?= admin_url('attendances/approve_attendances'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('approve_attendances'); ?></span>
                                        </a>
                                    </li>

                                <?php } if($Owner || $Admin || $GP['attendances-cancel_attendances']){ ?>
                                    <li id="attendances_cancel_attendances">
                                        <a class="submenu" href="<?= admin_url('attendances/cancel_attendances'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('cancel_attendances'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-ot']){ ?>
                                    <li id="attendances_approve_ot">
                                        <a class="submenu" href="<?= admin_url('attendances/ot'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('ot'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-approve_ot']){ ?>
                                    <li id="attendances_approve_ot">
                                        <a class="submenu" href="<?= admin_url('attendances/approve_ot'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('approve_ot'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-policies']){ ?>
                                    <li id="attendances_policies">
                                        <a class="submenu" href="<?= admin_url('attendances/policies'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('policies'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-ot_policies']){ ?>
                                    <li id="attendances_ot_policies">
                                        <a class="submenu" href="<?= admin_url('attendances/ot_policies'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('ot_policies'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-policies']){ ?>
                                    <li id="attendances_roster" class="">
                                        <a class="submenu" href="<?= admin_url('attendances/roster_code'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('roster_code'); ?></span>
                                        </a>
                                    </li>
                                    <li id="attendances_roster" class="">
                                        <a class="submenu" href="<?= admin_url('attendances/roster'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('roster'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-policies']){ ?>
                                    <li id="attendances_roster" class="">
                                        <a class="submenu" href="<?= admin_url('attendances/roster_calendar'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('roster_calendar'); ?></span>
                                        </a>
                                    </li>
                                <?php } if($Owner || $Admin || $GP['attendances-list_devices']){ ?>
                                    <li id="attendances_list_devices">
                                        <a class="submenu" href="<?= admin_url('attendances/'); ?>">
                                            <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('devices'); ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </li>
                        <?php 
                        }
                        if((($this->Settings->ui == 'full')? ($Settings->payroll && $session_module =='payroll') : $Settings->payroll)
                             && ($Owner || $Admin || $GP['payrolls-salary_banks_report'] || $GP['payrolls-payslips_report'] || $GP['payrolls-cash_advances_report'] || $GP['payrolls-cash_advances'] || $GP['payrolls-payment_details_report'] || $GP['payrolls-payments_report'] || $GP['payrolls-payments'] || $GP['payrolls-deductions'] || $GP['payrolls-additions'] || $GP['payrolls-salaries_report'] || $GP['payrolls-salary_details_report'] || $GP['payrolls-salaries'] ||$GP['payrolls-benefits'] || $GP['payrolls-benefits_report'] || $GP['payrolls-benefit_details_report'])){ ?>
                            <li class="mm_payrolls">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-envelope-open-dollar"></i>
                                    <span class="text"> <?= lang('payroll'); ?> </span> 
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if($Owner || $Admin || $GP['payrolls-cash_advances']) { ?>
                                    <li id="payrolls_cash_advances">
                                        <a class="submenu" href="<?= admin_url('payrolls/cash_advances'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('cash_advances'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['payrolls-benefits']) { ?>
                                    <li id="payrolls_benefits">
                                        <a class="submenu" href="<?= admin_url('payrolls/benefits'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('benefits'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['payrolls-salaries']) { ?>
                                    <li id="payrolls_pre_salaries">
                                        <a class="submenu" href="<?= admin_url('payrolls/pre_salaries'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('pre_salaries'); ?></span>
                                        </a>
                                    </li>
                                    <li id="payrolls_index">
                                        <a class="submenu" href="<?= admin_url('payrolls'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('salaries'); ?></span>
                                        </a>
                                    </li>
                                    <li id="payrolls_salaries_13">
                                        <a class="submenu" href="<?= admin_url('payrolls/salaries_13'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('salaries_13'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($this->Settings->module_school && ($Owner || $Admin || $GP['payrolls-salaries'])) { ?>
                                    <li id="payrolls_salaries_teacher">
                                        <a class="submenu" href="<?= admin_url('payrolls/salaries_teacher'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('salaries_teacher'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['payrolls-payments']) { ?>
                                    <li id="payrolls_payments">
                                        <a class="submenu" href="<?= admin_url('payrolls/payments'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('payments'); ?></span>
                                        </a>
                                    </li>   
                                    <?php } if($Owner || $Admin || $GP['payrolls-additions']) { ?>
                                    <li id="payrolls_additions">
                                        <a class="submenu" href="<?= admin_url('payrolls/additions'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('additions'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['payrolls-deductions']) { ?>
                                    <li id="payrolls_deductions">
                                        <a class="submenu" href="<?= admin_url('payrolls/deductions'); ?>">
                                            <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('deductions'); ?></span>
                                        </a>
                                    </li>   
                                    <?php } if(($Owner || $Admin || $GP['payrolls-severances'])) { ?>
                                        <li id="payrolls_severances" class="hide">
                                            <a class="submenu" href="<?= admin_url('payrolls/severances'); ?>">
                                                <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('severances'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if($Owner || $Admin || $GP['payrolls-indemnity']) { ?>
                                        <li id="payrolls_al_compensates" class="hide">
                                            <a class="submenu" href="<?= admin_url('payrolls/indemnity'); ?>">
                                                <i class="fa-regular fa-envelope-open-dollar"></i><span class="text"> <?= lang('indemnity'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } ?>

                                </ul>
                            </li>
                            <li class="mm_payroll hide">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-calculator"></i>
                                    <span class="text"> <?= lang('payroll'); ?></span>
                                    <span class="chevron closed"></span> 
                                </a>
                                <ul>
                                    <li id="payroll_index">
                                        <a class="submenu" href="<?= admin_url('payroll'); ?>">
                                            <i class="fa-regular fa fa-check-circle-o"></i><span class="text"> <?= lang('generate_payslip'); ?></span>
                                        </a>
                                    </li>
                                    <!-- <li id="payroll_index">
                                        <a class="submenu" href="<?= admin_url('payroll/salary_list'); ?>">
                                            <i class="fa-regular fa fa-check-circle-o"></i><span class="text"> <?= lang('salary_list'); ?></span>
                                        </a>
                                    </li> -->
                                    <li id="payroll_index">
                                        <a class="submenu" href="<?= admin_url('reports/salary_report'); ?>">
                                            <i class="fa-regular fa fa-check-circle-o"></i><span class="text"> <?= lang('report_salary'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php 
                        }
                        if ((($this->Settings->ui == 'full')? ($Settings->module_repair && $session_module =='repair') : $Settings->module_repair)
                            && ($Owner || $Admin || $GP['repairs-index'] || $GP['repairs-checks'] || $GP['repairs-problems'] || $GP['repairs-items'])) { ?>
                            <li class="mm_repairs">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-heart"></i>
                                    <span class="text"> <?= lang('repairs'); ?> 
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if (($Owner || $Admin || $GP['repairs-index']) && $Settings->module_repair) { ?>
                                        <li id="repairs_index">
                                            <a class="submenu" href="<?= admin_url('repairs'); ?>">
                                                <i class="fa-regular fa fa-magnet"></i><span class="text"> <?= lang('repairs'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php if (($Owner || $Admin || $GP['repairs-items']) && $Settings->module_repair) { ?>
                                        <li id="repairs_items">
                                            <a class="submenu" href="<?= admin_url('repairs/items'); ?>">
                                                <i class="fa-regular fa fa-retweet"></i><span class="text"> <?= lang('repair_items'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php if (($Owner || $Admin || $GP['repairs-checks']) && $Settings->module_repair) { ?>
                                        <li id="repairs_checks">
                                            <a class="submenu" href="<?= admin_url('repairs/checks'); ?>">
                                                <i class="fa-regular fa fa-bolt"></i>
                                                <span class="text"> <?= lang('checks'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php if (($Owner || $Admin || $GP['repairs-problems']) && $Settings->module_repair) { ?>
                                        <li id="repairs_problems">
                                            <a class="submenu" href="<?= admin_url('repairs/problems'); ?>">
                                                <i class="fa-regular fa fa-warning"></i>
                                                <span class="text"> <?= lang('problems'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php if (($Owner || $Admin || $GP['repairs-diagnostics']) && $Settings->module_repair) { ?>
                                        <li id="repairs_diagnostics">
                                            <a class="submenu" href="<?= admin_url('repairs/diagnostics'); ?>">
                                                <i class="fa-regular fa fa-h-square"></i>
                                                <span class="text"> <?= lang('diagnostics'); ?></span>
                                            </a>
                                        </li>
                                    <?php }if (($Owner || $Admin || $GP['repairs-machine_types']) && $Settings->module_repair) { ?>
                                        <li id="repairs_machine_types">
                                            <a class="submenu" href="<?= admin_url('repairs/machine_types'); ?>">
                                                <i class="fa-regular fa fa-th-list"></i>
                                                <span class="text"> <?= lang('machine_types'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if (($Owner || $Admin || $GP['reports-repairs']) && $Settings->module_repair) { ?>    
                                        <li id="reports_repairs">
                                            <a href="<?= admin_url('reports/repairs') ?>">
                                                <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('repairs_report'); ?></span>
                                            </a>
                                        </li>
                                        <li id="reports_repair_items">
                                            <a href="<?= admin_url('reports/repair_items') ?>">
                                                <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('repair_items_report'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>

                        <?php } if((($this->Settings->ui == 'full')? (($session_module =='expense') && $Settings->module_expense) : $Settings->module_expense)
                            && ($Owner||$Admin || $GP['expenses-index']||$GP['expenses-add']||$GP['purchases-expenses'] || $GP['purchases-expenses_budget'])) {
                        ?>
                            <li class="mm_expenses">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-dollar-sign"></i>
                                    <span class="text"> <?= lang('expenses'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php 
                                    if($Owner || $Admin || $GP['expenses-categories']) {?>
                                    <li id="expenses_expense_categories">
                                        <a href="<?= admin_url('expenses/expense_categories') ?>">
                                            <i class="fa-regular fa fa-folder-open"></i><span class="text"> <?= lang('expense_categories'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner||$Admin||$GP['expenses-index']) { ?>
                                    <li id="expenses_index">
                                        <a class="submenu" href="<?= admin_url('expenses'); ?>">
                                            <i class="fa-regular fa fa-dollar"></i><span class="text"> <?= lang('list_expenses'); ?></span>
                                        </a>
                                    </li>
                                    <li id="expenses-add">
                                        <a class="submenu" href="<?= admin_url('expenses/add'); ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_expense'); ?></span>
                                        </a>
                                    </li>
                                    <!-- <li id="expenses_expense_by_csv">
                                        <a class="submenu" href="<?= admin_url('purchases/expense_by_csv'); ?>">
                                            <i class="fa-regular fa fa-file-text"></i>
                                            <span class="text"> <?= lang('import_expense'); ?></span>
                                        </a>
                                    </li> -->
                                    <?php } if($Settings->expense_budget){ ?>
                                    <li id="purchases_expenses_budget">
                                        <a class="submenu" href="<?= admin_url('expenses/expenses_budget'); ?>">
                                            <i class="fa-regular fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_expenses_budget'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_expense_budget">
                                        <a class="submenu" href="<?= admin_url('expenses/add_expense_budget'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_expense_budget'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_budgets">
                                        <a class="submenu" href="<?= admin_url('expenses/budgets'); ?>">
                                            <i class="fa-regular fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_budgets'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_add_budget">
                                        <a class="submenu" href="<?= admin_url('expenses/add_budget'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_budget'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>  
                                </ul>
                            </li>
                        <?php 
                        } 
                        if ((($this->Settings->ui == 'full')? 
                            (((POS && ($this->pos_settings->pos_type =="table" || $this->pos_settings->pos_type =="room"))) && ((POS && ($this->pos_settings->pos_type =="table" || $this->pos_settings->pos_type =="room"))) ) : 

                            ((POS && ($this->pos_settings->pos_type =="table" || $this->pos_settings->pos_type =="room"))))


                            && ($Owner || $Admin || $GP['tables-index'])) { 

                                ?>
                            <li class="mm_table">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-heart"></i>
                                    <span class="text"> <?= lang('rooms'); ?>
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="table_suspend_note">
                                        <a class="submenu" href="<?= admin_url('table/suspend_note'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('Table|Rooms'); ?></span>
                                        </a>
                                    </li>
                                    <?php if($Settings->module_clinic){?>
                                    <li id="table_assign" class="hide">
                                        <a class="submenu" href="<?= admin_url('table/assign'); ?>">
                                            <i class="fa-regular fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('assign'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>
                                </ul>
                            </li>
                        <?php
                        }?>
                            <li class="mm_notifications">
                                <a class="submenu" href="<?= admin_url('notifications'); ?>">
                                    <i class="fa-regular fa fa-info-circle"></i><span class="text"> <?= lang('notifications'); ?></span>
                                </a>
                            </li>
                        <?php 
                        /*if ((($this->Settings->ui == 'full')? (($session_module =='sales'||$session_module =='crm'||$session_module =='school'||$session_module =='clinic') && 
                            ($Settings->module_sale||$Settings->module_hr || $Settings->module_crm || $Settings->module_school || $Settings->module_clinic)) : ($Settings->module_sale || $Settings->module_crm || $Settings->module_school || $Settings->module_clinic))
                            && ($Owner || $Admin ||$GP['leads-index']||$GP['leads-groups'] || $GP['leads-pipeline'])) { */?> 
                            <li class="mm_calendar">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-calendar"></i>
                                    <span class="text"><?= lang('calendar'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li class="calendar_index">
                                        <a class="submenu" href="<?= admin_url('calendar'); ?>">
                                            <i class="fa-regular fa fa-calendar"></i><span class="text"> <?= lang('calendar'); ?></span>
                                        </a>
                                    </li>
                                    <li id="calendar_appointment">
                                        <a class="submenu" href="<?= admin_url('calendar/appointment'); ?>">
                                            <i class="fa-regular fa-calendar-circle-user"></i><span class="text"> <?= lang('appointment'); ?></span>
                                        </a>
                                    </li>
                                    <li id="calendar_add">
                                        <a class="submenu" href="<?= admin_url('calendar/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_appointment'); ?></span>
                                        </a>
                                    </li>
                                    <li id="calendar_upcoming_appointments">
                                        <a class="submenu" href="<?= admin_url('calendar/upcoming_appointments'); ?>">
                                            <i class="fa fa-exclamation-triangle"></i><span class="text"> <?= lang('Upcoming_Appointments '); ?></span>
                                        </a>
                                    </li>
                                    <?php if($Settings->module_hr){?>
                                    <li id="mm_calendar_holidays">
                                        <a class="submenu" href="<?= admin_url('calendar/holidays'); ?>">
                                            <i class="fa-regular fa-calendar-xmark"></i><span class="text"> <?= lang('holidays'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>
                                </ul>
                            </li>
                            
                        <?php 
                        //}
                        if ((($this->Settings->ui == 'full')? ($Settings->module_e_ticket) : $Settings->module_e_ticket)
                            && ($Owner || $Admin)) { ?>
                            <li class="mm_e_ticket">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-heart"></i>
                                    <span class="text"> <?= lang('e_tickets'); ?> 
                                    </span> <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin || $GP['repairs-index']) { ?>
                                        <li id="e_ticket_index">
                                            <a class="submenu" href="<?= admin_url('e_ticket'); ?>">
                                                <i class="fa-regular fa fa-magnet"></i><span class="text"> <?= lang('registration'); ?></span>
                                            </a>
                                        </li>
                                        <li id="e_ticket_customers">
                                            <a class="submenu" href="<?= admin_url('e_ticket/events'); ?>">
                                                <i class="fa-regular fa fa-magnet"></i><span class="text"> <?= lang('events'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>

                        <?php } 
                        ?>
                        <?php if ($Owner || $Admin || $GP['customers-index']||$GP['customers-add'] || $GP['auth-saleman'] || $GP['suppliers-index']||$GP['suppliers-add'] || $GP['users-index']||$GP['users-add']||$GP['drivers-index']||$GP['drivers-add']) { ?>
                            <li class="mm_auth mm_suppliers">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-users"></i>
                                <span class="text"> <?= lang('people'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin || $GP['users-index']) { ?>
                                    <li id="auth_users">
                                        <a class="submenu" href="<?= admin_url('users'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_users'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if (($Settings->module_purchase || $Settings->project) && ($Owner||$Admin||$GP['suppliers-index'])) { ?>
                                    <li id="suppliers_index">
                                        <a class="submenu" href="<?= admin_url('suppliers'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_clinic && ($Owner || $Admin || $GP['clinic-doctors'])) { ?>
                                        <li id="auth_doctors">
                                            <a class="submenu" href="<?= admin_url('clinic/doctors'); ?>">
                                                <i class="fa-regular fa-user-doctor"></i><span class="text"> <?= lang('doctors'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } if ($Settings->sale_man && ($Owner || $Admin || $GP['auth-saleman'])) { ?>
                                        <li id="auth_salemans">
                                            <a class="submenu" href="<?= admin_url('auth/salemans'); ?>">
                                                <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_salemans'); ?></span>
                                            </a>
                                        </li>   
                                    <?php } if ($Owner||$Admin||$GP['customers-index']) { ?>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('customers'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner||$Admin||$GP['customers-add']) { ?>
                                    <li id="customers_index">
                                        <a class="submenu" href="<?= admin_url('customers/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($this->Settings->driver && ($Owner||$Admin||$GP['drivers-index'])) { ?>
                                    <li id="drivers_index">
                                        <a class="submenu" href="<?= admin_url('drivers'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('list_drivers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($this->Settings->module_school && ($Owner || $Admin || $GP['schools-teachers'])) { ?>    
                                    <li id="schools_teachers">
                                        <a class="submenu" href="<?= admin_url('schools/teachers'); ?>">
                                            <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('teachers'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } if ($Owner) { ?>
                            <li class="mm_billers">
                                <a class="dropmenu" href="#">
                                <i class="fa-regular fa fa-industry tip"></i>
                                <span class="text"> <?= lang('companies'); ?> </span>
                                <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="billers_index">
                                        <a class="submenu" href="<?= admin_url('billers'); ?>">
                                            <i class="fa-regular fa fa-industry"></i><span class="text"> <?= lang('list_billers'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } if ($Owner || $Admin || $GP['system_settings-telegrams'] || $GP['system_settings-cash_account'] || $GP['projects-index'] || $GP['system_settings'] || $GP['pos_settings'] || $GP['change_logo'] || $GP['billers-index'] || $GP['warehouses-index'] || $GP['areas-index'] || $GP['expenses-categories'] || $GP['categories-index'] || $GP['tables-index'] || $GP['units-index'] || $GP['brands-index'] || $GP['variants-index'] || $GP['system_settings-boms'] || $GP['customer_groups-index'] || $GP['price_groups-index']|| $GP['payment_terms-index']|| $GP['currencies-index']|| $GP['customer_opening_balances-index']|| $GP['supplier_opening_balances-index']|| $GP['tax_rates-index']|| $GP['list_printers-index'] || $GP['email_templates-index'] || $GP['group_permissions-index'] || $GP['backups-index'] || $GP['system_settings-rooms'] || $GP['system_settings-rewards']) { ?>
                            <li class="mm_system_settings <?= strtolower($this->router->fetch_method()) == 'sales' ? '' : 'mm_pos' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-cog"></i><span class="text"> <?= lang('settings'); ?> </span><span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <?php if ($Owner || $Admin || $GP['system_settings']){ ?>
                                    <li id="system_settings_index">
                                        <a href="<?= admin_url('system_settings') ?>">
                                            <i class="fa-regular fa fa-cogs"></i><span class="text"> <?= lang('system_settings'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if(($Owner || $Admin || $GP['pos_settings']) && POS){ ?>
                                    <li id="pos_settings">
                                        <a href="<?= admin_url('pos/settings') ?>">
                                            <i class="fa-regular fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
                                        </a>
                                    </li>
                                    <li id="promos_index hide" style="display: none;">
                                        <a href="<?= admin_url('promos') ?>">
                                            <i class="fa-regular fa fa-cogs"></i><span class="text"> <?= lang('promos'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if (($Owner || $Admin||$GP['list_printers-index']) && ($this->pos_settings->pos_type !="pos")){ ?>
                                    <li id="pos_printers">
                                        <a href="<?= admin_url('pos/printers') ?>">
                                            <i class="fa-regular fa fa-print"></i><span class="text"> <?= lang('list_printers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="pos_add_printer">
                                        <a href="<?= admin_url('pos/add_printer') ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_printer'); ?></span>
                                        </a>
                                    </li>

                                    <?php } if ($Settings->module_inventory && ($Owner || $Admin || $GP['categories-index'])){?>
                                    <li id="system_settings_categories">
                                        <a href="<?= admin_url('system_settings/categories') ?>">
                                            <i class="fa-regular fa fa-folder-open"></i><span class="text"> <?= lang('categories'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_inventory && ($Owner || $Admin || $GP['brands-index'])){ ?>
                                    <li id="system_settings_brands">
                                        <a href="<?= admin_url('system_settings/brands') ?>">
                                            <i class="fa-regular fa fa-th-list"></i><span class="text"> <?= lang('brands'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_inventory && ($Owner || $Admin || $GP['variants-index'])){ ?>
                                    <li id="system_settings_variants">
                                        <a href="<?= admin_url('system_settings/variants') ?>">
                                            <i class="fa-regular fa fa-tags"></i><span class="text"> <?= lang('variants'); ?></span>
                                        </a>
                                    </li>
                                    <li id="psystem_settings_options">
                                        <a href="<?= admin_url('system_settings/options') ?>">
                                            <i class="fa-regular fa fa-tags"></i><span class="text"> <?= lang('options'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_inventory && ($Owner || $Admin || $GP['units-index'])){ ?>
                                    <li id="system_settings_units">
                                        <a href="<?= admin_url('system_settings/units') ?>">
                                            <i class="fa-regular fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->reward_exchange && ($Owner || $Admin || $GP['system_settings-rewards'])) { ?>
                                    <li id="system_settings_rewards">
                                        <a href="<?= admin_url('system_settings/rewards') ?>">
                                            <i class="fa-regular fa fa-wrench"></i><span class="text"> <?= lang('rewards'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Settings->module_inventory && ($Owner || $Admin)) { ?>
                                    <!-- <li id="system_settings_products_alert">
                                        <a href="<?= admin_url('system_settings/products_alert') ?>">
                                            <i class="fa-regular fa fa-exclamation-circle"></i><span class="text"> <?= lang('products_alert'); ?></span>
                                        </a>
                                    </li> -->
                                    <?php } if ($Owner || $Admin || $GP['change_logo']){ ?>
                                    <li id="system_settings_change_logo">
                                        <a href="<?= admin_url('system_settings/change_logo') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                            <i class="fa-regular fa fa-upload"></i><span class="text"> <?= lang('change_logo'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if((($this->pos_settings->pos_type =="pos") && POS) && ($Owner || $Admin)) {?>
                                     <li id="system_settings_upload_slide">
                                        <a href="<?= admin_url('system_settings/upload_slide') ?>">
                                            <i class="fa-regular fa fa-upload"></i><span class="text"> <?= lang('upload_slide'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin || $GP['currencies-index']){ ?>
                                    <li id="system_settings_currencies">
                                        <a href="<?= admin_url('system_settings/currencies') ?>">
                                            <i class="fa-regular fa-hand-holding-dollar"></i><span class="text"> <?= lang('currencies'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Owner || $Admin || $GP['system_settings-cash_account']) { ?>
                                    <li id="system_settings_cash_accounts">
                                        <a href="<?= admin_url('system_settings/cash_accounts') ?>">
                                            <i class="fa-sharp fa-regular fa-box-dollar"></i><span class="text"> <?= lang('cash_accounts'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if(POS && ($Owner || $Admin)){ ?>
                                    <?php } if (($Settings->module_sale ||POS) && ($Owner || $Admin || $GP['customer_groups-index'])){ ?>
                                    <li id="system_settings_customer_groups">
                                        <a href="<?= admin_url('system_settings/customer_groups') ?>">
                                            <i class="fa-regular fa fa-chain"></i><span class="text"> <?= lang('customer_groups'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if (($Settings->module_sale||POS) && ($Owner || $Admin || $GP['price_groups-index'])){?>
                                    <li id="system_settings_price_groups">
                                        <a href="<?= admin_url('system_settings/price_groups') ?>">
                                            <i class="fa-regular fa fa-dollar"></i><span class="text"> <?= lang('price_groups'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if(($Settings->module_sale||POS) && ($Owner || $Admin)) {?>
                                    <li id="promos_index">
                                        <a href="<?= admin_url('system_settings/promotion') ?>">
                                            <i class="fa-regular fa fa-chain"></i><span class="text"> <?= lang('promotion'); ?></span>
                                        </a>
                                    </li>
                                    <?php if($Settings->product_commission){?>
                                    <li id="system_settings_commission_product">
                                        <a href="<?= admin_url('system_settings/commission_product') ?>">
                                            <i class="fa-regular fa fa-dollar"></i><span class="text"> <?= lang('commission_product'); ?></span>
                                        </a>
                                    </li>
                                    <li id="system_settings_sales_rank">
                                        <a href="<?= admin_url('system_settings/sales_rank') ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('sales_rank_commission'); ?></span>
                                        </a>
                                    </li>
                                    <li id="system_settings_sale_targets">
                                        <a href="<?= admin_url('system_settings/sale_targets') ?>">
                                            <i class="fa-regular fa fa-bullseye"></i><span class="text"> <?= lang('sale_targets'); ?></span>
                                        </a>
                                    </li>
                                    <?php }?>
                                    <li id="system_settings_multi_buy_groups">
                                        <a href="<?= admin_url('system_settings/multi_buy_groups') ?>">
                                            <i class="fa-regular fa fa-dollar"></i><span class="text"> <?= lang('multi_buys'); ?></span>
                                        </a>
                                    </li>
                                    
                                    <?php } if($Settings->module_inventory && ($this->pos_settings->pos_type !="pos") && ($Owner || $Admin) ) {?>
                                    <li id="system_settings_stocktypes">
                                        <a href="<?= admin_url('system_settings/stocktypes') ?>">
                                            <i class="fa-regular fa fa-tags"></i><span class="text"> <?= lang('stock_types'); ?></span>
                                        </a>
                                     </li>
                                    <?php } if(($Settings->zone) && $Owner || $Admin || $GP['areas-index']) {?>
                                    <li id="system_settings_zones">
                                        <a href="<?= admin_url('system_settings/zones') ?>">
                                            <i class="fa-regular fa fa-wrench"></i><span class="text"> <?= lang('zones'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if((($this->pos_settings->pos_type !="pos") && POS) && $Owner || $Admin || $GP['tables-index']) {?>
                                    <li id="system_settings_floors">
                                        <a href="<?= admin_url('system_settings/floors') ?>">
                                            <i class="fa-regular fa fa-th"></i><span class="text"> <?= lang('floors'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($this->Settings->module_loan == 1 && $Owner || $Admin || $GP['system_settings-frequencies']){ ?>
                                    <li id="system_settings_frequencies">
                                        <a href="<?= admin_url('system_settings/frequencies') ?>">
                                            <i class="fa-regular fa fa-thumbs-up"></i><span class="text"> <?= lang('frequencies'); ?></span>
                                        </a>
                                    </li>
                                     <li id="system_settings_interest_period">
                                        <a href="<?= admin_url('system_settings/interest_period') ?>">
                                            <i class="fa-regular fa fa-times"></i><span class="text"> <?= lang('interest_period'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if(($Settings->module_sale||$Settings->module_purchase) && ($Owner || $Admin || $GP['payment_terms-index'])){?>
                                    <li id="system_settings_payment_term">
                                        <a href="<?= admin_url('system_settings/payment_term') ?>">
                                            <i class="fa-regular fa-comments-dollar"></i><span class="text"> <?= lang('payment_term'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if (($Settings->module_sale||$Settings->module_purchase) && ($Owner || $Admin || $GP['tax_rates-index'])){?>
                                    <li id="system_settings_tax_rates">
                                        <a href="<?= admin_url('system_settings/tax_rates') ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if($Settings->module_inventory && ($Owner || $Admin || $GP['warehouses-index'])){?>
                                    <li id="system_settings_warehouses">
                                        <a href="<?= admin_url('system_settings/warehouses') ?>">
                                            <i class="fa-regular fa fa-building-o"></i><span class="text"> <?= lang('warehouses'); ?></span>
                                        </a>
                                    </li>
                                    <li id="system_settings_rack">
                                        <a href="<?= admin_url('system_settings/racks') ?>">
                                            <i class="fa-sharp fa-regular fa-table-cells"></i><span class="text"> <?= lang('rack'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin || $GP['group_permissions-index']){ ?>
                                    <li id="system_settings_user_groups">
                                        <a href="<?= admin_url('system_settings/user_groups') ?>">
                                            <i class="fa-regular fa fa-key"></i><span class="text"> <?= lang('group_permissions'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin || $GP['reports-audit_trails']) { ?>
                                    <li id="system_settings_audit_trail">
                                        <a href="<?= admin_url('system_settings/audit_trail') ?>">
                                            <i class="fa-regular fa fa-pencil"></i><span class="text"> <?= lang('audit_trail'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin || $GP['email_templates-index']){?>
                                    <li id="system_settings_email_templates">
                                        <a href="<?= admin_url('system_settings/email_templates') ?>">
                                            <i class="fa-regular fa fa-envelope"></i><span class="text"> <?= lang('email_templates'); ?></span>
                                        </a>
                                    </li>
                                    <?php } if ($Owner || $Admin || $GP['backups-index']){ ?>
                                    <li id="system_settings_backups">
                                        <a href="<?= admin_url('system_settings/backups') ?>">
                                            <i class="fa-regular fa fa-database"></i><span class="text"> <?= lang('backups'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="system_settings_custom_field">
                                        <a href="<?= admin_url('system_settings/custom_field') ?>">
                                            <i class="fa-regular fa fa-key"></i><span class="text"> <?= lang('constants'); ?></span>
                                        </a>
                                    </li>
                                    
                                    <li id="system_settings_change_logo">
                                        <a href="<?= admin_url('system_settings/language') ?>">
                                            <i class="fa-regular fa fa-upload"></i><span class="text"> <?= lang('change_language'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($Settings->enable_telegram && ($Owner || $Admin || $GP['system_settings-telegrams'])){ ?>     
                                        <li id="system_settings_telegrams">
                                            <a href="<?= admin_url('system_settings/telegrams') ?>">
                                                <i class="fa-regular fa fa-key"></i><span class="text"> <?= lang('telegrams'); ?></span>
                                            </a>
                                        </li>
                                    <?php } if(DEMO){ ?>
                                    <li id="reset">
                                        <a href="<?= admin_url('reset/demo') ?>">
                                            <i class="fa-regular fa fa-eraser"></i><span class="text"> <?= lang('reset_data'); ?></span>
                                        </a>
                                    <?php } if (($Owner || $Admin || $GP['system_settings-tanks']) && $this->Settings->module_fuel){ ?>      
                                        <li id="system_settings_tanks">
                                            <a href="<?= admin_url('system_settings/tanks') ?>">
                                                <i class="fa fa-cube"></i><span class="text"> <?= lang('fuel_tanks'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_fuel_times">
                                            <a href="<?= admin_url('system_settings/fuel_times') ?>">
                                                <i class="fa-regular fa fa-clock"></i><span class="text"> <?= lang('fuel_times'); ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    </li>
                                    <!-- <li id="system_settings_updates">
                                        <a href="<?= admin_url('system_settings/updates') ?>">
                                            <i class="fa-regular fa fa-upload"></i><span class="text"> <?= lang('updates'); ?></span>
                                        </a>
                                    </li> -->
                                    <?php if ($Settings->apis) { ?>
                                    <li id="api_settings_index">
                                        <a href="<?= admin_url('api_settings') ?>">
                                            <i class="fa-regular fa fa-key"></i><span class="text"> <?= lang('api_keys'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="system_settings_updates">
                                        <a href="<?= admin_url('chipmong') ?>">
                                            <i class="fa-regular fa fa-upload"></i><span class="text"> <?= lang('Chipmong'); ?></span>
                                        </a>
                                    </li>
                                    <li id="system_settings_cronjob">
                                        <a href="<?= admin_url('system_settings/cronjob') ?>">
                                            <i class="fa-regular fa fa-cogs"></i><span class="text"> <?= lang('cron_jobs'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } if ($Owner || $Admin || $GP['reports-index']) { ?>
                            <li class="mm_reports">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa-chart-line"></i>
                                    <span class="text"> <?= lang('reports'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul class="sub-menu">
                                    <?php if ($Settings->module_inventory && ($Owner || $Admin || $GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts'] || $GP['reports-products'] || $GP['reports-stock_in_out'] || $GP['reports-stock_received'] || $GP['reports-reward_exchange'])) { ?>
                                    <li class="sub_mm_reports_inventory">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('inventory'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($Owner||$Admin||$GP['reports-products']) { ?>
                                            <li id="reports_index">
                                                <a href="<?= admin_url('reports') ?>">
                                                    <i class="fa-regular fa fa-bars"></i><span class="text"> <?= lang('overview_chart'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_warehouse_stock">
                                                <a href="<?= admin_url('reports/warehouse_stock') ?>">
                                                    <i class="fa-regular fa fa-cubes"></i><span class="text"> <?= lang('stock_value'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_category_stock">
                                                <a href="<?= admin_url('reports/category_stock') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('category_stock_chart'); ?></span>
                                                </a>
                                            </li>
                                            <?php }?>
                                            <?php if ($Owner||$Admin||$GP['reports-quantity_alerts']) { ?>
                                            <li id="reports_quantity_alerts">
                                                <a href="<?= admin_url('reports/quantity_alerts') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['reports-payments_alerts']) { ?>
                                            <!-- <li id="reports-payments_alerts">
                                                <a href="<?= admin_url('reports/payments_alerts') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('payments_alerts'); ?></span>
                                                </a>
                                            </li> -->
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['reports-expiry_alerts']) { ?>
                                                <?php if ($Settings->product_expiry) { ?>
                                                <li id="reports_expiry_alerts">
                                                    <a href="<?= admin_url('reports/expiry_alerts') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['reports-products']) { ?>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/products') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('products_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/stock_movement') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('stock_movement'); ?></span>
                                                    </a>
                                                </li>
                                                <?php if($Settings->product_serial && ($Owner || $Admin || $GP['reports-product_serial_report'])){ ?>
                                                    <li id="reports_product_serial_report">
                                                        <a href="<?= admin_url('reports/product_serial_report') ?>">
                                                            <i class="fa fa-barcode"></i><span class="text"> <?= lang('product_serial_report'); ?></span>
                                                        </a>
                                                    </li>
                                                    <li id="reports_serial_by_product_report">
                                                        <a href="<?= admin_url('reports/serial_by_product_report') ?>">
                                                            <i class="fa fa-barcode"></i><span class="text"> <?= lang('serial_by_product_report'); ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                <li id="reports_product_sales_report">
                                                    <a href="<?= admin_url('reports/product_sales_report') ?>">
                                                        <i class="fa fa-barcode"></i><span class="text"> <?= lang('product_sales_report'); ?></span>
                                                    </a>
                                                </li>  
                                                <li id="reports_product_purchases_report">
                                                    <a href="<?= admin_url('reports/product_purchases_report') ?>">
                                                        <i class="fa fa-barcode"></i><span class="text"> <?= lang('product_purchases_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_product_monthly_sale">
                                                    <a href="<?= admin_url('reports/product_monthly_sale') ?>">
                                                        <i class="fa fa-barcode"></i><span class="text"> <?= lang('product_monthly_sale'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_product_yearly_sale">
                                                    <a href="<?= admin_url('reports/product_yearly_sale') ?>">
                                                        <i class="fa fa-barcode"></i><span class="text"> <?= lang('product_yearly_sale'); ?></span>
                                                    </a>
                                                </li>
                                                <?php if ($Owner||$Admin) { ?>
                                                    <?php if ($Settings->product_expiry) { ?>
                                                    <li id="reports_products_expiry">
                                                        <a href="<?= admin_url('reports/products_expiry') ?>">
                                                            <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('products_expiry_report'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                <?php } ?>
                                                <!--  -->
                                                <!-- <li id="reports_costs">
                                                    <a href="<?= admin_url('reports/cost_report') ?>">
                                                        <i class="fa-regular fa fa-dollar"></i><span class="text"> <?= lang('cost_report'); ?></span>
                                                    </a>
                                                </li> -->
                                                <li id="reports_adjustments">
                                                    <a href="<?= admin_url('reports/adjustments') ?>">
                                                        <i class="fa-regular fa fa-filter"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/adjustment_details') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('adjustment_details'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_warehouse_products">
                                                    <a href="<?= admin_url('reports/warehouse_products') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('warehouse_products'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_customer_sale_top">
                                                    <a href="<?= admin_url('reports/report_sale_top') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('product_sale_top'); ?></span>
                                                    </a>
                                                </li>
                                                <?php if($Settings->multi_warehouse){?>
                                                <li id="reports_transfers_report">
                                                    <a href="<?= admin_url('reports/transfers_report') ?>">
                                                        <i class="fa-regular fa-right-left"></i><span class="text"> <?= lang('transfers_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_transfers_report">
                                                    <a href="<?= admin_url('reports/transfer_details'); ?>">
                                                        <i class="fa-regular fa-right-left"></i><span class="text"><?= lang('transfer_details_report'); ?></span>
                                                    </a>
                                                </li> 
                                                <?php } ?>

                                            <?php } ?>
                                            <?php if ($Settings->stock_received) { ?>
                                                <?php if ($Owner || $Admin || $GP['reports-stock_received']) { ?>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/stock_received') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('stock_received_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($Owner || $Admin || $GP['reports-stock_in_out']) { ?>
                                            <!-- <li id="reports_products">
                                                <a href="<?= admin_url('reports/stock_in_out') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('products_in_out_category'); ?></span>
                                                </a>
                                            </li> -->
                                            <?php } ?>
                                            <?php  if ($Owner || $Admin || $GP['reports-products']) { 
                                            if ($Settings->stock_using) { ?>
                                            <li id="reports_list_using_stock_report">
                                                <a href="<?= admin_url('reports/list_using_stock_report') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('list_using_stock_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_products_using_report">
                                                <a href="<?= admin_url('reports/products_using_report') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('products_using_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_using_stock_details">
                                                <a href="<?= admin_url('reports/using_stock_details') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('using_stock_details'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Settings->sale_consignment) { ?>
                                            <li id="reports_consignment_report">
                                                <a href="<?= admin_url('reports/consignment') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('consignment'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <!-- <li id="reports_price_groups">
                                                <a href="<?= admin_url('reports/price_groups') ?>">
                                                    <i class="fa-regular fa fa-folder-open"></i><span class="text"> <?= lang('price_groups_report'); ?></span>
                                                </a>
                                            </li> -->
                                            <li id="reports_categories">
                                                <a href="<?= admin_url('reports/categories') ?>">
                                                    <i class="fa-regular fa fa-folder-open"></i><span class="text"> <?= lang('categories_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_brands">
                                                <a href="<?= admin_url('reports/brands') ?>">
                                                    <i class="fa-regular fa fa-cubes"></i><span class="text"> <?= lang('brands_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <!-- $Settings->reward_exchange && $Settings->stock_received -->
                                            <?php if ($Settings->reward_exchange) { ?>
                                                <?php if ($Owner || $Admin || $GP['reports-stock_received']) { ?>
                                                <li id="reports_products">
                                                    <a href="<?= admin_url('reports/reward_stock_received') ?>">
                                                        <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('rewards_stock_received_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($Settings->reward_exchange && ($Owner || $Admin || $GP['reports-reward_exchange'])) { ?>
                                            <li id="reports_customer_rewards_exchange">
                                                <a href="<?= admin_url('reports/customer_rewards_exchange_report') ?>">
                                                    <i class="fa-regular fa fa-cubes"></i><span class="text"> <?= lang('customer_rewards_exchange_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_supplier_rewards_exchange">
                                                <a href="<?= admin_url('reports/supplier_rewards_exchange_report') ?>">
                                                    <i class="fa-regular fa fa-cubes"></i><span class="text"> <?= lang('supplier_rewards_exchange_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_purchase && ($Owner||$Admin||$GP['reports-daily_purchases'] || $GP['reports-monthly_purchases'] || $GP['reports-purchases'] || $GP['reports-expenses'] || $GP['reports-budgets'] || $GP['reports-expenses_budget'])) { ?>
                                    <li class="sub_mm_reports_purchases">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-star"></i>
                                            <span class="text"> <?= lang('purchases'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                             <?php if ($Owner||$Admin||$GP['reports-daily_purchases']) { ?>
                                            <li id="reports_daily_purchases">
                                                <a href="<?= admin_url('reports/daily_purchases') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['reports-monthly_purchases']) { ?>
                                            <li id="reports_monthly_purchases">
                                                <a href="<?= admin_url('reports/monthly_purchases') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['reports-purchases']) { ?>
                                            <li id="reports_purchases">
                                                <a href="<?= admin_url('reports/purchases') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['reports-expenses']) { ?>
                                            <li id="reports_expenses">
                                                <a href="<?= admin_url('reports/expenses') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('expenses_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_expenses">
                                                <a href="<?= admin_url('reports/expenses_monthly') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('expenses_monthly_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($Settings->multi_level) { ?>
                                                <?php if ($Owner||$Admin||$GP['reports-budgets']) { ?>
                                                <li id="reports_budgets">
                                                    <a href="<?= admin_url('reports/budgets') ?>">
                                                        <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('budgets_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                                <?php if ($Owner||$Admin||$GP['reports-expenses_budget']) { ?>
                                                <li id="reports_expenses_budget">
                                                    <a href="<?= admin_url('reports/expenses_budget') ?>">
                                                        <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('expenses_budget_report'); ?></span>
                                                    </a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php
                                    }
                                    if($Settings->module_clinic && ($Owner||$Admin)) { ?>
                                    <li class="sub_mm_reports_purchases">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa-hospital"></i>
                                            <span class="text"> <?= lang('clinic'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li class="mm_clinic-opd">
                                                <a class="submenu" href="<?= admin_url('clinic/opd'); ?>">
                                                    <i class="fa-regular fa-syringe"></i>
                                                    <span class="text"> <?= lang('appointment_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if(($Owner||$Admin||$GP['clinic-opd'])) { ?>
                                            <li class="mm_clinic-opd">
                                                <a class="submenu" href="<?= admin_url('clinic/opd'); ?>">
                                                    <i class="fa-regular fa-syringe"></i>
                                                    <span class="text"> <?= lang('opd_report'); ?></span>
                                                </a>
                                                
                                            </li>
                                            <?php 
                                            }
                                            if(($Owner||$Admin||$GP['clinic-ipd'])) { ?>
                                                <li id="clinic_ipd_treatment">
                                                    <a class="submenu" href="<?= admin_url('clinic/ipd'); ?>">
                                                        <i class="fa-regular fa-syringe"></i>
                                                        <span class="text"> <?= lang('ipd_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($Owner||$Admin||$GP['customers-index']) { ?>
                                            <li id="clinic_birth">
                                                <a class="submenu" href="<?= admin_url('clinic/birth'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('birth_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Owner||$Admin||$GP['customers-index']) { ?>
                                            <li id="clinic_death">
                                                <a class="submenu" href="<?= admin_url('clinic/death'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('death_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php
                                    }
                                    if(($Settings->project) && ($Owner||$Admin)) { 
                                    ?>
                                    <li class="sub_mm_reports_sales">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('projects_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_projects">
                                                <a href="<?= admin_url('reports/projects') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('projects_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_project_detail">
                                                <a href="<?= admin_url('reports/project_detail') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('project_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_project_budget">
                                                <a href="<?= admin_url('reports/project_budget') ?>">
                                                    <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('project_budget_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php 
                                    }
                                    if(($Settings->module_hotel_apartment||$Settings->module_sale|| POS ||$Settings->module_property||$Settings->module_clinic) && ($Owner||$Admin||$GP['reports-daily_sales'] || $GP['reports-monthly_sales'] || $GP['reports-sale_targets'] || $GP['reports-sales'])) { 
                                    ?>
                                    <li class="sub_mm_reports_sales">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('sales'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if (POS) { ?>
                                            <li id="reports_register">
                                                <a href="<?= admin_url('reports/register') ?>">
                                                    <i class="fa-regular fa fa-th-large"></i><span class="text"> <?= lang('register_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if($this->pos_settings->pos_type !="pos"){  ?>
                                            <li id="reports_bill_sales">
                                                <a href="<?= admin_url('reports/audit_bill') ?>">
                                                    <i class="fa-regular fa fa-calendar"></i><span class="text"> <?= lang('bill_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_suspend_report" <?=($this->uri->segment(2) === 'suspends' ? 'class="active"' : '')?> >
                                                <a href="<?= admin_url('reports/suspends') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('suspend_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php }
                                            }
                                            ?>
                                            <li id="reports_best_sellers">
                                                <a href="<?= admin_url('reports/best_sellers') ?>">
                                                    <i class="fa-regular fa fa fa-heart"></i><span class="text"> <?= lang('best_sellers'); ?></span>
                                                </a>
                                            </li>
                                            <?php if ($Owner||$Admin||$GP['reports-daily_sales']) { ?>
                                            <li id="reports_daily_sales">
                                                <a href="<?= admin_url('reports/daily_sales') ?>">
                                                    <i class="fa-regular fa fa fa-heart"></i><span class="text"> <?= lang('daily_sales'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_daily_sale_lists">
                                                <a href="<?= admin_url('reports/daily_sale_lists') ?>">
                                                    <i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_sale_lists'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Owner||$Admin||$GP['reports-monthly_sales']) { ?>
                                            <li id="reports_monthly_sales">
                                                <a href="<?= admin_url('reports/monthly_sales') ?>">
                                                    <i class="fa-regular fa fa fa-heart"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Owner||$Admin||$GP['reports-sales']) { ?>
                                            <li id="reports_sales">
                                                <a href="<?= admin_url('reports/sales') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if ($Settings->module_express) { ?>
                                            <li id="reports_sales">
                                                <a href="<?= admin_url('reports/tickets') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('ticket_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                  
                                            <li id="reports_sales_detail">
                                                <a href="<?= admin_url('reports/sales_detail') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('sales_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_sales_discount">
                                                <a href="<?= admin_url('reports/sales_discount') ?>">
                                                    <i class="fa-regular fa fa-gift"></i><span class="text"> <?= lang('sales_discount_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_daily_sale_profit">
                                                <a href="<?= admin_url('reports/daily_sale_profit') ?>">
                                                    <i class="fa-regular fa fa-money"></i><span class="text"> <?= lang('daily_sale_profit'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_monthly_sale_profit">
                                                <a href="<?= admin_url('reports/monthly_sale_profit') ?>">
                                                    <i class="fa-regular fa fa-money"></i><span class="text"> <?= lang('monthly_sale_profit'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Settings->project){ ?>
                                            <li id="reports_project">
                                                <a href="<?= admin_url('reports/products_project') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('products_project'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Settings->commission && ($Owner||$Admin||$GP['reports-sale_targets'])) { ?>
                                            <li id="reports_sale_targets">
                                                <a href="<?= admin_url('reports/sale_targets') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('sale_targets_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Settings->commission && ($Owner||$Admin||$GP['reports-commission'])) { ?>
                                            <li id="reports_commission">
                                                <a href="<?= admin_url('reports/commission') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('commission_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if (($Owner || $Admin || $GP['reports-fuel_customers_report']) && $this->Settings->module_fuel) { ?>   
                                                <li id="reports_fuel_customers_report">
                                                    <a href="<?= admin_url('reports/fuel_customers_report') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('fuel_customers_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_fuel_customer_details_report">
                                                    <a href="<?= admin_url('reports/fuel_customer_details_report') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('fuel_customer_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if (($Owner || $Admin || $GP['reports-fuel_sales']) && $this->Settings->module_fuel) { ?>
                                                <li id="reports_nozzles_report">
                                                    <a href="<?= admin_url('reports/nozzles_report') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('nozzles_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_fuel_sales">
                                                    <a href="<?= admin_url('reports/fuel_sales') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('fuel_sales_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_fuel_sale_details">
                                                    <a href="<?= admin_url('reports/fuel_sale_details') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('fuel_sale_details_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_fuel_products">
                                                    <a href="<?= admin_url('reports/fuel_products') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('fuel_products_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_tanks">
                                                    <a href="<?= admin_url('reports/tanks') ?>">
                                                        <i class="fa fa-heart"></i><span class="text"> <?= lang('tanks_report'); ?></span>
                                                    </a>
                                                </li>
                                            
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_sale && $Settings->store_sales){ ?>
                                    <li class="sub_mm_reports_store_sales">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-heart"></i>
                                            <span class="text"> <?= lang('store_sales'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($Owner||$Admin||$GP['reports-store_sales']) { ?>
                                            <li id="reports_register">
                                                <a href="<?= admin_url('reports/store_sales') ?>">
                                                    <i class="fa-regular fa fa-th-large"></i><span class="text"> <?= lang('store_sales_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_manufacturing){ ?>
                                    <li class="sub_mm_reports_manufacturing">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('manufacturing'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <li id="reports_project">
                                                <a href="<?= admin_url('reports/workorder_reports') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('workorder_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_convert_reports"  class="hide">
                                                <a href="<?= admin_url('reports/convert_reports') ?>">
                                                    <i class="fa-regular fa fa-barcode"></i><span class="text"> <?= lang('convert_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_loan){ ?>
                                    <li class="sub_mm_reports_loan sub_mm_reports_pawn">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-money"></i>
                                            <span class="text"><?= lang('loans'); ?> & <?= lang('pawns'); ?>  </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if(($Owner || $Admin || $GP['reports-loans'])){ ?>
                                                <li id="reports_loans">
                                                    <a href="<?= admin_url('reports/loans') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loans_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(($Owner || $Admin || $GP['reports-loan_collection'])){ ?>
                                                <li id="reports_loan_collection">
                                                    <a href="<?= admin_url('reports/loan_collection') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loan_collection_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(($Owner || $Admin || $GP['reports-loan_collectable'])){ ?>
                                                <li id="reports_loan_collectable">
                                                    <a href="<?= admin_url('reports/loan_collectable') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loan_collectable_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(($Owner || $Admin || $GP['reports-loan_disbursement'])){ ?>
                                                <li id="reports_loan_disbursement">
                                                    <a href="<?= admin_url('reports/loan_disbursement') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loan_disbursement_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } 
                                            if(($Owner || $Admin || $GP['reports-pawns']) && $this->config->item('pawn')){ ?>
                                                <li id="reports_pawns">
                                                    <a href="<?= admin_url('reports/pawns') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('pawns_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if(($Owner || $Admin)){ ?>
                                                <li id="reports_print_history">
                                                    <a href="<?= admin_url('reports/print_history') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('print_history_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($Settings->module_installment && ($Owner||$Admin||$GP['reports-installments']||$GP['reports-installment_payments'])){ ?>
                                    <li class="sub_mm_reports_loan sub_mm_reports_pawn">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-money"></i>
                                            <span class="text"><?= lang('installments_report'); ?>  </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php  if (($Owner || $Admin || $GP['reports-installments']) && $Settings->module_installment) { ?>
                                                <li id="reports_installments">
                                                    <a href="<?= admin_url('reports/installments') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('installments_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_schdules_report">
                                                    <a href="<?= admin_url('reports/schdules_report') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('schdules_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if (($Owner || $Admin || $GP['reports-installment_payments']) && $Settings->module_installment) { ?>
                                            <li id="reports_installment_payments">
                                                <a href="<?= admin_url('reports/installment_payments') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('installment_payments'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_installment_payment_customers">
                                                <a href="<?= admin_url('reports/installment_payment_customers') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('installment_payment_customers_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_installment_missed_repayments">
                                                <a href="<?= admin_url('reports/installment_missed_repayments') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('installment_missed_repayments_report'); ?></span>
                                                </a>
                                            </li>
                                            
                                            <li id="reports_monthly_installment_payment">
                                                <a href="<?= admin_url('reports/monthly_installment_payment') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('monthly_installment_payment'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_installment_collectable">
                                                <a href="<?= admin_url('reports/installment_collectable') ?>">
                                                    <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('installment_collectable_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php }?>
                                        </ul>
                                    </li>
                                    <?php }?>
                                    <?php if($Settings->module_property){ ?>
                                    <li class="sub_mm_reports_property">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-barcode"></i>
                                            <span class="text"> <?= lang('property'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            
                                            <li id="reports_booking">
                                                <a href="<?= admin_url('reports/leasing_commission') ?>">
                                                    <i class="fa-regular fa fa-filter"></i><span class="text"> <?= lang('commission_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_booking">
                                                <a href="<?= admin_url('reports/booking') ?>">
                                                    <i class="fa-regular fa fa-filter"></i><span class="text"> <?= lang('booking_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_blocking">
                                                <a href="<?= admin_url('reports/blocking') ?>">
                                                    <i class="fa-regular fa fa-filter"></i><span class="text"> <?= lang('blocking_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_blocking">
                                                <a href="<?= admin_url('reports/properties') ?>">
                                                    <i class="fa-regular fa fa-filter"></i><span class="text"> <?= lang('properties_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if ($Owner||$Admin||$GP['reports-suppliers'] || $GP['reports-customers'] || $GP['reports-salemans'] || $GP['reports-commission']) { ?>
                                    <li class="sub_mm_reports_people">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-users"></i>
                                            <span class="text"> <?= lang('people_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($Settings->module_purchase && ($Owner||$Admin||$GP['reports-suppliers'])) { ?>
                                            <li id="reports_supplier_report">
                                                <a href="<?= admin_url('reports/suppliers') ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Settings->module_sale && ($Owner||$Admin||$GP['reports-customers'])) { ?>
                                            <li id="reports_customer_report">
                                                <a href="<?= admin_url('reports/customers') ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if ($Settings->sale_man && $Owner||$Admin||$GP['reports-salemans']) { ?>
                                            <li id="reports_saleman">
                                                <a href="<?= admin_url('reports/saleman') ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('saleman_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_saleman_detail">
                                                <a href="<?= admin_url('reports/saleman_report') ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('saleman_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <li id="reports_staff_report">
                                                <a href="<?= admin_url('reports/users') ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('staff_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li> 
                                    <?php 
                                    }
                                    if($Settings->module_hr && $Owner||$Admin || $GP['hr-employees_report']){ ?>
                                    <li class="sub_mm_reports_hr">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-users"></i>
                                            <span class="text"> <?= lang('hr_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php 
                                             if($Owner || $Admin || $GP['hr-employees_report']) { ?>
                                                <li id="hr_employees_report">
                                                    <a class="submenu" href="<?= admin_url('hr/employees_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('employees_report'); ?></span>
                                                    </a>
                                                </li> 
                                            <?php } if($Owner || $Admin || $GP['hr-banks_report']) { ?>
                                                <li id="hr_banks_report">
                                                    <a class="submenu" href="<?= admin_url('hr/banks_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('banks_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Settings->kpi_function &&($Owner || $Admin || $GP['hr-kpi_report'])) { ?>
                                                <li id="hr_kpi_report">
                                                    <a class="submenu" href="<?= admin_url('hr/kpi_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('kpi_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['hr-id_cards_report']) { ?>
                                                <li id="hr_id_cards_report">
                                                    <a class="submenu" href="<?= admin_url('hr/id_cards_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('id_cards_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['hr-salary_reviews_report']) { ?>
                                                <li id="hr_salary_reviews_report" class="hide">
                                                    <a class="submenu" href="<?= admin_url('hr/salary_reviews_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salary_reviews_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['hr-resignations_report']) { ?>
                                                <li id="hr_resignations_report">
                                                    <a class="submenu" href="<?= admin_url('hr/resignations_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('resignations_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['hr-contracts_report']) { ?>
                                                <li id="hr_contracts_report">
                                                    <a class="submenu" href="<?= admin_url('hr/contracts_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('contracts_report'); ?></span>
                                                    </a>
                                                </li>   
                                                   
                                            <?php } ?>
                                            <li id="hr_promotions_report">
                                                <a class="submenu" href="<?= admin_url('hr/promotions_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('promotions_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="hr_travels_report">
                                                <a class="submenu" href="<?= admin_url('hr/travels_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('travels_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="hr_retirement_report">
                                                <a class="submenu" href="<?= admin_url('hr/retirement_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('retirement_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="hr_transfer_report">
                                                <a class="submenu" href="<?= admin_url('hr/transfer_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('transfer_report'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li> 
                                    <?php } if($Settings->attendance && $Owner||$Admin || $GP['attendances-check_in_out_report']){ ?>
                                    <li class="sub_mm_reports_attendance">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-users"></i>
                                            <span class="text"> <?= lang('attendance_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php 
                                              if($Owner || $Admin || $GP['attendances-check_in_out_report']){ ?>
                                                <li id="attendances_check_in_out_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/check_in_out_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('check_in_out_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-check_in_out_report']){ ?>
                                                <li id="attendances_check_in_out_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/new_checkin_out_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('new_check_in_out_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-daily_attendance_report']){ ?>
                                                <li id="attendances_daily_attendance_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/daily_attendance_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('daily_attendance_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-montly_attendance_report']){ ?>
                                                <li id="attendances_montly_attendance_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/montly_attendance_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> 
                                                            <?= lang('montly_attendance_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="attendances_montly_attendance_summary_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/montly_attendance_summary_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('montly_attendance_summary_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-attendance_department_report']){ ?>
                                                <li id="attendances_attendance_department_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/attendance_department_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('attendance_department_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-employee_leave_report']){ ?>
                                                <li id="attendances_employee_leave_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/employee_leave_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('employee_leave_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="attendances_employee_leave_by_year_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/employee_leave_by_year_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('employee_leave_by_year_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['attendances-day_off_report']){ ?>
                                                <li id="attendances_day_off_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/day_off_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('day_off_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-ot_report']){ ?>
                                                <li id="attendances_ot_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/ot_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('ot_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['attendances-approve_attendance_report']){ ?>
                                                <li id="attendances_approve_attendance_report">
                                                    <a class="submenu" href="<?= admin_url('attendances/approve_attendance_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('approve_attendance_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['attendances-monthly_time_card_report']){ ?>
                                                <li id="attendances_monthly_time_card_report" class="hide">
                                                    <a class="submenu" href="<?= admin_url('attendances/monthly_time_card_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('monthly_time_card_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php }?>

                                        </ul>
                                    </li> 
                                    <?php 
                                    } if($Settings->payroll && $Owner||$Admin || $GP['payrolls-salaries_report']){ ?>
                                    <li class="sub_mm_reports_payroll">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-users"></i>
                                            <span class="text"> <?= lang('payroll_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php 
                                            if($Owner || $Admin || $GP['payrolls-cash_advances_report']) { ?>
                                                <li id="payrolls_cash_advances_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/cash_advances_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('cash_advances_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-benefits_report']) { ?>
                                                <li id="payrolls_benefits_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/benefits_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('benefits_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-benefit_details_report']) { ?>
                                                <li id="payrolls_benefit_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/benefit_details_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('benefit_details_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
                                                    <li id="payrolls_pre_salaries_report">
                                                        <a class="submenu" href="<?= admin_url('payrolls/pre_salaries_report'); ?>">
                                                            <i class="fa fa-users"></i><span class="text"> <?= lang('pre_salaries_report'); ?></span>
                                                        </a>
                                                    </li>
                                            <?php } if($Owner||$Admin||$GP['payrolls-salary_details_report']) { ?>
                                                    <li id="payrolls_pre_salary_details_report">
                                                        <a class="submenu" href="<?= admin_url('payrolls/pre_salary_details_report'); ?>">
                                                            <i class="fa fa-users"></i><span class="text"> <?= lang('pre_salary_details_report'); ?></span>
                                                        </a>
                                                    </li>
                                                    <li id="payrolls_pre_salary_groups_report">
                                                        <a class="submenu" href="<?= admin_url('payrolls/pre_salary_groups_report'); ?>">
                                                            <i class="fa fa-users"></i><span class="text"> <?= lang('pre_salary_groups_report'); ?></span>
                                                        </a>
                                                    </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_bank_notes_report']) { ?>
                                                    <li id="payrolls_pre_salary_bank_notes_report">
                                                        <a class="submenu" href="<?= admin_url('payrolls/pre_salary_bank_notes_report'); ?>">
                                                            <i class="fa fa-users"></i><span class="text"> <?= lang('pre_salary_bank_notes_report'); ?></span>
                                                        </a>
                                                    </li>   
                                                    <li id="payrolls_pre_payslips_report">
                                                        <a class="submenu" href="<?= admin_url('payrolls/pre_payslips_report'); ?>">
                                                            <i class="fa fa-users"></i><span class="text"> <?= lang('pre_payslips_report'); ?></span>
                                                        </a>
                                                    </li>   
                                                    <li id="payrolls_pre_payslip_forms_report">
                                                        <a class="submenu" href="<?= admin_url('payrolls/pre_payslip_forms_report'); ?>">
                                                            <i class="fa fa-users"></i><span class="text"> <?= lang('pre_payslip_forms_report'); ?></span>
                                                        </a>
                                                    </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
                                                <li id="payrolls_salaries_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salaries_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salaries_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_details_report']) { ?>
                                                <li id="payrolls_salary_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salary_details_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salary_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_banks_report']) { ?>
                                                <li id="payrolls_salary_banks_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salary_banks_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salary_banks_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salaries_report']) { ?>
                                                <li id="payrolls_salaries_13_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salaries_13_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salaries_13_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-salary_details_report']) { ?>
                                                <li id="payrolls_salary_13_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/salary_13_details_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('salary_13_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-payslips_report']) { ?>
                                                <li id="payrolls_payslips_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/payslips_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('payslips_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-payments_report']) { ?>
                                                <li id="payrolls_payments_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/payments_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-payment_details_report']) { ?>
                                                <li id="payrolls_payment_details_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/payment_details_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('payment_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['payrolls-nssf_report']) { ?>
                                                <li id="payrolls_nssf_report">
                                                    <a class="submenu" href="<?= admin_url('payrolls/nssf_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('nssf_report'); ?></span>
                                                    </a>
                                                </li>       
                                            <?php } if(($Owner || $Admin || $GP['payrolls-severance_details_report'])) { ?>
                                                <li id="payrolls_severance_details_report" class="hide">
                                                    <a class="submenu" href="<?= admin_url('payrolls/severance_details_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('severance_details_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['payrolls-al_compensate_details_report']) { ?>
                                                <li id="payrolls_al_compensate_details_report" class="hide">
                                                    <a class="submenu" href="<?= admin_url('payrolls/al_compensate_details_report'); ?>">
                                                        <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('indemnity_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } ?>

                                        </ul>
                                    </li> 
                                    <?php } ?>
                                    <!-----account---------->
                                     <?php if(($Settings->module_account||$Settings->module_sale||$Settings->module_purchase) && $Settings->accounting && ($Owner||$Admin|| $GP['reports-payments'] || $GP['reports-tax'] || $GP['account_report-index'])){ ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-book"></i>
                                            <span class="text"> <?= lang('ACCOUNTS'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if ($Owner||$Admin||$GP['reports-payments']) { ?>
                                                <li id="reports_payments">
                                                    <a href="<?= admin_url('reports/payments') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_cash_accounts">
                                                    <a href="<?= admin_url('reports/cash_management') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('cash_management_report'); ?></span>
                                                    </a>
                                                </li>
                                                <!-- <li id="reports_receive_payments_report">
                                                    <a href="<?= admin_url('reports/receive_payments_report') ?>">
                                                        <i class="fa fa-money"></i><span class="text"> <?= lang('receive_payments_report'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_receive_payments_summary_report">
                                                    <a href="<?= admin_url('reports/receive_payments_summary_report') ?>">
                                                        <i class="fa fa-money"></i><span class="text"> <?= lang('receive_payments_summary_report'); ?></span>
                                                    </a>
                                                </li> -->
                                            <?php } if($Settings->module_sale && ($Owner||$Admin||$GP['account_report-payments_received'])){ ?>
                                                <!-- <li id="reports_payments_received">
                                                    <a href="<?= admin_url('reports/payments_received') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('payments_received_report'); ?></span>
                                                    </a>
                                                </li> -->
                                            <?php } if($Settings->module_purchase && ($Owner||$Admin||$GP['account_report-payments_voucher'])){ ?>
                                                <!-- <li id="reports_payments_voucher">
                                                    <a href="<?= admin_url('reports/payments_voucher') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('payments_voucher_report'); ?></span>
                                                    </a>
                                                </li> -->
                                            <?php } if (($Settings->module_sale || $Settings->module_property) && ($Owner||$Admin||$GP['account-list_receivable'])){ ?>
                                                <li id="account_list_ac_recevable">
                                                    <a class="submenu" href="<?= admin_url('account/list_ac_recevable'); ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('list_ac_receivable'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Settings->module_purchase && ($Owner||$Admin||$GP['account-list_payable'])){ ?>
                                                <li id="account_list_ac_payable">
                                                    <a class="submenu" href="<?= admin_url('account/list_ac_payable'); ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('account_payable_list'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Settings->module_sale && ($Owner||$Admin||$GP['account-ar_by_customer'])){ ?>
                                                <li id="account_ar_by_customer">
                                                    <a class="submenu" href="<?= admin_url('account/ar_by_customer'); ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('ar_by_customer'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Settings->module_purchase && ($Owner||$Admin||$GP['account-ap_by_supplier'])){ ?>
                                                <li id="account_ap_by_supplier">
                                                    <a class="submenu" href="<?= admin_url('account/ap_by_supplier'); ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('ap_by_supplier'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Settings->module_sale && ($Owner||$Admin||$GP['account-list_ar_aging'])){ ?>
                                                <li id="account_list_ar_aging">
                                                    <a class="submenu" href="<?= admin_url('account/list_ar_aging'); ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('list_ar_aging'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Settings->module_purchase && ($Owner||$Admin||$GP['account-list_ap_aging'])){ ?>
                                                <li id="account_list_ap_aging">
                                                    <a class="submenu" href="<?= admin_url('account/list_ap_aging'); ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('list_ap_aging'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Owner||$Admin||$GP['reports-tax']) { ?>
                                                <li id="reports_tax" class="hide">
                                                    <a href="<?= admin_url('reports/tax') ?>">
                                                        <i class="fa-regular fa fa-area-chart"></i><span class="text"> <?= lang('tax_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if(!$Settings->module_account && $Owner||$Admin||$GP['reports-profit_loss']){ ?>
                                                <li id="reports_yearly_profit_loss" class="hide">
                                                    <a href="<?= admin_url('reports/yearly_profit_loss') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> Yearly Profit/or Loss</span>
                                                    </a>
                                                </li>
                                                <li id="reports_profit_loss_table">
                                                    <a href="<?= admin_url('reports/profit_loss_table') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('profit_and_loss_table'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if ($Settings->module_account && ($Owner||$Admin||$GP['account_report-index'])) { ?>
                                            <li id="reports_payments">
                                                <a href="<?= admin_url('account/tansfer_payment_report') ?>">
                                                    <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('transfer_payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if($Owner||$Admin||$GP['account_report-ledger']){ ?>
                                                <li id="reports_ledger">
                                                    <a href="<?= admin_url('reports/ledger') ?>">
                                                        <i class="fa-regular fa fa-book"></i><span class="text"> <?= lang('ledger'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner||$Admin||$GP['account_report-trail_balance']){ ?>
                                                <li id="reports_trial_balance">
                                                    <a href="<?= admin_url('reports/trial_balance') ?>">
                                                        <i class="fa-regular fa fa-bars"></i><span class="text"> <?= lang('trial_balance'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner||$Admin||$GP['account_report-income_statement']){ ?>
                                                <li id="reports_income_statement">
                                                    <a href="<?= admin_url('reports/income_statement') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('income_statement'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner||$Admin||$GP['account_report-income_statement_by_month']){ ?>
                                                <li id="reports_income_statement">
                                                    <a href="<?= admin_url('reports/income_statement_by_month') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('income_statement_by_month'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner||$Admin||$GP['account_report-balance_sheet']){ ?>
                                                <li id="reports_balance_sheet">
                                                    <a href="<?= admin_url('reports/balance_sheet') ?>">
                                                        <i class="fa-regular fa fa-balance-scale"></i><span class="text"> <?= lang('balance_sheet'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_balance_sheet_by_period">
                                                    <a href="<?= admin_url('reports/balance_sheet_by_period') ?>">
                                                        <i class="fa-regular fa fa-balance-scale"></i><span class="text"> <?= lang('balance_sheet_by_period'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="reports_balance_sheet">
                                                    <a href="<?= admin_url('reports/balance_sheet_by_month') ?>">
                                                        <i class="fa-regular fa fa-balance-scale"></i><span class="text"> <?= lang('balance_sheet_by_month'); ?></span>
                                                    </a>
                                                </li>
                                                <li id="accountings_balance_sheet_by_month">
                                                    <a href="<?= admin_url('reports/balance_sheet_with_last_month') ?>">
                                                        <i class="fa-regular fa fa-bars"></i><span class="text"> <?= lang('balance_sheet_with_last_month'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            
                                            <li id="reports_cashflow">
                                                <a href="<?= admin_url('reports/cash_flow') ?>">
                                                    <i class="fa-regular fa fa-book"></i><span class="text"> <?= lang('cashflow_report'); ?></span>
                                                </a>
                                            </li>                                                
                                            <li id="reports_cashflow" class="hide">
                                                <a href="<?= admin_url('reports/cashflow') ?>">
                                                    <i class="fa-regular fa fa-book"></i><span class="text"> <?= lang('cashflow_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php if($Owner||$Admin||$GP['account_report-cash_book']){ ?>
                                                <li id="reports_cash_book_report" <?=($this->uri->segment(2) === 'cash_books' ? 'class="active"' : '')?> >
                                                    <a href="<?= admin_url('reports/cash_books') ?>">
                                                        <i class="fa-regular fa fa-list"></i><span class="text"> <?= lang('cash_book'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <li id="reports_ledger">
                                                <a href="<?= admin_url('reports/bank_reconcile') ?>">
                                                    <i class="fa-regular fa fa-book"></i><span class="text"> <?= lang('bank_reconciliation'); ?></span>
                                                </a>
                                            </li>
                                            <li id="reports_ledger">
                                                <a href="<?= admin_url('reports/reconcile_report') ?>">
                                                    <i class="fa-regular fa fa-book"></i><span class="text"> <?= lang('bank_reconciliation_report'); ?></span>
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
                                            <i class="fa-regular fa fa-book"></i>
                                            <span class="text"> <?= lang('loans_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php
                                            if($Owner || $Admin || $GP['reports-loans']){ ?>
                                                <li id="reports_loans">
                                                    <a href="<?= admin_url('reports/loans') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loans_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['reports-loan_collection']){ ?>
                                                <li id="reports_loan_collection">
                                                    <a href="<?= admin_url('reports/loan_collection') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loan_collection_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['reports-loan_disbursement']){ ?>
                                                <li id="reports_loan_disbursement">
                                                    <a href="<?= admin_url('reports/loan_disbursement') ?>">
                                                        <i class="fa-regular fa fa-heart"></i><span class="text"> <?= lang('loan_disbursement_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($this->site->module('pawn')){ ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-book"></i>
                                            <span class="text"> <?= lang('pawn_report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php
                                            if($this->config->item('pawn') && ($Owner || $Admin || $GP['reports-pawn'])) { ?>       
                                                <li id="reports_pawn">
                                                    <a href="<?= admin_url('reports/pawn') ?>">
                                                        <i class="fa-regular fa fa-star"></i><span class="text"> <?= lang('pawn_report'); ?></span>
                                                    </a>
                                                </li>       
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($this->Settings->module_concrete && ($Owner || $Admin|| $GP['concretes-deliveries_report'] || $GP['concretes-daily_deliveries'] || $GP['concretes-daily_stock_outs'] || $GP['concretes-daily_stock_ins']|| $GP['concretes-inventory_in_outs'] || $GP['concretes-moving_waitings_report'] || $GP['concretes-missions_report'] || $GP['concretes-fuels_report'] || $GP['concretes-fuel_summaries_report'] || $GP['concretes-fuel_details_report'] || $GP['concretes-fuel_by_customer_report'] || $GP['concretes-fuel_expenses_report'] || $GP['concretes-fuel_expense_details_report'] || $GP['concretes-sales_report'] || $GP['concretes-sale_details_report'] || $GP['concretes-product_sales_report'] || $GP['concretes-product_customers_report'] || $GP['concretes-adjustments_report'] || $GP['concretes-daily_errors'] || $GP['concretes-daily_error_materials'] || $GP['concretes-absents_report'] || $GP['concretes-commissions_report'] || $GP['concretes-truck_commissions'] || $GP['concretes-pump_commissions'] || $GP['concretes-officer_commissions'])) { ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-book"></i>
                                            <span class="text"> <?= lang('concrete').' '.lang('report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if($Owner || $Admin || $GP['concretes-deliveries_report']) { ?>
                                                <li id="concretes_deliveries_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/deliveries_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('deliveries_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['concretes-daily_deliveries']) { ?>
                                                <li id="concretes_daily_deliveries">
                                                    <a class="submenu" href="<?= admin_url('concretes/daily_deliveries'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('daily_deliveries'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-daily_stock_outs']) { ?>
                                                <li id="concretes_daily_stock_outs">
                                                    <a class="submenu" href="<?= admin_url('concretes/daily_stock_outs'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('daily_stock_outs'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['concretes-daily_stock_ins']) { ?>
                                                <li id="concretes_daily_stock_ins">
                                                    <a class="submenu" href="<?= admin_url('concretes/daily_stock_ins'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('daily_stock_ins'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-inventory_in_outs']) { ?>
                                                <li id="concretes_inventory_in_outs">
                                                    <a class="submenu" href="<?= admin_url('concretes/inventory_in_outs'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('inventory_in_outs'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Settings->moving_waitings && ($Owner || $Admin || $GP['concretes-moving_waitings_report'])) { ?>
                                                <li id="concretes_moving_waitings_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/moving_waitings_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('moving_waitings_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Settings->missions && ($Owner || $Admin || $GP['concretes-missions_report'])) { ?>
                                                <li id="concretes_missions_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/missions_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('missions_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-fuels_report']) { ?>
                                                <li id="concretes_fuels_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/fuels_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuels_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-fuel_summaries_report']) { ?>
                                                <li id="concretes_fuel_summaries_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/fuel_summaries_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuel_summaries_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['concretes-fuel_details_report']) { ?>
                                                <li id="concretes_fuel_details_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/fuel_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuel_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-fuel_by_customer_report']) { ?>
                                                <li id="concretes_fuel_by_customer_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/fuel_by_customer_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuel_by_customer_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Settings->fuel_expenses && ($Owner || $Admin || $GP['concretes-fuel_expenses_report'])) { ?>
                                                <li id="concretes_fuel_expenses_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/fuel_expenses_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuel_expenses_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Settings->fuel_expenses && ($Owner || $Admin || $GP['concretes-fuel_expense_details_report'])) { ?>
                                                <li id="concretes_fuel_expense_details_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/fuel_expense_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('fuel_expense_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-sales_report']) { ?>
                                                <li id="concretes_sales_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/sales_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('sales_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-sale_details_report']) { ?>
                                                <li id="concretes_sale_details_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/sale_details_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('sale_details_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-product_sales_report']) { ?>
                                                <li id="concretes_product_sales_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/product_sales_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('product_sales_report'); ?></span>
                                                    </a>
                                                </li>   
                                            <?php } if($Owner || $Admin || $GP['concretes-product_customers_report']) { ?>
                                                <li id="concretes_product_customers_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/product_customers_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('product_customers_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Owner || $Admin || $GP['concretes-adjustments_report']) { ?>
                                                <li id="concretes_adjustments_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/adjustments_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Settings->errors && ($Owner || $Admin || $GP['concretes-daily_errors'])) { ?>
                                                <li id="concretes_daily_errors">
                                                    <a class="submenu" href="<?= admin_url('concretes/daily_errors'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('daily_errors'); ?></span>
                                                    </a>
                                                </li>       
                                            <?php } if($Settings->errors && ($Owner || $Admin || $GP['concretes-daily_error_materials'])) { ?>
                                                <li id="concretes_daily_error_materials">
                                                    <a class="submenu" href="<?= admin_url('concretes/daily_error_materials'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('daily_error_materials'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($Settings->absents && ($Owner || $Admin || $GP['concretes-absents_report'])) { ?>
                                                <li id="concretes_absents_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/absents_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('absents_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($this->config->item('concrete_commission') && $Owner || $Admin || $GP['concretes-commissions_report']) { ?>
                                                <li id="concretes_commissions_report">
                                                    <a class="submenu" href="<?= admin_url('concretes/commissions_report'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('commissions_report'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($this->config->item('concrete_commission') && $Owner || $Admin || $GP['concretes-truck_commissions']) { ?>
                                                <li id="concretes_truck_commissions">
                                                    <a class="submenu" href="<?= admin_url('concretes/truck_commissions'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('truck_commissions'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($this->config->item('concrete_commission') && $Owner || $Admin || $GP['concretes-pump_commissions']) { ?>
                                                <li id="concretes_pump_commissions">
                                                    <a class="submenu" href="<?= admin_url('concretes/pump_commissions'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('pump_commissions'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } if($this->config->item('concrete_commission') && $Owner || $Admin || $GP['concretes-officer_commissions']) { ?>
                                                <li id="concretes_officer_commissions">
                                                    <a class="submenu" href="<?= admin_url('concretes/officer_commissions'); ?>">
                                                        <i class="fa fa-users"></i><span class="text"> <?= lang('officer_commissions'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                    <?php if($this->Settings->module_school && ($Owner || $Admin || $GP['schools-skills'] || $GP['schools-testing_results'] || $GP['schools-black_lists'] || $GP['schools-graduation_report'] || $GP['schools-black_list_report'] || $GP['schools-dropping_out_report'] || $GP['schools-suspension_report'] || $GP['schools-reconfirmation_report'] ||  $GP['schools-student_status_report'] || $GP['schools-student_statuses'] || $GP['schools-testing_groups'] || $GP['schools-testings'] || $GP['schools-waiting_report'] || $GP['schools-waitings'] || $GP['schools-ticket_report'] || $GP['schools-tickets'] || $GP['schools-feedback_questions'] || $GP['schools-number_of_student_report'] || $GP['schools-enrollment_by_grade_report'] || $GP['schools-monthly_enrollment_report'] || $GP['schools-yearly_enrollment_report'] || $GP['schools-monthly_tuition_fee_report'] || $GP['schools-monthly_payment_report'] || $GP['schools-student_fee_report'] || $GP['schools-sales'] || $GP['schools-overview_chart'] || $GP['schools-failure_student_by_year_report'] || $GP['schools-best_student_by_grade_report'] || $GP['schools-teacher_attendance_report'] || $GP['schools-attendance_report'] || $GP['schools-teacher_attendances'] || $GP['schools-teacher_report'] || $GP['schools-student_report'] || $GP['schools-attendances'] || $GP['schools-class_result_report'] || $GP['schools-add'] || $GP['schools-teachers-add'] || $GP['schools-examinations-add'] || $GP['schools-add'] || $GP['schools-teachers-add']|| $GP['schools-yearly_top_five_form'] || $GP['schools-monthly_top_five_form'] || $GP['schools-result_by_student_form'] || $GP['schools-study_info_report'] || $GP['schools-sectionly_subject_result_report'] || $GP['schools-yearly_top_five_report'] || $GP['schools-yearly_class_result_report'] || $GP['schools-yearly_subject_result_report'] || $GP['schools-monthly_top_five_report'] || $GP['schools-sectionly_class_result_report'] || $GP['schools-section_by_month_report'] || $GP['schools-study_info_report'] || $GP['schools-examanition_report'] || $GP['schools-monthly_class_result_report'] || $GP['schools-programs'] || $GP['schools-grades'] || $GP['schools-subjects'] || $GP['schools-sections'] || $GP['schools-rooms'] || $GP['schools-classes'] || $GP['schools-credit_scores'])) { ?>
                                    <li class="sub_mm_reports_account">
                                        <a class="dropmenu sub_dropmenu" href="#">
                                            <i class="fa-regular fa fa-book"></i>
                                            <span class="text"> <?= lang('school').' '.lang('report'); ?> </span>
                                            <span class="chevron closed blue-color"></span>
                                        </a>
                                        <ul class="sub-sub-menu">
                                            <?php if($Owner || $Admin || $GP['schools-student_report']) { ?>  
                                            <li id="schools_students_report">
                                                <a class="submenu" href="<?= admin_url('schools/students_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('students_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-study_info_report']) { ?>   
                                            <li id="schools_study_info_report">
                                                <a class="submenu" href="<?= admin_url('schools/study_info_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('study_info_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_student_summary_report">
                                                <a class="submenu" href="<?= admin_url('schools/student_summary_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('student_summary_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_student_detail_report">
                                                <a class="submenu" href="<?= admin_url('schools/student_detail_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('student_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-student_by_class_report']) { ?>
                                            <li id="schools_student_by_class_report">
                                                <a class="submenu" href="<?= admin_url('schools/student_by_class_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('student_by_class_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-number_of_student_report']) { ?>
                                            <li id="schools_number_of_student_report">
                                                <a class="submenu" href="<?= admin_url('schools/number_of_student_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('number_of_student_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-enrollment_by_grade_report']) { ?>
                                            <li id="schools_enrollment_grade_by_academic">
                                                <a class="submenu" href="<?= admin_url('schools/enrollment_grade_by_academic'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('enrollment_grade_by_academic'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_enrollment_by_grade_report">
                                                <a class="submenu" href="<?= admin_url('schools/enrollment_by_grade_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('enrollment_by_grade_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-monthly_enrollment_report']) { ?>
                                            <li id="schools_monthly_enrollment_report">
                                                <a class="submenu" href="<?= admin_url('schools/monthly_enrollment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('monthly_enrollment_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-yearly_enrollment_report']) { ?>
                                            <li id="schools_yearly_enrollment_report">
                                                <a class="submenu" href="<?= admin_url('schools/yearly_enrollment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('yearly_enrollment_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-monthly_tuition_fee_report']) { ?>
                                            <li id="schools_monthly_tuition_fee_report">
                                                <a class="submenu" href="<?= admin_url('schools/monthly_tuition_fee_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('monthly_tuition_fee_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-monthly_payment_report']) { ?>
                                            <li id="schools_payment_report">
                                                <a class="submenu" href="<?= admin_url('schools/payment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_cash_account_payment_report">
                                                <a class="submenu" href="<?= admin_url('schools/cash_account_payment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('cash_account_payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_daily_payment_report">
                                                <a class="submenu" href="<?= admin_url('schools/daily_payment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('daily_payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_daily_payment_by_cash_account_report">
                                                <a class="submenu" href="<?= admin_url('schools/daily_payment_by_cash_account_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('daily_payment_by_cash_account_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_monthly_payment_report">
                                                <a class="submenu" href="<?= admin_url('schools/monthly_payment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('monthly_payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_annual_payment_report">
                                                <a class="submenu" href="<?= admin_url('schools/annual_payment_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('annual_payment_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-student_fee_report']) { ?>
                                            <li id="schools_sale_report">
                                                <a class="submenu" href="<?= admin_url('schools/sale_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sale_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_student_fee_report">
                                                <a class="submenu" href="<?= admin_url('schools/student_fee_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('student_fee_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_compulsory_fee_report">
                                                <a class="submenu" href="<?= admin_url('schools/compulsory_fee_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('compulsory_fee_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_fee_report">
                                                <a class="submenu" href="<?= admin_url('schools/fee_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fee_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_fee_by_grade_report">
                                                <a class="submenu" href="<?= admin_url('schools/fee_by_grade_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fee_by_grade_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_fee_by_branch_report">
                                                <a class="submenu" href="<?= admin_url('schools/fee_by_branch_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fee_by_branch_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_branchly_fee_by_grade_report">
                                                <a class="submenu" href="<?= admin_url('schools/branchly_fee_by_grade_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('branchly_fee_by_grade_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_fee_by_item_report">
                                                <a class="submenu" href="<?= admin_url('schools/fee_by_item_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fee_by_item_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_fee_by_category_report">
                                                <a class="submenu" href="<?= admin_url('schools/fee_by_category_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fee_by_category_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_fee_by_sub_category_report">
                                                <a class="submenu" href="<?= admin_url('schools/fee_by_sub_category_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('fee_by_sub_category_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-ticket_report']) { ?>
                                            <li id="schools_ticket_report">
                                                <a class="submenu" href="<?= admin_url('schools/ticket_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('ticket_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-waiting_report']) { ?>
                                            <li id="schools_waiting_report">
                                                <a class="submenu" href="<?= admin_url('schools/waiting_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('waiting_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-testing_report']) { ?>
                                            <li id="schools_testing_report">
                                                <a class="submenu" href="<?= admin_url('schools/testing_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_report'); ?></span>
                                                </a>
                                            </li>   
                                            <li id="schools_testing_detail_report">
                                                <a class="submenu" href="<?= admin_url('schools/testing_detail_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_detail_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_testing_by_grade_report">
                                                <a class="submenu" href="<?= admin_url('schools/testing_by_grade_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_by_grade_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_testing_by_student_report">
                                                <a class="submenu" href="<?= admin_url('schools/testing_by_student_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_by_student_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_testing_student_summary_report">
                                                <a class="submenu" href="<?= admin_url('schools/testing_student_summary_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('testing_student_summary_report'); ?></span>
                                                </a>
                                            </li>
                                            <li id="schools_accepted_student_by_grade_report">
                                                <a class="submenu" href="<?= admin_url('schools/accepted_student_by_grade_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('accepted_student_by_grade_report'); ?></span>
                                                </a>
                                            </li>
                                            
                                            <?php } if($Owner || $Admin || $GP['schools-student_status_report']) { ?>   
                                            <li id="schools_student_status_report">
                                                <a class="submenu" href="<?= admin_url('schools/student_status_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('student_status_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-suspension_report']) { ?>   
                                            <li id="schools_suspension_report">
                                                <a class="submenu" href="<?= admin_url('schools/suspension_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('suspension_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-dropping_out_report']) { ?> 
                                            <li id="schools_dropping_out_report">
                                                <a class="submenu" href="<?= admin_url('schools/dropping_out_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('dropping_out_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-reconfirmation_report']) { ?>   
                                            <li id="schools_reconfirmation_report">
                                                <a class="submenu" href="<?= admin_url('schools/reconfirmation_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('reconfirmation_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-black_list_report']) { ?>   
                                            <li id="schools_black_list_report">
                                                <a class="submenu" href="<?= admin_url('schools/black_list_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('black_list_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-graduation_report']) { ?>   
                                            <li id="schools_graduation_report">
                                                <a class="submenu" href="<?= admin_url('schools/graduation_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('graduation_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-teacher_report']) { ?>  
                                            <li id="schools_teacher_report">
                                                <a class="submenu" href="<?= admin_url('schools/teacher_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('teacher_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-examanition_report']) { ?>  
                                            <li id="schools_examanition_report" class="hide">
                                                <a class="submenu" href="<?= admin_url('schools/examanition_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('examanition_report'); ?></span>
                                                </a>
                                            </li>   
                                            <li id="schools_examanition_report">
                                                <a class="submenu" href="<?= admin_url('schools/exam_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('examanition_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-attendance_report']) { ?>   
                                            <li id="schools_attendance_report">
                                                <a class="submenu" href="<?= admin_url('schools/attendance_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('attendance_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-teacher_attendance_report']) { ?>   
                                            <li id="schools_teacher_attendance_report">
                                                <a class="submenu" href="<?= admin_url('schools/teacher_attendance_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('teacher_attendance_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-monthly_class_result_report']) { ?> 
                                            <li id="schools_monthly_class_result_report">
                                                <a class="submenu" href="<?= admin_url('schools/monthly_class_result_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('monthly_class_result_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-monthly_top_five_report']) { ?> 
                                            <li id="schools_monthly_top_five_report">
                                                <a class="submenu" href="<?= admin_url('schools/monthly_top_five_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('monthly_top_five_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-section_by_month_report']) { ?> 
                                            <li id="schools_section_by_month_report">
                                                <a class="submenu" href="<?= admin_url('schools/section_by_month_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('section_by_month_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-sectionly_class_result_report']) { ?>   
                                            <li id="schools_sectionly_class_result_report">
                                                <a class="submenu" href="<?= admin_url('schools/sectionly_class_result_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sectionly_class_result_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-class_result_report']) { ?> 
                                            <li id="schools_class_result_report">
                                                <a class="submenu" href="<?= admin_url('schools/class_result_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('class_result_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-yearly_class_result_report']) { ?>  
                                            <li id="schools_yearly_class_result_report">
                                                <a class="submenu" href="<?= admin_url('schools/yearly_class_result_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('yearly_class_result_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-yearly_top_five_report']) { ?>  
                                            <li id="schools_yearly_top_five_report">
                                                <a class="submenu" href="<?= admin_url('schools/yearly_top_five_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('yearly_top_five_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-sectionly_subject_result_report']) { ?> 
                                            <li id="schools_sectionly_subject_result_report">
                                                <a class="submenu" href="<?= admin_url('schools/sectionly_subject_result_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('sectionly_subject_result_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-yearly_subject_result_report']) { ?>    
                                            <li id="schools_yearly_subject_result_report">
                                                <a class="submenu" href="<?= admin_url('schools/yearly_subject_result_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('yearly_subject_result_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-result_by_student_form']) { ?>  
                                            <li id="schools_result_by_student_form">
                                                <a class="submenu" href="<?= admin_url('schools/result_by_student_form'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('result_by_student_form'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-monthly_top_five_form']) { ?>   
                                            <li id="schools_monthly_top_five_form">
                                                <a class="submenu" href="<?= admin_url('schools/monthly_top_five_form'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('monthly_top_five_form'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-yearly_top_five_form']) { ?>    
                                            <li id="schools_yearly_top_five_form">
                                                <a class="submenu" href="<?= admin_url('schools/yearly_top_five_form'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('yearly_top_five_form'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-best_student_by_grade_report']) { ?>    
                                            <li id="schools_best_student_by_grade_report">
                                                <a class="submenu" href="<?= admin_url('schools/best_student_by_grade_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('best_student_by_grade_report'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } if($Owner || $Admin || $GP['schools-failure_student_by_year_report']) { ?>  
                                            <li id="schools_failure_student_by_year_report">
                                                <a class="submenu" href="<?= admin_url('schools/failure_student_by_year_report'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('failure_student_by_year_report'); ?></span>
                                                </a>
                                            </li>
                                            <?php } if($Owner || $Admin || $GP['schools-overview_chart']) { ?>  
                                            <li id="schools_overview_chart">
                                                <a class="submenu" href="<?= admin_url('schools/overview_chart'); ?>">
                                                    <i class="fa-regular fa fa-users"></i><span class="text"> <?= lang('overview_chart'); ?></span>
                                                </a>
                                            </li>   
                                            <?php } ?>
                                        </ul>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php 
                        if ((SHOP) && ($Owner && file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'Shop.php'))) { ?>
                            <li class="mm_shop_settings">
                                <a class="dropmenu" href="#">
                                    <i class="fa-regular fa fa-shopping-cart"></i><span class="text"> <?= lang('front_end'); ?> </span>
                                    <span class="chevron closed"></span>
                                </a>
                                <ul>
                                    <li id="shop_settings_index">
                                        <a href="<?= admin_url('shop_settings') ?>">
                                            <i class="fa-regular fa fa-cog"></i><span class="text"> <?= lang('shop_settings'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_slider">
                                        <a href="<?= admin_url('shop_settings/slider') ?>">
                                            <i class="fa-regular fa fa-file"></i><span class="text"> <?= lang('slider_settings'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_pages">
                                        <a href="<?= admin_url('shop_settings/pages') ?>">
                                            <i class="fa-regular fa fa-file"></i><span class="text"> <?= lang('list_pages'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_pages">
                                        <a href="<?= admin_url('shop_settings/add_page') ?>">
                                            <i class="fa-regular fa fa-plus-circle"></i><span class="text"> <?= lang('add_page'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_sms_settings">
                                        <a href="<?= admin_url('shop_settings/sms_settings') ?>">
                                            <i class="fa-regular fa fa-cogs"></i><span class="text"> <?= lang('sms_settings'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_send_sms">
                                        <a href="<?= admin_url('shop_settings/send_sms') ?>">
                                            <i class="fa-regular fa-message-sms"></i><span class="text"> <?= lang('send_sms'); ?></span>
                                        </a>
                                    </li>
                                    <li id="shop_settings_sms_log">
                                        <a href="<?= admin_url('shop_settings/sms_log') ?>">
                                            <i class="fa-regular fa fa-file-text-o"></i><span class="text"> <?= lang('sms_log'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <a href="#" id="main-menu-act" class="full visible-md visible-lg">
                    <i class="fa-regular fa fa-angle-double-left"></i>
                </a>
            </div>
            </td><td class="content-con">

            <div id="content">
                <div class="row hide">
                    <div class="col-sm-12 col-md-12 ">
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