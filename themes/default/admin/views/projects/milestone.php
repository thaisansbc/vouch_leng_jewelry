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
            <h4 class="modal-title" id="myModalLabel"><?= $inv->project_name; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:30%;"><?= $this->lang->line('title'); ?></th>
                        <th style="width:30%;"><?= $this->lang->line('start_date'); ?></th>
                        <th style="width:15%;"><?= $this->lang->line('end_date'); ?></th>
                        <th style="width:10%;"><?= $this->lang->line('actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($milestones)) {
				    foreach ($milestones as $milestone) {
				        ?>
                            <tr class="row<?= $milestone->milestones_id ?>">
                            	<td><?= $milestone->milestones_title; ?></td>
                                <td><?= $this->bpas->hrld($milestone->milestones_start_date); ?></td>
	                             <td><?= $this->bpas->hrld($milestone->milestones_end_date); ?></td>
                                <td>
                    <div class="text-center">
                        <a href="<?= admin_url('sales/payment_note/' . $milestone->milestones_id) ?>"
                           data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                      
                            <a href="#" class="po"
                               title="<b><?= $this->lang->line('delete_payment') ?></b>"
                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $milestone->milestones_id ?>' href='<?= admin_url('sales/delete_payment/' . $milestone->milestones_id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                               rel="popover"><i class="fa fa-trash-o"></i></a>
                     
                    </div>
                                </td>
                            </tr>
                        <?php
    }
} else {
    echo "<tr><td colspan='5'>" . lang('no_data_available') . '</td></tr>';
} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $(document).on('click', '.po-delete', function () {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });
        $(document).on('click', '.email_payment', function (e) {
            e.preventDefault();
            var link = $(this).attr('href');
            $.get(link, function(data) {
                bootbox.alert(data.msg);
            });
            return false;
        });
    });
</script>
