<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
           
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px; " onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            
            <div class="relative">
        <header >
            <div>
                <img class="logo" src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="">
            </div>
            <div >
                <img class="barcodes" src="<?= admin_url('misc/barcode/' . $this->bpas->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>">
            </div>
          </header>
          <div class="title ">
            <span style="width: 30%; height: 2px; background-color: #0855a1 !important;"></span>
            <h2>Credit Note</h2>
            <span style="width: 30%; height: 2px; background-color:  #0855a1 !important;"></span>
          </div>

          <div class="info" style="display: flex; justify-content: space-between;">
               <div style="width: 40%;  padding-left: 10px;">
                    <strong style=" padding: 0 ; margin: 0;"><?= lang('customer_info') ?></strong>
                    <table style="margin-left:0px;   font-family: 'Khmer OS Siemreap', sans-serif;">
                        <tr>
                          <td style="white-space: nowrap;
                          vertical-align: top;
                          ">Name:</td>
                          <td style="
                          word-wrap: break-word; 
                          max-width: 250px;
                          display: inline-block; "><strong><?= $customer->name ?></strong></td>
                        </tr>
                        <tr>
                          <td style="white-space: nowrap;vertical-align: top;">Address:</td>
                         <td style="word-wrap: break-word; max-width: 250px;display: inline-block; ">   <?php
                            if (
                                empty($customer->street_no) && empty($customer->village) &&empty($customer->commune) && empty($customer->district) && empty($customer->city)
                            ) {
                                echo strip_tags($customer->cf6);
                            } else {
                                echo $customer->no . ', ' .
                                    $customer->street_no . ', ' .
                                    $customer->village . ', ' .
                                    $customer->commune . ', ' .
                                    $customer->district . ', ' .
                                    $customer->city;
                            }
                      
                            ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Tel:</td>
                            <td> <?= $customer->phone ?> </td>
                        </tr>
                    </table>
               </div>
               <div style="width: 40%; ">
                  <strong style=" padding: 0 ; margin: 0;"><?= lang('Document-Info') ?>.</strong>
                  <table style="margin-left:0px;   font-family: 'Khmer OS Siemreap', sans-serif;">
                    <tr>
                      <td style="white-space: nowrap;
                      vertical-align: top;
                      "><?= lang('credit_note_no') ?>:</td>
                      <td style="
                      word-wrap: break-word; 
                      max-width: 250px;
                      display: inline-block; "><strong><?= $inv->reference_no ?></strong></td>
                    </tr>
                    <tr>
                      <td style="white-space: nowrap;vertical-align: top;"><?= lang('credit_note_date') ?>:</td>
                     <td style="word-wrap: break-word;max-width: 250px;display: inline-block; "><strong> <?= date("d-M-Y", strtotime($inv->date)) ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td><?= lang('subject')?>:</td>
                        <td><strong><?= $inv->subject ?></strong></td>
                    </tr>
                </table>
           </div>
               </div>

               <table class="tables">
                <thead>
                    <tr>
                        <th><?= lang('no') ?></th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                       <?php $no = 1; $erow = 1; $totalRow = 0; $unit_price = 0 ;
                     
                                    foreach ($rows as $row) { 

                                        // var_dump($row);

                                        $unit_price += $row->unit_price;
                                       

                                         ?>
                    <tr>
                        <td> <?=  $no ?></td>
                        <td> <?php 
                        if ($row->description) {

                            // Decode HTML entities
                            $documents = html_entity_decode($row->description);

                            // Replace <p> tags with <div>
                            $documents = preg_replace('/<p[^>]*>/i', '<div style="margin:0; padding:0;">', $documents);
                            $documents = preg_replace('/<\/p>/i', '</div>', $documents);

                            // Remove unwanted tags but keep necessary ones
                            $allowed_tags = '<div><ul><li><br><strong><em><u><b><i>';

                            $documents = strip_tags($documents, $allowed_tags);

                            // Replace multiple spaces with non-breaking spaces
                            $documents = str_replace('  ', '&nbsp;&nbsp;', $documents);

                            // Display final content
                            echo '<div style="margin:0; padding:0; line-height:normal;">' . trim($documents) . '</div>';
                        }
                        ?>



                        </td>
                        <td><?= $this->bpas->formatMoney($row->unit_price); ?></td>
                    </tr>
               
                    <?php $no++; $erow++; $totalRow++;
                                
                                } ?>
                     
                </tbody>
            </table>
             <div class="position">
                <div class="total-amount-container">
                    <div class="total-amount-text">
                        <span class="highlight">[]</span> Total Amount (USD): 
                        <span class="highlight"><?=  $this->bpas->formatMoney($inv->grand_total) ?></span>
                    </div>
                </div>

                <div class="footer">
                    <!-- <div class="signatures"> -->
                        <div class="signature-box">
                            <span class="signature-line"></span>
                            <p>Prepared by</p>
                        </div>
                        <div class="signature-box">
                            <span class="signature-line"></span>
                            <p>Checked by</p>
                        </div>
                        <div class="signature-box">
                            <span class="signature-line"></span>
                            <p>Approved by</p>
                        </div>
                    <!-- </div> -->
                </div>
                <div class="hide">
                    <fieldset style="  border: 2px solid #0855a1 ; border-radius: 10px; ">
                        <div class="legend"><b><i>Bank Info.  </i></b></div>                               
                       <?php 
                        // if ($inv->bank_info) {
                        //   //  $documents = $this->bpas->decode_html($getBankInfo->description);
                        //     // Output with clean margins
                        //     echo '<div style="margin:0; padding:0; line-height:1.4;">' . $documents . '</div>';
                        // }
                        ?>
                      </fieldset>
                </div>
                <div style="width: 100%; height: 2px; background-color: #0855a1; margin-top: 10px; " ></div>
                 <div style="text-align: center; font-size: 11px;">
                      <p><?= $biller->address;?></p>
                 </div>
            </div>
        </div>
            </div>
        
        </div>
    </div>
