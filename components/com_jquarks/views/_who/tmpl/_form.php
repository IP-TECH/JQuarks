<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.mootools');
?>
<script type="text/javascript" language="javascript">

	function validEmail(email) 
	{
		pattern = new RegExp("^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$") ;

		return pattern.test(email) ;
	} 

	function checkForm() 
	{
		lastname = document.getElementById('lastname').value ;
		firstname = document.getElementById('firstname').value ;
		email = document.getElementById('email').value ;
		
		if (lastname != '' && firstname != '' && validEmail(email)) {
			return true ;
		} 
		else 
		{
			alert("<?php echo JText::_('ERROR_FILL_REQUIRED_FIELDS_PRVOIDE_VALIDE_EMAIL') ; ?>") ;
			return false ;
		}
	}

</script>

<h1><?php echo JText::_('THANK_YOU_LEAVE_COORDINATE') ; ?></h1>
<form method="post" onsubmit="return checkForm();">
	<table>
		<tr>
			<td><?php echo JText::_('GIVEN_NAME') ; ?> :</td>
			<td><input type="text" id="firstname" name="firstname" size="15" /> *</td>
		</tr>
		<tr>
			<td><?php echo JText::_('FAMILY_NAME') ; ?> :</td>
			<td><input type="text" id="lastname" name="lastname" size="15" /> *</td>
		</tr>
		<tr>
			<td><?php echo JText::_('EMAIL') ; ?> :</td>
			<td><input type="text" id="email" name="email" size="30"/> *</td>
		</tr>
		<tr>
			<td><input type="submit" value="<?php echo ($this->isShowResults)? JText::_('SEND_AND_VIEW_RESULTS') : JText::_('SEND'); ?>"/></td>
			<td><input type="reset" value="<?php echo JText::_('RESET') ;?>" />
		</tr>
		
	</table>
    	<input type="hidden" name="option"     value="com_jquarks" />
	<input type="hidden" name="controller" value="who" />
	<input type="hidden" name="task"       value="store" />
	<input type="hidden" name="sessionId"  value="<?php echo $this->sessionId ; ?>"/>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>