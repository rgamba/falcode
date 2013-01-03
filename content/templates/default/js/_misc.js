/**
* BM Framework
*/
var Mod = {}; // Modules namespace
var Misc = {}; // Miscenallenous namespace
/**
* Mascara de entrada para los textbox
*/
Misc.setMaskInputs = function(){    
    $("input[mask]").each(function(){
        if($(this).data("masked")==true)
            return;
        if($(this).attr("mask")!=undefined && $(this).attr("mask")!=""){
            $(this).data("mask",true);
            $(this).keypress(function(event){
                if(event.charCode==0)
                    return true;
                if(this.selectionStart!=this.selectionEnd)
                    $(this).val("");
                var value=$(this).val()+String.fromCharCode(event.charCode);
                if(event.charCode==0)
                    return true;
                var sel=selText();

                if(sel.length>0){
                    $(this).val("");
                }
                var regex=eval("/^"+$(this).attr("mask")+"$/g");
                if(!regex.test(value))
                    return false; 
            })
        }
    });
}

/**
* Habilita el alt del textbox
*/
Misc.enableAlt = function(){
    $("input[alt!='']").each(function(){
        var caption=$(this).attr('alt');
        if(caption!='' && caption!=null){
            if($(this).attr('value')=='' || $(this).attr('value')==null){
                $(this).attr('value',caption);
                $(this).css('color','#cccccc');
            }
            $(this).focus(function(){
                if($(this).attr('value')==caption){
                    $(this).attr('value','');
                    $(this).css('color','black');
                }
            });
            $(this).blur(function(){
                if($(this).attr('value')==''){
                    $(this).css('color','#cccccc');
                    $(this).attr('value',caption);
                }    
            });
        }
    });
}

/**
* Esta funcion se ejecutara cada ves que el campo
* de tipo input tenga una regla de validacion y la regla
* no se haya cumplido
* - Esta funcion puede personalizarse para desplegar otro tipo de error -
*/
Misc.displayError = function(errors){
    $.jGrowl.defaults.position = 'top-right';
    for(var i in errors){
        $(errors[i].element).addClass("validate_error");
        $(errors[i].element).tipsy({
            fallback:  errors[i].msg,
            gravity: 'w',
            trigger: 'manual'
        }).tipsy("show");
        $(errors[i].element).change(function(){
            $(this).tipsy("hide");
            $(this).removeClass("validate_error");
        })
    }
    errors[0].element.focus();
}

