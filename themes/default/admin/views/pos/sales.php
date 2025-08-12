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
<script>
    

    $(document).ready(function() {
        function balance__(x) {
            if (!x) {
                return 0.00;
            }
            var b = x.split('__');
            var total = formatNumber(parseFloat(b[0]));
            var rounding = formatNumber(parseFloat(b[1]));
            var paid = formatNumber(parseFloat(b[2]));
            return currencyFormat(total - paid);
        }

        oTable = $('#POSData').dataTable({
            "aaSorting": [
                [1, "desc"],
                [2, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('pos/getSales' . ($warehouse_id ? '/' . $warehouse_id : '') . '/?v=1' . $v) ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "receipt_link";

                var action = $('td:eq(12)', nRow);
                if(aData[10] =="completed" || aData[10] == "making"){
                    action.find('.making').remove();
                }

                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {
                "mRender": fld
            }, null, null, null, null, {
                "mRender": currencyFormat
            }, {
                "mRender": currencyFormat
            }, {
                "mRender": currencyFormat
            }, {
                "mRender": row_status
            }, {
                "mRender": row_status
            }, {
                "mRender": pay_status
            }, {
                "bSortable": false
            }],
            // "fnCreatedRow": function(row, data, index) {
            //     $('td', row).eq(0).html(index + 1);
            // },
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                // alert(aiDisplay);
                var gtotal = 0,
                    paid = 0,
                    balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                nCells[8].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('reference_no'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('biller'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('customer'); ?>]",
                filter_type: "text"
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('driver'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 9,
                filter_default_label: "[<?= lang('sale_status'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 10,
                filter_default_label: "[<?= lang('produce_status'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 11,
                filter_default_label: "[<?= lang('payment_status'); ?>]",
                filter_type: "text",
                data: []
            },
            
        ], "footer");

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

<?php //if ($Owner || $GP['bulk_actions']) {
//echo admin_form_open('sales/sale_actions', 'id="action-form"');
//} 
?>
<div class="breadcrumb-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('pos_sales') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
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
                <?php if ($this->Owner || $this->Admin) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('pos/sales') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('pos/sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('pos/sales') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $warehouse_id = explode(',', $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                foreach ($warehouse_id as $key => $value) {
                                    if ($warehouse->id == $value) {
                                        echo '<li><a href="' . admin_url('pos/sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
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
                <center>
                    <div class="table-responsive " style="display: none;">
                        <table id="POSDatas">
                            <thead>
                                <tr class="border-bottom border-top">
                                    <th>
                                        <center></center><?= lang('No'); ?>

                                    </th>
                                    <th>
                                        <span class="pull-center"><?= lang('Time'); ?>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="pull-left"><?= lang('Invoice'); ?>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="pull-right"><?= lang('Net'); ?>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="pull-right"><?= lang('Dis'); ?>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="pull-right"><?= lang('Bal'); ?>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tfoot class="dtFilter">
                                <tr>
                                    <center>
                                        <th class="border-bottom"></th>
                                        <th class="border-bottom"></th>
                                        <th class="border-bottom"><?= lang('TOTAL'); ?></th>
                                        <th class="border-bottom"></th>
                                        <th class="border-bottom"></th>
                                        <th class="border-bottom"></th>
                                    </center>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </center>
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
                            <th><?= lang('produce_status'); ?></th>
                            <th><?= lang('payment_status'); ?></th>
                            
                            <th class="printfield" style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data'); ?></td>
                        </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check" />
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang('grand_total'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('balance'); ?></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th class="printfield" style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                    </tfoot>
                </table>
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

        aoTable = $('#POSDatas').dataTable({
            'bProcessing': true,
            'bServerSide': true,
            "iDisplayLength": -1,
            'sAjaxSource': '<?= admin_url('pos/getSalesPrinting' . ($warehouse_id ? '/' . $warehouse_id : '') . '/?v=1' . $v) ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSettings = aoTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "receipt_link";
                return nRow;
            },

            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox,
                // "sWidth": "10%",
            }, {
                "aTargets": [3],
                "sType": "date",
                "mRender": function(oObj) {
                    if (oObj != null) {
                        var aDate = oObj.split('-');
                        var bDate = aDate[2].split(' ');
                        (year = aDate[0]), (month = aDate[1]), (day = bDate[0]), (time = bDate[1]);
                        if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return time;
                        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return time[0] + time[1] + time[2] + time[3] + time[4];
                        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return time[0] + time[1] + time[2] + time[3] + time[4];
                        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return time[0] + time[1] + time[2] + time[3] + time[4];
                        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return time[0] + time[1] + time[2] + time[3] + time[4];
                        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return time[0] + time[1] + time[2] + time[3] + time[4];
                        else return oObj;
                    } else {
                        return '';
                    }
                },
            }, null, {
                "mRender": function(x) {
                    return '<div class="text-right" background-color:yellow" >' + formatMoney(x != null ? x : 0) + '</div>';
                }
            }, {
                "mRender": function(x) {
                    return '<div class="text-right">' + formatMoney(x != null ? x : 0) + '</div>';
                }
            }, {
                "mRender": balance,
            }],
            "fnCreatedRow": function(row, data, index) {
                $('td', row).eq(0).html(index + 1);
            },
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                // alert(aiDisplay);
                var gtotal = 0,
                    balance = 0;
                discount = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][3]);
                    discount += parseFloat(aaData[aiDisplay[i]][4]);
                    balance += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[3].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[4].innerHTML = currencyFormat(parseFloat(discount));
                nCells[5].innerHTML = currencyFormat(parseFloat(balance));
            }
        })

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