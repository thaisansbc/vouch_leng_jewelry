<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
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
        .table_display{
            width: 145px !important;
            height: 200px !important;
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
                    <li class="dropdown">
                        <a class="btn bblue pos-tip" title="<?=lang('dashboard')?>" data-placement="bottom" href="<?=admin_url('welcome')?>">
                            <i class="fa fa-dashboard"></i>
                        </a>
                    </li>
                    <?php if ($Owner) {?>
                        <li class="dropdown hidden-sm">
                            <a class="btn pos-tip" title="<?=lang('settings')?>" data-placement="bottom" href="<?=admin_url('pos/settings')?>">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </li>
                    <?php }
                    ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn pos-tip" title="<?=lang('calculator')?>" data-placement="bottom" href="#" data-toggle="dropdown">
                            <i class="fa fa-calculator"></i>
                        </a>
                        <ul class="dropdown-menu pull-right calc">
                            <li class="dropdown-content">
                                <span id="inlineCalc"></span>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown hidden-sm">
                        <a class="btn pos-tip" title="<?=lang('shortcuts')?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                            <i class="fa fa-key"></i>
                        </a>
                    </li>
                    <li class="dropdown hidden-xs">
                        <a class="btn bred pos-tip" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
                            <i class="fa fa-eraser"></i>
                        </a>
                    </li>
                   
                </ul>
           
            </div>
        </div>
    </header>
    
    <div id="content">

        <div class="c1">
			<h1 class="title_name text-center">
                <?php echo "Room Display" ;?></h1>
            <div class="pos">
                 <div class="nav navbar-nav pull-left" style="height:600px;">
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
					elseif($note_order->status){
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
							if($note_order->status){
							    $start=date_format($date, 'Y-m-d H:i:s');
								$dteStart = new DateTime($now);
								$dteEnd   = new DateTime($start); 
								 $dteDiff  = $dteStart->diff($dteEnd); 
								 $dtt  = $dteDiff->format("%H:%I:%S");
								echo '<span>&nbsp;Start: '.$dtt.' ['.$note_order->customer_qty.']</span>';
							}elseif($note_order->booking){
								echo '<span>&nbsp;Booked</span>';
							}else{
								echo '<span>&nbsp;Available</span>';
							}
							echo '<div class="table_name text-center">'.$note_order->name.'</div>';
							
							if($note_order->status){
                                    echo '<img alt="" src="'.base_url().'assets/uploads/table/room_close.png">';
	
							}else{
                                    echo '<img alt="" src="'.base_url().'assets/uploads/table/room.png">';
                          
							}
					echo '</div>';
						if($note_order->status){
					
                            echo '<div class="sub_avail">';
                                 echo '<span style="float:right;padding-right:5px;" class="people_qty" broom="'.$note_order->note_id.'"> 1 Day</span>' ;
                            
                                 if($note_order->tmp == 1){
                                    echo '<a href="#" style="float:left;" table_id="'.$note_order->suspend_note.'" class="change_booking hide">'.$note_order->description.'</a>' ;
                                 }else{
                                    echo '<a href="#" style="float:left;" table_id="'.$note_order->suspend_note.'" class="change_booking">'.$note_order->description.'</a>' ;
                                 }
							echo '</div>';
						}else{
                            // var_dump($note_order);exit;
                            echo '<div class="sub_avail">';
                                 echo '<a href="'.admin_url('room/checkin/0/0/').$note_order->note_id.'" style="padding-right:5px;" broom="'.$note_order->note_id.'"> check_in</a>' ;
                                echo '<a href="#" style="float:left;" table_id="'.$note_order->suspend_note.'" class="change_booking">'.$note_order->description.'</a>' ;
							if(!$note_order->booking){
								echo '<span style="float:right;padding-right:5px;" class="booking" broom="'.$note_order->note_id.'">Booking</span>' ;
						
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
    <?php 
        $posbillers=null;
        $posbiller=null;
        if($billers){
            foreach ($billers as $biller) {
                $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                $bl[$biller->id] = $btest;
                $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                if ($biller->id == $pos_settings->default_biller) {
                    $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                }
            }
        }
    ?>
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
				$.ajax({
                    type: "get",
                    url: "<?=admin_url('table/bill_default');?>",
                    data: {
						sid: sid,
						price: price,
						discount: discount,
						room_name: room_name,
						room: room
					},
                    success: function (data) {
						localStorage.setItem('name_room',room_name)
						window.location.href = "<?= admin_url('sales/rent') ?>/" + room;
                    }
                });
					
				 return false;
			}
			
            if (count > 1) {
               // bootbox.confirm("<?= $this->lang->line('leave_alert') ?>", function (gotit) {
                  //  if (gotit == false) {
                     //   return true;
                  //  } else {
                        window.location.href = "<?= admin_url('sales/rent') ?>/" + sid;
                  //  }
                //});
            } else {
                window.location.href = "<?= admin_url('sales/rent') ?>/" + sid;
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