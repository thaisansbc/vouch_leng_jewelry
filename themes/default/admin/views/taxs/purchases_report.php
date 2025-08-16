<?php defined('BASEPATH') OR exit('No direct script access allowed');
	$biller_id = $this->input->post("biller") ? $this->input->post("biller") : false;
	$month = $this->input->post("month") ? $this->input->post("month") : date("m/Y");
	$date = explode("/",$month);
	$taxs = $this->taxs_model->getTaxs("purchase",$biller_id,$date[0],$date[1]);
	$biller_detail = $this->site->getCompanyByID($biller_id ? $biller_id : $Settings->default_biller);
?>
<style>
	.table1{
		width: 100%;
		max-width: 100%;
   		margin-bottom: 20px;
	}
	table td{
		padding: 5px;
	}
	@media print{    
		.dtFilter{
			display: table-footer-group !important;
		}
		#form{
			display:none !important;
		}
		.print_only{
			display:table-row !important;
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
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('purchases_report') ?></h2>
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
                    <?php echo admin_form_open("taxs/purchases"); ?>
                    <div class="row">
						<div class="col-sm-3">
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
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("month", "month"); ?>
                                <?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : date("m/Y")), 'class="form-control month" '); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
				

                <div class="table-responsive">
					<table id="MEData" class="table1 table-bordered table-hover table-striped">
                        <tbody class="exportExcel">
							<tr class="print_only">
								<td colspan="15" style="text-align:center; border:none !important">
									<div style="font-family: Khmer OS Muol Light !important">សៀវភៅទិន្នានុប្បវត្តិទិញ</div>
									<div style="font-family: Khmer OS Muol Light !important">ប្រចាំខែ<?= $this->bpas->numberToKhmerMonth($date[0]) ?> ឆ្នាំ<?= $this->bpas->numberToKhmer($date[1]) ?></div>
								</td>
							</tr>
							<tr class="print_only">
								<td colspan="8" style="text-align:left; border:none !important">
									<div>នាមករណ៍ៈ <?= $biller_detail->company ?></div>
									<div>លេខ អត្តសញ្ញាណកម្ម​ អ.ត.បៈ <?= $biller_detail->cf5 ?></div>
									<div>អាស័យដ្ឋានៈ <?= $biller_detail->cf2." ".$biller_detail->cf3 ?></div>
								</td>
								<td colspan="7" style="text-align:right; border:none !important">
									<div>Company Name: <?= $biller_detail->name ?></div>
									<div>VAT TIN No: <?= $biller_detail->vat_no?></div>
									<div>ទូរស័ព្ទៈ <?= $biller_detail->cf1?></div>
								</td>
							</tr>
							<tr>
								<th colspan="7" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">វិក្កយបត្រ</th>
								<th colspan="6" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការទិញ</th>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">សរុបការទិញបូករួម<br>ទាំងអាករ<br>Total<br>(VAT included)</th>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">សរុបការទិញបូករួម<br>ទាំងអាករ<br>Total<br>(VAT included)</th>
							</tr>
							<tr>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ថ្ងៃខែ<br>Date</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">លេខវិក្កយបត្រ/<br>ប្រតិវេទន៍គយ/<br>បង្កាន់ដៃបង់ប្រាក់ពន្ធ<br>Rec/Inv/Custom No.</td>
								<th colspan='4' style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អ្នកផ្គត់ផ្គង់<br>Supplier</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">បរិយាយ<br>Description</td>
								<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការទិញមិនជាប់អាករ <br>ឬ ការទិញគ្មានឥណទាន</td>
								<th colspan="4" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការទិញជាប់អាករ</td>
							</tr>
							<tr>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">លេខ អតប<br>VAT TIN /ID/TID</td>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ឈ្មោះខ្មែរ<br>Name (Khmer)</td>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ឈ្មោះ(ឡាតាំង)<br>Name (English)</td>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">លេខទូរសព្ទ<br>Phone Number</td>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">Purchase<br>(KHR)</td>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">Purchase<br>(USD)</td>
								<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការនាំចូល</td>
								<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការទិញក្នុងស្រុក</td>
							</tr>
							<tr>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">តម្លៃជាប់អាករ <br>Import</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អាករ<br>VAT</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">តម្លៃជាប់អាករ<br>Local Purchase</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អាករ<br>VAT</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">USD</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">KHR</td>
							</tr>
							<tr>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P1</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P2</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P3</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P4</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P5</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P6</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P7</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P8</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P9</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P10</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P11</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P12</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P13</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P14</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">P15</td>
							</tr>

                        </tbody>
                        <tbody class="exportExcel">
							<?php
								$tbody = "";
								$t_purchase = 0;
								$t_purchase_vat = 0;
								$t_vat = 0;
								$t_grand_total = 0;
								$t_grand_total_kh = 0;
								if($taxs){
									foreach($taxs as $tax){
										if($tax->order_tax > 0){
											$purchase_vat = $this->bpas->formatMoney($tax->total);
											$purchase = "";
											$t_purchase_vat += $tax->total;
										}else{
											$purchase_vat = "";
											$purchase = $this->bpas->formatMoney($tax->total);
											$t_purchase += $tax->total;
										}
										$grand_total_kh = $tax->grand_total * $tax->exchange_rate;
										$t_vat += $tax->order_tax;
										$t_grand_total += $tax->grand_total;
										$t_grand_total_kh += $grand_total_kh;
										$tbody .="<tr>
													<td>".$this->bpas->hrld($tax->date)."</td>
													<td><a href='#'> ".$tax->tax_reference."</a></td>
													<td>".$tax->vat_no."</td>
													<td>".$tax->company."</td>
													<td>".$tax->name."</td>
													<td>".$tax->phone."</td>
													<td>".($tax->note ? $this->bpas->decode_html($tax->note) : '')."</td>
													<td></td>
													<td class='text-right'>".$purchase."</td>
													<td></td>
													<td></td>
													<td class='text-right'>".$purchase_vat."</td>
													<td class='text-right'>".($tax->order_tax ? $this->bpas->formatMoney($tax->order_tax) : '')."</td>
													<td class='text-right'>".$this->bpas->formatMoney($tax->grand_total)."</td>
													<td class='text-right'>".$this->bpas->formatMoneyKH($grand_total_kh)."</td>
												</tr>";
										
									}
									$tbody .= "<tr>
													<th colspan='8'></th>
													<th class='text-right'>".$this->bpas->formatMoney($t_purchase)."</th>
													<th></th>
													<th></th>
													<th class='text-right'>".$this->bpas->formatMoney($t_purchase_vat)."</th>
													<th class='text-right'>".$this->bpas->formatMoney($t_vat)."</th>
													<th class='text-right'>".$this->bpas->formatMoney($t_grand_total)."</th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_grand_total_kh)."</th>
												</tr>";
								}else{
									$tbody = "<tr><td colspan='15'>".lang("sEmptyTable")."</td></tr>";
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
        $("#xls").click(function(e) {
			var html = '<table id="MEData" border="1" class="table table-bordered table-hover table-striped">';
			$(".exportExcel").each(function(){
				html += $(this).html();
			});
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + html);
			this.href = result;
			this.download = "purchases_report.xls";
			return true;			
		});
    });
</script>