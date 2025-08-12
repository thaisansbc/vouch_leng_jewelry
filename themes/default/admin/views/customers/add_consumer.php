<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_consumer') . ' (' . $company->name . ')'; ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('customers/add_consumer/' . $company->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <?php echo lang('first_name', 'first_name'); ?>
                        <div class="controls">
                            <?php echo form_input('first_name', '', 'class="form-control" id="first_name" required="required" pattern=".{3,10}"'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo lang('last_name', 'last_name'); ?>
                        <div class="controls">
                            <?php echo form_input('last_name', '', 'class="form-control" id="last_name" required="required"'); ?>
                        </div>
                    </div>

                   
                </div>
                <div class="col-md-6">
                 <div class="form-group">
                        <?php echo lang('gender', 'gender'); ?>
                        <div class="controls">
                            <?php $opts = ['male' => lang('male'), 'female' => lang('female')];
                            echo form_dropdown('gender', $opts, '', 'class="form-control select" id="gender" required="required"'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo lang('phone', 'phone'); ?>
                        <div class="controls">
                            <?php echo form_input('phone', '', 'class="form-control" id="phone"'); ?>
                        </div>
                    </div>
                </div>
                    <div class="clearfix"></div>

            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_consumer', lang('add_consumer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

