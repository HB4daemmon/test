<?php
require_once dirname(__FILE__)."/../util/connection.php";

function getHash($password, $salt=false)
{
    if (is_integer($salt)) {
        $salt = getRandomString($salt);
    }
    return $salt===false ? md5($password) : md5($salt.$password).':'.$salt;
}
/*
 * 验证密码
 @param string $password
* @param string $hash
* @return bool
 */
function validateHash($password,$hash)
{
    $hashArr = explode(':', $hash);
    switch (count($hashArr)) {
        case 1:
            return getHash($password) === $hash;
        case 2:
            return getHash($hashArr[1] . $password) === $hashArr[0];
    }
    return 'Invalid hash.';
}

function checkDriverUser($username,$password){
    try{
        $conn = db_connect();
        if(!$username || !$password){
            throw new Exception("Username or password is null");
        }
        $sql = "select * from api_user where username = '$username' and is_active = 1";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("select api user error");
        }
        if($res->num_rows == 0){
            throw new Exception("The account has not been registered");
        }
        $row = $res->fetch_assoc();
        if(!validateHash($password,$row['api_key'])){
            throw new Exception("Account or password error, login failed");
        }else{
            return array("success"=>1,"data"=>'success');
        }
        $conn->close();
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"error_msg"=>$e->getMessage());
    }
}

try{
    $param = $_REQUEST;
    if(!$param['method']){
        throw new Exception("Method is null");
    }

    if($param['method'] == 'checkDriverUser'){
        echo json_encode(checkDriverUser($param['username'],$param['password']));
    }else{
        throw new Exception("Invalid Method");
    }

}catch(Exception $e){
    echo json_encode(array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage()));
    exit;
}

?>