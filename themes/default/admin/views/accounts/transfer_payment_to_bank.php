<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = '';
/* if($this->input->post('name')){
    $v .= "&name=".$this->input->post('name');
} */
if ($this->input->post('payment_ref')) {
    $v .= '&payment_ref=' . $this->input->post('payment_ref');
}
if ($this->input->post('paid_by')) {
    $v .= '&paid_by=' . $this->input->post('paid_by');
}
if ($this->input->post('sale_ref')) {
    $v .= '&sale_ref=' . $this->input->post('sale_ref');
}
if ($this->input->post('purchase_ref')) {
    $v .= '&purchase_ref=' . $this->input->post('purchase_ref');
}
if ($this->input->post('supplier')) {
    $v .= '&supplier=' . $this->input->post('supplier');
}
if ($this->input->post('biller')) {
    $v .= '&biller=' . $this->input->post('biller');
}
if ($this->input->post('customer')) {
    $v .= '&customer=' . $this->input->post('customer');
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
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('biller')) {
    ?>
        $('#rbiller').select2({ allowClear: true });
        <?php } ?>
        <?php if ($this->input->post('supplier')) {
        ?>
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
        <?php
    } ?>
        <?php if ($this->input->post('customer')) {
        ?>
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
        <?php
    } ?>
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

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            } ?>
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
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a data-target="#myModal" data-toggle="modal" href="javascript:void(0)" id="combine_pay" data-action="combine_pay">
                                <i class="fa fa-money"></i> <?=lang('transfer')?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open('account/tansfer_payment'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('payment_ref', 'payment_ref'); ?>
                                <?php echo form_input('payment_ref', (isset($_POST['payment_ref']) ? $_POST['payment_ref'] : ''), 'class="form-control tip" id="payment_ref"'); ?>

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
                                <?= lang('sale_ref', 'sale_ref'); ?>
                                <?php echo form_input('sale_ref', (isset($_POST['sale_ref']) ? $_POST['sale_ref'] : ''), 'class="form-control tip" id="sale_ref"'); ?>

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
                                <label class="control-label" for="rcustomer"><?= lang('customer'); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="rcustomer" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '"'); ?>
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
                                <?= lang('supplier', 'rsupplier'); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control" id="rsupplier" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('supplier') . '"'); ?> </div>
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
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control date" id="end_date"'); ?>
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
                    <table class="table table-hover table-striped table-condensed reports-table">

                        <thead>
                        <tr>
                            <th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('payment_ref'); ?></th>
                            <th><?= lang('sale_ref'); ?></th>
                            <th><?= lang('purchase_ref'); ?></th>
                            <th><?= lang('paid_by'); ?></th>
                            <th><?= lang('amount'); ?></th>
                            <th><?= lang('type'); ?></th>
                            <?php
                            if($this->Settings->accounting){
                                echo '<th>'.lang('bank_account').'</th>';
                                echo '<th>'.lang('transfer').'</th>';
                            }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        echo $data;
                   
                        ?>
                        </tbody>
                        
                    </table>
                </div>
                <div class="row"> 
                    <div class="col-md-6 text-left">
                        <?php //echo $showing;?>
                    </div>
                    <div class="col-md-6  text-right">
                        <div class="dataTables_paginate paging_bootstrap">
                            <?= $pagination; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>

    $(document).ready(function(){
        $('body').on('click', '#combine_pay', function(e) {
            e.preventDefault();

            if($('.checkbox').is(":checked") === false){
                alert('Please select at least one.');
                return false;
            }
            var arrItems = [];
            $('.checkbox').each(function(i){
                if($(this).is(":checked")){
                    if(this.value != ""){
                        arrItems[i] = $(this).val();
                    }
                }
            });

            $('#myModal').modal({remote: '<?= admin_url('account/multi_tansfers');?>?data=' + arrItems + ''});
            $('#myModal').modal('show');
            return false;
        
            $('#form_action').val($('#combine_pay').attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
    });
</script>