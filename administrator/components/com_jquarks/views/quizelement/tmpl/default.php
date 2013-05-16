<form action="index.php" method="post" name="adminForm">
    <div id="editcell">
        <table class="adminlist">
            <thead>
                <tr>
                    <th width="20">
                        <?php echo JText::_( 'NUM' ); ?>
                    </th>
                    <th width="20%">
                        <?php echo JText::_('TITLE');  ?>
                    </th>           
                    <th>
                        <?php echo JText::_('DESCRIPTION');  ?>
                    </th>
                    <th width="5%">
                        <?php echo JText::_('PUBLISHED');  ?>
                    </th>
                    <th width="5%">
                        <?php echo JText::_('ACCESS');  ?>
                    </th>
                    <th width="5">
                        <?php echo JText::_('ID');  ?>           
                    </th>
                </tr>
            </thead>    
            <tbody>
            <?php 
                if (count( $this->quizzes )) :
                    $k = 0;
                    for ($i = 0, $n = count( $this->quizzes ) ; $i < $n ; $i++) :
                        $row =& $this->quizzes[$i];
                        ?>
                        <tr class="<?php echo "row$k"; ?>">
                            <td align="center">
                                <?php echo $i+1; ?>
                            </td>
                            <td>
                                <a style="cursor: pointer;" onclick="window.parent.jSelectQuiz('<?php echo $row->id; ?>', '<?php echo $row->title ; ?>', '<?php echo JRequest::getVar('object'); ?>');"><?php echo $row->title ; ?></a>
                            </td>           
                            <td>
                                <?php echo $row->description; ?>    
                            </td>
                            <td align="center">
                                <?php if ($row->published) : ?>
                                    <img src="images/publish_g.png"/>
                                <?php else : ?>
                                    <img src="images/publish_x.png"/>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    // to improve try using grid.access later   
                                    if ( !$row->access_id )  :
                                        $color_access = 'style="color: green;"';
                                    elseif ( $row->access_id == 1 ) :
                                        $color_access = 'style="color: red;"';
                                    else :
                                        $color_access = 'style="color: black;"';                                        
                                    endif ;                         
                                ?>
                                <span <?php echo $color_access ;?>><?php echo $row->groupname ;?></span>
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
            </tfoot>
        </table>    
    </div>

    <input type="hidden" name="option" value="com_jquarks" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="cid[]" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="quizzes" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>