<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('detail_task'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("projects/edit_task/" . $data, $attrib); ?>
        <div class="modal-body">
        	<div class="row">
    			<div class="col-sm-6">
    				<table class="table table-hover">
    					<tbody>
    						<tr>
    							<td>Title</td>
    							<td><?php echo $task->title; ?></td>
    						</tr>
    						<tr>
    							<td>Description</td>
    							<td><?php echo $task->description; ?></td>
    						</tr>
    						<tr>
    							<td>Start Date</td>
    							<td><?php echo $task->start_date; ?></td>
    						</tr>
    						<tr>
    							<td>End Date</td>
    							<td><?php echo $task->end_date; ?></td>
    						</tr>
    						<tr>
    							<?php  
    								$customers = explode(',', $task->user_id);
                                    $username = ''; 
                                    $i = 1;
                                    foreach ($customers as $key => $value) {
                                        if (count($customers) == $i) {
                                            foreach ($users as $key => $user) {
                                                if ($user->id == $value) {
                                                    $username .= $user->last_name.' '.$user->first_name;
                                                }
                                            }
                                        } else {
                                            foreach ($users as $key => $user) {
                                                if ($user->id == $value) {
                                                    $username .= $user->last_name.' '.$user->first_name.',';
                                                }
                                            }
                                        } 
                                        $i++;
                                    }
    							?>
    							<td>Assigned User</td>
    							<td><?php echo $username; ?></td>
    						</tr>
    						<tr>
    							<td>Project Name</td>
    							<td><?php echo $task->project_name; ?></td>
    						</tr>
    						<tr>
    							<td>Milestons</td>
    							<td><?php echo $task->milstone; ?></td>
    						</tr>
    						<tr>
    							<td>Status</td>
    							<td>
    								<?php if ($task->status == 'new') { ?>
                                    <span class="badge badge-secondary" style="background-color: ##007bff;"><?php echo $task->status; ?></span>
                                    <?php }else if ($task->status == 'progress') { ?>
                                    <span class="badge badge-secondary" style="background-color: #17a2b8;"><?php echo $task->status; ?></span>   
                                    <?php }else{ ?>
                                    <span class="badge badge-secondary" style="background-color: #28a745;"><?php echo $task->status; ?></span>
                                    <?php } ?>
    							</td>
    						</tr>
    						<tr>
    							<td>Progress</td>
    							<td>
    								<div class="progress">
                                        <div class="progress-bar progress-bar-primary progress-bar-striped bg-success" role="progressbar" aria-valuenow="<?php echo $task->progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $task->progress; ?>%">
                                          <?php echo $task->progress; ?>%
                                        </div>
                                    </div>
    							</td>
    						</tr>
    					</tbody>
    				</table>
    			</div>
    			<div class="col-sm-6">
    				<span style="margin-left: 30px;">
    					<img width="255px" height="255px" src="<?= $task->icon ? base_url() . 'assets/uploads/tasks/' . $task->icon : base_url('assets/uploads/tasks/no_image.png'); ?>" class="mini_avatar img-rounded">
    				</span>
    			</div>
    		</div>
        		
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