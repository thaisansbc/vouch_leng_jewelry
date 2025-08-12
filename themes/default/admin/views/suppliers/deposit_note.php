<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
    }
</style>\<style>
    .border_px {
        border: 2px solid black !important;
    }

    .border_tage {
        padding: 10px;
        border: 3px solid black;
        border-collapse: collapse;
    }

    .data_afd {
        color: #ce6301;
        border-color: inherit;
        text-align: center;
        vertical-align: top
    }

    .data_afd_t {
        border-color: inherit;
        text-align: center;
        vertical-align: top
    }

    .data_afd_n {
        color: #ce6301;
        border-color: inherit;
        text-align: right;
        vertical-align: top
    }

    .data_afd_l {
        /* color: #ce6301; */
        border-color: inherit;
        text-align: right;
        vertical-align: top
    }
</style>
<div class="modal-dialog no-modal-header" style='width:70%'>
    <div class="modal-content">
        <div class="modal-body print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <br>
            <div class="border_tage" style="margin:10px 0px;">
                <table style="width:100%; background-color:white;">
                    <tbody>
                        <tr>
                            <td rowspan="2" style="width:30%;">
                                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
                                <div class="clearfix"></div>
                            </td>
                            <td class="data_afd_t" style="width:40%;">
                                <h1>សក្ខីប័ត្របុរេប្រទាន</h1>
                                <h2>ADVANCE VOUCHER</h2>
                            </td>
                            <td style="width:30%;">លេខសក្ខីប័ត្រ RV No :
                                <span style="color:red;"><u><?php echo $deposit->reference; ?></u></span><br>
                                កាលបរិច្ឆេទ<?= lang('date'); ?>: <span style="color:red;"><u><?= $this->bpas->hrsd($deposit->date); ?></u></span><br>
                            </td>
                        </tr>
                        <tr>
                            <th class="data_afd_t">
                            </th>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">សំណើរដោយ Requested by : <span style="color:red;"><?= $customer->company ? $customer->company : $customer->name; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                អាសយដ្ឋាន <?= lang('address'); ?>: <span style="color:red;">
                                    <?= $customer->address . ", " . $customer->state . " " . $customer->country; ?></span>
                            </td>
                            <td colspan="2">មុខដំណែង Positon: <span style="color:red;"><u></u></span></td>
                        </tr>
                    </tbody>
                </table>
                <div>
                    <br>
                    <table class="table border_px" style="margin-bottom:0;width:100%">
                        <tbody class="border_px">
                            <tr class="border_px">
                                <td rowspan="2" class="border_px">ទូរទាត់ដោយ<br>
                                    Mode of Payment</td>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />Cash <input type="checkbox" value="Bank" style="margin:10px;" />Bank </td>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />KHR<input type="checkbox" value="Bank" style="margin:10px;" />USD</td>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />T.T<input type="checkbox" value="Bank" style="margin:10px;" />Cheque No:</td>
                            </tr>
                            <tr class="border_px">
                                <td colspan="3">Cash / Bank Name:</td>
                                <td colspan="3"><input type="checkbox" value="Bank" style="margin:10px;" />Bank Account No:</td>
                            </tr>
                            <tr class="border_px">
                                <td>លេខយោង Ref.No. : </td>
                                <td colspan="6"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />សំណើរលទ្ធកម្ម Purchase Requisition</td>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />ប័ណ្ណបញ្ជា​ទិញ Purchase Order</td>
                                <td colspan="2"><input type="checkbox" value="Cash" style="margin:10px;" />គម្រោងថវិកា Budget Plan</td>
                                <td colspan="1"><input type="checkbox" value="Cash" style="margin:10px;" />ផ្សេងៗ Other</td>
                            </tr>
                            <tr>
                                <td rowspan="2" class="data_afd_t border_px">លេខយោង<br>Ref.Number</td>
                                <td colspan="4" class="data_afd_t border_px" rowspan="2">បរិយា​ Description</td>
                                <td colspan="2" class="data_afd_t border_px">ចំនួនជាតួលេខ​​​ Amount in Figure</td>
                            </tr>
                            <tr class="border_px">
                                <td class="data_afd_t border_px">ប្រាក់រៀល​ KHR</td>
                                <td class="data_afd_t border_px">ប្រាក់ដុល្លា​ USD</td>
                            </tr>
                            <tr class="border_px">
                                <td rowspan="3" class=" border_px"><br><br><br><br></td>
                                <td colspan="4" rowspan="3" class="border_px"><br></td>
                                <td rowspan="1" class="data_afd_n border_px"><br><br><br></td>
                                <td rowspan="1" class="data_afd_n border_px"><br><br><br></td>
                            </tr>
                            <tr>
                            </tr>
                            <tr>
                                <td class="data_afd_n border_px"></td>
                                <td class="data_afd_n border_px"><?php echo $this->bpas->formatMoney($deposit->amount); ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" rowspan="1">ចំនួនជាអក្ស KHR : </td>
                                <td colspan="2" style="border-color: inherit;text-align: center; vertical-align: top" class=" border_px">អត្រា Ex.Rate</td>
                            </tr>
                            <tr>
                                <td colspan="5" rowspan="1">In words USD : </td>
                                <td colspan="2" class="border_px"><span style="border-color: inherit;text-align: left;vertical-align: top"> KHR:</span> <span style="border-color: inherit; text-align:right;vertical-align: top"></span></td>
                            </tr>
                            <tr class="border_px">
                                <td class="border_px" rowspan="2"><br><br></td>
                                <td class="border_px" rowspan="2"><br><br></td>
                                <td class="border_px" rowspan="2"><br><br></td>
                                <td class="border_px" rowspan="2"><br><br></td>
                                <td class="border_px" rowspan="2"><br><br></td>
                                <td class="border_px" rowspan="2"><br><br></td>
                                <td class="border_px" rowspan="2"><br><br></td>
                            </tr>
                            <tr class="border_px">
                            </tr>
                            <tr>
                                <td class="border_px"><br></td>
                                <td class="border_px"><br></td>
                                <td class="border_px"><br></td>
                                <td class="border_px"><br></td>
                                <td class="border_px"><br></td>
                                <td class="border_px"><br></td>
                                <td class="border_px"><br></td>
                            </tr>
                            <tr>
                            </tr>
                            <tr>
                                <td class="data_afd_t border_px">​រៀបចំដោយ<br> Prepare By</td>
                                <td class="data_afd_t border_px">ត្រូពិនិត្យដោយ<br> Check By</td>
                                <td class="data_afd_t border_px">ទទួលស្គាល់ ដោយ<br> ​Acknowledge By</td>
                                <td class="data_afd_t border_px">សម្រេចដោយ<br> Approved By</td>
                                <td class="data_afd_t border_px">សំគាល់​ដោយ<br> Remark</td>
                                <td class="data_afd_t border_px">ទូទាត់ដោយ<br> Paid By</td>
                                <td class="data_afd_t border_px">​ទទួលដោយ<br> Received By</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="border_tage">
                <table cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom:0;">
                    <thead style="background-color:blue;" class="border_px">
                        <tr class="border_px">
                            <td colspan="6" style="background-color:#340096;border-color:inherit;text-align:left;vertical-align:top"> សម្រាប់ការិយាល័យគណនេយ្យ For Accounting Department</td>
                        </tr>
                    </thead>
                    <tbody class="border_px">
                        <!-- <tr>
							<td colspan="6" style="background-color:#340096;border-color:inherit;text-align:left;vertical-align:top">សម្រាប់ការិយាល័យគណនេយ្យ For Accounting Department</td>
						</tr> -->
                        <tr class="border_px">
                            <td width="142" class="border_px data_afd_t">លេខគណនី <br>A/C Code</td>
                            <td width="251" class="border_px data_afd_t">ឈ្មោះគណនេយ្យ <br>Account Name</td>
                            <td width="269" colspan="2" class="border_px data_afd_t">បរិយាយ <br>
                                Description</td>
                            <td width="157" class="border_px data_afd_t">ឥណពន្ធ <br>Debit</td>
                            <td width="146" class="border_px data_afd_t">ឥណទាន <br> Credit</td>
                        </tr>
                        <tr>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td colspan="2" class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td colspan="2" class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td colspan="2" class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td colspan="2" class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td colspan="2" class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>
                        <tr class="border_px">
                            <td colspan="4" style="text-align: right;" class="border_px">សរុប Total:</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>

                    </tbody>
                </table>
                <table cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin:5px 0px ">
                    <tbody class="border_px">
                        <tr class="border_px">
                            <td width="143" class="border_px">
                                <p>កត់ត្រាដោយ</p>
                                <p>Posted by</p>
                            </td>
                            <td width="243" class="border_px">
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                            </td>
                            <td width="130" class="border_px">
                                <p>ត្រួតពិនិត្យដោយ</p>
                                <p>Verified By</p>
                            </td>
                            <td width="292" class="border_px">&nbsp;</td>
                            <td width="146">កត់ត្រា Posting</td>
                        </tr>
                        <tr>
                            <td height="52" class="border_px">ឈ្មោះ Name</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">ឈ្មោះ Name</td>
                            <td class="border_px">&nbsp;</td>
                            <td rowspan="2" class="border_px">
                                <input type="checkbox" name=""> Enter Bill <br>
                                <input type="checkbox" name=""> Pay Bill <br>
                                <input type="checkbox" name=""> Write Check <br>
                                <input type="checkbox" name=""> Journal
                            </td>
                        </tr>
                        <tr class="border_px">
                            <td height="53" class="border_px">កាលបរិច្ឆេទ Date</td>
                            <td class="border_px">&nbsp;</td>
                            <td class="border_px">កាលបរិច្ឆេទ Date</td>
                            <td class="border_px">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>