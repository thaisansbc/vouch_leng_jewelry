<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('import_promotions'); ?></h4>
        </div>
        <?php
        $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/import_promotions', $attrib)
        ?>
        <div class="modal-body">
            <div class="form-group">
                <label for="name"><?php echo lang("description",'description'); ?></label>
                <div
                    class="controls"> <?php echo form_input('description', '', 'class="form-control" id="description" required="required"'); ?> 
				</div>
            </div>
            <div class="form-group">
                    <?= lang('start_date', 'start_date'); ?>
                    <?php echo form_input('start_date', '', 'class="form-control tip date" id="start_date" required="required"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('end_date', 'end_date'); ?>
                    <?php echo form_input('end_date', '', 'class="form-control tip date" id="end_date" required="required"'); ?>
                </div>

		    	<div class="form-group">
	                	<?= lang('warehouse', 'warehouse'); ?>
		                <?php
		                foreach ($warehouses as $warehouse) {
		                    $wh[$warehouse->id] = $warehouse->name;
		                }
		                echo form_dropdown('warehouse',$wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" class="form-control select" placeholder="'.lang('select') . ' ' . lang('warehouse').'" style="width:100%;" required="required" ');
		                ?>
	            </div>
                <div class="form-group">
	                	<?= lang('promotion_type', 'promotion_type'); ?>
		                <?php
		                $ps = ['0' => lang('---- Select_one ----'), '1' => lang('by_category'),'2' => lang('by_product')];
		                echo form_dropdown('promotion_type',$ps, (isset($_POST['promotion_type']) ? $_POST['promotion_type'] : ''), 'id="promotion_type" class="form-control select" placeholder="'.lang('select') . ' ' . lang('promotion_type').'" style="width:100%;" required="required" ');
		                ?>
	            </div>
            <a href="<?php echo base_url(); ?>assets/excel/sample_promotions.xlsx" class="btn btn-primary pull-right">
                <i class="fa fa-download"></i> <?= lang('download_sample_file') ?>
            </a>
            <p><?= lang('enter_infomation'); ?></p>
             <div class="col-md-12">
                <div class="form-group">
                    <?= lang('upload_file', 'excel_file') ?>
                    <input id="excel_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="excel_file" data-bv-notempty="true" data-show-upload="false"
                        data-show-preview="false" class="form-control file">
                </div>  
            </div>

            <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

