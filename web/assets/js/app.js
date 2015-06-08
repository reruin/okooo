/**
 * Created by Administrator on 2015/6/7 0007.
 */

var app = (function(){

    function init(){
        $('body').on('click' , 'a.option' , function(){
            var m = $(this).attr('rel');
            var target = $(this).attr('data-for');
            var pre = '<span class="left">←</span><span class="remove">×</span>';
            var h = "<li>" + pre + $('#t-'+m).html() + "</li>";
            if(target.charAt(0) !="#"){
                $(this).parent().nextAll('.' + target).append(h);
            }else
                $( target).append(h);
        })

        $('body').on('click' , 'span.remove' , function(){
            $(this).parent().remove();
        })
        $('body').on('click' , 'span.left' , function(){
            var p = $(this).parent();
            var el = p.prev();
            if(el.length) el.before(p);
            //$(this).parent().remove();
        })

        $('body').on('click' , 'span.right' , function(){
            var p = $(this).parent();
            var el = p.next();
            if(el.length) el.after(p);
            //$(this).parent().remove();
        })
        $('#j-calc').on('click' , function(){
            if($(this).attr('rel') == 'calc') calc();
            if($(this).attr('rel') == 'analyse') analyse();

        })

        $('body').on('click' , '.checkbox a' , function(){
            $(this).toggleClass('select');
        })

    }

    /**
     * 链接查询字串
     * @returns {string}
     */
    function join(con , sp){
        con = con || $('#j-condition li');
        var sql = [];
        for(var i=0;i<con.length ; i++){
            var el = $(con[i]).find('select,input,textarea');
            var seg = "";
            for(var j = 0 ;j<el.length ; j++){
                if(el[j].tagName == 'A')
                    seg += $(el[j]).attr('data-value');
                else
                    seg += $(el[j]).val().replace("\n"," ");
            }
            sql.push(seg);
        }
        sp = sp || " ";
        return sql.join(sp);
    }

    function joinkey(el , fn , args , tonumber){
        if(!fn) fn = "val";
        var arr = [];
        for(var i=0;i<el.length;i++){
            if(el[i].tagName == 'A')
                arr[i] = $(el[i]).attr('data-value');
            else
                arr[i] = $(el[i])[fn]();
            if(tonumber){arr[i] = parseFloat(arr[i]);}
        }
        return arr;
    }

    function calc(){
        var sql = [], bet = [],res = [];

        var el = $("#j-model>li");

        for(var i=0;i<el.length ; i++){
            sql.push( join( $(el[i]).find('.j-condition li') ) );
            bet.push( join( $(el[i]).find('.j-bet') ) );
            res.push(joinkey( $(el[i]).find('.j-bet .checkbox a.select')).join(''));
        }

        console.log(sql);
        console.log(bet);
        console.log(res);
        //return;


        ui.loading();
        $.getJSON('../api/?m=model&a=calc',{
            'where':JSON.stringify(sql),
            'bet':JSON.stringify(bet),
            'result':JSON.stringify(res)
        },function(d){
            d = d.detail;
            console.log(d);
            var ret = 0, ret_all = 0, num = 0;
            var count = d[0].count;
            for(var i in d){
                ret += d[i].r;
                ret_all += d[i].r * count;
            }
            $('#j-return').html(ret);
            $('#j-return-all').html(ret_all);
            $('#j-num').html(d[0].count+"("+ (d[0].p*100).toFixed(2) +"%)");
            ui.loading(null);
        })

    }

    /**
     * 统计 绘制图形
     * @param d：分布数组
     * @param a：类型数字
     */
    function draw(d , a){
        var al = coll(d , a);
        console.log(al)
        var obj = {
            chart: { type: 'area' , zoomType:"x" },
            title: { text: '概率分布' },
            yAxis: {
                title: { text: '概率分布(%)' }
            },
            //tooltip: { formatter:function(){ return this.y +'% 概率 出现 '+ this.x.toFixed(2);} },
            plotOptions: {
                area: {
                    /*pointStart: 0,*/pointInterval:0.1,
                    marker: {
                        enabled: false,
                        symbol: 'circle',
                        radius: 1,
                        states: {
                            hover: {
                                enabled: true
                            }
                        }
                    }
                }
            },
            series: al
        }

        $('#j-result').highcharts(obj);
    }

    /**
     * 原始数据转换
     * @param data
     * @param order
     * @returns {Array}
     */
    function coll(data , order){
        var yas = [], count;
        var l = order.length;
        for(var j =0 ;j<l ; j++){
            var d = data[j];
            var ya = [];
            var count = 0;
            for(var i in d){
                var cur = parseInt(d[i].c);
                count += cur;
                if(d[i].v != 'null' && d[i].v != null){

                    var v = parseFloat(parseFloat(d[i].v).toFixed(1)) * 10;
                    if(v > 99) v = 99;
                    if(ya[v] === undefined) ya[v] = 0;
                    ya[v] = ya[v] + cur;
                }

            }
            for(var i=0;i<100;i++){
                if(ya[i] === undefined) ya[i] = 0;
                else ya[i] = Math.round((100 * ya[i] / count)*100)/100;
            }
            yas[j] = {"name":order[j] , "data":ya};

        }

        return yas;
    }

    function analyse(){
        var sql = join( $('.j-condition li') );
        var order = join( $('#j-order li')).split(" ");

        console.log(JSON.stringify(order))
        ui.loading();
        $.getJSON('../api/?m=model&a=get',{
            'where':sql,
            'order':JSON.stringify(order)
        },function(d){
            //$('#j-result').html( JSON.stringify(d) );
            //console.log(d)
            ui.loading(null);

            if(d.status == 0){
                draw(d.detail , order);
            }
        })
    }

    return {
        'init':init,
        'calc':calc,
        'analyse':analyse
    }
}());