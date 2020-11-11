<html>
<?
$var = date("H");

if ($var <= 11) {
        echo "good morning";
}
else {
        if ($var > 11 and $var < 18) {
                echo "good afternoon";
        }
        else {
                echo "good evening";
        }
}
?>
</html>
