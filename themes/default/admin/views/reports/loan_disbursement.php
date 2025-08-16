<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('loan_reference_no')) {
    $v .= "&loan_reference_no=" . $this->input->post('loan_reference_no');
}
if ($this->input->post('borrower')) {
    $v .= "&borrower=" . $this->input->post('borrower');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('currency')) {
    $v .= "&currency=" . $this->input->post('currency');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
        oTable = $('#LoanDisbursementTable').dataTable({
            "aaSorting": [[1, "desc"], [3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('reports/getLoanDisbursementReport?v=1&'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                return nRow;
            },
            "aoColumns": [
            {"mRender" : fld},
            null,
            null,
            null,
            null,
            null,
            {"mRender" : currencyFormat},
            {"sClass" : "text-center"},
            {"sClass" : "text-center"},
            {"sClass" : "text-center"},
            {"mRender" : currencyFormat},
            {"mRender" : currency_status},
            {"mRender" : row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var principal_amount =0, disbursement_amount =0;
                for (var i = 0; i < aaData.length; i++) {
                    principal_amount += parseFloat(aaData[aiDisplay[i]][6]);
                    disbursement_amount += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(principal_amount));
                nCells[10].innerHTML = currencyFormat(parseFloat(disbursement_amount));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('loan_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('borrower_code');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('borrower');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('loan_product');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('interest_rate');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('frequency');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('term');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('currency');?>]", filter_type: "text", data: []},
            {column_number: 12, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php echo admin_form_open("reports/loan_disbursement", ' id="form-submit" '); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('loan_disbursement_report'); ?></h2>
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
                
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("reference_no", "reference_no"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("loan_reference_no", "loan_reference_no"); ?>
                                <?php echo form_input('loan_reference_no', (isset($_POST['loan_reference_no']) ? $_POST['loan_reference_no'] : ""), 'class="form-control tip" id="loan_reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="borrower"><?= lang("borrower"); ?></label>
                                <?php echo form_input('borrower', (isset($_POST['borrower']) ? $_POST['borrower'] : ""), 'class="form-control" id="borrower" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("borrower") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="currency"><?= lang("currency"); ?></label>
                                <?php
                                $cu[""] = lang('select').' '.lang('currency');
                                foreach ($currencies as $currency) {
                                    $cu[$currency->code] = $currency->name;
                                }
                                echo form_dropdown('currency', $cu, (isset($_POST['currency']) ? $_POST['currency'] : ""), 'class="form-control" id="currency" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("currency") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="product"><?= lang('product') ?></label>
                                <?php
                                    $tp[''] = lang('select').' '.lang('product');
                                    if($products){
                                        foreach ($products as $product) {
                                            $tp[$product->id] = $product->name;
                                        }
                                    }
                                    echo form_dropdown('product', $tp, (isset($_POST['product']) ? $_POST['product'] : 0), ' class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("product") . '" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : $this->bpas->hrld(date("Y-m-d H:i"))), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : $this->bpas->hrld(date("Y-m-d H:i"))), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> 
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <div class="table-responsive">
                    <table id="LoanDisbursementTable" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th width="160px"><?= lang("date"); ?></th>
                            <th width="160px"><?= lang("reference_no"); ?></th>
                            <th width="160px"><?= lang("loan_reference_no"); ?></th>
                            <th width="200px"><?= lang("borrower_code"); ?></th>
                            <th width="200px"><?= lang("borrower"); ?></th>
                            <th width="200px"><?= lang("loan_product"); ?></th>
                            <th width="120px"><?= lang("principal_amount"); ?></th>
                            <th width="120px"><?= lang("interest_rate"); ?></th>
                            <th width="120px"><?= lang("frequency"); ?></th>
                            <th width="120px"><?= lang("term"); ?></th>
                            <th width="120px"><?= lang("disbursement") ?></th>
                            <th width="120px"><?= lang("currency") ?></th>
                            <th style="width:20px;"><?= lang("type"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
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
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getLoanDisbursementReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getLoanDisbursementReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        
        $('form[data-toggle="validator"]').bootstrapValidator({ feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'}, excluded: [':disabled'] });
        var borrower = "<?= isset($_POST['borrower'])?$_POST['borrower']:0; ?>";
        $('#borrower').val(borrower).select2({
           minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"loans/getBorrower/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                }); 
            },
               ajax: {
                url: site.base_url+"loans/borrower_suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if(data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    });
</script>