<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgUserJquarks extends JPlugin
{
    
    private $quizzesUrl = 'index.php?option=com_jquarks&view=quizzes';

    /**
     * 
     * @param object $subject The object to observe
     * @param array $config An array that holds the plugin configuration
     */
    public function __construct($subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Remove all affectation to quizzes for the deleted user
     *
     * Method is called after user data is deleted from the database
     *
     * @param 	array	  	holds the user data
     * @param	boolean		true if user was succesfully stored in the database
     * @param	string		message
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if ($success)
        {
            $db = &JFactory::getDBO();

            // getting the assignations for this user
            $query = 'SELECT id' .
                    ' FROM #__jquarks_users_quizzes' .
                    ' WHERE user_id = ' . $user['id'];

            $db->setQuery($query);
            $assignations = $db->loadResultArray();

            if (count($assignations))
            {
                foreach ($assignations AS $assignation)
                {
                    // getting the number of sessions
                    $query = 'SELECT count(id)' .
                            ' FROM #__jquarks_quizsession' .
                            ' WHERE affected_id = ' . $assignation;

                    $db->setQuery($query);
                    $db->getQuery();

                    if ($db->loadResult())
                    {
                        // there are session, so we archieve the assignations
                        $query = 'UPDATE #__jquarks_users_quizzes' .
                                ' SET archived = 1' .
                                ' WHERE id = ' . $assignation;

                        $db->setQuery($query);
                        $db->loadResult();
                    }
                    else
                    {
                        // no sessions are found we may delete the assignations
                        $query = 'DELETE FROM #__jquarks_users_quizzes' .
                                ' WHERE id = ' . $assignation;
                        $db->setQuery($query);
                        $db->loadResult();
                    }
                }
            }
        }

        return true;
    }

    /**
     * Add the newly created user to all public quizzes already existant
     *
     * Method is called after user data is deleted from the database
     *
     * @param 	array	  	holds the user data
     * @param   boolean		true if user is new
     * @param	boolean		true if user was succesfully stored in the database
     * @param	string		message
     */
    public function onUserAfterSave($user, $isnew, $success, $msg)
    {
        if ($isnew && $success)
        {
            // case of a new user to affect to public quizzes
            $db = &JFactory::getDBO();

            //getting all public quizzes
            $query = 'SELECT id' .
                    ' FROM #__jquarks_quizzes' .
                    ' WHERE access_id = 0';

            $db->setQuery($query);
            $publicQuizzes = $db->loadResultArray();

            if (count($publicQuizzes))
            {
                foreach ($publicQuizzes as $publicQuiz)
                {
                    $query = 'INSERT INTO' .
                            ' #__jquarks_users_quizzes' .
                            ' (`quiz_id`, `user_id`)' .
                            ' VALUES (' . $publicQuiz . ', ' . $user['id'] . ')';
                    $db->setQuery($query);
                    $db->loadResult();
                }
            }
        }

        return true;
    }

    /**
     * redirect logged in user to quizzes view
     * this function prevents unvalid URL after login
     *
     * @global JApplication $mainframe
     * @param  array $user
     * @param  array $options
     * @return boolean
     */
    public function onUserLogin($user, $options)
    {
        $mainframe = & JFactory::getApplication();

        $u = & JURI::getInstance();

        if ($u->getVar("option") == 'com_jquarks')
        {
            $mainframe->redirect($this->quizzesUrl);
        }
        
        return true;
    }

    /**
     * redirect logged out user to quizzes view
     * this function prevents unvalid URL after logout
     *
     * @global JApplication $mainframe
     * @param array $user
     * @return boolean
     */
    public function onUserLogout($user)
    {
        $mainframe = & JFactory::getApplication();

        $u = & JURI::getInstance();

        if ($u->getVar("option") == 'com_jquarks')
        {
            $mainframe->redirect($this->quizzesUrl);
        }
        
        return true;
    }

}
