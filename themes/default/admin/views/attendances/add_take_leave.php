<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

?>
<script type="text/javascript">
	var count = 1, an = 1;
    $(document).ready(function () {
        if (localStorage.getItem('remove_tls')) {
            if (localStorage.getItem('tlitems')) {
                localStorage.removeItem('tlitems');
            }
            if (localStorage.getItem('tlref')) {
                localStorage.removeItem('tlref');
            }
            if (localStorage.getItem('tlnote')) {
                localStorage.removeItem('tlnote');
            }
            if (localStorage.getItem('tldate')) {
                localStorage.removeItem('tldate');
            }
			if (localStorage.getItem('tlleavetypes')) {
                localStorage.removeItem('tlleavetypes');
            }
            localStorage.removeItem('remove_tls');
        }
		
		<?php if($leave_type){ ?>
				localStorage.setItem('tlleavetypes', '<?= $leave_type ?>');
		<?php } ?>

		<?php if ($Owner || $Admin || $GP['attendances-date']) { ?>
			if (!localStorage.getItem('tldate')) {
				$("#tldate").datetimepicker({
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
			}
			$(document).on('change', '#tldate', function (e) {
				localStorage.setItem('tldate', $(this).val());
			});
			if (tldate = localStorage.getItem('tldate')) {
				$('#tldate').val(tldate);
			}
        <?php } ?>
        

        <?php if (!$Owner && !$Admin && $this->session->userdata('employee_id')) { 
        ?>
            $.ajax({
                url: '<?= admin_url('attendances/suggestions'); ?>',
                dataType: 'json',
                cache: false,
                success: function(item) {

                   add_take_leave_employee(item);
                }
            });
            
        <?php
        }else{
        ?>
        $("#add_item").autocomplete({
            source: '<?= admin_url('attendances/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_employee_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_employee_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_take_leave_employee(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_employee_found') ?>');
                }
            }
        });

        <?php }?>
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_take_leave'); ?></h2>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("attendances/add_take_leave", $attrib);
                ?>
				<?= form_hidden('count_id', isset($count_id)? $count_id: ''); ?>
                <div class="row">
                    <div class="col-lg-12">
						<?php if ($Owner || $Admin || $GP['attendances-date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "tldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="tldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4 <?= ((!$Owner && !$Admin) ? 'hidden' : '') ?>">
                            <div class="form-group">
                                <?= lang("reference_no", "tlref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $reference), 'class="form-control input-tip" id="tlref"'); ?>
                            </div>
                        </div>
						
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
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
       
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="clearfix"></div>


                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_employee_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("employee"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="tlTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang("name") ?></th>
											<th><?= lang("leave_type"); ?></th>		
											<th class="col-md-1"><?= lang("start_date"); ?></th>
											<th class="col-md-1"><?= lang("end_date"); ?></th>
											<th class="col-md-1"><?= lang("timeshift"); ?></th>
											<th><?= lang("reason") ?></th> 
                                            <th style="max-width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang("note", "tlnote"); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="tlnote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_take_leave', lang("submit"), 'id="add_take_leave" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
