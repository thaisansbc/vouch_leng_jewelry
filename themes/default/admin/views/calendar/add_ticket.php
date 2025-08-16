<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_ticket'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('calendar/add_ticket', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang('schedules', 'schedules'); ?>
                        <?php 
                        $fields []= '';
                        if (!empty($schedules)) {
                            foreach ($schedules as $schedule) {
                                $fields[$schedule->id] = $schedule->start.' - '.$schedule->end;
                            }
                        }
                        echo form_dropdown('schedule',$fields,'', 'class="form-control select" id="schedule"'); ?>
                    </div>
                 </div>

                <div class="col-sm-12">
                    <div class="form-group">
                        <?= lang('customer', 'customer'); ?>
                            <?php if ($Owner || $Admin || $GP['customers-add']) { ?><div class="input-group"><?php } ?>
                            <?php
                            $cust[''] = lang("selected");
                            foreach ($customers as $customer) {
                                $cust[$customer->id] = $customer->text;
                            }
                            echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control"  id="slcustomer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"');
                            ?>
                            <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                <a href="#" id="view-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                    <i class="fa fa-2x fa-user" id="addIcon"></i>
                                </a>
                            </div>
                            <div class="input-group-addon no-print" style="padding: 2px 5px;"><a href="<?= admin_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal2">
                                <i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a>
                            </div>
                        </div>
                   
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <?php $status = ['pending' => lang('pending')]; ?>
                        <?= form_dropdown('status', $status, '', 'class="form-control tip" id="status" required="required"'); ?>
                    </div>
                </div>

            </div>

            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_event', lang('add_event'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'bpas',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
