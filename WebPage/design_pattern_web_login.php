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
    $tel_number = $_SESSION["tel_number"];
    $json_data = json_decode(file_get_contents("register_info/$tel_number.json"), true);
    $_SESSION["wallet"] = $json_data["wallet"];
    $json = array(
        "tel_number"=>$_SESSION["tel_number"],
        "gender"=>$_SESSION["gender"],
        "year"=>$_SESSION["year"],
        "name"=>$_SESSION["name"],
        "wallet"=>$_SESSION["wallet"]
    );
    outputJSON($json, "success");
}


/////////////////////////////////////
// booking : 방 예약하기
$services["booking"] = "_booking";
function _booking(){
    s00_log("Start ".__FUNCTION__);
    // post
    $index = $_POST["room_number"];
    $use_money = $_POST["use_money"];
    $time = $_POST["post_time"];
    // session
    $tel_number = $_SESSION["tel_number"];
    $name = $_SESSION["name"];
    error_log($_SESSION["wallet"]);
    // check money
    if ( $_SESSION["wallet"] < $use_money ) outputJSON("not enough money", "success");
    // money change
    $json_user = json_decode(file_get_contents("register_info/$tel_number.json"), true);
    $json_user["wallet"] = $json_user["wallet"] - $use_money;
    // update session wallet
    $_SESSION["wallet"] = $json_user["wallet"];
    file_put_contents("register_info/$tel_number.json", json_encode($json_user));

    // booking csr_info json
    $json_csr = json_decode(file_get_contents("room_info/CSR_info.json"), true);
    $json_csr[$index]["book"] = true; 
    $json_csr[$index]["use"] = true;
    $json_csr[$index]["name"] = $_SESSION["name"];
    $json_csr[$index]["system"] = "time";
    $json_csr[$index]["time"] = $time;
    $json_csr["use_room"]++; $json_csr["no_use_room"]--;
    file_put_contents("room_info/CSR_info.json", json_encode($json_csr));
    // ok
    outputJSON("ok", "success");
}

/////////////////////////////////////
// room setting : 방 정보들 가져오기
$services["room_setting"] = "_room_setting";
function  _room_setting(){
    s00_log("Start ".__FUNCTION__);
    // read room info data
    $file_dir = "room_info";
    $CSR_info = json_decode(file_get_contents("$file_dir/CSR_info.json"), true);
    // end
    outputJSON($CSR_info, "success");
}

?>