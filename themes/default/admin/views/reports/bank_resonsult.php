<?php
    $start_date = $this->input->post('start_date');
    $end_date = $this->input->post('end_date');
?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('bank_reconciliation'); ?><?php
            if ($this->input->post('start_date')) {
                echo " From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
		</h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
              
                        <li>
                            <a data-target="#myModal" data-toggle="modal" href="javascript:void(0)" id="bank_reconcile" data-action="bank_reconcile">
                                <i class="fa fa-money"></i> <?=lang('reconcile')?>
                            </a>
                        </li>
                    
                    </ul>
                </li>
            </ul>
        </div>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#">
						<i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("billers") ?>"></i>
					</a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
						<li><a href="<?= admin_url('reports/ledger') ?>"><i class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
						<li class="divider"></li>
						<?php
							foreach ($billers as $biller) {
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '>
										<a href="' . admin_url('reports/ledger/0/0/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
							}
						?>
					</ul>
				</li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo admin_form_open("reports/bank_reconcile/".$v_form); ?>
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("account_name"); ?></label>
                                <?php
                                $this->db->select('*')->from('gl_charts');
                                $accOption = $this->db->get()->result();
                                $accountArray = [];
                                foreach ($accOption as $a) {
                                    $accountArray[$a->accountcode] = $a->accountcode . " " . $a->accountname;
                                }
                               // echo form_dropdown('account[]', $accountArray, (isset($v_account) ? $v_account : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("account") . '" multiple="multiple" ');
                                echo form_dropdown('account', $accountArray, (isset($v_account) ? $v_account : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("account") . '" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($start_date) ? $start_date : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($end_date) ? $end_date : ""), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
                        <?php
                            $start_date=str_replace('/','-',$start_date);
                         //   $rep_space_end=str_replace(' ','_',$end);
                            $end_date=str_replace('/','-',$end_date);
                        ?>

                        <input type="hidden" id="sdate" value="<?= (isset($start_date) ? $start_date : "")?>">
                        <input type="hidden" id="edate" value="<?= (isset($end_date) ? $end_date : "")?>">
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>

                <div class="table-scroll">
                    <table cellpadding="0" id="checkboxes" cellspacing="0" border="0" class="table table-hover table-striped table-condensed">
						<thead>
							<tr>
								<th><?= lang('no'); ?></th>
								<th style="width:150px;"><?= lang('biller');?></th>
								<th style="width:150px;"><?= lang('type'); ?></th>
								<th style="width:150px;"><?= lang('date'); ?></th>
								<th style="width:200px;"><?= lang('ref'); ?></th>
								<th style="width:150px;"><?= lang('name');?></th>
								<th style="width:250px;"><?= lang('description'); ?></th>
								<th style="width:50px;"><?= lang('created_by'); ?></th>
								<th style="width:150px;"><?= lang('debit_amount'); ?></th>
								<th style="width:150px;"><?= lang('credit_amount'); ?></th>
								<th style="width:150px;"><?= lang('balance');?></th>
                                <th style="width:150px;"><?= lang('balance_type'); ?></th>						
							</tr>
                        </thead>
						<tbody>
						<?php
     
                            $this->db->select('*')->from('gl_charts');
                            
                            if ($multi_account) {
                                $this->db->where("accountcode IN (".$multi_account.")");
                            }
                            if($have_filter == ''){
                                $this->db->limit(0,0);
                            }
                            $q = $this->db->get();
                            $accounts = $q->result();		
                            foreach($accounts as $account){								
                                $startAmount = $this->db->select('sum(amount) as startAmount')
												   ->from('gl_trans')
												   ->where(
														array(
															'tran_date < '=> $this->bpas->fld($start_date),
															'account_code'=> $account->accountcode
															)
														)->get()->row();
                                						
								$endAccountBalance = 0.00;
                                $glTrans = $this->db->select("
									gl_trans.*,
									(CASE WHEN bpas_gl_trans.amount>0 THEN bpas_gl_trans.amount END ) as am1,
									(CASE WHEN bpas_gl_trans.amount<0 THEN bpas_gl_trans.amount END ) as am2,
									companies.company,
									companies.name,
									users.username")
								->from('gl_trans')
								->join('companies','companies.id=gl_trans.biller_id')
								->join('users', 'users.id = gl_trans.created_by', 'left')
								->order_by('tran_date', 'asc')
								->where('account_code', $account->accountcode);
								
							    if ($start_date) {
                                    $glTrans->where('date(tran_date) >=', $this->bpas->fld($start_date));
                                }
                                if ($end_date) {
                                    $glTrans->where('date(tran_date) <=', $this->bpas->fld($end_date));
                                }
								
								if($biller_id != ""){
									$glTrans->where('gl_trans.biller_id' ,$biller_id);
								}

                                $glTranLists = $glTrans->get()->result();
								
								if($glTranLists) {?>
                                <tr>
                                    <td colspan="4" style="font-weight: bold;"><?= lang("Account"); ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i> <?=$account->accountcode . ' ' .$account->accountname?></td>
									<td colspan="4" style="font-weight: bold;">Begining Account Balance <i class="fa fa-caret-right" aria-hidden="true"></i></td>
									<?php if($startAmount->startAmount > 0) { ?>
										<td class="right"><?= $this->bpas->formatMoney($startAmount->startAmount)?></td>
										<td class="right"></td>
										<td class="right"></td>
									<?php }else { ?>
										<td class="right"></td>
										<td class="right"><?= $this->bpas->formatMoney(abs($startAmount->startAmount))?></td>
										<td class="right"></td>
									<?php } ?>
                                </tr>
                                <?php
								$endAmount = $startAmount->startAmount;
								$endDebitAmount = 0;
								$endCreditAmount = 0;
                                foreach($glTranLists as $gltran)
                                {
									$endAccountBalance += $gltran->amount;
									$endAmount += $gltran->amount;
									//$endDebitAmount += $gltran->am1;
									//$endCreditAmount += $gltran->am2;
                                    ?>
									<tr>
										<td><?= $gltran->tran_no ?></td>
										<td><?= $gltran->company ?></td>
										<td><?= $gltran->tran_type ?></td>
										<td><?= $this->bpas->hrld($gltran->tran_date); ?></td>
										<td><?= $gltran->reference_no ?></td>
										<td><?= ($gltran->tran_type!='JOURNAL'?$gltran->name:$gltran->created_name) ?></td>
										<td><?= $gltran->note ?></td>
										<td><?= $gltran->username ?></td>
										<td class="right"><?= ($gltran->am1 > 0 ? $this->bpas->formatMoney($gltran->am1) : '0.00'); ?></td>
										<td class="right"><?= ($gltran->am2 < 1 ? $this->bpas->formatMoney(abs($gltran->am2)) : '0.00')?></td>
										<td class="right"><?= $this->bpas->formatMoney($endAccountBalance)?></td>
                                        <td>
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <input type="radio" class="checkbox type" value="<?= $gltran->tran_id ?>" id="<?= $gltran->tran_id ?>" name="val<?= $gltran->tran_id ?>[]">
                                                    <label for="full" class="padding05"><?= lang('balance_bank'); ?></label>
                                                </div>
                                                <div class="col-xs-6">
                                                    <input type="radio" class="checkbox_1 type" value="<?= $gltran->tran_id ?>" id="<?= $gltran->tran_id ?>" name="val<?= $gltran->tran_id ?>[]">
                                                    <label for="partial" class="padding05"><?= lang('balance_book'); ?></label>
                                                </div>
                                            </div>
                                        </td>
									</tr>
										<?php } ?>
									<tr>
										<td colspan="5"></td>
										<td colspan="3" style="font-weight: bold;">Ending Account Balance <i class="fa fa-caret-right" aria-hidden="true"></i></td>
										<?php if($endAmount > 0) { ?>
											<td class="right"><?= $this->bpas->formatMoney(abs($endAmount)); ?></td>
											<td class="right"></td>
											<td class="right"></td>
										<?php } else { ?>
											<td class="right"></td>
											<td class="right"><?= $this->bpas->formatMoney(abs($endAmount)); ?></td>
											<td class="right"></td>
										<?php } ?>
									</tr>
                                <?php
								}
                            }
                       ?>
						</tbody>
					</table>    
                </div>
            </div>
        </div>
    </div>
</div>
<h1>
    <?php
        if ($v_multi_account) {
            $v .= "&ac=" . $v_multi_account;
        }
    ?>
</h1>
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
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/ledger/pdf/0/'.$biller_id . '?v=1'.$v. '&sd='. $start_date . '&ed='. $end_date)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/ledger/0/xls/'.$biller_id . '?v=1'.$v . '&sd='. $start_date . '&ed='. $end_date)?>";
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
</script>
<script type="text/javascript">
    $(document).ready(function () {
            if (this.checked) {
                $('.1').each(function () {
                    this.checked = true;
                });
            } else {
                $('.a').each(function () {
                    this.checked = false;
                });
            }
    });

</script>
<script>

    $(document).ready(function(){
   
        $('body').on('click', '#bank_reconcile', function(e) {

            var start_date = $("#start_date").val();
            if(start_date ==''){
                alert('Please select date');
                location.reload();
            }

            e.preventDefault();
            /*if($('.checkbox').is(":checked") === false){
                alert('Please select at least one.');
                return false;
            }*/
            var arrItems = [];
            $('.checkbox').each(function(i){
                if($(this).is(":checked")){
                    if(this.value != ""){
                        arrItems[i] = $(this).val();
                    }
                }
            });
            //-----balance book
            var arrItems1 = [];
            $('.checkbox_1').each(function(j){
                if($(this).is(":checked")){
                    if(this.value != ""){
                        arrItems1[j] = $(this).val();
                    }
                }
            });
            var acc = <?= $v_multi_account ? $v_multi_account : 'NULL'; ?>;
           var start = $("#sdate").val();
           var end = $("#edate").val();
            // var i = 0;
            //     var items = [];
            //     var b=false;
            //     var k = false;
            //     $.each($("input[id='<?= $gltran->tran_id ?>']:checked"), function(){
            //         items[i] = {'id': $(this).val()};
            //         i++;
            //     });

                $('.checkbox:checked').each(function(indexa) {
                     arrItems[indexa] = $(this).val();
                });
                 $('.checkbox_1:checked').each(function(index) {
                     arrItems1[index] = $(this).val();
                });

                  if(arrItems == ""){
                        alert('Please select at least one.');
                        return false;
                }
                   if(arrItems1 == ""){
                        alert('Please select at least one.');
                        return false;
                }
                /*$.ajax({
                    type: 'get',
                    url: "<?= admin_url('account/checkrefer') ?>",
                    dataType: "json",
                    async:false,
                    data: { <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',items:items },
                    success: function (data) {
                        if(data.isAuth == 1){
                            b = true;
                        }
                        if(data.customer == 2){
                            k = true;
                        }
                    }
                });

                if(b == true){
                    bootbox.alert('Customer is not match!');
                    return false;
                }else {*/
                    $('#myModal').modal({remote: '<?= admin_url('account/bank_concile_form');?>?acc='+acc+'&data='+arrItems+'&data1='+arrItems1+'&start='+start+'&end='+end+''});
                    $('#myModal').modal('show');
                    return false;
                //}

            $('#form_action').val($('#combine_pay').attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('input[type="checkbox"]').on('change', function() {
          var checkedValue = $(this).prop('checked');
            // uncheck sibling checkboxes (checkboxes on the same row)
            $(this).closest('tr').find('input[type="checkbox"]').each(function(){
               $(this).prop('checked',false);
            });
            $(this).prop("checked",checkedValue);

        });
        $(".bank_account_amount_1 :input").each(function(){
            total_bank_account_amount += parseFloat($(this).val()); 
        });
    });
</script>
