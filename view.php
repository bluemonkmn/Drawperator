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

if (isset($illustration)) {
   $sql = 'SELECT tg_phrase.id, name user, googleid, timestamp
      FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id WHERE fk_illustration=? LIMIT 20';
   $stmt=$conn->prepare($sql);
   $stmt->execute(array($illustration));
   while($result=$stmt->fetch()) {
      if (empty($output))
         $output = "<p>The following people have already described this illustration:</p><ol>\n";
      $ts = strtotime($result['timestamp']);
      $entry = "<li>";
      if (isset($result['googleid'])) {
         $entry .= "<a href=\"https://plus.google.com/" . $result['googleid'] . "\">"
            . htmlspecialchars($result['user']) . "</a>";
      } else {
         $entry .= htmlspecialchars($result['user']);
      }
      $entry .= " <a href=\"?phrase=" . $result['id'] . "\">";
      $entry .= " described</a> this illustration on <time datetime=\"" 
         . date(DATE_ISO8601, $ts) . "\">"
         . date('l, F j, Y g:i:s a T', $ts) . "</time>";
      $entry .= "</li>";
      $output .= $entry;
   }
   if (!empty($output))
      $output .= "</ol>\n";
} else if (isset($phrase)) {
   $sql = 'SELECT tg_illustration.id, name user, googleid, timestamp
      FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id WHERE fk_phrase=? LIMIT 20';
   $stmt=$conn->prepare($sql);
   $stmt->execute(array($phrase));
   while($result=$stmt->fetch()) {
      if (empty($output))
         $output = "<p>The following people have already illustrated this phrase:</p><ol>\n";
      $ts = strtotime($result['timestamp']);
      $entry = "<li>";
      if (isset($result['googleid'])) {
         $entry .= "<a href=\"https://plus.google.com/" . $result['googleid'] . "\">"
            . htmlspecialchars($result['user']) . "</a>";
      } else {
         $entry .= htmlspecialchars($result['user']);
      }
      $entry .= " <a href=\"?illustration=" . $result['id'] . "\">";
      $entry .= " illustrated</a> this phrase on <time datetime=\"" 
         . date(DATE_ISO8601, $ts) . "\">"
         . date('l, F j, Y g:i:s a T', $ts) . "</time>";
      $entry .= "</li>\n";
      $output .= "$entry";
   }
   if (!empty($output))
      $output .= "</ol>\n";
}

if ($_REQUEST['warn']) {
   if ($output) {
      $entry = "<p>Others have already contributed to this sequence. You may want to follow a sequence ";
      $entry .= "to the end by clicking on illustration or description links below to add your own contribution ";
      $entry .= "at the end before spoiling the surprise, or you may:</p><ol>\n";
      if ($illustration) {
         $entry .= "<li><a href=\"?illustration=$illustration&list=1\">View this illustration and all its predecessors</a></li>\n";
         $entry .= "<li><a href=\"?illustration=$illustration\">View this illustration by itself</a></li>\n";
         $entry .= "<li><a href=\"index.html?phrase&predecessor=$illustration\">Add your own description</a></li>\n";
      } else if ($phrase) {
         $entry .= "<li><a href=\"?phrase=$phrase&list=1\">View this illustration and all its predecessors</a></li>\n";
         $entry .= "<li><a href=\"?phrase=$phrase\">View this illustration by itself</a></li>\n";
         $entry .= "<li><a href=\"index.html?illustration&predecessor=$phrase\">Add your own illustration</a></li>\n";
      }
      $entry .= "</ol>\n";
      $output = $entry . $output;
      $remainingMaxCount = 0;
   }
}

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
   } else if (isset($phrase)) {
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

if (isset($illustration)) {
   $output = "<a href=\"?illustration=$illustration\">Previous illustration</a>\n$output";
} else if (isset($phrase)) {
   $output = "<a href=\"?phrase=$phrase\">Previous phrase</a>\n$output";
}
echo $output;
?>
</body>
</html>