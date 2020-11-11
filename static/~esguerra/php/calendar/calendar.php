<html>
<head>
<title>Flat Calendar</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" type="text/css" name="style" href="calendar.css">
</head>

<body bgcolor="#FFFFFF" text="#000000">

<?php
// Include the FFDB library
include("ffdb.inc.php");
// Include Header
include("header.php");

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
      // Try and create it...
   if (!$db->create("calendar", $schema))
   {
      echo "Error creating database\n";
      return;
   }
}

function today($record)
{          global $event_day;
           global $event_month;
           global $event_year;

         //echo "$event_month $event_day $event_year";
   if (($record["event_month"] == $event_month) &&
           ($record["event_day"] == $event_day) &&
           ($record["event_year"] == $event_year))
          return true;
          return false;
}

function show_event($record){
         $eventNumber = $record["event_key"];
         $eventName = $record["event_name"];
         echo "<font class=\"eventLink\">-<a href=\"viewEvent.php?eventNumber=$eventNumber\" class=\"eventLink\">$eventName</a></font> <br>";
}

function getEvents(){
global $db;
         //get events for today
$result = $db->getbyfunction("today");

//display events if there are any
if($result != null)
foreach($result as $item)
   show_event($item);
}

function days_in_month($_month, $_year)
{

        if($_month == 2)
        {        return days_in_feb($_year);  }

        else {

        if($_month == 1 || $_month == 3 || $_month == 5 || $_month == 7 || $_month == 8 || $_month == 10 || $_month == 12)
             {        return(31);  }
        else {  return(30);  }
        }

}

function selectMonth($_month){

$selectstart = "
<select name=\"sent_month\">  ";
$selectend = "
</select>";

echo $selectstart;

echo "<option value=\"January\"";
       if($_month == 1)
          echo " selected=\"selected\" ";
echo ">January</option>
";

echo "<option value=\"February\"";
       if($_month == 2)
          echo " selected=\"selected\" ";
echo ">February</option>
";

echo "<option value=\"March\"";
       if($_month == 3)
          echo " selected=\"selected\" ";
echo ">March</option>
";

echo "<option value=\"April\"";
       if($_month == 4)
          echo " selected=\"selected\" ";
echo ">April</option>
";

echo "<option value=\"May\"";
       if($_month == 5)
          echo " selected=\"selected\" ";
echo ">May</option>
";

echo "<option value=\"June\"";
       if($_month == 6)
          echo " selected=\"selected\" ";
echo ">June</option>
";

echo "<option value=\"July\"";
       if($_month == 7)
          echo " selected=\"selected\" ";
echo ">July</option>
";

echo "<option value=\"August\"";
       if($_month == 8)
          echo " selected=\"selected\" ";
echo ">August</option>
";

echo "<option value=\"September\"";
       if($_month == 9)
          echo " selected=\"selected\" ";
echo ">September</option>
";

echo "<option value=\"October\"";
       if($_month == 10)
          echo " selected=\"selected\" ";
echo ">October</option>
";

echo "<option value=\"November\"";
       if($_month == 11)
          echo " selected=\"selected\" ";
echo ">November</option>
";

echo "<option value=\"December\"";
       if($_month == 12)
          echo " selected=\"selected\" ";
echo ">December</option>
";


echo $selectend;

}

function selectYear($selectedyear){
      $day = getdate();
      $year = $day['year'];


$selectyeartop = "

<select name=\"sent_year\">
";
$selectyearbottom = "
</select>
";
         echo $selectyeartop;

         for($i=$year; $i<$year+5; $i++) {
             echo "<option value=\"$i\"";
             if($i == $selectedyear)
                echo "selected=\"selected\"";
             echo "> $i</option>
             ";

         }

         echo $selectyearbottom;

}

function convertMonth($alpha_month){
         if($alpha_month == "January")
              return 1;
         else if($alpha_month== "February")
              return 2;
         else if($alpha_month== "February")
              return 2;
         else if($alpha_month== "March")
              return 3;
         else if($alpha_month== "April")
              return 4;
         else if($alpha_month== "May")
              return 5;
         else if($alpha_month== "June")
              return 6;
         else if($alpha_month== "July")
              return 7;
         else if($alpha_month== "August")
              return 8;
         else if($alpha_month== "September")
              return 9;
         else if($alpha_month== "October")
              return 10;
         else if($alpha_month== "November")
              return 11;
         else if($alpha_month== "December")
              return 12;

         return 1;

}


function days_in_feb($year){

        //$year must be YYYY
        //[gregorian] leap year math :

        if ($year < 0) $year++;
        $year += 4800;

    if ( ($year % 4) == 0) {
                if (($year % 100) == 0) {
                    if (($year % 400) == 0) {
                                return(29);
                    } else {
                                return(28);
                    }
                } else {
                    return(29);
                }
    } else {
                return(28);
    }
}


