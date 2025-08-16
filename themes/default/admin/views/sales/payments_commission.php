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
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_payments').' ('.lang('sale').' '.lang('reference').': '.$inv->reference_no.')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:20%;"><?= $this->lang->line("date"); ?></th>
                        <th style="width:20%;"><?= $this->lang->line("reference_no"); ?></th>
                        <th style="width:10%;"><?= $this->lang->line("amount"); ?></th>
                        <th style="width:10%;"><?= $this->lang->line("paid_by"); ?></th>
                        <th style="width:10%;"><?= lang("commision_type", "slsale_status"); ?></th>
                        <?php 
                            if($this->bpas->in_group('sale-agents')){
                                echo '<th style="width:10%;">'.lang("sa", "sa").'</th>';
                            }elseif($this->bpas->in_group('sale-team-leader')) {
                                echo '<th style="width:10%;">'.lang("stl", "stl").'</th>';
                            }else{
                                echo '<th style="width:10%;">'.lang("stl", "stl").'</th>';
                                echo '<th style="width:10%;">'.lang("sa", "sa").'</th>';
                            }
                        ?>
                        <th style="width:10%;"><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($payments)) {
						
                        foreach ($payments as $payment) { 
						$add_payment_link = anchor('admin/sale_property/edit_payment_commission/'.$payment->id.'', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
						?>
                            <tr class="row<?= $payment->id ?>">
                                <td><?= $this->bpas->hrld($payment->date); ?></td>
                                <td><?= $payment->reference_no; ?></td>
                                <td><?= $this->bpas->formatMoney($payment->amount) . ' ' . (($payment->attachment) ? '<a href="' . admin_url('welcome/download/' . $payment->attachment) . '"><i class="fa fa-chain"></i></a>' : ''); ?></td>
                                <td><?= lang($payment->paid_by); ?></td>
                                <td><?= lang($payment->commision_type); ?></td>
                                <?php
                                    if($this->bpas->in_group('sale-agents')){
                                        echo '<td>'.lang($payment->SA).'</td>';
                                    }elseif ($this->bpas->in_group('sale-team-leader')){
                                         echo '<td>'.lang($payment->STL).'</td>';
                                    }else{
                                        echo '<td>'.lang($payment->STL).'</td>';
                                        echo '<td>'.lang($payment->SA).'</td>';
                                    }
                                ?>
 
                                <td>
                                    <div class="text-center">
                                        <?php
                                            if($this->bpas->in_group('sale-agents') || $this->bpas->in_group('sale-team-leader')){
                                                if($payment->commision_type =='' && $payment->note ==''){ ?>
                                                    <a href="#" class="po btn btn-bg-info"
                                               title="<b><?= $this->lang->line("Sent Request") ?></b>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payment->id ?>' href='<?= admin_url('sale_property/sent_request_commision/' . $payment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                               rel="popover"><p><i class="fa fa-plus"></i> Request</p></a>
                                        <?php }elseif ($payment->commision_type =='' && $payment->note !='') {
                                                   echo '<p class="label label-warning">Waiting</p>';
                                                }
                                                else{ echo '<p class="label label-success">Approved</p>';}
                                        } ?>
                                        <?php 
                                            if($this->bpas->in_group('owner') || $this->bpas->in_group('admin')){
                                                if($payment->commision_type =='' && $payment->note !=''){
                                                    echo '<p class="label label-danger">Has Requested</p>';
                                                } ?>
                                        <a class="tip" title='<?= lang('Add Commission') ?>' href="<?= admin_url('sale_property/edit_payment_commission/' . $payment->id) ?>"
                                               data-toggle="modal" data-backdrop="static" data-target="#myModal2">
                                                <?php
                                                    if($payment->commision_type == ''){ 
                                                        echo '<i class="fa fa-plus"></i>';
                                                    }else{ echo '<i class="fa fa-edit"></i>';}
                                                ?>
                                               </a>

                                            <a href="<?= admin_url('sale_property/payment_note/' . $payment->id) ?>"
                                               data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                                            <?php if ($payment->paid_by != 'gift_card') { ?>
                                                
                                                
                                                <a href="#" class="po"
                                                   title="<b><?= $this->lang->line("delete_payment") ?></b>"
                                                   data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payment->id ?>' href='<?= admin_url('sale_property/delete_payment_commision/' . $payment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                                   rel="popover"><i class="fa fa-trash-o"></i></a>
                                            <?php } ?>
                                        <?php } ?>
										
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='5'>" . lang('no_data_available') . "</td></tr>";
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
