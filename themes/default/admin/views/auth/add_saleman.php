<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_saleman'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("auth/add_saleman", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("first_name", "first_name"); ?>
                        <?php echo form_input('first_name', '', 'class="form-control tip" id="first_name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("last_name", "last_name"); ?>
                        <?php echo form_input('last_name', '', 'class="form-control tip" id="last_name" required="required"'); ?>
                    </div>
					<div class="form-group">
						<?= lang('gender', 'gender'); ?>
						<?php
						$ge[''] = array('male' => lang('male'), 'female' => lang('female'));
						echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="tip form-control" id="gender" data-placeholder="' . lang("select") . ' ' . lang("gender") . '" required="required"');
						?>
					</div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?> 
                        <input type="text" name="phone" class="form-control"  id="phone"/>
                    </div>
					<div class="form-group hide">
                        <?= lang("position", "position"); ?> 
                        <input type="text" name="position" class="form-control"  id="position"/>
                    </div>
					<?php if($this->config->item('saleman_commission')){ ?>
						<div class="form-group">
							<?= lang("commission", "commission"); ?>
							<input type="text" name="commission" class="form-control" id="commission"/>
						</div>
						<div class="form-group">
							<?= lang('share_commissions', 'share_commissions'); ?>
							<?php
							if($leaders){
								foreach($leaders as $leader){
									$opt_leader[$leader->id] = $leader->last_name." ".$leader->first_name;
								}
							}
							echo form_dropdown('share_commissions[]', $opt_leader, (isset($_POST['share_commissions']) ? $_POST['share_commissions'] : ''), 'class="tip form-control" multiple id="share_commissions"'); ?>
						</div>
					<?php } ?>
					<div class="form-group">
						<?= lang('product_commission', 'product_commission'); ?>
						<?php
						$opt_product_commission[''] = lang("select") . ' ' . lang("product_commission");
						if($product_commissions){
							foreach($product_commissions as $product_commission){
								$opt_product_commission[$product_commission->id] = $product_commission->name;
							}
						}
						echo form_dropdown('product_commission', $opt_product_commission, (isset($_POST['product_commission_id']) ? $_POST['product_commission_id'] : ''), 'class="tip form-control" id="group"');
						?>
					</div>
					<div class="form-group">
						<?= lang('group', 'group'); ?>
						<?php
						$opt_group[''] = lang("select") . ' ' . lang("group");
						if($groups){
							foreach($groups as $group){
								$opt_group[$group->id] = $group->name;
							}
						}
						echo form_dropdown('group', $opt_group, (isset($_POST['group']) ? $_POST['group'] : ''), 'class="tip form-control" id="group"'); ?>
					</div>
					
					<div class="form-group">
						<?= lang('area', 'area'); ?>
						<?php
						$opt_area[''] = lang("select") . ' ' . lang("area");
						if($areas){
							foreach($areas as $area){
								$opt_area[$area->id] = $area->zone_name;
							}
						}
						echo form_dropdown('area', $opt_area, (isset($_POST['area']) ? $_POST['area'] : ''), 'class="tip form-control" id="area" data-placeholder="' . lang("select") . ' ' . lang("area") . '" ');
						?>
					</div>
					
					<?php if($this->config->item('fuel')==true){ ?>
						<div class="form-group">
							<?= lang('fuel_time', 'fuel_time'); ?>
							<?php
							$opt_fuel_time[''] = lang("select") . ' ' . lang("fuel_time");
							if($fuel_times){
								foreach($fuel_times as $fuel_time){
									$opt_fuel_time[$fuel_time->id] = $fuel_time->open_time.' -'.$fuel_time->close_time;
								}
							}
							echo form_dropdown('fuel_time', $opt_fuel_time, (isset($_POST['fuel_time']) ? $_POST['fuel_time'] : ''), 'class="tip form-control" id="fuel_time"'); ?>
						</div>
						<?php foreach($currencies as $currency){ ?>				
							<div class="form-group">
								<span><?= lang("change_amount", "change_amount"); ?> (<?=$currency->code?>)</span>
								<input name="amount[]" value="0.00" type="text" class="form-control"/>
								<input name="code[]" value="<?= $currency->code ?>" type="hidden" />
								<input name="rate[]" value="<?= $currency->rate ?>" type="hidden" />
							</div>
						<?php } ?>
					<?php } ?>

					<div class="form-group">
						<?= lang('status', 'status'); ?>
						<?php
						$opt = array(1 => lang('active'), 0 => lang('inactive'));
						echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" required="required" class="form-control select" style="width:100%;"');
						?>
					</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_saleman', lang('add_saleman'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?=$modal_js ?>