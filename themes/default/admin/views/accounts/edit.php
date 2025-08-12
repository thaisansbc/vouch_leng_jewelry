<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_chart_account'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("account/edit/".$chart_id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
        
			
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang("account_section", "account_section"); ?>
						<?php
						$acc_section = array(""=>"");
						foreach($sectionacc as $section){
							$acc_section[$section["sectionid"]] = $section["sectionname"];
						}	
							echo form_dropdown('account_section', $acc_section, $account->sectionid, 'id="account_section" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("Account") . ' ' . $this->lang->line("Section") . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
					<div class="form-group person sub_textbox" style="display:none;">
                        <?= lang("sub_account", "sub_account"); ?>
                        <?php 
							echo form_input('sub_account', '', 'class="form-control" id="sub_account"  placeholder="' . lang("select_sub_account") . '"');
						?>
                    </div>
                    <div class="form-group person sub_combobox">
                        <?= lang("sub_account", "sub_account"); ?>
                        <?php 

                        if(isset($subacc)){
                            $sub_acc[''] = lang('please_selected');
							foreach($subacc as $sub){
								$sub_acc[$sub->id] = $sub->text;
							}	
							echo form_dropdown('sub_acc', $sub_acc, $account->parent_acc, 'id="sub_acc" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("Account") . ' ' . $this->lang->line("Section") . '" style="width:100%;" ');
                        }
						?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("account_name", "account_name"); ?>
                        <?php echo form_input('account_name', $account->accountname, 'class="form-control" id="account_name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("account_code", "account_code"); ?>
                        <?php echo form_input('account_code', $account->accountcode, 'class="form-control" id="account_code" required="required" readonly'); ?>
                    </div>
         
				</div>
			
				<div class="col-md-6">
					<div class="form-group">
						<input type="checkbox" id="bank_account" class="form-control" name="bank_account" value="1" <?php echo set_checkbox('bank_account', '1', $account->bank==1?TRUE:FALSE); ?>>
						<?= lang("bank_account", "bank_account"); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group hide">
                        <label class="control-label" for="restrict_calendar"><?= lang('activity'); ?></label>

                        <div class="controls">
                            <?php
                            $opt_cal = [
                                0 => lang('none'),
                                1 => lang('business_activity'), 
                                2 => lang('investing_activity'),
                                3 => lang('financing_activity')];
                            echo form_dropdown('type', $opt_cal, $account->type, 'class="form-control tip" required="required" id="cashflow" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="cash_flow"><?php echo $this->lang->line("cash_flow"); ?></label>
                        <?php
                        $cfs['0'] = lang('select').' '.lang('cash_flow');
                        foreach ($cash_flows as $cash_flow) {
                            $cfs[$cash_flow->id] = $cash_flow->name;
                        }
                        echo form_dropdown('cash_flow', $cfs, $account->cash_flow, 'class="form-control tip select" id="cash_flow"  style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group nature_box" <?= ($account->cash_flow<>'0'?'':'style="display:none"') ?>>
                        <label class="control-label" for="nature"><?php echo $this->lang->line("nature"); ?></label>
                        <?php
                        $nts['debit'] = lang('debit');
                        $nts['credit'] = lang('credit');
                        echo form_dropdown('nature', $nts, $account->nature, 'class="form-control tip select" id="nature"  style="width:100%;"');
                        ?>
                    </div>
				</div>
			</div>
        <div class="modal-footer">
            <?php echo form_submit('edit_account', lang('edit_chart_account'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function () {
		$('#account_section').change(function () {
			$(".sub_textbox").show();
			$(".sub_combobox").hide();
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= admin_url('account/getSubAccount') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#sub_account").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            }
            $('#modal-loading').hide();
        });
        $("#cash_flow").live("change",function(){
            var cash_flow = $(this).val();
            if(cash_flow=='0'){
                $('.nature_box').slideUp();
            }else{
                $('.nature_box').slideDown();
            }
        });
	});
</script>
