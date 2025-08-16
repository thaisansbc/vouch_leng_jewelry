<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('multi_approved'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('system_settings/multi_approved/' . $group->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php 
                if($multi_approved){
            ?>
            <div class="form-group">
                 <?= lang('form', 'form'); ?>
                <?php
                    $form = [
                        'so'           => lang('sale_order'),
                        'pr'           => lang('purchase_request'),
                        'po'           => lang('purchase_order'),
                        'sr'           => lang('stock_received'),
                    ];
                    echo form_dropdown('form', $form, (isset($_POST['form']) ? $_POST['form'] : $all_multi_approved->form), 'class="form-control tip" style="width:100%;"');
                ?>
            </div>
          
            <?php
            // var_dump($multi_approved);
            foreach($multi_approved as $key  => $value){
                ?>
                    <div class="form-group">
                         <?= lang($key, $key); ?>
                         <?php
                        if($users){
                            $m_value = explode(',', $value);
                            foreach ($users as $user) {
                                $bls[$user->id] = $user->first_name.' '.$user->last_name;
                            }

                            echo form_dropdown(''.$key.'[]', $bls, (isset($_POST['approved_by']) ? $_POST['approved_by'] : $m_value), 'class="form-control select" data-placeholder="' . lang('select') . ' ' . lang($key) . '" style="width:100%;" multiple="multiple"');
                        }
                        ?>
                        <!-- <input type="text" class="form-control" style="width: 100%" name=""> -->
                    </div>
                <?php 
                }
            }
            ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('multi', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
