
/****************************************************** TODO LIST ********************************************** */

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

$('.colorPicker').colorselector();

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
