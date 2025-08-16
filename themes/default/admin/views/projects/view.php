<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<ul id="myTab" class="nav nav-tabs">
    <li class=""><a href="#details" class="tab-grey"><?= lang('overview') ?></a></li>
    <li class=""><a href="#budget" class="tab-grey"><?= lang('budget') ?></a></li>
    <li class="hide"><a href="#milestone" class="tab-grey"><?= lang('milestone') ?></a></li>
    <li class=""><a href="#task" class="tab-grey"><?= lang('task') ?></a></li>
    <li class=""><a href="#vendors" class="tab-grey"><?= lang('influencer') ?></a></li>
    <li class=""><a href="#expenses" class="tab-grey"><?= lang('expenses') ?></a></li>
    <li class=""><a href="#members" class="tab-grey"><?= lang('members') ?></a></li>
    <li class=""><a href="#noted" class="tab-grey"><?= lang('noted') ?></a></li>
    
</ul>


<div class="tab-content">
    <div id="details" class="tab-pane fade in">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-file-text-o nb"></i><?= lang('project')?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                <li>
                                    <a href="<?= admin_url('projects/edit/' . $project->project_id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                    </a>
                                </li>
                            
                                <li class="divider"></li>
                                <li class="hide">
                                    <a href="#" class="bpo" title="<b><?= lang('delete_product') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('projects/delete/' . $project->project_id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete') ?>
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
                        <div class="row">
                            <div class="col-sm-3">
                               <?= lang('project_name') ?>: <label><?= $project->project_name?></label>
                               <div><?= lang('start_date') ?>: <?= $this->bpas->hrld($project->start_date)?></div>
                               <div><?= lang('end_date') ?>: <?= $this->bpas->hrld($project->end_date)?></div>
                            </div>
                            <div class="col-sm-3">
                                <label><?= lang('approve_status')?></label>
                                <div>
                                    <?php echo '<label class="label '.($project->approve_status=='pending'?'label-warning':'label-success').'">'.$project->approve_status.'</label>';?>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label><?= lang('status')?></label>
                                <?php  
                                    $progress = 0;
                                    foreach ($task_progress as $pro) {
                                        $progress = $pro->result/$pro->project;
                                    }
                                ?>
                                <?php if ($progress == 0) {?>
                                <div><span class="badge badge-primary" style="background-color: ##007bff;">New</span></div>
                                <?php }else if ($progress >0 && $progress <100) {?>
                                <div><span class="badge badge-primary" style="background-color: #17a2b8;">In Progress</span></div>
                                <?php } else { ?>
                                <div><span class="badge badge-primary" style="background-color: #28a745;">Completed</span></div>
                                <?php } ?>
                                <div class="progress">
                                  <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="small-box padding1010">
                            <h4 class="bold"><?= lang('budget') ?></h4>
                            <i class="icon fa fa-line-chart"></i>
                            <h3 class="bold"><?= $this->bpas->formatMoney($budgetproject->amount); ?></h3>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box padding1010">
                            <h4 class="bold"><?= lang('expenses') ?></h4>
                            <i class="icon fa fa-line-chart"></i>
                            <h3 class="bold"><?= $this->bpas->formatMoney($expense->amount); ?></h3>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box padding1010">
                            <h4 class="bold"><?= lang('influencer') ?></h4>
                            <i class="icon fa fa-line-chart"></i>
                            <h3 class="bold"><?= $this->bpas->formatMoney($influencer->amount); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.tip').tooltip();
            });
        </script>
    </div>
    <div id="budget" class="tab-pane fade">
        <?php 
        if ($project->project_id) {
            $v .= "&project=" . $project->project_id;
        }
        $biller_id = $project->biller_id ? $project->biller_id:null;
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
                    'sAjaxSource': '<?= admin_url('projects/getBudgets'.($biller_id ?'/'.$biller_id : '') . '?v=1' . $v); ?>',
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
                        { "mRender": fld }, 
                        null, 
                        null,
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
                            total += parseFloat(aaData[aiDisplay[i]][4]);
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        nCells[4].innerHTML = currencyFormat(total);
                    }
                }).fnSetFilteringDelay().dtFilter([{
                        column_number: 1,
                        filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                        filter_type: "text",
                        data: []
                    },
                    {
                        column_number: 2,
                        filter_default_label: "[<?= lang('reference'); ?>]",
                        filter_type: "text",
                        data: []
                    },
                    {
                        column_number: 3,
                        filter_default_label: "[<?= lang('title'); ?>]",
                        filter_type: "text",
                        data: []
                    },
                ], "footer");

            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('budget') ?></h2>
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
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                         <div class="table-responsive">
                            <table id="BudEXPData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                                <thead>
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check" />
                                        </th>
                                        <th class="col-xs-2"><?= lang('date'); ?></th>
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
                                        <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
    </div>
    <div id="milestone" class="tab-pane fade">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('milestone') ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                <li>
                                    <a class="submenu" href="<?= admin_url('projects/add_milestone/'.$project->project_id.''); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-plus-circle"></i>
                                        <span class="text"> <?= lang('add_milestone'); ?></span>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= lang('delete_product') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('products/delete/' . $project->project_id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete') ?>
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
                        <p class="introtext"><?php echo lang('milestone'); ?></p>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th width="50"><?= lang('no'); ?></th>
                                    <th><?= lang('title'); ?></th>
                                    <th><?= lang('start_date'); ?></th>
                                    <th><?= lang('end_date'); ?></th>
                                    <th><?= lang('actions'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                     <?php if (!empty($milestones)) {
                                        $i=1;
                                        foreach ($milestones as $milestone) {
                                            ?>
                                                <tr class="row<?= $milestone->id ?>">
                                                    <td width="30"><?= $i; ?></td>
                                                    <td><?= $milestone->title; ?></td>
                                                    <td><?= $this->bpas->hrld($milestone->start_date); ?></td>
                                                     <td><?= $this->bpas->hrld($milestone->end_date); ?></td>
                                                    <td>
                                                        <div class="text-center">
                                                            <a href="<?= admin_url('projects/edit_milestone/' . $milestone->id .'/'. $milestone->project_id) ?>"
                                                               data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-edit"></i>
                                                            </a>
                                                          
                                                            <?php if ($Owner || $Admin) { ?>
                                                            <a href="#" class="bpo" title="Delete"
                                                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('projects/delete_milstone/' . $milestone->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                                                data-html="true" data-placement="left">
                                                                <i class="fa fa-trash-o"></i>
                                                            </a>
                                                            <?php } ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php
                                            $i++;
                                        }
                                    } ?>
                                </tbody>
                            
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="task" class="tab-pane fade">
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#QuRData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('projects/gettasks/'.$project->project_id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[6];
                  //      nRow.className = "quote_link2";
                        return nRow;
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        null,
                        null, 
                        {"mRender": fld}, 
                        {"mRender": fld},
                        {"mRender": row_status}, 
                        null,
                        {"bSortable": false}
                    ],
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('customer'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('grand_total'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('status'); ?>]", filter_type: "text", data: []},
                ], "footer");

                $('#tasks').dataTable();
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?= lang('task'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                 <li>
                                    <a class="submenu" href="<?= admin_url('projects/add_task/'.$project->project_id.''); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-plus-circle"></i>
                                        <span class="text"> <?= lang('add_task'); ?></span>
                                    </a>
                                </li>
                            
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= lang('delete_product') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('products/delete/' . $project->project_id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete') ?>
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
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="tasks" class="table table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th><?= lang('no') ?></th>
                                        <th><?= lang('milestone'); ?></th>
                                        <th><?= lang('title'); ?></th>
                                        <th><?= lang('start_date'); ?></th>
                                        <th><?= lang('end_date'); ?></th>
                                        <th><?= lang('status'); ?></th>
                                        <th><?= lang('users'); ?></th>
                                        <th width="70px"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if (!empty($tasks)) {
                                            $no = 1;
                                            foreach ($tasks as $key => $task) {
                                                $customers = explode(',', $task->user_id);
                                                $user_name = ''; 
                                                $i = 1;
                                                foreach ($customers as $key => $value) {
                                                    if (count($customers) == $i) {
                                                        foreach ($users as $key => $user) {
                                                            if ($user->id == $value) {
                                                                $user_name .= $user->last_name.' '.$user->first_name;
                                                            }
                                                        }
                                                    } else {
                                                        foreach ($users as $key => $user) {
                                                            if ($user->id == $value) {
                                                                $user_name .= $user->last_name.' '.$user->first_name.',';
                                                            }
                                                        }
                                                    } 
                                                    $i++;
                                                }
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?php echo $task->milstone; ?></td>
                                        <td><?php echo $task->title; ?></td>
                                        <td><?php echo $task->start_date; ?></td>
                                        <td><?php echo $task->end_date; ?></td>
                                        <td style="text-align: center;width: 70px"> 
                                            <?php if ($task->status == 'new') { ?>
                                            <span class="badge badge-secondary" style="background-color: ##007bff;"><?php echo $task->status; ?></span>
                                            <?php }else if ($task->status == 'progress') { ?>
                                            <span class="badge badge-secondary" style="background-color: #17a2b8;"><?php echo $task->status; ?></span>   
                                            <?php }else{ ?>
                                            <span class="badge badge-secondary" style="background-color: #28a745;"><?php echo $task->status; ?></span>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo $user_name; ?></td>
                                        <td>
                                            <div class="text-center">
                                                <a href="<?php echo admin_url('projects/detail_task/') ?><?= $task->id; ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-eye"></i></a>
                                                <a href="<?php echo admin_url('projects/edit_task/') ?><?= $task->id; ?>/<?= $task->project_id; ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                                <?php if ($Owner || $Admin) { ?>
                                                <a href="#" class="bpo" title="Delete"
                                                    data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('projects/delete_task/' . $task->id.'/'.$task->icon) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                                    data-html="true" data-placement="left">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php  
                                       } }
                                    ?>
                                </tbody>
                            </table>
                            <!-- Modal -->
                            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalCenterTitle">Modal title</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <div class="modal-body">
                                    ...
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary">Save changes</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="vendors" class="tab-pane fade">
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#VenData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('projects/getVendors/'.$project->project_id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[6];
                  //      nRow.className = "quote_link2";
                        return nRow;
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld},
                        null, 
                        null, 
                        null,
                       {"mRender": currencyFormat},
                        {"mRender": currencyFormat},
                        {"mRender": currencyFormat}, 
                        {"bSortable": false}
                    ],
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('customer'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('grand_total'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('status'); ?>]", filter_type: "text", data: []},
                ], "footer");

                $('#tasks').dataTable();
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?= lang('influencer'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                 <li>
                                    <a class="submenu" href="<?= admin_url('projects/add_vendor/'.$project->project_id.''); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-plus-circle"></i>
                                        <span class="text"> <?= lang('add_vendor'); ?></span>
                                    </a>
                                </li>
                            
                                <li class="divider"></li>
                             
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="VenData" class="table table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="max-width:30px;text-align: center;">
                                            <input class="checkbox checkth" type="checkbox" name="check" />
                                        </th>
                                        <th><?= lang('date') ?></th>
                                        <th><?= lang('name'); ?></th>
                                        <th><?= lang('phone'); ?></th>
                                        <th><?= lang('gender'); ?></th>
                                        <th><?= lang('amount'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th width="70px"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                           
                                </tbody>
                            </table>
                            <!-- Modal -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="members" class="tab-pane fade">
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#MemberData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('projects/getMembers/'.$project->project_id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[6];
                  //      nRow.className = "quote_link2";
                        return nRow;
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld},
                        null, 
                        null, 
                        null,
                        null,
                        {"bSortable": false}
                    ],
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('reference_no'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('customer'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('grand_total'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('status'); ?>]", filter_type: "text", data: []},
                ], "footer");

                $('#tasks').dataTable();
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?= lang('members'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                 <li>
                                    <a class="submenu" href="<?= admin_url('projects/add_member/'.$project->project_id.''); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-plus-circle"></i>
                                        <span class="text"> <?= lang('add_member'); ?></span>
                                    </a>
                                </li>
                            
                                <li class="divider"></li>
                             
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="MemberData" class="table table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="max-width:30px;text-align: center;">
                                            <input class="checkbox checkth" type="checkbox" name="check" />
                                        </th>
                                        <th><?= lang('date') ?></th>
                                        <th><?= lang('name'); ?></th>
                                        <th><?= lang('phone'); ?></th>
                                        <th><?= lang('gender'); ?></th>
                                        <th><?= lang('description'); ?></th>
                                        <th width="70px"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                           
                                </tbody>
                            </table>
                            <!-- Modal -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="noted" class="tab-pane fade">
  
        <script type="text/javascript">
            $(document).ready(function () {
                oTable = $('#NoteData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('projects/getNoted/'.$project->project_id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[6];
                  //      nRow.className = "quote_link2";
                        return nRow;
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld},
                        null, 
                        null, 
                        {"bSortable": false}
                    ],
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('title'); ?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('description'); ?>]", filter_type: "text", data: []},
                ], "footer");

                $('#tasks').dataTable();
            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?= lang('noted'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                 <li>
                                    <a class="submenu" href="<?= admin_url('projects/add_note/'.$project->project_id.''); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                        <i class="fa fa-plus-circle"></i>
                                        <span class="text"> <?= lang('note'); ?></span>
                                    </a>
                                </li>
                            
                                <li class="divider"></li>
                             
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="NoteData" class="table table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="max-width:30px;text-align: center;">
                                            <input class="checkbox checkth" type="checkbox" name="check" />
                                        </th>
                                        <th><?= lang('date') ?></th>
                                        <th><?= lang('title'); ?></th>
                                        <th><?= lang('description'); ?></th>
                                        <th width="70px"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                           
                                </tbody>
                            </table>
                            <!-- Modal -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="expenses" class="tab-pane fade">
        <?php 
        $project_id = $project->project_id;
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
                oTable = $('#EXPData').dataTable({
                    "aaSorting": [
                        [1, "desc"]
                    ],
                    "aLengthMenu": [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "<?= lang('all') ?>"]
                    ],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true,
                    'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('projects/getExpenses' . ($project_id ? '/' . $project_id : '')); ?>',
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
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld}, null, null, 
                        {"mRender": currencyFormat},
                        {"mRender": currencyFormat},
                        {"mRender": currencyFormat},
                        {"mRender": row_status, "bSearchable": false}, 
                        null,
                        {"bSortable": false,"mRender": attachment}, 
                        {"bSortable": false}
                    ],
                    'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[0];
                        nRow.reference = aData[2];
                        nRow.className = "expense_link";
                        return nRow;
                    },
                    "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                        var total = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            total += parseFloat(aaData[aiDisplay[i]][4]);
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        nCells[4].innerHTML = currencyFormat(total);
                    }
                }).fnSetFilteringDelay().dtFilter([{
                        column_number: 1,
                        filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                        filter_type: "text",
                        data: []
                    },
                    {
                        column_number: 2,
                        filter_default_label: "[<?= lang('reference'); ?>]",
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
                        column_number: 5,
                        filter_default_label: "[<?= lang('note'); ?>]",
                        filter_type: "text",
                        data: []
                    },
                    {
                        column_number: 6,
                        filter_default_label: "[<?= lang('created_by'); ?>]",
                        filter_type: "text",
                        data: []
                    },
                ], "footer");

            });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?= lang('expenses'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                 <li>
                                    <a class="submenu" target="_blank" href="<?= admin_url('expenses/add/0/0/'.$project->project_id.''); ?>" >
                                        <i class="fa fa-plus-circle"></i>
                                        <span class="text"> <?= lang('add_expense'); ?></span>
                                    </a>
                                </li>
                                <li class="divider"></li>
                             
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>
                        <table id="EXPData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th style="width:180px;"><?= lang('date'); ?></th>
                                <th><?= lang('reference'); ?></th>
                                <th><?= lang('biller'); ?></th>
                                <th><?= lang('amount'); ?></th>
                                <th><?= lang('paid'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th><?= lang('payment_status'); ?></th>
                                <th><?= lang('created_by'); ?></th>
                                <th style="min-width:30px; width: 30px; text-align: center !important;"><i class="fa fa-chain"></i>
                                </th>
                                <th style="width: 100px; text-align: center !important;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
</div>
