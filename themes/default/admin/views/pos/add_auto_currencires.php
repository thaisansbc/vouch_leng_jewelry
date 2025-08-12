<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<style>
    form {
        width: 300px;
        margin: 0 auto;
        text-align: center;
        padding-top: 50px;
    }
    .value-button {
        display: inline-block;
        border: 1px solid #ddd;
        margin: 0px;
        width: 40px;
        height: 20px;
        text-align: center;
        vertical-align: middle;
        padding: 11px 0;
        background: #eee;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    .value-button:hover {
        cursor: pointer;
    }
    form #decrease {
        margin-right: -4px;
        border-radius: 8px 0 0 8px;
    }
    form #increase {
        margin-left: -4px;
        border-radius: 0 8px 8px 0;
    }
    form #input-wrap {
        margin: 0px;
        padding: 0px;
    }
    input#number {
        text-align: center;
        border: none;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        margin: 0px;
        width: 40px;
        height: 40px;
    }
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #pos #product-list{
        overflow: auto !important;
    }
    #header .header-nav i{
        font-size: 20px;
    }
</style>
<style type="text/css">
    .parent { border-radius: 5px;display: block; position: relative; float: left; line-height: 30px; background-color: #d9534f; border-right:#CCC 1px solid;}
    .parent a { margin: 10px; color: #FFFFFF; text-decoration: none;}
    .parent:hover > ul { display: block; position: absolute; top: 103%;}
    .child, .child_1 { display: none;z-index: 5}
    .child li, .child_1 li{ line-height: 30px; border-bottom:#CCC 1px solid; border-right:#CCC 1px solid; width: 80%; }
    ul { list-style: none; margin: 0; padding: 0px; min-width:10em;}
    #postsList ul ul.child_1 { left: 0; top: 100%; margin-left: 1px; }
    #postsList ul ul ul { left: 100%; top: 0; margin-left: 1px; }
    #menu li:hover { background-color: #cf2e2e; }
    .expand { font-size:12px; float:right; margin-right: 10px; }
</style>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('pos_module') . " | " . $Settings->site_name; ?></title>
    <script type="text/javascript">
        if (parent.frames.length !== 0) {
            top.location = '<?= admin_url('pos') ?>';
        }
    </script>
    <base href="<?= base_url() ?>" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css" />
    <link rel="stylesheet" href="<?= $assets ?>styles/style.css" type="text/css" />
    <link rel="stylesheet" href="<?= $assets ?>pos/css/posajax.css" type="text/css" />
    <link rel="stylesheet" href="<?= $assets ?>pos/css/print.css" type="text/css" media="print" />
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <?php 
    $currency_bath = 33;
    $currency_khr = $this->site->getCurrencyByCode('KHR')->rate;
    ?>
    <script>
        localStorage.setItem('currency_code', '<?php echo isset($currency_code) ? $currency_code : 'USD' ?>');
        localStorage.setItem('default_currency', '<?php echo isset($settings->default_currency) ? $settings->default_currency : 'USD' ?>');
        localStorage.setItem('language', '<?php echo isset($language) ? $language : 'english' ?>');
        localStorage.setItem('exchange_kh', '<?php echo $currency_khr; ?>');
        localStorage.setItem('riel_rate', '<?php echo isset($exchange_rate->rate) ? $exchange_rate->rate : 0 ?>');
        localStorage.setItem('exchange_bat_out', '<?php echo $currency_bath; ?>');
        localStorage.setItem('bath_rate', '<?php echo isset($exchange_rate_bat->rate) ? $exchange_rate_bat->rate : 0 ?>');
    </script>
    <script>
        var time = new Date().getTime();
        $(document.body).bind("mousemove keypress", function(e) {
            time = new Date().getTime();
        });
        /*  setInterval(function() {
                $('#suspend_sale').click();
              location.reload();
            }, 36000); */
    </script>
    <style>
        /* span {cursor:pointer; }
            .number{
                margin:100px;
            }
            .minus, .plus{
                width:20px;
                height:20px;
                background:#f2f2f2;
                border-radius:4px;
                padding:8px 5px 8px 5px;
                border:1px solid #ddd;
        display: inline-block;
        vertical-align: middle;
        text-align: center;
            }
            input{
                height:34px;
        width: 100px;
        text-align: center;
        font-size: 26px;
                border:1px solid #ddd;
                border-radius:4px;
        display: inline-block;
        vertical-align: middle;
            } */
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
    <div id="wrapper">
        <header id="header" class="navbar">
            <div class="container">
                <a class="navbar-brand" href="<?= admin_url() ?>">
                    <span class="logo">
                        <span class="pos-logo-lg"><?= $Settings->site_name ?></span>
                        <span class="pos-logo-sm">
                            <?= lang('pos') ?>
                        </span>
                    </span>
                </a>
                <div class="header-nav login_account">
                    <ul class="nav navbar-nav pull-right ">
                        <li class="dropdown">
                            <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                                <img alt="" src="<?= $this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png'; ?>" class="mini_avatar img-rounded">
                                <div class="user">
                                    <span><?= $this->session->userdata('username'); ?></span>
                                </div>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= admin_url('auth/profile/' . $this->session->userdata('user_id')); ?>">
                                        <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= admin_url('auth/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>">
                                        <i class="fa fa-key"></i> <?= lang('change_password'); ?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= admin_url('auth/logout'); ?>">
                                        <i class="fa fa-sign-out"></i> <?= lang('logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav pull-right list_li">
                        <li class="dropdown hide">
                            <a class="pos-tip" title="<?= lang('dashboard') ?>" data-placement="bottom" href="<?= admin_url('welcome') ?>">
                                <i class="fa fa-dashboard"></i>
                            </a>
                        </li>
                        <?php if ($Owner) { ?>
                            <li class="dropdown hidden-sm hide">
                                <a class="btn pos-tip" title="<?= lang('settings') ?>" data-placement="bottom" href="<?= admin_url('pos/settings') ?>">
                                    <i class="fa fa-cogs"></i>
                                </a>
                            </li>
                        <?php }
                        ?>
                        <li class="dropdown hidden-xs_ hide">
                            <a class="pos-tip" title="<?= lang('calculator') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                <i class="fa fa-calculator"></i>
                            </a>
                            <ul class="dropdown-menu pull-right calc">
                                <li class="dropdown-content">
                                    <span id="inlineCalc"></span>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown hidden-sm_">
                            <a class="btn pos-tip" title="<?= lang('shortcuts') ?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                                <i class="fa fa-key"></i>
                            </a>
                        </li>
                        <?php  
                            if($this->config->item('ktv')){
                                if( $Admin || $Owner || $GP['pos-customer_stock'] ){ ?>
                                    <li class="dropdown">
                                        <a class="btn pos-tip" title="<?=lang('customer_stocks')?>" data-placement="bottom" href="<?=admin_url('pos/customer_stocks')?>">
                                            <i class="fa fa-bars"></i>
                                            <?php 
                                                $cuspendings = $this->pos_model->getAllCustomerStockPendings();
                                                if($cuspendings){
                                                    echo '<span class="number bred white">'.($cuspendings).'</span>';
                                                } 
                                            ?>
                                        </a>
                                    </li>
                                    <li class="dropdown">
                                        <a class="btn pos-tip" title="<?=lang('add_customer_stock')?>" data-placement="bottom" href="<?=admin_url('pos/add_customer_stock')?>">
                                            <i class="fa fa-plus-circle"></i>
                                        </a>
                                    </li>
                        <?php 
                                } 
                        
                            } 
                        ?>

                        <li class="dropdown">
                            <a class="btn pos-tip" title="<?= lang('view_kitchen') ?>"data-placement="bottom" href="<?= admin_url('pos/kitchen') ?>" target="_blank">
                                <i class="fa-regular fa-kitchen-set"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn pos-tip" title="<?= lang('view_bill_screen') ?>" data-placement="bottom" href="<?= admin_url('pos/view_bill') ?>" target="_blank">
                                <i class="fa-sharp fa-regular fa-file-invoice"></i>
                            </a>
                        </li>
                        <?php
                        if ($this->pos_settings->pos_type == "table" ||
                            $this->pos_settings->pos_type == "room"
                        ) {
                        ?>
                            <li class="dropdown hidden-xs">
                                <a class="btn pos-tip" title="<?= lang('table') ?>" data-placement="bottom" href="<?= admin_url('table') ?>">
                                    <i class="fa-regular fa-tablet-rugged"></i>
                                </a>
                            </li>
                        <?php
                        }
                        ?>
                        <li class="dropdown">
                            <a class="btn pos-tip" id="opened_bills" title="<span><?= lang('suspended_sales') ?></span>" data-placement="bottom" data-html="true" href="<?= admin_url('pos/opened_bills') ?>" data-toggle="ajax">
                                <i class="fa-regular fa-floppy-disk-circle-arrow-right"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn pos-tip" id="register_details" title="<span><?= lang('register_details') ?></span>" data-placement="bottom" data-html="true" href="<?= admin_url('pos/register_details') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-check-circle"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn pos-tip" id="close_register" title="<span><?= lang('close_register') ?></span>" data-placement="bottom" data-html="true" data-backdrop="static" href="<?= admin_url('pos/close_register') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-times-circle"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn pos-tip" id="add_expense" title="<span><?= lang('add_expense') ?></span>" data-placement="bottom" data-html="true" href="<?= admin_url('expenses/add_expense') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-dollar"></i>
                            </a>
                        </li>
                        <?php if ($Owner) { ?>
                            <li class="dropdown">
                                <a class="btn pos-tip" id="today_profit" title="<span><?= lang('today_profit') ?></span>" data-placement="bottom" data-html="true" href="<?= admin_url('reports/profit') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                    <i class="fa fa-hourglass-half"></i>
                                </a>
                            </li>
                        <?php }
                        ?>
                        <?php if ($Owner || $Admin) { ?>
                            <li class="dropdown">
                                <a class="btn pos-tip" id="today_sale" title="<span><?= lang('today_sale') ?></span>" data-placement="bottom" data-html="true" href="<?= admin_url('pos/today_sale') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                    <i class="fa fa-heart"></i>
                                </a>
                            </li>
                            <li class="dropdown hidden-xs">
                                <a class="btn pos-tip" title="<?= lang('list_open_registers') ?>" data-placement="bottom" href="<?= admin_url('pos/registers') ?>">
                                    <i class="fa fa-list"></i>
                                </a>
                            </li>
                            <li class="dropdown hidden-xs">
                                <a class="btn bred pos-tip" title="<?= lang('clear_ls') ?>" data-placement="bottom" id="clearLS" href="#">
                                    <i class="fa fa-eraser"></i>
                                </a>
                            </li>
                        <?php }
                        ?>
                    </ul>
                    <ul class="nav navbar-nav pull-right hide">
                        <li class="dropdown">
                            <a class="" style="cursor: default;"><span id="display_time"></span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        <div id="content">
            <div class="c1">
                <div class="pos">
                    <?php
                    if ($error) {
                        echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $error . "</div>";
                    }
                    ?>
                    <?php
                    if ($message) {
                        echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $message . "</div>";
                    }
                    ?>
                    <div id="pos">
                        <input type="hidden" id="time" value="15" />
                        <?php
                        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos-sale-form');
                        echo admin_form_open("pos", $attrib);
                        if(isset($customer_qty) && isset($room_n)){
                            echo '<input type="hidden" name="customer_qty" value="' . $customer_qty . '"/>';
                            echo '<input type="hidden" name="room_n" value="' . $room_n . '"/>';
                        }
                        if (isset($suspend_sale)) {
                            echo '<input type="hidden" id="start_time" value="' . date("d/m/Y H:i", strtotime($suspend_sale->start_date)) . '"/>';
                            echo '<input name="start_time" type="hidden" value="' . date("Y-m-d H:i:s", strtotime($suspend_sale->start_date)) . '"/>';
                            echo '<input name="end_time" type="hidden" value="' . date("Y-m-d H:i:s") . '"/>';
                        } else {
                            echo '<input type="hidden" name="start_time" id="start_time" value="' . date("Y-m-d H:i:s") . '"/>';
                        }
                        if (isset($room_number)) {
                             if($customer_qty > 1){
                            echo '<div class="title_room">' . $room_number . ' (<span style="color:blue;">' . $customer_qty .'</span>)</div>';
                            echo '<input type="hidden" id="suspend_id" value="' . $room_id . '"/>';
                             }else{
                                 echo '<div class="title_room">' . $room_number .'</div>';
                            echo '<input type="hidden" id="suspend_id" value="' . $room_id . '"/>';
                             }
                        } ?>
                        <div id="leftdiv">
                            <div id="printhead">
                                <h4 style="text-transform:uppercase;"><?php echo $Settings->site_name; ?></h4>
                                <?php
                                echo "<h5 style=\"text-transform:uppercase;\">" . $this->lang->line('order_list') . "</h5>";
                                echo $this->lang->line("date") . " " . $this->bpas->hrld(date('Y-m-d H:i:s'));
                                ?>
                            </div>
                            <div id="left-top">
                                <div style="position: absolute;left:-9999px;"><?php echo form_input('test', '', 'id="test" class="kb-pad"'); ?></div>
                                <!-- <div class="form-group">
                                <input type="text" id="customer_detail" class="form-control" />
                            </div>-->
                                <div class="row">
                                    <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <?php
                                                    foreach ($customers as $customer_) {
                                                        $cm[$customer_->id] = $customer_->name;
                                                    }
                                                    //echo form_dropdown('customer', $cm, (isset($_POST['customer']) ? $_POST['customer'] : ''), 'id="poscustomer" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" style="width:100%;" ');
                                                    echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="poscustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control pos-input-tip" style="width:100%;"');
                                                ?>
                                                <?php //echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="poscustomer" list="list_customer" placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control pos-input-tip" style="width:100%;"'); ?>
                                               <!--  <datalist id="list_customer">
                                                    <php foreach ($customers as $customer) { ?>
                                                    <option data-value="<?= $customer->id ?>"><?= $customer->name ?></option>
                                                    <php } ?>
                                                </datalist> -->
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <!-- <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="choose-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div> -->
                                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?= admin_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.5em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                    
                                    <?php 
                                    if ($Owner|| !$this->session->userdata('warehouse_id')) { ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php
                                            $wh[''] = '';
                                            foreach ($warehouses as $warehouse) {
                                                $wh[$warehouse->id] = $warehouse->name;
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $this->pos_settings->default_warehouse), 'id="poswarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                    <?php } else {
                                        $wh[''] = '';
                                        $arr_warehouse =  explode(',', $this->session->userdata('warehouse_id'));
                                        foreach ($warehouses as $warehouse) {
                                            foreach($arr_warehouse as $arr_wh){
                                                if($arr_wh == $warehouse->id){
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                            }
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : (count($arr_warehouse) == 1 ? $arr_warehouse[0] : $Settings->default_warehouse)), 'id="poswarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                        // $warehouse_input = array(
                                        //     'type' => 'hidden',
                                        //     'name' => 'warehouse',
                                        //     'id' => 'poswarehouse',
                                        //     'value' => $this->session->userdata('warehouse_id'),
                                        // );
                                        // echo form_input($warehouse_input);
                                    }
                                    ?>
                                    <?php if($this->pos_settings->member_card == 1){?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo form_input('membership_code', (isset($_POST['membership_code']) ? $_POST['membership_code'] : ''), 'class="form-control pos-input-tip" placeholder="' . $this->lang->line("scan_membership_card").'" id="posmembership_code" autocomplete="off" '); ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="no-print">
                                    
                                    <div class="form-group" id="ui">
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group">
                                            <?php } ?>
                                            <?php echo form_input('add_item', '', 'class="form-control pos-tip" id="add_item" data-placement="top" data-trigger="focus" placeholder="' . $this->lang->line("search_product_by_name_code") . '" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                                <div class="input-group-addon" style="padding: 2px 8px;">
                                                    <a href="#" id="addManually">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.5em;"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="print">
                                <div id="left-middle">
                                    <div id="product-list">
                                        <table class="table items table-striped table-bordered table-condensed table-hover sortable_table" id="posTable" style="margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th width="50%"><?= lang("product"); ?></th>
                                                    <th width="15%"><?= lang("price"); ?></th>
                                                    <th width="15%"><?= lang("qty"); ?></th>
                                                    <?php 
                                                    if ($Settings->using_weight) { 
                                                        echo '<th width="15%">'.lang('weight').'</th>';
                                                    }
                                                    ?>
                                                    <th width="20%"><?= lang("subtotal"); ?></th>
                                                    <?php if($Owner || (isset($permission->remove_item) && $permission->remove_item == 1)){ ?>
                                                        <th style="width: 5%; text-align: center;">
                                                            <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                        </th>
                                                    <?php }?>
                                                </tr>
                                            </thead>

                                            <tbody>

                                            </tbody>
                                        </table>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                                <script>
                                    var unit = 0;
                                    var total;
                                    // if user changes value in field
                                    $('.field').change(function() {
                                        unit = this.value;
                                    });
                                    $('.add').click(function() {
                                        unit++;
                                        var $input = $(this).prevUntil('.sub');
                                        $input.val(unit);
                                        unit = unit;
                                    });
                                    $('.sub').click(function() {
                                        if (unit > 0) {
                                            unit--;
                                            var $input = $(this).nextUntil('.add');
                                            $input.val(unit);
                                        }
                                    });
                                </script>
                                <div style="clear:both;"></div>
                                <div id="left-bottom">
                                    <table id="totalTable" style="width:100%; float:right; padding:5px; color:#000; background: #FFF;border:none;">
                                        <tr>
                                            <td style="padding: 5px 10px;"><?= lang('total').' '.lang('items'); ?></td>
                                            <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                                <span id="titems">0</span>
                                            </td>
                                            <td style="padding: 5px 10px;"><?= lang('total'); ?></td>
                                            <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                                <span id="total">0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">&nbsp;</td>
                                            <td style="padding: 5px 10px;"><?= lang('discount'); ?>
                                                <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                                                    <a href="#" id="ppdiscount">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                            <td class="text-right" style="padding: 5px 10px;font-weight:bold;">
                                                <span id="tds">0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">&nbsp;</td>
                                            <td style="padding: 5px 10px;"><?= lang('order_tax'); ?>
                                                <a href="#" id="pptax2">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                            <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;">
                                                <span id="ttax2">0.00</span>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td colspan="2">
                                                <?php if($this->pos_settings->pos_type !='pos'){ ?>
                                                    <button type="button" class="btn btn-block" id="print_order" style="background: #F5F5F5;color:#B60C74;border: 1px solid #B60C74;border-radius: 3px !important;">
                                                        <?= lang('print_order'); ?>
                                                    </button>
                                                <?php }?>
                                            </td>
                                            <td style="padding: 5px 10px;"><?= lang('delivery_fee'); ?>
                                                <a href="#" id="pshipping">
                                                    <i class="fa fa-plus-square"></i>
                                                </a>
                                                
                                            </td>
                                            <td class="text-right" style="padding: 5px 10px;font-weight:bold;">
                                                <span id="tship"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <?php if($this->pos_settings->pos_type !='pos'){ ?>
                                                <button type="button" class="btn btn-block" id="print_bill" style="background: #F5F5F5;color: #428bca;border: 1px solid #428bca;border-radius: 3px !important;">
                                                    <?= lang('print_bill'); ?>
                                                </button>
                                                <?php }?>
                                            </td>
                                            <td style="padding: 5px 10px; border-top: 1px solid #666;font-weight:bold; background:#6c757d; color:#FFF;" >
                                                <?= lang('total_payable'); ?> Rate: ៛<?= $currency_khr;?>
                                            </td>
                                            <td class="text-right" style="padding:5px 10px 5px 10px; font-weight:bold; background:#6c757d; color:#FFF;">
                                                (<span id="gtotal_en"></span>) <span id="gtotal">0.00</span>
                                            </td>
                                        </tr>
                                        <tr class="hide">
                                            <td colspan="2">&nbsp;</td>

                                            <td style="padding: 5px 10px; border-top: 1px solid #666;font-weight:bold; background:#6c757d; color:#FFF;" >
                                                <?= lang('total_payable_kh'); ?>
                                            </td>
                                            <td class="text-right" style="padding:5px 10px 5px 10px; font-weight:bold; background:#6c757d; color:#FFF;">
                                                <span id="gtotal_en"></span>
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="clearfix"></div>
                                    <div id="botbuttons" class="col-xs-12 text-center">
                                        <input type="hidden" name="biller" id="biller" value="<?= ($Owner || $Admin || !$this->session->userdata('biller_id')) ? $pos_settings->default_biller : $this->session->userdata('biller_id') ?>" />
                                        <div class="row" style="margin-top: 5px;">
                                            <div class="col-xs-4" style="padding-right: 5px;">
                                                    <button type="button" class="btn btn-block btn-flat" id="suspend" style="background: #F5F5F5;color: #17a2b8;border: 1px solid #17a2b8;border-radius: 3px !important;">
                                                        <?= lang('suspend'); ?>
                                                    </button>
                                                
                                                    <a href="#" table_id="'.$note_order->suspend_note.'" cu_id="'.$note_order->id.'" class="btn btn-block btn-flat split_bills hide"><?= lang('split_bills'); ?></a>
                                            </div>
                                            <div class="col-xs-4" style="padding-right: 5px;">
                                                    <?php if(isset($room_n) && $room_tmp == 0){?>
                                                     <button type="button" class="btn btn-block btn-flat split_bills"  id="split_bills" style="background: #F5F5F5;color: #dc3545;border: 1px solid #dc3545;border-radius: 3px !important;">
                                                        <?= lang('split_bill'); ?>
                                                    </button>
                                                    <?php } else { ?>
                                                    <button type="button" class="btn btn-block btn-flat " id="reset" style="background: #F5F5F5;color: #dc3545;border: 1px solid #dc3545;border-radius: 3px !important;">
                                                        <?= lang('cancel'); ?>
                                                    </button>
                                                    <?php } ?>
                                                    
                                                    
                                           
                                            </div>
                                            <div class="col-xs-4" style="padding-right: 5px;">
                                                <button type="button" class="btn btn-success btn-block" id="payment" style="border-radius: 3px !important;">
                                                    <i class="fa fa-money" style="margin-right: 5px;"></i><?= lang('pay_now'); ?>
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                    <div style="clear:both; height:5px;"></div>
                                    <div id="num">
                                        <div id="icon"></div>
                                    </div>
                                    <span id="hidesuspend"></span>
                                    <input type="hidden" name="project_1" id="project_1"/>
                                    <input type="hidden" name="saleman_1" id="saleman_1"/>
                                    <input type="hidden" name="delivery_by_1" id="delivery_by_1"/>

                                    <input type="hidden" name="bill_address" value="<?php echo isset($biller_adr->address) ? $biller_adr->address : ''; ?>" id="bill_address">
                                    <input name="bill_refer" id="bill_refer" type="hidden" value="<?= $suspend_sale ? $suspend_sale->refer : ($old_sale ? $old_sale->refer : ''); ?>">

                                    <?php if ($pos_settings->pos_type != 'pos') { ?>
                                        <input type="hidden" name="suspend_note" id="table_id" value="<?php if (isset($room_n)) { echo $room_n; } ?>">
                                    <?php } ?>
                                    
                                    <input type="hidden" name="pos_note" value="" id="pos_note">
                                    <input type="hidden" name="staff_note" value="" id="staff_note">
                                    <input type="hidden" name="table_note" value="<?php if (isset($room_number)) { echo $room_number; } ?>" id="table_note">
                                    <input type="hidden" name="check_bill" value="<?php if (isset($biller_audit)) { echo $biller_audit->reference; } ?>" id="check_bill">
                                    <input type="hidden" name="check_bill_refer" value="<?php if (isset($biller_audit_refer)) { echo $biller_audit_refer->reference; } ?>" id="check_bill_refer">
                                    <input type="hidden" name="kh_currenncy" value="khm" id="kh_currenncy">
                                    <input type="hidden" name="en_currenncy" value="" id="en_currenncy">

                                    <div id="payment-con">
                                        <?php for ($i = 1; $i <= 3; $i++) { ?>
                                            <input type="hidden" name="amount[]" id="amount_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="balance_amount[]" id="balance_amount_<?= $i ?>" value="" />
                                            <input type="hidden" name="paid_amount[]" id="paid_amount_<?= $i ?>" value="" />
                                            <input type="hidden" name="paid_amount_kh[]" id="paid_amount_kh_<?=$i?>" value=""/>
                                            <input type="hidden" name="paid_amount_bat[]" id="paid_amount_bat_<?=$i?>" value=""/>
                                            <input type="hidden" name="currency_rate[]" id="currency_rate_<?= $i ?>" value="1,<?= $exchange_rate->rate ?>,<?= (!empty($exchange_rate_bat->rate) ? $exchange_rate_bat->rate : '') ?>" />
                                            <input type="hidden" name="paid_by[]" id="paid_by_val_<?= $i ?>" value="cash" />
                                            <input type="hidden" name="cc_no[]" id="cc_no_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="paying_gift_card_no[]" id="paying_gift_card_no_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_holder[]" id="cc_holder_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cheque_no[]" id="cheque_no_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_month[]" id="cc_month_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_year[]" id="cc_year_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_type[]" id="cc_type_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_cvv2[]" id="cc_cvv2_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="payment_note[]" id="payment_note_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="months[]" id="months_<?= $i ?>" value="" />
                                        <?php }
                                        ?>
                                    </div>
                                   
                                    <input name="order_tax" type="hidden" value="<?= $suspend_sale ? $suspend_sale->order_tax_id : ($old_sale ? $old_sale->order_tax_id : $Settings->default_tax_rate2); ?>" id="postax2">
                                    <input name="discount" type="hidden" value="<?= $suspend_sale ? $suspend_sale->order_discount_id : ($old_sale ? $old_sale->order_discount_id : ''); ?>" id="posdiscount">
                                    <input name="shipping" type="hidden" value="<?= $suspend_sale ? $suspend_sale->shipping : ($old_sale ? $old_sale->shipping :  '0'); ?>" id="posshipping">
                                    <input type="hidden" name="rpaidby" id="rpaidby" value="cash" style="display: none;" />
                                    <input type="hidden" name="total_items" id="total_items" value="0" style="display: none;" />
                                    <input type="submit" id="submit_sale" value="Submit Sale" style="display: none;" />
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <style>
                            div.scrollmenu {
                                /*background-color: #333;*/
                                width: 100%;
                                height: 35px;
                                overflow: auto;
                                white-space: nowrap;
                            }
                            div.scrollmenu li {
                                display: inline-block;
                                color: white;
                                /*
                              text-align: center;*/
                                /*padding: 14px;*/
                                text-decoration: none;
                            }
                            div.scrollmenu li:hover {
                                background-color: #777;
                            }
                        </style>
                        <div id="cp">
                            <div id="cpinner">
                                <?php if($pos_settings->show_categories == 0){ ?> 
                                <div class="categories_div">
                                    <nav aria-label="Page navigation example">
                                        <ul style="list-style-type: none;">
                                            <div>
                                                <li class="page-item" id='pagination'></li>
                                                <center>
                                                    <li style="margin: 0 5px;" id="postsList" class="page-item"></li>
                                                </center>
                                            </div>
                                        </ul>
                                    </nav>
                                </div>
                                <?php } ?>
                                 <div class="row">
                                     <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo form_input('pos_search_product', '', 'class="form-control input-tip" placeholder="' . lang('Please search product code or name') . '" id="pos_search_product"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php echo form_input('pos_search_category', '', 'class="form-control input-tip" placeholder="' . lang('Please search category code or name') . '" id=""'); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="quick-menu">
                                    <div id="proContainer">
                                        <div id="ajaxproducts">
                                            <div id="item-list">
                                                <?php echo $products; ?>
                                            </div>
                                            <div class="btn-group btn-group-justified pos-grid-nav">
                                                <div class="btn-group">
                                                    <button style="z-index:10002;" class="btn btn-primary pos-tip" title="<?= lang('previous') ?>" type="button" id="previous">
                                                        <i class="fa fa-chevron-left"></i>
                                                    </button>
                                                </div>
                                                <?php if ($Owner || $Admin || $GP['sales-add_gift_card']) {?>
                                                <div class="btn-group">
                                                    <button style="z-index:10003;" class="btn btn-primary pos-tip" type="button" id="sellGiftCard" title="<?=lang('sell_gift_card')?>">
                                                        <i class="fa fa-credit-card" id="addIcon"></i> <?=lang('sell_gift_card')?>
                                                    </button>
                                                </div>
                                                <?php } ?>
                                                <div class="btn-group">
                                                    <button style="z-index:10004;" class="btn btn-primary pos-tip" title="<?= lang('next') ?>" type="button" id="next">
                                                        <i class="fa fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php if($pos_settings->show_categories == 1){ ?>
    <div class="rotate btn-cat-con">
        <button type="button" id="open-brands" class="btn btn-info open-brands"><?= lang('brands'); ?></button>
        <button type="button" id="open-subcategory" class="btn btn-warning open-subcategory"><?= lang('subcategories'); ?></button>
        <button type="button" id="open-category" class="btn btn-primary open-category"><?= lang('categories'); ?></button>
    </div>
    <div id="brands-slider">
        <div id="brands-list">
            <?php
            foreach ($brands as $brand) {
                echo "<button id=\"brand-" . $brand->id . "\" type=\"button\" value='" . $brand->id . "' class=\"btn-prni brand\" ><img src=\"assets/uploads/thumbs/" . ($brand->image ? $brand->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $brand->name . "</span></button>";
            }
            ?>
        </div>
    </div>
    <div id="category-slider">
        <div id="category-list">
            <?php
            foreach ($categories as $category) {
                echo "<button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
            ?>
        </div>
    </div>
    <div id="subcategory-slider">
        <div id="subcategory-list">
            <?php
            if (!empty($subcategories)) {
                foreach ($subcategories as $category) {
                    echo "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
                }
            }
            ?>
        </div>
    </div>
   <?php  } ?>
    <div class="modal" id="comboModal" tabindex="-1" role="dialog" aria-labelledby="comboModalLabel" aria-hidden="true" >
        <div class="modal-dialog" style="width:50%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                        <i class="fa fa-2x">&times;</i></span>
                        <span class="sr-only"><?=lang('close');?></span>
                    </button>
                    <h4 class="modal-title" id="comboModalLabel"></h4>
                </div>
                <div class="modal-body" style="margin-top:-15px !important;">
                    <label class="table-label"><?= lang("combo_products"); ?></label>
                    <table id="comboProduct" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                        <thead>
                            <tr>
                                <th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                <?php if ($Settings->qty_operation) { ?>
                                    <th><?= lang('width') ?></th>
                                    <th><?= lang('height') ?></th>
                                <?php } ?>
                                <th><?= lang('quantity') ?></th>
                                <th><?= lang('price') ?></th>
                                <th width="3%">
                                    <a id="add_comboProduct" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editCombo"><?=lang('submit')?></button>
                </div>
            </div>
        </div>
    </div>
     <div class="modal" id="addOnModal" tabindex="-1" role="dialog" aria-labelledby="addOnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="width: 780px;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                        <i class="fa fa-2x">&times;</i></span>
                        <span class="sr-only"><?= lang('close'); ?></span>
                    </button>
                    <h4 class="modal-title" id="addOnModalLabel"></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <div class="form-group">
                        <?= lang('Choice of Topping', 'add_on_items'); ?>    
                        <div class="addOn-box" style="background-color: ghostwhite; width: 100%; height: 550px; overflow: auto; overflow-y: scroll;"></div>
                    </div>
                    <input type="hidden" id="addon_model_row_id" value="" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addOn_smb"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
     aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                                class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                    <h4 class="modal-title" id="payModalLabel"><?=lang('finalize_sale');?></h4>
                </div>
                <div class="modal-body" id="payment_content">
                    <div class="row">
                        <div class="col-md-10 col-sm-9">
                            <div class="form-group">
                                <div class="row">
                                    
                                    <div class="col-sm-6">
                                    <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                        <div class="form-group">
                                            <?=lang("biller", "biller");?>
                                            <?php
                                                foreach ($billers as $biller) {
                                                    $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                                    $bl[$biller->id] = $btest;
                                                    $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                                    if ($biller->id == $pos_settings->default_biller) {
                                                        $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                                    }
                                                }
                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                            ?>
                                        </div>
                                    <?php } else {
                                            $biller_input = array(
                                                'type' => 'hidden',
                                                'name' => 'biller',
                                                'id' => 'posbiller',
                                                'value' => $this->session->userdata('biller_id'),
                                            );
                                            echo form_input($biller_input);
                                            foreach ($billers as $biller) {
                                                $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                                $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                                if ($biller->id == $this->session->userdata('biller_id')) {
                                                    $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                                }
                                            }
                                        }
                                    ?>
                                    </div>
                                       
                                      <?php if ($Settings->sale_man) { ?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <?= lang("saleman", "saleman"); ?>
                                                    <select name="saleman" id="saleman" class="form-control">
                                                        <?php
                                                        echo '<option value="">' .lang('selected'). '</option>';
                                                        foreach ($agencies as $agency) {
                                                            if ($this->session->userdata('username') == $agency->username) {
                                                                echo '<option value="' . $this->session->userdata('user_id') . '" selected>' . $agency->first_name . ' ' . $agency->last_name . '</option>';
                                                            } else {
                                                                echo '<option value="' . $agency->id . '">' . $agency->first_name . ' ' . $agency->last_name . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?=lang("project", "project");?>
                                            <?php
                                                foreach ($projects as $project) { 
                                                    $pro[$project->project_id] = $project->project_name;  
                                                }
                                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'class="form-control" id="posproject" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <?=form_textarea('sale_note', '', 'id="sale_note" class="form-control kb-text skip" style="height: 50px;" placeholder="' . lang('sale_note') . '" maxlength="250"');?>
                                    </div>
                                    <div class="col-sm-6">
                                        <?=form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 50px;" placeholder="' . lang('staff_note') . '" maxlength="250"');?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfir"></div>
                            <div id="payments">
                            <div class="well well-sm well_1">
                                <table class="table table-bordered table-condensed table-striped" style="font-size: 1.2em; font-weight: bold; margin-bottom: 0;">
                                    <thead>
                                        <tr>
                                            <th width="50%" height="45" class="text-left bold"><?= lang("currency"); ?></th>
                                            <?php
                                                $allCurrencies = $this->site->getAllCurrencies();
                                                $column = (2 + count($allCurrencies));
                                                foreach ($allCurrencies as $currency){ ?>
                                                    <th  class="text-center"><?=$currency->code?></th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <tr>
                                            <td><?=lang("total_payable");?></td>
                                            <?php 
                                                foreach ($allCurrencies as $currency){
                                                    $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                                ?>
                                                    <td class="text-right paid_<?=$currency->code?>">
                                                        <span class="total_payable twt_<?=$currency->code?>" base_rate="1" rate="<?=$currency->rate?>" id="total_payable">0</span>
                                                    </td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td><?= lang("paid")?></td>
                                            <?php 
                                            foreach ($allCurrencies as $currency){ 
                                                $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                            ?>
                                                    <td class="text-right">
                                                        <input name="camount[]" base_rate="<?= $base_currency->rate ?>" rate="<?=$currency->rate?>" type="text" class="form-control camount <?=($currency->code==$base_currency->code?"base_amount":"");?>" class="text-right"/>                                                
                                                    </td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td width="50%" height="45" class="text-left bold"><?= lang("balance"); ?></td>                                 
                                            <?php
                                                $count_currency = 0;
                                                foreach ($allCurrencies as $currency){
                                                    $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                                    $count_currency++;
                                            ?>
                                                    <td class="text-right"><span class="balance_1" base_rate="1" rate="<?=$currency->rate?>" id="balance_1">0</span></td>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>
                                    <div class="payment">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="loan_1" style="display:none;background: #BD7B52;padding: 8px 8px 1px 8px;color: #ffffff;">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label"><?=lang('enter_rate')?><label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <input name="interest_rate[]" readonly value="<?= $pos_settings->interest_rate?>%" type="text" id="interest_rate_1" class="form-control" required="required" placeholder="<?=lang('interest_rate')?>"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label"><?=lang('enter_months')?><label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <input name="months[]" type="text" id="months_1" class="form-control" required="required" placeholder="<?=lang('months')?>"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group gc_1" style="display: none;">
                                                    <?=lang("gift_card_no", "gift_card_no_1");?>
                                                    <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1" class="pa form-control kb-pad gift_card_no"/>
                                                    <div id="gc_details_1"></div>
                                                </div>
                                                <div class="pcc_1" style="display:none;">
                                                    <div class="form-group">
                                                        <input type="text" id="swipe_1" class="form-control swipe" placeholder="<?=lang('swipe')?>"/>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input name="cc_no[]" type="text" id="pcc_no_1" class="form-control" placeholder="<?=lang('cc_no')?>"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input name="cc_holer[]" type="text" id="pcc_holder_1" class="form-control" placeholder="<?=lang('cc_holder')?>"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <select name="cc_type[]" id="pcc_type_1" class="form-control pcc_type" placeholder="<?=lang('card_type')?>">
                                                                    <option value="Visa"><?=lang("Visa");?></option>
                                                                    <option value="MasterCard"><?=lang("MasterCard");?></option>
                                                                    <option value="Amex"><?=lang("Amex");?></option>
                                                                    <option value="Discover"><?=lang("Discover");?></option>
                                                                    <option value="UnionPay"><?=lang("UnionPay");?></option>
                                                                    <option value="JCB"><?=lang("JCB");?></option>
                                                                </select>
                                                                <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?=lang('card_type')?>" />-->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <input name="cc_month[]" type="text" id="pcc_month_1" class="form-control" placeholder="<?=lang('month')?>"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <input name="cc_year" type="text" id="pcc_year_1" class="form-control" placeholder="<?=lang('year')?>"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <input name="cc_cvv2" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?=lang('cvv2')?>"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?=lang("paying_by", "paid_by_1");?>
                                                            <select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                                <?= $this->bpas->paid_opts(); ?>
                                                                <?php //=$pos_settings->paypal_pro ? '<option value="ppp">' . lang("paypal_pro") . '</option>' : '';?>
                                                                <?php //=$pos_settings->stripe ? '<option value="stripe">' . lang("stripe") . '</option>' : '';?>
                                                                <?php //=$pos_settings->authorize ? '<option value="authorize">' . lang("authorize") . '</option>' : '';?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="pcheque_1" style="display:none;">
                                                            <div class="form-group"><?=lang("cheque_no", "cheque_no_1");?>
                                                                <input name="cheque_no[]" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <?= lang('payment_note', 'payment_note');?>
                                                            <textarea name="payment_note[]" id="payment_note_1" class="pa form-control kb-text payment_note"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="multi-payment"></div>
                            <button type="button" class="btn btn-primary col-md-12 addButton"><i class="fa fa-plus"></i> <?= lang('add_more_payments')?></button>
                            <div style="clear:both; height:15px;"></div>
                            <div class="font16">
                                <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
                                    <tbody>
                                        <tr>
                                            <td width="25%"><?=lang("total_items");?></td>
                                            <td width="25%" class="text-right"><span id="item_count">0.00</span></td>
                                            <td><?=lang("total_paying");?></td>
                                            <td class="text-right"><span id="total_paying">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><?=lang("balance");?></td>
                                            <td>
                                                <div class="paid_kh">
                                                    <span id="balance">0.00</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="paid_en">
                                                <span id="balance_kh">0.00</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="paid_bat">
                                                <span id="balance_bat">0.00</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-3 text-center">
                            <span style="font-size: 1.2em; font-weight: bold;"><?=lang('quick_cash');?></span>
                            <div class="btn-group btn-group-vertical">
                                <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable">0.00</button>
                                <!-- id="quick-cash-payable" -->
                                <?php foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                    echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . $cash_note_amount . '</button>';
                                } ?>
                                <button type="button" class="btn btn-lg btn-danger" id="clear-cash-notes"><?=lang('clear');?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-block btn-lg btn-primary" id="submit-sale"><?=lang('submit');?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                            <i class="fa fa-2x">&times;</i></span>
                        <span class="sr-only"><?= lang('close'); ?></span>
                    </button>
                    <h4 class="modal-title" id="cmModalLabel"></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    
                     <?php if ($Settings->comment_option == 0) { ?>
                            <div class="form-group">
                                <?= lang('comment', 'icomment'); ?>
                                <?= form_textarea('comment', '', 'class="form-control" id="icomment" style="height:80px;"'); ?>
                            </div>
                        <?php } elseif ($Settings->comment_option == 1)
                        { ?>
                            <div class="form-group">
                                <label for="comment" class="control-label"><?= lang('comment') ?></label>
                                    <?php 
                                        $get_fields = $this->site->getcustomfield('pocomment');
                                        //$field ['']= 'none';
                                        if (!empty($get_fields)) {
                                            foreach ($get_fields as $field_id) {
                                                $field[$field_id->name] = $field_id->name;
                                            }
                                        }
                                    // echo form_dropdown('comment',$field,'', 'class="form-control" id="icomment"'. '"  multiple="multiple"');
                                        echo form_dropdown('subcomment',$field,'', 'class="form-control" id="isubcomment" multiple="multiple"'); 

                                        echo form_textarea('comment', ' ', 'class="form-control" id="icomment" style="height:100px;"');
                                    ?>
                            </div>
                        <?php } ?>
                    <div class="form-group hide">
                        <?= lang('ordered', 'iordered'); ?>
                        <?php
                        $opts = array(0 => lang('no'), 1 => lang('yes'));
                        ?>
                        <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                    </div>
                    <input type="hidden" id="irow_id" value="" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editComment"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="printorderModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                            <i class="fa fa-2x">&times;</i></span>
                        <span class="sr-only"><?= lang('close'); ?></span>
                    </button>
                    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                        <i class="fa fa-print"></i> Print </button>
                    <h4 class="modal-title" id="cmModalLabel"></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <span id="head_print_order"></span>
                    <table width="100%" border="1">
                        <tr>
                            <th>No</th>
                            <th>Description</th>
                            <th>QTY</th>
                            <th>Noted</th>
                        </tr>
                        <tr>
                            <td>a</td>
                            <td>s</td>
                            <td>c</td>
                            <td>c</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                    <h4 class="modal-title" id="prModalLabel"></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <form class="form-horizontal" role="form">
                        <?php if ($Settings->tax1) {
                        ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                                <div class="col-sm-8">
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($Settings->product_serial) { ?>
                            <div class="form-group">
                                <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control kb-text" id="pserial">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pquantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                            <div class="col-sm-8">
                                <div id="punits-div"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                            <div class="col-sm-8">
                                <div id="poptions-div"></div>
                            </div>
                        </div>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount')) && $Settings->discount_option == 0) { ?>
                            <div class="form-group">
                                <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control kb-pad" data="" id="pdiscount">
                                </div>
                            </div>
                        <?php }elseif ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount')) && $Settings->discount_option == 1) { ?>
                            <div class="form-group">
                                <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                                <div class="col-sm-8">
                                    <?php 
                                    $get_fields = $this->site->getcustomfield('discount');
                                    $field_product['']= '0';
                                    if (!empty($get_fields)) {
                                        foreach ($get_fields as $field_id) {
                                            $field_product[$field_id->name] = $field_id->name;
                                        }
                                    }
                                    echo form_dropdown('pdiscount',$field_product,'', 'class="form-control" id="pdiscount"'); 
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                        <!--<div class="col-sm-8">
                                    <input type="text" class="form-control kb-pad" data="" id="pdiscount">
                                </div>-->
                        <div class="form-group">
                            <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                            </div>
                        </div>
                        <?php if($Settings->sale_man && $Settings->commission){ ?>
                            <label for="pdescription" class="col-sm-4 control-label"><?= lang('saleman') ?></label>
                            <div class="col-sm-8">
                                <div class="form-group">
                                     <select id="saleman_item" name="saleman_item" class="form-control input-tip">
                                        <?php
                                        echo '<option value="">----------</option>';
                                        if($this->session->userdata('group_id') == $Settings->group_saleman_id){
                                            echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
                                        } else {
                                            foreach($salemans as $agency){
                                                echo '<option value="'.$agency->id.'">'.$agency->first_name.' '.$agency->last_name.'</option>';
                                            }
                                        }
                                        ?>
                                    </select> 
                                </div>
                            </div>
                        <?php } ?>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                                <th style="width:25%;"><span id="net_price"></span></th>
                                <th style="width:25%;"><?= lang('product_tax'); ?></th>
                                <th style="width:25%;"><span id="pro_tax"></span></th>
                            </tr>
                        </table>
                        <input type="hidden" id="punit_price" value="" />
                        <input type="hidden" id="old_tax" value="" />
                        <input type="hidden" id="old_qty" value="" />
                        <input type="hidden" id="old_price" value="" />
                        <input type="hidden" id="row_id" value="" />
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?= lang('enter_info'); ?></p>
                    <div class="alert alert-danger gcerror-con" style="display: none;">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <span id="gcerror"></span>
                    </div>
                    <div class="form-group">
                        <?= lang("card_no", "gccard_no"); ?> *
                        <div class="input-group">
                            <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                <a href="#" id="genNo"><i class="fa fa-cogs"></i></a>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname" />
                    <div class="form-group">
                        <?= lang("value", "gcvalue"); ?> *
                        <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("price", "gcprice"); ?> *
                        <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("customer", "gccustomer"); ?>
                        <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("expiry_date", "gcexpiry"); ?>
                        <?php echo form_input('gcexpiry', $this->bpas->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                    <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" id="mcode">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" id="mname">
                            </div>
                        </div>
                        <?php if ($Settings->tax1) {
                        ?>
                            <div class="form-group">
                                <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>
                                <div class="col-sm-8">
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php }
                        ?>
                        <div class="form-group">
                            <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mquantity">
                            </div>
                        </div>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                            <div class="form-group">
                                <label for="mdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control kb-pad" id="mdiscount">
                                </div>
                            </div>
                        <?php }
                        ?>
                        <div class="form-group">
                            <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mprice">
                            </div>
                        </div>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                                <th style="width:25%;"><span id="mnet_price"></span></th>
                                <th style="width:25%;"><?= lang('product_tax'); ?></th>
                                <th style="width:25%;"><span id="mpro_tax"></span></th>
                            </tr>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="sckModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                            <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span>
                    </button>
                    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                        <i class="fa fa-print"></i> <?= lang('print'); ?>
                    </button>
                    <h4 class="modal-title" id="mModalLabel"><?= lang('shortcut_keys') ?></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <table class="table table-bordered table-striped table-condensed table-hover" style="margin-bottom: 0px;">
                        <thead>
                            <tr>
                                <th><?= lang('shortcut_keys') ?></th>
                                <th><?= lang('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $pos_settings->focus_add_item ?></td>
                                <td><?= lang('focus_add_item') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->add_manual_product ?></td>
                                <td><?= lang('add_manual_product') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->customer_selection ?></td>
                                <td><?= lang('customer_selection') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->add_customer ?></td>
                                <td><?= lang('add_customer') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->toggle_category_slider ?></td>
                                <td><?= lang('toggle_category_slider') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->toggle_subcategory_slider ?></td>
                                <td><?= lang('toggle_subcategory_slider') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->cancel_sale ?></td>
                                <td><?= lang('cancel_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->suspend_sale ?></td>
                                <td><?= lang('suspend_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->print_items_list ?></td>
                                <td><?= lang('print_items_list') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->finalize_sale ?></td>
                                <td><?= lang('finalize_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->today_sale ?></td>
                                <td><?= lang('today_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->open_hold_bills ?></td>
                                <td><?= lang('open_hold_bills') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->close_register ?></td>
                                <td><?= lang('close_register') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="fa fa-2x">&times;</i>
                    </button>
                    <h4 class="modal-title" id="dsModalLabel"><?= lang('edit_order_discount'); ?></h4>
                </div>
                        <!-- <div class="modal-body">
                            <div class="form-group">
                                <= lang("order_discount", "order_discount_input"); ?>
                                <php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                            </div>
                        </div> -->
                            <div class="modal-body">
                        <!--    <div class="form-group hide">
                                <= lang("order_discount", "order_discount_input"); ?>
                                <php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                            </div> -->
                   <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount')) && $Settings->discount_option == 0) { ?>
                            <div class="form-group">
                                <label for="order_discount_input" class="control-label"><?= lang('order_discount') ?></label>
                                <?php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                            
                            </div>
                        <?php }elseif ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount')) && $Settings->discount_option == 1) { ?>
                            <div class="form-group">
                                <label for="order_discount_input" class="control-label"><?= lang('order_discount') ?></label>
                             
                                    <?php 
                                    $get_fields = $this->site->getcustomfield('discount');
                                    $field_discount['']= '0';
                                    if (!empty($get_fields)) {
                                        foreach ($get_fields as $field_id) {
                                            $field_discount[$field_id->name] = $field_id->name;
                                        }
                                    }
                                    echo form_dropdown('order_discount_input',$field_discount,'', 'class="form-control kb-pad" id="order_discount_input"'); 
                                    ?>
                                
                            </div>
                        <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" id="updateOrderDiscount" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="sModal" tabindex="-1" role="dialog" aria-labelledby="sModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="fa fa-2x">&times;</i>
                    </button>
                    <h4 class="modal-title" id="sModalLabel"><?= lang('shipping'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <?= lang("shipping", "shipping_input"); ?>
                        <?php echo form_input('shipping_input', '', 'class="form-control kb-pad" id="shipping_input"'); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="updateShipping" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="txModal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="txModalLabel"><?= lang('edit_order_tax'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <?= lang("order_tax", "order_tax_input"); ?>
                        <?php
                        $tr[""] = "";
                        foreach ($tax_rates as $tax) {
                            $tr[$tax->id] = $tax->name;
                        }
                        echo form_dropdown('order_tax_input', $tr, "", 'id="order_tax_input" class="form-control pos-input-tip" style="width:100%;"');
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="updateOrderTax" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </div>
        </div>
    </div>
 <div class="modal fade in" id="splitItem" tabindex="-1" role="dialog" aria-labelledby="changeg_susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="changeg_susModalLabel"><?=lang('split_bill');?></h4>
            </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
            echo admin_form_open("pos/split_bill", $attrib); ?>
            <div class="modal-body">
            <div class="form-group">
                <label for="name">Note</label>
                <input type="text" class="form-control" name="note_name" placeholder="Max 5 Charater" maxlength="5" required>
                <input name="suspend_note" type="text" id ="suspend_note" class="hide" value="<?php echo $room_n; ?>">
            </div>
            <div class="form-group">
                <input name="warehouse" type="text" id ="warehouse" class="hide" value="<?php echo isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse;?>">
            </div>
            <div class="form-group">
                <input name="customer" type="text" id="customer" class="hide" value="<?php echo isset($_POST['customer']) ? $_POST['customer'] : $suspend_sale->customer_id; ?>">
            </div> 
            <div class="form-group">
                <input name="suspend_id" type="text" id="suspend_id" class="hide" value="<?php echo isset($suspend_sale->id) ? $suspend_sale->id : $suspend_sale->id; ?>">
            </div>
            <div class="modal-body">
                <!-- <p><?=lang('Please select items that you want to split.');?></p> -->
                <div class="form-group hide">
                    <?=lang("products", "select");?>
                    <?php 
                    if($inv_items){
                        $total_items = 0;
                        foreach ($inv_items as $noted) {
                            // for ($r = 0; $r < $noted->unit_quantity; $r++) {
                               $total_items +=  $noted->unit_quantity;
                            // }
                        }
                    }
                    ?>
                </div>
                <div class="form-group pull-right">
                        <p id="results">Total Number of Items Selected = <p> 
                </div>

                <div class="form-group " >
                    <?=lang("please_select_items", "select");?>
                    <?php 
                    if($inv_items){
                        krsort($inv_items);
                        foreach ($inv_items as $noted) {
                            $addon_item_split = "<span style='font-size: 13px; margin-top: -18px; margin-left: 45px; display: block;'>";
                            if($inv_addon_items){
                                foreach ($inv_addon_items as $inv_addon_item) {
                                    if($inv_addon_item->suspend_item_id == $noted->id){
                                        $addon_item_split .= "<br>> " . $inv_addon_item->product_name;
                                    }
                                }
                                $addon_item_split .= "</span>";
                            }

                            for ($r = 0; $r < $noted->unit_quantity; $r++) {
                                echo '<div style="font-size:17px;border: 1px solid #ddd;"><label style = "margin:15px;"><input name="split_id[]" class="checkit" type="checkbox" id="split_id" style="width:20px;height:20px;" value="'.$noted->product_id.'-space_explode-'.$noted->product_code .'-space_explode-'.$noted->id .'"> ';
                                if(!empty($noted->comment)){
                                    echo ' '. $noted->product_name . ' - '. $noted->comment . $addon_item_split .'</label></div>';
                                } else {
                                    echo ' '. $noted->product_name . $addon_item_split . '</label></div>';
                                }
                            }
                        }
                    }
                    ?>
                </div>

                <?php if($suspend_note_tmp){?>
                <div class="form-group hide">
                    <?=lang("suspended_note", "select");?>
                <select id="sus_tmp" name = "sus_tmp" class="form-control tip">
                    <?php 
                        foreach ($suspend_note_tmp as $noted) {
                            echo '<option value="'.$noted->suspend_note.'">';
                            echo ''. $noted->name .'';
                            echo '</option>';
                        }
                    ?>
                </select>
                </div>
                    <?php }?>
            <div class="form-group hide">
                <label for="option_bill"><?php echo lang('updat_bill'); ?></label>
                <select id="option_bill" name = "option_bill" class="form-control tip">
                     <option value="1"><?= lang('create') ?></option>
                     <option value="0"><?= lang('update') ?></option>
                   
                </select>
            </div>
            <div class="form-group hide">
                <label for="percent"><?php echo lang("discount_amount"); ?></label>
                <?php echo form_input('amount', '', 'class="form-control" id="percent"'); ?>
            </div>
        </div>
            </div>

        <div class="modal-footer">
         <!-- <input type="submit" id ="btn" value="Submit" disabled> -->
         <?php if(isset($room_n) && $room_tmp == 0){?>
            <?= form_submit('add_room', lang('split_bill'), 'id="sendNewSms" disabled="true" class="btn btn-primary"'); ?>
            <?php } ?>
        </div>
    </div>
    <?= form_close(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
showChecked();
function showChecked(){
  document.getElementById("results").textContent = "Total Number of Items Selected = " + document.querySelectorAll("input[id=split_id]:checked").length+ '/' + '<?php echo isset($total_items) ? $total_items : ''; ?>';
}
document.querySelectorAll("input[id=split_id]").forEach(i=>{
 i.onclick = function(){
  showChecked();
 }
});
$(document).ready(function() {
//   $('#select').click(function() {
    var checkboxes = $('input:checkbox:not(":checked")').length;
    // alert(checkboxes);
  })
// });
var check_opt = document.getElementsByClassName('checkit');
var btn = document.getElementById('sendNewSms');
function detect() {
  btn.disabled = true;
  for (var index = 0; index <  check_opt.length; ++index) {
    if (check_opt[index].checked == true) {
        if($( ".checkit:checked" ).length != check_opt.length){
            btn.disabled = false;
        }
    }
   
  }
}
selection_items();
function selection_items() {
  for (var i = 0; i < check_opt.length; i++) {
    //   alert(check_opt[i]);
    check_opt[i].addEventListener('click', detect)
  }
}
//change items
showItems();
function showItems(){
    document.getElementById("results").textContent = "Total Number of Items Selected = " + document.querySelectorAll("input[id=change_items_check]:checked").length + '/' + '<?php echo isset($total_items) ? $total_items : ""; ?>';
}
document.querySelectorAll("input[id=change_items_check]").forEach(i=>{
    i.onclick = function(){
        showItems();
    }
});
// $(document).ready(function() {
// //   $('#select').click(function() {
//     var checkboxes = $('input:checkbox:not(":checked")').length;
//     // alert(checkboxes);
//   })
// });
var change_option = document.getElementsByClassName('checkitem');
var button = document.getElementById('SendItems');
function detectitems() {
  button.disabled = true;
  for (var index = 0; index <  change_option.length; ++index) {
    if(change_option[index].checked == true) {
        if($( ".checkitem:checked" ).length != change_option.length){
            button.disabled = false;
        }
    }
   
  }
}
change_items();
function change_items() {
  for (var i = 0; i < change_option.length; i++) {
        change_option[i].addEventListener('click', detectitems)
  }
}
</script>
    <div class="modal fade in" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="susModalLabel"><?= lang('suspend_sale'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?= lang('type_reference_note'); ?></p>
                    <div class="form-group">
                        <?php
                        if ($pos_settings->pos_type == "pos") {
                            echo form_input('reference_note', (!empty($reference_note) ? $reference_note : ''), 'class="form-control kb-text" id="reference_note"');
                        /*
                            echo '<select class="form-control" name="reference_note" id="reference_note" required>';
                            echo '<option value="">Please choose Table</option>';
                            foreach ($suspend_note as $noted) {
                                echo '<option value="'.$noted->name.'">'.$noted->name.'</option>';

                            }
                            echo '<select>'; */
                        } else {
                            echo lang("reference_note", "reference_note");
                            //= form_input('reference_note', (!empty($reference_note) ? $reference_note : ''), 'class="form-control kb-text" id="reference_note"');
                            echo form_input(
                                array(
                                    'name' => "reference_note",
                                    'value' => (!empty($reference_note) ? $reference_note : ''),
                                    'class' => 'form-control kb-text',
                                    'id' => 'reference_note'
                                )
                            );
                        }
                        ?>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" id="suspend_sale" class="btn btn-primary"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div id="order_tbl"><span id="order_span"></span>
        <table id="order-table" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
    </div>
    <div id="bill_tbl"><span id="bill_span"></span>
        <table id="bill-table" width="100%" class="prT table table-striped" style="margin-bottom:0;"></table>
        <table id="bill-total-table" class="prT table" style="margin-bottom:0;" width="100%">
        </table>
        <span id="bill_footer"></span>
    </div>
    <div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
    <div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
    <div id="modal-loading" style="display: none;">
        <div class="blackbg"></div>
        <div class="loader"></div>
    </div>
    <input type="hidden" id="base_url" value="<?= base_url(); ?>">
    <?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>
    <script type="text/javascript">
        $('#posproject').change(function(){
          $('#project_1').val($(this).val());
        });
        $('#saleman').change(function(){
          $('#saleman_1').val($(this).val());
        });
        
        $('#delivery_by').change(function(){
          $('#delivery_by_1').val($(this).val());
        });

        /*$("#poscustomer").on('change', function (event) {
            location.reload();
        });*/

        $('.amount,.amount_kh,#amount_2').keypress(function(event) {
            
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });

        $(".amount_").keydown(function(event) {
            if (event.shiftKey == true) {
                event.preventDefault();
            }

            if ((event.keyCode >= 48 && event.keyCode <= 57) ||
                (event.keyCode >= 96 && event.keyCode <= 105) ||
                event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
                event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

            } else {
                event.preventDefault();
            }

            if ($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
                event.preventDefault();
            //if a decimal has been added, disable the "."-button
        });

        var site = <?= json_encode(array('url' => base_url(), 'base_url' => admin_url('/'), 'assets' => $assets, 'settings' => $Settings, 'dateFormats' => $dateFormats)) ?>,
            pos_settings = <?= json_encode($pos_settings); ?>;
            permission = <?= json_encode($permission); ?>;
        var lang = {
            unexpected_value: '<?= lang('unexpected_value'); ?>',
            select_above: '<?= lang('select_above'); ?>',
            r_u_sure: '<?= lang('r_u_sure'); ?>',
            bill: '<?= lang('bill'); ?>',
            order: '<?= lang('order'); ?>',
            total: '<?= lang('total'); ?>',
            items: '<?= lang('items'); ?>',
            discount: '<?= lang('discount'); ?>',
            order_tax: '<?= lang('order_tax'); ?>',
            grand_total: '<?= lang('grand_total'); ?>',
            total_payable: '<?= lang('total_payable'); ?>',
            rounding: '<?= lang('rounding'); ?>',
            merchant_copy: '<?= lang('merchant_copy'); ?>'
        };
    </script>
    <script type="text/javascript">
        var product_variant = 0,
            shipping = 0,
            p_page = 0,
            per_page = 0,
            tcp = "<?= $tcp ?>",
            pro_limit = <?= $pos_settings->pro_limit; ?>,
            brand_id = 0,
            obrand_id = 0,
            cat_id = "<?= $pos_settings->default_category ?>",
            ocat_id = "<?= $pos_settings->default_category ?>",
            sub_cat_id = 0,
            osub_cat_id,
            count = 1,
            an = 1,
            DT = <?= $Settings->default_tax_rate ?>,
            product_tax = 0,
            invoice_tax = 0,
            product_discount = 0,
            order_discount = 0,
            total_discount = 0,
            total = 0,
            total_paid = 0,
            grand_total = 0,
            KB = <?= $pos_settings->keyboard ?>,
            tax_rates = <?php echo json_encode($tax_rates); ?>;
        var protect_delete = <?php if (!$Owner && !$Admin) {
                                    echo $pos_settings->pin_code ? '1' : '0';
                                } else {
                                    echo '0';
                                } ?>,
            billers = <?= json_encode($posbillers); ?>,
            biller = <?= json_encode($posbiller); ?>;
        var username = '<?= $this->session->userdata('username'); ?>',
            user_id = '<?= $this->session->userdata('user_id'); ?>',
            order_data = '',
            bill_data = '';
        var user_detail = "user addresss";

        function widthFunctions(e) {
            var wh = $(window).height(),
                lth = $('#left-top').height(),
                lbh = $('#left-bottom').height();
            $('#item-list').css("height", wh - 250);
            $('#item-list').css("min-height", 440);
            $('#left-middle').css("height", wh - lth - lbh - 102);
            $('#left-middle').css("min-height", 200);
            $('#product-list').css("height", wh - lth - lbh - 107);
            $('#product-list').css("min-height", 200);
        }
        $(window).bind("resize", widthFunctions);
        $(document).ready(function() {
            var kh_rate = parseFloat(localStorage.getItem('exchange_kh'));
            var bat_rate = parseFloat(localStorage.getItem('exchange_bat_out'));
            var riel_rate = parseFloat(localStorage.getItem('riel_rate'));
            var bath_rate = parseFloat(localStorage.getItem('bath_rate'));

            $("#button_en").click(function() {
                $(".paid_en").removeClass("col_disable");
                $(this).css("background", "#F0AD4E");
                $('#amount_2').focus();
                $('#kh_currenncy').val('');
                $('#en_currenncy').val('usd');
                /////
                $(".paid_kh").addClass("col_disable");
                //  $("#amount_val_1").val('');
                //  $("#balance_amount_1").val('');

                document.getElementById('amount_1').setAttribute('disabled', 'disabled');
                document.getElementById('amount_2').removeAttribute('disabled');
            });
            $("#button_kh").click(function() {
                $(".paid_kh").removeClass("col_disable");
                $('#amount_1').focus();
                $('#kh_currenncy').val('khm');
                $('#en_currenncy').val('');
                /////
                $(".paid_en").addClass("col_disable");
                document.getElementById('amount_1').removeAttribute('disabled');
                document.getElementById('amount_2').setAttribute('disabled', 'disabled');
            });
            $('#view-customer').click(function() {
                $('#myModal').modal({
                    remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()
                });
                $('#myModal').modal('show');
            });
            $('textarea').keydown(function(e) {
                if (e.which == 13) {
                    var s = $(this).val();
                    $(this).val(s + '\n').focus();
                    e.preventDefault();
                    return false;
                }
            });
      
            <?php if ($sid) { ?>
                localStorage.setItem('positems', JSON.stringify(<?= $items ?>));
            <?php } ?>
            <?php if ($oid) { ?>
                localStorage.setItem('positems', JSON.stringify(<?= $items ?>));
            <?php } ?>
                localStorage.setItem('group_price', JSON.stringify(<?= $group_price; ?>));
            <?php if ($this->session->userdata('remove_posls')) { ?>
                if (localStorage.getItem('positems')) {
                    localStorage.removeItem('positems');
                }
                if (localStorage.getItem('group_price')) {
                    localStorage.removeItem('group_price');
                }
                if (localStorage.getItem('posdiscount')) {
                    localStorage.removeItem('posdiscount');
                }
                if (localStorage.getItem('postax2')) {
                    localStorage.removeItem('postax2');
                }
                if (localStorage.getItem('posshipping')) {
                    localStorage.removeItem('posshipping');
                }
                if (localStorage.getItem('poswarehouse')) {
                    localStorage.removeItem('poswarehouse');
                }
                if (localStorage.getItem('posnote')) {
                    localStorage.removeItem('posnote');
                }
                if (localStorage.getItem('poscustomer')) {
                    localStorage.removeItem('poscustomer');
                }
                if (localStorage.getItem('posbiller')) {
                    localStorage.removeItem('posbiller');
                }
                if (localStorage.getItem('poscurrency')) {
                    localStorage.removeItem('poscurrency');
                }
                if (localStorage.getItem('posnote')) {
                    localStorage.removeItem('posnote');
                }
                if (localStorage.getItem('staffnote')) {
                    localStorage.removeItem('staffnote');
                }
            <?php $this->bpas->unset_data('remove_posls');
            }
            ?>
            widthFunctions();
            <?php if ($suspend_sale) { ?>
                localStorage.setItem('postax2', '<?= $suspend_sale->order_tax_id; ?>');
                localStorage.setItem('posdiscount', '<?= $suspend_sale->order_discount_id; ?>');
                localStorage.setItem('poswarehouse', '<?= $suspend_sale->warehouse_id; ?>');
                localStorage.setItem('poscustomer', '<?= $suspend_sale->customer_id; ?>');
                localStorage.setItem('posbiller', '<?= $suspend_sale->biller_id; ?>');
                localStorage.setItem('posshipping', '<?= $suspend_sale->shipping; ?>');
            <?php } ?>
            <?php if ($old_sale) { ?>
                localStorage.setItem('postax2', '<?= $old_sale->order_tax_id; ?>');
                localStorage.setItem('posdiscount', '<?= $old_sale->order_discount_id; ?>');
                localStorage.setItem('poswarehouse', '<?= $old_sale->warehouse_id; ?>');
                localStorage.setItem('poscustomer', '<?= $old_sale->customer_id; ?>');
                localStorage.setItem('posbiller', '<?= $old_sale->biller_id; ?>');
                localStorage.setItem('posshipping', '<?= $old_sale->shipping; ?>');
            <?php } ?>
            <?php if ($this->input->get('customer')) { ?>
                // if (!localStorage.getItem('positems')) {
                    localStorage.setItem('poscustomer', '<?= $this->input->get('customer'); ?>');
                // } else if (!localStorage.getItem('poscustomer')) {
                //     localStorage.setItem('poscustomer', <?= $customer->id; ?>);
                // }
            <?php } else { ?>
                if (!localStorage.getItem('poscustomer')) {
                    localStorage.setItem('poscustomer', '<?= $customer->id; ?>');
                }
            <?php } ?>

            $(document).on('change', '#poscustomer', function(e) {
                localStorage.setItem('poscustomer', $(this).val());
            });
            if (poscustomer = localStorage.getItem('poscustomer')) {
                $('#poscustomer').val(poscustomer);
            }
            
            if (!localStorage.getItem('postax2')) {
                localStorage.setItem('postax2', <?= $Settings->default_tax_rate2; ?>);
            }
            $('.select').select2({
                minimumResultsForSearch: 7
            });
            var customers = [{
                  id: <?= $customer->id; ?>,
                  text: '<?= $customer->company == '-' ? $customer->name : $customer->company; ?>'
              }];
            $('#poscustomer').val(localStorage.getItem('poscustomer')).select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function(element, callback) {
                    $.ajax({
                        type: "get",
                        async: false,
                        url: "<?= admin_url('customers/getCustomer') ?>/" + $(element).val(),
                        dataType: "json",
                        success: function(data) {
                            //  $("#customer_detail").val(c_detail);
                            localStorage.setItem('customer_adress', data[0].address);
                            localStorage.setItem('customer_phone', data[0].phone);
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
            if (KB) {
                display_keyboards();
                var result = false,
                    sct = '';
                $('#poscustomer').on('select2-opening', function() {
                    sct = '';
                    $('.select2-input').addClass('kb-text');
                    display_keyboards();
                    $('.select2-input').bind('change.keyboard', function(e, keyboard, el) {
                        if (el && el.value != '' && el.value.length > 0 && sct != el.value) {
                            sct = el.value;
                        }
                        if (!el && sct.length > 0) {
                            $('.select2-input').addClass('select2-active');
                            setTimeout(function() {
                                $.ajax({
                                    type: "get",
                                    async: false,
                                    url: "<?= admin_url('customers/suggestions') ?>/?term=" + sct,
                                    dataType: "json",
                                    success: function(res) {
                                        if (res.results != null) {
                                            $('#poscustomer').select2({
                                                data: res
                                            }).select2('open');
                                            $('.select2-input').removeClass('select2-active');
                                        } else {
                                            // bootbox.alert('no_match_found');
                                            $('#poscustomer').select2('close');
                                            $('#test').click();
                                        }
                                    }
                                });
                            }, 500);
                        }
                    });
                });
                $('#poscustomer').on('select2-close', function() {
                    $('.select2-input').removeClass('kb-text');
                    $('#test').click();
                    $('select, .select').select2('destroy');
                    $('select, .select').select2({
                        minimumResultsForSearch: 7
                    });
                });
                $(document).bind('click', '#test', function() {
                    var kb = $('#test').keyboard().getkeyboard();
                    kb.close();
                });
            }

            $(document).on('change', '#posbiller', function() {
                var sb = $(this).val();
                $.each(billers, function() {
                    if (this.id == sb) {
                        biller = this;
                    }
                });
                $('#biller').val(sb);
            });
      <?php for ($i = 1; $i <= 5; $i++) {?>

        $('#paymentModal').on('change', '#amount_<?=$i?>', function (e) {

            $('#amount_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('blur', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
       
        
        $('#paymentModal').on('select2-close', '#paid_by_<?=$i?>', function (e) {
            $('#paid_by_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_no_<?=$i?>', function (e) {
            $('#cc_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_holder_<?=$i?>', function (e) {
            $('#cc_holder_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#gift_card_no_<?=$i?>', function (e) {
            $('#paying_gift_card_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_month_<?=$i?>', function (e) {
            $('#cc_month_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_year_<?=$i?>', function (e) {
            $('#cc_year_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_type_<?=$i?>', function (e) {
            $('#cc_type_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_cvv2_<?=$i?>', function (e) {
            $('#cc_cvv2_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cheque_no_<?=$i?>', function (e) {
            $('#cheque_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#payment_note_<?=$i?>', function (e) {
            $('#payment_note_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#months_<?=$i?>', function (e) {
            $('#months_<?=$i?>').val($(this).val());
        });
        <?php }
        ?>
            $('#cal_time').click(function() {
                var room_id = $('#suspend_id').val();
                $.ajax({
                    type: "get",
                    url: "<?= admin_url('pos/updateRoomPriceMinutely/'); ?>" + room_id,
                    data: {
                        room_id: room_id
                    },
                    success: function(data) {
                        if (data == "success") {
                            $('#suspend_sale').click();
                            location.reload();
                            $('#payment').show();
                        }
                    }
                });
                return false;
            });
            $('#payment').click(function() {
                var decimal = site.settings.decimals;

                var kh_rate = localStorage.getItem('exchange_kh');
                var riel_rate = parseFloat(localStorage.getItem('riel_rate'));
                var bath_rate = parseFloat(localStorage.getItem('bath_rate'));
                <?php if ($sid) { ?>
                    suspend = $('<span></span>');
                    suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />');
                    suspend.appendTo("#hidesuspend");
                <?php } ?>
                var twt = parseFloat((total + invoice_tax) - order_discount + shipping);
                if (count == 1) {
                    bootbox.alert('<?= lang('x_total'); ?>');
                    return false;
                }
                gtotal = twt; 
                
                $(".total_payable").each(function(){
                    var base_rate = $(this).attr("base_rate") - 0;
                    var rate = $(this).attr("rate") - 0;
                    var payable = (formatDecimal((total + invoice_tax) - order_discount + shipping) / base_rate) * rate;
                    if(rate > 1000){
                        payable = formatMoneyKH(Math.round(payable/100) * 100);
                    }else{
                        payable = formatMoney(payable);
                    }
                    $(this).text(payable);
                }); 
                $(".camount").on("keyup",function(){

                    var tamount = 0, i = 0;
                    $(".camount").each(function(){
                        var amount = $(this).val()-0;
                        var base_rate = $(this).attr("base_rate")-0;
                        var rate = $(this).attr("rate")-0;
                        var camount = (amount / rate) * base_rate;
                            tamount += camount;

                        $("#camount_"+i).val(amount);                                           
                        i++;
                    });
                    
                    var award_point = $("#award_point").val() - 0;
                    if(!isNaN(award_point)) {
                        tamount += award_point;
                    }
                    
                    
                    
                    /*$(".balance_1").each(function(){
                        var base_rate = $(this).attr("base_rate")-0;
                        var rate = $(this).attr("rate")-0;

                        var balance_1 = ((((total + invoice_tax)-order_discount+shipping)) / base_rate) * rate;
                        
                        alert(tamount);

                        if(rate > 1000){
                            balance_1 = formatMoneyKH(balance_1);
                        }else{
                            balance_1 = formatMoney(balance_1);
                        }
                        $(this).text(balance_1);
                        i++;
                    });*/
                    $("#amount_1").val(formatDecimal(tamount,decimal)).trigger("focus keyup keypress");
                    $('#amount_val_1').val(formatDecimal(tamount,decimal));
                    calculateTotals();
                });

                $('#item_count').text(count - 1);
                $('#paymentModal').appendTo("body").modal('show');
                $('#amount_1').focus();
            });


            $(".camount").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                         return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            $('.camount').keydown( function(e){
                if ($(this).val().length >= 16) { 
                    $(this).val($(this).val().substr(0, 16));
                }
            });
            $('.camount').keyup( function(e){
                if ($(this).val().length >= 16) { 
                    $(this).val($(this).val().substr(0, 16));
                }
            });

            $(document).on('focus keyup keypress', '.camount', function () {
                pi = $(this).attr('id');
                calculateTotals();
            }).on('blur keyup keypress', '.camount', function () {
                calculateTotals();
            });

            function calculateTotals() {
                var total_paying = 0;
                var ia = $(".camount");
                $.each(ia, function (i) {
                    var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
                    total_paying += parseFloat(this_amount);
                });
                $('#total_paying').text(formatMoney(total_paying));
                <?php if ($pos_settings->rounding) {?>
                    $('#balance').text(formatMoney(total_paying - round_total));
                    $('#balance_' + pi).val(formatDecimal(round_total - total_paying));
                    total_paid = total_paying;
                    grand_total = round_total;
                <?php } else {?>
                    $('#balance').text(formatMoney(gtotal - total_paying));
                    $('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
                    total_paid = total_paying;
                    grand_total = gtotal;
                <?php } ?>

                var change = total_paid - grand_total;
                if(change > 0){
                    change = change.toString();
                    if (change.indexOf(".") >= 0){
                        var res = change.split(".");
                        var change_usd = formatDecimal(res[0],4);
                        var change_riel = formatDecimal(("0."+res[1]),4) * kh_rate;
                        if(change_riel > 0){
                            change_riel = Math.round(change_riel / 100);
                            change_riel = change_riel * 100;
                        }
                        $("#change_usd").html(formatMoney(change_usd));
                        $("#change_riel").html(change_riel+" ៛");
                    }else{
                        $("#change_usd").html(formatMoney(change));
                        $("#change_riel").html(0);
                    }
                }else{
                    $("#change_usd").html(formatMoney(0));
                    $("#change_riel").html(0);
                } 
            }

            $('#paymentModal').on('show.bs.modal', function(e) {
                $('#submit-sale').text('<?= lang('submit'); ?>').attr('disabled', false);
            });
            $('#paymentModal').on('shown.bs.modal', function(e) {
                $('#amount_1').focus().val(0);
                //  $('#quick-cash-payable').click();
            });
            var pi = 'amount_1', pi_kh = 'amount_kh_1', pi_bat = 'amount_bat_1', pa = 2;
            $(document).on('click', '.quick-cash', function () {
            if ($('#quick-payable').find('span.badge').length) {
                $('#clear-cash-notes').click();
                $('#clear-notes').click();
            }
            var $quick_cash = $(this);
            var amt = $quick_cash.contents().filter(function () {
                return this.nodeType == 3;
            }).text();
            var th = ',';
            var $pi = $('#' + pi);
            amt = formatDecimal(amt.split(th).join("")) * 1 + $pi.val() * 1;
            $pi.val(formatDecimal(amt)).focus();
            var note_count = $quick_cash.find('span');
            if (note_count.length == 0) {
                $quick_cash.append('<span class="badge">1</span>');
            } else {
                note_count.text(parseInt(note_count.text()) + 1);
            }
        });
        $(document).on('click', '#quick-cash-payable', function () {
            $('#clear-cash-notes').click();
            $(this).append('<span class="badge">1</span>');
            $('#amount_1').val(grand_total);
        });
        $(document).on('click', '#clear-cash-notes', function () {
            $('.quick-cash').find('.badge').remove();
            $('#' + pi).val('0').focus();
        });
        $(document).on('change', '.gift_card_no', function() {
                var cn = $(this).val() ? $(this).val() : '';
                var payid = $(this).attr('id'),
                    id = payid.substr(payid.length - 1);
                if (cn != '') {
                    $.ajax({
                        type: "get",
                        async: false,
                        url: site.base_url + "sales/validate_gift_card/" + cn,
                        dataType: "json",
                        success: function(data) {
                            if (data === false) {
                                $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                                bootbox.alert('<?= lang('incorrect_gift_card') ?>');
                            } else if (data.customer_id !== null && data.customer_id !== $('#poscustomer').val()) {
                                $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                                bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');
                            } else {
                                $('#gc_details_' + id).html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                                $('#gift_card_no_' + id).parent('.form-group').removeClass('has-error');
                                //calculateTotals();
                                $('#amount_' + id).val(gtotal >= data.balance ? data.balance : gtotal).focus();
                            }
                        }
                    });
                }
            });
            $(document).on('click', '.addButton', function() {
                if (pa <= 5) {
                    $('#paid_by_1, #pcc_type_1').select2('destroy');
                    var phtml = $('#payments').html(),
                        update_html = phtml.replace(/_1/g, '_' + pa);
                    pi = 'amount_' + pa;
                    $('#multi-payment').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' + update_html);
                    $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({
                        minimumResultsForSearch: 7
                    });
                    read_card();
                    pa++;
                } else {
                    bootbox.alert('<?= lang('max_reached') ?>');
                    return false;
                }
                if (KB) {
                    display_keyboards();
                }
                $('#paymentModal').css('overflow-y', 'scroll');
            });
            $(document).on('click', '.close-payment', function() {
                $(this).next().remove();
                $(this).remove();
                pa--;
            });

        $('.split_bills').on('click', function (e) {
            // $('#suspend_sale').click();
            $('#splitItem').modal(); 
            // return false;
        });
  
            
   
            $("#add_item").autocomplete({
                source: function(request, response) {
                    if (!$('#poscustomer').val()) {
                        // $('#add_item').val('').removeClass('ui-autocomplete-loading');
                        bootbox.alert('<?= lang('select_above'); ?>');
                        //response('');
                        $('#add_item').focus();
                        return false;
                    }
                    $.ajax({
                        type: 'get',
                        url: '<?= admin_url('pos/suggestions'); ?>',
                        dataType: "json",
                        data: {
                            term: request.term,
                            warehouse_id: $("#poswarehouse").val(),
                            customer_id: $("#poscustomer").val()
                        },
                        success: function(data) {
                            $(this).removeClass('ui-autocomplete-loading');
                            response(data);
                        }
                    });
                },
                minLength: 1,
                autoFocus: false,
                delay: 250,
                response: function(event, ui) {
                    if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                        bootbox.alert('<?= lang('no_match_found') ?>', function() {
                            $('#add_item').focus();
                        });
                        $(this).val('');
                    } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                        ui.item = ui.content[0];
                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                    } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                        bootbox.alert('<?= lang('no_match_found') ?>', function() {
                            $('#add_item').focus();
                        });
                        $(this).val('');
                    } 
                },
                select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    var wh = $("#poswarehouse").val();
                    $.ajax({
                        type: "get",
                        url: "<?= admin_url('pos/getproductPromo'); ?>",
                        data: {product_id: ui.item.row.id, warehouse_id: wh, qty:ui.item.row.qty},
                        dataType: "json",
                        success: function (data) {
                            if (data) {
                                for (var i = 0; i < data.length; i++) {
                                    data.free = true;
                                    data.parent = ui.item.row.id;
                                    add_invoice_item(data[i]);
                                }
                            }
                            $("#add_item").removeClass('ui-autocomplete-loading');
                        }
                    }).done(function () {
                        $('#modal-loading').hide();
                    });
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?=lang('no_match_found')?>');
                }
            }
            });
            <?php if ($pos_settings->tooltips) {
                echo '$(".pos-tip").tooltip();';
            } ?>
            // $('#posTable').stickyTableHeaders({fixedOffset: $('#product-list')});
            $('#posTable').stickyTableHeaders({
                scrollableArea: $('#product-list')
            });
            $('#product-list, #category-list, #subcategory-list, #brands-list').perfectScrollbar({
                suppressScrollX: true
            });
            $('select, .select').select2({
                minimumResultsForSearch: 7
            });
            function audit_trail_bill() {
                wh = $('#poswarehouse').val(),
                    cu = $('#poscustomer').val();
                $.ajax({
                    type: "get",
                    url: "<?= admin_url('pos/getProductDataByCode') ?>",
                    data: {
                        user_id: user_id,
                        warehouse_id: wh,
                        customer_id: cu
                    },
                    dataType: "json",
                    success: function(data) {
                        e.preventDefault();
                        if (data !== null) {
                            add_invoice_item(data);
                            $('#modal-loading').hide();
                        } else {
                            bootbox.alert('<?= lang('no_match_found') ?>');
                            $('#modal-loading').hide();
                        }
                    }
                });
            }
            $(document).on('click', '.product', function(e) {
                //$('#modal-loading').show();
                code = $(this).val(),
                    wh = $('#poswarehouse').val(),
                    cu = $('#poscustomer').val();
                $.ajax({
                    type: "get",
                    url: "<?= admin_url('pos/getProductDataByCode') ?>",
                    data: {
                        code: code,
                        warehouse_id: wh,
                        customer_id: cu
                    },
                    dataType: "json",
                     success: function(data) {
                        e.preventDefault();
                        if (data !== null) {
                            add_invoice_item(data);
                            var id = data.row.id;
                            var qty = data.row.qty;
                            var category = data.category;
                            $.ajax({
                                type: "get",
                                url: "<?=admin_url('pos/getProductPromo');?>",
                                 data: {product_id: id, warehouse_id: wh, qty:qty},
                                dataType: "json",
                                success: function (data) {
                                    if (data) {
                                        for (var i = 0; i < data.length; i++) {
                                        data.free = true;
                                        data.parent = id;
                                        add_invoice_item(data[i]);
                                    }
                                    }
                                }
                            }).done(function () {
                                $('#modal-loading').hide();
                            });
                            // $.ajax({
                            //     type: "get",
                            //     url: "<?=admin_url('pos/getProductPromotion');?>",
                            //     data: {product_id: id, warehouse_id: wh, category_id: category},
                            //     dataType: "json",
                            //     success: function (data) {
                            //       if (data) {
                            //         for (var i = 0; i < data.length; i++) {
                            //             var ds = data[i].discount;
                            //             if (ds.indexOf("%") !== -1) {
                            //                 var pds = ds.split("%");
                            //                 if (!isNaN(pds[0])) {
                            //                     item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 4);
                            //                 } else {
                            //                     item_discount = formatDecimal(ds);
                            //                 }
                            //             } else {
                            //                 item_discount = formatDecimal(ds);
                            //             }
                            //             product_discount += formatDecimal(item_discount * qty);
                            //             }
                            //         }
                            //         // localStorage.setItem('product_discount_promotion', product_discount);
                            //         // console.log(product_discount);
                            //         return  product_discount;
                            //     }
                            // });
                        } else {
                            bootbox.alert('<?= lang('no_match_found') ?>');
                            $('#modal-loading').hide();
                        }
                    }
                });
            });
            $(document).on('click', '.category', function() {
                var wh = $('#poswarehouse').val();
                if (cat_id != $(this).val()) {
                    $('#open-category').click();
                    //$('#modal-loading').show();
                    cat_id = $(this).val();
                    $.ajax({
                        type: "get",
                        url: "<?= admin_url('pos/ajaxcategorydata'); ?>",
                        data: {
                            category_id: cat_id,
                            warehouse_id: wh
                        },
                        dataType: "json",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data.products);
                            newPrs.appendTo("#item-list");
                            $('#subcategory-list').empty();
                            var newScs = $('<div></div>');
                            newScs.html(data.subcategories);
                            newScs.appendTo("#subcategory-list");
                            tcp = data.tcp;
                            nav_pointer();
                        }
                    }).done(function() {
                        p_page = 'n';
                        $('#category-' + cat_id).addClass('active');
                        $('#category-' + ocat_id).removeClass('active');
                        ocat_id = cat_id;
                        $('#modal-loading').hide();
                        nav_pointer();
                    });
                }
            });
            $('#category-' + cat_id).addClass('active');
            $(document).on('click', '.brand', function() {
                var wh = $('#poswarehouse').val();
                if (brand_id != $(this).val()) {
                    $('#open-brands').click();
                    //$('#modal-loading').show();
                    brand_id = $(this).val();
                    $.ajax({
                        type: "get",
                        url: "<?= admin_url('pos/ajaxbranddata'); ?>",
                        data: {
                            brand_id: brand_id,
                            warehouse_id: wh
                        },
                        dataType: "json",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data.products);
                            newPrs.appendTo("#item-list");
                            tcp = data.tcp;
                            nav_pointer();
                        }
                    }).done(function() {
                        p_page = 'n';
                        $('#brand-' + brand_id).addClass('active');
                        $('#brand-' + obrand_id).removeClass('active');
                        obrand_id = brand_id;
                        $('#category-' + cat_id).removeClass('active');
                        $('#subcategory-' + sub_cat_id).removeClass('active');
                        cat_id = 0;
                        sub_cat_id = 0;
                        $('#modal-loading').hide();
                        nav_pointer();
                    });
                }
            });
            $(document).on('click', '.subcategory', function() {
                var wh = $('#poswarehouse').val();
                if (sub_cat_id != $(this).val()) {
                    $('#open-subcategory').click();
                    //$('#modal-loading').show();
                    sub_cat_id = $(this).val();
                    $.ajax({
                        type: "get",
                        url: "<?= admin_url('pos/ajaxproducts'); ?>",
                        data: {
                            category_id: cat_id,
                            subcategory_id: sub_cat_id,
                            per_page: p_page,
                            warehouse_id: wh
                        },
                        dataType: "html",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data);
                            newPrs.appendTo("#item-list");
                        }
                    }).done(function() {
                        p_page = 'n';
                        $('#subcategory-' + sub_cat_id).addClass('active');
                        $('#subcategory-' + osub_cat_id).removeClass('active');
                        $('#modal-loading').hide();
                    });
                }
            });
            $(document).on('keyup', '#pos_search_product, #pos_search_category', function() {
                var wh = $('#poswarehouse').val();
                var term_product  = $('#pos_search_product').val();
                var term_category = $('#pos_search_category').val();
                // $('#modal-loading').show();
                $.ajax({
                    type: "get",
                    url: "<?= admin_url('pos/ajaxproducts'); ?>",
                    data: { 
                        term_product: term_product,
                        term_category: term_category,
                        warehouse_id: wh,
                        per_page: p_page
                    },
                    dataType: "html",
                    success: function(data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }
                }).done(function() {
                    p_page = 'n';
                    $('#modal-loading').hide();
                });
            });
            $(document).on('click', '#pos_search_product, #pos_search_category', function() {
                $(this).select();
            });
            $('#next').click(function() {
                var wh = $('#poswarehouse').val();
                if (p_page == 'n') {
                    p_page = 0
                }
                p_page = p_page + pro_limit;
                if (tcp >= pro_limit && p_page < tcp) {
                    //$('#modal-loading').show();
                    $.ajax({
                        type: "get",
                        url: "<?= admin_url('pos/ajaxproducts'); ?>",
                        data: {
                            category_id: cat_id,
                            subcategory_id: sub_cat_id,
                            per_page: p_page,
                            warehouse_id: wh
                        },
                        dataType: "html",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data);
                            newPrs.appendTo("#item-list");
                            nav_pointer();
                        }
                    }).done(function() {
                        $('#modal-loading').hide();
                    });
                } else {
                    p_page = p_page - pro_limit;
                }
            });
            $('#previous').click(function() {
                var wh = $('#poswarehouse').val();
                if (p_page == 'n') {
                    p_page = 0;
                }
                if (p_page != 0) {
                    //$('#modal-loading').show();
                    p_page = p_page - pro_limit;
                    if (p_page == 0) {
                        p_page = 'n'
                    }
                    $.ajax({
                        type: "get",
                        url: "<?= admin_url('pos/ajaxproducts'); ?>",
                        data: {
                            category_id: cat_id,
                            subcategory_id: sub_cat_id,
                            per_page: p_page,
                            warehouse_id: wh
                        },
                        dataType: "html",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data);
                            newPrs.appendTo("#item-list");
                            nav_pointer();
                        }
                    }).done(function() {
                        $('#modal-loading').hide();
                    });
                }
            });
            $(document).on('change', '.paid_by', function() {
                //     $('#clear-cash-notes').click();
                //      $('#amount_1').val(grand_total);
                var p_val = $(this).val(),
                    id = $(this).attr('id'),
                    pa_no = id.substr(id.length - 1);
                $('#rpaidby').val(p_val);
                if (p_val == 'cash' || p_val == 'other') {
                    $('.pcheque_' + pa_no).hide();
                    $('.pcc_' + pa_no).hide();
                    $('.pcash_' + pa_no).show();
                    $('#amount_' + pa_no).focus();
                } else if (p_val == 'CC' || p_val == 'stripe' || p_val == 'ppp' || p_val == 'authorize') {
                    $('.pcheque_' + pa_no).hide();
                    $('.pcash_' + pa_no).hide();
                    $('.pcc_' + pa_no).show();
                    $('#swipe_' + pa_no).focus();
                } else if (p_val == 'Cheque') {
                    $('.pcc_' + pa_no).hide();
                    $('.pcash_' + pa_no).hide();
                    $('.pcheque_' + pa_no).show();
                    $('#cheque_no_' + pa_no).focus();
                } else {
                    $('.pcheque_' + pa_no).hide();
                    $('.pcc_' + pa_no).hide();
                    $('.pcash_' + pa_no).hide();
                }
                if (p_val == 'gift_card') {
                    $('.gc_' + pa_no).show();
                    $('.ngc_' + pa_no).hide();
                    $('#gift_card_no_' + pa_no).focus();
                } else {
                    $('.ngc_' + pa_no).show();
                    $('.gc_' + pa_no).hide();
                    $('#gc_details_' + pa_no).html('');
                }
                if (p_val == 'loan') {
                    $('.loan_' + pa_no).show();
                } else {
                    $('.loan_' + pa_no).hide();
                }
            });
            $(document).on('click', '#submit-sale', function() {
                var i = 1;
                var p_val = $("#paid_by_" + i + "").val();
                if (p_val == 'loan') {
                    var month = $("#months_" + i + "").val();
                    if (month == "" || month == 0) {
                        alert("<?= lang('enter_months'); ?>");
                        return false;
                    }
                    if (total_paid >= grand_total) {
                        alert("<?= lang('loan_can_not_access'); ?>");
                        return false;
                    }
                }
                if ( total_paid < grand_total) { 
                    <?php if($pos_settings->sale_due) { ?>
                        $('#pos_note').val(localStorage.getItem('posnote'));
                        $('#staff_note').val(localStorage.getItem('staffnote'));
                        $('#submit-sale').text('<?= lang('loading'); ?>').attr('disabled', true);
                        $('#pos-sale-form').submit();
                    <?php } else { ?>
                        bootbox.alert("<?= lang('paid_l_t_payable'); ?>");
                        return false;
                    <?php } ?>
                } else {
                    $('#pos_note').val(localStorage.getItem('posnote'));
                    $('#staff_note').val(localStorage.getItem('staffnote'));
                    $(this).text('<?= lang('loading'); ?>').attr('disabled', true);
                    $('#pos-sale-form').submit();
                }
                localStorage.clear();
            });
            $('#suspend').click(function() {
                if (count <= 1) {
                    bootbox.alert('<?= lang('x_suspend'); ?>');
                    return false;
                } else {
                    <?php if (
                        $pos_settings->pos_type == 'table' ||
                        $pos_settings->pos_type == 'room'
                    ) { ?>
                        $('#suspend_sale').click();
                    <?php } else { ?>
                        $('#susModal').modal();
                    <?php } ?>
                }
            });
            $('#suspend_sale').click(function() {
                ref = $('#reference_note').val();
                if (!ref || ref == '') {
                    bootbox.alert('<?= lang('type_reference_note'); ?>');
                    return false;
                } else {
                    suspend = $('<span></span>');
                    <?php if ($sid) { ?>
                        suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                    <?php } else { ?>
                        suspend.html('<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                    <?php } ?>
                    suspend.appendTo("#hidesuspend");
                    $('#total_items').val(count - 1);
                    $('#pos-sale-form').submit();
                }
            });
        });
        $(document).ready(function() {
            $('#print_order').click(function() {
                <?php if ($pos_settings->pos_type == 'pos') { ?>
                    if (count == 1) {
                        bootbox.alert('<?= lang('x_total'); ?>');
                        return false;
                    }
                    <?php if ($pos_settings->remote_printing != 1) { ?>
                        printOrder();
                    <?php } else { ?>
                        Popup($('#order_tbl').html());
                    <?php } ?>
                <?php } else { ?>
                    $('#suspend_sale').click();
                    sessionStorage.setItem("reloading_order", "true");
                    var bill_refer = $("#bill_refer").val();
                    var table_id = $("#table_id").val();
                <?php } ?>
            });
            window.onload = function() {
                var reloading = sessionStorage.getItem("reloading_order");
                if (reloading) {
                    sessionStorage.removeItem("reloading_order");
                    var head_print_order = $("#head_print_order").html();
                    var bill_refer = $("#bill_refer").val();
                    var table_id = $("#table_id").val();
                    // Initializing array with Checkbox checked values
                    items = positems;
                    bootbox.confirm("<?=$this->lang->line('are_you_sure?')?>", function (gotit) {
                        $('.modal-content').hide();
                        if (gotit == true) {
                            
                            $.ajax({
                                type: "POST",
                                async: false,
                                url: "<?= admin_url('pos/audit_trail_order') ?>",
                                data: {
                                    <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>",
                                    bill_refer: bill_refer,
                                    table_id: table_id,
                                    warehouse_id: $("#poswarehouse").val(),
                                    items : items
                                },
                                success: function(data) { 
                                    if(data === ''){
                                    <?php if ($pos_settings->remote_printing != 1 || 1) { ?>
                                        $.ajax({
                                            type: "get",
                                            async: false,
                                            url: "<?= admin_url('pos/kitchens') ?>",
                                            dataType: "json",
                                            data: {
                                                bill_refer: bill_refer,
                                                table_id: table_id,
                                                head_print_order,
                                                head_print_order
                                            },
                                            success: function(data) {
                                                ot_order_data = {};
                                                var ot_items = '';
                                                for (let i = 1; i < Object.keys(data).length; i++) {
                                                    ot_items = printLine(product_name('=>  ' + data[i].items.name , 100) + ': [' + data[i].items.qty + ']\n');
                                                    // var product_details = data[i].items.product_details;
                                                    // if(product_details != ""){
                                                    //     ot_items += "=============== Set Menu ===================\n";
                                                    //         pd = (product_details).split(','); 
                                                    //         for (var l = 0, com = pd.length; l < com; l++){
                                                    //             ot_items += ' -  ' + (pd[l].length > 0 ?  (pd[l]) : "");
                                                    //             ot_items += "\n"; 
                                                    //         }                              
                                                    // }
                                                    var combos = data[i].items.combos;  
                                                    if(combos != "" && combos != null){ 
                                                        ot_items += "=============== Set Menu =====================\n";
                                                        for (var l = 0, combo_len = combos.length; l < combo_len  ; l++) {
                                                            if(combos[l] != ""){
                                                                ot_items += combos[l]; 
                                                            }
                                                            ot_items += "\n";
                                                        } 
                                                    } 
                                                    var comments = data[i].items.comment;
                                                    if(comments != ""){
                                                        ot_items += "=============== Comment ===================\n";
                                                        for (var k = 0, len = comments.length; k < len; k++) {
                                                            ot_items += (comments[k].length > 0 ?  (comments[k]) : "");
                                                        }
                                                        ot_items += "\n";
                                                    }
                                                    var addons = data[i].items.addon;  
                                                    if(addons != "" && addons != null){ 
                                                        ot_items += "=============== AddOn =====================\n";
                                                        for (var k = 0, addon_len = addons.length; k < addon_len  ; k++) {
                                                            if(addons[k] != ""){
                                                                ot_items += addons[k]; 
                                                            }
                                                            ot_items += "\n";
                                                        } 
                                                    } 
                                                        ot_order_data.item = ot_items;
                                                        var item_stock_type = data[i].items.type;
                                                        var stock_type = item_stock_type.split(','); 
                                                        for (let j = 0; j < stock_type.length; j++) {
                                                            printOrders(ot_order_data, stock_type[j]);  
                                                        }
                                                }
                                            }
                                        });
                                    <?php } else { ?>
                                        $.ajax({
                                            type: "get",
                                            async: false,
                                            url: "<?= admin_url('pos/kitchens_popup') ?>",
                                            data: {
                                                bill_refer: bill_refer,
                                                head_print_order,
                                            },
                                            success: function(data) {
                                                Popup(data);
                                            }
                                        });
                                    <?php } ?>
                                    }
                                }

                            });
                        }
                    });
                  
                    return false
                }
            }

            $('#print_bill').click(function() {
         
                var bill_refer = $("#bill_refer").val();
                var table_id = $("#table_id").val();
                 $('#suspend_sale').click();
                items = positems;
                $.ajax({
                    type: "post",
                    async: false,
                    url: "<?= admin_url('pos/audit_trail_bill') ?>",
                    data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>",
                        bill_refer: bill_refer,
                        table_id: table_id,
                        warehouse_id: $("#poswarehouse").val(),
                        items : items

                    },
                    success: function(data) { 
                        var cus_id = $("#poscustomer").val();
                        if (count == 1) {
                            bootbox.alert('<?= lang('x_total'); ?>');
                            return false;
                        }
                        <?php if ($pos_settings->remote_printing != 1) { ?>
                            printBill();
                        <?php } else { ?>
                            Popup($('#bill_tbl').html());
                        <?php } ?>

                    }
                });
                return false;
            });

        });
        $(function() {
            $(".alert").effect("shake");
            setTimeout(function() {
                $(".alert").hide('blind', {}, 500)
            }, 15000);
            <?php if ($pos_settings->display_time) { ?>
                var now = new moment();
                $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
                setInterval(function() {
                    var now = new moment();
                    $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
                }, 1000);
            <?php }
            ?>
        });
        <?php if ($pos_settings->remote_printing == 1) { ?>

            function Popup(data) {
                var mywindow = window.open('', 'bpas_pos_print', 'height=500,width=305');
                //    var mywindow = window.open('', '_blank');
                mywindow.document.write('<html><head><title>Print</title><style type="text/css" media="all">body { font-size: 13px !important;} table{font-size: 13px !important;}</style>');
                mywindow.document.write('<link rel="stylesheet" href="<?= $assets ?>styles/helpers/bootstrap.min.css" type="text/css" />');
                mywindow.document.write('</head><body>');
                mywindow.document.write(data);
                mywindow.document.write('</body></html>');
                mywindow.print();
                mywindow.close();
                return true;
            }
        <?php }
        ?>
    </script>
    <?php
    $s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
    foreach (lang('select2_lang') as $s2_key => $s2_line) {
        $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
    }
    $s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
    ?>
    <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/plugins.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/parse-track-data.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/pos.ajax.js"></script>
    <?php if (!$pos_settings->remote_printing || 1) { ?>
        <script type="text/javascript">
            var order_printers = <?= json_encode($order_printers); ?>;
            function printOrder() {
                $.each(order_printers, function() {
                    var socket_data = {
                        'printer': this,
                        'logo': (biller && biller.logo ? biller.logo : ''),
                        'text': order_data
                    };
                    alert(JSON.stringify(order_data));
                    $.get('<?= admin_url('pos/p/order'); ?>', {
                        data: JSON.stringify(socket_data)
                    });
                });
                return false;
            }
            function printOrders(data, stock_type) {
                $.each(order_printers, function() {
                    var table_id = $("#table_id").val();

                    if(this.stock_type === stock_type){
                        var socket_data = {
                            'printer': this,
                            'logo': (biller && biller.logo ? biller.logo : ''),
                            'text': order_data,
                        };
                        console.log(socket_data);
                        $.get('<?= admin_url('pos/ps/order'); ?>', {
                        data: JSON.stringify(socket_data),
                        item: JSON.stringify(data),
                        table_id: JSON.stringify(table_id),
                        stock_type: JSON.stringify(stock_type),
                    });
                    }           
                });
                return false;
            }

            function printBill() {
                var socket_data = {
                    'printer': <?= json_encode($printer); ?>,
                    'logo': (biller && biller.logo ? biller.logo : ''),
                    'text': bill_data
                };
                $.get('<?= admin_url('pos/p'); ?>', {
                    data: JSON.stringify(socket_data)
                });
                return false;
            }
        </script>
    <?php
    } elseif ($pos_settings->remote_printing == 2) {
    ?>
        <script src="<?= $assets ?>js/socket.io.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            socket = io.connect('http://localhost:8080', {
                'reconnection': false
            });

            function printBill() {
                if (socket.connected) {
                    var socket_data = {
                        'printer': <?= json_encode($printer); ?>,
                        'text': bill_data
                    };
                    socket.emit('print-now', socket_data);
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }
            var order_printers = <?= json_encode($order_printers); ?>;

            function printOrder() {
                if (socket.connected) {
                    $.each(order_printers, function() {
                        var socket_data = {
                            'printer': this,
                            'text': order_data
                        };
                        socket.emit('print-now', socket_data);
                    });
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }
        </script>
    <?php
    } elseif ($pos_settings->remote_printing == 3) {
    ?>
        <script type="text/javascript">
            try {
                socket = new WebSocket('ws://127.0.0.1:6441');
                socket.onopen = function() {
                    console.log('Connected');
                    return;
                };
                socket.onclose = function() {
                    console.log('Not Connected');
                    return;
                };
            } catch (e) {
                console.log(e);
            }
            var order_printers = <?= $pos_settings->local_printers ? "''" : json_encode($order_printers); ?>;

            function printOrder() {
                if (socket.readyState == 1) {
                    if (order_printers == '') {
                        var socket_data = {
                            'printer': false,
                            'order': true,
                            'logo': (biller && biller.logo ? site.url + 'assets/uploads/logos/' + biller.logo : ''),
                            'text': order_data
                        };
                        socket.send(JSON.stringify({
                            type: 'print-receipt',
                            data: socket_data
                        }));
                    } else {
                        $.each(order_printers, function() {
                            var socket_data = {
                                'printer': this,
                                'logo': (biller && biller.logo ? site.url + 'assets/uploads/logos/' + biller.logo : ''),
                                'text': order_data
                            };
                            socket.send(JSON.stringify({
                                type: 'print-receipt',
                                data: socket_data
                            }));
                        });
                    }
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }

            function printBill() {
                if (socket.readyState == 1) {
                    var socket_data = {
                        'printer': <?= $pos_settings->local_printers ? "''" : json_encode($printer); ?>,
                        'logo': (biller && biller.logo ? site.url + 'assets/uploads/logos/' + biller.logo : ''),
                        'text': bill_data
                    };
                    socket.send(JSON.stringify({
                        type: 'print-receipt',
                        data: socket_data
                    }));
                    return false;
                } else {
                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                    return false;
                }
            }
        </script>
    <?php
    }
    ?>
    <script type="text/javascript">
        $('.sortable_table tbody').sortable({
            containerSelector: 'tr'
        });
    </script>
    <script type="text/javascript" charset="UTF-8">
        <?= $s2_file_date ?>
    </script>
    <script type='text/javascript'>
         $(document).ready(function() {
            $(".combo_product:not(.ui-autocomplete-input)").live("focus", function (event) {
                $(this).autocomplete({
                    source: '<?= admin_url('products/suggestions'); ?>',
                    minLength: 1,
                    autoFocus: false,
                    delay: 250,
                    response: function (event, ui) {
                        if (ui.content.length == 1 && ui.content[0].id != 0) {
                            ui.item = ui.content[0];
                            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                            $(this).removeClass('ui-autocomplete-loading');
                        }
                    },
                    select: function (event, ui) {
                        event.preventDefault();
                        if (ui.item.id !== 0) {
                            var parent = $(this).parent().parent();
                            parent.find(".combo_product_id").val(ui.item.id);
                            parent.find(".combo_name").val(ui.item.name);
                            parent.find(".combo_code").val(ui.item.code);
                            parent.find(".combo_price").val(formatDecimal(ui.item.price));
                            parent.find(".combo_qty").val(formatDecimal(1));
                            if (site.settings.qty_operation == 1) {
                                parent.find(".combo_width").val(formatDecimal(1));
                                parent.find(".combo_height").val(formatDecimal(1));
                            }
                            $(this).val(ui.item.label);
                        } else {
                            bootbox.alert('<?= lang('no_match_found') ?>');
                        }
                    }
                });
            });
            // Detect pagination click
            $('#pagination').on('click', 'a', function(e) {
                e.preventDefault();
                var pageno = $(this).attr('data-ci-pagination-page');
                loadPagination(pageno);
            });
            loadPagination(0);
            // Load pagination
            function loadPagination(pagno) {
                $.ajax({
                    url: '<?= admin_url() ?>pos/loadRecord/' + pagno,
                    type: 'get',
                    dataType: 'json',
                    success: function(response) {
                        $('#pagination').html(response.pagination);
                        // createTable(response.result, response.row);

                        $('#postsList').empty();
                        $('#postsList').append(createMultiLevelMenu(response.categories, 1));
                    }
                });
            }
            // Create table list
            function createTable(result, sno) {
                sno = Number(sno);
                $('#postsList').empty();
                for (index in result) {
                    var id = result[index].id;
                    var title = result[index].name;
                    var li = "<button style='margin:1px;' id='category-" + id + "' type = 'button' value = '" + id + "' class='category btn btn-danger'>" + title.charAt(0).toUpperCase() + title.slice(1) + "</button>"
                    $('#postsList').append(li);
                }
            }
            var menu = "";
            function createMultiLevelMenu (result, n) {
                menu = (n ? '<div style="display: inline-block;"><ul id="menu">' : '<ul class="child">');
                result.forEach((element, index, array) => {
                    var id    = element.id;
                    var title = element.name;
                    if(typeof(element.children) != "undefined" && element.children.length > 0) {
                        menu += '<li class="parent" style="padding: 2.2px 0 !important;">'+

                        '<a href="" style="margin-right: 5px;" class="category" id="category-' + id + '" data="' + id + '" onclick="return false;">'+
                            title.charAt(0).toUpperCase() + title.slice(1) + '<span class="expand">»</span></a>';
                        menu += createMultiLevelMenu(element.children, 0);
                    } else {
                        menu += "<li class='parent'>"+
                                    
                                    "<button style='width: 100%;' id='category-" + id + "' type = 'button' value = '" + id + "' class='category btn btn-secondary'>" + 
                                            title.charAt(0).toUpperCase() + title.slice(1) + "</button>";
                    }
                    menu += '</li>';
                });
                menu += (n ? '</ul></div>' : '</ul>');
                return menu;
            }
        });
    </script>

    <div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
    <?php
    if (isset($print) && !empty($print)) {
        /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */
        include 'remote_printing.php';
    }
    ?>
</body>
</html>
<script type="text/javascript">
    $(document).ready(function () {
        setProducts();    
        $('#poswarehouse').change(function () {
            setProducts();
        });

    });
    function setProducts() {
        var wh = $('#poswarehouse').val();
        if (wh) {
            //$('#modal-loading').show();
            $.ajax({
                type: "GET",
                url: "<?= admin_url('pos/getProductsAjax'); ?>",
                data: { warehouse_id: wh },
                dataType: "json",
                success: function(data) {
                    $('#item-list').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data.products);
                    newPrs.appendTo("#item-list");
                }, error: function(jqXHR, textStatus, errorThrown){
                    // console.log("Error!: " + textStatus);
                }, complete: function(xhr, statusText){
                    // console.log(xhr.status + " " + statusText);
                }
            }).done(function() {
                $('#modal-loading').hide();
            });
        }
    }
    <?php 
    if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('sldate')) {
                $("#sldate").datetimepicker({
                    format: site.dateFormats.js_ldate,
                    fontAwesome: true,
                    language: 'bpas',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    forceParse: 0
                }).datetimepicker('update', new Date());
            }
            $(document).on('change', '#sldate', function(e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (sldate = localStorage.getItem('sldate')) {
                $('#sldate').val(sldate);
            }
    <?php } ?>

</script>