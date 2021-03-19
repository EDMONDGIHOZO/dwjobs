<?php include('db_connect.php'); /*We call database options*/
    if(isset($_GET['auth']) AND !isset($_GET['token'])){
       
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
                $msg = 'The '.$column.' "'.$username.'" is already registered!';
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
                    $msg = 'Successfull register';
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
                $job = 'ok';
                $msg = 'Invalid username or password';
            }else if($UserAuth['status'] == 'pending'){
                $job = 'no';
                $rep -> job = $job;
                $msg = 'Your account is not verified!';
            } else{
                $job = 'ok';
                $rep -> job = $job;
                $user_id = $UserAuth['id'];
                $id =  $user_id;
                $rep -> id  = $id;
                $access = $UserAuth['access'];
                $level = $UserAuth['level'];
                $rep ->  access =  $access;
                $rep -> level  = $level;
                $msg = 'Successfull login';
            }
		}else if($_GET['auth'] == 'reset'){
            $username = $_POST['username'];
            $readUserId = $db -> query('SELECT * FROM users WHERE `email` = \''.$username.'\' OR `phone` = \''.$username.'\'') or die(print_r($db->errorinfo()));
            $UserId = $readUserId -> fetch();
            if(!$UserId){
                $job = 'no';
                $rep -> job = $job;
                $msg = '"'.$username.'" doesn\'t assigned to any account !';
            }else{
               
                $temp = uniqid();
                $updateAuth = $db -> query('UPDATE `users` SET `temp` =  \''.$temp.'\' WHERE `id` = \''.$UserId['id'].'\' ');
                if($updateAuth){
                    $job = 'ok';
                    $rep -> job = $job;
                     // send email 
                    $msg = 'A verification link has been sent to '.$username;
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
                $rep -> job = $job;
                 // send email 
                $msg = 'Password updated successfuly';
            }else{
                $job = 'no';
                $rep -> job = $job;
                 // send email 
                $msg = 'Token expired';
            }
        }
    }else if($_GET['auth'] == 'register' AND isset($_GET['token'])){
        $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.temp = \''.$_GET['token'].'\' AND users_auth.token = \''.$_GET['token'].'\'') or die(print_r($db->errorinfo()));
        $UserAuth = $readUserAuth -> fetch();
        if(!$UserAuth){
            $job = 'no';
            $rep -> job = $job;
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
                $rep -> job = $job;
                $user_id = $UserAuth['id'];
                $id =  $user_id;
                $rep -> id  = $id;
                $access = $UserAuth['access'];
                $level = $UserAuth['level'];
                $rep ->  access =  $access;
                $rep -> level  = $level;
                $msg = 'Successfull verification';
            }else{
                $msg = '';
                $job ='no';
                $rep -> job = $job;
            }
        }
    }else{
        $job ='no';
        $rep -> job = $job;
        $msg ='Bad Request!';
    }

$rep -> msg  = $msg;
die(json_encode($rep));
?>