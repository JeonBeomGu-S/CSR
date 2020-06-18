/* 
@Copyright Anhive
RESTful API Design pattern 
*/

var POST = function(api, data, func){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            try {
                //console.log(request.response);
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            console.log(resp.status + ': ' + resp.data);
            if (resp.status == 'error') {
                //alert(resp.data);
                return;
            }
            func(resp);
        }
    };
    request.open('POST', api);
    request.send(data);
    return request;
}

var config_width = function(){
    console.log("set width..");
    width  = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    height  = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    document.width = width + "px";
    document.height = height + "px";
}

var alerted = function(after, tle, txt, what, button_info="확인"){
    // what info : error, warning, success
    // how to use button info "examples" ==> button_info = ["확인", "취소"];
    swal({ title: tle, text: txt, icon: what, buttons : button_info }).then((ok)=>{
        if ( ok ){
            if (typeof after == "function") after();
            return;
        }
        else {
            return;
        }
    });
}

//  how to use url parsing?
//  example code _ >> parseURLParams(window.location.href).num[0];
//  example code _ >> parseURLParams(window.location.href).memberinfo[0];
function parseURLParams(url) { // url parsing
    var queryStart = url.indexOf("?") + 1,
        queryEnd   = url.indexOf("#") + 1 || url.length + 1,
        query = url.slice(queryStart, queryEnd - 1),
        pairs = query.replace(/\+/g, " ").split("&"),
        parms = {}, i, n, v, nv;

    if (query === url || query === "") return;
    for (i = 0; i < pairs.length; i++) {
        nv = pairs[i].split("=", 2);
        n = decodeURIComponent(nv[0]);
        v = decodeURIComponent(nv[1]);

        if (!parms.hasOwnProperty(n)) parms[n] = [];
        parms[n].push(nv.length === 2 ? v : null);
    }
    return parms;
}

var css = "background-color : black; font-size : 24px; color : whitesmoke;";