<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('request_status'). " " . $inv->sale_reference_no; ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form'); 
        echo admin_form_open_multipart("sales/remove_rejected/". $inv->id, $attrib); ?>
   
         <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= lang('sale_details'); ?>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed table-striped table-borderless" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td><?= lang('reference_no'); ?></td>
                                <td><?= $inv->reference_no; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('sale_reference_no'); ?></td>
                                <td><?= $inv->sale_reference_no; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('created_by'); ?></td>
                                <td><?= $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('status'); ?></td>
                                <td><strong><?= lang($inv->status); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?= lang('noted'); ?></td>
                                <td><?= $this->bpas->decode_html($inv->note); ?></td>
                            </tr> 
                            <tr>
                                <td><?= lang('attachment'); ?></td>
                                <td><?php if($inv->attachment != null){ ?>
                                        <img src="<?= 'assets/uploads/' . $inv->attachment; ?> " alt="<?= $inv->attachment; ?>" width="100%" height="150" style="background-color: #FDFDFD;" /> 
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <?= lang('status', 'status'); ?>
                <?php $opts = ['request' => lang('request'), 'rejected' => lang('rejected')]; ?>
                <?= form_dropdown('status', $opts, $inv->status, 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
            </div> 
           
            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->bpas->decode_html($inv->note)), 'class="form-control" id="note"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('submit_request', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>