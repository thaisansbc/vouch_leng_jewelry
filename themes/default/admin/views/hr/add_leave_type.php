<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_leave_type'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_leave_type"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
				<?php echo lang('category', 'category'); ?>
				<div class="controls">
					<?php
					$category_ops= array();
                    if (isset($categories) && $categories != false) {
                        foreach ($categories as $category) {
                           $category_ops[$category->id] = $category->name; 
                        }
                    }
					
					echo form_dropdown('category', $category_ops, 0, 'id="category" class="form-control category" required="required"');
					?>
				</div>
			</div>
            <div class="form-group">
                <?php echo lang('code', 'code'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="code" required="required"/>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('name', 'name'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="name" required="required"/>
                </div>
            </div>

			<div class="form-group">
                <?php echo lang('limit_day', 'limit_day'); ?>
                <div class="controls">
                    <input type="text" class="form-control" name="limit_day"/>
                </div>
            </div>
			
			<div class="form-group">
				<?php echo lang('include_holiday', 'include_holiday'); ?>
				<div class="controls">
					<?php
					$include_holiday_ops[0]=lang('no');
					$include_holiday_ops[1]=lang('yes');
					echo form_dropdown('include_holiday', $include_holiday_ops, 0, 'id="include_holiday" class="form-control include_holiday"');
					?>
				</div>
			</div>
			
			<div class="form-group">
                <?php echo lang('description', 'description'); ?>
                <div class="controls">
                    <textarea name="description" class="form-control"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>