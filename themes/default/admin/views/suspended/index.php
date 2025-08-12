<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=lang('pos_module') . " | " . $Settings->site_name;?></title>
    <script type="text/javascript">if(parent.frames.length !== 0){top.location = '<?=admin_url('pos')?>';}</script>
    <base href="<?=base_url()?>"/>
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
    <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>styles/style.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>pos/css/posajax.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>pos/css/print.css" type="text/css" media="print"/>
    <script type="text/javascript" src="<?=$assets?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?=$assets?>js/jquery-migrate-1.2.1.min.js"></script>
    <style type="text/css">
        .txt_title{
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            text-align: left;
            padding-left: 10px;
        }
    </style>
</head>
<body class ="bg_">
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>
<div id="wrapper">
    <header id="header" class="navbar">
    <div class="container">
                <a class="navbar-brand" href="<?=admin_url()?>"><span class="logo"><span class="pos-logo-lg"><?=$Settings->site_name?></span><span class="pos-logo-sm"><?=lang('pos')?></span></span></a>
                <div class="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown">
                            <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                                <img alt="" src="<?=$this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png';?>" class="mini_avatar img-rounded">
                                <div class="user">
                                    <span><?=lang('welcome')?>! <?=$this->session->userdata('username');?></span>
                                </div>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?=admin_url('auth/profile/' . $this->session->userdata('user_id'));?>">
                                        <i class="fa fa-user"></i> <?=lang('profile');?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?=admin_url('auth/profile/' . $this->session->userdata('user_id') . '/#cpassword');?>">
                                        <i class="fa fa-key"></i> <?=lang('change_password');?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?=admin_url('auth/logout');?>">
                                        <i class="fa fa-sign-out"></i> <?=lang('logout');?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown hide">
                            <a class="pos-tip" title="<?=lang('dashboard')?>" data-placement="bottom" href="<?=admin_url('welcome')?>">
                                <i class="fa fa-dashboard"></i>
                            </a>
                        </li>
                        <?php if ($Owner) {?>
                            <li class="dropdown hidden-sm">
                                <a class="btn bdarkGreen pos-tip" title="<?=lang('settings')?>" data-placement="bottom" href="<?=admin_url('pos/settings')?>">
                                    <i class="fa fa-cogs"></i><br>ការកំណត់
                                </a>
                            </li>
                        <?php } ?>
                        <li class="dropdown hidden-xs_ hide">
                            <a class="pos-tip" title="<?=lang('calculator')?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                <i class="fa fa-calculator"></i>
                            </a>
                            <ul class="dropdown-menu pull-right calc">
                                <li class="dropdown-content">
                                    <span id="inlineCalc"></span>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown hidden-sm_ hide">
                            <a class="pos-tip" title="<?=lang('shortcuts')?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                                <i class="fa fa-key"></i> <?=lang('shortcuts')?>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" title="<?= lang('E-Menu') ?>"data-placement="bottom" href="<?= base_url('shop/products') ?>" target="_blank">
                                <i class="fa fa-shopping-cart"></i><br>E-Menu
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" title="<?=lang('view_kitchen')?>"data-placement="bottom" href="<?=admin_url('pos/kitchen')?>" target="_blank">
                                <i class="fa fa-laptop"></i><br>ចង្ក្រានបាយ
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" title="<?=lang('view_bill_screen')?>" data-placement="bottom" href="<?=admin_url('pos/view_bill')?>" target="_blank">
                                <i class="fa fa-laptop"></i><br>មើលវិក្ក័បត្រ
                            </a>
                        </li>
                        <?php
                            if ($this->pos_settings->pos_type == "table" ||
                                $this->pos_settings->pos_type == "room"
                            ) {
                                ?>
                            <li class="dropdown hidden-xs">
                                <a class="btn bdarkGreen pos-tip" title="<?=lang('pos')?>" data-placement="bottom" href="<?=admin_url('table')?>">
                                    <i class="fa fa-th-large"></i> <br> តុ <?=lang('table')?>
                                </a>
                            </li>
                        <?php
                                }
                                ?>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="opened_bills" title="<span><?=lang('suspended_sales')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('pos/opened_bills')?>" data-toggle="ajax">
                                <i class="fa fa-th"></i><br>រក្សាទុកការលក់
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="register_details" title="<span><?=lang('register_details')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('pos/register_details')?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-check-circle"></i><br>ទឹកលុយលក់
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="close_register" title="<span><?=lang('close_register')?></span>" data-placement="bottom" data-html="true" data-backdrop="static" href="<?=admin_url('pos/close_register')?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-times-circle"></i><br>បិទការលក់
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="add_expense" title="<span><?=lang('add_expense')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('purchases/add_expense')?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-dollar"></i><br>ការចំណាយ
                            </a>
                        </li>
                        <?php if ($Owner) {?>
                            <li class="dropdown">
                                <a class="btn bdarkGreen pos-tip" id="today_profit" title="<span><?=lang('today_profit')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('reports/profit')?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                    <i class="fa fa-hourglass-half"></i><br>ចំណេញប្រចាំថ្ងៃ
                                </a>
                            </li>
                        <?php }
?>
                        <?php if ($Owner || $Admin) {?>
                            <li class="dropdown hide">
                                <a class="btn bdarkGreen pos-tip" id="today_sale" title="<span><?=lang('today_sale')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('pos/today_sale')?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                    <i class="fa fa-heart"></i><br>ការលក់ប្រចាំថ្ងៃ
                                </a>
                            </li>
                            <li class="dropdown hidden-xs">
                                <a class="btn bdarkGreen pos-tip" title="<?=lang('list_open_registers')?>" data-placement="bottom" href="<?=admin_url('pos/registers')?>">
                                    <i class="fa fa-list"></i><br>បើកការលក់
                                </a>
                            </li>
                            <li class="dropdown hidden-xs">
                                <a class="btn bred pos-tip" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
                                    <i class="fa fa-eraser"></i><br>សម្អាត
                                </a>
                            </li>
                        <?php }
