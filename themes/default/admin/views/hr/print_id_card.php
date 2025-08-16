<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php 

	$front_card = "";
	$back_card = "";
	if($id_card_items){
		foreach($id_card_items as $id_card_item){
			$photo = "no_image.png";
			if($id_card_item->photo!=NULL || $id_card_item->photo!=''){
				$photo = $id_card_item->photo;
			}

			$front_card .= '<table class="main_table">
								<tr>
									<td class="front_card">
										<div class="employee_photo">
											<img src="'.base_url('assets/uploads/'.$photo).'"  width="'.$sample_id_card->photo_width.'" height="'.$sample_id_card->photo_height.'"/>
										</div>
										<div class="employee_name">
											'.($id_card_item->gender== "male" ? "Mr. " : "Ms. ").$id_card_item->lastname." ".$id_card_item->firstname.'
										</div>
										<div class="employee_position">
											'.$id_card_item->position.'
										</div>
										<div class="employee_working">
											<table>
												<tr>
													<td class="working">ID Nº</td>
													<td class="working"> : </td>
													<td class="working">'.$id_card_item->empcode.'</td>
												</tr>
												<tr>
													<td class="working">Department</td>
													<td class="working"> : </td>
													<td class="working">'.$id_card_item->department.'</td>
												</tr>
												<tr>
													<td class="working">Issued Date</td>
													<td class="working"> : </td>
													<td class="working">'.$this->bpas->hrsd($id_card->date).'</td>
												</tr>
												<tr>
													<td class="working">Invalid Date</td>
													<td class="working"> : </td>
													<td class="working">'.$this->bpas->hrsd($id_card->invalid_date).'</td>
												</tr>
											</table>
										</div>
									</td>
								</tr>
							</table>';

			$back_card .='<table class="main_table">
							<tr>
								<td class="back_card">
									<div class="qrcode">
										'.$this->bpas->qrcode($id_card_item->finger_id, 2).'
									</div>
								</td>
							</tr>
						</table>';

		}

		
	}
?>

<center>
	<button type="button" onclick="window.print()" class="btn btn-xs btn-default no-print" style="margin-top:10px;  margin-bottom:10px">
		<i class="fa fa-print"></i> <?= lang('print'); ?>
	</button>
	<table>
		<tr>
			<td>
				<?= $front_card ?>
			</td>
			<td style="width:5px"></td>
			<td>
				<?= $back_card ?>
			</td>
		</tr>
	</table>
</center>



<style>
	.main_table { 
		height: auto;
		width:100%;
		border: 1px solid #dcdcdc;
	}
	.front_card {
		background:url(<?= base_url('assets/uploads/'.$sample_id_card->front_card) ?>) !important;
		width:<?= $sample_id_card->width ?>px;
		height:<?= $sample_id_card->height ?>px;
		margin:0 auto;
		padding : 1%;
		vertical-align: text-top;
	}
	.back_card {
		background:url(<?= base_url('assets/uploads/'.$sample_id_card->back_card) ?>) !important;
		width:<?= $sample_id_card->width ?>px;
		height:<?= $sample_id_card->height ?>px;
		margin:0 auto;
		padding : 1%;
		vertical-align: text-top;
	}	
	.employee_photo{
		padding-top:<?= $sample_id_card->profile_padding_top ?>%;
		padding-left:<?= $sample_id_card->profile_padding_left ?>%;
		text-align:center;
	}
	.employee_name{
		padding-top:3%;
		padding-left:<?= $sample_id_card->profile_padding_left ?>%;
		font-size:<?= $sample_id_card->font_size ?>px;
		font-weight:bold;
		text-align:center;
		color : #d1cfcf !important;
	}
	.employee_position{
		padding-top:2%;
		padding-left:<?= $sample_id_card->profile_padding_left ?>%;
		font-size:<?= ($sample_id_card->font_size - 3) ?>px;
		text-align:center;
		color : #d1cfcf !important;
	}
	.employee_working{
		padding-left:<?= $sample_id_card->working_padding_left ?>%;
		font-size: <?= ($sample_id_card->font_size - 7) ?>px;
		padding-top:2%;
		color : #d1cfcf !important;
	}
	.working{
		line-height : <?= ($sample_id_card->font_size + 7) ?>px;
		color : #d1cfcf !important;
	}
	
	.back_card {
		background:url(<?= base_url('assets/uploads/'.$sample_id_card->back_card) ?>) !important;
		width:<?= $sample_id_card->width ?>px;
		height:<?= $sample_id_card->height ?>px;
		margin:0 auto;
		padding : 1%;
		vertical-align: text-top;
	}
	.qrimg{
		float : none !important;
		width : <?= $sample_id_card->qrcode_size ?>px;
	}
	.qrcode{
		padding-top : <?= $sample_id_card->qrcode_padding_top ?>%;
		padding-left : <?= $sample_id_card->qrcode_padding_left ?>%;
	}
</style>
