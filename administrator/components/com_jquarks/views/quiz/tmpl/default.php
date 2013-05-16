<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">

	window.onload = function () {

		function checkDate(publish) 
		{
			dateStr = new String() ;
			dateStr = publish.value ;

			year = dateStr.slice(0,4) ;
			month = dateStr.slice(5,7) ;
			month -= 1 ;
			day = dateStr.slice(8,10) ;

			time = '' ;
			time = publish.value.slice(10) ;

			if (!year || !month || !day) { 
				date = new Date() ;
			} else {
				date = new Date(year, month, day) ;
			}
			
			// building the date			
			month = date.getMonth() + 1 ;
			day = date.getDate() ;
			if (month < 10) {
				month = '0' + month ;
			}
			
			if (day < 10) {
				day = '0' + day ;
			}
				
			publish.value = date.getFullYear() + '-' + month + '-' + day ;

			// adding time
			if (time) 
			{
				hours = time.slice(1,3) ;
				minutes = time.slice(4,6) ;
				seconds = time.slice(7,9) ;

				if (hours < 0 || hours > 23) {
					hours = minutes = seconds = '00' ;
				}
				
				if (minutes < 0 || minutes > 59) {
					hours = minutes = seconds = '00' ;
				}
				
				if (seconds < 0 || seconds > 59) {
					hours = minutes = seconds = '00' ;
				}
				
				t = hours + ':' + minutes + ':' + seconds ;
			
				publish.value += ' ' + t ;
			
			} else {
				publish.value += ' 00:00:00' ;
			}

		}
		
		use_global = document.getElementById('detailspaginate2') ;
		no_paginate = document.getElementById('detailspaginate0') ;
		paginate = document.getElementById('detailspaginate1') ;
		question_page = document.getElementById('detailsquestionPage') ;
		slide = document.getElementById('detailsslide1') ;
		no_slide = document.getElementById('detailsslide0') ; 
		publish_down = document.getElementById('detailspublish_down') ;
		publish_up = document.getElementById('detailspublish_up') ;
		
		function noPagination() 
		{
			slide.checked = false ;
			no_slide.checked = false ;
			slide.disabled = true ;
			no_slide.disabled = true ;
			question_page.value = 0 ;
			question_page.disabled = true ;

	    	return true ;    
		}             

		if (use_global.checked || no_paginate.checked ) {
			noPagination() ;
		}
		
	    use_global.onclick = function () {
			
			noPagination() ;
	    	return true ;    
	    }

		no_paginate.onclick = function () {

			noPagination() ;
			return true ;
		}
	    
	    paginate.onclick = function () {
			
	    	question_page.disabled = false ;
	    	question_page.value = 5 ;
	    	slide.disabled = false ;
	    	no_slide.disabled = false ;
			no_slide.checked = true ;
			
	    	return true;    
	    }

	    publish_up.onchange = function() {

	    	checkDate(publish_up) ;
	    }	    
	    
	    publish_down.onchange = function() {

	    	if (publish_down.value != "" && publish_down.value != "<?php echo JText::_('Never') ; ?>")
	    	{
		    	checkDate(publish_down) ;
	    	}
			
			if (publish_down.value == "") {
	    		publish_down.value = '<?php echo JText::_('Never') ; ?>' ;
			}
	    }
	    
	}

	function buildDate(object) 
	{
		dateStr = new String() ;
		dateStr = object.value ;

		year = dateStr.slice(0,4) ;
		month = dateStr.slice(5,7) ;
		month -= 1 ;
		day = dateStr.slice(8,10) ;

		hours = dateStr.slice(11,13) ;
		minutes = dateStr.slice(14,16) ;
		seconds = dateStr.slice(17,19) ;

		date = new Date(year, month, day) ;

		date.setHours(hours);
		date.setMinutes(minutes);
		date.setSeconds(seconds);
		
		return date ;
	}

	// show or hide the session control according to the value ok access of the quiz (public or registred) 
	function sessionControl()
	{
		accessPublic 		= document.getElementById('accessp') ;
		accessRegistred 	= document.getElementById('accessr') ;
		session_control 	=  document.getElementById('session_control') ;
		uniqueSession 		=  document.getElementById('unique_session') ;
		
		if (accessPublic.checked) 
		{
			session_control.style.display = 'none' ;
			uniqueSession.checked = false ;
		} 
		
		if (accessRegistred.checked) { 
			session_control.style.display = '' ;
		}
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
	
		var	errorList = document.getElementById('errorList') ;
		
		var errorUL = document.createElement("UL") ;
		var errorLI = document.createElement("LI") ; 

		errorLI.innerHTML = error ; 
		errorUL.appendChild(errorLI) ;
	
		errorList.appendChild(errorUL) ;
	}
	
	function submitbutton(pressbutton) 
	{
		clearMessages();
		
		var form = document.adminForm;

		if (pressbutton == 'cancel') 
		{
			submitform( pressbutton );
			return ;
		}

		detailspaginate1 = document.getElementById('detailspaginate1') ;
	  	if ( (isNaN(question_page.value) || question_page.value < 1) && detailspaginate1.checked ) 
		{
	  		question_page = document.getElementById('detailsquestionPage') ;
			addError("\"" + question_page.value + "\" " + "<?php echo JText::_("INVALID_NUMBER_OF_QUESTION_PER_PAGE") ; ?>") ;
		 	question_page.focus() ;
		 	question_page.select() ;	
		 	return false ; 
	  	}

		if (form.title.value == "") 
		{
			addError("<?php echo JText::_( 'PLEASE_PROVIDE_A_TITLE_FOR_THE_QUIZ' ); ?>") ;
			return false ;
		} 


		// getting the time limit
		timeLimit = document.getElementById('time_limit') ;
		if (timeLimit.value != "" && (isNaN(timeLimit.value) || timeLimit.value <= 0) ) 
		{
			addError("<?php echo JText::_( 'TIME_LIMIT_NOT_VALID') ?>" ) ;
			timeLimit.focus() ;
			timeLimit.select() ;
			return false ;
		}
		
		// getting the date of publishing
		publishUp = document.getElementById('detailspublish_up') ;
		publishUpDate = buildDate(publishUp) ;
				
		// getting the date of end of publishing
		publishDown = document.getElementById('detailspublish_down') ;
		publishDownDate = buildDate(publishDown) ;
		
		if (publishUpDate >= publishDownDate) 
		{
			addError("<?php echo JText::_( 'PUBLISH_DATE_INTERVAL_WRONG') ?>" ) ;
			publishDown.focus() ;
			return false ;
		} 

		submitform( pressbutton );
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
            
		<table><tr valign="top"><td>
		<fieldset class="adminfrom">
			<legend><?php echo JText::_( 'QUIZ' ); ?></legend>
			<table class="admintable">
			<tr>
				<td width="100" align="right" class="key">
					<label for="title">
						<?php echo JText::_( 'TITLE' ) ; ?>:
					</label>
				</td>
				<td>
					<input class="text_area" name="title" id="title" size=40 value="<?php echo $this->quiz->title ; ?>" />
				</td>
			</tr>
			<tr>
				<td width="20" align="right" class="key">
					<label for="description">
						<?php echo JText::_( 'DESCRIPTION' ) ; ?>:
					</label>
				</td>
				<td>
					<textarea class="text_area" name="description" id="description" cols=40 ><?php echo $this->quiz->description ; ?></textarea>
				</td>
			</tr>
			<tr>
				<td width="20" align="right" class="key">
					<label for="description">
						<?php echo JText::_( 'ACCESS' ) ; ?>:
					</label>
				</td>
                                <td style="display: -moz-box;">
					<input class="radio" type="radio" name="access" id="accessp" value="0" onchange="sessionControl()" <?php if ($this->quiz->access_id == 0) echo 'checked' ;?> /><label for="accessp"><?php echo JText::_('PUBLIC') ?></label>
					<input class="radio" type="radio" name="access" id="accessr" value="1" onchange="sessionControl()" <?php if ($this->quiz->access_id == 1 ) echo 'checked' ;?> /><label for="accessr"><?php echo JText::_('REGISTRED') ?></label>					
				</td>
			</tr>
			<tr id="session_control" style="<?php if ( 1 != $this->quiz->access_id ) : echo 'display:none;' ; endif ; ?>">
                <td width="20" align="right" class="key">
                    <span class="editlinktip hasTip" title="<?php echo JText::_( 'UNIQUE_SESSION' );?>::<?php echo JText::_("UNIQUE_SESSION_MAY_BE_PASSED_ONLY_ONCE"); ?>">
                        <label for="unique_session">
                            <?php echo JText::_('UNIQUE_SESSION') ; ?>
                        </label>
                    </span>             
                </td>
                <td>
                    <input type="checkbox" name="unique_session" id="unique_session" <?php if ($this->quiz->unique_session == true) echo 'checked' ?>/>
                </td>
            </tr>
			<tr>
				<td width="20" align="right" class="key">
                                        <label for="description" style="margin-bottom: 14px;">
						<?php echo JText::_( 'TIME_LIMIT' ) ; ?>:
					</label>
				</td>
				<td>
                                    <input type="text" name="time_limit" id="time_limit" value="<?php echo ($this->quiz->time_limit) ; ?>" size="4" />
                                    <p style="margin-top: 6px;"><?php echo ' ' . JText::_('MINUTES') ; ?></p>
				</td>
			</tr>
			<tr>
                <td width="20" align="ledt" class="key">
                    <label for="show_results">
                        <?php echo JText::_('SHOW_RESULTS') ; ?>
                    </label>
                </td>
                <td>
                    <input type="checkbox" name="show_results" id="show_results" <?php if ($this->quiz->show_results == true) echo 'checked' ?>/>
                </td>
            </tr>

			</table>	
		</fieldset>
		</td>
		
		<td>
		
		</td>
                <td style="width: 700px">
			<?php 
				jimport('joomla.html.pane');
				JHTML::_('behavior.tooltip');			
				$pane	=& JPane::getInstance('sliders', array('allowAllClose' => true));
			
				$title = JText::_( 'Parameters - Quiz' );
				echo $pane->startPane("content-pane");
				echo $pane->startPanel( $title, "detail-page" );
				echo $this->form->render('details');
				
				echo $pane->endPanel();
				echo $pane->endPane();
			?>
		</td>
		</tr>
		</table>
		
		
	</div>
	
	<div class="clr"></div>
	 
	<input type="hidden" name="notify_message" value="<?php echo htmlspecialchars($this->quiz->notify_message) ; ?>"/>
	<input type="hidden" name="option" value="com_jquarks"/>
	<input type="hidden" name="cid[]" value="<?php echo $this->quiz->id; ?>" />
	<input type="hidden" name="published" value="<?php echo $this->quiz->published ; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="quiz" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
        <style>
            #detailspublish_up_img, #detailspublish_down_img {
                margin-bottom: -5px;
            }
            #detailspaginate-lbl, #detailsslide-lbl, #detailsquestionPage-lbl,
            #detailspublish_up-lbl,#detailspublish_down-lbl
            {
                width: 120px;
                display: block;
                margin-top: 10px;
                margin-left: 5px;
            }
            #detailspaginate1, #detailsslide1, #detailsquestionPage, 
            #detailspublish_up, #detailspublish_down
            {
                margin-left: 60px;
                margin-top: 6px;
            }
            #accessr{
                margin-left: 5px;
            }
            #detailspublish_down{
                margin-bottom: 10px;
            }
        </style>
</form>