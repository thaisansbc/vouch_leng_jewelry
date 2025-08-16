<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        var oTable = $('#promotionTable').dataTable({
            "aaSorting": [[1, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getPromotions') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
            {"sClass" : "left"}, 
            {"sClass" : "left"},
            {"sClass" : "left"}, 
            {"sClass" : "left"},
            {"sClass" : "center", "mRender" : fsd},
            {"sClass" : "center", "mRender" : fsd},
            {"sClass" : "left"},
            {"bSortable": false, "sClass" : "center"}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('employee_level');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('promoted_date');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('official_promote');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('promoted_by');?>]", filter_type: "text", data: []},
            
        ], "footer");
    });
</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('hr/id_card_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-regular fa fa-list-ol"></i><?= lang('promotion').' ('.($biller ? $biller->name : lang('all_billers')).')' ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('hr/add_promotion') ?>" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus-circle"></i> <?= lang('add_promotion') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                    
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_id_cards") ?></b>" 
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                            data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_id_cards') ?>
                        </a>
                    </li>
                </ul>
            </li>
            
            <?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('hr/id_cards') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($billers as $biller) {
                            echo '<li><a href="' . admin_url('hr/id_cards/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </liv>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="promotionTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped dataTable">
                        <thead>
                        <tr>
                            <th style="width:100px;"><?php echo lang('code'); ?></th>
                            <th style="width:150px;"><?php echo lang('name'); ?></th>
                            <th style="width:150px;"><?php echo lang('position'); ?></th>
                            <th style="width:150px;"><?php echo lang('employee_level'); ?></th>
                            <th style="width:150px;"><?php echo lang('promoted_date'); ?></th>
                            <th style="width:250px;"><?php echo lang('official_promote'); ?></th>
                            <th style="width:150px;"><?php echo lang('promoted_by'); ?></th>
                            
                            <th style="width:60px;"><?php echo lang('action'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
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