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
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->profiles ); ?>);" />
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
            if ( count($this->profiles) ) :   
                $k = 0;
                for ($i = 0, $n = count( $this->profiles ) ; $i < $n ; $i++) :
                    $profile =& $this->profiles[$i];
                    $checked = JHTML::_( 'grid.id', $i, $profile->id );
                    $link = JRoute::_( 'index.php?option=com_jquarks&view=profile&task=edit&cid[]='. $profile->id );
                    
                    ?>
                    <tr class="<?php echo "profile$k"; ?>">
                        <td align="center">
                                <?php echo $i+1; ?>
                        </td>
                        <td>
                            <?php echo $checked; ?>
                        </td>
                        <td>
                            <a href="<?php echo $link ; ?>"><?php echo $profile->title; ?></a> 
                        </td>
                        <td>
                            <?php echo $profile->description ; ?>
                        </td>
                        <td>
                            <?php echo $profile->id; ?>                
                        </td>
                    </tr>
                    <?php
                    $k = 1 - $k;
                endfor ; 
            else :
                echo '<tr><td colspan="5">' . JText::_('THERE_ARE_NO_PROFILES') . '<br /><br />' . JText::_('MIN_TUTO_PROFILES') . '</td></tr>' ;
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
    <input type="hidden" name="controller" value="profiles" />
    <input type="hidden" name="view" value="profiles" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>