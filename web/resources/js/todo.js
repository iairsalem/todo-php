(function($){
    'use strict';
    $(function(){
        //
    });
})(jQuery);


(function($) {
    'use strict';
    $(function() {
        var csrf_token = $("meta[name='csrf-token']").attr("content");
        var local_tasks = $.jStorage.get("tasks");
        if(!local_tasks){
            local_tasks = [];
        }

        var is_current_user = $("meta[name='current-user']").attr("content");
        var last_task_id = 0;
        var last_task_id = next_task_id();
        var is_changed = {}; // to prevent closing of the
        var todoListItem = $('.todo-list');
        var todoListInput = $('.todo-list-input');
        var todoListAddBtn = $('.todo-list-add-btn');
        var originals = {};

        todoListInput.keyup(function(event){
            const btn = todoListAddBtn[0];
            if(this.value.length > 0){
                btn.disabled = false;
                is_changed[".todo-list-input"] = true;
            } else {
                btn.disabled = true;
                is_changed[".todo-list-input"] = false;
            }
        });

        onbeforeunload = function (e) {
            if (has_changed(is_changed, true)) {
                return "Make sure to save all changes.";
            }
        };

        function load_tasks(tasks){
            let name = null;
            for(let i=0; i<tasks.length;i++){
                name = tasks[i].name || tasks[i].task_name; 
                if(name){
                    add_task(name, tasks[i].completed, tasks[i]['task_id'], tasks[i].server_id);
                }
            }
        }

        function import_tasks(storage_key = "tasks"){
            if (!local_tasks){
                local_tasks = $.jStorage.get("tasks", []);
            }

            if(server_tasks.length > 0){
                load_tasks(server_tasks);
            }

            var tasks_to_import = [];
            //var task_bin = [];
            for(var i in local_tasks){

                if(!server_tasks || !has_item(server_tasks, local_tasks[i], "server_id")){
                    if (local_tasks[i] && local_tasks[i].task_name && !local_tasks[i].server_id){
                        delete local_tasks[i]['task_id'];
                        tasks_to_import.push(local_tasks[i]);
                    }
                } 
            }
            if(tasks_to_import.length > 0 ){
                var payload = {_token: csrf_token, import: tasks_to_import};
                var jqxhr = $.post( "/task/import/", payload, null, "json")
                    .done(function(data) {
                        var details = data["details"] || [];
                        if (data["success"]){
                            // Thank God.
                        } else {
                            for (var i in details){
                                local_tasks[i].task_name = "Import Error: " + local_tasks[i].task_name;
                                local_tasks[i].server_id = 0;

                            }
                        }
                        for(var i in tasks_to_import){
                            if(details[i]){
                                
                                tasks_to_import[i]['server_id'] = Number(details[i]);
                                tasks_to_import[i]['task_id'] = Number(details[i]);
                            }
                            console.log(tasks_to_import);
                        }
                        local_tasks = tasks_to_import;

                        load_tasks(local_tasks);

                    })
                    .fail(function(jqXHR, textStatus, error) {
                    }).always(function() {
                        local_tasks.push.apply(server_tasks);
                        $.jStorage.set(storage_key, local_tasks);
                        //$.jStorage.set(storage_key, local_tasks);
                        console.log($.jStorage.get(storage_key))
                        //local_tasks = task_bin;
                    });
            }
        }


        function init(){
            if(typeof sample_tasks !== 'undefined' && sample_tasks.length > 0){
                load_tasks(sample_tasks);
            } else {
                if(is_current_user=="connected"){
                    import_tasks("tasks"); // import from local storage to server
                } else {                
                    load_tasks(local_tasks);
                    $.jStorage.set("tasks", local_tasks);
                }
            }
        }

        function store_task(task_name, completed=false){
            var task_id = null;
            if (!local_tasks){
                local_tasks = $.jStorage.get("tasks", []);
            }
            task_id = next_task_id();

            let task = {task_id: task_id, task_name:task_name, completed:completed};
            local_tasks.push(task);
            $.jStorage.set("tasks", local_tasks);
            $.jStorage.set("next_task_id", parseInt(task_id) + 1);
            return true;
        }

        function add_task(task_name, completed="", id=null, server_id = null){
            if(!id || !task_id_available(id)){
                id = next_task_id(id);
                //console.log("task_id default");
            }
            var todoListItem = $('.todo-list');
            if (completed){
                completed = "completed";
            }
            if (task_name) {
                //in next version, use template from html.
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
            if(typeof sample_tasks !== 'undefined' && sample_tasks.length>0){
                add_task(task.task_name, false, null, null);
                return true;
            }
            if(is_current_user == "connected"){
                server_create_task(task);
                ret = true;
            } else{
                let id = next_task_id();
                ret = store_task(task.task_name, false, id);
                add_task(task.task_name, false, id, null);
            }
            is_changed[".todo-list-input"] = false;
            return ret;
        }

        function add_task_handler(event){
            if(event){
                event.preventDefault();
            }

            var task_name = $(this).prevAll('.todo-list-input').val().trim();
            if(!task_name){
                return false;
            }

            if(complete_add_task({task_name:task_name})){
                todoListInput.val("");
                todoListInput.focus();
                is_changed["txt_new_task"] = false;
            } else {
                //console.log("show alert: error while creating task");
            }
        }

        todoListInput.keyup(function(event){
            const btn = todoListAddBtn[0];
            if(event.target.value.length > 0){
                btn.disabled = false;
                is_changed["txt_new_task"] = true;
            } else {
                btn.disabled = true;
                is_changed["txt_new_task"] = false;
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
                        let t_id = find_index_purge(local_tasks, 'server_id', _c.server_id);
                        if(t_id!=-1){
                            local_tasks[t_id].completed = _c.checked;
                            $.jStorage.set("tasks", local_tasks);
                            console.log(local_tasks);
                        }

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


        todoListItem.on('click', '.del_button', function() {
            var task = $(this).parent()[0];
            var task_id = task.id.split("_")[1];
            return delete_task_controller(task_id);
        });

        //hide the edit icon for completed tasks.
        $("label.completed").parent().nextAll(".edit_button").css("visibility", "hidden");

        function save_task(event){
            event.preventDefault();
            var txt = $(event.target).prevAll(".edit-input");
            var id = txt[0].id.split("_")[2];
            var task_name = txt.val();
            if(originals["#original_task_"+id] == task_name){
                $("#task_" + id + " .cancel_button").trigger("click");
                return false;
            }
            let server_id = $("#task_" + id).data("server_id");

            is_changed["task_" +id] = true;
            if(is_current_user == "connected"){ //logged_in
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

                            let i = find_index_purge(local_tasks, "server_id", server_id);
                            //console.log(task["task_id"]);
                            if(i > -1){
                                local_tasks[i].task_name = task_name;
                                //local_tasks[i].completed = local_tasks.completed;
                            }
                            $.jStorage.set("tasks", local_tasks);
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
                is_changed["task_" +id] = false;
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
                is_changed["task_" + id] = true;
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
            is_changed["task_" + id] = false;
        }

        init();

        function server_create_task(task){
            var ret = false;
            task._token = csrf_token;
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
            //deprecated?
            console.log("server_complete_task not deprecated")
            var fail = false;
            var jqxhr = $.post( "/task/complete/" + id, {_token:csrf_token, _method:"PATCH"}, null, "json")
                .done(function(data) {
                    if(data["success"] == true){
                        //success
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
                return false;
            }
            return true;
        }


        const fn_txt_save_edit = function(){
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
            if(typeof sample_tasks !== 'undefined' && sample_tasks.length>0){
                $(task_el).remove();
                return;
                
            }
            
            save_element(task_el, "deleted_"+id);
            const task_name = $("#span_task_name_" + id).text();
            task_el.innerHTML = "Deleting...";
            if(is_current_user == "connected"){
                let server_id = task_el.dataset.server_id;
                if (server_id){
                    server_delete_task(server_id, id, task_el);
                    //server_id = id;
                } else {
                    $(task_el).remove();
                }
            } else { // delete from localStorage
                let i = find_index_purge(local_tasks, "task_id", id);
                //console.log(local_tasks);
                if (i != -1){
                    local_tasks.splice(i,1);
                    $(task_el).remove();
                    $.jStorage.set("tasks", local_tasks);
                    return true;
                } else{ 
                    console.log(task_name);
                    i = find_index_purge(local_tasks, "task_name", task_name);
                    if(i!=-1) {
                        local_tasks.splice(i,1);
                        $(task_el).remove();
                        $.jStorage.set("tasks", local_tasks);
                        return true;
                    }
                }
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

                        let t_id = find_index_purge(local_tasks, 'server_id', server_id);
                        if(t_id!=-1){
                            local_tasks.splice(t_id, 1);
                            $.jStorage.set("tasks", local_tasks);
                            console.log(local_tasks);
                        } 
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

        var head = document.getElementById("last_commit");
        if(head){
            head.innerText = "loading commit head";
            var jqxhr = $.get( "https://api.github.com/repos/iairsalem/todo-php/branches/dev", null, null, "json")
            .done(function(data) {
                if(data && data.commit && data.commit.sha){
                    var commit = data.commit.sha.substring(0, 7);
                    head.innerText = commit;
                }
                 
            });
        }

    });
})(jQuery);