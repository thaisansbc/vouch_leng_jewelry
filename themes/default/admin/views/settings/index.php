<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$wm = ['0' => lang('no'), '1' => lang('yes')];
$ps = ['0' => lang('disable'), '1' => lang('enable')];
?>
<script>
    $(document).ready(function() {
        <?php if (isset($message)) {
            echo 'localStorage.clear();';
        } ?>
        var timezones = <?= json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL)); ?>;
        $('#timezone').autocomplete({
            source: timezones
        });
        if ($('#protocol').val() == 'smtp') {
            $('#smtp_config').slideDown();
        } else if ($('#protocol').val() == 'sendmail') {
            $('#sendmail_config').slideDown();
        }
        $('#protocol').change(function() {
            if ($(this).val() == 'smtp') {
                $('#sendmail_config').slideUp();
                $('#smtp_config').slideDown();
            } else if ($(this).val() == 'sendmail') {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideDown();
            } else {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideUp();
            }
        });
        $('#overselling').change(function() {
            if ($(this).val() == 1) {
                if ($('#accounting_method').select2("val") != 2) {
                    bootbox.alert('<?= lang('overselling_will_only_work_with_AVCO_accounting_method_only') ?>');
                    $('#accounting_method').select2("val", '2');
                }
            }
        });
        $('#accounting_method').change(function() {
            var oam = <?= $Settings->accounting_method ?>,
                nam = $(this).val();
            if (oam != nam) {
                bootbox.alert('<?= lang('accounting_method_change_alert') ?>');
            }
        });
        $('#accounting_method').change(function() {
            if ($(this).val() != 2) {
                if ($('#overselling').select2("val") == 1) {
                    bootbox.alert('<?= lang('overselling_will_only_work_with_AVCO_accounting_method_only') ?>');
                    $('#overselling').select2("val", 0);
                }
            }
        });
        $('#item_addition').change(function() {
            if ($(this).val() == 1) {
                bootbox.alert('<?= lang('product_variants_feature_x') ?>');
            }
        });
        var sac = $('#sac').val()
        if (sac == 1) {
            $('.nsac').slideUp();
        } else {
            $('.nsac').slideDown();
        }
        $('#sac').change(function() {
            if ($(this).val() == 1) {
                $('.nsac').slideUp();
            } else {
                $('.nsac').slideDown();
            }
        });
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('system_settings'); ?></h2>

    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown"><a href="<?= admin_url('system_settings/paypal') ?>" class="toggle_up"><i class="icon fa fa-paypal"></i><span class="padding-right-10"><?= lang('paypal'); ?></span></a></li>
            <li class="dropdown"><a href="<?= admin_url('system_settings/skrill') ?>" class="toggle_down"><i class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('skrill'); ?></span></a>
            </li>
        </ul>
    </div>
