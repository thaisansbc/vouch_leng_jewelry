<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <!-- window.print(); -->
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="JavaScript:printDiv(); return false;">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_stock_details') . ' (' . lang('purcahse') . ' ' . lang('reference') . ': ' . $inv->reference_no . ')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width:15%;"><?= $this->lang->line('date'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('reference_no'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('total_quantity'); ?></th>
                            <th style="width:20%;"><?= $this->lang->line('note'); ?></th>
                            <th style="width:15%;"><?= $this->lang->line('stock_in_by'); ?></th>
                            <th style="width:10%; text-align: center !important;" class="no-print"><?= $this->lang->line('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($stock_ins)) {
                        $count = 0;
                        foreach ($stock_ins as $stock_in) {
                            $count    += ($stock_in->total_quantity ? $stock_in->total_quantity : 0);
                    ?>
                            <tr class="row<?= $stock_in->id ?>">
                                <td><?= $this->bpas->hrld($stock_in->date); ?></td>
                                <td><?= $stock_in->reference_no; ?></td>
                                <td><?= $this->bpas->formatQuantity($stock_in->total_quantity); ?></td>
                                <td><?= strip_tags(html_entity_decode($stock_in->note)) ?></td>
                                <td><?= $stock_in->created_by; ?></td>
                                <td class="no-print">
                                    <div class="text-center">
                                        <a href="<?= admin_url('purchases/view_stock_received/' . $stock_in->id) ?>" title="<?= $this->lang->line('view_stock_received') ?>"  data-toggle="modal" data-backdrop="static" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                                        <a href="<?= admin_url('purchases/edit_stock_received/' . $stock_in->id) ?>" title="<?= $this->lang->line('edit_stock_received') ?>" ><i class="fa fa-edit"></i></a>
                                        <a href="#" class="po" title="<?= $this->lang->line('delete_stock_received') ?>"
                                           data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $stock_in->id ?>' href='<?= admin_url('purchases/delete_stock_received/' . $stock_in->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                           rel="popover"><i class="fa fa-trash-o"></i>
                                       </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='6'>" . lang('no_data_available') . '</td></tr>';
                    } ?>
                    </tbody>
                    <?php if (!empty($stock_ins)) { ?>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="font-weight: bold;"><?= lang('total') ?></td>
                            <td colspan="4" style="font-weight: bold;"><?= $this->bpas->formatQuantity($count); ?></td>
                        </tr>
                    </tfoot>
                    <?php } ?>
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
        $(document).on('click', '.email_payment', function (e) {
            e.preventDefault();
            var link = $(this).attr('href');
            $.get(link, function(data) {
                bootbox.alert(data.msg);
            });
            return false;
        });
    });
</script>
<script type="text/javascript">
    function printDiv(divName) {
        var head = document.getElementsByTagName('head')[0];
        var newStyle = document.createElement('style');
        newStyle.setAttribute('type', 'text/css');
        newStyle.setAttribute('media', 'print');
        newStyle.appendChild(document.createTextNode(`
                                @media print {
                                    @page {
                                        page: A4;
                                        margin: 0;
                                    }
                                    .no-print{ display:none !important; } 
                                    .modal-body {
                                        padding-right: 35px;
                                    }
                                    #CompTable > thead > tr > th.no-print, .table td:nth-child(6) {
                                        display:none !important;
                                    }
                                }
                            `));

        head.appendChild(newStyle);
        window.print();
        return true;
    }
</script>