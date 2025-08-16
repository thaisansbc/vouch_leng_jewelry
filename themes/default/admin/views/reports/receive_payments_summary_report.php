<?php defined('BASEPATH') OR exit('No direct script access allowed');
	$biller_id = $this->input->post("biller") ? $this->input->post("biller") : false;
	$project_id = $this->input->post("project") ? $this->input->post("project") : false;
	$user_id = $this->input->post("user") ? $this->input->post("user") : false;
	$start_date =  $this->input->post("start_date") ? $this->input->post("start_date") : false;
	$end_date =  $this->input->post("end_date") ? $this->input->post("end_date") : false;
	$status = $this->input->post("status") ? $this->input->post("status") : false;
	$type = $this->input->post("type") ? $this->input->post("type") : false;
	$payments = $this->reports_model->getIndexReceivePayment($biller_id,$project_id,$user_id,$start_date,$end_date,$status,$type);
?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('receive_payments_summary_report'); ?></h2>
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
				<li class="dropdown">
					<a href="#" onclick="window.print(); return false;" id="print" class="tip" title="<?= lang('print') ?>">
						<i class="icon fa fa-print"></i>
					</a>
				</li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("reports/receive_payments_summary_report"); ?>
                    <div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label" for="biller"><?= lang("biller"); ?></label>
								<?php
								$bl[""] = lang('select').' '.lang('biller');
								foreach ($billers as $biller) {
									$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
								}
								echo form_dropdown('biller', $bl, $biller_id, 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
								?>
							</div>
						</div>
						<?php if($Settings->project == 1){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = '';
										if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("received_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('received_by');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="status"><?= lang("status"); ?></label>
                                <?php
									$status_opt[""] = lang('select').' '.lang('status');
									$status_opt["pending"] = lang('pending');
									$status_opt["checked"] = lang('checked');
									$status_opt["approved"] = lang('approved');
									$status_opt["verified"] = lang('verified');
									echo form_dropdown('status', $status_opt, (isset($_POST['status']) ? $_POST['status'] : ""), 'class="form-control" id="status" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("status") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("type", "type"); ?>
								<?php
									$opt_type[""] = lang("select")." ".lang("type");
									$opt_type["sale"] = lang("sale");
									$opt_type["pos"] = lang("pos");
									echo form_dropdown('type', $opt_type, (isset($_POST['type']) ? $_POST['type'] : ''), 'id="type" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("type") . '"  class="form-control input-tip select"');
								?>
							</div>
						</div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', $start_date, 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', $end_date, 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
				<table class="print_only" style="width:100%; margin-bottom: 10px">
					<?php
						$print_filter = "";
						$p = 1;

						if($biller_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("biller").": ".$bl[$biller_id]."</td>";
						}
						if($user_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("received_by").": ".$us[$user_id]."</td>";
						}
						if($status){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("status").": ".$status_opt[$status]."</td>";
						}
						if($start_date){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("start_date").": ".$this->bpas->hrsd($start_date)."</td>";
						}
						if($end_date){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("end_date").": ".$this->bpas->hrsd($end_date)."</td>";
						}
						
						$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
						$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("printing_date").": ".$this->bpas->hrsd(date("Y-m-d"))."</td></tr>";
					?>
					<tr>
						<td colspan="2" class="text-center" style="font-size:18px; font-family:Khmer OS Muol Light !important;"><?= $this->Settings->other_site_name ?></td>
					</tr>
					<tr>
						<th colspan="2" class="text-center" style="font-size:16px"><?= $this->Settings->site_name ?></th>
					</tr>
					<tr>
						<th colspan="2" class="text-center"><u><?= lang('receive_payments_summary_report'); ?></u></th>
					</tr>
					<?= $print_filter ?>
				</table>	
				<?php
					$colspan = 2;
					$th_cash_account = '';
					if($cash_accounts){
						foreach($cash_accounts as $cash_account){
							$colspan ++;
							$th_cash_account .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.$cash_account->name.'</th>';
						}
					}
					$th_cash_account .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang("other").'</th>';
					$th_cash_account .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang("total").'</th>';
				?>

                <div class="table-responsive">
					<table id="MEData" class="table table-bordered table-hover table-striped"> 
                        <thead class="exportExcel">
							<tr>
								<th rowspan="2"><?= lang("biller") ?></th>
								<th colspan="<?= $colspan ?>"><?= lang("amount") ?></th>
							</tr>
							<tr>
								<?= $th_cash_account ?>
							</tr>		
                        </thead>
                        <tbody class="exportExcel">
							<?php
								$tbody = "";
								$ttotal = false;
								if($billers){
									foreach($billers as $biller){
										$total = 0;
										if(!$biller_id || $biller_id == $biller->id){
											$tbody .= "</tr><td>".$biller->company."</td>";
											if($cash_accounts){
												foreach($cash_accounts as $cash_account){
													$amount = isset($payments[$biller->id][$cash_account->id]) ? $payments[$biller->id][$cash_account->id]->amount : 0;
													$tbody .= "<td class='text-right'>".$this->bpas->formatMoney($amount)."</td>";
													$total += $amount;
													$ttotal[$cash_account->id] = (isset($ttotal[$cash_account->id]) ? $ttotal[$cash_account->id] : 0) + $amount;
												}	
											}
											$amount = isset($payments[$biller->id]["other"]) ? $payments[$biller->id]["other"]->amount : 0;
											$tbody .= "<td class='text-right'>".$this->bpas->formatMoney($amount)."</td>";
											$total += $amount;
											$tbody .= "<td class='text-right'>".$this->bpas->formatMoney($total)."</td>";
											$ttotal["other"] = (isset($ttotal["other"]) ? $ttotal["other"] : 0) + $amount;
										}
										$tbody .= "</tr>";
									}
								}
								echo $tbody;
							?>
                        </tbody>
						<tfoot class="dtFilter">
							<?php
								$tfoot = "<tr class='active'><th></th>";
								$total = 0;
								if($cash_accounts){
									foreach($cash_accounts as $cash_account){
										$amount = isset($ttotal[$cash_account->id]) ? $ttotal[$cash_account->id] : 0;
										$tfoot .= "<th class='text-right'>".$this->bpas->formatMoney($amount)."</th>";
										$total += $amount;
									}	
								}
								$amount = isset($ttotal["other"]) ? $ttotal["other"] : 0;
								$tfoot .= "<th class='text-right'>".$this->bpas->formatMoney($amount)."</th>";
								$total += $amount;
								$tfoot .= "<th class='text-right'>".$this->bpas->formatMoney($total)."</th></tr>";
								echo $tfoot;
							?>
                        </tfoot>
                    </table>
                </div>
				<table class="print_only" id="table_sinature">
					<tr>
						<td class="text-center" style="width:25%"><?= lang("prepared_by") ?></td>
						<td class="text-center" style="width:25%"><?= lang("checked_by") ?></td>
						<td class="text-center" style="width:25%"><?= lang("verified_by") ?></td>
						<td class="text-center" style="width:25%"><?= lang("approved_by") ?></td>
					</tr>
					<tr>
						<?php
							$user = $this->site->getUserByID($this->session->userdata("user_id"));
						?>
						<td style="height:110px; padding-left:5px; vertical-align: bottom !important">
							<?= lang("date") ?>: <?= $this->bpas->hrsd(date("Y-m-d")) ?><br>
							<?= lang("name") ?>: <?= $user->last_name." ".$user->first_name ?>
						</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</table>
            </div>
        </div>
    </div>
</div>
<style>
	@media print{    
		.dtFilter{
			display: table-footer-group !important;
		}
		#form{
			display:none !important;
		}
		.print_only{
			display:table !important;
		}
		table .td_biller{ 
			display:none; !important
		} 
		.exportExcel tr th{
			background-color : #428BCA !important;
			color : white !important;
		}
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
		
	}
	.print_only{
		display:none;
	}
	#table_sinature{
		width:100%;
		margin-top:15px
	}
	#table_sinature td{
		border:1px solid black;
	}
</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {		
		$('#MEData').dataTable({
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "oTableTools": {
                "sSwfPath": "assets/media/swf/copy_csv_xls_pdf.swf",
                "aButtons": ["csv", {"sExtends": "pdf", "sPdfOrientation": "landscape", "sPdfMessage": ""}, "print"]
            }
		}).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('biller')?>]", filter_type: "text", data: []},

        ], "footer");
	
		$('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
        $("#xls").click(function(e) {
			var html = '<table id="MEData" border="1" class="table table-bordered table-hover table-striped">';
			$(".exportExcel").each(function(){
				html += $(this).html();
			});
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + html);
			this.href = result;
			this.download = "receive_payments_summary_report.xls";
			return true;			
		});
    });
</script>