<?php defined('BASEPATH') OR exit('No direct script access allowed');
	$biller_id = $this->input->post("biller") ? $this->input->post("biller") : false;
	$project_id = $this->input->post("project") ? $this->input->post("project") : false;
	$warehouse_id = $this->input->post("warehouse") ? $this->input->post("warehouse") : false;
	$customer_id = $this->input->post("customer") ? $this->input->post("customer") : false;
	$start_date = $this->input->post("start_date") ? $this->input->post("start_date") : false;
	$end_date = $this->input->post("end_date") ? $this->input->post("end_date") : false;
	$grade_id = $this->input->post("grade") ? $this->input->post("grade") : false;
	$fee_type = $this->input->post("fee_type") ? $this->input->post("fee_type") : false;
	$installment_times = $this->reports_model->installmentTimes();
	$installments = $this->reports_model->getInstallments("active",$biller_id,$project_id,$warehouse_id,$customer_id,$start_date,$end_date,$grade_id,$fee_type);
	$installment_items = $this->reports_model->getIndexInstallmentItems("active",$biller_id,$project_id,$warehouse_id,$customer_id,$start_date,$end_date,$grade_id,$fee_type);
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('schdules_report') ?></h2>
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
                    <?php echo admin_form_open("reports/schdules_report"); ?>
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
										echo form_dropdown('project', $pj, $project_id, 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } ?>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, $warehouse_id, 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						
						<?php if($this->config->item("schools")){ ?>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="grade"><?= lang("grade"); ?></label>
									<?php
									$grade_opt[""] = lang('select').' '.lang('grade');
									if($grades){
										foreach ($grades as $grade) {
											$grade_opt[$grade->id] = $grade->code.' - '.$grade->name;
										}
									}
									echo form_dropdown('grade', $grade_opt, (isset($_POST['grade']) ? $_POST['grade'] : ""), 'class="form-control" id="grade" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("grade") . '"');
									?>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<?php echo lang('fee_type', 'fee_type'); ?>
									<div class="controls">
										<?php
											$fee_opt[""] = lang("select")." ".lang("fee_type");
											$fee_opt["Other"] = lang("other");
											$fee_opt["Lunch"] = lang("lunch");
											$fee_opt["Uniform"] = lang("uniform");
											$fee_opt["Transportation"] = lang("transportation");
											$fee_opt["Enrollment"] = lang("enrollment");
											$fee_opt["Tuition"] = lang("tuition");
											echo form_dropdown('fee_type', $fee_opt, '', 'id="fee_type" class="form-control"');
										?>
									</div>
								</div>
							</div>
						<?php } ?>
						
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', $customer_id, 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> </div>
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
						if($project_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("project").": ".$pj[$project_id]."</td>";
						}
						if($warehouse_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("warehouse").": ".$wh[$warehouse_id]."</td>";
						}
						if($grade_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("grade").": ".$grade_opt[$grade_id]."</td>";
						}
						if($fee_type){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("fee_type").": ".$fee_opt[$fee_type]."</td>";
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
						<th colspan="2" class="text-center"><?= $this->Settings->site_name ?></th>
					</tr>
					<tr>
						<th colspan="2" class="text-center"><u><?= lang('schdules_report'); ?></u></th>
					</tr>
					<?= $print_filter ?>
				</table>
                <div class="table-responsive">
					<?php
						$thead_installment = '';
						$thead_sub = '';
						if($installment_times){
							for($i = 1; $i <= $installment_times->id; $i++){
								if($i==1){
									$n = $i."st";
								}else if($i==2){
									$n = $i."nd";
								}else if($i==3){
									$n = $i."rd";
								}else{
									$n = $i."th";
								}
								$thead_installment .= '<th colspan="2">'.$n.'</th>';
								$thead_sub .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang("amount").'</th>';
								$thead_sub .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang("deadline").'</th>';
							}
						}
						
					
					?>

					<table id="MEData" class="table table-bordered table-hover table-striped">
                        <thead class="exportExcel">
							<tr>
								<th rowspan="2"><?= lang("date") ?></th>
								<th rowspan="2"><?= lang("reference") ?></th>
								<th rowspan="2"><?= lang("customer_code") ?></th>
								<th rowspan="2"><?= lang("customer_name") ?></th>
								<?php if($this->config->item("schools")){ ?>
									<th rowspan="2"><?= lang("grade") ?></th>
								<?php } ?>
								<?= $thead_installment ?>
								<th rowspan="2"><?= lang("total") ?></th>
							</tr>
							<tr>
								<?= $thead_sub ?>
							</tr>
                        </thead>
                        <tbody class="exportExcel">
							<?php
								$tbody = "";
								$ttotal = false;
								if(isset($installments) && $installments){
									foreach($installments as $installment){
										$total = 0;
										$tbody .="<tr class='installment_schedule_link' id='".$installment->id."'>";
											$tbody .="<td>".$this->bpas->hrld($installment->created_date)."</td>";
											$tbody .="<td>".$installment->reference_no."</td>";
											$tbody .="<td class='text-center'>".$installment->customer_code."</td>";
											$tbody .="<td>".$installment->customer."</td>";
											if($this->config->item("schools")){
												$tbody .="<td>".$installment->grade_name."</td>";
											}
										
										if(isset($installment_items[$installment->id])){
											$i = 1;
											foreach($installment_items[$installment->id] as $installment_item){
												$total += $installment_item->payment;
												$tbody .="<td class='text-right'>".$this->bpas->formatMoney($installment_item->payment)."</td>";
												$tbody .="<td class='text-center'>".$this->bpas->hrsd($installment_item->deadline)."</td>";
												$ttotal[$i] = (isset($ttotal[$i]) ? $ttotal[$i] : 0) + $installment_item->payment;
												$i++;
											}
										}
										$tbody .="<td class='text-right'>".$this->bpas->formatMoney($total)."</td>";	
										$tbody .="</tr>";
									}
									
								}
								echo $tbody;
							?>
                        </tbody>
						<tfoot class="dtFilter">
							<?php
								$tfooter = "<tr>
											<th></th>
											<th></th>
											<th></th>
											<th></th>";
								if($this->config->item("schools")){
									$tfooter .= "<th></th>";
								}
								if($installment_times){
									$total = 0;
									for($i = 1; $i <= $installment_times->id; $i++){
										$total += $ttotal[$i];
										$tfooter .= "<th class='text-right'>".$this->bpas->formatMoney($ttotal[$i])."</th>";
										$tfooter .= "<th></th>";
									}
									$tfooter .= "<th class='text-right'>".$this->bpas->formatMoney($total)."</th></tr>";
								}
								echo $tfooter;
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
		$('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
		var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
		if (customer_id > 0) {
		  $('#customer_id').val(customer_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"customers/getCustomer/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  deietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
					return {results: data.results};
				} else {
					return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}else{
		  $('#customer_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  quietMillis: 15,
			  data: function (term, page) {
				return {
				  term: term,
				  limit: 10
				};
			  },
			  results: function (data, page) {
				if (data.results != null) {
					return {results: data.results};
				} else {
					return {results: [{id: '', text: 'No Match Found'}]};
				}
			  }
			}
		  });
		}
		
		$("#biller").change(biller);biller();
		function biller(){
			var biller = $("#biller").val();
			var project = "<?= $project_id ?>";
			$.ajax({
				url : "<?= admin_url("reports/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
		
		/*$('#MEData').dataTable({
			"aaSorting": [[0, "asc"],[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "oTableTools": {
                "sSwfPath": "assets/media/swf/copy_csv_xls_pdf.swf",
                "aButtons": ["csv", {"sExtends": "pdf", "sPdfOrientation": "landscape", "sPdfMessage": ""}, "print"]
            }
		}).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('date')?>]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('reference')?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('customer_code')?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('customer_name')?>]", filter_type: "text", data: []},
			<?php if($this->config->item("schools")){ ?>
				{column_number: 4, filter_default_label: "[<?=lang('grade')?>]", filter_type: "text", data: []},
			<?php } ?>
        ], "footer");*/
		

        $("#xls").click(function(e) {
			var html = '<table id="MEData" border="1" class="table table-bordered table-hover table-striped">';
			$(".exportExcel").each(function(){
				html += $(this).html();
			});
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + html);
			this.href = result;
			this.download = "schdules_report.xls";
			return true;			
		});
    });
</script>