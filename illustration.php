<?php
include_once "connect.php";

$conn = new mysqli($servername, $dbuser, $password, $dbname);
// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
} 

switch($_SERVER['REQUEST_METHOD']) {
case 'GET':
   $cmd = 'list';
   if (!empty($_REQUEST['request']) && preg_match('/^(\d+)(?:\/([^\/]+))?$/', $_REQUEST['request'], $matches)) {
      $id = $matches[1];
      $cmd = $matches[2];
   }
   if ($cmd == 'illustration') {
      $sql = 'SELECT format,illustration FROM tg_illustration';
   } else {
      $sql = 'SELECT tg_illustration.id,format,fk_phrase,clientaddress,name FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id';
      if (isset($id))
         $cmd = 'single';
   }
   
   if (isset($id))
      $sql .= " WHERE tg_illustration.id=?";      

   if ($cmd == 'list')
      echo '<illustrations>';

   if (!($stmt = $conn->prepare($sql))) {
      die ($conn->error);
   }
   if (isset($id))
      $stmt->bind_param('i', $id);
   $stmt->execute();
   if ($cmd == 'illustration') {
      $stmt->bind_result($format,$illustration);
      if (fetch()) {
         switch($format) {
            case 'PNG':
               header('Content-Type: image/png');
               break;
            case 'GIF':
               header('Content-Type: image/gif');
               break;
            case 'SVG':
               header('Content-Type: image/svg+xml');
               break;
         }
         print $illustration;
      }
   } else {
      $stmt->bind_result($id,$format,$fk_phrase,$clientaddress,$illustrationuser);
      while ($stmt->fetch()) {
         ?><illustration id="<?= $id ?>" format="<?= $format ?>" predecessor="<?= $fk_phrase ?>" clientaddress="<?= !empty($clientaddress)?inet_ntop($clientaddress):'' ?>" user="<?= $illustrationuser ?>" />
<?php
      }
   }
   $stmt->close();
   if ($cmd == 'list')
      echo "</illustrations>";
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
   
   if (!($stmt = $conn->prepare('INSERT INTO tg_illustration(id,fk_phrase,illustration,clientaddress,fk_user) VALUES(?,?,BINARY ?,BINARY ?,?)'));
      die ($conn->error);
   $illustrationid = mt_rand() . mt_rand(0,999999999);
   $clientip = inet_pton($_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']));
   $stmt->bind_param('iissi', $illustrationid, $_REQUEST["predecessor"], $_REQUEST["illustration"], $clientip, $uid);
   $stmt->execute();
   $stmt->close();
   ?><illustration id="<?= $illustrationid ?>" predecessor="<?= $_REQUEST["predecessor"] ?>" clientaddress="<?= !empty($clientip)?inet_ntop($clientip):'' ?>" user="<?= $username ?>" />
<?php   
   break;
}
$conn->close();
?>