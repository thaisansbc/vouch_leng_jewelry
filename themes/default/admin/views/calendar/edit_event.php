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
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_event'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('calendar/edit_event/'.$events->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('event_type', 'event_type'); ?>
                        <?php 
                        $get_fields = $this->site->getcustomfield('event_type');
                        $field ['']='';
                        if (!empty($get_fields)) {
                            foreach ($get_fields as $field_id) {
                                $field[$field_id->id] = $field_id->name;
                            }
                        }
                       echo form_dropdown('event_type', $field, isset($events->event_type) ? $events->event_type : $events->event_type, 'class="form-control select" id="event_type"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('title', 'title'); ?>
                        <?= form_input('title', $events->title, 'class="form-control tip" id="title" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group all">
                        <?= lang('product_image', 'product_image') ?>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="product_image" data-show-upload="false" data-show-preview="true" accept="image/*" class="form-control file">
                    </div>
                </div>
                <?php
                if(!$events->photo== NULL){
                    if($events->photo){
                ?>
                   <div class="col-md-12 photo_deleted">
                    <a class="img-thumbnail img_consumer" href="<?php echo base_url() . 'assets/uploads/' . $events->photo; ?>" style="margin-right:5px;">
                        <img class="img-responsive" src="<?php echo base_url() . 'assets/uploads/' . $events->photo; ?>" alt="<?php echo $events->photo; ?>" style="width:100%; height:200px;" />
                    </a>
                    <button class="btn btn-danger btn-xs delete-image" style="position: absolute;" data-id="<?php echo $events->id; ?>" data-photo="<?php echo $events->photo; ?>" title="<?= lang('delete_attachment_image') ?>"><i class="fa fa-trash-o"></i></button>
                  </div>           
                <?php
                    }
                
                }
                ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('start', 'start'); ?>
                        <?= form_input('start', $this->bpas->hrld($events->start), 'class="form-control datetime" id="start" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('end', 'end'); ?>
                        <?= form_input('end',$this->bpas->hrld($events->end), 'class="form-control datetime" id="end"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <!-- <?php $status = ['pending' => lang('pending'), 'approved' => lang('approved'),'rejected' => lang('rejected')]; ?> -->
                        <?php $status = ['public' => lang('public'),'unpublic' => lang('unpublic')]; ?>
                        <?= form_dropdown('status', $status, $events->status, 'class="form-control tip" id="status" required="required"'); ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('event_color', 'color'); ?>
                        <div class="input-group">
                            <span class="input-group-addon" id="event-color-addon" style="width:2em;"></span>
                            <input id="color" name="color" value="<?= $events->color;?>" type="text" class="form-control input-md" readonly="readonly" />
                        </div>
                    </div>
                </div>


                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang('schedules', 'schedules'); ?>
                        <?php 
                        $get_fieldschedules = $this->site->getschedulesedit();
                        $fields []= '';
                        if (!empty($get_fieldschedules)) {
                            foreach ($get_fieldschedules as $field_ids) {
                                $fields[$field_ids->id] = $field_ids->start.' - '.$field_ids->end;
                            }
                        }
                        if (!empty($eventschedule)) {
                            foreach ($eventschedule as $eschedule) {
                                $select[$eschedule->id] = $eschedule->id;
                            }
                        }
                        echo form_dropdown('schedules[]',$fields,$select, 'class="form-control select" id="schedules" multiple="multiple"'); ?>
                    </div>
                 </div>

            </div>
            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <textarea class="form-control" id="description" name="description"><?= $this->bpas->decode_html($events->description);?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('location_name', 'location_name'); ?>
                        <?= form_input('location_name', $events->location_name, 'class="form-control" id="location_name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('coordinates', 'coordinates'); ?>
                        <?= form_input('coordinates',$events->coordinates, 'class="form-control" id="coordinates"'); ?>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_event', lang('edit_event'), 'class="btn btn-primary"'); ?>
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


        $('#product_image').change(function() {
            $('.delete-image').click();
        });
        $('.delete-image').click(function() {
        var id = $(this).data('id');
        var photo = $(this).data('photo');
        var url = '<?php echo admin_url("calendar/delete_img"); ?>/' + id + '/' + photo;
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
               $('.photo_deleted').hide();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });


    });
</script>
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
