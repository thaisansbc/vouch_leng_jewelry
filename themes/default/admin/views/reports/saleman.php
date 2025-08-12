<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<ul id="myTab" class="nav nav-tabs no-print">
    <!-- <li class=""><a href="#commission-con" class="tab-grey"><?= lang('saleman_commission_product') ?></a></li> -->
    <li class=""><a href="#saleman-con" class="tab-grey"><?= lang('saleman') ?></a></li>
    <li class=""><a href="#saleman_rank_commission" class="tab-grey"><?= lang('saleman_rank_commission')?></a></li>
</ul>
<div class="tab-content">
    <div id="commission-con" class="tab-pane fade in">
    <?php
        $v = '';
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= '&biller=' . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= '&warehouse=' . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= '&user=' . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= '&serial=' . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= '&start_date=' . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= '&end_date=' . $this->input->post('end_date');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            oTable = $('#CommDATA').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= admin_url('reports/getCommissionProducts/?v=1' . $v) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    nRow.id = aData[14];
                    nRow.className = (aData[8] > 0) ? "invoice_link2-" : "invoice_link2- warning";
                    return nRow;
                },
       
                "aoColumns": [
                    {"mRender": img_hl}, 
                    null, 
                    null,
                    {"mRender": currencyFormat}, 
                    null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var rgtotal = 0, gtotal = 0, paid = 0, balance = 0, customer_total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    rgtotal += parseFloat(aaData[aiDisplay[i]][3]);
                    // gtotal += parseFloat(aaData[aiDisplay[i]][10]);
                    // paid += parseFloat(aaData[aiDisplay[i]][11]);
                    // balance += parseFloat(aaData[aiDisplay[i]][12]);
                    // if(aaData[aiDisplay[i]][7] != null){
                    //     customer_total += parseFloat(aaData[aiDisplay[i]][7]);
                    // }
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[3].innerHTML = currencyFormat(parseFloat(rgtotal));
                // nCells[9].innerHTML = currencyFormat(parseFloat(rgtotal));
                // nCells[10].innerHTML = currencyFormat(parseFloat(gtotal));
                // nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                // nCells[12].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('saleman');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            // {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            // {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            // {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            // {column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            // {column_number: 7, filter_default_label: "[<?=lang('customer').' (QTY)';?>]", filter_type: "text", data: []},
            // {column_number: 13, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#COMform').hide();
            $('.toggle_down').click(function () {
                $("#COMform").slideDown();
                return false;
            });
            $('.toggle_up').click(function () {
                $("#COMform").slideUp();
                return false;
            });
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('customer_sales_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                }
                ?></h2>

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
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                                <i
                                class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="COMform">

                            <?php echo admin_form_open('reports/saleman'); ?>
                            <div class="row">

                                <div class="col-sm-4 hide">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                        <?php
                                        $us[''] = lang('select') . ' ' . lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="saleman"><?= lang('saleman'); ?></label>
                                        <?php
                                        $sl[''] = lang('select') . ' ' . lang('saleman');
                                        foreach ($saleman as $salemans) {
                                            $sl[$salemans->id] = $salemans->last_name && $salemans->first_name ;
                                        }
                                        echo form_dropdown('saleman', $sl, (isset($_POST['saleman']) ? $_POST['saleman'] : ''), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('saleman') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                        <?php
                                        $bl[''] = lang('select') . ' ' . lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4 ">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                        <?php
                                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                        ?>
                                    </div>
                                </div>
                                <?php if ($Settings->product_serial) {
                                            ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php
                                        } ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('start_date', 'start_date'); ?>
                                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('end_date', 'end_date'); ?>
                                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                            <div class="clearfix"></div>
                            <div class="table-responsive">
                                <table id="CommDATA"
                                class="table table-hover table-striped table-condensed reports-table reports-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"><?= lang('date'); ?></th>
                                        <th><?= lang('saleman'); ?></th>
                                        <th><?= lang('reference_no'); ?></th>
                                        <th style="width: 50px;"><?= lang('total_commission'); ?></th>
                                        <th style="width: 50px;"><?= lang('items'); ?></th>
                                        <!--<th style="width: 40px;"><?= lang('phone'); ?></th>
                                        <th style="width: 20px;"><?= lang('address'); ?></th>
                                        <th><?= lang('customer').' (Qty)'; ?></th>
                                        <th><?= lang('product_qty'); ?></th>
                                        <th><?= lang('real_grand_total'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th><?= lang('payment_status'); ?></th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="width: 50px;"></th>
                                        <th></th>
                                        <th></th>
                                        <th style="width: 50px;"></th>
                                        <th style="width: 50px;"></th>
                                        <!--<th style="width: 40px;"></th>
                                        <th style="width: 20px;"></th>
                                        <th><?= lang('customer_total'); ?></th>
                                        <th><?= lang('product_qty'); ?></th>
                                        <th><?= lang('real_grand_total'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th></th>-->
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
    <div id="saleman-con" class="tab-pane fade in">
<?php
	$v = "";
	
	if ($this->input->post('start_date')) {
		$v .= "&start_date=" . $this->input->post('start_date');
	}
	if ($this->input->post('end_date')) {
		$v .= "&end_date=" . $this->input->post('end_date');
	}
	if(isset($date)){
		$v .= "&d=" . $date;
	}

?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#sform').hide();
        $('.stoggle_down').click(function () {
            $("#sform").slideDown();
            return false;
        });
        $('.stoggle_up').click(function () {
            $("#sform").slideUp();
            return false;
        });
        $("#product_id").autocomplete({
            source: '<?= admin_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);
                //$(this).val(ui.item.label);
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
    });
</script>
<?php if ($Owner) {
    echo admin_form_open('reports/saleman_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
			<i class="fa-fw fa fa-heart"></i><?=lang('saleman_report'); ?>
			<?php 
				if ($this->input->post('start_date')) {
					echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
				}
			?>
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="stoggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="stoggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('export_to_pdf')?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?=lang('list_results');?></p>
				<div id="sform">
                    <?php echo admin_form_open("reports/saleman"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = "";
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="SLData" class="table table-hover table-striped table-condensed">
                        <thead>
							<tr>
								<th style="width: 3% !important; text-align: center;">
									<input class="checkbox checkth input-xs" type="checkbox" name="check"/>
								</th>
								<th><?php echo $this->lang->line("username"); ?></th>
								<th><?php echo $this->lang->line("email"); ?></th>
								<th><?php echo $this->lang->line("phone"); ?></th>
                                <th style="text-align: right !important;"><?php echo $this->lang->line("commission"); ?></th>
                                <th style="text-align: right !important;"><?php echo $this->lang->line("commission_product"); ?></th>
                                <th style="text-align: right !important;"><?php echo $this->lang->line("total_commission"); ?></th>
								<th style="text-align: right !important;"><?php echo $this->lang->line("amount"); ?></th>
								<th style="text-align: right !important;"><?php echo $this->lang->line("paid"); ?></th>
                                <th style="text-align: right !important; width: 12% !important;"><?php echo $this->lang->line("balance"); ?></th>
								<th style="text-align: center !important;"><?php echo $this->lang->line("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
                        <?php
						if ($this->input->POST('biller')) {
							$biller = $this->input->POST('biller');
						} else {
							$biller = NULL;
						}
						$datt           = $this->reports_model->getLastDate("sales", "date");
                        $start_date     = $this->input->POST('start_date') ? $this->input->POST('start_date') : null;
                        $end_date       = $this->input->POST('end_date') ? $this->input->POST('end_date') : null;

                        if ($start_date) {
                            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
                            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
                        }

						$wheres = "";
						$sdv = $this->db;
                        $sdv->select("username, phone, id, email")->from('users u')->where('group_id = ' . $group_saleman_id);
						// if($this->session->userdata('biller_id') != NULL){
						// 	$sdv->where('u.biller_id', $this->session->userdata('biller_id'));
						// }
						$query = $sdv->get()->result();
						$i        = 1;
                        $tCms     = 0;   
                        $tCmsp    = 0;   
                        $tCmss    = 0;   
						$tAmount  = 0;
						$tPaid    = 0;
                        $tbalance = 0;

                        $qi = '
                                (SELECT 
                                    bpas_sale_items.sale_id AS sale_id,
                                    SUM(COALESCE(bpas_sale_items.purchase_unit_cost, 0) * bpas_sale_items.quantity) AS total_cost,
                                    SUM((COALESCE(bpas_sale_items.unit_price, 0) * bpas_sale_items.quantity) - (COALESCE(bpas_sale_items.purchase_unit_cost, 0) * bpas_sale_items.quantity)) AS total_profit
                                FROM bpas_sale_items
                                LEFT JOIN bpas_products ON bpas_sale_items.product_id = bpas_products.id
                                GROUP BY bpas_sale_items.sale_id) AS bpas_FSI
                            ';
                        $qj = '
                                (SELECT 
                                    bpas_payments.sale_id AS sale_id,
                                    bpas_payments.date AS p_date,
                                    SUM(COALESCE(bpas_payments.amount, 0)) AS total_paid
                                FROM bpas_payments
                                GROUP BY bpas_payments.sale_id) AS bpas_FSJ
                            ';
                        $s_cost_before = " (
                                SELECT 
                                    SUM(COALESCE(s_im.purchase_unit_cost, 0) * s_im.quantity)
                                FROM bpas_sale_items AS s_im
                                WHERE s_im.sale_id = (
                                        SELECT
                                            (SELECT x.id FROM bpas_sales AS x WHERE x.sale_status != 'returned' AND x.reference_no = ss.reference_no) 
                                        FROM bpas_sales AS ss
                                        WHERE ss.id = bpas_sales.id
                                    )
                                GROUP BY s_im.sale_id
                            ) ";
                        $s_paid_before = " (
                                SELECT 
                                    COALESCE(SUM(pmt_.amount), 0) 
                                FROM {$this->db->dbprefix('payments')} AS pmt_ 
                                LEFT JOIN {$this->db->dbprefix('sales')} AS ss_ ON pmt_.sale_id = ss_.id
                                WHERE 
                                    pmt_.sale_id < bpas_sales.id AND ss_.reference_no = bpas_sales.reference_no
                                GROUP BY pmt_.sale_id
                            ) ";
                        
                        if($cost_sale_commission){
                            $str = " 
                                IF(
                                    bpas_sales.sale_status != 'returned',
                                    IF(bpas_FSJ.total_paid - bpas_FSI.total_cost < 0, 0, (bpas_FSJ.total_paid - bpas_FSI.total_cost) * bpas_users.commission / 100),
                                    IF(
                                        " . $s_paid_before . " - " . $s_cost_before . " > 0,
                                        -1 * (Abs(bpas_FSJ.total_paid) - Abs(bpas_FSI.total_cost)) * bpas_users.commission / 100,
                                        0
                                    )
                                ) ";
                                
                            $q = " 
                                SUM(" . $str . ") AS commission,
                                SUM(bpas_sales.total) AS sale_amount,
                                SUM(bpas_sales.paid) AS sale_paid, 
                                commission_product
                            ";
                        } else {
                            // $q = 'SUM((total * commission) / 100) AS commission, SUM(total) AS sale_amount, SUM(paid) AS sale_paid';
                            $q = "SUM((bpas_FSJ.total_paid * commission) / 100) AS commission, SUM(total) AS sale_amount, SUM(paid) AS sale_paid, commission_product";
                        }

						foreach ($query as $rows) {
							$sale = $this->db->select($q);
                            $this->db->from('sales');
                            $this->db->join($qi, 'FSI.sale_id = sales.id', 'left');
                            $this->db->join($qj, 'FSJ.sale_id = sales.id', 'left');
                            $this->db->join('users', 'sales.saleman_by = users.id', 'left');
                            $this->db->group_by('sales.saleman_by');
                            $this->db->where('saleman_by = ' . $rows->id);

							if($biller){
								$this->db->where('sales.biller_id', $biller);
							}
							if($start_date){
                                $this->db->where('sales.date BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
							} 
                            // if($start_date){
                            //     $this->db->where('bpas_FSJ.p_date BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
							// }           
                            $sales = $sale->get()->result();
							$samount = 0; $spaid = 0; $scms = 0; $scmsp = 0; $scmss = 0; $samount = 0; $spaid = 0;
                            foreach($sales as $rw)
                            {
                                $scms       = $rw->commission;
                                $scmsp      = $rw->commission_product;
                                $scmss      = $rw->commission + $rw->commission_product;
                                $samount 	= $rw->sale_amount;
                                $spaid		= $rw->sale_paid;
                            }
						    ?>
							<tr class="active">
								<td style="width: 3% !important; text-align: center;">
									<input class="checkbox multi-select input-xs" type="checkbox" name="val[]" value="<?= $rows->id?>" />
								</td>
								<td><?= ucwords($rows->username) ?></td>
								<td><?=$rows->email?></td>
								<td><?=$rows->phone?></td>
                                <td style="text-align: right;"><?= $scms ? $this->bpas->formatMoney($scms) : $this->bpas->formatMoney(0) ?></td>
                                <td style="text-align: right;"><?= $scmsp ? $this->bpas->formatMoney($scmsp) : $this->bpas->formatMoney(0) ?></td>
                                <td style="text-align: right;"><?= $scmss ? $this->bpas->formatMoney($scmss) : $this->bpas->formatMoney(0) ?></td>
								<td style="text-align: right;"><?= $samount ? $this->bpas->formatMoney($samount) : $this->bpas->formatMoney(0) ?></td>
								<td style="text-align: right;"><?= $spaid ? $this->bpas->formatMoney($spaid) : $this->bpas->formatMoney(0) ?></td>
                                <td style="text-align: right; width: 12% !important;"><?= $samount - $spaid ? $this->bpas->formatMoney($samount - $spaid) : $this->bpas->formatMoney(0) ?></td>
								<td style="text-align: center !important;"><a href="<?= admin_url('reports/saleman_report/' . $rows->id) ?>"><span class='label label-primary'><?= lang('view_report') ?></span></a></td>
							</tr>
						    <?php
                            $tCmsp	  += $scmsp;
                            $tCmss	  += $scmss;
                            $tCms	  += $scms;
							$tAmount  += $samount;
							$tPaid	  += $spaid;
							$tbalance += ($samount - $spaid);
						}
						?>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="width: 3% !important; text-align: center;">
                                <input class="checkbox checkft input-xs" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang('username')?></th>
                            <th><?= lang('email')?></th>
                            <th><?= lang('phone')?></th>
                            <th class="text-right"><?= $this->bpas->formatMoney($tCms) ?></th>
                            <th class="text-right"><?= $this->bpas->formatMoney($tCmsp) ?></th>
                            <th class="text-right"><?= $this->bpas->formatMoney($tCmss) ?></th>
                            <th class="text-right"><?= $this->bpas->formatMoney($tAmount) ?></th>
                            <th class="text-right"><?= $this->bpas->formatMoney($tPaid) ?></th>
                            <th class="text-right"><?= $this->bpas->formatMoney($tbalance) ?></th>
                            <th class="text-center"><?= lang('actions') ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <div id="saleman_rank_commission" class="tab-pane fade">
        <div class="box">
            <div class="box-header">
                <h2 class="blue">
                    <i class="fa-fw fa fa-heart"></i><?=lang('saleman_rank_commission'); ?>
                </h2>
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
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown" style="margin-top: 10px; padding: 0 10px;">
                            <a href="#" id="excel_saleman" class="tip" title="<?= lang('export_to_excel') ?>">
                               <i class="fa fa-file-excel-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?=lang('list_results');?></p>
                        <div class="form">
                            <?php echo admin_form_open("reports/saleman"); ?>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="saleman"><?= lang("saleman"); ?></label>
                                        <?php
                                            $sm[""] = "";
                                            foreach ($saleman as $saleman) {
                                                $sm[$saleman->id] = $saleman->first_name .' '. $saleman->last_name;
                                            }
                                            echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"');
                                        ?>
                                    </div>
                                </div>
                    
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="group"><?= lang('month'); ?></label>
                                        <?php
                                        $cur_mth = date('m');
                                        foreach ($months as $key => $month) {
                                            $mth[$key] = $month;
                                        }
                                        echo form_dropdown('month', $mth, (isset($_POST['month']) ? $_POST['month'] : $cur_mth), 'class="form-control" id="month" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('month') . '"'); ?>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="year"><?= lang('year'); ?></label>
                                        <?php
                                            foreach ($years as $key => $year) {
                                                $yr[$key] = $year;
                                            }
                                        ?>
                                        <?php echo form_dropdown('year', $yr, (isset($_POST['year']) ? $_POST['year'] : ''), 'class="form-control" id="year" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('year') . '"'); ?>
                                    </div>
                                </div>
                                            
                            </div>
                            <div class="form-group">
                                <div
                                    class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <?php
                            $l = "";
                            if ($this->input->post('month')){
                                $l .= "&month=" . $this->input->post('month');
                            }
                            if ($this->input->post('year')) {
                                $l .= "&year=" . $this->input->post('year');
                            }
                            if($this->input->post('saleman')){
                                $l .= "&saleman=" . $this->input->post('saleman'); 
                            }
                        ?>
                        <script type="text/javascript">
                                $(document).ready(function () {
                                    function link_url(x){                                        
                                        return "<div style='text-align: center !important;' ><a style='text-decoration:none;' href='<?= admin_url('reports/sales/?sale_rank=') ?>" + x + "'> <span  class='label label-primary'><?= lang('view_report') ?></span></a></div>";
                                    }
                                    oTable = $('#TrRData').dataTable({
                                        "aaSorting": [[0, "desc"]],
                                        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                                        "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                        'bProcessing': true, 'bServerSide': true,
                                        'sAjaxSource': '<?= admin_url('reports/getsale_rank_commisstion/?l=1' . $l) ?>',
                                        'fnServerData': function (sSource, aoData, fnCallback) {
                                            aoData.push({
                                                "name": "<?= $this->security->get_csrf_token_name() ?>",
                                                "value": "<?= $this->security->get_csrf_hash() ?>"
                                            });
                                            $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                                        },
                                        "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                                            var amount=0 , com_s=0;
                                            for (var i = 0; i < aaData.length; i++){
                                                amount += parseFloat(aaData[aiDisplay[i]][4]);
                                                com_s  += parseFloat(aaData[aiDisplay[i]][5]);
                                            }
                                            var nCells = nRow.getElementsByTagName('th');
                                            nCells[4].innerHTML = currencyFormat(formatMoney(amount));
                                            nCells[5].innerHTML = currencyFormat(formatMoney(com_s));
                                        },
                                        "aoColumns": [{"mRender": checkbox}, null ,null, null, {"mRender": currencyFormat }, {"mRender": currencyFormat }, null, {"mRender": link_url}],
                                    }).fnSetFilteringDelay().dtFilter([
                                        {column_number: 1, filter_default_label: "[<?=lang('username'); ?>]", filter_type: "text", data: []},
                                        {column_number: 6, filter_default_label: "[<?=lang('month'); ?>]", filter_type: "text", data: []},
                                        {column_number: 6, filter_default_label: "[<?=lang('Commisstion_month'); ?>]", filter_type: "text", data: []},
                                    ], "footer");

                                    $('#excel_saleman').click(function() {
                                        event.preventDefault();
                                        window.location.href = "<?=admin_url('reports/getsale_rank_commisstion/0/xls/?l=1' . $l)?>";
                                        return false;
                                    });
                                });
                        </script>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table id="TrRData" class="table table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width: 3% !important; text-align: center;">
                                            <input class="checkbox checkth input-xs" type="checkbox" name="check"/>
                                        </th>
                                        <th><?php echo $this->lang->line("username"); ?></th>
                                        <th><?php echo $this->lang->line("email"); ?></th>
                                        <th><?php echo $this->lang->line("phone"); ?></th>
                                        <th style="text-align: right !important;"><?php echo $this->lang->line("amount"); ?></th>
                                        <th style="text-align: right !important;"><?php echo $this->lang->line("commission_sale"); ?></th>
                                        <th style="text-align: left !important; width: 12% !important;"><?php echo $this->lang->line("commission_month"); ?></th>
                                        <th style="text-align: center !important;"><?php echo $this->lang->line("actions"); ?></th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8"
                                            class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="width: 3% !important; text-align: center;">
                                            <input class="checkbox checkft input-xs" type="checkbox" name="check"/>
                                        </th>
                                        <th></th>
                                        <th><?= lang('email')?></th>
                                        <th><?= lang('phone')?></th>
                                        <th class="text-left"></th>
                                        <th class="text-right"></th>
                                        <th class="text-right"></th>
                                        <th class="text-center"><?= lang('actions')?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                   </div>
               </div>
           </div>
       </div>
    </div>
</div>