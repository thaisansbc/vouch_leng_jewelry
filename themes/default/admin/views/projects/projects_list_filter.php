<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// var_dump($clients);
// exit();
$v = '';
if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}
if ($this->input->post('reference_no')) {
    $v .= '&reference_no=' . $this->input->post('reference_no');
}
if ($this->input->post('project')) {
    $v .= '&project=' . $this->input->post('project');
}
if ($this->input->post('supplier')) {
    $v .= '&supplier=' . $this->input->post('supplier');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}

?>
<script>
    $(document).ready(function() {
        oTable = $('#STData').dataTable({
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
            'sAjaxSource': '<?= admin_url('projects/getCounts') ?>',
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

            "aoColumns": [{
                    "bSortable": false,
                    "mRender": checkbox
                },
                null,
                null,
                null,
                null,
                {
                    "mRender": fld
                },
                {
                    "mRender": fld
                },
                null,
                null,
                {
                    "bSortable": false
                },
                //	{"mRender": fld}, null, null,
            ]
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



<?= admin_form_open('projects/styles_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i class="icon fa fa-toggle-up"></i></a></li>
                        <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i class="icon fa fa-toggle-down"></i></a></li>
                    </ul>
                </div>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('projects/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_project') ?>
                            </a>
                        </li>
                        <!-- <li>
                            <a href="<php echo admin_url('system_settings/import_categories'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('import_categories') ?>
                            </a>
                        </li> -->
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_projects') ?>
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

                <p class="introtext"><?= lang("list_results"); ?></p>


                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;"><?= lang("no") ?></th>
                                <th><?= lang("client") ?></th>
                                <th><?= lang("project_name") ?></th>
                                <th><?= lang("warehouse") ?></th>
                                <th><?= lang("user_access") ?></th>
                                <th><?= lang("description") ?></th>
                                <th width="220px">Progress</th>
                                <th><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                           
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox" type="checkbox" name="val[]" value="<?= $projects->project_id ?>" id="<?= $projects->project_id ?>" />
                                </th>
                                <td><?= $clients->company; ?></td>
                                <td><?= $projects->project_name; ?></td>
                                <td><?= $projects->warehouse_id; ?></td>
                                <td><?= $projects->customer_id; ?></td>
                                <td><?= $projects->description; ?></td>
                                <td><?= $projects->description; ?></td>
                                <td>
                                    <a href='<?php echo admin_url('projects/view/') ?><?= $projects->project_id; ?>' class='tip btn btn-info btn-xs' title='Detail'><i class="fa fa-eye"></i> Detail</a>
                                    <a href='<?php echo admin_url('projects/edit/') ?><?= $projects->project_id; ?>' class='tip btn btn-success btn-xs' title='Edit' data-toggle='modal' data-target='#myModal'><i class="fa fa-pencil"></i></a>
                                    <?php if ($Owner || $Admin) { ?>
                                        <a href="#" class="tip po" title="" data-content="<p>Are you sure?</p><a class='btn btn-danger' href='<?= admin_url('projects/delete2/' . $projects->project_id) ?>'>Yes I'm sure</a> <button class='btn po-close'>No</button>" data-original-title="Delete Project"><i class="fa fa-trash-o"></i></a>
                                    <?php } ?>

                                </td>

                            </tr>
                                   
                        </tbody>
                    </table>
                    <p><?php echo $links; ?></p>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?php form_close() ?>

</head>

<script language="javascript">
    $(document).ready(function() {

        $('#delete').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>