Misc.toCheckbox = function(){
    $("input[check]").toCheckbox();
}
/**
* Tooltip
*/
Misc.toolTip = function(obj,tip){
    opener=obj.id;
    tt=document.getElementById("tooltip");
    $("#tooltip").toggle("fast");
    if(tip!=''){
        tt.style.left=(getElementLeft(opener)+getElementWidth(opener)+10)+'px';
        tt.style.top=(getElementTop(opener)+5)+'px';
        tt.innerHTML=tip;
        $("#tooltip").animate({opacity: "show", top: "+=10"},"fast");
    }else{
        $("#tooltip").animate({opacity: "hide", top: "-=10"},"fast");
    }
}
/**
* Abre mensaje jgrowl
*/
Misc.popMsg = function(msg,header){
    if(header==undefined)
        header="Error";
    $.jGrowl(msg,{
        header:header, 
        life: 5000
    });
}
/**
* AJAX Forms
*/
/**
* Funcion para capturar los formularios por GET
* y procesar las variables para enviarlas en formato
* correcto de url phplus.
* También verifica que el formato de cada input sea el correcto en caso 
* de que tenga una regla de validación desde el HTML, y tambien hablilita
* el envio de formularios por medio de ajax
*/
Misc.richForms=function(){
    $(document).on("submit","form",function(event){
        var obj=$(this);
        var uri="";
        var ret=true;
        var errors = [];
         $(this).find("input[type=text],input[type=password],textarea,input[type=hidden],select").each(function(i){
             var self = this;
            if($(this).attr("type")=="hidden"){
                uri+=this.name+','+this.value+"/";
                return true;
            }
            // Si tiene alt y el valor es alt, esta vacio
            if($(this).val()==$(this).attr("alt")){
                $(this).val("");
            }
            if($(this).is(':hidden') || $(this).is(':hiddenByParent'))
                return;
            if(($(this).attr('validate')=="no-empty" || $(this).attr('validate')=="") && this.value==""){
                errors.push({
                    element : self,
                    msg : ($(self).attr('error')!="" && $(self).attr('error')!=null ? $(self).attr("error") : "Campo obligatorio")
                });
            }else if($(this).attr('validate')=="email"){
                // Validamos que sea email
                var emailre=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if(!emailre.test($(this).attr('value'))){
                    errors.push({
                        element : self,
                        msg : ($(self).attr('error')!="" && $(self).attr('error')!=null ? $(self).attr("error") : "Dirección de correo inválida")
                    });
                }
            }else if($(this).attr('validate')=="number"){
                // Validamos campo de tipo numerico
                if(isNaN($(this).attr('value')) || $(this).attr('value')==""){
                    errors.push({
                        element : self,
                        msg : ($(self).attr('error')!="" && $(self).attr('error')!=null ? $(self).attr("error") : "Ingresar un valor numérico")
                    });
                }
            }else if($(this).attr('validate')=="check-with"){
                // Verifica que el valor del campo coincida con el valor del selector en el attr target
                if($(this).val() != $($(this).attr("target")).val()){
                    errors.push({
                        element : self,
                        msg : ($(self).attr('error')!="" && $(self).attr('error')!=null ? $(self).attr("error") : "Los campos no coinciden")
                    });
                }
            }else if($(this).attr('validate')=="custom" || $(this).attr('validate')=="regex"){
                // Validamos la RegEx
                if($(this).attr('regex')!="" && $(this).attr('regex')!=null){
                    var filter=eval("/"+$(this).attr('regex')+"/");
                    if(!filter.test($(this).attr('value'))){
                        errors.push({
                            element : self,
                            msg : ($(self).attr('error')!="" && $(self).attr('error')!=null ? $(self).attr("error") : "Formato de entrada incorrecto")
                        });
                    }
                }
            }else if($(this).attr('validate')=="custom-or-empty" || $(this).attr('validate')=="regex-or-empty"){
                // Validamos la RegEx
                if($(this).attr('regex')!="" && $(this).attr('regex')!=null){
                    if($(this).val()!=""){
                        var filter=eval("/"+$(this).attr('regex')+"/");
                        if(!filter.test($(this).attr('value'))){
                            errors.push({
                                element : self,
                                msg : ($(self).attr('error')!="" && $(self).attr('error')!=null ? $(self).attr("error") : "Formato de entrada incorrecto")
                            });
                        }
                    }
                }
            }
            uri+=this.name+','+this.value+"/";
        });
        if(errors.length > 0){
            event.stopPropagation();
            Misc.displayError(errors);
            return false;
        }
        if(ret==true){
            // Envio mediante AJAX   
            if($(this).attr("rel")=="ajax"){
                var action=$(this).attr("action");
                if(action=="" || action==undefined)
                    action=document.location.href;
                if($(this).attr("enctype")=="multipart/form-data"){
                    // Tenemos archivos adjuntos
                    // Creamos iframe
                    showLoader();
                    var callback=$(this).attr("callback");
                    var fname="tmp_ajax_form";
                    if($("#"+fname).length==0){
                        $("<iframe></iframe>")
                            .hide()
                            .attr("src","about:blank")
                            .attr("id","tmp_ajax_file_uploader")
                            .attr("name",fname)
                            .attr("id",fname)
                            .appendTo("body");
                    }
                    $(this).attr("target",fname);
                    var io=document.getElementById(fname);
                    var response="";
                    // Event listener para el callback
                    var self=this;
                    var uploadCallback=function(){
                        if(io.contentWindow){
                            response=io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;  
                        }else if(io.contentDocument){
                            response=io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
                        }else{
                            response=null;
                        }
                        if($(self).attr("callback")!=undefined && $(self).attr("callback")!="")
                            eval($(self).attr("callback"));
                        hideLoader();
                    }
                    if(window.attachEvent){
                        io.attachEvent('onload',uploadCallback);
                    }else{
                        io.addEventListener('load',uploadCallback,false);
                    }
                    return true;
                }else{
                    var self=this;
                    showLoader();
                    $.post(action,$(this).serialize(),function(){
                        if($(self).attr("callback")!=undefined && $(self).attr("callback")!="")
                            eval($(self).attr("callback"));
                        else
                            popMsg("Registro guardado","Información");    
                        hideLoader();
                    });
                    return false;
                }
            }
        
            //if($(this).attr("method").toLowerCase()=="post")
                return true;
            if(ret)
                window.location.href=uri;
            return false;
        }
    });
}
/**
* Selecciona elemento de select
*/
Misc.selectCurrent = function(){
    $("select[current]").each(function(obj){
        var value=$(this).attr("current");
        $(this).children().each(function(child){
            if($(this).attr("value")==value){
                $(this).attr("selected","selected");
            }
        });
    });
}
/**
* Tooltips plugin
*/
Misc.toolTips = function(){
    $("input[tooltip],label[tooltip],span[tooltip],textarea[tooltip],a[tooltip],img[tooltip],div[tooltip]").each(function(){
         var v_gravity=($(this).attr("gravity")=="" || $(this).attr("gravity")==undefined) ? "sw" : $(this).attr("gravity");
         if(v_gravity=="auto")
            v_gravity=$.fn.tipsy.autoNS;
         var v_trigger=($(this).attr("trigger")=="" || $(this).attr("trigger")==undefined) ? "hover" : $(this).attr("trigger");    
         $(this).tipsy({
                title:'tooltip',
                gravity:v_gravity,
                trigger: v_trigger,
                fade: false,
                delayIn: 250,
                html:true
         });  
    });  
}
/**
* Seleccionar todos los checkboxes
*/
Misc.checkAll = function(){
    $("input[type=checkbox].checkall").click(function(){
        $(this).parents('form').find(':checkbox').attr('checked', this.checked);
    });
}

