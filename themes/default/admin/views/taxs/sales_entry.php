<?php defined('BASEPATH') OR exit('No direct script access allowed');
	$biller_id = $this->input->post("biller") ? $this->input->post("biller") : false;
	$month = $this->input->post("month") ? $this->input->post("month") : date("m/Y");
	$date = explode("/",$month);
	$taxs = $this->taxs_model->getTaxs("sale",$biller_id,$date[0],$date[1]);
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
		body{
			font-size: 11px;
		}
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
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('sales_report') ?></h2>
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
                    <?php echo admin_form_open("taxs/sales"); ?>
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
                        <tbody class="exportExcel" class="header_1">
							<tr class="print_only">
								<td colspan="14" style="text-align:center; border:none !important">
									<div style="font-family: Khmer OS Muol Light !important">សៀវភៅ ទិន្នានុប្បវត្តលក់</div>
									<div style="font-family: Khmer OS Muol Light !important">ប្រចាំខែ<?= $this->bpas->numberToKhmerMonth($date[0]) ?> ឆ្នាំ<?= $this->bpas->numberToKhmer($date[1]) ?></div>
								</td>
							</tr>
							<tr class="print_only">
								<td colspan="7" style="text-align:left; border:none !important">
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
								<th colspan="6" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការលក់</th>
								<th rowspan="4" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">សរុបការទិញបូករួម<br>ទាំងអាករ<br>Total<br>(VAT Inclusive)</th>
							</tr>
							<tr>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ថ្ងៃខែ<br>Date</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">លេខវិក្កយបត្រ<br>Invoice No.</td>
								<th colspan='2' style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អតិថជន<br>Customer</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">បរិយាយ<br>Description</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">បរិមាណ<br>Qty</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អត្រាប្ដូរប្រាក់<br>Exchange Rate</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការផ្គត់ផ្គង់មិនជាប់អាករ<br>Sale (VAT Exemption)</td>
								<th rowspan="3" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការនាំចេញ<br>Export</td>
								<th colspan="4" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការលក់ជាប់អាករ<br>Sale (VAT Included)</td>
							</tr>
							<tr>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ឈ្មោះ<br>Name</td>
								<th rowspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">លេខ អតប<br>VAT TIN</td>
								<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការលក់អោយបុគ្គលជាប់អាករ</td>
								<th colspan="2" style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">ការលក់អោយអ្នកប្រើប្រាស់</td>
							</tr>
							<tr>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">តម្លៃជាប់អាករ <br>Sale (VAT Registered)</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អាករ<br>VAT</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">តម្លៃជាប់អាករ<br>Sale (Consumer)</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">អាករ<br>VAT</td>
							</tr>
							<tr>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S1</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S2</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S3</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S4</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S5</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S6</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S7</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S8</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S9</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S10</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S11</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S12</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S13</td>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center">S14</td>
							</tr>

                        </tbody>
                        <tbody class="exportExcel bodyshow">
							<?php
								$tbody = "";
								$t_sale = 0;
								$t_sale_vat = 0;
								$t_sale_vat_r = 0;
								$t_vat = 0;
								$t_vat_r = 0;
								$t_grand_total = 0;
								if($taxs){
									foreach($taxs as $tax){
										$sale = 0;
										$sale_vat = 0;
										$sale_vat_r = 0;
										$vat = 0;
										$vat_r = 0;
										if($tax->order_tax > 0){
											if($tax->vat_no){
												$sale_vat_r = $tax->total;
												$vat_r = $tax->order_tax; 
											}else{
												$sale_vat = $tax->total;
												$vat = $tax->order_tax; 
											}
										}else{
											$sale= $tax->total;
										}
										$sale = $sale * $tax->exchange_rate;
										$sale_vat = $sale_vat * $tax->exchange_rate;
										$sale_vat_r = $sale_vat_r * $tax->exchange_rate;
										$vat = $vat * $tax->exchange_rate;
										$vat_r = $vat_r * $tax->exchange_rate;
										$grand_total = $tax->grand_total * $tax->exchange_rate;
										
										$t_sale += $sale;
										$t_sale_vat += $sale_vat;
										$t_sale_vat_r += $sale_vat_r;
										$t_vat += $vat;
										$t_vat_r += $vat_r;
										$t_grand_total += $grand_total;
										
										$tbody .="<tr>
													<td>".$this->bpas->hrld($tax->date)."</td>
													<td><a href='".admin_url('pos/view/'.$tax->transaction_id.'/1/').$tax->transaction_id."' data-toggle='modal' data-backdrop='static' data-target='#myModal'>".$tax->tax_reference."</a></td>
													<td>".(($tax->company !='-')? $tax->company:$tax->name)."</td>
													<td>".$tax->vat_no."</td>
													<td>".($tax->note ? $this->bpas->decode_html($tax->note) : '')."</td>
													<td class='text-right'>".$this->bpas->formatQuantity($tax->quantity)."</td>
													<td class='text-right'>".$tax->exchange_rate."</td>
													<td class='text-right'>".($sale ? $this->bpas->formatMoneyKH($sale) : '')."</td>
													<td></td>
													<td class='text-right'>".($sale_vat_r ? $this->bpas->formatMoneyKH($sale_vat_r) : '')."</td>
													<td class='text-right'>".($vat_r ? $this->bpas->formatMoneyKH($vat_r) : '')."</td>
													<td class='text-right'>".($sale_vat ? $this->bpas->formatMoneyKH($sale_vat) : '')."</td>
													<td class='text-right'>".($vat ? $this->bpas->formatMoneyKH($vat) : '')."</td>
													<td class='text-right'>".$this->bpas->formatMoneyKH($grand_total)."</td>
												</tr>";
										
									}
									$tbody .= "<tr>
													<th colspan='7'></th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_sale)."</th>
													<th></th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_sale_vat_r)."</th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_vat_r)."</th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_sale_vat)."</th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_vat)."</th>
													<th class='text-right'>".$this->bpas->formatMoneyKH($t_grand_total)."</th>
												</tr>";
								}else{
									$tbody = "<tr><td colspan='13'>".lang("sEmptyTable")."</td></tr>";
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
			this.download = "sales_report.xls";
			return true;			
		});
    });
</script>