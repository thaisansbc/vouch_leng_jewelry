<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    function checkboxss(x) {
        console.log(x);
        return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" /></div>';
    }
    $(document).ready(function () {
        oTable = $('#SLData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('sales_order/getSalesOrder' . ($biller_id ? '/' . $biller_id : '') . '?v=1' . ($this->input->get('shop') ? '&shop=' . $this->input->get('shop') : '') . ($this->input->get('attachment') ? '&attachment=' . $this->input->get('attachment') : '') . ($this->input->get('delivery') ? '&delivery=' . $this->input->get('delivery') : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name()?>",
                    "value": "<?= $this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
              
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[9]);
                nRow.className = "sale_order_link re" + aData[9];
                var action = $('td:eq(14)', nRow);

                if(aData[6] != 'order' || aData[11] == 'partial' || aData[11] == 'completed'){
                    action.find('.unapproved').remove();
                    action.find('.rejected').remove();
                }
                if (aData[6] == 'sale' || aData[6] == 'completed' || aData[11] == 'partial' || aData[11] == 'completed') {
                    action.find('.edit').remove();
                    action.find('.delete').remove();
                }
                if (aData[10] == 'approved') {
                    action.find('.edit').remove();
                    action.find('.approved').remove();
                    action.find('.delete').remove();
                }
                if (aData[10] == 'pending' || aData[10] == 'rejected' || aData[11] == 'partial' || aData[11] == 'completed') {
                    action.find('.add').remove();
                    action.find('.unapproved').remove();
                    action.find('.view_deposit').remove();
                    action.find('.add_deposit').remove(); 
                    if (aData[10] == 'rejected' || aData[11] == 'partial' || aData[11] == 'completed') {
                        action.find('.reject').remove();
                    }
                }
                if (aData[6] == 'sale' || aData[6] == 'completed' || aData[10] == 'pending' || aData[10] == 'rejected' || aData[11] == 'completed') {
                    action.find('.add_delivery').remove();
                }
                return nRow;
            },
            "aoColumns": [
                { "bSortable": false, "mRender": checkboxss },
                {"mRender": fld}, 
                null, null, null, null, 
                {"mRender": row_status}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, 
                {"mRender": approved_status3}, 
                {"mRender": row_status}, 
                null, 
                {"bSortable": false,"mRender": attachment}, 
                {"bSortable": false}
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal      += parseFloat(aaData[aiDisplay[i]][7]);
                    paid        += parseFloat(aaData[aiDisplay[i]][8]);
                    balance     += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells          = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                nCells[9].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('sale_status');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('order_status');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('delivery_status');?>]", filter_type: "text", data: []},
        ], "footer");
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('srref')) {
                localStorage.removeItem('srref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slsaleman_by')) {
                localStorage.removeItem('slsaleman_by');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            localStorage.removeItem('remove_slls');
        }
        <?php if ($this->session->userdata('remove_slls')) { ?>
        if (localStorage.getItem('slitems')) {
            localStorage.removeItem('slitems');
        }
        if (localStorage.getItem('sldiscount')) {
            localStorage.removeItem('sldiscount');
        }
        if (localStorage.getItem('sltax2')) {
            localStorage.removeItem('sltax2');
        }
        if (localStorage.getItem('srref')) {
            localStorage.removeItem('srref');
        }
        if (localStorage.getItem('slshipping')) {
            localStorage.removeItem('slshipping');
        }
        if (localStorage.getItem('slwarehouse')) {
            localStorage.removeItem('slwarehouse');
        }
        if (localStorage.getItem('slnote')) {
            localStorage.removeItem('slnote');
        }
        if (localStorage.getItem('slinnote')) {
            localStorage.removeItem('slinnote');
        }
        if (localStorage.getItem('slcustomer')) {
            localStorage.removeItem('slcustomer');
        }
        if (localStorage.getItem('slbiller')) {
            localStorage.removeItem('slbiller');
        }
        if (localStorage.getItem('slsaleman_by')) {
            localStorage.removeItem('slsaleman_by');
        }
        if (localStorage.getItem('slcurrency')) {
            localStorage.removeItem('slcurrency');
        }
        if (localStorage.getItem('sldate')) {
            localStorage.removeItem('sldate');
        }
        if (localStorage.getItem('slsale_status')) {
            localStorage.removeItem('slsale_status');
        }
        if (localStorage.getItem('slpayment_status')) {
            localStorage.removeItem('slpayment_status');
        }
        if (localStorage.getItem('paid_by')) {
            localStorage.removeItem('paid_by');
        }
        if (localStorage.getItem('amount_1')) {
            localStorage.removeItem('amount_1');
        }
        if (localStorage.getItem('paid_by_1')) {
            localStorage.removeItem('paid_by_1');
        }
        if (localStorage.getItem('pcc_holder_1')) {
            localStorage.removeItem('pcc_holder_1');
        }
        if (localStorage.getItem('pcc_type_1')) {
            localStorage.removeItem('pcc_type_1');
        }
        if (localStorage.getItem('pcc_month_1')) {
            localStorage.removeItem('pcc_month_1');
        }
        if (localStorage.getItem('pcc_year_1')) {
            localStorage.removeItem('pcc_year_1');
        }
        if (localStorage.getItem('pcc_no_1')) {
            localStorage.removeItem('pcc_no_1');
        }
        if (localStorage.getItem('cheque_no_1')) {
            localStorage.removeItem('cheque_no_1');
        }
        if (localStorage.getItem('slpayment_term')) {
            localStorage.removeItem('slpayment_term');
        }
        <?php $this->bpas->unset_data('remove_slls'); } ?>
        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
        $(document).on('click', '.slduplicate', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });
