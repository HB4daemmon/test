<?php
require_once dirname(__FILE__)."/../util/mailer.php";
require_once dirname(__FILE__)."/../util/connection.php";

function saveEmail($email){
    try{
        $conn = db_connect();
        $create = "create table if not exists kantwait_invite_emails(id int auto_increment primary key,
                                      email varchar(200) not null,
                                      invite_time timestamp NOT NULL DEFAULT current_timestamp,
                                      note varchar(500)
)ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $conn->query($create);

        $insert = "insert into kantwait_invite_emails(email,note) values ('$email','')";
        $conn->query($insert);
        $conn->commit();
        $conn->close();
    }catch (Excetpion $e){
        $conn->rollback();
        $conn->close();
    }


}

if(isset($_REQUEST)){
    $param = $_REQUEST;
    if(isset($param['method'])){
        $method = $param['method'];
        $subject = 'Invite you to kantwait';
        $content = 'email content';
        if($method == 'invite_friends'){
            if(sendmail($param['email'],$subject,$content)){
                saveEmail($param['email']);
                header("Location:/kantwait/index.php/invite-success");
            }else{
                header("Location:/kantwait/index.php/invite-failure");
            }
        }
    }
}
?>