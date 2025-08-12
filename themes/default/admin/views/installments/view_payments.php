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
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_payments').' ('.lang('installment').' '.lang('reference').': '.$installment->reference_no.')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?= $this->lang->line("date"); ?></th>
                        <th><?= $this->lang->line("reference_no"); ?></th>
                        <th><?= $this->lang->line("principal_paid"); ?></th>
						<th><?= $this->lang->line("interest_paid"); ?></th>
						<th><?= $this->lang->line("penalty_paid"); ?></th>
						<th><?= $this->lang->line("amount"); ?></th>
						<th><?= $this->lang->line("paid_by"); ?></th>
                        <th><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($payments)) {
                        foreach ($payments as $payment) { ?>
                            <tr class="row<?= $payment->id ?>">
                                <td><?= $this->bpas->hrld($payment->date); ?></td>
                                <td><?= $payment->reference_no; ?></td>
                                <td class="text-right"><?= $this->bpas->formatMoney($payment->amount) . ' ' . (($payment->attachment) ? '<a href="' . admin_url('welcome/download/' . $payment->attachment) . '"><i class="fa fa-chain"></i></a>' : ''); ?></td>
								<td class="text-right"><?= $this->bpas->formatMoney($payment->interest_paid) ?></td>
								<td class="text-right"><?= $this->bpas->formatMoney($payment->penalty_paid) ?></td>
								<td class="text-right"><?= $this->bpas->formatMoney($payment->penalty_paid+$payment->amount + $payment->interest_paid); ?></td>
								<td class="text-center"><?= lang($payment->paid_by); ?></td>
                                <td>
                                    <div class="text-center">
										<?php if($this->config->item("schools")){ ?>
											<a href="<?= admin_url('sales/payment_note/' . $payment->id) ?>" data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>	
										<?php }else{ ?>
											<a href="<?= admin_url('installments/payment_note/' . $payment->id) ?>" data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>	
										<?php } ?>
                                        <a href="<?= admin_url('installments/edit_payment/' . $payment->id) ?>" data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                        <a href="#" class="po" title="<b><?= $this->lang->line("delete_payment") ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payment->id ?>' href='<?= admin_url('installments/delete_payment/' . $payment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>" rel="popover"><i class="fa fa-trash-o"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='8'>" . lang('no_data_available') . "</td></tr>";
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
