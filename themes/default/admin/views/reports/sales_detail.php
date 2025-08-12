<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('saleman_by')) {
    $v .= "&saleman=" . $this->input->post('saleman_by');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('product_id')) {
    $v .= "&product=" . $this->input->post('product_id');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('sale_type')) {
    $v .= "&sale_type=" . $this->input->post('sale_type');
}
if ($this->input->post('payment_status')) {
    $v .= "&payment_status=" . $this->input->post('payment_status');
}

?>
<script type="text/javascript" src="<?= $assets ?>js/core.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>datetimepicker/js/jquery.datetimepicker.js"></script>
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
        $("#product").autocomplete({
            source: '<?= admin_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);               
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
    });
</script>
<style type="text/css">
    .numeric { text-align: right !important; }
    #POData .active th,#POData .foot td{
        color: #fff;
        background-color: #428BCA !important;
        border-color: #357ebd;
    }
    -webkit-print-color-adjust: exact; 
    #POData > tbody > tr > td:nth-child(1), #POData > tbody > tr.active > th:nth-child(1) {
        width: 2.5% !important;
        text-align: center;
    }
   
    #POData > tbody > tr > td:nth-child(2), #POData > tbody > tr.active > th:nth-child(2),
    #POData > tbody > tr > td:nth-child(3), #POData > tbody > tr.active > th:nth-child(3) {
        width: 10% !important;
    }
    #POData > tbody > tr > td:nth-child(4), #POData > tbody > tr.active > th:nth-child(4) {
        width: 20% !important;
    }
    #POData > tbody > tr > td:nth-child(5), #POData > tbody > tr.active > th:nth-child(5),
    #POData > tbody > tr > td:nth-child(6), #POData > tbody > tr.active > th:nth-child(6),
    #POData > tbody > tr > td:nth-child(7), #POData > tbody > tr.active > th:nth-child(7),
    #POData > tbody > tr > td:nth-child(8), #POData > tbody > tr.active > th:nth-child(8) {
        width: 10% !important;
    } 
    #POData > tbody > tr:nth-child(4) > td:nth-child(4),
    #POData > tbody > tr.foot > td:nth-child(4),
    #POData > tbody > tr > td:nth-child(4) {
        width: 10% !important;  
    }
    #POData > tbody > tr > td:nth-child(9), #POData > tbody > tr.active > th:nth-child(9) {
        width: 12% !important;
    }
    @media print {
        .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
            border-top: 1px solid #000000 !important;
        }
        @page {
            size: A4;
        }
        .HT { display: block !important; margin: 0; padding: 0; }
        thead { display: table-header-group; }

        #POData .active th,#POData .foot td{
            color: #fff;
            background-color: #5DADE2 !important;
        }
        body {-webkit-print-color-adjust: exact;}
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star"></i><?= strtoupper(lang('report_sale_by_invoice_detail')) . ' (' . ($customer_details ? $customer_details->name : lang('All_Customer')) . ')'; ?> - <?= lang('from') . ' ' . $this->bpas->hrsd($start_date) . ' 00:00:00 ' . lang('to') . ' ' . $this->bpas->hrsd($end_date) . ' 23:59:59'; ?></h2>
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
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="print_new_form" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="print" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="print_logo" class="tip" title="<?= lang('print_logo') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>  
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?=lang('list_results');?></p>
                <div id="form">
                    <?php echo admin_form_open("reports/sales_detail"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("customer", "customer"); ?>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                $pr[""] = "";
                                foreach ($products as $product_) {
                                    $pr[$product_->id] = $product_->name . " | " . $product_->code;
                                }
                                echo form_dropdown('product_id', $pr, (isset($_POST['product_id']) ? $_POST['product_id'] : ""), 'class="form-control" id="product_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="project"><?= lang("project"); ?></label>
                                <?php
                                if ($Owner || $Admin) {
                                    $bl[""] = "";
                                    foreach ($billers as $biller_) {
                                        $bl[$biller_->id] = $biller_->company != '-' ? $biller_->company : $biller_->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                } else {
                                    $user_pro[""] = "";
                                    // foreach ($user_billers as $user_biller) {
                                    $user_pro[$user_billers->id] = $user_billers->company;
                                    // }
                                    echo form_dropdown('biller', $user_pro, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse_) {
                                    $wh[$warehouse_->id] = $warehouse_->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="saleman_by"><?= lang('saleman'); ?></label>
                                <?php
                                $sm[''] = lang('select') . ' ' . lang('saleman');
                                if (!empty($salemans)) {
                                    foreach ($salemans as $saleman_) {
                                        $sm[$saleman_->id] = $saleman_->first_name . ' ' . $saleman_->last_name;
                                    }
                                }
                                echo form_dropdown('saleman_by', $sm, (isset($_POST['saleman_by']) ? $_POST['saleman_by'] : ''), 'class="form-control" id="saleman_by" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('saleman') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control date" id="start_date" autocomplete=off'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control date" id="end_date" autocomplete=off'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('sale_type', 'sale_type'); ?>
                                <?php $sales = ['' => 'All','0' => 'SALE','1' => 'POS']; ?>
                                <?= form_dropdown('sale_type', $sales, (isset($_POST['sale_type']) ? $_POST['sale_type'] : ''), 'class="form-control tip" id="sale_type"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="payment_status"><?= lang('payment_status'); ?></label>
                                <?php
                                $ps[''] = ['' => lang('select') . ' ' . lang('status'), 'paid' => lang('paid'), 'unpaid' => lang('unpaid')];
                                echo form_dropdown('payment_status', $ps, (isset($_POST['payment_status']) ? $_POST['payment_status'] : ''), 'class="form-control" id="saleman_by" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('reports/sale_detail_actions', 'id="action-form"');
                } ?>
                <div class="clearfix"></div>
                <!-- <div class="table-responsive"> -->
                <div>
                    <?php if (!empty($company_info)) { ?>
                    <div class="row hide" id="company_info">
                        <div class="col-xs-2">
                            <div><img style="width: 180px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$company_info->logo; ?>" ></div>                                
                        </div>
                        <div class="col-xs-8" style="padding-left: 0; text-align: center;">
                            <h2 style="font-weight: bold; font-family: 'Khmer OS Muol Light';"><?= $company_info->cf1; ?></h2>
                            <h2 style="font-weight: bold; font-family: 'FontAwesome';"><?= $company_info->company && $company_info->company != '-' ? $company_info->company : $company_info->name; ?></h2>
                            <div style="font-size:14px; font-weight: bold; line-height: normal; text-align: center;">
                                <?php
                                    echo '<p style="letter-spacing: 3px;">' . $company_info->cf3 . '</p>';
                                    echo '<p>' . $company_info->cf2 . '</p>';
                                    if($company_info->address){
                                        echo '<p>' . $company_info->address . '' . $company_info->postal_code . '' . $company_info->city . ' ' . $company_info->country . '</p>';
                                    }
                                    if($company_info->phone){
                                        echo '<p>Tel: ' . $company_info->phone . '</p>';
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="col-xs-2" style="margin-top: 15px;">&nbsp;</div>
                    </div>
                    <?php } ?>
                    <center class="hide HT"><h2 style="margin: 0;">របាយការណ៍លក់តាមវិក័យលំអិត / Sales Detail Report By Invoice</h2></center><br>
                    <div class="hide HT" style="border: 1px solid black; padding: 10px; margin-bottom: 10px;">
                        <?php if (!empty($customer_details)) { ?>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="width: 33% !important;">
                                        <table>
                                            <tr>
                                                <td><?= lang('customer'); ?></td>
                                                <td style="width: 8px !important;"> :</td>
                                                <td><?= $customer_details->name; ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('contact'); ?></td>
                                                <td style="width: 8px !important;"> :</td>
                                                <td><?= $customer_details->phone; ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('address'); ?></td>
                                                <td style="width: 8px !important;"> :</td>
                                                <td><?= $customer_details->address; ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="width: 33% !important;">
                                        <table>
                                            <tr>
                                                <td style="width: 30%;"><?= lang('from_date'); ?></td>
                                                <td style="width: 3px !important;"> :</td>
                                                <td><?= $this->bpas->hrsd($start_date) . ' 00:00'; ?></td>
                                            </tr>
                                            <tr rowspan="2">
                                                <td><?= lang('salesman'); ?></td>
                                                <td> :</td>
                                                <td id="salesman" style="width: 60% !important;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="width: 33% !important;">
                                        <table>
                                            <tr>
                                                <td style="padding-left: 10px; width: 41%;"><?= lang('to_date'); ?></td>
                                                <td style="width: 8px !important; padding-left: 10px;"> :</td>
                                                <td style="padding-left: 10px;"><?= $this->bpas->hrsd($end_date) . ' 23:59'; ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding-left: 10px;"><?= lang('total_invoice'); ?></td>
                                                <td style="width: 8px !important; padding-left: 10px;"> :</td>
                                                <td style="padding-left: 10px;" id="total_invoice"></td>
                                            </tr>
                                            <tr>
                                                <td style="padding-left: 10px;"><?= lang('total_delivery'); ?></td>
                                                <td style="width: 8px !important; padding-left: 10px;"> :</td>
                                                <td style="padding-left: 10px;" id="total_delivery"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        <?php } ?>
                    </div>
                    <table id="POData" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-bordered table-hover table-striped">
                        <tr class="active">
                            <th class="text-center"><?php echo $this->lang->line("Nº") ?></th>
                            <th class="text-center"><?php echo $this->lang->line("date"); ?></th>
                            <th class="text-center"><?php echo $this->lang->line("reference"); ?></th>
                            <th class="text-center"><?php echo $this->lang->line("product_code") . ' (' . lang('name') . ')'; ?></th>
                            <th class="text-center"><?php echo $this->lang->line("quantity"); ?></th>
                            <th class="text-right"><?php echo $this->lang->line("unit_price"); ?></th>
                            <th class="text-right"><?php echo $this->lang->line("tax"); ?></th>
                            <th class="text-right"><?php echo $this->lang->line("discount"); ?></th>
                            <th class="text-right"><?php echo $this->lang->line("subtotal"); ?></th>
                        </tr>

                        <?php 
                            $total_grand_total = 0; $total_paid = 0; $total_balance = 0;
                            if(!empty($customers)) {
                            foreach($customers as $cus){ 
                                $total_invoice  = 0; 
                                $total_delivery = 0; 
                                $salesman_by    = null;
                                $sales = $this->reports_model->getCustomersPending('sale', null, $cus->id, $product, $reference, $biller, $warehouse, $saleman, $start_date, $end_date, $sale_type, $payment_status);
                                if(is_array($sales)){
                                    $total_invoice = count($sales);
                            ?>
                            <tr>
                                <th class="th_parent" colspan="9" style="background-color: #66CDAA;"><?= lang("customer") ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i> <?= $cus->customer ?></th>
                            </tr>
                            <?php
                                $grand_total = 0; $paid = 0; $balance = 0;
                                foreach($sales as $inv){
                                    $total_delivery += $inv->total_delivery;
                                    if ($inv->saleman_by) {
                                        $user = $this->site->getUserByID($inv->saleman_by);
                                        $salesman_by[$inv->saleman_by] = $user->first_name . ' ' . $user->last_name;
                                    }
                            ?>
                                    <tr class="noBorder">
                                        <td colspan="4" style="border:0; text-align: left; background-color: #D3D3D3;"><?= $inv->reference_no ?></td>
                                        <td style="background-color: #D3D3D3;"></td>
                                        <td style="background-color: #D3D3D3;"></td>
                                        <td style="background-color: #D3D3D3;"></td>
                                        <td style="background-color: #D3D3D3;"></td>
                                        <td style="background-color: #D3D3D3;"></td>
                                    </tr>
                            <?php
                                    $return_sale = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
                                    $items = $this->reports_model->getCustomersPending('item', $inv->id);
                                    if(is_array($items)){
                                        foreach($items as $item_index => $row){
                            ?>
                                        <tr>
                                            <td><?= $item_index + 1 ?></td>
                                            <td><?=$this->bpas->hrld($inv->date)?></td>
                                            <td><?= $inv->reference_no ?></td>
                                            <td><?= $row->product_code . ' (' . $row->product_name . ')' ?></td>
                                            <td><?= $this->bpas->formatQuantity($row->unit_quantity) . ' ' . ($inv->sale_status == 'returned' ? $row->base_unit_code : $row->product_unit_code); ?></td>
                                            <td style="text-align: right;"><?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del style="color: red;">' . $this->bpas->formatMoney($row->real_unit_price) . '</del> ' : ''; ?><?= $this->bpas->formatMoney($row->unit_price); ?></td>
                                            <td style="text-align: right;"><?= ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->bpas->formatMoney($row->item_tax) ?></td>
                                            <td style="text-align: right;"><?= ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) ?></td>
                                            <td style="text-align: right;"><?= $this->bpas->formatMoney($row->subtotal); ?></td>
                                        </tr>
                                        <?php }
                                    } ?>
                            <?php
                            $grand_total += $inv->grand_total;
                            $paid        += $inv->paid;
                            $balance     += ($inv->grand_total - $inv->paid);

                            if ($return_sale) {
                                echo '<tr><td colspan="8" style="text-align:right;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->bpas->formatMoney($return_sale->grand_total) . '</td></tr>';
                            }
                            if ($inv->surcharge != 0) {
                                echo '<tr><td colspan="8" style="text-align:right;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->bpas->formatMoney($inv->surcharge) . '</td></tr>';
                            } ?>
                            <?php if ($inv->order_discount != 0) {
                                echo '<tr><td colspan="8" style="text-align:right;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->bpas->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                            } ?>
                            <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                echo '<tr><td colspan="8" style="text-align:right;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->bpas->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                            } ?>
                            <?php if ($inv->shipping != 0) {
                                echo '<tr><td colspan="8" style="text-align:right;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->bpas->formatMoney($inv->shipping - ($return_sale && $return_sale->shipping ? $return_sale->shipping : 0)) . '</td></tr>';
                            } ?>
                            <tr>
                                <td colspan="8" style="text-align: right;"><?= lang('total_amount'); ?> (<?= $default_currency->code; ?>)</td>
                                <td style="text-align: right;"><?= $this->bpas->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: right;"><?= lang('paid'); ?> (<?= $default_currency->code; ?>)</td>
                                <td style="text-align: right;"><?= $this->bpas->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: right;"><?= lang('balance'); ?> (<?= $default_currency->code; ?>)</td>
                                <td style="text-align: right;"><?= $this->bpas->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                            </tr>
                        <?php } ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: red;"><b>Total Balance</b></td>   
                                <td style="text-align: right; color: red;"><b><?= $this->bpas->formatMoney($grand_total); ?></b></td>
                                <td style="text-align: right; color: red;"><b><?= $this->bpas->formatMoney($paid); ?></b></td>
                                <td style="text-align: right; color: red;"><b><?= $this->bpas->formatMoney($balance); ?></b></td>
                            </tr>
                            <?php
                                    $total_grand_total += $grand_total;
                                    $total_paid        += $paid;
                                    $total_balance     += $balance;

                                    $str_salesman = "";
                                    $new_arr      = [];
                                    if (!empty($salesman_by)) {
                                        foreach ($salesman_by as $value) {
                                            $new_arr[] = $value;
                                        }
                                        foreach ($new_arr as $key => $value) {
                                            $str_salesman .= ((count($new_arr) -1 != $key) ?  ($value . ', ') : $value);
                                        }
                                    }
                                }
                            } 
                        } ?>
                        <tr class="foot">
                            <td colspan="8" style="text-align: center;"><b>All Grand Total</b></td>    
                            <td style="text-align: right;"><b><?= $this->bpas->formatMoney($total_grand_total); ?></b></td>
                        </tr>
                        <tr class="foot">
                            <td colspan="8" style="text-align: center;"><b>All Paid</b></td>    
                            <td style="text-align: right;"><b><?= $this->bpas->formatMoney($total_paid); ?></b></td>
                        </tr>
                        <tr class="foot">
                            <td colspan="8" style="text-align: center;"><b>All Total Balance</b></td>    
                            <td style="text-align: right;"><b><?= $this->bpas->formatMoney($total_balance); ?></b></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function(){
        $("#xls").click(function(e){
            e.preventDefault();
            window.location.href = "<?= admin_url('reports/sales_detail/0/xls/?v=1' . $v)?>";
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
        $('#print_logo').click(function (e) {
            e.preventDefault();
            $('#company_info').addClass('HT');
            window.print();
        });
        $('#print').click(function (e) {
            e.preventDefault();
            $('#company_info').removeClass('HT');
            window.print();
        });
        $('#print_new_form').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/sales_detail/0/0/preview/?v=1' . $v) ?>";
            return false;
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#total_invoice').text('<?= $total_invoice; ?>');
        $('#total_delivery').text('<?= $total_delivery; ?>');
        $('#salesman').text('<?= (isset($str_salesman) && !empty($str_salesman)) ? $str_salesman : ''; ?>');
    });
</script>