<?php
    defined( '_JEXEC' ) or die( 'Restricted access' );

    $mainframe =& JFactory::getApplication();
    $doc =& JFactory::getDocument();
    $href = $this->baseurl.'/components/com_jquarks/assets/stylesheets/session.css'; 
    $attribs = array('type' => 'text/css'); 
    $doc->addHeadLink( $href, 'stylesheet', 'rel', $attribs );
    
        
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseurl ?>/components/com_jquarks/assets/js/jquery-1.7.2.js">
</script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseurl ?>/components/com_jquarks/assets/js/session.js">
</script>
<script language="javascript" type="text/javascript">
    window.onload = function () { onLoadWindow(document); }
</script>
<script language="javascript" type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function(){
    jQuery("#toggleresults").click(function () {
        if (jQuery("#newviewdetails").is(":hidden"))
        {
            jQuery("#results").fadeOut();
            jQuery("#newviewdetails").fadeIn();
            jQuery("#toggleresults").html('(-)');
        }
        else
        {
            jQuery("#newviewdetails").fadeOut();
            jQuery("#results").fadeIn();
            jQuery("#toggleresults").html('(+)');
        }
    });
});
</script>

<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
	<table>
            <tr>
                <td width="25%" valign="top">
                    <fieldset class="batch">
                        <legend><?php echo JText::_("SCORING")?></legend>
                        <table class="admintable">
                            <tr>
                                <td width="100" align="left" class="key">
                                        <label for="title">
                                                <?php echo JText::_( 'SCORE' ) ; ?>:
                                        </label>
                                </td>
                                <td>
                                        <?php echo $this->session->score == "" ? 0 : $this->session->score ; ?>
                                </td>
                            </tr>
                            <tr>
                                    <td width="200" align="left" class="key">
                                            <label for="title">
                                                    <?php echo JText::_( 'MAX_SCORE' ) ; ?>:
                                            </label>
                                    </td>
                                    <td align="center">
                                            <?php echo $this->session->maxScore ; ?>
                                    </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
                <td width="25%" valign="top">
                        <fieldset class="batch">
                                <legend><?php echo JText::_("ANSWERS_STATUS")?></legend>
                                <table class="admintable">
                                        <tr>
                                                <td width="200" align="left" class="key">
                                                        <label for="title">
                                                                <?php echo JText::_( 'IS_UNANSWERED' ) ; ?>:
                                                        </label>
                                                </td>
                                                <td align="center">
                                                        <?php echo $this->session->unanswered ; ?>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td width="100" align="left" class="key">
                                                        <label for="title">
                                                                <?php echo JText::_( 'TO_CORRECT' ) ; ?>:
                                                        </label>
                                                </td>
                                                <td align="center">
                                                        <?php echo $this->session->evaluate ; ?>
                                                </td>
                                        </tr>
                                </table>
                        </fieldset>
                </td>
			<td width="25%" valign="top">
				<fieldset class="batch">
					<legend><?php echo JText::_("TIME_STATUS")?></legend>
					<table class="admintable">
						<tr>
							<td width="100" align="left" class="key">
								<label for="title">
									<?php echo JText::_( 'STARTED_ON' ) ; ?>:
								</label>
							</td>
							<td>
								<?php echo $this->session->started_on ; ?>
							</td>
						</tr>
						<tr>
							<td width="100" align="left" class="key">
								<label for="title">
									<?php echo JText::_( 'FINISHED_ON' ) ; ?>:
								</label>
							</td>
							<td>
								<?php echo $this->session->finished_on == "0000-00-00 00:00:00" ? JText::_("UNFINISHED") : $this->session->finished_on ; ?>
							</td>
						</tr>
						<tr>
							<td width="100" align="left" class="key">
								<label for="title">
									<?php echo JText::_( 'SPENT_TIME' ) ; ?>:
								</label>
							</td>
							<td>
								<?php echo $this->session->spent_time ; ?>
							</td>
						</tr>
					</table>
				</fieldset>					
			</td>
			<td width="25%" valign="top">
                            <fieldset class="batch">
			         <legend><?php echo JText::_( 'USERS_PROFILES' ) ; ?></legend>
			         <?php
                                        $profilesList = '';
				         foreach ($this->profiles as $profile)
                                         {
                                             if ($profile->assigned)
                                             {
                                                 $profilesList .= "<li>$profile->title</li>" ;
                                             }
                                         }
                                         if ($profilesList != '')
                                         {
                                             $profilesList = '<ul>'.$profilesList.'</ul>';
                                         }
                                         echo $profilesList;
			         ?>
			    </fieldset>
			</td>
		</tr>
	</table>	
	<fieldset class="batch">
		<legend><?php echo JText::_("ANSWERS_DETAILS").' '?><span style="cursor: pointer;" title="<?php echo JText::_("EXTEND_DETAILS").' '?>" id="toggleresults">(+)</span></legend>
		<table class="adminlist" id="results">
			<thead>
				<tr>
					<th>
						<?php echo JText::_( 'QUESTION' ) ; ?>
					</th>
					<th>
						<?php echo JText::_( 'ANSWER' ); ?>
					</th>				
					<th>
						<?php echo JText::_( 'RESULT' )?>
					</th>
				</tr>
			</thead>	
			<tbody>
			<?php foreach ($this->answers as $answer)  : //$answer->published = 1 ; $linkPublish = JHTML::_('grid.published', $answer->published, 1); ?>		
				
				<tr>
                                    <td><?php echo strip_tags($answer->question) ; ?></td>
					<?php 
						if ($answer->altanswer != '')
                                                {
                                                    $userAns = $answer->altanswer ;
                                                }
						else
                                                {
                                                    $userAns = $answer->answer ;
                                                }

                                                echo '<td> '.$userAns.' </td>';

                                                    switch ($answer->status)
                                                    {
                                                        case -2 :
                                                                $html = "<td style=\"color:gray\">". JText::_('IS_UNANSWERED');
                                                                break;

                                                        case -1 :
                                                                $html = "<td style=\"color:gray\">". JText::_('IS_UNANSWERED');
                                                                break;

                                                        case 0 :

                                                                $html = "<td style=\"color:red\">". JText::_("IS_FALSE");
                                                                break;

                                                        case 1 :
                                                                $html = "<td style=\"color:green\">". JText::_("IS_TRUE");
                                                                break;

                                                        case 2 :

                                                                $html = "<td style=\"color:red\">". JText::_("IS_OMITTED");
                                                                break ;

                                                        default :
                                                                $html = "<td>". JText::_('TYPE_ANSWER_NOT_SUPPORTED') ;
                                                    }
                                                    echo $html ;
                                                    if ($answer->type_id == '1'):
                                                    ?>
                                                        <a href="javascript:void(0)" onclick="submitAnswer(<?php echo $answer->id ?> , 'correct');">
                                                        <img src="templates/bluestork/images/admin/tick.png"
                                                            border="0"
                                                            title="<?php echo JText::_('CORRECT') ?>"
                                                            alt="<?php echo JText::_('CORRECT') ?>" />
                                                        </a>
                                                        <a href="javascript:void(0)" onclick="submitAnswer(<?php echo $answer->id ?> , 'incorrect');">
                                                        <img src="templates/bluestork/images/admin/publish_r.png"
                                                            border="0"
                                                            title="<?php echo JText::_('INCORRECT') ?>"
                                                            alt="<?php echo JText::_('INCORRECT') ?>" />
                                                        </a>
                                                   <?php
                                                    endif;
                                                    echo '</td>';
                                                                                              
					?>	
				</tr>
			<?php endforeach ; ?>	
			</tbody>
		</table>	
            <table class="adminlist" id="newviewdetails" style="display: none;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>
                            <?php echo JText::_( 'Question' ) ; ?>
                        </th>
                        <th>
                            <?php echo JText::_( 'ANSWER' ); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            $key = 0;
                            foreach ($this->formattedAnswers as $question_id => $question):
                                $statement    = $question['statement'];
                                $propositions = $question['propositions'];
                                $type_id      = $question['type_id'];                                  
                        ?>
                        <tr>
                            <td rowspan="2" style="vertical-align: top;">
                                <?php echo $key+1 ?>
                            </td>
                            <td colspan="2">
                                <?php echo strip_tags($statement) ?>
                            </td>
                        </tr>
                        <?php
                            // input question
                            if ($type_id == 1):
                        ?>
                            <tr>
                                <td colspan="2">
                                    <div style="float:left">
                                        <textarea readonly cols="70" rows="" ><?php echo (array_key_exists('altanswer', $question)) ? $question['altanswer'] : ''; ?></textarea>
                                    </div>
                                    <?php
                                        if (array_key_exists('status', $question)) {
                                            $status = $question['status'];                                            
                                        } else {
                                            $status = 9999;
                                        }
                                        $inputstatus = '';
                                        switch ($status)
                                        {
                                            case -1:
                                                $inputstatus = JText::_( 'IS_UNANSWERED' );
                                                break;

                                            case 0:
                                                $inputstatus = '<span style="color:red;">'.JText::_('INCORRECT').'</span>';
                                                break;

                                            case 1:
                                                $inputstatus = '<span style="color:green;">'.JText::_('CORRECT').'</span>';
                                                break;
                                            case 9999:
                                                break;
                                        }
                                    ?>
                                    <div style="float:left">
                                        <div>
                                            <a href="javascript:void(0)" onclick="submitAnswer(<?php echo (array_key_exists('answer_session_id', $question)) ? $question['answer_session_id'] : '0';  ?> , 'correct');">
                                                <img src="templates/bluestork/images/admin/tick.png"
                                                     border="0"
                                                     title="<?php echo JText::_('CORRECT') ?>"
                                                     alt="<?php echo JText::_('CORRECT') ?>" />
                                            </a>
                                            <a href="javascript:void(0)" onclick="submitAnswer(<?php echo (array_key_exists('answer_session_id', $question)) ? $question['answer_session_id'] : '0';  ?> , 'incorrect');">
                                                <img src="templates/bluestork/images/admin/publish_r.png"
                                                     border="0" title="<?php echo JText::_('INCORRECT') ?>"
                                                     alt="<?php echo JText::_('INCORRECT') ?>" />
                                            </a>
                                        </div>
                                        <div>
                                            <?php echo $inputstatus ?>
                                        </div>
                                    </div>
                                    <br />
                                    
                                </td>
                            </tr>
                        <?php
                            // if not input question
                            else:
                        ?>
                            <tr>
                                <td>
                                    <ul>
                                    <?php
                                        // display correct answers
                                        foreach ($propositions as $proposition)
                                        {
                                            switch ($type_id)
                                            {
                                                case 2:
                                                    $type = 'option';
                                                    break;
                                                case 3:
                                                case 4:
                                                    $type = 'checkbox';
                                                    break;
                                            }
                                            $correct = ($proposition['correct']) ? 'checked' : 'unchecked';
                                            $class = $correct.'_'.$type;
                                            echo '<li class="'.$class.'">'.$proposition['proposition'].'</li>';
                                        }
                                    ?>
                                    </ul>
                                </td>
                                <td>
                                    <ul>
                                    <?php
                                        // display user answers
                                        foreach ($propositions as $proposition_id => $proposition)
                                        {
                                            switch ($type_id)
                                            {
                                                case 2:
                                                    $type = 'option';
                                                    break;
                                                case 3:
                                                case 4:
                                                    $type = 'checkbox';
                                                    break;
                                            }
                                            
                                            if (array_key_exists('status', $proposition)) {
                                                $status = (int)$proposition['status'];
                                            } else {
                                                $status = 9999;
                                            }

                                            
                                            switch ($status)
                                            {
                                                // incorrect
                                                case 0:
                                                    $correct = 'checked';
                                                    $color = 'red';
                                                    break;

                                                // correct
                                                case 1:
                                                    $correct = 'checked';
                                                    $color = 'green';
                                                    break;

                                                // omitted
                                                case 2:
                                                    $correct = 'unchecked';
                                                    $color = 'orange';
                                                    break;
                                                
                                                case 9999:
                                                    $correct = 'unchecked';
                                                    $color = '';
                                            }

                                            $colorStyle = 'color:'.$color.';';
                                            $class = $correct.'_'.$type;
                                            echo '<li class="'.$class.'"><span style="'.$colorStyle.'">'.$proposition['proposition'].'</span></li>';
                                        }
                                    ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php
                            $key++;
                        endforeach;
                        ?>
                    </tbody>
            </table>
        </fieldset>
	</div>
	
	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="cid[]" value="<?php echo $this->session->id ; ?>" />
	<input type="hidden" name="sessAnsId" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="sessions" />
	<input type="hidden" name="view" value="session" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
