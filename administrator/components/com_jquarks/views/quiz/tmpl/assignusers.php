<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('behavior.tooltip');
	
        $mainframe =& JFactory::getApplication();
    $context   = 'com_jquarks.quiz.users.list.' ;
    $search    = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
    $search    = JString::strtolower($search) ;
?>

<script language="javascript" type="text/javascript">

	function insertValue(element) 
	{
	    var notifMess = document.getElementById('notifMess') ;
	    element = "["+element+"]" ;
	    
	    var scrollPos = notifMess.scrollTop;  
	    var strPos = 0; 
	    var br = ((notifMess.selectionStart || notifMess.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false ) ); 
	
	    if (br == "ie") 
	    { 
	        notifMess.focus() ;
	        var range = document.selection.createRange(); 
	        range.text= element ;           
	    } 
	    else if (br == "ff")
	    {
	        strPos = notifMess.selectionStart;
	        var front = (notifMess.value).substring(0,strPos); 
	        var back = (notifMess.value).substring(strPos,notifMess.value.length);  
	        notifMess.value=front+element+back; 
	        strPos = strPos + element.length; 

	        notifMess.focus();
	        notifMess.selectionStart = strPos; 
            notifMess.selectionEnd = strPos;

            notifMess.scrollTop = scrollPos;
	    }
	    
	}

	function submitbutton(pressbutton) 
	{
		var form = document.adminForm;
		
		if (pressbutton == 'cancel') 
		{
//			submitform( pressbutton );
//			return ;
                        document.adminForm.task.value = pressbutton ;
                        document.adminForm.submit() ;
		}

		if (pressbutton == 'assignUsers') {
//			submitform(pressbutton) ;
                        document.adminForm.task.value = pressbutton ;
                        document.adminForm.submit() ;
		}

		if (pressbutton == 'unassignUsers' ) {
//			submitform(pressbutton) ;
                        document.adminForm.task.value = pressbutton ;
                        document.adminForm.submit() ;
		}

		if ( pressbutton == 'notifyUsers' && $('published').value == 1)
		{
			var confirmed = confirm("NOTIFY_ALL_AFFECTED_USERS");
			if (confirmed) {
//				submitform(pressbutton) ;
                                document.adminForm.task.value = pressbutton ;
                                document.adminForm.submit() ;
			}
		} else if (pressbutton == 'notifyUsers' && $('published').value == 0) {
			alert("THIS_QUIZ_IS_NOT_YET_PUBLISHED");
		}

		if (pressbutton == 'assignUser') {
//                    submitform(pressbutton) ;
                    document.adminForm.task.value = pressbutton ;
                    document.adminForm.submit() ;
                }

                if (pressbutton == 'unassignUser' ) {
                    submitform(pressbutton) ;
                }

                if ( pressbutton == 'notifyUser' ) {
//                    submitform(pressbutton) ;
                    document.adminForm.task.value = pressbutton ;
                    document.adminForm.submit() ;
                }

                if ( pressbutton == 'save') {
//                        submitform('saveNotif') ;
                    document.adminForm.task.value = 'saveNotif' ;
                    document.adminForm.submit() ;
                }

                if ( pressbutton == 'apply') {
//                        submitform('applyNotif') ;
                    document.adminForm.task.value = 'applyNotif' ;
                    document.adminForm.submit() ;
                }
	}

	function resetFilters() 
    {  
		$('search').value=''; 
		$('filter_assigned_users').value='';
        document.adminForm.submit();
        return true;
    }
</script>

<style>
    .right {text-align: right;}
</style>

