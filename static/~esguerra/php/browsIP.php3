<html>
<br>
<h1> The browser and operating system you are using is: </h1>
<h3> <?php  echo "$HTTP_USER_AGENT"; ?> </h3>
<hr>
and your IP address is:
<br>
<?php echo "$REMOTE_ADDR,   $HTTP_USER_AGENT";   ?>
<br>
<br>
And now you are seeing the output from the function phpinfo()
<hr>
<br>
<?php phpinfo() ?>

</html>
