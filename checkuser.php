<?php
function CheckUser() {
   global $username, $googleid, $uid, $conn;
   if (empty($username))
      die("User name required.");
   if (empty($googleid)) {
      $googleid = null;
      $stmt = $conn->prepare('SELECT id, name, googleid FROM tg_user WHERE name=? and googleid IS NULL');
      $stmt->execute(array($username));
   } else {
      $stmt = $conn->prepare('SELECT id, name, googleid FROM tg_user WHERE googleid=?');
      $stmt->execute(array($googleid));
   }
   if (!($result=$stmt->fetch())) {
      $stmt2 = $conn->prepare('INSERT INTO tg_user(name, googleid) VALUES(?,?)');
      $stmt2->execute(array($username, $googleid));
      $uid = $conn->lastInsertId();
   } else {
      $uid = $result['id'];
      if ($result['name'] != $username) {
         $stmt2 = $conn->prepare('UPDATE tg_user SET name=? WHERE id=?');
         $stmt2->execute(array($username, $uid));
      }
   }
   if (empty($uid))
      die("Failed to get user id.");
}
?>