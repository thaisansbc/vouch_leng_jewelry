<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_custom_field'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th><?= $this->lang->line('name'); ?></th>
                            <th><?= $this->lang->line('description'); ?></th>
                            <th><?= $this->lang->line('discount'); ?></th>
                            <th><?= $this->lang->line('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($custom_fields)) {
                            foreach ($custom_fields as $row) {
                        ?>
                            <tr>
                                <td><?= $row->name; ?></td>
                                <td><?= $row->description; ?></td>
                                <td><?= lang($row->discount); ?></td>
                                <td>
                                    <a href="<?= admin_url('system_settings/edit_custom_field/' . $row->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                    <a href="#" class="po" title="<b><?= $this->lang->line('delete_custom_field') ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $row->id ?>' href='<?= admin_url('system_settings/delete_custom_field/' . $row->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>" rel="popover"><i class="fa fa-trash-o"></i></a>
                                   
                                  
                                </td>
                            </tr>
                        <?php
                            }
                        }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $(document).on('click', '.po-delete', function() {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });
        $(document).on('click', '.email_payment', function(e) {
            e.preventDefault();
            var link = $(this).attr('href');
            $.get(link, function(data) {
                bootbox.alert(data.msg);
            });
            return false;
        });
    });
</script>