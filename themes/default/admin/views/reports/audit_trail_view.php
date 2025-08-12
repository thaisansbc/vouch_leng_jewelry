<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
			<table width="100%" style="white-space:wrap !important;">
				<tr>
					<td><?= lang("date") ?></td>
					<td>: <?= $this->bpas->hrsd($row->created_at) ?></td>
				</tr>
				<tr>
					<td><?= lang("user") ?></td>
					<td>: <?= $user->first_name . ' '. $user->last_name ?></td>
				</tr>
				<tr>
					<td><?= lang("event") ?></td>
					<td>: <?= $row->event ?></td>
				</tr>
				<tr>
					<td><?= lang("table") ?></td>
					<td>: <?= $row->table_name ?></td>
				</tr>
				<tr>
					
					<td class="well well-sm" style="vertical-align:top;" width="50%">
						<table width="100%" class="table">
							
							<tr>
								<th><?= lang("old_values") ?></th>
								<th><?= lang("data") ?></th>
							</tr>
						<?php 
						$old = str_replace(['{"', '"}'], ' ', $row->old_values);


						$array_old_value = explode('","', $old);
						foreach ($array_old_value as $values)
						{
					
							$old_value = explode('":"',$values);
							?>
							<tr>
								<td width="50%"><?= $old_value[0];?></td>
								<td width="50%"><?= $old_value[1];?></td>
							</tr>
						<?php 
						}
						?>
						</table>
					
					</td>
					<td class="well well-sm" style="vertical-align:top">
						<table width="100%" class="table">
							
							<tr>
								<th><?= lang("new_values") ?></th>
								<th><?= lang("data") ?></th>
							</tr>
						<?php 
						$new = str_replace(['{"', '"}'], ' ', $row->new_values);


						$array_new_value = explode('","', $new);
						foreach ($array_new_value as $values)
						{
					
							$new_value = explode('":"',$values);
							?>
							<tr>
								<td width="50%"><?= $new_value[0];?></td>
								<td width="50%"><?= $new_value[1];?></td>
							</tr>
						<?php 
						}
						?>
						</table>
				
					</td>
				</tr>
				
				<tr>
					<td><?= lang("url") ?></td>
					<td>: <?= $row->url ?></td>
				</tr>
				
			</table>
		</div>
	</div>
</div>
