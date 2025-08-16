
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('income_statement_by_month'); ?>
			<?php
				if ($this->input->post('year')) {
					echo " ( " . $this->input->post('year') ." )";
				}else{
					echo " ( " . date("Y") ." )";
				}
            ?>
		</h2>
		
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

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("reports/income_statement_by_month"); ?>
					
                    <div class="row">
						
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller[]', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control biller" id="biller" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						<?php if($Settings->project == 1){ ?>
							<div class="col-md-3 project">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project-multi">
										<?php
										$mpj[''] = array(); 
										if(isset($multi_projects) && $multi_projects){
											foreach ($multi_projects as $multi_project) {
												$mpj[$multi_project->id] = $multi_project->name;
											}
										}
										
										echo form_dropdown('project_multi[]', $mpj, (isset($_POST['project_multi']) ? $_POST['project_multi'] : $Settings->project_id), 'id="project_multi" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" multiple');
										?>
									</div>	
								</div>
							 </div>
						<?php } ?>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("year", "year"); ?>
                                <?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : date("Y")), 'class="form-control year" id="year"'); ?>
                            </div>
                        </div>
						
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="sub_account"><?= lang("sub_account"); ?></label>
                                <?php
                                $sub_acc["no"] = lang('no');
								$sub_acc["yes"] = lang('yes');
                                echo form_dropdown('sub_account', $sub_acc, (isset($_POST['sub_account']) ? $_POST['sub_account'] : ""), 'class="form-control" id="sub_account" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sub_account") . '"');
                                ?>
                            </div>
                        </div>

                    </div>
					
                    <div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
						</div>
                    </div>
					
                    <?php echo form_close(); ?>

                </div>
				<div class="clearfix"></div>
				<table class="print_only" style="width:100%; margin-bottom: 10px">
					<?php
						$print_filter = "";
						$p = 1;
						$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
						$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("printing_date").": ".$this->bpas->hrsd(date("Y-m-d"))."</td></tr>";
					?>
					<tr>
						<th colspan="2" class="text-center" style="font-size:16px"><?= $this->Settings->site_name ?></th>
					</tr>
					<tr>
						<th colspan="2" class="text-center"><u><?= lang('income_statement_by_month'); ?></u></th>
					</tr>
					<?= $print_filter ?>
				</table>	
				
                <div class="clearfix"></div>

			<?php
					if(isset($_POST['year']) && $_POST['year']){
						$year = $_POST['year'];
					}else{
						$year = date("Y");
					}

					if(isset($_POST['biller']) && $_POST['biller']){
						$u = 0;
						foreach($_POST['biller'] as $biller){
							if($u==0){
								$u = 1;
								$billers = $biller;
							}else{
								$billers .= "a".$biller;
							}
							
						}
					}else{
						$billers = 'x';
					}
			

					if(isset($_POST['project_multi']) && $_POST['project_multi']){
						$u = 0;
						foreach($_POST['project_multi'] as $project){
							if($u==0){
								$u = 1;
								$projects = $project;
							}else{
								$projects .= "a".$project;
							}
						}
					}else{
						$projects = 'x';
					}


					if($year == date('Y')){
						$last_month = date('n');
					}else{
						$last_month = 12;
					}
					
					$array_months = array(0 => ($year - 1),1 => lang('jan'), 2 => lang('feb'), 3 => lang('mar'), 4 => lang('apr'), 5 => lang('may'), 6 => lang('jun'), 7 => lang('jul'), 8 => lang('aug'), 9 => lang('sep'), 10 => lang('oct'), 11 => lang('nov'), 12 => lang('dec'));
					$months = array();
					for($i=0; $i <= $last_month; $i++){
						$months[$i] = $array_months[$i]; 
					}
					$thead = '';
					$sub_thead = '';
					$rowspan= 2;
					$colspan_main = 2;
					foreach($months as $index => $month ){
						$colspan_main += 1;
						if($index > 0){
							$sub_thead .= '<th>'.lang($month).'</td>';
						}
						
					}
					$sub_thead .= '<th>'.lang('total').'</td>';
					$thead .= '<th colspan="'.($colspan_main - 2).'">'.$year.'</th>';
				?>
				
				
				
				<?php
					function getAccountByParent($parent_code){
						$CI =& get_instance();
						$data = $CI->accounts_model->getAccountByParent($parent_code);
						return $data;
				
					}
					$accTrans = array();
					$accTranMonths = array();

					$getAccTranAmounts = $this->accounts_model->getMonthAccTranAmounts();
					if($getAccTranAmounts){
						foreach($getAccTranAmounts as $getAccTranAmount){
							$accTrans[$getAccTranAmount->account] = ($getAccTranAmount->amount * $getAccTranAmount->nature) + (isset($accTrans[$getAccTranAmount->account])?$accTrans[$getAccTranAmount->account]:0);
							$accTranMonths[$getAccTranAmount->account][$getAccTranAmount->month] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
						}
						
					}

					$getAccLastTranAmounts = $this->accounts_model->getMonthAccTranAmounts(1);
					if($getAccLastTranAmounts){
						foreach($getAccLastTranAmounts as $getAccLastTranAmount){
							$accTranMonths[$getAccLastTranAmount->account][0] = ($getAccLastTranAmount->amount * $getAccLastTranAmount->nature);
						}
					}
					
					
					function formatMoney($number)
					{
						$CI =& get_instance();
						$data = $CI->bpas->formatMoney($number);
						return $data;
					}
					
					

					function getSubAccount($subAccounts,$accTrans,$accTranMonths, $months, $year, $last_month, $billers, $projects){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_months = array();
						foreach($subAccounts as $subAccount){
							$tmp_td = '';
							$space ='&nbsp;';
							$split = explode('/',$subAccount->lineage);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							$amount = (isset($accTrans[$subAccount->accountcode])?$accTrans[$subAccount->accountcode]:0);
							$last_amount = (isset($accTranMonths[$subAccount->accountcode][0])?$accTranMonths[$subAccount->accountcode][0]:0);
							$SubSubAccounts = getAccountByParent($subAccount->accountcode);
							if($SubSubAccounts){
								$SubSubAccount = getSubSubAccount($SubSubAccounts,$accTrans,$accTranMonths, $months, $year, $last_month, $billers, $projects);
								$tmp_td = $SubSubAccount['sub_td'];
								$amount += $SubSubAccount['total_amount'];
								$last_amount = $last_amount + (isset($SubSubAccount['total_amount_months'][0])?$SubSubAccount['total_amount_months'][0]:0);
							}else{
								$SubSubAccount = array();
							}

							foreach($months as $month => $value){
								$amount_month = (isset($accTranMonths[$subAccount->accountcode][$month])?$accTranMonths[$subAccount->accountcode][$month]:0);
								$total_amount_months[$month] = $amount_month + (isset($total_amount_months[$month])?$total_amount_months[$month]:0) + (isset($SubSubAccount['total_amount_months'][$month])?$SubSubAccount['total_amount_months'][$month]:0);
							}

							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount != 0 || $last_amount != 0){
									if($amount < 0){
										$v_amount = '( '.formatMoney(abs($amount)).' )';
									}else{
										$v_amount = formatMoney($amount);
									}
									$sub_td_month = '';								
									foreach($months as $month => $value){
										$amount_month = (isset($accTranMonths[$subAccount->accountcode][$month])?$accTranMonths[$subAccount->accountcode][$month]:0) + (isset($SubSubAccount['total_amount_months'][$month])?$SubSubAccount['total_amount_months'][$month]:0);
										if($amount_month < 0){
											$v_amount_month = '( '.formatMoney(abs($amount_month)).' )';
										}else{
											$v_amount_month = formatMoney($amount_month);
										}
										if($month==0){
											$start_date = date("Y-m-d", strtotime(($year-1).'-01-01'));
											$end_date = date("Y-m-t", strtotime(($year-1).'-12-01'));
										}else{
											$start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
											$end_date = date("Y-m-t", strtotime($start_date));
										}
										$sub_td_month .= '<td class="accounting_link" id="'.$subAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount_month.'</td>';
									}
									$start_date = $year.'-01-01';
									$end_date = date("Y-m-t", strtotime($year.'-'.$last_month.'-01'));	
									$sub_td .= '<tr>
												<td>'.$space.$subAccount->accountcode.' - '.$subAccount->name.'</td>
												'.$sub_td_month.'
												<td class="accounting_link" id="'.$subAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount.'</td>
											</tr>';
								}
							}
							
							$sub_td .=	$tmp_td;		
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_months' => $total_amount_months
								);
						return $data;
					}	
					
					function getSubSubAccount($SubSubAccounts,$accTrans,$accTranMonths, $months, $year, $last_month, $billers, $projects){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_months = array();
						foreach($SubSubAccounts as $SubSubAccount){
							$tmp_td = '';
							$space ='&nbsp;';
							$split = explode('/',$SubSubAccount->lineage);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							
							$amount = (isset($accTrans[$SubSubAccount->accountcode])?$accTrans[$SubSubAccount->accountcode]:0);
							$last_amount = (isset($accTranMonths[$SubSubAccount->accountcode][0])?$accTranMonths[$SubSubAccount->accountcode][0]:0);
							$subAccounts = getAccountByParent($SubSubAccount->accountcode);
							if($subAccounts){
								$subAccount = getSubAccount($subAccounts,$accTrans,$accTranMonths, $months, $year, $last_month, $billers, $projects);
								$tmp_td = $subAccount['sub_td'];
								$amount += $subAccount['total_amount'];
								$last_amount = $last_amount + (isset($subAccount['total_amount_months'][0])?$subAccount['total_amount_months'][0]:0);
							}else{
								$subAccount = array();
							}
					
							foreach($months as $month => $value){
								$amount_month = (isset($accTranMonths[$SubSubAccount->accountcode][$month])?$accTranMonths[$SubSubAccount->accountcode][$month]:0);
								$total_amount_months[$month] = $amount_month + (isset($total_amount_months[$month])?$total_amount_months[$month]:0) + (isset($subAccount['total_amount_months'][$month])?$subAccount['total_amount_months'][$month]:0);
							}
						
							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount != 0 || $last_amount != 0){
									if($amount < 0){
										$v_amount = '( '.formatMoney(abs($amount)).' )';
									}else{
										$v_amount = formatMoney($amount);
									}
							
									$sub_td_month = '';										
									foreach($months as $month => $value){
										$amount_month = (isset($accTranMonths[$SubSubAccount->accountcode][$month])?$accTranMonths[$SubSubAccount->accountcode][$month]:0)  + (isset($subAccount['total_amount_months'][$month])?$subAccount['total_amount_months'][$month]:0);
										if($amount_month < 0){
											$v_amount_month = '( '.formatMoney(abs($amount_month)).' )';
										}else{
											$v_amount_month = formatMoney($amount_month);
										}


										if($month==0){
											$start_date = date("Y-m-d", strtotime(($year-1).'-01-01'));
											$end_date = date("Y-m-t", strtotime(($year-1).'-12-01'));
										}else{
											$start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
											$end_date = date("Y-m-t", strtotime($start_date));
										}
			
										$sub_td_month .= '<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount_month.'</td>';
									}
									$start_date = $year.'-01-01';
									$end_date = date("Y-m-t", strtotime($year.'-'.$last_month.'-01'));
									$sub_td .= '<tr>
													<td>'.$space.$SubSubAccount->accountcode.' - '.$SubSubAccount->name.'</td>
													'.$sub_td_month.'
													<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount.'</td>
												</tr>';
								}
							}
							$sub_td .= $tmp_td;				
						}
						$data = array(
										'sub_td' => $sub_td,
										'total_amount' => $total_amount,
										'total_amount_months' => $total_amount_months
									);
						return $data;
					}

					
				
					$tbody = '';
					$gross_profit = 0;
					$net_profit = 0;
					$gross_profit_months = array();
					$net_profit_months = array();

					foreach($income_statements as $income_statement){
						$sections = $this->accounts_model->getAccountSectionsByCode(array($income_statement));	
						if($sections){
							foreach($sections as $section){
								$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left' colspan='".$colspan_main."'><span>".$section->sectionname."</span></td></tr>";
								$mainAccounts = $this->accounts_model->getMainAccountBySection($section->sectionid);
								if($mainAccounts){
									$space ='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									foreach($mainAccounts as $mainAccount){
										$subAccounts = getAccountByParent($mainAccount->accountcode);			
										$amount = (isset($accTrans[$mainAccount->accountcode])?$accTrans[$mainAccount->accountcode]:0);
										$last_amount = (isset($accTranMonths[$mainAccount->accountcode][0])?$accTranMonths[$mainAccount->accountcode][0]:0);
										$tmp_td = '';
										if($subAccounts){
											$sub_acc = getSubAccount($subAccounts,$accTrans,$accTranMonths, $months, $year, $last_month, $billers, $projects);
											$tmp_td = $sub_acc['sub_td'];
											$amount += $sub_acc['total_amount'];
											$last_amount = $last_amount + (isset($sub_acc['total_amount_months'][0])?$sub_acc['total_amount_months'][0]:0);
										}else{
											$sub_acc = array();
										}
										
										if($amount != 0 || $last_amount != 0){
											if($amount < 0){
												$v_amount = '( '.formatMoney(abs($amount)).' )';
											}else{
												$v_amount = formatMoney($amount);
											}
											
											$sub_td_month = '';										
											foreach($months as $month => $value){
												$amount_month = (isset($accTranMonths[$mainAccount->accountcode][$month])?$accTranMonths[$mainAccount->accountcode][$month]:0);
												$amount_month = $amount_month + (isset($sub_acc['total_amount_months'][$month])?$sub_acc['total_amount_months'][$month]:0);
												if($amount_month < 0){
													$v_amount_month = '( '.formatMoney(abs($amount_month)).' )';
												}else{
													$v_amount_month = formatMoney($amount_month);
												}

												if($month==0){
													$start_date = date("Y-m-d", strtotime(($year-1).'-01-01'));
													$end_date = date("Y-m-t", strtotime(($year-1).'-12-01'));
												}else{
													$start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
													$end_date = date("Y-m-t", strtotime($start_date));
												}
												

												$sub_td_month .= '<td class="accounting_link" id="'.$mainAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right; font-weight:bold">'.$v_amount_month.'</td>';
												
												if($income_statement == 'RE'){
													$gross_profit_months[$month] = (isset($gross_profit_months[$month])?$gross_profit_months[$month]:0) + $amount_month;
													$net_profit_months[$month] = (isset($net_profit_months[$month])?$net_profit_months[$month]:0) + $amount_month;
												}else if($income_statement == 'OI'){
													$net_profit_months[$month] = (isset($net_profit_months[$month])?$net_profit_months[$month]:0) + $amount_month;
												}else if($income_statement == 'CO'){
													$gross_profit_months[$month] = (isset($gross_profit_months[$month])?$gross_profit_months[$month]:0) - $amount_month;
													$net_profit_months[$month] = (isset($net_profit_months[$month])?$net_profit_months[$month]:0) - $amount_month;
												}else{
													$net_profit_months[$month] = (isset($net_profit_months[$month])?$net_profit_months[$month]:0) - $amount_month;
												}

											}
											$start_date = $year.'-01-01';
											$end_date = date("Y-m-t", strtotime($year.'-'.$last_month.'-01'));
											$tbody .='<tr>
														<td style="font-weight:bold">'.$space.$mainAccount->accountcode.' - '.$mainAccount->accountname.'</td>
														'.$sub_td_month.'
														<td class="accounting_link" id="'.$mainAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right; font-weight:bold">'.$v_amount.'</td>
													</tr>';
										}
										if($income_statement == 'RE'){
											$gross_profit += $amount;	
											$net_profit += $amount;
										}else if($income_statement == 'OI'){
											$net_profit += $amount;
										}else if($income_statement == 'CO'){
											$gross_profit -= $amount;	
											$net_profit -= $amount;
										}else{
											$net_profit -= $amount;	
										}
										$tbody .= $tmp_td;		
									}
								}
							}
						}

						
						if($income_statement=='CO'){
							$td_gross_profit_month = '';
							foreach($months as $month => $value){
								$gross_profit_month = $gross_profit_months[$month];
								if($gross_profit_month < 0){
									$v_td_gross_profit_month = '( '.formatMoney(abs($gross_profit_month)).' )';
								}else{
									$v_td_gross_profit_month = formatMoney($gross_profit_month);
								}
								$td_gross_profit_month .='<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_td_gross_profit_month.'</td>';
							}

							
							if($gross_profit < 0){
								$v_gross_profit = '( '.formatMoney(abs($gross_profit)).' )';
							}else{
								$v_gross_profit = formatMoney($gross_profit);
							}
							$tbody .='<tr>
										<td style="font-weight:bold; color:#4286f4">'.lang('gross_profit_loss').'</td>
										'.$td_gross_profit_month.'
										<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_gross_profit.'</td>
									</tr>';		
						}
					}
					$td_net_profit_month = '';
					foreach($months as $month => $value){
						$net_profit_month = $net_profit_months[$month];
						if($net_profit_month < 0){
							$v_net_profit_month = '( '.formatMoney(abs($net_profit_month)).' )';
						}else{
							$v_net_profit_month = formatMoney($net_profit_month);
						}
						$td_net_profit_month .='<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_net_profit_month.'</td>';
					}

					
					if($net_profit < 0){
						$v_net_profit= '( '.formatMoney(abs($net_profit)).' )';
					}else{
						$v_net_profit = formatMoney($net_profit);
					}
					$tbody .='<tr>
								<td style="font-weight:bold; color:#4286f4">'.lang('net_profit_loss').'</td>
								'.$td_net_profit_month.'
								<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_net_profit.'</td>
							</tr>';
				?>
				
                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;" border="1" class="table table-bordered table-hover table-striped table-condensed accountings-table dataTable">
						<thead>
							<tr>
								<th rowspan="<?= $rowspan ?>"><?= lang('account'); ?></th>
								<th rowspan="<?= $rowspan ?>"><?= ($year-1) ?></th>
								<?= $thead ?>
							</tr>
							<tr class="sub_theader">
								<?= $sub_thead ?>
							</tr>
                        </thead>
						<tbody>
							<?= $tbody ?>
						</tbody>
                    </table>
                </div>
				<table class="print_only" id="table_sinature">
					<tr>
						<td class="text-center" style="width:50%; padding-bottom:100px"><u><?= lang("prepared_by") ?></u></td>
						<td class="text-center" style="width:50%; padding-bottom:100px"><u><?= lang("approved_by") ?></u></td>
					</tr>
				</table>
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
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "income_statement_by_month.xls";
			return true;			
		});
		
		$('#project').live('change', function() {
			var project_id = $(this).val();
			if(project_id != '0'){
				$(".seperate_project").slideUp();
			}else{
				$(".seperate_project").slideDown();
				
			}
		});
		 
		biller();
		$("#biller").change(biller);
		function biller(){
			var biller = $("#biller").val();
			<?php
				$multi_project = '';
				if(isset($_POST['project_multi'])){
					for($i=0; $i<count($_POST['project_multi']); $i++){
						$multi_project .=$_POST['project_multi'][$i].'#';
					}
				}
				
			?>
			var project_multi = '<?= $multi_project ?>';
			$.ajax({
				url : "<?= admin_url("accountings/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project_multi : project_multi },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$(".no-project-multi").html(data.multi_resultl);
						$("#project_multi").select2();
					}
				}
			})
		}
    });
</script>
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
		@page{
			margin: 5mm; 
			size: landscape;
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;        
			zoom:85% !important;
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

