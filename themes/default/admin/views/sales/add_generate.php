<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(setZoneSelector);
    $('#slsaleman_by').change(setZoneSelector);
    function setZoneSelector(){
        var saleman_id =  $('#slsaleman_by').val() ?  $('#slsaleman_by').val() : '';
        if(saleman_id != ""){
            $.ajax({
                type: "get",
                url: site.base_url + "sales/getZonesBySaleman_ajax/" + saleman_id,
                dataType: "json",
                success: function (data) {
                    if(data != false){
                        $("#zone_id").find('option').remove().end();
                        if(data['z_b_user']['multi_zone'] != null){
                            $("#zone_id").append("<option selected='selected'>Select Zone</option>");
                            mz_id = data['z_b_user']['multi_zone'].split(',');
                            mz_id.forEach((element, index, array) => {
                                let zone = data['z_all'].find(x => x.id === element);
                                $("#zone_id").append("<option value='" + zone.id + "'>" + zone.zone_name + "</option>");
                                if(zone.parent_id == 0){
                                    data['z_all'].forEach(element => {
                                        if(element.parent_id == zone.id){
                                            $("#zone_id").append("<option value='" + element.id + "'>" + "&emsp;" + element.zone_name + "</option>");
                                        }
                                    });
                                } 
                            });
                        } else {
                            $("#zone_id").append("<option selected='selected'>No matches found</option>");
                        }
                        $("#zone_id option:first").attr('selected','selected').trigger('change');
                    }
                },
            }).fail(function(xhr, error){
                console.debug(xhr); 
                console.debug(error);
            });
        }
    }
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_generate'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('sales/add_generate', $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('date', 'qudate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" id="qudate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('reference_no', 'quref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $qunumber), 'class="form-control input-tip" id="quref"'); ?>
                            </div>
                        </div>

                        <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="qubiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } elseif (count($user_billers) > 1) { ?>
                            <div class="col-md-3">
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
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="qubiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type'  => 'hidden',
                                'name'  => 'biller',
                                'id'    => 'qubiller',
                                'value' => $user_billers[0],
                            );
                            echo form_input($biller_input);
                        } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('month', 'month'); ?>
                                <?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] :date('m/Y')), 'class="form-control month" id="month" readonly'); ?>
                            </div>
                        </div>
                       <?php if ($Settings->zone) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang('zone', 'zone_id') ?>
                                    <?php
                                    /*if($Settings->zone_by_saleman){
                                        echo form_dropdown('zone_id', lang("select") . ' ' . lang("zone"), (isset($_POST['zone_id']) ? $_POST['zone_id'] : ''), 'class="form-control select" id="zone_id" data-placeholder="' . lang("select") . ' ' . lang("zone") . '"style="width:100%"');
                                    }else{*/
                                        $zon[""] = "";
                                        foreach ($zones as $zone) {
                                            $zon[$zone->id] = $zone->zone_code.' '.$zone->zone_name;
                                        }
                                        echo form_dropdown('zone_id',$zon, (isset($_POST['zone_id']) ? $_POST['zone_id'] :''), 'class="form-control select" data-placeholder="'.lang("select").' ' .lang("zone").'"style="width:100%"');
                                    //}
                                    ?>
                                </div>
                            </div>
                        <?php } if($Settings->sale_man){ ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                     <?= lang('salesman', 'salesman'); ?>
                                     <select id="slsaleman_by" name="saleman_by" class="form-control input-tip select">
                                        <?php
                                        echo '<option value="">----------</option>';
                                        if($this->session->userdata('group_id') == $Settings->group_saleman_id){
                                            echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
                                        } else {
                                            foreach($salemans as $agency){
                                                echo '<option value="'.$agency->id.'">'.$agency->first_name.' '.$agency->last_name.'</option>';
                                            }
                                        }
                                        ?>
                                    </select> 
                                </div>
                            </div>
                        <?php } ?>                    
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('document', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row" id="bt">
                            <div class="col-sm-6">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang('note', 'qunote'); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="qunote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_quote', $this->lang->line('submit'), 'id="add_quote" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                                ?>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php
                                            } ?>
                            <?php if ($Settings->tax2) {
                                                ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php
                                            } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>