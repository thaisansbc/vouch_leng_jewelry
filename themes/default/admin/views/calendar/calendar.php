<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href='<?= $assets ?>fullcalendar/css/fullcalendar.min.css' rel='stylesheet' />
<link href='<?= $assets ?>fullcalendar/css/fullcalendar.print.css' rel='stylesheet' media='print' />
<link href="<?= $assets ?>fullcalendar/css/bootstrap-colorpicker.min.css" rel="stylesheet" />

<style>
    .fc th {
        padding: 10px 0px;
        vertical-align: middle;
        background:#F2F2F2;
        width: 14.285%;
    }
    .fc-content {
        cursor: pointer;
    }
    .fc-day-grid-event>.fc-content {
        padding: 4px;
    }

    .fc .fc-center {
        margin-top: 5px;
    }
    .error {
        color: #ac2925;
        margin-bottom: 15px;
    }
    .event-tooltip {
        width:150px;
        background: rgba(0, 0, 0, 0.85);
        color:#FFF;
        padding:10px;
        position:absolute;
        z-index:10001;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 11px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('calendar'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('calendar_line') ?></p>

                <div id='calendar'></div>

                <div class="modal fade cal_modal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                    <i class="fa fa-2x">&times;</i>
                                </button>
                                <h4 class="modal-title"></h4>
                            </div>
                            <div class="modal-body">
                                <div class="error"></div>
                                <form>
                                    <input type="hidden" value="" name="eid" id="eid">
                                    <div class="form-group">
                                        <?= lang('title', 'title'); ?>
                                        <?= form_input('title', set_value('title'), 'class="form-control tip" id="title" required="required"'); ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <?= lang('customer', 'customer'); ?>
                                                    <?php if ($Owner || $Admin || $GP['customers-add']) { ?><div class="input-group"><?php } ?>
                                                    <?php
                                                    $cust["0"] = "None";
                                                    foreach ($customers as $customer) {
                                                        $cust[$customer->id] = $customer->text;
                                                    }
                                                    echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control"  id="slcustomer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '" required="required"');
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
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <?= lang('assign_to', 'assign_to'); ?>
                                                    <?php
                                                        $u[''] = 'None';
                                                        foreach ($users as $user) {
                                                            $u[$user->id] = $user->first_name .' '.$user->last_name;
                                                        }
                                                        echo form_dropdown('assign_to', $u, '', 'id="assign_to" class="form-control input-tip select" required="required" data-placeholder="' . lang("select") . ' ' . lang("user") . '" ');
                                                    ?>
                                               
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('status', 'status'); ?>
                                                <?php $status = ['pending' => lang('pending')]; ?>
                                                <?= form_dropdown('status', $status, '', 'class="form-control tip" id="status" required="required"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('start', 'start'); ?>
                                                <?= form_input('start', set_value('start'), 'class="form-control datetime" id="start" required="required"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('end', 'end'); ?>
                                                <?= form_input('end', set_value('end'), 'class="form-control datetime" id="end"'); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('event_color', 'color'); ?>
                                                <div class="input-group">
                                                    <span class="input-group-addon" id="event-color-addon" style="width:2em;"></span>
                                                    <input id="color" name="color" type="text" class="form-control input-md" readonly="readonly" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 hide">
                                            <div class="form-group">
                                                <?= lang('status', 'status'); ?>
                                                <?= form_input('status', set_value('status'), 'class="form-control" id="status"'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <?= lang('description', 'description'); ?>
                                        <textarea class="form-control skip" id="description" name="description"></textarea>
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    var currentLangCode = '<?= $cal_lang; ?>', moment_df = '<?= strtoupper($dateFormats['js_sdate']); ?> HH:mm', cal_lang = {},
    tkname = "<?=$this->security->get_csrf_token_name()?>", tkvalue = "<?=$this->security->get_csrf_hash()?>";
    cal_lang['add_event'] = '<?= lang('add_event'); ?>';
    cal_lang['edit_event'] = '<?= lang('edit_event'); ?>';
    cal_lang['delete'] = '<?= lang('delete'); ?>';
    cal_lang['event_error'] = '<?= lang('event_error'); ?>';
</script>
<script src='<?= $assets ?>fullcalendar/js/moment.min.js'></script>
<script src="<?= $assets ?>fullcalendar/js/fullcalendar.min.js"></script>
<script src="<?= $assets ?>fullcalendar/js/lang-all.js"></script>
<script src='<?= $assets ?>fullcalendar/js/bootstrap-colorpicker.min.js'></script>
<script src='<?= $assets ?>fullcalendar/js/main.js'></script>
