<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('consumers') . ' (' . $company->name . ')'; ?></h4>
        </div>

        <div class="modal-body">
            <!--<p><?= lang('list_results'); ?></p>-->

            <div class="table-responsive">
                <table id="CSUData" cellpadding="0" cellspacing="0" border="0"
                       class="table table-condensed table-hover table-striped">
                    <thead>
                    <tr class="primary">
                        <!--<th style="width:55px;"><?= lang('id'); ?></th>-->
                        <th><?= lang('first_name'); ?></th>
                        <th><?= lang('last_name'); ?></th>
                        <th><?= lang('phone'); ?></th>
                        <th><?= lang('create_date'); ?></th>
                        <th><?= lang('update_date'); ?></th>
                        <th style="width:85px;"><?= lang('actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($consumers)) {
                    foreach ($consumers as $consumer) {
                        echo '<tr>' .
                                '<td>' . $consumer->first_name . '</td>' .
                                '<td>' . $consumer->last_name . '</td>' .
                                '<td>' . $consumer->phone . '</td>' .
                                 '<td>' . $consumer->create_date . '</td>' .
                                '<td>' . $consumer->update_date . '</td>' .
                                '<td class="text-center"><a href="' . admin_url('customers/edit_consumer/' . $consumer->id) . '" class="tip" title="' . lang('edit_consumer') . '"><i class="fa fa-edit"></i></a> <a href="#" class="tip po" title="' . $this->lang->line('delete_consumer') . '" data-content="<p>' . lang('r_u_sure') . '</p><a class=\'btn btn-danger\' href=\'' . admin_url('customers/delete_consumer/' . $consumer->id) . '\'>' . lang('i_m_sure') . '</a> <button class=\'btn po-close\'>' . lang('no') . '</button>"  rel="popover"><i class="fa fa-trash-o"></i></a></td>' .
                                '</tr>';
                            }
                        } else {
                            ?>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('sEmptyTable') ?></td>
                        </tr>
                    <?php
                    } ?>
                    </tbody>
                </table>
            </div>


        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
        </div>
    </div>
    <?= $modal_js ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.tip').tooltip();
        });
    </script>

