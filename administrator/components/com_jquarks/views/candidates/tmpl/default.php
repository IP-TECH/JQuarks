<?php 
    defined('_JEXEC') or die('Restricted access'); 
    
    $mainframe =& JFactory::getApplication();
    $context = 'com_jquarks.candidates.list.' ;
	$search  = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
	$search  = JString::strtolower($search) ;
?>

<script language="javascript" type="text/javascript">

    function resetFilters() 
    {
        document.getElementById('search').value=''; 
        document.getElementById('filter_session_status').value = '0'; 
       // this.form.submit();
        return true;
    }

</script>

<form action="index.php" method="post" name="adminForm">
    <div id="editcell">
        <table>
            <tr>
                <td align="left" width="100%">
                    <?php echo JText::_( 'FILTER' );  ?>:
                    <input type="text" name="search" id="search" value="<?php echo $search ; ?>" onclick="this.value=''" class="text_area"  />
                    <button onclick="submitform();"><?php echo JText::_( 'Go' ); ?></button>
                    <button onclick="resetFilters();"><?php echo JText::_( 'RESET_FILTER' ); ?></button>              
                </td>
                <td nowrap="nowrap">
                    <?php echo $this->lists['status'] ; ?>
                </td>
            </tr>
        </table>
        <table class="adminlist">
        <thead>
            <tr>
                <th width="20">
                    <?php echo JText::_( 'NUM' ); ?>
                </th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->candidates ); ?>);" />
                </th>           
                <th>
                    <?php echo JHTML::_( 'grid.sort', 'NAME', 'NAME', $this->lists['order_Dir'], $this->lists['order'] );  ?>
                </th>
                <th>
                    <?php echo JText::_( 'PROFILES' ); ?>
                </th>               
                <th>
                    <?php echo JText::_( 'QUIZZES' ); ?>
                </th>
                <th>
                    <?php echo JText::_( 'FINISHED' ); ?>
                </th>
            </tr>
        </thead>    
        <tbody>
        <?php 
            if ( count($this->candidates) ) :   
                $k = 0;
                for ($i = 0, $n = count( $this->candidates ) ; $i < $n ; $i++) :
                    $candidate =& $this->candidates[$i];
                    $checked = JHTML::_( 'grid.id', $i, $candidate->id );
                    $link = JRoute::_( 'index.php?option=com_jquarks&view=candidate&task=edit&cid[]='. $candidate->id );
                    
                    ?>
                    <tr class="<?php echo "candidate$k"; ?>">
                        <td align="center">
                                <?php echo $i+1; ?>
                        </td>
                        <td>
                            <?php echo $checked; ?>
                        </td>
                        <td>
                            <a href="<?php echo $link ; ?>"><?php echo $candidate->name; ?></a> 
                        </td>
                        <td>
                            <?php  
                                foreach ($candidate->profiles AS $profile) {
                                    echo $profile . " " ;
                                }
                            ?>
                        </td>
                        <td>
                            <?php 
                                foreach ($candidate->quizzes AS $quiz)
                                {
                                	$quizLink = JRoute::_( 'index.php?option=com_jquarks&view=quiz&cid[]='. $quiz->id );
                                	if ($quiz->session_id && $quiz->published) 
                                	{
                                		echo '<a href="'.$quizLink.'" style="color:green">'.$quiz->title . "</a><br />";
                                	}
                                	else 
                                	{
                                		echo '<a href="'.$quizLink.'" style="color:red">'.$quiz->title . "</a><br />";
                                	}
                                    
                                }
                            ?>                
                        </td>
                        <td>
                            <?php 
                                foreach ($candidate->quizzes AS $quiz)
                                {
                                	if ($quiz->title)
                                	{
	                                    if (!$quiz->started_on) {
	                                        echo JText::_('NOT_PASSED') . "<br />" ;
	                                    } 
	                                    else
	                                    { 
	                                        if ($quiz->finished_on == '0000-00-00 00:00:00') {
	                                            echo JText::_('UNFINISHED') . "<br />" ;
	                                        } 
	                                        else 
	                                        {
	                                        	$session = JRoute::_( 'index.php?option=com_jquarks&view=session&cid[]='. $quiz->session_id );
		                                    	echo "<a href=" . $session . ">" . $quiz->finished_on . "</a><br />" ;
		                                    }
	                                    }
                                	}
                                }
                            ?>                
                        </td>
                    </tr>
                    <?php
                    $k = 1 - $k;
                endfor ; 
            else :
                echo '<tr><td colspan="7">' . JText::_('THERE_ARE_NO_CANDIDATES') . '<br /><br />' . JText::_('MIN_TUTO_CANDIDATES') . '</td></tr>' ;
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
    <input type="hidden" name="controller" value="candidates" />
    <input type="hidden" name="view" value="candidates" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>