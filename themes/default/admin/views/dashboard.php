<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets; ?>js/hc/modules/highcharts.js"></script>
<script type="text/javascript" src="<?= $assets; ?>js/hc/modules/funnel.js"></script>
<!-- <script type="text/javascript" src="<?= $assets; ?>js/hc/modules/highcharts-3d.js"></script> -->
<script type="text/javascript" src="<?= $assets; ?>js/hc/modules/exporting.js"></script>
<script type="text/javascript" src="<?= $assets; ?>js/hc/modules/export-data.js"></script>
<script type="text/javascript" src="<?= $assets; ?>js/hc/modules/accessibility.js"></script>
<script type="text/javascript" src="<?= $assets; ?>js/hc/canvasjs.min.js"></script>
<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('welcome/index'); ?>';
    });
</script>
<style>
    .widget-user-header{
        padding: 0 0 10px 25px;
        height: 110px;    
    }
    .small-box{
        background: #ffffff;
    }
    @media print {
        .fa {
            color: #EEE;
            display: none;
        }
        .small-box {
            border: 1px solid #CCC;
        }
    }
    .text_title {
        color: #a5a3ae;
    }
    .padding1010 {
        min-height: 181px;
    }
    .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        padding: 5px !important;
    }
</style>

<?php
function row_status($x) {
    if ($x == null) {
        return '';
    } elseif ($x == 'pending') {
        return '<div class="text-center"><span class="label label-warning">' . lang($x) . '</span></div>';
    } elseif ($x == 'completed' || $x == 'paid' || $x == 'sent' || $x == 'received') {
        return '<div class="text-center"><span class="label label-success">' . lang($x) . '</span></div>';
    } elseif ($x == 'partial' || $x == 'transferring') {
        return '<div class="text-center"><span class="label label-info">' . lang($x) . '</span></div>';
    } elseif ($x == 'due') {
        return '<div class="text-center"><span class="label label-danger">' . lang($x) . '</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-default">' . lang($x) . '</span></div>';
    }
} ?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('graph_report'); ?></h2>
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
</div>
<?php
if (($Owner || $Admin) && $chatData) {
    foreach ($chatData as $month_sale) {
        $months[]     = date('M-Y', strtotime($month_sale->month));
        $msales[]     = $month_sale->sales;
        $mtax1[]      = $month_sale->tax1;
        $mtax2[]      = $month_sale->tax2;
        $mpurchases[] = $month_sale->purchases;
        $mtax3[]      = $month_sale->ptax;
    } ?>
<?php if ($Settings->module_hr) {
    
    $dat = isset($attendances) ? $attendances : '' ; 
    $user_group = $this->site->getUserGroup($user->group_id);
    $bgatt = 'bg-success';
    if(isset($attendances)) {
        $bgatt = 'bg-success';
    } else {
        $bgatt = 'bg-danger';
    }
    ?> 
<div class="col-sm-12 hide"> 
    <div class="box">
        <div class="box-content"> 
            <div class="col-md-4">
                <div class="widget-user-header <?php echo $bgatt;?> bg-darken-2">
                    <h2 class="widget-user-username"><br><?php echo $user->first_name. ' ' .$user->last_name; ?> </h2>
                    <h5 class="widget-user-desc"><?php echo $user_group->name; ?> </h5>
                </div> 
                <div class="widget-user-image text-center"><img alt="" src="<?= $this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : base_url('assets/images/' . $this->session->userdata('gender') . '.png'); ?>" 
                    style="width:80px;height:80px;margin-top:-40px;background-color:white;" class="mini_avatar img-rounded img-circle"></div>
                <div class="row text-center">
                    <div class="col-sm-12">
                        <div class="description-block">
                            <p class="text-muted pb-0-5"><?php echo $this->lang->line('last_login');?>: <?php echo date("D-d-M-Y H:i:s", $user->last_login); ?></p>
                            <p class="text-muted pb-0-5"><?php echo $this->lang->line('my')." ". $this->lang->line($employees_policy->policy);?>: <?= $employees_policy->time_in_one ." to ".$employees_policy->time_out_one ?> and <br><?= $employees_policy->time_in_two ." to ".$employees_policy->time_out_two ?></p>
                        </div>
                    </div>
                </div>
                <div class="text-xs-center">
                    <input type="hidden" name="timeshseet" value="<?php echo $user->id;?>">
                    <!-- 2023-04-11 17:10 -->
                    <?php if(!$attendances) { ?>
                        <input type="hidden" value="clock_in" name="clock_state" id="clock_state">
                        <input type="hidden" value="<?= date("Y-m-d h:i:s"); ?>" name="date" id="date">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="clock_btn btn btn-success btn-block text-uppercase" type="submit" id="clock_btn"><i class="fa fa-arrow-circle-right"></i><?php echo $this->lang->line('clock_in');?></button>
                            </div>
                            <div class="col-md-6">
                                <button class="clock_btn btn btn-danger btn-block text-uppercase" disabled="disabled" type="submit" id="clock_btn"><i class="fa fa-arrow-circle-left"></i> <?php echo $this->lang->line('clock_out');?></button>
                            </div>
                        </div>
                    <?php } else { ?>
                        <input type="hidden" value="clock_out" name="clock_state" id="clock_state">
                        <input type="hidden" value="<?= date("Y-m-d h:i:s"); ?>" name="date" id="date">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="clock_btn btn btn-success btn-block text-uppercase" disabled="disabled" type="submit" id="clock_btn"><i class="fa fa-arrow-circle-right"></i><?php echo $this->lang->line('clock_in');?></button>
                            </div>
                            <div class="col-md-6">
                                <button class="clock_btn btn btn-danger btn-block text-uppercase" type="submit" id="clock_btn"><i class="fa fa-arrow-circle-left"></i> <?php echo $this->lang->line('clock_out');?></button>
                            </div>
                        </div>
                    <?php } ?>
                </div> 
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<div class="col-sm-12"> 
    <div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-th"></i><span class="break"></span><?= lang('quick_links') ?></h2>
    </div>
    <div class="box-content"> 
        <div class="col-md-4 col-xs-6">
                <a class="bblue white quick-button small" href="<?= admin_url('hr') ?>">
                    <div style="height: 70px;"> <i class="fa fa-barcode"></i>
                        <p class="bold" style="font-size: 15px;"><?= ($total_employees->total)?> <?= lang('employee') ?></p>
                    </div>
                    <div class="row">
                        <div class="col-xs-6"><p style="background-color: green; width: 50%; height: 18px; font-size: 13px; margin-left:30%;"><?= lang('active') ?>: <?= ($total_employees->active_users)?></p></div> 
                        <div class="col-xs-6"><p style="background-color: red; width: 50%; height: 18px; font-size: 13px; margin-left:20%;"><?= lang('inactive') ?>: <?= ($total_employees->inactive_users)?></p></div>     
                    </div>        
                </a>
                <div>
            </div>
        </div>
        <?php if ($Owner) { ?>
            <div class="col-md-4 col-xs-6">
                <a class="bblue white quick-button small" href="<?= admin_url('auth/users') ?>">
                    <div style="height: 70px;">
                        <i class="fa fa-group"></i>
                        <p class="bold" style="font-size: 15px;"><?= lang('users') ?></p>
                    </div>
                    <div class="row">    
                        <div class="col-xs-12"><p style="background-color: green; width: 20%; height: 18px; font-size: 13px; margin-left:40%;">All Users : <?= ($total_users->total)?></p></div>
                    </div>  
                </a>
            </div>
            <div class="col-md-4 col-xs-6">
                <a class="bblue white quick-button small" href="<?= admin_url('system_settings') ?>">
                    <div style="height: 70px;">
                        <i class="fa fa-cogs"></i>
                        <p class="bold" style="font-size: 15px;"><?= lang('settings') ?></p> 
                    </div>
                    <div class="row">    
                        <div class="col-xs-12"><p style="background-color: green; width: 20%; height: 18px; font-size: 13px; margin-left:40%;"><?= lang('settings') ?></p></div>     
                    </div> 
                </a>
            </div>
        <?php } ?>
        <div class="clearfix"></div>
    </div>
</div>
<!-- ===============12/11/22 ===========!-->
<div class="row">
    
    <div class="col-sm-6">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('positions'); ?>
                </h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="overflow-scrolls" style="overflow:auto; height:200px;">
                        <table class="table">
                        <?php $c_color = array('#9932CC','#00A5A8','#FF4558','#16D39A','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558');?>
                        <?php $j=0;foreach($total_employees_position as $position) { ?>
                            <tr>
                                <td width="5"><div style="width:4px;border:5px solid <?php echo $c_color[$j];?>;"></div></td>
                                <td><div class="bold" style="color:383838" ><?= $position->name. ' ('.$position->total.')';?> </div></td>
                            </tr>  
                        <?php $j++; } ?>
                        </table>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div id="employees_position" style="width:150; height:200px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if(isset($total_employees_department)){?>
    <div class="col-sm-6">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('departments'); ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                <div class="box-block">
                    <div class="col-sm-6">
                        <div class="overflow-scrolls" style="overflow:auto; height:200px;">
                        <table class="table">
                        <?php $c_color = array('#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#556B2F','#9932CC');?>
                           <?php $j=0;foreach ($total_employees_department as $department) { ?>
                            <tr>
                                <td width="5"><div style="width:2px;border:5px solid <?php echo $c_color[$j];?>;"></div></td>
                                <td><div class="bold" style="color:383838;width-left: 9px;"><?= ($department->name. ' ('.$department->total.')');?></div></td>
                            </tr>
                            <?php $j++;} ?> 
                        </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="employees_department" style="width:150; height:200px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
    <div class="row hide">
        <div class="col-sm-3">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-regular fa-arrow-right-from-bracket"></i> <?= lang('take_leaves'); ?></h2>
                </div>
                <div class="box-content">
                    <div class="overflow-scrolls" style="overflow:auto; height:200px;">
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;margin-bottom: 10px;">
                            <div>thaisan has take leave on 25/10/2024</div>
                          
                        </div>
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;">
                            <div>thaisan has take leave on 25/10/2024</div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-regular fa-arrow-right-from-bracket"></i> <?= lang('day_off'); ?></h2>
                </div>
                <div class="box-content">
                    <div class="overflow-scrolls" style="overflow:auto; height:200px;">
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;margin-bottom: 10px;">
                            <div>thaisan has take leave on 25/10/2024</div>
                          
                        </div>
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;">
                            <div>thaisan has take leave on 25/10/2024</div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-regular fa-screen-users"></i> <?= lang('training'); ?></h2>
                </div>
                <div class="box-content">
                    <div class="overflow-scrolls" style="overflow:auto; height:200px;">
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;margin-bottom: 10px;">
                            <div>thaisan has take leave on 25/10/2024</div>
                          
                        </div>
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;">
                            <div>thaisan has take leave on 25/10/2024</div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-regular fa-cake-candles"></i><?= lang('birthday'); ?></h2>
                </div>
                <div class="box-content">
                    <div class="overflow-scrolls" style="overflow:auto; height:200px;">
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;margin-bottom: 10px;">
                            <div><i class="fa-regular fa-cake-candles"></i> Wish thaisan a Happy Birthday!</div>
                            <div>His/Her birth day on 25/10/2024</div>
                          
                        </div>
                        <div style="background: #CCE3FF;padding: 10px;border-radius: 5px;">
                            <div><i class="fa-regular fa-cake-candles"></i> Wish thaisan a Happy Birthday!</div>
                            <div>His/Her birth day on 25/10/2024</div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<!-- ===================end============= !-->
<div class="row">
    <?php if ($Settings->module_sale || POS) { ?>
    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('overview_chart'), ' (' . date('M-Y', time()) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div id="ov-chart" style="width:100%; height:450px;"></div>
                        <p class="text-center"><?= lang('chart_lable_toggle'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
    <div class="col-sm-4">
        <div class="row">
            <?php if ($Settings->module_sale || POS) { ?>
            <div class="col-sm-6">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('sales') ?></h4>
                    <i class="icon fa fa-line-chart"></i>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_sales->sTotal_amount) ?></h3>
                    <p class="bold"><?= $total_sales->total . ' ' . lang('sales') ?> </p>
                    <table width="100%" class="text_title">
                        <tr>
                            <td width="30%"><?= lang('paid') ?></td>
                            <td>: <?= $this->bpas->formatMoney($total_sales->paid) ?></td>
                        </tr>
                        <tr>
                            <td><?= lang('tax') ?></td>
                            <td>: <?= $this->bpas->formatMoney($total_sales->tax) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('receivable') ?></h4>
                    <i class="icon fa fa-line-chart"></i>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_sales->sTotal_amount - $total_sales->paid) ?></h3>
                    <p class="bold"><?= $total_sales->total . ' ' . lang('sales') ?> </p>
                    <table width="100%" class="text_title">
                    
                    </table>
                </div>
            </div>
           
            <div class="col-sm-6">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('payment_received') ?></h4>
                    <i class="icon fa fa-usd"></i>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_received->total_amount) ?></h3>
                    <p class="bold"><?= $total_received->total . ' ' . lang('received') ?> </p>
                    <div style="width:100%" class="text_title">
                        <?php 
                        if($total_received_currencies){
                        foreach ($total_received_currencies as $currency){ ?>
                        <div style="width:20%;float: left;"><?= lang($currency->paid_by) ?></div>
                        <div style="width:30%;float: left;">: <?= $this->bpas->formatMoney($currency->amount) ?></div>
                        <?php } }?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
             <?php }?>
            <?php if ($Settings->module_purchase) { ?>
            <div class="col-sm-6">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('purchases') ?></h4>
                    <i class="icon fa fa-star"></i>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_purchases->total_amount) ?></h3>
                    <p class="bold"><?= $total_purchases->total . ' ' . lang('purchases') ?> </p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('payable') ?></h4>
                    <i class="icon fa fa-star"></i>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_purchases->total_amount-$total_purchases->paid) ?></h3>
                    <p class="bold"><?= $total_purchases->total . ' ' . lang('purchases') ?> </p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('expenses') ?></h4>
                    <i class="icon fa fa-usd"></i>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_expenses->total_amount) ?></h3>
                    <p class="bold"><?= $total_expenses->total . ' ' . lang('expenses') ?></p>

                    <p>&nbsp;</p>
                </div>
            </div>
            <div class="col-sm-12 hide">
                <div class="small-box padding1010 bgrey">
                    <h4 class="bold"><?= lang('payments_sent') ?></h4>
                    <hr>
                    <h3 class="bold"><?= $this->bpas->formatMoney($total_paid->total_amount) ?></h3>
                    <p><?= $total_paid->total . ' ' . lang('sent') ?></p>
                    <p>&nbsp;</p>
                </div>
            </div>
            <?php } ?>
            <?php if ($Settings->module_property) { ?>
            <div class="col-sm-12">
                <div class="small-box padding1010">
                    <h4 class="bold"><?= lang('properties') ?></h4>
                    <i class="icon fa fa-pie-chart"></i>
                    <h3 class="bold"><?= $this->bpas->formatQuantity($total_property->total) ?></h3>
                    <table width="100%" class="text_title">
                        <tr>
                            <td width="30%"><?= lang('booking') ?></td>
                            <td>: <?= $this->bpas->formatQuantity($total_status->booking); ?></td>
                        </tr>
                        <tr>
                            <td><?= lang('block') ?></td>
                            <td>: <?= $this->bpas->formatQuantity($total_status->blocking); ?></td>
                        </tr>
                        <tr>
                            <td><?= lang('sold') ?></td>
                            <td>: <?= $this->bpas->formatQuantity($total_status->sold); ?></td>
                        </tr>
                        <tr>
                            <td><?= lang('available') ?></td>
                            <td>: <?= $this->bpas->formatQuantity($total_status->available); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php }?>
        </div>
    </div>
