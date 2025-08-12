<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_membership'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("customers/add_membership"); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?php echo lang('name', 'name'); ?>
                <div class="controls">
                    <input type="text" class="form-control" id="name" name="name" required="required"/>
                </div>
            </div>
            
            <div class="form-group">
                <div class="row">
                    <div class="col">
                         <div class="col-md-6">
                             <div class="form-group">
                                <?php echo lang('membership_period', 'membership_period'); ?>
                                <div class="controls">
                                 <input type="number" class="form-control" id="membership_period" name="membership_period" required="required"/>
                                </div>
                             </div>  
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                    <?= lang('period_type', 'period_type'); ?>
                                    <?php
                                    $period= [
                                        'weeks'      => lang('weeks'),
                                        'months'     => lang('months')
                                    ];
                                    echo form_dropdown('period_type', $period, null, 'class="form-control tip" id="period_type" required="required" style="width:100%;"');
                                    ?>
                            </div>   
                        </div>
                        <div class="col-md-12">
                             <div class="form-group">
                                <?php echo lang('class', 'class'); ?>
                                <div class="controls">
                                 <input type="text" class="form-control" id="class" name="class" required="required"/>
                                </div>
                             </div>  
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('price', 'price'); ?>
                <div class="controls">
                    <input type="text" class="form-control" id="price" name="price" required="required"/>
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