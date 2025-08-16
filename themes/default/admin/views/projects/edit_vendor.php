<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_vendor'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("projects/edit_vendor/".$vendor->id, $attrib); ?>
        <input type="hidden" name="project_id" value="<?= $vendor->project_id;?>">
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
				<div class="row">
					<?= lang('date', 'date'); ?>
					<?= form_input('date', $this->bpas->hrld($vendor->date), 'class="form-control datetime"'); ?>
				</div>
			</div>
			<div class="form-group">
				<?= lang('vendor', 'vendor'); ?>
				<?php
				foreach ($suppliers as $supplier) {
					$wh1[$supplier->id] = $supplier->name;
                    
                }
                echo form_dropdown('vendor',$wh1, (isset($_POST['vendor']) ? $_POST['vendor'] : $vendor->supplier_id), 'id="vendor" class="form-control select" placeholder="'.lang('select') . ' ' . lang('vendor').'" style="width:100%;" required="required" ');
                ?>
            </div>
            <div class="form-group">
                <?= lang("description", "description") ?>
                <?= form_textarea('description', $this->bpas->decode_html($vendor->description), 'class="form-control" id ="slnote"'); ?>	
			</div>
        </div>
        <div class="modal-footer">
            <?= form_submit('edit', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
</script>

