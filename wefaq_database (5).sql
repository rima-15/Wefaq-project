-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: 10 أبريل 2025 الساعة 19:15
-- إصدار الخادم: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wefaq_database`
--

-- --------------------------------------------------------

--
-- بنية الجدول `chat`
--

CREATE TABLE `chat` (
  `chat_ID` int(11) NOT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `chat`
--

INSERT INTO `chat` (`chat_ID`, `project_ID`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- بنية الجدول `file`
--

CREATE TABLE `file` (
  `file_ID` int(11) NOT NULL,
  `file_name` varchar(40) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(10) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploaded_by` int(11) DEFAULT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `file`
--

INSERT INTO `file` (`file_ID`, `file_name`, `file_type`, `file_size`, `uploaded_at`, `uploaded_by`, `project_ID`) VALUES
(1, 'requirements.pdf', 'PDF', '2.5MB', '2023-01-15 21:00:00', 8, 1),
(2, 'design_mockups.fig', 'FIGMA', '5.1MB', '2023-01-17 21:00:00', 7, 1),
(3, 'api_spec.yaml', 'YAML', '0.3MB', '2023-02-20 21:00:00', 6, 2);

-- --------------------------------------------------------

--
-- بنية الجدول `invite`
--

CREATE TABLE `invite` (
  `inviter_ID` int(11) NOT NULL,
  `invitee_ID` int(11) NOT NULL,
  `project_ID` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `invite`
--

INSERT INTO `invite` (`inviter_ID`, `invitee_ID`, `project_ID`, `status`, `timestamp`) VALUES
(6, 9, 2, 'accepted', '2025-04-10 18:02:58'),
(6, 7, 2, 'accepted', '2025-04-10 18:03:02'),
(6, 8, 2, 'accepted', '2025-04-10 18:03:11'),
(6, 10, 2, 'pending', '2025-04-10 18:03:30'),
(9, 10, 3, 'accepted', '2025-04-10 18:05:50'),
(9, 7, 3, 'accepted', '2025-04-10 18:07:50'),
(9, 8, 3, 'pending', '2025-04-10 18:09:05'),
(8, 6, 1, 'accepted', '2025-04-10 18:09:47'),
(8, 9, 1, 'pending', '2025-04-10 18:10:43'),
(9, 6, 3, 'pending', '2025-04-10 18:12:03'),
(8, 10, 1, 'pending', '2025-04-10 18:20:18');

-- --------------------------------------------------------

--
-- بنية الجدول `message`
--

CREATE TABLE `message` (
  `message_ID` int(11) NOT NULL,
  `message_text` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sender_ID` int(11) DEFAULT NULL,
  `chat_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `message`
--

INSERT INTO `message` (`message_ID`, `message_text`, `timestamp`, `sender_ID`, `chat_ID`) VALUES
(1, 'Has everyone reviewed the requirements?', '2023-01-16 06:30:00', 8, 1),
(2, 'Yes, I have some design ideas', '2023-01-16 07:15:00', 7, 1),
(3, 'When can we schedule a kickoff meeting?', '2023-02-21 08:20:00', 9, 2);

-- --------------------------------------------------------

--
-- بنية الجدول `notification`
--

CREATE TABLE `notification` (
  `notification_ID` int(11) NOT NULL,
  `type` enum('invite','rate') NOT NULL,
  `message` varchar(255) NOT NULL,
  `status` enum('Unread','read') NOT NULL DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_ID` int(11) NOT NULL,
  `related_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `notification`
--

INSERT INTO `notification` (`notification_ID`, `type`, `message`, `status`, `created_at`, `user_ID`, `related_ID`) VALUES
(2, 'rate', 'Please rate your team members for Mobile App', 'read', '2023-03-10 21:00:00', 7, 3),
(4, 'rate', 'come and rate your team members ', 'Unread', '2025-03-25 20:42:18', 9, 3),
(5, 'rate', 'rating is waiting for you !', 'Unread', '2025-03-24 20:43:17', 10, 3),
(17, 'invite', 'New project invitation: Mobile App', 'read', '2025-04-10 18:02:58', 9, 2),
(18, 'invite', 'Team up on project: Mobile App - invitation waiting!', 'read', '2025-04-10 18:03:02', 7, 2),
(19, 'invite', 'You\'ve been invited to collaborate on project: Mobile App', 'read', '2025-04-10 18:03:11', 8, 2),
(20, 'invite', 'New project invitation: Mobile App', 'Unread', '2025-04-10 18:03:30', 10, 2),
(21, 'invite', 'Data Analytics Dashboard wants you to join their project team!', 'read', '2025-04-10 18:05:50', 10, 3),
(22, 'invite', 'You\'ve been invited to collaborate on project: Data Analytics Dashboard', 'read', '2025-04-10 18:07:50', 7, 3),
(23, 'invite', 'Data Analytics Dashboard wants you to join their project team!', 'Unread', '2025-04-10 18:09:05', 8, 3),
(24, 'invite', 'New project invitation: E-commerce Platform', 'read', '2025-04-10 18:09:47', 6, 1),
(25, 'invite', 'E-commerce Platform is requesting your skills on their project', 'Unread', '2025-04-10 18:10:43', 9, 1),
(26, 'invite', 'Data Analytics Dashboard wants you to join their project team!', 'Unread', '2025-04-10 18:12:03', 6, 3),
(27, 'invite', 'Team up on project: E-commerce Platform - invitation waiting!', 'Unread', '2025-04-10 18:20:18', 10, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `project`
--

CREATE TABLE `project` (
  `project_ID` int(11) NOT NULL,
  `project_name` varchar(40) NOT NULL,
  `project_description` varchar(255) DEFAULT NULL,
  `project_deadline` date NOT NULL,
  `status` enum('in progress','completed') NOT NULL DEFAULT 'in progress',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leader_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `project`
--

INSERT INTO `project` (`project_ID`, `project_name`, `project_description`, `project_deadline`, `status`, `created_at`, `leader_ID`) VALUES
(1, 'E-commerce Platform', 'Build a scalable online store', '2023-12-31', 'in progress', '2023-01-14 21:00:00', 8),
(2, 'Mobile App', 'Develop a fitness tracking application', '2023-11-30', 'in progress', '2023-02-19 21:00:00', 6),
(3, 'Data Analytics Dashboard', 'Create visualization tools for business metrics', '2023-10-15', 'completed', '2023-03-09 21:00:00', 9);

-- --------------------------------------------------------

--
-- بنية الجدول `projectteam`
--

CREATE TABLE `projectteam` (
  `user_ID` int(11) NOT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `projectteam`
--

INSERT INTO `projectteam` (`user_ID`, `project_ID`) VALUES
(8, 1),
(6, 2),
(9, 3),
(9, 2),
(10, 3),
(7, 2),
(7, 3),
(8, 2),
(6, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `rate`
--

CREATE TABLE `rate` (
  `rater_ID` int(11) DEFAULT NULL,
  `rated_ID` int(11) NOT NULL,
  `rating_value` int(11) NOT NULL,
  `skill_ID` int(11) NOT NULL,
  `related_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `rate`
--

INSERT INTO `rate` (`rater_ID`, `rated_ID`, `rating_value`, `skill_ID`, `related_ID`) VALUES
(7, 9, 1, 1, 3),
(7, 9, 4, 2, 3),
(7, 9, 2, 3, 3),
(7, 9, 3, 4, 3),
(7, 9, 4, 5, 3),
(7, 9, 5, 6, 3),
(7, 10, 1, 1, 3),
(7, 10, 5, 2, 3),
(7, 10, 3, 3, 3),
(7, 10, 5, 4, 3),
(7, 10, 1, 5, 3),
(7, 10, 5, 6, 3);

-- --------------------------------------------------------

--
-- بنية الجدول `skill`
--

CREATE TABLE `skill` (
  `skill_ID` int(11) NOT NULL,
  `skill_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `skill`
--

INSERT INTO `skill` (`skill_ID`, `skill_name`) VALUES
(1, 'Design'),
(2, 'Programming'),
(3, 'Communication Skills'),
(4, 'Work Quality'),
(5, 'Adaptability to feedback'),
(6, 'Time Management');

-- --------------------------------------------------------

--
-- بنية الجدول `task`
--

CREATE TABLE `task` (
  `task_ID` int(11) NOT NULL,
  `task_name` varchar(25) NOT NULL,
  `task_description` varchar(85) DEFAULT NULL,
  `status` enum('unassigned','not started','in progress','completed') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `task_deadline` date NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `task`
--

INSERT INTO `task` (`task_ID`, `task_name`, `task_description`, `status`, `created_at`, `task_deadline`, `assigned_to`, `created_by`, `project_ID`) VALUES
(1, 'Design Homepage', 'Create wireframes for main page', 'in progress', '2023-01-19 21:00:00', '2023-02-15', 8, 8, 1),
(2, 'API Development', 'Build product catalog endpoints', 'not started', '2023-01-21 21:00:00', '2023-03-01', 6, 8, 1),
(3, 'User Authentication', 'Implement login/signup flow', 'completed', '2023-02-24 21:00:00', '2023-03-10', 7, 6, 2),
(4, 'Data Pipeline', 'Set up ETL process for analytics', 'unassigned', '2023-03-14 21:00:00', '2023-04-20', NULL, 9, 3);

-- --------------------------------------------------------

--
-- بنية الجدول `user`
--

CREATE TABLE `user` (
  `user_ID` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `gender` char(1) NOT NULL,
  `user_type` varchar(15) NOT NULL,
  `organization` varchar(40) NOT NULL,
  `bio` varchar(80) DEFAULT NULL,
  `email` varchar(40) NOT NULL,
  `phone_num` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- إرجاع أو استيراد بيانات الجدول `user`
--

INSERT INTO `user` (`user_ID`, `username`, `gender`, `user_type`, `organization`, `bio`, `email`, `phone_num`, `password`) VALUES
(6, 'Alice', 'F', 'professional', 'TechCorp', 'Tech enthusiast', 'alice@example.com', '1234567890', '$2y$10$o2jczlQ0GS7WFYvdorPD5uPavCwrrVVvdnjZDZ0vmLgPYRndU9MYG'),
(7, 'Steve', 'M', 'student', 'Ksu', 'Loves coding', 'steve@example.com', '0987654321', '$2y$10$bKnwnqPtXWtxKAvzlNmbyer9lXo84.S3ktWz1cZxQpckLzqJh08vm'),
(8, 'Charlie', 'M', 'professional', 'CreativeWorks', 'Graphic designer', 'charlie@example.com', '1122334455', '$2y$10$KKxStYrIWJraiHDtngipGuv6kt1.UcTPergxrEe3TImWJlWrZjHoS'),
(9, 'David', 'M', 'student', 'KFU', 'Data scientist', 'david@example.com', '2233445566', '$2y$10$actwT1Blcog82jAAAil/ZOuOTIKt3Uo4DATkd1BBCSMyfBK35OPoC'),
(10, 'Sophia', 'F', 'professional', 'TestersHub', 'Software teste', 'Sophia@example.com', '3344556677', '$2y$10$RBoNiqxOFA9KkjF1jR5YJ.Wi99wtKjo75BA0xWMseP4x9sW15TZY.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`chat_ID`),
  ADD KEY `project_ID` (`project_ID`);

--
-- Indexes for table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`file_ID`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `project_ID` (`project_ID`);

--
-- Indexes for table `invite`
--
ALTER TABLE `invite`
  ADD KEY `inviter_ID` (`inviter_ID`),
  ADD KEY `invitee_ID` (`invitee_ID`),
  ADD KEY `project_ID` (`project_ID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_ID`),
  ADD KEY `sender_ID` (`sender_ID`),
  ADD KEY `chat_ID` (`chat_ID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `related_ID` (`related_ID`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_ID`),
  ADD KEY `leader_ID` (`leader_ID`);

--
-- Indexes for table `projectteam`
--
ALTER TABLE `projectteam`
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `project_ID` (`project_ID`);

--
-- Indexes for table `rate`
--
ALTER TABLE `rate`
  ADD KEY `rater_ID` (`rater_ID`),
  ADD KEY `rated_ID` (`rated_ID`),
  ADD KEY `skill_ID` (`skill_ID`),
  ADD KEY `related_ID` (`related_ID`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`skill_ID`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`task_ID`),
  ADD KEY `project_ID` (`project_ID`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `chat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `file_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `skill_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `file_project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_user` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- القيود للجدول `invite`
--
ALTER TABLE `invite`
  ADD CONSTRAINT `invitee` FOREIGN KEY (`invitee_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inviter_user` FOREIGN KEY (`inviter_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_chat` FOREIGN KEY (`chat_ID`) REFERENCES `chat` (`chat_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_sender` FOREIGN KEY (`sender_ID`) REFERENCES `user` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- القيود للجدول `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `noti_project` FOREIGN KEY (`related_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `noti_user` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `leader` FOREIGN KEY (`leader_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `projectteam`
--
ALTER TABLE `projectteam`
  ADD CONSTRAINT `project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `rate`
--
ALTER TABLE `rate`
  ADD CONSTRAINT `rated_user` FOREIGN KEY (`rated_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rater_user` FOREIGN KEY (`rater_ID`) REFERENCES `user` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `related_project` FOREIGN KEY (`related_ID`) REFERENCES `project` (`project_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `skill_skill` FOREIGN KEY (`skill_ID`) REFERENCES `skill` (`skill_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `task-project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_assign` FOREIGN KEY (`assigned_to`) REFERENCES `projectteam` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `task_create` FOREIGN KEY (`created_by`) REFERENCES `projectteam` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
