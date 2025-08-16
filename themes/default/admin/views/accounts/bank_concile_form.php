<style type="text/css">
    table td{
        padding: 8px 10px;
        border: 1px solid #cccccc;
    }
</style>
<?php
    $start_date=str_replace('-','/',$start_date);
    $end_date=str_replace('-','/',$end_date);
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('bank_reconciliation'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("account/bank_concile_save", $attrib); ?>
        <div class="modal-body">
            <center><div style="font-size: 18px;font-weight: bold;"><?= lang('bank_reconciliation'); ?></div></center>
            <center><div style="font-size: 16px;"><?= $start_date.' To '.$end_date;?></div></center>
            <?php
          //  var_dump($getTrans);
            //var_dump($getTrans_book);
            ?>
            <div class="row">
                <div class="col-ms-4"><input type="hidden" name="account_code" value="<?= $account_code;?>"></div>
                <div class="col-ms-4"><input type="hidden" name="start_date" value="<?= $start_date;?>"></div>
                <div class="col-ms-4"><input type="hidden" name="end_date" value="<?= $end_date;?>"></div>
            </div>

            
            <div class="row">
              
              <div class="col-sm-6">
                  <?php if ($Owner || $Admin) { ?>
                <div class="form-group">
                    <?= lang("biller", "biller"); ?>
                    <?php
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                            ?>
                        </div>
                    <?php } else {
                        $biller_input = array(
                            'type' => 'hidden',
                            'name' => 'biller',
                            'id' => 'posbiller',
                            'value' => $this->session->userdata('biller_id'),
                        );

                        echo form_input($biller_input);
                    }
                    ?>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <br>
                  <?= lang("bank_account"); ?>
                  <strong>: <?= $account_code;?></strong>
                </div>
            </div>
            

            <table width="100%">
            
                <tr>
                   <td>1. Balance Per Bank Statement:</td>
                   <td><input type="text" name="ending_bank" id="ending_bank" value="0"></td>
                </tr>
                <?php
                foreach ($getTrans as $value) {
                ?>
                  <tr>
                     <td style="padding-left: 30px;"><?= ($value->amount>0 ? 'Add: ' : 'Less: ').$value->narrative;?></td>
                     <input type="hidden" name="tran_id[]" value="<?= $value->tran_id;?>">
                     <input type="hidden" name="amount[]" value="<?= $value->amount;?>">
                     <input type="hidden" name="description[]" value="<?= $value->narrative;?>">
                     <td><input type="text" name="adjust_bank[]" class="adjust_bank" value="<?= $value->amount;?>" readonly="readonly"></td>
                  </tr>
                <?php
                }
                ?>
                
                <tr>
                   <td style="padding-left: 30px;"><strong>Adjusted Bank Statement Balance </strong></td>
                   <td><strong><input type="text" name="total_balance_bank" id="total_balance_bank" value="" disabled="disabled"></strong></td>
                </tr>
                <tr>
                   <td>2. Balance per Book</td>
                   <td><input type="text" name="ending_book" id="ending_book" value="0"></td>
                </tr>
                <?php
                foreach ($getTrans_book as $value1) {
                ?>
                  <tr>
                     <td style="padding-left: 30px;"><?= ($value1->amount>0 ? 'Add: ' : 'Less: ').$value1->narrative;?></td>
                     <input type="hidden" name="tran_id1[]" value="<?= $value1->tran_id;?>">
                     <input type="hidden" name="amount1[]" value="<?= $value1->amount;?>">
                     <input type="hidden" name="description1[]" value="<?= $value1->narrative;?>">
                     <td><input type="text" name="adjust_book[]" class="adjust_book" value="<?= $value1->amount;?>" readonly="readonly"></td>
                  </tr>
                <?php
                }
                ?>
                <tr>
                   <td style="padding-left: 30px;"><strong>Adjusted Bank Statement Balance </strong></td>
                   <td><strong><input type="text" name="total_balance_book" id="total_balance_book" value="" disabled="disabled"></strong></td>
                </tr>
            </table>
       
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment', lang('reconcile'), 'class="btn btn-primary" id="add_submit"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
</script>
<script>
    $(document).ready(function(){
        
        $(document).on('change', '#ending_bank', function () {
          var total_adjust_bank=0;
          var ending_bank = parseFloat($(this).val());
          $(".adjust_bank").each(function(){
            total_adjust_bank += parseFloat($(this).val()); 
          });

          $("#total_balance_bank").val(parseFloat(total_adjust_bank + ending_bank).toFixed(2));
        });
        
        $(document).on('change', '#ending_book', function () {
          var total_adjust_book=0;
          var ending_book = parseFloat($(this).val());
          $(".adjust_book").each(function(){
            total_adjust_book += parseFloat($(this).val()); 
          });

          $("#total_balance_book").val(parseFloat(total_adjust_book + ending_book).toFixed(2));
        });
    });
</script>

<?= $modal_js ?>
