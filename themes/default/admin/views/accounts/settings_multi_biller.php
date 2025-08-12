<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
<div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cogs"></i><?= lang('account_settings'); ?></h2>
        <?php if(isset($pos->purchase_code) && ! empty($pos->purchase_code) && $pos->purchase_code != 'purchase_code') { ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= admin_url('pos/updates') ?>" class="toggle_down"><i class="icon fa fa-upload"></i><span class="padding-right-10"><?= lang('updates'); ?></span></a>
                </li>
            </ul>
        </div>
        <?php }?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
		    <p class="introtext"><?php echo lang('update_info'); ?></p>
		    <ul id="myTab" class="nav nav-tabs">
			<?php 
				foreach($get_biller as $data_biller){  ?>
				<li class=""><a href="#default_<?= $data_biller->id;?>" class="tab-grey"><?= $data_biller->company;?></a></li>
			<?php } ?>
			</ul> 
			
			<div class="tab-content">
				<?php 
				foreach($get_biller as $data_biller){  ?>
				<div id="default_<?= $data_biller->id;?>" class="tab-pane fade in">
					<?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'account_setting');
					echo admin_form_open("account/settings/".$data_biller->id."", $attrib);
                    $data = $this->site->getAccountSettingByBiller($data_biller->id);
					?>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('default') ?></legend>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_biller", "biller"); ?>
                                <!--
                                <?= form_input('biller', (isset($_POST['biller']) ? $_POST['biller'] : $data->biller_id), 'class="form-control tip" id="biller1" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" class="form-control" style="width:100%;"'); ?>
                                -->
                           
                                <input type="hidden" value="<?= $data->biller_id;?>" name="biller_id" class="form-control" style="width:100%;"/><?= $data_biller->company;?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_open_balance","default_open_balance"); ?>
                                <!--
                                <?php
                                echo form_input('default_open_balance', (isset($_POST['default_open_balance']) ? $_POST['default_open_balance'] : $data->default_open_balance), ' id="defaut_open_balance" data-placeholder="' . $data->default_open_balance . '" class="form-control tip" style="width:100%;"');
                                ?>
                                -->
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_open_balance', $acc_section, $data->default_open_balance ,'id="default_open_balance" class="form-control" data-placeholder="' . $data->default_open_balance . '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_payroll", "default_payroll"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    $payroll = "";
                                    foreach($getpayroll as $payrolls){
                                        $payroll = $payrolls->accountname;
                                    }
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_payroll', $acc_section, $data->default_payroll ,'id="default_payroll" class="form-control" data-placeholder="' . $data->default_payroll . ' | ' . $this->lang->line($payroll) . '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_salary_payable", "default_salary_payable"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('default_salary_payable', $acc_section, $data->default_salary_payable,'id="default_salary_payable" class="form-control" tyle="width:100%;" ');
                                ?>

                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_retained_earnings","default_retained_earnings"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    $get_retained_earning = "";
                                    foreach($retained_earning as $retained_earnings){
                                        $get_retained_earning = $retained_earnings->accountname;
                                    }
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_retained_earnings', $acc_section, $data->default_retained_earnings ,'id="default_retained_earnings" class="form-control" data-placeholder="' . $data->default_retained_earnings . ' | ' . $this->lang->line($get_retained_earning) . '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_transfer_owner","default_transfer_owner"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    $default_transfer_owner = "";
                                    if($transfer_owner) {
                                        foreach($transfer_owner as $to){
                                            $default_transfer_owner = $to->accountname;
                                        }
                                    }
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_transfer_owner', $acc_section, $data->default_transfer_owner ,'id="default_transfer_owner" class="form-control" data-placeholder="' . $data->default_transfer_owner . ' | ' . $this->lang->line($default_transfer_owner) . '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 hide">
                            <div class="form-group">
                                <?= lang("default_product_tax","default_product_tax"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('default_product_tax', $acc_section, $data->default_product_tax,'id="default_product_tax" class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('inventory') ?></legend>
                        
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_stock","default_stock"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    $stock = "";
                                    foreach($getstock as $getstocks){
                                        $stock = $getstocks->accountname;
                                    }
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_stock', $acc_section, $data->default_stock ,'id="default_stock" class="form-control" data-placeholder="' . $data->default_stock . ' | ' . $this->lang->line($stock) . '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_stock_adjust","default_stock_adjust"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_stock_adjust', $acc_section, $data->default_stock_adjust ,'id="default_stock_adjust" class="form-control" data-placeholder="' . $data->default_stock_adjust. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("stock_using","stock_using"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('default_stock_using', $acc_section, $data->default_stock_using,'id="default_stock_using" class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_cost_adjustment","default_cost_adjustment"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_cost_adjustment', $acc_section, $data->default_cost_adjustment ,'id="default_stock_adjust" class="form-control" data-placeholder="' . $data->default_cost_adjustment. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_convert","default_convert"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_convert', $acc_section, $data->default_convert_account ,'id="default_stock_adjust" class="form-control" data-placeholder="' . $data->default_convert_account. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('cash') ?></legend>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_cash", "default_cash"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_cash', $acc_section, $data->default_cash,'id="default_cash" class="form-control" data-placeholder="' . $data->default_cash. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_credit_card", "default_credit_card"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_credit_card', $acc_section, $data->default_credit_card ,'id="default_credit_card" class="form-control" data-placeholder="' . $data->default_credit_card.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_gift_card","default_gift_card"); ?>
                                <?php
                                $acc_section = array(""=>"");
                                foreach($chart_accounts as $section){
                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                }
                                echo form_dropdown('default_gift_card', $acc_section, $data->default_gift_card ,'id="default_gift_card" class="form-control" data-placeholder="' . $data->default_gift_card. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_cheque","default_cheque"); ?>
                                <?php
                                $acc_section = array(""=>"");
                                foreach($chart_accounts as $section){
                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                }
                                echo form_dropdown('default_cheque', $acc_section, $data->default_cheque,'id="default_cheque" class="form-control" data-placeholder="'.$data->default_cheque.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_other_paid","default_other_paid"); ?>
                                <?php
                                $acc_section = array(""=>"");
                                foreach($chart_accounts as $section){
                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                }
                                echo form_dropdown('default_loan', $acc_section, $data->default_loan,'id="default_loan" class="form-control" data-placeholder="' . $data->default_loan. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_payment_pos","default_payment_pos"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('default_payment_pos', $acc_section, $data->default_payment_pos,'id="default_payment_pos" class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("write_off","write_off"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('write_off', $acc_section, $data->default_write_off,'id="write_off" class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('sale') ?></legend>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_sale","default_sale"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_sale', $acc_section, $data->default_sale ,'id="default_sale" class="form-control" data-placeholder="' . $data->default_sale.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("other_income","other_income"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('other_income', $acc_section, $data->other_income,'id="other_income" class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_sale_discount","default_sale_discount"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_sale_discount', $acc_section, $data->default_sale_discount,'id="default_sale_discount" class="form-control" data-placeholder="' . $data->default_sale_discount. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_sale_tax","default_sale_tax"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_sale_tax', $acc_section, $data->default_sale_tax ,'id="default_sale_tax" class="form-control" data-placeholder="' . $data->default_sale_tax.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_receivable","default_receivable"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_receivable', $acc_section,  $data->default_receivable,'id="default_receivable" class="form-control" data-placeholder="' . $data->default_receivable. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_sale_freight","default_sale_freight"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_sale_freight', $acc_section, $data->default_sale_freight,'id="default_sale_freight" class="form-control" data-placeholder="' . $data->default_sale_freight. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_sale_deposit","default_sale_deposit"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_sale_deposit', $acc_section, $data->default_sale_deposit,'id="default_sale_deposit" class="form-control" data-placeholder="' .$data->default_sale_deposit. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_interest_income","default_interest_income"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_interest_income', $acc_section, $data->default_interest_income ,'id="default_interest_income" class="form-control" data-placeholder="' . $data->default_interest_income. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("credit_note","credit_note"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('credit_note', $acc_section, $data->credit_note,'class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('purchases') ?></legend>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_purchase","default_purchase"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_purchase', $acc_section, $data->default_purchase ,'id="default_purchase" class="form-control" data-placeholder="' . $data->default_purchase.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_purchase_tax","default_purchase_tax"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_purchase_tax', $acc_section,$data->default_purchase_tax,'id="default_purchase_tax" class="form-control" data-placeholder="' . $data->default_purchase_tax. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_purchase_discount","default_purchase_discount"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_purchase_discount', $acc_section,$data->default_purchase_discount,'id="default_purchase_discount" class="form-control" data-placeholder="' . $data->default_purchase_discount. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_payable","default_payable"); ?>
                                <?php 
                                    $acc_section = array(""=>"");

                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_payable', $acc_section, $data->default_payable,'id="default_payable" class="form-control" data-placeholder="' . $data->default_payable. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_purchase_freight","default_purchase_freight"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_purchase_freight', $acc_section,$data->default_purchase_freight,'id="default_purchase_freight" class="form-control" data-placeholder="' . $data->default_purchase_freight.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_purchase_deposit","default_purchase_deposit"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_purchase_deposit', $acc_section,$data->default_purchase_deposit,'id="default_purchase_deposit" class="form-control" data-placeholder="' . $data->default_purchase_deposit. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_cost","default_cost"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_cost', $acc_section,$data->default_cost,'id="default_cost" class="form-control" data-placeholder="' . $data->default_cost. '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_cost_variant","default_cost_variant"); ?>
                                <?php
                                    $acc_section = array(""=>"");
               
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_cost_variant', $acc_section, $data->default_cost_variant ,'id="default_cost_variant" class="form-control" data-placeholder="' . $data->default_cost_variant.'" style="width:100%;" ');
                                ?>
                                <input type="hidden" value="<?= $data->default_cost_variant;?>" name="cost_variant" class="form-control" style="width:100%;"/>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("default_expense","default_expense"); ?>
                                <?php
                                    $acc_exp = array(""=>"");
                                    foreach($chart_accounts as $section){
                                        $acc_exp[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('default_expense', $acc_exp, $data->default_expense ,'id="default_expense" class="form-control" data-placeholder="' . $data->default_expense.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("debit_note","debit_note"); ?>
                                <?php
                                    $acc_section = array(""=>"");
                                    foreach($chart_accounts as $section) {
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                echo form_dropdown('debit_note', $acc_section, $data->debit_note,'class="form-control" tyle="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('installments') ?></legend>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                <?= lang("outstanding_installment","outstanding_installment"); ?>
                                <?php 
                                    $acc_section = array(""=>"");
                            
                                    foreach($chart_accounts as $section){
                                        $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                    }
                                    echo form_dropdown('outstanding_installment', $acc_section, $data->installment_outstanding_acc ,'id="default_purchase" class="form-control" data-placeholder="'.$data->installment_outstanding_acc.'" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                    </fieldset>

					<?php echo form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary"'); ?>
					<?php echo form_close(); ?> 
				</div>						
				<?php } ?>
						
				
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function (e) {
        $('#account_setting').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        });
        $('select.select').select2({minimumResultsForSearch: 6});
        fields = $('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('#account_setting').bootstrapValidator('revalidateField', iname);
                });
            }
        });
        $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });

        $('#customer1').val('<?= $account->default_customer; ?>').select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customerszz/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
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
		
		$('#biller1').val('<?= $account->default_biller; ?>').select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customerszz/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
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
		
		$('#defaut_open_balance').val('<?= $account->default_open_balance; ?>').select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customerszz/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/balance_suggest",
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
        
    });
</script>
