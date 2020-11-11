<html>
<head>
<title>Flat Calendar: Add Event</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" type="text/css" name="style" href="../calendar.css">
</head>

<body bgcolor="#FFFFFF" text="#000000">

<BR><BR>
<center>
<form action="calAdd.php" method="post">
<table cellpadding="0" cellspacing="2" border="0" bgcolor="#000000"><tr><td>

<table cellpadding="3" cellspacing="0" border="0" bgcolor="#CC0000">
<tr><td><font class="addHead"><a href="calendar.php" class="addHead">Calendar</a></font></TD></tr>
<tr><td>

<table cellpadding="3" cellspacing="0" border="0" bgcolor="#CCCCCC">
        <tr>

    <td width="95" align="right"><font class="AddLeft">Event</font></td>

    <td width="405"><input type="text" name="event" maxlength="18"></td>
        </tr>
                <tr bgcolor="#E3E3E3">

    <td width="95" align="right"><font class="AddLeft">Event<br>Description</font></td>

    <td width="405"><TEXTAREA NAME="description" ROWS=6 COLS=40></TEXTAREA></td>
        </tr>
                <tr>

    <td width="95" align="right"><font class="AddLeft">Date</font></td>

    <td width="405" >
<?php

      $day = getdate();
      $current_year = $day['year'];
      $month = $day['month'];
      $today = $day['mday'];

echo "
<select name=\"month\">
        <option value=\"January\"";
         if($month=="January")
            echo " selected ";
            echo ">January</option>";

        echo "<option value=\"February\"";
         if($month=="February")
            echo " selected ";
            echo ">February</option>";

        echo "<option value=\"March\"";
         if($month=="March")
            echo " selected ";
            echo ">March</option>";

        echo "<option value=\"April\"";
         if($month=="April")
            echo " selected ";
            echo ">April</option>";

        echo "<option value=\"May\"";
         if($month=="May")
            echo " selected ";
            echo ">May</option>";

        echo "<option value=\"June\"";
         if($month=="June")
            echo " selected ";
            echo ">June</option>";

        echo "<option value=\"July\"";
         if($month=="July")
            echo " selected ";
            echo ">July</option>";

        echo "<option value=\"August\"";
         if($month=="August")
            echo " selected ";
            echo ">August</option>";

        echo "<option value=\"September\"";
         if($month=="September")
            echo " selected ";
            echo ">September</option>";

        echo "<option value=\"October\"";
         if($month=="October")
            echo " selected ";
            echo ">October</option>";

        echo "<option value=\"November\"";
         if($month=="November")
            echo " selected ";
            echo ">November</option>";

        echo "<option value=\"December\"";
         if($month=="December")
            echo " selected ";
            echo ">December</option>";
echo "</select>

<select name=\"day\">";

        for($i = 1; $i <= 31; $i++){
            echo "<option value=\"$i\"";
            if($i == $today)
               echo " selected ";
            echo ">$i</option>
            ";
        }
        echo "</select>

<select name=\"year\">";



      for($i = $current_year; $i < ($current_year + 5); $i++){
        echo "<option value=\"$i\"";
               if ($year == $i)
                   echo " selected ";
        echo ">$i</option>
        ";
      }

echo "</select>";
?>

</td>
        </tr>
                        <tr bgcolor="#E3E3E3">

    <td width="95" align="right"><font class="AddLeft">Submitted By</font></td>

    <td width="405"><input type="text" name="submitted"></td>
        </tr>
</table>
</td><tr>
<tr><td align="right"><input type="submit" value="Add Event"></td></tr>
</table>
</td></tr></table>
</form>
</center>




</body>
</html>