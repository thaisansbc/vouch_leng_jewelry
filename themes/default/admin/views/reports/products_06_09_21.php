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
    function zero(x) {
        return 0;//'('+formatQuantity2(v[0])+') <strong>'+formatMoney(v[1])+'</strong>';
    }
    $(document).ready(function () {
        function spb(x) {
            v = x.split('__');
            return formatQuantity2(v[0]);
            //return '('+formatQuantity2(v[0])+') <strong>'+formatMoney(v[1])+'</strong>';
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
            'sAjaxSource': '<?= admin_url('reports/getProductsReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                // nRow.id = aData[8];
                // nRow.className = "product_link2";
                // return nRow;
            },
            "aoColumns": [null, null, {"mRender": spb}, {"mRender": spb}, {"mRender": spb}, {"mRender": spb}, {"mRender": spb}, {"mRender": spb}, {"mRender": spb} /*, {"mRender": spw} */, {"mRender": qnp}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var pq = 0, sq = 0, bq = 0, pa = 0, sa = 0, ba = 0, pl = 0, pd = 0, sn = 0, so = 0, sb = 0, pw = 0, pr = 0, tq = 0, ta = 0, stq = 0, sta = 0, aj = 0; 
                for (var i = 0; i < aaData.length; i++) {
                    t  = (aaData[aiDisplay[i]][2]).split('__');
                    p  = (aaData[aiDisplay[i]][3]).split('__');
                    q  = (aaData[aiDisplay[i]][4]).split('__');
                    b  = (aaData[aiDisplay[i]][5]).split('__');
                    b  = (aaData[aiDisplay[i]][6]).split('__');
                    st = (aaData[aiDisplay[i]][8]).split('__');
                    s  = (aaData[aiDisplay[i]][9]).split('__');
                    tq += parseFloat(t[0]);
                    ta += parseFloat(t[1]);
                    bq += parseFloat(b[0]);
                    ba += parseFloat(b[1]);
                    pq += parseFloat(p[0]);
                    pa += parseFloat(p[1]);
                    sq += parseFloat(q[0]);
                    sa += parseFloat(q[1]);
                    stq += parseFloat(st[0]);
                    sta += parseFloat(st[1]);
                    sn += parseFloat(s[0]);
                    so += parseFloat(s[2]);
                    sb += parseFloat(s[6]);
                    aj += parseFloat(aaData[aiDisplay[i]][5]);
                    pr += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">('+formatQuantity2(tq)+') </div>';
                nCells[3].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">('+formatQuantity2(pq)+') </div>';
                nCells[4].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">('+formatQuantity2(sq)+') </div>';
                nCells[5].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatMoney(aj)+'</div>';
                nCells[6].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">('+formatQuantity2(bq)+') </div>';
                nCells[7].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">'+formatMoney(pr)+'</div>';
                nCells[8].innerHTML = '<div class="text-right pull-right" style="width: 120px !important;">('+formatQuantity2(stq)+') </div>';
                nCells[9].innerHTML = '<div class="text-right pull-right" style="width: 100px !important;">('+formatQuantity2(sn)+')New, ('+formatQuantity2(so)+')Old, ('+formatQuantity2(sb)+')Broken</div>';
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('variants');?>]", filter_type: "text", data: []},
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
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?></h2>

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

                    <?php echo admin_form_open('reports/products'); ?>
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
                            <th><?= lang('stock_qty'); ?></th>
                            <th><?= lang('variants'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
            window.location.href = "<?=admin_url('reports/getProductsReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProductsReport/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProductsReport/0/0/preview/?v=1' . $v)?>";
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
