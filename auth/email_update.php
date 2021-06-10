<?php 
$to = $username;
if (@mail($to, $subject, wordwrap($message), $headers,"-f no-reply@".DOM)){
	$job = 'ok';
	$msg = 'Password updated successfuly';
}else{
	$job = 'no';
	$msg = 'Failed email notification!';
}
?>