<html>
<head>
<title>Flat Calendar: View All</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" type="text/css" name="style" href="calendar.css">
</head>

<body bgcolor="#FFFFFF" text="#000000">

<BR><BR>

<?php
//always returns true so all records will be returned
function returnAll($item){
         if($item)
          return true;
}

//displays record
function show_record($record){
         $eventNumber = $record["event_key"];
         $eventName = $record["event_name"];
         $eventYear = $record["event_year"];
         $eventDay = $record["event_day"];
         $eventMonth = $record["event_month"];


         echo "<tr><td width=\"40\">&nbsp;</td>
         <td width=\"150\"><a href=\"viewEvent.php?eventNumber=$eventNumber\" class=\"viewall\">$eventName</a></td>
         <td width=\"150\"><font class=\"viewall\">$eventMonth $eventDay, $eventYear</font></td>
         <td width=\"50\"><a href=\"admin/deleteEvent.php?eventNumber=$eventNumber\" class=\"delete\">Delete</a></td></tr>";
}

// Include the FFDB library
include("ffdb.inc.php");

//open db or create new db
$db = new FFDB();
if (!$db->open("calendar"))
{
   // Define the database shema.
   // Note that the "last_name" field is our key.
   $schema = array(
      array("event_key", FFDB_INT, "key"),
      array("event_name", FFDB_STRING),
      array("event_description", FFDB_STRING),
      array("event_submitted_by", FFDB_STRING),
      array("event_month", FFDB_STRING),
      array("event_day", FFDB_INT),
      array("event_year", FFDB_INT)
   );
}

echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" bgcolor=\"#CC0000\" align=\"center\"><tr>
<td><font class=\"addHead\"><a href=\"calendar.php\" class=\"addHead\">Calendar</a> : All Events</font>
<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" bgcolor=\"#e9e9e9\" align=\"center\">
";
$result = $db->getbyfunction("returnAll", "event_key");

foreach($result as $item)
   show_record($item);
echo "</table></td></tr></table>
";
?>

</BODY>
</HTML>