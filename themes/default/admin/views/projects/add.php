<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_project'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("projects/add", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
				<div class="row">
					<?php if ($Owner || $Admin || $GP['change_date']) { ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('date', 'sldate'); ?>
                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                        </div>
                    </div>
                    <?php } ?>
	                <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("biller", "biller"); ?>
                            <?php
                            $bl[""] = lang("select");
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                            }
                            echo form_dropdown('biller_id', $bl, (isset($_POST['biller']) ? $_POST['biller'] :''), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
	                <?php } else {
	                    $biller_input = array(
	                        'type'  => 'hidden',
	                        'name'  => 'biller_id',
	                        'id'    => 'slbiller',
	                        'value' => $user_billers[0],
	                    );
	                    echo form_input($biller_input);
	                } ?>
					<div class="col-sm-4">
						<?= lang('code', 'code'); ?>
						<?= form_input('style_code', set_value('style_code'), 'class="form-control" id="code"'); ?>
					</div>
					<div class="col-sm-4">
						<?= lang('project_name', 'name'); ?>
						<?= form_input('name', set_value('name'), 'class="form-control" id="name" required="required"'); ?>
					</div>
            		
	                <div class="col-sm-4 hide">
						<?= lang('target', 'target'); ?>
						<?= form_input('target', set_value('target'), 'class="form-control" id="line"'); ?>
					</div>
					<div class="col-sm-4">
		                <?= lang('client', 'client'); ?>
		                <?php
		                $client1['']=lang('select') . ' ' . lang('client');
		                foreach ($clients as $client) {
		                    $client1[$client->id] = ($client->company =='-')?$client->name: $client->company.' '.$client->name;
		                }
		                echo form_dropdown('client',$client1, (isset($_POST['client']) ? $_POST['client'] : ''), 'id="client" class="form-control select" placeholder="'.lang('select') . ' ' . lang('client').'" style="width:100%;"');
		                ?>
		            </div>

	            </div>
            </div>
            <div class="form-group hide">
                <?= lang('user', 'user'); ?>
                <?php
                foreach ($users as $user) {
                    $wh[$user->id] = $user->first_name.' '.$user->last_name;
                }
                echo form_dropdown('user[]',$wh, (isset($_POST['user']) ? $_POST['user'] : ''), 'id="user" class="form-control select" placeholder="'.lang('select') . ' ' . lang('user').'" style="width:100%;" multiple="multiple" ');
                ?>
            </div>
			<div class="form-group">
				<div class="row">
					<div class="col-sm-4">
						<?= lang('start_date', 'start_date'); ?>
						<?= form_input('start_date', set_value('start_date'), 'class="form-control datetime" id="year"'); ?>
					</div>
					

					<div class="col-sm-4">
						<?= lang('end_date', 'end_date'); ?>
						<?= form_input('end_date', set_value('end_date'), 'class="form-control datetime" id="season"'); ?>
					</div>
					<div class="col-sm-4">
	                    <?= lang('status', 'status'); ?>
	                    <?php 
	                    $status = [
	                    	'pending' => lang('pending'), 
	                    	'completed' => lang('completed')
	                    ]; ?>
	                    <?= form_dropdown('status',$status,'', 'class="form-control tip" id="hide_price" required="required"'); ?>
		              
		            </div>
		        </div>
	        </div>
			<div class="form-group">
				<div class="row">
					<div class="col-sm-12">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('description', set_value('description'), 'class="form-control"'); ?>
					</div>
				</div>
			</div>
			
        </div>
        <div class="modal-footer">
            <?= form_submit('add', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
	    $('#customer2').select2({
	        minimumInputLength: 1,
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
	});
</script>

<?= $modal_js ?>