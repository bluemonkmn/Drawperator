<?php
include_once "connect.php";
header('Content-Type: application/json');

try {
   $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbuser, $password);
} catch (PDOException $e) {
   die("Connection failed: " . $e->getMessage());
} 

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

switch($_SERVER['REQUEST_METHOD']) {
case 'GET':
   if (!empty($_REQUEST['request']) && preg_match('/^\d+$/', $_REQUEST['request'], $matches)) {
      $id = $matches[0];
   }
   $sql = 'SELECT tg_phrase.id,fk_illustration predecessor,phrase,clientaddress,name user,timestamp FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id';
   $single = false;
   if (isset($id)) {
      $single = true;
      $sql .= " WHERE tg_phrase.id=?";      
   }
   if (!($stmt = $conn->prepare($sql)))
      die ($conn->error);
   if ($single)
      $stmt->bind_param('i', $id);
   $stmt->execute();
   $stmt->bind_result($id,$fk_illustration,$phrase,$clientaddress,$phraseuser);
   $rowset = $stmt->get_result();
   $results = array();
   while ($result = $rowset->fetch_array(MYSQL_ASSOC)) {
      $result['clientaddress'] = !empty($result['clientaddress']) ? inet_ntop($result['clientaddress']) : '';
      // javascript can't handle bigint
      $result['id'] = (string)$result['id'];
      $result['predecessor'] = (string)$result['predecessor'];
      array_push($results, $result);
   }
   $stmt->close();
   if ($single)
      echo json_encode($results[0]);
   else
      echo json_encode($results);
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
   
   if (!($stmt = $conn->prepare('INSERT INTO tg_phrase(id,fk_illustration,phrase,clientaddress,fk_user) VALUES(?,?,?,BINARY ?,?)
      SELECT timestamp FROM tg_phrase WHERE id=?')))
      die ($conn->error);
   $result = array('id' => mt_rand() . mt_rand(0,999999999),
      'predecessor' => $_REQUEST['predecessor'],
      'phrase' => $_REQUEST['phrase'],
      'clientaddress' => $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']),
      'user' => $username);
   $stmt->bind_param('iissi', $result['id'], $result['predecessor'], $result['phrase'], inet_pton($result['clientaddress']), $uid);
   $stmt->execute();
   $stmt->close();
   // javascript can't handle bigint
   $result['id'] = (string)$result['id'];
   $result['predecessor'] = (string)$result['predecessor'];
   echo json_encode($result);
   break;
}
$conn->close();
?>