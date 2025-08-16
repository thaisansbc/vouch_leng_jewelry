<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_driver'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
        echo admin_form_open_multipart("drivers/create_driver", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('logo', 'driver_logo'); ?>
                        <?php
                        $driver_logos[''] = '';
                        foreach ($logos as $key => $value) {
                            $driver_logos[$value] = $value;
                        }
                        echo form_dropdown('logo', $driver_logos, '', 'class="form-control select" id="driver_logo" '); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="logo-con" class="text-center"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang('company', 'company'); ?>
                        <?php echo form_input('company', '', 'class="form-control tip" id="company"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang('driver_name', 'driver_name'); ?>
                        <?php echo form_input('driver_name', '', 'class="form-control tip" id="driver_name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('name_kh', 'name_kh'); ?>
                        <?= form_input('name_kh','', 'class="form-control" id="name_kh"');
                        ?>
                    </div>
                    <div class="form-group hide">
                        <?= lang('vat_no', 'vat_no'); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group hide">
                        <?= lang('gst_no', 'gst_no'); ?>
                        <?php echo form_input('gst_no', '', 'class="form-control" id="gst_no"'); ?>
                    </div>
                    <!--<div class="form-group company">
                        <?= lang('contact_person', 'contact_person'); ?>
                        <?php echo form_input('contact_person', '', 'class="form-control" id="contact_person" '); ?>
                    </div>-->
                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" id="email_address" />
                    </div>
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" id="phone" />
                    </div>
                    <div class="form-group">
                        <?= lang('address', 'address'); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('city', 'city'); ?>
                        <?php echo form_input('city', '', 'class="form-control" id="city"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('state', 'state'); ?>
                        <?php
                        if ($Settings->indian_gst) {
                            $states = $this->gst->getIndianStates();
                            echo form_dropdown('state', $states, '', 'class="form-control select" id="state" ');
                        } else {
                            echo form_input('state', '', 'class="form-control" id="state"');
                        } ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('postal_code', 'postal_code'); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('country', 'country'); ?>
                        <?php echo form_input('country', '', 'class="form-control" id="country"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('price', 'price'); ?>
                        <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('plate', 'plate'); ?>
                        <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('cf3', 'cf3'); ?>
                        <?php echo form_input('cf3', '', 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('cf4', 'cf4'); ?>
                        <?php echo form_input('cf4', '', 'class="form-control" id="cf4"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('cf5', 'cf5'); ?>
                        <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('cf6', 'cf6'); ?>
                        <?php echo form_input('cf6', '', 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
                <div class="col-md-12 hide">
                    <div class="form-group">
                        <?= lang('invoice_footer', 'invoice_footer'); ?>
                        <?php echo form_textarea('invoice_footer', '', 'class="form-control skip" id="invoice_footer" style="height:100px;"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_driver', lang('add_driver'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        $('#driver_logo').change(function(event) {
            var driver_logo = $(this).val();
            $('#logo-con').html('<img   src="<?= base_url('assets/uploads/logos') ?>/' + driver_logo + '" alt="">');
        });
    });
</script>
<?= $modal_js ?>