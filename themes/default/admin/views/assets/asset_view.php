<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($Owner || $Admin) {
    ?>
    <ul id="myTab" class="nav nav-tabs">
        <li class=""><a href="#details" class="tab-grey"><?= lang('asset_details') ?></a></li>
        <li class="hide"><a href="#sales" class="tab-grey"><?= lang('depreciation_list') ?></a></li>

        <li class="hide"><a href="#purchases" class="tab-grey"><?= lang('purchases') ?></a></li>
        <li class=""><a href="#depreciation" class="tab-grey"><?= lang('depreciation_list') ?></a></li>
    

    </ul>

<div class="tab-content">
    <div id="details" class="tab-pane fade in">
        <?php
} ?>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-file-text-o nb"></i> <?= $product->name . (SHOP && $product->hide != 1 ? ' (' . lang('shop_views') . ': ' . $product->views . ')' : ''); ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                <li>
                                    <a href="<?= admin_url('products/edit_asset/' . $product->id) ?>">
                                        <i class="fa fa-edit"></i> <?= lang('edit') ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= admin_url('products/print_barcodes/' . $product->id) ?>">
                                        <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= admin_url('products/pdf/' . $product->id) ?>">
                                        <i class="fa fa-download"></i> <?= lang('pdf') ?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= lang('delete_asset') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('products/delete_asset/' . $product->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('asset_details'); ?></p>

                        <div class="row">
                            <div class="col-sm-5">
                                <img src="<?= base_url() ?>assets/uploads/<?= $product->image ?>"
                                     alt="<?= $product->name ?>" class="img-responsive img-thumbnail"/>

                                <div id="multiimages" class="padding10">
                                    <?php if (!empty($images)) {
        echo '<a class="img-thumbnail" data-toggle="lightbox" data-gallery="multiimages" data-parent="#multiimages" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
        foreach ($images as $ph) {
            echo '<div class="gallery-image"><a class="img-thumbnail" data-toggle="lightbox" data-gallery="multiimages" data-parent="#multiimages" href="' . base_url() . 'assets/uploads/' . $ph->photo . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $ph->photo . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
            if ($Owner || $Admin || $GP['products-edit']) {
                echo '<a href="#" class="delimg" data-item-id="' . $ph->id . '"><i class="fa fa-times"></i></a>';
            }
            echo '</div>';
        }
    }
                                    ?>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-striped dfTable table-right-left">
                                        <tbody>
                                        <tr>
                                            <td colspan="2" style="background-color:#FFF;"></td>
                                        </tr>
                                        <tr>
                                            <td style="width:30%;"><?= lang('barcode_qrcode'); ?></td>
                                            <td style="width:70%;">
                                            <img src="<?= admin_url('misc/barcode/' . $product->code . '/' . $product->barcode_symbology . '/74/0'); ?>" alt="<?= $product->code; ?>" class="bcimg" />
                                                <?= $this->bpas->qrcode('link', urlencode(admin_url('products/asset_view/' . $product->id)), 2); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('type'); ?></td>
                                            <td><?php echo lang($product->type); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('name'); ?></td>
                                            <td><?php echo $product->name; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('code'); ?></td>
                                            <td><?php echo $product->code; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('brand'); ?></td>
                                            <td><?= $brand ? $brand->name : ''; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('serial_no'); ?></td>
                                            <td><?= $product->serial_no; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('max_serial'); ?></td>
                                            <td><?= $product->max_serial; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('category'); ?></td>
                                            <td><?php echo $category->name; ?></td>
                                        </tr>
                                        <?php if ($product->subcategory_id) {
                                        ?>
                                            <tr>
                                                <td><?= lang('subcategory'); ?></td>
                                                <td><?php echo $subcategory->name; ?></td>
                                            </tr>
                                        <?php
                                    } ?>
                                        <tr>
                                            <td><?= lang('unit'); ?></td>
                                            <td><?= $unit ? $unit->name . ' (' . $unit->code . ')' : ''; ?></td>
                                        </tr>
                                        <?php if ($Owner || $Admin) {
                                        echo '<tr><td>' . lang('cost') . '</td><td>' . $this->bpas->formatMoney($product->cost) . '</td></tr>';
                                        echo '<tr><td>' . lang('price') . '</td><td>' . $this->bpas->formatMoney($product->price) . '</td></tr>';
                                        if ($product->promotion) {
                                            echo '<tr><td>' . lang('promotion') . '</td><td>' . $this->bpas->formatMoney($product->promo_price) . ' (' . $this->bpas->hrsd($product->start_date) . ' - ' . $this->bpas->hrsd($product->end_date) . ')</td></tr>';
                                        }
                                    } else {
                                        if ($this->session->userdata('show_cost')) {
                                            echo '<tr><td>' . lang('cost') . '</td><td>' . $this->bpas->formatMoney($product->cost) . '</td></tr>';
                                        }
                                        if ($this->session->userdata('show_price')) {
                                            echo '<tr><td>' . lang('price') . '</td><td>' . $this->bpas->formatMoney($product->price) . '</td></tr>';
                                            if ($product->promotion) {
                                                echo '<tr><td>' . lang('promotion') . '</td><td>' . $this->bpas->formatMoney($product->promo_price) . ' (' . $this->bpas->hrsd($product->start_date) . ' - ' . $this->bpas->hrsd($product->start_date) . ')</td></tr>';
                                            }
                                        }
                                    }
                                        ?>

                                        <?php if ($product->tax_rate) {
                                            ?>
                                            <tr>
                                                <td><?= lang('tax_rate'); ?></td>
                                                <td><?php echo $tax_rate->name; ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('tax_method'); ?></td>
                                                <td><?php echo $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?></td>
                                            </tr>
                                        <?php
                                        } ?>
                                        <?php if ($product->alert_quantity != 0) {
                                            ?>
                                            <tr>
                                                <td><?= lang('alert_quantity'); ?></td>
                                                <td><?php echo $this->bpas->formatQuantity($product->alert_quantity); ?></td>
                                            </tr>
                                        <?php
                                        } ?>
                                        <?php if ($variants) {
                                            ?>
                                            <tr>
                                                <td><?= lang('asset_variants'); ?></td>
                                                <td><?php foreach ($variants as $variant) {
                                                echo '<span class="label label-primary">' . $variant->name . '</span> ';
                                            } ?></td>
                                            </tr>
                                        <?php
                                        } ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) {
                                            ?>
                                            <h3 class="bold"><?= lang('custom_fields') ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('custom_field') ?></th>
                                                        <th><?= lang('value') ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    if ($product->cf1) {
                                                        echo '<tr><td>' . lang('pcf1') . '</td><td>' . $product->cf1 . '</td></tr>';
                                                    }
                                            if ($product->cf2) {
                                                echo '<tr><td>' . lang('pcf2') . '</td><td>' . $product->cf2 . '</td></tr>';
                                            }
                                            if ($product->cf3) {
                                                echo '<tr><td>' . lang('pcf3') . '</td><td>' . $product->cf3 . '</td></tr>';
                                            }
                                            if ($product->cf4) {
                                                echo '<tr><td>' . lang('pcf4') . '</td><td>' . $product->cf4 . '</td></tr>';
                                            }
                                            if ($product->cf5) {
                                                echo '<tr><td>' . lang('pcf5') . '</td><td>' . $product->cf5 . '</td></tr>';
                                            }
                                            if ($product->cf6) {
                                                echo '<tr><td>' . lang('pcf6') . '</td><td>' . $product->cf6 . '</td></tr>';
                                            } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php
                                        } ?>

                                        <?php if ((!$Supplier || !$Customer) && !empty($warehouses) && $product->type == 'standard') {
                                            ?>
                                            <h3 class="bold"><?= lang('warehouse_quantity') ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('warehouse_name') ?></th>
                                                        <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($warehouses as $warehouse) {
                                                if ($warehouse->quantity != 0) {
                                                    echo '<tr><td>' . $warehouse->name . ' (' . $warehouse->code . ')</td><td><strong>' . $this->bpas->formatQuantity($warehouse->quantity) . '</strong>' . ($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') . '</td></tr>';
                                                }
                                            } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php
                                        } ?>
                                    </div>
                                    <div class="col-sm-7">
                                        <?php if ($product->type == 'combo') {
                                            ?>
                                            <h3 class="bold"><?= lang('combo_items') ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('asset_name') ?></th>
                                                        <th><?= lang('quantity') ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($combo_items as $combo_item) {
                                                echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->bpas->formatQuantity($combo_item->qty) . '</td></tr>';
                                            } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php
                                        } ?>
                                        <?php if (!empty($options)) {
                                            ?>
                                            <h3 class="bold"><?= lang('asset_variants_quantity'); ?></h3>
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-striped table-condensed dfTable">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('warehouse_name') ?></th>
                                                        <th><?= lang('asset_variant'); ?></th>
                                                        <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                                        <?php if ($Owner || $Admin) {
                                                echo '<th>' . lang('cost') . '</th>';
                                                echo '<th>' . lang('price') . '</th>';
                                            } ?>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    foreach ($options as $option) {
                                                        if ($option->wh_qty != 0) {
                                                            echo '<tr><td>' . $option->wh_name . '</td><td>' . $option->name . '</td><td class="text-center">' . $this->bpas->formatQuantity($option->wh_qty) . '</td>';
                                                            if ($Owner || $Admin && (!$Customer || $this->session->userdata('show_cost'))) {
                                                                echo '<td class="text-right">' . $this->bpas->formatMoney($option->cost) . '</td><td class="text-right">' . $this->bpas->formatMoney($option->price) . '</td>';
                                                            }
                                                            echo '</tr>';
                                                        }
                                                    } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php
                                        } ?>
                                    </div>
                                </div>
                              
                            </div>

                            <div class="col-sm-12">

                                <?= $product->details ? '<div class="panel panel-success"><div class="panel-heading">' . lang('asset_details_for_invoice') . '</div><div class="panel-body">' . $product->details . '</div></div>' : ''; ?>
                                <?= $product->product_details ? '<div class="panel panel-primary"><div class="panel-heading">' . lang('asset_details') . '</div><div class="panel-body">' . $product->product_details . '</div></div>' : ''; ?>

                            </div>
                        </div>

                        <?php if (!$Supplier || !$Customer) {
                                            ?>
                        <div class="buttons">
                            <div class="btn-group btn-group-justified">
                                <div class="btn-group">
                                    <a href="<?= admin_url('products/print_barcodes/' . $product->id) ?>" class="tip btn btn-primary" title="<?= lang('print_barcode_label') ?>">
                                        <i class="fa fa-print"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('print_barcode_label') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="<?= admin_url('products/pdf/' . $product->id) ?>" class="tip btn btn-primary" title="<?= lang('pdf') ?>">
                                        <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="<?= admin_url('assets/edit/' . $product->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit_asset') ?>">
                                        <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a href="#" class="tip btn btn-danger bpo" title="<b><?= lang('delete_asset') ?></b>"
                                        data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('assets/delete_asset/' . $product->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                        data-html="true" data-placement="top">
                                        <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.tip').tooltip();
            });
        </script>
        <?php
        } ?>

        <?php 
        if ($Owner || $Admin) {
        ?>
    </div>

    <div id="depreciation" class="tab-pane fade">
        <script type="text/javascript">
            // $(document).ready(function () {
            //     oTable = $('#TrRData').dataTable({
            //         "aaSorting": [[0, "desc"]],
            //         "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            //         "iDisplayLength": 100,
            //         'bProcessing': true, 'bServerSide': true,
            //         'sAjaxSource': '<?= admin_url('assets/getEvaluationList/?v=1&product=' . $product->id) ?>',
            //         'fnServerData': function (sSource, aoData, fnCallback) {
            //             aoData.push({
            //                 "name": "<?= $this->security->get_csrf_token_name() ?>",
            //                 "value": "<?= $this->security->get_csrf_hash() ?>"
            //             });
            //             $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
            //         },
            //         'fnRowCallback': function (nRow, aData, iDisplayIndex) {
            //             var oSettings = oTable.fnSettings();
            //             nRow.id = aData[4];
            //             nRow.className = "asset_link2";

            //             return nRow;
            //         },
            //         "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
            //             var gtotal = 0;
            //             for (var i = 0; i < aaData.length; i++) {
            //                 gtotal += parseFloat(aaData[aiDisplay[i]][5]);;
            //             }
            //             var nCells = nRow.getElementsByTagName('th');
            //             nCells[3].innerHTML = currencyFormat(formatMoney(gtotal));
            //         },
            //         "aoColumns": [
            //             {"mRender": fsd}, 
            //             {"mRender": currencyFormat}, 
            //            {"mRender": currencyFormat}, 
            //            {"mRender": currencyFormat},
            //            null
            //            ],
            //     });
            // });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-star-o nb"></i><?= $product->name . ' ' . lang('transfers'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown"><a href="#" id="pdf3" class="tip" title="<?= lang('download_pdf') ?>"><i
                                    class="icon fa fa-file-pdf-o"></i></a></li>
                        <li class="dropdown"><a href="#" id="xls3" class="tip" title="<?= lang('download_xls') ?>"><i
                                    class="icon fa fa-file-excel-o"></i></a></li>
                        <li class="dropdown"><a href="#" id="image3" class="tip image"
                                                title="<?= lang('save_image') ?>"><i
                                    class="icon fa fa-file-picture-o"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="TrRData" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('depreciation'); ?></th>
                                    <th><?= lang('accumulated'); ?></th>
                                    <th><?= lang('net_value'); ?></th>
                                    <th><?= lang('status'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php 
                                if(isset($evaluation_list)){
                                foreach($evaluation_list as $value){ ?>
                                <tr>
                                    <td><?= $this->bpas->hrsd($value->evaluation_date); ?></td>
                                    <td><?= $value->current_cost; ?></td>
                                    <td><?= $value->accumulated; ?></td>
                                    <td><?= $value->net_value; ?></td>
                                    <td>
                                        <?php
                                        $detail_link = anchor('admin/account/asset_expense/'.$value->id.'', '<label class="label label-primary">' . lang('add_expense').'</label>', 'class="tip" title="' . lang('show') . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"');

                                        $delete_link ='<div class="text-left">
                              
                                    <a href="#" data-href="'.admin_url('assets/delete_depreciation/'.$value->id.'').'" data-toggle="modal" data-target="#confirm-delete">
                                        <i class="fa fa-trash-o"></i> '.lang("delete").'</a>
                                            <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4><i class="fa fa-trash-o"></i> Cancel Expense</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h2>'.lang('r_u_sure').'</h2>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">NO</button>
                                                            <a class="btn btn-danger btn-ok">
                                                            '.lang('i_m_sure').'</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>';

                                        echo $value->is_expense ? $delete_link:$detail_link;
                                    
                                        ?>
                                        
                                    </td>
                                </tr>
                                <?php }} ?>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
</div>

    <script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
         
            $('#pdf3').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getTransfersReport/pdf/?v=1&product=' . $product->id)?>";
                return false;
            });
            $('#xls3').click(function (event) {
                event.preventDefault();
                window.location.href = "<?=admin_url('reports/getTransfersReport/0/xls/?v=1&product=' . $product->id)?>";
                return false;
            });
         
            $('.image').click(function (event) {
                var box = $(this).closest('.box');
                event.preventDefault();
                html2canvas(box, {
                    onrendered: function (canvas) {
                        openImg(canvas.toDataURL());
                    }
                });
                return false;
            });

            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
                                
                $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
            });
        });
    </script>
<?php
} ?>
