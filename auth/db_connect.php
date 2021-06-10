<?php include('.db_auth.php'); try{$db = new PDO('mysql:host='.$HOST.';dbname='.$DBNAME.'',''.$USER.'',''.$PASSWORD.'') or die(print_r($db->errorInfo()));$db->exec("SET NAMES 'utf8';");}catch(Exception $e){die('Error:' .$e->GetMessage());} 
date_default_timezone_set('Africa/Kigali');
$rep = new stdClass();
DEFINE('DOM',$_SERVER['SERVER_NAME']);