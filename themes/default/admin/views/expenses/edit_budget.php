<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_budget'); ?></h4>
        </div>
        <!-- 'data-toggle' => 'validator', -->
        <?php $attrib = ['role' => 'form'];
        echo admin_form_open_multipart('expenses/edit_budget/' . $budget->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                <div class="form-group">
                    <?= lang('date', 'date'); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($budget->date)), 'class="form-control datetime" required="required"'); ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <?= lang('reference', 'reference'); ?>
                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $budget->reference), 'class="form-control tip" id="reference" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('title', 'title'); ?>
                <?= form_input('title', (isset($_POST['title']) ? $_POST['title'] : $budget->title), 'class="form-control tip" id="title" required="required"'); ?>
            </div>
            <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
            <div class="form-group">
                <?= lang("biller", "biller"); ?>
                <?php
                    $bl[""] = "";
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $budget->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                    ?>
                </div>        
            <?php } elseif (count($user_billers) > 1) { ?>
            <div class="form-group">
                <?= lang("biller", "biller"); ?>
                <?php
                    $bl[""] = "";
                    foreach ($billers as $biller) {
                        foreach ($user_billers as $value) {
                            if ($biller->id == $value) {
                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                            }
                            }
                        }
                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $budget->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                    ?>
                </div>                    
            <?php } else {
                $biller_input = array(
                    'type'  => 'hidden',
                    'name'  => 'biller',
                    'id'    => 'slbiller',
                    'value' => $budget->biller_id,
                );
                echo form_input($biller_input);
            } ?>
            <?php if($this->Settings->project) {?>
            <div class="form-group">
                <?= lang("project", "project"); ?>
                <?php
                $project_id = $budget->project_id;
                $pro[""] = "---Please select---";
                foreach ($projects as $project) {
                    $pro[$project->project_id] = $project->project_name;
                }
                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
            <?php }?>
            <table width="100%" id="dynamic_field" border="0"> 
                <div class="form-group">
                    <?= lang('amount*', 'amount'); ?>
                    <input name="amount" type="text" value="<?= $this->bpas->formatDecimal($budget->amount); ?>" class="pa form-control kb-pad amount" required="required" />
                </div>
            </table>
            <div class="form-group">
                <?= lang('attachment', 'attachment') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $budget->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_budget', lang('edit_budget'), 'class="btn btn-primary"'); ?>
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