</div>

<style>
      @import url('https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap');
        * {
                font-family:Arial, Helvetica, sans-serif;     
        }
         header {
       margin-top:30px ;
      display: flex;
      justify-content: space-between;
    }
    .main {
            display: flex; justify-content: space-between;
    }
    .main1{
         width: 50%;
    }
    .main2{
          width: 48%;
    }

    .top{
         margin-top: 10px;
    }

    .title {
      position: relative;
      display: flex;
      justify-content: space-between;
      justify-items: center;
      /* width: 100%; */
    }

    .title h2 {
      font-family: "Fredoka One", sans-serif;
      font-weight: bold;
      font-size: 25px;
      position: absolute;
      top: -35px;
      left: 43%;
    }

     .legend {
        font-size: 10px;
        width: 60px;
        margin-top: -18px;
        margin-left: 20px;
        background: white;
        padding:0px 2px;
     }
     fieldset {
          font-size: 10px;
            border: 2px solid #0855a1;
    border-radius: 10px; /* Larger border radius */
    padding: 10px;
     }
      
     .tables {
           margin-top: 20px;
            width: 100%;
            font-size: 10px;
            text-align: center;
            color: black !important;
            border-collapse: collapse;
        }
        .tables th, .tables td {
            
            border: 1px solid #ddd;
            padding: 5px;
        }
        .tables th {
            background-color: #2E7D32;
            color: white;
        }
        .bold {
            text-align: left;
            color: black;
            font-weight: bold;
        }
        .tables  tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    
        .merged-header {
            text-align: center;
        }

        .footer {
            margin-top: 5%;
            margin-bottom: 5%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 98%;
            margin-left: auto;
            margin-right: auto;
            /* max-width: 900px; */
            /* margin: 20px auto; */
        }


        .signature-box {
            font-size: 11px;
            width: 30%;
        }

        .signature-line {

            margin-top: 10px;
            display: block;
            width: 100%;
            height: 1px;
            background-color: #1a49c3;
            margin-bottom: 5px;
        }
        .total-amount-container {
            font-size: 10px;
            margin-top: 5%;
            background-color: #176b1b;
            display: flex;
            justify-content: flex-end;
            padding: 5px 0;
            padding-right: 5%;
            border-radius: 5px;
        }

        .total-amount-text {
            color: white;
        }
        .info {
            font-size: 10px;
            margin-top: 20px;
        }

        .barcodes {
            
            width: 120px; height: 35px;  margin-top: 20px;
         }
         .logo {
          
            width: 120px; height: 70px;
         }
        .highlight {
            color: orange;
            padding-left: 50px;
        }
        @media print {
         
         * {
             -webkit-print-color-adjust: exact;
             print-color-adjust: exact;
         }
         header {
         
      margin-top: 0px;
      display: flex;
      justify-content: space-between;
    }

    .title {
      position: relative;
      display: flex;
      justify-content: space-between;
      justify-items: center;
     
    }

    .title h2 {
      font-family: "Fredoka One", sans-serif;
      font-weight: bold;
      font-size: 17px;
      position: absolute;
      top: -30px;
      left: 45%;
    }

     .legend {
        font-size: 11px;
        width: 60px;
        margin-top: -17px;
        margin-left: 20px;
        background: white !important;
        padding:0px 2px;
     }
     fieldset {
        font-size: 11px;
            border: 2px solid #0855a1;
    border-radius: 10px; 
    padding: 10px;
     }
     
        .merged-header {
            text-align: center;
        }

        .footer {
            margin-top: 2%;
            margin-bottom: 2%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 98%;
            margin-left: auto;
            margin-right: auto;
          
        }


        .signature-box {
            font-size: 11px;
            width: 30%;
        }

        .signature-line {

            margin-top: 10px;
            display: block;
            width: 100%;
            height: 1px;
            background-color:  #0855a1 !important;
            margin-bottom: 5px;
        }
        .total-amount-container {
            font-size: 11px;
            margin-top: 0;
            background-color: #176b1b !important;
            display: flex;
            justify-content: flex-end;
            padding: 5px 0;
            padding-right: 5%;
            border-radius: 5px;
        }

        .tables {
           margin-top: 10px;
            width: 100%;
            font-size: 11px;
            text-align: center;
            color: white !important;
            border-collapse: collapse;
        }
        .tables th, .tables td {
            
            border: 1px solid #ddd !important;
            padding: 5px;
        }
        .tables th {
            background-color: #2E7D32 !important;
            color: white !important;
        }
        .bold {
            text-align: left;
            color: black;
            font-weight: bold;
        }
        .tables  tr:nth-child(even) {
            background-color: #f2f2f2 !important;
        }
    

        .total-amount-text {
            color: white !important;
        }
        .info {
            margin-top: 10px;
            font-size: 11px;
        }
        .relative {
        position: relative; width: 100%; height: 95vh; 
       }
       .position {
        width: 100%;
        position: absolute;
        bottom:0px;
        left:0px;
       }
       .barcodes {
            
            width: 120px; height: 35px;  margin-top: 20px;
         }
         .logo {
          
            width: 120px; height: 70px;
         }

        .highlight {
            color: orange !important;
            padding-left: 50px;
        }
        @page {
         
            margin: none;
        }
        }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
    });
</script>