<?php defined('BASEPATH') OR exit('No direct script access allowed');
	$biller_id = $this->input->post("biller") ? $this->input->post("biller") : false;
	$project_id = $this->input->post("project") ? $this->input->post("project") : false;
	$warehouse_id = $this->input->post("warehouse") ? $this->input->post("warehouse") : false;
	$customer_id = $this->input->post("customer") ? $this->input->post("customer") : false;
	$year = $this->input->post("year") ? $this->input->post("year") : date("Y");
	$payments = $this->reports_model->getMonthlyInstallmentPayments($biller_id,$project_id,$warehouse_id,$customer_id,$year);
?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('monthly_installment_payment'); ?></h2>
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
                    <?php echo admin_form_open("reports/monthly_installment_payment"); ?>
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
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', $customer_id, 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("year", "year"); ?>
                                <?php echo form_input('year', $year, 'class="form-control year" id="year"'); ?>
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
						if($project_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("project").": ".$pj[$project_id]."</td>";
						}
						if($warehouse_id){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("warehouse").": ".$wh[$warehouse_id]."</td>";
						}
						if($year){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("year").": ".$year."</td>";
						}
						$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
						$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("printing_date").": ".$this->bpas->hrsd(date("Y-m-d"))."</td></tr>";
					?>
					
					<tr>
						<th colspan="2" class="text-center"><?= $this->Settings->site_name ?></th>
					</tr>
					<tr>
						<th colspan="2" class="text-center"><u><?= lang('monthly_installment_payment'); ?></u></th>
					</tr>
					<?= $print_filter ?>
				</table>
				<?php
					if($year == date('Y')){
						$last_month = date('n');
					}else{
						$last_month = 12;
					}
					$array_months = array(1 => lang('jan'), 2 => lang('feb'), 3 => lang('mar'), 4 => lang('apr'), 5 => lang('may'), 6 => lang('jun'), 7 => lang('jul'), 8 => lang('aug'), 9 => lang('sep'), 10 => lang('oct'), 11 => lang('nov'), 12 => lang('dec'));
					$months = array();
					for($i=1; $i <= $last_month; $i++){
						$months[$i] = $array_months[$i]; 
					}
					$thead_year = '';
					$thead_month = '';
					$thead_qty = '';
					$colspan_main = 1;
					foreach($months as $month){
						$colspan_main++;
						$thead_month .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang($month).'</th>';
					}
					$thead_month .= '<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">'.lang("total").'</th>';
					$thead_year .= '<th colspan="'.$colspan_main.'">'.$year.'</th>';
				?>
				
                <div class="table-responsive">
					<table id="MEData" class="table table-bordered table-hover table-striped">
                        <thead class="exportExcel">
							<tr>
								<th rowspan="2"><?= lang("biller") ?></th>
								<?= $thead_year ?>
							</tr>
							<tr>
								<?= $thead_month ?>
							</tr>
                        </thead>
                        <tbody class="exportExcel">
							<?php
								$tbody = "";
								$ttotal = false;
								if($billers){
									foreach($billers as $biller){
										if(!$biller_id || $biller_id == $biller->id){
											$total_paid = 0;
											$tbody .= "<tr><td>".$biller->company."</td>";
											foreach($months as $index => $month){
												$paid = isset($payments[$biller->id][$index]) ? $payments[$biller->id][$index]->paid : 0;
												$tbody .="<td class='text-right'>".$this->bpas->formatMoney($paid)."</td>";
												$total_paid += $paid;
												
												$ttotal[$index] = (isset($ttotal[$index]) ? $ttotal[$index] : 0) + $paid;
											}
											$tbody .="<td class='text-right'>".$this->bpas->formatMoney($total_paid)."</td>";
											$tbody .= "</tr>";
										}
									}
								}
								echo $tbody;
							?>
                        </tbody>
						<tfoot class="dtFilter">
							<?php
								$tfoot = "<tr class='active'><th class='text-right'></th>";
								$total_paid = 0;
								foreach($months as $index => $month){
									$paid = $ttotal[$index];
									$tfoot .="<th class='text-right'>".$this->bpas->formatMoney($paid)."</th>";
									$total_paid += $paid;
								}
								$tfoot .="<th class='text-right'>".$this->bpas->formatMoney($total_paid)."</th>";
								$tfoot .="</tr>";
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
			size: landscape;
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
			this.download = "monthly_installment_payment.xls";
			return true;			
		});
    });
</script>