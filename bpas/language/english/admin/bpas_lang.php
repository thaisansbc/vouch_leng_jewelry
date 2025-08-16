<?php

defined('BASEPATH') or exit('No direct script access allowed');
$ci =& get_instance();
$ci->db->select()->from('language');
$query = $ci->db->get();
$result = $query->result_array();

foreach($result as $value){
$lang[$value['code']] = $value['english']?$value['english']:$value['code'];
}

/* --------------------- CUSTOM FIELDS ------------------------ */
/*
* Below are custome field labels
* Please only change the part after = and make sure you change the the words in between "";
* $lang['bcf1']                         = "Branch Custom Field 1";
* Don't change this                     = "You can change this part";
* For support email contact@sbcsolution.biz Thank you!
*/

$lang['customer_rewards_exchange_report']		= "Customer Rewards Exchange Report";
$lang['supplier_rewards_exchange_report']		= "Supplier Rewards Exchange Report";
$lang['employees_depaterment']			= "Employees Depaterment ";
$lang['employees_position']				= "Employees Position";
$lang['datatables_lang'] = [
	'sEmptyTable'     => 'No data available in table',
	'sInfo'           => 'Showing _START_ to _END_ of _TOTAL_ entries',
	'sInfoEmpty'      => 'Showing 0 to 0 of 0 entries',
	'sInfoFiltered'   => '(filtered from _MAX_ total entries)',
	'sInfoPostFix'    => '',
	'sInfoThousands'  => ',',
	'sLengthMenu'     => 'Show _MENU_ ',
	'sLoadingRecords' => 'Loading...',
	'sProcessing'     => 'Processing...',
	'sSearch'         => 'Search',
	'sZeroRecords'    => 'No matching records found',
	'oAria'           => [
	'sSortAscending'  => ': activate to sort column ascending',
	'sSortDescending' => ': activate to sort column descending',
	],
	'oPaginate' => [
	'sFirst'    => '<< First',
	'sLast'     => 'Last >>',
	'sNext'     => 'Next >',
	'sPrevious' => '< Previous',
	],
];

$lang['select2_lang'] = [
'formatMatches_s'         => 'One result is available, press enter to select it.',
'formatMatches_p'         => 'results are available, use up and down arrow keys to navigate.',
'formatNoMatches'         => 'No matches found',
'formatInputTooShort'     => 'Please type {n} or more characters',
'formatInputTooLong_s'    => 'Please delete {n} character',
'formatInputTooLong_p'    => 'Please delete {n} characters',
'formatSelectionTooBig_s' => 'You can only select {n} item',
'formatSelectionTooBig_p' => 'You can only select {n} items',
'formatLoadMore'          => 'Loading more results...',
'formatAjaxError'         => 'Ajax request failed',
'formatSearching'         => 'Searching...',
];
$lang["upload_slide"]				= "Upload Slide";
// update
$lang['services']                   = 'Services';
$lang['assign_workout']                   = 'Assign Workout';
$lang['daily_workout']                   = 'Daily Workout';
$lang['level']                   = 'Level';
$lang['document_lists']                   = 'Document Lists';
$lang['student_summary_report']  = "Student Summary Report";
$lang['student_detail_report']  = "Student Detail Report";
$lang['student_by_class_report']  = "Student by Class Report";
$lang['number_of_student_report']  = "Number Of Student Report";
$lang['enrollment_grade_by_academic']  = "Enrollment Grade By Academic";
$lang['enrollment_by_grade_report']  = "Enrollment By Grade Report";
$lang['monthly_enrollment_report']  = "Monthly Enrollment Report";
$lang['payment_report']  = "Payment Report";
$lang['monthly_tuition_fee_report']  = "Monthly Tuition Fee Report";
$lang['cash_account_payment_report']  = "Cash Account Payment Report";
$lang['daily_payment_report']  = "Daily Payment Report";
$lang['daily_payment_by_cash_account_report']  = "Daily Payment By Cash Account Report";
$lang['monthly_payment_report']  = "Monthly Payment Report";
$lang['annual_payment_report']  = "Annual Payment Report";
$lang['yearly_enrollment_report']  = "Yearly Enrollment Report";
$lang['student_fee_report']  = "Student Fee Report";
$lang['compulsory_fee_report']  = "Compulsory Fee Report";
$lang['fee_report']  = "Fee Report";
$lang['fee_by_grade_report']  = "Fee By Grade Report";
$lang['fee_by_branch_report']  = "Fee By Branch Report";
$lang['fee_by_item_report']  = "Fee by Item Report";
$lang['branchly_fee_by_grade_report']  = "Branchly Fee by Grade Report";
$lang['fee_by_category_report']  = "Fee by Category Report";
$lang['fee_by_sub_category_report']  = "Fee By Sub Category Report";
$lang['ticket_report']  = "Ticket Report";
$lang['waiting_report']  = "Waiting Report";
$lang['testing_report']  = "Testing Report";
$lang['testing_detail_report']  = "Testing Detail Report";
$lang['testing_by_grade_report']  = "Testing By Grade Report";
$lang['testing_by_student_report']  = "Testing By Student Report";
$lang['testing_student_summary_report']  = "Testing Student Summary Report";
$lang['accepted_student_by_grade_report']  = "Accepted Sutdent By Grade Report";
$lang['student_status_report']  = "Student Status Report";
$lang['suspension_report']  = "Suspension Report";
$lang['dropping_out_report']  = "Dropping Out Report";
$lang['reconfirmation_report']  = "Reconfirmation Report";
$lang['black_list_report']  = "Black list Report";
$lang['graduation_report']  = "Graduation Report";
$lang['teacher_report']  = "Teacher Report";
$lang['examanition_report']  = "Examanition Report";
$lang['teacher_attendance_report']  = "Teacher Attendance Report";
$lang['monthly_class_result_report']  = "Monthly Class Result Report";
$lang['monthly_top_five_report']  = "Monthly Top Five Report";
$lang['section_by_month_report']  = "Section By Month Report";
$lang['failure_student_by_year_report']  = "Failure Student By Year Report";
$lang['yearly_top_five_form']  = "Yearly Top Five Form";
$lang['best_student_by_grade_report']  = "Best Student By Grade Report";
$lang['monthly_top_five_form']  = "Monthly Top Five Form";
$lang['section_by_month_report']  = "Section By Month Report";
$lang['sectionly_class_result_report']  = "Sectionly Class Result Report";
$lang['class_result_report']  = "Class  Result Report";
$lang['yearly_class_result_report']  = "Yearly Class Result Report";
$lang['yearly_top_five_report']  = "Yearly Top Five Report";
$lang['sectionly_subject_result_report']  = "Sectionly Subject Result Report";
$lang['yearly_subject_result_report']  = "Yearly Subject Result Report";
$lang['result_by_student_form']  = "Result By Student Form";


