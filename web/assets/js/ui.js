(function( factory ) {
    //define.amd
    if ( typeof define === "function" ) {
        // AMD CMD
        define(['$'],factory);
    }
    var $ = window.jQuery || zepto || $;
    if($){
        window.ui = window.ui || factory($);
        $.ui = factory($)
    }
}(function($) {
    var back = false;
    // 由于 垃圾回收
    window.requestAnimationFrame =
        window.requestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        function (callback, element) {
            window.setTimeout(callback, 16);
        };


    var clear = (function(){
        var obj = [];
        var a = function(){
            for(var i in obj){
                if(!obj[i].el.css(obj[i].key) == obj[i].value){
                    obj[i].el.remove();
                    obj.splice(i,1);
                }
            }
            if(!obj.length) return;
            requestAnimationFrame(function(){
                a();
            });
        }
        return function(el , key , value){
            obj.push({'el':el ,'key':key ,'value':value});
            if(obj.length == 1) a();
        }
    }());

    var prefix = (function(){
        var styles = window.getComputedStyle ? window.getComputedStyle(document.documentElement, '') : document.documentElement.style;
        if(styles.transform){
            return {
                'transitionend': 'transitionend',
                'transitionDuration' : 'transitionDuration',
                'transform':'transform'
            }
        }
        var
            pre = (Array.prototype.slice
                .call(styles)
                .join('')
                .match(/-(moz|webkit|ms)-/) || (styles.OLink === '' && ['', 'o'])
            )[1];

        return {
            'transitionend': pre + 'TransitionEnd',
            'transitionDuration' : pre + 'TransitionDuration',
            'transform':pre+'Transform'
        }
    }());

    function loading(v){
        var el = '<div id="___loading" style="position:fixed;width:100%;height:100%;;top:0;left:0;z-index:999999;"><div class="loading circle"></div></div>';
        if(v === null){
            $("#___loading").remove();
        }else{
            $('body').append(el);
        }
    }

    function modal(v , mask){
        if(typeof(v) == 'number')
        {
            mask = v; v = 'body';
        }

        if(arguments.length == 0){ v = 'body' ; mask = 0.4; }
        if(mask === undefined) mask = 0.4;

        if(v === null) {
            $("#___loading").remove();
            loading_delay && window.clearTimeout(loading_delay);
            loading_delay = null;
        }
        else {
            v = v || 'body';
            if($('#___loading').length) return;
            var img = "<div class='m-loading' style='display:block;position:fixed;width:48px;height:48px;left:50%;top:50%;margin-left:-24px;margin-top:-24px;' />";

            var html = '<div id="___loading" style="position:fixed;width:100%;height:100%;background: rgba(0,0,0,{opacity});top:0;left:0;z-index:10000;">'+img+'</div>';
            loading_delay = window.setTimeout(function(){

                $(v).append( html.replace('{opacity}',mask));

                window.clearTimeout(loading_delay);
            },200);

        }
    }

    var remove_alert_handler;
    function _alert(title , content , okfn , okstring){
        if(!content) content = '';
        var uid = +new Date + ''+ Math.round(Math.random() * 1000);
        var html = '<div id="alert_'+uid+'" class="modal-overlay"><div class="modal" style="margin-top: -61px;"><div class="modal-inner"><div class="modal-title">{title}</div><div class="modal-text">{content}</div></div><div class="modal-buttons "><span class="modal-button modal-button-bold"><b lang="l2002">{okstring}</b></span></div></div></div>';
        html = html.replace(/{title}/g,title||'提示')
            .replace(/{content}/g,content)
            .replace(/{okstring}/g,okstring || '确定');

        var el = $(html);

        el.appendTo('body')
            .on('click' , '.modal-button' , function(){
                if(remove_alert_handler) {
                    window.clearTimeout(remove_alert_handler);
                    return;
                }
                el.off('span.modal-button');
                el.addClass('modal-out');
                if(okfn) okfn();
                remove_alert_handler = setTimeout(function(){
                    el.remove();
                    remove_alert_handler = null;
                },400)
            });
        window.requestAnimationFrame(function(){
            el.addClass('modal-in');
        })

    }

    function error(el){
        $(el).html('<div onclick="location.reload();" class="m-error"><i></i><span>网络出错，轻触屏幕重新加载</span></div>');
    }
    return {
        'loading' : loading,
        'forward' : loading,
        'back': function(v){
            back = v
        },
        'modal':modal,
        'alert':_alert,
        'error':error
    }
}))