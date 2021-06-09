<?php include('db_connect.php'); /*We call database options*/
if(isset($_GET['auth'])){
    if($_GET['auth'] != 'otp'){
        if(!isset($_GET['token'])){
            if($_GET['auth']== 'register'){
                $password = sha1($_POST['password']);
                $username = $_POST['username'];
                if($_POST['type'] == 'phone'){
                    /*PHONE REGISTRATION*/
                    $column = 'phone';
                }else{
                    /*EMAIL REGISTRATION*/
                    $column = 'email';
                }
                
                if(!isset($_SESSION['temp'])){
                    $_SESSION['temp'] = uniqid();
                }
                $temp = $_SESSION['temp'];
                $readUser = $db -> query('SELECT * FROM users WHERE '.$column.' = \''.$username.'\' AND `password` = \''.$password.'\'') or die(print_r($db->errorinfo()));
                $User = $readUser -> fetch();
                if($User){
                    $job = 'no';
                    $msg = 'The '.$column.' "'.$username.'" is already registered, login or reset password!';
                }else{
                    $addUser = $db -> query('INSERT INTO `users` ('.$column.', `password`, `temp`) VALUES ( \''.$username.'\', \''.$password.'\', \''.$temp.'\') ') or die(print_r($db->errorinfo()));
                    if($addUser){
                        $readUserId = $db -> query('SELECT * FROM users WHERE '.$column.' = \''.$username.'\' AND password = \''.$password.'\'') or die(print_r($db->errorinfo()));
                        $UserId = $readUserId -> fetch();

                        $user_id = $UserId['id'];
                        $level = '1';
                        $access = 'user';
                        $last_login_date = date('Y-m-d H:i:s');
                        if(!isset($device)){
                            $device = 'desk';
                        }
                        $last_login_device = $device;
                        $token = $temp;
                        $addAuth = $db -> query('INSERT INTO `users_auth`( `user_id`, `level`, `access`, `last_login_date`, `last_login_device`, `token`) VALUES ( \''.$user_id.'\', \''.$level.'\', \''.$access.'\', \''.$last_login_date.'\', \''.$last_login_device.'\', \''.$token.'\')') or die(print_r($db->errorinfo()));
                        
                        $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.'.$column.' = \''.$username.'\'  AND users.password = \''.$password.'\'') or die(print_r($db->errorinfo()));
                        $UserAuth = $readUserAuth -> fetch();
                        $access = $UserAuth['access'];
                        $level = $UserAuth['level'];
                        $id =  $user_id;
                        $job = 'wait';
                        $msg = 'Successfull registered';
                        /*SEND VERIFICATION CODE*/
                        $link = '/auth/register/'.$token;
                    }else{
                        $job = 'no';
                        $msg = 'Failed registration';
                    }
                }
            }else if($_GET['auth'] == 'login'){
                $password = sha1($_POST['password']);
                $username = $_POST['username'];
                $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE (users.email = \''.$username.'\' OR users.phone = \''.$username.'\') AND users.password = \''.$password.'\'') or die(print_r($db->errorinfo()));
                $UserAuth = $readUserAuth -> fetch();

                if(!$UserAuth){
                    $job = 'no';
                    $msg = 'Invalid username or password';
                }else if($UserAuth['status'] == 'pending'){
                    $job = 'no';
                    $msg = 'Your account is not verified!';
                } else{
                    $access = $UserAuth['access'];
                    $level = $UserAuth['level'];
                    $id  = $UserAuth['id'];
                    $access = $UserAuth['access'];
                    $level = $UserAuth['level'];
                    $_SESSION['id'] = $id;
                    if($UserAuth['email'] != ''){
                        $username = $UserAuth['email'];
                    }else{
                        $username = $UserAuth['phone'];
                    }
                    $_SESSION['username'] = $username;
                    $_SESSION['level'] = $level;
                    $_SESSION['access'] = $access;
					$_SESSION['id'] = $UserAuth['id'];
					$_SESSION['username'] = $username;
                    $job = 'ok';
                    $rep -> id  = $id;
                    $rep ->  access =  $access;
                    $rep -> level  = $level;
                    $msg = 'Successfull login';
                    if(!isset($_SESSION['link'])){
                        $link ='/account/dashboard';
                    }else{
                        $link = $_SESSION['link'];
                    }
                    $rep -> link  = $link;
                }
            }else if($_GET['auth'] == 'reset'){
                
                if($_POST['type'] == 'phone'){
                    /*PHONE REGISTRATION*/
                    $username = $_POST['code'].$_POST['phone'];
                    $readUserId = $db -> query('SELECT * FROM users WHERE `phone` = \''.$username.'\'') or die(print_r($db->errorinfo()));
                    $UserId = $readUserId -> fetch();
                    if(!$UserId){
                        $job = 'no';
                        $msg = '"'.$username.'" doesn\'t assigned to any account !';
                    }else{
                        function generateRandomString($length = 4) {
                            return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
                        }
                        $otp =   generateRandomString();
						//(TODO)
                        $user = "mytiem.com";
                        $api = "3650648";
                        $pass = "ac@MT2KXXI";
                        $code = $_POST['code'];
                        $phone = $_POST['phone'];
                        $phoneNumber = $code.$phone;
                        $userinfo = $phoneNumber;
                        $api      = urlencode($api);
                        $to       = urlencode($userinfo);
                        $username = urlencode($user);
                        $pass = urlencode($pass);
                        $msg  = urlencode("Your OTP is ". $otp ." and will expire in 10 minutes.");
                        if(file_get_contents("https://api.clickatell.com/http/sendmsg" . "?user=$user&password=$pass&api_id=$api&to=$to&text=$msg")/*.$to*/){
                            $_SESSION['userinfo'] = $to;
                            $_SESSION['OTP'] = $otp;
                            $adOtp = $db -> query('INSERT INTO `users_otp`(`user`, `otp`, `date_added`) VALUES (\''.$userinfo.'\',\''.$otp.'\')');
                            $job = 'ok';
                        }
                        $updateAuth = $db -> query('UPDATE `users` SET `otp` =  \''.$otp.'\' WHERE `id` = \''.$UserId['id'].'\' ');
                        if($updateAuth){
                            $job = 'ok';
                            // send email 
                            $msg = 'A verification OTP has been sent to '.$username;
                        }
                    }
                }else{
                    /*EMAIL REGISTRATION*/
                    $username = $_POST['username'];
                    $readUserId = $db -> query('SELECT * FROM users WHERE `email` = \''.$username.'\'') or die(print_r($db->errorinfo()));
                    $UserId = $readUserId -> fetch();
                    if(!$UserId){
                        $job = 'no';
                        $msg = '"'.$username.'" doesn\'t assigned to any account !';
                    }else{
                        $temp = uniqid();
                        $updateAuth = $db -> query('UPDATE `users` SET `temp` =  \''.$temp.'\' WHERE `id` = \''.$UserId['id'].'\' ');
                        if($updateAuth){
                            $job = 'ok';
                            // send email 
                            $msg = 'A verification link has been sent to '.$username;
                        }
                    }
                }
            }else if($_GET['auth'] == 'update'){
                $temp = $_POST['temp'];
                $username = $_POST['username'];
                $password = sha1($_POST['password']);
                $uniqid = uniqid();
                $updatePass = $db -> query('UPDATE `users` SET `password` =  \''.$password.'\' , `temp` = \''.$uniqid.'\'  WHERE (`email` = \''.$username.'\' OR `phone` = \''.$username.'\') AND `temp` =  \''.$temp.'\'') or die(print_r($db->errorinfo()));
                if($updatePass){
                    $job = 'ok';
                        // send email 
                    $msg = 'Password updated successfuly';
                }else{
                    $job = 'no';
                        // send email 
                    $msg = 'Token expired';
                }
            }
        }else if($_GET['auth'] == 'register' AND isset($_GET['token'])){
            $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.temp = \''.$_GET['token'].'\' AND users_auth.token = \''.$_GET['token'].'\'') or die(print_r($db->errorinfo()));
            $UserAuth = $readUserAuth -> fetch();
            if(!$UserAuth){
                $job = 'no';
                $msg = 'Verification failed!';
            }else{
                $last_login_date = date('Y-m-d H:i:s');
                    if(!isset($device)){
                    $device = 'desk';
                    $last_login_divice = $device ;
                }
                $updateAuth = $db -> query('UPDATE `users_auth` SET `last_login_date` = \''.$last_login_date.'\',`last_login_device`= \''.$last_login_divice.'\',`token` = "",`status` = "verified" WHERE token = \''.$_GET['token'].'\' ')or die(print_r($db->errorinfo()));
                if($updateAuth){
                    $job = 'ok';
                    $id  = $UserAuth['id'];
                    $access = $UserAuth['access'];
                    $level = $UserAuth['level'];
                    $_SESSION['level'] = $level;
                    $_SESSION['access'] = $access;
                    if($UserAuth['email'] != ''){
                        $username = $UserAuth['email'];
                    }else{
                        $username = $UserAuth['phone'];
                    }
                    $_SESSION['username'] = $username;
                    $_SESSION['id'] = $id;
                    $rep ->  access =  $access;
                    $rep -> level  = $level;
                    $msg = 'Successfull verification';
                }else{
                    $msg = '';
                    $job = 'no';
                }
            }
        }
    }else if($_GET['auth'] == 'otp' ){
        function generateRandomString($length = 4) {
            return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
        }
        $otp =   generateRandomString();
        $user = "mytiem.com";
        $api = "3650648";
        $pass = "ac@MT2KXXI";
        $code = $_POST['code'];
        $phone = $_POST['phone'];
        $phoneNumber = $code.$phone;
        $userinfo = $phoneNumber;
        $api      = urlencode($api);
        $to       = urlencode($userinfo);
        $username = urlencode($user);
        $pass = urlencode($pass);
        $msg  = urlencode("Your OTP is ". $otp ." and will expire in 10 minutes.");
        if(file_get_contents("https://api.clickatell.com/http/sendmsg" . "?user=$user&password=$pass&api_id=$api&to=$to&text=$msg")/*.$to*/){
            $_SESSION['userinfo'] = $to;
            $_SESSION['OTP'] = $otp;
            $adOtp = $db -> query('INSERT INTO `users_otp`(`user`, `otp`, `date_added`) VALUES (\''.$userinfo.'\',\''.$otp.'\')');
            $job = 'ok';
        }
    }else{
        $job ='no';
        $msg ='Bad Request!';
    }
	$rep -> job = $job;
    $rep -> msg  = $msg;
    die(json_encode($rep));
}
?>