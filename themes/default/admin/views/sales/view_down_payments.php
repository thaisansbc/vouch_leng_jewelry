<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    table{
        width: 100%;
        
    }
    #CompTable th,td{
        padding: 5px;
    }
    .th{
        border: 2px solid #000000 !important;
        background: #eeeeee;
    }
    .td{
        border: 2px solid #000000;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            
        </div>
        <div class="modal-body">
            <div class="col-sm-12 text-center"><h4 class="modal-title" id="myModalLabel"><?= lang('តារាងបង់ប្រាក់'); ?></h4></div>
            <?php 
            if(isset($inv->customer) && $this->Settings->module_property){?>
            <table width="100%">
                <tr>
                    <td><?= lang('customer')?></td><td>: <?= $inv->customer; ?></td>
                    <td><?= lang('property')?></td><td>: <?= $this->bpas->formatDecimal($inv->total); ?></td>    
                </tr>
                <tr>
                    <td><?= lang('reference')?></td><td>: <?= $inv->reference_no?></td>
                    <td><?= lang('discount')?></td><td>: <?= $this->bpas->formatDecimal($inv->order_discount); ?></td>
                </tr>
                <tr>
                    <td><?= lang('property')?></td><td>: <?=$inv->iqty;?></td>
                    <td><?= lang('price')?></td><td>: <?= $this->bpas->formatDecimal($inv->grand_total - $inv->order_discount); ?></td>
                </tr>
            </table>
            <?php }?>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table-hover table-striped">
                    <thead>
                        <tr style="background: #eeeeee;">
                            <th class="th" style="width:5%;"><?= lang('ល.រ'); ?></th>
                            <th class="th"><?= lang('ដំណាក់កាល'); ?></th>
                            <th class="th"><?= lang('ទឹកប្រាក់ត្រូវបង់'); ?></th>
                            <th class="th"><?= lang('កាលបរិច្ឆេទបង់ប្រាក់'); ?></th>
                            <th class="th"><?= lang('ផ្សេងៗ'); ?></th>
                            <th class="th"><?= lang('ប្រភេទ'); ?></th>
                            <th class="th no-print"><?= lang('status'); ?></th>
                            <th class="th no-print"><?= lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         if (!empty($down_payments)) {
                            $i=1;
                            foreach ($down_payments as $payment) {
                        ?>
                                <tr class="row<?= $payment->id ?>">
                                    <td class="td"><?= $i;?></td>
                                    <td class="td"><?= $payment->title ?></td>
                                    <td class="td"><?= $this->bpas->formatDecimal($payment->amount); ?></td>
                                    <td class="td"><?= $this->bpas->hrsd($payment->payment_date); ?></td>
                                    <td class="td"><?= $payment->description;?></td>
                                    <td class="td"><?= lang($payment->type) ?></td>
                                    <td class="td no-print">
                                        <?php 
                                        $add_payment_link ='';
                                        $installment_link='';
                                        $edit_link = '';

                                        $status = $payment->status ? 'paid':'pending';
                                        if($payment->status == 1){
                                            
                                            echo '<button style="padding:2px;outline:none;width:100px;" type="button" class="btn btn-success">'.lang($status).'</button>';
                                        }else{
                                            echo '<button style="padding:2px;outline:none;width:100px;" type="button" class="btn btn-warning">'.lang($status).'</button>';
                                            if($payment->type == 'down_payment'){
                                                $add_payment_link = anchor('admin/sales/add_payment/'.$inv->id.'/'. $payment->id, '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payment" data-target="#myModal2"');
                                            }else{
                                                $installment_link = anchor('admin/installments/add/'.$inv->id.'/'. $payment->id, '<i class="fa fa-plus-circle"></i> ' . lang('add_installment'),'class="add_installment"');
                                            }
                                            $edit_link = anchor('admin/sales/edit_downpayment/'.$payment->id, '<i class="fa fa-edit"></i> ' . lang('edit_payment'), 'data-toggle="modal" class="sledit" data-backdrop="static" data-keyboard="false" data-target="#myModal2"');

                                     
                                        }
                                        $delete_link ='';
                                        if ($Owner||$Admin) { 
                                            $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_payment") . "</b>' data-content=\"<p>".lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='<?= $payment->id ?>' href='" . site_url('admin/sales/delete_downpayment/'.$payment->id) . "'>".lang('i_m_sure')."</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                                            . lang('delete_payment') . "</a>";
                                        }
                                        ?>
                                    </td>
                                    <td class="td no-print">
                                        <div class="text-center">
                                            <div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown"><?= lang('actions');?><span class="caret"></span></button>
                                                <ul class="dropdown-menu pull-right" role="menu">
                                                    <li><?= $add_payment_link; ?></li>
                                                    <li><?= $installment_link; ?></li>
                                                    <li><?= $edit_link; ?></li>
                                                    <li><?= $delete_link; ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            $i++;
                            }
                        } else {
                            echo "<tr><td colspan='7'>" . lang('no_data_available') . '</td></tr>';
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