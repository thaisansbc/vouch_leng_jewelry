<?php

$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
} */

if ($this->input->post('account')) {
    $v .= "&account=" . $this->input->post('account');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<style type="text/css">
    .topborder div { border-top: 1px solid #CCC; }
</style>

<style>
   .table td:nth-child(6) {
        text-align: center;
    }
   #registerTable { white-space: nowrap; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('cash_management_report'); ?><?php
            if ($this->input->post('start_date')) {
                echo " From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown hide"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                    <i class="icon fa fa-file-picture-o"></i></a></li>
				<li class="dropdown hide">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
                        aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('reports/cash_books') ?>"><i
                                    class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($billers as $biller) {
                            echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/cash_books/0/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("reports/cash_management"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php

                                $bill[""] = " ";
                                foreach ($billers as $biller) {
                                    $bill[$biller->id] = ($biller->company !='-')? $biller->company:$biller->name;
                                }
                                echo form_dropdown('biller', $bill, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : $this->bpas->hrld($start_date2)), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : $this->bpas->hrld($end_date2)), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-scroll">
                    <table cellpadding="0" cellspacing="0" class="table table-hover table-bordered">
						<thead>
							<tr>
								<th rowspan="2"><div class="fix-text"><?= lang('cash'); ?></div></th>
								<th colspan="2"><div class="text-center"><?= lang('in'); ?></div></th>
								<th colspan="2"><div class="text-center"><?= lang('out'); ?></div></th>
                                <th rowspan="2"><div class="text-center"><?= lang('balance'); ?></div></th>
							</tr>
                            <tr>
                                <th><div class="text-center"><?= lang('sale'); ?></div></th>
                                <th><div class="text-center"><?= lang('sale_return'); ?></div></th>
                                <th><div class="text-center"><?= lang('purchase'); ?></div></th>
                                <th><div class="text-center"><?= lang('expense'); ?></div></th>
                            </tr>
                        </thead>
					       <?php 
                           $totalsale=0;$total_salereturn=0;$total_purchase=0;$total_pexpense=0;$tsubtotal=0;
                           foreach($cash_transaction as $cash){
                                $paymentIn = $this->site->getpaymentIn_by($cash->code,$start_date2,$end_date2,0);
                                $paymentInReturn = $this->site->getpaymentInReturn_by($cash->code,$start_date2,$end_date2,0);
                                $money_in = ($paymentIn->amount!=0) ? $paymentIn->amount:0;
                                $money_inReturn =($paymentInReturn->amount !=0) ? abs($paymentInReturn->amount) :0;

                                $paymentout = $this->site->getpaymentOut_by($cash->code,$start_date2,$end_date2,0);
                                $money_out = ($paymentout->amount !=0) ? ($paymentout->amount):0;

                                $paymentExpense = $this->site->getpaymentExpense_by($cash->code,$start_date2,$end_date2,0);
                                $money_expense = ($paymentExpense->amount !=0) ? ($paymentExpense->amount):0;

                                $subtotal = ($money_in - $money_inReturn) - $money_out - $money_expense;
                            ?>
                            <tr>
                                <td><?= $cash->name;?></td>
                                <td class="text-center"><?= $this->bpas->formatMoney($money_in);?></td>
                                <td class="text-center"><?= '('.$this->bpas->formatMoney($money_inReturn).')';?></td>
                                <td class="text-center"><?= '('.$this->bpas->formatMoney($money_out).')';?></td>
                                <td class="text-center"><?= '('.$this->bpas->formatMoney($money_expense).')';?></td>
                                <td class="text-center"><?= $this->bpas->formatMoney($subtotal)?></td>
                            </tr>
                            <?php
                                $totalsale          +=  $money_in;
                                $total_salereturn   +=  $money_inReturn;
                                $total_purchase     +=  $money_out;
                                $total_pexpense     +=  $money_expense;
                                $tsubtotal          +=  $subtotal;
                           }
                           ?>
                        <tfooter>
						      <th></th>
                              <th class="text-center"><?= $this->bpas->formatMoney($totalsale);?></th>
                              <th class="text-center">(<?= $this->bpas->formatMoney($total_salereturn);?>)</th>
                              <th class="text-center">(<?= $this->bpas->formatMoney($total_purchase);?>)</th>
                              <th class="text-center">(<?= $this->bpas->formatMoney($total_pexpense);?>)</th>
                              <th class="text-center"><?= $tsubtotal > 0 ? $this->bpas->formatMoney($tsubtotal) : 
                                    '('.$this->bpas->formatMoney(abs($tsubtotal)).')';?></th>
                        </tfooter>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
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
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/cash_management/pdf')?>";
            return false;
        });
        $("#xls").click(function(e) {
            var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + $('.table-scroll').html());
            this.href = result;
            this.download = "Cash Report.xls";
            return true;            
        });

        /*$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/cash_management/0/0/xls/?v=1'.$v)?>";
            return false;
        });*/
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>