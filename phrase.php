<?php
include_once "connect.php";
include_once "checkuser.php";

try {
switch($_SERVER['REQUEST_METHOD']) {
case 'GET':
   if (!empty($_REQUEST['request']) && preg_match('/^\d+$/', $_REQUEST['request'], $matches)) {
      $id = $matches[0];
   }
   $sql = 'SELECT tg_phrase.id,fk_illustration predecessor,phrase,clientaddress,name user,googleid,timestamp
   FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id';
   $single = false;
   if (isset($id)) {
      $single = true;
      $sql .= " WHERE tg_phrase.id=?";      
   }
   $stmt = $conn->prepare($sql);
   if ($single)
      $stmt->bindParam(1, $id);
   $stmt->execute();
   $results = array();
   while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $result['clientaddress'] = !empty($result['clientaddress']) ? inet_ntop($result['clientaddress']) : '';
      // javascript can't handle bigint
      $result['id'] = (string)$result['id'];
      $result['predecessor'] = (string)$result['predecessor'];
      array_push($results, $result);
   }
   header('Content-Type: application/json');
   if ($single)
      echo json_encode($results[0]);
   else
      echo json_encode($results);
   break;
case 'POST':
   CheckUser();
   $stmt = $conn->prepare('INSERT INTO tg_phrase(id,fk_illustration,phrase,clientaddress,fk_user)
      VALUES(:id,:predecessor,:phrase,BINARY :clientaddress,:user)');
   $id = mt_rand() . mt_rand(0,999999999);
   $stmt->execute(array(':id' => $id,
      ':predecessor' => $_REQUEST['predecessor'],
      ':phrase' => $_REQUEST['phrase'],
      ':clientaddress' => inet_pton($_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP'])),
      ':user' => $uid));
   $stmt = $conn->prepare("SELECT p.id, p.fk_illustration predecessor, p.phrase, p.clientaddress, u.name user, u.googleid, p.timestamp
      FROM tg_phrase p JOIN tg_user u on p.fk_user = u.id WHERE p.id=?");
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
$conn = null;
?>