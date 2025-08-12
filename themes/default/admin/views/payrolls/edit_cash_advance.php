<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_cash_advance'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/edit_cash_advance/".$cash_advance->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['payrolls-cash_advances_date']) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($cash_advance->date)), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								<?php } ?>
								<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
									<div class="form-group">
										<?= lang("reference_no", "cvref"); ?>
										<?php echo form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $cash_advance->reference_no), 'class="form-control input-tip" id="cvref"'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("document", "document") ?>
										<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false"
											   data-show-preview="false" class="form-control file">
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label" for="suggest_employee"><?= lang("employee"); ?></label>
										<input type="text" name="employee_id" id="suggest_employee" value="<?= $requested_by->lastname.' '.$requested_by->firstname. ' ('.$requested_by->empcode.')' ?>" required="required" class="form-control ui-autocomplete-input" />
										<input type="hidden" name="employee" value="<?= $cash_advance->requested_by ?>" id="suggest_employee_id">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("amount", "cvamount"); ?>
										<?php echo form_input('amount', (isset($_POST['amount']) ? $_POST['amount'] : $cash_advance->amount), 'class="form-control input-tip text-right" id="cvamount" required="required"'); ?>
									</div>
								</div>
								
								
								<?php if($Settings->accounting == 1){ ?>
									<div class="col-sm-4">
										<div class="form-group">
											<?= lang("paying_from", "paying_from"); ?>
											<select name="paying_from" class="form-control select" id="paying_from" style="width:100%">
												<?= $cash_account ?>
											</select>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
                    </div>
					<div class="col-md-12">
						<div class="form-group">
							<?= lang("description", "description"); ?>
							<?php echo form_textarea('description', (isset($_POST['description']) ? $_POST['description'] : $cash_advance->description), 'class="form-control" id="cvdescription" style="margin-top: 10px; height: 100px;" '); ?>
						</div>
					</div>
					<div class="col-lg-12">
						<input type="submit" class="btn btn-primary" value="<?= lang('submit') ?>" />
					</div>
                </div>
                <?php echo form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		var old_value;
		$(document).on("focus", '#cvamount', function () {
			old_value = $(this).val();
		}).on("change", '#cvamount', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});

	});
</script>