<?php 
    defined('_JEXEC') or die('Restricted access'); 

    $mainframe =& JFactory::getApplication();
    $context   = 'com_jquarks.quiz.sets.list.' ;
    $search    = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
    $search    = JString::strtolower($search) ;
?>

<script language="javascript" type="text/javascript">

	function submitAssignSet(id) 
	{
		document.adminForm.setId.value = id ;
		document.adminForm.task.value = 'assignSet' ;
		
		document.adminForm.submit() ;
	}

	function submitUnassignSet(id) 
	{
		document.adminForm.setId.value = id ;
		document.adminForm.task.value = 'unassignSet' ;
		
		document.adminForm.submit() ;
	}

	function resetFilters() 
	{
		$('search').value=''; 
		$('filter_assigned_sets').value=''; 
		$('filter_setType').value=''; 
		document.adminForm.submit();
		return true;
	}

</script>
	
<form action="index.php" method="post" name="adminForm" id="adminForm">	
	<div class="col100"> 	
		<fieldset class="adminform">	
			<legend><?php echo JText::_('SETS_OF_QUESTIONS') ; ?></legend>
			<table>
			<tr>
				<td align="left" width="100%">
					<?php echo JText::_( 'FILTER' );  ?>:
					<input type="text" name="search" id="search" value="<?php echo $search ; ?>" onclick="this.value='';" class="text_area"  />
					<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>				
					<button onclick="resetFilters()"><?php echo JText::_( 'RESET_FILTER' ); ?></button>
				</td>	
				<td>
					<?php echo $this->lists['setType'] ;?>
				</td>		
				<td>
					<?php echo $this->lists['statusSets'] ;?>
				</td>
			</tr>
			</table>				
				
			<table class="adminlist">
				<thead>
					<tr>
						<th width="20">
							<?php echo JText::_( 'NUM' ); ?>
						</th>			
						<th>
							<?php echo JText::_( 'TITLE' ); ?>
						</th>
						<th>
							<?php echo JText::_( 'ASSOCIATED_QUESTIONS'); ?>
						</th>
						<th>
							<?php echo JText::_('SET_TYPE') ; ?>
						</th>
						<th width="10%">
							<?php echo JText::_('ASSIGNED') ; ?>
						</th>	
						<th width="5">
							<?php echo JHTML::_( 'grid.sort', 'ID', 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>								
					</tr>
				</thead>	
				<tbody>
				<?php 
				$k = 0;
				for ($i = 0, $n = count( $this->setsOfQuestions ) ; $i < $n ; $i++) :
					$row =& $this->setsOfQuestions[$i];
					$link = JRoute::_( 'index.php?option=com_jquarks&controller=setofquestions&task=edit&cid[]='. $row->id );
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
							<?php echo $i+1; ?>
						</td>			
						<td>
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'TITLE' );?> : <?php echo $row->title; ?>">
								<a href="<?php echo JRoute::_( $link ); ?>"><?php echo $row->title; ?></a>
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
								if ( $row->type == 'random' ) :
									echo JText::_('RANDOM') ;
								else :
								  	echo JText::_('CUSTOM') ;
								endif ;
							?>
						</td>
						<td align=center> 
							<?php if ($row->assigned) : ?>
                                                        <a class="icon-16-allow" href="javascript:void(0)" onclick="submitUnassignSet(<?php echo $row->id ; ?>);" ></a>	
							<?php else : ?>
                                                        <a class="icon-16-deny" href="javascript:void(0)" onclick="submitAssignSet(<?php echo $row->id ; ?>);" ></a>
							<?php endif ; ?>
						</td>	
						<td align=center>
							<?php echo $row->id ; ?>
						</td>				
					</tr>
					<?php
					$k = 1 - $k;
				endfor ; 
				?>		
				</tbody>
				<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $this->pageNavSets->getListFooter() ; ?>
						</td>
					</tr>
				</tfoot>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>
	 
	<input type="hidden" name="option" value="com_jquarks"/>
	<input type="hidden" name="cid[]" value="<?php echo $this->quiz->id; ?>" />
	<input type="hidden" name="setId" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="quiz" />
	<input type="hidden" name="layout" value="assignSets" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>