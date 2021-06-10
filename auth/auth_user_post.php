<?php include('db_connect.php'); /*We call database options*/
if(isset($_GET['auth']) AND !isset($_GET['temp'])){
	include('send_otp.php');
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
						if($column == 'phone'){
							include('otp.php');
						}else{
							/*SEND EMAIL VERIFICATION LINK*/
							$subject = 'Confirm Registration';
							$message = '<a href="'.DOM.'/auth/register/'.$token.'">Verify Account</a>';
                            $to = $username;
							include('email_headers.php');
							include('email.php');
						}
                    }else{
                        $job = 'no';
                        $msg = 'Failed registration! internal error! please try again later!';
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
            }else if($_GET['auth'] == 'reset' AND !isset($_GET['token'])){
                if($_POST['type'] == 'phone'){
                    /*PHONE RESET PASSWORD*/
                    $username = $_POST['username'];
                    $readUserId = $db -> query('SELECT * FROM users WHERE `phone` = \''.$username.'\'') or die(print_r($db->errorinfo()));
                    $UserId = $readUserId -> fetch();
                    if(!$UserId){
                        $job = 'no';
                        $msg = '"'.$username.'" doesn\'t assigned to any account !';
                    }else{
						include('otp.php');
                    }
                }else{
                    /*EMAIL RESET PASSWORD*/
                    $username = $_POST['username'];
                    $readUserId = $db -> query('SELECT * FROM users WHERE `email` = \''.$username.'\'') or die(print_r($db->errorinfo()));
                    $UserId = $readUserId -> fetch();
                    if(!$UserId){
                        $job = 'no';
                        $msg = '"'.$username.'" doesn\'t assigned to any account !';
                    }else{
                        $temp = uniqid();
                        $updateAuth = $db -> query('UPDATE `users_auth` SET `token` =  \''.$temp.'\' WHERE `user_id` = \''.$UserId['id'].'\' ');
                        if($updateAuth){
							$subject = 'Reset Passord';
							$message = '<a href="'.DOM.'/auth/reset/'.$temp.'">Reset password</a>';
                            $to = $username;
							include('email_headers.php');
							include('email.php');
                        }
                    }
                }
            }else if($_GET['auth'] == 'update'){
                $temp = $_POST['temp'];
				$type = $_POST['type'];
				$username = $_POST['username'];
				if($type == 'email'){
					$whr = '`email` = "'.$username.'"';
				}else{
					$whr = '`phone` = "'.$username.'"';
				}
                $password = sha1($_POST['password']);
                $uniqid = uniqid();
                $updatePass = $db -> query('UPDATE `users` SET `password` =  \''.$password.'\' , `temp` = ""  WHERE '.$whr.' AND `temp` =  \''.$temp.'\'') or die(print_r($db->errorinfo()));
                if($updatePass){
					$updateAuth = $db -> query('UPDATE `users_auth` SET `token` = "" WHERE token = \''.$_POST['temp'].'\' ')or die(print_r($db->errorinfo()));
					$subject = 'Passord Updated';
					$message = 'Password updated ';
                    $to = $username;
					include('email_headers.php');
					include('email_update.php');
                }else{
                    $job = 'no';
                    $msg = 'Token expired';
                }
            }
        }else if($_GET['auth'] == 'register' AND isset($_GET['token'])){

            if(isset($_POST['otp'])){
                $readUserAuth = $db -> query('SELECT * FROM users_otp  WHERE user = \''.$_POST['username'].'\' AND otp = \''.$_POST['otp'].'\'') or die(print_r($db->errorinfo()));
                $UserAuth = $readUserAuth -> fetch();
                if(!$UserAuth){
                    $job = 'no';
                    $msg = 'Invalid OTP!';
                }else{
                    $phone = $UserAuth['user'];
                    $last_login_date = date('Y-m-d H:i:s');
                    if(!isset($device)){
                        $device = 'desk';
                        $last_login_divice = $device ;
                    }
                    $readUserAuth2 = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.phone = \''.$phone.'\'') or die(print_r($db->errorinfo()));
                    $UserAuth2 = $readUserAuth2 -> fetch();
                    $updateAuth = $db -> query('UPDATE `users_auth` SET `last_login_date` = \''.$last_login_date.'\',`last_login_device`= \''.$last_login_divice.'\',`token` = "",`status` = "verified" WHERE user_id = \''.$UserAuth2['id'].'\' ')or die(print_r($db->errorinfo()));
                    if($updateAuth){
                        $updateAuth3 = $db -> query('DELETE FROM `users_otp` WHERE user = \''.$phone.'\'');
                        $readUserAuth2 = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.phone = \''.$phone.'\'') or die(print_r($db->errorinfo()));
                        $UserAuth2 = $readUserAuth2 -> fetch();
                        $job = 'ok';
                        $id  = $UserAuth2['id'];
                        $access = $UserAuth2['access'];
                        $level = $UserAuth2['level'];
                        $_SESSION['level'] = $level;
                        $_SESSION['access'] = $access;
                        if($UserAuth['email'] != ''){
                            $username = $UserAuth2['email'];
                        }else{
                            $username = $UserAuth2['phone'];
                        }
                        $_SESSION['username'] = $username;
                        $_SESSION['id'] = $id;
                        $rep ->  access =  $access;
                        $rep -> level  = $level;
                        $msg = 'Successfull verification';
                    }
                }
            }else{
                $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.temp = \''.$_GET['token'].'\' AND users_auth.token = \''.$_GET['token'].'\'') or die(print_r($db->errorinfo()));
                $UserAuth = $readUserAuth -> fetch();
                
                if(!$UserAuth){
                    $job = 'no';
                    $msg = 'Verification failed! invalid token!';
                    die($msg);
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
                        header('location:/auth/reset/password');
                    }else{
                        $msg = '';
                        $job = 'no';
                    }
                }
            }
        }else if($_GET['auth'] == 'reset' AND isset($_GET['token'])){
            if(isset($_POST['username'])){
                $readUserAuth = $db -> query('SELECT * FROM users_otp LEFT JOIN users ON users.phone = users_otp.user WHERE users_otp.user = \''.$_POST['username'].'\' AND users_otp.otp = \''.$_POST['otp'].'\'') or die(print_r($db->errorinfo()));
                $UserAuth = $readUserAuth -> fetch();
                if($UserAuth){
                    $_SESSION['setpass'] = uniqid();
                    $msg = 'Valid otp: Set new password for '.$_SESSION['setpass'];
                    $job = 'ok';
                    $rep -> temp = $_SESSION['setpass'];
                    $phone = $_POST['username'];
                    $updateAuth = $db -> query('UPDATE `users_auth` SET `token` = \'' .$_SESSION['setpass'].'\' WHERE `user_id` = \''.$UserAuth['id'].'\' ');
                    $updateAuth3 = $db -> query('DELETE FROM `users_otp` WHERE user = \''.$phone.'\'');
                }else{
                    $job = 'no';
                    $msg = 'Invalid OTP  !';
                }
            }else{
                $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users_auth.token = \''.$_GET['token'].'\'') or die(print_r($db->errorinfo()));
                $UserAuth = $readUserAuth -> fetch();
                if($UserAuth){
                    $msg = 'Valid token: Set new password';
                    $job = 'wait';
                    $_SESSION['setpass'] = uniqid();
                    $rep -> temp = $_SESSION['setpass'];
                    $updateAuth = $db -> query('UPDATE `users_auth` SET `token` = \'' .$_SESSION['setpass'].'\' WHERE `token` = \''.$_GET['token'].'\' ');
                    //(TODO)
                    header('location:/auth/password/'.$_SESSION['setpass']);
                }else{
                    $msg = 'Invalid token or expired!';
                    $job = 'no';
                }
            }
		}
    }else if($_GET['auth'] == 'otp' ){
        //VERIFY OTP (TODO)
    }else{
        $job ='no';
        $msg ='Bad Request!';
    }
	$rep -> job = $job;
    $rep -> msg  = $msg;
	die(json_encode($rep));
}else if(isset($_GET['auth']) AND $_GET['auth'] =='password'){
    //(TODO);
    die('SET NEW PASSWORD FORM FOR '.$_SESSION['setpass']);
}else{
	$rep = new stdClass();
	$rep -> job = 'no';
    $rep -> msg  = 'Bad request';
	die(json_encode($rep));
}
?>