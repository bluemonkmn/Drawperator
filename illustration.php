<?php
include_once "connect.php";
include_once "checkuser.php";

try {
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
      $sql = 'SELECT tg_illustration.id,format,fk_phrase predecessor,clientaddress,name user, timestamp
         FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id';
      if (isset($id))
         $cmd = 'single';
   }

   if (isset($id))
      $sql .= " WHERE tg_illustration.id=?";      

   $stmt = $conn->prepare($sql);
   
   if (isset($id))
      $stmt->bindParam(1, $id);
   $stmt->execute();
   if ($cmd == 'illustration') {
      if ($row = $stmt->fetch()) {
         switch($row['format']) {
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
         print $row['illustration'];
      }
   } else {
      header('Content-Type: application/json');
      $results = array();
      while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
         $result['clientaddress'] = $result['clientaddress'] ? inet_ntop($result['clientaddress']) : '';
         // javascript can't handle bigint
         $result['id'] = (string)$result['id'];
         $result['predecessor'] = (string)$result['predecessor'];
         array_push($results, $result);
      }
   }
   if ($cmd == 'list')
      echo json_encode($results);
   elseif ($cmd == 'single')
      echo json_encode($results[0]);
   break;
case 'POST':
   CheckUser();
   
   if (!($illustration = base64_decode($_REQUEST['illustration'])))
      die("Failed to decode picture data");
   
   $stmt = $conn->prepare('INSERT INTO tg_illustration(id,fk_phrase,format,illustration,clientaddress,fk_user)
      VALUES(:id,:predecessor,:format,BINARY :illustration,BINARY :clientaddress,:user)');
   $id = mt_rand() . mt_rand(0,999999999);
   $stmt->execute(array(':id' => $id,
      ':predecessor' => $_REQUEST['predecessor'],
      ':format' => $_REQUEST['format'],
      ':illustration' => $illustration,
      ':clientaddress' => inet_pton($_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP'])),
      ':user' => $uid));
   $stmt = $conn->prepare('SELECT i.id,i.fk_phrase,i.format,i.clientaddress,u.name,i.timestamp
      FROM tg_illustration i JOIN tg_user u ON i.fk_user = u.id
      WHERE i.id=?');
   $stmt->execute(array($id));
   if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $result['clientaddress'] = $result['clientaddress'] ? inet_ntop($result['clientaddress']) : '';
      // javascript can't handle bigint
      $result['id'] = (string)$result['id'];
      $result['predecessor'] = (string)$result['predecessor'];
      header('Content-Type: application/json');
      echo json_encode($result);
   } else {
      http_response_code(500);
      header('Content-Type: text/plain');
      echo "Failed to retrieve inserted row $id user $uid";
   }
   break;
}
} catch (PDOException $e) {
   http_response_code(500);
   header('Content-Type: text/plain');
   echo $e->getMessage();
}
$conn=null;
?>