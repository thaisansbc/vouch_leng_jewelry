<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#STData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('projects/getCounts') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
          
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                null,
                null,
                null,
                null,
                {"mRender": fld},
                {"mRender": fld},
                null,
                null,
                {"bSortable": false},
            //  {"mRender": fld}, null, null,
            ]
        });
        $('#tasks_data').dataTable();

        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<?= admin_form_open('projects/styles_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("list_results"); ?></p>
                <div id="form">

                    <?php echo admin_form_open('projects/purchases'); ?>
                    <div class="row">
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <?php
                                $pro[""] = "";
                                foreach ($getprojects as $project) {
                                    $pro[$project->project_id] = $project->project_name;
                                }
                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                <?php
                                $us[''] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('status', 'status'); ?>
                                <?php
                             //   $client1 = lang('select') . ' ' . lang('client');
                                 $client1 = array(
                                    '' => '',
                                    'new' => lang('new'), 
                                    'progress' => lang('progress'), 
                                    'completed' => lang('completed'), 
                                    );

                                echo form_dropdown('status', $client1, (isset($_POST['client']) ? $_POST['client'] : ''), 'class="form-control select" id="client" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="table-responsive">
                    <table id="tasks_data" class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= lang('no') ?></th>
                                <th><?= lang('task') ?></th>
                                <th><?= lang("status") ?></th>
                                <th><?= lang("project_name") ?></th>
                                <th>Progress</th>
                                <th><?= lang("start_date") ?></th>
                                <th><?= lang("due_date") ?></th>
                                <th><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $i = 1;
                                foreach ($tasks as $task) { 
                            ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $task->title; ?></td>
                                <td style="text-align: center;"> 
                                    <?php if ($task->status == 'new') { ?>
                                    <span class="badge badge-secondary" style="background-color: ##007bff;"><?php echo $task->status; ?></span>
                                    <?php }else if ($task->status == 'progress') { ?>
                                    <span class="badge badge-secondary" style="background-color: #17a2b8;"><?php echo $task->status; ?></span>   
                                    <?php }else{ ?>
                                    <span class="badge badge-secondary" style="background-color: #28a745;"><?php echo $task->status; ?></span>
                                    <?php } ?>
                                </td>
                                <td><?= $task->project_name; ?></td>
                                <td width="180px">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-primary progress-bar-striped bg-success" role="progressbar" aria-valuenow="<?php echo $task->progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $task->progress; ?>%">
                                          <?php echo $task->progress; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?= $task->start_date; ?></td>
                                <td><?= $task->end_date; ?></td>
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
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        //$('#example').DataTable();

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

