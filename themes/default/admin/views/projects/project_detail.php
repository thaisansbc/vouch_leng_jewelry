<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#STData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('projects/getCountsByWarehouse') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
          
            "aoColumns": [
         //       {"bSortable": false, "mRender": checkbox}, 
				{"bSortable": false, "mRender": checkbox},
				{"bSortable": false},
				{"bSortable": false},
				{"bSortable": false},
                {"bSortable": false},
                {"bSortable": false},
				{"bSortable": false}
			//	{"mRender": fld}, null, null,
            ]
        });
    });
</script>
<?= admin_form_open('projects/styles_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('projects/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-plus"></i> <?= lang('add_project') ?></a></li>
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#" id="delete" data-action="delete"><i class="fa fa-trash-o"></i> <?= lang('delete_currencies') ?></a></li>
                    </ul>
                </li>
				<?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('projects') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . admin_url('projects/project_detail/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php if (empty($warehouses)) { ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("list_results"); ?></p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= lang("project_code") ?></th>
                                <th><?= lang("project_name") ?></th>
                                <th><?= lang("warehouse") ?></th>
                                <th><?= lang("description") ?></th>
                                <th style="width:150px;"><?= lang("user_access") ?></th>
                                
                            
                                <!-- <th style="width:65px;"><?= lang("actions"); ?></th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach ($projects as $project) { 
                                $customers = explode(',', $project->customer_id);
                            ?>
                            <tr>
                                <?php  
                                    foreach ($customers as $key => $value) {
                                        if ($value==$user->id) {
                                ?>
                                <td><?= $project->project_code; ?></td>
                                <td><?= $project->project_name; ?></td>
                                <td><?= $project->name; ?></td>
                                <td><?= $project->description; ?></td>
                                <td style="width:150px;"><?= $user->first_name; ?> <?= $user->last_name; ?></td>
                                
                                <?php }} ?>
                                <!-- <td style="width:65px;"><?= lang("actions"); ?></td> -->
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <p><?php echo $links; ?></p>
                </div>

            </div>

        </div>
    </div>
    <?php }else{ ?>
        <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("list_results"); ?></p>

                <div class="table-responsive">
                    <table id="STData" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang("project_code") ?></th>
                                <th><?= lang("project_name") ?></th>
                                <th><?= lang("warehouse") ?></th>
                                <th><?= lang("description") ?></th>
                                <th style="width:150px;"><?= lang("user_access") ?></th>
                                
                            
                                <th style="width:65px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>

            </div>

        </div>
    </div>
    <?php } ?>
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

