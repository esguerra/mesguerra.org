<? function show_form($email="",$message="",$subject="") {  ?>

<h2>Send Me an E-mail</h2> 

<form action="mail.php3" method="post">
Your E-mail address:<br>
<input type=text name=email size=30 value="<?echo $email?>"><br>
The Subject:<br>
<input type=text name=subject size=30 value="<?echo $subject?>"><br> 
Your Message:<br>
<textarea rows=10 cols=50 name=message><?echo $message?></textarea><br>
<input type=submit value="Send E-mail">
</form>

<? }  // close function


if (!isset($email) or !isset($message)) {
    show_form();
}
else {
    if (empty($email) or empty($message)) {
	echo "<H1>There is a Problem:</H1>";
	if (empty($email)) {
	    echo "I need your email address in order 
				to write back.  Please fill it 
				in below.  Thank you.";
	}
	if (empty($message)) {
	    echo "You did not write anything.  
				Please write something.  Thank You.";  
	}
	show_form($email,$message,$subject);
    }
    else {
	if (empty($subject)) {  // Implement #1 here
	    $subject="your email";  
	}
	// Implement #2 here
	$sent = mail($receiver, $subject, $message, "From: $email"))
	    // Implement #3 here
	if($sent){
	    echo "<H1>Your Message Has Been Sent.</H1>";  
	    echo "Thank you, <b>$email</b>.  <p>I will 
				read your email regarding <b>$subject</b>
				and reply soon.";  
	}
	else {
	    echo "<H1>There is a Problem:</H1>
				<p>The server was unable to send your mail.";
	}
    }
}
?>
