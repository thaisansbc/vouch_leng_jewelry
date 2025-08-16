<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if($this->Settings->auto_count){?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('stock_count'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('products/add_adjustment/' . $stock_count->id, $attrib); ?>
        <div class="modal-body">

            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered table-condensed">
                        <tbody>
                            <tr>
                                <td><?= lang('warehouse'); ?></td>
                                <td><?= $warehouse->name . ' ( ' . $warehouse->code . ' )'; ?></td>
                                <input type="hidden" name="warehouse" value="<?= $stock_count->warehouse_id; ?>">
                            </tr>
                            <tr>
                                <td><?= lang('start_date'); ?></td>
                                <td><?= $this->bpas->hrld($stock_count->date); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('end_date'); ?></td>
                                <td><?= $this->bpas->hrld($stock_count->updated_at); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('reference'); ?></td>
                                <td><?= $stock_count->reference_no; ?></td>
                                <input type="hidden" name="reference_no" value="<?= $stock_count->reference_no; ?>">
                            </tr>
                            <tr>
                                <td><?= lang('type'); ?></td>
                                <td><?= lang($stock_count->type); ?></td>
                            </tr>
                            <?php if ($stock_count->type == 'partial') {
                            ?>
                                <tr>
                                    <td><?= lang('categories'); ?></td>
                                    <td><?= $stock_count->category_names; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('brands'); ?></td>
                                    <td><?= $stock_count->brand_names; ?></td>
                                </tr>
                            <?php
                            } ?>
                            <tr>
                                <td><?= lang('files'); ?></td>
                                <td>
                                    <?= anchor('admin/welcome/download/' . $stock_count->initial_file, '<i class="fa fa-download"></i> ' . lang('initial_file'), 'class="btn btn-primary btn-xs"'); ?>
                                    <?= anchor('admin/welcome/download/' . $stock_count->initial_file, '<i class="fa fa-download"></i> ' . lang('final_file'), 'class="btn btn-primary btn-xs"'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <script>
                        $(document).ready(function() {
                            oTable = $('#TOData2').dataTable({
                                "aaSorting": [
                                    [1, "desc"],
                                    [2, "desc"]
                                ],
                                "aLengthMenu": [
                                    [10, 25, 50, 100, -1],
                                    [10, 25, 50, 100, "<?= lang('all') ?>"]
                                ],
                                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                'bProcessing': true,
                                'bServerSide': true,
                                'sAjaxSource': '<?= admin_url('products/getStockCount' . ($id ? '/' . $id : '')) ?>',
                                'fnServerData': function(sSource, aoData, fnCallback) {
                                    aoData.push({
                                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                                        "value": "<?= $this->security->get_csrf_hash() ?>"
                                    });
                                    $.ajax({
                                        'dataType': 'json',
                                        'type': 'POST',
                                        'url': sSource,
                                        'data': aoData,
                                        'success': fnCallback
                                    });
                                },
                                "aoColumns": [{
                                    "sWidth": "1px",
                                    "bSortable": false,
                                    "mRender": checkbox
                                }, null, {
                                    "mRender": currencyFormat
                                }, {
                                    "mRender": currencyFormat
                                }, {
                                    "mRender": currencyFormat
                                }, {
                                    "mRender": currencyFormat
                                }],
                                'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                                    var oSettings = oTable.fnSettings();
                                    nRow.id = aData[0];
                                    nRow.className = "count_stock_link";
                                    return nRow;
                                },
                                "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                                    var expected = 0,
                                        counted = 0,
                                        difference = 0,
                                        cost = 0; 
                                    for (var i = 0; i < aaData.length; i++) {
                                        expected += parseFloat(aaData[aiDisplay[i]][2]);
                                        counted += parseFloat(aaData[aiDisplay[i]][3]);
                                        difference += parseFloat(aaData[aiDisplay[i]][4]);
                                        cost += parseFloat(aaData[aiDisplay[i]][5]);
                                    }
                                    var nCells = nRow.getElementsByTagName('th');
                                    nCells[2].innerHTML = currencyFormat(formatMoney(expected));
                                    nCells[3].innerHTML = currencyFormat(formatMoney(counted));
                                    nCells[4].innerHTML = currencyFormat(formatMoney(difference));
                                    nCells[5].innerHTML = currencyFormat(formatMoney(cost));
                                }
                            })
                        });
                    </script>
                    <?php if (!empty($stock_count_items)) {
                    ?>
                        <div class="table-responsive hide">
                            <table id="TOData2" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-hover table-striped">
                                <thead>
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="val" />
                                        </th>
                                        <th><?= lang('description'); ?></th>
                                        <th><?= lang('expected'); ?></th>
                                        <th><?= lang('counted'); ?></th>
                                        <th><?= lang('difference'); ?></th>
                                        <th><?= lang('cost'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check" />
                                        </th>
                                        <th><?= lang('total'); ?></th>
                                        <th></th>
                                        <th></th>
                                        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                        <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped order-table">
                                <thead>
                                    <tr>
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkall checkft checkbox" id="myCheck" type="checkbox" name="check" />
                                        </th>
                                        <th style="text-align:center; vertical-align:middle;"><?= lang('no'); ?></th>
                                        <th style="vertical-align:middle;"><?= lang('description'); ?></th>
                                        <th style="text-align:center; vertical-align:middle;"><?= lang('expected'); ?></th>
                                        <th style="text-align:center; vertical-align:middle;"><?= lang('counted'); ?></th>
                                        <th style="text-align:center; vertical-align:middle;"><?= lang('difference'); ?></th>
                                        <th style="text-align:center; vertical-align:middle;"><?= lang('cost'); ?></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $r = 1;
                                    $total                       = 0;
                                    $items                       = 0;
                                    foreach ($stock_count_items as $row) :
                                        if ($row->expected != $row->counted) {
                                    ?>
                                            <tr>
                                                <input type="hidden" name="product_id[]" value="<?= $row->product_id; ?>">
                                                <input type="hidden" name="expected[]" value="<?= $row->expected; ?>">
                                                <input type="hidden" name="counted[]" value="<?= $row->counted; ?>">
                                                <td style="text-align:center; width:25px;">
                                                    <input type="checkbox" name="val[]" class="check checkbox multi-select" value="<?= $row->product_id; ?>">
                                                </td>
                                                <td style="text-align:center; width:25px;"><?= $r; ?></td>
                                                <td style="text-align:left;">
                                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->product_variant ? ' (' . $row->product_variant . ')' : ''); ?>
                                                </td>
                                                <td style="text-align:center; width:80px;">
                                                    <?= $this->bpas->formatQuantity($row->expected); ?>
                                                </td>
                                                <td style="text-align:center; width:80px;">
                                                    <?= $this->bpas->formatQuantity($row->counted); ?>
                                                </td>
                                                <td style="text-align:right; width:80px;">
                                                    <?= $this->bpas->formatQuantity($row->counted - $row->expected); ?>
                                                </td>
                                                <td style="text-align:right; width:100px;">
                                                    <?= $this->bpas->formatMoney($row->cost * ($row->counted - $row->expected)); ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                    <?php $r++;
                                        $items += $row->counted - $row->expected;
                                        $total += $row->cost * ($row->counted - $row->expected);
                                    endforeach;
                                    $totalrow = $r - 1;
                                    // echo $totalrow;
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"><?= lang('total'); ?></th>
                                        <th style="text-align:right; width:80px;">
                                            <?= $this->bpas->formatQuantity($items); ?>
                                        </th>
                                        <th style="text-align:right; width:100px;">
                                            <?= $this->bpas->formatMoney($total); ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php
                    } else {
                        echo '<div class="well well-sm">' . lang('no_mismatch_found') . '</div>';
                    }
                    ?>
                    <?php
                    /* if ($adjustment) {
                        echo '<a href="' . admin_url('products/view_adjustment/' . $adjustment->id) . '" class="btn btn-primary btn-block no-print" data-toggle="modal" data-backdrop="static" data-target="#myModal2">' . lang('view_adjustment') . '</a>';
                    } elseif (!empty($stock_count_items)) {
                        echo '<a href="' . admin_url('products/add_adjustment/' . $stock_count->id) . '" class="btn btn-primary btn-block no-print">' . lang('add_adjustment') . '</a>';
                    }*/
                    echo form_submit('submit_report', lang('count_all'), 'id="submit_report" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');

                    ?>

                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="confirm" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Modal Header</h4>
                        </div>
                        <div class="modal-body">
                            <p>Some text in the modal.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <script type="text/javascript">
            var checkBox = document.getElementById("myCheck");
            $("#myCheck").click(function() {

                var checks = 5;
                for (var i = 0; i < checks; i++) {
                    $('.check').each(function() {
                        $('.check').checked = true;
                    });

                    //checks[i].checked = true;
                }

            });


            // var checks = document.querySelectorAll(".check");
            // var max = 2;
            // for (var i = 0; i < checks.length; i++)
            //     checks[i].onclick = selectiveCheck;
            //     function selectiveCheck (event) {
            //       var checkedChecks = document.querySelectorAll(".check:checked");
            //       if (checkedChecks.length >= max + 1)
            //         return false;
            // }
            $(document).ready(function() {
                $("#btn_addjust").click(function() {
                    var check = confirm("Are you sure you want to leave?");
                    if (check == true) {
                        return true;
                    } else {
                        return false;
                    }

                });
            });
        </script>
        <?php echo form_close(); ?>
    </div>
</div>
    <?php }else{?>
        <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('stock_count'); ?></h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered table-condensed">
                        <tbody>
                            <tr>
                                <td><?= lang('warehouse'); ?></td>
                                <td><?= $warehouse->name . ' ( ' . $warehouse->code . ' )'; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('start_date'); ?></td>
                                <td><?= $this->bpas->hrld($stock_count->date); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('end_date'); ?></td>
                                <td><?= $this->bpas->hrld($stock_count->updated_at); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('reference'); ?></td>
                                <td><?= $stock_count->reference_no; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('type'); ?></td>
                                <td><?= lang($stock_count->type); ?></td>
                            </tr>
                            <?php if ($stock_count->type == 'partial') {
                            ?>
                                <tr>
                                    <td><?= lang('categories'); ?></td>
                                    <td><?= $stock_count->category_names; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('brands'); ?></td>
                                    <td><?= $stock_count->brand_names; ?></td>
                                </tr>
                            <?php
                            } ?>
                            <tr>
                                <td><?= lang('files'); ?></td>
                                <td>
                                    <?= anchor('admin/welcome/download/' . $stock_count->initial_file, '<i class="fa fa-download"></i> ' . lang('initial_file'), 'class="btn btn-primary btn-xs"'); ?>
                                    <?= anchor('admin/welcome/download/' . $stock_count->initial_file, '<i class="fa fa-download"></i> ' . lang('final_file'), 'class="btn btn-primary btn-xs"'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <script>
                        $(document).ready(function() {
                       
                            oTable = $('#TOData2').dataTable({
                                "aaSorting": [
                                    [1, "desc"],
                                    [2, "desc"]
                                ],
                                "aLengthMenu": [
                                    [10, 25, 50, 100, -1],
                                    [10, 25, 50, 100, "<?= lang('all') ?>"]
                                ],
                                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                'bProcessing': true,
                                'bServerSide': true,
                                // 'sAjaxSource': '<?= admin_url('products/getStockCount') ?>',
                                'sAjaxSource': '<?= admin_url('products/getStockCount' . ($id ? '/' . $id : '')) ?>',

                                'fnServerData': function(sSource, aoData, fnCallback) {
                                    aoData.push({
                                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                                        "value": "<?= $this->security->get_csrf_hash() ?>"
                                    });
                                    $.ajax({
                                        'dataType': 'json',
                                        'type': 'POST',
                                        'url': sSource,
                                        'data': aoData,
                                        'success': fnCallback
                                    });
                                },
                                "aoColumns": [{
                                    "sWidth": "1px",
                                    "bSortable": false,
                                    "mRender": checkbox
                                }, null,null,{
                                    "mRender": currencyFormat
                                }, {
                                    "mRender": currencyFormat
                                }, {
                                    "mRender": currencyFormat
                                }],
                                'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                                    var oSettings = oTable.fnSettings();
                                    nRow.id = aData[0];
                                    nRow.className = "transfer_link";
                                    return nRow;
                                },
                                "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                                    var expected = 0,
                                        counted = 0,
                                        difference = 0,
                                        cost = 0;

                                    for (var i = 0; i < aaData.length; i++) {
                                        expected += parseFloat(aaData[aiDisplay[i]][3]);
                                        counted += parseFloat(aaData[aiDisplay[i]][4]);
                                        difference += parseFloat(aaData[aiDisplay[i]][5]);
                                      
                                    }
                                    var nCells = nRow.getElementsByTagName('th');
                                    nCells[3].innerHTML = currencyFormat(formatMoney(expected));
                                    nCells[4].innerHTML = currencyFormat(formatMoney(counted));
                                    nCells[5].innerHTML = currencyFormat(formatMoney(difference));
                                    
                                }
                            })

                        });
                    </script>

                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php
                                echo admin_form_open_multipart('products/add_adjustment/' . $stock_count->id, 'id="action-form"');
                                ?>
                                <?php if (!empty($stock_count_items)) {
                                ?>
                                    <div class="table-responsive">
                                        <table id="TOData2" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-hover table-striped">
                                            <thead>
                                                <tr class="active">
                                                    <th style="min-width:30px; width: 30px; text-align: center;">
                                                        <input class="checkbox checkft" type="checkbox" name="val" />
                                                    </th>
                                                    <th><?= lang('description'); ?></th>
                                                    <th><?= lang('expiry'); ?></th>
                                                    <th><?= lang('expected'); ?></th>
                                                    <th><?= lang('counted'); ?></th>
                                                    <th><?= lang('difference'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="dtFilter">
                                                <tr class="active">
                                                    <th style="min-width:30px; width: 30px; text-align: center;">
                                                        <input class="checkbox checkft" type="checkbox" name="check" />
                                                    </th>
                                                    <th><?= lang('total'); ?></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                               
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                <?php
                                } else {
                                    echo '<div class="well well-sm">' . lang('no_mismatch_found') . '</div>';
                                }
                                ?>
                                <?php
                                ?>
                                <div>
                                    <?php

                                    // echo '<button class="btn btn-primary btn-block no-print" type>' . lang('add_adjustment') . '</button>';
                                    ?>
                                    <div class="form-group">
                                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line('add_adjustment'), 'class="btn btn-primary"'); ?> </div>
                                    </div>
                                </div>
                                <?= form_close() ?>
                                <?php
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // if ($adjustment) {
            //     echo '<a href="' . admin_url('products/view_adjustment/' . $adjustment->id) . '" class="btn btn-primary btn-block no-print" data-toggle="modal" data-backdrop="static" data-target="#myModal2">' . lang('view_adjustment') . '</a>';
            // } 
            // elseif (!empty($stock_count_items)) {
            //     echo '<a href="' . admin_url('products/add_adjustment/' . $stock_count->id) . '" class="btn btn-primary btn-block no-print">' . lang('add_adjustment') . '</a>';
            // }
            ?>
        </div>
    </div>
</div>
</div>
</div>
<?php } ?>