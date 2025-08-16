<?php defined('BASEPATH') or exit('No direct script access allowed');


$bgs = glob(VIEWPATH . 'default/admin/assets/images/login-bgs/*.jpg');
foreach ($bgs as &$bg) {
    $af = explode('assets/', $bg);
    $bg = $assets . $af[1];
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= base_url(); ?>/sbc_favicon.ico">
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet"/>
    <link href="<?= $assets ?>datetimepicker/css/jquery.datetimepicker.css" rel="stylesheet"/>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>datetimepicker/js/jquery.datetimepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>kanban/css/dropzone.css"/>
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>kanban/css/theme.css" rel="stylesheet">
    <noscript><style type="text/css">#loading { display: none; }</style></noscript>
    <script type="text/javascript">
        $(window).load(function () {
            $("#loading").fadeOut("slow");
        });
    </script>
    <style>
        #contain_module {
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            background-size: cover !important;
            background-position: center !important;
            background-image: url("<?= $bgs[mt_rand(0, count($bgs) - 1)] ?>") !important;
        }
    </style>
</head>

<body>
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>
<div id="loading"></div>
<div id="app_wrapper">
    <header id="header" class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <span class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa-regular fa-grid"></i>
                    </a>
                    <ul class="dropdown-menu" style="margin-top: 15px;">
                        <?php
                        if ($Settings->module_inventory) { ?>
                            <li><a class="submenu" href="<?= admin_url('products'); ?>"><?= lang('inventory'); ?></a></li>
                        <?php } if($Settings->module_asset){ ?>
                            <li><a class="submenu" href="<?= admin_url('assets'); ?>"><?= lang('assets'); ?></a></li>
                        <?php } if($Settings->module_purchase) { ?>
                            <li><a class="submenu" href="<?= admin_url('purchases'); ?>"><?= lang('purchases'); ?></a></li>
                        <?php } if ($Settings->module_sale) { ?>
                            <li><a class="submenu" href="<?= admin_url('sales'); ?>"><?= lang('list_sales'); ?></a></li>
                        <?php } if ($Settings->module_hr) { ?>
                            <li><a class="submenu" href="<?= admin_url('hr'); ?>"><?= lang('hr'); ?></a></li>
                        <?php } if ($Settings->payroll) { ?>
                            <li><a class="submenu" href="<?= admin_url('payrolls'); ?>"><?= lang('payroll'); ?></a></li>
                        <?php } if ($Settings->attendance) { ?>
                            <li><a class="submenu" href="<?= admin_url('attendances'); ?>"><?= lang('attendance'); ?></a></li>
                        <?php } if (POS) { ?>
                            <li><a class="submenu" href="<?= admin_url('pos'); ?>"><?= lang('pos'); ?></a></li>
                        <?php } if ($Settings->module_installment) { ?>
                            <li><a class="submenu" href="<?= admin_url('installments'); ?>"><?= lang('installments'); ?></a></li>
                        <?php } if ($Settings->module_property) { ?>
                            <li><a class="submenu" href="<?= admin_url('property'); ?>"><?= lang('property'); ?></a></li>
                        <?php }

                        ?>
                        <li class="divider"></li>
                        <?php 
                        if ($Owner && POS) { ?>
                            <li id="pos_settings">
                                <a href="<?= admin_url('pos/settings') ?>">
                                    <i class="fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
                                </a>
                            </li>
                        <?php } if ($Owner) { ?>
                            <li>
                                <a title="<?= lang('settings') ?>" href="<?= admin_url('system_settings') ?>"><i class="fa fa-cogs"></i> <?= lang('settings'); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a title="<?= lang('settings') ?>" href="<?= admin_url('system_settings/modules') ?>"><i class="fa-regular fa-grid"></i> <?= lang('modules'); ?>
                            </a>
                        </li>
                    </ul>
                </span>
                <span class="logo">
                    <a href="<?= admin_url()?>">
                    <?php 
                    $session_module = $this->session->userdata('module');
                    
                    if($session_module){
                        echo ($session_module);
                    }else{
                       // admin_redirect('welcome');
                        echo $Settings->site_name;
                    }?>
                    </a> 
                </span>
            </div>
            
            <div style="float:right;width: -moz-calc(100% - 251px);width: -webkit-calc(100% - 251px);">
                <div style="float:left;display: none;">
                    menu
                </div>
                <div>
                    <div class="btn-group pull-right visible-xs btn-visible-sm">
                        <button class="navbar-toggle btn" type="button" data-toggle="collapse" data-target="#sidebar_menu">
                            <span class="fa fa-bars"></span>
                        </button>

                        <?php 
                        if (POS || 0) {
                            if (SHOP) {
                            ?>
                            <a href="<?= site_url('/') ?>" class="btn">
                                <span class="fa fa-shopping-cart"></span>sfdsf
                            </a>
                            <?php
                            } 
                            if($this->pos_settings->pos_type =="table" || 
                                $this->pos_settings->pos_type =="room"){
                            ?>
                            <!-- <li class="dropdown hidden-xs">
                                <a class="btn bpurple tip" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= admin_url('table') ?>">
                                    <i class="fa fa-th-large"></i> <span class="padding05"><?= lang('table') ?></span>
                                </a>
                            </li> -->
                            <li class="dropdown hidden-xs">
                                <a class="btn bpurple tip" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= admin_url('table/order') ?>">
                                    <i class="fa fa-th-book"></i> <span class="padding05"><?= lang('order') ?></span>
                                </a>
                            </li>
                            <?php }else{
                            ?>
                            <a href="<?= admin_url('pos') ?>" class="btn">
                                <span class="fa fa-th-large"></span>
                            </a>
                            <?php
                            }
                        }
                        ?>
                       
                        <a href="<?= admin_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn">
                            <span class="fa fa-user"></span>
                        </a>
                        <a href="<?= admin_url('logout'); ?>" class="btn">
                            <span class="fa fa-sign-out"></span>
                        </a>
                    </div>
                    <div class="header-nav">
                        <ul class="nav navbar-nav pull-right">
                            <li class="dropdown">
                                <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                                    <img alt="" src="<?= $this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : base_url('assets/images/' . $this->session->userdata('gender') . '.png'); ?>" class="mini_avatar img-rounded">

                                    <div class="user">
                                        <span><?= $this->session->userdata('username'); ?></span>
                                    </div>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="<?= admin_url('users/profile/' . $this->session->userdata('user_id')); ?>">
                                            <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= admin_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>"><i class="fa fa-key"></i> <?= lang('change_password'); ?>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="<?= admin_url('logout'); ?>">
                                            <i class="fa fa-sign-out"></i> <?= lang('logout'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav pull-right">
                            <li class="dropdown hidden-xs"><a class="tip" title="<?= lang('dashboard') ?>" data-placement="bottom" href="<?= admin_url('welcome') ?>"><i class="fa-sharp fa-regular fa-house"></i></a></li>
                            <?php if (SHOP) { ?>
                            <li class="dropdown hidden-xs"><a class="tip" title="<?= lang('shop') ?>" data-placement="bottom" href="<?= base_url() ?>"><i class="fa fa-shopping-cart"></i></a></li>


                            <li class="dropdown hidden-xs"><a class="tip" title="<?= lang('e_menu') ?>" data-placement="bottom" href="<?= base_url('e_menu') ?>"><?= lang('e_menu');?></a></li>
                       
                            <?php } ?>
                            <?php if ($info) { ?>
                                <li class="dropdown hidden-xs">
                                    <a class="tip" title="<?= lang('notifications') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                        <i class="fa fa-info-circle"></i>
                                        <span class="number blightOrange black"><?= sizeof($info) ?></span>
                                    </a>
                                    <ul class="dropdown-menu pull-right content-scroll">
                                        <li class="dropdown-header"><i class="fa fa-info-circle"></i> <?= lang('notifications'); ?></li>
                                        <li class="dropdown-content">
                                            <div class="scroll-div">
                                                <div class="top-menu-scroll">
                                                    <ol class="oe">
                                                        <?php foreach ($info as $n) {
                                                            echo '<li>' . $n->comment . '</li>';
                                                        } ?>
                                                    </ol>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <li class="dropdown hidden-sm" style="display: none;">
                                <a class="tip" title="<?= lang('styles') ?>" data-placement="bottom" data-toggle="dropdown" href="#">
                                    <i class="fa-regular fa-circle-half-stroke"></i>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    <li class="bwhite noPadding">
                                        <a href="#" id="fixed" class="">
                                            <i class="fa fa-angle-double-left"></i>
                                            <span id="fixedText">Fixed</span>
                                        </a>
                                        <a href="#" id="cssBlack" class="grey">
                                           <i class="fa-sharp fa-regular fa-sun"></i> Light
                                        </a>
                                        <a href="#" id="cssLight" class="black">
                                            <i class="fa-sharp fa-regular fa-moon"></i> Dark
                                        </a>
                                        <a href="#" id="cssBlue" class="blue">
                                            <i class="fa fa-stop"></i> Classic
                                        </a>
                                        
                                   </li>
                                </ul>
                            </li>
                  
                            <li class="dropdown hidden-xs">
                                <a class="tip" title="<?= lang('language') ?>" data-placement="bottom" data-toggle="dropdown"
                                   href="#">
                                    <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>" alt="">
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    <?php $scanned_lang_dir = array_map(function ($path) {
                                        return basename($path);
                                    }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
                                    foreach ($scanned_lang_dir as $entry) {
                                        ?>
                                        <li>
                                            <a href="<?= admin_url('welcome/language/' . $entry); ?>">
                                                <img src="<?= base_url('assets/images/' . $entry . '.png'); ?>" class="language-img">
                                                &nbsp;&nbsp;<?= ucwords($entry); ?>
                                            </a>
                                        </li>
                                    <?php
                                    } ?>
                                </ul>
                            </li>
                            <?php 
                          //  $alert_using = $this->site->getUsingStockAlert();
                            $alert_delivery = $this->site->getPendingDelivery();

                            if (($Owner || $Admin || $GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts']) ||$alert_delivery > 0 || ($qty_alert_num > 0 || $exp_alert_num > 0 || $shop_sale_alerts)) { ?>
                                <li class="dropdown hidden-xs" style="">
                                    <a class="tip" title="<?= lang('alerts') ?>"
                                        data-placement="left" data-toggle="dropdown" href="#">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <span class="number bred black">
                                            <?= $qty_alert_num + 
                                            (($Settings->product_expiry) ? $exp_alert_num : 0) + 
                                            $shop_sale_alerts + 
                                            $shop_payment_alerts +
                                            $get_purchases_request_alerts + 
                                            $get_purchases_order_alerts + 
                                            $get_purchases_order_deadline_alerts + 
                                            $get_purchases_request_deadline_alerts + 
                                            $maintenance_alert_num + (isset($expired_document) ? $expired_document : 0)+
                                            (($this->Settings->module_installment) ? $missed_payment_alert_num:0);
                                        ?></span>
                                    </a>
                                    <ul class="dropdown-menu pull-right" style="overflow:auto;max-height: 6in">
                                        <?php if ($Settings->module_inventory && $qty_alert_num > 0) { ?>
                                        <li>
                                            <a href="<?= admin_url('reports/quantity_alerts') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $qty_alert_num; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('quantity_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if (($Owner || $Admin || $GP['deliveries-add']) && $alert_delivery) { ?>
                                        <li>
                                            <a href="<?= site_url('deliveries/?status=pending') ?>" class="">
                                                <span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $alert_delivery; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('delivery_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Settings->module_installment && $payment_alert_num) { ?>
                                          <li>
                                            <a href="<?= admin_url('reports/payments_alerts') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $payment_alert_num; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('step_payments_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Settings->product_expiry && $exp_alert_num >0) { ?>
                                        <li>
                                            <a href="<?= admin_url('reports/expiry_alerts') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $exp_alert_num; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('expiry_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Settings->module_sale && (!empty($payment_customer_alert_num))) { ?>
                                            <li>
                                                <a href="<?= admin_url('sales/?alert_id='. $payment_customer_alert_num->id) ?>" class="">
                                                    <span class="label label-danger pull-right" style="margin-top:3px;"><?= $payment_customer_alert_num->count; ?></span>
                                                    <span style="padding-right: 35px;"><?= lang('ar_alerts') ?></span>
                                                </a>
                                            </li>
                                        <?php } if ($Settings->module_purchase && (!empty($payment_supplier_alert_num))) { ?>
                                            <li>
                                                <a href="<?= admin_url('purchases/?alert_id='. $payment_supplier_alert_num->id) ?>" class="">
                                                    <span class="label label-danger pull-right" style="margin-top:3px;"><?= $payment_supplier_alert_num->count; ?></span>
                                                    <span style="padding-right: 35px;"><?= lang('ap_alerts') ?></span>
                                                </a>
                                            </li>
                                        <?php } if(!empty($customers_alert_num)){ ?>
                                            <li>
                                                <a href="<?= admin_url('sales/customers_alerts/') ?>" class="">
                                                    <span class="label label-danger pull-right" style="margin-top:3px;"><?= $customers_alert_num; ?></span>
                                                    <span style="padding-right: 35px;"><?= lang('customers_alerts') ?></span>
                                                </a>
                                            </li>
                                        <?php } if ($shop_sale_alerts) { ?>
                                        <li>
                                            <a href="<?= admin_url('sales?shop=yes&delivery=no') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $shop_sale_alerts; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('sales_x_delivered') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($shop_payment_alerts) { ?>
                                        <li>
                                            <a href="<?= admin_url('sales?shop=yes&attachment=yes') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $shop_payment_alerts; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('manual_payments') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($Settings->module_purchase && $get_purchases_request_alerts){ ?>
                                        <li>
                                            <a href="<?= admin_url('purchases_request/purchases_request_alerts') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $get_purchases_request_alerts; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('purchases_request_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($Settings->module_purchase && $get_purchases_order_alerts){  ?>
                                            <li>
                                                <a href="<?= admin_url('purchases_order/purchase_order_alerts') ?>" class="">
                                                    <span class="label label-danger pull-right" style="margin-top:3px;"><?= $get_purchases_order_alerts; ?></span>
                                                    <span style="padding-right: 35px;"><?= lang('purchase_order_alerts') ?></span>
                                                </a>
                                            </li>
                                        <?php } if($Settings->module_purchase && $get_purchases_order_deadline_alerts){ ?>
                                            <li>
                                                <a href="<?= admin_url('purchases_order/purchase_order_deadline_alerts') ?>" class="">
                                                    <span class="label label-danger pull-right" style="margin-top:3px;"><?= $get_purchases_order_deadline_alerts; ?></span>
                                                    <span style="padding-right: 35px;"><?= lang('purchase_order_deadline_alert') ?></span>
                                                </a>
                                            </li>
                                        <?php } if($get_purchases_request_deadline_alerts){ ?>
                                        <li>
                                            <a href="<?= admin_url('purchases_request/purchases_request_deadline_alerts') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $get_purchases_request_deadline_alerts; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('purchase_request_deadline_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($this->Settings->module_loan && $loan_dates >0) { ?>
                                        <li class="hide">
                                            <a href="<?= admin_url('leasing/loan_alert?status=alert') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?php echo $loan_dates; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('loan_alert') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($this->Settings->module_loan && $loan_exp_day >0) { ?>
                                        <li class="hide">
                                            <a href="<?= admin_url('leasing/loan_alert?status=exp_alert') ?>">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $loan_exp_day; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('loan_exp') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($this->Settings->module_loan && $loan_late_exp_day >0) { ?>
                                        <li class="hide">
                                            <a href="<?= admin_url('leasing/loan_alert?status=late_exp') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $loan_late_exp_day; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('loan_late_exp') ?></span>
                                            </a>
                                        </li>
                                        <?php } if ($Settings->module_installment && $missed_payment_alert_num > 0) { ?>
                                           <li>
                                                    <a href="<?= admin_url('installments/missed_repayments') ?>">
                                                        <span class="label label-danger pull-right alert-no" style="margin-top:3px;"><?= $missed_payment_alert_num; ?></span>
                                                        <span style="padding-right: 35px;"><?= lang('missed_repayment_alerts') . ' ' . lang('installments') ?></span>
                                                    </a> 
                                                </li>
                                        <?php } if($Settings->module_sale && $quoties_alert_num){?>
                                        <li>
                                            <a href="<?= admin_url('quotes/quote_alerts/') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $quoties_alert_num; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('quote_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($Settings->module_sale && $get_sale_order_order_alerts){?>
                                        <li>
                                            <a href="<?= admin_url('sales_order/sale_order_alerts/') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $get_sale_order_order_alerts; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('sale_order_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($Settings->module_sale && $deliveries_alert_num){?>
                                        <li>
                                            <a href="<?= admin_url('sales/delivery_alerts/') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $deliveries_alert_num; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('delivery_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($transfer_alert_num){?>
                                        <li>
                                            <a href="<?= admin_url('transfers/transfer_alerts/') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $transfer_alert_num;?></span>
                                                <span style="padding-right: 35px;"><?= lang('transfer_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($Settings->module_sale && $maintenance_alert_num){?>
                                        <li>
                                            <a href="<?= admin_url('sales/maintenance_alert/') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $maintenance_alert_num; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('maintenance_alerts') ?></span>
                                            </a>
                                        </li>
                                        <?php } if($Settings->module_hr && $expired_document){?>
                                        <li>
                                            <a href="<?= admin_url('hr/expired_document') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $expired_document; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('expired_contract') ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= admin_url('hr/expired_document') ?>" class="">
                                                <span class="label label-danger pull-right" style="margin-top:3px;"><?= $expired_document; ?></span>
                                                <span style="padding-right: 35px;"><?= lang('expired_document') ?></span>
                                            </a>
                                        </li>
                                        <?php } if(($Admin || $Owner) && $edit_sale_request_num){ 
                                            foreach($edit_sale_request_num as $row){ 
                                                $first_date = new DateTime($row->date);
                                                $second_date = new DateTime(date('Y-m-d H:i:s'));
                                                $difference = $first_date->diff($second_date);
                                            ?>
                                                <li>
                                                    <a title="<?= $row->sale_reference_no ." ".  $this->bpas->format_interval($difference); ?>" href="<?= admin_url('sales/update_edit_sale_status/'. $row->id) ?>" class="" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $row->sale_reference_no; ?><div style="font-size: 8px;color:#000000;"><?= $this->bpas->format_interval($difference); ?></div></span>
                                                        <span style="padding-right: 35px;"><?= lang('edit_request_alert') ?></span>
                                                    </a>
                                                </li>
                                        <?php }
                                        }else{ 
                                            if($results_approved){
                                                foreach($results_approved as $row){ 
                                                    $first_date = new DateTime($row->updated_date);
                                                    $second_date = new DateTime(date('Y-m-d H:i:s'));
                                                    $difference = $first_date->diff($second_date);
                                                ?>
                                                <li>
                                                    <a title="<?=  $row->sale_reference_no ." ". $this->bpas->format_interval($difference); ?>" href="<?= admin_url('sales/edit/'. $row->sale_id) ?>" class="" >
                                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $row->sale_reference_no; ?><div style="font-size: 8px;color:#000000;"><?= $this->bpas->format_interval($difference); ?></div></span>
                                                        
                                                        <span style="padding-right: 35px;"><?= (($row->note != null && $row->note == "") ? $this->bpas->decode_html($row->note) : lang('approved_alert')) ?></span>
                                                    </a>
                                                </li>
                                            <?php } 
                                            }
                                            if($edit_sale_request_padding){
                                                foreach($edit_sale_request_padding as $row){ 
                                                    $first_date = new DateTime($row->date);
                                                    $second_date = new DateTime(date('Y-m-d H:i:s'));
                                                    $difference = $first_date->diff($second_date);
                                                ?>
                                                <li>
                                                    <a title="<?= $row->sale_reference_no ." ".  $this->bpas->format_interval($difference); ?>" href="<?= admin_url('sales/pending_alert/'. $row->id) ?>" class=""  style="background-color: #EFEFFB" disabled="disabled">
                                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $row->sale_reference_no; ?><div style="font-size: 8px;color:#000000;"><?= $this->bpas->format_interval($difference); ?></div></span>
                                                        <span style="padding-right: 35px;"><?= (($row->noted != null && $row->noted == "") ? $this->bpas->decode_html($row->noted) : lang('pending_alert')) ?></span>
                                                    </a>
                                                </li>
                                            <?php } 
                                            }
                                        if($edit_sale_request_rejects){
                                                foreach($edit_sale_request_rejects as $row){ 
                                                    $first_date = new DateTime($row->updated_date);
                                                    $second_date = new DateTime(date('Y-m-d H:i:s'));
                                                    $difference = $first_date->diff($second_date);
                                                    ?>
                                                <li>
                                                    <a title="<?= $row->sale_reference_no ." ".  $this->bpas->format_interval($difference); ?>" href="<?= admin_url('sales/remove_rejected/'. $row->id) ?>" class="" data-toggle="modal" data-backdrop="static" data-target="#myModal" style="background-color: #EFEFFB" >
                                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $row->sale_reference_no; ?><div style="font-size: 8px;color:#000000;"><?= $this->bpas->format_interval($difference); ?></div></span>
                                                        <span style="padding-right: 35px;"><?= (($row->noted != null && $row->noted == "") ? $this->bpas->decode_html($row->noted) : lang('rejected_alert')) ?></span>
                                                    </a>
                                                </li>
                                            <?php } 
                                            }
                                        } ?>
                                    </ul>
                                </li>
                            <?php
                               } ?>
                            <?php
                            if (POS) {
                            if($this->pos_settings->pos_type =="table" || 
                                $this->pos_settings->pos_type =="room"){
                            ?>
                            <li class="dropdown hidden-xs">
                                <a class="tip" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= admin_url('table') ?>">
                                    <i class="fa fa-th-large"></i> <span class="padding05"><?= lang('table') ?></span>
                                </a>
                            </li>
                            <?php }else{ ?>
                            <li class="dropdown hidden-xs">
                                <a class="tip" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= admin_url('pos') ?>">
                                    <i class="fa fa-th-large"></i> <span class="padding05"><?= lang('pos') ?></span>
                                </a>
                            </li>
                            <?php } }?>

                            <?php if ($Owner) {
                                        ?>
                                <li class="dropdown">
                                    <a class="tip" id="today_profit" title="<span><?= lang('today_profit') ?></span>"
                                        data-placement="bottom" data-html="true" href="<?= admin_url('reports/profit') ?>"
                                        data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-hourglass-2"></i>
                                    </a>
                                </li>
                            <?php
                                    } ?>
                                    
                            <?php if ($Owner || $Admin) {
                            ?>
                                <?php if (POS) { ?>
                                <li class="dropdown hidden-xs">
                                    <a class="tip" title="<?= lang('list_open_registers') ?>" data-placement="bottom" href="<?= admin_url('pos/registers') ?>">
                                        <i class="fa fa-list"></i>
                                    </a>
                                </li>
                                <?php } ?>
                                <li class="dropdown hidden-xs">
                                    <a class="tip" title="<?= lang('clear_ls') ?>" data-placement="bottom" id="clearLS" href="#">
                                        <i class="fa fa-eraser"></i>
                                    </a>
                                </li>
                            <?php
                            } ?>

                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </header>