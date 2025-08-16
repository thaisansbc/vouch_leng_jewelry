<style type="text/css">
    table td{
        padding: 8px 10px;
        border: 1px solid #cccccc;
    }
</style>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('bank_reconciliation'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("account/bank_concile_save", $attrib); ?>
        <div class="modal-body">
            <center><div style="font-size: 18px;font-weight: bold;"><?= lang('bank_reconciliation'); ?></div></center>
            <center><div style="font-size: 16px;"><?= $getReconcile->start_date.' To '.$getReconcile->end_date;?></div></center>
         
           
            <strong>Bank Account: <?= $getReconcile->account_code;?></strong>
            
            <table width="100%">
            
                <tr>
                   <td>1. Balance Per Bank Statement:</td>
                   <td><?= $getReconcile->balance_bank;?></td>
                </tr>
                <?php
                $adjust_bank =0;
                foreach ($getReconcileItems as $value) {
                    if($value->bank_type ==0){
                ?>
                  <tr>
                     <td style="padding-left: 30px;"><?= ($value->amount>0 ? 'Add: ' : 'Less: ').$value->amount;?></td>
                     <td><?= $value->amount;?></td>
                  </tr>
                <?php
                    $adjust_bank +=$value->amount;
                  }
                }
                ?>
                
                <tr>
                   <td style="padding-left: 30px;"><strong>Adjusted Bank Statement Balance </strong></td>
                   <td><strong><?= $adjust_bank;?></td>
                </tr>
                <tr>
                   <td>2. Balance per Book</td>
                   <td><?= $getReconcile->balance_book;?></td>
                </tr>
                <?php
                $adjust_book =0;
                foreach ($getReconcileItems as $value1) {
                    if($value1->bank_type ==1){
                ?>
                  <tr>
                     <td style="padding-left: 30px;"><?= ($value1->amount>0 ? 'Add: ' : 'Less: ').$value1->amount;?></td>
                     <td><?= $value1->amount;?></td>
                  </tr>
                <?php
                    $adjust_book +=$value1->amount;
                  }
                }
                ?>
                <tr>
                   <td style="padding-left: 30px;"><strong>Adjusted Bank Statement Balance </strong></td>
                   <td><strong><?= $adjust_book;?></strong></td>
                </tr>
            </table>
       
        </div>
        <div id="footer" class="row">
          <div class="col-sm-6 col-xs-6">
            <center>
              <hr style="border:dotted 1px; width:50%; vertical-align:bottom !important; " />
              <p style="margin-top: -20px !important"><b style="font-size:14px;"><?= lang('​prepared_by'); ?></b></p>
            </center>
          </div>
          <div class="col-sm-6 col-xs-6" style="float: right;">
            <center>
              <hr style="border:dotted 1px; width:50%; vertical-align:bottom !important; " />
              <p style="margin-top: -20px !important"><b style="font-size:14px;"><?= lang('​approved_by'); ?></b></p>
            </center>
          </div>
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
