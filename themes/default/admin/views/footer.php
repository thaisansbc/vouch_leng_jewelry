<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="clearfix"></div>
<?= '</div></div></div></td></tr></table></div></div>'; ?>
<div class="clearfix"></div>
<footer>
<a href="#" id="toTop" class="blue" style="position: fixed; bottom: 30px; right: 30px; font-size: 30px; display: none;">
    <i class="fa fa-chevron-circle-up"></i>
</a>
    <a href="http://sbcsolution.biz/" target="_blank">
    <p style="text-align:center;">&copy; <?= date('Y') . ' ' . $Settings->site_name; ?> ( BPAS ERP v<?= $Settings->version; ?>
        )
    </p>
</a>
</footer>
<?= '</div>'; ?>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
<!--<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>-->
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>
<script type="text/javascript">
var dt_lang = <?=$dt_lang?>, dp_lang = <?=$dp_lang?>, site = <?=json_encode(['url' => base_url(), 'base_url' => admin_url(), 'assets' => $assets, 'settings' => $Settings, 'dateFormats' => $dateFormats])?>;
var lang = {use: '<?=lang('use');?>', return: '<?=lang('return');?>', paid: '<?=lang('paid');?>', unpaid: '<?=lang('unpaid');?>', requested: '<?=lang('requested');?>', approved: '<?=lang('approved');?>', reject: '<?=lang('rejected');?>', rejected: '<?=lang('rejected');?>', order: '<?=lang('ordered');?>', pending: '<?=lang('pending');?>', completed: '<?=lang('completed');?>', ordered: '<?=lang('ordered');?>', received: '<?=lang('received');?>', partial: '<?=lang('partial');?>', sent: '<?=lang('sent');?>', r_u_sure: '<?=lang('r_u_sure');?>', due: '<?=lang('due');?>', returned: '<?=lang('returned');?>', transferring: '<?=lang('transferring');?>', active: '<?=lang('active');?>', inactive: '<?=lang('inactive');?>', unexpected_value: '<?=lang('unexpected_value');?>', no_match_found: '<?=lang('no_match_found');?>', select_above: '<?=lang('select_above');?>', download: '<?=lang('download');?>', making: '<?=lang('making');?>',verified: '<?=lang('verified');?>',spoiled: '<?=lang('spoiled');?>',paid: '<?=lang('paid');?>', expired: '<?=lang('expired');?>', assigned: '<?=lang('assigned');?>',cleared: '<?=lang('cleared');?>', approved: '<?=lang('approved');?>',inactive: '<?=lang('inactive');?>', unexpected_value: '<?=lang('unexpected_value');?>',payoff: '<?=lang('payoff');?>', pawn_rate: '<?=lang('pawn_rate');?>', pawn_received: '<?=lang('pawn_received');?>', pawn_sent: '<?=lang('pawn_sent');?>', closed: '<?=lang('closed');?>', yes: '<?=lang('yes');?>', no: '<?=lang('no');?>', morning: '<?=lang('morning');?>', afternoon: '<?=lang('afternoon');?>', full: '<?=lang('full');?>', freight: '<?=lang('freight');?>', packaging: '<?=lang('packaging');?>', take_away: '<?=lang('take_away');?>', fixed: '<?=lang('fixed');?>', difference: '<?=lang('difference');?>', checked_in: '<?=lang('checked_in');?>', checked_out: '<?=lang('checked_out');?>',checked: '<?=lang('checked');?>', expense : '<?= lang('expense') ?>', draft : '<?= lang('draft') ?>', enrolled : '<?= lang('enrolled') ?>', 
        returned_to_borrower : '<?= lang('returned_to_borrower') ?>', collateral_with_borrower : '<?= lang('collateral_with_borrower') ?>',
        deposited_into_branch : '<?= lang('deposited_into_branch') ?>',
        repossessed : '<?= lang('repossessed') ?>', sold : '<?= lang('sold') ?>',
        lost : '<?= lang('lost') ?>', disbursed : '<?= lang('disbursed') ?>',
        requested : '<?= lang('requested') ?>', declined : '<?= lang('declined') ?>',
        applied : '<?= lang('applied') ?>', cancelled :'<?= lang('cancelled') ?>',
        suspended :'<?= lang('suspended') ?>', repairing : '<?= lang('repairing') ?>',
        done : '<?= lang('done') ?>', reservation : '<?= lang('reservation')?>',
        not_done : '<?=lang('not_done')?>', over : '<?=lang('over')?>',
        voided : '<?=lang('voided')?>', accepted : '<?=lang('accepted')?>',
        follow_up : '<?=lang('follow_up')?>', deposited : '<?=lang('deposited')?>',
        drop_out : '<?=lang('drop_out')?>', black_list : '<?=lang('black_list')?>',
        suspend : '<?=lang('suspend')?>', reconfirm : '<?=lang('reconfirm')?>',
        income : '<?=lang('income')?>', penalty : '<?=lang('penalty')?>',
        booking : '<?=lang('booking')?>', consignment : '<?=lang('consignment')?>', 
        usd : '<?=lang('usd')?>', khr : '<?=lang('khr')?>',
        delivered : '<?=lang('delivered')?>', 
        completed : '<?=lang('completed')?>', 
        candidate : '<?=lang('candidate')?>', 
        interview : '<?=lang('interview')?>', 
        public : '<?=lang('Public')?>', 
        permission : '<?=lang('Permission')?>',  
        absent : '<?=lang('Absent')?>',  
        present : '<?=lang('Present')?>',  
        employee : '<?=lang('employee')?>',  
        unpublic : '<?=lang('Unpublic')?>', 
        used : '<?=lang('Used')?>', 
        shortlist : '<?=lang('shortlist')?>', 
    };
