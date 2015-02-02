<?php
$refresh = false;
if (file_exists('oauthkey')) {
   $age = time() - filemtime('oauthkey');
   if ($age > 20000)
      $refresh = true;   
} else
   $refresh = true;

if ($refresh) {
   $oauthKey = file_get_contents('https://www.googleapis.com/oauth2/v1/certs')
      or die('Failed to retrieve google public key.');
   $keyFile = fopen('oauthkey', 'w') or die ('Failed to open public key file for writing.');
   fwrite($keyFile, $oauthKey);
   fclose($keyFile);
} else {
   $keyFile = fopen('oauthkey', 'r') or die ('Failed to open public key file for reading.');
   $oauthKey = fread($keyFile, 5000) or die ('Failed to read from public key file.');
   fclose($keyFile);   
}
$oauthKey = json_decode($oauthKey, true);
?>