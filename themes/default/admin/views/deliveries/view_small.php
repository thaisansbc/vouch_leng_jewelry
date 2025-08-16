<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content">
	<div class="box-body">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
		<html>
			<body>
				<div id="wrapper">
					<div id="receiptData">
						<div id="receipt-data">
							<table style="width:100%">
								<tr>
									
									<td class="text-center">
										<b style="font-size:20px;"><?= $biller->name ?></b>
										<p class="font_12"> <?= $biller->address . " " . $biller->city ?></p>
										<p class="font_12"> <?= $biller->phone ?></p>
									</td>
									
								</tr>
							</table>
							<table  class="font_12" style="width:100%; margin-bottom:10px">
								<tr>
									<td><?= lang('name') ?></td>
									<td> : <strong><?= $delivery->customer ?></strong></td>
								</tr>
								<tr>
									<td><?= ($delivery->sale_reference_no?lang('si_reference'):lang('so_reference')) ?></td>
									<td style="text-align:left"> : <b><?= ($delivery->sale_reference_no?$delivery->sale_reference_no:$delivery->so_reference_no) ?></b></td>
								</tr>
								<tr>
									<td><?= lang('dn_reference') ?></td>
									<td style="text-align:left"> : <b><?= $delivery->do_reference_no ?></b></td>
								</tr>
								<tr>
									<td><?= lang('date') ?></td>
									<td style="text-align:left"> : <b><?= $this->bpas->hrsd($delivery->date) ?></b></td>
								</tr>
							</table>
							<table class="table table-striped table-condensed font_12">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("quantity"); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
										$tbody = '';
										$i=1;
										foreach ($rows as $row){
											$tbody .='<tr>
															<td class="text_center">'.$i.'</td>
															<td class="text_left">
																'.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').'
																'.($row->serial_no ? '<br>' . $row->serial_no : '').'
															</td>
															<td class="text_right">'.$this->bpas->formatQuantity($row->unit_quantity).' '.$row->unit_name.'</td>
														</tr>';		
											$i++;
										}
										echo $tbody;
									?>
								</tbody>
							</table>
						</div>
						<div style="clear:both;"></div>
						<div id="buttons" class="no-print">
							<?php 
								echo '<button onclick="window.print();" class="btn btn-block btn-primary print">'.lang("print").'</button>';
							?>
						</div>
					</div>
				</div>
			</body>
		</html>
	</div>
</div>	

<style>
	.box-body{
		padding: 15px !important;
	}
	.font_12{
		font-size:12px !important
	}
	@media print {
		.font_12 {
           font-size:10px !important
		}
		.main_content {
            display: none !important;
        }
		.box-body{
			padding: 0px !important;
		}
	}
</style>