</div>
<div class="row">
    <?php if($Settings->module_sale || POS){?>
    <div class="col-sm-6">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('profit_loss'), ' (' . date('M-Y', time()) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        
                        
                        <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
  
    <?php if ($Settings->module_purchase) { ?>
    <div class="col-sm-6">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('expenses'), ' (' . date('M-Y', time()) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div id="expenses" style="width:100%; height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
    <?php if($Settings->module_sale || POS){?>
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('revenue'), ' (' . date('M-Y', time()) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div id="revenue"  style="width:100%; height:380px;"></div>
                        <button id="plain">Plain</button>
                        <button id="inverted">Inverted</button>
                        <button id="polar">Polar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers'), ' (' . date('M-Y', time()) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div id="bschart" style="width:100%; height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers') . ' (' . date('M-Y', strtotime('-1 month')) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div id="lmbschart" style="width:100%; height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<?php } ?>
<?php if ($Settings->module_property) { ?>
    <div class="row">
        <div class="col-sm-4">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('lead_status') . ' (' . date('M-Y', strtotime('-1 month')) . ')'; ?></h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                        <div id="lead_status" style="width:100%; height:400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('source') . ' (' . date('M-Y', strtotime('-1 month')) . ')'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                    <div id="source_lead" style="width:100%; height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('schedule'), ' (' . date('M-Y', time()) . ')'; ?></h2>
        </div>
        <div class="box-content">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                        <tr>
                            <th style="width:5%;"><?= $this->lang->line("no"); ?></th>
                            <th style="width:12%;"><?= $this->lang->line("name"); ?></th>
                            <th style="width:17%;"><?= $this->lang->line("sale_reference"); ?></th>
                            <th style="width:10%;"><?= $this->lang->line("monthly"); ?></th>
                            <th style="width:10%;"><?= $this->lang->line("principal"); ?></th>
                            <th style="width:10%;"><?= $this->lang->line("interest"); ?></th>
                            <th style="width:12%;"><?= $this->lang->line("balance"); ?></th>
                            <th style="width:10%;"><?= $this->lang->line("status"); ?></th>
                            <th style="width:13%;"><?= $this->lang->line("repay_date"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) {
                        $r = 1;
                        foreach ($rows as $row) { 
                            $start = strtotime($row->pay_date); ?>
                        <tr>
                            <td><?= $r ?></td>
                            <td><?= $row->name; ?></td>
                            <td><?= $row->reference; ?></td>
                            <td><?= $this->bpas->formatMoney($row->monthly_payment); ?></td>
                            <td><?= $this->bpas->formatMoney($row->principal); ?></td>
                            <td><?= $this->bpas->formatMoney($row->interest); ?></td>
                            <td><?= $this->bpas->formatMoney($row->balance); ?></td>
                            <td><?= $this->lang->line($row->status); ?></td>
                            <td><?= $this->bpas->hrsd($row->pay_date); ?></td>
                        </tr>
                    <?php 
                    $r++; 
                    } 
                    } else {
                        echo "<tr><td colspan='5'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    <tfoot>
                        <tr>
                            <td colspan="8"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>
<?php } ?>
<?php if (($Owner || $Admin) && $chatData && ($Settings->module_sale || POS)) { ?>
    <style type="text/css" media="screen">
        .tooltip-inner {
            max-width: 500px;
        }
    </style>
    
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('Top_10_Sales_Chart'), ' (' . date('M-Y', time()) . ')'; ?></h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="pyramidsale" style="width:100%; height:400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('expense_categories'), ' (' . date('M-Y', time()) . ')'; ?></h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="expensechart" style="width:100%; height:400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('Net_Profit_Chart'), ' (' . date('M-Y', time()) . ')'; ?></h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="net_profit" style="width:100%; height:400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('Warehouse_Product_Chart'), ' (' . date('M-Y', time()) . ')'; ?></h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="wpchart" style="width:100%; height:400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
<?php } ?>
<script type="text/javascript">
    $(document).ready(function () {
      
        $('.order').click(function () {
            window.location.href = '<?=admin_url()?>orders/view/' + $(this).attr('id') + '#comments';
        });
        $('.invoice').click(function () {
            window.location.href = '<?=admin_url()?>orders/view/' + $(this).attr('id');
        });
        $('.quote').click(function () {
            window.location.href = '<?=admin_url()?>quotes/view/' + $(this).attr('id');
        });
   
        $('.clock_btn').click(function () {
            var date = $('#date').val();
            $.ajax({
                type: "get",
                url: "<?= admin_url('attendances/employee_check_in_out'); ?>",
                data: {
                    date: date,
                },
                success: function(data) {
                    return true;
                }
            });
            return false;
        }); 
    });
</script>
<script type="text/javascript">
<?php 
$dataPoints1 = array(
    array("label"=> "2019", "y"=> 72.50),
    array("label"=> "2020", "y"=> 81.30),
    array("label"=> "2021", "y"=> 63.60),
    array("label"=> "2022", "y"=> 69.38),
    array("label"=> "2023", "y"=> 98.70)
);                        
$dataPoints2 = array(
    array("label"=> "2019", "y"=> 40.30),
    array("label"=> "2020", "y"=> 35.30),
    array("label"=> "2021", "y"=> 39.50),
    array("label"=> "2022", "y"=> 50.82),
    array("label"=> "2023", "y"=> 74.70)
);  
?>
window.onload = function () {
    var chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        theme: "light2",
        // title:{
        //     text: "Profit AND Loss"
        // },
        axisY:{
            includeZero: true
        },
        legend:{
            cursor: "pointer",
            verticalAlign: "center",
            horizontalAlign: "right",
            itemclick: toggleDataSeries
        },
        colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
        data: [{
            type: "column",
            name: "Profit",
            indexLabel: "{y}",
            yValueFormatString: "$#0.##",
            showInLegend: true,
            dataPoints: <?php echo json_encode($dataPoints1, JSON_NUMERIC_CHECK); ?>
        },{
            type: "column",
            name: "Loss",
            indexLabel: "{y}",
            yValueFormatString: "$#0.##",
            showInLegend: true,
            dataPoints: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
        }]
    });
    chart.render();
    function toggleDataSeries(e){
        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        }
        else{
            e.dataSeries.visible = true;
        }
        chart.render();
    }
    //-----------------------
}
</script>
<script type="text/javascript">
    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
            };
        });
        $('#ov-chart').highcharts({
            chart: {},
            credits: {enabled: false},
            title: {text: ''},
            xAxis: {categories: <?= json_encode($months); ?>},
            yAxis: {min: 0, title: ""},
            tooltip: {
                shared: true,
                followPointer: true,
                formatter: function () {
                    if (this.key) {
                        return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                    } else {
                        var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                        $.each(this.points, function () {
                            s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                            currencyFormat(this.y) + '</b></td></tr>';
                        });
                        s += '</table></div>';
                        return s;
                    }
                },
                useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                style: {fontSize: '14px', padding: '0', color: '#000000'}
            },
            colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
            series: [
                {
                    type: 'column',
                    name: '<?= lang('sp_tax'); ?>',
                    data: [<?php echo implode(', ', $mtax1); ?>]
                },
                {
                    type: 'column',
                    name: '<?= lang('order_tax'); ?>',
                    data: [<?php echo implode(', ', $mtax2); ?>] 
                },
                {
                    type: 'column',
                    name: '<?= lang('sales'); ?>',
                    data: [<?php echo implode(', ', $msales); ?>]
                }, 
                {
                    type: 'spline',
                    name: '<?= lang('purchases'); ?>',
                    data: [<?php echo implode(', ', $mpurchases); ?>],
                    marker: {
                        lineWidth: 2,
                        states: {
                            hover: {
                                lineWidth: 4
                            }
                        },
                        lineColor: Highcharts.getOptions().colors[3],
                        fillColor: 'white'
                    }
                }, 
                {
                    type: 'spline',
                    name: '<?= lang('pp_tax'); ?>',
                    data: [<?php echo implode(', ', $mtax3); ?>],
                    marker: {
                        lineWidth: 2,
                        states: {
                            hover: {
                                lineWidth: 4
                            }
                        },
                        lineColor: Highcharts.getOptions().colors[3],
                        fillColor: 'blue'
                    }
                }, 
                {
                    type: 'pie',
                    name: '<?= lang('stock_value'); ?>',
                    data: [
                        ['', 0],
                        ['', 0],
                        ['<?= lang('stock_value_by_price'); ?>', <?php echo (isset($stock->stock_by_price) && !empty($stock)) ? $stock->stock_by_price : null; ?>],
                        ['<?= lang('stock_value_by_cost'); ?>', <?php echo (isset($stock->stock_by_cost) && !empty($stock)) ? $stock->stock_by_cost : null; ?>],
                    ],
                    center: [80, 42],
                    size: 80,
                    showInLegend: false,
                    dataLabels: {
                        enabled: false
                    }
                }
            ]
        });
    });
    $(function () {
        <?php if (isset($lmbs) && $lmbs) { ?>
        $('#lmbschart').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: false},
            colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
            series: [{
                name: '<?=lang('sold'); ?>',
                data: [<?php
                foreach ($lmbs as $r) {
                    if ($r->quantity > 0) {
                        echo "['" . str_replace(array('\'', '"'), '',$r->product_name). '<br>(' . $r->product_code . ")', " . $r->quantity . '],';
                    }
                } ?>],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#000',
                    align: 'right',
                    y: -25,
                    style: {fontSize: '12px'}
                }
            }]
        });
        <?php }
    if (isset($bs) && $bs) { ?>
        $('#bschart').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: false},
            colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
            series: [{
                name: '<?=lang('sold'); ?>',
                data: [<?php
            foreach ($bs as $r) {
                if ($r->quantity > 0) {
                    echo '["' . $r->product_name . "<br>(" . $r->product_code . ')", ' . $r->quantity . "],";
                }
            } ?>],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#000',
                    align: 'right',
                    y: -25,
                    style: {fontSize: '12px'}
                }
            }]
        });
        <?php
    } ?>
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $(function () {
            // Data retrieved from https://olympics.com/en/olympic-games/beijing-2022/medals
            $('#expensechart').highcharts({
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: ''
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45
                    }
                },
                colors: ['#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: 'Medals',
                    data: [
                         <?php 
                        if($getallexpenses){
                            foreach($getallexpenses as $stock){
                        ?>
                            ['<?php echo $stock->name.' ('.$this->bpas->formatMoney($stock->total).')'; ?>', <?php echo$this->bpas->formatMoney( $stock->total); ?>],
                        <?php
                            }
                        }else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 0; ?>],
                        <?php } ?>

                    ]
                }]
            });
     
            $('#wpchart').highcharts({
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 100
                    }
                },
                title: {
                    text: ''
                },
             
                plotOptions: {
                    pie: {
                        depth: 100
                    }
                },
                colors: ['#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: 'Medals',
                    data: [
                        <?php if($getallwarehousesproducts) {
                            foreach($getallwarehousesproducts as $stock) { ?>
                                ['<?php echo $stock->name.' ('.$stock->code.')'; ?>', <?php echo $stock->total_quantity; ?>],
                        <?php }
                        } else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 0; ?>],
                        <?php } ?>
                    ]
                }]
            });
            var colors = ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']; 

            $('#employees_department').highcharts({
                chart: {
                    type: 'pie',
                    options3d: {
                        alpha:100,
                        responsive: true,
                        maintainAspectRatio: false,
                        responsiveAnimationDuration:1000,
                    }
                },
                title: {
                    text: ''
                },   
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        depth: 100
                    },
                }, 
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: 'Medals',
                    data: [
                        <?php  if(isset($total_employees_department)) {
                            foreach($total_employees_department as $department){
                        ?>
                                ['<?php echo $department->name.' ('.$department->total.')'; ?>', <?php echo $department->total; ?>],
                        <?php  }
                        }  else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 0; ?>],
                        <?php }?>
                    ]
                }],
            });

            $('#employees_position').highcharts({
                credits: { enabled: false },
                chart: {
                    type: 'pie',
                    plotBackgroundColor: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                },
                title: {
                    text: ''
                },
                // subtitle: {
                //     text: '3D donut in Highcharts'
                // },
                // plotOptions: {
                //     pie: {
                //         innerSize: '60%'
                //     },
                // },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: 'Medals',
                    data: [
                        <?php   
                        if(isset($total_employees_position)){
                            foreach($total_employees_position as $position){
                        ?>
                                ['<?php echo $position->name.' ('.$position->total.')'; ?>', <?php echo $position->total; ?>],
                        <?php  }
                        }  else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 0; ?>],
                        <?php }?>
                    ]
                }],
            });
       
                //--------------
            $('#expenses').highcharts({
                chart: {
                    type: 'bar'
                },
                title: {
                    text: '.'
                },
                xAxis: {
                    categories: [
                            <?php 
                                for ($i=0; $i < 12; $i++){ 
                                    $month = date('m', strtotime("-{$i} month"));
                                    $monthName = date('F', mktime(0, 0, 0, $month, 10));
                                    $m= ($i == 11 ? "'$monthName'" : ("'$monthName'" . ', '));
                                    echo $m;
                                }
                        ?>
                    ]
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Goals'
                    }
                },
                legend: {
                    reversed: true
                },
                plotOptions: {
                    series: {
                        stacking: 'normal'
                    }
                },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: 
                [
                    <?php 
                    foreach ($Categories as $category) { ?>
                    {
                        name: '<?= $category->name ?>',
                        data: [
                            <?php 
                                for ($i=0; $i < 12; $i++){ 
                                    $month = date('m', strtotime("-{$i} month"));
                                    $TotalExapenses = $this->reports_model->getTotalExapensesByCategory_Month($category->id, $month);
                                    $amount         = $TotalExapenses ? $TotalExapenses->total_amount : 0;
                                    echo ($i == 11 ? $amount : ($amount . ', '));
                            } ?>
                        ]
                    },
                    <?php } ?>
                ]
            });
            const chart = Highcharts.chart('revenue', {
                title: {
                    text: '.'
                },
                xAxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    type: 'column',
                    name: '<?= lang('grand_total');?>',
                    colorByPoint: true,
                    data: [
                            <?php 
                                 $array = array('01','02','03','04','05','06','07','08','09','10','11','12');
                                 for($i=0 ; $i < 12 ;$i++){
                                     $month = $array[$i];
                                     $TotalRevenue = $this->reports_model->getAllGreandtotalSaleBy_Month($month);
                                     $amount         = $TotalRevenue ? $TotalRevenue->total_amount : 0;
                                     echo ($i == 11 ? $this->bpas->formatDecimal($amount) : ($this->bpas->formatDecimal($amount) . ', '));
                                 }
                            ?>
                        ],
                    showInLegend: false
                }]
            });
            document.getElementById('plain').addEventListener('click', () => {
                chart.update({
                    chart: {
                        inverted: false,
                        polar: false
                    },
                    colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                    subtitle: {
                        text: 'Chart option'
                    }
                });
            });
            document.getElementById('inverted').addEventListener('click', () => {
                chart.update({
                    chart: {
                        inverted: true,
                        polar: false
                    },
                    colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                    subtitle: {
                        text: 'Chart option: Inverted'
                    }
                });
            });
            document.getElementById('polar').addEventListener('click', () => {
                chart.update({
                    chart: {
                        inverted: false,
                        polar: true
                    },
                    colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                    subtitle: {
                        text: 'Chart option: Polar '
                    }
                });
            });
            $('#pyramidsale').highcharts({
                chart: {
                    type: 'pyramid'
                },
                title: {
                    text: 'Sales pyramid',
                    x: -50
                },
                plotOptions: {
                    series: {
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b> ({point.y:,.0f})',
                            softConnector: true
                        },
                        center: ['40%', '50%'],
                        width: '80%'
                    }
                },
                legend: {
                    enabled: false
                },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: 'Unique users',
                    data: [
                        <?php 
                        if($gettop10sale){
                            foreach($gettop10sale as $stock){
                        ?>
                                ["<?php echo $stock->product_name.' ('.$stock->product_code.')'; ?>", <?php echo $stock->quantity; ?>],
                        <?php }
                        } else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 0; ?>],
                        <?php } ?>
                    ]
                }],
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            plotOptions: {
                                series: {
                                    dataLabels: {
                                        inside: true
                                    },
                                    center: ['50%', '50%'],
                                    width: '100%'
                                }
                            }
                        }
                    }]
                }
            });
            $("#attendance_overview").highcharts({
                chart: {
                    type: 'line'
                },
                title: {
                    text: 'Monthly Average Temperature'
                },
                subtitle: {
                    text: 'source:'
                },
                xAxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
                },
                yAxis: {
                    title: {
                        text: 'Temperature (°C)'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true
                        },
                        enableMouseTracking: false
                    }
                },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: '.',
                    data: [1, 3.6, 1.6, 4.8, 10.2, 14.5]
                }]
            });
            $("#net_profit").highcharts({
                chart: {
                    type: 'line'
                },
                title: {
                    text: 'Monthly Average Temperature'
                },
                subtitle: {
                    text: 'source:'
                },
                xAxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
                },
                yAxis: {
                    title: {
                        text: 'Temperature (°C)'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true
                        },
                        enableMouseTracking: false
                    }
                },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    name: '.',
                    data: [1, 3.6, 1.6, 4.8, 10.2, 14.5]
                }]
            });
            <?php if($Settings->module_crm){?>
            $("#lead_status").highcharts({
                credits: {
                    enabled: false
                        },
                chart: {
                    type: 'pie',
                    plotBackgroundColor: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                    },
                title: {
                    text: ''
                },
                colors:['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
                series: [{
                    type: 'pie',
                    allowPointSelect: true,
                    keys: ['name', 'y', 'selected', 'sliced'],
                    showInLegend: true,
                    name: 'Lead Status',
                    data: [
                        <?php   
                        if($lead_status){
                            foreach($lead_status as $lead_status_value){
                        ?>
                                ['<?php echo $lead_status_value->group_name.' ('.$lead_status_value->count.')'; ?>', <?php echo $lead_status_value->count; ?>],
                        <?php  }
                        }  else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 1; ?>],
                        <?php }?>
                    ]
                }],
            });

            $("#source_lead").highcharts({
                credits: {
                    enabled: false
                        },
                chart: {
                    type: 'pie',
                    plotBackgroundColor: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                    },
                title: {
                    text: ''
                },
                colors:['#48d1cc','#9370db','#acace6','#30bfbf','#ff34b3','#f653a6','#8470ff','#20b2aa','#ffaeb9'],
                series: [{
                    type: 'pie',
                    allowPointSelect: true,
                    keys: ['name', 'y', 'selected', 'sliced'],
                    showInLegend: true,
                    name: 'Source Lead',
                    data: [
                        <?php if ($source_lead) {
                            foreach($source_lead as $source_lead_value){
                        ?>
                                ['<?php echo $source_lead_value->source.' ('.$source_lead_value->count.')'; ?>', <?php echo $source_lead_value->count; ?>],
                        <?php  }
                        }  else { ?>
                            ['<?php echo $this->lang->line('nothing_found'); ?>', <?php echo 1; ?>],
                        <?php }?>
                    ]
                }],
            });
            <?php }?>
        });
    });
</script>