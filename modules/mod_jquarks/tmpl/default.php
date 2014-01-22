<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<style type="text/css">
.jquarks_mod ul {list-style: none;}
.jquarks_mod img {vertical-align: bottom; }
.view_all_quizzes {margin-left: auto; width: 90%; display: block; text-align: right; } 
</style>

<div class="jquarks_mod">
	<?php 
		if ($publicQuizzes || $userQuizzes) :
			$nbrQuiz = $params->get('numberQuiz', 1) ;
			if (count($publicQuizzes)) :
			?>
				<h4><?php echo JText::_('PUBLIC_QUIZZES') ; ?></h4>
				<ul>
				<?php for ($i = 0 ; $i < $nbrQuiz && $i < count($publicQuizzes) ; $i++ ) : 
						$link = JRoute::_('index.php?option=com_jquarks&view=quiz&id=' . $publicQuizzes[$i]->id) ;
				?> 
						<li><a href="<?php echo $link ; ?>"><?php echo $publicQuizzes[$i]->title ; ?></a>
							<?php if ($publicQuizzes[$i]->time_limit) : ?>
								<img src="components/com_jquarks/assets/images/clock.png" alt="<?php echo JText::_('TIMED_QUIZ') ; ?>" title="<?php echo JText::sprintf('YOU_HAVE_MINUTES_TO_FINISH_THIS_QUIZ', (int)$publicQuizzes[$i]->time_limit ) ; ?>" />
							<?php endif ;?>
						</li>
				<?php endfor ; ?>
				</ul>
			<?php endif ;
		  	
			if (count($userQuizzes)) :
			?>
				<h4><?php echo JText::_('PRIVATE_QUIZZES') ; ?></h4>
				<ul>
				<?php for ($i = 0 ; $i < $nbrQuiz && $i < count($userQuizzes) ; $i++ ) : 
						$link = JRoute::_('index.php?option=com_jquarks&view=quiz&id=' . $userQuizzes[$i]->id) ;
				?> 
						<li><a href="<?php echo $link ; ?>"><?php echo $userQuizzes[$i]->title ; ?></a>
							<?php if ($userQuizzes[$i]->time_limit) : ?>
								<img src="components/com_jquarks/assets/images/clock.png" alt="<?php echo JText::_('TIMED_QUIZ') ; ?>" title="<?php echo JText::sprintf('YOU_HAVE_MINUTES_TO_FINISH_THIS_QUIZ', (int)$userQuizzes[$i]->time_limit ) ; ?>" />
							<?php endif ;?>
							<?php if ($userQuizzes[$i]->unique_session) : ?>
							  	<img src="components/com_jquarks/assets/images/attention.png" alt="<?php echo JText::_('UNIQUE_SESSION') ; ?>" title="<?php echo JText::_('THIS_QUIZ_MAY_ONLY_BE_PASSED_ONCE') ; ?>" />
							<?php endif ;?>
						</li>
				<?php endfor ; 	?>	
				</ul>
				
			<?php endif ;

				  if ($nbrQuiz < count($publicQuizzes) || $nbrQuiz < count($userQuizzes)) : 
				?>
					<a href="index.php?option=com_jquarks&view=quizzes" class="view_all_quizzes"><?php echo JText::_('VIEW_ALL_QUIZZES') ; ?></a>
				<?php endif ;		  
		endif ;
	?>
	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="quiz" />
</div>
