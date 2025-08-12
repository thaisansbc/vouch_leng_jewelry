
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('income_statement'); ?>
			<?php
				if ($this->input->post('start_date')) {
					echo lang('from') .' '.$this->input->post('start_date') ." ". lang('to'). " " . $this->input->post('end_date');
				}else{
					echo lang('from') .' '.date("d/m/Y") ." ".lang('to'). " " . date("d/m/Y");
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

                    <?php echo admin_form_open("reports/income_statement"); ?>
					
                    <div class="row">
						
                        <div class="col-sm-6">
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
						
						<div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date("d/m/Y")), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date("d/m/Y")), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
						
						
						<div class="col-sm-2">
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

						if($this->input->post('start_date')){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("start_date").": ".$this->input->post('start_date')."</td>";
						}
						if($this->input->post('end_date')){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("end_date").": ".$this->input->post('end_date')."</td>";
						}
						$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
						$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("printing_date").": ".$this->bpas->hrsd(date("Y-m-d"))."</td></tr>";
					?>
					<tr>
						<th colspan="2" class="text-center" style="font-size:16px"><?= $this->Settings->site_name ?></th>
					</tr>
					<tr>
						<th colspan="2" class="text-center"><u><?= lang('income_statement'); ?></u></th>
					</tr>
					<?= $print_filter ?>
				</table>	
				
                <div class="clearfix"></div>

			<?php
					if(isset($_POST['start_date'])){
						$start_date = $this->bpas->fsd($_POST['start_date']);
					}else{
						$start_date = date("Y-m-d");
					}
					if(isset($_POST['end_date'])){
						$end_date = $this->bpas->fsd($_POST['end_date']);
					}else{
						$end_date = date("Y-m-d");
					}
					
					$biller_multi = (isset($_POST['biller']) ? $_POST['biller'] : false);
				
					$project_multi = (isset($_POST['project_multi']) ? $_POST['project_multi'] : false);
					$project = (isset($_POST['project'])?$_POST['project']: false);
					$thead = '';
					if($biller_multi && !$project_multi){
						$rowspan = 1;
						$colspan_main = 2;
						for($i=0; $i<count($biller_multi); $i++){
							$colspan_main += 1;
							$biller_detail = $this->site->getCompanyByID($biller_multi[$i]);
							if($biller_detail){
								$thead .= '<th>'.$biller_detail->name.'</td>';
							}else{
								$thead .= '<th>'.lang('no_biller').'</td>';
							}
						}
						$thead.='<th>'.lang('total').'</th>';
					}else if($project_multi){
						$rowspan = 1;
						$colspan_main = 2;
						for($i=0; $i<count($project_multi); $i++){
							$colspan_main += 1;
							$project_detail = $this->site->getProjectByID($project_multi[$i]);
							if($project_detail){
								$thead .= '<th>'.$project_detail->name.'</td>';
							}else{
								$thead .= '<th>'.lang('no_project').'</td>';
							}
						}
						$thead.='<th>'.lang('total').'</th>';
					}else{
						$rowspan = 1;
						$colspan_main = 2;
						$thead .= '<th></th>';
					}
					
				?>
				
				
				
				<?php
					function getAccountByParent($parent_code){
						$CI =& get_instance();
						$data = $CI->accounts_model->getAccountByParent($parent_code);
						return $data;
				
					}
					$accTrans = array();
					$accTranBillers = array();
					$accTranProjects = array();
					
					$getAccTranAmounts = $this->accounts_model->getAccTranAmounts();
					if($getAccTranAmounts){
						foreach($getAccTranAmounts as $getAccTranAmount){
							$accTrans[$getAccTranAmount->account_code] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
						}
						
					}
					
					if($biller_multi && !$project_multi){
						$getAccTranAmounts = $this->accounts_model->getAccTranAmountBillers();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranBillers[$getAccTranAmount->account_code][$getAccTranAmount->biller_id] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}
							
						}
					}else if($project_multi){
						$getAccTranAmounts = $this->accounts_model->getAccTranAmountProjects();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranProjects[$getAccTranAmount->account_code][$getAccTranAmount->project_id] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}
							
						}
					}
					
					
					function formatMoney($number)
					{
						$CI =& get_instance();
						$data = $CI->bpas->formatMoney($number);
						return $data;
					}
					
					

					function getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_billers = array();
						$total_amount_projects = array();
						foreach($subAccounts as $subAccount){
							$tmp_td = '';
							$space ='&nbsp;';
							$split = explode('/',$subAccount->lineage);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							$amount = (isset($accTrans[$subAccount->accountcode])?$accTrans[$subAccount->accountcode]:0);
							$SubSubAccounts = getAccountByParent($subAccount->accountcode);
							if($SubSubAccounts){
								$SubSubAccount = getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date);
								$tmp_td = $SubSubAccount['sub_td'];
								$amount += $SubSubAccount['total_amount'];
							}else{
								$SubSubAccount = array();
							}
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$amount_biller = (isset($accTranBillers[$subAccount->accountcode][$biller_id])?$accTranBillers[$subAccount->accountcode][$biller_id]:0);
									$total_amount_billers[$biller_id] = $amount_biller + (isset($total_amount_billers[$biller_id])?$total_amount_billers[$biller_id]:0) + (isset($SubSubAccount['total_amount_billers'][$biller_id])?$SubSubAccount['total_amount_billers'][$biller_id]:0);
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$amount_project = (isset($accTranProjects[$subAccount->accountcode][$project_id])?$accTranProjects[$subAccount->accountcode][$project_id]:0);
									$total_amount_projects[$project_id] = $amount_project + (isset($total_amount_projects[$project_id])?$total_amount_projects[$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
								}
							}
							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount != 0){
									if($amount < 0){
										$v_amount = '( '.formatMoney(abs($amount)).' )';
									}else{
										$v_amount = formatMoney($amount);
									}
									$sub_td_biller = '';
									$sub_td_project = '';
									if($biller_multi && !$project_multi){									
										foreach($biller_multi as $biller_id){
											$amount_biller = (isset($accTranBillers[$subAccount->accountcode][$biller_id])?$accTranBillers[$subAccount->accountcode][$biller_id]:0) + (isset($SubSubAccount['total_amount_billers'][$biller_id])?$SubSubAccount['total_amount_billers'][$biller_id]:0);
											if($amount_biller < 0){
												$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
											}else{
												$v_amount_biller = formatMoney($amount_biller);
											}
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right">'.$v_amount_biller.'</td>';
										}
									}else if($project_multi){								
										foreach($project_multi as $project_id){
											$amount_project = (isset($accTranProjects[$subAccount->accountcode][$project_id])?$accTranProjects[$subAccount->accountcode][$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
											}else{
												$v_amount_project = formatMoney($amount_project);
											}
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right">'.$v_amount_project.'</td>';
										}
									}
									$sub_td .= '<tr>
												<td>'.$space.$subAccount->accountcode.' - '.$subAccount->accountname.'</td>
												'.$sub_td_biller.'
												'.$sub_td_project.'
												<td class="accounting_link" id="'.$subAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount.'</td>
											</tr>';
								}
							}
							
							$sub_td .=	$tmp_td;		
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_billers' => $total_amount_billers,
								'total_amount_projects' => $total_amount_projects);
						return $data;
					}	
					
					function getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_billers = array();
						$total_amount_projects = array();
						foreach($SubSubAccounts as $SubSubAccount){
							$tmp_td = '';
							$space ='&nbsp;';
							$split = explode('/',$SubSubAccount->lineage);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							
							$amount = (isset($accTrans[$SubSubAccount->accountcode])?$accTrans[$SubSubAccount->accountcode]:0);
							$subAccounts = getAccountByParent($SubSubAccount->accountcode);
							if($subAccounts){
								$subAccount = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date);
								$tmp_td = $subAccount['sub_td'];
								$amount += $subAccount['total_amount'];
							}else{
								$subAccount = array();
							}
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$amount_biller = (isset($accTranBillers[$SubSubAccount->accountcode][$biller_id])?$accTranBillers[$SubSubAccount->accountcode][$biller_id]:0);
									$total_amount_billers[$biller_id] = $amount_biller + (isset($total_amount_billers[$biller_id])?$total_amount_billers[$biller_id]:0) + (isset($subAccount['total_amount_billers'][$biller_id])?$subAccount['total_amount_billers'][$biller_id]:0);
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$amount_project = (isset($accTranProjects[$SubSubAccount->accountcode][$project_id])?$accTranProjects[$SubSubAccount->accountcode][$project_id]:0);
									$total_amount_projects[$project_id] = $amount_project + (isset($total_amount_projects[$project_id])?$total_amount_projects[$project_id]:0) + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);
								}
							}
							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if($amount != 0){
									if($amount < 0){
										$v_amount = '( '.formatMoney(abs($amount)).' )';
									}else{
										$v_amount = formatMoney($amount);
									}
									$sub_td_biller = '';
									$sub_td_project = '';
									if($biller_multi && !$project_multi){								
										foreach($biller_multi as $biller_id){
											$amount_biller = (isset($accTranBillers[$SubSubAccount->accountcode][$biller_id])?$accTranBillers[$SubSubAccount->accountcode][$biller_id]:0)  + (isset($subAccount['total_amount_billers'][$biller_id])?$subAccount['total_amount_billers'][$biller_id]:0);
											if($amount_biller < 0){
												$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
											}else{
												$v_amount_biller = formatMoney($amount_biller);
											}
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right">'.$v_amount_biller.'</td>';
										}
									}else if($project_multi){
										foreach($project_multi as $project_id){
											$amount_project = (isset($accTranProjects[$SubSubAccount->accountcode][$project_id])?$accTranProjects[$SubSubAccount->accountcode][$project_id]:0)  + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
											}else{
												$v_amount_project = formatMoney($amount_project);
											}
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right">'.$v_amount_project.'</td>';
										}
									}
									$sub_td .= '<tr>
													<td>'.$space.$SubSubAccount->accountcode.' - '.$SubSubAccount->name.'</td>
													'.$sub_td_biller.'
													'.$sub_td_project.'
													<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount.'</td>
												</tr>';
								}
							}
							$sub_td .= $tmp_td;				
						}
						$data = array(
								'sub_td' => $sub_td,
								'total_amount' => $total_amount,
								'total_amount_billers' => $total_amount_billers,
								'total_amount_projects' => $total_amount_projects);
						return $data;
					}

					
				
					$tbody = '';
					$gross_profit = 0;
					$net_profit = 0;
					$gross_profit_billers = array();
					$net_profit_billers = array();
					$gross_profit_projects = array();
					$net_profit_projects = array();
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
										$tmp_td = '';
										if($subAccounts){
											$sub_acc = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $start_date, $end_date);
											$tmp_td = $sub_acc['sub_td'];
											$amount += $sub_acc['total_amount'];
										}else{
											$sub_acc = array();
										}
										if($amount != 0){
											if($amount < 0){
												$v_amount = '( '.formatMoney(abs($amount)).' )';
											}else{
												$v_amount = formatMoney($amount);
											}
											$sub_td_biller = '';		
											$sub_td_project = '';
											if($biller_multi && !$project_multi){
													
												foreach($biller_multi as $biller_id){
													$amount_biller = (isset($accTranBillers[$mainAccount->accountcode][$biller_id])?$accTranBillers[$mainAccount->accountcode][$biller_id]:0);
													$amount_biller = $amount_biller + (isset($sub_acc['total_amount_billers'][$biller_id])?$sub_acc['total_amount_billers'][$biller_id]:0);
													if($amount_biller < 0){
														$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
													}else{
														$v_amount_biller = formatMoney($amount_biller);
													}
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller.'</td>';
													
													if($income_statement == 'RE'){
														$gross_profit_billers[$biller_id] = (isset($gross_profit_billers[$biller_id])?$gross_profit_billers[$biller_id]:0) + $amount_biller;
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) + $amount_biller;
													}else if($income_statement == 'OI'){
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) + $amount_biller;
													}else if($income_statement == 'CO'){
														$gross_profit_billers[$biller_id] = (isset($gross_profit_billers[$biller_id])?$gross_profit_billers[$biller_id]:0) - $amount_biller;
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) - $amount_biller;
													}else{
														$net_profit_billers[$biller_id] = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0) - $amount_biller;
													}

												}
											} else if($project_multi){
																						
												foreach($project_multi as $project_id){
													$amount_project = (isset($accTranProjects[$mainAccount->accountcode][$project_id])?$accTranProjects[$mainAccount->accountcode][$project_id]:0);
													$amount_project = $amount_project + (isset($sub_acc['total_amount_projects'][$project_id])?$sub_acc['total_amount_projects'][$project_id]:0);
													if($amount_project < 0){
														$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
													}else{
														$v_amount_project = formatMoney($amount_project);
													}
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project.'</td>';
													
													if($income_statement == 'RE'){
														$gross_profit_projects[$project_id] = (isset($gross_profit_projects[$project_id])?$gross_profit_projects[$project_id]:0) + $amount_project;
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) + $amount_project;
													}else if($income_statement == 'OI'){
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) + $amount_project;
													}else if($income_statement == 'CO'){
														$gross_profit_projects[$project_id] = (isset($gross_profit_projects[$project_id])?$gross_profit_projects[$project_id]:0) - $amount_project;
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) - $amount_project;
													}else{
														$net_profit_projects[$project_id] = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0) - $amount_project;
													}
												}
											}
											$tbody .='<tr>
														<td style="font-weight:bold">'.$space.$mainAccount->accountcode.' - '.$mainAccount->accountname.'</td>
														'.$sub_td_biller.'
														'.$sub_td_project.'
														<td class="accounting_link" id="'.$mainAccount->accountcode.'/'.$start_date.'/'.$end_date.'/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount.'</td>
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
							$td_gross_profit_biller = '';
							$td_gross_profit_project = '';
							if($biller_multi && !$project_multi){
								foreach($biller_multi as $biller_id){
									$gross_profit_biller = (isset($gross_profit_billers[$biller_id])?$gross_profit_billers[$biller_id]:0);
									if($gross_profit_biller < 0){
										$v_gross_profit_biller = '( '.formatMoney(abs($gross_profit_biller)).' )';
									}else{
										$v_gross_profit_biller = formatMoney($gross_profit_biller);
									}
									$td_gross_profit_biller .='<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_gross_profit_biller.'</td>';
								}
							}else if($project_multi){
								foreach($project_multi as $project_id){
									$gross_profit_project = (isset($gross_profit_projects[$project_id])?$gross_profit_projects[$project_id]:0);
									if($gross_profit_project < 0){
										$v_gross_profit_project = '( '.formatMoney(abs($gross_profit_project)).' )';
									}else{
										$v_gross_profit_project = formatMoney($gross_profit_project);
									}
									$td_gross_profit_project .='<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_gross_profit_project.'</td>';
								}
							}
							
							if($gross_profit < 0){
								$v_gross_profit = '( '.formatMoney(abs($gross_profit)).' )';
							}else{
								$v_gross_profit = formatMoney($gross_profit);
							}
							$tbody .='<tr>
										<td style="font-weight:bold; color:#4286f4">'.lang('gross_profit_loss').'</td>
										'.$td_gross_profit_biller.'
										'.$td_gross_profit_project.'
										<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_gross_profit.'</td>
									</tr>';		
						}
					}
					$td_net_profit_biller = '';
					$td_net_profit_project = '';
					if($biller_multi && !$project_multi){
						foreach($biller_multi as $biller_id){
							$net_profit_biller = (isset($net_profit_billers[$biller_id])?$net_profit_billers[$biller_id]:0);
							if($net_profit_biller < 0){
								$v_net_profit_biller = '( '.formatMoney(abs($net_profit_biller)).' )';
							}else{
								$v_net_profit_biller = formatMoney($net_profit_biller);
							}
							$td_net_profit_biller .='<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_net_profit_biller.'</td>';
						}
					}else if($project_multi){
						foreach($project_multi as $project_id){
							$net_profit_project = (isset($net_profit_projects[$project_id])?$net_profit_projects[$project_id]:0);
							if($net_profit_project < 0){
								$v_net_profit_project = '( '.formatMoney(abs($net_profit_project)).' )';
							}else{
								$v_net_profit_project = formatMoney($net_profit_project);
							}
							$td_net_profit_project .='<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_net_profit_project.'</td>';
						}
					}
					if($net_profit < 0){
						$v_net_profit= '( '.formatMoney(abs($net_profit)).' )';
					}else{
						$v_net_profit = formatMoney($net_profit);
					}
					$tbody .='<tr>
								<td style="font-weight:bold; color:#4286f4">'.lang('net_profit_loss').'</td>
								'.$td_net_profit_biller.'
								'.$td_net_profit_project.'
								<td style="text-align:right; font-weight:bold; color:#4286f4">'.$v_net_profit.'</td>
							</tr>';
				?>
				
                <div class="table-responsive">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;" border="1" class="table table-bordered table-hover table-striped table-condensed accountings-table dataTable">
						<thead>
							<tr>
								<th rowspan="<?= $rowspan ?>"><?= lang('account'); ?></th>
								<?= $thead ?>
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
			this.download = "income_statement.xls";
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
				url : "<?= site_url("accountings/get_project") ?>",
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
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;        
			zoom: 85% !important;
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