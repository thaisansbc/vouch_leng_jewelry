<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}
if ($this->input->post('reference_no')) {
    $v .= '&reference_no=' . $this->input->post('reference_no');
}
if ($this->input->post('project')) {
    $v .= '&project=' . $this->input->post('project');
}
if ($this->input->post('supplier')) {
    $v .= '&supplier=' . $this->input->post('supplier');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
if ($this->input->post('tax_status')) {
    $v .= '&tax_status=' . $this->input->post('tax_status');
}

?>
<script type="text/javascript">

function qnp_table(x) {
     if (x != null) {
         var strText = '',
             c=1,
             pqc = x.split('___');
             strText = '<table border="1" width="100%" style="padding: 3px; table-layout: fixed; word-wrap: break-word;">';
         for (index = 0; index < pqc.length; ++index) {
             var pq = pqc[index];
             var v = pq.split('__');

                strText += '<tr id="'+v[0]+'">';
                strText +=  '<td style="width: 10%; padding-right: 5px; text-align: center;">'+ c +'</td>';
                if(v[3] == 'N/A'){
                    strText +=  '<td style="width: 70%; padding-right: 5px; text-align: center;">'+v[1]+'</td>';
                }else{
                    strText +=  '<td style="width: 70%; padding-right: 5px; text-align: center;">'+v[1]+' ('+v[3]+')</td>';
                }
              
                strText +=  '<td style="width: 14%; padding-right: 5px; text-align: center;">'+formatQuantity2(v[2])+'</td>';
                strText += '</tr>';
                 c++;
         }
         strText += '</table>';
         return strText;
     } else {
         return '';
     }
 }


    // function pqFormat1(x) {
    //     if (x != null) {
    //         var d = '',
    //             pqc = x.split('___');
    //         for (index = 0; index < pqc.length; ++index) {
    //             var pq = pqc[index];
    //             var v = pq.split('__');
    //             d += v[0] + ' (' + formatQuantity2(v[1]) + ')'+'[Exp: '+ v[2]+']<br>';
    //         }
    //         return d;
    //     } else {
    //         return '';
    //     }
    // }

    $(document).ready(function () {
        oTable = $('#PoRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getPurchasesReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[10];
                console.log(aData[10]);
                nRow.className = (aData[6] > 0) ? "purchase_link2" : "purchase_link2 warning";
                return nRow;
            },
            "aoColumns": [
                {"mRender": fld},
                 null,
                 null,
                 null,
                 null,
                 {"bSearchable": false,"mRender": qnp_table},
                 {"mRender": currencyFormat},
                 {"mRender": currencyFormat},
                 {"mRender": currencyFormat},
                 {"mRender": row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                nCells[8].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('customer')) { ?>
        $('#supplier').val(<?= $this->input->post('supplier') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "suppliers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
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
        $('#supplier').val(<?= $this->input->post('supplier') ?>);
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
<style type="text/css">
    body {
        position: static !important;
        overflow-y: auto !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star"></i><?= lang('purchases_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="preview" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
                <li class="dropdown"><a href="#" id="xls_detail" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-table"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open('reports/purchases'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('product', 'suggest_product'); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : '' ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang('reference_no'); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <?php
                                $pro[""] = "";
                                foreach ($projects as $project) {
                                    $pro[$project->project_id] = $project->project_name;
                                }
                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                                ?>
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
                                <?= lang('supplier', 'supplier'); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control" id="supplier"'); ?> 
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                <?php
                                $wh[''] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('tax_status', 'tax_status'); ?>
                                <?php $sst = ['' => lang('Select_VAT'),'vat' => lang('VAT'), 'no_vat' => lang('NO_VAT')];
                                echo form_dropdown('tax_status', $sst, (isset($_POST['tax_status']) ? $_POST['tax_status'] : ''), 'class="form-control" id="tax_status" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('tax') . '"');  ?> 
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PoRData"
                           class="table table-hover table-striped table-condensed reports-table">
                        <thead>
                            <tr class="active">
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('project'); ?></th>
                                <th><?= lang('reference_no'); ?></th>
                                <th><?= lang('warehouse'); ?></th>
                                <th><?= lang('supplier'); ?></th>
                                <?php if($this->Settings->product_expiry == 1){ ?>
                                    <th><?= lang('product(Unit)(Expiry)'); ?></th>
                                <?php } else { ?>
                                    <th><?= lang('product_qty'); ?></th>
                                <?php } ?>
                                <th><?= lang('grand_total'); ?></th>
                                <th><?= lang('paid'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th><?= lang('status'); ?></th>
                            </tr>
                            
                            <tr class="active">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>
                                    <table width="100%" border="1">
                                        <tr>
                                            <td style="width: 10%; text-align: center;">N</td>
                                           <?php if($this->Settings->product_expiry == 1){ ?>
                                                <td style="width: 75%; text-align: center;">Product(Unit)(Expiry)</td>
                                            <?php } else { ?>
                                                <td style="width: 75%; text-align: center;">Product(Unit)</td>
                                            <?php } ?>

                                            <td style="width: 15%; text-align: center;">Qty</td>
                                        </tr>
                                    </table>
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>

                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th style="width:5%!important;"></th>
                            <th style="width:7%!important;"></th>
                            <th style="width:6%!important;"></th>
                            <th style="width:10%!important;"></th>
                            <th style="width:35%!important;"><?= lang('product_qty'); ?></th>
                            <th><?= lang('grand_total'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('balance'); ?></th>
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
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPurchasesReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPurchasesReport/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#xls_detail').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPurchasesReport/0/0/0/excel_deatail/?v=1' . $v)?>";
            return false;
        });
        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getPurchasesReport/0/0/preview/?v=1' . $v)?>";
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