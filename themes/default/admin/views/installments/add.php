<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i>
			<?= lang('add_installment') ?>
		</h2>
    </div>
    <div class="box-content">
		<div class="row">
			<div class="col-sm-12">
				<p class="introtext"><?php echo lang('enter_info'); ?></p>
				<?php
					$attrib = array('role' => 'form', 'id' => 'form-submit'); 
					echo admin_form_open("installments/add/".$id."/".$down_payment_id, $attrib); ?>
					<?php if ($Owner || $Admin || $GP['installments-date']) { ?>
						<div class="col-md-4">
							<div class="form-group">
								<label for="date"><?= lang('date') ?></label>
								<?php echo form_input('date', $this->bpas->hrld(date("Y-m-d H:i")), 'class="form-control datetime" id="date" required="required"'); ?>
							</div>
						</div>
					<?php } ?>
                    <div class="col-md-4 hide">
                        <?= lang("biller", "biller"); ?>
                        <?php
                        $bl[""] = "";
                        foreach ($billers as $biller) {
                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                        }
                        echo form_dropdown('biller', $bl, (isset($sale->biller_id) ? $sale->biller_id : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                        ?>
                    </div>
					<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
						<div class="form-group">
							<label for="reference_no"><?= lang('reference_no') ?></label>
							<input name="reference_no" type="text" id="reference_no" class="form-control"/>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang("si_reference", "si_reference"); ?>
							<?php
							$sa_opts[""] =  lang('select').' '.lang('sale') ;
							if($sales){
								foreach ($sales as $r_sale) {
									$sa_opts[$r_sale->id] = $r_sale->reference_no;
								}
							}
							echo form_dropdown('si_reference', $sa_opts, (isset($id) ? $id: ''), 'id="si_reference" class="form-control input-tip select" required="required"');
							?>
						</div>
					</div>
					<!-- <div class="col-md-4">
						<div class="form-group">
							<?= lang("product", "product"); ?>
							<?php
							if ($sales) {
								$item_opts[""] =  lang('all').' '.lang('product') ;
								foreach ($sale_items as $item) {
									$item_opts[$item->product_id] = $item->product_name;
								}
							}
							echo form_dropdown('product', $item_opts, (isset($id) ? $id: ''), 'id="product" class="form-control input-tip select" required="required"');
							?>
						</div>
					</div> -->
					<div class="col-md-4">
						<div class="form-group">
							<label for="product"><?= lang('product') ?></label>
							<select name="product" class="form-control" id="product" placeholder="<?= lang('product') ?>" required="required">
								<option value=""><?= lang("all") . ' ' . lang('product'); ?></option>
								<?php if (!empty($sales)) { ?>
									<?php foreach ($sale_items as $item) { ?>
										<option value="<?= $item->product_id; ?>" sale_item_id="<?= $item->id; ?>"><?= $item->product_name; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>
					<input type="hidden" name="sale_item_id" id="sale_item_id" value="">
					<div class="col-md-4">
						<div class="form-group">
							<label for="installment_amount"><?= lang('installment_amount') ?></label>
							<input name="installment_amount" type="text" required value="<?= set_value("installment_amount",$installment_amount);?>" id="installment_amount" class="form-control" placeholder="<?= lang('installment_amount') ?>"/>
						</div>
					</div>
					<div class="col-md-4 hidden">
						<div class="form-group">
							<label for="deposit"><?= lang('deposit') ?></label>
							<input name="deposit" readonly type="text" value="<?= set_value("deposit", $deposit);?>" id="deposit" class="form-control" placeholder="<?= lang('deposit') ?>"/>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="principal_amount"><?= lang('principal_amount') ?></label>
							<span style="color:#FF5454; font-weight:bold; font-size:11px;"> ( <?= lang("deposit") ?> = <?= $deposit; ?> ) </span>
							<input name="principal_amount" readonly value="<?= set_value("principal_amount", 0);?>" type="text" id="principal_amount" class="form-control" placeholder="<?= lang('principal_amount') ?>" required />
						</div>
					</div>
					<div class="col-md-4 hide">
						<div class="form-group">
							<label for="insurance"><?= lang('insurance') ?></label>
							<input name="insurance" value="<?= set_value("insurance", 0);?>" type="text" id="insurance" class="form-control" placeholder="<?= lang('insurance') ?>" />
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-8">
								<div class="form-group">
									<label for="interest_rate">
										<?= lang('interest_rate') ?> (%)
									</label>
									<input name="interest_rate" type="text" value="<?= set_value("interest_rate", 0);?>" id="interest_rate" class="form-control number_only" placeholder="<?= lang('interest_rate') ?>"/>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="interest_number">&nbsp;</label>
									<input type="text" id="interest_number" class="form-control number_only"/>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="frequency"><?= lang('frequency') ?></label>
							<select name='frequency' id='frequency' required class='form-control'>
								<?php foreach($frequencies as $frequency){
									echo '<option day="'.$frequency->day.'" value="'.$frequency->id.'">'.$frequency->description.'</option>';
								} ?>
							</select>
						</div>
					</div>
					<div class="col-md-4 schedule_box">
						<div class="form-group">
							<label for="term"><?= lang('term') ?></label>
							<input name="term" type="number" value="12" id="term" min="0" class="form-control" placeholder="<?= lang('term') ?>" required/>
						</div>
					</div>
					<div class="col-md-4 schedule_box">
						<div class="form-group">
							<label for="payment_date"><?= lang('first_payment_date') ?></label>
							<?php echo form_input('payment_date', $this->bpas->hrsd(date("Y-m-d", strtotime(" + 1 MONTH"))), 'class="form-control date" id="payment_date" required="required"'); ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="interest_method"><?= lang('interest_method') ?></label>
							<select name="interest_method" class="form-control" id="interest_method" required placeholder="<?= lang('interest_method') ?>">
								<option value="1"><?= lang("amortize"); ?></option>
								<option value="2"><?= lang("effective"); ?></option>
								<option value="3"><?= lang("flat_rate"); ?></option>
							</select>
						</div>
					</div>
					<!-- <div class="col-md-4">
						<div class="form-group">
							<label for="interest_period"><?= lang('interest_period') ?></label>
							<select name="interest_period" class="form-control" id="interest_period" required placeholder="<?= lang('interest_period') ?>">
								<option value="0"><?= lang("yearly"); ?></option>
								<option value="1"><?= lang("monthly"); ?></option>
								<option value="2"><?= lang("weekly"); ?></option>
								<option value="3"><?= lang("daily"); ?></option>
							</select>
						</div>
					</div> -->
					<div class="col-md-4">
						<div class="form-group">
							<label for="interest_period"><?= lang('interest_period') ?></label>
							<!-- <select name="interest_period" class="form-control" id="interest_period" required placeholder="<?= lang('interest_period') ?>">
								<option value="0"><?= lang("yearly"); ?></option>
								<option value="1"><?= lang("monthly"); ?></option>
								<option value="2"><?= lang("weekly"); ?></option>
								<option value="3"><?= lang("daily"); ?></option>
							</select> -->
							<?php 
								$ip  = array();
								$ipd = array();
								foreach($interest_period as $period){
									$ip[$period->id] = lang($period->name);
									$ipd[$period->id] = $period->day;
								}
								echo form_dropdown("interest_period", $ip, 0, " id='interest_period' class='form-control' ");
							?>
						</div>
					</div>
					<?php if ($Settings->installment_penalty_option == 2) { ?>
					<div class="col-md-4">
						<div class="form-group">
							<?= lang("penalty", "penalty"); ?>
							<?php
							if (!empty($penalty)) {
								foreach ($penalty as $pnt) {
									$penalty_opts[$pnt->id] = $pnt->amount . $pnt->type;
								}
							} else {
								$penalty_opts[""] =  lang('select').' '.lang('penalty') ;
							}
							echo form_dropdown('penalty', $penalty_opts, '', 'id="penalty" class="form-control input-tip select" required="required"');
							?>
						</div>
					</div>
					<?php } ?>
					<div class="clearfix"></div>
					<div class="col-md-12">
						<label class="table-label"><br/></label>
						<div class="table-responsive">
							<table class='table table-bordered table-hover table-striped'>
								<thead>
									<tr>
										<th width='5%'><?= lang('#') ?></th>
										<th	width='130'><?= lang('deadline') ?></th>
										<th	width='130'><?= lang('payment') ?></th>
										<th	width='130'><?= lang('interest') ?></th>
										<th	width='130'><?= lang('principal') ?></th>
										<th	width='130'><?= lang('balance') ?></th>
										<th	width='130'><?= lang('remark') ?></th>
									</tr>
								</thead>
								<tbody id="data_schedule"></tbody>
							</table>
							<?php //if(isset($id) && $id){ ?>
								<button type="submit" name="add_installment" class="btn btn-primary no-print" id="add_installment"><?= lang('submit') ?></button>
							<?php //} ?>
						</div>
					</div>
				<?= form_close(); ?>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	@media print{
		table{
			margin-top:20px;
		}
		input{
			border:none !important;
			box-shadow:none !important;
		}
	}
</style>
<script type="text/javascript">
	$(function(){
		$('#add_installment').on("click",function(e){
			e.preventDefault();
			bootbox.confirm({
				message: "<?= lang('confirm_installment') ?>",
				buttons: {
					confirm: {
						label: 'Yes',
						className: 'btn-success'
					},
					cancel: {
						label: 'No',
						className: 'btn-danger'
					}
				},
				callback: function (result) {
					if(result){
						$("#form-submit").submit();
					}
				}
			});
			return false;
		});
		
		<?php if($Settings->installment_holiday==1){ ?>
			var holiday = [];
			$.ajax({
				url  : "<?= admin_url("installments/get_holiday") ?>",
				type : "GET",
				contentType : "application/json; charset=utf-8",
				success:function(data){
					var data = JSON.parse(data);
						$(data).each(function(){
							holiday.push(this);
						});
				}
			});
		<?php } ?>
		
		$("#interest_rate, #installment_amount, #deposit").keydown(function (e) {
			if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
				(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
				(e.keyCode >= 35 && e.keyCode <= 40)) {
					 return;
			}
			if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
				e.preventDefault();
			}
		});
		
		$("#interest_number").on("change",function(){
			var interest_number = $("#interest_number").val() != null ? ($("#interest_number").val()) : 0;
			var principal_amount = formatDecimal($("#principal_amount").val());
			var interest_rate = (interest_number * 100) / principal_amount;
			$("#interest_rate").val(interest_rate).change();
		});
		
		$("#installment_amount, #deposit, #interest_method").on("change",function(){
			var installment_amount = formatDecimal($("#installment_amount").val());
			var deposit = formatDecimal($("#deposit").val());
			var principal_amount = installment_amount - deposit;
			$("#principal_amount").val(principal_amount);
		});
		
		$("#frequency").live("change",function(){
			frequencyDeadline();
		});
		frequencyDeadline();
		function frequencyDeadline(){
			var frequency = parseInt($("#frequency option:selected").attr("day"));
			if(frequency == 0){
				$('.schedule_box').slideUp();
				var frequency_id = $('#frequency').val();
				$.ajax({
					url  : "<?= admin_url("installments/get_frequency_deadlines") ?>",
					dataType: "json",
                    data: {
                        frequency_id: frequency_id,
                    },
                    success: function (data) {
						$("#term").val(data.length);
						$("#term").change();
						var u = 0;
						$('.tdate').each(function(){
							var tdate = $(this).val(fsd(data[u].deadline));
							u++;
						});
                    }
				});
			}else{
				$('.schedule_box').slideDown();
			}
		}
		
		$("#term, #frequency, #interest_period, #interest_method, #principal_amount, #interest_rate, #deposit, #payment_date").live("change", calculator);
		
		calculator();
		
		function calculator(){
			$("#installment_amount").change();
			var interest_method = parseInt($("#interest_method option:selected").val());
			var frequency = parseInt($("#frequency option:selected").attr("day"));
			var interest_period = parseInt($("#interest_period option:selected").val());
			var term = $("#term").val() != null ? parseInt($("#term").val()) : 0;
			var interest_rate = $("#interest_rate").val() != null ? ($("#interest_rate").val()) : 0;
			var installment_payment_date = $("#payment_date").val();
			var principal_amount = formatDecimal($("#principal_amount").val());
			var number_term = term;
			var html = "";
			var w = 0; 
			var x = 1; 
			var y = 1;
			var total_principal = 0; 
			var total_payment = 0;
			var total_rate = 0;
			var trate = 0;
			var tprincipal = formatDecimal(principal_amount);
			var tbalance = formatDecimal(principal_amount);
			var tpayment_date_split = installment_payment_date.split("/");
				tpayment_date  = new Date(tpayment_date_split[2], tpayment_date_split[1] - 1, tpayment_date_split[0]);
			// var period = {
			// 	0 : 360,
			// 	1 : 30,
			// 	2 : 7,
			// 	3 : 1
			// }
			var period = <?= json_encode($ipd); ?>;
			if(interest_method == 1){
				var rate = (interest_rate / 100) * (frequency / period[interest_period]);
				var rate_paid = Math.pow(( 1 + rate ),number_term);
				var tpayment = (tbalance * rate) * rate_paid / (rate_paid - 1); 
				if(interest_rate <= 0){
					tpayment = tbalance / number_term;
				}
				for(var i = 1; i <= number_term; i++){
					if(i==1){
						var tdeadline = moment(tpayment_date).format('DD/MM/YYYY');
					}else{
						var tdeadline = moment(tpayment_date).add(w,'days').format('DD/MM/YYYY');
						if(frequency == 30){
							var tdeadline = moment(tpayment_date).add(w,'months').format('DD/MM/YYYY');
						}
						
					}
					// Check weekend day
					if(frequency == 30 && 0) {
						<?php if($Settings->installment_holiday==1){ ?>
							$(holiday).each(function(i,e){
								var hol = e.split("-");
								var hol_d = new Date(hol[2], hol[1] - 1, hol[0]);
								var dl = tdeadline.split("/");
								var hdl = new Date(dl[2], dl[1] - 1, dl[0]);
								if (hdl.getTime() == hol_d.getTime()){
									hdl.setDate(hdl.getDate() + 1);
									tpayment_date.setDate(hdl.getDate());
									tdeadline = ('0' + hdl.getDate()).slice(-2)+"/"+ ('0' + (hdl.getMonth() + 1)).slice(-2) + "/" + hdl.getFullYear();
								}
							});
							var from = tdeadline.split("/");
							var d = new Date(from[2], from[1] - 1, from[0]);
							if(d.getDay() == 6){
								var d = new Date(from[2], from[1] - 1, from[0]);	
										d.setDate(d.getDate() + 2);
										tpayment_date.setDate(d.getDate());
							}
							if(d.getDay() == 0){
								var d = new Date(from[2], from[1] - 1, from[0]);
										d.setDate(d.getDate() + 1);
										tpayment_date.setDate(d.getDate());
							}
							tdeadline = ('0' + d.getDate()).slice(-2)+"/"+ ('0' + (d.getMonth() + 1)).slice(-2) + "/" + d.getFullYear();
						<?php } ?>
					}
					// End check weekend day
					var trate = formatDecimal(tbalance*(interest_rate/100)) * (frequency / period[interest_period]);
					tprincipal = formatDecimal(tpayment) - formatDecimal(trate);
					tbalance -= formatDecimal(tprincipal);
					if(tbalance <= 0){
						tbalance = 0;
					}else if(i == number_term){
						tprincipal = formatDecimal(tprincipal) + formatDecimal(tbalance);
						tpayment = formatDecimal(tpayment) + formatDecimal(tbalance);
						tbalance = 0;
					}
					html += "<tr>";
						html += "<td class=center><input class='form-control tperiod' name='tperiod[]' type='hidden' value='"+y+"' />"+y+"</td>";
						html += "<td class=center><input class='form-control date tdate' name='tdeadline[]' style='text-align:center;' value='"+tdeadline+"' /></td>";
						html += "<td class=center><input class='form-control tpayment' name='tpayment[]' style='text-align:right;' value='"+formatDecimal(tpayment)+"' /></td>";
						html += "<td class=center><input class='form-control trate' name='trate[]' style='text-align:right;' value='"+formatDecimal(trate)+"' /></td>";
						html += "<td class=center><input class='form-control tprincipal' name='tprincipal[]' style='text-align:right;' value='"+formatDecimal(tprincipal)+"' /></td>";
						html += "<td class=center><input readonly class='form-control tbalance' name='tbalance[]' style='text-align:right;' value='"+formatDecimal(tbalance)+"' /></td>";
						html += "<td class=center><input class='form-control note' name='note[]' /></td>";
					html += "</tr>";
					total_payment += formatDecimal(tpayment);
					total_rate += formatDecimal(trate);
					total_principal += formatDecimal(tprincipal);
					if(frequency == 30){
						w += 1;
					}else{
						w += frequency;
					}
					y++;
				}
				
				html += "<tr>";
					html += "<th></th>";
					html += "<th><input type='text' class='form-control' style='text-align:right;' autocomplete='off' id='keyin_payment' /></th>";
					html += "<th class='right total_payment'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_payment)+"'/></th>";
					html += "<th class='right total_rate'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_rate)+"'/></th>";
					html += "<th class='right total_principal'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_principal)+"'/></th>";
					html += "<th></th><th></th>";
				html += "</tr>";
			}
			else if(interest_method == 2){
				tprincipal = (tbalance / number_term);
				for(i = 1; i <= number_term; i++){
					if(i==1){
						var tdeadline = moment(tpayment_date).format('DD/MM/YYYY');
					}else{
						var tdeadline = moment(tpayment_date).add(w,'days').format('DD/MM/YYYY');
						if(frequency == 30){
							var tdeadline = moment(tpayment_date).add(w,'months').format('DD/MM/YYYY');
						}
					}
					// Check weekend day
					if(frequency == 30 && 0) {
						<?php if($Settings->installment_holiday==1){ ?>
							$(holiday).each(function(i,e){
								var hol = e.split("-");
								var hol_d = new Date(hol[2], hol[1] - 1, hol[0]);
								var dl = tdeadline.split("/");
								var hdl = new Date(dl[2], dl[1] - 1, dl[0]);
								if (hdl.getTime() == hol_d.getTime()){
									hdl.setDate(hdl.getDate() + 1);
									tpayment_date.setDate(hdl.getDate());
									tdeadline = ('0' + hdl.getDate()).slice(-2)+"/"+ ('0' + (hdl.getMonth() + 1)).slice(-2) + "/" + hdl.getFullYear();
								}
							});
							var from = tdeadline.split("/");
							var d = new Date(from[2], from[1] - 1, from[0]);
							if(d.getDay() == 6){
								var d = new Date(from[2], from[1] - 1, from[0]);	
										d.setDate(d.getDate() + 2);
										tpayment_date.setDate(d.getDate());
							}
							if(d.getDay() == 0){
								var d = new Date(from[2], from[1] - 1, from[0]);
										d.setDate(d.getDate() + 1);
										tpayment_date.setDate(d.getDate());
							}
							tdeadline = ('0' + d.getDate()).slice(-2)+"/"+ ('0' + (d.getMonth() + 1)).slice(-2) + "/" + d.getFullYear();
						<?php } ?>
					}
					// End check weekend day
					trate = (tbalance*(interest_rate/100)) * (frequency / period[interest_period]);
					tpayment = (tprincipal + trate);
					tbalance  -= tprincipal;
					if(tbalance <= 0){
						tbalance = 0;
					}else if(i == number_term){
						tprincipal = tprincipal + tbalance;
						tpayment = tpayment + tbalance;
						tbalance = 0;
					}
					html += "<tr>";
						html += "<td class=center><input class='form-control tperiod' name='tperiod[]' type='hidden' value='"+y+"' />"+y+"</td>";
						html += "<td class=center><input class='form-control date tdate' name='tdeadline[]' value='"+tdeadline+"' style='text-align:center;'  /></td>";
						html += "<td class=center><input class='form-control tpayment' name='tpayment[]' value='"+formatDecimal(tpayment)+"' style='text-align:right;'  /></td>";
						html += "<td class=center><input class='form-control trate' name='trate[]' value='"+formatDecimal(trate)+"'  style='text-align:right;' /></td>";
						html += "<td class=center><input class='form-control tprincipal' name='tprincipal[]' value='"+formatDecimal(tprincipal)+"' style='text-align:right;'  /></td>";
						html += "<td class=center><input readonly class='form-control tbalance' name='tbalance[]' value='"+formatDecimal(tbalance)+"' style='text-align:right;'  /></td>";
						html += "<td class=center><input class='form-control note' name='note[]' /></td>";
					html += "</tr>";
					total_payment += formatDecimal(tpayment);
					total_rate += formatDecimal(trate);
					total_principal += formatDecimal(tprincipal);
					if(frequency == 30){
						w += 1;
					}else{
						w += frequency;
					}
					y++;
				}
				html += "<tr>";
					html += "<th></th>";
					html += "<th></th>";
					html += "<th class='right total_payment'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_payment)+"'/></th>";
					html += "<th class='right total_rate'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_rate)+"'/></th>";
					html += "<th class='right total_principal'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_principal)+"'/></th>";
					html += "<th></th><th></th>";
				html += "</tr>";

			}else if(interest_method == 3){
				tprincipal = (principal_amount / number_term);
				for(i = 1; i <= number_term; i++){
					if(i==1){
						var tdeadline = moment(tpayment_date).format('DD/MM/YYYY');
					}else{
						var tdeadline = moment(tpayment_date).add(w,'days').format('DD/MM/YYYY');
						if(frequency == 30){
							var tdeadline = moment(tpayment_date).add(w,'months').format('DD/MM/YYYY');
						}
					}
					// Check weekend day
					if(frequency == 30 && 0) {
						<?php if($Settings->installment_holiday==1){ ?>
							$(holiday).each(function(i,e){
								var hol = e.split("-");
								var hol_d = new Date(hol[2], hol[1] - 1, hol[0]);
								var dl = tdeadline.split("/");
								var hdl = new Date(dl[2], dl[1] - 1, dl[0]);
								if (hdl.getTime() == hol_d.getTime()){
									hdl.setDate(hdl.getDate() + 1);
									tpayment_date.setDate(hdl.getDate());
									tdeadline = ('0' + hdl.getDate()).slice(-2)+"/"+ ('0' + (hdl.getMonth() + 1)).slice(-2) + "/" + hdl.getFullYear();
								}
							});
							var from = tdeadline.split("/");
							var d = new Date(from[2], from[1] - 1, from[0]);
							if(d.getDay() == 6){
								var d = new Date(from[2], from[1] - 1, from[0]);	
										d.setDate(d.getDate() + 2);
										tpayment_date.setDate(d.getDate());
							}
							if(d.getDay() == 0){
								var d = new Date(from[2], from[1] - 1, from[0]);
										d.setDate(d.getDate() + 1);
										tpayment_date.setDate(d.getDate());
							}
							tdeadline = ('0' + d.getDate()).slice(-2)+"/"+ ('0' + (d.getMonth() + 1)).slice(-2) + "/" + d.getFullYear();
						<?php } ?>
					}
					// End check weekend day
					trate = (principal_amount*(interest_rate/100)) * (frequency / period[interest_period]);
					tpayment = (tprincipal + trate);
					tbalance -= tprincipal;
					if(tbalance <= 0){
						tbalance = 0;
					}else if(i == number_term){
						tprincipal = tprincipal + tbalance;
						tpayment = tpayment + tbalance;
						tbalance = 0;
					}
					html += "<tr>";
						html += "<td class=center><input class='form-control tperiod' name='tperiod[]' type='hidden' value='"+y+"' />"+y+"</td>";
						html += "<td class=center><input class='form-control date tdate' name='tdeadline[]' value='"+tdeadline+"' style='text-align:center;'  /></td>";
						html += "<td class=center><input class='form-control tpayment' name='tpayment[]' value='"+formatDecimal(tpayment)+"' style='text-align:right;'  /></td>";
						html += "<td class=center><input class='form-control trate' name='trate[]' value='"+formatDecimal(trate)+"'  style='text-align:right;' /></td>";
						html += "<td class=center><input class='form-control tprincipal' name='tprincipal[]' value='"+formatDecimal(tprincipal)+"' style='text-align:right;'  /></td>";
						html += "<td class=center><input readonly class='form-control tbalance' name='tbalance[]' value='"+formatDecimal(tbalance)+"' style='text-align:right;'  /></td>";
						html += "<td class=center><input class='form-control note' name='note[]' /></td>";
					html += "</tr>";
					total_payment += formatDecimal(tpayment);
					total_rate += formatDecimal(trate);
					total_principal += formatDecimal(tprincipal);
					if(frequency == 30){
						w += 1;
					}else{
						w += frequency;
					}
					y ++;
				}
				html += "<tr>";
					html += "<th></th>";
					html += "<th></th>";
					html += "<th class='right total_payment'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_payment)+"'/></th>";
					html += "<th class='right total_rate'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_rate)+"'/></th>";
					html += "<th class='right total_principal'><input type='text' readonly class='form-control' style='text-align:right;' value='"+formatDecimal(total_principal)+"'/></th>";
					html += "<th></th><th></th>";
				html += "</tr>";
			}
			var interest_number = (interest_rate * principal_amount) / 100;
			$("#interest_number").val(interest_number);
			$("#data_schedule").html(html);
		}
		
		// Principal and Rate change
		$(document).on('change', '.tprincipal, .trate', function (e) {
			var parent = $(this).parent().parent();
			var amt_frequency = parseInt($("#frequency option:selected").attr("day"));
			var amt_period = parseInt($("#interest_period option:selected").val());
			var amt_installment = formatDecimal($("#principal_amount").val());
			var amt_rate = formatDecimal($("#interest_rate").val());
			var amt_method = parseInt($("#interest_method option:selected").val());
			var self = parent.find(".tperiod").val() - 0;
			var amt_principal = 0, i = 0;
			// var period = {
			// 	0 : 360,
			// 	1 : 30,
			// 	2 : 7,
			// 	3 : 1
			// }
			var period = <?= json_encode($ipd); ?>;
			$(".tprincipal").each(function(){
				var parent = $(this).parent().parent();
				var loop_above = parent.find(".tperiod").val() - 0;
				if(loop_above > self){
					i++;
				}else{
					var tprincipal = $(this).val() - 0
					amt_principal += tprincipal;
				}
			});
			var balance = (amt_installment - amt_principal);
			var below = (balance / i);
			$('.tprincipal').each(function(){
				var parent = $(this).parent().parent();
				var loop_below = parent.find(".tperiod").val() - 0;
				if(loop_below > self){
					$(this).val(below);
				}else{
					if(loop_below == self){
						var self_principal = formatDecimal($(this).val());
						var self_interest = formatDecimal(parent.find(".trate").val());
						var self_payment = self_principal + self_interest;
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".tpayment").val(formatDecimal(self_payment));
					}
				}
			});
			var total_principal = 0;
			var total_rate = 0;
			var total_payment = 0;
			if(amt_method == 1){
				var rate = (amt_rate / 100) * (amt_frequency / period[amt_period]);
				var rate_paid = Math.pow(( 1 + rate ),i);
				var payment = (balance * rate) * rate_paid / (rate_paid - 1); 
				if(amt_rate <= 0){
					payment = balance / i;
				}
				$('.tprincipal').each(function(){
					var parent = $(this).parent().parent();
					var loop_below = parent.find(".tperiod").val() - 0;
					if(loop_below > self){
						var rate = ((balance * amt_rate) / 100) * (amt_frequency / period[amt_period]);
						var principal = payment - rate
						balance -= formatDecimal(principal);
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".tprincipal").val(formatDecimal(principal));
						parent.find(".trate").val(formatDecimal(rate));
						parent.find(".tpayment").val(formatDecimal(payment));
					}
					total_payment += parent.find(".tpayment").val()-0;
					total_rate += parent.find(".trate").val()-0;
					total_principal += parent.find(".tprincipal").val()-0;
				});
			}else if(amt_method == 2){
				$('.tprincipal').each(function(){
					var parent = $(this).parent().parent();
					var loop_below = parent.find(".tperiod").val() - 0;
					if(loop_below > self){
						var rate = ((balance * amt_rate) / 100) * (amt_frequency / period[amt_period]);
						balance -= formatDecimal(below);
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".trate").val(formatDecimal(rate));
						parent.find(".tpayment").val(formatDecimal(below + rate));
					}
					total_payment += parent.find(".tpayment").val()-0;
					total_rate += parent.find(".trate").val()-0;
					total_principal += parent.find(".tprincipal").val()-0;
				});
			}else{
				$('.tprincipal').each(function(){
					var parent = $(this).parent().parent();
					var loop_below = parent.find(".tperiod").val() - 0;
					if(loop_below > self){
						var rate = ((amt_installment * amt_rate) / 100) * (amt_frequency / period[amt_period]);
						balance -= formatDecimal(below);
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".trate").val(formatDecimal(rate));
						parent.find(".tpayment").val(formatDecimal(below + rate));
					}
					total_payment += parent.find(".tpayment").val()-0;
					total_rate += parent.find(".trate").val()-0;
					total_principal += parent.find(".tprincipal").val()-0;
				});
			}
			$(".total_payment input").val(formatDecimal(total_payment));
			$(".total_rate input").val(formatDecimal(total_rate));
			$(".total_principal input").val(formatDecimal(total_principal));
		});
		
		// Payment change
		$(document).on('change', '.tpayment', function (e) {
			var parent = $(this).parent().parent();
			var amt_frequency = parseInt($("#frequency option:selected").attr("day"));
			var amt_period = parseInt($("#interest_period option:selected").val());
			var amt_installment = formatDecimal($("#principal_amount").val());
			var amt_rate = formatDecimal($("#interest_rate").val());
			var amt_method = parseInt($("#interest_method option:selected").val());
			var self = parent.find(".tperiod").val() - 0;
			var amt_principal = 0, i = 0;
			// var period = {
			// 	0 : 360,
			// 	1 : 30,
			// 	2 : 7,
			// 	3 : 1
			// }
			var period = <?= json_encode($ipd); ?>;
			$('.tprincipal').each(function(){
				var parent = $(this).parent().parent();
				var loop_below = parent.find(".tperiod").val() - 0;
				if(loop_below > self){
				}else{
					if(loop_below == self){
						var self_payment = formatDecimal(parent.find(".tpayment").val());
						var self_interest = formatDecimal(parent.find(".trate").val());
						var self_principal = self_payment - self_interest;
						parent.find(".tprincipal").val(formatDecimal(self_principal));
					}
				}
			});
			$(".tprincipal").each(function(){
				var parent = $(this).parent().parent();
				var loop_above = parent.find(".tperiod").val() - 0;
				if(loop_above > self){
					i++;
				}else{
					var tprincipal = $(this).val() - 0
					amt_principal += tprincipal;
				}
			});
			var balance = (amt_installment - amt_principal);
			var below = (balance / i);
			$('.tprincipal').each(function(){
				var parent = $(this).parent().parent();
				var loop_below = parent.find(".tperiod").val() - 0;
				if(loop_below > self){
					$(this).val(formatDecimal(below));
				}else{
					if(loop_below == self){
						parent.find(".tbalance").val(formatDecimal(balance));
					}
				}
			});
			var total_principal = 0;
			var total_rate = 0;
			var total_payment = 0;
			if(amt_method == 1){
				var rate = (amt_rate / 100) * (amt_frequency / period[amt_period]);
				var rate_paid = Math.pow(( 1 + rate ),i);
				var payment = (balance * rate) * rate_paid / (rate_paid - 1); 
				if(amt_rate <= 0){
					payment = balance / i;
				}
				$('.tprincipal').each(function(){
					var parent = $(this).parent().parent();
					var loop_below = parent.find(".tperiod").val() - 0;
					if(loop_below > self){
						var rate = ((balance * amt_rate) / 100) * (amt_frequency / period[amt_period]);
						var principal = payment - rate
						balance -= formatDecimal(principal);
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".tprincipal").val(formatDecimal(principal));
						parent.find(".trate").val(formatDecimal(rate));
						parent.find(".tpayment").val(formatDecimal(payment));
					}
					total_payment += parent.find(".tpayment").val()-0;
					total_rate += parent.find(".trate").val()-0;
					total_principal += parent.find(".tprincipal").val()-0;
				});
			}else if(amt_method == 2){
				$('.tprincipal').each(function(){
					var parent = $(this).parent().parent();
					var loop_below = parent.find(".tperiod").val() - 0;
					if(loop_below > self){
						var rate = ((balance * amt_rate) / 100) * (amt_frequency / period[amt_period]);
						balance -= formatDecimal(below);
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".trate").val(formatDecimal(rate));
						parent.find(".tpayment").val(formatDecimal(below + rate));
					}
					total_payment += parent.find(".tpayment").val()-0;
					total_rate += parent.find(".trate").val()-0;
					total_principal += parent.find(".tprincipal").val()-0;
				});
			}else{
				$('.tprincipal').each(function(){
					var parent = $(this).parent().parent();
					var loop_below = parent.find(".tperiod").val() - 0;
					if(loop_below > self){
						var rate = ((amt_installment * amt_rate) / 100) * (amt_frequency / period[amt_period]);
						balance -= formatDecimal(below);
						parent.find(".tbalance").val(formatDecimal(balance));
						parent.find(".tpayment").val(formatDecimal(below + rate));
					}
					total_payment += parent.find(".tpayment").val()-0;
					total_rate += parent.find(".trate").val()-0;
					total_principal += parent.find(".tprincipal").val()-0;
				});
			}
			$(".total_payment input").val(formatDecimal(total_payment));
			$(".total_rate input").val(formatDecimal(total_rate));
			$(".total_principal input").val(formatDecimal(total_principal));
		});
		
		$(document).on('change', '#keyin_payment', function (e) {
			var keyin_payment = $(this).val();
			$(".tpayment").not('.tpayment_0').each(function(i,e){
				$(this).val(keyin_payment).change();
			});
		});

		$('#si_reference').on('change',function(){
			var si_reference = $(this).val();
			console.log(site.base_url);
			location.replace(site.base_url+"installments/add/"+si_reference);
		});
		$('#product').on('change',function(){
			var sale_id 	= $("#si_reference").val();
			var product_id  = $(this).val();
			var sale_item_id = $(this).find(":selected").attr('sale_item_id');
			$.ajax({
				type: "get",
				url  : "<?= admin_url("installments/getSaleItemPrice") ?>",
				dataType: "json",
                data: {
                    sale_id: sale_id,
                    product_id: product_id,
                    sale_item_id: sale_item_id
                },
                success: function (data) {
					$("#installment_amount").val(data.price);
					calculator();
                }
			});
		});
  });
</script>