$lang['account_settings'] = "Account Settings";
$lang['sent'] = "Sent";
$lang['not_done'] = "Not Done";
$lang['view_problems'] = "View Problems";
$lang['machine_types'] = "Machine Types";
$lang['add_machine_type'] = "Add Machine Type";
$lang['edit_machine_type'] = "Edit Machine Type";
$lang['delete_machine_type'] = "Delete Machine Type";
$lang['delete_machine_type'] = "Delete Machine Types";
$lang['machine_type_added'] = "Machine Type successfully added";
$lang['machine_type_deleted'] = "Machine Type successfully deleted";
$lang['machine_type_updated'] = "Machine Type successfully updated";
$lang['machine_type_deleted'] = "Machine Types successfully deleted";
$lang['repair_items'] = "Repair Items";
$lang['items'] = "Items";
$lang['contact_number'] = "Contact Number";
$lang['item_details'] = "Item Details";
$lang['repair_invoice'] = "Repair Invoice";
$lang['repairing'] = "Repairing";
$lang['received'] = "Received";
$lang['add_problem_to_order'] = "Please add problems to order list";
$lang['please_select_these_before_adding_problem'] = "Please select these before adding any problem";
$lang['update_diagnostic'] = "Update Diagnostic";
$lang['import_diagnostics'] = "Import Diagnostic";
$lang['delete_diagnostic'] = "Delete Diagnostic";
$lang['edit_diagnostic'] = "Edit Diagnostic";
$lang['add_diagnostic'] = "Add Diagnostic";
$lang['delete_diagnostics'] = "Delete Diagnostics";
$lang['diagnostic_details'] = "Diagnostic Details";
$lang['diagnostic_deleted'] = "Diagnostic successfully deleted"; 
$lang['diagnostic_updated'] = "Diagnostic successfully updated"; 
$lang['diagnostic_added'] = "Diagnostic successfully added"; 
$lang['diagnostics_deleted'] = "Diagnostics successfully deleted"; 
$lang['problem'] = "Problem";
$lang['barcode_qrcode'] = "Barcode &amp; QRcode";
$lang['problem_name'] = "Problem Name";
$lang['problem_details'] = "Problem Details";
$lang['problem_code_duplicate'] = "Problem code duplicate, please check problem code.";
$lang['brand_code_not_found'] = "Brand code not found, please check brand code.";
$lang['model_code_not_found'] = "Model code not found, please check model code.";
$lang['problems_added']  = "Problems successfully imported";
$lang['import_problems'] = "Import Problems";
$lang['update_problem'] = "Update Problem";
$lang['problem_details'] = "Problem Details";
$lang['delete_problem'] = "Delete Problem";
$lang['warranty_note'] = "Warranty Notices";
$lang['add_problem'] = "Add Problem";
$lang['edit_problem'] = "Edit Problem";
$lang['delete_problem'] = "Delete Problem";
$lang['delete_problems'] = "Delete Problems";
$lang['problem_added'] = "Problem successfully added";
$lang['problem_updated'] = "Problem successfully updated";
$lang['problem_deleted'] = "Problem succesfully deleted";
$lang['problems_deleted'] = "Problem succesfully deleted";
$lang['problems'] = "Problems";
$lang['warranty'] = "Warranty";
$lang['you_scan_your_barcode_too'] = "You can scan your barcode  and select the correct symbology below";
$lang['check_added'] = "Check Phone successfully added";
$lang['check_updated'] = "Check Phone successfully updated";
$lang['check_deleted'] = "Check Phone successfully deleted";
$lang['checks_deleted'] = "Checks Diagnostic successfully deleted";
$lang['repair_reference_no'] = "Repair Reference No";
$lang['check_reference_no'] = "Check Phone Reference No";
$lang['check_no'] = "Check No";
$lang['check_diagnostics_form'] = "Check Diagnostic Form";
$lang['diagnostics'] = "Diagnostics";
$lang['characteristic'] = "Characteristic";
$lang['symptom'] = "Symptom";
$lang['troubleshooting'] = "Troubleshooting";
$lang['running_diagnostics'] = "Running Diagnostics";
$lang['add_diagnostic_to_order'] = "Add diagnostic to diagnostics list";
$lang['please_select_these_before_adding_any_diagnostic'] = "Please select these before adding any diagnostic";
$lang['checks'] = "Check Phones";
$lang['add_check'] = "Add Check Phone";
$lang['edit_check'] = "Edit Check Phone";
$lang['delete_check'] = "Delete Check Phone";
$lang['check_details'] = "Check Phone Details";
$lang['delete_checks'] = "Delete Check Phones";
$lang['the_membership_code_you_enter_is_success'] = "The membership code you enter is successfully :)";
$lang['the_membership_code_you_enter_is_expired'] = "The membership code you enter is expired.";
$lang['the_membership_code_you_enter_is_not_valid'] = "The membership code you enter is not valid.";
$lang['membership_code'] = "Membership Code";
$lang['repair_already_sent'] = "Repair already sent";
$lang['done'] = "Done";
$lang['repair_note'] = "Repair Order Form";
$lang['technician'] = "Technician";
$lang['receive_date'] = "Receive Date";
$lang['staff_note'] = "Staff Note";
$lang['used'] = "Used";
$lang['new'] = "New";
$lang['machine_type'] = "Machine Type";
$lang['model'] = "Model";
$lang['imei_number'] = "IMEI Number";
$lang['payment_term']                   = "Payment Term";
$lang['add_repair']                      = "Add Repair";
$lang['edit_repair']                     = "Edit Repair";
$lang['delete_repair']                   = "Delete Repair";
$lang['delete_repairs']                  = "Delete Repairs";
$lang['repair_added']                    = "Repair successfully added";
$lang['repair_updated']                  = "Repair successfully updated";
$lang['repair_deleted']                  = "Repair successfully deleted";
$lang['repairs_deleted']                 = "Repairs successfully deleted";
$lang['repair_details']                  = "Repair Details";
$lang['email_repair']                    = "Email Repair";
$lang['view_repair_details']             = "View Repair Details";
$lang['repair_no']                       = "Repair No";
$lang['send_email']                     = "Send Email";
$lang['repair_items']                    = "Repair Items";
$lang['no_repair_selected']              = "No repair selected. Please select at least one repair.";
$lang['create_sale']                    = "Create Sale";
$lang['create_invoice']                    = "Create Sale";

