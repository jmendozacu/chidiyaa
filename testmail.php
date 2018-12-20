<?php //phpinfo(); die("herer");
 ini_set( 'display_errors', 1 );
 error_reporting( E_ALL );
$to_email = 'hpnaveen.sharma@gmail.com';
$subject = 'Testing PHP Mail Chdiyaa';
$message = 'This mail is sent using the PHP mail function';
$headers = 'From: henryluiis121@gmail.com';
if(mail($to_email,$subject,$message,$headers)){
	echo"mailsend";
	}else{
	//var_dump($ss);
	echo "Not send";	
}

//~ try {
 //~ var_dump(mail($to_email,$subject,$message,$headers));
 //~ echo"herer";
//~ }

//~ catch(Exception $e) {
  //~ echo 'Message: ' .$e->getMessage();
  //~ echo "notsend";
//~ }


?>




