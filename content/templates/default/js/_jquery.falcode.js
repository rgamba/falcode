/**
* Plataforma Linker
* ---
* Funciones y extensiones para jQuery
* @uses Libreria jQuery 
* ---
*/
jQuery.expr[':'].hiddenByParent = function(a) { 
   return jQuery(a).is(':hidden') && jQuery(a).css('display') != 'none'; 
};
/**
* Habilita los links para poder cargar html dentro de un contenedor
* por medio de ajax.
*/
window.phplus_cur_ajax=null;
window.phplus_cur_tar=null;
jQuery.fn.ajaxLink=function(){
    $(this).each(function(){
        if($(this).data("parsed")==true)
            return;
        if($(this).attr("rel")!="ajax")
            return;
        $(this).removeAttr("rel");
        // LLamada ajax
        $(this).click(function(){
            var target=$(this).attr("tar");
            if(target=="" || target==undefined || target==null){
                target="#tab_cont_ajax";
            }
            if(target=="#tab_cont_ajax"){
                $("#tab_main_cont").hide(); // Ocultamos tabs normales
            }
            if($(target).length>0){
                if(window.phplus_cur_ajax!=null){
                    if(window.phplus_cur_tar==target)
                        window.phplus_cur_ajax.abort();
                }
                window.phplus_cur_tar=target;
                Misc.showLoader();
                var self=this;
                var url=$(this).attr("href");
                $(target).html("<div class='ajax_loader'></div>");
                eval('var post = '+($(this).attr("post")=="" || $(this).attr("post")==undefined ? 'null' : $(this).attr("post"))+';');
                window.phplus_cur_ajax=$.ajax({
                    url: url,
                    type: (post==null ? 'GET' : 'POST'),
                    data: post,
                    success: function(html){
                        $(target).html(html);
                        $(target).data("url",url);
                        if($(self).attr("callback")!="" && $(self).attr("callback")!=undefined){
                            eval($(self).attr("callback"));
                        }
                        if(target=="#tab_cont_ajax"){   
                            $("#tab_cont_ajax").css("display","block");
                        }
                        if($(this).attr("callback")!=undefined && $(this).attr("callback")!=""){
                            eval($(this).attr("callback"));
                        }
                        if($(this).attr("update")!="false"){
                            Misc.docReady();
                        }
                        Misc.hideLoader();
                    }
                });
                /*
                $(target).load(url,function(){
                    $(target).data("url",url);
                    if($(self).attr("callback")!="" && $(self).attr("callback")!=undefined){
                        eval($(self).attr("callback"));
                    }
                    if(target=="#tab_cont_ajax"){   
                        $("#tab_cont_ajax").css("display","block");
                    }
                    if($(this).attr("callback")!=undefined && $(this).attr("callback")!=""){
                        eval($(this).attr("callback"));
                    }
                    if($(this).attr("update")!="false"){
                        docReady();
                    }
                    hideLoader();
                });*/
            }
            return false;
        });
        // Si ya parseamos el anchor, lo apuntamos
        $(this).data("parsed",true);
    });
}
/**
* Recarga el contenedor cuyo contenido fue cargado mediante un link de la función
* ajaxLink()
*/
jQuery.fn.reload=function(){
    var callback=arguments[0];
    var target=$(this[0]);
    if(target.data("url")==undefined || target.data("url")=="")
        return false;
    
    showLoader();
    target.load(target.data("url"),function(){
        hideLoader();
        if(typeof callback=="function")
            callback();
        docReady();
    });
    return;    
}
/**
* Convierte el input en checkbox
*/
jQuery.fn.toCheckbox=function(){
    $(this).each(function(i){
        var hiddentmp = $(document.createElement("input"));
            $(hiddentmp).attr("value","0");
            $(hiddentmp).attr("type","hidden");
            $(hiddentmp).attr("name",$(this).attr("name"));  
        var checktmp = $(document.createElement("input"));
            $(checktmp).attr("value",$(this).attr("default"));
            $(checktmp).attr("type","checkbox");
            $(checktmp).attr("name",$(this).attr("name"));
        if($(this).attr("value") == $(this).attr("default"))
            $(checktmp).attr("checked","checked");
            
        var contenedor = $(this).parent();
            $(contenedor).append($(hiddentmp));
            $(contenedor).append($(checktmp));
            $(this).remove();
    });
}
/**
* Establece el formato de entrada para un campo de texto
* en formato de regex.
* Se puede enviar el regex en el atributo "mask" del objeto
* o bien se puede enviar como parametro al momento de instanciar la funcion
* puede ser un objeto RegEx o un string.
*/
jQuery.fn.mask=function(){
    var args=arguments[0] || null;
    $(this).each(function(){
        if($(this).data("masked")==true)
            return;
            
        if($(this).attr("mask")!=undefined && $(this).attr("mask")!="")
            var regex=new RegExp("^"+$(this).attr("mask")+"$","g");
        else
            var regex=null;
        if(regex==null && args!=null){
            if(typeof args=="object"){
                regex=args;
            }else{
                regex=new RegExp(args,"g");
            }
        }
        if(regex==null)
            return false;
        $(this).data("masked",true);
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
            if(!regex.test(value))
                return false; 
        })
    });
}
/**
* Agrega imagen de icon dependiendo la extension
*/
jQuery.fn.fileIcon=function(){
    $(this).each(function(){
        if(this.hasIcon!=true){
            var file=$(this).text();
            file=file.split('.');
            var ext=file[file.length-1];
            var icon='';
            switch(ext.toLowerCase()){
                case 'gif':
                case 'jpg':
                case 'png':
                case 'jpeg':
                    icon='image';
                    break;
                case 'doc':
                case 'docx':
                    icon='word';
                    break;
                case 'xls':
                case 'xlsx':
                    icon='excel';
                    break;
                case 'ppt':
                case 'pptx':
                    icon='power_point';
                    break;
                case 'mpg':
                case 'avi':
                    icon='video';
                    break;   
                case 'pdf':
                    icon='pdf';
                    break;
                case 'zip':
                case 'rar':
                    icon='zip';
                    break;
                default:
                    if($(this).hasClass("folder")){
                        icon="folder-closed";
                    }else
                        icon='unknown';
                    break;
            }
            icon=PATH_IMG+'icon_'+icon+'.png';
            icon_tag=$('<img />');
            $(icon_tag).attr("src",icon);
            $(icon_tag).css("margin-right","3px");
            //$(this).prepend(icon_tag);
            $(this).css({
                'padding-left':'23px',
                'background':'transparent url('+icon+') no-repeat 0px center'
            });
            this.hasIcon=true;
        }
    })    
}
/**
* Misma que load() pero el envio se hace por post en vez de por get
*/
jQuery.fn.loadPost=function(){
    var url=arguments[0] || null;
    var params=arguments[1] || {};
    var callback=arguments[2] || false;
    if(callback!=false)
        if(typeof callback!="function")
            callback=false;
            
    if(url==null)
        return false;
    if(this.length==0)
        return false;
    $(this).each(function(){
        var obj=this;
        $.post(url,params,function(data){
            $(obj).html(data); 
            if(callback!=false)
                callback(data);
        });
    })
}
/**
* Funciones de eventos para Mobile Safari
*/
jQuery.fn.touchstart=function(){
    var callback=arguments[0];
    $(this).each(function(){
        this.addEventListener("touchstart",function(event){
            if(typeof callback=="function")
                callback(event);  
        },false);  
    })
    return;    
}
jQuery.fn.touchmove=function(){
    var callback=arguments[0];
    $(this).each(function(){
        this.addEventListener("touchmove",function(event){
            if(typeof callback=="function")
                callback(event);  
        },false);  
    })
    return;    
}
jQuery.fn.touchend=function(){
    var callback=arguments[0];
    $(this).each(function(){
        this.addEventListener("touchend",function(event){
            if(typeof callback=="function")
                callback(event);  
        },false);  
    })
    return;     
}
