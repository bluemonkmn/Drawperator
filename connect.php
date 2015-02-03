<?php 
// Set $password outside of public source control
include "secure.php";
include "JWT.php";
include "getkey.php";

try {
   if ($_REQUEST['id_token']) {
      $token = JWT::decode($_REQUEST['id_token'], $oauthKey);
      if ($token->aud != '287420793573-8tg9b6aoq8c89ooctbiqmu89uncddfa0.apps.googleusercontent.com') {
         http_response_code(500);
         header("Content-Type: text/plain");
         die("Authentication failed: token was not intended for this application.");
      }
      $googleid = $token->sub;
   } /* else {
      http_response_code(403);
      header("Content-Type: text/plain");
      die("Authentication required.");
   }*/
} catch (UnexpectedValueException $e) {
   $googleid = null;
}

if (!empty($_REQUEST['username']))
   setcookie("username", $_REQUEST['username'], time() + 60 * 60 * 24 * 7, '/drawperator/');
$servername = "mysql.enigmadream.com";
$dbname = "enigmadream";
$dbuser = "enigmadream_tg";
//  Moved to secure.php
// $password = "****";

if (!empty($_POST['username']))
   $username = $_POST['username'];
elseif (!empty($_REQUEST['username']))
   $username = $_REQUEST['username'];

try {
   $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $password);
} catch (PDOException $e) {
   http_response_code(500);
   header("Content-Type: text/plain");
   die("Connection failed: " . $e->getMessage());
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>