/*
  prints the month and year that are passed to it in
  $date
*/
function printMonth($_month, $_year)
{

$timestamp = mktime(0,0,0,$_month,1,2000);
$date = getdate ($timestamp);
$monthText = $date['month'];


 $monthtext1 =
"
<!-- month heading -------------------------------------------->
<table align=\"center\">
<tr>
<td align=\"left\">
 <form name=\"goto\" action=\"calendar.php\" method=\"POST\">
<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" bgcolor=\"#000000\" align=\"center\">
<tr><td>
<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" bgcolor=\"#CC0000\">
<tr><td width=\"200\">";

$monthtext2 = "<input type=\"image\" src=\"go.gif\" name=\"go\" border=\"0\">

</td></tr></table>
</td></tr></table>
</form>
</td>

<td align=\"center\" width=\"355\" valign=\"top\"><font class=\"month\">$monthText $_year</font></td>
<td align=\"right\" width=\"186\" valign=\"top\"></td>
</tr>
</table>
<!-- end of month heading ------------------------------------->

";
 echo "$monthtext1";
 selectMonth($_month);
 selectYear($_year);
 echo "$monthtext2";
}

/*
  prints the bar that contains the days of the week
*/
function printDays()
{
 $daybar =
"
<!-- days of the week heading --------------------------------->
<table align=\"center\" bgcolor=\"#000000\">
<TR>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Sunday</font></td>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Monday</font></td>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Tuesday</font></td>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Wednesday</font></td>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Thursday</font></td>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Friday</font></td>
<td width=\"105\" align=\"center\" bgcolor=\"#CC0000\"><font class=\"daysfont\">Saturday</font></td>
</tr>

<!-- end days of the week heading ----------------------------->

";

  echo $daybar;
}


/*
  opens table for calendar which is closed by last week, then prints the first
  week to the calendar
*/
function printFirstWeek($_day)
{
 global $event_day;
//write table declarations
 echo "

<!-- begin day boxes ------------------------------------------>


<tr>

 ";

 $current_day = 1;

 $spot = 0;

 while ($spot < $_day){

        echo "<td width=\"105\" height=\"115\" valign=\"top\" bgcolor=\"#e3e3e3\">&nbsp</td>
        ";
        $spot++;
 }

 while ($spot < 7){
        $event_day = $current_day;
        echo "<td width=\"105\" height=\"115\" valign=\"top\" bgcolor=\"#CCCCCC\"><font class=\"number\">$current_day</font><br>";
        getEvents();
        echo "</td>
        ";

        $spot++;
        $current_day++;

 }

echo"</tr>
";

return $current_day;
}

function printWeek($_day, $_last)
{
 global $event_day;

 while ($_day <= ($_last - 7)){
    $count = 0;
    echo "<TR>
    ";
    while($count < 7){
          $event_day = $_day;
          echo "<td width=\"105\" height=\"115\" valign=\"top\" bgcolor=\"#CCCCCC\"><font class=\"number\">$_day</font><br>";
          getEvents();
          echo "</td>
          ";
          $_day++;
          $count++;
    }
    echo "</TR>
    ";
 }
    return $_day;

}

function printLastWeek($_day, $_lastday)
{
        global $event_day;
 $count = 0;
      echo "<TR>
    ";
 while($count <= $_lastday)
 {
           $event_day = $_day;
           echo "<td width=\"105\" height=\"115\" valign=\"top\" bgcolor=\"#CCCCCC\"><font class=\"number\">$_day</font><br>";
           getEvents();
           echo "</td>
          ";
          $_day++;
          $count++;
 }

 while($count < 7)
 {
         echo "<td width=\"105\" height=\"115\" valign=\"top\" bgcolor=\"#e3e3e3\">&nbsp;</td>
        ";
        $count++;
        $_day++;
 }

 echo "</tr>
 ";
 echo "</table>
 <!-- end day boxes ------------------------------------------>

 ";
 }



//calculate all date information needed
if($sent_month && $sent_year){
   $month = convertMonth($sent_month);
   $year = $sent_year;
   $event_day = 1;
   $event_month = $sent_month;
   $event_year = $year;
   }
else {
      $day = getdate();
      $month = $month = $day['mon'];
      $mday = $mday = $day['mday'];
      $year = $year = $day['year'];
      $event_day = 1;
      $event_month = $day['month'];
      $event_year = $year;
      }


$days_in_month = days_in_month ($month, $year);

$first_day = mktime(0,0,0,$month,1,$year);
$date_first = getdate ($first_day);

$last_day = mktime(0,0,0,$month,$days_in_month,$year);
$date_last = getdate($last_day);

$dayofweekfirst = $date_first['wday'];
$dayofweeklast = $date_last['wday'];
//end date calculations





printMonth($month, $year);
printDays();
$current = printFirstWeek($dayofweekfirst);
$current = printWeek($current, $days_in_month);
printLastWeek($current, $dayofweeklast);


// Include Footer
include("footer.php");
?>


</BODY>
</HTML>
