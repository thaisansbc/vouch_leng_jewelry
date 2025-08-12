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
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
} ?>
<script>
    $(document).ready(function() {
        oTable = $('#SLData').dataTable({
            "aaSorting": [[2, "desc"], [3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('deliveries/getShippingRequest' . ($biller_id ? '/' . $biller_id : '') . '?v=1' . $v); ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                return nRow;
            },
            "aoColumns": [
                { "bSortable": false, "mRender": checkbox },
                { "mRender": fld }, 
                null, null, null, null, null, null, null,
                { "mRender": row_status },
                { "bSortable": false }  
            ]
        }).fnSetFilteringDelay().dtFilter([
            { column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: [] },
            { column_number: 2, filter_default_label: "[<?= lang('reference_no'); ?>]", filter_type: "text", data: [] },
            { column_number: 3, filter_default_label: "[<?= lang('biller'); ?>]", filter_type: "text", data: [] },
            { column_number: 4, filter_default_label: "[<?= lang('warehouse'); ?>]", filter_type: "text", data: [] },
            { column_number: 5, filter_default_label: "[<?= lang('customer'); ?>]", filter_type: "text", data: [] },
            { column_number: 6, filter_default_label: "[<?= lang('phone'); ?>]", filter_type: "text", data: [] },
            { column_number: 7, filter_default_label: "[<?= lang('address'); ?>]", filter_type: "text", data: [] },
            { column_number: 8, filter_default_label: "[<?= lang('note'); ?>]", filter_type: "text", data: [] },
            { column_number: 9, filter_default_label: "[<?= lang('status'); ?>]", filter_type: "text", data: [] }
        ], "footer");
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
</style>
<div class="breadcrumb-header">
    <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
    <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('shipping_request') . ' (' . $biller_title . ')';?></h2>
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
            <li class="dropdown">
                <a href="#" id="excel" title="<?= lang('excel') ?>" data-action="export_excel">
                    <i class="icon fa fa-file-excel-o"></i> 
                </a>
            </li>
        </ul>
    </div>
    <div class="box-icon">
        <ul class="btn-tasks">
            <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('deliveries/shipping_request') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($billers as $biller) {
                            echo '<li><a href="' . admin_url('deliveries/shipping_request/' . $biller->id) . '"><i class="fa fa-industry"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
            <?php } elseif (!empty($billers)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('deliveries/shipping_request') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        $biller_id_ = $count_billers;
                        foreach ($billers as $biller) {
                            foreach ($biller_id_ as $key => $value) {
                                if ($biller->id == $value) {
                                    echo '<li><a href="' . admin_url('deliveries/shipping_request/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
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
                    <?php echo admin_form_open("deliveries/shipping_request"); ?>
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
                                $wh[""] = $this->lang->line("select") . " " . $this->lang->line("warehouse");
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
                                $wh[""] = $this->lang->line("select") . " " . $this->lang->line("warehouse");
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
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('deliveries/shipping_request_actions', 'id="action-form"');
                } ?>
                <div class="table-responsive">
                    <table id="SLData" class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('reference_no'); ?></th>
                                <th><?= lang('biller'); ?></th>
                                <th><?= lang('warehouse'); ?></th>
                                <th><?= lang('customer'); ?></th>
                                <th><?= lang('phone'); ?></th>
                                <th><?= lang('address'); ?></th>
                                <th><?= lang('note'); ?></th>
                                <th><?= lang('status'); ?></th>
                                <th style="width: 80px; text-align: center;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="dataTables_empty"><?= lang('loading_data'); ?></td>
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
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
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