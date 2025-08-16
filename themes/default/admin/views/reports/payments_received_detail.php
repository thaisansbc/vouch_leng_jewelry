<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?= lang('payments_received_detail') ?></title>
	<link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo $assets ?>styles/custome.css" rel="stylesheet">
</head>
<style>
	body {
		font-size: 14px !important;
	}
    button {
        border-radius: 0 !important;
    }
	.container {
		width: 21cm;
		height: 29.7cm;
		margin: 20px auto;
		box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
	}
	.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        border: 1px solid #000 !important;
    }
	@media print {
		@page { size: A4; margin: 0; }
        .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        	border: 1px solid #000 !important;
    	}
        thead { display: table-header-group; }
    }
</style>
<body>
	<div class="container" style="margin: 15px auto;">
		<div class="col-xs-12" style="width: 100% !important; margin-bottom: 15px;">
			<div style="position: relative; min-height: 28.5cm;">
				<div class="row" style="margin-top: 20px !important;">
					<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();"><i class="fa fa-print"></i> <?= lang('print'); ?></button>
				</div>
				<div class="row" style="font-size: 14px; margin-top: 15px !important;">
					<div class="col-xs-7">
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td><?= lang('PR Nº') ?></td>
									<td style="width: 3%;">:</td>
									<td><?= $payments ? $payments[0]->reference_no : ''; ?></td>
								</tr>
								<tr>
									<td><?= lang('date') ?></td>
									<td>:</td>
									<td><?= $payments ? $this->bpas->hrsd($payments[0]->date) : ''; ?></td>
								</tr>
								<tr>
									<td><?= lang('vender') ?></td>
									<td>:</td>
									<td><?= $biller ? ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) : ''; ?></td>
								</tr>
								<tr>
									<td><?= lang('phone') ?></td>
									<td>:</td>
									<td><?= $biller ? $biller->phone : ''; ?></td>
								</tr>
								<tr>
									<td style="vertical-align: top;"><?= lang('address') ?></td>
									<td style="vertical-align: top;">:</td>
									<td style="vertical-align: top;"><?= $biller ? ($biller->address . ' ' . $biller->postal_code . ' ' . $biller->city . ' ' . $biller->country . ' ' . $biller->state) : ''; ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-xs-5">
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td style="width: 34%; vertical-align: top;"><?= lang('customer') ?></td>
									<td style="width: 5%; vertical-align: top;">:</td>
									<td><?= $customer ? ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) : ''; ?></td>
								</tr>
								<tr>
									<td><?= lang('phone') ?></td>
									<td>:</td>
									<td><?= $customer ? $customer->phone : ''; ?></td>
								</tr>
								<tr>
									<td style="vertical-align: top;"><?= lang('address') ?></td>
									<td style="vertical-align: top;">:</td>
									<td style="vertical-align: top;"><?= $customer ? ($customer->address . (($customer->city || $customer->postal_code || $customer->state) ? ', ' : '') . $customer->city . ' ' . $customer->postal_code . ' ' . ($customer->state != 0 ? $customer->state : '') . ($customer->country ? ', ' : '') . $customer->country) : ''; ?></td>
								</tr>
								<tr>
									<td><?= lang('payment_type') ?></td>
									<td>:</td>
									<td><?= $payments ? lang($payments[0]->paid_by) : ''; ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 text-center">
						<h3 style="font-family: 'Khmer OS Muol Light'; margin-top: 15px; color: #2874A6 !important;">បណ្ណទទួលប្រាក់</h3>
						<h4 style="font-weight: bold; font-family: 'Time New Romance';"><?= strtoupper(lang('payment_received')); ?></h4>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<table class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="1">
								<thead>
									<tr>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('Nº') ?></th>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('reference_no') ?></th>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('date') ?></th>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('amount') ?></th>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('paid') ?></th>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('discount') ?></th>
										<th style="background-color: #3498DB !important; text-align: center;"><?= lang('balance') ?></th>
									</tr>
								</thead>
								<tbody>
									<?php if(!empty($invs)) { ?>
										<?php 
											$i = 1; 
											$amount         = 0;
											$paid           = 0;
											$discount       = 0;
											$total_balance  = 0;
										?>
										<?php foreach($invs as $key => $inv) { ?>
											<tr>
												<td style="text-align: center;"><?= $i++; ?></td>
												<td><?= $inv->reference_no; ?></td>
												<td><?= $this->bpas->hrsd($inv->date); ?></td>
												<td>
													<div class="pull-left"><?= $currency->symbol ?></div>
													<div class="pull-right"><?= $inv->amount_before_paid != 0 ? $this->bpas->formatMoney($inv->amount_before_paid + $payments[$key]->discount) : '-'; ?></div>
												</td>
												<td>
													<div class="pull-left"><?= $currency->symbol ?></div>
													<div class="pull-right"><?= $payments[$key]->amount != 0 ? $this->bpas->formatMoney($payments[$key]->amount) : '-'; ?></div>
												</td>
												<td>
													<div class="pull-left"><?= $currency->symbol ?></div>
													<div class="pull-right"><?= $payments[$key]->discount != 0 ? $this->bpas->formatMoney($payments[$key]->discount) : '-'; ?></div>
												</td>
												<td>
													<?php $balance = $inv->amount_before_paid - $payments[$key]->amount; ?>
													<div class="pull-left"><?= $currency->symbol ?></div>
													<div class="pull-right"><?= $balance != 0 ? $this->bpas->formatMoney($balance) : '-'; ?></div>
												</td>
											</tr>
											<?php 
												$amount         += ($inv->amount_before_paid + $payments[$key]->discount);
												$paid           += $payments[$key]->amount;
												$discount       += $payments[$key]->discount;
												$total_balance  += $balance;
											?>
										<?php } ?>
									<?php } ?>
									<?php
										$G = ((count($invs) / 10) - floor(count($invs) / 10)) * 10;
										if(count($invs) != 10){
											if($G < 10 && $G != 0){
												$G++;
												$num = count($invs) + 1;
												while($G <= 10) {
													echo '<tr><td style="text-align: center;">'.$num.'</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
													$G++;
													$num++;
												}
											}
										}
									?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="3" style="text-align: center; font-weight: bold; background-color: #CACFD2 !important;"><?= lang('total') ?></td>
										<td style="font-weight: bold; background-color: #CACFD2 !important;">
											<div class="pull-left"><?= $currency->symbol ?></div>
											<div class="pull-right"><?= isset($amount) && $amount != 0 ? $this->bpas->formatMoney($amount) : '-'; ?></div>
										</td>
										<td style="font-weight: bold; background-color: #CACFD2 !important;">
											<div class="pull-left"><?= $currency->symbol ?></div>
											<div class="pull-right"><?= isset($paid) && $paid != 0 ? $this->bpas->formatMoney($paid) : '-'; ?></div>
										</td>
										<td style="font-weight: bold; background-color: #CACFD2 !important;">
											<div class="pull-left"><?= $currency->symbol ?></div>
											<div class="pull-right"><?= isset($discount) && $discount != 0 ? $this->bpas->formatMoney($discount) : '-'; ?></div>
										</td>
										<td style="font-weight: bold; background-color: #CACFD2 !important;">
											<div class="pull-left"><?= $currency->symbol ?></div>
											<div class="pull-right"><?= isset($total_balance) && $total_balance != 0 ? $this->bpas->formatMoney($total_balance) : '-'; ?></div>
										</td>
									</tr>
								</tfoot>
						</table>
					</div>
				</div>
			</div>
			<div class="footer" style="width: 100%; position: absolute; bottom: 0; left: 0;">
				<div class="row">
					<div class="col-xs-6 pull-left text-center">
						<hr class="signature" style="border-top: 2px dotted black; width: 60%; display: block; margin: 35px auto 0 auto;">
						<p style="margin-top: 2px;">Authorized Signatures</p>
					</div>
					<div class="col-xs-6 pull-right text-center">
						<hr class="signature" style="border-top: 2px dotted black; width: 60%; display: block; margin: 35px auto 0 auto;">
						<p style="margin-top: 2px;">Authorized Signatures</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 21cm; margin: 0 auto;">
		<a class="btn btn-warning no-print" href="<?= admin_url('reports/payments_received'); ?>">
			<i class="fa fa-hand-o-left" aria-hidden="true"></i>&nbsp;<?= lang("back"); ?>
		</a>
	</div>
</body>
</html>