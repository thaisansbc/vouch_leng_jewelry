<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('balance_sheet_with_last_month'); ?>
			<?php
				if ($this->input->post('date')) {
					echo  $this->input->post('date') ;
				}else{
					echo  date("m/d/Y");
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

                    <?php echo form_open("accountings/balance_sheet_with_last_month"); ?>
					
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
					
                    if(isset($_POST['end_date']) && $_POST['end_date']){
                        $last_month = date('m', strtotime($this->bpas->fld($_POST['end_date']))) - 1;
                        $year = date('Y', strtotime($this->bpas->fld($_POST['end_date'])));
                        $date = $_POST['end_date'];
					}else{
                        $last_month = date('m') - 1;
                        $year = date('Y');
                        $date = date('d/m/Y');
                    }
                    
                    $last_date =  date("Y-m-d", strtotime($this->bpas->fld($date)));
     
         
                    if($last_month == 0){
                        $last_month = 12;
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
			

					if(isset($_POST['project_multi']) && $_POST['biller']){
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
					
					$array_months = array(1 => lang('jan'), 2 => lang('feb'), 3 => lang('mar'), 4 => lang('apr'), 5 => lang('may'), 6 => lang('jun'), 7 => lang('jul'), 8 => lang('aug'), 9 => lang('sep'), 10 => lang('oct'), 11 => lang('nov'), 12 => lang('dec'));
					$months[$last_month] = $array_months[$last_month]; 

					$thead = '';
					$rowspan= 2;
                    $colspan_main = 3;
                    $thead .= '<th>'.lang('last_month').' ('.$months[$last_month].')</th>';
                    $thead .= '<th>'.$date.'</th>';
                   
				?>
				
				
				
				<?php
					function getAccountByParent($parent_code){
						$CI =& get_instance();
						$data = $CI->accounts_model->getAccountByParent($parent_code);
						return $data;
				
					}
					$retainearning_acc = $this->accounting_setting->default_retained_earnings;
					$accTrans = array();
					$accTranMonths = array();
					$netIncomeMonths= array();
					
					$getAccTranAmounts = $this->accounts_model->getMonthAccTranAmounts(false,1,1);
                    $retainearning = $this->accounts_model->getAmountRetainEarning()->amount;
					$retainearning_array = (object) array(
											'account' => $retainearning_acc,
											'year' => $year,
											'month' => 1,
											'amount' => $retainearning,
											'nature' => (-1),
                    );
                
					array_push($getAccTranAmounts,$retainearning_array);

					if($getAccTranAmounts){
						foreach($getAccTranAmounts as $getAccTranAmount){
							$accTrans[$getAccTranAmount->account] = ($getAccTranAmount->amount * $getAccTranAmount->nature) + (isset($accTrans[$getAccTranAmount->account])?$accTrans[$getAccTranAmount->account]:0);
							if(($year!=$getAccTranAmount->year) || ($year==$getAccTranAmount->year && $getAccTranAmount->month <= $last_month && $last_month != 12)){
								$accTranMonths[$getAccTranAmount->account][$last_month] = (isset($accTranMonths[$getAccTranAmount->account][$last_month])?$accTranMonths[$getAccTranAmount->account][$last_month]:0) + ($getAccTranAmount->amount * $getAccTranAmount->nature);
							}					
						}
                    }

					if($last_month==12){
                        $getNetIncomeMonths = $this->accounts_model->getMonthAmountNetIncome(1);
                        $retainearning_last_year = $this->accounts_model->getAmountRetainEarningByYear($year)->amount;
                        $accTranMonths[$retainearning_acc][$last_month] = (isset($accTranMonths[$retainearning_acc][$last_month])?$accTranMonths[$retainearning_acc][$last_month]:0) + ($retainearning_last_year * (-1));
                    }else{
                        $getNetIncomeMonths = $this->accounts_model->getMonthAmountNetIncome();
                    }
					
                    if($getNetIncomeMonths){
                        foreach($getNetIncomeMonths as $getNetIncomeMonth){
                            if($getNetIncomeMonth->month <= $last_month){
                                $netIncomeMonths[$last_month] = (isset($netIncomeMonths[$last_month])?$netIncomeMonths[$last_month]:0) + $getNetIncomeMonth->amount;
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
					
					function getSubAccount($subAccounts,$accTrans,$accTranMonths, $months, $year, $billers, $projects, $last_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_months = array();
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
								$SubSubAccount = getSubSubAccount($SubSubAccounts,$accTrans,$accTranMonths, $months, $year, $billers, $projects, $last_date);
								$tmp_td = $SubSubAccount['sub_td'];
								$amount += $SubSubAccount['total_amount'];
							}else{
								$SubSubAccount = array();
                            }
                            
                            foreach($months as $month => $value){
                                $amount_month = (isset($accTranMonths[$subAccount->accountcode][$month])?$accTranMonths[$subAccount->accountcode][$month]:0);
                                $total_amount_months[$month] = $amount_month + (isset($total_amount_months[$month])?$total_amount_months[$month]:0) + (isset($SubSubAccount['total_amount_months'][$month])?$SubSubAccount['total_amount_months'][$month]:0);
                            }

							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if(formatDecimal($amount) != 0){
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
                                        }else if($amount_month > 0){
                                            $v_amount_month = formatMoney($amount_month);
                                        }else{
                                            $v_amount_month = '';
                                        }
                                        $start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
										$end_date = date("Y-m-t", strtotime($start_date));
                                        $sub_td_month .= '<td class="accounting_link" id="'.$subAccount->accountcode.'/x/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount_month.'</td>';
                                    }
									$sub_td .= '<tr>
												<td>'.$space.$subAccount->accountcode.' - '.$subAccount->accountname.'</td>
                                                '.$sub_td_month.'
                                                <td class="accounting_link" id="'.$subAccount->accountcode.'/x/'.$last_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount.'</td>
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
					
					function getSubSubAccount($SubSubAccounts,$accTrans,$accTranMonths, $months, $year, $billers, $projects, $last_date){
						$sub_td = '';
						$total_amount = 0;
						$amount = 0;
						$total_amount_months = array();
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
								$subAccount = getSubAccount($subAccounts,$accTrans,$accTranMonths, $months, $year, $billers, $projects, $last_date);
								$tmp_td = $subAccount['sub_td'];
								$amount += $subAccount['total_amount'];
							}else{
								$subAccount = array();
							}

                            foreach($months as $month => $value){
                                $amount_month = (isset($accTranMonths[$SubSubAccount->accountcode][$month])?$accTranMonths[$SubSubAccount->accountcode][$month]:0);
                                $total_amount_months[$month] = $amount_month + (isset($total_amount_months[$month])?$total_amount_months[$month]:0) + (isset($subAccount['total_amount_months'][$month])?$subAccount['total_amount_months'][$month]:0);
                            }

							$total_amount += $amount;
							if(isset($_POST['sub_account']) && $_POST['sub_account']=='yes'){
								if(formatDecimal($amount) != 0){
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
                                        }else if($amount_month > 0){
                                            $v_amount_month = formatMoney($amount_month);
                                        }else{
                                            $v_amount_month = '';
										}
										$start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
										$end_date = date("Y-m-t", strtotime($start_date));
                                        $sub_td_month .= '<td class="accounting_link" id="'.$SubSubAccount->accountcode.'/x/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount_month.'</td>';
                                    }

									$sub_td .= '<tr>
													<td>'.$space.$SubSubAccount->accountcode.' - '.$SubSubAccount->accountname.'</td>
                                                    '.$sub_td_month.'
                                                    <td class="accounting_link" id="'.$SubSubAccount->accountcode.'/x/'.$last_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right">'.$v_amount.'</td>
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
					$total_li_qu = 0;
					$total_li_qu_months = array();
					foreach($balance_sheets as $balance_sheet){
						$total_main_section = 0;	
						$main_section_months = array();
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
								$section_months = array();
								$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left' colspan='".$colspan_main."'><span>&nbsp;&nbsp;&nbsp;&nbsp;".$section->sectionname."</span></td></tr>";
								$mainAccounts = $this->accounts_model->getMainAccountBySection($section->sectionid);
								if($mainAccounts){
									$space ='&nbsp&nbsp&nbsp';
									foreach($mainAccounts as $mainAccount){
										$tmp_td = '';
										$subAccounts = getAccountByParent($mainAccount->accountcode);			
										$amount = (isset($accTrans[$mainAccount->accountcode])?$accTrans[$mainAccount->accountcode]:0);
										if($subAccounts){
											$sub_acc = getSubAccount($subAccounts,$accTrans,$accTranMonths, $months, $year, $billers, $projects, $last_date);
											$tmp_td = $sub_acc['sub_td'];
											$amount += $sub_acc['total_amount'];
										}else{
											$sub_acc = array();
										}
										if(formatDecimal($amount) != 0){
                                            $total_section += $amount;
											if($amount < 0){
												$v_amount = '( '.formatMoney(abs($amount)).' )';
											}else{
												$v_amount = formatMoney($amount);
											}
                                            $sub_td_month = '';										
                                            foreach($months as $month => $value){
                                                $amount_month = (isset($accTranMonths[$mainAccount->accountcode][$month])?$accTranMonths[$mainAccount->accountcode][$month]:0);
                                                $amount_month = $amount_month + (isset($sub_acc['total_amount_months'][$month])?$sub_acc['total_amount_months'][$month]:0);
                                                $section_months[$month] = (isset($section_months[$month])?$section_months[$month]:0) + $amount_month;
                                                $main_section_months[$month] = (isset($main_section_months[$month])?$main_section_months[$month]:0) + $amount_month;
                                                if($amount_month < 0){
                                                    $v_amount_month = '( '.formatMoney(abs($amount_month)).' )';
                                                }else if($amount_month > 0){
                                                    $v_amount_month = formatMoney($amount_month);
                                                }else{
                                                    $v_amount_month = '';
												}
												
												$start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
												$end_date = date("Y-m-t", strtotime($start_date));

                                                $sub_td_month .= '<td class="accounting_link" id="'.$mainAccount->accountcode.'/x/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right; font-weight:bold">'.$v_amount_month.'</td>';
                                            }
                                            $end_date = $last_date;
											$tbody .='<tr>
														<td style="font-weight:bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$space.$mainAccount->accountcode.' - '.$mainAccount->accountname.'</td>
                                                        '.$sub_td_month.'
                                                        <td class="accounting_link" id="'.$mainAccount->accountcode.'/x/'.$end_date.'/x/'.$billers.'/'.$projects.'" style="text-align:right; font-weight:bold">'.$v_amount.'</td>
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
								
								$td_section_month = '';

                                foreach($months as $month => $value){
                                    $section_month = isset($section_months[$month]) ? $section_months[$month] : false ;
                                    if($section_month < 0){
                                        $v_section_month = '( '.formatMoney(abs($section_month)).' )';
                                    }else if($section_month > 0){
                                        $v_section_month = formatMoney($section_month);
                                    }else{
                                        $v_section_month = '';
                                    }
                                    $td_section_month .="<td style='text-align:right; font-weight:bold'>".$v_section_month."</td>";
                                }

								$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left'><span>&nbsp;&nbsp;&nbsp;&nbsp;".lang("total").' '.$section->sectionname."</span></td>
                                            ".$td_section_month."
                                            <td style='text-align:right; font-weight:bold'>".$v_total_section."</td>
										</tr>";
										
								if($balance_sheet=='EQ'){
                                    $td_net_month = '';
                                    $net_income = ($this->accounts_model->getAmountNetIncome()->amount) * $section->nature;
									$total_main_section += $net_income;
									if($net_income < 0){
										$v_net_income = '( '.formatMoney(abs($net_income)).' )';
									}else if($net_income > 0){
										$v_net_income = formatMoney($net_income);
									}else{
										$v_net_income= '';
									}
                                    foreach($months as $month => $value){
                                        $netIncomeMonth = $netIncomeMonths[$month] * $section->nature;
                                        $main_section_months[$month] = (isset($main_section_months[$month])?$main_section_months[$month]:0) + $netIncomeMonth;
                                        if($netIncomeMonth < 0){
                                            $v_net_month = '( '.formatMoney(abs($netIncomeMonth)).' )';
                                        }else if($netIncomeMonth > 0){
                                            $v_net_month = formatMoney($netIncomeMonth);
                                        }else{
                                            $v_net_month = '';
                                        }
                                        $td_net_month .="<td style='text-align:right; font-weight:bold'>".$v_net_month."</td>";
                                    }
									
									$tbody .="<tr style='color:#39c65c; font-weight:bold'><td style='text-align:left'><span>&nbsp;&nbsp;&nbsp;&nbsp;".lang('total')." ".lang('net_income')."</span></td>
                                                    ".$td_net_month."
                                                    <td style='text-align:right; font-weight:bold'>".$v_net_income."</td> 
												</tr>";
                                }	
                                $total_main_section += $total_section;	
							}
						}
						
						$td_main_section_month = '';
                        foreach($months as $month => $value){
                            $main_section_month = (isset($main_section_months[$month])?$main_section_months[$month]:0);
                            if($balance_sheet == 'LI' || $balance_sheet == 'EQ'){
                                $total_li_qu_months[$month] = (isset($total_li_qu_months[$month])?$total_li_qu_months[$month]:0) + $main_section_month;
                            }
                            if($main_section_month < 0){
                                $v_main_section_month = '( '.formatMoney(abs($main_section_month)).' )';
                            }else if($main_section_month > 0){
                                $v_main_section_month = formatMoney($main_section_month);
                            }else{
                                $v_main_section_month = '';
                            }
                            $td_main_section_month .="<td style='text-align:right; font-weight:bold'>".$v_main_section_month."</td>";
                        }
                        if($total_main_section < 0){
							$v_total_main_section = '( '.formatMoney(abs($total_main_section)).' )';
						}else if($total_main_section > 0){
							$v_total_main_section = formatMoney($total_main_section);
						}else{
							$v_total_main_section = '';
						}
						$tbody .="<tr style='font-weight:bold; color:#4286f4'><td style='text-align:left'><span>".lang("total").' '.$main_section."</span></td>
                                    ".$td_main_section_month."
                                    <td style='text-align:right; font-weight:bold'>".$v_total_main_section."</td>
                                </tr>";
                        if($balance_sheet == 'LI' || $balance_sheet == 'EQ'){
                            $total_li_qu += $total_main_section;
                        }        
								
					}

					$td_li_qu_month = '';
                    foreach($months as $month => $value){
                        $total_li_qu_month = $total_li_qu_months[$month];
                        if($total_li_qu_month < 0){
                            $v_li_qu_month = '( '.formatMoney(abs($total_li_qu_month)).' )';
                        }else if($total_li_qu_month > 0){
                            $v_li_qu_month = formatMoney($total_li_qu_month);
                        }else{
                            $v_li_qu_month = '';
                        }
                        $td_li_qu_month .="<td style='text-align:right; font-weight:bold'>".$v_li_qu_month."</td>";
                    }

                    if($total_li_qu < 0){
						$v_total_li_qu = '( '.formatMoney(abs($total_li_qu)).' )';
					}else{
						$v_total_li_qu = formatMoney($total_li_qu);
					}
					
					$tbody .="<tr style='font-weight:bold; color:#4286f4'><td style='text-align:left'><span>".lang('total')." ".lang('liabilities')." ".lang('and')." ".lang('equities')."</span></td>
                                ".$td_li_qu_month."		
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
			this.download = "balance_sheet_with_last_month.xls";
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
				if(isset($_POST['project_multi']) && $_POST['project_multi']){
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