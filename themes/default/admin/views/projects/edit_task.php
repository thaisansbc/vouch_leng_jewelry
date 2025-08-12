<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_task'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("projects/edit_task/" . $data, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php if ($Owner || $Admin) { ?>
            <div class="form-group">
				<div class="row">
					<div class="col-sm-12">
						<?= lang('title', 'title'); ?>
						<?= form_input('name', set_value('name',$task->title), 'class="form-control tip" id="name" required="required"'); ?>
					</div>

				</div>
            </div>
 
			<div class="form-group">
				<div class="row">
					<div class="col-sm-6">
						<?= lang('start_date', 'start_date'); ?>
						<?= form_input('start_date', set_value('start_date',date("d/m/Y H:i:s", strtotime($task->start_date)),false), 'class="form-control datetime"'); ?>
					</div>
					

					<div class="col-sm-6">
						<?= lang('end_date', 'end_date'); ?>
						<?= form_input('end_date', set_value('end_date',date("d/m/Y H:i:s", strtotime($task->end_date)),false), 'class="form-control datetime"'); ?>
					</div>
					
				</div>
            </div>
		
			<div class="form-group">
				<div class="row">
					<div class="col-sm-12">
						<?= lang('task_image', 'task_image') ?>
                    	<input id="task_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="task_image" data-show-upload="false" data-show-preview="true" accept="image/*" class="form-control file">
                    	<input type="hidden" name="update_image" class="form-control" value="<?php echo $task->icon; ?>">
					</div>
					<div class="col-sm-12">
                        <?= lang("project_details", "description") ?>
                        <?= form_textarea('description',  $this->bpas->decode_html($task->description), 'class="form-control" id ="slnote"'); ?>
					</div>
				</div>
			</div>	
			<div class="form-group">
				<div class="row">
					<div class="col-sm-12">
						<?= lang('milestone', 'milestone'); ?>
						<?php // form_input('milestone', set_value('milestone'), 'class="form-control"'); ?>
						<?php
						$wh = [];
						if ($milestones) {
							foreach ($milestones as $milestone) {
			                    $wh[$milestone->id] = $milestone->title.' ['.$milestone->start_date.' To '.$milestone->end_date.']
			                    ';
			                }
						}
		                
		                echo form_dropdown('milestone',$wh, (isset($_POST['milestone']) ? $_POST['milestone'] : $task->milestone_id), 'id="milestone" class="form-control select" placeholder="'.lang('select') . ' ' . lang('milestone').'" style="width:100%;" required="required" ');
		                ?>
					</div>
					

					<div class="col-sm-12">
						<?= lang('assign_to', 'assign_to'); ?>
						<?php // form_input('assign_to', set_value('assign_to'), 'id="customer2" class="form-control"'); ?>
						<?php
						$user_id = explode(',', $task->user_id);
						foreach ($users as $user) {
							$wh1[$user->id] = $user->first_name.' '.$user->last_name;
		                    
		                }
		                
		                echo form_dropdown('assign_to[]',$wh1, (isset($_POST['user']) ? $_POST['user'] : $user_id), 'id="user" class="form-control select" placeholder="'.lang('select') . ' ' . lang('user').'" style="width:100%;" multiple="multiple" required="required" ');
		                ?>
					</div>
				</div>
            </div>
            <?php } ?>
            <div class="form-group">
				<div class="row">
					<div class="col-sm-6">
						<?= lang('status', 'status'); ?>
						<?php
	                        $post = ['new' => lang('New'), 'progress' => lang('In Progress'), 'completed' => lang('Completed')];
	                        echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : $task->status), 'id="status" class="form-control select" placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
	                    ?>
					</div>
					

					<div class="col-sm-6">
						<?= lang('progress number only', 'progress'); ?>
						<?= form_input('progress', set_value('progress',$task->progress), 'class="form-control tip" id="progress" required="required"'); ?>
					</div>
					
				</div>
            </div>	
        </div>
        <div class="modal-footer">
            <?= form_submit('edit', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

<script>
	$(document).ready(function() {
		$('#status').change(function() {
			var status = $(this).val();
			if (status == 'new') {
				$('#progress').val(0);
			} else if (status == 'progress') {
				$('#progress').val(10);
			} else {
				$('#progress').val(100);
			}
		});
		//$( ".taskdate" ).datepicker();
	});
</script>

<?= $modal_js ?>