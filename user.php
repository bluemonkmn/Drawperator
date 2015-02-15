<?php
include_once "connect.php";

try {
switch($_SERVER['REQUEST_METHOD']) {
case 'GET':
   if (!empty($_REQUEST['request']) && preg_match('/^([^\/]+)(?:\/([^\/]+)?)?$/', $_REQUEST['request'], $matches)) {
      $idtype = $matches[1];
      $id = $matches[2];
   }

   $sql = 'SELECT id, name, googleid FROM tg_user';
   
   if (strlen($id) > 0)
      $idSpecified = true;
   else
      $idSpecified = false;
   
   if ($idtype == 'google') {
      if ($idSpecified)
         $sql .= ' WHERE googleid=?';
      else
         $sql .= ' WHERE googleid IS NOT NULL';
   } else if ($idtype == 'name') {
      if ($idSpecified)
         $sql .= ' WHERE name=?';
   } else {
      http_response_code(404);
      header('Content-Type: text/plain');
      echo "$idtype not recognized.";
      exit;
   }

   $stmt = $conn->prepare($sql);
   
   if ($idSpecified)
      $stmt->bindParam(1, $id);

   $stmt->execute();
   header('Content-Type: application/json');
   $results = array();
   while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
      array_push($results, $result);
   }
   if (!$idSpecified)
      echo json_encode($results);
   else
      echo json_encode($results[0]);
   break;
default:
   http_response_code(405);
   header('Allow: GET');
   header('Content-Type: text/plain');
   echo $_SERVER['REQUEST_METHOD'] . ' not allowed.';
   break;
}
} catch (PDOException $e) {
   http_response_code(500);
   header('Content-Type: text/plain');
   echo $e->getMessage();
}
$conn=null;
?>