// Smartresize
jQuery.noConflict();
!function(e){var r,i=e.event;i.special.smartresize={setup:function(){e(this).bind("resize",i.special.smartresize.handler)},teardown:function(){e(this).unbind("resize",i.special.smartresize.handler)},handler:function(e,i){var s=this,t=arguments;e.type="smartresize",r&&clearTimeout(r),r=setTimeout(function(){jQuery.event.handle.apply(s,t)},"execAsap"===i?0:100)}},e.fn.smartresize=function(e){return e?this.bind("smartresize",e):this.trigger("smartresize",["execAsap"])}}(jQuery);