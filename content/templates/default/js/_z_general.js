// General functions and handlers here
Mod.G = {};

// Doc ready
$(function(){
    Hash.inspect();

    // Submit on enter
    $("body").on("keypress","form input.submit_on_enter",function(e){
        if(e.keyCode == 13){
            $(this).parents("form:first").submit();
        }
    });
    
    // Slim scroll
    $('div.slim_scroll').each(function(){
        $(this).slimscroll({
              color: '#000000',
              size: '7px',
              width: $(this).width(),
              height: $(this).height()+"px",
              alwaysVisible: true       
          });
    });
    
    $(".sys-msg,.error-msg").click(function(){
        $(this).fadeOut("slow");
    });

    // Drop Down combo box
    $("body").on("click","div.select",function(event){
        event.stopPropagation();

        var options = $(this).find("div.options");
        if($(this).hasClass("active")){
            $(options).hide();
            $(this).removeClass("active");
            $(document).unbind('click',Mod.G.ddDocClick);
        }else{
            $("div.select.active").removeClass("active").find(".options").hide();
            $(options).show();
            $(this).addClass("active");
            $(document).bind('click',Mod.G.ddDocClick);
        }

    });
    $("body").on("click","div.select div.options a",function(){
        if($(this).attr("val") != $(this).parent().parent().find("input[type=hidden]").val())
            $(this).parent().parent().trigger("changed",[this,$(this).attr("val")]);

        $(this).parent().prev().html($(this).html());
        $(this).parent().parent().trigger("change",$(this).attr("val"));
        if($(this).parent().parent().find("input[type=hidden]").length <= 0){
            var input = $("<input />").attr("type","hidden").attr("name",$(this).parent().parent().attr("name")).val($(this).attr("val"));
            $(this).parent().parent().append(input);
        }else{
            $(this).parent().parent().find("input[type=hidden]").val($(this).attr("val"));
        }
        $(this).parent().find("a.selected").removeClass("selected");
        $(this).addClass("selected");
        Mod.G.ddDocClick();

        return false;
    });

    // Tab buttons
    $("body").on("click","a.toggle-button",function(){
        if($(this).is("[family]")){
            $("a.toggle-button[family="+$(this).attr("family")+"].active").removeClass("active");
        }
        if($(this).hasClass("active")){
            $(this).removeClass("active");
        }else{
            $(this).addClass("active");
        }
    });

    // Flash messages
    if(Sys.flash != "" && Sys.flash != null){
        if(Sys.flash.json != undefined && typeof Sys.flash.json == "object"){
            Mod.G.inspectJsonResponse(Sys.flash.json);
        }
    }

    $("body").on("change","input.error,textarea.error,select.error",function(){
        $(this).removeClass("error");
        $(this).parent().find("span.error-msg").remove();
    });

    $("input.error,textarea.error,select.error").first().focus();

    // Global helpers
    $("a.login-button").click(function(){
        Mod.G.Frames.login();
        return false;
    });
    $("a.join-button").click(function(){
        Mod.G.Frames.join();
        return false;
    });
    $("a.curso-add").click(function(){
        Mod.G.Frames.curso_add();
        return false;
    });

    // Misc utility initiation
    // Datepicker defaults
    $.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});
    // Checkall
    Misc.checkAll();
    Misc.richForms();
    Misc.docReady();

    // Trigger window resize
    $(window).trigger("resize");

    if(Hash.get("tab"))
        $('#tabs_cont a[tab='+Hash.get("tab")+']').trigger("click");

    // Login form
    $("#head_login_btn").click(function(){
        $.colorbox({
            title: '',
            href: Sys.path.http+"login",
            top: "70px",
            onComplete: function(){
                var $context = $("#login_lb");
                $context.find("a.login").click(function(){
                    $context.find("#login_form").submit();
                    return false;
                });
                $context.find("#login_form").submit(function(){
                    var self = $context.find("a.login");
                    self.html("Espere...");
                    $.post(Sys.path.http+"login.check",$(this).serialize(),function(data){
                        // Pass
                        self.html("Iniciar sesión");
                    },"json");
                    return false;
                });
                $context.find("a.join_link").click(function(){
                    $("#head_join_btn").trigger("click");
                    return false;
                });
            }
        });

        return false;
    });

    // Join form
    $("#head_join_btn").click(function(){
        $.colorbox({
            title: '',
            href: Sys.path.http+"registro",
            top: "70px",
            onComplete: function(){
                var $context = $("#join_lb");
                $context.find("input").first().focus();
                $context.find("a.submit").click(function(){
                    $context.find("#join_form").submit();
                    return false;
                });
                $context.find("#join_form").submit(function(){
                    var self = $context.find("a.submit");
                    self.html("Espere...");
                    $.post(Sys.path.http+"registro.save",$(this).serialize(),function(data){
                        // Pass
                        self.html("Registrarme");
                    },"json");
                    return false;
                });
                $context.find("a.login_link").click(function(){
                    $("#head_login_btn").trigger("click");
                    return false;
                });
            }
        });

        return false;
    });

});

// Global handlers
// Window resize handler
$(window).resize(function(){

});

