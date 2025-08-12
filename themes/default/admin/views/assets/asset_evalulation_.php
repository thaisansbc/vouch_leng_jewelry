<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>ul.ui-autocomplete {
        z-index: 1100;
    }</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $product->name; ?></h4>
        </div>
        <?php echo admin_form_open('assets/evaluation/' . $product->id); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="col-md-12">
                <br>
                <div class="col-md-6">
        			<div class="form-group">
                        <?php echo lang('evaluation_date', 'evaluation_date'); ?>
                        <div class="controls">
                            <?php echo form_input('date', date('d/m/Y'), 'id="date" class="form-control" required="required"'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                        <?php
                        $bl[""] = "";
                        foreach ($billers as $biller) {
                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                        }
                        
                        echo form_dropdown('biller_id', $bl, (isset($_POST['biller_id']) ? $_POST['biller_id'] : $this->site->get_setting()->default_biller), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '" required="required"');
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Depreciation Cost/Month</label>
                        <div class="controls">
                            <?php 
        					$created_date = date("Y", strtotime($product->created_date));

                            // depreciation 1/year = (cost - residual_value) / useful_life;

                            $current_cost1 =  $this->bpas->formatDecimal((($product->cost - $product->residual_value) / $product->useful_life)/12);


        					//$current_cost = ($product->cost - ($product->cost * (DATE('Y') - $created_date))/$product->useful_life);
        					echo form_input('current_cost', $current_cost1, 'id="current_cost" class="form-control" required="required"'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 hide">
                    <div class="form-group">
                        <?php echo lang('created_date', 'created_date'); ?>
                        <div class="controls">
                            <?php echo form_input('created_date', $product->created_date, 'id="created_date" readonly="readonly" class="form-control" required="required"'); ?>
                        </div>
                    </div>
               </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?php echo lang('useful', 'useful'); ?>
                        <div class="controls">
                            <?php echo form_input('useful', $product->useful_life, 'id="useful" readonly="readonly" class="form-control" required="required"'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?php echo lang('residual_value', 'residual_value'); ?>
                        <div class="controls">
                            <?php echo form_input('residual_value', $product->residual_value, 'id="residual_value" readonly="readonly" class="form-control" required="required"'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?php echo lang('cost', 'cost'); ?>
                        <div class="controls">
                            <?php echo form_input('cost', $product->cost, 'id="cost" readonly="readonly" class="form-control" required="required"'); ?>
                        </div>
                    </div>
                </div>
                <?php 
                if($this->Settings->accounting) {  
                ?>
                <div class="col-sm-6" id="bank_acc">
                    <div class="form-group">
                        <?= lang("asset_account", "bank_account_1"); ?>
                        <?php
                       // $bankAccounts =  $this->site->getAllBankAccounts();
                            $bank1 = array('' => '-- Select Bank Account --');
                            foreach($sectionacc as $bankAcc) {
                                $bank1[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                            }
                            echo form_dropdown('asset_account', $bank1, '', 'id="bank_account_1" class="ba form-control kb-pad bank_account" required="required" data-bv-notempty="true"');
                        ?>
                    </div>
                </div>
                <div class="col-sm-6" id="bank_acc1">
                    <div class="form-group">
                        <?= lang("ExpenseAccounts", "bank_charge"); ?>
                        <?php
                            $bank = array('' => '-- Select Bank Account --');
                            foreach($sectionacc as $bankAcc1) {
                                $bank[$bankAcc1->accountcode] = $bankAcc1->accountcode . ' | '. $bankAcc1->accountname;
                            }
                            echo form_dropdown('expense_account', $bank, '', 'id="bank_charge" class="ba form-control kb-pad" required="required" data-bv-notempty="true"');
                           
                        ?>
                    </div>
                </div>
                <?php } ?>
                
                
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
    