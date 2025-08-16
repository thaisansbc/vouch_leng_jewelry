<?php
$url = "attendances/save_note";

if ($clock_out == "1") {
    $url = "attendances/log_time/$user_id/$model_info->id";
} 
$attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'attendance-note-form'];
echo admin_form_open_multipart($url, $attrib); 
// echo form_open(get_uri($url), array("id" => "attendance-note-form", "class" => "general-form", "role" => "form"));
// var_dump($model_info);
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('clock_out'); ?></h4>
        </div>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>"/> 
    <div class="form-group">
        <label for="note" class=" col-md-12"><?php echo lang('note'); ?></label>
        <div class=" col-md-12">
            <?php
            echo form_textarea(array(
                "id" => "note",
                "name" => "note",
                "class" => "form-control",
                "placeholder" => lang('note'),
                "value" => $model_info->note,
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
        <input name="clock_out" type="hidden" value="<?php echo $clock_out; ?>" />
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
</div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#attendance-note-form").appForm({
            onSuccess: function (result) {
                if (result.clock_widget) {
                    $("#timecard-clock-out").closest("#js-clock-in-out").html(result.clock_widget);
                } else {
                    if (result.isUpdate) {
                        $(".dataTable:visible").appTable({newData: result.data, dataId: result.id});
                    } else {
                        $(".dataTable:visible").appTable({reload: true});
                    }
                }
            }
        });

        $("#note").focus();
    });
</script>