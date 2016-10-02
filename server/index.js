var config = {
    // 抓取间隔 ms
    'tick':1 * 3600 * 1000,

    //赛事api
    //'match':'http://o.com/api/?m=soccer&a=list',
    'match':'http://localhost:803/api/?m=soccer&a=list',

    // odds api
    //'odds':'http://o.com/api/?m=soccer&a=odds',
    'odds':'http://localhost:803/api/?m=soccer&a=odds',

    //'odds_off':true,
    'start':1,
    len : 10,
    // 开赔公司
    'company':[
        ['ave','24']
        ,['williamhill','14'],
        ['interwetten','43'],
        //['ysb','35'],
        ['ladbrokes','82'],
        ['bet365','27'],
        //['macau','84'],
        ['oddset','18']
    ]

};

var app = require('./lib/app.js');

app(config);