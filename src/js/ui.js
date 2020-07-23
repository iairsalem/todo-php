
/*
$(document).ready(function() {
    $(".replaceable").each(function() {
        $this = $(this);
        for (key in config) {
            placeholder = new RegExp("{{" + key + "}}", "g");

            $this.html($this.html().replace(placeholder, config[key]))
        };
    }).css("visibility", "visible");
    $("#loading").hide();
    $("#section1").show();

});

jQuery.cachedScript = function( url, callback, options ) {

    // Allow user to set any option except for dataType, cache, and url
    options = $.extend( options || {}, {
        dataType: "script",
        cache: true,
        url: url,
        success: callback
    });

    // Use $.ajax() since it is more flexible than $.getScript
    // Return the jqXHR object so we can chain callbacks
    return jQuery.ajax( options );
};

$( window ).on("load", function() {
    $.cachedScript("https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js");
    $.cachedScript("https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js");
    $.getScript("https://www.google.com/recaptcha/api.js?render=6LdHDOYUAAAAAM5qJu86aSw1wua1iBeULiD_vMCB", function () {
        set_recaptcha_token();
        setInterval(function () {
            set_recaptcha_token();
        }, 110000);
        $("input").attr("onblur", "setCustomValidity('')");
        $("input").attr("oninput", "setCustomValidity('')");
    });

    var jqxhr = $.post(config['process_form'], make_obj($(this).serializeArray()), function () {
    }, "json")
        .done(function (data) {
            $("#section1").hide();
            $("#section2").hide();
            $("#section3").show();
            response_data = data;
            if ("edit_confirmation" in response_data) {
                $mc = document.getElementById("mensaje_confirmacion");
                $mc.innerHTML = "Formulario editado exitosamente.";
            }
        })
        .fail(function () {
            alert("Ha ocurrido un error, el formulario no ha sido enviado. contáctese con nosotros a la brevedad");
        }).always(function () {
            $("#loading").hide();
        });
});


function set_recaptcha_token(){
    grecaptcha.ready(function () {
        grecaptcha.execute('{{recaptcha_site}}', { action: 'form_venta' }).then(function (token) {
            $("#recaptchaResponse").val(token);
        });
    });
}

*/

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


    //var config = {{config_front}};
/*
var config = {

    "ph_anio": "2020",
    "fecha_limite": "Martes 7 de abril de 2020 23:00 hs",
    "ph_fecha_bedika": "Martes (7/04/2020)",
    "ph_hora_bedika": "19:20 hs",
    "ph_hora_venta": "Miercoles 8 abril 2020, Bs. As: 11:32am",
    "h1_formulario": "Delegación de Venta de Jametz: Completar formulario"
}
 */