</script>

<?php
$s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
foreach (lang('select2_lang') as $s2_key => $s2_line) {
    $s2_data[$s2_key] = str_replace(['{', '}'], ['"+', '+"'], $s2_line);
}
$s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>

<?php 

/*-------------------checking fetch_class && fetch_method-------------------*/
$n=$m;
$t=$v;

?>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/core.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?= $assets; ?>js/hc/highcharts.js"></script>

<?= ($m == 'purchases_order' && ($v == 'add' || $v == 'edit' || $v == 'purchase_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases_order.js"></script>' : ''; ?>
<?= ($m == 'purchases_request' && ($v == 'add' || $v == 'edit' || $v == 'purchase_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases_request.js"></script>' : ''; ?>
<?= ($m == 'purchases' && ($v == 'add_asset_expense' || $v == 'edit_asset_expense' || $v == 'add' || $v == 'edit' || $v == 'purchase_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases.js"></script>' : ''; ?>
<?= ($m == 'purchases' && ($v == 'add_receive' || $v == 'edit_receive')) ? '<script type="text/javascript" src="' . $assets . 'js/receives.js"></script>' : ''; ?>
<?= ($m == 'transfers' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/transfers.js"></script>' : ''; ?>
<?= (($m == 'sales' || $m == 'rental') && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/sales.js?version=1.1"></script>' : ''; ?>
<?= ($m == 'property' && ($v == 'add_sale' || $v == 'edit_sale')) ? '<script type="text/javascript" src="' . $assets . 'js/sales_property.js"></script>' : ''; ?>
<?= ($m == 'sales_order' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/sales_order.js"></script>' : ''; ?>
<?= ($m == 'clinic' && ($v == 'add_prescription' || $v == 'edit_prescription')) ? '<script type="text/javascript" src="' . $assets . 'js/prescription.js"></script>' : ''; ?>
<?= ($m == 'clinic' && ($v == 'add_consultation' || $v == 'edit_consultation')) ? '<script type="text/javascript" src="' . $assets . 'js/sales_order.js"></script>' : ''; ?>
<?= ($m == 'account' && ($v == 'add_enter_journal' || $v == 'edit_enter_journal')) ? '<script type="text/javascript" src="' . $assets . 'js/enter_journal.js"></script>' : ''; ?>
<?= ($m == 'returns' && ($v == 'add'||$v == 'edit'|| $v == 'add_keep')) ?'<script type="text/javascript" src="'. $assets.'js/returns.js"></script>' : ''; ?>
<?= ($m == 'returns_request' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/returns_request.js"></script>' : ''; ?>
<?= ($m == 'quotes' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/quotes.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_adjustment' || $v == 'edit_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/adjustments.js"></script>' : ''; ?>
<?= (($m == 'products' || $m == 'library') && ($v == 'add_using_stock' || $v == 'edit_using_stock' || $v == 'return_using_stock' || $v=='add_borrow' || $v== 'edit_borrow')) ? '<script type="text/javascript" src="' . $assets . 'js/using_stock.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'adjust_cost')) ? '<script type="text/javascript" src="' . $assets . 'js/adjust_costs.js"></script>' : ''; ?>
<?= ($m == 'projects' && ($v == 'add_plan' || $v == 'edit_plan') ) ? '<script type="text/javascript" src="' . $assets . 'js/project_plan.js"></script>' : ''; ?>
<?= ($m == 'deliveries' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/deliveries.js"></script>' : ''; ?>
<?= ($m == 'room'  && ($v == 'add_booking' || $v == 'checkin' || $v == 'edit_rent')) ? '<script type="text/javascript" src="' . $assets . 'js/rooms.js"></script>' : ''; ?>



<?= ($m == 'room'  && ($v == 'ticket_booking' || $v == 'edit_ticket')) ? '<script type="text/javascript" src="' . $assets . 'js/rooms.js"></script>' : ''; ?>
<?= ($m == 'tickets'  && ($v == 'ticket_booking' || $v == 'edit_ticket')) ? '<script type="text/javascript" src="' . $assets . 'js/tickets.js"></script>' : ''; ?>
<?= ($m == 'sales'  && ($v == 'ticket' || $v == 'add_ticket' || $v == 'edit_ticket')) ? '<script type="text/javascript" src="' . $assets . 'js/express.js"></script>' : ''; ?>
<?= ($m == 'hr' && ($v == 'add_kpi' || $v == 'edit_kpi')) ? '<script type="text/javascript" src="' . $assets . 'js/kpi.js"></script>' : ''; ?>
<?= ($m == 'attendances' && ($v == 'add_take_leave' || $v == 'edit_take_leave')) ? '<script type="text/javascript" src="' . $assets . 'js/take_leave.js"></script>' : ''; ?>
<?= ($m == 'attendances' && ($v == 'add_day_off' || $v == 'edit_day_off')) ? '<script type="text/javascript" src="' . $assets . 'js/day_off.js"></script>' : ''; ?>
<?= ($m == 'attendances' && ($v == 'add_check_in_out' || $v == 'edit_check_in_out')) ? '<script type="text/javascript" src="' . $assets . 'js/check_in_out.js"></script>' : ''; ?>
<?= ($m == 'pawns' && ($v =='add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/pawns.js"></script>' : ''; ?>
<?= ($m == 'sales_store'&& ($v=='add'||$v =='edit'))?'<script type="text/javascript" src="' . $assets . 'js/sales_store.js"></script>' : ''; ?>
<?= ($m == 'sales_order_store' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/sales_order_store.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add_consignment' || $v == 'edit_consignment')) ? '<script type="text/javascript" src="' . $assets . 'js/consignment.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_consignment' || $v == 'edit_consignment')) ? '<script type="text/javascript" src="' . $assets . 'js/pro_consignment.js"></script>' : ''; ?>
<?= ($m == 'schools'&& ($v == 'add_sale' || $v == 'edit_sale' || $v == 'sale_by_excel')) ?'<script type="text/javascript" src="' . $assets . 'js/sh_sales.js"></script>' : ''; ?>
<?= ($m == 'schools'&&($v =='add_ticket'||$v =='edit_ticket'))?'<script type="text/javascript" src="'.$assets.'js/sh_ticket.js"></script>':''; ?>
<?= ($m =='schools'&&($v =='add_waiting'||$v =='edit_waiting'))?'<script type="text/javascript" src="' . $assets . 'js/sh_waiting.js"></script>' : ''; ?>
<?= ($m == 'schools' && ($v == 'add_testing' || $v == 'edit_testing')) ? '<script type="text/javascript" src="' . $assets . 'js/sh_testing.js"></script>' : ''; ?>
<?= ($m == 'schools' && ($v == 'add_document_forms' || $v == 'edit_document_form')) ? '<script type="text/javascript" src="' . $assets . 'js/sh_student_document_form.js"></script>' : ''; ?>
<?= ($m == 'schools' && ($v == 'assign_student' || $v == 'edit_assign_student')) ? '<script type="text/javascript" src="' . $assets . 'js/sh_student_assign.js"></script>' : ''; ?>
<?= ($m == 'schools' && ($v == 'student_status' || $v == 'add_student_status' || $v == 'edit_student_status')) ? '<script type="text/javascript" src="' . $assets . 'js/sh_student_status.js"></script>' : ''; ?>
<?= ($m == 'schools' && ($v == 'add_examination' || $v == 'edit_examination')) ? '<script type="text/javascript" src="' . $assets . 'js/examinations.js"></script>' : ''; ?>
<?= ($m == 'gym'  && ($v == 'add_sale' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/gym.js"></script>' : ''; ?>

<?= ($m == 'account' && ($v == 'add_credit_note' || $v == 'edit_credit_note')) ? '<script type="text/javascript" src="' . $assets . 'js/credit_note.js"></script>' : ''; ?>
<?= ($m == 'account' && ($v == 'add_debit_note' || $v == 'edit_debit_note')) ? '<script type="text/javascript" src="' . $assets . 'js/debit_note.js"></script>' : ''; ?>


<?= ($m == 'repairs' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/repairs.js"></script>' : ''; ?>
<?= ($m == 'repairs' && ($v == 'add_check' || $v == 'edit_check')) ? '<script type="text/javascript" src="' . $assets . 'js/repair_checks.js"></script>' : ''; ?>
<?= ($m == 'expenses' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/expenses.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'reward_ring' || $v == 'reward_money' )) ? '<script type="text/javascript" src="' . $assets . 'js/reward_ring.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_reward_exchange' || $v == 'edit_reward_exchange')) ? '<script type="text/javascript" src="' . $assets . 'js/rewards_exchange.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_count_ring' || $v == 'edit_count_ring')) ? '<script type="text/javascript" src="' . $assets . 'js/count_ring.js"></script>' : ''; ?>


<?= ($m == 'pos' && ($v == 'add_customer_stock' || $v == 'edit_customer_stock')) ? '<script type="text/javascript" src="' . $assets . 'js/customer_stocks.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_delivery' || $v == 'edit_delivery')) ? '<script type="text/javascript" src="' . $assets . 'js/con_delivery.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_fuel' || $v == 'edit_fuel')) ? '<script type="text/javascript" src="' . $assets . 'js/con_fuel.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_error' || $v == 'edit_error')) ? '<script type="text/javascript" src="' . $assets . 'js/con_error.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_moving_waiting' || $v == 'edit_moving_waiting')) ? '<script type="text/javascript" src="' . $assets . 'js/con_moving_waiting.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_mission' || $v == 'edit_mission')) ? '<script type="text/javascript" src="' . $assets . 'js/con_mission.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_absent' || $v == 'edit_absent')) ? '<script type="text/javascript" src="' . $assets . 'js/con_absent.js"></script>' : ''; ?>
<?= ($m == 'concretes' && ($v == 'add_adjustment' || $v == 'edit_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/con_adjustment.js"></script>' : ''; ?>
<?= ($m == 'workorder' && ($v == 'add_bom' || $v == 'edit_bom')) ? '<script type="text/javascript" src="' . $assets . 'js/boms.js"></script>' : ''; ?>
<?= (($m == 'money') && ($v == 'add_exchange' || $v == 'edit_exchange')) ? '<script type="text/javascript" src="' . $assets . 'js/money_exchange.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add_fuel_sale' || $v == 'edit_fuel_sale')) ? '<script type="text/javascript" src="' . $assets . 'js/fuel_sale.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add_fuel_customer' || $v == 'edit_fuel_customer')) ? '<script type="text/javascript" src="' . $assets . 'js/fuel_customer.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add_sale_return' || $v == 'edit_sale_return')) ? '<script type="text/javascript" src="' . $assets . 'js/sale_returns.js"></script>' : ''; ?>


