<script>$(document).ready(function () {
        CURI = '<?= admin_url('reports/cashflow'); ?>';
    });</script>
<style>
@media print {
    .fa {
        color: #EEE;
        display: none;
    }

    .small-box {
        border: 1px solid #CCC;
    }
}
.second_lv{
	padding-left: 25px !important;
	text-align: left;
}
.third_lv{
	padding-left: 45px !important;
	text-align: left;
}
.amount_cash{

}
.positive{
	width: 100px !important;
	float: right;
	border-right: 1px solid #000;
	padding-right: 10px;
	text-align: right;
}
.negative{
	width: 100px !important;
	float: right;
	text-align: left;
	padding-left: 10px;
}
</style>
<?php

	$start_date=date('Y-m-d',strtotime($start));
	$rep_space_end=str_replace(' ','_',$end);
	$end_date=str_replace(':','-',$rep_space_end);
?>
<?php if ($Owner) {
    echo admin_form_open('reports/income_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('cashflow'); ?> >> <?= (isset($start)?$start:""); ?> >> <?= (isset($end)?$end:""); ?> </h2>

        <div class="box-icon">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                               id="daterange" class="form-control">
                        <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown"><a id="downloadLink" style="cursor: pointer;" onclick="exportF(this)" class="tip" title="<?= lang('export_excel') ?>">
                	<i class="icon fa fa-file-excel-o"></i></a></li>
				<li class="dropdown hide"><a href="#" id="xls" data-action="export_excel" class="tip" title="<?= lang('download_excel') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
				<li class="dropdown hide">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
							class="icon fa fa-building-o tip" data-placement="left"
							title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= admin_url('reports/cashflow') ?>"><i
									class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
						<li class="divider"></li>
						<?php
						$b_sep = 0;
						foreach ($billers as $biller) {
							$biller_sep = explode('-', $this->uri->segment(7));
							if($biller_sep[$b_sep] == $biller->id){
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="biller_checkbox[]" class="checkbox biller_checkbox" checked value="'. $biller->id .'" >&nbsp;&nbsp;' . $biller->company . '</li>';
								echo '<li class="divider"></li>';
								$b_sep++;
							}else{
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="biller_checkbox[]" class="checkbox biller_checkbox" value="'. $biller->id .'" >&nbsp;&nbsp;' . $biller->company . '</li>';
								echo '<li class="divider"></li>';
							}							
						}
						?>
						<li class="text-center"><a href="#" id="biller-filter" class="btn btn-primary"><?=lang('submit')?></a></li>
					</ul>
                </li>
                <li class="dropdown hide">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
							class="icon fa fa-building-o tip" data-placement="left"
							title="<?= lang("projects") ?>"></i></a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= admin_url('reports/cashflow') ?>"><i
									class="fa fa-building-o"></i> <?= lang('projects') ?></a></li>
						<li class="divider"></li>
						<?php
						$b_sep = 0;
						foreach ($projects as $project) {
							$biller_sep = explode('-', $this->uri->segment(7));
							if($biller_sep[$b_sep] == $project->project_id){
								echo '<li ' . ($biller_id && $biller_id == $project->project_id ? 'class="active"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="biller_checkbox[]" class="checkbox biller_checkbox" checked value="'. $project->project_id .'" >&nbsp;&nbsp;' . $project->project_name . '</li>';
								echo '<li class="divider"></li>';
								$b_sep++;
							}else{
								echo '<li ' . ($biller_id && $biller_id == $project->project_id ? 'class="active"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="biller_checkbox[]" class="checkbox biller_checkbox" value="'. $project->project_id .'" >&nbsp;&nbsp;' . $project->project_name . '</li>';
								echo '<li class="divider"></li>';
							}							
						}
						?>
						<li class="text-center"><a href="#" id="biller-filter" class="btn btn-primary"><?=lang('submit')?></a></li>
					</ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
				<?php 
				$num_col=2; 
				$total_income = 0;
                $totalBeforeAyear_income = 0;
				$total_income_array = array();
				$total_cost_array = array();
				$total_op_array = array();
				$sum_total_income = array();
				$sum_total_cost = array();
				$sum_total_op = array();
				$sum_total_gross = array();
				$tpl = array();
				$total_b =array();
				if(isset($this->uri->segments["3"])){
					$from = $start;//explode("%",$this->uri->segments["3"])[0];
				}

				if(isset($this->uri->segments["4"])){
					$to = $end; //explode("%",$this->uri->segments["4"])[0];
				}
											
				$from_st = !empty($from)? "&start_date=".$this->bpas->hrld($from) : "";
				$to_st = !empty($to)? "&end_date=".$this->bpas->hrld($to) : "";	
				
				?>
				<table class="hide">
					<tr>
					<?php
					//-------------------
							$new_billers = array();
							foreach ($billers as $b1) {
								if($this->uri->segment(7)){
									$biller_sep = explode('-', $this->uri->segment(7));
									for($i=0; $i < count($biller_sep); $i++){
										if($biller_sep[$i] == $b1->id){
											echo '<th style="text-align:right;">' . $b1->company . '</th>';
											$new_billers[] = array('id' => $b1->id);
										}
										
									}
								}else{
									$new_billers = $billers;
									echo '<th style="text-align:right !important;padding-right:100px;">' . $b1->company . '
									</th>';
								}
								
								$num_col++;
							}
					?>
					</tr>
					<?php
                        foreach($IncomesBeforeAyear->result() as $row){
						?>
						<tr class="hide">
						    <?php 
							$index = 0;
							$total_per_income = 0;
							$total_per_receivedBefore =0;
							$total_received =0;
							for($i = 1; $i <= count($new_billers); $i++){
								$bill_id = 0;
								if($this->uri->segment(7)){
									$bill_id = $new_billers[$index]['id'];
								}else{
									$bill_id = $new_billers[$index]->id;
								}
							  	$query = $this->db->query("SELECT
	                                SUM(COALESCE(bpas_payments.amount, 0)) AS amount
	                            FROM
	                                bpas_payments
	                            Left join bpas_sales ON bpas_sales.id = bpas_payments.sale_id		                            
	                            WHERE bpas_sales.biller_id = '" . $bill_id . "'
									AND bpas_payments.date < '$from_date' 
								");
								
	                            $data = $query->row();
								
								$business1 = $data->amount ? $data->amount:0;
								if(($index+1)==1){
								?>
									<td class="third_lv">b1</td>
									<td style="text-align:center !important;" class="amount_cash"><?= $business1;?></td>
								<?php
									$total_income_array_before[] = array(
										'id' => $bill_id,
										'amount' => $business1,
									);
								}else{?>
									<td class="right third_lv"><?= $business1;?></td>
								<?php	
									$total_income_array_before[] = array(
										'id' => $bill_id,
										'amount' => $business1,
									);
								}

								$total_per_receivedBefore += $data->amount;
								$index++;
							}
						
							echo '<td class="right third_lv">'. $total_per_receivedBefore .'</td>';
							?>
						</tr>
						<?php
						}
						?>
						<tr class="hide">
						    <?php 
							$index = 0;
							$total_per_incomeBefore = 0;
							for($i = 1; $i <= count($new_billers); $i++){
								$bill_id = 0;
								if($this->uri->segment(7)){
									$bill_id = $new_billers[$index]['id'];
								}else{
									$bill_id = $new_billers[$index]->id;
								}
							    $query = $this->db->query("SELECT SUM(COALESCE(bpas_gl_trans.amount, 0)) AS amount
	                            FROM bpas_gl_trans WHERE bpas_gl_trans.biller_id = '" . $bill_id . "'
									AND bpas_gl_trans.tran_date < '$from_date' AND bpas_gl_trans.activity_type =1
								GROUP BY bpas_gl_trans.biller_id");
								
	                            $data = $query->row();
	                            $amount1 = $data->amount;
								$amount_income = $amount1 *(-1);
								if(($index+1)==1){
									?>
									<td class="third_lv">b2</td>
									<td style="text-align:center !important;"><?= $amount_income ? $amount_income:0 ;?></td>
								<?php 
									$total_business_array_before[] = array(
										'id' => $bill_id,
										'amount' => $amount_income,
									);
								}else{?>
									<td class="right third_lv"><?= $amount_income ? $amount_income:0 ;?></td>
								<?php
									$total_business_array_before[] = array(
										'id' => $bill_id,
										'amount' => $amount_income,
									);
								}
								$total_per_incomeBefore += $amount1*-1;
								$index++;
							}
							
							echo '<td class="right third_lv">'. $total_per_incomeBefore .'</td>';
							?>
						</tr>
						<tr class="hide">
							<?php
							$index1 = 0;
							$total_per_costBefore = 0;
							for($j = 1; $j <= count($new_billers); $j++){
								
								$bill_id = 0;
								if($this->uri->segment(7)){
									$bill_id = $new_billers[$index1]['id'];
								}else{
									$bill_id = $new_billers[$index1]->id;
								}
								
								$query = $this->db->query("SELECT
									sum(bpas_gl_trans.amount) AS amount
								FROM
									bpas_gl_trans
								WHERE
									bpas_gl_trans.biller_id = '" . $bill_id . "'
									AND bpas_gl_trans.tran_date < '$from_date'
									AND bpas_gl_trans.activity_type =2
								GROUP BY bpas_gl_trans.biller_id
								");
								$data2 = $query->row();
								
								$amount_cost = 0;
								$amount2=$data2->amount;
								$amount_cost = $amount2 *(-1);
								
								if(($index1+1)==1){
									
							?>
								<td class="third_lv">c1</td>
								<td class="right"><?= $amount_cost;?></td>
							<?php
								$total_invest_array_before[] = array(
									'id' => $bill_id,
									'amount' => $amount_cost,
								);
							}else{
								?>
								<td class="right"><?= $amount_cost;?></td>
								<?php 									
								$total_invest_array_before[] = array(
									'id' => $bill_id,
									'amount' => $amount_cost,
								);
							}
							$total_per_costBefore += (-1)*$amount2;
							$index1++;
							}
							$total_per_costBefore = $total_per_costBefore;
						
							echo '<td class="right ">'. $total_per_costBefore .'</td>';
						?>
						</tr>
						<tr class="hide">
							<?php
							$in_op = 0;
							$total_per_op = 0;
							for($i = 1; $i <= count($new_billers); $i++){
								$bill_id = 0;
								if($this->uri->segment(7)){
									$bill_id = $new_billers[$in_op]['id'];
								}else{
									$bill_id = $new_billers[$in_op]->id;
								}
								
								$query = $this->db->query("SELECT
									SUM(COALESCE(bpas_gl_trans.amount, 0)) AS amount
								    
								FROM bpas_gl_trans
								WHERE biller_id = '" . $bill_id . "' 
									AND bpas_gl_trans.tran_date < '$from_date'
									AND bpas_gl_trans.activity_type =3
								GROUP BY bpas_gl_trans.biller_id
								");
								$data3 = $query->row();
								$amount3=$data3->amount*(-1);
								if($i==1){
									
								?>
									<td class="third_lv">d1</td>
									<td class="right"><?= $amount3;?></td>
								<?php 
									$total_finance_array_before[] = array(
										'id' => $bill_id,
										'amount' => $amount3,
									);
								}else{
									?>
									<td class="right"><?= $amount3;?></td>
									<?php
									$total_finance_array_before[] = array(
										'id' => $bill_id,
										'amount' => $amount3,
									);
								}
								$total_per_op += -1* $data3->amount;
								$in_op++;
							}
						
							echo '<td class="right">' . $total_per_op .'</td>';
							?>
						</tr>
				</table>
                <div class="table-scroll">
                    <table cellpadding="0" id="export_contain" cellspacing="0" border="0" class="table table-hover table-striped table-condensed">
						<thead>
							<tr class="hide">
								<td colspan="3" style="font-weight: bold;font-size: 18px;"><?= $this->Settings->site_name;?></td>
                    		</tr>
							<tr class="hide">
								<td colspan="3" style="font-weight: bold;font-size: 16px;">Cash Flow Report</td>
                    		</tr>
                    		<tr class="hide">
								<td colspan="3" style="font-weight: bold;font-size: 16px;">Report of <?= ($start ? $this->bpas->hrld($start) : '') . ' To ' . ($end ? $this->bpas->hrld($end) : ''); ?></td>
                    		</tr>
                        <tr>
                            <th style="text-align:left; width:300px;"><?= lang("account_name"); ?></th>
							<?php 
							
							//-------------------
							$new_billers = array();
							foreach ($billers as $b1) {
								if($this->uri->segment(7)){
									$biller_sep = explode('-', $this->uri->segment(7));
									for($i=0; $i < count($biller_sep); $i++){
										if($biller_sep[$i] == $b1->id){
											echo '<th style="text-align:right;">' . $b1->company . '</th>';
											$new_billers[] = array('id' => $b1->id);
										}
										
									}
								}else{
									$new_billers = $billers;
									echo '<th style="text-align:right !important;padding-right:100px;">' . $b1->company . '
									</th>';
								}
								
								$num_col++;
							}
							?>
							<th style="text-align:right !important;"><?= lang("total"); ?></th>
                        </tr>
                        
						
                        <tr class="primary">
                            <th style="text-align:left;">CASH FLOW </th>
                            <?php 
							$new_billers = array();
							foreach ($billers as $b1) {
								
									$new_billers = $billers;
								echo '
								<th style="text-align:center !important;" class="amount_cash">
									<div class="negative">Cash Out</div>
									<div class="positive">Cash In</div>
									
								</th>';
								
								
								$num_col++;
							}
							?>
							<th style="text-align:right !important;"><?= lang('total_in_out');?></th>
                        </tr>
                        <tr>
                            <th style="text-align:left;">BEGINNING OF THE PERIOD</th>
							<?php
							for($i = 0; $i < count($new_billers); $i++){
								$biller_id = 0; $total_b[$i] = 0;
								if($this->uri->segment(7)){
									$biller_id = $new_billers[$i]['id'];
								}else{
									$biller_id = $new_billers[$i]->id;
								}
								$total_amt_inc = 0;$total_cashIn=0;$total_cashOut=0;$total_payment_received=0;
								$total_amt_business=0;$total_amt_invest=0;$total_amt_finance=0;

								foreach ($total_income_array_before as $val) {
									if($biller_id == $val['id']){
										$total_amt_inc += $val['amount'];
									}
								}
								foreach ($total_business_array_before as $val_b) {
									if($biller_id == $val_b['id']){
										$total_amt_business += $val_b['amount'];
									}
								}
								foreach ($total_invest_array_before as $val_in) {
									if($biller_id == $val_in['id']){
										$total_amt_invest += $val_in['amount'];
									}
								}
								foreach ($total_finance_array_before as $val_fi) {
									if($biller_id == $val_fi['id']){
										$total_amt_finance += $val_fi['amount'];
									}
								}
								
								$total_income_beg += $total_amt_inc + $total_amt_business + $total_amt_invest + $total_amt_finance;

								$total_b[$i] += $total_income_beg;
					
								$total_bigining  = $total_amt_inc+$total_amt_business+$total_amt_invest+$total_amt_finance;
								$total_each_ending = $total_bigining >0 ? $this->bpas->formatMoney($total_bigining): '('.$this->bpas->formatMoney($total_bigining*(-1)).')';
								echo '<td class="right" style="border-top:2px solid #000;font-weight:bold;">'.$total_each_ending.'</td>';
							}
							?>
							<?php 
							$total_amount_beginning  = $total_income_beg;
							
							?>
							<th style="text-align:right !important;"><?= $this->bpas->formatMoney($total_amount_beginning);?></th>
                        </tr>
                        <tr>
                            <th class="second_lv" colspan="<?=$num_col?>"><?= lang('business_activity')?></th>	
                        </tr>
					
                        </thead>
					
                        <tbody>
						<?php

							foreach($Incomes->result() as $row){
							?>
							<tr>
							    <?php 
								$index = 0;
								$total_per_income = 0;
								$total_per_received =0;
								$total_received =0;
								for($i = 1; $i <= count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$index]['id'];
									}else{
										$bill_id = $new_billers[$index]->id;
									}
								  	$query = $this->db->query("SELECT
		                                SUM(COALESCE(bpas_payments.amount, 0)) AS amount
		                            FROM
		                                bpas_payments
		                            Left join bpas_sales ON bpas_sales.id = bpas_payments.sale_id
		                            
		                            WHERE
		                                
		                                bpas_sales.biller_id = '" . $bill_id . "'
										AND DATE_FORMAT(bpas_payments.date, '%Y-%m-%d %H:%m:%s') BETWEEN '$from_date' AND '$to_date'
									");
									
		                            $data = $query->row();
										$amount_income = $data->amount;
										if($amount_income < 0){
											$amount_income = '( '.$this->bpas->formatMoney(abs($data->amount)).' )';
										}else{
											$amount_income = $this->bpas->formatMoney(abs($data->amount));
										}
										$business_amount_positive = $data->amount;
										if(($index+1)==1){
											?>
												<td class="third_lv">
													<?= lang('payment_received');?>
												</td>
												<td style="text-align:center !important;" class="amount_cash">
													<span class="negative">
														0.00
													</span>
													<span class="positive">
														<?= $this->bpas->formatMoney($business_amount_positive);?>
													</span>
												</td>
											<?php 
											$total_received_array[] = array(
												'id' => $bill_id,
												'amount' => $data->amount,
												'payment_received' => $data->amount,
											);
										}else{?>
											
											<td class="right third_lv">
												<span class="negative">
													0.00
												</span>
												<span class="positive">
													<?= $this->bpas->formatMoney($business_amount_positive);?>
												</span>
											
											</td>
											
											<?php
											$total_received_array[] = array(
												'id' => $bill_id,
												'amount' => $data->amount,
												'payment_received' => $data->amount,
											);
										}
										$total_per_received += $data->amount;
										$index++;
								}
									if($total_per_received < 0){
										$total_per_received = '( ' . $this->bpas->formatMoney(abs($total_per_received)) . ' )';
									}else{
										$total_per_received = $this->bpas->formatMoney(abs($total_per_received));
									}
									echo '<td class="right third_lv">'. $total_per_received .'</td>';
										?>
							</tr>
							<?php
							}

							foreach($business->result() as $row){
							?>
							<tr>
							    <?php 
								$index = 0;
								$total_per_income = 0;
								for($i = 1; $i <= count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$index]['id'];
									}else{
										$bill_id = $new_billers[$index]->id;
									}
								  $query = $this->db->query("SELECT
		                                SUM(COALESCE(bpas_gl_trans.amount, 0)) AS amount,
		                                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END ) AS cashOut,
									    SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) AS cashIn
		                            FROM
		                                bpas_gl_trans
		                            WHERE
		                                 account_code = '" . $row->account_code . "'
										AND bpas_gl_trans.biller_id = '" . $bill_id . "'
										AND bpas_gl_trans.tran_date BETWEEN '$from_date' AND '$to_date'
										AND bpas_gl_trans.activity_type =1
									");
									
		                            $data = $query->row();
										$amount_income = $data->amount;
										if($amount_income > 0){
											$amount_income = '( '.$this->bpas->formatMoney(abs($data->amount)).' )';
										}else{
											$amount_income = $this->bpas->formatMoney(abs($data->amount));
										}
										$business_amount_positive = $this->bpas->formatMoney($data->cashIn *-1);
										$business_amount_negative = ($data->cashOut !=0) ? '('.$this->bpas->formatMoney($data->cashOut).')': (($data->cashOut !=0)?$data->cashOut:0);

										if(($index+1)==1){
											?>
												<td class="third_lv">
													<?php echo $row->accountname;?>
												</td>
												<td style="text-align:center !important;" class="amount_cash">
													<span class="negative">
														<span><?= $business_amount_negative;?></span>
													</span>
													<span class="positive">
														<?= $business_amount_positive;?>
													</span>
												</td>
											<?php 
											$total_income_array[] = array(
												'id' => $bill_id,
												'amount' => $data->amount,
												'cashIn' => $data->cashIn,
												'cashOut' => $data->cashOut,
											);
										}else{?>
											
											<td class="right third_lv">
												<span class="negative">
														<span><?= $business_amount_negative;?></span>
													</span>
													<span class="positive">
														<?= $business_amount_positive;?>
													</span>
											
											</td>
											
											<?php
											$total_income_array[] = array(
												'id' => $bill_id,
												'amount' => $data->amount,
												'cashIn' => $data->cashIn,
												'cashOut' => $data->cashOut,
											);
										}
										$total_per_income += (-1)*$data->amount;
										$index++;
								}
									if($total_per_income < 0){
										$total_per_income = '( ' . $this->bpas->formatMoney(abs($total_per_income)) . ' )';
									}else{
										$total_per_income = $this->bpas->formatMoney(abs($total_per_income));
									}
									echo '<td class="right third_lv">'. $total_per_income .'</td>';
										?>
							</tr>
							<?php
							}

							?>
							<tr>
								<td style="font-weight:bold;" class="third_lv"><?= lang("total").' '.lang('business_activity'); ?></td>
									<?php
									for($i = 0; $i < count($new_billers); $i++){
										$biller_id = 0; $tpl[$i] = 0;
										if($this->uri->segment(7)){
											$biller_id = $new_billers[$i]['id'];
										}else{
											$biller_id = $new_billers[$i]->id;
										}
										$total_amt_inc = 0;$total_cashIn=0;$total_cashOut=0;$total_payment_received=0;
										foreach ($total_income_array as $val) {
											if($biller_id == $val['id']){
												$total_amt_inc += -1 * $val['amount'];
												$total_cashIn  += (-1)* $val['cashIn'];
												$total_cashOut += (-1)* $val['cashOut'];
											}
										}
										foreach ($total_received_array as $val) {
											if($biller_id == $val['id']){
												$total_payment_received += $val['payment_received'];
											}
										}
										

										$total_income += $total_amt_inc;
										$total_received += $total_payment_received;

										$sum_total_income[] = array(
											'biller_id' => $biller_id,
											'amount' => $total_amt_inc
										);
									
										$tpl[$i] +=$total_amt_inc + $total_payment_received;

										$total_amt_inc = $total_amt_inc;

										//$this->bpas->formatMoney(abs($total_amt_inc));
										if($total_amt_inc < 0){
											$total_amt_inc = '( '.$this->bpas->formatMoney(abs($total_amt_inc)).' )';
										}else{
											$total_amt_inc = $this->bpas->formatMoney(abs($total_amt_inc));
										}
										$total_cashIn  = $total_payment_received+$total_cashIn;
										echo '
										<td class="right" style="font-weight:bold;border-top:2px solid #000">
											<span class="negative">
												
												'.(($total_cashOut < 0)? ('('.$this->bpas->formatMoney((-1)*$total_cashOut).')'):$total_cashOut).'
											
											</span>
											<span class="positive">
												'.$this->bpas->formatMoney($total_cashIn).'
											</span>
										</td>';
										//' .$total_amt_inc. '
									}
									?>
									<?php 
									$total_income_display = '';
									$total_income  = $total_income + $total_received;
									if($total_income < 0){
										$total_income_display = '( '.$this->bpas->formatMoney($total_income).' )';
									}else{
										$total_income_display = $this->bpas->formatMoney($total_income);
									}
									?>
								<td class="right" style="font-weight:bold;border-top:2px solid #000;">
									<?php echo $total_income_display;?>
								</td>
							</tr>
							<tr>
	                            <th class="second_lv" colspan="<?=$num_col?>">INVESTING ACTIVITY</th>	
	                        </tr>
	                        <?php
							$total_cost = 0;
                            $totalBeforeAyear_cost = 0;
							foreach($investing->result() as $rowcost){
							//$this->bpas->print_arrays($rowcost);
							?>
							<tr>
								
								<?php
								$index1 = 0;
								$total_per_cost = 0;
								for($j = 1; $j <= count($new_billers); $j++){
									
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$index1]['id'];
									}else{
										$bill_id = $new_billers[$index1]->id;
									}
									
									$query = $this->db->query("SELECT
										sum(bpas_gl_trans.amount) AS amount,
										SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END ) AS cashOut,
									    SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) AS cashIn
									FROM
										bpas_gl_trans
									WHERE
										account_code = '" . $rowcost->account_code . "'
										AND bpas_gl_trans.biller_id = '" . $bill_id . "'
										AND bpas_gl_trans.tran_date BETWEEN '$from_date' AND '$to_date'
										AND bpas_gl_trans.activity_type =2
									");
									$data2 = $query->row();
								//	$totalBeforeAyear_cost += $data2->amount;
									
									$amount_cost = 0;
									if($data2->amount > 0){
										$amount_cost = '( '.$this->bpas->formatMoney($data2->amount).' )';
									}else{
										$amount_cost = $this->bpas->formatMoney($data2->amount);
									}
									
									$business_amount_positive2 = $this->bpas->formatMoney($data2->cashIn * (-1));
									$business_amount_negative2 = ($data2->cashOut != 0) ? '('.$data2->cashOut.')' : $this->bpas->formatMoney($data2->cashOut);

									if(($index1+1)==1){
										$total_cost_array[] = array(
											'id' => $bill_id,
											'amount' => $data2->amount,
											'cashIn' => $data2->cashIn,
											'cashOut' => $data2->cashOut,
										);
								?>
								
								<td class="third_lv">
						
									<?php echo $rowcost->accountname;?>
								
								</td>
								
								<td class="right">
									<span class="negative">
										<span><?= $business_amount_negative2;?></span>
									</span>
									<span class="positive">
										<?= $business_amount_positive2;?>
									</span>
									
								</td>
								
								<?php
								}else{
									?>
									<td class="right">
										<span class="negative">
											<span><?= $business_amount_negative2;?></span>
										</span>
										<span class="positive">
											<?= $business_amount_positive2;?>
										</span>
									</td>
									<?php 									
									$total_cost_array[] = array(
										'id' => $bill_id,
										'amount' => $data2->amount,
										'cashIn' => $data2->cashIn,
										'cashOut' => $data2->cashOut,
									);
								}
								$total_per_cost += (-1)*$data2->amount;
								$index1++;
								}
							
								if($total_per_cost < 0){
									$total_per_cost = '( '.$this->bpas->formatMoney(abs($total_per_cost)).' )';
								}else{
									$total_per_cost = $this->bpas->formatMoney(abs($total_per_cost));
								}
								echo '<td class="right ">'. $total_per_cost .'</td>';
								echo '</tr>';
							}
							?>
							<tr>
								<td style="font-weight:bold;" class="third_lv"><?= lang("total").' '.lang("investing_activity"); ?></td>
								<?php
								for($in = 0; $in < count($new_billers); $in++){
									$in_bill_id = 0;
									if($this->uri->segment(7)){
										$in_bill_id = $new_billers[$in]['id'];
									}else{
										$in_bill_id = $new_billers[$in]->id;
									}
									$total_amt_cost = 0;$total_cashIn=0;$total_cashOut=0;
									foreach ($total_cost_array as $val) {
										if($in_bill_id == $val['id']){
											$total_amt_cost += (-1) * $val['amount'];
											$total_cashIn 	+= (-1) * $val['cashIn'];
											$total_cashOut 	+= (-1) * $val['cashOut'];
										}
									}
									$total_cost += $this->bpas->formatDecimal($total_amt_cost);
									$tpl[$in]   += $total_amt_cost;
									$sum_total_cost[] = array(
										'biller_id' => $in_bill_id,
										'amount' => $total_amt_cost
									);
									
									if($total_amt_cost < 0){
										$total_amt_cost = '( '.$this->bpas->formatMoney(abs($total_amt_cost)).' )';
									}else{
										$total_amt_cost = $this->bpas->formatMoney(abs($total_amt_cost));
									}

									echo '<td class="right" style="font-weight:bold;border-top:2px solid #000">
											<span class="negative">
												'.(($total_cashOut < 0)? ('('.$this->bpas->formatMoney((-1)*$total_cashOut).')'):$total_cashOut).'
											</span>
											<span class="positive">
												'.$this->bpas->formatMoney($total_cashIn).'
											</span>
									</td>';
									//		' . $total_amt_cost . '
								}
								$total_cost_display = '';
								if($total_cost < 0){
									$total_cost_display = '( '.$this->bpas->formatMoney(abs($total_cost)).' )';
								}else{
									$total_cost_display = $this->bpas->formatMoney(abs($total_cost));
								}
								?>
								
								<td class="right" style="font-weight:bold;border-top:2px solid #000;">
									<?php echo $total_cost_display; ?>
								</td>
							</tr>
	                        <tr>
	                            <th class="second_lv" colspan="<?=$num_col?>">FINANCING ACTIVITY</th>	
	                        </tr>
	                   		<?php
							$total_expense = 0;
                            $totalBeforeAyear_expense = 0;
							foreach($financing->result() as $row){
							$total_expense += -1*$row->amount;
							?>
							<tr>
								<?php
								$in_op = 0;
								$total_per_op = 0;
								for($i = 1; $i <= count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$in_op]['id'];
									}else{
										$bill_id = $new_billers[$in_op]->id;
									}
									
									$query = $this->db->query("SELECT
										SUM(COALESCE(bpas_gl_trans.amount, 0)) AS amount,
										SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) AS cashOut,
										SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END ) AS cashIn
									    
									FROM
										bpas_gl_trans
									WHERE
										account_code = '" . $row->account_code . "'
										AND biller_id = '" . $bill_id . "' 
										AND bpas_gl_trans.tran_date BETWEEN '$from_date' AND '$to_date'
										AND bpas_gl_trans.activity_type =3
									");
									$data3 = $query->row();
									$totalBeforeAyear_expense += $data3->amount;
									$amount_op = 0;
									if($data3->amount > 0){
										$amount_op = '( '.$this->bpas->formatMoney(abs($data3->amount)).' )';
									}else{
										$amount_op = $this->bpas->formatMoney(abs($data3->amount));
									}
									
									$business_cashIn3 = $this->bpas->formatMoney($data3->cashIn *(-1));
									$business_cashOut3 = $data3->cashOut;

									if($i==1){
										$total_op_array[] = array(
											'id' => $bill_id,
											'amount' => $data3->amount,
											'cashIn' => $data3->cashIn,
											'cashOut' => $data3->cashOut,
										);
									?>
										<td class="third_lv">
											<?php echo $row->accountname;?>
										</td>
										
										<td class="right">
											<span class="negative">
												<span><?= ($business_cashOut3 !=0)? '('.$business_cashOut3.')':0;?></span>
											</span>
											<span class="positive">
												<?= $business_cashIn3;?>
											</span>
											
										</td>
								
								<?php }else{
										$total_op_array[] = array(
											'id' => $bill_id,
											'amount' => $data3->amount,
											'cashIn' => $data3->cashIn,
											'cashOut' => $data3->cashOut,
										);?>
										<td class="right">
											<span class="negative">
												<span><?= ($business_cashOut3 !=0)?$business_cashOut3:0;?></span>
											</span>
											<span class="positive">
												<?= $business_cashIn3;?>
											</span>
										</td>
										<?php
									}
									$total_per_op += -1* $data3->amount;
									$in_op++;
								}
								if($total_per_op < 0){
									$total_per_op = '( '.$this->bpas->formatMoney(abs($total_per_op)).' )';
								}else{
									$total_per_op = $this->bpas->formatMoney(abs($total_per_op));
								}
								echo '<td class="right">' . $total_per_op .'</td>';
								?>
							</tr>
							<?php
								}
							?>
							<tr>
								<td style="font-weight:bold" class="third_lv"><?= lang("total").' '.lang('financing_activity'); ?></td>
								<?php
								$total_each_finance ='';
								for($i = 0; $i < count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$i]['id'];
									}else{
										$bill_id = $new_billers[$i]->id;
									}
									$total_amt_op = 0; $total_fiance=0;$total_cashIn=0;$total_cashOut=0;
									foreach ($total_op_array as $val) {
										if($bill_id == $val['id']){
											$total_amt_op += (-1)* $val['amount'];
											$total_cashIn += (-1)* $val['cashIn'];
											$total_cashOut += (-1)* $val['cashOut'];
										}
									}
									
									$sum_total_op[] = array(
										'biller_id' => $bill_id,
										'amount' => $total_amt_op
									);
									
									if($total_amt_op < 0){
										$total_fiance = $total_amt_op;
										$total_amt_op = '( '.$this->bpas->formatMoney(abs($total_amt_op)).' )';
									}else{
										$total_fiance = $total_amt_op;
										$total_amt_op = $this->bpas->formatMoney(abs($total_amt_op));
									}
									$tpl[$i] += $total_fiance;
									$total_each_finance = $total_fiance;
									echo '<td class="right" style="border-top:2px solid #000;font-weight:bold">
											<span class="negative">
												'.(($total_cashOut < 0)? ('('.(-1)*$total_cashOut.')'):$total_cashOut).'
											</span>
											<span class="positive">
												'.$total_cashIn.'
											</span>
									</td>';
									//$total_amt_op
								}
								//echo $total_each_finance.'--';
								$total_each_finance = $total_each_finance;
								$total_expense_display = '';
								if($total_expense < 0){
									$total_expense_display = '( '.$this->bpas->formatMoney(abs($total_expense)).' )';
								}else{
									$total_expense_display = $this->bpas->formatMoney(abs($total_expense));
								}
								?>

								<td class="right" style="border-top:2px solid #000;font-weight:bold">
									<?php echo $total_expense_display;?>
								</td>
							</tr>
							<tr class="active">                            
								<th><?= lang("net_cashflow"); ?></th>
								<?php
								for($i = 0; $i < count($new_billers); $i++){
									$bill_id = 0;
									if($this->uri->segment(7)){
										$bill_id = $new_billers[$i]['id'];
									}else{
										$bill_id = $new_billers[$i]->id;
									}

									if($tpl[$i] < 0){
										$total_period = '( '.$this->bpas->formatMoney((-1)* $tpl[$i]).' )';
									}else{
										$total_period = $this->bpas->formatMoney($tpl[$i]);
									}

									echo '
									<td class="right" style="border-top:2px solid #000;font-weight:bold">
										'.$total_period.'</td>';
								}
								?>
								
								<?php 
								$total_profit_per = $total_income + $total_cost + $total_expense;

								$total_profit_loss_display = '';
								if($total_profit_per < 0){
									$total_profit_loss_display = '( '.$this->bpas->formatMoney(abs($total_profit_per)).' )';
								}else{
									$total_profit_loss_display = $this->bpas->formatMoney(abs($total_profit_per));
								}
								?>
								<th class="right" style="border-top:2px solid #000;font-weight:bold">
									<?php echo $total_profit_loss_display;?></th>
								<?php 
								?>
							</tr>
							<tr class="active">                            
								<th>CASH AT THE END OF THE PERIOD :</th>
								<?php
								for($i = 0; $i < count($new_billers); $i++){
									$biller_id = 0; $total_b[$i] = 0;
									if($this->uri->segment(7)){
										$biller_id = $new_billers[$i]['id'];
									}else{
										$biller_id = $new_billers[$i]->id;
									}
									$total_amt_inc = 0;$total_cashIn=0;$total_cashOut=0;$total_payment_received=0;
									$total_amt_business=0;$total_amt_invest=0;$total_amt_finance=0;

									foreach ($total_income_array_before as $val) {
										if($biller_id == $val['id']){
											$total_amt_inc += $val['amount'];
										}
									}
									foreach ($total_business_array_before as $val_b) {
										if($biller_id == $val_b['id']){
											$total_amt_business += $val_b['amount'];
										}
									}
									foreach ($total_invest_array_before as $val_in) {
										if($biller_id == $val_in['id']){
											$total_amt_invest += $val_in['amount'];
										}
									}
									foreach ($total_finance_array_before as $val_fi) {
										if($biller_id == $val_fi['id']){
											$total_amt_finance += $val_fi['amount'];
										}
									}
									
									$total_bigining  = $total_amt_inc+$total_amt_business+$total_amt_invest+$total_amt_finance+$tpl[$i];
									$total_each_ending = $total_bigining >0 ? $this->bpas->formatMoney($total_bigining): '('.$this->bpas->formatMoney($total_bigining*(-1)).')';
									echo '<td class="right" style="border-top:2px solid #000;font-weight:bold;">'.$total_each_ending.'</td>';
								}
								?>
								
								<?php 
								//---------------correctly------------
								$total_profit_per = $total_income + $total_cost + $total_expense + $total_amount_beginning;

								$total_profit_loss_display = '';
								if($total_profit_per < 0){
									$total_profit_loss_display = '( '.$this->bpas->formatMoney($total_profit_per).' )';
								}else{
									$total_profit_loss_display = $this->bpas->formatMoney(abs($total_profit_per));
								}
								?>
								<th class="right" style="border-top:2px solid #000;font-weight:bold">
									<?php echo $total_profit_loss_display;?></th>
								<?php 
								?>
							</tr>
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?php echo form_close(); ?>
<?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$("#biller-filter").on('click', function(event){
			event.preventDefault();
			var hasCheck = false;
			biller_ids = '';
			$.each($("input[name='biller_checkbox[]']:checked"), function(){
				hasCheck = true;
				biller_ids += $(this).val() + '-';
			});
			var billers = removeSymbolLastString(biller_ids, '-');
			if(hasCheck == true){
				var encodedName = encodeURIComponent(billers);
				var url = "<?php echo admin_url('reports/cashflow/'.$start.'/'.$end.'/0/0') ?>" + '/' + encodeURIComponent(billers);
				window.location.href = "<?=admin_url('reports/cashflow/'. $start .'/'.$end.'/0/0')?>" + '/' + encodedName;
			}
			
			if(hasCheck == false){
				bootbox.alert('Please select project first!');
				return false;
			}
			return false;
		});
		
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/cashflow/'. $start .'/'.$end.'/pdf/0/'.$biller_id)?>";
            return false;
        });
		
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/cashflow/'. $start .'/'.$end.'/0/xls/'.$biller_id)?>";
            return false;
        });
		
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
    function exportF(elem) {
	  var table = document.getElementById("export_contain");
	  var html = table.outerHTML;
	  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
	  elem.setAttribute("href", url);
	  elem.setAttribute("download", "Cash Flow Report.xls"); // Choose the file name
	  return false;
	}
	function removeSymbolLastString(string, symbol = ','){
		var strVal = $.trim(string);
		var lastChar = strVal.slice(-1);
		if (lastChar == symbol) {
			strVal = strVal.slice(0, -1);
		}
		return strVal;
	}
</script>
