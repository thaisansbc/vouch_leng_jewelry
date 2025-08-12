<div class="breadcrumb-header">
	<h2 class="blue">
		<i class="fa-fw fa fa-newspaper-o"></i>
		<?= lang('upcoming_appointments'); ?>
	</h2>
</div>
<div class="box">
	
	<div class="box-content">
		<div class="row">
			<div class="col-lg-12">
				<p class="introtext"><?= lang('enter_info'); ?></p>
			</div>
			<script type="text/javascript">
				$(document).ready(function () {
					'use strict';
					var oTable = $('#documentTable').dataTable({
						"aaSorting": [[1, "asc"], [3, "asc"]],
						"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
						"iDisplayLength": <?= $Settings->rows_per_page ?>,
						'bProcessing': true, 
						'bServerSide': true,
						'sAjaxSource': '<?= admin_url('calendar/getUpcommingAppointments') ?>',
						'fnServerData': function (sSource, aoData, fnCallback) {
							aoData.push({
								"name": "<?= $this->security->get_csrf_token_name() ?>",
								"value": "<?= $this->security->get_csrf_hash() ?>"
							});
							$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
						},
						"aoColumns": [
							{"sClass" : "center"}, 
							{"sClass" : "center"},
							{"sClass" : "center"},
							{"sClass" : "center", "mRender" : fld},
							{"sClass" : "center", "mRender" : fld},
						]
					}).fnSetFilteringDelay().dtFilter([
						{column_number: 0, filter_default_label: "[<?=lang('title');?>]", filter_type: "text", data: []},
						{column_number: 1, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
						{column_number: 2, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
						{column_number: 3, filter_default_label: "[<?=lang('start');?>]", filter_type: "text", data: []},
					], "footer");
				});
			</script>
			<div class="col-lg-12">
				<div class="table-responsive">
					<table id="documentTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped dataTable">
						<thead>
						<tr>
							<th style="width:200px;"><?php echo lang('title'); ?></th>
							<th style="width:250px;"><?php echo lang('customer'); ?></th>
							<th style="width:250px;"><?php echo lang('phone'); ?></th>
							<th style="width:250px;"><?php echo lang('start'); ?></th>
							<th style="width:250px;"><?php echo lang('end'); ?></th>
							
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