<?= ($m == 'leads'  && ($v == 'pipeline')) ? '
<script type="text/javascript" src="' . $assets . 'js/express.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/moment.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/notify.min.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/ajaxform.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/kanban.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/bootstrap-colorselector.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/jquery.runner.js"></script>
<script type="text/javascript" src="' . $assets . 'kanban/js/dropzone.js"></script>
' : ''; ?>
<?php if($m == 'leads'){?>
<script>
    /****************** TODO LIST ********************************************** */
    // Click on a close button to hide the current list item
    var close = document.getElementsByClassName("close");
    var i;
    for (i = 0; i < close.length; i++) {
        close[i].onclick = function() {
            var div = this.parentElement;
            div.style.display = "none";
        }
    }
    var todo_json = [];
    $('.todo_ul_edit_mode').on("click", "li", function(e) {
        if ($(this).hasClass("checked")) {
            new_value = 0;
        } else {
            new_value = 1;
        }
        $(this).toggleClass("checked");
        current_todo_id = $(this).data('todoid');
        $.ajax({
            url: site.base_url + "ajax/update_field/tasks_todo/status/" + new_value + "/id/" + current_todo_id,
            dataType: 'json',
            cache: false,
            success: function(data) {
            }
        });
        e.preventDefault();
    });
    $('.todo_ul_edit_mode ').on("click", ".close", function(e) {
        e.preventDefault();
        parent_li = $(this).parent();
        current_todo_id = $(this).parent().data('todoid');
        $.ajax({
            url: site.base_url + "ajax/delete/tasks_todo/id/" + current_todo_id,
            dataType: 'json',
            cache: false,
            success: function(data) {
                parent_li.remove();
            }
        });
        e.stopPropagation();
    });
    $('#newTaskAddBtn').on('click', function() {
        var li = document.createElement("li");
        var inputValue = $('#AddTodoInput').val();
        var t = document.createTextNode(inputValue);
        li.appendChild(t);
        if (inputValue === '') {
            alert("You must write something!");
        } else {
            todo_json.push(inputValue);
            console.log(todo_json);
            $('#add_task_todo').val(JSON.stringify(todo_json));
            $('#newTaskTodoUl').append("<li>" + inputValue + "</li>");;
        }
        $('#AddTodoInput').val("");
    });
    $('#editTaskAddBtn').on('click', function() {
        var li = document.createElement("li");
        var inputValue = $('#editTodoInput').val();
        var t = document.createTextNode(inputValue);
        li.appendChild(t);
        if (inputValue === '') {
            alert("You must write something!");
        } else {
            todo_json.push(inputValue);
            console.log(todo_json);
            $('#edit_task_todo').val(JSON.stringify(todo_json));
            $('#editTaskTodoUl').append("<li>" + inputValue + "</li>");;
        }
        $('#editTodoInput').val("");
    });
    function removeA(arr) {
        var what, a = arguments,
            L = a.length,
            ax;
        while (L > 1 && arr.length) {
            what = a[--L];
            while ((ax = arr.indexOf(what)) !== -1) {
                arr.splice(ax, 1);
            }
        }
        return arr;
    }
    /****************************************************** VARIOUS ********************************************** */
    //$('.colorPicker').colorselector();
    $('#delete_task').on('click', function(event) {
        var result = confirm("Are you sure?");
        var task_id = $(this).attr("rel");
        if (result) {
            $.ajax({
                url: site.base_url + "ajax/delete/tasks/task_id/" + task_id,
                dataType: 'json',
                cache: false,
                success: function(data) {
                    window.location.reload();
                }
            });
        }
    })
    /****************************************************** MODALS  ********************************************** */
    $('#addTaskModal').on('show.bs.modal', function(event) {

        var button = $(event.relatedTarget) // Button that triggered the modal
        var container_name = button.data('container_name');
        var container_id = button.data('container_id');

        todo_json = [];
        $('#add_task_todo').val("");

        var modal = $(this)
        modal.find('.modal-title').text('Add Task in: ' + container_name)
        $('#task_container').val(container_id)

        modal.find('.todo_ul').html("");
        modal.find('.todo_ul').on("click", "li", function() {
            removeA(todo_json, $(this).html());
            $('#task_todo').val(JSON.stringify(todo_json));
            $(this).remove();

            /*var index = $.inArray("prova", todo_json);
            if (index >= 0) todo_json.splice(index, 1);*/

        });
    })
    function popolate_attachment(a) {
        $('.attachments_body').append("<tr><td><img width='25' src='<?php echo admin_url(); ?>images/file.png' /></td><td><a href='<?php echo admin_url(); ?>uploads/" + a.attachment_filename + "'>" + a.attachment_original_filename + "</a></td><td>" + a.user_name + "</td><td>" + a.attachment_creation_date + "</td><td><img class='delete_attachment' rel='" + a.attachment_id + "' width='25' alt='Delete file' title='Delete file' src='<?php echo admin_url(); ?>images/delete.png'></tr>");
        $('.delete_attachment').on('click', function(event) {
            var result = confirm("Are you sure?");
            var attachment_id = $(this).attr("rel");
            if (result) {
                $.ajax({
                    url: site.base_url + "ajax/delete/attachments/attachment_id/" + attachment_id,
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                        window.location.reload();
                    }
                });
            }
        })
    }
    $(function() {
        <?php if (!empty($data['task_standby']['task_title'])) : ?>
            $('#resumeWorkTaskModal').modal('show');
        <?php endif; ?>
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD H:mm'
        });

        /* Here we will store all data */
        var myArguments = {};
        function assembleData(object, arguments) {
            var data = $(object).sortable('toArray'); // Get array data
            var container_id = $(object).attr("rel"); // Get step_id and we will use it as property name
            var arrayLength = data.length; // no need to explain
            /* Create step_id property if it does not exist */
            if (!arguments.hasOwnProperty(container_id)) {
                arguments[container_id] = new Array();
            }
            /* Loop through all items */
            for (var i = 0; i < arrayLength; i++) {
                if (data[i]) {
                    var task_id = data[i];
                    /* push all image_id onto property step_id (which is an array) */
                    arguments[container_id].push(task_id);
                }
            }
            return arguments;
        }
        /* Sort task */
        var globalTimer;
            $(".sortable").sortable({
                connectWith: ".sortable",
                cancel: ".nodrag",
                opacity: 0.7,
                placeholder: "li-placeholder",
                /* That's fired first */
                start: function(event, ui) {
                    $('.column').css('overflow-y', 'inherit'); // fix for x scroll bug
                    myArguments = {};
                    /*$('.column').css('overflow', 'hidden');*/
                    ui.item.addClass('rotate');
                    globalTimer = setTimeout(function() {
                        $('.drag_options').fadeIn(300);
                    }, 800);
                },
                /* That's fired second */
                remove: function(event, ui) {
                    /* Get array of items in the list where we removed the item */
                    myArguments = assembleData(this, myArguments);
                },
                /* That's fired thrird */
                receive: function(event, ui) {
                    /* Get array of items where we added a new item */
                    myArguments = assembleData(this, myArguments);
                },
                update: function(e, ui) {
                    if (this === ui.item.parent()[0]) {
                        /* In case the change occures in the same container */
                        if (ui.sender == null) {
                            myArguments = assembleData(this, myArguments);
                        }
                    }
                },
                /* That's fired last */
                stop: function(event, ui) {
                    clearTimeout(globalTimer);
                    ui.item.removeClass('rotate');
                    $('.column').css('overflow-y', 'auto'); // fix for x scroll bug
                    if ($(ui.item.parent()[0]).attr('rel') == 'archive' || $(ui.item.parent()[0]).attr('rel') == 'bin') {
                        ui.item.hide();
                    }
                    $('.drag_options').fadeOut(100);

                    $('.bin_container').fadeOut(500);
                    /* Send JSON to the server */
                    // console.log("Send JSON to the server:<pre>" + myArguments + "</pre>");

                    if ($(ui.item.parent()[0]).attr('rel') == 'bin') {
                        task_id = $(ui.item).attr('id');

                        $.ajax({
                            url: site.base_url + "leads/update_field/delete/tasks/task_id/" + task_id,
                            type: 'post',
                            dataType: 'json',
                            data: myArguments,
                            cache: false
                        });
                    } else if ($(ui.item.parent()[0]).attr('rel') == 'archive') {
       
                        task_id = $(ui.item).attr('id');
                        $.ajax({
                            url: site.base_url + "leads/update_field/tasks/task_archived/1/task_id/" + task_id,
                            type: 'post',
                            dataType: 'json',
                            data: myArguments,
                            cache: false
                        });
                    } else {
                        // alert(JSON.stringify(myArguments));
                        // console.log(JSON.stringify(myArguments));
                        $.ajax({
                            type: 'post',
                            url: site.base_url + "leads/update_position",
                            dataType: 'json',
                            data:{movedata:myArguments,
                                <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'},
                            // data:myArguments,
                            success: function(response) {
                                console.log('SUCCESS BLOCK');
                                console.log(response);
                            },
                            error: function(response) {
                                console.log(response);
                                console.log('ERROR BLOCK');
                                console.log(response);
                            }
                        });
                    }
                },
            });
        $(".portlet").addClass("ui-helper-clearfix ui-corner-all");
        $(".portlet-toggle").on("click", function() {
            var icon = $(this);
            icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
            icon.closest(".portlet").find(".portlet-content").toggle();
            return false;
        });
        $(".column").on("tap", function() {
        });
    });
</script>
<?php } ?>
<script type="text/javascript" charset="UTF-8">var oTable = '', r_u_sure = "<?=lang('r_u_sure')?>";
    <?=$s2_file_date?>
    $.extend(true, $.fn.dataTable.defaults, {"oLanguage":<?=$dt_lang?>});
    $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
   /* $(window).load(function () {
        $('.mm_<?=$m?>').addClass('active');
        $('.mm_<?=$m?>').find("ul").first().slideToggle();
        $('#<?=$m?>_<?=$v?>').addClass('active');
        $('.mm_<?=$m?> a .chevron').removeClass("closed").addClass("opened");
    });
    */
/*------------------result check-------------------*/
    $(window).load(function () {
        $('.mm_<?=$n?>').addClass('active');
        $('.mm_<?=$n?>').find("ul").slideToggle();
        $('#<?=$n?>_<?=$t?>').addClass('active');
        $('.mm_<?=$n?> a .chevron').removeClass('closed').addClass('opened');
    });

</script>
</body>
</html>
