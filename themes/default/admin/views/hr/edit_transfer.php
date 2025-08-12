<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1;
    $(document).ready(function () {
        <?php if ($trasfer) { ?>
			localStorage.setItem('dflsdate', '<?= $this->bpas->hrld($trasfer->date); ?>');
			localStorage.setItem('dflsbiller', '<?= $trasfer->biller_id; ?>');
			localStorage.setItem('dflsitems', JSON.stringify(<?= $trasfer_items; ?>));
        <?php } ?>
        if (localStorage.getItem('dflsitems')) {
	        loadItems();
	    }
	    $('#dflsbiller').change(function (e) {
	        localStorage.setItem('dflsbiller', $(this).val());
	    });
	    if (dflsbiller = localStorage.getItem('dflsbiller')) {
	        $('#dflsbiller').select2("val",dflsbiller);
	    }
        function loadItems() {
		    if (localStorage.getItem('dflsitems')) {
				count = 1;
		        an = 1;
		        $("#tlTable tbody").empty();
		        dflsitems = JSON.parse(localStorage.getItem('dflsitems'));
		        $.each(dflsitems, function () {
		            var item = this;
		            var item_id = item.id;
		            item.order = item.order ? item.order : new Date().getTime();
		            var employee_id = item.row.id, employee_code = item.row.empcode, item_name = item.row.firstname +' '+item.row.lastname;
					var description = item.row.description;
					var row_no = (new Date).getTime();
		            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
		            tr_html = '<td><input name="employee_id[]" type="hidden" class="rid" value="' + employee_id + '"><span class="sname" id="name_' + row_no + '">' + employee_code +' - ' + item_name +'</span></td>';

					tr_html += '<td><input class="form-control description" name="description[]" type="text" value="' + description + '" data-id="' + row_no + '" data-item="' + item_id + '" id="reason_' + row_no + '"></td>';
					tr_html += '<td class="text-center"><i class="fa fa-times tip tldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
		            newTr.html(tr_html);
		            newTr.prependTo("#tlTable");
		            count ++;
		            an++;
		        });
		        var col = 2;
		        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total Employee : ' + formatNumber(parseFloat(count) - 1) + '</th>';
		        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
		        $('#tlTable tfoot').html(tfoot);
		        $('select.select').select2({minimumResultsForSearch: 7});
		        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
		            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
		            $(window).scrollTop($(window).scrollTop() + 1);
		        }
				
				if (count > 1) {
		            $('#dflsbiller').select2("readonly", true);
		        }else{
					$('#dflsbiller').select2("readonly", false);
				}
		        set_page_focus();
		    }
		}
		function add_employee(item) {
		    if (count == 1) {
		        dflsitems = {};
		    }
		    if (item == null)
		        return;

		    var item_id = item.id;
		    dflsitems[item_id] = item;
		    dflsitems[item_id].order = new Date().getTime();
		    localStorage.setItem('dflsitems', JSON.stringify(dflsitems));
		    loadItems();
		    return true;
		}

        if (typeof (Storage) === "undefined") {
		    $(window).bind('beforeunload', function (e) {
		        if (count > 1) {
		            var message = "You will loss data!";
		            return message;
		        }
		    });
		}
        $("#add_item").autocomplete({
           source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('hr/Employeesuggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        biller_id: $("#dflsbiller").val(),
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
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
                    var row = add_employee(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_employee_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_transfer'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("hr/edit_transfer/".$trasfer->id, $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['attendances-date']) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("date", "dflsdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($trasfer->date)), 'class="form-control input-tip datetime" id="dflsdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("biller", "dflsbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $trasfer->biller_id), 'id="dflsbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'dflsbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?php echo lang('from_department', 'from_department'); ?>
                                <div class="controls">
                                    <?php
                                    $depa[] = lang("select") . " " .lang("from_department");
                                    if($departments){
                                        foreach($departments as $dp){
                                            $depa[$dp->id] = $dp->name;
                                        }
                                    }           
                                    echo form_dropdown('from_department', $depa, $trasfer->from_department, 'id="from_department" class="form-control" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?php echo lang('to_department', 'to_department'); ?>
                                <div class="controls">
                                    <?php
                                    $depa[] = lang("select") . " " .lang("to_department");
                                    if($departments){
                                        foreach($departments as $dp){
                                            $depa[$dp->id] = $dp->name;
                                        }
                                    }           
                                    echo form_dropdown('to_department', $depa,$trasfer->to_department, 'id="to_department" class="form-control" required="required"');
                                    ?>
                                </div>
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
												<th><?= lang("note") ?></th> 
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
						
						<div class="col-md-8">
							<div class="form-group">
								<?= lang("note", "dflsnote"); ?>
								<?php echo form_textarea('note',$this->bpas->decode_html($trasfer->note), 'class="form-control" id="dflsnote" style="margin-top: 10px; height: 100px;"'); ?>
							</div>
						</div>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="fprom-group"><?php echo form_submit('edit_trasfer', lang("submit"), 'id="edit_trasfer" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>


