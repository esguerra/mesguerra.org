<html>
<body>
<h2>PHP and HTML Forms Example:</h2>

This is the first time you have come to this form. <br>
		The variable <b>&#36;value</b> is not set, so
                <b>!isset(&#36;value)</b> is true.  <br>
		After you have observed the code below, try clicking <br>
		the <b>Submit Query</b> button without typing
                any text in the box.    <form action="submit.php3" method="post">
  <input type=text name=value value="">
  <input type=submit>
  </form>
The following code in <b>bold</b> (as well the function) 
has been executed:
	<pre>
	&lt;&#63; function show_form(&#36;value=&#148;&#148;) {  &#63;>
	  &lt;form action=&#148;submit.php3&#148; method=&#148;post&#148;>
	  &lt;input type=text name=value
	        value=&#148;&lt;&#63;echo &#36;value&#63;>&#148;>
	  &lt;input type=submit>
	  &lt;/form>
	&lt;&#63; }  // close function  
	<b>
	if(!isset(&#36;value)) {
        	echo &#36;comment1&#59;
	        show_form()&#59;
	        echo &#36;code_comment1&#59;
	}</b>
	else {
	   if(empty(&#36;value)) {
	        echo &#36;comment&#59;
	        show_form(&#36;value)&#59;
	        echo &#36;code_comment2&#59;
	   }
	   else {
	        echo &#36;comment3&#59;
		echo &#148;&lt;h3>Thank you!&lt;/h3>&#148;&#59;		
	        echo &#36;code_comment3&#59;
	   }    
	}
	&#63;>
	</pre>
</body></html>





