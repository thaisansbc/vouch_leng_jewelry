<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('type')) {
    $v .= "&type=" . $this->input->post('type');
}
if ($this->input->post('payment_status')) {
    $arr = array();
    $arr = $this->input->post('payment_status');
    $get_status = "";
    $get_status = implode('_', $arr);
    $v .= "&payment_status=" . $get_status;
} ?>
<script>
    $(document).ready(function() {
        function pqFormat1(x) {
            if (x != null) {
                var d = '',
                    pqc = x.split('___');
                for (index = 0; index < pqc.length; ++index) {
                    var pq = pqc[index];
                    var v = pq.split('__');
                    d += v[0] + ' (' + formatQuantity2(v[1]) + ')<br>'+'('+ v[2]+')<br>';
                }
                return d;
            } else {
                return '';
            }
        }
        oTable = $('#SLData').dataTable({
            "aaSorting": [[2, "desc"], [3, "desc"]],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getRewardsExchangeCustomer/?v=1' . $v) ?>',
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
                nRow.id = aData[12];
                nRow.className = "reward_exchange_link";
                if (aData[11] == 'product') {
                    nRow.className = "reward_exchange_link warning";
                } else if (aData[11] == 'money') {
                    nRow.className = "reward_exchange_link danger";
                }
                var action = $('td:eq(15)', nRow);              
                if (aData[9] == 'completed' && aData[13] == 'paid') {
                    action.find('.edit').remove();
                    action.find('.delete').remove();
                    action.find('.add_payment').remove();
                }
                return nRow;
            },
            "aoColumns": [
                { "mRender": fld },
                null,
                null,
                null,
                {"bSearchable": false, "mRender": pqFormat },
                {"bSearchable": false, "mRender": pqFormat },
                { "mRender": row_status },
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                { "mRender": pay_status },
            ],
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal  = 0,
                    paid    = 0,
                    balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal  += parseFloat(aaData[aiDisplay[i]][7]);
                    paid    += parseFloat(aaData[aiDisplay[i]][8]);
                    balance += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                nCells[9].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?= lang('reference_no'); ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('biller'); ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('customer'); ?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?= lang('exchange_product'); ?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?= lang('Receive Product'); ?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?= lang('status'); ?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?= lang('payment_status'); ?>]", filter_type: "text", data: []}
        ], "footer");
        $(document).on('click', '.slduplicate', function(e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?= lang('you_will_loss_data') ?>", function(result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
        $(document).ready(function() {
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
<style type="text/css">
    body {
        position: static !important;
        overflow-y: auto !important;
    }
    #dtFilter-filter--SLData-6, #dtFilter-filter--SLData-10 {
        text-align: center !important;
    }
</style>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('rewards_exchange_customer_reports');?></h2>
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
            <li class="dropdown">
                <a href="#" id="xls2" class="tip" title="<?= lang('download_xls_detial') ?>">
                    <i class="icon fa fa-file-excel-o"></i>
                </a>
            </li>
            <li class="dropdown ">
                <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                    <i class="icon fa fa-file-excel-o"></i>
                </a>
             </li>
        </ul>
    </div>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("reports/customer_rewards_exchange_report"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
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
                        <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
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
                        <?php } elseif (!empty($billers) && count($count_billers) > 1) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                    $bl[""] = "";
                                    $biller_id_ = $count_billers;
                                    foreach ($billers as $biller) {
                                        foreach ($biller_id_ as $key => $value) {
                                            if ($biller->id == $value) {
                                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                            }
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if (($this->Owner || $this->Admin) || !$this->session->userdata('warehouse_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"'); ?>
                            </div>
                        </div>
                        <?php } elseif (!empty($warehouses) && count($count_warehouses) > 1) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse) {
                                    foreach ($count_warehouses as $key => $value) {
                                        if ($warehouse->id == $value) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                    }
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("payment_status", "payment_status"); ?>
                                <?php
                                $payment_status = array('due' => 'Due', 'pending' => 'Pending', 'paid' => 'Paid');
                                echo form_dropdown('payment_status[]', $payment_status, (isset($_POST['payment_status']) ? $_POST['payment_status'] : ''), 'id="payment_status" class="form-control select" placeholder="Please Status" style="width:100%;" multiple="multiple"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date" autocomplete="off"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date" autocomplete="off"'); ?>
                            </div>
                        </div> 
                         <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("type", "type"); ?>
                                <?php
                                $type = array('' => '', 'money' => lang('money'), 'product' => lang('product'));
                                echo form_dropdown('type', $type, (isset($_POST['type']) ? $_POST['type'] : ''), 'id="type" class="form-control select" placeholder="Please Type" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('products/rewards_exchange_actions', 'id="action-form"');
                } ?>
                <div class="table-responsive">
                    <table id="SLData" class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                            <tr>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('reference_no'); ?></th>
                                <th><?= lang('biller'); ?></th>
                                <th><?= lang('customer'); ?></th>
                                <th><?= lang('exchange_product'); ?></th>
                                <th><?= lang('receive_product'); ?></th>
                                <th style="text-align: center !important;"><?= lang('status'); ?></th>
                                <th style="text-align: right !important;"><?= lang('grand_total'); ?></th>
                                <th style="text-align: right !important;"><?= lang('paid'); ?></th>
                                <th style="text-align: right !important;"><?= lang('balance'); ?></th>
                                <th style="text-align: center !important;"><?= lang('payment_status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="dataTables_empty"><?= lang('loading_data'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th><?= lang('grand_total'); ?></th>
                                <th><?= lang('paid'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getRewardsExchangeCustomer/xls/?v=1' . $v)?>";
            return false;
        });
        $('#xls2').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getRewardsExchangeCustomer/0/xls2/?v=1' . $v)?>";
            return false;
        });
    });
</script>