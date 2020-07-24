(function($) {
    'use strict';
    $(function() {
        window.csrf_token = $("meta[name='csrf-token']").attr("content");
        var local_tasks = [];
        var tasks_map = {}; // not used
        window.is_current_user = $("meta[name='current-user']").attr("content");
        //console.log(is_current_user);
        var last_task_id = 0;
        last_task_id = next_task_id();
        window.is_changed = {}; // to prevent closing of the window

        var sample_tasks = [];//["For what reason would it be advisable for me to think.", "For what reason would it be advisable."];
        var todoListItem = $('.todo-list');
        var todoListInput = $('.todo-list-input');
        var todoListAddBtn = $('.todo-list-add-btn');
        var originals = {};

        todoListInput.keyup(function(event){
            const btn = todoListAddBtn[0];
            if(this.value.length > 0){
                btn.disabled = false;
                window.is_changed[".todo-list-input"] = true;
            } else {
                btn.disabled = true;
                window.is_changed[".todo-list-input"] = false;
            }
        });


        window.onbeforeunload = function (e) {
            if (has_changed(window.is_changed, true)) {
                return "Make sure to save all changes.";
            }
        };

        function load_tasks(tasks){
            for(let i=0; i<tasks.length;i++){
                add_task(tasks[i].name, tasks[i].completed, tasks[i].task_id, tasks[i].task_id);
            }
        }

        function import_tasks(storage_key = "tasks"){
            var local_tasks = $.jStorage.get(storage_key);
            var task_bin = [];
            for(var i in local_tasks){
                if(!local_tasks[i] || !local_tasks[i].task_name){
                    local_tasks.splice(i, 1);
                    i--;
                }
            }
            if(local_tasks && local_tasks.length > 0 ){
                var payload = {_token: window.csrf_token, import:local_tasks}
                var jqxhr = $.post( "/task/import/", payload, null, "json")
                    .done(function(data) {
                        var details = data["details"] || [];
                        if (data["success"]){
                            // Thank God.
                        } else {

                            for (var i in details){
                                if(!details[i]){
                                    task_bin.push(local_tasks[i]);
                                    local_tasks.splice(i, 1);
                                    i--;
                                    //continue;
                                }
                            }


                        }
                        for(var i in local_tasks){
                            //console.log("hi");
                            add_task(local_tasks[i].task_name, local_tasks[i].completed, details[i], details[i]);
                        }
                        //ret = data;
                    })
                    .fail(function(jqXHR, textStatus, error) {
                    }).always(function() {
                        $.jStorage.set(storage_key, task_bin);
                        local_tasks = task_bin;
                    });
            }
        }


        function init(){


            if(window.is_current_user == "connected"){

                import_tasks("tasks");
            } else {
                local_tasks = $.jStorage.get("tasks");

                for(let i in local_tasks){
                    if (!local_tasks[i] || local_tasks[i].task_name == undefined){
                        local_tasks.splice(i, 1);
                        continue;
                    }
                    add_task(local_tasks[i].task_name, local_tasks[i].completed, local_tasks[i].task_id)
                    //console.log(local_tasks[i]);
                }
                $.jStorage.set("tasks", local_tasks);
            }
            if(server_tasks && server_tasks.length>0){
                load_tasks(server_tasks);
                $.jStorage.set("tasks", server_tasks);
                local_tasks = server_tasks;
            }

        }

        function init_old(){
            tasks = $.jStorage.get("tasks");
            console.log(tasks);
            //console.log(JSON.stringify(tasks));
            //console.log(tasks);
            let created = false;
            /*
            if(tasks.length==0){
                for (let i in sample_tasks){
                    tasks.push({task_name:sample_tasks[i], completed:false, task_id:(parseInt(last_task_id)+parseInt(i))});
                }
            }*/
            let tasks_failed = [];
            if(local_tasks){
                for (let task in local_tasks){
                    if(!local_tasks[task]){
                        local_tasks.splice(task,1);
                        continue;
                    }
                    if("task_name" in local_tasks[task]){
                        var server_id = null;
                        if(window.is_current_user == "connected"){
                            let created = server_create_task({task_name: local_tasks[task].task_name, completed:tasks[task].completed})






                            local_tasks.splice(task, 1);
                            if(created){
                                server_id = created.id;
                            } else{
                                tasks_failed.push(tasks[task]);
                            }
                        }
                        created = add_task(tasks[task].task_name, tasks[task].completed, tasks[task].task_id, server_id || null);
                    }
                }
                if (tasks_failed.length > 0){
                    local_tasks = local_tasks.concat(tasks_failed);
                }
                $.jStorage.set("tasks", local_tasks);
            }
        }
        function store_task(task_name, completed=false){
            var task_id = null;
            //local_tasks = $.jStorage.get("tasks", []);
            console.log(local_tasks.length);
            task_id = next_task_id();

            let task = {task_id: task_id, task_name:task_name, completed:completed};
            local_tasks.push(task);
            //tasks_map[task_id] = task;
            $.jStorage.set("tasks", local_tasks);
            $.jStorage.set("next_task_id", parseInt(task_id) + 1);
            return true;
        }

        function add_task(task_name, completed="", id=null, server_id = null){
            if(!id || !task_id_available(id)){
                id = next_task_id(id);
            }
            var todoListItem = $('.todo-list');
            if (completed){
                completed = "completed";
            }
            if (task_name) {
                //var task_server = create_task_server(task_name);
                todoListItem.append("<li id='task_" + id + "'><div class='form-check show-text'><label class='form-check-label'><input class='checkbox' type='checkbox'><i class='input-helper'></i><span id='span_task_name_" + id + "'></span><i class='input-helper'></i></label></div><i class='edit_button remove mdi mdi-close-circle-outline'></i><i class='del_button remove mdi mdi-close-circle-outline'></i></li>");
                $("#span_task_name_"+id).text(task_name);
                if(completed){
                    $("#task_" + id + " div label").addClass(completed);
                    $("#task_" + id + " div label input")[0].checked = true;
                    $("#task_" + id + " .edit_button").css("visibility", "hidden");
                }
                if(server_id){
                    document.getElementById("task_"+id).dataset.server_id = server_id;
                }
                //console.log("task added");
                return true;
            }
            return false;
        }

        function task_id_available(id){
            return ($("#task_" + (id)).length == 0);
        }

        function next_task_id(start_from=0){
            var last = Math.max(undefined || Number(last_task_id), Number(start_from));
            var found = true;
            var i = 0;
            while(found){
                i++;
                found = $("#task_" + (last+i)).length;
            }
            return last+i;
        }

        function complete_add_task(task){
            // handles creation of task: server or localstorage, ui
            let ret = false;
            if(window.is_current_user == "connected"){
                server_create_task(task);
                ret = true;
            } else{
                let id = next_task_id();
                ret = store_task(task.task_name, false, id);
                console.log(local_tasks);
                add_task(task.task_name, false, id, null);
            }
            return ret;
        }

        function add_task_handler(event){
            if(event){
                event.preventDefault();
            }

            var task_name = $(this).prevAll('.todo-list-input').val().trim();
            console.log(task_name + " TASK NAME");
            if(!task_name){
                return false;
            }

            if(complete_add_task({task_name:task_name})){
                todoListInput.val("");
                todoListInput.focus();
            } else {
                console.log("show alert: error while creating task");
            }
        }


        function add_button_press(event) {
            //deprecated
            if(event){
                event.preventDefault();
            }
            var task_name = $(this).prevAll('.todo-list-input').val().trim();
            if(!task_name){
                return false;
            }
            var task_server = server_create_task({task_name:task_name});
            var id = null;

            var server_id = null;
            if(task_server){
                id = task_server.id;
                server_id = id;
            } else {
                id = next_task_id();
                store_task(task_name, false, id);
            }
            add_task(task_name, false, id, server_id);
            todoListInput.val("");
            todoListInput.focus();
        }

        todoListInput.keyup(function(event){
            const btn = todoListAddBtn[0];
            if(event.target.value.length > 0){
                btn.disabled = false;
                window.is_changed["txt_new_task"] = true;
            } else {
                btn.disabled = true;
                window.is_changed["txt_new_task"] = false;
            }
        });


        todoListInput.on('keypress', function(event){
            var keycode = event.keyCode || event.which;
            if (keycode == 13){
                $(".todo-list-add-btn").trigger("click");
            }
        });

        todoListAddBtn.on("click", add_task_handler);

        todoListItem.on('change', '.checkbox', function() {

            var _c = {};
            _c.$this = $(this);
            _c.$this[0].disabled = true;
            _c.parent = _c.$this.parent();
            _c.task = _c.parent.parent().parent()[0];
            _c.task_id = _c.task.id.split("_")[1];
            _c.checked = this.checked;
            _c.t_name_el = $("#task_name_" + _c.task_id);
            _c.visibility = null;
            _c.task_name = _c.t_name_el.text();
            _c.loading = ["Marking as pending...", "Marking as complete..."];
            is_changed["task_" + _c.task_id] = true;

            if(is_current_user == "connected"){ // logged_in
                _c.server_id = _c.parent.parent().parent()[0].dataset.server_id;
                set_task_status_server(_c);
            } else { // not logged_in
                //_c.local_tasks = $.jStorage.get("tasks");
                set_task_status_local(_c);
            }
        })



        function set_task_status_server(_c){
            let action = _c.checked?"complete":"pending";
            _c.t_name_el = _c.loading[_c.checked?1:0];
            var fail = false;

            var jqxhr = $.post( "/task/" + action + "/" + _c.server_id, {_token:csrf_token, _method:"PATCH"}, null, "json")
                .done(function(data) {
                    if(data["success"] == true){
                        // Thank God.
                    } else{
                        alert('Task: "' + _c.task_name + '". Probably it has been deleted on the server. Consider Backing it up now and removing it.');
                        fail=true;
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    fail=true;
                }).always(function() {
                    is_changed["task_" + _c.task_id] = false;
                    $("#task_name_" + _c.task_id).text(_c.task_name);
                    if (fail){
                        _c.$this[0].checked = !_c.$this[0].checked;

                    } else{
                        _c.parent.toggleClass('completed');
                        _c.$this.parent().parent().nextAll(".edit_button").css("visibility", _c.$this[0].checked? 'hidden':'visible');
                    }
                    _c.$this[0].disabled = false;
                });
        }

        function set_task_status_local(_c){

            let i = find_index_purge(local_tasks, "task_id", _c.task_id);
            if (i > -1){
                local_tasks[i].completed = _c.checked;
                $.jStorage.set("tasks", local_tasks);
                _c.parent.toggleClass('completed');
                _c.$this.parent().parent().nextAll(".edit_button").css("visibility", _c.$this[0].checked? 'hidden':'visible');
            }
            is_changed["task_" + _c.task_id] = false;
            _c.$this[0].disabled = false;
        }

        /*
        todoListItem.on('change', '.checkbox', function() {
            let $this = $(this);
            $this[0].disabled = true;
            let parent = $this.parent();
            let task_id = parent.parent().parent()[0].id.split("_")[1];
            let checked = this.checked;
            const t_name_el = $("#task_name_" + task_id);
            const task_name = t_name_el.text();
            let visibility = null;
            let ok = false;
            if(checked){ // mark as completed
                t_name_el.text("Marking as complete...");
                //ok = complete_task_controller(task_id);
                //copy paste controller
                let error = false;
                const $task = $("#task_" + id);
                const task_complete = $task[0];
                //save_element(task_complete, "complete_"+id);
                is_changed["task_" + id] = true;
                let response = false;
                if(is_current_user == "connected"){
                    let server_id = $task[0].dataset.server_id;
                    //response = server_complete_task(server_id);

                    /////
                    var fail = false;
                    var jqxhr = $.post( "/task/complete/" + id, {_token:csrf_token, _method:"PATCH"}, null, "json")
                        .done(function(data) {
                            if(data["success"] == true){

                                is_changed["task_" + id] = false;
                                $("#task_name_" + id).text(task_name);
                                //$task.detach().prependTo($("#completed_tasks"));
                                //if($("#pending_tasks .task-pending").length == 0){
                                //    //console.log("hi");
                                //    $("#pending_tasks").html($("#empty_list").html());
                                //}

                                //$task.removeClass("task-pending").addClass("task-completed");
                            } else{
                                alert('Task: "' + task_name + '". Probably it has been deleted on the server. Consider Backing it up now and removing it.');
                                fail=true;
                            }
                        })
                        .fail(function(jqXHR, textStatus, error) {
                            fail=true;
                        }).always(function() {

                        });

                } else{
                    response = {localStorage: true};
                    //perhaps some logic depending on the error. defaulting to localStorage
                    response = (response || {});
                    response.localStorage = true;
                    if(response.localStorage){
                        let i = find_index_purge(tasks, "task_id", id);
                        if (i > -1){
                            tasks[i].completed = true;
                            $.jStorage.set("tasks", tasks);
                        }
                    }else{
                        alert("Couldn't set task as completed 2");
                    }
                }

                visibility="hidden";
            } else { // mark as pending
                t_name_el.text("Un-Marking as complete...");
                ok = pending_task_controller(task_id);
                visibility="visible";
            }
            if (ok){
                parent.toggleClass('completed');
                $this.parent().parent().nextAll(".edit_button").css("visibility", visibility);

            } else {
                alert("Error marking task as completed 1");
            }
            t_name_el.text(task_name);
            $this[0].disabled = false;

        });
        */

        todoListItem.on('click', '.del_button', function() {
            var task = $(this).parent()[0];
            var task_id = task.id.split("_")[1];
            return delete_task_controller(task_id);
            /*
            for(var i in tasks){
                if (tasks[i] && tasks[i].task_id == task_id){
                    delete tasks[i];
                    $(this).parent().remove();
                    if(is_current_user!='false'){
                        // remove from server
                        delete_task_server(task_id);
                    }else{
                        //simpleStorage.set("tasks", tasks);
                        $.jStorage.set("tasks", tasks);
                    }
                    return;
                }
            }
             */
        });

        //hide the edit icon for completed tasks.
        $("label.completed").parent().nextAll(".edit_button").css("visibility", "hidden");

        function save_task(event){
            event.preventDefault();
            console.log("hihi");
            console.log(local_tasks);
            var txt = $(event.target).prevAll(".edit-input");
            var id = txt[0].id.split("_")[2];
            var task_name = txt.val();
            if(originals["#original_task_"+id] == task_name){
                $("#task_" + id + " .cancel_button").trigger("click");
                return false;
            }
            let server_id = $("#task_" + id).data("server_id");

            window.is_changed["task_" +id] = true;
            if(window.is_current_user == "connected"){ //logged_in
                /////
                if(!server_id){
                    alert('The task: "' + task_name + '" could not be saved.');
                }
                const payload = {id:id, task_name:task_name, _token:csrf_token, _method:"PATCH"};
                var jqxhr = $.post( "/task/edit/" + server_id, payload, null, "json")
                    .done(function(data) {
                        if(data["success"]){
                            is_changed["task_" + id] = false;
                            delete originals["task_" + id];
                            $("#task_"+id).replaceWith(get_element("original_task_"+id));
                            $("#span_task_name_"+id).text(task_name);
                        }
                    })
                    .fail(function(jqXHR, textStatus, error) {
                        //
                    }).always(function(data) {
                        if(!data["success"]){
                            alert('The task: "' + task_name + '" could not be saved 4.');
                            $("#task_" + task_id).replaceWith(get_element(draft_id));
                        }
                    });


                /////
                $("#task_"+id).replaceWith(get_element("original_task_"+id));
                $("#span_task_name_"+id).text(task_name);
                //function ends here
            }else{ //localStorage
                //local_tasks = $.jStorage.get("tasks");
                let i = find_index_purge(local_tasks, "task_id", id);
                //console.log(task["task_id"]);
                if(i > -1){
                    local_tasks[i].task_name = task_name;
                    //local_tasks[i].completed = local_tasks.completed;
                }
                $.jStorage.set("tasks", local_tasks);
                $("#task_"+id).replaceWith(get_element("original_task_"+id));
                $("#span_task_name_"+id).text(task_name);
            }
        }


        function save_task_old(event){
            var txt = $(event.target).prevAll(".edit-input");
            var id = txt[0].id.split("_")[2];
            console.log(id + "sembusak");
            var task_name = txt.val();
            if(originals["#original_task_"+id] == task_name){
                $("#task_" + id + " .cancel_button").trigger("click");
                return false;
            }
            var task = {task_name: task_name, task_id: id};
            var ok = false;
            window.is_changed["task_" +id] = true;
            if(window.is_current_user == "connected"){
                var response = server_edit_task(task);
                if (response && response["success"]){
                    ok = true;
                }
            } else {
                let i = find_index_purge(tasks, "task_id", task["task_id"]);
                console.log(task["task_id"]);
                if(i > -1){
                    tasks[i].task_name = task.task_name;
                    tasks[i].completed = task.completed;
                }
                $.jStorage.set("tasks", local_tasks);
                console.log(local_tasks);
                ok = true;
            }

            if(ok){
                $("#task_"+id).replaceWith(get_element("original_task_"+id));
                $("#span_task_name_"+id).text(task_name);
            } else {
                alert("Error Saving task. Please Back up and try again.")
            }
        }


        todoListItem.on('click', '.edit_button', function() {
            let $li = $(this).parent();
            var id = $li[0].id.split("_")[1];
            let task_name = $("#span_task_name_"+id).html();
            originals["original_task_" + id] = task_name;
            save_element($li[0], "original_task_" + id);
            let edit_template = $("#edit_template");
            let edit_templateHTML = edit_template.html();
            edit_templateHTML = placeholders(edit_templateHTML,{txt_edit_00:"txt_edit_" +id}, "", "");
            $li[0].innerHTML = edit_templateHTML;
            var save_button = $li.find(".save_button").click(save_task);
            $li.find(".cancel_button").click(cancel_edit);
            let txt = $("#txt_edit_"+id).val(task_name).width("770px");

            txt.on('keypress', function(event){
                window.is_changed["task_" + id] = true;
                var keycode = event.keyCode || event.which;
                if (keycode == 13){
                    save_button.trigger("click");
                }
            });

        });

        function cancel_edit(event){
            var txt = $(event.target).prevAll(".edit-input");
            var id = txt[0].id.split("_")[2];
            var task_name = txt.val();
            $("#task_"+id).replaceWith(get_element("original_task_"+id));
            window.is_changed["task_" + id] = false;
        }

        init();

        //window.tasks = tasks;

        $("#frm_create_task").submit(function(event){
            //deprecated
            event.preventDefault();
            const task = make_obj($(this).serializeArray());
            task["task"] = task["task"].trim();
            let created = server_create_task(task);
            if(created){
                is_changed["txt_new_task"] = false;
                let server_id = created.id;
                add_task(task.task_name, task.completed, server_id || null, server_id || null);
            } else {
                store_task(task);
            }
        });


        function server_create_task(task){
            var ret = false;
            task._token = window.csrf_token;
            var jqxhr = $.post( "/task/create/", task, null, "json")
                .done(function(data) {
                    ret = data;
                    let server_id = data["id"];
                    add_task(task.task_name, false, server_id, server_id);
                })
                .fail(function(jqXHR, textStatus, error) {
                }).always(function() {
                });
            return ret;
        }


        function server_complete_task(id){
            var fail = false;
            var jqxhr = $.post( "/task/complete/" + id, {_token:csrf_token, _method:"PATCH"}, null, "json")
                .done(function(data) {
                    if(data["success"] == true){

                        //$task.detach().prependTo($("#completed_tasks"));
                        //if($("#pending_tasks .task-pending").length == 0){
                        //    //console.log("hi");
                        //    $("#pending_tasks").html($("#empty_list").html());
                        //}

                        //$task.removeClass("task-pending").addClass("task-completed");
                    } else{
                        alert('Task: "' + task_name + '". Probably it has been deleted on the server. Consider Backing it up now and removing it.');
                        fail=true;
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    fail=true;
                }).always(function() {

                });
            if(fail){
                //$("#task_name_" + id).text(task_name);
                //$task[0].childNodes[0].checked = false;
                //$task[0].style.backgroundColor = "Chocolate";
                return false;
            }
            return true;
        }

        function complete_task_controller(id){
            let error = false;
            const $task = $("#task_" + id);
            const task_complete = $task[0];
            //save_element(task_complete, "complete_"+id);
            is_changed["task_" + id] = true;
            let response = false;
            if(is_current_user == "connected"){
                let server_id = $task[0].dataset.server_id;
                response = server_complete_task(server_id);
            } else{
                response = {localStorage: true};
            }

            if(!response){
                error = "empty response";
            } else {
                if(response["success"]){
                    is_changed["task_" + id] = false;
                    $("#task_name_" + id).text(task_name);
                }else {
                    //perhaps some logic depending on the error. defaulting to localStorage
                    response = (response || {});
                    response.localStorage = true;
                    if(response.localStorage){
                        let i = find_index_purge(tasks, "task_id", id);
                        if (i > -1){
                            tasks[i].completed = true;
                            $.jStorage.set("tasks", tasks);
                        }
                    }else{
                        alert("Couldn't set task as completed 2");
                    }
                }
            }

            if(!error){
                return true;
            }
            console.log(error);
            return false;
        }


        function server_pending_task(id){
            var jqxhr = $.post( "/task/pending/" + id, {_token:csrf_token, _method:"PATCH"}, null, "json")
                .done(function(data) {
                    if(data["success"] == true){
                        //
                    } else{
                        alert('Task: "' + task_name + '". Probably it has been deleted on the server. Consider Backing it up now.');
                        fail=true;
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    fail=true;
                }).always(function() {
                });
            if(fail){
                return false;
            }
            return true;
        }

        function pending_task_controller(id){
            let error = false;
            const $task = $("#task_" + id);
            const task_complete = $task[0];
            //save_element(task_complete, "complete_"+id);
            is_changed["task_" + id] = true;
            let response = false;
            if(is_current_user == "connected"){
                let server_id = $task[0].dataset.server_id;
                response = server_pending_task(server_id);
            } else{
                response = {localStorage: true};
            }

            if(!response){
                error = "empty response";
            } else {
                if(response["success"]){
                    is_changed["task_" + id] = false;
                    $("#task_name_" + id).text(task_name);
                }else {
                    //perhaps some logic depending on the error. defaulting to localStorage
                    response = (response || {});
                    response.localStorage=true;
                    if(response.localStorage){
                        let i = find_index_purge(tasks, "task_id", id);
                        if (i > -1){
                            tasks[i].completed = false;
                            $.jStorage.set("tasks", tasks);
                        }
                    }else{
                        alert("Couldn't set task as pending");
                        error = true;
                    }
                }
            }

            if(!error){
                return true;
            }
            console.log(error);
            return false;
        }


        function server_edit_task(task){
            if(is_current_user == "connected"){
                console.log("TODO: server_edit_task");
            } else {
                let i = find_index_purge(tasks, "task_id", task["id"]);
                if(i > -1){
                    tasks[i].task_name = task.task_name;
                    tasks[i].completed = task.completed;
                }
                $.jStorage.set("tasks", tasks);
            }
        }

        const fn_txt_save_edit = function(){
            //const task_id = this.id.split("_")[2];
            //const task_textbox = $("#txt_edit_" + task_id)[0];
            //const draft_id = "draft_" + this.parentElement.id;
            //save_element(this.parentElement, draft_id);
            //const task_name = task_textbox.value.trim();
            //if(task_name === originals["task_"+task_id]){
            //    $("#cancel_edit_" + task_id).click();
            //    return;
            //}
            is_changed["task_" + task_id] = true;
            const payload = {id:task_id, task_name:task_name, _token:csrf_token, _method:"PATCH"};
            var jqxhr = $.post( "/task/edit/" + task_id, payload, null, "json")
                .done(function(data) {
                    if(data["success"]){
                        is_changed["task_" + task_id] = false;
                        delete originals["task_" + task_id];
                        $("#task_"+task_id).replaceWith(get_element("task_" + task_id));
                        $("#task_name_"+task_id).text(task_name);
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    //
                }).always(function(data) {
                    if(!data["success"]){
                        alert('The task: "' + task_name + '" could not be saved.');
                        $("#task_" + task_id).replaceWith(get_element(draft_id));
                    }
                });
        }


        function delete_task_controller(id){
            let task_el = $("#task_" + id)[0];
            save_element(task_el, "deleted_"+id);
            task_el.innerHTML = "Deleting...";
            if(is_current_user == "connected"){
                let server_id = task_el.dataset.server_id;
                if (!server_id){
                    server_id = id;
                }
                //console.log(server_id);
                server_delete_task(server_id, id, task_el);
            } else { // delete from localStorage

                let i = find_index_purge(local_tasks, "task_id", id);
                console.log(local_tasks);
                if (i != -1){
                    local_tasks.splice(i,1);
                    $(task_el).remove();
                    $.jStorage.set("tasks", local_tasks);
                    //console.log(tasks);
                    return true;
                }
                /*
                for(var i in tasks){
                    if (tasks[i] && tasks[i].task_id == id){
                        delete tasks[i];
                        $(task_el).remove();
                        $.jStorage.set("tasks", tasks);
                        return true;
                    }
                }
                 */
            }
            return false;
        }

        function server_delete_task(server_id, id, task_el){
            var ret = false;
            var jqxhr = $.post( "/task/delete/" + server_id, {_token:csrf_token}, null, "json")
                .done(function(server_response) {
                    console.log(server_response)
                    if(server_response && server_response["success"]){
                        is_changed["task_" + id] = false;
                        console.dir(task_el);
                        $(task_el).remove();
                    }else{
                        if(server_response["error"] == "fail"){ //server could not delete it
                            const task_name = $("#task_name_" + id).text();
                            alert('Error while deleting "' + task_name + '". Probably it has been deleted on the server. Will be removed');
                            $(task_el).remove();
                        } else{ // "connection". roll back.
                            $(task_el).replaceWith(get_element("deleted_"+id));
                        }
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    //$("#task_" + task_id).replaceWith(get_element("deleted_"+id));
                    ret_data["error"] = "fail";
                }).always(function() {
                    //
                });
        }

        var element_store = {};
        function save_element(el, key){
            if(element_store[key]){
                delete element_store[key];
            }
            element_store[key] = $(el).clone(true, true);
        }

        function get_element(key){
            if(element_store[key]){
                return element_store[key];
            }
        }
        /*
        window.onbeforeunload = function (e) {
            if (has_changed(is_changed)) {
                return "Make sure to save all changes.";
            }
        };
        */
    });
})(jQuery);

/*
<li id='task_1'>
                                    <div class="form-check"> <label class="form-check-label"> <input class="checkbox" type="checkbox"><i class="input-helper"></i><span id="span_task_name_1">For what reason would it be advisable.</span></label> </div><i class="edit_button remove mdi mdi-close-circle-outline"></i><i class="del_button remove mdi mdi-close-circle-outline"></i>
                                </li>
                                <li id='task_2'>
                                    <div class="form-check"> <label class="form-check-label completed"> <input class="checkbox" type="checkbox" checked=""><i class="input-helper"></i><span id="span_task_name_2">For what reason would it be advisable for me to think.</span><i class="input-helper"></i></label> </div> <i class="edit_button remove mdi mdi-close-circle-outline"></i><i class="del_button remove mdi mdi-close-circle-outline"></i>
                                </li>
*
* */