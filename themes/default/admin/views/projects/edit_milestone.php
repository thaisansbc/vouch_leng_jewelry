<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_milestne'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("projects/edit_milestone/" . $data, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
				<div class="row">
					<div class="col-sm-12">
						<?= lang('title', 'title'); ?>
						<?= form_input('name', set_value('name',$milestone->title), 'class="form-control tip" id="name" required="required"'); ?>
					</div>

				</div>
            </div>
 
			<div class="form-group">
				<div class="row">
					<div class="col-sm-6">
						<?= lang('start_date', 'start_date'); ?>
						<?= form_input('start_date', set_value('start_date',date("d/m/Y H:i", strtotime($milestone->start_date))), 'class="form-control datetime" id="year"'); ?>
					</div>
					

					<div class="col-sm-6">
						<?= lang('end_date', 'end_date'); ?>
						<?= form_input('end_date', set_value('end_date',date("d/m/Y H:i", strtotime($milestone->end_date))), 'class="form-control datetime" id="season"'); ?>
					</div>
					
				</div>
            </div>
		
			<div class="form-group hide">
				<div class="row">
					<div class="col-sm-12">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('description', $this->bpas->decode_html($milestone->description), 'class="form-control"'); ?>
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