<?php 

/////////////////////////////////////
// 현재 로그인 상태 확인
$services['get_login_status'] = "_get_login_status";
function _get_login_status(){
    s00_log("Start ".__FUNCTION__);
    outputJSON(array("login_status"=>$_SESSION["login"]), "success");
}


/////////////////////////////////////
// 로그인 상태 소멸 ( 로그아웃 기능 )
$services['logout'] = "_logout";
function _logout(){
    s00_log("Start ".__FUNCTION__);
    session_destroy();
    outputJSON("logout", "success");
}

/////////////////////////////////////
// get login data : 로그인 정보 가지고 오기..
$services['get_login_data'] = "_get_login_data";
function _get_login_data(){
    s00_log("Start ".__FUNCTION__);
    $json = array(
        "tel_number"=>$_SESSION["tel_number"],
        "gender"=>$_SESSION["gender"],
        "year"=>$_SESSION["year"],
        "name"=>$_SESSION["name"]
    );
    outputJSON($json, "success");
}
?>