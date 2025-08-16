<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_paybacks') ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?= $this->lang->line("date"); ?></th>
                        <th><?= $this->lang->line("amount"); ?></th>
						<th><?= $this->lang->line("paid_by"); ?></th>
                        <th><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($paybacks)) {
                        foreach ($paybacks as $payback) { ?>
                            <tr class="row<?= $payback->id ?>">
                                <td><?= $this->bpas->hrld($payback->date); ?></td>
                                <td style="text-align:right !important"><?= $this->bpas->formatMoney($payback->amount)  ; ?></td>
								<td><?= lang($payback->paid_by); ?></td>
                                <td>
                                    <div class="text-center">
										<a href="<?= admin_url('payrolls/edit_payback/' . $payback->id) ?>"data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                        <a href="#" class="po" title="<?= $this->lang->line("delete_payback") ?>" data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payback->id ?>' href='<?= admin_url('payrolls/delete_payback/' . $payback->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>" rel="popover"><i class="fa fa-trash-o"></i></a>
										<?= (($payback->attachment) ? '<a href="' . admin_url('welcome/download/' . $payback->attachment) . '"><i class="fa fa-chain"></i></a>' : '') ?>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='4'>" . lang('no_data_available') . "</td></tr>";
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

    });
</script>
