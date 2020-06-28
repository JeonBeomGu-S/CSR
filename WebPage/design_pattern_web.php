<?php
// session start
session_start();
// include if login is true
if ( isset($_SESSION["login"]) && $_SESSION["login"] == true ) include_once("./design_pattern_web_login.php");

//  outputJSON, design pattern....;;;;
function outputJSON($msg, $status = 'error'){
    if ($status == 'error') error_log (print_r($msg, true)." in ".__FILE__); 
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

// $trace=yes;
$trace=0;
function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
} 

/////////////////////////////////////
// null service
$services['test'] = '_test';
function _test() { 
    s00_log ("Start ".__FUNCTION__);
    throw new Exception ( __FILE__.'is Available.');
};

/////////////////////////////////////
// 회원가입 -> json 파일 저장
$services['register'] = "_register";
function _register(){
    s00_log ("Start ".__FUNCTION__);
    // info dir set
    $file_dir = "register_info";
    
    // post_tel_number, post_name, post_year, post_password, post_gender set
    $tel_number = $_POST["post_tel_number"];
    $password = $_POST["post_password"];
    $gender = $_POST["post_gender"];
    $year = $_POST["post_year"];
    $name = $_POST["post_name"];
    $email = $_POST["post_email"];

    // tel number check
    if ( !preg_match("/\b[0-9]{3}-[0-9]{4}-[0-9]{4}\b/i", $tel_number) ) outputJSON("phone wrong", "success");
    // email check
    if ( !preg_match("/\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/i", $email) ) outputJSON("email wrong", "success");
    // post_gender data  to number
    $year_arr = explode("-", $year);
    $year = implode("", $year_arr);

    // json file name set
    $file_name = $tel_number . ".json";

    // overlap data check : 중복검사, tel_number..
    $overlap_check = scandir($file_dir);
    foreach($overlap_check as $chk){
        if ( $chk == "." || $chk == ".." ) continue;
        if ( $chk == $file_name ){
            // overlap..
            outputJSON("overlap", "success");
        }
    }

    $json = array(
        "tel_number"=>$tel_number,
        "password"=>$password,
        "gender"=>$gender,
        "year"=>$year,
        "name"=>$name,
        "email"=>$email,
        "wallet"=> 0
    );
    $json = json_encode($json);
    file_put_contents("$file_dir/$file_name", $json);
    outputJSON("register", "success");
}

/////////////////////////////////////
// 사용중인 방 정보
$services['room_check'] = "_check_room";
function _check_room(){
    s00_log ("Start ".__FUNCTION__);
    // set room info dir
    $room_info_dir = "room_info";
    $json = json_decode(file_get_contents("$room_info_dir/CSR_info.json"), true);
    $use_room = $json["use_room"]; 
    $no_use_room = $json["no_use_room"]; 
    $response_json = array(
        "use_room" => $use_room,
        "no_use_room" => $no_use_room
    );
    outputJSON(array("roominfo"=>$response_json), "success");
}

/////////////////////////////////////
// 로그인
$services['login_check'] = "_login_check";
function _login_check(){
    s00_log("Start ".__FUNCTION__);
    $file_dir = "register_info";
    $tel_number = $_POST['post_tel_number'];
    $password = $_POST["post_password"];
    if ( !preg_match("/\b[0-9]{3}-[0-9]{4}-[0-9]{4}\b/i", $tel_number) ) outputJSON("phone wrong", "success");
    // file is exits check
    $scan_file_dir = scandir("$file_dir");
    $file_is_exists = false;
    foreach($scan_file_dir as $file){
        if ( $file == "." || $file == ".." ) continue;
        if ( $file == "$tel_number.json" ) { $file_is_exists = true; break; }
    }
    if ( $file_is_exists == false ) outputJSON("file_not_exists", "success");
    $json = json_decode(file_get_contents("$file_dir/$tel_number.json"), true);
    // password check
    if ( $password != $json["password"] ) outputJSON("password_wrong", "success");
    // session start
    $_SESSION["login"] = true;
    $_SESSION["tel_number"] = $json["tel_number"];
    $_SESSION["gender"] = $json["gender"];
    $_SESSION["year"] = $json["year"];
    $_SESSION["name"] = $json["name"];
    $_SESSION["wallet"] = $json["wallet"];
    outputJSON("login_ok", "success");
}

