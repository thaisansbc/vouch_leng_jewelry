<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('attendances/device_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('devices'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <!-- <a href="<?php echo admin_url('attendances/add_device/'.$parent_id); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_device') ?>
                            </a> -->
                            <a href="<?php echo admin_url('attendances/add_device'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_device') ?>
                            </a>                           
                        </li>
						<li>
                            <a href="#" id="excel" data-action="connect_device">
                                <i class="fa fa-refresh"></i> <?=lang('connect')?> <?=lang('devices')?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="get_att_log">
                                <i class="fa fa-calendar"></i> <?=lang('get_att_log')?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="clear_att_log">
                                <i class="fa fa-trash"></i> <?=lang('clear_att_log')?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="synchronize_time">
                                <i class="fa fa-refresh"></i> <?=lang('synchronize_time')?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo"
								title="<b><?=lang("delete_devices")?></b>"
								data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
								data-html="true" data-placement="left">
								<i class="fa fa-trash-o"></i> <?=lang('delete_devices')?>
							</a>
						</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
	
	<?php
		$tbody = '';
		if($devices){
			foreach($devices as $device){
				$delete_link = '<a href="#" class="po" title="'.lang('delete_device').'"
									data-content="<p>'.lang('r_u_sure').'</p><a href=\''.admin_url("attendances/delete_device/".$device->id."").'\' class=\'btn btn-danger\'>'.lang('i_m_sure').'</a>
									<button class=\'btn po-close\'>'.lang('no').'</button>">
								<i class="fa fa-trash-o"></i>'.lang('delete_device').'</a>';
				
				$action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
									<ul class="dropdown-menu pull-right" role="menu">
										<li><a href="'.admin_url('attendances/edit_device/'.$device->id.'').'" data-toggle="modal" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_device').'</a></li>
										<li>'.$delete_link.'</li>
									</ul>
								</div>';
				$employee = '';
				$date = '';
				if(isset($device->inactive) && $device->inactive==1){
					$status = '<span class="row_status label label-danger">'.lang('inactive').'<span>';
				}else{
					if(isset($connect[$device->id]) && $connect[$device->id]==1){
						$this->load->library('zk');
						$zk = new ZKLib($device->ip_address, $device->port);
						if($zk->connect()){
							$status = '<span class="row_status label label-success">'.lang('connect').'<span>';
							$employee = count($zk->getUser());
							$date = $zk->getTime();
						}else{
							$status = '<span class="row_status label label-warning">'.lang('disconnect').'<span>';
						}
					}else{
						$status = '<span class="row_status label label-warning">'.lang('disconnect').'<span>';
					}
				}	
				
								
				$tbody .= '<tr>	
							<td><input value="'.$device->id.'" class="checkbox multi-select input-xs" type="checkbox" name="val[]"/>
							<td>'.$device->name.'</td>
							<td>'.$device->ip_address.'</td>
							<td class="text-center">'.$device->port.'</td>
							<td>'.$device->description.'</td>
							<td class="text-center">'.$status.'</td>
							<td class="text-center">'.($date ? $this->bpas->hrld($date,true) : '').'</td>
							<td class="text-right">'.$employee.'</td>
							<td class="text-center">'.$action_link.'</td>
						</tr>';
			}
		}else{
			$tbody = '<tr>
						<td colspan="10" class="dataTables_empty">'.lang('no_data').'</td>
					</tr>';
		}
		
	?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="deviceTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-condensed table-bordered table-hover table-striped dataTable">
                        <thead>
							<tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang("device_name"); ?></th>
								<th><?= lang("ip_address"); ?></th>
                                <th><?= lang("port"); ?></th>
								<th><?= lang("description"); ?></th>
								<th><?= lang("status"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("employee"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
							<?= $tbody ?>
                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>