<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 70;
	$font_size = 15;
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
	<div class="no-print" style="height:15px;"></div>
	<table>
		<tbody>
			<tr>
				<td>
					<table style="width:100%; margin-bottom:20px">
						<tr>
							<td style="width:20%" class="text_left">
								<?php
									echo '<img style="margin-bottom:20px" width="130px" src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
								?>
							</td>
							<td style="font-family:Khmer OS Muol Light !important; font-size:18px" class="text-center">
								ព្រះរាជាណាចក្រកម្ពុជា<br>ជាតិ សាសនា ព្រះមហាក្សត្រ
								<br><img width="130px" src="<?=base_url()?>assets/uploads/symbol.png">
							</td>
							<td style="width:20%">
								&nbsp;
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<?php 
						if($customer->gender=='male'){
							$customer->gender = 'ប្រុស';
						}else{
							$customer->gender = 'ស្រី';
						}
						$sale_agreement_install  = $biller->sale_agreement_install;
						$dob = $this->bpas->dateToKhmerDate($this->bpas->hrsd($customer->dob));
						$sample = ["{name}", "{gender}", "{dob}", "{nric}", "{address}", "{phone}", "{occupation}"];
						$data   = [$customer->name, $customer->gender, $dob ,$customer->nric,$customer->address,$customer->phone,$customer->occupation];
						$agreement = str_replace($sample, $data, $sale_agreement_install);
						echo $this->bpas->decode_html($agreement);
						
					?>
				</td>
			</tr>
			
		</tbody>

	</table>
	
	<div class="clearfix"></div>
	
	<div class="buttons" style="margin-top:15px; margin-bottom:15px">
		<div class="btn-group btn-group-justified">
			<div class="btn-group">
				<a class="tip btn btn-warning" href="<?= admin_url("installments/view/".$installment->id) ?>" title="<?= lang('print') ?>">
					<i class="fa fa-usd"></i>
					<span class="hidden-sm hidden-xs"><?= lang('installment_details') ?></span>
				</a>
			</div>
			<div class="btn-group">
				<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
					<i class="fa fa-close"></i>
					<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
				</a>
			</div>
			<div class="btn-group">
				<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
					<i class="fa fa-print"></i>
					<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
				</a>
			</div>
		</div>
	</div>
	
	<div class="clearfix"></div>
	
</div>
<style>
	@media print{
		.no-print{
			display:none !important;
		}
		.tr_print{
			display:table-row !important;
		}
		.modal-dialog{
			<?= $hide_print ?>
		}
		.bg-text{
			display:block !important;
		}
		#myModal .main_content {
            display: none !important;
        }
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
	}
	
	.tr_print{
		display:none;
	}
	
	h4{
		font-size:18px !important;
		font-weight:bold !important;
	}
	
	#tbody .td_print{
		border:none !important;
		border-left:1px solid black !important;
		border-right:1px solid black !important;
		border-bottom:1px solid black !important;
	}
	.modal-dialog{
		background-color:white !important;
		padding-left:12px; !important;
		padding-right:12px; !important;
	}
	.hr_title{
		border:3px double #000000 !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:26px !important;
	}
	.table_item td{
		border:1px solid black;
		line-height:<?=$td_line_height?>px !important;
	}
	.footer_des[rowspan] {
	  vertical-align: top !important;
	  text-align: left !important;
	  border:0px !important;
	}
	
	.text_center{
		text-align:center !important;
	}
	.text_left{
		text-align:left !important;
		padding-left:3px !important;
	}
	.text_right{
		text-align:right !important;
		padding-right:3px !important;
	}
	
	fieldset{
		-moz-border-radius: 9px !important;
		-webkit-border-radius: 15px !important;
		border-radius:9px !important;
		border:none !important;
		min-height:<?= $min_height ?>px !important;
		margin-bottom : <?= $margin ?>px !important;
		padding-left : <?= $margin ?>px !important;
	}

	legend{
		width: initial !important;
		margin-bottom: initial !important;
		border: initial !important;
	}
	
	.modal table{
		width:100% !important;
		font-size:<?= $font_size ?>px !important;
		border-collapse: collapse !important;
	}
	
	.bg-text{
		opacity: 0.1;
		color:lightblack;
		font-size:100px;
		position:absolute;
		transform:rotate(300deg);
		-webkit-transform:rotate(300deg);
		display:none;
	}
</style>

<script type="text/javascript">


</script>