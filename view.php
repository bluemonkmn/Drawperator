<?php
include_once "connect.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Drawperator Viewer</title>
</head>
<body>
<?php
$illustration = $_REQUEST['illustration'];
$phrase = $_REQUEST['phrase'];

if (isset($illustration)) {
   $sql = 'SELECT name user, timestamp
      FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id WHERE tg_illustration.id=?';
   $stmt=$conn->prepare($sql);
   $stmt->execute(array($illustration));
   if($result=$stmt->fetch()) {
      echo "<span itemscope itemtype=\"http://schema.org/WebPageElement\">";
      echo "<p>Author: <span itemprop=\"author\">" . htmlspecialchars($result['user']) . "</span></p>";
      $ts = strtotime($result['timestamp']);
      echo "<p>Date: <time itemprop=\"dateCreated\" datetime=\"" .
         date(DATE_ISO8601, $ts) . "\">" .
         date('l, F j, Y g:i:s a T', $ts) . "</time></p>";
      echo "<p>Image:<br /><img itemprop=\"image\" src=\"illustration/$illustration/illustration\" /></p>";
      echo "</span>";
   }
}

if (isset($phrase)) {
   $sql = 'SELECT phrase, name user, timestamp
      FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id WHERE tg_phrase.id=?';
   $stmt=$conn->prepare($sql);
   $stmt->execute(array($phrase));
   if($result=$stmt->fetch()) {
      echo "<span itemscope itemtype=\"http://schema.org/WebPageElement\">";
      echo "<p>Author: <span itemprop=\"author\">" . htmlspecialchars($result['user']) . "</span></p>";
      $ts = strtotime($result['timestamp']);
      echo "<p>Date: <time itemprop=\"dateCreated\" datetime=\"" .
         date(DATE_ISO8601, $ts) . "\">" .
         date('l, F j, Y g:i:s a T', $ts) . "</time></p>";
      echo "<p>Phrase: <span itemprop=\"text\">" . $result['phrase'] . "</span></p>";
      echo "</span>";
   }
}
?>
</body>
</html>