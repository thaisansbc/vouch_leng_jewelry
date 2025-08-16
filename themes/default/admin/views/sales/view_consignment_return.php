<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('consignment').' '.lang('reference').': '.$consignment->reference_no  ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?= $this->lang->line("date"); ?></th>
                        <th><?= $this->lang->line("reference_no"); ?></th>
                        <th><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($returns)) {
                        foreach ($returns as $return) { ?>
                            <tr class="row<?= $return->id ?>">
                                <td><?= $this->bpas->hrld($return->date); ?></td>
                                <td><?= $return->reference_no; ?></td>
                                <td>
                                    <div class="text-center">
                                        <a href="<?= admin_url('products/modal_view_consignment/' . $return->id) ?>"
                                           data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                                            <a href="#" class="po"
                                               title="<b><?= $this->lang->line("delete_consignment_return") ?></b>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $return->id ?>' href='<?= admin_url('products/delete_consignment/' . $return->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                               rel="popover"><i class="fa fa-trash-o"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='3'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $(document).on('click', '.po-delete', function () {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });
    });
</script>
