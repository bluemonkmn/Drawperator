<?php 
if (!empty($_REQUEST['username']))
   setcookie("username", $_REQUEST['username'], time() + 60 * 60 * 24 * 7);
$servername = "mysql.enigmadream.com";
$dbname = "enigmadream";
$dbuser = "enigmadream_tg";
$password = "Cowboyabomination265358";

if (!empty($_POST['username']))
   $username = $_POST['username'];
elseif (!empty($_REQUEST['username']))
   $username = $_REQUEST['username'];
?>