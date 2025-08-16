<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
        .modal-content { page-break-after: auto; }
    }
</style>
<style>
    .border_tage {
        border-collapse: collapse;
    }
    .data_afd {
        color: #ce6301;
        border-color: inherit;
        text-align: center;
        vertical-align: top
    }
    .data_afd_t {
        border-color: inherit;
        text-align: center;
        vertical-align: top
    }
    .data_afd_n {
        color: #ce6301;
        border-color: inherit;
        text-align: right;
        vertical-align: top
    }

    .data_afd_l {
        /* color: #ce6301; */
        border-color: inherit;
        text-align: right;
        vertical-align: top
    }
</style>

<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">

            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button><br>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="border_tage print">
                <table style="width:100%; background-color:white;">
                    <tbody>
                        <tr>
                            <td width="25%">
                                <?php if ($biller->logo) {
                                ?>
                                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                                <?php
                                } ?>
                            </td>
                            <td width="40%" style="text-align:center;vertical-align: top;">
                                <h2>បង្កាន់ដៃទទួលប្រាក់ / RECEIPT</h2>

                                <h2><?= ($biller->company !='-')? $biller->company:$biller->name; ?></h2>
                                ទូរស័ព្ទ / <?= lang('phone'); ?>: <span><?= $biller->phone ?></span><br>
                                អាសយដ្ឋាន / <?= lang('address'); ?>: <span><?= $biller->address ?></span>
                            </td>
                            <td>
                                លេខសក្ខីប័ត្រ / Receipt No : <span style="font-weight:bold;"><?= $payment->reference_no; ?></span><br>
                                កាលបរិច្ឆេទ / <?= lang('date'); ?>: <span style="font-weight:bold;"><?= $this->bpas->hrsd($payment->date); ?></span><br> 
                        </tr>
                    </tbody>
                </table>
                <div>
                    <br>
                    <table class="table border_px" style="margin-bottom:0;width:100%">
                        <tbody class="border_px">
                            <tr class="border_px">
                                <td class="border_px" colspan="5">បានទទួលពី / Received From: <b><?= ($customer->company !='-') ? $customer->company : $customer->name; ?>
                                </b><br></td>
                            </tr>
                            <tr class="border_px">
                                <td class="border_px" colspan="5">ទឹកប្រាក់ / The Sum of Amount: <b>
                                    <?php echo $this->bpas->numberToWords($payment->amount); ?> dollar<?= ($payment->amount > 1)? 's':'';?> only 
                                     ($<?= $this->bpas->formatMoney($payment->amount);?>)
                                </b><br></td>
                            </tr>
                            <tr class="border_px">
                                <td class="border_px" colspan="5">សំគាល់ / Being payment of: <b><?php echo $this->bpas->remove_tag($payment->note); ?>
                                    
                                </b><br></td>
                            </tr>

                            <tr class="border_px">
                                <td rowspan="2" class="border_px">ទូរទាត់ដោយ<br>
                                    Mode of Payment</td>
                                <td colspan="1"><input type="checkbox" value="Cash" style="margin:10px;" />Cash <input type="checkbox" value="Bank" style="margin:10px;" />Bank </td>
                                <td colspan="1"><input type="checkbox" value="Cash" style="margin:10px;" />KHR<input type="checkbox" value="Bank" style="margin:10px;" />USD</td>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />T.T<input type="checkbox" value="Bank" style="margin:10px;" />Cheque No:</td>
                            </tr>
                            <tr>
                                <td colspan="2">Cash / Bank Name:</td>
                                <td colspan="2"><input type="checkbox" value="Bank" style="margin:10px;" />Bank Account No:</td>
                            </tr>
                          
                            <tr>
                                <td colspan="3">&nbsp;</td>
                                <td><br><br>
                                Received By: ---------------------------</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>