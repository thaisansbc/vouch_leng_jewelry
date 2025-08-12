<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = '';
if ($this->input->post('payment_ref')) {
    $v .= '&payment_ref=' . $this->input->post('payment_ref');
}
if ($this->input->post('paid_by')) {
    $v .= '&paid_by=' . $this->input->post('paid_by');
}
if ($this->input->post('sale_ref')) {
    $v .= '&sale_ref=' . $this->input->post('sale_ref');
}
if ($this->input->post('biller')) {
    $v .= '&biller=' . $this->input->post('biller');
}
if ($this->input->post('supplier')) {
    $v .= '&supplier=' . $this->input->post('supplier');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('cheque')) {
    $v .= '&cheque=' . $this->input->post('cheque');
}
if ($this->input->post('tid')) {
    $v .= '&tid=' . $this->input->post('tid');
}
if ($this->input->post('card')) {
    $v .= '&card=' . $this->input->post('card');
}
if ($this->input->post('type')) {
    $v .= '&type=' . $this->input->post('type');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
if (isset($start)) {
    $v .= '&start=' . $start;
}
if (isset($end)) {
    $v .= '&end=' . $end;
}
?>
<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('reports/payments_voucher'); ?>'; 
    });
</script>
<script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }
        function ref(x) {
            return (x != null) ? x : ' ';
        }
        var oTable = $('#PayRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getPaymentsVoucherReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            }, 
            'fnRowCallback': function( nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var oSettings = $('#PayRData').dataTable().fnSettings();
                $('td:first', nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                $(nRow).attr('id','row_' + aData.id);

                if (aData[8] == 'sent') {
                    nRow.className = "warning";
                } else if (aData[8] == 'returned') {
                    nRow.className = "danger";
                }
                return nRow;
            },
            "aoColumns": [
                // NO, Date, Ref, Customer, Paid By, Total Pay, Discount, Grand Total, Type, Action
                {"bSortable": false, "bSearchable": false},
                {"mRender": fld}, {"mRender": ref}, null, 
                {"mRender": paid_by}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat},
                {"mRender": row_status}, 
                {"bSortable": false}
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total_pay = 0, discount = 0, grand_total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total_pay   += parseFloat(aaData[aiDisplay[i]][5]);
                    discount    += parseFloat(aaData[aiDisplay[i]][6]);
                    grand_total += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(parseFloat(total_pay));
                nCells[6].innerHTML = currencyFormat(parseFloat(discount));
                nCells[7].innerHTML = currencyFormat(parseFloat(grand_total));
            }
        })
        .fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer")
        ;
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('biller')) { ?>
            $('#rbiller').select2({ allowClear: true });
        <?php } ?>
        <?php if ($this->input->post('supplier')) { ?>
        $('#rsupplier').val(<?= $this->input->post('supplier') ?>).select2({
            minimumInputLength: 1,
            allowClear: true,
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?= admin_url('suppliers/getSupplier') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        $('#rsupplier').val(<?= $this->input->post('supplier') ?>);
        <?php } ?>
        <?php if ($this->input->post('customer')) { ?>
        $('#rcustomer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            allowClear: true,
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?= admin_url('customers/getCustomer') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<style>
    #dtFilter-filter--PayRData-8 { text-align: center; }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('payments_voucher_report'); ?> 
        <?php if ($this->input->post('start_date')) {
            echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
        } ?>
        </h2>
        <div class="box-icon">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                               id="daterange" class="form-control">
                        <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="preview" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo admin_form_open('reports/payments_voucher'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('payment_ref', 'payment_ref'); ?>
                                <?php echo form_input('payment_ref', (isset($_POST['payment_ref']) ? $_POST['payment_ref'] : ''), 'class="form-control tip" id="payment_ref"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('purchase_ref', 'purchase_ref'); ?>
                                <?php echo form_input('purchase_ref', (isset($_POST['purchase_ref']) ? $_POST['purchase_ref'] : ''), 'class="form-control tip" id="purchase_ref"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                            <?=lang('paid_by', 'paid_by');?>
                                <select name="paid_by" id="paid_by" class="form-control paid_by">
                                    <?= $this->bpas->paid_opts($this->input->post('paid_by'), false, true); ?>
                                    <?=$pos_settings && $pos_settings->paypal_pro ? '<option value="ppp">' . lang('paypal_pro') . '</option>' : '';?>
                                    <?=$pos_settings && $pos_settings->stripe ? '<option value="stripe">' . lang('stripe') . '</option>' : '';?>
                                    <?=$pos_settings && $pos_settings->authorize ? '<option value="authorize">' . lang('authorize') . '</option>' : '';?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('supplier', 'rsupplier'); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control" id="rsupplier" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('supplier') . '"'); ?> 
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="rbiller"><?= lang('biller'); ?></label>
                                <?php
                                $bl[''] = '';
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="rbiller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('transaction_id', 'tid'); ?>
                                <?php echo form_input('tid', (isset($_POST['tid']) ? $_POST['tid'] : ''), 'class="form-control" id="tid"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('card_no', 'card'); ?>
                                <?php echo form_input('card', (isset($_POST['card']) ? $_POST['card'] : ''), 'class="form-control" id="card"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('cheque_no', 'cheque'); ?>
                                <?php echo form_input('cheque', (isset($_POST['cheque']) ? $_POST['cheque'] : ''), 'class="form-control" id="cheque"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                <?php
                                $us[''] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                            <?=lang('type', 'type');?>
                                <select name="type" id="type" class="form-control paid_by">
                                    <option value=""><?= lang('please_selected');?></option>
                                    <option value="sent"><?= lang('sent');?></option>
                                    <option value="received"><?= lang('received');?></option>
                                    <option value="returned"><?= lang('returned');?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control date" id="start_date" autocomplete=off'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control date" id="end_date" autocomplete=off'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="PayRData" class="table table-hover table-condensed reports-table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 5% !important;"><?= lang('Nº'); ?></th>
                                <th style="width: 12% !important;"><?= lang('date'); ?></th>
                                <th style="width: 12% !important;"><?= lang('payment_ref'); ?></th>
                                <th><?= lang('supplier'); ?></th>
                                <th><?= lang('paid_by'); ?></th>
                                <th style="text-align: right !important;"><?= lang('total_pay'); ?></th>
                                <th style="text-align: right !important;"><?= lang('discount'); ?></th>
                                <th style="text-align: right !important; width: 12% !important;"><?= lang('grand_total'); ?></th>
                                <th style="text-align: center !important;"><?= lang('type'); ?></th>
                                <th style="text-align: center !important; width: 10% !important;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="width: 5% !important;"><?= lang('no'); ?></th>
                                <th style="width: 12% !important;"></th>
                                <th style="width: 12% !important;"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="width: 12% !important;"></th>
                                <th></th>
                                <th style="text-align: center !important; width: 10% !important;"><?= lang('actions'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPaymentsVoucherReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPaymentsVoucherReport/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPaymentsVoucherReport/0/0/preview/?v=1' . $v)?>";
            return false;
        });
        $('#transfer').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPaymentsVoucherReport/0/0/0/transfer/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>