?>
                    </ul>
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown">
                            <a class="" style="cursor: default;"><span id="display_time"></span></a>
                        </li>
                    </ul>
                </div>
            </div>
        <!-- <div class="container">
            <a class="navbar-brand" href="<?=admin_url()?>"><span class="logo"><span class="pos-logo-lg"><?=$Settings->site_name?></span><span class="pos-logo-sm"><?=lang('pos')?></span></span></a>
            <div class="header-nav">
                <ul class="nav navbar-nav pull-right">
                    <li class="dropdown">
                        <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                            <img alt="" src="<?=$this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png';?>" class="mini_avatar img-rounded">
                            <div class="user">
                                <span><?=lang('welcome')?>! <?=$this->session->userdata('username');?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="<?=admin_url('auth/profile/' . $this->session->userdata('user_id'));?>">
                                    <i class="fa fa-user"></i> <?=lang('profile');?>
                                </a>
                            </li>
                            <li>
                                <a href="<?=admin_url('auth/profile/' . $this->session->userdata('user_id') . '/#cpassword');?>">
                                    <i class="fa fa-key"></i> <?=lang('change_password');?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="<?=admin_url('auth/logout');?>">
                                    <i class="fa fa-sign-out"></i> <?=lang('logout');?>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

            </div>
        </div> -->
    </header>
    
    <div id="content">

        <div class="c1">
			<h1 class="title_name text-center"><?php 
			if($default_img =="BAT"){
				echo "Bungalow Display" ;
			}else{
				echo ($pos_type =="room")? "Room Display" : "Table Display" ;
			}
			?></h1>
            <div class="pos">
                 <div class="nav navbar-nav pull-left"style="margin-left:40px;height:600px;">
                     <?php foreach($floors as $floor){
                         echo '<table>';
                         if($pos_settings->show_floor == $floor->id){
                            echo '<tr><span style="width:151px;float:left;margin:1px 12px;color:blank;font-size:22px;cursor: pointer;" bfloor="'.$floor->id.'" class="sort_floor">'.$floor->name.'</span></tr>';
                         }else{
                            echo '<tr><span style="width:150px;float:right;padding:0 5px;margin:1px 10px;border: 1px solid;border-radius: 5px;color:white;font-size:20px;cursor: pointer; " bfloor="'.$floor->id.'" class="sort_floor">'.$floor->name.'</span></tr>';
                         }
                         echo '</table>';
                      } 
                       echo '<table>';
                            echo '<tr><span style="width:151px;margin:12px;padding:12px;cursor: pointer;" bfloor="-1" class="sort_floor">Show All</span></tr>';
                       echo '</table>';
                      ?>
				</div>
				<div class="containt_room">
                <?php
				$i=1;
				if(isset($kitchen_note[0])){
				foreach($kitchen_note as $note_order){
                    if($note_order->print_status){
						$class="border_title_blue";
					}
					elseif($note_order->suspend_note){
						$class="border_title_red";
					}elseif($note_order->booking){
                        $class="border_title_book";
                    }else{
						$class="border_title";
					}
					$date = date_create($note_order->start_date);
					$now=date("Y-m-d H:i:s");
					echo '<div class="table_display">';
						echo '<div class="'.$class.' sus_sale" room="'.$note_order->note_id.'" room_name="'.$note_order->name.'"	sid="'.$note_order->id.'" price="'.$note_order->price.'" discount="'.$note_order->amount.'">';
							if($note_order->suspend_note){
							    $start=date_format($date, 'Y-m-d H:i:s');
								$dteStart = new DateTime($now);
								$dteEnd   = new DateTime($start); 
								 $dteDiff  = $dteStart->diff($dteEnd); 
								 $dtt  = $dteDiff->format("%H:%I:%S");
								echo '<span>&nbsp;CheckIn: '.$dtt.'</span>';
							}elseif($note_order->booking){
								echo '<span>&nbsp;Booked</span>';
							}else{
								echo '<span>&nbsp;Available</span>';
							}
							echo '<div class="table_name text-center">'.$note_order->name.'</div>';
							
							if($note_order->suspend_note){
								echo '<div style="width:100%;height:80px;background:#D82924;"> 
                                        <div class="text-center txt_title">Total Cus: '.$note_order->customer_qty.' </div>
                                        <div class="text-center txt_title">Items: '.$note_order->count.' </div>
                                        <div class="text-center txt_title">Amount: '.$note_order->total.' </div>
                               </div>';
							}else{
                                echo '<div style="width:100%;height:80px;background:#78CD51;"> '.$note_order->total.'</div>';
							}
					echo '</div>';
						if($note_order->suspend_note){
					
                            echo '<div class="sub_avail">';
                                 echo '<span style="float:right;padding-right:5px;text-transform: uppercase;" class="people_qty" broom="'.$note_order->note_id.'"> <i class="fa fa-users"></i></span>' ;
                            
                                 if($note_order->tmp == 1){
                                    echo '<a href="#" style="float:left;" table_id="'.$note_order->suspend_note.'" class="change_booking hide">'.$note_order->description.'</a>' ;
                                 }else{
                                    echo '<a href="#" style="float:left;" table_id="'.$note_order->suspend_note.'" class="change_booking">'.$note_order->description.'</a>' ;
                                 }
							if ($this->Owner || $GP->close_table) {
								echo '|	<a href="#" cu_id="'.$note_order->id.'" broom="'.$note_order->note_id.'" class="sus-delete"><i class="fa fa-trash-o"></i></a>';
							}
                                echo '<a href="#" table_id="'.$note_order->suspend_note.'" cu_id="'.$note_order->id.'" class="r_change"> <i class="fa fa-random"></i></a>';
							echo '</div>';
						}else{
                            echo '<div class="sub_avail">';
                                 echo '<span style="float:right;padding-right:5px;text-transform: uppercase;" class="people_qty" broom="'.$note_order->note_id.'"> <i class="fa fa-users"></i></span>' ;
                                echo '<a href="#" style="float:left;" table_id="'.$note_order->suspend_note.'" class="change_booking">'.$note_order->description.'</a>' ;
							if(!$note_order->booking){
								echo '<span style="float:right;padding-right:5px;text-transform: uppercase;" class="booking" broom="'.$note_order->note_id.'">Booking</span>' ;
						
							}else{
								
								echo '<span style="float:right;padding-right:5px;text-transform: uppercase;" class="cancel_booking" broom="'.$note_order->note_id.'">Cancel</span>' ;
							}
							echo ' </div>';
                        }
					echo '</div>';
					$i++;
				}
				}
				?>
				</div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="payModalLabel"><?=lang('finalize_sale');?></h4>
            </div>
            <div class="modal-body" id="payment_content">
                <div class="row">
                    <div class="col-md-10 col-sm-9">
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="form-group">
                                <?=lang("biller", "biller");?>
                                <?php
                                    foreach ($billers as $biller) {
                                        $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                        $bl[$biller->id] = $btest;
                                        $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                        if ($biller->id == $pos_settings->default_biller) {
                                            $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                ?>
                            </div>
                        <?php } else {
                                $biller_input = array(
                                    'type' => 'hidden',
                                    'name' => 'biller',
                                    'id' => 'posbiller',
                                    'value' => $this->session->userdata('biller_id'),
                                );
                                echo form_input($biller_input);
                                foreach ($billers as $biller) {
                                    $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                    $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                    if ($biller->id == $this->session->userdata('biller_id')) {
                                        $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                    }
                                }
                            }
                        ?>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <?=form_textarea('sale_note', '', 'id="sale_note" class="form-control kb-text skip" style="height: 50px;" placeholder="' . lang('sale_note') . '" maxlength="250"');?>
                                </div>
                                <div class="col-sm-6">
                                    <?=form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 50px;" placeholder="' . lang('staff_note') . '" maxlength="250"');?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfir"></div>
                        <div id="payments">
							<table class="table table-condensed table-striped" style="font-size: 1.2em; font-weight: bold; margin-bottom: 0;">
								<tbody>
									<tr>
										<th width="33%" style="text-align:left;"><?= lang("currency"); ?></th>
										<th width="33%" style="text-align:center;"><div id="button_kh__" class="btn-info currency button_kh"><?= lang("KH"); ?></div></th>
										<th width="33%" style="text-align:center;"><div id="button_en__" class="btn-info currency button_en"><?= lang("USD"); ?></div></th>
									</tr>
									<tr>
										<td><?=lang("total_payable");?></td>
										<td>
											<div class="paid_kh">
											<span id="twt">0.00</span>
											</div>
										</td>
										<td>
											<div class="paid_en">
											<span id="twt_en">0.00</span>
											</div>
										</td>
									</tr>
									<tr>
										<td><?= lang("paid")?></td>
										<td> 
                                                <input currency="kh" name="amount[]" type="text" id="amount_1"
                                                       class="pa form-control kb-pad1 amount"/>
										</td>
										<td>
											<input currency="en" name="other_cur_paid[]" type="text" id="amount_2"
                                                       class="pa_en form-control kb-pad2 amount_en"/>
										</td>
									</tr>
									<tr>
										<td><?=lang("balance");?></td>
										<td>
											<div class="paid_kh">
												<span id="balance">0.00</span></td>
											</div>
										<!--<input type="text" name="kh_currenncy" value="kh" id="kh_currenncy">-->
										<td>
											<div class="paid_en">
											<span id="balance_en">0.00</span>
									
											</div>
						
								</tbody>
							</table>
                            <div class="well well-sm well_1">
                                <div class="payment">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group gc_1" style="display: none;">
                                                <?=lang("gift_card_no", "gift_card_no_1");?>
                                                <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1"
                                                       class="pa form-control kb-pad gift_card_no"/>
                                                <div id="gc_details_1"></div>
                                            </div>
                                            <div class="pcc_1" style="display:none;">
                                                <div class="form-group">
                                                    <input type="text" id="swipe_1" class="form-control swipe"
                                                           placeholder="<?=lang('swipe')?>"/>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input name="cc_no[]" type="text" id="pcc_no_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cc_no')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input name="cc_holer[]" type="text" id="pcc_holder_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cc_holder')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <select name="cc_type[]" id="pcc_type_1"
                                                                    class="form-control pcc_type"
                                                                    placeholder="<?=lang('card_type')?>">
                                                                <option value="Visa"><?=lang("Visa");?></option>
                                                                <option
                                                                    value="MasterCard"><?=lang("MasterCard");?></option>
                                                                <option value="Amex"><?=lang("Amex");?></option>
                                                                <option
                                                                    value="Discover"><?=lang("Discover");?></option>
                                                            </select>
                                                            <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?=lang('card_type')?>" />-->
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input name="cc_month[]" type="text" id="pcc_month_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('month')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input name="cc_year" type="text" id="pcc_year_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('year')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input name="cc_cvv2" type="text" id="pcc_cvv2_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cvv2')?>"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<?=lang("paying_by", "paid_by_1");?>
														<select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
															<?= $this->bpas->paid_opts(); ?>
															<?=$pos_settings->paypal_pro ? '<option value="ppp">' . lang("paypal_pro") . '</option>' : '';?>
															<?=$pos_settings->stripe ? '<option value="stripe">' . lang("stripe") . '</option>' : '';?>
															<?=$pos_settings->authorize ? '<option value="authorize">' . lang("authorize") . '</option>' : '';?>
														</select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="pcheque_1" style="display:none;">
														<div class="form-group"><?=lang("cheque_no", "cheque_no_1");?>
															<input name="cheque_no[]" type="text" id="cheque_no_1"
																   class="form-control cheque_no"/>
														</div>
													</div>
													<div class="form-group">
														<?=lang('payment_note', 'payment_note');?>
														<textarea name="payment_note[]" id="payment_note_1"
																  class="pa form-control kb-text payment_note"></textarea>
													</div>
												</div>
											</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="multi-payment"></div>
                    <!--    <button type="button" class="btn btn-primary col-md-12 addButton"><i
                                class="fa fa-plus"></i> <?php //lang('add_more_payments')?></button>-->
                        <div style="clear:both; height:15px;"></div>
                        <div class="font16">
                            <table class="table table-condensed table-striped" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <td width="25%"><?=lang("total_items");?></td>
                                    <td width="25%" class="text-right"><span id="item_count">0.00</span></td>
                                    <td><?=lang("total_paying");?></td>
                                    <td class="text-right"><span id="total_paying">0.00</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-3 text-center">
                        <span style="font-size: 1.2em; font-weight: bold;"><?=lang('quick_cash');?></span>
                        <div class="btn-group btn-group-vertical">
                            <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable">0.00
                            </button>
                            <?php
                                foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                    echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . $cash_note_amount . '</button>';
                                }
                            ?>
                            <button type="button" class="btn btn-lg btn-danger"
                                    id="clear-cash-notes"><?=lang('clear');?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-block btn-lg btn-primary" id="submit-sale"><?=lang('submit');?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="form-group">
                    <?= lang('comment', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control" id="icomment" style="height:80px;"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php
                    $opts = array(0 => lang('no'), 1 => lang('yes'));
                    ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?=lang('product_tax')?></label>
                            <div class="col-sm-8">
                                <?php
                                    $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?=lang('serial_no')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" id="pserial">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?=lang('quantity')?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?=lang('product_option')?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?=lang('product_discount')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?=lang('unit_price')?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pprice" >
                        </div>
                    </div>
                    <table class="table table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?=lang('sell_gift_card');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('enter_info');?></p>
                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?=lang("card_no", "gccard_no");?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                            <a href="#" id="genNo"><i class="fa fa-cogs"></i></a>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?=lang('gift_card')?>" id="gcname"/>
                <div class="form-group">
                    <?=lang("value", "gcvalue");?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("price", "gcprice");?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("customer", "gccustomer");?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("expiry_date", "gcexpiry");?>
                    <?php echo form_input('gcexpiry', $this->bpas->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?=lang('sell_gift_card')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('add_product_manually')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?=lang('product_code')?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?=lang('product_name')?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?=lang('product_tax')?> *</label>
                            <div class="col-sm-8">
                                <?php
                                    $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?=lang('quantity')?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?=lang('product_discount')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mdiscount">
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?=lang('unit_price')?> *</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mprice">
                        </div>
                    </div>
                    <table class="table table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="sckModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span>
                </button>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('shortcut_keys')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <table class="table table-striped table-condensed table-hover"
                       style="margin-bottom: 0px;">
                    <thead>
                    <tr>
                        <th><?=lang('shortcut_keys')?></th>
                        <th><?=lang('actions')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?=$pos_settings->focus_add_item?></td>
                        <td><?=lang('focus_add_item')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_manual_product?></td>
                        <td><?=lang('add_manual_product')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->customer_selection?></td>
                        <td><?=lang('customer_selection')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_customer?></td>
                        <td><?=lang('add_customer')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_category_slider?></td>
                        <td><?=lang('toggle_category_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_subcategory_slider?></td>
                        <td><?=lang('toggle_subcategory_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->cancel_sale?></td>
                        <td><?=lang('cancel_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->suspend_sale?></td>
                        <td><?=lang('suspend_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->print_items_list?></td>
                        <td><?=lang('print_items_list')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->finalize_sale?></td>
                        <td><?=lang('finalize_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->today_sale?></td>
                        <td><?=lang('today_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->open_hold_bills?></td>
                        <td><?=lang('open_hold_bills')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->close_register?></td>
                        <td><?=lang('close_register')?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="dsModalLabel"><?=lang('edit_order_discount');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("order_discount", "order_discount_input");?>
                    <?php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderDiscount" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="sModal" tabindex="-1" role="dialog" aria-labelledby="sModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="sModalLabel"><?=lang('shipping');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("shipping", "shipping_input");?>
                    <?php echo form_input('shipping_input', '', 'class="form-control kb-pad" id="shipping_input"'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="updateShipping" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="txModal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="txModalLabel"><?=lang('edit_order_tax');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("order_tax", "order_tax_input");?>
<?php
    $tr[""] = "";
    foreach ($tax_rates as $tax) {
        $tr[$tax->id] = $tax->name;
    }
    echo form_dropdown('order_tax_input', $tr, "", 'id="order_tax_input" class="form-control pos-input-tip" style="width:100%;"');
?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderTax" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="susModalLabel"><?=lang('suspend_sale');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('type_reference_note');?></p>
                <div class="form-group">
                    <?=lang("reference_note", "reference_note");?>
                    <?= form_input('reference_note', (!empty($reference_note) ? $reference_note : ''), 'class="form-control kb-text" id="reference_note"'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="suspend_sale" class="btn btn-primary"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="changesusModal" tabindex="-1" role="dialog" aria-labelledby="changeg_susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="changeg_susModalLabel"><?=lang('Move Room');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('Please type room and submit to suspend this sale');?></p>
                <div class="form-group">
                    <?=lang("reference_note", "select");?>
					<?php 
					echo '<select class="form-control" name="change_reference_note" id="change_reference_note" required>';
						echo '<option value="">Please choose available Room</option>';
						foreach ($available_room as $noted) {
							echo '<option value="'.$noted->note_id.'">'.$noted->name.'</option>';
						}
					echo '<select>';
					?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="change_suspend_sale" class="btn btn-primary"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div id="order_tbl"><span id="order_span"></span>
    <table id="order-table" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
</div>
<div id="bill_tbl"><span id="bill_span"></span>
    <table id="bill-table" width="100%" class="prT table table-striped" style="margin-bottom:0;"></table>
    <table id="bill-total-table" class="prT table" style="margin-bottom:0;" width="100%">
	</table>
    <span id="bill_footer"></span>
</div>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2"
     aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code);?>
<script type="text/javascript">
setInterval(function() {
  location.reload();
}, 36000); 
$("#poscustomer").on('change', function (event) {
	location.reload();
});
$("#amount_1,#amount_2").keydown(function(event) {
	// Allow: backspace, delete, tab and escape
	if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || 
		 // Allow: Ctrl+A
		(event.keyCode == 65 && event.ctrlKey === true) || 
		 // Allow: home, end, left, right
		(event.keyCode >= 35 && event.keyCode <= 39)) {
			 // let it happen, don't do anything
			 return;
	}else {
		// Ensure that it is a number and stop the keypress
		if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
			event.preventDefault(); 
		}   
	}
});
	
var site = <?=json_encode(array('url' => base_url(), 'base_url' => admin_url('/'), 'assets' => $assets, 'settings' => $Settings, 'dateFormats' => $dateFormats))?>, pos_settings = <?=json_encode($pos_settings);?>;
var permission = <?= json_encode($GP); ?>;
var lang = {
    unexpected_value: '<?=lang('unexpected_value');?>',
    select_above: '<?=lang('select_above');?>',
    r_u_sure: '<?=lang('r_u_sure');?>',
    bill: '<?=lang('bill');?>',
    order: '<?=lang('order');?>',
    total: '<?=lang('total');?>',
    items: '<?=lang('items');?>',
    discount: '<?=lang('discount');?>',
    order_tax: '<?=lang('order_tax');?>',
    grand_total: '<?=lang('grand_total');?>',
    total_payable: '<?=lang('total_payable');?>',
    rounding: '<?=lang('rounding');?>',
    merchant_copy: '<?=lang('merchant_copy');?>'
};
</script>
<script type="text/javascript">
	
    var product_variant = 0, shipping = 0, p_page = 0, per_page = 0, tcp = "<?=$tcp?>", pro_limit = <?= $pos_settings->pro_limit; ?>,
        brand_id = 0, obrand_id = 0, cat_id = "<?=$pos_settings->default_category?>", ocat_id = "<?=$pos_settings->default_category?>", sub_cat_id = 0, osub_cat_id,
        count = 1, an = 1, DT = <?=$Settings->default_tax_rate?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, total_paid = 0, grand_total = 0,
        KB = <?=$pos_settings->keyboard?>, tax_rates =<?php echo json_encode($tax_rates); ?>;
    var protect_delete = <?php if (!$Owner && !$Admin) {echo $pos_settings->pin_code ? '1' : '0';} else {echo '0';} ?>, billers = <?= json_encode($posbillers); ?>, biller = <?= json_encode($posbiller); ?>;
    var username = '<?=$this->session->userdata('username');?>', order_data = '', bill_data = '';
    var user_detail="user addresss";
	function widthFunctions(e) {
        var wh = $(window).height(),
            lth = $('#left-top').height(),
            lbh = $('#left-bottom').height();
        $('#item-list').css("height", wh - 140);
        $('#item-list').css("min-height", 515);
        $('#left-middle').css("height", wh - lth - lbh - 102);
        $('#left-middle').css("min-height", 278);
        $('#product-list').css("height", wh - lth - lbh - 107);
        $('#product-list').css("min-height", 278);
    }
    $(window).bind("resize", widthFunctions);
        $(document).ready(function () {
            $('.sus_sale').on('click', function (e) {
                var sid = $(this).attr("sid");
                var room = $(this).attr("room");
                var room_name = $(this).attr("room_name");
                var price = $(this).attr("price");
                var discount = $(this).attr("discount");
                if (sid ==""){ 
                    $('#myModal').modal({remote: '<?= admin_url('table/input_customer_amount');?>?sid='+sid+'&room='+room+'&price='+price+'&discount='+discount+''});
                    $('#myModal').modal('show');
                }else{
                    if (count > 1) {
                        window.location.href = "<?=admin_url('pos/index')?>/" + sid; 
                    } else {
                        window.location.href = "<?=admin_url('pos/index')?>/" + sid;
                    }
                }
                return false;
            });
		$('.booking').on('click', function (e) {

            var room = $(this).attr("broom");
            var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Who is booking ?",
                message: '<input id="pos_pin" name="pos_pin" type="text" placeholder="Enter their name please!" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> Submit",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var pos_pin = $('#pos_pin').val();
                            if(pos_pin != "") {
                                $.ajax({
                                    type: "get",
                                    url: "<?=admin_url('table/booking_room');?>",
                                    data: {
                                        pos_pin: pos_pin,
                                        room: room
                                    },
                                    success: function (data) {
                                        location.reload();
                                    }
                                });
                                return false;
                            } 
                        }
                    }
                }
            });

            /*
            $.ajax({
                type: "get",
                url: "<?=admin_url('table/booking_room');?>",
                data: {
                    room: room
                },
                success: function (data) {
                    location.reload();
                }
            });*/
            return false;
        });
        $('.people_qty').on('click', function (e) {

            var room = $(this).attr("broom");
            var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> How many people ?",
                message: '<input id="pos_pin" name="pos_pin" type="number" placeholder="number" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> Submit",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var pos_pin = $('#pos_pin').val();
                            if(pos_pin != "") {

                                $.ajax({
                                    type: "get",
                                    url: "<?=admin_url('table/customer_qty');?>",
                                    data: {
                                        pos_pin: pos_pin,
                                        room: room
                                    },
                                    success: function (data) {
                                        location.reload();
                                    }
                                });
                                return false;
                        }
                        }
                    }
                }
            });
            return false;
        });
        $('.sort_floor').on('click', function (e) {
            var floor = $(this).attr("bfloor");
             $.ajax({
                type: "get",
                url: "<?=admin_url('table/index');?>",
                data: {
                    floor: floor
                },
                success: function (data) {
                    location.reload();
                }
                });
                return false;
        });
        $('.change_booking').on('click', function (e) {
            var table_id = $(this).attr("table_id");
            var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Who is booking ?",
                message: '<input id="pos_pin" name="pos_pin" type="text" placeholder="Enter their name please!" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> Submit",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var pos_pin = $('#pos_pin').val();
                            if(pos_pin != "") {
                                $.ajax({
                                    type: "get",
                                    url: "<?=admin_url('table/booking_room');?>",
                                    data: {
                                        pos_pin: pos_pin,
                                        room: table_id
                                    },
                                    success: function (data) {
                                        location.reload();
                                    }
                                });
                                return false;
                            } 
                        }
                    }
                }
            });
            return false;
        });
		$('.cancel_booking').on('click', function (e) {

            var room = $(this).attr("broom");
			$.ajax({
				type: "get",
				url: "<?=admin_url('table/cancel_booking_room');?>",
				data: {
					room: room
				},
				success: function (data) {
					location.reload();
				}
			});
			return false;
        });
		$('.r_change').on('click', function (e) {
			var sus_id= $(this).attr('cu_id');
            var table_id= $(this).attr('table_id');
            localStorage.setItem('table_id',table_id);
			localStorage.setItem('note_id',sus_id);
			$('#changesusModal').modal();
			return false;
        });
		$('.sus-delete').on('click', function (e) {
            var sus_id= $(this).attr('cu_id');
            var room = $(this).attr("broom");

            var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Pin Code",
                message: '<input id="pos_pin" name="pos_pin" type="password" placeholder="Pin Code" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> OK",
                        className: 'btn-success verify_pin',
                        callback: function() {
                            var pos_pin = md5($('#pos_pin').val());
                            if (pos_pin == pos_settings.pin_code) {
                                $.ajax({
                                    type: "get", 
                                    url: "<?=admin_url('pos/delete');?>/" + sus_id,
                                    dataType: 'json',
                                    data: {
					                room: room
				                    },
                                    success: function(data) {
                                    //  alert('Sucess Cancel...');
                                        location.reload();
                                    }
                                });
                            } else {
                                bootbox.alert('Wrong Pin Code');
                            }
                        },
                    },
                },
            });
			return false;
        });
		
		var kh_rate = localStorage.getItem('exchange_kh');
	
		$("#button_en").click(function(){
			$(".paid_en").removeClass("col_disable");
			 $(this).css("background", "#F0AD4E");
			 $('#amount_2').focus();
			 $('#kh_currenncy').val('');
			 $('#en_currenncy').val('usd');
			/////
			$(".paid_kh").addClass("col_disable");
		//	$("#amount_val_1").val('');
		//	$("#balance_amount_1").val('');
			
			document.getElementById('amount_1').setAttribute('disabled', 'disabled');
			document.getElementById('amount_2').removeAttribute('disabled'); 
		});
		$("#button_kh").click(function(){
			$(".paid_kh").removeClass("col_disable");
			$('#amount_1').focus();
			$('#kh_currenncy').val('khm');
			 $('#en_currenncy').val('');
			/////
			$(".paid_en").addClass("col_disable");
			document.getElementById('amount_1').removeAttribute('disabled'); 
			document.getElementById('amount_2').setAttribute('disabled', 'disabled');
		});
        $('#view-customer').click(function(){
            $('#myModal').modal({remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()});
            $('#myModal').modal('show');
        });
        $('textarea').keydown(function (e) {
            if (e.which == 13) {
               var s = $(this).val();
               $(this).val(s+'\n').focus();
               e.preventDefault();
               return false;
            }
        });
        <?php if ($sid) { ?>
        localStorage.setItem('positems', JSON.stringify(<?=$items;?>));
        <?php } ?>
        <?php if ($oid) { ?>
        localStorage.setItem('positems', JSON.stringify(<?=$items;?>));
        <?php } ?>
