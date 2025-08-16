<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-4">
                    <img src="<?= base_url('assets/uploads/') ?><?= $customer->attachment? $customer->attachment:'male.png';?>" class="avatar img-responsive">
                    <div class="text-center" style="margin-top:20px;">
                    
                        <?= $this->bpas->qrcode('link', urlencode(admin_url('products/view/' . $customer->name)), 2); ?>
                    </div>
                </div>
                <div class="col-xs-8">
                    <div class="table-responsive">
                        <table class="table table-striped" style="border-left:1px solid #dddddd;border-right: 1px solid #dddddd;">
                            <tr>
                                <td><strong><?= lang('code'); ?></strong></td>
                                <td>: <?= $customer->code; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang('name'); ?></strong></td>
                                <td>: <?= $customer->name; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang('phone'); ?></strong></td>
                                <td>: <?= $customer->phone; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang('email'); ?></strong></td>
                                <td>: <?= $customer->email; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang('address'); ?></strong></td>
                                <td>: <?= $customer->address; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang('company'); ?></strong></td>
                                <td>: <?= $customer->company; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang('customer_group'); ?></strong></td>
                                <td>: <?= $customer->customer_group_name; ?></strong></td>
                            </tr>

                            <?php 
                            if($customer->gender){?>
                            <tr>
                                <td><strong><?= lang('gender'); ?></strong></td>
                                <td><?= $customer->gender; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->age){?>
                            <tr>
                                <td><strong><?= lang('age'); ?></strong></td>
                                <td><?= $customer->age; ?></strong></td>
                            </tr>
                            <?php }

                            if($customer->cf1){?>
                            <tr>
                                <td><strong><?= lang('ccf1'); ?></strong></td>
                                <td><?= $customer->cf1; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->cf2){?>
                            <tr>
                                <td><strong><?= lang('ccf2'); ?></strong></td>
                                <td><?= $customer->cf2; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->cf3){?>
                            <tr>
                                <td><strong><?= lang('ccf3'); ?></strong></td>
                                <td><?= $customer->cf3; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->cf4){?>
                            <tr>
                                <td><strong><?= lang('ccf4'); ?></strong></td>
                                <td><?= $customer->cf4; ?></strong></td>
                            </tr>
                            <?php }
                            if ($customer->package) { ?>
                            <tr>
                                <td><strong><?= lang('package'); ?></strong></td>
                                <td>: <?= $customer->package; ?></strong></td>
                            </tr>
                            <?php } if ($customer->vat_no) { ?>
                            <tr>
                                <td><strong><?= lang('vat_no'); ?></strong></td>
                                <td>: <?= $customer->vat_no; ?></strong></td>
                            </tr>
                            <?php } if ($customer->gst_no) { ?>
                            <tr>
                                <td><strong><?= lang('gst_no'); ?></strong></td>
                                <td>: <?= $customer->gst_no; ?></strong></td>
                            </tr>
                            <?php } if ($customer->deposit_amount >0) { ?>
                            <tr>
                                <td><strong><?= lang('deposit'); ?></strong></td>
                                <td>: <?= $this->bpas->formatMoney($customer->deposit_amount); ?></strong></td>
                            </tr>
                            <?php } if ($customer->award_points) { ?>
                            <tr>
                                <td><strong><?= lang('award_points'); ?></strong></td>
                                <td>: <?= $customer->award_points; ?></strong></td>
                            </tr>
                            <?php } if ($customer->city) { ?>
                            <tr>
                                <td><strong><?= lang('city'); ?></strong></td>
                                <td><?= $customer->city; ?></strong></td>
                            </tr>
                            <?php } if (isset($state)) { ?>
                            <tr>
                                <td><strong><?= lang('state'); ?></strong></td>
                                <td><?= $customer->state; ?></strong></td>
                            </tr>
                            <?php }
                            if (isset($zone)) { ?>
                            <tr>
                                <td><strong><?= lang('zone'); ?></strong></td>
                                <td><?php echo $zone->zone_name; ?></strong></td>
                            </tr>
                            <?php } 
                            if($customer->postal_code){?>
                            <tr>
                                <td><strong><?= lang('postal_code'); ?></strong></td>
                                <td><?= $customer->postal_code; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->country){?>
                            <tr>
                                <td><strong><?= lang('country'); ?></strong></td>
                                <td><?= $customer->country; ?></strong></td>
                            </tr>
                            <?php }
                            
                            if($customer->cf5){?>
                            <tr>
                                <td><strong><?= lang('ccf5'); ?></strong></td>
                                <td><?= $customer->cf5; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->cf6){?>
                            <tr>
                                <td><strong><?= lang('ccf6'); ?></strong></td>
                                <td><?= $customer->cf6; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->credit_limit){
                            ?>
                            <tr>
                                <td><strong><?= lang('credit_limit'); ?></strong></td>
                                <td><?= $customer->credit_limit; ?></strong></td>
                            </tr>
                            <?php }
                            if($customer->package){?>
                            <tr>
                                <td><strong><?= lang('service_package'); ?></strong></td>
                                <td><?= $customer->package; ?></strong></td>
                            </tr>
                            <?php }
                            if ($customer->attachment) {
                                ?>
                                <td><strong><?= lang('attachment'); ?></strong></td>
                                <td>
                                    <a href="<?= admin_url('welcome/download/' . $customer->attachment) ?>" class="tip btn btn-warning" title="<?= lang('attachment') ?>">
                                        <i class="fa fa-chain"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                    </a>
                                </td>
                                <?php
                                } 
                            ?>
                            
                        </table>
                    </div>
                </div>
                
            </div>

             
            
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                <?php if ($Owner || $Admin || $GP['reports-customers']) {
    ?>
                    <a href="<?=admin_url('reports/customer_report/' . $customer->id); ?>" target="_blank" class="btn btn-primary"><?= lang('customers_report'); ?></a>
                <?php
} ?>
                <?php if ($Owner || $Admin || $GP['customers-edit']) {
        ?>
                    <a href="<?=admin_url('customers/edit/' . $customer->id); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal2" class="btn btn-primary"><?= lang('edit_customer'); ?></a>
                <?php
    } ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
