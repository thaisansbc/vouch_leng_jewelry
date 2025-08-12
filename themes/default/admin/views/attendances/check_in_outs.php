<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#dmpData').dataTable({
            "aaSorting": [[2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('attendances/get_check_in_outs/'); ?>',
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
                {"mRender": fldt},
              
                {"bSortable": false}],

        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('employee_code');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('employee');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
        ], "footer");

        if (localStorage.getItem('remove_iols')) {
            if (localStorage.getItem('ioitems')) {
                localStorage.removeItem('ioitems');
            }
			if (localStorage.getItem('ionote')) {
				localStorage.removeItem('ionote');
			}
            localStorage.removeItem('remove_iols');
        }

        <?php if ($this->session->userdata('remove_iols')) { ?>
            if (localStorage.getItem('ioitems')) {
                localStorage.removeItem('ioitems');
            }
			if (localStorage.getItem('ionote')) {
				localStorage.removeItem('ionote');
			}
        <?php $this->bpas->unset_data('remove_iols');}
        ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo admin_form_open('attendances/check_in_out_actions', 'id="action-form"');
    }
?>
<div class="breadcrumb-header">
    <h2 class="#428BCA"><i class="fa-fw fa fa-calendar"></i><?= lang('check_in_outs'); ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i> <?= lang("actions") ?> <span class="fa fa-angle-down"></span>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('attendances/add_check_in_out') ?>">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_check_in_out') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= admin_url('attendances/import_check_in_out'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                            <i class="fa fa-plus-circle"></i> <?= lang("import_check_in_out"); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel">
                            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                    
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" 
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left">
                        <i class="fa fa-trash-o"></i> <?= lang('delete_check_in_outs') ?>
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
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:200px; width: 200px; text-align: center;"><?= lang("employee_code"); ?></th>
                            <th><?= lang("employee"); ?></th>
                            <th><?= lang("check_time"); ?></th>
                            <?php if($this->Settings->project){?>
                            <th><?= lang("project"); ?></th>
                            <?php }?>
                            <th style="min-width:75px;width: 100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th>
                            <?php if($this->Settings->project){?><th></th><?php }?>
                            <th style="width:75px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>
