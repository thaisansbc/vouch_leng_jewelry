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
            <h4 class="modal-title" id="myModalLabel"><?= lang('credit_note'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width:15%;"><?= $this->lang->line('date'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('reference_no'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('amount'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('note'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($credit_note)) {
         
                        ?>
                                <tr class="row<?= $credit_note->id ?>">
                                    <td><?= $this->bpas->hrld($credit_note->date); ?></td>
                                    <td><?= $credit_note->reference_no; ?></td>
                                    <td><?= $this->bpas->formatMoney($credit_note->grand_total); ?></td>
                                    <td><?= $this->bpas->decode_html($credit_note->note); ?></td>
                                    <td><?= $this->bpas->row_status($credit_note->sale_status); ?></td>
                                </tr>
                        <?php
                            
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