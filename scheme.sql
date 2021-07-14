CREATE TABLE `assignments` (
  `assignment_id` int(12) UNSIGNED NOT NULL,
  `assignment_user` text NOT NULL,
  `assignment_subject` int(16) UNSIGNED NOT NULL,
  `assignment_name` text NOT NULL,
  `assignment_expires` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`);
  
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;



CREATE TABLE `assignment_responses` (
  `response_id` int(12) UNSIGNED NOT NULL,
  `response_user` text NOT NULL,
  `response_assignment` int(12) UNSIGNED NOT NULL,
  `response_date` text NOT NULL,
  `response_file` text NOT NULL,
  `response_file_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `assignment_responses`
  ADD PRIMARY KEY (`response_id`);
  
ALTER TABLE `assignment_responses`
  MODIFY `response_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `calendar_events` (
  `event_id` int(12) UNSIGNED NOT NULL,
  `event_subject` int(16) UNSIGNED DEFAULT NULL,
  `event_class` int(12) UNSIGNED DEFAULT NULL,
  `event_date` text NOT NULL,
  `event_user` text NOT NULL,
  `event_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`event_id`);
  
ALTER TABLE `calendar_events`
  MODIFY `event_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `classes` (
  `class_id` int(12) UNSIGNED NOT NULL,
  `class_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);
  
ALTER TABLE `classes`
  MODIFY `class_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `files` (
  `file_id` int(16) UNSIGNED NOT NULL,
  `file_name` text NOT NULL,
  `file_uid` text NOT NULL,
  `file_owner` text NOT NULL,
  `file_date` text NOT NULL,
  `file_size` text NOT NULL,
  `file_fav` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`);
  
ALTER TABLE `files`
  MODIFY `file_id` int(16) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `lefkoma` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` text NOT NULL,
  `owner` text NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `lefkoma`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lefkoma`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `lefkoma_comments` (
  `comm_id` int(12) NOT NULL,
  `comm_from` text NOT NULL,
  `comm_to` text NOT NULL,
  `comm_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `lefkoma_comments`
  ADD PRIMARY KEY (`comm_id`);
  
ALTER TABLE `lefkoma_comments`
  MODIFY `comm_id` int(12) NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `messages` (
  `message_id` int(12) UNSIGNED NOT NULL,
  `message_sender` text NOT NULL,
  `message_recipient` text NOT NULL,
  `message_date` text NOT NULL,
  `message_content` mediumtext NOT NULL,
  `message_type` tinyint(1) NOT NULL,
  `message_opened` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);
  
ALTER TABLE `messages`
  MODIFY `message_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  

CREATE TABLE `notif_subs` (
  `subscription_id` int(12) UNSIGNED NOT NULL,
  `subscription_username` text NOT NULL,
  `subscription_endpoint` text NOT NULL,
  `subscription_publickey` text NOT NULL,
  `subscription_authtoken` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `notif_subs`
  ADD PRIMARY KEY (`subscription_id`);
  
ALTER TABLE `notif_subs`
  MODIFY `subscription_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `options` (
  `option_id` int(6) UNSIGNED NOT NULL,
  `option_name` text NOT NULL,
  `option_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `options` (`option_id`, `option_name`, `option_value`) VALUES
(1, 'program-students', ''),
(2, 'program-teachers', ''),
(3, 'program-text', ''),
(4, 'program-students-file', ''),
(5, 'program-teachers-file', '');

ALTER TABLE `options`
  ADD PRIMARY KEY (`option_id`);
  
ALTER TABLE `options`
  MODIFY `option_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
  
  

CREATE TABLE `posts` (
  `post_id` int(16) UNSIGNED NOT NULL,
  `post_usage` varchar(256) NOT NULL,
  `post_used_id` int(16) UNSIGNED DEFAULT NULL,
  `post_author` varchar(256) NOT NULL,
  `post_date` text NOT NULL,
  `post_visibility` tinyint(1) NOT NULL,
  `post_title` text DEFAULT NULL,
  `post_text` longtext DEFAULT NULL,
  `post_files` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`);
  
ALTER TABLE `posts`
  MODIFY `post_id` int(16) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `radio_dir` (
  `dir_id` int(6) UNSIGNED NOT NULL,
  `dir_time` text NOT NULL,
  `dir_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `radio_dir`
  ADD PRIMARY KEY (`dir_id`);
  
ALTER TABLE `radio_dir`
  MODIFY `dir_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `radio_messages` (
  `message_id` int(6) UNSIGNED NOT NULL,
  `message_time` text NOT NULL,
  `message_name` text NOT NULL,
  `message_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `radio_messages`
  ADD PRIMARY KEY (`message_id`);
  
ALTER TABLE `radio_messages`
  MODIFY `message_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `subjects` (
  `subject_id` int(16) UNSIGNED NOT NULL,
  `subject_name` text NOT NULL,
  `subject_class` int(12) UNSIGNED DEFAULT NULL,
  `subject_latest_update` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);
  
ALTER TABLE `subjects`
  MODIFY `subject_id` int(16) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `tests` (
  `test_id` int(12) UNSIGNED NOT NULL,
  `test_user` text NOT NULL,
  `test_subject` int(16) UNSIGNED NOT NULL,
  `test_name` text NOT NULL,
  `test_data` longtext NOT NULL,
  `test_expires` text NOT NULL,
  `test_visibility` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `tests`
  ADD PRIMARY KEY (`test_id`);
  
ALTER TABLE `tests`
  MODIFY `test_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `test_responses` (
  `response_id` int(12) UNSIGNED NOT NULL,
  `response_test` int(12) UNSIGNED NOT NULL,
  `response_user` text NOT NULL,
  `response_start` bigint(20) NOT NULL,
  `response_end` bigint(20) DEFAULT NULL,
  `response_data` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `test_responses`
  ADD PRIMARY KEY (`response_id`);
  
ALTER TABLE `test_responses`
  MODIFY `response_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `users` (
  `user_id` int(12) UNSIGNED NOT NULL,
  `user_username` text NOT NULL,
  `user_password` text NOT NULL,
  `user_secret` text DEFAULT NULL,
  `user_name` text NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `user_class` int(12) UNSIGNED DEFAULT NULL,
  `user_last_ping` bigint(20) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);
  
ALTER TABLE `users`
  MODIFY `user_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;



CREATE TABLE `user_links` (
  `link_id` int(17) UNSIGNED NOT NULL,
  `link_user` text NOT NULL,
  `link_usage` text NOT NULL,
  `link_used_id` int(16) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_links`
  ADD PRIMARY KEY (`link_id`);
  
ALTER TABLE `user_links`
  MODIFY `link_id` int(17) UNSIGNED NOT NULL AUTO_INCREMENT;



CREATE TABLE `user_polls` (
  `poll_id` int(12) UNSIGNED NOT NULL,
  `poll_by` text NOT NULL,
  `poll_date` text NOT NULL,
  `poll_shown` text NOT NULL,
  `poll_text` text NOT NULL,
  `poll_options` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_polls`
  ADD PRIMARY KEY (`poll_id`);
  
ALTER TABLE `user_polls`
  MODIFY `poll_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  

CREATE TABLE `user_poll_ans` (
  `ans_id` int(12) UNSIGNED NOT NULL,
  `ans_poll` int(12) NOT NULL,
  `ans_user` text NOT NULL,
  `ans_val` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_poll_ans`
  ADD PRIMARY KEY (`ans_id`);
  
ALTER TABLE `user_poll_ans`
  MODIFY `ans_id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;