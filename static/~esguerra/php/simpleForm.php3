<h2>Simple Form Example</h2>

	<? function show_form($first="",$last="") {  ?>

		<form action="simpleForm.php3" method="post">
                First Name:
                <input type=text name=first value="<?echo $first?>">
                <br>
                Last Name:
                <input type=text name=last value="<?echo $last?>">
                <br> 
                <input type=submit>
                </form>
        <? } 

		     if (!isset($first)) {
			 show_form();
		     }
		     else { 
			 if(empty($first) or empty($last)) {
			     echo "Please supply your First Name and Last";  
			     show_form($first, $last);
			 }
			 else {
			     echo "Thank you, $first $last";  
			 }
		     }
        ?>
