<html>
<head>
<title>Flat Calendar: Add</title>
<?php
if(ereg("[a-zA-Z0-9]",$event))
{
?>

<META HTTP-EQUIV="refresh" content="1;URL=../calendar.php">
<LINK rel="stylesheet" type="text/css" name="style" href="../calendar.css">
</head>

<body bgcolor="#FFFFFF" text="#000000">



<?php


//returns highest key in the database
function getMaxKey($db) {

$maxKey = 0;

$sortby = "event_key";
$result = $db->getall();

foreach($result as $item){
           $key = $item["event_key"];
           if($key > $maxKey)
              $maxKey = $key;
}

return $maxKey;

}

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

//if no key file create a new one
if(!file_exists("key.dat"))
{
 $newKey = getMaxKey($db);
 $newFile = fopen("key.dat", "w") Or die("Can't open file");
 fwrite($newFile,$newKey);
 fclose($newFile);

}

//add a record
      //convert forms to record
$fileread = fopen("key.dat", "r")Or die("Can't open file");
$data = (int) fread($fileread, 10);
fclose($fileread);
$data++;
$fileread = fopen("key.dat", "w") Or die("Can't open file");
fwrite($fileread,$data);
fclose($fileread);

//removes escape slashes
   $event = stripslashes($event);
   $description = stripslashes($description);
   $submitted = stripslashes($submitted);

//add html entities
   $event = htmlentities($event,ENT_QUOTES);
   $submitted = htmlentities($submitted,ENT_QUOTES);

   $record["event_key"] = $data;
   $record["event_name"] = $event;
   $record["event_description"] = $description;
   $record["event_submitted_by"] = $submitted;
   $record["event_month"] = $month;
   list($record["event_day"]) = sscanf($day, "%d"); // string -> int
   list($record["event_year"]) = sscanf($year, "%d"); // string -> int

      // Add a _new_ entry
      echo("");
      if (!$db->add($record))
         echo("failed!\n");
      else {



//table to display after adding
  $addedTable ="


      <center><font class=\"back\">Record Added: taking you back</font> </center>
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
else {
     echo "You must enter an event name. Please go back to the previous
     page and do so.";
}
?>
</body>
</html>
