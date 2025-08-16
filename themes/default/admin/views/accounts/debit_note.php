<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$v = '';
if ($this->input->post('supplier')) {
		$v .= "&supplier=" . $this->input->post('supplier');
      
	}
	
	if ($this->input->post('start_date')) {
		$v .= "&start_date=" . $this->input->post('start_date');
	}  
	if ($this->input->post('end_date')) {
		$v .= "&end_date=" . $this->input->post('end_date');
	}
	
	if ($this->input->post('payment_status')) {
    $arr = array();
    $arr = $this->input->post('payment_status');
    $get_status = "";
    $get_status = implode('_', $arr);
    $v .= "&payment_status=" . $get_status;
}
	

if (isset($alert_id)) {
    $v .= "&a=" . $alert_id;
}
?>
<script>
    $(document).ready(function () {
        oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('account/getDebitNote' . ($biller_id ? '/' . $biller_id : '') . '?v=1' . $v) ?>',
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
                null,
                null, null,
                {"mRender": row_status},  
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat},
                {"mRender": pay_status},  
                
                {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "debit_note_link";
                var action =$('td:eq(11)', nRow);
                if(aData[5] == 'voided'){
                    action.find('.delete').remove();
                    action.find('.edit').remove();
                }
                if(aData[9] == 'paid'){
                    action.find('.add_payment').remove();
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][5]);
                    paid += parseFloat(aaData[aiDisplay[i]][6]);
                    balance += parseFloat(aaData[aiDisplay[i]][7]);

                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(total);
                nCells[6].innerHTML = currencyFormat(paid);
                nCells[7].innerHTML = currencyFormat(balance);

            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('debit_note_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('subject');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
              {column_number: 8, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
          
        ], "footer");

    <?php if ($this->session->userdata('remove_pols')) { ?>
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
        <?php $this->bpas->unset_data('remove_pols');
   }
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
      $('body').on('click', '#delete', function(e) {
			e.preventDefault();
            // alert(0);
			$('#form_action').val($(this).attr('data-action'));
			$('#action-form-submit').trigger('click');
		});
		
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('account/debit_note_actions', 'id="action-form"');
	}
?>
<style type="text/css">
    body {
        position: static !important;
        overflow-y: auto !important;
    }
</style>

<div class="breadcrumb-header">
    <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
    <h2 class="blue"><i class="fa-fw fa fa-star"></i><?=lang('debit_note') . ' (' . $biller_title . ')';?></h2>
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
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang('actions')?>"></i></a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?=admin_url('account/add_debit_note')?>">
                            <i class="fa fa-plus-circle"></i> <?=lang('add_debit_note')?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel">
                            <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="combine" data-action="combine">
                            <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?=lang('delete_purchases')?></b>"
                            data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                            data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?=lang('delete_purchases')?>
                        </a>
                    </li>
                    
                </ul>
            </li>
            <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('account') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($billers as $biller) {
                            echo '<li><a href="' . admin_url('account/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
            <?php } elseif (!empty($billers)){ ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('account') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        $biller_id_ = $count_billers;
                        foreach ($billers as $biller) {
                            foreach ($biller_id_ as $key => $value) {
                                if ($biller->id == $value) {
                                    echo '<li><a href="' . admin_url('account/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                }
                            }
                            
                        } ?>
                    </ul>
                </li>
            <?php } ?>
            
        </ul>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>

<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
					<?php  echo admin_form_open("account/debit_note"); ?>
                    <div class="row">
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("supplier", "supplier"); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier"'); ?> 
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
                                <?= lang("payment_status", "payment_status"); ?>
                                <?php
                                $payment_status = array('due' => 'Due', 'pending' => 'Pending', 'paid' => 'Paid');
                                echo form_dropdown('payment_status[]', $payment_status, (isset($_POST['payment_status']) ? $_POST['payment_status'] : ''), 'id="payment_status" class="form-control select" placeholder="Please Status" style="width:100%;" multiple="multiple"');
                                ?>
                            </div>
                        </div>
                      
						
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
					<?php if ($GP['bulk_actions']) { echo form_close(); } ?>
                </div>
              
                <p class="introtext"><?=lang('list_results');?></p>
                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('debit_note_no'); ?></th>
                            <th><?= lang('subject'); ?></th>
                            <th><?= lang('supplier'); ?></th>
                            <th><?= lang('status'); ?></th>
                            <th><?= lang('grand_total'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('balance'); ?></th>
                             <th><?= lang('payment_status'); ?></th>
                            
                           
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px;"><?= lang('actions'); ?></th>
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
                            <th><?= lang('grand_total'); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
