<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#templateTable').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getTemplates') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, null,null,null,null,{"bSortable": false, "bSearchable" : false}]
			}).fnSetFilteringDelay().dtFilter([          
				{column_number: 1, filter_default_label: "[<?= lang("biller") ?>]", filter_type: "text", data: []},	
				{column_number: 2, filter_default_label: "[<?= lang("name") ?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?= lang("type") ?>]", filter_type: "text", data: []},	
				{column_number: 4, filter_default_label: "[<?= lang("employee_type") ?>]", filter_type: "text", data: []},	
			], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('templates') ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('hr/add_template'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_template') ?>
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
                    <table id="templateTable" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-bordered table-hover table-striped dataTable">
                        <thead>
							<tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
								<th><?= lang("biller"); ?></th>
								<th><?= lang("name"); ?></th>
								<th><?= lang("type"); ?></th>
								<th><?= lang("employee_type"); ?></th>
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
								<th style="width:100px; text-align: center;"></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
