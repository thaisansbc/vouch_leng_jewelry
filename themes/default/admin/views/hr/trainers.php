<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#OfficerData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getTrainers') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                null, null, null,null, 
                {"mRender": decode_html},
                {"mRender": decode_html}, 
                {"bSortable": false,"mRender": attachment}, 
                {"bSortable": false,"bSearchable": false}]
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('full_name_kh');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('full_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []}
			
        ], "footer");
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('hr/trainer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('trainers'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"
                                                                                  data-placement="left"
                                                                                  title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('hr/add_trainer'); ?>" data-backdrop='static' data-keyboard='false' data-toggle="modal" data-target="#myModal"
                               id="add"><i class="fa fa-plus-circle"></i> <?= lang("add_trainer"); ?></a></li>
                        <li><a href="#" id="excel" data-action="export_excel"><i
                                    class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        
                        <li class="divider"></li>
                        <li><a href="#" class="bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                               data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                               data-html="true" data-placement="left"><i
                                    class="fa fa-trash-o"></i> <?= lang('delete_trainers') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="OfficerData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("full_name_kh"); ?></th>
                            <th><?= lang("full_name"); ?></th>
                            <th><?= lang("phone"); ?></th>
							<th><?= lang("gender"); ?></th>
                            <th><?= lang("address"); ?></th>
							<th><?= lang("note"); ?></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							<th><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th class="text-center"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>


