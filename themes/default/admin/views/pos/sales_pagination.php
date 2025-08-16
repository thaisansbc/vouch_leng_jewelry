<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        /*#myModal .modal-content {
            display: none !important;
        }*/

        .printfield {
            display: none !important;
        }

        #POSData {
            display: none !important;
        }

        .date1 {
            display: none !important;
        }

        .date2 {
            display: block !important;
        }

        /* .dtFilter {
            display: block !important;
        } */

        .table-responsive {
            display: block !important;
        }

        /* td .sorting_1 {
            display: compact !important;
        }*/

    }
</style>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('saleman')) {
    $v .= "&saleman=" . $this->input->post('saleman');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('delivered_by')) {
    $v .= "&delivered_by=" . $this->input->post('delivered_by');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= "&serial=" . $this->input->post('serial');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
    $start_date = $this->input->post('start_date');
} else {
    $start_date = null;
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
    $end_date = $this->input->post('end_date');
} else {
    $start_date = null;
}
if ($this->input->post('product_id')) {
    $v .= "&product_id=" . $this->input->post('product_id');
}
if (isset($date)) {
    $v .= "&d=" . $date;
}
?>
<?php //if ($Owner || $GP['bulk_actions']) {
//echo admin_form_open('sales/sale_actions', 'id="action-form"');
//} 
?>
<script>
    $(document).ready(function() {
        $(document).on('click', '.duplicate_pos', function(e) {
            e.preventDefault();
            var link = $(this).attr('href');
            if (localStorage.getItem('positems')) {
                bootbox.confirm("<?= $this->lang->line('leave_alert') ?>", function(gotit) {
                    if (gotit == false) {
                        return true;
                    } else {
                        window.location.href = link;
                    }
                });
            } else {
                window.location.href = link;
            }
        });
        $(document).on('click', '.email_receipt', function() {
            var sid = $(this).attr('data-id');
            var ea = $(this).attr('data-email-address');
            var email = prompt("<?= lang('email_address'); ?>", ea);
            if (email != null) {
                $.ajax({
                    type: "post",
                    url: "<?= admin_url('pos/email_receipt') ?>/" + sid,
                    data: {
                        <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>",
                        email: email,
                        id: sid
                    },
                    dataType: "json",
                    success: function(data) {
                        bootbox.alert(data.msg);
                    },
                    error: function() {
                        bootbox.alert('<?= lang('ajax_request_failed'); ?>');
                        return false;
                    }
                });
            }
        });

        $(document).ready(function () {
            $('#delivered_by').select2(); 
        });

        $('#form').hide();
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<div class="breadcrumb-header">
 
    <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
    <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('pos_sales') . ' (' . $biller_title . ')';?></h2>
    </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('pos') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_sale') ?></a></li>
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <?php
                        if($this->Settings->accounting){
                        ?>
                        <li>
                            <a href="#" id="sync_account" data-action="sync_account">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_account') ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li><a href="#" id="preview" data-action="preview"><i class="fa fa-file-excel-o"></i> <?= lang('preview') ?></a></li>
                        <li><a href="#" id="view_multi_invoices" data-action="view_multi_invoices"><i class="fa fa-print"></i> <?= lang('view_multi_invoices') ?></a></li>
                        <?php
                        if($this->Settings->module_tax){
                        ?>
                        <li><a href="#" id="excel" data-action="declare_tax">
                            <i class="fa fa-plus-circle"></i> <?= lang('declare_tax') ?></a></li>
                        <?php }?>
                        <li class="divider"></li>
                        <li><a href="#" class="bpo" title="<b><?= $this->lang->line('delete_sales') ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_sales') ?></a></li>
                    </ul>
                </li>
                <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('sales') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('sales/' . $biller->id) . '"><i class="fa fa-industry"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('sales') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = $count_billers;
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id == $value) {
                                        echo '<li><a href="' . admin_url('sales/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                                    }
                                }
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("pos/sales"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                $pr[""] = "";
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code;
                                }
                                echo form_dropdown('product_id', $pr, (isset($_POST['product_id']) ? $_POST['product_id'] : ""), 'class="form-control" id="product_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4 hide">
                            <div class="form-group">
                                <?= lang("saleman", "saleman"); ?>
                                <?php
                                $salemans['0'] = lang("all");
                                foreach ($agencies as $agency) {
                                    $salemans[$agency->id] = $agency->username;
                                }
                                echo form_dropdown('saleman', $salemans, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'id="saleman" class="form-control saleman"');
                                ?>
                                </select>

                            </div>
                        </div>

                        <?php if ($this->Owner || $this->Admin || $this->session->userdata('view_right')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = "";
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } else {
                            echo form_hidden('user', $this->session->userdata('user_id'),'class="form-control"'); 
                        }
                        ?>
                        <?php
                        if ($this->Owner || $this->Admin) {
                        ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = "";
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php }?>
                        <?php if ($warehouses != NULL){?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                            <?php }?>
                        <?php if ($this->Settings->product_serial) { ?>
                            <div class="col-sm-4 hide">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('delivered_by', 'delivered_by'); ?>
                                <?php
                                $opt[''] = '';
                                foreach ($drivers as $driver) {
                                                $opt[$driver->id] = $driver->name;
                                            }
                                ?>
                                <?= form_dropdown('delivered_by', $opt,(isset($_POST['delivered_by']) ? $_POST['delivered_by'] : ""), 'class="form-control" id="delivered_by"  style="width:100%;" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("driver") . '"'); ?>
                                
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('sales/sale_actions', 'id="action-form"');
                } ?>
                    <input type="hidden" name="start_date" value="<?= $start_date; ?>">
                    <input type="hidden" name="end_date" value="<?= $start_date; ?>">
                <?php

                if ($start_date) {
                ?>
                    <center class="date1"><strong>Report <?= $start_date; ?> To <?= $end_date; ?></strong></center>
                    <span style="display:none" class="date2">Date: <?= $start_date; ?> - <?= $end_date; ?></span>
                    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                        <i class="fa fa-print"></i> <?= lang('print'); ?>
                    </button>
                <?php } ?>
       
            <div class="table-responsive">
                <table id="POSData" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check" />
                            </th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('reference_no'); ?></th>
                            <th><?= lang('biller'); ?></th>
                            <th><?= lang('customer'); ?></th>
                            <th><?= lang('driver'); ?></th>
                            <th><?= lang('grand_total'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('balance'); ?></th>
                            <th><?= lang('sale_status'); ?></th>
                            <th><?= lang('payment_status'); ?></th>
                            
                            <th class="printfield" style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        echo $datas;
                        ?>
                    </tbody>
                </table>
                <div class="row"> 
                    <div class="col-md-6 text-left">
                        <?php //echo $showing;?>
                    </div>
                    <div class="col-md-6  text-right">
                        <div class="dataTables_paginate paging_bootstrap">
                            <?= $pagination; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {
?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php
} ?>
<script>
    
    $(document).ready(function() {
        function balance(x) {
            if (!x) {
                return 0.00;
            }
            var b = x.split('__');
            var total = formatNumber(parseFloat(b[0]));
            var rounding = formatNumber(parseFloat(b[1]));
            var paid = formatNumber(parseFloat(b[2]));
            // alert(total + rounding - paid);
            return currencyFormat(total + rounding - paid);
        }
        $(document).on('click', '#view_multi_invoices', function(e) {
                e.preventDefault();
                var arrItems = [];
                var k = 0;
                $('.checkbox').each(function(i){
                    if($(this).is(":checked")){
                        if(this.value != "" && this.value != "on" && this.value != "null"){
                            arrItems[k] = $(this).val();
                            k++;
                        } 
                    }
                });
                window.location.replace('<?= site_url("admin/pos/view_multi_invoices");?>?data=' + arrItems + '');
        });
        
    });
</script>