// Inspect global AJAX responses
$(document).ajaxSuccess(function(e, xhr, settings) {
    try{
        var response = JSON.parse(xhr.responseText);
        Mod.G.inspectJsonResponse(response);
    }catch(e){
        // Pass
    }
});

// Hash change event
$(window).bind('hashchange',function(e){
    if($('#tabs_cont a.tab[tab=' + Hash.get("tab")+']').is(".selected"))
        return false;
    $('#tabs_cont a.tab[tab=' + Hash.get("tab")+']').trigger('click');
});

// Mod.G Global functions
/**
 * This function inspects global JSON responses from the server
 * The JSON object must have the following syntax:
 * {
 *      result: 'ok' | 'error',
 *      sys_error: '[error_code]', // JUST in case result = error
 *      error_msg: '[message]', // In case error message is sent from server
 *      info_msg: '[message]', // In case information message was sent from the server
 *      fields: [{  // This is an object array containing fields that failed validation (just in case sys_error = validation)
 *          name: 'username',
 *          error: 'Username is required'
 *      }],
 *      redirect: 'http://<url>' | 'reload', // Browser needs redirection or reload
 * }
 * @param response
 * @return {Boolean}
 */
Mod.G.inspectJsonResponse = function(response){
    if(typeof response != "object")
        return false;
    // Error messages
    if(response.sys_error != undefined){
        switch(response.sys_error){
            case "access_denied":
                if(!Sys.user.logged)
                    Mod.G.Frames.login();
                else
                    Misc.showErrorMsg("Acceso denegado",5);
                break;
            case "validation":
                if(response.fields.length > 0){
                    for(var i in response.fields){
                        if(response.context != undefined && response.context != "" && response.context != null){
                            var $input = $(response.context).find("[name='"+ response.fields[i].field + "']:not(input[type=hidden])");
                        }else{
                            var $input = $("[name='"+ response.fields[i].field + "']:not(input[type=hidden])");
                        }
                        if($input.is(".error"))
                            continue;
                        $($input).addClass("error").after("<span class='error-msg'>" + response.fields[i].error + "</span>");
                        if(i == 0){
                            $($input).focus();
                        }
                    }
                    $("[name='"+ response.fields[0].field + "']").focus();
                }
                if(response.context != undefined && response.context != "" && response.context != null && response.desc != null && $(response.context).length > 0){
                    if($(response.context).find("div.error-desc").length <= 0){
                        $(response.context).prepend(
                            $("<div></div>").addClass("error-desc")
                        );
                    }
                    $(response.context).find("div.error-desc").html(response.desc).slideDown("fast");
                }else{
                    Misc.showErrorMsg(response.desc);
                }
                break;
            default:
                Misc.showErrorMsg(response.desc);
                break;
        }
    }
    // Success
    if(response.result != undefined && response.result == "ok"){
        // Redirect
        if(response.redirect != undefined && response.redirect != "" && response.redirect != null){
            if(response.redirect == "reload"){
                window.location.reload();
            }else{
                location.href = response.redirect;
            }
        }
        // Msg
        if(response.info != undefined && typeof response.info == "object"){
            if(response.info.timeout != undefined){
                Misc.showInfoMsg(response.info.msg || "",response.info.timeout);
            }else{
                Misc.showInfoMsg(response.info.msg || "");
            }
        }else if(response.info != undefined && typeof response.info == "string"){
            Misc.showInfoMsg(response.info);
        }
    }
}
Mod.G.checkValidationFlash = function(){
    try{
        if(typeof Sys.flash.json == "object")
            Mod.G.inspectJsonResponse(Sys.flash.json);
    }catch(e){
        // Pass
    }
}
// Body clicks
Mod.G.ddDocClick = function(event){
    $("div.select.active").removeClass("active").find(".options").hide();
    $(document).unbind('click',Mod.G.ddDocClick);
}

// Frames
Mod.G.Frames = {};
// Login
Mod.G.Frames.login = function(){
    Misc.toggleFrame("login",{
        parent: "#frame_cont",
        min_parent: "#taskbar",
        title: "Inicia sesión",
        type: "frame",
        width: "auto",
        minimizable: true,
        frame: "#frm_login",
        origin: "center",
        anim_type: "fade",
        modal: true,
        onLoaded: function(frame){
            var self = this;
            frame.find("a.cancel").click(function(){
                self.close();
                return false;
            });
            frame.find("a.join").click(function(){
                self.close();
                Mod.G.Frames.join();
                return false;
            });
            frame.find("form").attr("id",frame.find("form input[name=context]").val().replace(/#/,'')).on("submit",function(){
                frame.find("a.submit").trigger("click");
                return false;
            });
            frame.find("form").append('<input type="hidden" name="tz_offset" value="'+Misc.getTzOffset()+'" />');
            frame.find("a.submit").click(function(){
                self.showLoader();
                $.post(frame.find("form").attr("action"),frame.find("form").serialize(),function(data){
                    if(data.result != "ok")
                        self.hideLoader();
                },"json");
                return false;
            });
            frame.find("input[type=text]").first().focus();
        }
    });
}

// Default settings
$.colorbox.settings.opacity = 0.85;

