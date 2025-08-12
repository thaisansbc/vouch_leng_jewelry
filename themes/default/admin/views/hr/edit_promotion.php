<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_promotion'); ?></h4>
        </div>

		<?php 
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo admin_form_open("hr/edit_promotion/".$id, $attrib); 
		?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-lg-12">
					<div class="form-group">
						<?php echo lang('employee_type', 'employee_type'); ?>
						<div class="controls">
							<?php
								$tp[''] = lang("select") . " " .lang("employee_type");
								foreach($types as $type){
									$tp[$type->id] = $type->name;
								}
								echo form_dropdown('employee_type', $tp, $row->employee_type_id, 'id="employee_type" required class="form-control" ');
							?>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('position', 'position'); ?>
						<div class="controls">
							<?php
								$pot[''] = lang("select") . " " .lang("position");
								foreach($positions as $position){
									$pot[$position->id] = $position->name;
								}
								echo form_dropdown('position', $pot, $row->position_id, 'id="position" required class="form-control" ');
							?>
						</div>
					</div>
                    <div class="form-group all">
                        <?= lang('employee_level', 'employee_level'); ?>
                        <div class="input-group" style="width: 100%">
                            <?php 
                            $form_category = null;
                            function formMultiLevelCategory($data, $n, $str = '', $p_category_id)
                            {
                                $form_category = ($n ? '<select id="employee_level" name="employee_level" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" required="required"><option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
                                foreach ($data as $key => $categories) {
                                    if (!empty($categories->children)) {
                                        $form_category .= '<option disabled>' . $str . $categories->name . '</option>';
                                        $form_category .= formMultiLevelCategory($categories->children, 0, ($str.'&emsp;&emsp;'), $p_category_id);
                                    } else {
                                        if ($p_category_id == $categories->id) 
                                            $form_category .= ('<option value="' . $categories->id . '" selected>' . $str . $categories->name . '</option>');
                                        else 
                                            $form_category .= ('<option value="' . $categories->id . '">' . $str . $categories->name . '</option>');
                                    }
                                }

                                $form_category .= ($n ? '</select>' : '');
                                return $form_category;
                            }
                            echo formMultiLevelCategory($employee_levels, 1, '',$row->employee_level); ?>
                        </div>
                    </div>
	                      
					<div class="form-group">
						<?php echo lang('department', 'department'); ?>
						<div class="controls">
							<?php
								$dpt[''] = lang("select") . " " .lang("department");
								foreach($departments as $department){
									$dpt[$department->id] = $department->name;
								}
								echo form_dropdown('department', $dpt, $row->department_id, 'id="department" required class="form-control" ');
							?>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('promoted_date', 'promoted_date'); ?>
						<div class="controls">
							<input type="text" value="<?= $this->bpas->hrsd($row->promoted_date); ?>" name="promoted_date" class="form-control date" required/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('official_promote', 'official_promote'); ?>
						<div class="controls">
							<input type="text" name="official_promote" value="<?= $this->bpas->hrsd($row->official_promote); ?>" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('promoted_by', 'promoted_by'); ?>
						<div class="controls">
							<?php
								$emp[] = lang("select") . " " .lang("promoted_by");
								foreach($employees as $employee){
									$emp[$employee->id] = $employee->firstname." - ".$employee->lastname;
								}
								echo form_dropdown('promoted_by', $emp, $row->promoted_by, 'id="promoted_by" class="form-control" ');
							?>
						</div>
					</div>
			
                    <div class="form-group">
                        <?= lang("attachment", "attachment") ?>
                        <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachment" data-show-upload="false" data-show-preview="false" class="form-control file">
                    </div>
           
					<div class="form-group">
						<?php echo lang('description', 'description'); ?>
						<div class="controls">
							<textarea id="description" name="description" class="form-control"><?= $row->description ?></textarea>
						</div>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_promotion', lang('edit_promotion'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>