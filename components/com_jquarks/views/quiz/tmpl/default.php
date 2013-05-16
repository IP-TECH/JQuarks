<?php
	defined( '_JEXEC' ) or die( 'Restricted access' );
	JHTML::_('behavior.mootools');
?>

<?php if ($this->quiz) : ?>

<script language="javascript" type="text/javascript">
	
	var hours ;
	var minutes ; 
	var seconds ;
	var t ;
	var availableTime ;
	var remainingTime ;
	
	window.onload = function () {

		availableTime = <?php echo ($this->quiz->time_limit)  ? $this->quiz->time_limit : 0 ; ?> ;
		if (availableTime)
		{
			hours = Math.floor(availableTime / 60) ;
			minutes = availableTime % 60 ;

			if (hours < 10) {
				hours = '0' + hours ;
			}

			if (minutes < 10) {
				minutes = '0' + minutes ;
			}

			seconds = '00' ;

			var timer = document.getElementById('timer') ;
			timer.innerHTML = '<?php echo JText::_('TIME_REMAINING') ; ?> ' + hours + ":" + minutes + ":" + seconds ;

			remainingTime = availableTime *= 60 ;
			
			updateTimer();
		}
	}

	function pad(number, length) 
	{
	    var str = '' + number;
	    if (str.length < length) {
	        str = '0' + str;
	    }

	    return str;
	}

	function updateTimer()
	{
		seconds -= 1 ;
		seconds = pad(seconds, 2) ;
		
		if (seconds < 0)
		{
			seconds = '59' ;
			minutes -= 1 ;
			minutes = pad(minutes, 2) ;
			
			if (minutes < 0)
			{
				minutes = '59' ;
				hours -= 1 ;
				hours = pad(hours, 2) ;
				
				if (hours < 0)
				{
					hours = minutes = seconds = '00' ;
					document.getElementById('timeUp').value = 1 ;
					document.jquarks_quiz.submit();
					return true ;
				}
			}
		}

		var timer = document.getElementById('timer') ;
		timer.innerHTML = '<?php echo JText::_('TIME_REMAINING') ; ?> ' + hours + ':' + minutes + ':' + seconds ;

		remainingTime -= 1 ;
		if (Math.floor(availableTime / 10) == remainingTime ) 
		{
			timer.style.color="white" ;
			timer.style.backgroundColor = "#DD0000" ;
		}
		
		t = setTimeout("updateTimer()", 1000);
	}

	function validation()
	{
		var message = "<?php echo JText::_('PLEASE_CHECK_THAT_YOU_ANSWERED_ALL_QUESTIONS_BEFORE_PROCEEDING')?>" ; 

		if (<?php echo $this->quiz->unique_session ; ?>) {
			message += "\n<?php echo JText::_('YOU_CAN_ONLY_PASS_THIS_QUIZ_ONCE') ; ?>" ;
		}

		if (confirm(message)) {
			return true ;
		} else {
			return false ;
		}
	}

</script>
<style>
	#timer_wrap 
	{
		text-align:left;
		margin-bottom: 10px;
		
	}
	
	.timer
	{
		border: 1px solid gray;
		display: table-cell;
		font-size: larger;
		padding: 5px;
		margin: 5px;
		margin-rigth: 0px;
		position: relative;
	}
        #jquarksNav ul {
            list-style: none outside none;
        }
        #jquarksNav {
            width: 100%; 
        }
        #jquarksNav ul { 
            list-style : none ; 
        } 
        #jquarksNav div.hidden {
            display: block; 
            float: left; 
            position:fixed; 
        }
        #jquarksNav div.shown {
            display: block; 
            float: left; 
            position:static; 
        }
</style>


<div class="items-row">
    <div class="item">
        <h2><?php echo $this->quiz->title ; ?></h2>

<?php if ($this->quiz->time_limit) : ?>
    <div id="timer_wrap">
        <p id="timer" class="timer"></p>
    </div>
<?php endif ; ?>

