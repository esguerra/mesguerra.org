<html>
<head>
<title>Flat Calendar: Delete Event</title>
<META HTTP-EQUIV="refresh" content="1;URL=../calendar.php">
<LINK rel="stylesheet" type="text/css" name="style" href="../calendar.css">
</head>

<body bgcolor="#FFFFFF" text="#000000">


<?php
// Include the FFDB library
include("../ffdb.inc.php");

//open db or create new db
$db = new FFDB();
if (!$db->open("../calendar"))
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

   // Try and create it...
   if (!$db->create("calendar", $schema))
   {
      echo "Error creating database\n";
      return;
   }
}



   if (!$db->exists($eventNumber))
      echo("Item does not exist!\n");
   else
   {
      // Delete the item
      if (!$db->removebykey($eventNumber))
         echo("Unable to delete item '".$eventNumber."'.\n");
      else
        {



//table to display after adding
  $addedTable ="


 <center><font class=\"back\">Record Deleted: taking you back</font> </center>
  <table cellpadding=\"0\" cellspacing=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\"><tr><td>
  <table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" bgcolor=\"#CC0000\" align=\"center\">
<tr><td><font class=\"addHead\"><a href=\"calendar.php\" class=\"addHead\">Calendar</a></font></TD></tr>
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
<tr><td align=\"right\"></td></tr>
</table>
</td></tr></table>

";

  echo $addedTable;
  }
  }
?>
</body>
</html>