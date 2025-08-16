<script>
    $(document).ready(function () {
        'use strict';
        oTable = $('#UsrTable').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('auth/getSalemans') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, null, null, null, null, null, null,{"mRender": user_status}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('first_name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('last_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('commission');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('area');?>]", filter_type: "text", data: []},
            {
                column_number: 9, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('status');?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: '1', label: '<?=lang('active');?>'}, {value: '0', label: '<?=lang('inactive');?>'}]
            }
        ], "footer");
    });
</script>

<?php 
    echo admin_form_open('auth/saleman_actions', 'id="action-form"');
 ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('salemans'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"
                                                                                  data-placement="left"
                                                                                  title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
						<li>
                            <a href="<?= admin_url('auth/add_saleman'); ?>" data-backdrop='static' data-keyboard='false' data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" id="add">
                                <i class="fa fa-plus-circle"></i> <?= lang("add_saleman"); ?>
                            </a>
                        </li>
                        <li><a href="#" id="excel" data-action="export_excel"><i
                                    class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        
						<li class="divider"></li>
						<li><a href="#" class="bpo" title="<b><?= $this->lang->line("delete_salemans") ?></b>"
							   data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
							   data-html="true" data-placement="left"><i
							   class="fa fa-trash-o"></i> <?= lang('delete_salemans') ?></a></li>
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
                    <table id="UsrTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-2"><?php echo lang('first_name'); ?></th>
                            <th class="col-xs-2"><?php echo lang('last_name'); ?></th>
                            <th class="col-xs-2"><?php echo lang('gender'); ?></th>
                            <th class="col-xs-1"><?php echo lang('phone'); ?></th>
							<th class="col-xs-1"><?php echo lang('position'); ?></th>
							<th class="col-xs-2"><?php echo lang('commission'); ?></th>
                            <th class="col-xs-2"><?php echo lang('group'); ?></th>
							<th class="col-xs-4"><?php echo lang('area'); ?></th>
                            <th style="width:100px;"><?php echo lang('status'); ?></th>
                            <th style="width:80px;"><?php echo lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
							<th></th>
                            <th></th>
							<th></th>
							<th></th>
                            <th style="width:100px;"></th>
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php if ($Owner || $this->GP['bulk_actions'] ) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>


<?php } ?>