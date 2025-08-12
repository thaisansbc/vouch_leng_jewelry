<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $saleman->username ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="margin-bottom:0;">
                    <tbody>
                        <tr>
                            <td><strong><?= lang('first_name'); ?></strong></td>
                            <td><?= $saleman->first_name; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('last_name'); ?></strong></td>
                            <td><?= $saleman->last_name; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('company'); ?></strong></td>
                            <td><?= $saleman->company; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('phone'); ?></strong></td>
                            <td><?= $saleman->phone; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('gender'); ?></strong></td>
                            <td><?= $saleman->gender ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('username'); ?></strong></td>
                            <td><?= $saleman->username; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('email'); ?></strong></td>
                            <td><?= $saleman->email; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('status'); ?></strong></td>
                            <td><?php $status = ["0" => "inactive", "1" => "active"]; echo $status[$saleman->active]; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('group'); ?></strong></td>
                            <td><?= $group->name; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('zone'); ?></strong></td>
                            <td>
                                <?php 
                                if (isset($saleman->multi_zone)) {
                                    $zns = "";
                                    $m_zones = explode(',', $saleman->multi_zone);
                                    foreach ($zones as $zone) {
                                        foreach ($m_zones as $z_id) {
                                            if($z_id == $zone->id){
                                                $z_id == end($m_zones) ? $zns = $zns . $zone->zone_name : $zns = $zns . $zone->zone_name . ", ";
                                            }
                                        }
                                    }
                                    echo $zns;
                                }
                                ?>
                            </strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('award_points'); ?></strong></td>
                            <td><?= $saleman->award_points ?></strong></td>
                        </tr>
                        <td><strong><?= lang('save_point'); ?></strong></td>
                        <td><?php if ($saleman->save_point == '1') { echo 'Yes'; } else { echo 'No'; } ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer no-print">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
            <?php if ($Owner || $Admin || $GP['reports-salemans']) {
                ?>
                <a href="<?=admin_url('reports/saleman_report'); ?>" target="_blank" class="btn btn-primary"><?= lang('salemans_report'); ?></a>
                <?php
            } ?>
            <?php if ($Owner || $Admin || $GP['saleman-edit']) {
                ?>
                <a href="<?=admin_url('salemans/profile/' . $saleman->id); ?>" class="btn btn-primary"><?= lang('edit_saleman'); ?></a>
                <?php
            } ?>
        </div>
    </div>
</div>
