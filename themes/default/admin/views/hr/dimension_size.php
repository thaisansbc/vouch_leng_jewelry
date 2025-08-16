<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('dimension_size'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("hr/dimension_size/".$sample_id_card->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-body" style="padding: 5px;">
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("photo_width", "photo_width"); ?>
										<?php echo form_input('photo_width', (isset($_POST['photo_width']) ? $_POST['photo_width'] : $sample_id_card->photo_width), 'class="form-control text-right" required="required" id="photo_width"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("photo_height", "photo_height"); ?>
										<?php echo form_input('photo_height', (isset($_POST['photo_height']) ? $_POST['photo_height'] : $sample_id_card->photo_height), 'class="form-control text-right" required="required" id="photo_height"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("font_size", "font_size"); ?>
										<?php echo form_input('font_size', (isset($_POST['font_size']) ? $_POST['font_size'] : $sample_id_card->font_size), 'class="form-control text-right" required="required" id="font_size"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("profile_padding_top", "profile_padding_top"); ?>
										<?php echo form_input('profile_padding_top', (isset($_POST['profile_padding_top']) ? $_POST['profile_padding_top'] : $sample_id_card->profile_padding_top), 'class="form-control text-right" required="required" id="profile_padding_top"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("profile_padding_left", "profile_padding_left"); ?>
										<?php echo form_input('profile_padding_left', (isset($_POST['profile_padding_left']) ? $_POST['profile_padding_left'] : $sample_id_card->profile_padding_left), 'class="form-control text-right" required="required" id="profile_padding_left"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("working_padding_left", "working_padding_left"); ?>
										<?php echo form_input('working_padding_left', (isset($_POST['working_padding_left']) ? $_POST['working_padding_left'] : $sample_id_card->working_padding_left), 'class="form-control text-right" required="required" id="working_padding_left"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("qrcode_size", "qrcode_size"); ?>
										<?php echo form_input('qrcode_size', (isset($_POST['qrcode_size']) ? $_POST['qrcode_size'] : $sample_id_card->qrcode_size), 'class="form-control text-right" required="required" id="qrcode_size"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("qrcode_padding_top", "qrcode_padding_top"); ?>
										<?php echo form_input('qrcode_padding_top', (isset($_POST['qrcode_padding_top']) ? $_POST['qrcode_padding_top'] : $sample_id_card->qrcode_padding_top), 'class="form-control text-right" required="required" id="qrcode_padding_top"'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<?= lang("qrcode_padding_left", "qrcode_padding_left"); ?>
										<?php echo form_input('qrcode_padding_left', (isset($_POST['qrcode_padding_left']) ? $_POST['qrcode_padding_left'] : $sample_id_card->qrcode_padding_left), 'class="form-control text-right" required="required" id="qrcode_padding_left"'); ?>
									</div>
								</div>
							</div>
						</div>
                    </div>
					<div class="col-sm-12">
						<div class="fprom-group">
							<?php echo form_submit('save_dimension', $this->lang->line("submit"), 'id="save_dimension" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
						</div>
					</div>
                </div>
                <?php echo form_close(); ?>
            </div>

        </div>
    </div>
</div>

<center>
	<table>
		<tr>
			<td>
				<table class="main_table">
					<tr>
						<td class="front_card">
							<div class="employee_photo">
								<img id="employee_photo" src="<?= base_url('assets/images/male.png') ?>"  width="<?= $sample_id_card->photo_width ?>" height="<?= $sample_id_card->photo_height ?>"/>
							</div>
							<div class="employee_name">
								Mr. Lionel Messi
							</div>
							<div class="employee_position">
								Software Manager
							</div>
							<div class="employee_working">
								<table>
									<tr>
										<td class="working">ID Nº</td>
										<td class="working"> : </td>
										<td class="working">E001</td>
									</tr>
									<tr>
										<td class="working">Department</td>
										<td class="working"> : </td>
										<td class="working">IT</td>
									</tr>
									<tr>
										<td class="working">Issued</td>
										<td class="working"> : </td>
										<td class="working"><?= $this->bpas->hrsd(date("2020-01-01")) ?></td>
									</tr>
									<tr>
										<td class="working">Expired</td>
										<td class="working"> : </td>
										<td class="working"><?= $this->bpas->hrsd(date("2020-12-31")) ?></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</td>
			<td style="width:5px"></td>
			<td>
				<table class="main_table">
					<tr>
						<td class="back_card">
							<div class="qrcode">
								<?=  $this->bpas->qrcode('',111, 2) ?>
							</div>
						</td>
					</tr>
				</table>
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

<script type="text/javascript">
	$(document).ready(function () {
		var old_value;
		$(document).on("focus", '#photo_width, #photo_height, #font_size, #profile_padding_top, #profile_padding_left, #working_padding_left, #qrcode_size, #qrcode_padding_top, #qrcode_padding_left', function () {
			old_value = $(this).val();
		}).on("change", '#photo_width, #photo_height, #font_size, #profile_padding_top, #profile_padding_left, #working_padding_left, #qrcode_size, #qrcode_padding_top, #qrcode_padding_left', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) ) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			generateDimension();
		});
		
		function generateDimension(){
			var font_size = $("#font_size").val() - 0;
			var profile_padding_top = $("#profile_padding_top").val() - 0;
			var profile_padding_left = $("#profile_padding_left").val() - 0;
			var working_padding_left = $("#working_padding_left").val() - 0;
			var qrcode_size = $("#qrcode_size").val() - 0;
			var qrcode_padding_top = $("#qrcode_padding_top").val() - 0;
			var qrcode_padding_left = $("#qrcode_padding_left").val() - 0;
			var photo_width = $("#photo_width").val() - 0;
			var photo_height = $("#photo_height").val() - 0;
			
			$('.employee_name').css("font-size",font_size+"px");
			$('.employee_position').css("font-size",(font_size - 3)+"px");
			$('.employee_working').css("font-size",(font_size - 3)+"px");
			$('.working').css("line-height",(font_size + 7)+"px");
			
			$('.employee_photo').css("padding-top",profile_padding_top+"%");
			$('.employee_photo').css("padding-left",profile_padding_left+"%");
			$('.employee_name').css("padding-left",profile_padding_left+"%");
			$('.employee_position').css("padding-left",profile_padding_left+"%");
			
			$('.employee_working').css("padding-left",working_padding_left+"%");
			$('.qrimg').css("width",qrcode_size+"px");
			$('.qrcode').css("padding-top",qrcode_padding_top+"%");
			$('.qrcode').css("padding-left",qrcode_padding_left+"%");
			
			$('#employee_photo').attr('width',photo_width);
			$('#employee_photo').attr('height',photo_height);
		}

	});
</script>