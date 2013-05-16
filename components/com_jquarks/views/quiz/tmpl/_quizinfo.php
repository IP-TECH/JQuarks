<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<script type="text/javascript" language="javascript">

	function proceed()
	{
	    check = document.getElementById('checkToProceed') ;
	    proceedButton = document.getElementById('proceedButton') ;
	
	    if (check.checked) {
	    	proceedButton.disabled = false ;
	    } else {
	    	proceedButton.disabled = true ;
	    }
	}
        
</script>

<form name="quiz_info" method="post">

<?php 
	echo '<div class="items-row"><div class="item">';
        
        echo '<h2>' . JText::sprintf('YOU_HAVE_CHOSEN_TO_TAKE_QUIZ', '"'.$this->quiz->title.'"') . '</h2>' ;
	
	echo '<ul>' ;

	if ($this->quiz->time_limit) {
		echo '<li><p>' . JText::sprintf('THE_QUIZ_IS_LIMITED_IN_TIME_YOU_HAVE_TO_FINISH_IT_IN_MINUTES', $this->quiz->time_limit) . '</p></li>' ;
	}
	
	if ($this->quiz->unique_session) {
		echo '<li><p>' . JText::_('THE_QUIZ_IS_A_UNIQUE_SESSION_QUIZ_WHICH_MEAN_YOU_WILL_ONLY_ABLE_TO_PASS_IT_ONCE') . '</p></li>' ;
	}
	
	if ($this->quiz->paginate['use_pagination'] == 1 || ($this->quiz->paginate['use_pagination'] == 2 && $this->params['paginate'] == 1) ) {
        echo '<li><p>' . JText::_('THE_QUIZ_IS_PAGINATED_PLEASE_BE_SURE_TO_ANSWER_ALL_QUESTIONS_BEFORE_SUBMITTING'). '</p></li>' ;			
	}
	
	echo '</ul>' ;
	$option = JRequest::getCmd('option');
        
	$link =  JRoute::_('index.php?option='. $option . '&controller=quiz&layout=default') ;
	
	echo '<p><input type="checkbox" id="checkToProceed" name="checkToProceed" onclick="proceed();" /><label for="checkToProceed">' . JText::_('I_HAVE_READ_AND_UNDERSTOOD') . '</label></p>' ;
	echo '<input id="proceedButton" name="proceedButton" disabled="true" value="' . JText::_('PROCEED_TO_QUIZ') . '" type="submit" />' ;
        
        echo '</div></div>';
?>
	
    <input type="hidden" name="option" value="com_jquarks" />
    <input type="hidden" name="id" value="<?php echo $this->quiz->id ; ?>"/>
    <input type="hidden" name="task" value="showQuiz" />
    <input type="hidden" name="view" value="quiz" />
    <input type="hidden" name="layout" value="default" />
    <?php echo JHTML::_( 'form.token' );  ?> 
</form>