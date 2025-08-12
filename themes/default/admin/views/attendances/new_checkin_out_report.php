<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
	<div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('new_check_in_out_report') ?></h2>
		<div class="box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
					<a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
						<i class="icon fa fa-toggle-up"></i>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
						<i class="icon fa fa-toggle-down"></i>
					</a>
				</li>
			</ul>
		</div>
		<div class="box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
					<a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
						<i class="icon fa fa-file-excel-o"></i>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="box-content">
		<div class="row">
			<div class="col-lg-12">
				<p class="introtext"><?= lang("check_in_out_report") ?></p>
				<?php echo admin_form_open("attendances/new_checkin_out_report", ' id="form-submit" '); ?>
				<div id="form">
					<div class="row">
						<div class="col-sm-3">
							<div class="form-group">
								<label class="control-label" for="biller"><?= lang("biller"); ?></label>
								<?php
								$bl[""] = lang('all') . ' ' . lang('biller');
								foreach ($billers as $biller) {
									$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
								}
								echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
								?>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label class="control-label" for="group"><?= lang("group"); ?></label>
								<?php
								$group_opt = array(lang('all') . " " . lang('group'));
								foreach ($groups as $group) {
									$group_opt[$group['id']] = $group['name'];
								}
								echo form_dropdown('group', $group_opt, (isset($_POST['group']) ? $_POST['group'] : ""), 'id="group" class="form-control"');
								?>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label class="control-label" for="group"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<?= lang("start_date", "start_date"); ?>
								<?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date("d/m/Y")), 'class="form-control date" id="start_date"'); ?>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="controls" style="float:left">
							<?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?>
						</div>
						<div style="clear:both"></div>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-striped dfTable reports-table">
						<thead>
							<tr>
								<th><?= lang("code") ?></th>
								<th><?= lang("name") ?></th>
								<th><?= lang("group") ?></th>
								<th><?= lang("date") ?></th>
								<th><?= lang("checkin_out") ?></th>
								<th><?= lang("total_working_time") ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$tbody = '';
							if (count($attendances) > 0) {
								foreach ($attendances as $key => $value) {
									$tbody .= '<tr>
											<td>' . $value['employee_code'] . '</td>
											<td>' . $value['employee_name'] . '</td>
											<td>' . $value['group_name'] . '</td>
											<td>' . $value['shift_date'] . '</td>
											<td>
												<table border="1" width="100%">
													<tr>
														<th style="padding: 5px;"><small>'.lang("check_in").'</small></th>
														<th style="padding: 5px;"><small>'.lang("check_out").'</small></th>
														<th style="padding: 5px;"><small>'.lang("working_time").'</small></th>
													</tr>';
													foreach ($value[$value['shift_date']] as $ikey => $ivalue) {
														
														$tbody .= '<tr>
														<td style="padding: 5px;"><small>' . date('h:i a', strtotime($ivalue['shift_start_time'])) . '</small>';
														if( $ivalue['shift_intype']==1 && !empty($ivalue['checkin_location'])){
														$tbody .= '<br> <a style="font-size: 13px;" target="_blank" href="https://www.google.com/maps/place/'.@$ivalue['checkin_location']->latitute.','.@$ivalue['checkin_location']->longitute.'">Location</a>';
														}
														$tbody .= ' </td>';
														$shift_end_time = ($ivalue['shift_end_time']!='')?date('h:i a', strtotime($ivalue['shift_end_time'])):"--";
														$tbody .= '<td style="padding: 5px;"><small>'.$shift_end_time.'</small>';
														if($ivalue['shift_outtype']==1 && !empty($ivalue['checkout_location'])){
														$tbody .= '<br> <a style="font-size: 13px;" target="_blank" href="https://www.google.com/maps/place/'.@$ivalue['checkout_location']->latitute.','.@$ivalue['checkout_location']->longitute.'">Location</a>';
														}
														$tbody .= '</td>
														<td style="padding: 5px;"><small>' . $ivalue['shift_total_time'] . '</small></td>
													</tr>';
													}
													$tbody .= '</table>
											</td>
											<td>' . $value['total_work_time'] . '</td>';
										$tbody .= '</tr>';
							}
							} else {
								$tbody = '<tr><td colspan="12" class="dataTables_empty">' . lang('sEmptyTable') . '</td></tr>';
							}
							echo $tbody;
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#form').hide();
		$('.toggle_down').click(function() {
			$("#form").slideDown();
			return false;
		});
		$('.toggle_up').click(function() {
			$("#form").slideUp();
			return false;
		});

		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent('<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:12px !important; border:1px solid black !important }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "check_in_out_report.xls";
			return true;
		});
	});
</script>