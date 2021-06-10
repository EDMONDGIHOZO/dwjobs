<?php 
function sendOtp($user,$api,$pass,$code,$phone,$db){
	function generateRandomString($length = 4) {
		return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
	}
	$otp =   generateRandomString();
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
		$adOtp = $db -> query('INSERT INTO `users_otp`(`user`, `otp`, `date_added`) VALUES (\''.$userinfo.'\',\''.$otp.'\',\''.date('Y-m-d h:i:s').'\')');
		return ($otp);
	}
}
?>