<table border="1" cellpadding="3">

<tr>
  <? for($i = 1; $i < 9; $i++) { ?>
         <td align="center">
         <? echo $i; ?>
         </td>
  <? } ?>
</tr>

<tr>
  <? for($i = 1; $i < 9; $i++) { ?>
         <td align="center">
         <?
		if( $i<=5      ) {
                	echo "yes";
                 }
                 else {
                        echo "no";
                 }
         ?>
         </td>
  <? } ?>       
</tr>
</table>
