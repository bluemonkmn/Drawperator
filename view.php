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
$output = '';
$remainingMaxCount = $_REQUEST['list'] ? 20 : 1;
$dateThreshold = $_REQUEST['startDate'] ? strtotime($_REQUEST['startDate']) : false;

while($remainingMaxCount > 0) {
   if (isset($illustration)) {
      $sql = 'SELECT name user, timestamp, fk_phrase predecessor
         FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id WHERE tg_illustration.id=?';
      $stmt=$conn->prepare($sql);
      $stmt->execute(array($illustration));
      if($result=$stmt->fetch()) {
         $ts = strtotime($result['timestamp']);
         if ($dateThreshold && ($ts < $dateThreshold))
            break;
         $entry = "<div itemscope itemtype=\"http://schema.org/WebPageElement\">\n";
         $entry .= "<p>Author: <span itemprop=\"author\">" . htmlspecialchars($result['user']) . "</span></p>\n";
         $entry .= "<p>Date: <time itemprop=\"dateCreated\" datetime=\"" .
            date(DATE_ISO8601, $ts) . "\">" .
            date('l, F j, Y g:i:s a T', $ts) . "</time></p>\n";
         $entry .= "<p>Image:<br /><img width=\"400px\" height=\"400px\" itemprop=\"image\" src=\"illustration/$illustration/illustration\" /></p>\n";
         $entry .= "</div>\n";
         $output = $entry . $output;
         $phrase = $result['predecessor'];
      } else {
         unset($phrase);
      }
      unset($illustration);
   }

   if (isset($phrase)) {
      $sql = 'SELECT phrase, name user, timestamp, fk_illustration predecessor
         FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id WHERE tg_phrase.id=?';
      $stmt=$conn->prepare($sql);
      $stmt->execute(array($phrase));
      if($result=$stmt->fetch()) {
         $ts = strtotime($result['timestamp']);
         if ($dateThreshold && ($ts < $dateThreshold))
            break;
         $entry = "<div itemscope itemtype=\"http://schema.org/WebPageElement\">\n";
         $entry .= "<p>Author: <span itemprop=\"author\">" . htmlspecialchars($result['user']) . "</span></p>\n";
         $entry .= "<p>Date: <time itemprop=\"dateCreated\" datetime=\"" .
            date(DATE_ISO8601, $ts) . "\">" .
            date('l, F j, Y g:i:s a T', $ts) . "</time></p>\n";
         $entry .= "<p>Phrase: <span itemprop=\"text\">" . $result['phrase'] . "</span></p>\n";
         $entry .= "</div>\n";
         $output = $entry . $output;
         $illustration = $result['predecessor'];
      } else {
         unset($illustration);
      }
      unset($phrase);
   }
   
   $remainingMaxCount--;
}
echo $output;
?>
</body>
</html>