<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $sp = ['0' => lang('no'), '1' => lang('yes')]; ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form'];
        echo admin_form_open_multipart('customers/add', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div role="tabpanel">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#important" aria-controls="uploadTab" role="tab" data-toggle="tab">
                        <?= lang('important')?></a>
                    </li>
                    <li role="presentation" ><a href="#optional"  role="tab" data-toggle="tab"><?= lang('optional')?></a>
                    </li>
                    <li role="presentation" ><a href="#additional"  role="tab" data-toggle="tab"><?= lang('additional')?></a>
                    </li>

                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="important">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                <label class="control-label" for="customer_group"><?php echo $this->lang->line('customer_group'); ?></label>
                                    <?php
                                    foreach ($customer_groups as $customer_group) {
                                        $cgs[$customer_group->id] = $customer_group->name;
                                    }
                                    echo form_dropdown('customer_group', $cgs, $Settings->customer_group, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="price_group"><?php echo $this->lang->line('price_group'); ?></label>
                                    <?php
                                    $pgs[''] = lang('select') . ' ' . lang('price_group');
                                    foreach ($price_groups as $price_group) {
                                        $pgs[$price_group->id] = $price_group->name;
                                    }
                                    echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control select" id="price_group" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <?php 
                            if($Lastrow && $Lastrow->code){
                                $tmp_number = 1;
                                $number = preg_replace('/[^0-9]/', '', $Lastrow->code);
                                $letter = preg_replace('/[^^!<>@&\/\sA-Za-z_]/', '', $Lastrow->code);
                                $number = ($tmp_number.$number)+1;
                                $number = substr($number, 1);
                                $new_number = $letter.($number);
                            } else {
                                $new_number = '';
                            }
                            ?>
                            <div class="col-md-6">
                                <div class="form-group person">
                                    <?= lang('customer_code', 'code'); ?>
                                    <?php echo form_input('code', $new_number, 'class="form-control tip" id="code" '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group person">
                                    <?= lang('name', 'name'); ?>
                                    <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('phone', 'phone'); ?>
                                    <input type="tel" name="phone" class="form-control"  id="phone"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('email_address', 'email_address'); ?>
                                    <input type="email" name="email" class="form-control" id="email_address"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('address', 'address'); ?>
                                    <?php echo form_input('address', '', 'class="form-control" id="address" '); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="optional">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group company">
                                    <?= lang('company', 'company'); ?>
                                    <?php echo form_input('company', '-', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('company_kh', 'postal_code'); ?>
                                    <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group company">
                                    <?= lang('contact_Phone', 'contact_Phone'); ?>
                                    <?php echo form_input('contact_person', '', 'class="form-control" id="contact_person"'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('vat_no', 'vat_no'); ?>
                                    <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('sources', 'sources'); ?>
                                    <?php 
                                    $get_fields = $this->site->getcustomfield('sources');
                                    $field = [''];
                                    if (!empty($get_fields)) {
                                        foreach ($get_fields as $field_id) {
                                            $field[$field_id->id] = $field_id->name;
                                        }
                                    }
                                    echo form_dropdown('state',$field, '', 'class="form-control select" id="state"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('zone', 'zone_id'); ?>
                                    <div class="input-group" style="width:100%">
                                        <?php 
                                        $z[''] = '';
                                        if (!empty($zones)) {
                                            foreach ($zones as $zone) {
                                                $z[$zone->id] = $zone->zone_name;
                                            }
                                        }
                                        echo form_dropdown('zone_id', $z, (isset($_POST['zone_id']) ? $_POST['zone_id'] : ''), 'id="zone_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('zone') . '" style="width:100%;" '); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('gender', 'gender'); ?>
                                    <?php
                                    $cgs = array('Male' => lang('Male') , 'Female'=> lang('Female'));
                                    echo form_dropdown('gender', $cgs, '', 'class="form-control select" id="cf4" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('age', 'age'); ?>
                                    <?php echo form_input('age', '', 'class="form-control"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('ccf1', 'ccf1'); ?>
                                    <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('ccf2', 'ccf2'); ?>
                                    <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('ccf3', 'ccf3'); ?>
                                    <?php echo form_input('cf3', '', 'class="form-control date" id="cf3"'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('ccf4', 'ccf4'); ?>
                                    <?php
                                    echo form_input('cf4', '', 'class="form-control" id="cf4" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('ccf5', 'ccf5'); ?>
                                    <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'); ?>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('ccf6', 'cff6'); ?>
                                    <?php echo form_input('cf6', '', 'class="form-control" id="cf6"'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('city', 'city'); ?>
                                    <?php echo form_input('city', '', 'class="form-control" id="city" '); ?>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('country', 'country'); ?>
                                    <?php echo form_input('country', '', 'class="form-control" id="country"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('find_consumer_comission', 'find_consumer_comission'); ?>
                                    <input name="find_consumer_comission" class="form-control"  id="find_consumer_comission"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('credit_limit', 'credit_limit'); ?>
                                    <?php echo form_input('credit_limit', '', 'class="form-control" id="credit_limit"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('save_point', 'save_point'); ?>
                                    <div class="controls"> 
                                        <?php echo form_dropdown('save_point', $sp, (isset($_POST['save_point']) ? $_POST['save_point'] : '0'), 'class="form-control input-tip select" required="required" id="save_point" style="width:100%;"'); ?> 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('attachment', 'attachment') ?>
                                    <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="additional">
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('service_fee', 'service_fee'); ?>
                                    <?php echo form_input('service_fee','', 'class="form-control" '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('business_type', 'gst_no'); ?>
                                    <?php echo form_input('gst_no', '', 'class="form-control" '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('telegram', 'telegram'); ?>
                                    <?php
                                    $telegram = array('1' => lang('yes') , '0'=> lang('no'));
                                    echo form_dropdown('telegram', $telegram, '', 'class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('village', 'village'); ?>
                                    <?php echo form_input('village', '', 'class="form-control"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('commune', 'commune'); ?>
                                    <?php echo form_input('commune', '', 'class="form-control" '); ?>
                                </div>
                            </div>
                     
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('street_no', 'street_no'); ?>
                                    <?php echo form_input('street_no', '', 'class="form-control" '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('number_of_people', 'number_of_people'); ?>
                                    <?php echo form_input('number_of_people', '', 'class="form-control" '); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('stamp_type', 'stamp_type'); ?>
                                    <?php
                                    $stamp_type = array('black' => lang('black') , 'red'=> lang('red'), 'marble'=> lang('marble'));
                                    echo form_dropdown('stamp_type', $stamp_type, '', 'class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('service_package', 'service_package'); ?>
                                    <?php
                                    $spackage = [''];
                                    if (!empty($customer_package)) {
                                        foreach ($customer_package as $package) {
                                            $spackage[$package->id] = $package->name;
                                        }
                                    }
                                    echo form_dropdown('service_package', $spackage, '', 'class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('prefer_cantact_by', 'prefer_cantact_by'); ?>
                                    <?php
                                    $prefer_cantact_by = array(
                                        '1' => lang('house_visit'), 
                                        '2' => lang('phone_call')
                                    );
                                    echo form_dropdown('prefer_cantact_by', $prefer_cantact_by, '', 'class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                          
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('prefer_to_pay_by', 'prefer_to_pay_by'); ?>
                                    <select name="paid_by" class="form-control select">
                                        <?= $this->bpas->paid_opts(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang('agent'); ?></label>
                                    <?php
                                    $us[''] = lang('select') . ' ' . lang('agent');
                                    if (!empty($agents)) {
                                        foreach ($agents as $user) {
                                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                        }
                                    }
                                    echo form_dropdown('agent', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control select" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('agent') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('date', 'date'); ?>
                                    <?php echo form_input('date', date('d/m/Y H:i'), 'class="form-control datetime" '); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    $(document).ready(function (e) {
        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });
    });
</script>
