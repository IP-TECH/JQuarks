<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
    
    $url = JRoute::_("components/com_jquarks/assets/stylesheets/jquarks_style.css");
    $document =& JFactory::getDocument();
    $document->addHeadLink($url, "stylesheet", "rel");
?>

<div class="componentheading">
	<?php echo JText::_('SESSION_RESULTS'); ?>
</div>

<!-- Score and answers status -->
<?php
    $score = $this->session->score;
    $totalScore = $this->totalQuestions - $this->totalInput + $this->inputEvaluated;
    $percent = $score * 100 / $totalScore;
?>
<br />
<div class="contentheading">
 <p>
     <span class="session-title"><?php echo JText::_('QUIZ').': ' ;?></span>
    <?php echo $this->session->title; ?>
 </p>
 <p>
     <span class="session-title"><?php echo JText::_('SCORE').': ' ;?></span>
    <?php echo  round($percent, 2) . '%&nbsp;&nbsp;&nbsp;'; ?>
     <span class="note-small">
          <?php echo JText::_('INPUT_QUESTIONS_ARE_NOT_RECORDED_IN_THE_SCORE'); ?>
     </span>
 </p>
 <p>
     <span class="session-title"><?php echo JText::_('IS_UNANSWERED').': ' ; ?></span>
    <?php echo $this->session->unanswered; ?>
 </p>
</div>

<!-- Answers details -->
<br />
<div >

<?php

     //defining format for evaluated propositions
    $li      = '<li>';
    $eli     = '</li>';
    $ligreen = '<li class="green-li">';
    $corr    = '<span class="li-highlight-green">  ('  . JText::_('GOOD')    . ')</span>';
    $incorr  = '<span class="li-highlight-red">  ('    . JText::_('WRONG')   . ')</span>';
    $omi     = '<span class="li-highlight-orange">  (' . JText::_('OMITTED') . ')</span>';


     $idq = NULL; // contains the actual question id
     foreach ($this->results as $res) :

        // show question statement if this latter doesn't exist
        $idq_temp = $res->question_id;
        if ($idq_temp != $idq)
        {
            $idq = $idq_temp;
            if ($idq != NULL) {
                echo '</ul>';
            }
            echo '<br /><span class="session-title">' . $res->statement . '</span>';
            echo '<ul class="result-list">';
        }

        switch($res->type_id) :
                case 1: // input question
                    echo $li . $res->altanswer. $eli;
                    break;

                case 2: // option question
                    if ($res->answer_id != NULL) // exists in answers
                    {
                        if($res->correct) {
                            echo $ligreen . $res->answer . $corr . $eli; //correct
                        } else {
                            echo $li . $res->answer . $incorr . $eli;  // incorrect
                        }

                    }
                    else
                    {
                        if ($res->correct) {
                            echo $ligreen . $res->answer . $eli;
                        } else {
                            echo $li . $res->answer . $eli;
                        }
                    }
                    break;

                 case 3: //checkbox question
                 case 4: // option as checkbox
                          if($res->correct) {
                              $debut = $ligreen . $res->answer;
                          } else {
                              $debut = $li      . $res->answer;
                          }
                          if (is_null($res->status))
                          {
                              $fin = $eli;
                          }
                          else
                          {
                              switch($res->status)
                              {
                                  case -2: $fin = $eli;           break;
                                  case  0: $fin = $incorr . $eli; break;
                                  case  1: $fin = $corr   . $eli; break;
                                  case  2: $fin = $omi    . $eli; break;
                              }
                          }
                          echo $debut.$fin;
                
         endswitch;
    endforeach;
?>
</div>
