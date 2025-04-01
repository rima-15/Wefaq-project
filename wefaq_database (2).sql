-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 01, 2025 at 09:10 AM
-- Server version: 5.7.24
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
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `chat_ID` int(11) NOT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`chat_ID`, `project_ID`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `file_ID` int(11) NOT NULL,
  `file_name` varchar(40) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` double NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploaded_by` int(11) DEFAULT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `file`
--

INSERT INTO `file` (`file_ID`, `file_name`, `file_type`, `file_size`, `uploaded_at`, `uploaded_by`, `project_ID`) VALUES
(1, 'design_mockup.png', 'image/png', 2048, '2025-03-26 13:00:00', 2, 1),
(2, 'api_documentation.pdf', 'application/pdf', 1024, '2025-03-26 14:15:00', 3, 2),
(3, 'security_report.docx', 'application/msword', 3072, '2025-03-26 15:30:00', 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `invite`
--

CREATE TABLE `invite` (
  `inviter_ID` int(11) NOT NULL,
  `invitee_ID` int(11) NOT NULL,
  `project_ID` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `invite`
--

INSERT INTO `invite` (`inviter_ID`, `invitee_ID`, `project_ID`, `status`, `timestamp`) VALUES
(1, 2, 1, 'accepted', '2025-03-26 18:00:00'),
(2, 3, 2, 'pending', '2025-03-26 18:30:00'),
(3, 4, 3, 'declined', '2025-03-26 19:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_ID` int(11) NOT NULL,
  `message_text` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sender_ID` int(11) DEFAULT NULL,
  `chat_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_ID`, `message_text`, `timestamp`, `sender_ID`, `chat_ID`) VALUES
(1, 'Hello, team!', '2025-03-26 16:00:00', 1, 1),
(2, 'API development is ongoing.', '2025-03-26 16:15:00', 2, 2),
(3, 'Security testing will start soon.', '2025-03-26 16:30:00', 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
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
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_ID`, `type`, `message`, `status`, `created_at`, `user_ID`, `related_ID`) VALUES
(1, 'invite', 'You have been invited to a project.', 'Unread', '2025-03-26 17:00:00', 2, 1),
(2, 'rate', 'You have received a new rating.', 'read', '2025-03-26 17:30:00', 3, 3),
(3, 'rate', 'Come and rate your team members !', 'Unread', '2025-03-28 09:30:32', 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_ID` int(11) NOT NULL,
  `project_name` varchar(40) NOT NULL,
  `project_description` varchar(255) NOT NULL,
  `project_deadline` date NOT NULL,
  `status` enum('in progress','completed') NOT NULL DEFAULT 'in progress',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leader_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`project_ID`, `project_name`, `project_description`, `project_deadline`, `status`, `created_at`, `leader_ID`) VALUES
(1, 'AI Assistant', 'Developing an AI-powered chatbot', '2025-12-31', 'in progress', '2025-03-26 07:15:00', 1),
(2, 'E-Commerce Platform', 'Building an online marketplace', '2025-10-15', 'in progress', '2025-03-26 08:30:00', 2),
(3, 'Cyber Security Tool', 'Developing a security monitoring system', '2025-08-20', 'completed', '2025-03-26 09:45:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `projectteam`
--

CREATE TABLE `projectteam` (
  `user_ID` int(11) NOT NULL,
  `project_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projectteam`
--

INSERT INTO `projectteam` (`user_ID`, `project_ID`) VALUES
(1, 1),
(2, 1),
(2, 2),
(3, 2),
(3, 3),
(4, 3),
(5, 3),
(5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `rate`
--

CREATE TABLE `rate` (
  `rater_ID` int(11) NOT NULL,
  `rated_ID` int(11) NOT NULL,
  `rating_value` int(11) NOT NULL,
  `skill_ID` int(11) NOT NULL,
  `related_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rate`
--

INSERT INTO `rate` (`rater_ID`, `rated_ID`, `rating_value`, `skill_ID`, `related_ID`) VALUES
(3, 4, 2, 1, 3),
(3, 4, 4, 2, 3),
(3, 4, 2, 3, 3),
(3, 4, 0, 4, 3),
(3, 4, 5, 5, 3),
(3, 4, 4, 6, 3),
(3, 5, 2, 1, 3),
(3, 5, 4, 2, 3),
(3, 5, 3, 3, 3),
(3, 5, 4, 4, 3),
(3, 5, 1, 5, 3),
(3, 5, 3, 6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `skill_ID` int(11) NOT NULL,
  `skill_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `skill`
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
-- Table structure for table `task`
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
-- Dumping data for table `task`
--

INSERT INTO `task` (`task_ID`, `task_name`, `task_description`, `status`, `created_at`, `task_deadline`, `assigned_to`, `created_by`, `project_ID`) VALUES
(1, 'Design UI', 'Create the interface for the app', 'not started', '2025-03-26 10:00:00', '2025-06-01', 2, 1, 1),
(2, 'Backend API', 'Develop API endpoints', 'in progress', '2025-03-26 11:15:00', '2025-07-01', 3, 2, 2),
(3, 'Penetration Testing', 'Perform security testing', 'completed', '2025-03-26 12:30:00', '2025-09-15', 4, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `user`
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
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_ID`, `username`, `gender`, `user_type`, `organization`, `bio`, `email`, `phone_num`, `password`) VALUES
(1, 'Alice', 'F', 'Professional', 'TechCorp', 'Tech enthusiast', 'alice@example.com', '1234567890', 'password123'),
(2, 'Bob', 'M', 'Student', 'Innova', 'Loves coding', 'bob@example.com', '0987654321', 'securepass'),
(3, 'Charlie', 'M', 'Professional', 'CreativeWorks', 'Graphic designer', 'charlie@example.com', '1122334455', 'designpass'),
(4, 'David', 'M', 'Student', 'DataCorp', 'Data scientist', 'david@example.com', '2233445566', 'datapass'),
(5, 'Emma', 'F', 'Professional', 'TestersHub', 'Software tester', 'emma@example.com', '3344556677', 'testpass');

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
  MODIFY `chat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `notification_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `skill_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `file_project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_user` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invite`
--
ALTER TABLE `invite`
  ADD CONSTRAINT `invitee` FOREIGN KEY (`invitee_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inviter_user` FOREIGN KEY (`inviter_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_chat` FOREIGN KEY (`chat_ID`) REFERENCES `chat` (`chat_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_sender` FOREIGN KEY (`sender_ID`) REFERENCES `user` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `noti_project` FOREIGN KEY (`related_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `noti_user` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `leader` FOREIGN KEY (`leader_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `projectteam`
--
ALTER TABLE `projectteam`
  ADD CONSTRAINT `project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rate`
--
ALTER TABLE `rate`
  ADD CONSTRAINT `rated_user` FOREIGN KEY (`rated_ID`) REFERENCES `user` (`user_ID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `rater_user` FOREIGN KEY (`rater_ID`) REFERENCES `user` (`user_ID`) ON DELETE NO ACTION,
  ADD CONSTRAINT `related_project` FOREIGN KEY (`related_ID`) REFERENCES `project` (`project_ID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `skill_skill` FOREIGN KEY (`skill_ID`) REFERENCES `skill` (`skill_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `task-project` FOREIGN KEY (`project_ID`) REFERENCES `project` (`project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_assign` FOREIGN KEY (`assigned_to`) REFERENCES `projectteam` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `task_create` FOREIGN KEY (`created_by`) REFERENCES `projectteam` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
