<?php 
$to = $username;
if (@mail($to, $subject, wordwrap($message), $headers,"-f no-reply@".DOM)){
	$job = 'wait';
	$msg = 'A verification link has been sent to "'.$username.'"';
}else{
	$job = 'no';
	$msg = 'Failed to send verification link! contact admin for verification link!';
}
?>