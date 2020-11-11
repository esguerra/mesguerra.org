<h2>Email Form</h2>

	<? function show_form($first="",$last="") {  ?>

		<form action="emailform.php3" method="post">
                Email 
                <input type=text name=subject value="<?echo $last?>">
                <br> 
                Mail sent TO:
                <input type=text name=receiver value="<?echo $receiver?>">
                <br>
                SUBJECT:
                <input type=text name=subject value="<?echo $subject?>">
                <br> 
                MESAGGE:
                <input type=text name=message value="<?echo $message?>">
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
