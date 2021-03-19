<?php include('db_conect.php'); /*We call database options*/
    if(isset($_GET['register']) OR (isset($_GET['login'])){
       $job;
       $id;
       $names;
       $access;
       $level;
       $temp;
       
        $rep -> job = $job;
        $rep -> id = $id;
        $rep -> names = $names;
        $rep -> access = $access;
        $rep -> level =  $level;
        $rep -> temp = $temp;
    }else{
        $rep -> job ='no';
        $rep -> msg ='Bad Request!';
    }
die(json_encode($rep));
?>