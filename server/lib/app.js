var fs = require('fs');
var http = require("http");
var config;

var utils = {
    'replace' : function(str, data) {
        return str.replace(/\{ *([\w_]+) *\}/g, function (str, key) {
            var value = data[key];
            if (value === undefined) {
                console.log('No value provided for variable ' + str);
                value = "{" + key + "}";
            } else if (typeof value === 'function') {
                value = value(data);
            }
            return value;
        })
    },

    has : function(obj, key) {
        return obj != null && hasOwnProperty.call(obj, key);
    },

    key : function(obj){
        var k = [];
        for(var i in obj){
            if(this.has(obj , i)) k.push(i);
        }
        return k;
    },
    select : function(obj , key){
        var a = [];

        for(var i=0;i<obj.length;i++){
            if(obj[i][key]) a.push(obj[i][key])
        }
        return a;
    },

    time : (function(){
        var fix0 = function(v){
            return parseInt(v)<10 ? ('0'+v) : v;
        }
        return function(){
            var d = new Date();
            return fix0(d.getHours()) + ':' + fix0(d.getMinutes()) + ':' + fix0(d.getSeconds());
        }
    }()),

    'timeToStr' : function(v){
        v = Math.floor(v / 1000);
        if(v < 60) return v + 's';
        else if(v>=60 && v < 3600) return Math.floor(v/60)+'m '+ (v%60) + 's';
        else return v + 's';
    },

    'before' : function(c){
        var dd = new Date();
        dd.setDate(dd.getDate()-c);
        return dd.getFullYear() + '-' + (dd.getMonth() + 1) + '-' + dd.getDate();
    }
}



var logger = {
    'info' : function(v,record){
        v = '['+utils.time()+']' +  ' ' +v;
        if(record!==false) {
            console.log('\x1B[36m%s\x1B[0m', v);
            fs.appendFile('log/request.log', v+"\n", function (error) {});
        }
    },
    'warn':function(v){
        v = '['+utils.time()+']' +  ' ' +v;
        console.log('\x1B[33m%s\x1b[0m', v);
        fs.appendFile('log/error.log', v + "\n", function (error) {});

    }
}

// 定时器
var timer = null;

/***
 * 抓取当天比赛
 *
 */
function getJson(url , success , error){
    var retry = 50;
    var process = function(){

        http.get( url , function(res){
            var data = '';
            res.setEncoding("utf8");
            res.on('data', function(chunk){
                data += chunk;
            });
            res.on('end', function(){
                try{
                    data = JSON.parse(data);
                    success && success(data)
                }catch(e){
                    if(retry--){
                        logger.warn('retry ...' + url , false);
                        setTimeout(process , 10);
                    }
                    else
                        error && error();
                }

            });
        }).on('error', function(e) {

            if(retry--){
                logger.warn('retry '+ (50 - retry) + " " + url);
                setTimeout(process , 20);
            }
            else
                error && error();
        });
    }

    process();
}

function getMatches(date , fn)
{
    logger.info('Start Crawl \n');
    timer = Date.now();
    getJson(config.match + "&save=true&date="+date, function(resp){
        var d = resp.detail, len = d.length , i = 0;
        logger.info('Get matches list : '+len);
        var process = function(){
            if(i < len){
                var match = d[i][14];
                logger.info('Get odds for match : ' + match + "," + d[i][3]+" VS "+d[i][6] , false);
                getOdds(match , function(){
                    i++;
                    setTimeout(process , 10);

                });
            }else{
                var et = utils.timeToStr(Date.now() - timer);
                logger.info('Finish Crawl , '+len+' match(es) in '+ et +'\n');
                logger.info('Service is running ... \n');
                fn && fn();
            }
        };

        if(config.odds_off) i = len;

        process();
    });
}

/**
 *  抓取赔率信息
 * @param match 比赛id
 * @param id 开赔公司
 */
function getOdds(match ,fn){
    var cpy = config.company, i = 0;//&save=true
    var url = config.odds + '&save=true&match=' + match + '&id=';
    var process = function(){
        if(i<cpy.length){
            //console.log('get : '+ url + cpy[i][1] + '&field=' + cpy[i][0]);
            getJson(url + cpy[i][1] + '&field=' + cpy[i][0]  , function(resp){
                logger.info('odds for ' + cpy[i][0] , false);

                if(resp.status == 0){
                    i++;
                    setTimeout(process , 10);
                }
            },function(){
                logger.warn('miss match ' + match);
                i++;
                setTimeout(process , 10);
            });
        }else{
            fn && fn();
        }

    }
    process();
}


/**
 * 主函数
 * @param d
 */
function main2(){
    var now = utils.before(0);
    getMatches(now , function(){
        setTimeout( main  , config.tick);
    });
}

function main(){

    var len = config.len || 365, i = config.start || 0, now;
    var process = function(){
        now = utils.before(i);
        logger.info('load date : ' + now);
        if(i<len){
            getMatches(now , function(){
                i++;
                setTimeout( process  , 10);
            });
        }else{
            logger.info('load history finished');
        }
    }

    process();

}

module.exports = function(c){
    config = c;
    logger.info('Service Start');
    main();
}