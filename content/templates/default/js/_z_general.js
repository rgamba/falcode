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
    
    $(".sys-msg,.error-msg").click(function(){
        $(this).fadeOut("slow");
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



    // Misc utility initiation
    // Datepicker defaults
    if($.datepicker)
        $.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});

    // Uncomment to enable the extra functions available in Misc.js
    //Misc.richForms();
    //Misc.docReady();

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

// Default settings
$.colorbox.settings.opacity = 0.85;

