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
                         <?= $rows[0]->reference;?></h2>
                </div> 
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
					<thead> 
                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("order_print"); ?></th>
                        <th><?= lang("code"); ?></th>
                        <th><?= lang("product_name"); ?></th>
                        <th><?= lang("qty"); ?></th>
                        <th><?= lang("new qty"); ?></th>
                        <th><?= lang("description"); ?></th>
                    </tr> 
                    </thead>
                    <tbody> 
                    <?php $r = 1;
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"><?= $r; ?></td>
                            <td style="text-align:center; width:40px; vertical-align:middle; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"><?= $row->print_index; ?></td>
                            <td style="vertical-align:middle; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"><?= $row->item_code ; ?></td>
							<td style="text-align:left; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"> <?= $row->item_name ; ?></td>
                            <td style="text-align:center; vertical-align:middle; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"> <?= $row->qty ; ?></td>
                            <td style="text-align:center; vertical-align:middle; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"> <?= $row->new_qty ; ?></td>
                            <td style="text-align:center; vertical-align:middle; <?= ($row->status== 1) ? 'background:#ff000061' : ''?>"> <?= $row->description ; ?></td>
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
