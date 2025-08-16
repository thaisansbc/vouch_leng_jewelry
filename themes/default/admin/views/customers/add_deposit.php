<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_deposit') . ' (' . $company->name . ')'; ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('customers/add_deposit/' . $company->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                    <?php if ($Owner || $Admin) { ?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('date', 'date'); ?>
                            <div class="controls">
                                <?php echo form_input('date', set_value('date', date($dateFormats['php_ldate'])), 'class="form-control datetime" id="date" required="required"'); ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                        <div class="form-group">
                            <div class="controls">
                                <?= lang("biller", "slbiller"); ?>
                                <?php
                                $bl[""] = "";
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } else {
                        $biller_input = array(
                            'type' => 'hidden',
                            'name' => 'biller',
                            'id' => 'slbiller',
                            'value' => $this->session->userdata('biller_id'),
                        );

                        echo form_input($biller_input);
                    } ?>
                    <?php if($Settings->project == 1){ ?>
                        <?php if ($Owner || $Admin) { ?>
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                                    <div class="no-project">
                                        <?php
                                        $pj[''] = '';
                                        foreach ($projects as $project) {
                                            $pj[$project->id] = $project->name;
                                        }
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                        <?php } else { ?>
                            
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                                    <div class="no-project">
                                        <?php
                                        $pj[''] = ''; $right_project = json_decode($user->project_ids);
                                        foreach ($projects as $project) {
                                            if(in_array($project->id, $right_project)){
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>

                            
                        <?php } ?>
                    <?php } ?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('amount', 'amount'); ?>
                            <div class="controls">
                                <?php echo form_input('amount', set_value('amount', '0.00'), 'class="form-control amount" id="amount" required="required"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('paid_by', 'paid_by'); ?>
                            <div class="controls">
                                <select name="paid_by" id="paid_by" class="form-control paid_by" required="required">
                                    <?= $this->bpas->paid_opts(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?php echo lang('note', 'note'); ?>
                            <div class="controls">
                                <?php echo form_textarea('note', set_value('note'), 'class="form-control" id="note"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_deposit', lang('add_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('keypress', '.amount', function(){
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        })
        $(document).on('focus', '.amount', function () {
            $(this).select();
        });
        $(document).on('focusout', '.amount', function () {
            if($(this).val() == '' || $(this).val() < 0){
                $(this).val('0.00');
            }
            $(this).val(parseFloat($(this).val()).toFixed(2));
        });
    });
</script>