</div>
<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('system_settings', $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('site_config') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('site_name', 'site_name'); ?>
                                    <?= form_input('site_name', $Settings->site_name, 'class="form-control tip" id="site_name"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('language', 'language'); ?>
                                    <?php
                                    $lang = [
                                        'english'              => 'English',
                                        'khmer'                => 'Khmer',
                                        'simplified-chinese'   => 'Simplified Chinese',
                                        'thai'                 => 'Thai',
                                        'vietnamese'           => 'Vietnamese',
                                    ];
                                    echo form_dropdown('language', $lang, $Settings->language, 'class="form-control tip" id="language" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="currency"><?= lang('default_currency'); ?></label>

                                    <div class="controls">
                                        <?php
                                        foreach ($currencies as $currency) {
                                            $cu[$currency->code] = $currency->name;
                                        }
                                        echo form_dropdown('currency', $cu, $Settings->default_currency, 'class="form-control tip" id="currency" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('sidebar', 'ui'); ?>
                                    <?php 
                                    $sidebar['default'] = 'Default';
                                    $sidebar['full'] = 'Full';
                                    ?>
                                    <?= form_dropdown('theme_ui', $sidebar, $Settings->ui, 'class="form-control tip" id="sidebar" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="email"><?= lang('default_email'); ?></label>

                                    <?= form_input('email', $Settings->default_email, 'class="form-control tip" required="required" id="email"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="customer_group"><?= lang('default_customer_group'); ?></label>
                                    <?php
                                    foreach ($customer_groups as $customer_group) {
                                        $pgs[$customer_group->id] = $customer_group->name;
                                    }
                                    echo form_dropdown('customer_group', $pgs, $Settings->customer_group, 'class="form-control tip" id="customer_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="price_group"><?= lang('default_price_group'); ?></label>
                                    <?php
                                    foreach ($price_groups as $price_group) {
                                        $cgs[$price_group->id] = $price_group->name;
                                    }
                                    echo form_dropdown('price_group', $cgs, $Settings->price_group, 'class="form-control tip" id="price_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang('default_warehouse'); ?></label>

                                    <div class="controls"> 
                                        <?php
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                        }
                                        echo form_dropdown('warehouse', $wh, $Settings->default_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('default_biller', 'biller'); ?>
                                    <?php
                                    $bl[''] = '';
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('default_project', 'project'); ?>
                                    <?php
                                    $pro[''] = '';
                                    foreach ($projects as $project) {
                                        $pro[$project->project_id] = $project->project_name;
                                    }
                                    echo form_dropdown('default_project', $pro, (isset($_POST['project']) ? $_POST['project'] : $Settings->default_project), 'id="project" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('maintenance_mode', 'mmode'); ?>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('mmode', $wm, (isset($_POST['mmode']) ? $_POST['mmode'] : $Settings->mmode), 'class="tip form-control" required="required" id="mmode" style="width:100%;"');
                                        ?> </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="theme"><?= lang('theme'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $themes = [
                                            'default' => 'Default',
                                        ];
                                        echo form_dropdown('theme', $themes, $Settings->theme, 'id="theme" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="captcha"><?= lang('login_captcha'); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('captcha', $ps, $Settings->captcha, 'id="captcha" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="disable_editing"><?= lang('disable_editing'); ?></label>
                                    <?= form_input('disable_editing', $Settings->disable_editing, 'class="form-control tip" id="disable_editing" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="alert_day"><?= lang('alert_day'); ?></label>
                                    <?= form_input('alert_day', $Settings->alert_day, 'class="form-control tip" id="alert_day" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="rows_per_page"><?= lang('rows_per_page'); ?></label>
                                    <?php
                                    //$rppopts = ['10' => '10', '25' => '25', '50' => '50',  '100' => '100', '-1' => lang('all') . ' (' . lang('not_recommended') . ')'];
                                    //echo form_dropdown('rows_per_page', $rppopts, $Settings->rows_per_page, 'id="rows_per_page" class="form-control tip" style="width:100%;" required="required"');
                                    ?>
                                    <?= form_input('rows_per_page', $Settings->rows_per_page, 'class="form-control tip" id="rows_per_page" required="required"'); ?>
                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="dateformat"><?= lang('dateformat'); ?></label>

                                    <div class="controls">
                                        <?php
                                        foreach ($date_formats as $date_format) {
                                            $dt[$date_format->id] = $date_format->js;
                                        }
                                        echo form_dropdown('dateformat', $dt, $Settings->dateformat, 'id="dateformat" class="form-control tip" style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="timezone"><?= lang('timezone'); ?></label>
                                    <?php
                                    $timezone_identifiers = DateTimeZone::listIdentifiers();
                                    foreach ($timezone_identifiers as $tzi) {
                                        $tz[$tzi] = $tzi;
                                    }
                                    ?>
                                    <?= form_dropdown('timezone', $tz, TIMEZONE, 'class="form-control tip" id="timezone" required="required"'); ?>
                                </div>
                            </div>
                            <!--<div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reg_ver', 'reg_ver'); ?>
                            <div class="controls">  <?php
                                                    echo form_dropdown('reg_ver', $wm, (isset($_POST['reg_ver']) ? $_POST['reg_ver'] : $Settings->reg_ver), 'class="tip form-control" required="required" id="reg_ver" style="width:100%;"');
                                                    ?> </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('allow_reg', 'allow_reg'); ?>
                            <div class="controls">  <?php
                                                    echo form_dropdown('allow_reg', $wm, (isset($_POST['allow_reg']) ? $_POST['allow_reg'] : $Settings->allow_reg), 'class="tip form-control" required="required" id="allow_reg" style="width:100%;"');
                                                    ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reg_notification', 'reg_notification'); ?>
                            <div class="controls">  <?php
                                                    echo form_dropdown('reg_notification', $wm, (isset($_POST['reg_notification']) ? $_POST['reg_notification'] : $Settings->reg_notification), 'class="tip form-control" required="required" id="reg_notification" style="width:100%;"');
                                                    ?>
                            </div>
                        </div>
                    </div>-->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="restrict_calendar"><?= lang('calendar'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt_cal = [1 => lang('private'), 0 => lang('shared')];
                                        echo form_dropdown('restrict_calendar', $opt_cal, $Settings->restrict_calendar, 'class="form-control tip" required="required" id="restrict_calendar" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('pdf_lib', 'pdf_lib'); ?>
                                    <?php $pdflibs = ['mpdf' => 'mPDF', 'dompdf' => 'Dompdf']; ?>
                                    <?= form_dropdown('pdf_lib', $pdflibs, $Settings->pdf_lib, 'class="form-control tip" id="pdf_lib" required="required"'); ?>
                                </div>
                            </div>
                            <?php //if (SHOP) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('apis_feature', 'apis'); ?>
                                        <?= form_dropdown('apis', $ps, $Settings->apis, 'class="form-control tip" id="apis" required="required"'); ?>
                                    </div>
                                </div>
                            <?php
                            //} ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('hide_price', 'hide_price'); ?>
                                    <?php $hide_price = ['yes' => 'Yes', 'no' => 'No']; ?>
                                    <?= form_dropdown('hide_price', $hide_price, $Settings->disable_price, 'class="form-control tip" id="hide_price" required="required"'); ?>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('developed_by', 'developed_by'); ?>
                                    <?php $developed_by = ['1' => 'Yes', '0' => 'No']; ?>
                                    <?= form_dropdown('developed_by', $developed_by, $Settings->developed_by, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('warranty', 'warranty'); ?>
                                    <?php $warranty = ['1' => 'Yes', '0' => 'No']; ?>
                                    <?= form_dropdown('warranty', $warranty, $Settings->warranty, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('combo_price_match', 'combo_price_match'); ?>
                                    <?php $combo_price_match = ['1' => 'Yes', '0' => 'No']; ?>
                                    <?= form_dropdown('combo_price_match', $combo_price_match, $Settings->combo_price_match, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('remove/delete', 'remove/delete'); ?>
                                    <?php $hide = ['1' => 'Delete', '0' => 'Remove']; ?>
                                    <?= form_dropdown('hide', $hide, $Settings->hide, 'class="form-control tip" id="hide_sale" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                <?= lang('cost/Price', 'cost/Price'); ?>
                                 <?php $select_price = [1 => lang('customized'), 0 => lang('unit_of_measurement')]; ?>
                                <?= form_dropdown('select_price', $select_price, $Settings->select_price, 'class="form-control" id="select_price" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('payment_term', 'payment_term'); ?>
                                    <?php $payment_term = ['1' => lang('flexible'), '0' => lang('setup')]; ?>
                                    <?= form_dropdown('payment_term', $payment_term, $Settings->payment_term, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('discount_option', 'discount_option'); ?>
                                    <?php $discount_option = ['1' => lang('yes'), '0' => lang('no')]; ?>
                                    <?= form_dropdown('discount_option', $discount_option, $Settings->discount_option, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                         <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('comment_option', 'comment_option'); ?>
                                    <?php $comment_option = ['1' => lang('yes'), '0' => lang('no')]; ?>
                                    <?= form_dropdown('comment_option', $comment_option, $Settings->comment_option, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('reason_option', 'reason_option'); ?>
                                    <?php $reason_option = ['1' => lang('yes'), '0' => lang('no')]; ?>
                                    <?= form_dropdown('reason_option', $reason_option, $Settings->reason_option, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('seperate_product_by_biller', 'seperate_product_by_biller'); ?>
                                    <?php $seperate_product_by_biller_option = ['0' => lang('no'),'1' => lang('yes')]; ?>
                                    <?= form_dropdown('seperate_product_by_biller', $seperate_product_by_biller_option, $Settings->seperate_product_by_biller, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('multiple_code_unit', 'multiple_code_unit'); ?>
                                    <?php $warranty = ['1' => 'Show', '0' => 'Hide']; ?>
                                    <?= form_dropdown('multiple_code_unit', $warranty, $Settings->multiple_code_unit, 'class="form-control tip" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('enable_telegram', 'enable_telegram'); ?>
                                    <?php
                                    $enable_telegram = [0 => lang('no'), '1' => lang('yes')];
                                    echo form_dropdown('enable_telegram', $enable_telegram, $Settings->enable_telegram, 'class="form-control tip" id="enable_telegram"');
                                        ?> 
                               
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('products') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('product_tax', 'tax_rate'); ?>
                                    <?php
                                    echo form_dropdown('tax_rate', $ps, $Settings->default_tax_rate, 'class="form-control tip" id="tax_rate" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="racks"><?= lang('racks'); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('racks', $ps, $Settings->racks, 'id="racks" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="attributes"><?= lang('attributes'); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('attributes', $ps, $Settings->attributes, 'id="attributes" class="form-control tip"  required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_option"><?= lang('product_option'); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('product_option', $ps, $Settings->product_option, 'id="product_option" class="form-control tip"  required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_expiry"><?= lang('product_expiry'); ?></label>
                                    <div class="controls">
                                        <?php echo form_dropdown('product_expiry', $ps, $Settings->product_expiry, 'id="product_expiry" class="form-control tip" required="required" style="width:100%;"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 expiry">
                                <div class="form-group">
                                    <?= lang('expiry_alert_days', 'expiry_alert_days'); ?>
                                    <?= form_input('expiry_alert_days', $Settings->expiry_alert_days, 'class="form-control tip" id="expiry_alert_days" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4 expiry">
                                <div class="form-group">
                                    <label class="control-label" for="expiry_alert_by"><?= lang('expiry_alert_by'); ?></label>
                                    <?php
                                    $expiry_alert_by = [1 => lang('settings'), 2 => lang('product')];
                                    echo form_dropdown('expiry_alert_by', $expiry_alert_by, $Settings->expiry_alert_by, 'id="expiry_alert_by" class="form-control tip" required="required" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="remove_expired"><?= lang('remove_expired'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $re_opts = [0 => lang('no') . ', ' . lang('i_ll_remove'), 1 => lang('yes') . ', ' . lang('remove_automatically')];
                                        echo form_dropdown('remove_expired', $re_opts, $Settings->remove_expired, 'id="remove_expired" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="image_size"><?= lang('image_size'); ?> (Width :
                                        Height) *</label>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?= form_input('iwidth', $Settings->iwidth, 'class="form-control tip" id="iwidth" placeholder="image width" required="required"'); ?>
                                        </div>
                                        <div class="col-xs-6">
                                            <?= form_input('iheight', $Settings->iheight, 'class="form-control tip" id="iheight" placeholder="image height" required="required"'); ?></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="thumbnail_size"><?= lang('thumbnail_size'); ?>
                                        (Width : Height) *</label>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?= form_input('twidth', $Settings->twidth, 'class="form-control tip" id="twidth" placeholder="thumbnail width" required="required"'); ?>
                                        </div>
                                        <div class="col-xs-6">
                                            <?= form_input('theight', $Settings->theight, 'class="form-control tip" id="theight" placeholder="thumbnail height" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('watermark', 'watermark'); ?>
                                    <?php
                                    echo form_dropdown('watermark', $wm, (isset($_POST['watermark']) ? $_POST['watermark'] : $Settings->watermark), 'class="tip form-control" required="required" id="watermark" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('display_all_products', 'display_all_products'); ?>
                                    <?php
                                    $dopts = [0 => lang('hide_with_0_qty'), 1 => lang('show_with_0_qty')];
                                    echo form_dropdown('display_all_products', $dopts, (isset($_POST['display_all_products']) ? $_POST['display_all_products'] : $Settings->display_all_products), 'class="tip form-control" required="required" id="display_all_products" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('barcode_separator', 'barcode_separator'); ?>
                                    <?php
                                    $bcopts = ['-' => lang('dash'), '.' => lang('dot'), '~' => lang('tilde'), '_' => lang('underscore')];
                                    echo form_dropdown('barcode_separator', $bcopts, (isset($_POST['barcode_separator']) ? $_POST['barcode_separator'] : $Settings->barcode_separator), 'class="tip form-control" required="required" id="barcode_separator" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('barcode_renderer', 'barcode_renderer'); ?>
                                    <?php
                                    $bcropts = [1 => lang('image'), 0 => lang('svg')];
                                    echo form_dropdown('barcode_renderer', $bcropts, (isset($_POST['barcode_renderer']) ? $_POST['barcode_renderer'] : $Settings->barcode_img), 'class="tip form-control" required="required" id="barcode_renderer" style="width:100%;"');
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="overselling"><?= lang('multi_currency'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('multi_currency', $opt, $Settings->multi_currency, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="overselling"><?= lang('auto_count'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('auto_count', $opt, $Settings->auto_count, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="search_custom_field"><?= lang("custom_field_search"); ?></label>
                                    <div class="controls">
                                        <?php
                                            $cfs = array(1 => lang('enable'), 0 => lang('disable'));
                                            echo form_dropdown('search_custom_field', $cfs, $Settings->search_custom_field, 'class="form-control tip" required="required" id="set_custom_field" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('cbm', 'cbm'); ?>
                                    <?= form_dropdown('cbm', $wm, $Settings->cbm, 'class="form-control" id="cbm" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="search_custom_field"><?= lang("show_warehouse_qty"); ?></label>
                                    <div class="controls">
                                        <?php
                                            $cfs = array(1 => lang('yes'), 0 => lang('no'));
                                            echo form_dropdown('show_warehouse_qty', $cfs, $Settings->show_warehouse_qty, 'class="form-control tip" required="required" id="set_custom_field" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('sales') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="overselling"><?= lang('over_selling'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('restrict_sale', $opt, $Settings->overselling, 'class="form-control tip" id="overselling" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="reference_format"><?= lang('reference_format'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $ref = [1 => lang('prefix_year_no'), 2 => lang('prefix_month_year_no'), 3 => lang('sequence_number'), 4 => lang('random_number')];
                                        echo form_dropdown('reference_format', $ref, $Settings->reference_format, 'class="form-control tip" required="required" id="reference_format" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="reference_reset"><?= lang("reference_reset"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $ref_reset = array(0 => lang('no_reset'), 1 => lang('year_reset'), 2 => lang('month_reset'));
                                        echo form_dropdown('reference_reset', $ref_reset, $Settings->reference_reset, 'class="form-control tip" required="required" id="reference_reset" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('invoice_tax', 'tax_rate2'); ?>
                                    <?php $tr['0'] = lang('disable');
                                    foreach ($tax_rates as $rate) {
                                        $tr[$rate->id] = $rate->name;
                                    }
                                    echo form_dropdown('tax_rate2', $tr, $Settings->default_tax_rate2, 'id="tax_rate2" class="form-control tip" required="required" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_discount"><?= lang('product_level_discount'); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('product_discount', $ps, $Settings->product_discount, 'id="product_discount" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang('product_serial'); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('product_serial', $ps, $Settings->product_serial, 'id="product_serial" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="detect_barcode"><?= lang('auto_detect_barcode'); ?></label>

                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('detect_barcode', $ps, $Settings->auto_detect_barcode, 'id="detect_barcode" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="bc_fix"><?= lang('bc_fix'); ?></label>
                                    <?= form_input('bc_fix', $Settings->bc_fix, 'class="form-control tip" required="required" id="bc_fix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="item_addition"><?= lang('item_addition'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $ia = [0 => lang('add_new_item'), 1 => lang('increase_quantity_if_item_exist')];
                                        echo form_dropdown('item_addition', $ia, $Settings->item_addition, 'id="item_addition" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('set_focus', 'set_focus'); ?>
                                    <?php
                                    $sfopts = [0 => lang('add_item_input'), 1 => lang('last_order_item')];
                                    echo form_dropdown('set_focus', $sfopts, (isset($_POST['set_focus']) ? $_POST['set_focus'] : $Settings->set_focus), 'id="set_focus" data-placeholder="' . lang('select') . ' ' . lang('set_focus') . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_view"><?= lang('invoice_view'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt_inv = [1 => lang('tax_invoice'), 0 => lang('commercial_invoice')];
                                        echo form_dropdown('invoice_view', $opt_inv, $Settings->invoice_view, 'class="form-control tip" required="required" id="invoice_view" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_discount_formate"><?= lang('invoice_discount_formate'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt_inv_dis = [1 => lang('total_discount'), 0 => lang('standard')];
                                        echo form_dropdown('invoice_discount_formate', $opt_inv_dis, $Settings->invoice_discount_formate, 'class="form-control tip" required="required" id="invoice_discount_formate" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" id="states" style="display: none;">
                                <div class="form-group">
                                    <label class="control-label" for="state"><?= lang('biz_state'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $states = $this->gst->getIndianStates();
                                        echo form_dropdown('state', $states, $Settings->state, 'class="form-control tip" required="required" id="state" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"><?= lang('create_customer'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [
                                            0 => lang('default'),
                                            1 => lang('short'),
                                        ];
                                        echo form_dropdown('customer_detail', $opt, $Settings->customer_detail, 'class="form-control tip"  required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"><?= lang('zone'); ?></label>

                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('zone', $opt, $Settings->zone, 'class="form-control tip"  required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="allow_change_date">
                                        <?= lang('allow_change_date'); ?>
                                    </label>
                                    <?php 
                                        $acd = array('1'=> lang('yes'), '0'=> lang('no'));
                                        echo form_dropdown('allow_change_date', $acd, (isset($_POST['allow_change_date']) ? $_POST['allow_change_date'] : $Settings->allow_change_date), 'id="allow_change_date" data-placeholder="' . lang("select") . ' ' . lang("acd") . '" required="required" class="form-control input-tip select" style="width:100%;"'); 
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="business_type">
                                        <?= lang('tax_calculate'); ?>
                                    </label>
                                    <?php 
                                        $tm = array('0'=> lang('tax_before'), '1'=> lang('tax_after'));
                                        echo form_dropdown('tax_calculate', $tm, (isset($_POST['tax_calculate']) ? $_POST['tax_calculate'] : $Settings->tax_calculate), 'id="tax_calculate" data-placeholder="' . lang("select") . ' ' . lang("tax_calculate") . '" required="required" class="form-control input-tip select" style="width:100%;"'); 
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("separate_code"); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('separate_code', $wm, $Settings->separate_code, 'id="separate_code" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("show_code"); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('show_code', $wm, $Settings->show_code, 'id="show_code" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="auto_print"><?= lang("auto_print"); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('auto_print', $ps, $Settings->auto_print, 'id="auto_print" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="cost_sale_commission"><?= lang("deduct_cost_sale"); ?></label>
                                    <div class="controls">
                                        <?php
                                        echo form_dropdown('cost_sale_commission', $wm, $Settings->cost_sale_commission, 'id="cost_sale_commission" class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="payment_expense"><?= lang('fefo'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('fefo', $opt, $Settings->fefo, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_combo"><?= lang('product') . ' ' . lang('combo'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $cmb = [1 => lang('collapse'), 0 => lang('expand')];
                                        echo form_dropdown('product_combo', $cmb, $Settings->product_combo, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("date_with_time"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('date_with_time', $opt, $Settings->date_with_time, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php
                                        $lm_print[0] = lang('unlimited');
                                        $lm_print[2] = lang('re-print');
                                        $lm_print[1] = lang('limited');
                                    ?>
                                    <?= lang('limit_print', 'limit_print'); ?>
                                    <?= form_dropdown('limit_print', $lm_print, $Settings->limit_print, 'class="form-control" id="limit_print" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("show_unit"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('show_unit', $opt, $Settings->show_unit, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("show_qoh"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('show_qoh', $opt, $Settings->show_qoh, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("store_sales"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $ssp = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('store_sales', $ssp, $Settings->store_sales, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="product_serial"><?= lang("customer_group_discount"); ?></label>
                                    <div class="controls">
                                        <?php
                                        $discount_type = [1 => lang('order_discount'), 2 => lang('item_discount')];
                                        echo form_dropdown('customer_group_discount', $discount_type, $Settings->customer_group_discount, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php if($this->config->item('saleman_commission')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('product_commission', 'product_commission'); ?>
                                    <?= form_dropdown('product_commission', $wm, $Settings->product_commission, 'class="form-control" id="product_commission" required="required"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('payment_after_delivery', 'payment_after_delivery'); ?>
                                    <?php 
                                    $payment_after_delivery_opt = [0 => lang('no'),1 => lang('yes')];
                                    echo form_dropdown('payment_after_delivery', $payment_after_delivery_opt, $Settings->payment_after_delivery, 'class="form-control" id="payment_after_delivery" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('show_item_combo', 'show_item_combo'); ?>
                                    <?php 
                                    $show_item_combo = [0 => lang('no'),1 => lang('yes')];
                                    echo form_dropdown('show_item_combo', $show_item_combo, $Settings->show_item_combo, 'class="form-control" id="show_item_combo" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('stok_sale_order', 'stok_sale_order'); ?>
                                    <?php 
                                    $stok_sale_order = [0 => lang('no'),1 => lang('yes')];
                                    echo form_dropdown('stok_sale_order', $stok_sale_order, $Settings->stok_sale_order, 'class="form-control" id="stok_sale_order" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="using_weight"><?= lang("using_weight"); ?></label>
                                    <div class="controls">
                                        <?php
                                            $cfs = array(1 => lang('yes'), 0 => lang('no'));
                                            echo form_dropdown('using_weight', $cfs, $Settings->using_weight, 'class="form-control tip" required="required" id="set_custom_field" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('purchase') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('default_supplier', 'supplier'); ?>
                                    <?php
                                    $sup[''] = '';
                                    foreach ($suppliers as $supplier) {
                                        $sup[$supplier->id] = $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name;
                                    }
                                    echo form_dropdown('supplier', $sup, (isset($_POST['supplier']) ? $_POST['supplier'] : $Settings->default_supplier), 'id="supplier_" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('update_cost_with_purchase', 'update_cost'); ?>
                                    <?= form_dropdown('update_cost', $wm, $Settings->update_cost, 'class="form-control" id="update_cost" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="stock_received"><?= lang('stock_received'); ?></label>
                                    <div class="controls">
                                        <?php
                                            $opt = [1 => lang('yes'), 0 => lang('no')];
                                            echo form_dropdown('stock_received', $opt, $Settings->stock_received, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('cost_shipping', 'cost_shipping'); ?>
                                    <?php
                                    $avg_costing = ['0' => lang('no'), '1' => lang('yes')];
                                    echo form_dropdown('avg_cost', $avg_costing, $Settings->avc_costing, 'class="form-control" id="avc_costing" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('payment_expense', 'payment_expense'); ?>
                                    <?php
                                    $payment_expense = ['0' => lang('no'), '1' => lang('yes')];
                                    echo form_dropdown('payment_expense', $payment_expense, $Settings->payment_expense, 'class="form-control" id="payment_expense" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="expense_budget"><?= lang("expense_budget"); ?></label>
                                    <div class="controls">
                                        <?php
                                            $cfs = array(1 => lang('yes'), 0 => lang('no'));
                                            echo form_dropdown('expense_budget', $cfs, $Settings->expense_budget, 'class="form-control tip" required="required" id="set_custom_field" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('accounting') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="accounting">
                                        <?= lang('accounting'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('accounting', $opt, $Settings->accounting, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('accounting_method', 'accounting_method'); ?>
                                    <?php
                                    $am = [0 => 'FIFO (First In First Out)', 1 => 'LIFO (Last In First Out)', 2 => 'AVCO (Average Cost Method)'];
                                    echo form_dropdown('accounting_method', $am, $Settings->accounting_method, 'class="form-control tip" id="accounting_method" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="profit_loss"><?= lang('profit_loss'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $opt = [1 => lang('total_sales'), 0 => lang('payment_received')];
                                        echo form_dropdown('profit_loss_method', $opt, $Settings->profit_loss_method, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 hide">
                                <div class="form-group">
                                    <label class="control-label" for="payroll_atttendance"><?= lang('payroll_atttendance'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $show_payroll_atttendance = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('payroll_atttendance', $show_payroll_atttendance, $Settings->show_payroll_atttendancence, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                         <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('loan'). "/" .lang('pawn'). "/" .lang('installment') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="loan_alert_days"><?= lang('loan_alert_days'); ?></label>
                                    <?= form_input('loan_alert_days', $Settings->loan_alert_days, 'class="form-control tip" id="loan_alert_days" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="installment_alert_days"><?= lang('installment_alert_days'); ?></label>
                                    <?= form_input('installment_alert_days', $Settings->installment_alert_days, 'class="form-control tip" id="installment_alert_days" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="installment_late_days"><?= lang('installment_late_days'); ?></label>
                                    <?= form_input('installment_late_days', $Settings->installment_late_days, 'class="form-control tip" id="installment_late_days" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="installment_holiday"><?= lang('installment_holiday'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $installment_holiday = [1 => lang('yes'), 0 => lang('no')];
                                        echo form_dropdown('installment_holiday', $installment_holiday, $Settings->installment_holiday, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="installment_penalty_option"><?= lang('installment_penalty_option'); ?></label>
                                    <div class="controls">
                                        <?php
                                        $installment_penalty_option = [1 => lang('days'), 2 => lang('options')];
                                        echo form_dropdown('installment_penalty_option', $installment_penalty_option, $Settings->installment_penalty_option, 'class="form-control tip" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <!-----for property----->
                        <?php if($this->Settings->module_hr) {?>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('hr') ?></legend>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="seniority"><?= lang('seniority_pay'); ?></label>
                                            <div class="controls">
                                                <?php
                                                $seniority_option = [0 => lang('manual'), 1 => lang('auto')];
                                                echo form_dropdown('seniority_pay', $seniority_option, $Settings->seniority_pay, 'class="form-control tip" required="required" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="severance"><?= lang('severance_pay'); ?></label>
                                            <div class="controls">
                                                <?php
                                                $severance_option = [0 => lang('manual'), 1 => lang('auto')];
                                                echo form_dropdown('severance_pay', $severance_option, $Settings->severance_pay, 'class="form-control tip" required="required" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="using_roster"><?= lang('using_roster'); ?></label>
                                            <div class="controls">
                                                <?php
                                                $roster_option = [0 => lang('no'), 1 => lang('yes')];
                                                echo form_dropdown('using_roster', $roster_option, $Settings->using_roster, 'class="form-control tip" required="required" style="width:100%;"');
                                                ?>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="scan"><?= lang('scan_per_shift'); ?></label>
                                            <div class="controls">
                                                <?php
                                                $policy_option = [4 => lang('four_time'), 2 => lang('two_time')];
                                                echo form_dropdown('scan_per_shift', $policy_option, $Settings->scan_per_shift, 'class="form-control tip" required="required" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="from_date"><?= lang('from_date'); ?></label>
                                            <?= form_input('roster_from_day', $Settings->roster_from_day, 'class="form-control tip" id="roster_from_day"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <?php }?>
                        <!-----close for property----->
                        <?php if($this->Settings->module_concrete){ ?>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('concretes') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('moving_waitings', 'moving_waitings'); ?>
                                    <?= form_dropdown('moving_waitings', $wm, $Settings->moving_waitings, 'class="form-control" id="moving_waitings"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('missions', 'missions'); ?>
                                    <?= form_dropdown('missions', $wm, $Settings->missions, 'class="form-control" id="missions"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('fuel_expenses', 'fuel_expenses'); ?>
                                    <?= form_dropdown('fuel_expenses', $wm, $Settings->fuel_expenses, 'class="form-control" id="fuel_expenses"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('errors', 'errors'); ?>
                                    <?= form_dropdown('errors', $wm, $Settings->errors, 'class="form-control" id="errors"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('absents', 'absents'); ?>
                                    <?= form_dropdown('absents', $wm, $Settings->absents, 'class="form-control" id="absents"'); ?>
                                </div>
                            </div>
                        </fieldset>
                        <?php } ?>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('prefix') ?></legend>
                            <?php if ($settings->module_sale || POS) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="sales_prefix"><?= lang('sales_prefix'); ?></label>
                                    <?= form_input('sales_prefix', $Settings->sales_prefix, 'class="form-control tip" id="sales_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="return_prefix"><?= lang('return_prefix'); ?></label>
                                    <?= form_input('return_prefix', $Settings->return_prefix, 'class="form-control tip" id="return_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="payment_prefix"><?= lang('payment_prefix'); ?></label>
                                    <?= form_input('payment_prefix', $Settings->payment_prefix, 'class="form-control tip" id="payment_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="ppayment_prefix"><?= lang('ppayment_prefix'); ?></label>
                                    <?= form_input('ppayment_prefix', $Settings->ppayment_prefix, 'class="form-control tip" id="ppayment_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="delivery_prefix"><?= lang('delivery_prefix'); ?></label>

                                    <?= form_input('delivery_prefix', $Settings->delivery_prefix, 'class="form-control tip" id="delivery_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="returnp_prefix"><?= lang('returnp_prefix'); ?></label>

                                    <?= form_input('returnp_prefix', $Settings->returnp_prefix, 'class="form-control tip" id="returnp_prefix"'); ?>
                                </div>
                            </div>
                            <?php if ($settings->quotation) { ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="quote_prefix"><?= lang('quote_prefix'); ?></label>

                                    <?= form_input('quote_prefix', $Settings->quote_prefix, 'class="form-control tip" id="quote_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->module_purchase) { ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="purchase_prefix"><?= lang('purchase_prefix'); ?></label>

                                    <?= form_input('purchase_prefix', $Settings->purchase_prefix, 'class="form-control tip" id="purchase_prefix"'); ?>
                                </div>
                            </div>
                           
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="transfer_prefix"><?= lang('transfer_prefix'); ?></label>
                                    <?= form_input('transfer_prefix', $Settings->transfer_prefix, 'class="form-control tip" id="transfer_prefix"'); ?>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('expense_prefix', 'expense_prefix'); ?>
                                    <?= form_input('expense_prefix', $Settings->expense_prefix, 'class="form-control tip" id="expense_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('qa_prefix', 'qa_prefix'); ?>
                                    <?= form_input('qa_prefix', $Settings->qa_prefix, 'class="form-control tip" id="qa_prefix"'); ?>
                                </div>
                            </div>
                            <?php if ($settings->sale_order) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="sales_order_prefix"><?= lang("sale_order_prefix"); ?></label>
                                    <?php echo form_input('sales_order_prefix', $Settings->sales_order_prefix, 'class="form-control tip" id="sales_order_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->purchase_order) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('purchase_order_prefix','purchase_order_prefix'); ?>
                                    <?= form_input('purchase_order_prefix',$settings->purchase_order_prefix,'class="form-control tip" id="purchase_order_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->purchase_request) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('purchase_request_prefix','purchase_request_prefix'); ?>
                                    <?= form_input('purchase_request_prefix',$settings->purchase_request_prefix,'class="form-control tip" id="purchase_request_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->module_manufacturing) { ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('convert_prefix','convert_prefix'); ?>
                                    <?= form_input('convert_prefix',$settings->convert_prefix,'class="form-control tip" id="convert_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->module_account) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('journal_prefix','journal_prefix'); ?>
                                    <?= form_input('journal_prefix',$settings->journal_prefix,'class="form-control tip" id="journal_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('project_code_prefix','project_code_prefix'); ?>
                                    <?= form_input('project_code_prefix',$settings->project_code_prefix,'class="form-control tip" id="project_code_prefix"'); ?>
                                </div>
                            </div>
                            <?php if ($settings->module_hr) { ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('employee_code_prefix','employee_code_prefix'); ?>
                                    <?= form_input('employee_code_prefix',$settings->employee_code_prefix,'class="form-control tip" id="employee_code_prefix"'); ?>
                                </div>
                            </div> 
                            <?php } ?>
                            <?php if ($settings->module_save) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('sav_prefix','sav_prefix'); ?>
                                    <?= form_input('sav_prefix',$settings->sav_prefix,'class="form-control tip" id="sav_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('sav_tr_prefix','sav_tr_prefix'); ?>
                                    <?= form_input('sav_tr_prefix',$settings->sav_tr_prefix,'class="form-control tip" id="sav_tr_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                             <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('edit_sale_request_prefix','edit_sale_request_prefix'); ?>
                                    <?= form_input('edit_sale_request_prefix',$settings->edit_sale_request_prefix,'class="form-control tip" id="edit_sale_request_prefix"'); ?>
                                </div>
                            </div>
                            <?php if ($settings->module_loan) { ?>

                             <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('loan_prefix','loan_prefix'); ?>
                                    <?= form_input('loan_prefix',$settings->loan_prefix,'class="form-control tip" id="loan_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                             <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('app_prefix','app_prefix'); ?>
                                    <?= form_input('app_prefix',$settings->app_prefix,'class="form-control tip" id="app_prefix"'); ?>
                                </div>
                            </div>
                            <?php if ($settings->module_pawn) { ?>

                             <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('pawn_prefix','pawn_prefix'); ?>
                                    <?= form_input('pawn_prefix',$settings->pawn_prefix,'class="form-control tip" id="pawn_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->module_installment) { ?>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('installment_prefix','installment_prefix'); ?>
                                    <?= form_input('installment_prefix', $settings->installment_prefix,'class="form-control tip" id="installment_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->attendance) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('take_leave_prefix','take_leave_prefix'); ?>
                                    <?= form_input('take_leave_prefix', $settings->take_leave_prefix,'class="form-control tip" id="take_leave_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if ($settings->module_school) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('student_prefix', 'student_prefix'); ?>
                                    <?= form_input('student_prefix', $settings->student_prefix,'class="form-control tip" id="student_prefix"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('cfuel_prefix', 'cfuel_prefix'); ?>
                                    <?= form_input('cfuel_prefix', $settings->cfuel_prefix,'class="form-control tip" id="cfuel_prefix"'); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('csale_prefix', 'csale_prefix'); ?>
                                    <?= form_input('csale_prefix', $settings->csale_prefix,'class="form-control tip" id="csale_prefix"'); ?>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('money_number_format') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="decimals"><?= lang('decimals'); ?></label>
                                    <div class="controls"> 
                                        <?php
                                            $decimals = [0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4'];
                                            echo form_dropdown('decimals', $decimals, $Settings->decimals, 'class="form-control tip" id="decimals"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="qty_decimals"><?= lang('qty_decimals'); ?></label>
                                    <div class="controls"> 
                                        <?php
                                            $qty_decimals = [0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3'];
                                            echo form_dropdown('qty_decimals', $qty_decimals, $Settings->qty_decimals, 'class="form-control tip" id="qty_decimals"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('sac', 'sac'); ?>
                                    <?= form_dropdown('sac', $ps, set_value('sac', $Settings->sac), 'class="form-control tip" id="sac"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="nsac">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="decimals_sep"><?= lang('decimals_sep'); ?></label>
                                        <div class="controls"> 
                                            <?php
                                                $dec_point = ['.' => lang('dot'), ',' => lang('comma')];
                                                echo form_dropdown('decimals_sep', $dec_point, $Settings->decimals_sep, 'class="form-control tip" id="decimals_sep"  style="width:100%;" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="thousands_sep"><?= lang('thousands_sep'); ?></label>
                                        <div class="controls"> 
                                            <?php
                                                $thousands_sep = ['.' => lang('dot'), ',' => lang('comma'), '0' => lang('space')];
                                                echo form_dropdown('thousands_sep', $thousands_sep, $Settings->thousands_sep, 'class="form-control tip" id="thousands_sep"  style="width:100%;" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('display_currency_symbol', 'display_symbol'); ?>
                                    <?php $opts = [0 => lang('disable'), 1 => lang('before'), 2 => lang('after')]; ?>
                                    <?= form_dropdown('display_symbol', $opts, $Settings->display_symbol, 'class="form-control" id="display_symbol" style="width:100%;" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('currency_symbol', 'symbol'); ?>
                                    <?= form_input('symbol', $Settings->symbol, 'class="form-control" id="symbol" style="width:100%;"'); ?>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('email') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="protocol"><?= lang('email_protocol'); ?></label>
                                    <div class="controls"> 
                                        <?php
                                            $popt = ['mail' => 'PHP Mail Function', 'sendmail' => 'Send Mail', 'smtp' => 'SMTP'];
                                            echo form_dropdown('protocol', $popt, $Settings->protocol, 'class="form-control tip skip" id="protocol"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row" id="sendmail_config" style="display: none;">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="mailpath"><?= lang('mailpath'); ?></label>
                                            <?= form_input('mailpath', $Settings->mailpath, 'class="form-control tip" id="mailpath"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row" id="smtp_config" style="display: none;">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_host"><?= lang('smtp_host'); ?></label>
                                            <?= form_input('smtp_host', $Settings->smtp_host, 'class="form-control tip" id="smtp_host"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_user"><?= lang('smtp_user'); ?></label>
                                            <?= form_input('smtp_user', $Settings->smtp_user, 'class="form-control tip" id="smtp_user"'); ?> 
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_pass"><?= lang('smtp_pass'); ?></label>
                                            <?= form_password('smtp_pass', $Settings->smtp_pass, 'class="form-control tip" id="smtp_pass"'); ?> 
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_port"><?= lang('smtp_port'); ?></label>
                                            <?= form_input('smtp_port', $Settings->smtp_port, 'class="form-control tip" id="smtp_port"'); ?> 
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="smtp_crypto"><?= lang('smtp_crypto'); ?></label>
                                            <div class="controls"> 
                                                <?php
                                                    $crypto_opt = ['' => lang('none'), 'tls' => 'TLS', 'ssl' => 'SSL'];
                                                    echo form_dropdown('smtp_crypto', $crypto_opt, $Settings->smtp_crypto, 'class="form-control tip" id="smtp_crypto"');
                                                ?> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('award_points') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls"> 
                                    <?php
                                    $point_opt =['spent' =>lang('by_spent'),'qty' =>lang('by_qty')];
                                    echo form_dropdown('apoint_option', $point_opt, $Settings->apoint_option,'class="form-control" style="width:100%;" required="required"');
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><?= lang('customer_award_points');?></label>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_spent'); ?><br>
                                            <?= form_input('each_spent', $this->bpas->formatDecimal($Settings->each_spent), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-4 col-xs-5">
                                            <?= lang('award_points'); ?><br>
                                            <?= form_input('ca_point', $Settings->ca_point, 'class="form-control"'); ?>
                                        </div>
                                    </div>
                                </div>
               
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_quantity'); ?><br>
                                            <?= form_input('each_qty', $this->bpas->formatDecimal($Settings->each_qty), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-4 col-xs-5">
                                            <?= lang('award_points'); ?><br>
                                            <?= form_input('qca_point', $Settings->qca_point, 'class="form-control"'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><?= lang('staff_award_points'); ?></label>

                                    <div class="row">
                                        <div class="col-sm-4 col-xs-6">
                                            <?= lang('each_in_sale'); ?><br>
                                            <?= form_input('each_sale', $this->bpas->formatDecimal($Settings->each_sale), 'class="form-control"'); ?>
                                        </div>
                                        <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                        </div>
                                        <div class="col-sm-4 col-xs-5">
                                            <?= lang('award_points'); ?><br>
                                            <?= form_input('sa_point', $Settings->sa_point, 'class="form-control"'); ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </fieldset>
                        <?php if($this->Settings->module_school){?>
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('school') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("default_program", "default_program"); ?>
                                    <?php
                                    $pg_opt[""] = "";
                                    foreach ($programs as $program) {
                                        $pg_opt[$program->id] = $program->name;
                                    }
                                    echo form_dropdown('default_program', $pg_opt, (isset($_POST['default_program']) ? $_POST['default_program'] : $Settings->default_program), 'id="default_program" data-placeholder="' . lang("select") . ' ' . lang("default_program") . '"  class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('auto_invoice', 'auto_invoice'); ?>
                                    <?= form_dropdown('auto_invoice', $wm, $Settings->auto_invoice, 'class="form-control" id="auto_invoice"'); ?>
                                </div>
                            </div>
                           
                        </fieldset>
                        <?php }?>
                        <fieldset class="scheduler-border hide">
                            <legend class="scheduler-border"><?= lang('license') ?></legend>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="license_name"><?= lang('license_name'); ?></label>
                                            <?= form_input('license_name', $Settings->license_name, 'class="form-control tip" id="license_name"'); ?> 
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="license_key"><?= lang('license_key'); ?></label>
                                            <?= form_password('license_key', $Settings->license_key, 'class="form-control tip" id="license_key"'); ?> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="cleafix"></div>
                <div class="form-group">
                    <div class="controls">
                        <?= form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary btn-lg"'); ?>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

    </div>
    <?php if (!DEMO) {?>
        <div class="alert alert-info" role="alert">
            <p>
                <a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= admin_url('cron/run'); ?>"><?= lang('run_manual_now')?></a>
                <p><strong>Cron Job</strong> (run at 1:00 AM daily):</p>
                <pre>0 1 * * * wget -qO- <?= admin_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</pre>
                OR
                <pre>0 1 * * * <?= (defined('PHP_BINDIR') ? PHP_BINDIR . DIRECTORY_SEPARATOR : '') . 'php ' . FCPATH . SELF . ' admin/cron run'; ?> >/dev/null 2>&1</pre>
                For CLI: <code>schedule path/to/php path/to/index.php controller method</code>
            </p>
        </div>
        <div class="alert alert-info" role="alert">
            <p>
                <a class="btn btn-primary btn-xs pull-right" target="" href="<?= admin_url('cron/alert_to_telegram'); ?>" style="margin-right: 10px;"><?= lang('run_manual_now')?> </a>
                <p><strong>Cron Job alert to telegram</strong> (run at 1:00 AM daily):</p>
                <pre>0 1 * * * wget -qO- <?= admin_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</pre>
                
            </p>
        </div>
        <div class="alert alert-info" role="alert">
            <p>
                <a class="btn btn-primary btn-xs pull-right" target="" href="<?= admin_url('cron/chipmong_daily'); ?>" style="margin-right: 10px;"><?= lang('run_manual_now')?></a>
                <p><strong>Cron Job Chipmong</strong> (run at 1:00 AM daily):</p>
                <pre>0 1 * * * wget -qO- <?= admin_url('cron/chipmong_daily/1'); ?> &gt;/dev/null 2&gt;&amp;1</pre>
                
            </p>
        </div>
        <?php } ?>
</div>
<script>
    $(document).ready(function() {
        $('#invoice_view').change(function(e) {
            if ($(this).val() == 2) {
                $('#states').show();
            } else {
                $('#states').hide();
            }
        });
        if ($('#invoice_view').val() == 2) {
            $('#states').show();
        } else {
            $('#states').hide();
        }

        var product_expiry = '<?= $Settings->product_expiry ?>';
        if (product_expiry == 1) {
            $('.expiry').show();
        } else {
            $('.expiry').hide();
        }
        $('#product_expiry').change(function(e) {
            $('.expiry').fadeToggle(800);
        });
    });
</script>