<?php

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class ModJQuarksHelper
{

    /**
     * get the list of the public Quizzes
     * 
     * @return array
     */
    public function getPublicQuizzes($params)
    {
        $db = &JFactory::getDBO();

        $config = & JFactory::getConfig();
        $jnow = & JFactory::getDate();
        $jnow->setOffset($config->getValue('config.offset'));
        $now = $jnow->toMySQL(true);

        $query = 'SELECT qui.*' .
                ' FROM #__jquarks_quizzes AS qui' .
                ' WHERE qui.published = 1' .
                ' AND qui.access_id = 0' .
                ' AND qui.publish_up < \'' . $now . '\'' .
                ' AND ( qui.publish_down = "0000-00-00 00:00:00" OR qui.publish_down > "' . $now . '" )';

        $query = ModJQuarksHelper::getOrder($params, $query);

        $db->setQuery($query);

        $publicQuizzes = $db->loadObjectList();
        if ($db->getErrorNum())
        {
            return false;
        }

        return $publicQuizzes;
    }

    /**
     * Get the list of the private Quizzes for the current logged in user
     * 
     * @return array
     */
    public function getUserQuizzes($params)
    {
        $db = & JFactory::getDBO();
        $user = & JFactory::getUser();

        $config = & JFactory::getConfig();
        $jnow = & JFactory::getDate();
        $jnow->setOffset($config->getValue('config.offset'));
        $now = $jnow->toMySQL(true);

        $query = 'SELECT qui.*' .
                ' FROM #__jquarks_quizzes AS qui' .
                '   LEFT JOIN #__jquarks_users_quizzes as users_quizzes ON qui.id = users_quizzes.quiz_id' .
                ' WHERE qui.published = 1' .
                '   AND qui.access_id = 1' .
                '   AND users_quizzes.user_id = ' . $user->id .
                '   AND users_quizzes.archived <> 1' .
                '   AND qui.publish_up < \'' . $now . '\'' .
                '   AND ( qui.publish_down > \'' . $now . '\' OR qui.publish_down = \'0000-00-00 00:00:00\' )';

        $query = ModJQuarksHelper::getOrder($params, $query);

        $db->setQuery($query);
        $userQuizzes = $db->loadObjectList();
        if ($db->getErrorNum())
        {
            return false;
        }

        return $userQuizzes;
    }

    /**
     * Add the order of display of quizzes to the query provided
     * 
     * @param $params
     * @param $query
     * @return string
     */
    private function getOrder($params, $query)
    {
        $order = $params->get('orderQuiz', 'title');
        switch ($order)
        {
            case 'title' :
                $query .= ' ORDER BY title ASC';
                break;

            case 'date' :
                $query .= ' ORDER BY id ASC';
                break;

            case 'dateReverse' :
                $query .= ' ORDER BY id DESC';
                break;
        }

        return $query;
    }

}
