<?php 
    defined('_JEXEC') or die('Restricted access'); 
?>

<script language="javascript" type="text/javascript">
			

    function submitbutton(pressbutton) 
	{
		if (pressbutton == 'exportCSV') 
		{
//			submitform( pressbutton );
//			return;
                        document.adminForm.task.value = 'exportCSV' ;
                        document.adminForm.submit() ;
		}

		if (pressbutton == 'clearUnfinished') 
		{
//			submitform(pressbutton) ;
//			return ;
                        document.adminForm.task.value = 'clearUnfinished' ;
                        document.adminForm.submit() ;
		}

        if (pressbutton == 'search') 
        {
            submitform(pressbutton) ;
            return ;
        }
		
		if (pressbutton == 'remove') 
		{
			submitform(pressbutton) ;
			return ;
		}
	}

	function resetFilters() 
	{
            document.getElementById('filter_quiz').value='0'; 
            document.getElementById('filter_user').value='0'; 
            document.getElementById('filter_unfinished').value='0'; 
            document.getElementById('filter_profile').value='0'; 
            document.adminForm.submit();
		return ;
	}

</script>
		
<form action="index.php" method="post" name="adminForm" id="adminForm">	
	<div class="width-100 fltlft">
                <fieldset class="adminfrom" style="margin-left: 10px;">
			<legend><?php echo JText::_( 'ANSWERS' ); ?></legend>
			<table class="admintable" width="100%">
				<tr valign="top">
					<td >
					<table class="admintable">
					<tr>
						<td width="250" align="left" class="key">
							<label for="unfinished">
								<?php echo JText::_('CLEAR_UNFINISHED_SESSIONS') ; ?> :
							</label>
						</td>
						<td>
                                                    <button onclick="submitbutton('clearUnfinished')"><?php echo JText::_( 'CLEANING' ); ?></button>
						</td>
					</tr>
					<tr>
						<td width="250" align="left" class="key">
							<?php echo JText::_( 'EXPORT_CSV' ) ; ?> :	
						</td>
						<td>
                                                    <button style="margin-bottom: 14px;" onclick="submitbutton('exportCSV')"><?php echo JText::_( 'EXPORT_CSV' ); ?></button>
						</td>
					</tr>
					</table>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset class="adminform">	
			<legend><?php echo JText::_('SESSIONS') ; ?></legend>

			<table>
				<tr>
					<td align="left" width="100%">
                                                <label><?php   echo JText::_( 'FILTER' );  ?>:</label>
						<?php
							echo $this->lists['quiz'] ;
							echo " " ;
							echo $this->lists['user'] ;
							echo " " ;
							echo $this->lists['profiles'];
							echo " " ;
							echo $this->lists['unfinished']; 
						?>
                                                <button onclick="resetFilters();" style="margin-left: 16px; margin-top: 5px;"><?php echo JText::_( 'RESET_FILTER' ); ?></button>
					</td>
				</tr>
			</table>
			<table class="adminlist" id="modules-mgr">
			<thead>
				<tr>
					<th width="20">
							<?php echo JText::_( 'NUM' ); ?>
					</th>			
					<?php if ($this->user->usertype == "Super Administrator" ) : ?>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->sessions ); ?>);" />
						</th>
					<?php endif ; ?>
					<th>
						<?php echo JText::_( 'DETAILS' ); ?>
					</th>	
					<th>
						<?php echo JText::_( 'QUIZ' ); ?>
					</th>
					<th>
						<?php echo JText::_( 'USER' ); ?>
					</th>	
					<th>
					    <?php echo JText::_( 'PROFILES' ); ?>
					</th>	
					<th>
						<?php echo JHTML::_( 'grid.sort', 'SCORE', 'score', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHTML::_( 'grid.sort', 'MAX_SCORE', 'maxScore', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHTML::_( 'grid.sort', 'IS_UNANSWERED', 'unanswered', $this->lists['order_Dir'], $this->lists['order']); ?>						
					</th>
					<th>
						<?php echo JHTML::_( 'grid.sort', 'TO_CORRECT', 'evaluate', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>						
						<?php echo JHTML::_( 'grid.sort', 'SPENT_TIME', 'spent_time', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>						
						<?php echo JHTML::_( 'grid.sort', 'STARTED_ON', 'started_on', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>						
						<?php echo JHTML::_( 'grid.sort', 'FINISHED_ON', 'finished_on', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JText::_('IP_ADDRESS') ; ?>
					</th>
					<th width="5">
						<?php echo JHTML::_( 'grid.sort', 'ID', 'id', $this->lists['order_Dir'], $this->lists['order'] );  ?>
					</th>
				</tr>
			</thead>	
			<tbody>
			<?php 
				if (count( $this->sessions )) :	
					$k = 0;
					for ($i = 0, $n = count( $this->sessions ) ; $i < $n ; $i++) :
						$row =& $this->sessions[$i];
						$checked = JHTML::_( 'grid.id', $i, $row->id );
						$link = JRoute::_( 'index.php?option=com_jquarks&view=session&cid[]='. $row->id );
						
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td align="center">
								<?php echo $i+1; ?>
							</td>	
							<?php if ($this->user->usertype == "Super Administrator" ) : ?>
								<td align="center">
									<?php echo $checked; ?>
								</td>	
							<?php endif ; ?>
							<td>
                                                            <a href="<?php echo $link ; ?>"><img src="templates/hathor/images/menu/icon-16-search.png" title="<?php echo JText::_('SEE_DETAILS') ; ?>" alt="<?php echo JText::_('SEE_DETAILS') ; ?>" style="margin-left: 9px;"/></a>	
							</td>
							<td>
								<?php if ($row->title == "") :
										echo JText::_("QUIZ_DELETED") ;
									  else : 
									  	echo $row->title ;
									  endif ;
								?>
							</td>
							<td>
								<?php 
								  	if ($row->givenname) :
								  		echo $row->givenname . " " . $row->familyname . "<br />$row->email" ;
								  	else : 
								  		echo JText::_('GUEST') ;
								  	endif ;
								?>
							</td>
                            <td>
                                <?php echo str_replace(',', '<br />', $row->profile) ; ?>
                            </td>							
							<td>
								<?php echo $row->score ; ?>
							</td>
							<td>
								<?php echo $row->maxScore ; ?>
							</td>
							<td>
								<?php echo $row->unanswered ; ?>
							</td>
							<td>
								<?php echo $row->evaluate ; ?>
							</td>
							<td>
								<?php echo $row->spent_time ; ?>
							</td>
							<td>
								<?php echo $row->started_on ; ?>
							</td>
							<td>
								<?php echo $row->finished_on == "0000-00-00 00:00:00" ? JText::_("UNFINISHED") : $row->finished_on ; ?>
							</td>
							<td>
								<?php echo $row->ip_address ; ?>
							</td>
							<td>
								<?php echo $row->id; ?>				
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					endfor ; 
				else :
					if ($this->user->usertype == "Super Administrator" ) {
						echo '<tr><td colspan="15">' . JText::_('THERE_ARE_NO_SESSIONS') . '<br /><br />' . JText::_('MIN_TUTO_SESSIONS') . '</td></tr>' ;
					} else {
						echo '<tr><td colspan="14">' . JText::_('THERE_ARE_NO_SESSIONS') . '<br /><br />' . JText::_('MIN_TUTO_SESSIONS') . '</td></tr>' ;
					}
				endif ;
			?>		
			</tbody>
			<tfoot>
				<tr>
				<?php if ($this->user->usertype == "Super Administrator" ) : ?>
					<td colspan="15">
				<?php else : ?>
					<td colspan="14">
				<?php endif ; ?>
						<?php echo $this->pageNav->getListFooter() ; ?>
					</td>
				</tr>
			</tfoot>
			</table>
		</fieldset>
	</div>

	<div class="clr"></div>
 
	<input type="hidden" name="option" value="com_jquarks"/>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="sessions" />
	<input type="hidden" name="view" value="sessions" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?> 
</form>