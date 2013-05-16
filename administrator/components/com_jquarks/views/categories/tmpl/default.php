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
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->categories ); ?>);" />
				</th>			
				<th>
					<?php echo JHTML::_( 'grid.sort', 'TITLE', 'TITLE', $this->lists['order_Dir'], $this->lists['order'] );  ?>
				</th>
				<th>
					<?php echo JText::_( 'DESCRIPTION' ); ?>
				</th>				
				<th width="5">
					<?php echo JHTML::_( 'grid.sort', 'ID', 'id', $this->lists['order_Dir'], $this->lists['order'] );  ?>
				</th>
			</tr>
		</thead>	
		<tbody>
		<?php 
			if ( count($this->categories) ) :	
				$k = 0;
				for ($i = 0, $n = count( $this->categories ) ; $i < $n ; $i++) :
					$category =& $this->categories[$i];
					$checked = JHTML::_( 'grid.id', $i, $category->id );
					$link = JRoute::_( 'index.php?option=com_jquarks&view=category&cid[]='. $category->id );
					
					?>
					<tr class="<?php echo "category$k"; ?>">
						<td align="center">
								<?php echo $i+1; ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td>
							<a href="<?php echo $link ; ?>"><?php echo $category->title; ?></a>	
						</td>
						<td>
							<?php echo $category->description ; ?>
						</td>
						<td>
							<?php echo $category->id; ?>				
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				endfor ; 
			else :
				echo '<tr><td colspan="5">' . JText::_('THERE_ARE_NO_CATEGORIES') . '<br /><br />' . JText::_('MIN_TUTO_CATEGORIES') . '</td></tr>' ;
			endif ; 
		?>		
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pageNav->getListFooter() ; ?>
				</td>
			</tr>
		</tfoot>
		</table>	
	</div>
	
	<input type="hidden" name="option" value="com_jquarks" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="categories" />
	<input type="hidden" name="view" value="categories" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />	
	<?php echo JHTML::_( 'form.token' ); ?>
</form>