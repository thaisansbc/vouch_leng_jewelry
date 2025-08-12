<?php
	$v = "";
	/* if($this->input->post('name')){
	  $v .= "&name=".$this->input->post('name');
	  } */
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
	}
	if ($this->input->post('supplier')) {
		$v .= "&supplier=" . $this->input->post('supplier');
	}
	if ($this->input->post('warehouse')) {
		$v .= "&warehouse=" . $this->input->post('warehouse');
	}
	if ($this->input->post('user')) {
		$v .= "&user=" . $this->input->post('user');
	}
	if ($this->input->post('start_date')) {
		$v .= "&start_date=" . $this->input->post('start_date');
	}
	if ($this->input->post('end_date')) {
		$v .= "&end_date=" . $this->input->post('end_date');
	}
	if ($this->input->post('note')) {
		$v .= "&note=" . $this->input->post('note');
	}
    if ($this->input->post('project')) {
        $v .= "&project=" . $this->input->post('project');
    }
	if(isset($date)){
		$v .= "&d=" . $date;
	}
	if($warehouse_id == null){
		$warehouse_id='';
	}else{
	    // $warehouse_id = explode(',',$warehouse_id);
		$warehouse_id = $warehouse_id;
	}
?>
<script>
    $(document).ready(function () {
        oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('projects/getplans' . ($biller_id ? '/' . $biller_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
			{"bSortable": false,"mRender": checkbox}, 
			{"mRender": fld}, 
            {"bSortable": true},
            null, null, null,
			{"mRender": row_status}, 
			
			{"mRender": approved_status},
			{"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_request_link";
				var action =$('td:eq(8)',nRow);

				if(aData[7]=="approved"){
					 action.find('.approved').remove();
					 action.find('.unreject').remove();
				}else if(aData[7]=="requested"){
					action.find('.unapproved').remove();
					action.find('.unreject').remove();
				}else if(aData[7]=="reject"){
					action.find('.edit').remove();
					action.find('.create').remove();
					action.find('.unapproved').remove(); 
					action.find('.reject').remove();
				}
				if(aData[6]=="completed"){
					action.find('.edit').remove();
					action.find('.create').remove();
					action.find('.unapproved').remove(); 
					action.find('.reject').remove();
				}
                if (aData[8] == 'approved') {
                    action.find('.edit').remove();
                    action.find('.approved').remove();
                }
                if (aData[8] == 'requested') {
                    action.find('.unapproved').remove();
					action.find('.create').remove();
                }
                if (aData[8] == 'reject') {
                    action.find('.edit').remove();
                    action.find('.reject').remove();
					action.find('.create').remove();
                }
                if (aData[7] == 'completed') {
                    action.find('.approved').remove();
                    action.find('.unapproved').remove();
                    action.find('.reject').remove();
                    action.find('.create').remove();
                }
				
                return nRow;
            },
    
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 2, filter_default_label: "[<?=lang('styles');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('title');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('purchase_status');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");

        <?php if ($this->session->userdata('remove_pols')) {?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('poextras')) {
            localStorage.removeItem('poextras');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        <?php $this->bpas->unset_data('remove_pols');}
        ?>
    });

</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $("#product").autocomplete({
            source: '<?= admin_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);
                //$(this).val(ui.item.label);
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
		
		$('body').on('click', '#delete', function(e) {
			e.preventDefault();
			$('#form_action').val($(this).attr('data-action'));
			$('#action-form-submit').trigger('click');
		});
		
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('products/plan_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
        <h2 class="blue"><i class="fa-fw fa fa-star"></i><?=lang('list_plan') . ' (' . $biller_title . ')';?> </h2>
        <?php if (!empty($warehouses)) { ?>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <?php } ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('projects/add_plan') ?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_plan')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?=lang("delete_plan")?></b>"
                                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('delete_plan')?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('purchases_request') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('purchases_request/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)){ ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('purchases_request') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = $count_billers;
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id == $value) {
                                        echo '<li><a href="' . admin_url('purchases_request/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '/' . $biller->name . '</a></li>';
                                    }
                                }
                                
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>
				<div id="form">
					<?php echo admin_form_open("products/plans"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = "";
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("supplier", "supplier"); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier"'); ?> </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <?php
                                if(isset($quote)){
                                    $project_id=  $quote->project_id;
                                }else{
                                    $project_id=  "";
                                }
                                $bl[""] = "";
                                foreach ($projects as $project) {
                                    $bl[$project->project_id] = $project->project_name;
                                }
                                echo form_dropdown('project', $bl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
						
						<div class="col-sm-4">
                           <div class="form-group">
                                <?= lang("note", "note"); ?>
                                <?php echo form_input('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control tip" id="note"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                  
					<?php if ($GP['bulk_actions']) {
					echo form_close();
					}
					?>
                </div>
                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("styles"); ?></th>
                            <th><?= lang("title"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("plan_status"); ?></th>
                            
                            <th><?= lang("status"); ?></th>
                            <th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
