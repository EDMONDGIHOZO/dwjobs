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
                $job = 'ok';
                $addUser = $db -> query('INSERT INTO `users`('.$column.', `password`, `temp`) VALUES ( \''.$username.'\', \''.$password.'\', \''.$temp.'\'')  or die(print_r($db->errorinfo()));
                if($addUser){
                    $readUser = $db -> query('SELECT * FROM users WHERE '.$column.' = \''.$username.'\' AND `password` = \''.$password.'\'') or die(print_r($db->errorinfo()));
                    $User = $readUser -> fetch();
                    $access = '';
                    $level = '';
                    $id = '';

                }
                $rep -> access = $access;
                $rep -> level =  $level;
                $rep -> temp = $temp;
                $rep -> job = $job;
                $rep -> id = $id;
                
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
        $rep -> job ='no';
        $rep -> msg ='Bad Request!';
    }
die(json_encode($rep));
?>