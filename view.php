<?php
include_once "connect.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title>Drawperator</title>
<link rel="stylesheet" type="text/css" href="<?= "$baseUri/drawperator.css" ?>" />
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
$suppressOutput = false;

if (isset($illustration)) {
   $sql = 'SELECT tg_phrase.id, name user, googleid, timestamp
      FROM tg_phrase JOIN tg_user ON fk_user = tg_user.id WHERE fk_illustration=? LIMIT 20';
   $stmt=$conn->prepare($sql);
   $stmt->execute(array($illustration));
   while($result=$stmt->fetch()) {
      if (empty($output))
         $output = "<p>Others have already described this illustration, leaving you with these choices:</p><ol>\n";
      $ts = strtotime($result['timestamp']);
      $entry = "<li><a class=\"button\" href=\"$baseUri/view.php?phrase=" . $result['id'];
      if ($_REQUEST['warn'])
         $entry .= '&warn=1';
      $entry .= "\">Go to</a> ";      
      if (isset($result['googleid'])) {
         $entry .= "<a href=\"https://plus.google.com/" . $result['googleid'] . "\">"
            . htmlspecialchars($result['user']) . "</a>";
      } else {
         $entry .= htmlspecialchars($result['user']);
      }
      $entry .= "'s description from <time datetime=\"" 
         . date(DATE_ISO8601, $ts) . "\">"
         . date('l, F j, Y g:i:s a T', $ts) . "</time>";
      $entry .= "</li>";
      $output .= $entry;
   }
} else if (isset($phrase)) {
   $sql = 'SELECT tg_illustration.id, name user, googleid, timestamp
      FROM tg_illustration JOIN tg_user ON fk_user = tg_user.id WHERE fk_phrase=? LIMIT 20';
   $stmt=$conn->prepare($sql);
   $stmt->execute(array($phrase));
   while($result=$stmt->fetch()) {
      if (empty($output))
         $output = "<p>Others have already illustrated this phrase, leaving you with these choices:</p><ol>\n";
      $ts = strtotime($result['timestamp']);
      $entry = "<li><a class=\"button\" href=\"$baseUri/view.php?illustration=" . $result['id'];
      if ($_REQUEST['warn'])
         $entry .= '&warn=1';
      $entry .= "\">Go to</a> ";
      if (isset($result['googleid'])) {
         $entry .= "<a href=\"https://plus.google.com/" . $result['googleid'] . "\">"
            . htmlspecialchars($result['user']) . "</a>";
      } else {
         $entry .= htmlspecialchars($result['user']);
      }
      $entry .= "'s illustration from <time datetime=\"" 
         . date(DATE_ISO8601, $ts) . "\">"
         . date('l, F j, Y g:i:s a T', $ts) . "</time>";
      $entry .= "</li>\n";
      $output .= "$entry";
   }
}

if (!empty($output)) {
   if ($_REQUEST['warn']) {
      $suppressOutput = true;
      $entry = '';
      if ($illustration) {
         $entry .= "<li><a class=\"button\" href=\"$baseUri/view.php?illustration=$illustration&list=1\">Expose</a> this illustration and predecessors</li>\n";
         $entry .= "<li><a class=\"button\" href=\"$baseUri/view.php?illustration=$illustration\">View</a> this illustration by itself</li>\n";
         $entry .= "<li><a class=\"button\" href=\"$baseUri/index.html?phrase&predecessor=$illustration\">Add</a> another description for the illustration</li>\n";
      } else if ($phrase) {
         $entry .= "<li><a class=\"button\" href=\"$baseUri/view.php?phrase=$phrase&list=1\">Expose</a> this phrase and predecessors</li>\n";
         $entry .= "<li><a class=\"button\" href=\"$baseUri/view.php?phrase=$phrase\">View</a> this phrase by itself</li>\n";
         $entry .= "<li><a class=\"button\" href=\"$baseUri/index.html?illustration&predecessor=$phrase\">Add</a> another illustration for this phrase</a>.</li>\n";
      }
      $output .= $entry;
   }
   $output .= "</ol>\n";
} else {
   if ($illustration)
      $output .= "<p><a class=\"button\" href=\"$baseUri/index.html?phrase&predecessor=$illustration\">Add</a> a description</p>\n";
   else if ($phrase)
      $output .= "<p><a class=\"button\" href=\"$baseUri/index.html?illustration&predecessor=$phrase\">Add</a> an illustration</p>\n";
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
         if ($suppressOutput)
            $entry .= " drew: <a href=\"$baseUri/view.php?illustration=$illustration\">Click to reveal illustration</a></p>\n";
         else
            $entry .= " drew:</p>\n<img class=\"reviewImage\" width=\"400\" height=\"400\" itemprop=\"image\" src=\"$baseUri/illustration/$illustration/illustration\" />\n";
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
         if ($suppressOutput)
            $entry .= " wrote: <a href=\"$baseUri/view.php?phrase=$phrase\">Click to reveal phrase</a></p>\n";
         else
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
   $entry = "<a href=\"$baseUri/view.php?illustration=$illustration";
   if ($_REQUEST['warn'])
      $entry .= "&warn=1";
   $entry .= "\">Previous illustration</a>\n";
   $output = $entry . $output;
} else if (isset($phrase)) {
   $entry = "<a href=\"$baseUri/view.php?phrase=$phrase";
   if ($_REQUEST['warn'])
      $entry .= "&warn=1";
   $entry .= "\">Previous phrase</a>\n";
   $output = $entry . $output;
}
echo $output;
?>
</body>
</html>