<?php if ($this->session->userdata('remove_posls')) {?>
        if (localStorage.getItem('positems')) {
            localStorage.removeItem('positems');
        }
        if (localStorage.getItem('posdiscount')) {
            localStorage.removeItem('posdiscount');
        }
        if (localStorage.getItem('postax2')) {
            localStorage.removeItem('postax2');
        }
        if (localStorage.getItem('posshipping')) {
            localStorage.removeItem('posshipping');
        }
        if (localStorage.getItem('poswarehouse')) {
            localStorage.removeItem('poswarehouse');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('poscustomer')) {
            localStorage.removeItem('poscustomer');
        }
        if (localStorage.getItem('posbiller')) {
            localStorage.removeItem('posbiller');
        }
        if (localStorage.getItem('poscurrency')) {
            localStorage.removeItem('poscurrency');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('staffnote')) {
            localStorage.removeItem('staffnote');
        }
        <?php $this->bpas->unset_data('remove_posls');}
        ?>
        widthFunctions();
        <?php if ($suspend_sale) {?>
        localStorage.setItem('postax2', '<?=$suspend_sale->order_tax_id;?>');
        localStorage.setItem('posdiscount', '<?=$suspend_sale->order_discount_id;?>');
        localStorage.setItem('poswarehouse', '<?=$suspend_sale->warehouse_id;?>');
        localStorage.setItem('poscustomer', '<?=$suspend_sale->customer_id;?>');
        localStorage.setItem('posbiller', '<?=$suspend_sale->biller_id;?>');
        localStorage.setItem('posshipping', '<?=$suspend_sale->shipping;?>');
        <?php }
        ?>
        <?php if ($old_sale) {?>
        localStorage.setItem('postax2', '<?=$old_sale->order_tax_id;?>');
        localStorage.setItem('posdiscount', '<?=$old_sale->order_discount_id;?>');
        localStorage.setItem('poswarehouse', '<?=$old_sale->warehouse_id;?>');
        localStorage.setItem('poscustomer', '<?=$old_sale->customer_id;?>');
        localStorage.setItem('posbiller', '<?=$old_sale->biller_id;?>');
        localStorage.setItem('posshipping', '<?=$old_sale->shipping;?>');
        <?php }
        ?>
