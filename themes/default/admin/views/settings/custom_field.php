<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->get('biller')) {
    $v .= "&biller=" . $this->input->get('biller');
}

if ($this->input->get('parent_id')) {
    $v .= "&parent_id=" . $this->input->get('parent_id');
}

?>
<script>
    $(document).ready(function () {
        $('#CategoryTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getcustom_field?v=1'.$v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, null, null, {"bSortable": false}]
        });
    });
</script>
<style type="text/css">
    .link_parent{
        width: 100%;
        background: #eeeeee;
        border-bottom: 1px solid #dbdee0;
        padding: 5px 0;
    }
    .link_parent a{
        color: #6f6b7d !important;
    }
</style>
<?= admin_form_open('system_settings/custom_field_actions', 'id="action-form"') ?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('custom_field'); ?></h2>

    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?php echo admin_url('system_settings/add_custom_field'); ?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-plus"></i> <?= lang('add_custom_field') ?>
                        </a>
                    </li>
                    
                </ul>
            </li>
        </ul>
    </div>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-3">
                <?php 
                foreach($constants as $row){
                ?>
                <div class="link_parent">
                    <a href="<?= admin_url('system_settings/custom_field?parent_id=').$row->id;?>" class="btn-lg" role="button" aria-pressed="true"><?= lang($row->name);?></a>
                </div>
                <?php
                }
                ?>
            </div>
            <div class="col-lg-9">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="CategoryTable" class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= $this->lang->line('name'); ?></th>
                                <th><?= $this->lang->line('description'); ?></th>
                                <th><?= lang('parent_category'); ?></th>
                                <th style="width:100px;"><?= $this->lang->line('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty">
                                    <?= lang('loading_data_from_server') ?>
                                </td>
                            </tr>
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