</script>
<?php 
    if ($Owner || $GP['bulk_actions']) {
        echo admin_form_open('sales_order/sale_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?=lang('list_sales_order') . ' (' . $biller_title . ')';?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang('actions')?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?=admin_url('sales_order/add')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_sale_order')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('sales_order') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('sales_order/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company .'/'. $biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)){ ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('sales_order') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = $count_billers;
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id==$value) {
                                        echo '<li><a href="' . admin_url('sales_order/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company .'/'. $biller->name . '</a></li>';
                                    }
                                }
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php if (SHOP) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-list-alt tip" data-placement="left" title="<?=lang('sales')?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li<?= $this->input->get('shop') == 'yes' ? ' class="active"' : ''; ?>><a href="<?=admin_url('sales?shop=yes')?>"><i class="fa fa-shopping-cart"></i> <?=lang('shop_sales')?></a></li>
                        <li<?= $this->input->get('shop') == 'no' ? ' class="active"' : ''; ?>><a href="<?=admin_url('sales?shop=no')?>"><i class="fa fa-heart"></i> <?=lang('staff_sales')?></a></li>
                        <li<?= !$this->input->get('shop') ? ' class="active"' : ''; ?>><a href="<?=admin_url('sales')?>"><i class="fa fa-list-alt"></i> <?=lang('all_sales')?></a></li>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?=lang('list_results');?></p>
                <div class="table-responsive">
                    <table id="SLData" class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('project'); ?></th>
                                <th><?= lang('reference_no'); ?></th>
                                <th><?= lang('biller'); ?></th>
                                <th><?= lang('customer'); ?></th>
                                <th style="text-align: center !important;"><?= lang('sale_status'); ?></th>
                                <th style="text-align: right !important;"><?= lang('grand_total'); ?></th>
                                <th style="text-align: right !important;"><?= lang('deposit'); ?></th>
                                <th style="text-align: right !important;"><?= lang('balance'); ?></th>
                                <th style="text-align: center !important;"><?= lang('order_status'); ?></th>
                                <th style="text-align: center !important;"><?= lang('delivery_status'); ?></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th></th>
                                <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="14" class="dataTables_empty"><?= lang('loading_data'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th></th><th></th><th></th><th></th><th></th>
                                <th></th>
                                <th><?= lang('grand_total'); ?></th>
                                <th><?= lang('deposit'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th></th>
                                <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>