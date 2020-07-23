<input type="button" id="btn1" value="Button 1">
<input type="button" id="btn2" value="Button 2">
<div id="change_{{id}}">
    <div id="kipe_{{id}}">
        <div id="roll_{{id}}">
        Hola
        </div>
    </div>
</div>
<script>
    function placeholders(html, replacements, open="{{", close="}}"){
        for (key in replacements) {
            placeholder = new RegExp(open + key + close, "g");
            html = html.replace(placeholder, replacements[key]);
            //$this.html($this.html().replace(placeholder, replacements[key]));
        }
        return html;
    }

    function change_id(el, id_replace){
        console.log(el);
        if(el.id){
            $el = $(el);
            el.id = placeholders(el.id, {id: id_replace});
            if($el.children().length>0){
                $el.children().each(function(){
                    change_id(this, id_replace);
                });
            }
        }
    }
    $(document).ready(function(){
        $("#btn2").click(function(){
            console.log("mejshi");
            let url = {
              url: "/kipe/",
              method:"post"
            };
            $.getJSON(url, {hello:"world"}, null);
        });

        $("#btn1").click(function(){
            $("#btn2")[0].id = "btn3";
            console.log("btn2 is now btn3. does it print mejshi?");
        });
        let el = document.getElementById("change_{{id}}");
//        let el = $("#change_{{id}}")[0];
        change_id(el, 99);
    });

</script>

