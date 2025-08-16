<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
	$v = "";

	if ($this->input->post('user')) {
		$v .= "&user=" . $this->input->post('user');
	}
	if ($this->input->post('month')) {
		$v .= "&month=" . $this->input->post('month');
    }
    if ($this->input->post('year')) {
		$v .= "&year=" . $this->input->post('year');
	}
?>

<script>
    $(document).ready(function () {
        'use strict';

        function fd(x){
            var arr = x.split(' ');
            return arr[0].split('-').reverse().join('/') + ' ' + arr[1];
        }
        function fstatus(x){
            if(x == 1){
                return '<div class="text-center"><span class="row_status label label-success">' + lang['paid'] + '</span></div>';
            } else {
                return '<div class="text-center"><span class="row_status label label-warning">' + lang['unpaid'] + '</span></div>';
            }
        }

        oTable = $('#UsrTable').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getSalaryReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, 
                { "mRender": fd }, 
                null, 
                { "mRender": currencyFormat }, 
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                { "mRender": currencyFormat },
                null,
                { "mRender": fstatus },
            ],
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var basic_salary = 0, total_allowance = 0, total_deduction = 0, leave_deduction = 0, tax = 0, commission = 0, net_salary = 0;
                for (var i = 0; i < aaData.length; i++) {
                    basic_salary    += parseFloat(aaData[aiDisplay[i]][3]);
                    total_allowance += parseFloat(aaData[aiDisplay[i]][4]);
                    total_deduction += parseFloat(aaData[aiDisplay[i]][5]);
                    total_allowance += parseFloat(aaData[aiDisplay[i]][6]);
                    tax             += parseFloat(aaData[aiDisplay[i]][7]);
                    commission      += parseFloat((aaData[aiDisplay[i]][8] != null ? aaData[aiDisplay[i]][8] : 0));
                    net_salary      += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[3].innerHTML = currencyFormat(parseFloat(basic_salary));
                nCells[4].innerHTML = currencyFormat(parseFloat(total_allowance));
                nCells[5].innerHTML = currencyFormat(parseFloat(total_deduction));
                nCells[6].innerHTML = currencyFormat(parseFloat(total_allowance));
                nCells[7].innerHTML = currencyFormat(parseFloat(tax));
                nCells[8].innerHTML = currencyFormat(parseFloat(commission));
                nCells[9].innerHTML = currencyFormat(parseFloat(net_salary));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('username');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('salary_month');?>]", filter_type: "text", data: []},
            {
                column_number: 11, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('status');?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: '1', label: '<?=lang('paid');?>'}, {value: '0', label: '<?=lang('unpaid');?>'}]
            },
        ], "footer");
    });
</script>
<style>
    #dtFilter-filter--UsrTable-11 {
        text-align: center !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('salary_report'); ?></h2>
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
        </div>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open('reports/salary_report'); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('user'); ?></label>
                                <?php
                                $usr[''] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $usr[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                                echo form_dropdown('user', $usr, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang('month'); ?></label>
                                <?php
                                $mth[''] = lang('select') . ' ' . lang('month');
                                foreach ($months as $key => $month) {   
                                    $mth[$month] = $month;
                                }
                                echo form_dropdown('month', $mth, (isset($_POST['month']) ? $_POST['month'] : ''), 'class="form-control" id="month" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('month') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="year"><?= lang('year'); ?></label>
                                <?php
                                    $yr[''] = lang('select') . ' ' . lang('year');
                                    foreach ($years as $key => $year) {
                                        $yr[$key] = $year;
                                    }
                                ?>
                                <?php echo form_dropdown('year', $yr, (isset($_POST['year']) ? $_POST['year'] : ''), 'class="form-control" id="year" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('year') . '"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"><?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?></div>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('reports/salary_report_actions', 'id="action-form"');
                } ?>
    
                <div class="table-responsive">
                    <table id="UsrTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?php echo lang('payroll_date'); ?></th>
                                <th><?php echo lang('username'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('basic_salary'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('total_allowance'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('total_deduction'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('leave_deduction'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('tax'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('commission'); ?></th>
                                <th style="text-align: right !important;"><?php echo lang('net_salary'); ?></th>
                                <th><?php echo lang('salary_month'); ?></th>
                                <th style="text-align: center !important;"><?php echo lang('status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th></th>
                                <th></th>
                                <th><?php echo lang('basic_salary'); ?></th>
                                <th><?php echo lang('total_allowance'); ?></th>
                                <th><?php echo lang('total_deduction'); ?></th>
                                <th><?php echo lang('leave_deduction'); ?></th>
                                <th><?php echo lang('tax'); ?></th>
                                <th><?php echo lang('commission'); ?></th>
                                <th><?php echo lang('net_salary'); ?></th>
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