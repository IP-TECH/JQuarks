
-- Creation of table #__jquarks_types which has the type of question permitted by our component
-- This table is read-only for our component 

CREATE TABLE IF NOT EXISTS `#__jquarks_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100),
  PRIMARY KEY (`id`)
)ENGINE=INNODB;

-- Insertion of the permitted types

INSERT INTO `#__jquarks_types` (`title`) VALUES
('input'),
('radio'),
('checkbox'),
('radio as checkbox');

-- ------------------------------------------------------------

-- Creation of table #__jquarks_categories which has the categories of question defined by the user

CREATE TABLE IF NOT EXISTS `#__jquarks_categories` (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(50) NOT NULL UNIQUE,
  description varchar(255),
  PRIMARY KEY (id)
)ENGINE=INNODB;

-- ------------------------------------------------------------

-- Creation of table #__jquarks_questions which has the questions created by the user

CREATE TABLE IF NOT EXISTS `#__jquarks_questions` (
  id int(11) NOT NULL AUTO_INCREMENT,
  statement text NOT NULL,
  category_id int,
  type_id int NOT NULL,
  archived boolean NOT NULL DEFAULT '0',
  PRIMARY KEY (id),  
  INDEX(category_id),
  FOREIGN KEY (category_id) REFERENCES `#__jquarks_categories`(id) ON DELETE SET NULL,
  INDEX(type_id),
  FOREIGN KEY (type_id) REFERENCES `#__jquarks_types`(id) ON DELETE CASCADE
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table #__jquarks_propositions which has the possible answers for the existants questions

CREATE TABLE IF NOT EXISTS `#__jquarks_propositions` (
  id int(11) NOT NULL AUTO_INCREMENT,
  answer varchar(250) NOT NULL,
  correct boolean,
  question_id int NOT NULL,
  archived boolean NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),  
  INDEX(question_id),
  FOREIGN KEY (question_id) REFERENCES `#__jquarks_questions`(id) ON DELETE CASCADE
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table #__jquarks_setsofquestions which has a collection of questions grouped by the user

CREATE TABLE IF NOT EXISTS `#__jquarks_setsofquestions` (
  id int(11) NOT NULL AUTO_INCREMENT,  
  title varchar(255) NOT NULL,
  type varchar(10) NOT NULL DEFAULT 'custom',
  PRIMARY KEY (id)
)ENGINE=INNODB;

-- ------------------------------------------------------------

-- Creation of table #__jquarks_customsets which contain the custom sets

CREATE TABLE IF NOT EXISTS `#__jquarks_customsets` (
  id int(11) NOT NULL AUTO_INCREMENT,
  set_id int,
  PRIMARY KEY (id),
  INDEX (set_id),
  FOREIGN KEY (set_id) REFERENCES `#__jquarks_setsofquestions`(id) ON DELETE CASCADE
)ENGINE=INNODB;

-- ------------------------------------------------------------

-- Creation of table #__jquarks_randomsets which contain the random sets

CREATE TABLE IF NOT EXISTS `#__jquarks_randomsets` (
  id int(11) NOT NULL AUTO_INCREMENT,
  set_id int,
  nquestions int(11) NOT NULL,
  category_id int DEFAULT NULL,
  PRIMARY KEY(id),
  INDEX (set_id),
  FOREIGN KEY (set_id) REFERENCES `#__jquarks_setsofquestions`(id) ON DELETE CASCADE,
  INDEX (category_id),
  FOREIGN KEY (category_id) REFERENCES `#__jquarks_categories`(id) ON DELETE CASCADE
)ENGINE=INNODB;

-- ------------------------------------------------------------

-- Creation of table setsofquestions_questions which has the questions assigned to each set

CREATE TABLE IF NOT EXISTS `#__jquarks_setsofquestions_questions` (
  id int(11) NOT NULL AUTO_INCREMENT,
  setofquestions_id int NOT NULL,
  question_id int NOT NULL,
  PRIMARY KEY (id),
  INDEX(setofquestions_id),
  FOREIGN KEY (setofquestions_id) REFERENCES `#__jquarks_customsets`(set_id) ON DELETE CASCADE,
  INDEX(question_id),
  FOREIGN KEY (question_id) REFERENCES `#__jquarks_questions`(id) ON DELETE CASCADE,
  CONSTRAINT uc_SoqQ UNIQUE (setofquestions_id, question_id)
)ENGINE=INNODB;

-- ------------------------------------------------------------

-- Creation of table #__jquarks_quizzes which has a collection of setsofquestions grouped by the user

CREATE TABLE IF NOT EXISTS `#__jquarks_quizzes` (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(100) NOT NULL,
  description varchar(255),
  published boolean NOT NULL,
  access_id int NOT NULL,
  time_limit int DEFAULT NULL,
  unique_session boolean NOT NULL DEFAULT 0, 
  paginate text, 
  publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  notify_message text NOT NULL,
  PRIMARY KEY (id)
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table quizzes_setsofquestions which has the sets of questions assigned to each quiz

CREATE TABLE IF NOT EXISTS `#__jquarks_quizzes_setsofquestions` (
  id int(11) NOT NULL AUTO_INCREMENT,
  quiz_id int NOT NULL,
  setofquestions_id int NOT NULL,
  PRIMARY KEY (id),
  INDEX(quiz_id),
  FOREIGN KEY (quiz_id) REFERENCES `#__jquarks_quizzes`(id) ON DELETE CASCADE,
  INDEX(setofquestions_id),
  FOREIGN KEY (setofquestions_id) REFERENCES `#__jquarks_setsofquestions`(id) ON DELETE CASCADE,
  CONSTRAINT uc_QuiS UNIQUE (quiz_id, setofquestions_id) 
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table users_quizzes which store the user affected to each quiz

CREATE TABLE IF NOT EXISTS `#__jquarks_users_quizzes` (
  id int(11) NOT NULL AUTO_INCREMENT,
  quiz_id int NOT NULL,  
  user_id int NOT NULL,
  archived boolean NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  INDEX(quiz_id),
  FOREIGN KEY (quiz_id) REFERENCES `#__jquarks_quizzes`(id) ON DELETE CASCADE,
  CONSTRAINT uc_UserQ UNIQUE (user_id, quiz_id)
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table #__jquarks_quizsession which store all the sessions of answer  

CREATE TABLE IF NOT EXISTS `#__jquarks_quizsession` (
  id int(11) NOT NULL AUTO_INCREMENT,
  affected_id int NOT NULL,
  questions_id text,
  started_on datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  finished_on datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  spent_time time,
  ip_address varchar(20), 
  PRIMARY KEY (id),
  INDEX(affected_id),
  FOREIGN KEY (affected_id) REFERENCES `#__jquarks_users_quizzes`(id) ON DELETE CASCADE,
  CONSTRAINT uc_QuiSes UNIQUE (affected_id, started_on)
)ENGINE=INNODB;  

-- -----------------------------------------------------------

-- Creation of table #__jquarks_sessionwho that store the information about the session owner

CREATE TABLE IF NOT EXISTS `#__jquarks_sessionwho` (
  id int(11) NOT NULL AUTO_INCREMENT,
  session_id int NOT NULL,
  user_id int NOT NULL,
  givenname varchar(30),
  familyname varchar(30),
  email varchar(50),
  PRIMARY KEY (id),
  INDEX(session_id),
  FOREIGN KEY (session_id) REFERENCES `#__jquarks_quizsession`(id) ON DELETE CASCADE
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table #__jquarks_answersessions that store the provided answers for each session

CREATE TABLE IF NOT EXISTS `#__jquarks_quizzes_answersessions` (
  id int(11) NOT NULL AUTO_INCREMENT,
  quizsession_id int NOT NULL,
  question_id int NOT NULL,
  answer_id int NOT NULL,
  altanswer varchar(250),
  status int,
  score float(3,2),
  PRIMARY KEY (id),
  INDEX(quizsession_id),
  FOREIGN KEY (quizsession_id) REFERENCES `#__jquarks_quizsession`(id) ON UPDATE CASCADE ON DELETE CASCADE
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table #__jquarks_profiles that store the profiles of the candidates

CREATE TABLE IF NOT EXISTS `#__jquarks_profiles` (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(50) NOT NULL UNIQUE,
  description varchar(255),
  PRIMARY KEY (id)
)ENGINE=INNODB;

-- -----------------------------------------------------------

-- Creation of table #__jquarks_users_profiles that store the profiles of the candidates

CREATE TABLE IF NOT EXISTS `#__jquarks_users_profiles` (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  profile_id int NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (profile_id) REFERENCES `#__jquarks_profiles`(id) ON DELETE CASCADE,
  CONSTRAINT uc_CanPro UNIQUE (user_id, profile_id)
)ENGINE=INNODB;

-- -----------------------------------------------------------

ALTER TABLE `#__jquarks_quizzes_answersessions` 
change altanswer altanswer text;

ALTER TABLE `#__jquarks_quizzes`
add column show_results tinyint(1) DEFAULT 0;

ALTER TABLE `#__jquarks_questions`
ADD `description` VARCHAR( 100 ) NOT NULL AFTER `statement`;