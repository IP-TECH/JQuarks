<?php 
    defined('_JEXEC') or die('Restricted access');
    JHTML::_('behavior.tooltip');

    $context = 'com_jquarks.candidat.list.' ;
    $mainframe =& JFactory::getApplication();
    $search  = $mainframe->getUserStateFromRequest( $context.'search', 'search', '', 'string') ;
    $search  = JString::strtolower($search) ;
    
$url = "index.php?option=com_jquarks&controller=candidate" ;

$messages = <<<MESSAGES

    function clearMessages()
    {
        // remove joomla messages
        statusMessage = document.getElementsByTagName('dl') ;
        if (statusMessage.length == 2)
        {
            JoomlaStatusMessage = statusMessage[0] ;
            JoomlaStatusMessage.parentNode.removeChild(JoomlaStatusMessage) ; 
        }
        
        // clear JQuarks messages
        messageDiv = document.getElementById('message') ;
        messageDiv.style.display = "none" ;

        successList = document.getElementById('successList') ;
        successList.innerHTML = '' ;

        var errorList = document.getElementById('errorList') ;
        errorList.innerHTML = '' ;
    }

    function addError(error)
    {
        messageDiv = $('message') ;
        messageDiv.style.display = "block" ;
    
        var errorList = document.getElementById('errorList') ;
        
        var errorUL = document.createElement("UL") ;
        var errorLI = document.createElement("LI") ; 

        errorLI.innerHTML = error.message ; 
        errorUL.appendChild(errorLI) ;
    
        errorList.appendChild(errorUL) ;
    }
    
    function addSuccess(success)
    {
        messageDiv = $('message') ;
        messageDiv.style.display = "block" ;

        successList = document.getElementById('successList') ;

        var successUL = document.createElement("UL") ;
        var successLI = document.createElement("LI") ;
        successLI.innerHTML = success.message ;
        successUL.appendChild(successLI) ; 
        successList.appendChild(successUL) ;
    }

MESSAGES;


