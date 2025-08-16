<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#SkillTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('customers/getMembership/') ?>',
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
                { "mRender": currencyFormat },
                null,
                {"mRender": decode_html},
                {"bSortable": false, "sClass" : "center"}
            ]
            }).fnSetFilteringDelay().dtFilter([            
                {column_number: 1, filter_default_label: "[<?= lang("title") ?>]", filter_type: "text", data: []},    
                {column_number: 2, filter_default_label: "[<?= lang("package") ?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?= lang("price") ?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?= lang("class") ?>]", filter_type: "text", data: []},    
                {column_number: 5, filter_default_label: "[<?= lang("description") ?>]", filter_type: "text", data: []},  
                {column_number: 6, filter_default_label: "[<?= lang("action") ?>]", filter_type: "text", data: []},     
            ], "footer");
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('customers/customer_package_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('membership'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('customers/add_membership'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_membership') ?>
                            </a>                           
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
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
                <div class="table-responsive">
                    <table id="SkillTable" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-bordered table-hover table-striped dataTable">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang("title");?></th>
                                <th><?= lang("package");?></th>
                                <th><?= lang("price"); ?></th>
                                <th><?= lang("class"); ?></th>
                                <th><?= lang("description");?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                                <th style="width:100px; text-align: center;"></th>
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
    <script language="javascript">
        $(document).ready(function () {
            $('#set_admin').click(function () {
                $('#usr-form-btn').trigger('click');
            });

        });
    </script>
<?php } ?>