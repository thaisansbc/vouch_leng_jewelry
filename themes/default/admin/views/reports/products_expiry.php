<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = '';

if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= '&category=' . $this->input->post('category');
}
if ($this->input->post('brand')) {
    $v .= '&brand=' . $this->input->post('brand');
}
if ($this->input->post('subcategory')) {
    $v .= '&subcategory=' . $this->input->post('subcategory');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
if ($this->input->post('cf1')) {
    $v .= '&cf1=' . $this->input->post('cf1');
}
if ($this->input->post('cf2')) {
    $v .= '&cf2=' . $this->input->post('cf2');
}
if ($this->input->post('cf3')) {
    $v .= '&cf3=' . $this->input->post('cf3');
}
if ($this->input->post('cf4')) {
    $v .= '&cf4=' . $this->input->post('cf4');
}
if ($this->input->post('cf5')) {
    $v .= '&cf5=' . $this->input->post('cf5');
}
if ($this->input->post('cf6')) {
    $v .= '&cf6=' . $this->input->post('cf6');
}
?>
<script>
    $(document).ready(function () {
         function fsd(oObj) {
            if (oObj != null) {
                var aDate = oObj.split('-');
                if (site.dateFormats.js_sdate == 'dd-mm-yyyy') return aDate[2] + '-' + aDate[1] + '-' + aDate[0];
                else if (site.dateFormats.js_sdate === 'dd/mm/yyyy') return aDate[2] + '/' + aDate[1] + '/' + aDate[0];
                else if (site.dateFormats.js_sdate == 'dd.mm.yyyy') return aDate[2] + '.' + aDate[1] + '.' + aDate[0];
                else if (site.dateFormats.js_sdate == 'mm/dd/yyyy') return aDate[1] + '/' + aDate[2] + '/' + aDate[0];
                else if (site.dateFormats.js_sdate == 'mm-dd-yyyy') return aDate[1] + '-' + aDate[2] + '-' + aDate[0];
                else if (site.dateFormats.js_sdate == 'mm.dd.yyyy') return aDate[1] + '.' + aDate[2] + '.' + aDate[0];
                else return oObj;
            } else {
                return 'N/A';
            }
        }
        function spb(x) {
            v = x.split('__');
            return formatQuantity2(v[0]);
        }
        function sps(x) {
            return formatQuantity2(x);
        }
        function spt(x) {
            v = x.split('__');
            return '<div>' + formatQuantity2(v[0]) + '<span style="width: 45px !important; display: inline-block;">(IN)</span></div>' + '<div style="color: red;">' + formatQuantity2(v[1]) + '<span style="width: 45px !important; display: inline-block;">(OUT)</span></div>';
        }
        function spj(x) {
            v = x.split('__');
            return '<div>' + formatQuantity2(v[0]) + '<span style="width: 45px !important; display: inline-block;">(ADD)</span></div>' + '<div style="color: red;">' + formatQuantity2(v[1]) + '<span style="width: 45px !important; display: inline-block;">(SUB)</span></div>';
        }
        function qnp(x) {
            v = x.split('__');
            var strText = '';
            if(v[1]){
                   strText =  '('+formatQuantity2(v[0])+') <strong>'+v[1]+'</strong>';
            }
            if(v[3]){
                   strText +=  ', ('+formatQuantity2(v[2])+') <strong>'+v[3]+'</strong>';
            }
            if(v[5]){
                   strText +=  ', ('+formatQuantity2(v[4])+') <strong>'+v[5]+'</strong>';
            }
            return strText;
        }
        function spw(x) {
            return '(Kg) ' + '<strong>' + x + '</strong>';
        }
        oTable = $('#PrRData').dataTable({
            "aaSorting": [[3, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getProductsExpiry/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11];
                nRow.className = "product_link2";
                return nRow;
            },
            "aoColumns": [null, null, {"mRender": sps}, {"mRender": spb}, {"mRender": spt}, {"mRender": spj}, {"mRender": spb}, {"mRender": sps}, {"mRender": sps}, {"mRender": fsd}, {"mRender": qnp}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var beginning = 0; var purchases = 0; var transfers = 0; var adjustments = 0; var sold = 0; var returns = 0; var stock = 0;
                var sn = 0, so = 0, sb = 0;
                for (var i = 0; i < aaData.length; i++) {
                    t  = (aaData[aiDisplay[i]][4]).split('__');
                    j  = (aaData[aiDisplay[i]][5]).split('__');
                    s  = (aaData[aiDisplay[i]][6]).split('__');
                    v  = (aaData[aiDisplay[i]][10]).split('__');

                    beginning += parseFloat(aaData[aiDisplay[i]][2]);
                    purchases += parseFloat(aaData[aiDisplay[i]][3]);
                    transfers += parseFloat(t[0]);
                    transfers -= parseFloat(t[1]);
                    adjustments += parseFloat(j[0]);
                    adjustments -= parseFloat(j[1]);
                    sold += parseFloat(s[0]);
                    returns += parseFloat(aaData[aiDisplay[i]][7]);
                    stock += parseFloat(aaData[aiDisplay[i]][8]);

                    sn += parseFloat(v[0]);
                    so += parseFloat(v[2]);
                    sb += parseFloat(v[6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatQuantity2(beginning)+'</div>';
                nCells[3].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatQuantity2(purchases)+'</div>';
                nCells[4].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatQuantity2(transfers)+'</div>';
                nCells[5].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatQuantity2(adjustments)+'</div>';
                nCells[6].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatQuantity2(sold)+'</div>';
                nCells[7].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatQuantity2(returns)+'</div>';
                // nCells[7].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatMoney(returns)+'</div>';
                nCells[8].innerHTML = '<div class="text-right pull-right" style="width: 120px !important;">'+formatQuantity2(stock)+'</div>';
                nCells[10].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">('+formatQuantity2(sn)+')New, ('+formatQuantity2(so)+')Old, ('+formatQuantity2(sb)+')Broken</div>';
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('expiry_date');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('variants');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
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
    });
</script>
<style type="text/css">
    #PrRData td:nth-child(5) {
         font-weight: normal; 
    }
    #PrRData td:nth-child(8), #PrRData td:nth-child(9) {
        text-align: right !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products_expiry'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }?>
        </h2>
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

                    <?php echo admin_form_open('reports/products_expiry'); ?>
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
                                <?= lang('category', 'category') ?>
                                <?php
                                $cat[''] = lang('select') . ' ' . lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang('select') . ' ' . lang('category') . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('subcategory', 'subcategory') ?>
                                <div class="controls" id="subcat_data"> <?php
                                    echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang('select_category_to_load') . '"');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('brand', 'brand') ?>
                                <?php
                                $bt[''] = lang('select') . ' ' . lang('brand');
                                foreach ($brands as $brand) {
                                    $bt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang('select') . ' ' . lang('brand') . '" style="width:100%"')
                                ?>
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

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
                                <?= form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ''), 'class="form-control tip" id="cf1"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf2', 'cf2') ?>
                                <?= form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ''), 'class="form-control tip" id="cf2"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf3', 'cf3') ?>
                                <?= form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ''), 'class="form-control tip" id="cf3"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf4', 'cf4') ?>
                                <?= form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ''), 'class="form-control tip" id="cf4"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf5', 'cf5') ?>
                                <?= form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ''), 'class="form-control tip" id="cf5"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf6', 'cf6') ?>
                                <?= form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ''), 'class="form-control tip" id="cf6"') ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date" autocomplete="off" '); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"  autocomplete="off"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <!-- <div class="clearfix"></div> -->
                <div class="table-responsive">
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
                            <th><?= lang('product_code'); ?></th>
                            <th><?= lang('product_name'); ?></th>
                            <th><?= lang('beginning'); ?></th>
                            <th><?= lang('purchased'); ?></th>
                            <th><?= lang('transfer'); ?></th>
                            <th><?= lang('adjustment'); ?></th>
                            <th><?= lang('sold'); ?></th>
                            <th><?= lang('return'); ?></th>
                            <th><?= lang('stock_in_hand'); ?></th> 
                            <th><?= lang('expiry_date'); ?></th>
                            <th><?= lang('variants'); ?></th>
                            
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th><?= lang('beginning'); ?></th>
                            <th><?= lang('purchased'); ?></th>
                            <th><?= lang('transfer'); ?></th>
                            <th><?= lang('adjustment'); ?></th>
                            <th><?= lang('sold'); ?></th>
                            <th><?= lang('return'); ?></th>
                            <th><?= lang('stock_in_hand'); ?></th>
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
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProductsExpiry/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProductsExpiry/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProductsExpiry/0/0/preview/?v=1' . $v)?>";
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
