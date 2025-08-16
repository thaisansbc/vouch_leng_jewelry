<?php
    //$this->bpas->print_arrays($stock_item);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("enter_using_stock") ; ?></title>
    <link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
    <style type="text/css">
        html, body {
            height: 100%;
            background: #FFF;
        }

        body:before, body:after {
            display: none !important;
        }

        .table th {
            text-align: center;
            padding: 5px;
        }

        .table td {
            padding: 4px;
        }
        hr{
            border-color: #333;
            width:100px;
            margin-top: 70px;
        }


    </style>
</head>

<body>
<div class="print_rec" id="wrap" style="width: 90%; margin: 40px auto;">
    <div class="row">
        <div class="col-lg-12">
            <div class="clearfix"></div>
            <div class="row">           
				<div class="col-sm-12 col-xs-12">
					<div class="text-center" style="margin-bottom:20px;">
						<img src="<?= base_url() . 'assets/uploads/logos/'. $biller->logo ?>">
					</div>
				</div>
            </div>
			<div class="col-sm-12 col-xs-12">
					<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                		<i class="fa fa-print"></i> <?= lang('print'); ?>
            		</button>
				</div>
			<!-- <div class="row">
				<div class="col-sm-12 col-xs-12 ">
						<center><img style="width: 165px;margin-top:-18px !important;" src="<?= base_url() ?>assets/uploads/logos/header_logo.png"></center>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-xs-12 company_addr" style="margin-top:20px 	!important">
					<center>
						<p style="font-size: 12px;">លេខអត្តសញ្ញាណកម្ម អតប (VAT No):&nbsp;K344509877645</p>
						<p style="margin-top:-10px !important;font-size: 12px;">អាសយដ្ឋាន ៖ &nbsp;Phnom Penh</p>
						<p style="margin-top:-10px !important;font-size: 12px;">ទូរស័ព្ទលេខ (Tel):&nbsp;012 585 569</p>
						<p style="margin-top:-10px !important;font-size: 12px;">សារអេឡិចត្រូនិច (E-mail):&nbsp;info@sbcsolution.biz</p>
					</center>
				</div>
			</div> -->
			
			<div class="row">
				<div class="col-sm-12 col-xs-12 inv" style="margin-top:-15px !important">
					<center>
						<h1 style="font-family:Time New Roman;font-size:25px;">Print Using Stock</h1>
					</center>
				</div>
			</div>
			
            <div class="row padding10">
                <div class="col-xs-6" style="float: left;font-size:14px;">
                    <b><p style="font-size: 17px;"><?= lang('information');?></p></b>
                    <table>
                        <tr>
                            <td>Project</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td width="250px"><b style="font-size: 12px;"><?=$biller->company; ?></b></td>
                        </tr>
                        <tr>
                            <td width="130px">Authorize Name</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td><b><?=$au_info->username; ?></b></td>
                        </tr>
                        <tr>
                            <td>Employee Name</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td><b><?=$using_stock->first_name ." ".$using_stock->last_name; ?></b></td>
                        </tr>
                        <tr>
                            <td>Project Plan</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td><b><?=$using_stock->title; ?></b></td>
                        </tr>
                    </table>
                 </div>
                <div class="col-xs-1" style="text-align:center;margin-top:-20px">
                </div>
                <div class="col-xs-5"  style="float: right;font-size:14px">
                    <b><p style="font-size: 17px;"><?= lang('reference');?></p></b>
                    <table>
                        <tr>
                            <td width="130px">Reference No</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td><b><?=$using_stock->reference_no; ?></b></td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td><b><?=$this->bpas->hrsd($using_stock->date); ?></b></td>
                        </tr>
                        <tr>
                            <td>Warehouse</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td width="250px" style="font-size: 12px;"><b><?=$using_stock->name; ?></b></td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                            <td><b><?=$using_stock->address; ?></b></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="row padding10" style="display:none">
                <div class="col-xs-6" style="float: left;">
                   
                </div>
                <div class="col-xs-5" style="float: right;">
                  
                </div>
            </div>

            <div class="clearfix"></div>
            <div><br/></div>
            <div class="-table-responsive">
                <table class="table table-bordered table-hover table-striped" style="width: 100%;">
                    <thead  style="font-size: 13px;">
                        <tr>
                            <th><?= lang("no"); ?></th>
                            <th><?= lang("product_code"); ?></th>
                            <th><?= lang("product_name"); ?></th>
                            <th><?= lang("description"); ?></th>                            
                            <th><?= lang("unit"); ?></th>
                            <th><?= lang("quantity"); ?></th>                              
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        <?php
                        $i=1;
                        $total = 0;
                            foreach($stock_item as $si){
                                echo '
                                    <tr>
                                        <td style="text-align:center;">'.$i.'</td>
                                        <td style="text-align:center;">'.$si->code.'</td>
                                        <td>'.$si->product_name.' </td>
                                        <td class='."text-center".'>'.$si->description.'</td>
                                        <td style="text-align:center;">'.$si->unit_name.'</td>
                                        <td style="text-align:center;">' . $this->bpas->formatQuantity($si->qty_by_unit) . '</td>
                                        
                                    </tr>
                                
                                ';
                                $total += $si->qty_by_unit;
                                $i++;

                            }
                            echo'
                                    <tr>
                                        <td colspan='."6".'>
                                            <p><b>Note:</b>'.'&nbsp;&nbsp;'.strip_tags($using_stock->note).'</p>
                                        </td>
                                    <tr>
                                ';
                        ?>
                    </tfoot>
                </table>
            </div>
            <div class="row">
                <div class="col-md-4  pull-left" style="text-align:center">
                    <hr/>
                    <p><b><?= lang("Signature Stock Manager"); ?></b></p>
                </div>
                <div class="col-md-4 " style="text-align:center">
                    <hr/>
                    <p><b><?= lang("Stock"); ?></b></p>
                </div>
				 <div class="col-md-4  pull-left" style="text-align:center">
                    <hr/>
                    <p><b><?= lang("Receiver"); ?></b></p>
                </div>
                
            </div>
        </div>
        <div class="col-md-12">
            
        </div>
    </div>
</div>
<div id="mydiv" style="">
    
<div id="wrap" style="width: 90%; margin: 0 auto;">
    <div class="row">
        <div class="col-lg-12 no-print">
                <button type="button" class="btn btn-primary btn-default  pull-left" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>&nbsp;&nbsp;
            <a href="<?= admin_url('products/using_stock'); ?>"><button class="btn btn-warning " ><i class="fa fa-backward "></i>&nbsp;<?= lang("back"); ?></button></a>
        </div>
    </div>
</div>
</div>
<br/><br/>
<div id="wrap" style="width: 90%; margin:0px auto;">
<div class="col-md-12" style="margin-bottom:20px;">
</div>
</div>
<div></div>
</script>
</body>
</html>