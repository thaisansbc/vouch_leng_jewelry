<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
	.divTable {
        display: table;
        width: 100%;
		margin-top: 20px;
    }
    .divRow {
        display: table-row;
        font-size: 12px;
		height: 25px;
    }
	.tb_1 .row_1 .divCell {
		margin-bottom: -8px;
	}
    .tb_1 .divCell, .tb_2 .divCell {
        float: left;
        display :table-column;
    }
	.tb_1 .cell_1 {
		width: 30%
	}
    .tb_1 .cell_2 {
        width: 5%;
    }
	.tb_1 .cell_3 {
        width: 62%;
    }
	.tb_2 .cell_1 {
		width: 45%
	}
    .tb_2 .cell_2 {
        width: 5%;
    }
</style>
<div class="modal-dialog modal-lg no-modal-header">
	<div class="modal-content">
		<div class="modal-body">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
			<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
				<i class="fa fa-print"></i> <?= lang('print'); ?>
			</button>
			<div class="col-xs-4">
				<?php if ($logo) { ?>
					<img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>" style="width:90%">
				<?php } ?>
			</div>
			
			<div class="col-xs-4" style="line-height: 90%; text-align: center;">
				<p style="font-family: 'Khmer OS Content'; font-size: 18px; font-weight: bold; margin-top: 20px;">សក្ខីប័ត្រចំណាយ</p>
				<p style="font-size: 14px; font-weight: bold;">PAYMENT VOUCHER</p>
			</div> 

			<div class="clearfix"></div>
			<div class="col-xs-7">
				<div class="divTable tb_1 ">
                    <div class="divRow row_1">
                        <div class="divCell cell_1">អាសយដ្ឋាន / <?= lang('address'); ?></div>
                        <div class="divCell cell_2">:</div>
                        <div class="divCell cell_3" style="margin-top: -8px;"><?= $biller->address; ?></div>
                    </div>
                    <div class="divRow row_2">
                        <div class="divCell cell_1">ទូរស័ព្ទលេខ / <?= lang('tel'); ?></div>
                        <div class="divCell cell_2">:</div>
                        <div class="divCell cell_3"><?= $biller->phone ? $biller->phone : "";?></div>
                    </div>
                </div>
			</div>
			<div class="col-xs-5">
				<div class="divTable tb_2" style="padding-left: 10%;">
					<div class="divRow row_1">
						<div class="divCell cell_1">លេខសក្ខីប័ត្រ / P.V No</div>
						<div class="divCell cell_2">:</div>
						<div class="divCell cell_3"><?php echo $expense->reference; ?></div>
					</div>
					<div class="divRow row_2">
						<div class="divCell cell_1">កាលបរិច្ឆេទ / <?= lang('date'); ?></div>
						<div class="divCell cell_2">:</div>
						<div class="divCell cell_3"><?php echo $expense->date; ?></div>
					</div>
				</div>
			</div>
			<div style="clear: both;"></div>
			<div class="col-xs-12">
				<table width="100%">
					<tr>
						<td width="147" rowspan="2">
							<p>ទូទាត់ដោយៈ </p>
							<p>Payment By</p>
						</td>
						<td height="29" colspan="6">&nbsp;
							<input type="checkbox" name="" style="margin:0 2px;">
							Cash &emsp;
							<input type="checkbox" name=""> Bank
							&emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
							<input type="checkbox" name="" style="margin:0 2px;"> T.T&emsp;
							<input type="checkbox" name=""> Cheque No
						</td>
					</tr>
					<tr>
						<td height="29" colspan="6">&nbsp;
							Cash Bank Name:&emsp;Cash on hand
							&emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
							<input type="checkbox" name=""> Bank Account No:
						</td>
					</tr>
					<tr>
						<td height="42" colspan="7">
							<p>
								<input type="checkbox" name=""> សំណងទូទាត់ Reimbursement &nbsp;
								<input type="checkbox" name=""> ទូទាត់បុរេប្រទាន​ Advance Settlement &nbsp;
								<input type="checkbox" name=""> គម្រោងថវិការ Budget Plan &nbsp;
								<input type="checkbox" name=""> ផ្សេងៗ​
							</p>
							<!--  &emsp; -->
						</td>
					</tr>
				</table>
			</div>
			<div>
				<table cellspacing="0" border="1" class="table table-hover table-striped" style="margin-bottom: 0; margin-top: 0px;">
					<thead>
						<tr>
							<td style="border-bottom: 1px solid #000000;border-top:1px solid #000000;border-right:1px solid #000000;"><strong>លេខរៀង<br> No:</strong></td>	
							<td style="border-bottom: 1px solid #000000;border-top:1px solid #000000;border-right:1px solid #000000;">ចំណាយ <br> Expense</td>
							<td style="border-bottom: 1px solid #000000;border-top:1px solid #000000;border-right:1px solid #000000;">បរិយាយ <br> Description</td>
							<td style="border-bottom: 1px solid #000000;border-top:1px solid #000000;border-right:1px solid #000000;">តំលៃ<br><?= lang('cost')?></td>
							<td style="border-bottom: 1px solid #000000;border-top:1px solid #000000;border-right:1px solid #000000;">ចំនួន<br><?= lang('qty')?></td>
							<td style="border-bottom: 1px solid #000000;border-top:1px solid #000000;">សរុប<br><?= lang('subtotal')?></td>
						</tr>
					</thead>
					<tbody>
						
					<?php 
					$i = 0; 
					foreach($items as $value){
						$i++; ?>
						<tr>
							<td width="20" style="border-bottom: 1px solid #000000;border-right:1px solid #000000;"><?= $i; ?></td>
							<td style="border-bottom: 1px solid #000000;border-right:1px solid #000000;"><?= $value->category_name ?></td>
							<td style="border-bottom: 1px solid #000000;border-right:1px solid #000000;"><?= $value->description ?></td>
							<td style="border-bottom: 1px solid #000000;border-right:1px solid #000000;"><?= $this->bpas->formatDecimal($value->unit_cost); ?></td>
							<td style="border-bottom: 1px solid #000000;border-right:1px solid #000000;"><?= $value->quantity; ?></td>
							<td style="border-bottom: 1px solid #000000;"><?= $this->bpas->formatDecimal($value->subtotal); ?></td>
						</tr>
						<?php } 
						?>
						
					</tbody>
						
					<tfoot>
						<tr>
							<td colspan="3" align="right">&nbsp;</td>
							<td align="right" colspan="2" style="border-bottom: 1px solid #000000;border-left:1px solid #000000;">
								<?= 'សរុប / Sub Total';
								?>
								</td>
							<td style="border-bottom: 1px solid #000000;"><?=  $this->bpas->formatMoney($expense->amount); ?></td>
						</tr>
						<?php 
						if($expense->order_discount){
						?>
						<tr>
							<td colspan="3" align="right">&nbsp;</td>
							<td align="right" colspan="2" style="border-bottom: 1px solid #000000;border-left:1px solid #000000;">
								<?= 'បញ្ចុះតំលៃ / Order Discount';?>
								</td>
							<td style="border-bottom: 1px solid #000000;"><?=  $this->bpas->formatMoney($expense->order_discount); ?></td>
						</tr>
						<?php
						}
						if($expense->order_tax){
						?>
						<tr>
							<td colspan="3" align="right">&nbsp;</td>
							<td align="right" colspan="2" style="border-bottom: 1px solid #000000;border-left:1px solid #000000;">
								<?= 'ពន្ធអាករ / Order Tax';?>
								</td>
							<td style="border-bottom: 1px solid #000000;"><?=  $this->bpas->formatMoney($expense->order_tax); ?></td>
						</tr>
						<?php
						}
						$p_currencies = array();
						$g_currencies = json_decode($expense->currency);							
						foreach($g_currencies as $currency){
						?>
						<tr>
							<td colspan="3" align="right">&nbsp;</td>
							<td align="right" colspan="2" style="border-bottom: 1px solid #000000;border-left:1px solid #000000;">
								<?php 
								if($currency->code == 'KHR'){
									echo 'សរុបជាលុយរៀល / Total Riel';
								}else if($currency->code == 'BAHT'){
									echo 'សរុបជាលុយបាត / Total BAHT';
								}else{
									echo 'សរុបជាលុយដុល្លា / Total Dollar';
								}
								?>
								</td>
							<td style="border-bottom: 1px solid #000000;"><?=  $this->bpas->formatMoney($expense->grand_total*$currency->rate); ?></td>
						</tr>
						<?php
						}
						?>
						

					</tfoot>
				</table>
				<div class="row" style="font-size: 11px;">
	                
	                <div class="col-xs-3 pull-left text-center">
	                    <p style="margin-top: 2px;">អ្នករៀបចំ / Prepared by</p><br><br>
	                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 30px auto 0 auto;">
	                </div>
	                <div class="col-xs-3 pull-left text-center">
	                    <p style="margin-top: 2px;">អ្នកត្រួតពិនិត្យ / Checked by</p><br><br>
	                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 30px auto 0 auto;">
	                </div>
	                <div class="col-xs-3 pull-left text-center ">
	                    <p style="margin-top: 2px;">អ្នកទទួល / Received by</p><br><br>
	                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 30px auto 0 auto;">
	                </div>
	                <div class="col-xs-3 pull-left text-center">
	                    <p style="margin-top: 2px;">អ្នកអនុម័ត / Approved by</p><br><br>
	                    <hr class="signature" style="border-top: 2px dotted black; width: 50%; display: block; margin: 30px auto 0 auto;">
	                </div>
	            </div>
			</div>
		</div>
	</div>
</div>