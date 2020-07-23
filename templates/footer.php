<script>

    function make_obj(serialized) {
        var obj = {};
        for (var i = 0; i < serialized.length; i++) {
            if (obj[serialized[i].name] === undefined) {
                obj[serialized[i].name] = serialized[i].value;
            } else {
                if (!Array.isArray(obj[serialized[i].name])) {
                    obj[serialized[i].name] = [];
                }
                obj[serialized[i].name].push(serialized[i].value);
            }
        }
        return obj;
    }

    function placeholders(html, replacements, open="{{", close="}}"){
        for (key in replacements) {
            placeholder = new RegExp(open + key + close, "g");
            html = html.replace(placeholder, replacements[key]);
        }
        return html;
    }

    function change_id(node, id_replace){
        if(node.id){
            node.id = placeholders(node.id, {id: id_replace}, "00", "00");
            const children = node.childNodes;
            const len = children.length;
            for (let i = 0; i < len; i++) {
                change_id(children[i], id_replace);
            }
        }
    }

    ///////////////////

    $("#frm_create_task").submit(function(event){
        this_form = this;
        event.preventDefault();
        const payload = make_obj($(this).serializeArray());
        payload['task'] = payload['task'].trim();
        var jqxhr = $.post( "/task/create/", payload, null, "json")
            .done(function(data) {
                is_changed["txt_new_task"] = false;
                //console.log($("#pending_tasks #empty_list").length);
                $("#pending_tasks #no_pending").remove();
                const task_id = data["id"] || 0;
                let html = placeholders($("#pending_task").html(), {id:task_id, 'task_name': payload["task"]}, "00", "00");
                const task_li = $('<li />').html(html);
                const pending_tasks = document.getElementById("pending_tasks");
                //console.log(task_li[0].childNodes[1].childNodes[1]);
                //task_li[0].childNodes[1].childNodes[1] = payload["task"];
                pending_tasks.appendChild(task_li[0].childNodes[1]);
                add_event_handlers(task_id);
                this_form.reset();
            })
            .fail(function(jqXHR, textStatus, error) {
                //$("#task_" + task_id).replaceWith(get_element(draft_id));
            }).always(function() {
                //
            });
    });


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

    function complete_task(id){
        const $task = $("#task_" + id);
        const task_complete = $task[0];
        save_element(task_complete, "complete_"+id);
        const task_name = $("#task_name_" + id).text();
        $("#task_name_" + id).text("Marking as complete...");
        is_changed["task_" + id] = true;
        let fail = false;
        var jqxhr = $.post( "/task/complete/" + id, {_token:csrf_token, _method:"PATCH"}, null, "json")
            .done(function(data) {
                if(data["success"] == true){
                    is_changed["task_" + id] = false;
                    $task.detach().prependTo($("#completed_tasks"));
                    if($("#pending_tasks .task-pending").length == 0){
                        //console.log("hi");
                        $("#pending_tasks").html($("#empty_list").html());
                    }
                    $("#task_name_" + id).text(task_name);
                    $task.removeClass("task-pending").addClass("task-completed");
                } else{
                    alert('Task: "' + task_name + '". Probably it has been deleted on the server. Consider Backing it up now and removing it.');
                    fail=true;
                }
            })
            .fail(function(jqXHR, textStatus, error) {
                fail=true;
            }).always(function() {
                if(fail){
                    $("#task_name_" + id).text(task_name);
                    $task[0].childNodes[0].checked = false;
                    $task[0].style.backgroundColor = "Chocolate";
                }
            });
    }


    function delete_task(id){
        save_element($("#task_" + id)[0], "deleted_"+id);
        const task_delete = $("#task_" + id)[0];
        const task_name = $("#task_name_" + id).text();
        task_delete.innerHTML = "Deleting...";
        var jqxhr = $.post( "/task/delete/" + id, {_token:csrf_token}, null, "json")
            .done(function(data) {
                is_changed["task_" + id] = false;
                if(data["success"] == true){
                    $(task_delete).remove();
                    if($("#pending_tasks .task-pending").length == 0){
                        //console.log("hi");
                        $("#pending_tasks").html($("#empty_list").html());
                    }
                } else{
                    //$("#task_" + id).replaceWith(get_element("deleted_"+id));
                    //task_delete.innerHTML = "Problem... Got you covered...";
                    alert('Error while deleting "' + task_name + '". Probably it has been deleted on the server. Will be removed');
                    $(task_delete).remove();
                }
            })
            .fail(function(jqXHR, textStatus, error) {
                $("#task_" + task_id).replaceWith(get_element("deleted_"+id));
            }).always(function() {
                //
            });
    }

    /*
    * un-processed:
    *
    * */


    jQuery(document).ready(function( $ ) {

        function has_changed(arr){
            for (let key in arr){
                if(arr[key]){
                    return true;
                }
            }
            return false;
        }

        let is_changed = {}; // to prevent closing of the window
        $("#txt_new_task").keyup(function(event){
            const btn = $("#btn_new_task")[0];
            if(this.value.length > 0){
                btn.disabled = false;
                is_changed["txt_new_task"] = true;
            } else {
                btn.disabled = true;
                is_changed["txt_new_task"] = false;
            }
        });



        window.onbeforeunload = function (e) {
            if (has_changed(is_changed)) {
                return "Make sure to save all changes.";
            }
        };


        var csrf_token = $("meta[name='csrf-token']").attr("content");
        var originals = {}; // not ported to test2



        function pending_task(id){
            //implemented differently
            const $task = $("#task_" + id);
            const task_pending = $task[0];
            save_element(task_pending, "pending_"+id);
            const task_name = $("#task_name_" + id).text();
            $("#task_name_" + id).text("Marking as complete...");
            is_changed["task_" + id] = true;
            let fail = false;
            var jqxhr = $.post( "/task/pending/" + id, {_token:csrf_token, _method:"PATCH"}, null, "json")
                .done(function(data) {
                    if(data["success"] == true){
                        is_changed["task_" + id] = false;
                        $("#pending_tasks #no_pending").remove();
                        $task.detach().appendTo($("#pending_tasks"));
                        //$("#chk_task_" + id)[0].checked = true;
                        $("#task_name_" + id).text(task_name);
                        $task.removeClass("task-completed").addClass("task-pending");
                    } else{
                        alert('Task: "' + task_name + '". Probably it has been deleted on the server. Consider Backing it up now and removing it.');
                        //$(task_delete).remove();
                        fail=true;
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    fail=true;
                }).always(function() {
                    if(fail){
                        $("#task_name_" + id).text(task_name);
                        //let chk = $("#chk_task_" + id);
                        //console.log($task);

                        //console.log(chk);
                        $task[0].childNodes[0].checked = true;
                        //$($task[0].childNodes[0]).trigger("change");
                        //chk[0].checked = false;

                        //chk.trigger("change");
                        $task[0].style.backgroundColor = "Chocolate";

                    }
                });
        }


        const btn_edit_fn = function(){
            //implemented differently

            //could be simplified using save_element and get_element

            const task_id = this.id.split("_")[1];
            const task_element =  $("#task_" + task_id)[0];
            save_element(task_element, task_element.id);
            const li = $("#task_" + task_id)[0]; //needed?
            const template_id = "template_" + li.id;
            const task_text = li.innerText;
            originals[task_element.id] = task_text;
            li.innerHTML = placeholders($("#edit_task_template")[0].innerHTML, {id:task_id});
            const txt_edit = $("#txt_edit_"+task_id)[0];
            txt_edit.value = task_text;
            $(txt_edit).change(fn_txt_edit).keyup(fn_txt_edit);
            $("#cancel_edit_"+task_id).click(function(){
                const task_id = this.id.split("_")[2];
                $("#task_" + task_id).replaceWith(get_element("task_" + task_id));
                is_changed["task_" + id] = false;
            });

            $("#save_edit_"+task_id).click(fn_txt_save_edit);

        }


        const btn_del_fn = function(){
            const task_id = this.id.split("_")[1];
            let task_name = $("#task_name_" + task_id).text();

            let msg = "";
            if(task_name){
                msg = 'Do you want to delete: "' + task_name + '" ?'
            } else {
                msg = "Do you want to delete the task?";
            }
            if (confirm(msg)) {
                //alert("You are so brave!");
                delete_task(task_id);
            }
        }


        const btn_del_enter = function(event){
            const task_id = event.target.id.split("_")[1];
            const t = $("#task_"+task_id)[0];
            if(t){
                t.style.backgroundColor = "orange";
            }
        }

        const btn_del_leave = function(event){
            const task_id = event.target.id.split("_")[1];
            const t = $("#task_"+task_id)[0];
            if(t){
                t.style.backgroundColor = "";
            }
        }


        const handle_chk_change = function (event){
            let task = event.target;
            let task_id = task.id.split("_")[2];
            if (task.checked){
                complete_task(task_id);
            } else {
                pending_task(task_id);
            }
        }

        $(".btn_edit").click(btn_edit_fn);
        $(".btn_del").click(btn_del_fn);
        $(".btn_del").mouseenter(btn_del_enter);
        $(".btn_del").mouseleave(btn_del_leave);
        $(".chk_task").change(handle_chk_change);

        function add_event_handlers(id){
            $("#task_"+ id + " .btn_edit").click(btn_edit_fn);
            $("#task_"+ id + " .btn_del").click(btn_del_fn);
            $("#task_"+ id + " .btn_del").mouseenter(btn_del_enter);
            $("#task_"+ id + " .btn_del").mouseleave(btn_del_leave);
            //$("#task_" + id + " .chk_task").change(handle_chk_change);
            $("#chk_task_" +id).change(handle_chk_change);
        }

        const fn_txt_save_edit = function(){
            const task_id = this.id.split("_")[2];
            const task_textbox = $("#txt_edit_" + task_id)[0];
            const draft_id = "draft_" + this.parentElement.id;
            save_element(this.parentElement, draft_id);
            const task_name = task_textbox.value.trim();
            if(task_name === originals["task_"+task_id]){
                $("#cancel_edit_" + task_id).click();
                return;
            }
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

        /*
        * un-processed:
        * */



        const fn_txt_edit = function(event){
            // will have to implement
            const task_id = this.id.split("_")[2];
            //console.log("#save_edit_"+task_id);
            const btn = $("#save_edit_"+task_id)[0];
            if(this.value.length > 0 && this.value != originals["task_" + task_id]){
                btn.disabled = false;
                is_changed["task_" + task_id] = true;
            } else {
                btn.disabled = true;
                is_changed["task_" + task_id] = false;
            }
        }

    });
</script>



<div class="page-content page-container" id="page-content">
    <div class="padding">
        <div class="row container d-flex justify-content-center">
            <div class="col-lg-12">
                <div class="card px-3">
                    <div class="card-body">
                        <h4 class="card-title">Awesome Todo list</h4>
                        <form method="post" action="/task/create/" id="frm_create_task">
                            <div class="add-items d-flex">
                                <input type="text" class="form-control todo-list-input" placeholder="What do you need to do today?" id="txt_new_task" autocomplete="off">
                                <input type="submit" value="Add" class="add btn btn-primary font-weight-bold todo-list-add-btn" id="btn_new_task">
                                <input type="text" name="_token" value="<?=$_SESSION['_token']?>" style="display:none"/>
                            </div>
                        </form>
                        <div class="list-wrapper">
                            <ul class="d-flex flex-column-reverse todo-list">
                                <li>
                                    <div class="form-check"> <label class="form-check-label"> <input class="checkbox" type="checkbox"><span class="task_name">For what reason would it be advisable.</span><i class="input-helper"></i></label> </div> <i class="remove mdi mdi-close-circle-outline"></i>
                                </li>
                                <i class="completed_separator" id="completed_separator"></i>
                                <li class="completed">
                                    <div class="form-check"> <label class="form-check-label"> <input class="checkbox" type="checkbox" checked=""><span class="task_name">For what reason would it be advisable for me to think.</span><i class="input-helper"></i></label> </div><i class="remove mdq mdi-close-circle-outline"><i class="remove mdi mdi-close-circle-outline"></i>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href='/static/todo.css'>
<link href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js'></script>
<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css'>
</body>
</html>