<?php defined('_JEXEC') or die('Restricted access'); ?>

<script type="text/javascript" language="javascript">

function submitAssignUsers(id){
    
    quizId = document.getElementsByName('cid[]') ;
    quizId = quizId[quizId.length - 1];
    quizId.value = id ;

    document.adminForm.task.value = 'assignUsers' ;
    document.adminForm.submit() ;
}

function submitAssignSets(id) {
    
    quizId = document.getElementsByName('cid[]') ;
    quizId = quizId[quizId.length - 1];
    quizId.value = id ;

    document.adminForm.task.value = 'assignSets' ;
    document.adminForm.submit() ;
}
	
</script>
 

<form action="index.php" method="post" name="adminForm">
	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->quizzes ); ?>);" />
					</th>
					<th width="20%">
						<?php echo JHTML::_( 'grid.sort', 'TITLE', 'TITLE', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>			
					<th>
						<?php echo JHTML::_( 'grid.sort', 'DESCRIPTION', 'description', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
					<th>
						<?php echo JText::_('AFFECTED_SETS') ; ?>
					</th>
					<th width="5%">
						<?php echo JHTML::_( 'grid.sort', 'PUBLISHED', 'published', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
					<th width="5%">
						<?php echo JHTML::_( 'grid.sort', 'ACCESS', 'groupname', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
					<th width="5%">
						<?php echo JText::_('USER_AFFECTATION') ;  ?>
					</th>
					<th width="5%">
						<?php echo JText::_('SETS_ASSIGNATION') ; ?>
					</th>
					<th width="5">
						<?php echo JHTML::_( 'grid.sort', 'ID', 'id', $this->lists['order_Dir'], $this->lists['order'] );  ?>			
					</th>
				</tr>
			</thead>	
			<tbody>
			<?php 
				if (count( $this->quizzes )) :
					$k = 0;
					for ($i = 0, $n = count( $this->quizzes ) ; $i < $n ; $i++) :
						$row =& $this->quizzes[$i];
						$checked = JHTML::_( 'grid.id', $i, $row->id );
						$link = JRoute::_( 'index.php?option=com_jquarks&controller=quiz&task=edit&cid[]='. $row->id ) ;
						$linkPublish = JHTML::_('grid.published', $row, $i, 'publish_g.png', 'publish_x.png' );
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td align="center">
								<?php echo $i+1; ?>
							</td>
							<td>
								<?php echo $checked; ?>
							</td>			
							<td>
								<a href="<?php echo $link ; ?>"><?php echo $row->title; ?></a>	
							</td>			
							<td>
								<?php echo $row->description; ?>	
							</td>
							<td>
								<?php 
									foreach ( $this->affectedSets[$row->id] AS $set) :
										$linkSet = JRoute::_( 'index.php?option=com_jquarks&controller=setofquestions&task=edit&cid[]='. $set->id );
										echo '<a href=' . $linkSet . ' title=' . $set->title . '>' . $set->title . '</a><br />' ;
									endforeach ;	
								?>
							</td>
							<td align="center">
								<?php echo $linkPublish ; ?>
							</td>
							<td>
								<?php 
									// to improve try using grid.access later	
									if ( !$row->access_id )  :
							            $color_access = 'style="color: green;"';
                                                                    $group_name = JText::_('PUBLIC');
							        elseif ( $row->access_id == 1 ) :
							            $color_access = 'style="color: red;"';
                                                                    $group_name = JText::_('REGISTRED');
							        else :
							            $color_access = 'style="color: black;"';
                                                                    $group_name = "";
							        endif ;							
								?>
								<span <?php echo $color_access ;?>><?php echo $group_name ?></span>
							</td>
							<td align="center">
								<?php if ($row->access_id == 1) : ?>
									<a href="javascript:void(0);" onclick="submitAssignUsers(<?php echo $row->id ; ?>)">
                                                                            <img src="templates/bluestork/images/menu/icon-16-newuser.png"  title="<?php JText::_('PUBLIC_QUIZ') ?>" alt="<?php JText::_('PUBLIC_QUIZ') ?>"/>
                                                                        </a>
								<?php else : ?>
                                                                        <img src="templates/bluestork/images/admin/icon-16-denyinactive.png"  title="<?php JText::_('PUBLIC_QUIZ') ?>" alt="<?php JText::_('PUBLIC_QUIZ') ?>"/>
								<?php endif ; ?>
							</td>
							<td align="center">
                                                            <a href="javascript:void(0);" onclick="submitAssignSets(<?php echo $row->id ; ?>)">
                                                                <img src="templates/bluestork/images/admin/collapseall.png"  title="" alt=""/>
                                                            </a> 
							</td>
							<td>
								<?php echo $row->id; ?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					endfor ;
				 else :
				 	echo '<tr><td colspan="10">' . JText::_('THERE_ARE_NO_QUIZZES') . '<br /><br />' . JText::_('MIN_TUTO_QUIZZES') . '</td></tr>' ;
				 endif ;
			?>		
			</tbody>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pageNav->getListFooter() ; ?>
					</td>
				</tr>
			</tfoot>
		</table>	
	</div>

	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cid[]" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="quizzes" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>