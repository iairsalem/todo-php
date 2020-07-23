<?php
global $auth;

$auth->require_logged_in();

$tasks = list_tasks(false);

function checkbox($name, $checked=''){
    if ($checked){
        $checked = ' ' . $checked;
    }
    return "<input type='checkbox' id='{$name}'{$checked}>";
}

function img_button($src, $id, $extra = ''){
    return "<img width='20px' src='static/{$src}' id='{$id}'{$extra}>";
}

function span($id, $value, $extra=''){
    return "<span id='{$id}'{$extra}>{$value}</span>";
}

$extra_edit = 'class="btn_edit"';
$extra_del = 'class="btn_del"';

?>

<div>
    <form method="post" action="/task/create/" id="frm_create_task">
        <input type="text" placeholder="Enter New Task Here" name="task" id="txt_new_task" autocomplete="off" />
        <input type="submit" value="Create" id="btn_new_task">
        <input type="text" name="_token" value="<?=$_SESSION['_token']?>" style="display:none"/>
    </form>
</div>

<h1>Your Tasks</h1>
<ul id="pending_tasks">
    <?php
    foreach($tasks['pending']  as $task){
        echo "<li class='task-pending' id='task_{$task['task_id']}'>" . checkbox('chk_task_'.$task['task_id'], 'class="chk_task"').span('task_name_'. $task['task_id'],$task['name']) .img_button('edit.svg', 'edit_'. $task['task_id'], $extra_edit) . img_button('del.svg', 'del_'. $task['task_id'], $extra_del).'</li>';
    }
    if(empty($tasks['pending'])){
        echo "<strong id='no_pending'>No pending Tasks!</strong>";
    }
    ?>
</ul>
<p>Completed:</p>
<ul id="completed_tasks">
    <?php
        foreach($tasks['completed'] as $task){
            echo "<li class='task-completed' id='task_{$task['task_id']}'>". checkbox('chk_task_'.$task['task_id'], 'checked class="chk_task"').$task['name'].img_button('edit.svg', 'edit_'. $task['task_id'], $extra_edit) . img_button('del.svg', 'del_'. $task['task_id'], $extra_del).'</li>';
        }
    ?>
</ul>
<template id="pending_task">
    <?php echo "<li class='task-pending' id='task_00id00'>" . checkbox('chk_task_00id00') . span('task_name_00id00','00task_name00'). img_button('edit.svg', 'edit_00id00', $extra_edit) . img_button('del.svg', 'del_00id00', $extra_del) . '</li>'; ?>
</template>
<template id="complete_task">
    <?php echo "<li class='task-completed' id='task_00id00'>" . checkbox('chk_task_00id00') . span('task_name_00id00','00task_name00'). img_button('edit.svg', 'edit_00id00', $extra_edit) .img_button('del.svg', 'del_00id00', $extra_del).'</li>'; ?>
</template>
<template id="edit_task_template">
    <input type="text" class="form-control todo-list-input" id="txt_edit_{{id}}">
    <button id="save_edit_{{id}}" disabled>Save</button>
    <button id="cancel_edit_{{id}}">Cancel</button>
</template>
<template id="empty_list">
    <strong id='no_pending'>No pending Tasks!</strong>
</template>
<?php