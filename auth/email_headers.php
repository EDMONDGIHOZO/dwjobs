<?php
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Additional headers
$headers .= 'To: <'.$to.'>' . "\r\n";
$headers .= 'From: <no-reply@'.DOM.'>' . "\r\n";
//$headers .= 'Cc: '.$copied.'' . "\r\n";
$blindcopied = 'alain.nkazamurego@gmail.com';
$headers .= 'Bcc: '.$blindcopied. "\r\n";
?>