/////////////////////////////////////
// 회원정보 읽어들이기
$services['member_read'] = "_member_read";
function _member_read(){
    s00_log("Start ".__FUNCTION__);
    $file_dir = "register_info";
    $tel_number = $_POST['post_tel_number'];
    $json = json_decode(file_get_contents("$file_dir/$tel_number.json"), true);
    outputJSON($json, "success");
}

/////////////////////////////////////
// 금액 충전
$services['money_charge'] = "_money_charge";
function _money_charge(){
    s00_log("Start ".__FUNCTION__);
    $file_dir = "register_info";
    $tel_number = $_POST['post_tel_number'];
    $charge_wallet = $_POST["charge_money"];

    $json = json_decode(file_get_contents("$file_dir/$tel_number.json"), true);
    $json["wallet"] = $json["wallet"] + $charge_wallet;
    $json_save = json_encode($json);
    file_put_contents("$file_dir/$tel_number.json", $json_save);
    outputJSON("ok", "success");
}

/////////////////////////////////////
// 금액 사용 -> 체크인
$services['check_in'] = "_check_in";
function _check_in(){
    s00_log("Start ".__FUNCTION__);
    // file dir variable set
    $register_file_dir = "register_info";
    $room_file_dir = "room_info";
    // init var
    $room_number = $_POST["room_number"];
    $system = $_POST["system"];
    $room_charge = $_POST["room_charge"];
    $tel_number = $_POST["post_tel_number"];
    $use_money = $_POST["use_money"];

    // process : 1. wallet check , 2. room_info data change

    // #1.
    $json = json_decode(file_get_contents("$register_file_dir/$tel_number.json"), true);
    if ( $use_money > $json["wallet"] ) outputJSON("not enough money", "success"); // error 돈 부족
    $json["wallet"] = $json["wallet"] - $use_money;
    $json_save = json_encode($json);
    file_put_contents("$register_file_dir/$tel_number.json", $json_save);

    // #2.
    $json = json_decode(file_get_contents("$room_file_dir/CSR_info.json"), true);
    if ( $json["$room_number"]["use"] == true ) outputJSON("this room is already used", "success"); // 이미 해당 방은 사용중.
    $json["$room_number"]["system"] = $system;
    $json["$room_number"]["use"] = true;
    $json["$room_number"][$system] = $room_charge;
    $json["use_room"]++;
    $json["no_use_room"]--;
    $json_save = json_encode($json);
    file_put_contents("$room_file_dir/CSR_info.json", $json_save);
    outputJSON("ok", "success");
}
/////////////////////////////////////
// 비회원 요금제 결제
$services['non_member_use'] = "_non_member_use";
function _non_member_use(){
    s00_log("Start ".__FUNCTION__);

    // room var set
    $room_file_dir = "room_info";

    // set variable
    $room_number = $_POST["room_num"];
    $value = $_POST["value"];
    $system = $_POST["system"];
    $num = $_POST["num"];

    // json set
    $json = json_decode(file_get_contents("$room_file_dir/CSR_info.json"), true);
    if ( $json["$room_number"]["use"] == true ) outputJSON("this room is already used", "success"); // 이미 해당 방은 사용중.
    $json["$room_number"]["system"] = $system;
    $json["$room_number"]["use"] = true;
    $json["$room_number"][$system] = $num;
    $json["use_room"]++;
    $json["no_use_room"]--;
    $json_save = json_encode($json);
    file_put_contents("$room_file_dir/CSR_info.json", $json_save);
    outputJSON("ok", "success");
}
/////////////////////////////////////
// 문의사항 : 이메일 서비스
$services['question'] = "_question";
function _question(){
    s00_log("Start ".__FUNCTION__);
    $email_dir = "./email_service";
    $post_user = $_POST["post_user"];
    $post_title = $_POST["post_title"];
    $post_content = $_POST["post_content"];
    $post_email = $_POST["post_email"];
    $operator_json = json_decode(file_get_contents("$email_dir/operator_email.json"), true);
    $operator_json["email_name"];
    // file make 
    $file_content = " 
        제목 : $post_title
        작성자 : $post_user
        내용 : $post_content 
        작성자 이메일 : $post_email
        ";
    file_put_contents("$email_dir/$post_user.txt", $file_content);
    exec("$email_dir/question.sh $email_dir/$post_user.txt '$post_title' " . $operator_json['email_name']);
    outputJSON("ok", "success");
}
/////////////////////////////////////
// 비밀번호 찾기 ( 인증번호 생성 및 이메일 )
$services['find_password'] = "_find_password";
function _find_password(){
    s00_log("Start ".__FUNCTION__);
    $file_dir = "register_info";
    $tel_number = $_POST["post_tel"];
    if ( !preg_match("/\b[0-9]{3}-[0-9]{4}-[0-9]{4}\b/i", $tel_number) ) outputJSON("phone wrong", "success");
    if ( !file_exists("$file_dir/$tel_number.json") ) outputJSON("file wrong", "success");
    $json_user = json_decode(file_get_contents("$file_dir/$tel_number.json"), true);

    // make session for cert
    $rand_number = rand(100000, 999999);
    $_SESSION["cert"] = $rand_number;

    // make cert period time
    $_SESSION['LAST_ACTIVITY'] = time();

    // send email
    $email_dir = "./email_service";
    $email = $json_user["email"];
    exec("$email_dir/password.sh $rand_number $email");

    // response ( end )
    outputJSON("ok", "success");
}
/////////////////////////////////////
// 비밀번호 찾기 ( 인증번호 생성 및 이메일 )
$services['password_change'] = "_password_change";
function _password_change(){
    $file_dir = "register_info";
    // 만료 확인
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 180)) {
        // last request was more than 30 minutes ago
        session_unset();     // unset $_SESSION variable for the run-time 
        session_destroy();   // destroy session data in storage
        outputJSON("expiration", "success");
    }
    error_log("post_cert : " . $_POST['post_cert'] . " session_cert : " . $_SESSION["cert"]);
    if ( $_POST["post_cert"] != $_SESSION['cert'] ) outputJSON("wrong cert", "success");
    $post_tel = $_POST["post_tel"];
    $post_cert = $_POST["post_cert"];
    $post_pw = $_POST["post_pw"];
    $user_json = json_decode(file_get_contents("$file_dir/$post_tel.json"), true);
    $user_json["password"] = $post_pw;
    file_put_contents("$file_dir/$post_tel.json", json_encode($user_json));
    // end
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    outputJSON("ok", "success");
}

/////////////////////////////////////
// 존재하지 않은 함수 또는 로그인이 필요한 서비스 
$services['function_not_exists'] = "_function_not_exists";
function _function_not_exists(){
    s00_log("Start ".__FUNCTION__);
    error_log("function_not_exists : " . __LINE__ );
    throw new Exception(outputJSON(null, "success"));
}

$func = isset($_POST['func'])?$_POST["func"]:"test";
$func = function_exists($services[$func]) ? $func : "function_not_exists";

try {	
    call_user_func( $services[$func] );
    //s00_log2(4, print_r($services,true));
} catch (Exception $e) {
    s00_log($e->getLine().'@'.__FILE__."::".$e->getMessage());
    s00_log(print_r($e->getTrace(),true));
    outputJSON($e->getMessage());
}
?>
