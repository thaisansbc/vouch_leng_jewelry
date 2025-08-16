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
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_deposits').' ('.lang('sale_order').' '.lang('reference').': '.$saleorder->reference_no.')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?= $this->lang->line("date"); ?></th>
                        <th><?= $this->lang->line("reference_no"); ?></th>
                        <th><?= $this->lang->line("amount"); ?></th>
						<th><?= $this->lang->line("paid_by"); ?></th>
                        <th><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($deposits)) {
                        foreach ($deposits as $deposit) { ?>
                            <tr class="row<?= $deposit->id ?>">
                                <td><?= $this->bpas->hrld($deposit->date); ?></td>
                                <td><?= $deposit->reference_no; ?></td>
                                <td style="text-align:right !important"><?= $this->bpas->formatMoney($deposit->amount) . ' ' . (($deposit->attachment) ? '<a href="' . admin_url('welcome/download/' . $deposit->attachment) . '"><i class="fa fa-chain"></i></a>' : ''); ?></td>
								<td><?= lang($deposit->paid_by); ?></td>
                                <td>
                                    <div class="text-center">
                                        <a href="<?= admin_url('sales_order/deposit_note/' . $deposit->id) ?>"
                                           data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                                        <?php if ($deposit->paid_by != 'gift_card') { ?>
                                            <a href="<?= admin_url('sales_order/edit_deposit/' . $deposit->id) ?>"
                                               data-toggle="modal" data-target="#myModal2"><i
                                                    class="fa fa-edit"></i></a>
                                            <a href="#" class="po"
                                               title="<b><?= $this->lang->line("delete_deposit") ?></b>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $deposit->id ?>' href='<?= admin_url('sales_order/delete_deposit/' . $deposit->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                               rel="popover"><i class="fa fa-trash-o"></i></a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='6'>" . lang('no_data_available') . "</td></tr>";
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
