<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<script type="text/javascript">
    $(document).ready(function () {
    	localStorage.setItem('customer2', '<?= $data->customer_id ?>');
	    $('#customer2').val('<?= $data->customer_id ?>').select2({
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
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_project'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("projects/edit/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<div class="row">
					<?php if ($Owner || $Admin || $GP['change_date']) { ?>
            		<div class="col-md-6">
                        <div class="form-group">
                            <?= lang('date', 'sldate'); ?>
                            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($data->date)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                        </div>
                    </div>
                    <?php } ?>
	                <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("biller", "biller"); ?>
                            <?php
                            $bl[""] = "";
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                            }
                            echo form_dropdown('biller_id', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $data->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
						<?= lang('project_code', 'style_code'); ?>
						<?= form_input('style_code', set_value('style_code', $data->project_code), 'class="form-control tip" id="style_code"'); ?>
					</div>
					<div class="col-sm-4">
						<?= lang('project_name', 'name'); ?>
						 <?= form_input('name', set_value('name', $data->project_name), 'class="form-control tip" id="name" required="required"'); ?>
					</div>
	                <div class="col-sm-4 hide">
						<?= lang('target', 'target'); ?>
						<?= form_input('target', set_value('target',$data->target), 'class="form-control tip" id="line"'); ?>
					</div>
					<div class="col-sm-4">
		                <?= lang('client', 'client'); ?>
		                <?php
		                $client1['']=lang('select') . ' ' . lang('client');
		                foreach ($clients as $client) {
		                    $client1[$client->id] = $client->company.'-'.$client->name;
		                }
		                echo form_dropdown('client',$client1, (isset($_POST['client']) ? $_POST['client'] : $data->clients_id), 'id="client" class="form-control select" placeholder="'.lang('select') . ' ' . lang('client').'" style="width:100%;"');
		                ?>
		            </div>
	            </div>
            </div>
            <div class="form-group hide">
                <?= lang('user', 'user'); ?>
				<?php
				$warehouse_id = explode(',', $data->customer_id);
                foreach ($users as $user) {
                    $wh[$user->id] = $user->username;
                }
                echo form_dropdown('user[]',$wh, (isset($_POST['user']) ? $_POST['user'] : $warehouse_id), 'id="user" class="form-control select" placeholder="'.lang('select') . ' ' . lang('user').'" style="width:100%;" multiple="multiple" ');
                ?>
            </div>
         
			<div class="form-group">
				<div class="row">
					<div class="col-sm-4">
						<?= lang('start_date', 'start_date'); ?>
						<?= form_input('start_date', set_value('start_date', $this->bpas->hrld($data->start_date)), 'class="form-control datetime" id="year"'); ?>
					</div>
					

					<div class="col-sm-4">
						<?= lang('end_date', 'end_date'); ?>
						<?= form_input('end_date', set_value('end_date', $this->bpas->hrld($data->end_date)), 'class="form-control datetime" id="season"'); ?>
					</div>
					<div class="col-sm-4">
	                    <?= lang('status', 'status'); ?>
	                    <?php 
	                    $status = [
	                    	'pending' => lang('pending'), 
	                    	'completed' => lang('completed')
	                    ]; ?>
	                    <?= form_dropdown('status',$status, $data->status, 'class="form-control tip" id="hide_price" required="required"'); ?>
		            </div>
		        </div>
	        </div>
		
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_textarea('description', $data->description, 'class="form-control" id="description"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('edit', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
    	localStorage.setItem('customer2', '<?= $data->customer_id ?>');
	    $('#customer2').val('<?= $data->customer_id ?>').select2({
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