$ajax = <<<DOC
    
    var AJAX_QUEUE = [];

    Ajax = $.extend({
 
        initialize: function( url, options ) {
            this.parent( url, options );
            this.ready = false;
            this.output = '';
        },
     
        onComplete: function() {
            this.ready = true;
            this.parent();
        }
     
    });

    window.addEvent( 'domready', function() {
 
        quizzesAssign = $$('#assignTable tr') ;
        
        for ( i = 0 ; i < quizzesAssign.length ; i++)
        {
            quizImg = quizzesAssign[i].getElements('img') ;
            
            // assignation/unassignation event
            
            quizImg[0].addEvent('click', function() {
                
                clearMessages() ;
            
                quizId = this.id.slice(10) ; // extracting the quizId from the image Id "quizAssignX"
	            
                img = new String(this.src) ; 

	            if ( img.search('components/com_jquarks/assets/images/tick.png') > 0 ) { // according to the img getting the task
                   task = "unassignFromQuiz" ;
	            } else {
	               task = "assignToQuiz" ;
	            }

	            notifImg = $('quizId' + quizId).getElements('img')[1]; // getting the notification image
	            
	            this.src="components/com_jquarks/assets/images/ajax-loader.gif" ; // setting the spinner on assignation image
	            notifImg.src="components/com_jquarks/assets/images/ajax-loader.gif" ; // setting spinner on user notification image
	            
	            var a = new Request({
                                url : '{$url}',
	                        method: 'get',
	                        data:{ 
	                           'quizId': quizId, 
	                           'userId': {$this->user->id},
	                           'task': task
	                        },
	                        onComplete: function( response ) {
	                            
	                            var resp = JSON.decode( response );
	                            
	                            while ( AJAX_QUEUE.length ) {
                                        
	                                // Pop the request from the queue.
	                                r = AJAX_QUEUE.shift();
	                         
	                                // Only output to document when queue is empty.
	                                if ( !AJAX_QUEUE.length ) 
	                                {
	                                   quiz = $('quizAssign' + this.options.data['quizId']) ; 
	                                   quizNotif = $('quizId' + quizId).getElements('img')[1];
	                                   
	                                    if ( resp.type == 'message' )  
		                                { 
		                                    addSuccess(resp) ;
		                                    
		                                    if (resp.task == "assignToQuiz") 
		                                    {
		                                        quiz.src = "components/com_jquarks/assets/images/tick.png" ;
		                                        if (resp.published) 
		                                        {
		                                          quizNotif.src = "components/com_jquarks/assets/images/nomail.png" ;
		                                          quizNotif.className = "notify" ;
		                                        } 
		                                        else 
		                                        {
		                                          quizNotif.src = "components/com_jquarks/assets/images/nomail_dis.png" ;
		                                          quizNotif.className = "" ;
		                                        }
		                                    }
		                                    else 
		                                    {
		                                        quiz.src = "templates/bluestork/images/admin/publish_x.png" ;
		                                        quizNotif.src = "components/com_jquarks/assets/images/nomail_dis.png" ;
		                                        quizNotif.className = "" ;
		                                    }
		                                    
		                                } 
		                                else 
		                                {
		                                    addError(resp) ;
		                                    
		                                    if (resp.task == "assignToQuiz") 
		                                    {
		                                        quiz.src = "templates/bluestork/images/admin/publish_x.png" ;
		                                        quizNotif.src = "components/com_jquarks/assets/images/nomail_dis.png" ;  
		                                        quizNotif.className = "" ;  
		                                    } 
		                                    else 
		                                    {
		                                        quiz.src = "components/com_jquarks/assets/images/tick.png" ;  
		                                        
		                                        if (resp.published) 
		                                        {
                                                  quizNotif.src = "components/com_jquarks/assets/images/nomail.png" ;
                                                  quizNotif.className = "notify" ;
                                                } 
                                                else 
                                                {
                                                  quizNotif.className = "" ;
                                                  quizNotif.src = "components/com_jquarks/assets/images/nomail_dis.png" ;
                                                }  
		                                    }
		                                }
	                                }
	                         
	                            }
	                            
	                        }
	                        
	            })
	            AJAX_QUEUE.push( a );
	            a.send();
	            
            });

            // e-mail notification event
            NotifImg = new String(quizImg[1].src) ;
            if (NotifImg.search('components/com_jquarks/assets/images/nomail.png') > 0)
            {
                quizImg[1].addEvent('click', function() {
                
                    clearMessages() ;
                
                    quizId = this.id.slice(9) ;
	                
                    task = "notifyUser" ;
		                
	                this.src="components/com_jquarks/assets/images/ajax-loader.gif" ; // setting the spinner on assignation image
		                
	                var a = new Request({
                                    url : '{$url}',
	                            method: 'get',
	                            data:{ 
	                               'quizId': quizId, 
	                               'userId': {$this->user->id},
	                               'task': task
	                            },
	                            onComplete: function( response ) {
	                               
	                                var resp = JSON.decode( response );
                                
	                                while ( AJAX_QUEUE.length ) {
	                             
	                                    // Pop the request from the queue.
	                                    r = AJAX_QUEUE.shift();
	                             
	                                    // Only output to document when queue is empty.
	                                    if ( !AJAX_QUEUE.length ) 
	                                    {
	                                        quizNotif = $('quizId' + quizId).getElements('img')[1];
	                                        quizNotif.src = "components/com_jquarks/assets/images/nomail_dis.png" ;
	                                        
	                                        if ( resp.type == 'message' ) {
	                                           addSuccess(resp) ;
	                                        } else {
	                                           addError(resp) ;
	                                        }//End if
	                                    }//End if
	                               }//End while
	                            }
	               });
                   AJAX_QUEUE.push( a );
                   a.send();
	           
	           });
	           
               
            }
            
         }
    })
    
    function resetFilters() 
    {
        document.getElementById('search').value=''; 
        return true;
    }
