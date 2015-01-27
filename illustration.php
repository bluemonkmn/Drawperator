<?php
include_once "connect.php";

try {
   $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $password);
} catch (PDOException $e) {
   die("Connection failed: " . $e->getMessage());
} 

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
      $sql = 'SELECT tg_illustration.id,format,fk_phrase predecessor,clientaddress,name user FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id';
      if (isset($id))
         $cmd = 'single';
   }

   if (isset($id))
      $sql .= " WHERE tg_illustration.id=?";      

   if (!($stmt = $conn->prepare($sql)))
      die ($conn->error);
   if (isset($id))
      $stmt->bind_param('i', $id);
   $stmt->execute();
   if ($cmd == 'illustration') {
      $stmt->bind_result($format,$illustration);
      if ($stmt->fetch()) {
         switch($format) {
            case 'GIF':
               header('Content-Type: image/gif');
               break;
            case 'SVG':
               header('Content-Type: image/svg+xml');
               break;
            default:
               header('Content-Type: image/png');
               break;
         }
         print $illustration;
      }
   } else {
      header('Content-Type: application/json');
      $rowset = $stmt->get_result();
      $results = array();
      while ($result = $rowset->fetch_array(MYSQL_ASSOC)) {
         $result['clientaddress'] = $result['clientaddress'] ? inet_ntop($result['clientaddress']) : '';
         // javascript can't handle bigint
         $result['id'] = (string)$result['id'];
         $result['predecessor'] = (string)$result['predecessor'];
         array_push($results, $result);
      }
   }
   $stmt->close();
   if ($cmd == 'list')
      echo json_encode($results);
   elseif ($cmd == 'single')
      echo json_encode($results[0]);
   break;
case 'POST':
   if (empty($username))
      die("User name required.");
   if (!($stmt = $conn->prepare('SELECT id FROM tg_user WHERE name=?')))
      die ($conn->error);
   $stmt->bind_param('s', $username);
   $stmt->execute();
   $stmt->bind_result($uid);   
   if (!$stmt->fetch()) {
      if (!($stmt2 = $conn->prepare('INSERT INTO tg_user(name) VALUES(?)')))
         die ($conn->error);      
      $stmt2->bind_param('s', $username);
      $stmt2->execute();
      $uid = $conn->insert_id;
      $stmt2->close();
   }
   $stmt->close();
   if (empty($uid))
      die("Failed to get user id.");
   
   if (!($illustration = base64_decode($_REQUEST['illustration'])))
      die("Failed to decode picture data");
   
   if (!($stmt = $conn->prepare('INSERT INTO tg_illustration(id,fk_phrase,format,illustration,clientaddress,fk_user) VALUES(?,?,?,BINARY ?,BINARY ?,?)')))
      die ($conn->error);
   $result = array('id' => mt_rand() . mt_rand(0,999999999),
      'format' => $_REQUEST['format'],
      'predecessor' => $_REQUEST['predecessor'],
      'clientaddress' => $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']),
      'user' => $username);
   $stmt->bind_param('iisssi', $result['id'], $result['predecessor'], $result['format'], $illustration, inet_pton($result['clientaddress']), $uid);
   if (!$stmt->execute())
      die($conn->error);
   $stmt->close();
   header('Content-Type: application/json');
   // javascript can't handle bigint
   $result['id'] = (string)$result['id'];
   $result['predecessor'] = (string)$result['predecessor'];
   echo json_encode($result);
   break;
}
$conn->close();
?>