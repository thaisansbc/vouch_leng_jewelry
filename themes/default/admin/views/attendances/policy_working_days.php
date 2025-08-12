<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('policy_working_days'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("set_working_day"); ?></p>
				
				<?php
					$working_day = array();
					if($policy_working_days){
						foreach($policy_working_days as $policy_working_day){
							if($policy_working_day->time_one==1){
								$working_day[$policy_working_day->day]['time_one'] = true;
							}
							if($policy_working_day->time_two==1){
								$working_day[$policy_working_day->day]['time_two'] = true;
							}
							
						}
					}
					
					$ot_working_day = array();
					if($ot_policy_working_days){
						foreach($ot_policy_working_days as $ot_policy_working_day){
							$ot_working_day[$ot_policy_working_day->day][$ot_policy_working_day->ot_policy_id] = true;
						}
					}
					
					$days = array(
						'0'=>'Mon',
						'1'=>'Tue',
						'2'=>'Wed',
						'3'=>'Thu',
						'4'=>'Fri',
						'5'=>'Sat',
						'6'=>'Sun',
						'7'=>'Hol',
					);
				?>
                <?php echo admin_form_open("attendances/policy_working_days/" . $id); ?>
					<div class="table-responsive">
						<input type="hidden" value="<?= $id ?>" name="policy"/>
						<table class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th colspan="9"
										class="text-center"><?= $policy->policy ?></th>
								</tr>
								<tr>
									<th class="text-center"><?= lang("timeshift"); ?></th>
									<th class="text-center"><?= lang("monday"); ?></th>
									<th class="text-center"><?= lang("tuesday"); ?></th>
									<th class="text-center"><?= lang("wednesday"); ?></th>
									<th class="text-center"><?= lang("thursday"); ?></th>
									<th class="text-center"><?= lang("friday"); ?></th>
									<th class="text-center"><?= lang("saturday"); ?></th>
									<th class="text-center"><?= lang("sunday"); ?></th>
									<th class="text-center"><?= lang("holiday"); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-left"><?= $policy->policy ?>(<?= $policy->time_in_one ?> - <?= $policy->time_out_one ?>)</td>
									<?php foreach($days as $day){ 
											if (array_key_exists($day, $working_day)) {
									?>
												<td class="text-center">
													<input type="checkbox" value="<?= $day ?>" class="checkbox" name="<?= $day ?>-one" <?= (isset($working_day[$day]['time_one']) ? 'checked':'') ?>>
												</td>
									<?php
											}else{
									?>
												<td class="text-center">
													<input type="checkbox" value="<?= $day ?>" class="checkbox" name="<?= $day ?>-one" <?= '' ?>>
												</td>
									<?php
											}
										?>
										
									<?php } ?>

								</tr>
								<?php if($policy->time_in_two !='' && $policy->time_in_two !='00:00:00' && $policy->time_out_two !='' && $policy->time_out_two !='00:00:00'){ ?>
									<tr>
										<td class="text-left"><?= $policy->policy ?>(<?= $policy->time_in_two ?> - <?= $policy->time_out_two ?>)</td>
										
										<?php foreach($days as $day){ 

												if (array_key_exists($day, $working_day)) {
										?>
													<td class="text-center">
														<input type="checkbox" value="<?= $day ?>" class="checkbox" name="<?= $day ?>-two" <?= (isset($working_day[$day]['time_two']) ? 'checked':'') ?>>
													</td>
										<?php
												}else{
										?>
													<td class="text-center">
														<input type="checkbox" value="<?= $day ?>" class="checkbox" name="<?= $day ?>-two" <?= '' ?>>
													</td>
										<?php
												}
											?>
											
										<?php } ?>
										

									</tr>
								<?php } ?>
								
								<?php if($ot_policies) { ?>
									<tr>
										<td class="text-center"><?= lang('ot') ?></td>
										<td colspan="8"></td>
									</tr>
									<?php foreach($ot_policies as $ot_policie){ ?>
										<tr>
											<td class="text-left"><?= $ot_policie->ot_policy ?>(<?= $ot_policie->time_in ?> - <?= $ot_policie->time_out ?>)
										</td>
										
										<?php foreach($days as $day){ ?>
											<td class="text-center">
												<input type="checkbox" value="<?= $day ?>" class="checkbox" name="<?= $day.'-'.$ot_policie->id ?>" <?= (isset($ot_working_day[$day][$ot_policie->id]) ? 'checked':'') ?>>
											</td>
										<?php } ?>
											

										
										</tr>
									<?php } ?>
								<?php } ?>

							</tbody>
						</table>
					</div>
				<div class="form-actions">
					<button type="submit" class="btn btn-primary"><?=lang('update')?></button>
				</div>
				<?php echo form_close();  ?>
            </div>
        </div>
    </div>
</div>