<form name="jquarks_quiz" method="post" action="index.php" onsubmit="return validation()">
		
		<?php 
		
			// get the total number of pages for the quiz

			// no pagination 
			$nbrQuestionPage = 0 ;
			
			if ($this->quiz->paginate['use_pagination'] == 2 ) 
			{
				// case of use global 
				if($this->params['paginate']) 
				{
					// pagination is activ
					// getting if the pagination is by slide or by page
					switch ($this->params['slide']) 
					{
						case 0 :
							$showType = "show" ;
							$hideType = "hide" ;
							break ;
						
						case 1 :
							$showType = "slideIn" ;
							$hideType = "slideOut" ;
							break ;	
					}
					
					$nbrQuestionPage = $this->params['question_page'] ; 
				}
			}
			elseif ($this->quiz->paginate['use_pagination'] == 1) 
			{
				
				// case of local quiz parameter
				if($this->quiz->paginate['use_pagination']) 
				{
					switch ($this->quiz->paginate['use_slide']) :
					
						case 0 :
							$showType = "show" ;
							$hideType = "hide" ;
							break ;
	
						case 1 :
							$showType = "slideIn" ;
							$hideType = "slideOut" ;
							break ;	
					
					endswitch ;
					
					$nbrQuestionPage = $this->quiz->paginate['question_page'] ; 
				}
			} 
			
			
			$totalNbrQuestions = count($this->questions) ;	
			
			if ($nbrQuestionPage) 
			{
				$nbrPage = (int)($totalNbrQuestions / $nbrQuestionPage) ;
				
				if($totalNbrQuestions % $nbrQuestionPage) {
					$nbrPage++ ;
				}
			} else {
					$nbrPage = 1 ;
			}
					
			$document =& JFactory::getDocument();
			
			// The quiz is multi-pages
			if ($nbrPage > 1) 
			{
				$script = 'window.addEvent(\'domready\', function() {  ' ;
				
				// declaring the pages
				for( $pageNumber = 1 ; $pageNumber <= $nbrPage ; $pageNumber++ ) 
				{
					$script .= ' 
								var jquarksPage_' . $pageNumber . ' = new Fx.Slide(\'jquarksPage_' . $pageNumber . '\',  {mode: \'horizontal\'}) ; ' ;	
				}
				 
				// hiding the next pages
				for( $pageNumber = 2 ; $pageNumber <= $nbrPage ; $pageNumber++ ) 
				{
					$script .= ' 
								jquarksPage_' . $pageNumber . '.hide() ; 
								page' . $pageNumber . ' = document.getElementById(\'jquarksPage_' . $pageNumber . '\') ;
						  		page' . $pageNumber . '.className="hidden" ;
						  		page' . $pageNumber . '.style.visibility="hidden" ;' ;
				} 
				
				// adding the events to the pages
				for ($pageNumber = 1, $prevPage = $pageNumber - 1, $nextPage = $pageNumber + 1 ; $pageNumber <= $nbrPage ; $prevPage++, $pageNumber++, $nextPage++)
				{	
					if ($prevPage) 
					{
						$script .= 	' 
							   $(\'jquarksPage_' . $pageNumber . 'back\').addEvent(\'click\', function(e){ 
									
							  		jquarksPage_' . $pageNumber . '.' . $hideType .'() ;
									page' . $pageNumber . ' = document.getElementById(\'jquarksPage_' . $pageNumber . '\') ;
							  		page' . $pageNumber . '.className="hidden" ;
							  		page' . $pageNumber . '.style.visibility="hidden" ;
							  		
									jquarksPage_' . $prevPage . '.' . $showType . '() ;
									page' . $prevPage . ' = document.getElementById(\'jquarksPage_' . $prevPage . '\') ;
							  		page' . $prevPage . '.className="shown" ;
									page' . $prevPage . '.style.visibility="" ;
							   }) ' ;
					}
					
					if ($nextPage <= $nbrPage) 
					{
						$script .= ' 
						       $(\'jquarksPage_' . $pageNumber . 'next\').addEvent(\'click\', function(e){ 

							  		jquarksPage_' . $pageNumber . '.' . $hideType . '() ;
							  		page' . $pageNumber . ' = document.getElementById(\'jquarksPage_' . $pageNumber . '\') ;
							  		page' . $pageNumber . '.className="hidden" ;
							  		page' . $pageNumber . '.style.visibility="hidden" ;
							  		
							  	 	jquarksPage_' . $nextPage . '.' . $showType . '() ;
							  		page' . $nextPage . ' = document.getElementById(\'jquarksPage_' . $nextPage . '\') ;
							  		page' . $nextPage . '.className="shown" ;
							  		page' . $nextPage . '.style.visibility="" ;
								
							   }) '	;
					}
				}
				
				$script .= ' 
							}); ' ;
				
				$document->addScriptDeclaration($script) ;
			}	

			// adding the corresponding styling TODO make this a css file 
			$style = '#jquarksNav {width: 100%; }
                                  #jquarksNav ul { list-style : none ; } 
                                  #jquarksNav div.hidden {display: block; float: left; position:fixed; }
                                  #jquarksNav div.shown {display: block; float: left; position:static; }';
			
			$document->addStyleDeclaration($style) ;
		?>
	
	<div id="jquarksNav">
		<?php 
			 // qNum number of the current question
			 // pNum number of the current page  
			$qNum = $pNum = 1 ;
			 
			foreach ($this->questions as $question)  
			{ 
			  	if ($nbrQuestionPage) 
			  	{
				  	if( $qNum % $nbrQuestionPage == 1 || $qNum == 1 || $nbrQuestionPage == 1) {
				  		echo '<div id="jquarksPage_' . $pNum . '">' ; 
				  	}
			  	} 
			  	else 
			  	{ 
				    if ( $qNum == 1 ) {
						echo '<div id="jquarksPage_1">' ;
				    } 
			  	}
			  	
			 	echo '<h3>' . $question->statement . '</h3>'  ; 
		 
				switch($question->type_id) 
				{
					case '1' : // input question
						$questionInputId = 'answers[' . $question->id . ']' ;
						$html = '<textarea name="' . $questionInputId . '"rows="2" cols="50"></textarea><br /><br />' ;
						echo $html ;
						break ;
					
					case '2' : // radio question
						$propositions = $this->propositions[$question->id] ;
						echo '<ul>' ;
						foreach ($propositions as $proposition) :

							$questionRadioId = 'answers[' . $question->id . ']'  ;
							$questionRadioProp = $proposition->id ;
							$html = '<li><input type="radio" name="' . $questionRadioId . '" id="' . $questionRadioProp . '" value="' . $questionRadioProp . '" /><label for="' . $questionRadioProp . '">' . nl2br(htmlentities($proposition->answer)) . '</label></li>' ;
							echo $html ;
						endforeach ;
						echo '</ul>' ;
						break ;
					
					case '3' : // checkbox question
					case '4' : // radio shown as checkbox	
						$propositions = $this->propositions[$question->id] ;
						echo '<ul>' ; 
						foreach ($propositions as $proposition) :
		
							$questionCheckboxId = 'answers[' . $question->id . '][' . $proposition->id . ']' ;
							$questionCheckbox = 'q'. $question->id .'p' . $proposition->id ;
							$html = '<li><input type="checkbox" name="' . $questionCheckboxId . '" id="' . $questionCheckbox . '" /><label for="' . $questionCheckbox . '">' . nl2br(htmlentities($proposition->answer)) . '</label></li>' ;
							echo $html ;
						endforeach ;
						echo '</ul>' ;
						break ;
							
					default :
						$html = 'Invalid Type of question' ;
						echo $html ;
				}

			// adding the back next link if multi-pages	
			if ($nbrPage > 1) 
			{
				if( $pNum == (int)($qNum / $nbrQuestionPage) || $qNum == $totalNbrQuestions ) 
				{
					
					if ($pNum == 1) 
					{
						echo '<span class="jquarks_qprog"><a id="jquarksPage_' . $pNum . 'next" href="#">' . JText::_('NEXT') . '</a>'
						     . '<p>' . JText::_("PAGE") . ' ' . $pNum . ' / ' . $nbrPage .'</p>' .'</span></div>' ; 
					} 
					elseif ( $pNum == $nbrPage ) 
					{
						echo '<span class="jquarks_qprog"><a id="jquarksPage_' . $pNum . 'back" href="#">' . JText::_('BACK') . '</a>'
							 . '<p>' . JText::_("PAGE") . ' ' . $pNum . ' / ' . $nbrPage .'</p>' .'</span></div>' ;	
					} 
					else 
					{
						echo '<span class="jquarks_qprog"><a id="jquarksPage_' . $pNum . 'back" href="#">' . JText::_('BACK') . '</a>
						     |
						     <a id="jquarksPage_' . $pNum . 'next" href="#">' . JText::_('NEXT') . '</a>' 
							. '<p>' . JText::_("PAGE") . ' ' . $pNum . ' / ' . $nbrPage .'</p>'   
						    . '</span></div>' ;
					}
					$pNum ++ ;
			 	} 
			} 
		 	 $qNum++ ;
		   } 
		   if ($nbrPage == 1 ) :
		   		echo "</div>" ;
		   endif ;
		   ?>
		
	</div>
	<div style="clear: both;"></div>
	<div>
		<p>
		<?php if ($nbrPage > 1) : ?>
			<input type="submit" value="<?php echo JText::_('SUBMIT_ANSWERS_CHECK_PAGES') ; ?>" id="send" name="send" />
		<?php else : ?>
            <input type="submit" value="<?php echo JText::_('SUBMIT_ANSWERS') ; ?>" id="send" name="send" />
		<?php endif ; ?>
		</p>  	
	  	<p>
	  	    <a href="http://www.jquarks.org" target="_blank">Powered by JQuarks</a>
	  	</p>
	</div>
	<?php 
		$attribs = array('type' => 'text/css');
		$document->addHeadLink(JRoute::_("components/com_jquarks/assets/stylesheets/SyntaxHighlighter.css"), "stylesheet", "rel", $attribs) ; 
	?>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shCore.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushCpp.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushCSharp.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushCss.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushDelphi.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushJava.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushJScript.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushPhp.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushPython.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushRuby.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushSql.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushVb.js") ; ?>"></script>
	<script language="javascript" src="<?php echo JRoute::_("components/com_jquarks/assets/js/shBrushXml.js") ; ?>"></script>
	<script language="javascript">
		dp.SyntaxHighlighter.ClipboardSwf = 'administrator/components/com_jquarks/assets/js/clipboard.swf';
		dp.SyntaxHighlighter.BloggerMode();
		dp.SyntaxHighlighter.HighlightAll('code');
	</script>
						  
	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="id" value="<?php echo $this->quiz->id ; ?>"/>
	<input type="hidden" name="sessionId" value="<?php echo $this->sessionId ; ?>"/>
	<input type="hidden" id="timeUp" name="timeUp" value="0" />
	<input type="hidden" name="task" value="submitAnswer" />
	<input type="hidden" name="view" value="quiz" />
    <input type="hidden" name="showResults" value="<?php echo $this->isShowResults ; ?>" />

	<?php echo JHTML::_( 'form.token' );  ?> 
</form>
    </div>
</div>

<?php endif ; ?>