Misc.getTzOffset = function(){
    var date = new Date();
    return date.getTimezoneOffset()*60*-1;
}

/*
* AjaxLinks
*/
Misc.ajaxLinks = function(){ 
    $("a[rel]").ajaxLink();
}
Misc.ajaxLinkClick = function(obj){
    var target=$(obj).attr("tar");
    if(target=="" || target==undefined || target==null){
        target="#tab_cont_ajax";
    }
    if(target=="#tab_cont_ajax"){
        $("#tab_main_cont").hide(); // Ocultamos tabs normales
    }
    this.showLoader();
    if($(target).length>0){
        var self=obj;
        $(target).load($(obj).attr("href"),function(){
            if($(self).attr("callback")!="" && $(self).attr("callback")!=undefined){
                eval($(self).attr("callback"));
            }
            this.hideLoader();
            if(target=="#tab_cont_ajax"){   
                $("#tab_cont_ajax").css("display","block");
            }
            this.docReady();
        });
    }
    return false;
}

/**
* LLama a todas las funciones necesarias para emular el document load
* cuando se carga una pagina via ajax
*/
Misc.docReady = function(){
    this.datepickers();
    this.ajaxLinks();
    this.enableAlt();
    this.selectCurrent();
    this.toolTips();
    this.setMaskInputs();
    this.toCheckbox();
}

Misc.loading_timer;
Misc.loading_counter;
Misc.showLoader = function(modal){
    if($("#loading_box").is(":visible"))
        return;
    if($("#loading_box").length==0){
        $("body").append($("<div></div>").attr("id","loading_box").show());
        $("#loading_box").css("top",($(window).height()/2)-($("#loading_box").height()/2));
        $("#loading_box").css("left",($(window).width()/2)-($("#loading_box").width()/2));
    }else{
        $("#loading_box").show();    
    }
    if(modal==true)
        $("#modal").show();
    this.loading_counter=1;
    this.loading_timer=setInterval("Misc.loaderTimer()",100);
}
Misc.loaderTimer = function(){
    if(this.loading_counter>=12)
        this.loading_counter=1;
   
    $("#loading_box").css("background-position","0 "+((this.loading_counter*40)*-1)+"px");
    this.loading_counter++;
}
Misc.hideLoader = function(){
    clearInterval(this.loading_timer);
    this.loading_counter=1;
    $("#loading_box").css("background-position","0 0");
    $("#loading_box").hide();
    //$("#loading_box").remove();
    $("#modal").hide();
    $("#loading_box").remove();
}
/**
* Obtener variables enviadas despues de hashtag (depreciada)
*/
Misc.getVars = function(){
    var get={};
    var url=document.location.href;
    if(url==undefined || url=="")
        url=window.location.href;
    if(url.indexOf('?')>=0){
        url=url.split("?");
        url=url[1];
        url=url.replace('#','');
        url=url.split("&");
        window.get={};
        for(var i in url){
            var kp=url[i].split("=");
            if(kp.length==1){
                window.get.tab=kp[0];
            }else{
                eval("window.get."+kp[0]+"='"+kp[1]+"';");
            }
        }
    }
    return window.get;
}
/**
* Instala los datepickers en textboxes
*/
Misc.datepickers = function(){
    $("input[type=text].date").each(function(i){
        $(this).datepicker();
        format=$(this).attr('format');
        if(format=='')
            format='yy-mm-dd';
        $(this).datepicker('option', {dateFormat: format});
    });
}

