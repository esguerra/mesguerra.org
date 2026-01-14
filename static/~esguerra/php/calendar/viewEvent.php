<html>
<head>
<title>Flat Calendar: View Event</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" type="text/css" name="style" href="calendar.css">
</head>

<body bgcolor="#FFFFFF" text="#000000">

<BR><BR>
<?php
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

//outputs an eventView table
function viewEvent($eventNumber, $db){
      $record = $db->getbykey($eventNumber);
   if (!$record)
   {
        $event = "No Event";
        $description = "n/a";
        $day = "";
        $month = "";
        $year = "";
        $submitted = "Master Josh";
   }
  else {
         $event = $record["event_name"];
         $description = $record["event_description"];
         $day = $record["event_day"];
         $month = $record["event_month"];
         $year = $record["event_year"];
         $submitted = $record["event_submitted_by"];
       }
echo "
  <form action=\"admin/eventEdit.php\" method=\"post\">
  <table cellpadding=\"0\" cellspacing=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\"><tr><td>
  <table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" bgcolor=\"#CC0000\" align=\"center\">
<tr><td><font class=\"addHead\"><a href=\"calendar.php\" class=\"addHead\">Calendar</a> : $event</font></TD></tr>
<tr><td>

<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" bgcolor=\"#CCCCCC\">

<tr>
        <td width=\"150\" align=\"right\" height=\"26\"><font class=\"AddLeft\">Event : </font></td>
        <td width=\"350\" height=\"26\"><font class=\"AddRight\">$event</font></td>
</tr>

<tr bgcolor=\"#E3E3E3\">
    <td width=\"150\" align=\"right\"><font class=\"AddLeft\">Event Description : </font></td>
    <td width=\"350\"><font class=\"AddRight\">$description</font></td>
</tr>

<tr>
        <td width=\"150</h5>\" align=\"right\"><font class=\"AddLeft\">Date : </font></td>
        <td width=\"350\" ><font class=\"AddRight\">$day $month $year</font></td>
</tr>

<tr bgcolor=\"#E3E3E3\">
        <td width=\"150\" align=\"right\"><font class=\"AddLeft\">Submitted By : </font></td>
        <td width=\"350\"><font class=\"AddRight\">$submitted</font></td>
</tr>
</table>
</td><tr>
<tr><td align=\"right\">
<input type=\"hidden\" name=\"eventNumber\" value=\"$eventNumber\">
<input type=\"submit\" value=\"Edit Event\"></td></tr>
</table>
</td></tr></table>
</form>
";
}

//prints header of the HTML file
function printHead()
{
 echo "
<html>
<head>
<title>OISSS: Calendar: Event View</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
<LINK rel=\"stylesheet\" type=\"text/css\" name=\"style\" href=\"calendar.css\">
</head>

<body bgcolor=\"#FFFFFF\" text=\"#000000\">

";

}

//prints the footer of the HTML file
function printFoot(){
echo "

</center>

";
}

printHead();
viewEvent($eventNumber,$db);
printFoot();

?>
</body>
</html>