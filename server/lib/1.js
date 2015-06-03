var model = [
    {
        "title":"国家",
        "values": {
            "all":[
                {"title":"中国",'key':'china'},
                {"title":"U.S.A",'key':'usa'},
            ]
        }
    },
    {
        "title":"省份",
        "values":{
            "china":[
                {"title":"浙江",'key':'zj'},
                {"title":"上海",'key':'sh'},
            ]
        }
    },
    {
        "title":"市",
        "values":{
            "zj":[
                {"title":"杭州",'key':'hz'},
                {"title":"金华",'key':'jh'}
            ]
        }
    }
]

/**
 * 渲染 select
 * @param lv 层级
 * @param data 数据
 */
function render(lv , data){
    var el = $('#'+lv);
    var html = '';
    for(var i in data){
        html += '<option value="'+data[i].key+'">'+data[i].title+'</option>';
    }

    el.html(html);
}

$('.sele').on("change" , function(){
    //获取层级
    var lv  = $(this).attr('data-lv'),
        key = $(this).val();
    if(lv !== undefined){
        render(lv+1 , model[lv]['values'][key]);
    }
});

render('0',model[0]["values"]["all"]);


[
    {"title":"中国","id":"1","parent":null},
    {"title":"USA","id":"2","parent":null},
    {"title":"浙江","id":"11","parent":"1"},
    {"title":"杭州","id":"111","parent":"11"},
]


function getValuesById(id){
    var values = [];
    for(var i in model){
        if(model[i].parent == id){
            values.push(model[i]);
        }
    }
    return values;
}


function JsGoTo(pageId) {
    var formObj = $("<form></form>");
    formObj[0].action = window.location.href;
    formObj[0].method = "POST";
    formObj[0].target = "_self";
    var formHtml = '<input type="hidden" name="LeagueID" value="' + defCsVal.join(",") + '"><input type="hidden" name="HandicapNumber" value="' + defRqVal.join(",") + '"><input type="hidden" name="BetDate" value="' + defDateVal.join(",") + '"><input type="hidden" name="MakerType" value="' + defMakerType + '"><input type="hidden" name="PageID" value="' + pageId + '">';
    if (document.getElementById("HasEnd").checked) {
        formHtml += '<input type="hidden" name="HasEnd" value="1">';
    } else {
        formHtml += '<input type="hidden" name="HasEnd" value="0">';
    }
    if (document.getElementById("search") && document.getElementById("search").value) {
        var search = document.getElementById("search").value;
        var start = document.getElementById("StartDate").value;
        var end = document.getElementById("EndDate").value;
        formHtml += '<input type="hidden" name="startdate" value=' + start + '>';
        formHtml += '<input type="hidden" name="enddate" value=' + end + '>';
        formHtml += '<input type="hidden" name="search" value=' + search + '>';
        formHtml += '<input type="hidden" name="way" value="auther">';
    }
    formObj.html(formHtml);
    $("body").append(formObj);
    formObj.submit();
}