<?php if ($this->input->get('customer')) {?>
        if (!localStorage.getItem('positems')) {
            localStorage.setItem('poscustomer', <?=$this->input->get('customer');?>);
        } else if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?=$customer->id;?>);
        }
        <?php } else {?>
        if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?=$customer->id;?>);
        }
        <?php }
        ?>
        if (!localStorage.getItem('postax2')) {
            localStorage.setItem('postax2', <?=$Settings->default_tax_rate2;?>);
        }
        $('.select').select2({minimumResultsForSearch: 7});
       /* var customers = [{
             id: <?=$customer->id;?>,
             text: '<?=$customer->company == '-' ? $customer->name : $customer->company;?>'
         }];*/
        $('#poscustomer').val(localStorage.getItem('poscustomer')).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?=admin_url('customers/getCustomer')?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
					//	$("#customer_detail").val(c_detail);
						localStorage.setItem('customer_adress', data[0].address);
						localStorage.setItem('customer_phone', data[0].phone);
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        if (KB) {
            display_keyboards();
            var result = false, sct = '';
            $('#poscustomer').on('select2-opening', function () {
                sct = '';
                $('.select2-input').addClass('kb-text');
                display_keyboards();
                $('.select2-input').bind('change.keyboard', function (e, keyboard, el) {
                    if (el && el.value != '' && el.value.length > 0 && sct != el.value) {
                        sct = el.value;
                    }
                    if(!el && sct.length > 0) {
                        $('.select2-input').addClass('select2-active');
                        setTimeout(function() {
                            $.ajax({
                                type: "get",
                                async: false,
                                url: "<?=admin_url('customers/suggestions')?>/?term=" + sct,
                                dataType: "json",
                                success: function (res) {
                                    if (res.results != null) {
                                        $('#poscustomer').select2({data: res}).select2('open');
                                        $('.select2-input').removeClass('select2-active');
                                    } else {
                                        // bootbox.alert('no_match_found');
                                        $('#poscustomer').select2('close');
                                        $('#test').click();
                                    }
                                }
                            });
                        }, 500);
                    }
                });
            });
            $('#poscustomer').on('select2-close', function () {
                $('.select2-input').removeClass('kb-text');
                $('#test').click();
                $('select, .select').select2('destroy');
                $('select, .select').select2({minimumResultsForSearch: 7});
            });
            $(document).bind('click', '#test', function () {
                var kb = $('#test').keyboard().getkeyboard();
                kb.close();
            });
        }
        $(document).on('change', '#posbiller', function () {
            var sb = $(this).val();
            $.each(billers, function () {
                if(this.id == sb) {
                    biller = this;
                }
            });
            $('#biller').val(sb);
        });
        <?php for ($i = 1; $i <= 5; $i++) {?>
        $('#paymentModal').on('change', '#amount_<?=$i?>', function (e) {
			var kh_rate = localStorage.getItem('exchange_kh');
			<?php if($i !=2){ ?>
			$('#amount_val_<?=$i?>').val($(this).val());
			<?php }else{ ?>
			$('#amount_val_<?=$i?>').val($(this).val() * kh_rate);
			<?php }	?>
			
        });
        $('#paymentModal').on('blur', '#amount_<?=$i?>', function (e) {
			var kh_rate = localStorage.getItem('exchange_kh');
			<?php if($i !=2){ ?>
			$('#amount_val_<?=$i?>').val($(this).val());
			<?php }else{ ?>
			$('#amount_val_<?=$i?>').val($(this).val() * kh_rate);
			<?php }	?>
			
        });
        $('#paymentModal').on('select2-close', '#paid_by_<?=$i?>', function (e) {
            $('#paid_by_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_no_<?=$i?>', function (e) {
            $('#cc_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_holder_<?=$i?>', function (e) {
            $('#cc_holder_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#gift_card_no_<?=$i?>', function (e) {
            $('#paying_gift_card_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_month_<?=$i?>', function (e) {
            $('#cc_month_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_year_<?=$i?>', function (e) {
            $('#cc_year_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_type_<?=$i?>', function (e) {
            $('#cc_type_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_cvv2_<?=$i?>', function (e) {
            $('#cc_cvv2_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cheque_no_<?=$i?>', function (e) {
            $('#cheque_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#payment_note_<?=$i?>', function (e) {
            $('#payment_note_val_<?=$i?>').val($(this).val());
        });
        <?php }
        ?>
        $('#payment').click(function () {
			var kh_rate = localStorage.getItem('exchange_kh');
            <?php if ($sid) {?>
            suspend = $('<span></span>');
            suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />');
            suspend.appendTo("#hidesuspend");
            <?php }
            ?>
            var twt = formatDecimal((total + invoice_tax) - order_discount + shipping);
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            gtotal = formatDecimal(twt);
            <?php if ($pos_settings->rounding) {?>
            round_total = roundNumber(gtotal, <?=$pos_settings->rounding?>);
            var rounding = formatDecimal(0 - (gtotal - round_total));
            $('#twt').text('៛ '+formatMoney(round_total) + ' (' + formatMoney(rounding) + ')');
            $('#quick-payable').text(round_total);
			//-------convert KH to EN--------
			var gtotal_kh = parseFloat(gtotal / kh_rate);
			$('#twt_en').text('$'+gtotal_kh);
            <?php } else {?>
            $('#twt').text('៛ '+formatMoney(gtotal));
            $('#quick-payable').text(gtotal);
			//-------convert KH to EN--------
			var gtotal_kh = parseFloat(gtotal / kh_rate);
			$('#twt_en').text('$'+gtotal_kh);
            <?php }
            ?>
            $('#item_count').text(count - 1);
            $('#paymentModal').appendTo("body").modal('show');
            $('#amount_1').focus();
        });
        $('#paymentModal').on('show.bs.modal', function(e) {
            $('#submit-sale').text('<?=lang('submit');?>').attr('disabled', false);
        });
        $('#paymentModal').on('shown.bs.modal', function(e) {
            $('#amount_1').focus().val(0);
        //  $('#quick-payable').click();
        });
        var pi = 'amount_1', pa = 2;
        $(document).on('click', '.quick-cash', function () {
            if ($('#quick-payable').find('span.badge').length) {
                $('#clear-cash-notes').click();
            }
            var $quick_cash = $(this);
            var amt = $quick_cash.contents().filter(function () {
                return this.nodeType == 3;
            }).text();
            var th = ',';
            var $pi = $('#' + pi);
            amt = formatDecimal(amt.split(th).join("")) * 1 + $pi.val() * 1;
            $pi.val(formatDecimal(amt)).focus();
            var note_count = $quick_cash.find('span');
            if (note_count.length == 0) {
                $quick_cash.append('<span class="badge">1</span>');
            } else {
                note_count.text(parseInt(note_count.text()) + 1);
            }
        });
        $(document).on('click', '#quick-payable', function () {
            $('#clear-cash-notes').click();
            $(this).append('<span class="badge">1</span>');
            $('#amount_1').val(grand_total);
        });
        $(document).on('click', '#clear-cash-notes', function () {
            $('.quick-cash').find('.badge').remove();
            $('#' + pi).val('0').focus();
        });
        $(document).on('change', '.gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            var payid = $(this).attr('id'),
                id = payid.substr(payid.length - 1);
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#poscustomer').val()) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            $('#gc_details_' + id).html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no_' + id).parent('.form-group').removeClass('has-error');
                            //calculateTotals();
                            $('#amount_' + id).val(gtotal >= data.balance ? data.balance : gtotal).focus();
                        }
                    }
                });
            }
        });
        $(document).on('click', '.addButton', function () {
            if (pa <= 5) {
                $('#paid_by_1, #pcc_type_1').select2('destroy');
                var phtml = $('#payments').html(),
                    update_html = phtml.replace(/_1/g, '_' + pa);
                pi = 'amount_' + pa;
                $('#multi-payment').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' + update_html);
                $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({minimumResultsForSearch: 7});
                read_card();
                pa++;
            } else {
                bootbox.alert('<?=lang('max_reached')?>');
                return false;
            }
            if (KB) { display_keyboards(); }
            $('#paymentModal').css('overflow-y', 'scroll');
        });
        $(document).on('click', '.close-payment', function () {
            $(this).next().remove();
            $(this).remove();
            pa--;
        });
        $(document).on('focus', '.amount', function () {
            pi = $(this).attr('id');
            calculateTotals();
        }).on('blur', '.amount', function () {
            calculateTotals();
			
        });
		$(document).on('change', '#amount_1', function () {
			var kh_rate = localStorage.getItem('exchange_kh');
			var gtotal_en = parseFloat(gtotal / kh_rate );
			var amount_1=$("#amount_1").val();
			var amount_2=$("#amount_2").val() * kh_rate;
			var bal=parseInt(amount_1) + parseInt(amount_2);
			$('#balance').text(bal - gtotal);
			$('#balance_en').text(((bal/kh_rate) - gtotal_en).toFixed(3));
		});
		$(document).on('change', '.amount_en', function () {
			var curr=$(this).attr('currency');
			var total_paying = 0;
            pi = $(this).attr('id');
            var kh_rate = localStorage.getItem('exchange_kh');
			var gtotal_kh = parseFloat(gtotal / kh_rate );
			var ia_en = $(".amount_en");
			$.each(ia_en, function (i) {
				var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
				total_paying += parseFloat(this_amount);
			});
			$('#total_paying').text(formatMoney(total_paying));
			//------
			var total_en=(gtotal_kh - total_paying).toFixed(3);	
			//-----text---
			var gtotal_en = parseFloat(gtotal / kh_rate );
			var amount_1=$("#amount_1").val();
			var amount_2=$("#amount_2").val() * kh_rate;
			var bal=parseInt(amount_1) + parseInt(amount_2);
			$('#balance').text(bal - gtotal);
			$('#balance_en').text(((bal/kh_rate) - gtotal_en).toFixed(3));
			//--/text----
        }).on('blur', '.amount_en', function () {
			var kh_rate = localStorage.getItem('exchange_kh');
			var gtotal_kh = parseFloat(gtotal / kh_rate );
			var total_paying = 0;
			
			var ia_en = $(".amount_en");
			$.each(ia_en, function (i) {
				var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
				total_paying += parseFloat(this_amount);
			});
			$('#total_paying').text(formatMoney(total_paying));
			//------
			var total_en=(total_paying - gtotal_kh).toFixed(3);
		//	$('#balance_en').text(total_en);
			//
			var amount_1=$("#amount_1").val();
			var amount_2=$("#amount_2").val() * kh_rate;
			var final_amount=parseInt(amount_1) + parseInt(amount_2);
			$('#amount_val_1').val(final_amount);
			$('#balance_amount_1').val(final_amount - gtotal);
			$('#paid_amount_val_1').val(amount_1+','+ $("#amount_2").val());
			//
			total_paid = total_paying  * kh_rate;
			grand_total = gtotal  * kh_rate;
			//-----text---
			var gtotal_en = parseFloat(gtotal / kh_rate );
			var amount_1=$("#amount_1").val();
			var amount_2=$("#amount_2").val() * kh_rate;
			var bal=parseInt(amount_1) + parseInt(amount_2);
			$('#balance').text(bal - gtotal);
			$('#balance_en').text(((bal/kh_rate) - gtotal_en).toFixed(3));
			//--/text----
        });
        function calculateTotals() {
			var currency=$(this).attr('currency');
			//	alert(currency);
			var kh_currenncy= $("#kh_currenncy").val();
			var total_paying = 0;
			var ia = $(".amount");
			$.each(ia, function (i) {
				var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
				total_paying += parseFloat(this_amount);
			});
			$('#total_paying').text(formatMoney(total_paying));
			<?php if ($pos_settings->rounding) {?>
		//	$('#balance').text(formatMoney(total_paying - round_total));
		//	$('#balance_' + pi).val(formatDecimal(total_paying - round_total));
					//
			var amount_1=$("#amount_1").val();
			var amount_2=$("#amount_2").val() * kh_rate;
			var final_amount=parseInt(amount_1) + parseInt(amount_2);
			$('#amount_val_1').val(final_amount);
			$('#balance_amount_1').val(final_amount - gtotal);
			$('#paid_amount_val_1').val(amount_1+','+ $("#amount_2").val());
			//
			total_paid = total_paying;
			grand_total = round_total;
			
			<?php } else {?>
		//	$('#balance').text(formatMoney(total_paying - gtotal));
		//	$('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
			//
			var amount_1=$("#amount_1").val();
			var amount_2=$("#amount_2").val() * kh_rate;
			var final_amount=parseInt(amount_1) + parseInt(amount_2);
			$('#amount_val_1').val(final_amount);
			$('#balance_amount_1').val(final_amount - gtotal);
			$('#paid_amount_val_1').val(amount_1+','+ $("#amount_2").val());
			//
			total_paid = total_paying;
			grand_total = gtotal;
			<?php }?>
        }
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#poscustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    //response('');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?=admin_url('sales/suggestions');?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#poswarehouse").val(),
                        customer_id: $("#poscustomer").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?=lang('no_match_found')?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?=lang('no_match_found')?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?=lang('no_match_found')?>');
                }
            }
        });
        <?php if ($pos_settings->tooltips) {echo '$(".pos-tip").tooltip();';}
        ?>
        // $('#posTable').stickyTableHeaders({fixedOffset: $('#product-list')});
        $('#posTable').stickyTableHeaders({scrollableArea: $('#product-list')});
        $('#product-list, #category-list, #subcategory-list, #brands-list').perfectScrollbar({suppressScrollX: true});
        $('select, .select').select2({minimumResultsForSearch: 7});
        $(document).on('click', '.product', function (e) {
            $('#modal-loading').show();
            code = $(this).val(),
                wh = $('#poswarehouse').val(),
                cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?=admin_url('pos/getProductDataByCode')?>",
                data: {code: code, warehouse_id: wh, customer_id: cu},
                dataType: "json",
                success: function (data) {
                    e.preventDefault();
                    if (data !== null) {
                        add_invoice_item(data);
                        $('#modal-loading').hide();
                    } else {
                        bootbox.alert('<?=lang('no_match_found')?>');
                        $('#modal-loading').hide();
                    }
                }
            });
        });
        $('#next').click(function () {
            if (p_page == 'n') {
                p_page = 0
            }
            p_page = p_page + pro_limit;
            if (tcp >= pro_limit && p_page < tcp) {
                $('#modal-loading').show();
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }
                }).done(function () {
                    $('#modal-loading').hide();
                });
            } else {
                p_page = p_page - pro_limit;
            }
        });
        $('#previous').click(function () {
            if (p_page == 'n') {
                p_page = 0;
            }
            if (p_page != 0) {
                $('#modal-loading').show();
                p_page = p_page - pro_limit;
                if (p_page == 0) {
                    p_page = 'n'
                }
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }
                }).done(function () {
                    $('#modal-loading').hide();
                });
            }
        });
        $(document).on('change', '.paid_by', function () {
            $('#clear-cash-notes').click();
            $('#amount_1').val(grand_total);
            var p_val = $(this).val(),
                id = $(this).attr('id'),
                pa_no = id.substr(id.length - 1);
            $('#rpaidby').val(p_val);
            if (p_val == 'cash' || p_val == 'other') {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).show();
                $('#amount_' + pa_no).focus();
            } else if (p_val == 'CC' || p_val == 'stripe' || p_val == 'ppp' || p_val == 'authorize') {
                $('.pcheque_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcc_' + pa_no).show();
                $('#swipe_' + pa_no).focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcheque_' + pa_no).show();
                $('#cheque_no_' + pa_no).focus();
            } else {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
            }
            if (p_val == 'gift_card') {
                $('.gc_' + pa_no).show();
                $('.ngc_' + pa_no).hide();
                $('#gift_card_no_' + pa_no).focus();
            } else {
                $('.ngc_' + pa_no).show();
                $('.gc_' + pa_no).hide();
                $('#gc_details_' + pa_no).html('');
            }
        });
        $(document).on('click', '#submit-sale', function () {
			
        //  if (total_paid == 0 || total_paid < grand_total) {
            if (total_paid == 0) {
                bootbox.confirm("<?=lang('paid_l_t_payable');?>", function (res) {
                    if (res == true) {
                        $('#pos_note').val(localStorage.getItem('posnote'));
                        $('#staff_note').val(localStorage.getItem('staffnote'));
                        $('#submit-sale').text('<?=lang('loading');?>').attr('disabled', true);
                        $('#pos-sale-form').submit();
                    }
                });
                return false;
            } else {
                $('#pos_note').val(localStorage.getItem('posnote'));
                $('#staff_note').val(localStorage.getItem('staffnote'));
                $(this).text('<?=lang('loading');?>').attr('disabled', true);
                $('#pos-sale-form').submit();
            }
        });
        $('#suspend').click(function () {
            if (count <= 1) {
                bootbox.alert('<?=lang('x_suspend');?>');
                return false;
            } else {
                $('#susModal').modal();
            }
        });
        $('#suspend_sale').click(function () {
            ref = $('#reference_note').val();
            if (!ref || ref == '') {
                bootbox.alert('<?=lang('type_reference_note');?>');
                return false;
            } else {
                suspend = $('<span></span>');
                <?php if ($sid) {?>
                suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                <?php } else {?>
                suspend.html('<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                <?php }
                ?>
                suspend.appendTo("#hidesuspend");
                $('#total_items').val(count - 1);
                $('#pos-sale-form').submit();
            }
        });
		$('#change_suspend_sale').click(function () {
            new_table = $('#change_reference_note').val();
            var note_id = localStorage.getItem('note_id');
            var old_table = localStorage.getItem('table_id');

            if(new_table == old_table){
                bootbox.alert('<?=lang('The same room');?>');
                return false;
            }else if (!new_table || new_table == '') {
                bootbox.alert('<?=lang('type_reference_note');?>');
                return false;
            } else {
                bootbox.confirm("<?= $this->lang->line('are_you_sure?') ?>", function (gotit) {
                    if (gotit == true) {
                        $.ajax({
                            type: "get",
                            url: "<?=admin_url('table/change_room');?>",
                            data: {
                                note_id: note_id,
                                new_table: new_table,
                                old_table: old_table
                            },
                            success: function (data) {
                                bootbox.alert('<?=lang('room_has_been_changed');?>');
                                location.reload();
                            }
                        });
                    }
                });
                return false;
            }
        });
    });
    $(document).ready(function () {
        $('#print_order').click(function () {
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if ($pos_settings->remote_printing != 1) { ?>
                printOrder();
            <?php } else { ?>
                Popup($('#order_tbl').html());
            <?php } ?>
        });
        $('#print_bill').click(function () {
			var cus_id=$("#poscustomer").val();
		//	alert(cus_id);
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if ($pos_settings->remote_printing != 1) { ?>
                printBill();
            <?php } else { ?>
                Popup($('#bill_tbl').html());
            <?php } ?>
        });
    });
    $(function () {
        $(".alert").effect("shake");
        setTimeout(function () {
            $(".alert").hide('blind', {}, 500)
        }, 15000);
        <?php if ($pos_settings->display_time) {?>
        var now = new moment();
        $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        setInterval(function () {
            var now = new moment();
            $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        }, 1000);
        <?php }
        ?>
    });
    <?php if ($pos_settings->remote_printing == 1) { ?>
    function Popup(data) {
        var mywindow = window.open('', 'sma_pos_print', 'height=500,width=480');
     //   var mywindow = window.open('', '_blank');
        mywindow.document.write('<html><head><title>Print</title>');
        mywindow.document.write('<link rel="stylesheet" href="<?=$assets?>styles/helpers/bootstrap.min.css" type="text/css" />');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');
        mywindow.print();
        mywindow.close();
        return true;
    }
    <?php }
    ?>
