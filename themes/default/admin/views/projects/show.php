<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('user_access'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("projects/edit/".$id, $attrib); ?>
        <div class="modal-body">
            <div class="form-group">
                
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                        </tr>
                    </thead>
	                <tbody>
                        <?php
                        $customer_id = explode(',', $data->customer_id);
                        foreach ($users as $user) {
                            $wh[$user->id] = $user->username;
                            foreach ($customer_id as $key => $value) {
                                if ($user->id==$value) {
                        ?>
		                <tr>
		                    <td><?= $user->first_name; ?> <?= $user->last_name; ?></td>
                            <td><?= $user->username; ?></td>
		                </tr>
                        <?php } }}?>
	                </tbody>
	            </table>
	        	
            </div>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
