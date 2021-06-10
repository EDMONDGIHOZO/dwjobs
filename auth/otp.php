<?php include('.cli.php');
$code = $_POST['code'];
$phone = $_POST['phone'];
$otp = sendOtp($user,$api,$pass,$code,$phone,$db);
if($otp){
	$updateAuth = $db -> query('UPDATE `users` SET `otp` =  \''.$otp.'\' WHERE `id` = \''.$UserId['id'].'\' ');
	if($updateAuth){
		$job = 'wait';
		$msg = 'A verification OTP has been sent to '.$code.$phone;
	}else{
		$job = 'no';
		$msg = 'OTP failed to send to "'.$code.$phone.'"!';
	}
}else{
	$job = 'no';
	$msg = 'Failed to send otp! contact admin for otp!';
}
?>