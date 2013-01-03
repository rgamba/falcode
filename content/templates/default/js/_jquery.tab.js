$.tabs = function(selector, start) {

    $(selector).each(function(i, element) {
        $($(element).attr('tab')).css('display', 'none');
        $(element).click(function() {
            $(selector).each(function(i, element) {
                $(element).removeClass('selected');
                $($(element).attr('tab')).css('display', 'none');
            });
            
            $(this).addClass('selected');
            
            $($(this).attr('tab')).css('display', 'block');
            
            Hash.append("tab",$(element).attr('tab').replace('#',''));
            return false;
        });
    });
    if (!start) {
        start = $(selector + ':first').attr('tab');
    }
    // Tabs
    if(Hash.get("tab")){
        $(selector + '[tab=\'#' + Hash.get("tab") + '\']').trigger('click'); 
    }else{
        $(selector + '[tab=\'' + start + '\']').trigger('click');   
    }
    
    $(window).bind('hashchange',function(e){
        $(selector + '[tab=\'#' + Hash.get("tab") + '\']').trigger('click'); 
    });
};