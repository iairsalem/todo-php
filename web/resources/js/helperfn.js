function placeholders(html, replacements, open="{{", close="}}"){
    let placeholder = null;
    for (let key in replacements) {
        placeholder = new RegExp(open + key + close, "g");
        html = html.replace(placeholder, replacements[key]);
    }
    return html;
}

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

function has_changed(arr, debug=false){
    for (let key in arr){
        if(debug){
            console.log(key);
        }
        if(arr[key]){
            if(debug){
                console.log("has changed: " + key);
            }
            return true;
        }
    }
    return false;
}


function find_index_purge(arr, key, needle){
    if(!arr){
        return -1;
    }
    for(let i in arr){
        if(!arr[i]){
            delete arr[i];
            continue;
        }
        if(arr[i] && arr[i][key] == needle){
            return i;
        }
    }
    return -1;
}