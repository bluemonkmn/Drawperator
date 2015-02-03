<?php
include_once "connect.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title>Drawperator</title>
<link rel="stylesheet" type="text/css" href="drawperator.css" />
</head>
<body>
<h1>Drawperator</h1>
<h2>See your drawings and descriptions below</h2>
<?php
$illustration = $_REQUEST['illustration'];
$phrase = $_REQUEST['phrase'];
$output = '';
$remainingMaxCount = $_REQUEST['list'] ? 20 : 1;
$dateThreshold = $_REQUEST['startDate'] ? strtotime($_REQUEST['startDate']) : false;

while($remainingMaxCount > 0) {
   if (isset($illustration)) {
      $sql = 'SELECT name user, googleid, timestamp, fk_phrase predecessor
         FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id WHERE tg_illustration.id=?';
      $stmt=$conn->prepare($sql);
      $stmt->execute(array($illustration));
      if($result=$stmt->fetch()) {
         $ts = strtotime($result['timestamp']);
         if ($dateThreshold && ($ts < $dateThreshold))
            break;
         $entry = "<div itemscope itemtype=\"http://schema.org/WebPageElement\">\n";
         $entry .= "<p class=\"reviewHeading\">On <time itemprop=\"dateCreated\" datetime=\"" .
            date(DATE_ISO8601, $ts) . "\">" .
            date('l, F j, Y g:i:s a T', $ts) . "</time>, ";
         if (isset($result['googleid'])) {
               $entry .= "<a itemprop=\"author\" href=\"https://plus.google.com/"
               . $result['googleid'] . "\">" . htmlspecialchars($result['user']) . "</a>";
         } else {
            $entry .= "<span itemprop=\"author\">" . htmlspecialchars($result['user']) . "</span>";
         }
         $entry .= " drew:</p>\n<img class=\"reviewImage\" width=\"400\" height=\"400\" itemprop=\"image\" src=\"illustration/$illustration/illustration\" />\n";
         $entry .= "</div>\n";
         $output = $entry . $output;
         $phrase = $result['predecessor'];
      } else {
         unset($phrase);
      }
      unset($illustration);
   }

   if (isset($phrase)) {
      $sql = 'SELECT phrase, name user, googleid, timestamp, fk_illustration predecessor
         FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id WHERE tg_phrase.id=?';
      $stmt=$conn->prepare($sql);
      $stmt->execute(array($phrase));
      if($result=$stmt->fetch()) {
         $ts = strtotime($result['timestamp']);
         if ($dateThreshold && ($ts < $dateThreshold))
            break;
         $entry = "<div itemscope itemtype=\"http://schema.org/WebPageElement\">\n";
         $entry .= "<p class=\"reviewHeading\">On <time itemprop=\"dateCreated\" datetime=\"" .
            date(DATE_ISO8601, $ts) . "\">" .
            date('l, F j, Y g:i:s a T', $ts) . "</time>, ";
         if (isset($result['googleid'])) {
               $entry .= "<a itemprop=\"author\" href=\"https://plus.google.com/"
               . $result['googleid'] . "\">" . htmlspecialchars($result['user']) . "</a>";
         } else {
            $entry .= "<span itemprop=\"author\">" . htmlspecialchars($result['user']) . "</span>";
         }
         $entry .= " wrote:</p>\n<p class=\"reviewPhrase\" itemprop=\"text\">" . $result['phrase'] . "</p>\n";
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