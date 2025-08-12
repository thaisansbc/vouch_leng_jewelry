<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
	$v = "";
	$v2 = "";

	if ($this->input->post('group')) {
		$v .= "&group=" . $this->input->post('group');
	}
	if ($this->input->post('month')) {
		$v .= "&month=" . $this->input->post('month');
        $v2 .= "&month=" . $this->input->post('month');
    }
    if ($this->input->post('year')) {
		$v .= "&year=" . $this->input->post('year');
		$v2 .= "&year=" . $this->input->post('year');
	}
	if ($this->input->post('warehouse')) {
		$v .= "&warehouse=" . $this->input->post('warehouse');
	}
?>

<script>
    $(document).ready(function () {
        function f_user_status(x){
            var value = x.split('__');
            if(value[0] == 1){
                return '<div class="text-center"><span class="row_status label label-success"><i class="fa fa-check" aria-hidden="true"></i> ' + lang['active'] + '</span></div>';
            } else {
                return '<div class="text-center"><span class="row_status label label-danger"><i class="fa fa-times" aria-hidden="true"></i> ' + lang['inactive'] + '</span></div>';
            }
        }
        function f_gen_status(x){
            if(x == 0){
                return '<div class="text-center"><span class="row_status label label-warning"><?= lang('unpaid') ?></span></div>';
            } else {
                return '<div class="text-center"><span class="row_status label label-success"><?= lang('paid') ?></span></div>';
            }
        }

        'use strict';
        oTable = $('#UsrTable').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('payroll/getUsers/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, 
                null, null, null, null, null, null, null, null, 
                {"mRender": f_user_status}, {"mRender": f_gen_status},
            ],
            
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('email');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
            {
                column_number: 9, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('active_status');?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: '1', label: '<?=lang('active');?>'}, {value: '0', label: '<?=lang('inactive');?>'}]
            },
            {
                column_number: 10, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('payment_status');?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: '1', label: '<?=lang('paid');?>'}, {value: '0', label: '<?=lang('unpaid');?>'}]
            },
            // {column_number: 10, filter_default_label: "[<?=lang('generate_status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<style>
    .table td:nth-child(6), .table td:nth-child(7), .table td:nth-child(8) {
        text-align: right;
        width: 10%;
    }
    #dtFilter-filter--UsrTable-10 {
        text-align: center;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('all_users'); ?></h2>
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
        <!-- <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="excel" data-action="export_excel" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div> -->
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-money"></i><?= lang('generate_payslip') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                <?php echo admin_form_open('payroll'); ?>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="control-label" for="group"><?= lang('role'); ?></label>
                            <?php
                            $gr[''] = lang('select') . ' ' . lang('role');
                            foreach ($groups as $group) {
                                $gr[$group->id] = $group->name;
                            }
                            echo form_dropdown('group', $gr, (isset($_POST['group']) ? $_POST['group'] : ''), 'class="form-control" id="group" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('role') . '"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="control-label" for="group"><?= lang('month'); ?></label>
                            <?php
                            $cur_mth = date('F');
                            foreach ($months as $key => $month) {
                                $mth[$month] = $month;
                            }
                            echo form_dropdown('month', $mth, (isset($_POST['month']) ? $_POST['month'] : $cur_mth), 'class="form-control" id="month" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('month') . '"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="control-label" for="year"><?= lang('year'); ?></label>
                            <?php
                                foreach ($years as $key => $year) {
                                    $yr[$key] = $year;
                                }
                            ?>
                            <?php echo form_dropdown('year', $yr, (isset($_POST['year']) ? $_POST['year'] : ''), 'class="form-control" id="year" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('year') . '"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                            <?php
                            $warehouses = array_reverse($warehouses);
                            $wh[''] = lang('select') . ' ' . lang('warehouse');
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="controls"><?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?></div>
                </div>
            </div>
            <?php echo form_close(); ?>
            <?php if ($Owner || $GP['bulk_actions']) {
                echo admin_form_open('payroll/user_actions/?v=1' . $v2, 'id="action-form"');
            } ?>

            <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="UsrTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="width: 10%;"><?php echo lang('name'); ?></th>
                                <th><?php echo lang('gender'); ?></th>
                                <th style="width: 10%;"><?php echo lang('phone'); ?></th>
                                <th style="width: 10%;"><?php echo lang('email'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('award_points'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('basic_salary'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('commission'); ?></th>
                                <th><?php echo lang('group'); ?></th>
                                <th style="text-align: center !important;"><?php echo lang('active_status'); ?></th>
                                <th style="text-align: center !important;"><?php echo lang('payment_status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="text-align: right !important;"><?php echo lang('award_points'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('basic_salary'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('commission'); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>