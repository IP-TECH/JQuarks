<?php defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript" language="javascript">

	function clearMessages()
	{
		// remove joomla messages
		statusMessage = document.getElementsByTagName('dl') ;
		if (statusMessage.length == 2)
		{
			JoomlaStatusMessage = statusMessage[0] ;
			JoomlaStatusMessage.parentNode.removeChild(JoomlaStatusMessage) ; 
		}
		
		// clear JQuarks messages error
		var errorList = document.getElementById('errorList') ;
		errorList.innerHTML = '' ;
	}

	function addError(error)
	{
		messageDiv = document.getElementById('message') ;
		messageDiv.style.display = "block" ;
	
		var errorList = document.getElementById('errorList') ;
		
		var errorUL = document.createElement("UL") ;
		var errorLI = document.createElement("LI") ; 

		errorLI.innerHTML = error ; 
		errorUL.appendChild(errorLI) ;
	
		errorList.appendChild(errorUL) ;
	}
	
	function submitbutton(pressbutton) 
	{
		clearMessages() ;
		
		title = document.getElementById('title') ;

		if (pressbutton == 'cancel' ){
			submitform(pressbutton) ;
		}
		else if (title.value == "") 
		{
			addError("<?php echo JText::_( 'PLEASE_PROVIDE_A_TITLE_FOR_THE_CATEGORY', true ); ?>") ;
			return false ;
		}
	
		submitform(pressbutton) ;
	}	 

	
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div id="message" style="display:none;">
		<dl id="system-message">
			<dt class="error">Error</dt>
			<dd id="errorList" class="error message fade"></dd>			
		</dl>
	</div>
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'CATEGORY' ); ?></legend>
			<table class="admintable">
			<tr>
				<td width="100" align="right" class="key">
					<label for="title">
						<?php echo JText::_( 'TITLE' ) ; ?> :
					</label>
				</td>
				<td>
					<input class="text_area" type="text" name="title" id="title" size="32" maxlength="250" value="<?php echo $this->category->title ; ?>" />
				</td>
			</tr>
			<tr>	
				<td width="100" align="right" class="key">
					<label for="description" class="hasTip">
						<?php echo JText::_( 'DESCRIPTION' ) ;?> :
					</label>
				</td>
				<td>
					<textarea class="text_area" name="description" id="description" cols="34"><?php echo $this->category->description ;?></textarea>
				</td>
			</tr>
			</table>	
		</fieldset>
	</div>
	
	<div class="clr"></div>
	 
	<input type="hidden" name="option" value="com_jquarks"/>
	<input type="hidden" name="id" value="<?php echo $this->category->id ; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="categories" />
	<input type="hidden" name="view" value="category" />
	<?php  echo JHTML::_( 'form.token' ); ?> 
</form>