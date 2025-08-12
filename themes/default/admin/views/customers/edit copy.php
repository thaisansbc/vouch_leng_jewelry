<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $sp = ['0' => lang('no'), '1' => lang('yes')]; ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_customer'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('customers/edit/' . $customer->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php if($this->Settings->customer_detail){ ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                    <label class="control-label" for="customer_group"><?php echo $this->lang->line('customer_group'); ?></label>
                        <?php
                        foreach ($customer_groups as $customer_group) {
                            $cgs[$customer_group->id] = $customer_group->name;
                        }
                        echo form_dropdown('customer_group', $cgs, $customer->customer_group_id, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
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
                        echo form_dropdown('price_group', $pgs, $customer->price_group_id, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
            </div>
            <?php }?>
            <div class="row">
                <div class="col-md-6">
                    <?php if($this->Settings->customer_detail){ ?>
                    <div class="form-group company">
                        <?= lang('company', 'company'); ?>
                        <?php echo form_input('company', $customer->company, 'class="form-control tip" id="company" required="required"'); ?>
                    </div>
                    <?php }?>
                    <div class="form-group person">
                        <?= lang('code', 'code'); ?>
                        <?php echo form_input('code', $customer->code, 'class="form-control tip" id="code" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang('name', 'name'); ?>
                        <?php echo form_input('name', $customer->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone" value="<?= $customer->phone ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" required="required" id="email_address" value="<?= $customer->email ?>"/>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('address', 'address'); ?>
                        <?php echo form_input('address', $customer->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('city', 'city'); ?>
                        <?php echo form_input('city', $customer->city, 'class="form-control" id="city" required="required"'); ?>
                    </div>
                    <?php if($this->Settings->customer_detail){ ?>
                        <div class="form-group company">
                            <?= lang('contact_person', 'contact_person'); ?>
                            <?php echo form_input('contact_person', $customer->contact_person, 'class="form-control" id="contact_person"');?>
                        </div>
                        <div class="form-group">
                            <?= lang('vat_no', 'vat_no'); ?>
                            <?php echo form_input('vat_no', $customer->vat_no, 'class="form-control" id="vat_no"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('gst_no', 'gst_no'); ?>
                            <?php echo form_input('gst_no', $customer->gst_no, 'class="form-control" id="gst_no"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('state', 'state'); ?>
                            <?php
                            if ($Settings->indian_gst) {
                                $states = $this->gst->getIndianStates(true);
                                echo form_dropdown('state', $states, $customer->state, 'class="form-control select" id="state" required="required"');
                            } else {
                                echo form_input('state', $customer->state, 'class="form-control" id="state"');
                            }
                            ?>
                    </div>
                    <?php }?>
                </div>
                <div class="col-md-6">
                    <?php if($this->Settings->customer_detail){ ?>
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
                            echo form_dropdown('zone_id', $z, (isset($_POST['zone_id']) ? $_POST['zone_id'] : $customer->zone_id), 'id="zone_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('zone') . '" style="width:100%;" '); 
                            ?>
                        </div>
                    </div>
                    <?php }?>
                    <div class="form-group">
                        <?= lang('postal_code', 'postal_code'); ?>
                        <?php echo form_input('postal_code', $customer->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('country', 'country'); ?>
                        <?php echo form_input('country', $customer->country, 'class="form-control" id="country"'); ?>
                    </div>
                    <div class="form-group hide">
                        <?= lang('identity', 'cf1'); ?>
                        <?php echo form_input('cf1', $customer->cf1, 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("identity", "cf2"); ?>
                        <?php echo form_input('cf2', $customer->cf2, 'class="form-control" id="cf2"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("dob", "cf3"); ?>
                        <?php echo form_input('cf3', $customer->cf3, 'class="form-control date" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("gender", "cf4"); ?>
                        <?php
                        $cgs = array('Male' => 'Male' , 'Female'=>'Female');
                        echo form_dropdown('cf4', $cgs, $customer->cf4, 'class="form-control select" id="cf4" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("wife_Husband", "cf5"); ?>
                        <?php echo form_input('cf5', $customer->cf5, 'class="form-control" id="cf5"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("age", "cf6"); ?>
                        <?php echo form_input('cf6', $customer->cf6, 'class="form-control" id="cf6"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('attachment', 'attachment') ?>
                        <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
                    </div>
                </div>
            </div>
            <?php if($this->Settings->customer_detail){ ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                            <?= lang('save_point', 'save_point'); ?>
                            <div class="controls"> 
                                <?php echo form_dropdown('save_point', $sp, (isset($_POST['save_point']) ? $_POST['save_point'] : $customer->save_point), 'class="form-control input-tip select" required="required" id="save_point" style="width:100%;"'); ?> 
                            </div>
                        </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('award_points', 'award_points'); ?>
                        <?= form_input('award_points', set_value('award_points', $customer->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_customer', lang('edit_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>