Misc.showErrorMsg = function(msg,timeout){
    $("div.sys-error,div.sys-info").remove();
    var err = $("<div></div>").addClass("sys-error").html(msg).append($("<a></a>").attr("href","#").addClass("close").click(function(){
        $(this).parent().remove();
        return false;
    })).hide();
    $("body").prepend(err);
    if(timeout != undefined){
        setTimeout(function(){
            err.slideUp("fast",function(){
                $(this).remove();
            })
        },timeout*1000);
    }
    err.slideDown("fast");
}
Misc.hideErrorMsg = function(){
    $("div.sys-error").remove();
}
Misc.showInfoMsg = function(msg,timeout){
    $("div.sys-error,div.sys-info").remove();
    var err = $("<div></div>").addClass("sys-info").html(msg).append($("<a></a>").attr("href","#").addClass("close").click(function(){
        $(this).parent().remove();
        return false;
    })).hide();
    $("body").prepend(err);
    if(timeout != undefined){
        setTimeout(function(){
            err.slideUp("fast",function(){
                $(this).remove();
            })
        },timeout*1000);
    }
    err.slideDown("fast");
}
Misc.hideInfoMsg = function(){
    $("div.sys-info").remove();
}
Misc.url = function(str){
    // TODO
}
Misc.toggleFrame = function(id,vars){
    if(id==undefined){
        id="tmp";
    }
    var openMin=arguments[2];
    if(window.jframes==undefined)
        window.jframes={};
    if(window.jframes[id]!=undefined && window.jframes[id].opened==true){
        if(vars==undefined){
            window.jframes[id].close();
            return;
        }else{
            window.jframes[id].close(function(){
                delete window.jframes[id];
                toggleFrame(id,vars);
            })
            return;
        }
    }
    var fixed_top=150;
    var step=30;
    var top=fixed_top;
    if(window.jframes!=undefined && typeof window.jframes=="object"){
        var left_m=fixed_top;
        var right_m=fixed_top;
        for(x in window.jframes){
            if(window.jframes[x]!=undefined){
                if(window.jframes[x].params.origin=="left")
                    left_m+=step;
                else
                    right_m+=step;
            }
        }
        if(vars.origin=="left"){
            top=left_m;
        }else{
            top=right_m;
        }
    }
    if(vars.origin=="center")
        vars.anim_type="fade";
    window.jframes[id]=new jFrame({
        title: vars.title,
        script: (vars.script!=undefined ? PATH_JS+vars.script : undefined),
        //icon: PATH_IMG+vars.icon,
        type: (vars.url!=undefined ? "ajax" : "frame"),
        frame: vars.frame,
        url: vars.url,
        width: vars.width,
        height: vars.height,
        position: "fixed",
        minimizable: vars.minimizable,
        modal: vars.modal,
        onCloseClick:function(){
            if(typeof vars.onCloseClick=="function")
                vars.onCloseClick.call();
        },
        onCreated: function(frame){
            var top=1;
            for(x in window.jframes){
                if(parseInt(window.jframes[x].frame.css("z-index"))>top)
                    top=parseInt(window.jframes[x].frame.css("z-index"));

            }
            if(top<100)
                top+=100;
            frame.css("z-index",top+1);
            if(typeof vars.onCreated=="function")
                vars.onCreated.call(this,frame);
            if(openMin==true){
                this.frame.hide();
            }
            // Guardamos la ventana en sesion
            if(vars.modal==true)
                $("#modal").css('z-index',top+1);
            if(vars.minimizable==false || vars.modal==true) return;
            $.post(Sys.path.http+'ui.frame_open/val,'+id+'/',{'params':JSON.stringify((vars))});
        },
        onLoaded: function(frame,jvars){
            if(typeof vars.onLoaded=="function")
                vars.onLoaded.call(this,frame,jvars);
            if(openMin==true){
                this.minimize();
            }
        },
        onClosed: function(){
            delete window.jframes[id];
            if(typeof vars.onClosed=="function")
                vars.onClosed.call();
            // Borramos la ventana de sesion
            if(vars.minimizable==false || vars.modal==true) return;
            $.post(Sys.path.http+'ui.frame_close/val,'+id+'/');
        },
        onClick: function(frame){
            // Ponemos el frame hasta arriba
            var top=1;
            for(x in window.jframes){
                if(parseInt(window.jframes[x].frame.css("z-index"))>top)
                    top=parseInt(window.jframes[x].frame.css("z-index"));
            }
            if(top<100)
                top+=100;
            frame.css("z-index",top+1);
        },
        onRestore: function(frame){
            var top=1;
            for(x in window.jframes){
                if(parseInt(window.jframes[x].frame.css("z-index"))>top)
                    top=parseInt(window.jframes[x].frame.css("z-index"));
            }
            if(top<100)
                top+=100;
            frame.css("z-index",top+1);
        },
        tabs:(vars.tabs!=undefined && vars.tabs.length>0 ? vars.tabs : []),
        buttons: (vars.buttons!=undefined && vars.buttons.length>0 ? vars.buttons : []),
        parent: "#frame_cont",
        top: top,
        origin: vars.origin,
        min_parent: "#taskbar",
        anim_type: vars.anim_type,
        modal_close_on_click: vars.modal_close_on_click,
        speed: (vars.origin=="center" ? "fast" : undefined)
    })
    window.jframes[id].open();
}

