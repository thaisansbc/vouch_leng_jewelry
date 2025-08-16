<script>
    $(document).ready(function () {
        var oTable = $('#SupData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('account/getAccountSections') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [ null, null, null, null, null],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				nRow.id = aData[0];
                nRow.className = "acc_head";
                return nRow;
            }
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('ID');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name_kh');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner) {
    echo admin_form_open('account/account_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('chart_account_section'); ?></h2>

    <div class="box-icon">
        <ul class="btn-tasks"> 
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a>
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
                    <table id="SupData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                           
                            <th style="width:20%;"><?= lang("ID"); ?></th>
                            <th style="width:20%;"><?= lang("name"); ?></th>
							<th style="width:20%;"><?= lang("name_kh"); ?></th>
                            <th style="width:20%;"><?= lang("type"); ?></th>
                            <th style="width:20%;"><?= lang("description"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                           
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>
<script type="text/javascript">
	/*$("document").ready(function(){
		$("#excel").click(function(e){
			e.preventDefault();
			window.location.href="<?= admin_url('products/getProductAll/0/xls/') ?>";
			return false;
		});
			
	});*/
</script>
	

