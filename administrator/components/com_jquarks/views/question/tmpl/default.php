<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">

    window.onload = function() {

    	var lastProp = document.getElementById("propositions") ;
        lastProp = lastProp.getElementsByTagName("tr") ;

        if (lastProp[0]) {
        	document.getElementById('propHeader').style.display = "" ;
        	document.getElementById('showAsCheck').style.display = "" ;
        }
    }

	function checked() 
	{
		var lines = document.getElementById("propositions").getElementsByTagName("tr") ;
		total = lines.length ;

		var checkBox ;
		var prop = true ;

		for (i = 0 ; i < total ; i++) 
		{
			checkBox = lines[i].getElementsByTagName("input") ;

			if (!checkBox[1].checked) {
				prop = false ;
			}

			if (checkBox[0].checked && !checkBox[1].checked) { 
				return true ;
			}
		}
		
		return prop ;
	}

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
		
		var form = document.adminForm;

		if (pressbutton == 'cancel') 
		{
			submitform( pressbutton );
			return;
		}

		// case writing in the textarea in TinyMCE mode off
		if ( form.statement.value != "" && tinyMCE.get('statement').getContent() == "") {
			tinyMCE.get('statement').setContent(form.statement.value);
		}
		
		// do field validation
	    if (tinyMCE.get('statement').getContent() == "") {
			addError("<?php echo JText::_( 'PLEASE_PROVIDE_A_STATEMENT_FOR_THE_QUESTION', true ); ?>") ;
		} else if ( !checked() ) {
			addError("<?php echo JText::_( 'PLEASE_MARK_AT_LEAST_ONE_PROPOSITION_AS_CORRECT', true ); ?>") ;
		} else {
			submitform( pressbutton );
		}
	}
	
	function addPropositionField() 
	{
        var lastProp = document.getElementById("propositions") ;
        lastProp = lastProp.getElementsByTagName("tr") ;
        
		lastPropNumber = 1 ;
        
		if (lastProp[0]) 
		{
			lastProp = lastProp[lastProp.length -1].getElementsByTagName("td")[0] ;
	        lastProp = lastProp.getElementsByTagName("textarea")[0].name ;
			lastPropNumber = new String(lastProp) ;
			lastPropNumber = lastPropNumber.slice(4) ;
			lastPropNumber++ ;
		}
		else
		{
			document.getElementById('propHeader').style.display = "" ;
			document.getElementById('showAsCheck').style.display = "" ;
		}

		propositionFieldCount = lastPropNumber ;
		
        var row = document.createElement("tr") ;
        var cellProposition = document.createElement("td") ;
        var cellCorrect = document.createElement("td") ;
        var cellDelete = document.createElement("td") ;
          
        var proposition = document.createElement("textarea");
       	proposition.rows = "1" ;
       	proposition.cols = "35" ;
        proposition.name = "prop" + propositionFieldCount ;
        proposition.id = "prop" + propositionFieldCount ;

		var correctCB = document.createElement("input");
		correctCB.type = "checkbox" ;
		correctCB.name = "propcorrect" + propositionFieldCount ;

		var deleteCB = document.createElement("input");
		deleteCB.type = "checkbox" ;
		deleteCB.name = "propdelete" + propositionFieldCount ;
		
        p = document.getElementById("propositions") ;

        cellProposition.appendChild(proposition) ;
        cellCorrect.appendChild(correctCB) ;
        cellDelete.appendChild(deleteCB) ;
        
        row.appendChild(cellProposition) ;
        row.appendChild(cellCorrect) ;
        row.appendChild(cellDelete) ;
           
        p.appendChild(row) ;
    }

	function getXhr()
	{
		var xhr = null;
		if(window.XMLHttpRequest) {
			xhr = new XMLHttpRequest();
		} 
		else if(window.ActiveXObject)
		{ 
			try {
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
		} 
		else 
		{ 
			alert("NO_SUPPORT_XMLHTTPREQUEST");
			xhr = false;
		}

		return xhr;
	}

	function addCategory() 
	{
		clearMessages() ;
		
		var xhr = getXhr();
		
		xhr.onreadystatechange = function() {
			
		    if(xhr.readyState == 4 && xhr.status == 200)
			{
				var category = eval ("(" + xhr.responseText + ")")  ;
	
			    if (category['id'] == 0) 
				{
				    alert('<?php echo JText::_('ERROR_ADD_CATEGORY') ; ?>') ;
				    return false ;
			    }
	
		        var categoryOption = document.createElement('option');
		        categoryOption.value = category['id'] ;
		        categoryOption.text = category['title'] ; 
		        
		        categories = document.getElementById('category_id') ;
		        
		        try {
			        categories.add(categoryOption, null); // standard
		        } catch(ex) {
		            categories.add(categoryOption); // IE 
		        }
	
				categories.selectedIndex = categories.length - 1 ; // selecting the newly added category
		    }
		}
		title = document.getElementById('newCategory') ; 
		if ('' == title.value) 
		{
			addError('<?php echo JText::_('PLEASE_PROVIDE_A_TITLE_FOR_THE_CATEGORY') ; ?>') ;
			title.focus() ;
			return false ;
		}	        
	    xhr.open("GET","index.php?option=com_jquarks&controller=question&task=addCategory&title="+title.value,true);
	    xhr.send(null);
	}

	function removeCategory() 
	{
		if (confirm('<?php echo JText::_('CONFIRM_SUPPRESSION_CATEGORIES') ; ?>') )
		{
			var xhr = getXhr() ;
	
			categories = document.getElementById('category_id') ;
	
			id = categories.options[categories.selectedIndex].value ;
	
			xhr.onreadystatechange = function(){
				
		        if(xhr.readyState == 4 && xhr.status == 200)
			    {
					var category = eval ("(" + xhr.responseText + ")")  ;
	
			        if (category['id'] == 0) 
				    {
				        alert('<?php echo JText::_('ERROR_DELETING_CATEGORY') ; ?>') ;
				        return false ;
			        }
					categories.remove(categories.selectedIndex) ;				
				}
			}
				
			xhr.open("GET","index.php?option=com_jquarks&controller=question&task=removeCategory&id="+id, true) ;
			xhr.send(null) ;
		}
	}
	
</script>
	
<style>
	.codehighlight {
		background:transparent url(../templates/system/images/j_button2_blank.png) no-repeat scroll 100% 0;
	}
	
	input, select {
		vertical-align:top ;
	}
</style>
	
<form action="index.php" method="post" name="adminForm" id="adminForm" >	
	<div id="message" style="display:none;">
		<dl id="system-message">
			<dt class="error">Error</dt>
			<dd id="errorList" class="error message fade"></dd>			
		</dl>
	</div>
	<div class="width-100 fltlft">
		
		<table class="adminform" style="text-indent: 30px;" >
				<tr>
					<td>
						<label><?php echo JText::_( 'SELECT_CATEGORY' ); ?></label>
					</td>
					<td>
						<?php echo $this->lists['categories'] ; ?>
						<?php if (false) : ?>
							<a onclick="removeCategory()" href="javascript:void(0)">
								<img src="images/publish_x.png" title="<?php echo JText::_('DELETE_CATEGORY') ; ?>" alt="<?php echo JText::_('DELETE_CATEGORY') ; ?>"/>
							</a>	
						<?php endif ; ?>
					</td>
					<td>
						<?php echo JText::_('OR') ; ?>
					</td>
					<td>
						<label><?php echo JText::_( 'CREATE_NEW_CATEGORY' ); ?></label>
					</td>
					<td>
						<input style="margin-right: 5px;" type="text" id="newCategory" name="newCategory"/>
                                                <a onclick="addCategory()" href="javascript:void(0)" class="saveorder" title="<?php echo JText::_('ADD_CATEGORY') ; ?>"></a>
					</td>
				</tr>
				<?php if (false) : ?>
				<tr>
					<td width="100" align="right" class="key"><label><?php echo JText::_( 'Type' ); ?></label></td>
					<td><?php echo $this->lists['type'] ; ?></td>
				</tr>
				<?php endif ; ?>
			</table>
                <fieldset class="adminform">
                    <legend><?php echo JText::_( 'DESCRIPTION' ); ?></legend>
                    <input type="text" size="120" name="description" value="<?php echo $this->question->description  ?>" />
                </fieldset>
		<fieldset class="adminform" style="float:left; margin-right: 20px; margin-top: 0px;">
			<legend><?php echo JText::_( 'QUESTION' ); ?></legend>
				<?php
                                    echo $this->editor->display('statement', $this->question->statement , 
                                            '600', '350', '20', '20',
                                            array('image', 'pagebreak', 'readmore'), $this->editor_params);
				?>
		</fieldset>
		
		<fieldset class="adminform">
			<legend><?php echo JText::_('PROPOSITIONS') ; ?></legend>				
			
			<div style="text-align:right;"><a href="javascript:void(0)" onclick="addPropositionField();" ><?php echo JText::_('ADD_A_PROPOSITION') ;?></a></div>
			<table id="showAsCheck" style="display:none;">
			     <tr>
			         <td><input type="checkbox" id="asCheck" name="asCheck" <?php if ($this->question->type_id == 4) echo "checked" ; ?> /></td>
			         <td><label for="asCheck"><?php echo JText::_('SHOW_AS_MULTIPLE_ANSWER_QUESTION') ; ?></label></td>
			     </tr>     
			</table>
			<table>									
				<?php  $i = 1 ;  ?>
				<thead>
					<tr id="propHeader" style="display:none;">
						<th><?php echo JText::_('PROPOSITION') ;?></th>
						<th><?php echo JText::_('CORRECT') ; ?></th>
						<th><?php echo JText::_('DELETE') ; ?></th>
					</tr>	
				</thead>
				<tbody id="propositions">
					<?php foreach ($this->propositions as $proposition ) : ?>
					<tr>							
						<td>
							<textarea name="<?php echo 'prop' . $i ; ?>" id="<?php echo 'prop' . $i ; ?>" cols="35" rows="1"><?php echo $proposition->answer ; ?></textarea>
						</td>							
						<td>
							<input type="checkbox" name="propcorrect<?php echo $i ;?>" id="propcorrect<?php echo $i ;?>" <?php if ($proposition->correct) : ?> checked <?php endif ; ?> />
						</td>							
						<td>
							<input type="checkbox" name="propdelete<?php echo $i ;?>" id="propdelete<?php echo $i ;?>"/>
						</td>							
						<td>
							<input type="hidden" name="propid<?php echo $i ; ?>" value="<?php echo $proposition->id ; ?>"/>
						</td>
					</tr>			
			
					<?php $i++ ; 
						  endforeach ;	
					?>						
					
				</tbody>
			</table>
		</fieldset>
	</div>
	 
	<input type="hidden" name="option" value="com_jquarks"/>
	<input type="hidden" name="id" value="<?php echo $this->question->id; ?>" />
	<input type="hidden" name="type_id" value="1" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="questions" />
	<input type="hidden" name="view" value="question" />
	<?php  echo JHTML::_( 'form.token' ); ?> 	
</form>	