<?php 
    defined('_JEXEC') or die('Restricted access'); 

    $mainframe =& JFactory::getApplication();
    $context  = 'com_jquarks.questions.list.' ;
    $search   = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
    $search   = JString::strtolower($search) ;
?>

<script language="javascript" type="text/javascript">

	function resetFilters() 
	{
		document.getElementById('search').value=''; 
		document.getElementById('filter_category').value='-1'; 
		document.getElementById('filter_type').value='0'; 
		document.adminForm.submit();
		return ;
	}

</script>

<form action="index.php" method="post" name="adminForm">
	 
	<table>
	<tr>
		<td align="left" width="100%">
			<?php echo JText::_( 'FILTER' );  ?>:
			<input type="text" name="search" id="search" value="<?php echo $search ; ?>" onclick="this.value='';" class="text_area" />
			<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
			<button onclick="resetFilters()"><?php echo JText::_( 'RESET_FILTER' ); ?></button>
		</td>
		<td nowrap="nowrap">
			<?php
				echo $this->lists['category'];
				echo $this->lists['type'];
			?>
		</td>
	</tr>
	</table>
		
	<div id="editcell">
		<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
						<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->questions ); ?>);" />
				</th>				
				<th width="700">
					<?php echo JHTML::_( 'grid.sort', 'Description', 'Description', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>						
				<th>
					<?php echo JHTML::_( 'grid.sort', 'Category', 'category', $this->lists['order_Dir'], $this->lists['order'] );  ?>
				</th>
				<th>
					<?php echo JHTML::_( 'grid.sort', 'Type', 'type', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>
				<th width="5">
					<?php echo JHTML::_( 'grid.sort', 'ID', 'id', $this->lists['order_Dir'], $this->lists['order'] );  ?>				
				</th>
			</tr>
		</thead>	
		<tbody>
		<?php 
			if ( count($this->questions)) :
				$k = 0;
				for ($i = 0, $n = count( $this->questions ) ; $i < $n ; $i++) :
					$row =& $this->questions[$i];
					$checked = JHTML::_( 'grid.id', $i, $row->id );
					$link = JRoute::_( 'index.php?option=com_jquarks&view=question&task=edit&cid[]='. $row->id );
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
							<?php echo $i+1; ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>				
						<td>
                                                    <a href="<?php echo $link ; ?>"><?php echo $row->description ; ?></a>
						</td>
						<td>
							<?php if ($row->category) :
									echo $row->category ;
								  else :
								  	echo JText::_('UNCATEGORIZED') ;
								  endif ;		 
							?>
						</td>
						<td>
							<?php echo $row->type ; ?>
						</td>
						<td>
							<?php echo $row->id; ?>				
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				endfor ; 
			else :	
				echo '<tr><td colspan="6">' . JText::_('THERE_ARE_NO_QUESTIONS') . '<br /><br />' . JText::_('MIN_TUTO_QUESTIONS') . '</td></tr>' ;
			endif ; 
		?>		
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $this->pageNav->getListFooter() ; ?>
				</td>
			</tr>
		</tfoot>
		</table>	
	</div>
	
	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="questions" />
	<input type="hidden" name="view" value="questions" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php  echo JHTML::_( 'form.token' ); ?> 
</form>
