<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
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
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            localStorage.removeItem('remove_slls');
        }
        <?php if($this->input->get('customer')) { ?>
        if (!localStorage.getItem('slitems')) {
            localStorage.setItem('slcustomer', <?=$this->input->get('customer');?>);
        }
        <?php } ?>
        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
        <?php } ?>
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
    });
</script>

    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_contract'); ?></h2>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">

                    <p class="introtext"><?php echo lang('enter_info'); ?></p>
                    <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                    echo admin_form_open_multipart("contracts/add", $attrib);
                    
                    ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if ($Owner || $Admin) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("date", "sldate"); ?>
                                        <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("contract_no", "slref"); ?>
                                    <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
                                </div>
                            </div>
                            <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("seller", "slbiller"); ?>
                                        <?php
                                        $bl[""] = "";
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } else {
                                $biller_input = array(
                                    'type' => 'hidden',
                                    'name' => 'biller',
                                    'id' => 'slbiller',
                                    'value' => $this->session->userdata('biller_id'),
                                );

                                echo form_input($biller_input);
                            } ?>

                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="panel panel-warning">
                                    <div
                                        class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                    <div class="panel-body" style="padding: 5px;">
                                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("warehouse", "slwarehouse"); ?>
                                                    <?php
                                                    $wh[''] = '';
                                                    foreach ($warehouses as $warehouse) {
                                                        $wh[$warehouse->id] = $warehouse->name;
                                                    }
                                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                                    ?>
                                                </div>
                                            </div>
                                        <?php } else {
                                            $warehouse_input = array(
                                                'type' => 'hidden',
                                                'name' => 'warehouse',
                                                'id' => 'slwarehouse',
                                                'value' => $this->session->userdata('warehouse_id'),
                                            );

                                            echo form_input($warehouse_input);
                                        } ?>
                                        <div class="col-md-4">
                                          
                                            <div class="form-group">
                                                <?= lang("customer", "slcustomer"); ?>
                                                <div class="input-group">
                                                    <?php
                                                    $wh[''] = '';
                                                    foreach ($customers as $customer) {
                                                        $wh[$customer->id] = $customer->name;
                                                    }
                                                    echo form_dropdown('customer', $wh, (isset($_POST['customer']) ? $_POST['customer'] : ''), 'id="customer" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" style="width:100%;" ');
                                                    ?>
                                                    <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?= base_url('customers/add'); ?>" id="add-customer"class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                       
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("commodity", "commodity"); ?>
                                        <?php echo form_input('commodity', '', 'class="form-control tip" data-trigger="focus" data-placement="top" id="commodity"'); ?>

                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("price", "price"); ?>
                                        <?php echo form_input('price', '', 'class="form-control tip" data-trigger="focus" data-placement="top" id="price"'); ?>

                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("quantity", "quantity"); ?>
                                        <?php echo form_input('quantity', '', 'class="form-control tip" data-trigger="focus" data-placement="top" id="quantity"'); ?>

                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("total_amount", "total_amount"); ?>
                                    <?php echo form_input('total_amount', '', 'class="form-control tip" data-trigger="focus" data-placement="top" id="total_amount"'); ?>

                                </div>
                            </div>
                            <div class="clearfix"></div>

                            <div class="row" id="bt">

                                <div class="col-md-12">

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang("payment_term", "slpayment_term"); ?>
                                            <?php echo form_textarea('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" id="slpayment_term" style="margin-top: 10px; height: 100px;"'); ?>

                                        </div>
                                    </div>
                                     <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang("delivery", "delivery_term"); ?>
                                            <?php echo form_textarea('delivery_term', (isset($_POST['delivery_term']) ? $_POST['delivery_term'] : ""), 'class="form-control tip" style="margin-top: 10px; height: 100px;"'); ?>

                                        </div>
                                    </div>
                                  
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang("account_no", "account_no"); ?>
                                            <?php echo form_textarea('account_no', (isset($_POST['account_no']) ? $_POST['account_no'] : ''), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang("note", "note"); ?>
                                            <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="col-md-12">
                                <div
                                    class="fprom-group"><?php echo form_submit('add_sale', lang("submit"), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                    <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                            </div>
                        </div>
                    </div>

                    <?php echo form_close(); ?>

                </div>

            </div>
        </div>
    </div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#gccustomer').select2({
            minimumInputLength: 1,
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
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
    });
</script>
