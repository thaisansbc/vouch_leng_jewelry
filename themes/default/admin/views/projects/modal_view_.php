<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
		
                <div class="text-center" style="margin-bottom:20px;">
						<h2>
                         <?= $row_data->color; ?></h2>
                </div>
       
            <div class="table-responsive">
                <table class="table table-hover table-striped print-table order-table">
					<thead>

                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("Color Code"); ?></th>
                        <th><?= lang("quantity"); ?></th>
                        <th><?= lang("Consumption"); ?></th>
                        <th><?= lang("Makeup"); ?></th>
                        <th><?= lang("Color"); ?></th>
                        <th><?= lang("total"); ?></th>
                        <th><?= lang("total mak"); ?></th>
                        <th><?= lang("grandtotal"); ?></th>
                    </tr>

                    </thead>
                    <tbody>
					
                    <?php $r = 1;
                    foreach ($rows as $row):
						$total_con =$row->qty * $row_data->consumption ; 
						$total_mak =$row->qty * $row_data->makeup ; 
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;"><?= $row->color_code ; ?></td>
                            <td style="text-align:center; vertical-align:middle;"> <?= $row->qty ; ?></td>
                            <td style="text-align:left;"> <?= $row_data->consumption ; ?></td>
                            <td style="text-align:left;"> <?= $row_data->makeup ; ?></td>
							<td style="text-align:left;"> <?= $row->color_name ; ?></td>
							<td style="text-align:left;"> <?= $total_con ?></td>
							<td style="text-align:left;"> <?= $total_mak ; ?></td>
							<td style="text-align:left;"> <?= $total_con + $total_mak ; ?></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
           
                    ?>
                    </tbody>
                
                </table>
            </div>
        </div>
    </div>
</div>