// Lang function misc
Lang.get = function(){
    var key = arguments[0];
    var string = Lang[key];
    for(var i = 1; i <= arguments.length - 1; i++){
        string = string.replace("%"+i,arguments[i]);
    }
    return string;
}

/**
* Hash functions
*/
var Hash = {};
Hash.hashvars = {};
Hash.inspect = function(){
    if(window.location.hash == undefined || window.location.hash == "")
        return false;
    var hash=window.location.hash.replace('#','').split("&");
    for(var i in hash){
        var pair=hash[i].split("=");
        this.hashvars[pair[0]] = pair[1];
    }
}
Hash.add = function(name,val){
    this.hashvars[name]=val;
}
Hash.append = function(name,val){
    this.add(name,val);
    this.set();
}
Hash.remove = function(name){
    delete this.hashvars[name];
}
Hash.del = function(name){
    this.remove(name);
    this.set();
}
Hash.set = function(){
    var hash=[];
    for(var name in this.hashvars){
        hash.push(name+"="+this.hashvars[name]);
    }
    window.location.hash=(hash.length>0 ? "#" : "")+hash.join("&");
}
Hash.get = function(name){
    var hash=window.location.hash;
    hash = hash.replace('#','').split("&");
    for(var i in hash){
        var pair=hash[i].split("=");
        if(pair[0]==name)
            return pair[1];
    }
    return false;
}
Hash.getAll = function(){
    var hash=window.location.hash.replace('#','').split("&");
    var all={};
    for(var i in hash){
        var pair=hash[i].split("=");
        all[pair[0]]=pair[1];
    }
    return all;
}
/**
 * Agrega todo el hash actual a un input hidden y lo agrega a un formulario
 * de manera que el hash sea accesible desde el backend
 * @param selector jquery selector del formulario al que se va a agregar el input
 */
Hash.addToForm = function(selector){
    var hash=[];
    for(var name in this.hashvars){
        hash.push(name+"="+this.hashvars[name]);
    }
    var uri = hash.join("&");
    var $sel = $(selector);
    if($sel.find("input[name='__hash']").length > 0){
        $sel.find("input[name='__hash']").val(uri);
    }else{
        $sel.append(
            $('<input />').attr("type","hidden").attr("name","__hash").val(uri)
        );
    }
}

Misc.ckeditorBar = [
    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
    { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Blockquote',
        '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
    { name: 'links', items : [ 'Link','Unlink','Anchor' ] },
    { name: 'styles', items : [ 'Format' ] },
    { name: 'colors', items : [ 'TextColor' ] }
];