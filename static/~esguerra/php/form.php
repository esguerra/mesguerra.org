<html>
<? function show_form($value=””) {  ?>
	  <form action=”submit.php3” method=”post”>
	  <input type=text name=value
	        value=”<?echo $value?>”>
	  <input type=submit>
	  </form>
	<? }  // close function  
	
	if(!isset($value)) {
        	echo $comment1;
	        show_form();
	        echo $code_comment1;
	}
	else {
	   if(empty($value)) {
	        echo $comment;
	        show_form($value);
	        echo $code_comment2;
	   }
	   else {
	        echo $comment3;
		echo ”<h3>Thank you!</h3>”;		
	        echo $code_comment3;
	   }    
	}
	?>
</html>