DOC;

    $doc = & JFactory::getDocument();
    $doc->addScriptDeclaration( $ajax );
    $doc->addScriptDeclaration( $messages ) ;
?>

<style>
    .assign { cursor: pointer;}
    .notify { cursor: pointer;}
</style>

<form action="index.php" method="post" name="adminForm">
    <div id="message" style="display:none;">
        <dl id="system-message">
            <dt class="notice">Notice</dt>
            <dd id="noticeList" class="notice message fade"></dd>

            <dt class="error">Error</dt>
            <dd id="errorList" class="error message fade"></dd>
            
            <dt class="message">Message</dt>
            <dd id="successList" class="message message fade"></dd>
        </dl>
    </div>
    <div id="editcell">
        <fieldset class="batch">
        <legend><?php echo JText::_( 'USER_INFORMATIONS' ) ; ?></legend>
            <table class="admintable" style="float:left; width:40%;">
                <tr>
                    <td width="70" align="left" class="key">
                        <?php echo JText::_('NAME') ; ?> : 
                    </td>
                    <td>
                        <?php echo $this->user->name ; ?>
                    </td>
                </tr>
                <tr>
                    <td width="70" align="left" class="key">
                        <?php echo JText::_('EMAIL') ;?> :
                    </td>
                    <td>
                        <?php echo $this->user->email ; ?>
                    </td>
                </tr>
            </table>
            
            <fieldset class="batch">
	        <legend><?php echo JText::_('PROFILES') ; ?></legend>
	            <?php foreach ( $this->userProfiles AS $profile) : ?>
                    <input type="checkbox" <?php if ($profile->assigned) echo 'checked' ?> name="profiles[<?php echo $profile->id ?>]" id="profiles[<?php echo $profile->id ?>]" /><label for="profiles[<?php echo $profile->id ?>]"><?php echo $profile->title ?></label>
	            <?php endforeach ; ?>                
	        </fieldset>
	        
        </fieldset>
        
        
        
        <div style="clear: both;"></div>
        
        <fieldset class="batch">
        <legend><?php echo JText::_( 'USER_AFFECTATION' ) ; ?></legend>
            <table>
                <tr>
                    <td width=100%>
                        <label style="width: 40px;"><?php echo JText::_( 'FILTER' );  ?>:</label>
                        <input type="text" name="search" id="search" value="<?php echo $search ; ?>" onclick="this.value=''" class="text_area"  />
                        <button onclick="submitform();"><?php echo JText::_( 'GO' ); ?></button>
                        <button onclick="resetFilters();"><?php echo JText::_( 'RESET_FILTER' ); ?></button>   
                    </td>
                    <td nowrap="nowrap">
                        
                    </td>
                    <td>
                        <?php //echo $this->lists['status'] ; ?>
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
                            <?php echo JHTML::_( 'grid.sort', 'QUIZ', 'title', $this->lists['order_Dir'], $this->lists['order'] );  ?>
                        </th>
                        <th style="width:100px ;">
                            <?php echo JText::_( 'USERS_ASSIGNATION' ) ; ?>
                        </th>
                        <th style="width:100px ;">
                            <?php echo JText::_( 'NOTIFY_USER' ) ; ?>
                        </th>
                         <th width="20">
                            <?php echo JText::_( 'ID' ); ?>
                        </th>
                    </tr>
                </thead>
                <tbody id="assignTable">
                    <?php for ($i = 0 ; $i < count($this->userQuizzes) ; $i++) : 
                            $quiz = $this->userQuizzes[$i] ;
                            $link = JRoute::_( 'index.php?option=com_jquarks&view=quiz&task=edit&cid[]='. $quiz->id );  
                    ?>
                    <tr <?php echo "class=row" . ($i%2) ; ?> id="quizId<?php echo $quiz->id ; ?>" >
                        <td>
                            <?php echo ($i+1) ; ?>
                        </td>
                        <td>
                            <a href="<?php echo $link ; ?>" title="<?php echo $quiz->title ; ?>"><?php echo $quiz->title ; ?></a>
                        </td>   
                        <?php if ( $quiz->private ) : ?>
	                        <td align="center">
                                <?php if ( $quiz->assigned && !$quiz->archived ) : ?>
                                    <img class="assign" id="quizAssign<?php echo $quiz->id ; ?>" src="components/com_jquarks/assets/images/tick.png" alt="<?php echo JText::_('UNASSIGN_USER') ; ?>" title="<?php echo JText::_('UNASSIGN_USER') ; ?>" style="margin-left: 40px;"/>
                                <?php else : ?>
                                    <img class="assign" id="quizAssign<?php echo $quiz->id ; ?>" src="templates/bluestork/images/admin/publish_x.png" alt="<?php echo JText::_('ASSIGN_USER') ; ?>" title="<?php echo JText::_('ASSIGN_USER') ; ?>" style="margin-left: 40px;"/>
                                <?php endif ; ?>    
                            </td>
                            <td align="center">
                                <?php if ( $quiz->assigned && !$quiz->archived && $quiz->published ) : ?>
                                    <img class="notify" id="quizNotif<?php echo $quiz->id ; ?>" src="components/com_jquarks/assets/images/nomail.png" alt="<?php echo JText::_('NOTIFY_USER') ; ?>" title="<?php echo JText::_('NOTIFY_USER') ; ?>" style="margin-left: 40px;" />
                                <?php else : ?>
                                    <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTIFICATION_DISABLED' );?>::<?php echo JText::_("QUIZ_MUST_BE_PUBLISHED_AND_USER_ASSIGNED_TO_NOTIFY_USER"); ?>">
                                        <img id="quizNotif<?php echo $quiz->id ; ?>" src="components/com_jquarks/assets/images/nomail_dis.png" alt="<?php echo JText::_('NOTIFY_USER') ; ?> "style="margin-left: 38px;" />
                                    </span> 
                                <?php endif ; ?>
                            </td>	                        
                        <?php else :?>
                            <td align="center">
                                <span class="editlinktip hasTip" title="<?php echo JText::_( 'ASSIGNATION_DISABLED' );?>::<?php echo JText::_("ASSIGNATION_DISABLED_FOR_PUBLIC_QUIZZES"); ?>">
                                    <img src="components/com_jquarks/assets/images/tick_dis.png" alt="<?php echo JText::_('PUBLIC_ASSIGNATION') ; ?>" style="margin-left: 40px;" style="margin-left: 40px;" />
                                </span>
                            </td>
                            <td align="center">
                                <?php if ($quiz->published) : ?>
                                    <img class="notify" id="quizNotif<?php echo $quiz->id ; ?>" src="components/com_jquarks/assets/images/nomail.png" alt="<?php echo JText::_('NOTIFY_USER') ; ?>" title="<?php echo JText::_('NOTIFY_USER') ; ?>" style="margin-left: 40px;"/>
	                            <?php else : ?>
	                                <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTIFICATION_DISABLED' );?>::<?php echo JText::_("QUIZ_MUST_BE_PUBLISHED_AND_USER_ASSIGNED_TO_NOTIFY_USER"); ?>">
                                        <img id="quizNotif<?php echo $quiz->id ; ?>" src="components/com_jquarks/assets/images/nomail_dis.png" alt="<?php echo JText::_('NOTIFY_USER') ; ?>" style="margin-left: 40px;" />
                                    </span>
	                            <?php endif ;?>    
                            </td>
                        <?php endif ; ?>
                         <td>
                            <?php echo $quiz->id ; ?>
                        </td>
                    </tr>
                    <?php  endfor ; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <?php echo $this->pageNav->getListFooter() ; ?>
                        </td>
                    </tr>
                </tfoot>            
            </table>
        </fieldset>   
    </div>
    
    <input type="hidden" name="option" value="com_jquarks" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="cid[]" value="<?php echo $this->user->id ; ?>" />
    <input type="hidden" name="controller" value="candidates" />
    <input type="hidden" name="view" value="candidate" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>