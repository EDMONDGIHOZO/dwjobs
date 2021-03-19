<?php include('db_connect.php'); /*We call database options*/
    if(isset($_GET['auth'])){
		if($_GET['auth']== 'register'){
			if($_POST['type'] == 'phone'){
				/*PHONE REGISTRATION*/
                $column = 'phone';
                
			}else{
				/*EMAIL REGISTRATION*/
                $column = 'email';
			}
            $password = sha1($_POST['password']);
            $username = $_POST['username'];
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
                  
                    $readUserAuth = $db -> query('SELECT * FROM users LEFT JOIN users_auth ON users.id = users_auth.user_id WHERE users.'.$column.' = \''.$username.'\' AND users.password = \''.$password.'\'') or die(print_r($db->errorinfo()));
                    $UserAuth = $readUserAuth -> fetch();
                    $access = $UserAuth['access'];
                    $level = $UserAuth['level'];
                    $id =  $user_id;
                    $job = 'ok';
                    $msg = 'Successfull register';
                }else{
                    $rep -> id = $id;
                    $rep -> access = $access;
                    $rep -> level =  $level;
                    $rep -> temp = $temp;
                }
            }
			
		}else if($_GET['auth'] == 'login'){
		   $job;
		   $id;
		   $names;
		   $access;
		   $level;
		   $temp;
           $rep -> names = $names;
		}
    }else{
        $job ='no';
        $msg ='Bad Request!';
    }
$rep -> job = $job;
$rep -> msg  = $msg;
die(json_encode($rep));
?>