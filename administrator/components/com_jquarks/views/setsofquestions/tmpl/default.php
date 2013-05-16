<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
								<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->setsofquestions ); ?>);" />
					</th>
					<th>
						<?php echo JHTML::_( 'grid.sort', 'TITLE', 'TITLE', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
					<th>
						<?php echo JText::_( 'ASSOCIATED_QUESTIONS' );  ?>
					</th>
					<th>
						<?php echo JText::_( 'ASSOCIATED_TO_QUIZZES' );  ?>
					</th>
					<th>
						<?php echo JHTML::_( 'grid.sort', 'SET_TYPE', 'type', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
					<th width="5">
						<?php echo JHTML::_( 'grid.sort', 'ID', 'id', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
				</tr>
			</thead>	
			<tbody>
			<?php 
				if (count( $this->setsofquestions )) :	
					$k = 0;
					for ($i = 0, $n = count( $this->setsofquestions ) ; $i < $n ; $i++) :
						$row =& $this->setsofquestions[$i];
						$checked = JHTML::_( 'grid.id', $i, $row->id );
						$link = JRoute::_( 'index.php?option=com_jquarks&view=setofquestions&task=edit&cid[]='. $row->id );
						
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td align="center">
								<?php echo $i+1; ?>
							</td>
							<td>
								<?php echo $checked; ?>
							</td>
							<td>
								<span class="editlinktip hasTip" title="<?php echo JText::_( 'TITLE' );?> : <?php echo $row->title; ?>">
									<a href="<?php echo $link ; ?>"><?php echo $row->title; ?></a>
								</span>		
							</td>
							<td>
								<?php 
									if ( $row->type == 'random' ) : 
										echo $row->nquestions ;
									else :
									  	echo $row->count ;
									endif ;
								?>
							</td>
							<td>
								<?php 
									foreach ( $this->affectedQuizzes[$row->id] AS $quiz) :
										
										$linkQuiz = JRoute::_( 'index.php?option=com_jquarks&view=quiz&task=edit&cid[]='. $quiz->id );
										
										if ($quiz->published) :
											echo '<a href=' . $linkQuiz . ' style="color:green" title=' . JText::_('PUBLISHED_QUIZ') . '>' . $quiz->title . '</a><br />' ;
										else :
											echo '<a href=' . $linkQuiz . ' style="color:red" title=' . JText::_('UNPUBLISHED_QUIZ') . '>' . $quiz->title . '</a><br />' ;
										endif ;
											
									endforeach ;
								?>
							</td>
							<td>
								<?php 
									if ( $row->type == 'random' ) :
										echo JText::_('RANDOM') ;
									else :
									  	echo JText::_('CUSTOM') ;
									endif ;
								?>
							</td>
							<td>
								<?php echo $row->id; ?>				
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					endfor ;
				else :
					echo '<tr><td colspan="7">' . JText::_('THERE_ARE_NO_SETS_OF_QUESTIONS') . '<br /><br />' . JText::_('MIN_TUTO_SETS_OF_QUESTIONS') . '</td></tr>' ;
				endif ;	 
			?>		
			</tbody>
			<tfoot>
				<tr>
					<td colspan="7">
						<?php echo $this->pageNav->getListFooter() ; ?>
					</td>
				</tr>
			</tfoot>
		</table>	
	</div>
	
	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="setsofquestions" />
	<input type="hidden" name="view" value="setsofquestions" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>