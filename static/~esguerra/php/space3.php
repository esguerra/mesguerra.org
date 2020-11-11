<html>
<?php
function space_function($j){
$i=0;
while ($i <= $j){
	echo "<br>\n";
		$i = $i + 1;
	}
}
?>
<?php
for ($i >= 5; $i < 8; $i++){
    space_function($i);
}
?>
</html>
