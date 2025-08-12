<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_status'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('projects/update_status/' . $inv->project_id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= $inv->project_name; ?>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed table-striped table-borderless" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td><?= lang('Project'); ?></td>
                                <td><?= $inv->project_name; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('approve_status'); ?></td>
                                <td><strong><?= lang($inv->approve_status); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?= lang('status'); ?></td>
                                <td><?= lang($inv->status); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-group">
                <?= lang('approve_status', 'status'); ?>
                <?php
                $opts = ['pending' => lang('pending'),'approved' => lang('approved'), 'rejected' => lang('rejected')]; ?>
                <?= form_dropdown('status', $opts, $inv->approve_status, 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->bpas->decode_html($inv->approve_note)), 'class="form-control" id="note"'); ?>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
