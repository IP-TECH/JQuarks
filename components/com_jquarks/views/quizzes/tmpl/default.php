<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<style>
	.listQuizzes li {list-style:none ; }
	.listQuizzes img {vertical-align: bottom; }
	.quiz {margin-bottom: 20px ;}
	.quiz li {margin-bottom: 2px; }
</style>
<div class="items-row">
    <div class="item">
        <h2><?php echo JText::_('QUIZZES') ;?></h2>
        <?php if (count($this->publicQuizzes) ) : ?>
                <h3><?php echo JText::_('PUBLIC_QUIZZES') ; ?></h3>
        <?php endif ; ?>	  

        <ul class="listQuizzes">
                <?php foreach($this->publicQuizzes as $quiz)  : 
                                $link = JRoute::_('index.php?option=com_jquarks&view=quiz&id=' . $quiz->id) ;
                ?>		
                        <li class="quiz">
                            <p class="readmore">
                                <a href="<?php echo $link ;?>"><?php echo $quiz->title;?></a><br />
                            </p>
                                <ul>
                                        <?php if ($quiz->description) :?>
                                        <li>
                                            <img src="components/com_jquarks/assets/images/description.png" alt="<?php echo JText::_('DESCRIPTION') ; ?>" />
                                            <?php echo ' ' . $quiz->description ; ?>
                                        </li>
                                        <?php endif ;?>

                                        <?php if ($quiz->time_limit) :?>
                                        <li>
                                            <img src="components/com_jquarks/assets/images/clock.png" alt="<?php echo JText::_('TIMED_QUIZ') ; ?>" /><?php echo ' ' . $quiz->time_limit . ' ' . JText::_('MINUTES') ; ?>
                                        </li>
                                        <?php endif ;?>
                                </ul>
                        </li>
                <?php endforeach ; ?>
        </ul>

        <?php if (count($this->userQuizzes) ) : ?>
                <h3><?php echo JText::_('PRIVATE_QUIZZES') ; ?></h3>
        <?php endif ; ?>

        <ul class="listQuizzes">	
                <?php foreach($this->userQuizzes as $quiz) : 
                                $link = JRoute::_('index.php?option=com_jquarks&view=quiz&id=' . $quiz->id) ;
                ?>		
                        <li class="quiz">
                            <p class="readmore">
                                <a href="<?php echo $link ;?>"><?php echo $quiz->title;?></a><br />
                            </p>
                                <ul>
                                        <?php if ($quiz->description) :?>
                                        <li><img src="components/com_jquarks/assets/images/description.png" alt="<?php echo JText::_('DESCRIPTION') ; ?>" /><?php echo ' ' . $quiz->description ; ?></li>
                                        <?php endif ;?>

                                        <?php if ($quiz->time_limit) :?>
                                        <li>
                                                <img src="components/com_jquarks/assets/images/clock.png" alt="<?php echo JText::_('TIMED_QUIZ') ; ?>" /><?php echo ' ' . $quiz->time_limit . ' ' . JText::_('MINUTES') ; ?>
                                        </li>
                                        <?php endif ;?>
                                        <?php 
                                                if ($quiz->unique_session) :
                                        ?>
                                                <li>
                                                        <img src="components/com_jquarks/assets/images/attention.png" alt="<?php echo JText::_('UNIQUE_SESSION') ; ?>" /><?php echo ' ' . JText::_('THIS_QUIZ_MAY_ONLY_BE_PASSED_ONCE') ; ?>
                                                </li>
                                        <?php 	
                                                endif ;
                                        ?>
                                </ul>
                        </li>
                <?php endforeach ; ?>
        </ul>
    </div>
</div>