<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
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
	if ($this->input->post('project')) {
		$v .= "&project=" . $this->input->post('project');
	}
	if ($this->input->post('title')) {
		$v .= "&title=" . $this->input->post('title');
	}

	if(isset($date)){
		$v .= "&d=" . $date;
	}
	if($biller_id == null){
		$biller_id='';
	}else{
		$biller_id = $biller_id;
	}
?>
<script>
    $(document).ready(function() {
        function attachment(x) {
            if (x != null) {
                return '<a href="' + site.url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }
        function checkbox(y) {
            return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + y + '" /></div>';
        }

        oTable = $('#BudEXPData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('expenses/getBudgets' . ($biller_id ? '/' . $biller_id : '') . '?v=1' . $v); ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback
                });
            },
            "aoColumns": [
                { "bSortable": false, "mRender": checkbox }, 
                { "mRender": fld }, null, null, 
                 null, null,
                { "mRender": currencyFormat }, 
                { "bSortable": false, "mRender": attachment }, 
                { "bSortable": false }
            ],
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.reference = aData[2];
                nRow.className = "budget_link";
                return nRow;
            },
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total);
            }
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('biller'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('project'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('reference'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('title'); ?>]",
                filter_type: "text",
                data: []
            },
        ], "footer");

    });
</script>

<div class="box">
    <div class="box-header">
        <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('budgets') . ' (' . $biller_title . ')';?></h2>
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
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('expenses/add_budget') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_budget') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<?= $this->lang->line('delete_budgets') ?>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_budgets') ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('expenses/budgets') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('expenses/budgets/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)){ ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('expenses/budgets') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = $count_billers;
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id == $value) {
                                        echo '<li><a href="' . admin_url('expenses/budgets/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                    }
                                }
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("expenses/budgets"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="title"><?= lang("title"); ?></label>
                                <?php echo form_input('title', (isset($_POST['title']) ? $_POST['title'] : ""), 'class="form-control tip" id="title"'); ?>
                            </div>
                        </div>
                        <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="project"><?= lang("brand"); ?></label>
                                <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('project', $bl, (isset($_POST['project']) ? $_POST['project'] : ""), 'class="form-control" id="project" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("brand") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } elseif (!empty($billers)){ ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="project"><?= lang("brand"); ?></label>
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
                                    echo form_dropdown('project', $bl, (isset($_POST['project']) ? $_POST['project'] : ""), 'class="form-control" id="project" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("brand") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-sm-4 hide">
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
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('expenses/budget_actions', 'id="action-form"');
                } ?>
                <div class="table-responsive">
                    <table id="BudEXPData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th class="col-xs-2"><?= lang('date'); ?></th>
                                
                                <th class="col-xs-3"><?= lang('biller'); ?></th>
                                <th class="col-xs-2"><?= lang('project'); ?></th>
                                <th class="col-xs-2"><?= lang('reference'); ?></th>
                                <th class="col-xs-2"><?= lang('title'); ?></th>
                                <th class="col-xs-1"><?= lang('amount'); ?></th>
                                
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                </th>
                                <th style="width:100px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                </th>
                                <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
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
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function(){
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