</script>
<?php
    $s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
    foreach (lang('select2_lang') as $s2_key => $s2_line) {
        $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
    }
    $s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?=$assets?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/select2.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/custom.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/plugins.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/parse-track-data.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/pos.ajax.js"></script>
<?php
if ( ! $pos_settings->remote_printing) {
    ?>
    <script type="text/javascript">
        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            $.each(order_printers, function() {
                var socket_data = { 'printer': this,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': order_data };
                $.get('<?= admin_url('pos/p/order'); ?>', {data: JSON.stringify(socket_data)});
            });
            return false;
        }
        function printBill() {
            var socket_data = {
                'printer': <?= json_encode($printer); ?>,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': bill_data
            };
            $.get('<?= admin_url('pos/p'); ?>', {data: JSON.stringify(socket_data)});
            return false;
        }
    </script>
    <?php
} elseif ($pos_settings->remote_printing == 2) {
    ?>
    <script src="<?= $assets ?>js/socket.io.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        socket = io.connect('http://localhost:6440', {'reconnection': false});
        function printBill() {
            if (socket.connected) {
                var socket_data = {'printer': <?= json_encode($printer); ?>, 'text': bill_data};
                socket.emit('print-now', socket_data);
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.connected) {
                $.each(order_printers, function() {
                    var socket_data = {'printer': this, 'text': order_data};
                    socket.emit('print-now', socket_data);
                });
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php
} elseif ($pos_settings->remote_printing == 3) {
    ?>
    <script type="text/javascript">
        try {
            socket = new WebSocket('ws://127.0.0.1:6441');
            socket.onopen = function () {
                console.log('Connected');
                return;
            };
            socket.onclose = function () {
                console.log('Not Connected');
                return;
            };
        } catch (e) {
            console.log(e);
        }
        var order_printers = <?= $pos_settings->local_printers ? "''" : json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.readyState == 1) {
                if (order_printers == '') {
                    var socket_data = { 'printer': false, 'order': true,
                    'logo': (biller && biller.logo ? site.url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': order_data };
                    socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
                } else {
					$.each(order_printers, function() {
						var socket_data = { 'printer': this,
						'logo': (biller && biller.logo ? site.url+'assets/uploads/logos/'+biller.logo : ''),
						'text': order_data };
						socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
					});
				}
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
        function printBill() {
            if (socket.readyState == 1) {
                var socket_data = {
                    'printer': <?= $pos_settings->local_printers ? "''" : json_encode($printer); ?>,
                    'logo': (biller && biller.logo ? site.url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': bill_data
                };
                socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php
}
?>
<script type="text/javascript">
$('.sortable_table tbody').sortable({
    containerSelector: 'tr'
});
</script>
<script type="text/javascript" charset="UTF-8"><?=$s2_file_date?></script>
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
<?php
if (isset($print) && !empty($print)) {
    /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */
    include 'remote_printing.php';
}
?>
</body>
</html>