<form action="index.php" method="post" name="adminForm" id="adminForm">	
	<div class="width-100 fltlft">	
            <div class="width-50 fltlft">	
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'USER_AFFECTATION') ; ?></legend>
			<table>
				<tr>
					<td width=100%>
                                                <label><?php echo JText::_( 'FILTER' );  ?>:</label>
						<input type="text" name="search" id="search" value="<?php echo $search ; ?>" onclick="this.value=''" class="text_area"  />
						<button onclick="submitform();"><?php echo JText::_( 'GO' ); ?></button>
						<button onclick="resetFilters();"><?php echo JText::_( 'RESET_FILTER' ); ?></button>				
					</td>
					<td nowrap="nowrap">
						<?php echo $this->lists['statusUsers'] ; ?>
					</td>
				</tr>
			</table>
			
			<table class="adminlist">
				<thead>
					<tr>
                                            <th width="20" style="margin-left: 5px;">
                                                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->assignedUsers ); ?>);" />
                                            </th>
						<th>
							<?php echo JHTML::_( 'grid.sort', 'QUIZ', 'title', $this->lists['order_Dir'], $this->lists['order'] );  ?>
						</th>
						<th style="width:100px ;">
							<?php echo JText::_( 'USERS_ASSIGNATION' ) ; ?>
						</th>
						<th style="width:100px ;">
							<?php echo JText::_( 'NOTIFY_USER' ) ; ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php for ($i = 0 ; $i < count($this->assignedUsers) ; $i++) : 
					        $user = $this->assignedUsers[$i] ;
                                                $link = JRoute::_( 'index.php?option=com_users&task=edit&cid[]='. $user->id );
                                                $checked = JHTML::_( 'grid.id', $i, $user->id, false, 'uid' );
					?>
					<tr <?php echo "class=row" . ($i%2) ;?> >
                                            <td><?php echo $checked; ?></td>
						<td>
							<a href="<?php echo $link ; ?>" title="<?php echo $user->name ; ?>"><?php echo $user->name ; ?></a>
						</td>	
						<td align="center">
							<?php if ( $user->assigned_id && !$user->archived  ) : ?>
                                                                <a href="javascript:void(0)" onclick="$('selected_user').value='<?php echo $user->id ;?>'; submitbutton('unassignUser');"><img src="templates/bluestork/images/admin/tick.png" alt="<?php echo JText::_('UNASSIGN_USER') ; ?>" title="<?php echo JText::_('UNASSIGN_USER') ; ?>" style="margin-left: 38px;"/></a>
							<?php else : ?>
								<a href="javascript:void(0)" onclick="$('selected_user').value='<?php echo $user->id ;?>'; submitbutton('assignUser');"><img src="templates/bluestork/images/admin/publish_r.png" alt="<?php echo JText::_('ASSIGN_USER') ; ?>" title="<?php echo JText::_('ASSIGN_USER') ; ?>" style="margin-left: 38px;"/></a>
							<?php endif ; ?>	
						</td>
						<td align="center">
							<?php if ( $this->quiz->published && $user->assigned_id && !$user->archived) : ?>
                                                                <a href="javascript:void(0)" onclick="$('selected_user').value='<?php echo $user->id ;?>'; submitbutton('notifyUser');"><img src="components/com_jquarks/assets/images/nomail.png" alt="<?php echo JText::_('NOTIFY_USER') ; ?>" title="<?php echo JText::_('NOTIFY_USER') ; ?>" style="margin-left: 38px;" /></a>
							<?php else : ?>
								<span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTIFICATION_DISABLED' );?>::<?php echo JText::_("QUIZ_MUST_BE_PUBLISHED_TO_NOTIFY_USERS"); ?>">
									<img src="components/com_jquarks/assets/images/nomail_dis.png" alt="<?php echo JText::_('NOTIFY_USER') ; ?>" style="margin-left: 38px;" />
								</span>	
							<?php endif ; ?>
						</td>
					</tr>
					<?php  endfor ; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4">
							<?php echo $this->pageNavUsers->getListFooter() ; ?>
						</td>
					</tr>
				</tfoot>			
			</table>
		</fieldset>
            </div>
            <div class="width-50 fltlft">	
		<fieldset>
			<legend><?php echo JText::_("NOTIFICATION_MESSAGE") ; ?></legend>
			<input type="button" value="<?php echo JText::_("USER_NAME") ; ?>" onclick="insertValue('userName')" />
			<input type="button" value="<?php echo JText::_("QUIZ_TITLE") ; ?>" onclick="insertValue('quizTitle')" />
			<input type="button" value="<?php echo JText::_("QUIZ_DESCRIPTION") ; ?>" onclick="insertValue('quizDescription')" />
			<input type="button" value="<?php echo JText::_("UNPUBLICATION_DATE") ; ?>" onclick="insertValue('unpublishDate')" />
			<input type="button" value="<?php echo JText::_("QUIZ_LINK") ; ?>" onclick="insertValue('quizLink')" />
			<br />
			<br />
			<textarea cols="75" rows="15" name="notifMess" id="notifMess"><?php echo $this->quiz->notify_message ;?></textarea>
		</fieldset>
            </div>
		<div style="clear:both;"></div>
					
	</div>
	<div class="clr"></div>

        <input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_jquarks"/>
	<input type="hidden" name="cid[]" value="<?php echo $this->quiz->id; ?>" />
	<input type="hidden" name="published" id="published" value="<?php echo $this->quiz->published ; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="selected_user" id="selected_user" value="" />
	<input type="hidden" name="controller" value="quiz" />
	<input type="hidden" name="layout" value="assignUsers" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>	