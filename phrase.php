<?php
include_once "connect.php";
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo "\n";

$conn = new mysqli($servername, $dbuser, $password, $dbname);
// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
} 

switch($_SERVER['REQUEST_METHOD']) {
case 'GET':
   if (!empty($_REQUEST['request']) && preg_match('/^\d+$/', $_REQUEST['request'], $matches)) {
      $id = $matches[0];
   }
   $sql = 'SELECT tg_phrase.id,fk_illustration,phrase,clientaddress,name FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id';
   $single = false;
   if (isset($id)) {
      $single = true;
      $sql .= " WHERE tg_phrase.id=?";      
   } else
      echo "<phrases>";
   if (!($stmt = $conn->prepare($sql))) {
      die ($conn->error);
   }
   if ($single)
      $stmt->bind_param('i', $id);
   $stmt->execute();
   $stmt->bind_result($id,$fk_illustration,$phrase,$clientaddress,$phraseuser);
   while ($stmt->fetch()) { ?>
<phrase id="<?= $id ?>" predecessor="<?= $fk_illustration ?>" phrase="<?= htmlspecialchars($phrase)?>" clientaddress="<?= !empty($clientaddress)?inet_ntop($clientaddress):'' ?>" user="<?= $phraseuser ?>" />
<?php
   }
   $stmt->close();
   if (!$single)
      echo "</phrases>";
   break;
case 'POST':
   if (empty($username))
      die("User name required.");
   if (!($stmt = $conn->prepare('SELECT id FROM tg_user WHERE name=?'));
      die ($conn->error);   
   $stmt->bind_param('s', $username);
   $stmt->execute();
   $stmt->bind_result($uid);   
   if (!$stmt->fetch()) {
      if (!($stmt2 = $conn->prepare('INSERT INTO tg_user(name) VALUES(?)')));
         die ($conn->error);
      $stmt2->bind_param('s', $username);
      $stmt2->execute();
      $uid = $conn->insert_id;
      $stmt2->close();
   }
   $stmt->close();
   if (empty($uid))
      die("Failed to get user id.");
   
   if (!($stmt = $conn->prepare('INSERT INTO tg_phrase(id,fk_illustration,phrase,clientaddress,fk_user) VALUES(?,?,?,BINARY ?,?)')));
      die ($conn->error);   
   $phraseid = mt_rand() . mt_rand(0,999999999);
   $clientip = inet_pton($_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']));
   $stmt->bind_param('iissi', $phraseid, $_REQUEST["predecessor"], $_REQUEST["phrase"], $clientip, $uid);
   $stmt->execute();
   $stmt->close();?>
<phrase id="<?= $phraseid ?>" predecessor="<?= $_REQUEST["predecessor"] ?>" phrase="<?= htmlspecialchars($_REQUEST["phrase"])?>" clientaddress="<?= !empty($clientip)?inet_ntop($clientip):'' ?>" user="<?= $username ?>" />
<?php   
   break;
}
$conn->close();
?>