//-------project plan-----
$lang['add_project_plan']               = 'Add Project Plan';
$lang['project_plan_prefix']            = 'Project Plan Prefix';
$lang['qoh']            				= 'QOH';
$lang['biller']            				= 'Biller';
$lang['product_option']            		= 'Product Option';
$lang['project_plan']            		= 'Project Plan';
$lang['project_plan_added']            	= 'Project Plan Successful Added';
$lang['plan']            				= 'Plan';
$lang['project_plan_details']           = 'Project Plan Details';
$lang['edit_project_plan']           	= 'Edit Project Plan';
$lang['delete_project_plan']           	= 'Delete Project Plan';
$lang['qoh_small']           			= 'Quantity is out of stock!';
$lang['qty_bigger']           			= 'Quantity is bigger than stock in hand!';
$lang['address_details']           		= 'Address Details';
$lang['edit_address']           		= 'Edit Address';
$lang['plz_product']					= "Please select any product!";
$lang['add_shortlist']					= "Add Shortlist";
$lang['gmail']							= "Gmail";
$lang['import_employee']				= "Import Employee";
$lang['delete_interview']				= "Delete Interview";
$lang['total_mark']						= "Total Mark";
$lang['interviewer']					= "Interviewer";
$lang['edit_shortlist']					= "Edit Shortlist";
$lang['shortlist_date']					= "Shortlist Date";
$lang['interview_date']					= "Interview Date";
//////////////////////////////////////////STOP ADD HERE /////////////////////////
//////////////////Please add in system Settings/ Change Language ////////////////
////////////////// OR admin/system_settings/language/////////////////////////////
