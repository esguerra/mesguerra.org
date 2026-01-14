<html>

<?php
    function fib($i) {
	if ($i == 0)
		return 0;
	if ($i == 1)
		return 1;
	else
		return fib($i-1) + fib($i-2);
}
?>

<?php
     for ($i = 0; $i < 10; $i++) {
	echo fib($i); 
?>
<br>
<?php 
}
?>

</html>
