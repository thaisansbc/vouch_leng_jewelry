<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .plus{
        color:white;
        background-color:#0080ff;
        float:right;
        margin:5px 20px;
        padding:6px 18px;
        cursor: pointer;
    }
    .rank3{
        width:250px;
        margin:auto;
        
        /* margin-left:149px; */
    }
    .plus:hover{
        opacity: 0.6;
    }
    .thead tr th p{
        text-align:center;
        margin-top:7px;
    }
  
    .form-control{
        padding:10px 20px;
        border:1px solid green;
        outline:none;
    }
    .comiss{
        padding:7px 20px;
        border:1px solid green;
        text-align:center;
        outline:none;
    }  
    .comiss1{
        padding:7px 20px;
        border:1px solid green;
        outline:none;
    } 
    .checkbox{
        width:21px;
        height:30px;
    }
</style>
<script>
    $(document).ready(function(){
        var s= <?php echo json_encode($table_compare); ?>;
        if( s == false)
        {
            $('#CGData').removeAttr('id');
        }
    });
    $(document).on('keypress','.rank1',function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
    });
    $(document).on('keypress','.rank2',function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
    });
    $(document).on('keypress','.comiss',function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
    });
</script>

<script>
    $(document).ready(function () {
        var ti = 0;
        $(document).on('change', '.price', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        
        $(document).on("click",".plus",function(){
            var tbody='<tr>'
                            +'<td style="min-width:30px; width: 30px; text-align: center; color:red;">'
                            +'<input class="checkbox checkth" type="checkbox" name="check"/>'
                            +'</td>'
                            +'<td style="text-align:center;">'
                            +'<input type="text" placeholder="Start Rank" name="start_rank_1" value="" class="form-control text-center rank1 rank3 amount" style="padding:6px; height:auto;width:250px;">'
                            +'</td >'
                            +'<td style="text-align:center;">'
                            +'<input type="text" placeholder="End Rank" name="end_rank_1" value="" class="form-control text-center rank2 rank3 amount" style="padding:6px; height:auto; width:250px;">'
                            +'</td>'
                            +'<td style="text-align:center">'
                            +'<input type="text" name="commission_1" placeholder="Commission" class="comiss amount" value="" style="padding:7px 20px;border:1px solid green;outline:none;text-align:center;">'
                            +'</td>'
                            +'<td>'
                            +'<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-square"></i></button></div>'
                            +'</td>'
                        +'</tr>';
                $('.tb_sale_rank tbody').append(tbody);
        });

        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var sales_rank_id = row.attr('id');
            if (typeof(sales_rank_id) == "undefined")
            {
                sales_rank_id = '';
            }
            var name = row.find('.name').val();
            var rank1 = row.find('.rank1').val();
            var rank2 = row.find('.rank2').val();
            var commiss = row.find('.comiss').val();
            var checkbox = row.find('.checkbox');
        
            $.ajax({
                type: 'post',
                url: '<?= admin_url('system_settings/insert_sales_rank_commission'); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    name : name , rank1 : rank1 , rank2 : rank2, commiss : commiss,sales_rank_id:sales_rank_id
                },
                success: function (data) {
                    if (data.status != 1)
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    else {
                        $(checkbox).attr('name', 'val[]');
                        $(checkbox).val(data.id);
                        btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-primary').html('<i class="fa fa-check"></i>');
                    }
                },  
                error: function (data) {
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                }
            });
        });
        
        function start_rank_input(x) {
            return "<div  class='text-center'><input type='text' placeholder='Start Rank' name='start_rank' value='"+x+"' class='form-control rank1 amount' style='padding:6px;width:250px; height:auto;text-align:center;'></div>";
        }
        function end_rank_input(x) {
            return "<div class='text-center'><input type='text' placeholder='Start Rank' name='end_rank' value='"+x+"' class='form-control rank2 amount' style='padding:6px; width:250px; height:auto;text-align:center;'></div>";
        }
        function commission_input(x) {
            return '<div class="text-center"><input type="text" style="text-align:center" name="commission" class="comiss amount" value="'+x+'"></div>';
        }

        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getSales_rank_permmission') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className ="product_group_price_id";
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                // {"bSortable": false, "mRender": name_rank_input},
                {"bSortable": false, "mRender": start_rank_input},
                {"bSortable": false, "mRender": end_rank_input},
                {"bSortable": false, "mRender": commission_input}, 
                {"bSortable": false}
            ]
        }).fnSetFilteringDelay();
    });
</script>
<?= admin_form_open('system_settings/sales_rank_action', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building "></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_sale_ranks') ?>
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
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped reports-table tb_sale_rank">
                        <thead class="thead">
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th ><p><?= lang('start_rank'); ?></p></th>
                                <th><p><?= lang('end_rank'); ?></p></th>
                                <th><p><?= lang('commission'); ?></p></th>
                                <th style="width:85px;"><p><?= lang('action'); ?></p></th>
                            </tr>
                        </thead>
                        <tbody class='text'>
                            <!-- <tr>
                                <td style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </td>
                                <td><input style="width:250px;margin:auto;" type='text' name='start_rank' value=''  placeholder="Start Rank" class='form-control text-center rank1 amount'></td>
                                <td><input style="width:250px;margin:auto;" type='text' name='end_rank' value='' placeholder="End Rank" class='form-control text-center rank2 amount'></td>
                                <td style="text-align:center;"><input type="text" name="commission"  placeholder='commission' class="comiss amount" value=""></td>
                                <td><div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-square"></i></button></div></td>
                            </tr> -->

                            <!-- <tr>
                            <td style="min-width:30px; width: 30px; text-align: center;">
                          <input class="checkbox checkth" type="checkbox" name="check"/>
                            </td>
                            <td style="text-align:center;">
                          <input type="text" placeholder="Start Rank" name="start_rank_1" value="" class="form-control text-center rank1 rank3 amount" style="padding:6px; height:auto;width:250px;">
                          </td >
                          <td style="text-align:center;">
                           <input type="text" placeholder="End Rank" name="end_rank_1" value="" class="form-control text-center rank2 rank3 amount" style="padding:6px; height:auto; width:250px;">
                            </td>
                           <td style="text-align:center">
                         <input type="text" name="commission_1" placeholder="Commission" class="comiss amount" value="" style="padding:7px 20px;border:1px solid green;outline:none;text-align:center;">
                           </td>
                           <td>
                            <div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-square"></i></button></div>
                            </td> -->
                        </tr>
                        </tbody>
                        <tfoot class="footer">
                            <tr class="active">
                                <th colspan='4'></th>
                                <th style="width:30px;height:50px"><label class="plus"><i class="fa fa-plus-circle "></i></label></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hiden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {
        $('#delete').click(function (e){
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
        $('#excel').click(function (e){
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
        $(document).on('focusout', '.amount', function(){
            if($(this).val() == '') $(this).val(0);
            $(this).val(parseFloat($(this).val()).toFixed(2));
        });
        $(document).on('focus', '.amount', function(){
            $(this).select();
        });
    });
</script>
