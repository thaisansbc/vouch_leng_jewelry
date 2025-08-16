<?php
    $start_date = $this->input->post('start_date');
    $end_date = $this->input->post('end_date');
?>
<style type="text/css">

</style>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('bank_reconcile'); ?><?php
        if ($this->input->post('end_date')) {
            echo $this->input->post('end_date');
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
    <div class="box-icon hide">
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

    <div class="box-icon hide">
        <ul class="btn-tasks">
            <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                        class="icon fa fa-file-excel-o"></i></a></li>
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
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo admin_form_open("account/bank_reconcile/".$v_form); ?>
                    <div class="row">

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("account_name"); ?></label>
                                <?php
                            
                                $accOption = $this->db->select('*')
                                        ->from('gl_charts')
                                        ->where('bank', 1)
                                        ->get()->result();

                                $accountArray = [];
                                foreach ($accOption as $a) {
                                    $accountArray[$a->accountcode] = $a->accountcode . " " . $a->accountname;
                                }
                               
                                echo form_dropdown('account', $accountArray, (isset($v_account) ? $v_account : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("account") . '" ');
                                ?>
                            </div>
                        </div>
                        <?php
                        $las_reconcile = $this->site->getBank_reconcileByCode();
                        ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("last_reconcile", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($start_date) ? $start_date : $this->bpas->hrsd($las_reconcile->last_recincile)), 'class="form-control date" readonly="readonly" id="start_date"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("ending_balance", "ending_balance"); ?>
                                <?php echo form_input('ending_balance', (isset($ending_balance) ? $ending_balance : 0), 'class="form-control" id="ending_balance"'); ?>
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
                            $end_date=str_replace('/','-',$end_date);
                        ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("service_charge", "service_charge"); ?>
                                <?php echo form_input('service_charge', (isset($service_charge) ? $service_charge : 0), 'class="form-control" id="service_charge"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("account_name"); ?></label>
                                <?php
                                $this->db->select('*')->from('gl_charts');
                                $accOption = $this->db->get()->result();
                                $accountArray = [];
                                foreach ($accOption as $a) {
                                    $accountArray[$a->accountcode] = $a->accountcode . " " . $a->accountname;
                                }
                                echo form_dropdown('service_charge_acc', $accountArray, (isset($_POST['service_charge_acc']) ? $_POST['service_charge_acc'] : ""), 'class="form-control" id="service_charge_acc" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("account") . '" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                <?= lang("interest_earned", "interest_earned"); ?>
                                <?php echo form_input('interest_earned', (isset($interest_earned) ? $interest_earned : 0), 'class="form-control" id="interest_earned"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("account_name"); ?></label>
                                <?php
                                $this->db->select('*')->from('gl_charts');
                                $accOption = $this->db->get()->result();
                                $accountArray = [];
                                foreach ($accOption as $interest) {
                                    $accountArray[$interest->accountcode] = $interest->accountcode . " " . $interest->accountname;
                                }
                                echo form_dropdown('interest_earned_acc', $accountArray, (isset($_POST['interest_earned_acc']) ? $_POST['interest_earned_acc'] : ""), 'class="form-control" id="interest_earned_acc" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("account") . '" ');
                                ?>
                            </div>
                        </div>
                        
                    </div>
                
                    <div class="form-group">
                        <input type="hidden" id="sdate" value="<?= (isset($start_date) ? $start_date : "")?>">
                        <input type="hidden" id="edate" value="<?= (isset($end_date) ? $end_date : "")?>">

                        <div class="controls"> <?php echo form_submit('submit', $this->lang->line("start_reconciling"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="row">
         <?php echo admin_form_open("Account/sync_reconcile"); ?>
         <?php echo form_dropdown('account', $accountArray, (isset($v_account) ? $v_account : ""), 'class="form-control hide"');?>
         <?php echo form_input('end_date', (isset($end_date) ? $end_date : ""), 'class="form-control date hide"'); ?>
            

            <div class="col-lg-6">
                <h2>Deposits and Other Credits</h2>
                <input type="hidden" id="debitID" name="debit_val" value="">
                <table cellpadding="0" id="course_id" cellspacing="0" border="0" class="table table-hover table-striped table-condensed">
                        <thead>
                            <tr>
                            
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth debit" type="checkbox" name="check" value=""/>
                                </th>
                              
                                <th style="width:150px;"><?= lang('no'); ?></th>
                                <th style="width:150px;"><?= lang('type'); ?></th>
                                <th style="width:150px;"><?= lang('date'); ?></th>   
                                <th style="width:200px;"><?= lang('ref'); ?></th>
                                <th style="width:200px;"><?= lang('description'); ?></th>
                                <th style="width:150px;"><?= lang('amount'); ?></th>
                                                     
                            </tr>
                        </thead>
                        
                        <?php
                        if($end_date){

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
                                                            'tran_date <= '=> $this->bpas->fsd($end_date). '23:59:59',
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
                                ->join('companies','companies.id=gl_trans.biller_id', 'left')
                                ->join('users', 'users.id = gl_trans.created_by', 'left')
                                ->order_by('tran_date', 'asc')
                                ->where('reconciled', 0)
                                ->where('account_code', $account->accountcode);
                                
                              
                                if ($end_date) {
                                    $glTrans->where('date(tran_date) <=', $this->bpas->fsd($end_date). '23:59:59');
                                }
                                
                                if($biller_id != ""){
                                    $glTrans->where('gl_trans.biller_id' ,$biller_id);
                                }

                                $glTrans->where('gl_trans.amount >' ,0);

                                $glTranLists = $glTrans->get()->result();
                                
                                if($glTranLists) {?>
                                
                                <?php
                                $endAmount = $startAmount->startAmount;
                                $endDebitAmount = 0;
                                $endCreditAmount = 0;
                                
                                foreach($glTranLists as $gltran)
                                {
                                    $endAccountBalance += $gltran->amount;
                                    $endAmount += $gltran->amount;
                                    ?>
                                    <tr>
                                        <td>
                                        <input type="checkbox" onclick="total_debit()" class="debit checkbox multi-select" value="<?= $gltran->tran_id ?>" name="val<?= $gltran->tran_id ?>[]" title="<?= ($gltran->am1 > 0 ? $this->bpas->formatMoney($gltran->am1) : '0.00'); ?>">
                                        </td>
                                        <td><?= $gltran->tran_no ?></td>
                                        <td><?= $gltran->tran_type ?></td>
                                        <td><?= $this->bpas->hrld($gltran->tran_date); ?></td>
                                        <td><?= $gltran->reference_no ?></td>
                                        <td title="<?= $this->bpas->decode_html($gltran->description); ?>"><?= substr($this->bpas->decode_html($gltran->description,0,50)) ?></td>
                                        <td class="right"><?= ($gltran->am1 > 0 ? $this->bpas->formatMoney($gltran->am1) : '0.00'); ?></td>
                                    </tr>
                                        <?php } ?>
                                <?php
                                }
                            }
                        }
                        ?>
                    </table> 
            </div>
            <div class="col-lg-6">
                <h2>Checks and Payments</h2>
                <table cellpadding="0" id="checkboxes" cellspacing="0" border="0" class="table table-hover table-striped table-condensed">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth1 credit" type="checkbox" name="check" value=""/>
                                </th>
                                <th style="width:150px;"><?= lang('no'); ?></th>
                                <th style="width:150px;"><?= lang('type'); ?></th>
                                <th style="width:150px;"><?= lang('date'); ?></th>
                                <th style="width:200px;"><?= lang('ref'); ?></th>
                                <th style="width:200px;"><?= lang('description'); ?></th>
                                <th style="width:150px;"><?= lang('amount'); ?></th>
                                                     
                            </tr>
                        </thead>
                   <input type="hidden" id="creditID" name="credit_val" value="">
                        <?php
                        if($end_date){

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
                                                            'tran_date <= '=> $this->bpas->fsd($end_date). ' 23:59:59',
                                                            'account_code'=> $account->accountcode
                                                            )
                                                        )->get()->row();
                                $endAccountBalance = 0.00;
                                $glTrans = $this->db->select("
                                    gl_trans.*,
                                    (CASE WHEN bpas_gl_trans.amount > 0 THEN bpas_gl_trans.amount END ) as am1,
                                    (CASE WHEN bpas_gl_trans.amount < 0 THEN bpas_gl_trans.amount END ) as am2,
                                    companies.company,
                                    companies.name,
                                    users.username")
                                ->from('gl_trans')
                                ->join('companies','companies.id=gl_trans.biller_id', 'left')
                                ->join('users', 'users.id = gl_trans.created_by', 'left')
                                ->order_by('tran_date', 'asc')
                                ->where('reconciled', 0)
                                ->where('account_code', $account->accountcode);
                                
                            
                                if ($end_date) {
                                    $glTrans->where('date(tran_date) <=', $this->bpas->fsd($end_date). ' 23:59:59');
                                }
                                
                                if($biller_id != ""){
                                    $glTrans->where('gl_trans.biller_id' ,$biller_id);
                                }

                                $glTrans->where('gl_trans.amount <' ,0);
                                $glTranLists = $glTrans->get()->result();
                                
                                if($glTranLists) {?>
                                
                                <?php
                                $endAmount = $startAmount->startAmount;
                                $endDebitAmount = 0;
                                $endCreditAmount = 0;
                                foreach($glTranLists as $gltran)
                                {
                                    $endAccountBalance += $gltran->amount;
                                    $endAmount += $gltran->amount;
                                    ?>
                                    <tr>
                                        <td> 
                                            <input type="checkbox" class="checkbox multi-select1 type credit" value="<?= $gltran->tran_id ?>" id="<?= $gltran->tran_id ?>" name="val<?= $gltran->tran_id ?>[]" title="<?= ($gltran->am2 < 1 ? $this->bpas->formatMoney(abs($gltran->am2)) : '0.00')?>">
                                        </td>
                                   
                                        <td><?= $gltran->tran_no ?></td>
                                        <td><?= $gltran->tran_type ?></td>
                                        <td><?= $this->bpas->hrld($gltran->tran_date); ?></td>
                                        <td><?= $gltran->reference_no ?></td>
                                        <td><?= $gltran->description ?></td>
                                        <td class="right"><?= ($gltran->am2 < 1 ? $this->bpas->formatMoney(abs($gltran->am2)) : '0.00')?></td>
                                        
                                    </tr>
                                        <?php } ?>
                                 
                                <?php
                                }
                            }
                        }
                        ?>
                    </table> 
            </div>
        </div>
        <?php 
            echo form_hidden('charge_amount', (isset($service_charge) ? $service_charge : 0), '');
            echo form_hidden('charge_amount_acc', (isset($_POST['service_charge_acc']) ? $_POST['service_charge_acc'] : ""), '');
            echo form_hidden('earned_amount', (isset($interest_earned) ? $interest_earned : 0), '');
            echo form_hidden('earned_amount_acc',(isset($_POST['interest_earned_acc']) ? $_POST['interest_earned_acc'] : ""), '');
        ?>
                       
        <div class="row well well-sm well_1">
            <div class="col-md-4">
                <table border="0" width="100%">
                    <tr>
                        <td width="50%"><?= lang('deposits_credits')?></td>
                        <td width="50%">: <input type="hidden" name="total_debit" id='itotal_debit' value="0"><span id='total_debit'></span></td>
                    </tr>
                    <tr>
                        <td width="50%"><?= lang('checks_payments')?></td>
                        <td width="50%">: <input type="hidden" name="total_credit" id='itotal_credit' value="0"><span id='total_credit'></span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-4">
                <table border="0" width="100%">
                    <tr class="hide">
                        <td width="50%"><?= lang('interest_earned')?></td>
                        <td width="50%">: <input type="hidden" name="interest_earned1" id='interest_earned1' value="<?= (isset($interest_earned) ? $interest_earned : 0); ?>"><span id='total_interest_earned'><?= (isset($interest_earned) ? $interest_earned : 0); ?></span></td>
                    </tr>
                    <tr>
                        <td width="50%"><?= lang('service_charge')?></td>
                        <td width="50%">: - <input type="hidden" name="service_charge" id='service_charge1' value="<?= (isset($service_charge) ? $service_charge : 0); ?>"><span id='total_service_charge'><?= (isset($service_charge) ? $service_charge : 0); ?></span></td>
                    </tr>
                    
                    <tr>
                        <td width="50%"><?= lang('ending_balance')?></td>
                        <td width="50%">: <input type="hidden" name="total_Endbalance" id='itotal_Endbalance' value="<?= (isset($ending_balance) ? $ending_balance : 0); ?>"><span id='total_Endbalance'>$<?= (isset($ending_balance) ? $ending_balance : 0); ?></span></td>
                    </tr>
                    <tr>
                        <td width="50%"><?= lang('cleared_balance')?></td>
                        <td width="50%">: <input type="hidden" name="total_balance" id='itotal_balance' value="0"><span id='total_balance'></span></td>
                    </tr>
                    <tr>
                        <td width="50%"><?= lang('difference')?></td>
                        <td width="50%">: <input type="hidden" name="total_difference" id='itotal_difference' value ="0" ><span id='total_difference'>$<?= (isset($ending_balance) ? $ending_balance : 0); ?></span></td>
                    </tr>
                </table>
            </div>
        
            <div class="col-md-4">
                <div class="form-group">
                    <?php echo form_submit('submit', $this->lang->line('reconcile_now'), 'class="btn btn-primary" id ="btnSubmit" disabled="true" '); ?>
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
<script type="text/javascript">
    function total_debit() {
      var input = document.getElementsByClassName("debit");
      var total_debit = 0;
      for (var i = 0; i < input.length; i++) {
        if (input[i].checked) {
          total_debit += parseFloat(getNumberFromCurrency(input[i].title));
        }
      }
      document.getElementById("total_debit").innerHTML = "$" + total_debit.toFixed(2);
      document.getElementById('itotal_debit').value = total_debit.toFixed(2);
      total_balance()
    }
    function total_credit() {
        var input = document.getElementsByClassName("credit");
          var total_credit = 0;
          for (var i = 0; i < input.length; i++) {
            if (input[i].checked) {
              total_credit += parseFloat(getNumberFromCurrency(input[i].title));
            }
        }
        document.getElementById("total_credit").innerHTML = "$" + total_credit.toFixed(2);
        document.getElementById('itotal_credit').value = total_credit.toFixed(2);
        total_balance();
    }
   
    function total_balance() {
        document.getElementById("total_balance").innerHTML ="$" +  (parseFloat($('#total_debit').text().substring(1))+parseFloat($('#interest_earned1').val()) -parseFloat($('#total_credit').text().substring(1)) -parseFloat($('#service_charge1').val()) ).toFixed(2);
        document.getElementById('itotal_balance').value = parseFloat($('#total_balance').text().substring(1)).toFixed(2);
        total_difference();
    }
    function total_difference() {
        document.getElementById("total_difference").innerHTML ="$" +  (parseFloat($('#total_Endbalance').text().substring(1))-parseFloat($('#total_balance').text().substring(1))).toFixed(2);
        document.getElementById('itotal_difference').value = parseFloat($('#total_difference').text().substring(1)).toFixed(2);
        syncstatus();
    }
    function syncstatus(){
        if(parseFloat($('#total_difference').text().substring(1)) == 0){
            $('#btnSubmit').attr("disabled", false);
        }else{
             $('#btnSubmit').attr("disabled", true);
        }
        //  start selection checkbox 
         var creditItems = [];
            $('.credit').each(function(i){
                if($(this).is(":checked")){
                    if(this.value != ""){
                        creditItems[i] = $(this).val();
                    }
                }
            });
            //-----balance book
            var debitItems = [];
            $('.debit').each(function(j){
                if($(this).is(":checked")){
                    if(this.value != ""){
                        debitItems[j] = $(this).val();
                    }
                }
            });
             $('.credit:checked').each(function(indexa) {
                     creditItems[indexa] = $(this).val();
                });
                 $('.debit:checked').each(function(index) {
                     debitItems[index] = $(this).val();
                });
                creditItems = creditItems.filter((item) => item);
                debitItems = debitItems.filter((item) => item);
                document.getElementById('creditID').value = Array.from(new Set(creditItems));
                document.getElementById('debitID').value = Array.from(new Set(debitItems));
                //   if(arrItems == "" || arrItems1 == ""){
                //         alert('Please select at least one.');
                //         return false;
                // }
        // end selection checkbox
    }
    $(document).ready(function () {
        
        $('.credit').on('ifChecked', function() {
            total_credit();
        });
        $('.debit').on('ifChecked', function() {
            total_debit();
        });
        $('.debit').on('ifUnchecked', function() {
            total_debit();
        });
        $('.credit').on('ifUnchecked', function() {
            total_credit();
        });

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
    //-------------
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
    //-------------
    $(document).ready(function(){
   
        $('body').on('click', '#bank_reconcile', function(e) {

            var end_date = $("#end_date").val();
            if(end_date ==''){
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
      

                $('.checkbox:checked').each(function(indexa) {
                     arrItems[indexa] = $(this).val();
                });
                 $('.checkbox_1:checked').each(function(index) {
                     arrItems1[index] = $(this).val();
                });

                if(arrItems == "" && arrItems1 == ""){
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
    function getNumberFromCurrency(currency) {
      return Number(currency.replace(/[$,]/g,''))
    }
</script>   