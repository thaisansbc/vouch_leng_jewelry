<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('approved_status'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
            echo admin_form_open_multipart("purchases_request/approved_status/" . $id, $attrib); ?>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?= lang('multi_approval_status'); ?>
                    </div>
                    <div class="panel-body">
                    <table class="table table-condensed table-striped table-borderless" style="margin-bottom:0;">
                        <tbody>
                        <?php
                      
                        foreach($PersonApproved as $key => $val){
                            if($val){
                                $log_user_id = $this->session->userdata('user_id');
                                if ($Owner || $Admin || in_array($log_user_id, explode(",", $val))) {
                                ?>
                            <tr>
                            <td><?= lang('' . $key . ''); ?></td>
                            <td>
                                <div class="form-group">
                                    <?php $opts = array(''=>lang('waiting'), 'approved' => lang('approved'), 'rejected' => lang('rejected'), 'unapproved' => lang('unapproved')); ?>
                                    <?php $x = explode('_by', $key); $g = $x[0].'_status'; ?>  
                                    <?= form_dropdown('' . $key . '', $opts, (isset($approved->$g) ? $approved->$g : ''), 'class="form-control" style="width:100%;"'); ?>  
                                </div>
                            </td>
                            </tr>
                        <?php
                            }
                        }
                    } ?>
                        </tbody>
                    </table>
                </div>
            </div>
          <!--   <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->bpas->decode_html($approved->note)), 'class="form-control" id="note"'); ?>
            </div> -->
        </div>
        <?php if (!$returned) { ?>
        <div class="modal-footer">
            <?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
        <?php } ?>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
