<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('balance_sheet'); ?>
			<?php
				if ($this->input->post('end_date')) {
					echo $this->input->post('end_date');
				}else{
					echo date("d/m/Y");
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
            </ul>
        </div>
		
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("reports/balance_sheet"); ?>
					
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
												$mpj[$multi_project->project_id] = $multi_project->project_name;
											}
										}
										
										echo form_dropdown('project_multi[]', $mpj, (isset($_POST['project_multi']) ? $_POST['project_multi'] : ''), 'id="project_multi" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '"  style="width:100%;" multiple');
										?>
									</div>	
								</div>
							 </div>
						<?php } ?>
						
						
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date("d/m/Y")), 'class="form-control date" id="end_date"'); ?>
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
				<?php
					
					if(isset($_POST['end_date'])){
						$end_date = $this->bpas->fsd($_POST['end_date']);
					}else{
						$end_date = date("Y-m-d");
					}
				
					$biller_multi = (isset($_POST['biller'])?$_POST['biller']:false);
					$project_multi = (isset($_POST['project_multi'])?$_POST['project_multi']:false);
					$project = (isset($_POST['project'])?$_POST['project']:false);
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
						$thead .= '<th>'.lang('total').'</th>';
					}
				?>
				
				
				
				<?php
					function getAccountByParent($parent_code){
						$CI =& get_instance();
						$data = $CI->accounts_model->getAccountByParent($parent_code);
						return $data;
				
					}
					$retainearning_acc = $this->accounting_setting->default_retained_earnings;
					$accTrans = array();
					$accTranBillers = array();
					$accTranProjects = array();
					$netIncomeBillers= array();
					$netIncomeProjects= array();
					
					$getAccTranAmounts = $this->accounts_model->getAccTranAmounts(1);
					if($getAccTranAmounts){

						foreach($getAccTranAmounts as $getAccTranAmount){
							$accTrans[$getAccTranAmount->account_code] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
						}
						
					}
					$retainearning = ($this->accounts_model->getAmountRetainEarning()->amount) * (-1);
					$accTrans[$retainearning_acc] = $retainearning + (isset($accTrans[$retainearning_acc]) ? $accTrans[$retainearning_acc] : 0);
					
					if((isset($biller_multi) && $biller_multi) && !$project_multi){
						$getAccTranAmounts = $this->accounts_model->getAccTranAmountBillers();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranBillers[$getAccTranAmount->account_code][$getAccTranAmount->biller_id] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}
							
						}
						$getNetIncomeBillers = $this->accounts_model->getAmountNetIncomeBillers();
						if($getNetIncomeBillers){
							foreach($getNetIncomeBillers as $getNetIncomeBiller){
								$netIncomeBillers[$getNetIncomeBiller->biller_id] = $getNetIncomeBiller->amount;
							}
						}
						
						$getRetainEarningBillers = $this->accounts_model->getAmountRetainEarningBillers();
						if($getRetainEarningBillers){
							foreach($getRetainEarningBillers as $getRetainEarningBiller){
								$accTranBillers[$retainearning_acc][$getRetainEarningBiller->biller_id] = (isset($accTranBillers[$retainearning_acc][$getRetainEarningBiller->biller_id])?$accTranBillers[$retainearning_acc][$getRetainEarningBiller->biller_id]:0) + ($getRetainEarningBiller->amount) * (-1);
							}
						}
						
					}else if($project_multi){
						$getAccTranAmounts = $this->accounts_model->getAccTranAmountProjects();
						if($getAccTranAmounts){
							foreach($getAccTranAmounts as $getAccTranAmount){
								$accTranProjects[$getAccTranAmount->account_code][$getAccTranAmount->project_id] = ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}
						}
						$getNetIncomeProjects = $this->accounts_model->getAmountNetIncomeProjects();
						if($getNetIncomeProjects){
							foreach($getNetIncomeProjects as $getNetIncomeProject){
								$netIncomeProjects[$getNetIncomeProject->project_id] = $getNetIncomeProject->amount;
							}
						}
						
						$getRetainEarningProjects = $this->accounts_model->getAmountRetainEarningProjects();
						if($getRetainEarningProjects){
							foreach($getRetainEarningProjects as $getRetainEarningProject){
								$accTranProjects[$retainearning_acc][$getRetainEarningProject->project_id] = (isset($accTranProjects[$retainearning_acc][$getRetainEarningProject->project_id])?$accTranProjects[$retainearning_acc][$getRetainEarningProject->project_id]:0) + ($getRetainEarningProject->amount * (-1));
							}
						}
					}
					
					
					function formatMoney($number)
					{
						$CI =& get_instance();
						$data = $CI->bpas->formatMoney($number);
						return $data;
					}
					function formatDecimal($number)
					{
						$CI =& get_instance();
						$data = $CI->bpas->formatDecimal($number);
						return $data;
					}
					
					

					function getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_billers = array();
						$total_amount_projects = array();
						foreach($subAccounts as $subAccount){
							$tmp_td = '';
							$space ='&nbsp&nbsp';
							$split = explode('/',$subAccount->lineage);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							$amount = (isset($accTrans[$subAccount->accountcode])?$accTrans[$subAccount->accountcode]:0);
							$SubSubAccounts = getAccountByParent($subAccount->accountcode);
							if($SubSubAccounts){
								$SubSubAccount = getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $end_date);
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
								if(formatDecimal($amount) != 0){
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
											}else if($amount_biller > 0){
												$v_amount_biller = formatMoney($amount_biller);
											}else{
												$v_amount_biller = '';
											}
											
											$sub_td_biller .= '<td class="accounting_link" id="'.$subAccount->accountcode.'/x/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right">'.$v_amount_biller.'</td>';
										}
									}else if($project_multi){								
										foreach($project_multi as $project_id){
											$amount_project = (isset($accTranProjects[$subAccount->accountcode][$project_id])?$accTranProjects[$subAccount->accountcode][$project_id]:0) + (isset($SubSubAccount['total_amount_projects'][$project_id])?$SubSubAccount['total_amount_projects'][$project_id]:0);
											if($amount_project < 0){
												$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
											}else if($amount_project > 0){
												$v_amount_project = formatMoney($amount_project);
											}else{
												$v_amount_project = '';
											}
											$sub_td_project .= '<td class="accounting_link" id="'.$subAccount->accountcode.'/x/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right">'.$v_amount_project.'</td>';
										}
									}
									$sub_td .= '<tr>
												<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$space.$subAccount->accountcode.' - '.$subAccount->accountname.'</td>
												'.$sub_td_biller.'
												'.$sub_td_project.'
												<td class="accounting_link" id="'.$subAccount->accountcode.'/x/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount.'</td>
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
					
					function getSubSubAccount($SubSubAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $end_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_billers = array();
						$total_amount_projects = array();
						foreach($SubSubAccounts as $SubSubAccount){
							$tmp_td = '';
							$space ='&nbsp&nbsp';
							$split = explode('/',$SubSubAccount->lineage);
							for($i = 0 ; $i < count($split); $i++){
								$space.= $space;
							}
							
							$amount = (isset($accTrans[$SubSubAccount->accountcode])?$accTrans[$SubSubAccount->accountcode]:0);
							$subAccounts = getAccountByParent($SubSubAccount->accountcode);
							if($subAccounts){
								$subAccount = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $end_date);
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
								if(formatDecimal($amount) != 0){
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
											}else if($amount_biller > 0){
												$v_amount_biller = formatMoney($amount_biller);
											}else{
												$v_amount_biller = '';
											}
											$sub_td_biller .= '<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/x/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right">'.$v_amount_biller.'</td>';
										}
									}else if($project_multi){								
										foreach($project_multi as $project_id){
											$amount_project = (isset($accTranProjects[$SubSubAccount->accountcode][$project_id])?$accTranProjects[$SubSubAccount->accountcode][$project_id]:0)  + (isset($subAccount['total_amount_projects'][$project_id])?$subAccount['total_amount_projects'][$project_id]:0);			
											if($amount_project < 0){
												$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
											}else if($amount_project > 0){
												$v_amount_project = formatMoney($amount_project);
											}else{
												$v_amount_project = '';
											}
											$sub_td_project .= '<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/x/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right">'.$v_amount_project.'</td>';
										}
									}
									$sub_td .= '<tr>
													<td>'.$space.$SubSubAccount->accountcode.' - '.$SubSubAccount->name.'</td>
													'.$sub_td_biller.'
													'.$sub_td_project.'
													<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/x/'.$end_date.'/x/x/x" style="text-align:right">'.$v_amount.'</td>
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
					$total_li_qu = 0;
					$total_li_qu_billers = array();
					$total_li_qu_projects = array();

					foreach($balance_sheets as $balance_sheet){
						$total_main_section = 0;	
						$main_section_billers = array();
						$main_section_projects = array();
						if($balance_sheet=='AS'){
							$main_section = 'ASSETS';
						}else if ($balance_sheet=='LI'){
							$main_section = 'LIABILITIES';
						}else{
							$main_section = 'EQUITIES';
						}
						$tbody .="<tr style='font-weight:bold; color:#4286f4'><td style='text-align:left' colspan='".$colspan_main."'><span>".$main_section."</span></td></tr>";
						$sections = $this->accounts_model->getAccountSectionsByCode(array($balance_sheet));	
						if($sections){
							
							foreach($sections as $section){
								$total_section = 0;
								$section_billers = array();
								$section_projects = array();
								$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left' colspan='".$colspan_main."'><span>&nbsp;&nbsp;&nbsp;&nbsp;".$section->sectionname."</span></td></tr>";
								$mainAccounts = $this->accounts_model->getMainAccountBySection($section->sectionid);
								if($mainAccounts){
									// echo '<pre>';
									// print_r($mainAccounts);
									// echo '</pre>';
									$space ='&nbsp&nbsp&nbsp';
									foreach($mainAccounts as $mainAccount){
										$tmp_td = '';
										$subAccounts = getAccountByParent($mainAccount->accountcode);			
										$amount = (isset($accTrans[$mainAccount->accountcode])?$accTrans[$mainAccount->accountcode]:0);

										if($subAccounts){
											$sub_acc = getSubAccount($subAccounts,$accTrans,$accTranBillers, $biller_multi, $accTranProjects, $project_multi, $end_date);
											$tmp_td = $sub_acc['sub_td'];
											$amount += $sub_acc['total_amount'];
										}else{
											$sub_acc = array();
										}
									
										if(formatDecimal($amount) != 0){
											$amount = $amount;
											$total_section += $amount;
											if($amount < 0){
												$v_amount = '( '.formatMoney(abs($amount)).' )';
											}else{
												$v_amount = formatMoney($amount);
											}
											$sub_td_biller = '';
											$sub_td_project = '';
											if(isset($biller_multi) && $biller_multi && !$project_multi){					
												foreach($biller_multi as $biller_id){
													$amount_biller = (isset($accTranBillers[$mainAccount->accountcode][$biller_id])?$accTranBillers[$mainAccount->accountcode][$biller_id]:0);
													$amount_biller = $amount_biller + (isset($sub_acc['total_amount_billers'][$biller_id])?$sub_acc['total_amount_billers'][$biller_id]:0);
													$section_billers[$biller_id] = (isset($section_billers[$biller_id])?$section_billers[$biller_id] :0)+ $amount_biller;
													$main_section_billers[$biller_id] = (isset($main_section_billers[$biller_id])?$main_section_billers[$biller_id]:0) + $amount_biller;
													if($amount_biller < 0){
														$v_amount_biller = '( '.formatMoney(abs($amount_biller)).' )';
													}else if($amount_biller > 0){
														$v_amount_biller = formatMoney($amount_biller);
													}else{
														$v_amount_biller = '';
													}
													$sub_td_biller .= '<td class="accounting_link" id="'.$mainAccount->accountcode.'/x/'.$end_date.'/x/'.$biller_id.'/x" style="text-align:right; font-weight:bold">'.$v_amount_biller.'</td>';
												}
											} else if($project_multi){
																						
												foreach($project_multi as $project_id){
													$amount_project = (isset($accTranProjects[$mainAccount->accountcode][$project_id])?$accTranProjects[$mainAccount->accountcode][$project_id]:0);
													$amount_project = $amount_project + (isset($sub_acc['total_amount_projects'][$project_id])?$sub_acc['total_amount_projects'][$project_id]:0);
													$section_projects[$project_id] = (isset($section_projects[$project_id])?$section_projects[$project_id]:0) + $amount_project;
													$main_section_projects[$project_id] = (isset($main_section_projects[$project_id])?$main_section_projects[$project_id]:0) + $amount_project;
													if($amount_project < 0){
														$v_amount_project = '( '.formatMoney(abs($amount_project)).' )';
													}else if($amount_project > 0){
														$v_amount_project = formatMoney($amount_project);
													}else{
														$v_amount_project = '';
													}
													$sub_td_project .= '<td class="accounting_link" id="'.$mainAccount->accountcode.'/x/'.$end_date.'/x/x/'.$project_id.'" style="text-align:right; font-weight:bold">'.$v_amount_project.'</td>';
												}
											}
											
											$tbody .='<tr>
														<td style="font-weight:bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$space.$mainAccount->accountcode.' - '.$mainAccount->accountname.'</td>
														'.$sub_td_biller.'
														'.$sub_td_project.'
														<td class="accounting_link" id="'.$mainAccount->accountcode.'/x/'.$end_date.'/x/x/x" style="text-align:right; font-weight:bold">'.$v_amount.'</td>
													</tr>';
											
										}
										$tbody .= $tmp_td;		
									}
								}
								
								if($total_section < 0){
									$v_total_section = '( '.formatMoney(abs($total_section)).' )';
								}else{
									$v_total_section = formatMoney($total_section);
								}
								
								$td_section_biller = '';
								$td_section_project = '';
								if($biller_multi && !$project_multi){
									foreach($biller_multi as $biller_id){
										$section_biller = (isset($section_billers[$biller_id])?$section_billers[$biller_id]:0);
										if($section_biller < 0){
											$v_section_biller = '( '.formatMoney(abs($section_biller)).' )';
										}else if($section_biller > 0){
											$v_section_biller = formatMoney($section_biller);
										}else{
											$v_section_biller = '';
										}
										$td_section_biller .="<td style='text-align:right; font-weight:bold'>".$v_section_biller."</td>";
									}
								}else if($project_multi){
									foreach($project_multi as $project_id){
										$section_project = (isset($section_projects[$project_id])?$section_projects[$project_id]:0);
										if($section_project < 0){
											$v_section_project = '( '.formatMoney(abs($section_project)).' )';
										}else if($section_project > 0){
											$v_section_project = formatMoney($section_project);
										}else{
											$v_section_project = '';
										}
										$td_section_project .="<td style='text-align:right; font-weight:bold'>".$v_section_project."</td>";
									}
								}
								$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left'><span>&nbsp;&nbsp;&nbsp;&nbsp;".lang("total").' '.$section->sectionname."</span></td>
											".$td_section_biller."
											".$td_section_project."
											<td style='text-align:right; font-weight:bold'>".$v_total_section."</td>
											
										</tr>";
										
								if($balance_sheet=='EQ'){
									$td_net_biller = '';
									$td_net_project = '';
									$net_income = ($this->accounts_model->getAmountNetIncome()->amount) * $section->nature;
									$total_main_section += $net_income;
									if($net_income < 0){
										$v_net_income = '( '.formatMoney(abs($net_income)).' )';
									}else if($net_income > 0){
										$v_net_income = formatMoney($net_income);
									}else{
										$v_net_income= '';
									}
									
									
									if($biller_multi && !$project_multi){
										foreach($biller_multi as $biller_id){
											$netIncomeBiller = (isset($netIncomeBillers[$biller_id])?$netIncomeBillers[$biller_id]:0) * $section->nature;
											$main_section_billers[$biller_id] = (isset($main_section_billers[$biller_id])?$main_section_billers[$biller_id]:0) + $netIncomeBiller;
											if($netIncomeBiller < 0){
												$v_net_biller = '( '.formatMoney(abs($netIncomeBiller)).' )';
											}else if($netIncomeBiller > 0){
												$v_net_biller = formatMoney($netIncomeBiller);
											}else{
												$v_net_biller = '';
											}
											$td_net_biller .="<td style='text-align:right; font-weight:bold'>".$v_net_biller."</td>";
										}
									}else if($project_multi){
										foreach($project_multi as $project_id){
											$netIncomeProject = (isset($netIncomeProjects[$project_id])?$netIncomeProjects[$project_id]:0) * $section->nature;
											$main_section_projects[$project_id] = (isset($main_section_projects[$project_id])?$main_section_projects[$project_id]:0) + $netIncomeProject;
											if($netIncomeProject < 0){
												$v_net_project = '( '.formatMoney(abs($netIncomeProject)).' )';
											}else if($netIncomeProject > 0){
												$v_net_project = formatMoney($netIncomeProject);
											}else{
												$v_net_project = '';
											}
											$td_net_project .="<td style='text-align:right; font-weight:bold'>".$v_net_project."</td>";
										}
									}
									
									
									
									$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left'><span>&nbsp;&nbsp;&nbsp;&nbsp;".lang('total')." ".lang('net_income')."</span></td>
													".$td_net_biller."
													".$td_net_project."
													<td style='text-align:right; font-weight:bold'>".$v_net_income."</td>
												</tr>";
								}		
								
								$total_main_section += $total_section;
							}
						}
						
						$td_main_section_biller = '';
						$td_main_section_project = '';
						if($biller_multi && !$project_multi){
							foreach($biller_multi as $biller_id){
								$main_section_biller = $main_section_billers[$biller_id];
								if($balance_sheet == 'LI' || $balance_sheet == 'EQ'){
									$total_li_qu_billers[$biller_id] = (isset($total_li_qu_billers[$biller_id])?$total_li_qu_billers[$biller_id]:0) + $main_section_biller;
								}
								if($main_section_biller < 0){
									$v_main_section_biller = '( '.formatMoney(abs($main_section_biller)).' )';
								}else if($main_section_biller > 0){
									$v_main_section_biller = formatMoney($main_section_biller);
								}else{
									$v_main_section_biller = '';
								}
								$td_main_section_biller .="<td style='text-align:right; font-weight:bold'>".$v_main_section_biller."</td>";
							}
						}else if($project_multi){
							foreach($project_multi as $project_id){
								$main_section_project = $main_section_projects[$project_id];
								if($balance_sheet == 'LI' || $balance_sheet == 'EQ'){
									$total_li_qu_projects[$project_id] = (isset($total_li_qu_projects[$project_id])? $total_li_qu_projects[$project_id]:0) + $main_section_project;
								}
								if($main_section_project < 0){
									$v_main_section_project = '( '.formatMoney(abs($main_section_project)).' )';
								}else if($main_section_project > 0){
									$v_main_section_project = formatMoney($main_section_project);
								}else{
									$v_main_section_project = '';
								}
								$td_main_section_project .="<td style='text-align:right; font-weight:bold'>".$v_main_section_project."</td>";
							}
						}
						
						
						if($total_main_section < 0){
							$v_total_main_section = '( '.formatMoney(abs($total_main_section)).' )';
						}else if($total_main_section > 0){
							$v_total_main_section = formatMoney($total_main_section);
						}else{
							$v_total_main_section = '';
						}
						
						$tbody .="<tr style='font-weight:bold; color:#4286f4'><td style='text-align:left'><span>".lang("total").' '.$main_section."</span></td>
									".$td_main_section_biller."
									".$td_main_section_project."
									<td style='text-align:right; font-weight:bold'>".$v_total_main_section."</td>
						
								</tr>";
								
						if($balance_sheet == 'LI' || $balance_sheet == 'EQ'){
							$total_li_qu += $total_main_section;
						}		
					}
					if($total_li_qu < 0){
						$v_total_li_qu = '( '.formatMoney(abs($total_li_qu)).' )';
					}else{
						$v_total_li_qu = formatMoney($total_li_qu);
					}
					
					$td_li_qu_biller = '';
					$td_li_qu_project = '';
					if($biller_multi && !$project_multi){
						foreach($biller_multi as $biller_id){
							$total_li_qu_biller = (isset($total_li_qu_billers[$biller_id])?$total_li_qu_billers[$biller_id]:0);
							if($total_li_qu_biller < 0){
								$v_li_qu_biller = '( '.formatMoney(abs($total_li_qu_biller)).' )';
							}else if($total_li_qu_biller > 0){
								$v_li_qu_biller = formatMoney($total_li_qu_biller);
							}else{
								$v_li_qu_biller = '';
							}
							$td_li_qu_biller .="<td style='text-align:right; font-weight:bold'>".$v_li_qu_biller."</td>";
						}
					}else if($project_multi){
						foreach($project_multi as $project_id){
							$total_li_qu_project = (isset($total_li_qu_projects[$project_id])?$total_li_qu_projects[$project_id]:0);
							if($total_li_qu_project < 0){
								$v_li_qu_project = '( '.formatMoney(abs($total_li_qu_project)).' )';
							}else if($total_li_qu_project > 0){
								$v_li_qu_project = formatMoney($total_li_qu_project);
							}else{
								$v_li_qu_project = '';
							}
							$td_li_qu_project .="<td style='text-align:right; font-weight:bold'>".$v_li_qu_project."</td>";
						}
					}
					
					$tbody .="<tr style='font-weight:bold; color:#4286f4'><td style='text-align:left'><span>".lang('total')." ".lang('liabilities')." ".lang('and')." ".lang('equities')."</span></td>
								".$td_li_qu_biller."
								".$td_li_qu_project."
								<td style='text-align:right; font-weight:bold'>".$v_total_li_qu."</td>					
							</tr>";
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
			this.download = "balance_sheet.xls";
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