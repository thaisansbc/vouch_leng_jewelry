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

?>


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

          
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PoRData"
                           class="table table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('reference_no'); ?></th>
                            <th><?= lang('supplier'); ?></th>
                            <th><?= lang('product_name'); ?></th>
                            <th><?= lang('expiry'); ?></th>
                            <th><?= lang('product_qty'); ?></th>
                            <th><?= lang('cost'); ?></th>
                            <th><?= lang('total'); ?></th>
                            <th><?= lang('discount'); ?></th>
                            <th><?= lang('tax'); ?></th>
                            <th><?= lang('balance'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($rows)) {
                                foreach($rows as $value){
                            ?>
                            <tr>
                                <td><?= $this->bpas->hrld($value->date);?></td>
                                <td><?= $value->reference_no;?></td>
                                <td><?= $value->supplier;?></td>
                                <td><?= $value->product_name;?></td>
                                <td><?= $value->expiry;?></td>
                                <td><?= $value->quantity;?></td>
                                <td><?= $value->unit_cost;?></td>
                                <td><?= $value->quantity * $value->unit_cost;?></td>
                                <td><?= $value->item_discount;?></td>
                                <td><?= $value->item_tax;?></td>
                                <td><?= $value->subtotal;?></td>
                            </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
               
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