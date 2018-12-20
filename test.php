<?php
$ip_address=$_SERVER['REMOTE_ADDR'];

$geopluginURL='http://www.geoplugin.net/php.gp?ip='.$ip_address;
$addrDetailsArr = unserialize(file_get_contents($geopluginURL));

$country_code = $addrDetailsArr['geoplugin_countryCode'];
 
/*Comment out these line to see all the posible details*/
/*echo '<pre>';
print_r($addrDetailsArr);
die();*/

echo '<strong>Country</strong>:- '.$country.'<br/>';
if($country_code == "IN"){
	echo "India";
}

die();
echo phpinfo();
//~ ini_set('display_errors', 0);
//~ mail("manju.softprodigy@gmail.com","My subject",'test');
?>
