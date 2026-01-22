-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 16, 2025 at 04:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

-- Create and select the database
CREATE DATABASE IF NOT EXISTS `tictracker_V10` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tictracker_V10`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

-- user_profile

-- structure for table `user_profile`
CREATE TABLE IF NOT EXISTS `user_profile` (
  `User_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_Image` varchar(250) NOT NULL COMMENT 'meter link na imagem',
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(250) NOT NULL,
  `Birthday` DATE NOT NULL,
  `Role` enum('Professional','Patient') NOT NULL,
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- insert data into `user_profile`
INSERT IGNORE INTO `user_profile` (`User_ID`,`User_Image`,`First_Name`,`Last_Name`,`Email`,`Password`,`Birthday`,`Role`) VALUES
(1,  '/images/users/1.jpg', 'Maria',  'Silva',   'maria.silva@example.com',   'pass_placeholder_1', '1997-01-01', 'Patient'),
(2,  '/images/users/2.jpg', 'João',   'Pereira', 'joao.pereira@example.com',  'pass_placeholder_2', '1991-01-01', 'Patient'),
(3,  '/images/users/3.jpg', 'Ana',    'Costa',   'ana.costa@example.com',     'pass_placeholder_3', '2006-01-01', 'Patient'),
(4,  '/images/users/4.jpg', 'Lucas',  'Oliveira','lucas.oliveira@example.com','pass_placeholder_4', '1983-01-01', 'Patient'),
(5,  '/images/users/5.jpg', 'Sofia',  'Martins', 'sofia.martins@example.com', 'pass_placeholder_5', '2009-01-01', 'Patient'),
(6,  '/images/users/6.jpg', 'Ana',    'Almeida', 'ana.almeida@clinic.example', 'pass_placeholder_6', '1980-01-01', 'Professional'),
(7,  '/images/users/7.jpg', 'Roberto','Sousa',   'roberto.sousa@clinic.example','pass_placeholder_7', '1975-01-01', 'Professional'),
(8,  '/images/users/8.jpg', 'Laura',  'Mendes',  'laura.mendes@clinic.example', 'pass_placeholder_8', '1987-01-01', 'Professional'),
(9,  '/images/users/9.jpg', 'Helena', 'Ribeiro', 'helena.ribeiro@clinic.example','pass_placeholder_9', '1984-01-01', 'Professional'),
(10, '/images/users/10.jpg','Miguel', 'Santos',  'miguel.santos@clinic.example',  'pass_placeholder_10','1988-01-01', 'Professional'),
(11, '../../uploads/1764756088_photo-1560250097-0b93528c311a.avif', 'Bhawna', 'Panwar', 'bhawna.panwar@gmail.com', '$2y$10$/272OYEtL3h553dDJbzB7OiK1hzCMT/HjA8i8ZqZx0RJx/W6cu896', '2004-05-10', 'Patient'),
(12, '../../uploads/1764714069_IMG-20251113-WA0014.jpg', 'Julia', 'Vidal', 'julia.vidal@gmail.com', '$2y$10$zwmknepbbzqPXAzeh9/WYu8KriRJ.nbMHpX84CGphSHq0K9GRNm/W', '2010-11-20', 'Patient'),
(13, '../../uploads/1764716169_photo-1560250097-0b93528c311a.avif', 'Rodrigo', 'Neves', 'rodrigo.neves@gmail.com', '$2y$10$B/UEJoxt.Pbz9VZZkhtfEOPU8xHJtb9tPRVAzxzY/alU5N2wddCqm', '1995-03-08', 'Professional'),
(14, '/images/users/14.jpg', 'Oleg', 'Stepanov', 'oleg@gmail.com', '$2y$10$3TKcbtvaU9JrKhERBGAHce1iKM2jyPD5iE4T41YLJFvWTy41Cy4Li', '2000-01-01', 'Patient'),
(15, '/images/users/15.jpg', 'Mr', 'Doctor', 'doctor13@gmail.com', '$2y$10$WtAphIp3SmwpptxgE9LAuufVJqpFU4UcwGxeZgF6lH2hC1utjluoa', '1970-01-01', 'Professional'),
(16, '/images/users/16.jpg', 'Maria', 'Mihailova', 'maria19@gmail.com', '$2y$10$QGVaCy.YLGp2GmSw2Zd5Oe6MXj/rbAKB3QBjdMxIXg/RtvB4Kxih2', '2000-01-01', 'Patient'),
(17, '/images/users/17.jpg', 'doctor', 'nopatients', 'doctornopatients@gmail.com', '$2y$10$gE4TyeyjdfPQiAniRtl18u0VH4Iu.6g1bI9j/B0pvpFzbemcTJgmC', '1970-01-01', 'Professional');

ALTER TABLE `user_profile`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

-- --------------------------------------------------------

-- patient_professional_link


-- structure for table `patient_professional_link`

CREATE TABLE IF NOT EXISTS `patient_professional_link` (
  `Link_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Professional_ID` int(11) NOT NULL,
  `Assigned_Date` date NOT NULL,
  `Status` enum('Pending','Currently Followed','Discharged','Drop Out') DEFAULT 'Pending',
  `Connection_Status` enum('Pending','Accepted') NOT NULL DEFAULT 'Pending',
  `Treatment_Type` enum('Medical','Psychological','Both') DEFAULT NULL,
  PRIMARY KEY (`Link_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Professional_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- insert data into `patient_professional_link`

INSERT IGNORE INTO `patient_professional_link` (`Link_ID`, `Patient_ID`, `Professional_ID`, `Assigned_Date`, `Status`, `Connection_Status`, `Treatment_Type`) VALUES
(1, 1, 6, '2025-02-10', 'Pending', 'Pending', NULL),
(2, 2, 7, '2024-11-05', 'Pending', 'Pending', NULL),
(3, 3, 8, '2025-06-01', 'Pending', 'Pending', NULL),
(4, 4, 9, '2023-09-20', 'Pending', 'Pending', NULL),
(5, 5, 10, '2025-08-15', 'Pending', 'Pending', NULL),
(8, 11, 8, '2025-12-02', 'Pending', 'Pending', 'Medical'),
(10, 12, 13, '2025-12-02', 'Currently Followed', 'Accepted', 'Medical'),
(17, 11, 13, '2025-12-02', 'Discharged', 'Accepted', 'Medical'),
(18, 11, 7, '2025-12-02', 'Pending', 'Pending', 'Medical'),
(19, 12, 7, '2025-12-03', 'Pending', 'Pending', 'Medical'),
(20, 11, 10, '2025-12-03', 'Pending', 'Pending', 'Medical'),
(21, 3, 13, '2025-12-03', 'Currently Followed', 'Accepted', 'Medical'),
(16, 15, 6, '2025-12-19', 'Currently Followed', 'Accepted', 'Medical'),
(14, 15, 6, '2025-12-19', 'Currently Followed', 'Accepted', 'Medical');


-- Trigger to validate roles in 'patient_professional_link' on INSERT

DELIMITER $$
CREATE TRIGGER `trg_ppl_before_ins` BEFORE INSERT ON `patient_professional_link` FOR EACH ROW BEGIN
  DECLARE role_patient VARCHAR(50);
  DECLARE role_prof VARCHAR(50);

  SELECT Role INTO role_patient FROM user_profile WHERE User_ID = NEW.Patient_ID;
  SELECT Role INTO role_prof    FROM user_profile WHERE User_ID = NEW.Professional_ID;

  IF role_patient IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Patient user not found in user_profile';
  END IF;
  IF role_prof IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Professional user not found in user_profile';
  END IF;
  IF role_patient <> 'Patient' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Patient_ID must refer to a user with role = "Patient"';
  END IF;
  IF role_prof <> 'Professional' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Professional_ID must refer to a user with role = "Professional"';
  END IF;
  IF NEW.Patient_ID = NEW.Professional_ID THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Patient_ID and Professional_ID cannot be the same user';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$


-- Trigger to validate roles in 'patient_professional_link' on UPDATE

CREATE TRIGGER `trg_ppl_before_upd` BEFORE UPDATE ON `patient_professional_link` FOR EACH ROW BEGIN
  DECLARE role_patient VARCHAR(50);
  DECLARE role_prof VARCHAR(50);

  SELECT Role INTO role_patient FROM user_profile WHERE User_ID = NEW.Patient_ID;
  SELECT Role INTO role_prof    FROM user_profile WHERE User_ID = NEW.Professional_ID;

  IF role_patient IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Patient user not found in user_profile';
  END IF;
  IF role_prof IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Professional user not found in user_profile';
  END IF;
  IF role_patient <> 'Patient' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Patient_ID must refer to a user with role = "Patient"';
  END IF;
  IF role_prof <> 'Professional' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Professional_ID must refer to a user with role = "Professional"';
  END IF;
  IF NEW.Patient_ID = NEW.Professional_ID THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Patient_ID and Professional_ID cannot be the same user';
  END IF;
END
$$
DELIMITER ;


-- --------------------------------------------------------

-- patient_profile

-- structure for table `patient_profile`

CREATE TABLE IF NOT EXISTS `patient_profile` (
  `User_ID` int(11) NOT NULL,
  `Patient_Status` enum('Drop_Out','Followed','Discharged') NOT NULL,
  `Treatment_Type` enum('Psychological','Medical','Both') NOT NULL,
  `Start_Date` date NOT NULL,
  PRIMARY KEY (`User_ID`),
  FOREIGN KEY (`User_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- insert data into `patient_profile`

INSERT IGNORE INTO `patient_profile` (`User_ID`, `Patient_Status`, `Treatment_Type`, `Start_Date`) VALUES
(1, 'Followed', 'Both', '2025-02-10'),
(2, 'Drop_Out', 'Psychological', '2024-11-05'),
(3, 'Followed', 'Medical', '2025-06-01'),
(4, 'Discharged', 'Both', '2023-09-20'),
(5, 'Followed', 'Psychological', '2025-08-15'),
(11, 'Drop_Out', 'Medical', '0000-00-00');




-- --------------------------------------------------------

-- professional_profile


-- structure for table `professional_profile`

CREATE TABLE IF NOT EXISTS `professional_profile` (
  `User_ID` int(11) NOT NULL,
  `Specialization` varchar(100) DEFAULT 'General Practitioner',
  PRIMARY KEY (`User_ID`),
  FOREIGN KEY (`User_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- insert data into `professional_profile`

INSERT IGNORE INTO `professional_profile` (`User_ID`, `Specialization`) VALUES
(6, 'General Practitioner'),
(7, 'General Practitioner'),
(8, 'General Practitioner'),
(9, 'General Practitioner'),
(10, 'General Practitioner'),
(13, 'Physcologist');


-- --------------------------------------------------------

-- emotional_diary


-- structure for table `emotional_diary`
CREATE TABLE IF NOT EXISTS `emotional_diary` (
  `Emotional_Diary_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Occurrence` datetime NOT NULL,
  `Emotion` varchar(50) NOT NULL,
  `Stress` int(10) NOT NULL,
  `Anxiety` int(10) NOT NULL,
  `Sleep` int(10) NOT NULL,
  `Notes` text DEFAULT NULL,
  PRIMARY KEY (`Emotional_Diary_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- insert data into `emotional_diary`
INSERT IGNORE INTO `emotional_diary` (`Emotional_Diary_ID`, `Patient_ID`, `Occurrence`, `Emotion`, `Stress`, `Anxiety`, `Sleep`, `Notes`) VALUES
(1, 1, '2025-10-12 08:30:00', 'Frustration', 6, 5, 6, 'Woken up tense after a vivid dream. Practiced breathing for 10 minutes.'),
(2, 1, '2025-11-01 20:10:00', 'Hopeful', 3, 2, 7, 'Good session today; felt motivated to try exercises.'),
(3, 2, '2025-09-28 21:15:00', 'Relief', 3, 2, 7, 'Medication adjustment helped; slept better.'),
(4, 3, '2025-07-05 14:45:00', 'Anxious', 8, 8, 4, 'Felt panic when leaving home; noted heartbeat and sweating.'),
(5, 4, '2025-04-02 10:00:00', 'Calm', 2, 1, 8, 'Finished program and feeling improved; family supportive.'),
(6, 5, '2025-11-10 09:00:00', 'Sad', 6, 5, 5, 'Struggled with school stress today; practiced grounding.'),
(7, 12, '0000-00-00 00:00:00', 'Good', 4, 3, 6, ''),
(9, 12, '0000-00-00 00:00:00', 'Good', 4, 3, 6, ''),
(10, 12, '0000-00-00 00:00:00', 'Neutral', 2, 3, 4, ''),
(11, 12, '2025-11-20 01:10:12', 'Neutral', 2, 3, 4, ''),
(12, 12, '2025-11-20 01:11:02', 'Little control', 2, 3, 3, 'okay'),
(13, 11, '2025-11-20 01:14:22', 'Mildly tense or uneasy', 5, 5, 5, ''),
(14, 11, '2025-11-30 20:30:09', 'Stressed and worried', 7, 5, 3, ''),
(15, 11, '2025-11-30 20:32:22', 'Panic / Out of control', 10, 10, 1, ''),
(16, 11, '2025-12-03 01:48:44', 'Neutral — neither good nor bad', 5, 2, 5, ''),
(17, 12, '2025-12-03 03:18:31', 'Somewhat in control', 7, 2, 3, ''),
(18, 12, '2025-12-03 04:44:51', 'Out of control', 5, 8, 5, ''),
(19, 11, '2025-12-03 10:58:52', 'Generally positive', 10, 5, 8, '');



-- --------------------------------------------------------

-- tic_log

-- structure for table `tic_log`


CREATE TABLE IF NOT EXISTS `tic_log` (
  `Tic_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Type` varchar(50) NOT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Type_Description` varchar(255) NOT NULL,
  `Muscle_Group` varchar(255) DEFAULT NULL,
  `Duration` varchar(50) NOT NULL,
  `Intensity` int(11) NOT NULL,
  `Pain_Level` int(11) DEFAULT 0,
  `Premonitory_Urge` varchar(10) DEFAULT NULL,
  `Describe_Text` text NOT NULL,
  `Self_Reported` tinyint(1) NOT NULL,
  `Created_At` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`Tic_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- insert data into `tic_log`

INSERT IGNORE INTO `tic_log` (`Tic_ID`, `Patient_ID`, `Type`, `Category`, `Type_Description`, `Muscle_Group`, `Duration`, `Intensity`, `Pain_Level`, `Premonitory_Urge`, `Describe_Text`, `Self_Reported`, `Created_At`) VALUES
(1, 1, '', NULL, 'Motor Tic', 'Face', '5s', 3, 0, NULL, 'Blinking rapidly during conversation.', 1, '2025-11-30 13:22:19'),
(2, 2, '', NULL, 'Vocal Tic', 'Throat', '10s', 2, 0, NULL, 'Sudden throat clearing in public.', 1, '2025-11-30 13:22:19'),
(3, 3, '', NULL, 'Motor Tic', 'Shoulder', '7s', 4, 0, NULL, 'Shrugging shoulder repeatedly when stressed.', 0, '2025-11-30 13:22:19'),
(4, 1, '', NULL, 'Motor Tic', 'Hand', '3s', 2, 0, NULL, 'Tapping fingers on desk rhythmically.', 1, '2025-11-30 13:22:19'),
(5, 4, '', NULL, 'Vocal Tic', 'Mouth', '5s', 3, 0, NULL, 'Making unusual noises unintentionally.', 0, '2025-11-30 13:22:19'),
(6, 2, '', NULL, 'Motor Tic', 'Face', '8s', 5, 0, NULL, 'Rapid eye blinking and lip movement.', 1, '2025-11-30 13:22:19'),
(7, 3, '', NULL, 'Vocal Tic', 'Throat', '6s', 3, 0, NULL, 'Occasional grunting during intense focus.', 0, '2025-11-30 13:22:19'),
(8, 5, '', NULL, 'Motor Tic', 'Neck', '4s', 2, 0, NULL, 'Quick head movements when anxious.', 1, '2025-11-30 13:22:19'),
(9, 1, '', NULL, 'Vocal Tic', 'Voice', '12s', 4, 0, NULL, 'Repeating words unconsciously during stress.', 1, '2025-11-30 13:22:19'),
(10, 4, '', NULL, 'Motor Tic', 'Arm', '3s', 2, 0, NULL, 'Brief twitching in right arm while writing.', 0, '2025-11-30 13:22:19'),
(13, 11, '', NULL, 'Complex motor tics: Mouth movements', 'Facial muscles', 'Less than a minute', 2, 3, 'yes', '', 1, '2025-11-30 14:10:19'),
(14, 11, 'Motor', 'Simple motor tics', 'Nose movements', 'Facial muscles', 'More than 5 minutes', 2, 2, 'no', '', 1, '2025-11-30 14:17:02'),
(15, 11, '', NULL, 'No Tics Today', '', 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-11-30 15:01:28'),
(16, 11, '', NULL, 'No Tics Today', '', 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-11-30 15:01:50'),
(17, 11, '', NULL, 'No Tics Today', '', 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-11-30 15:25:18'),
(18, 11, 'Motor', 'Simple motor tics', 'Shoulder shrugs', 'Shoulders / Upper trapezius', 'More than 5 minutes', 1, 0, 'yes', '', 1, '2025-11-30 15:25:46'),
(19, 11, 'Motor', 'Complex motor tics', 'Rotating', 'Facial muscles', 'Continuous / Flurry', 8, 2, 'yes', '', 1, '2025-11-30 15:35:26'),
(20, 11, 'Motor', 'Complex motor tics', 'Copropraxia', NULL, 'Less than a minute', 3, 4, 'yes', '', 1, '2025-11-30 15:43:03'),
(21, 11, '', NULL, 'No Tics Today', NULL, 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-11-30 15:43:46'),
(22, 11, '', NULL, 'No Tics Today', NULL, 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-11-30 15:43:57'),
(23, 11, 'Motor', 'Complex motor tics', 'Copropraxia', NULL, 'Less than a minute', 3, 4, 'yes', '', 1, '2025-11-30 15:44:28'),
(24, 11, 'Motor', 'Simple motor tics', 'Leg/foot/toe movements', 'Legs / Feet', 'Continuous / Flurry', 3, 5, 'no', '', 1, '2025-11-30 17:44:33'),
(25, 11, 'Motor', 'Simple motor tics', 'Hand movements', 'Arms / Hands', '1 - 5 minutes', 7, 1, 'no', '', 1, '2025-11-30 18:45:02'),
(26, 11, '', NULL, 'No Tics Today', NULL, 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-11-30 18:50:04'),
(27, 11, 'Motor', 'Simple motor tics', 'Abdominal tensing', 'Abdominal muscles', 'Continuous / Flurry', 0, 0, 'yes', '', 1, '2025-11-30 18:58:13'),
(28, 11, 'Motor', 'Complex motor tics', 'Compulsive behaviors', NULL, 'Less than a minute', 6, 2, 'yes', '', 0, '2025-12-03 01:04:02'),
(29, 11, 'Motor', 'Complex motor tics', 'Mouth movements', 'Facial muscles', '1 - 5 minutes', 3, 3, 'no', '', 1, '2025-12-03 03:39:55'),
(30, 12, 'Motor', 'Simple motor tics', 'Eye movements', 'Orbicularis oculi (eyes)', 'Continuous / Flurry', 7, 1, 'no', '', 0, '2025-12-03 03:43:59'),
(31, 11, '', NULL, 'No Tics Today', NULL, 'No tics', 0, 0, NULL, 'Patient reported no tics today.', 1, '2025-12-03 09:53:59'),
(32, 11, 'Motor', 'Simple motor tics', 'Eye movements', 'Orbicularis oculi (eyes)', '1 - 5 minutes', 4, 2, 'no', 'i felt really anxious', 1, '2025-12-03 09:56:34');

-- --------------------------------------------------------

-- track_medication

-- structure for table `track_medication`

CREATE TABLE IF NOT EXISTS `track_medication` (
  `Track_Medication_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Medication_Name` varchar(50) NOT NULL,
  `Medication_Time` datetime NOT NULL,
  `Medication_Status` tinyint(1) NOT NULL,
  PRIMARY KEY (`Track_Medication_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- insert data into `track_medication`

INSERT IGNORE INTO `track_medication` (`Track_Medication_ID`, `Patient_ID`, `Medication_Name`, `Medication_Time`, `Medication_Status`) VALUES
(1, 1, 'Clonidine', '2025-11-13 08:00:00', 1),
(2, 2, 'Aripiprazole', '2025-11-13 09:00:00', 1),
(3, 3, 'Risperidone', '2025-11-13 08:30:00', 0),
(4, 1, 'Clonidine', '2025-11-13 20:00:00', 0),
(5, 4, 'Guanfacine', '2025-11-13 07:45:00', 1),
(6, 2, 'Aripiprazole', '2025-11-13 21:00:00', 0),
(7, 3, 'Risperidone', '2025-11-13 19:00:00', 1),
(8, 5, 'Clonazepam', '2025-11-13 08:15:00', 1),
(9, 1, 'Clonidine', '2025-11-14 08:00:00', 1),
(10, 4, 'Guanfacine', '2025-11-14 20:00:00', 0);

-- --------------------------------------------------------

-- profesional_notes


-- structure for table `professional_notes`

CREATE TABLE IF NOT EXISTS `professional_notes` (
  `Note_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Professional_ID` int(11) NOT NULL,
  `Note_Title` varchar(250) NOT NULL,
  `Note_Text` text NOT NULL,
  PRIMARY KEY (`Note_ID`),
  FOREIGN KEY (`Professional_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- insert data into `professional_notes`

INSERT IGNORE INTO `professional_notes` (`Note_ID`, `Professional_ID`, `Note_Title`, `Note_Text`) VALUES
(1, 6, 'Initial Consultation', 'Discussed goals and challenges with the client. Agreed weekly check-ins.'),
(2, 7, 'Follow-up Session', 'Reviewed progress and adjusted the action plan accordingly.'),
(3, 8, 'Assessment Results', 'Client showed improvement in stress management after med change.'),
(4, 6, 'Therapy Notes', 'Introduced mindfulness and breathing strategies.'),
(5, 9, 'Meeting Summary', 'Outlined next steps for vocational support.'),
(6, 7, 'Client Feedback', 'Client reported better sleep with new routine.'),
(7, 8, 'Progress Evaluation', 'Improvements in social confidence noted.'),
(8, 10, 'Observation Notes', 'Observed interactions in group session; positive engagement.'),
(9, 6, 'Plan Update', 'Updated treatment plan to include CBT exercises.'),
(10, 7, 'Session Reflection', 'Client reflected on personal achievements and challenges.');

-- --------------------------------------------------------

-- resource_hub


-- structure for table `resource_hub`
CREATE TABLE IF NOT EXISTS `resource_hub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` enum('banner','category','article') NOT NULL,
  `banner_content_type` enum('article','video') DEFAULT NULL,
  `category_type` enum('competing_behaviours','habit_reversal','anxiety_management','pmr_training') DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `thumb_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `professional_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- insert data into `resource_hub`

INSERT IGNORE INTO `resource_hub` (`id`, `item_type`, `banner_content_type`, `category_type`, `title`, `subtitle`, `content`, `media_url`, `image_url`, `thumb_url`, `sort_order`, `created_at`) VALUES
(29, 'banner', NULL, NULL, 'for oleg', 'wee', '', NULL, NULL, NULL, 0, '2025-11-30 17:57:08'),
(35, 'category', NULL, 'competing_behaviours', 'Competing Behaviours', '', 'https://apbs.org/wp-content/uploads/2024/10/competingbehav_prac.pdf?sfvrsn=2', NULL, NULL, NULL, 0, '2025-12-01 13:50:55'),
(36, 'category', NULL, 'habit_reversal', 'Habit Reversal Training', '', 'https://my.clevelandclinic.org/health/treatments/habit-reversal-training', NULL, NULL, NULL, 0, '2025-12-01 13:53:01'),
(37, 'category', NULL, 'anxiety_management', 'Anxiety Management', '', '', 'AnxietyManagement_1764597265.pdf', NULL, NULL, 0, '2025-12-01 13:54:25'),
(40, 'category', NULL, 'pmr_training', 'Progressive Muscle Relaxation Training', '', 'https://www.cci.health.wa.gov.au/~/media/CCI/Mental-Health-Professionals/Panic/Panic---Information-Sheets/Panic-Information-Sheet---05---Progressive-Muscle-Relaxation.pdf', NULL, NULL, NULL, 0, '2025-12-01 13:58:24'),
(41, 'article', NULL, NULL, 'Tics and Tic Disorders', '', '', 'tics-and-tic-disorders_1764597565.jpg', NULL, NULL, 0, '2025-12-01 13:59:25'),
(43, 'article', NULL, NULL, 'What are tics', '', '', 'What_are_tics_MYU___2__1764598087.pdf', NULL, NULL, 0, '2025-12-01 14:08:07'),
(44, 'article', NULL, NULL, "Tourette's syndrome video", 'For Maria', 'https://www.youtube.com/watch?v=1w8lPOgFxt4', NULL, NULL, NULL, 0, '2025-12-01 14:14:38'),
(45, 'banner', 'video', NULL, 'Deep Breathing', '', 'https://youtube.com/shorts/_S-Xyfp3D3k?si=y6mfYFPP7KrHcJB-', NULL, NULL, NULL, 0, '2025-12-01 14:17:22'),
(46, 'banner', 'video', NULL, 'Treatment', '', 'https://www.youtube.com/watch?v=4_qquZsLbYY', NULL, NULL, NULL, 0, '2025-12-01 14:26:54'),
(48, 'banner', NULL, NULL, 'AAA', '', '', NULL, NULL, NULL, 0, '2025-12-01 16:07:17'),
(50, 'article', NULL, NULL, 'Test', '', '', 'PEEP_1764606108.jpg', NULL, NULL, 0, '2025-12-01 16:21:48');


-- --------------------------------------------------------

-- patient_resources


-- structure for table `patient_resources`

CREATE TABLE IF NOT EXISTS `patient_resources` (
  `Patient_Resource_ID` int(10) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(10) NOT NULL,
  `Resource_ID` int(10) NOT NULL,
  `Sent_By` int(10) NOT NULL COMMENT 'user id of the professional who sent',
  `Skill_Key` varchar(64) NOT NULL DEFAULT '',
  `Sent_At` datetime NOT NULL,
  PRIMARY KEY (`Patient_Resource_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Resource_ID`) REFERENCES `resource_hub`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Sent_By`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- insert data into `patient_resources`

INSERT IGNORE INTO `patient_resources` (`Patient_Resource_ID`, `Patient_ID`, `Resource_ID`, `Sent_By`, `Skill_Key`, `Sent_At`) VALUES
(1, 6, 28, 7, '', '2025-11-30 17:56:47'),
(2, 6, 29, 7, '', '2025-11-30 17:57:08'),
(3, 8, 30, 7, '', '2025-12-01 13:28:27'),
(4, 8, 31, 7, '', '2025-12-01 13:31:28'),
(5, 8, 32, 7, '', '2025-12-01 13:38:11'),
(6, 8, 33, 7, '', '2025-12-01 13:40:47'),
(7, 8, 34, 7, '', '2025-12-01 13:47:15'),
(8, 8, 35, 7, '', '2025-12-01 13:50:55'),
(9, 8, 36, 7, '', '2025-12-01 13:53:01'),
(10, 8, 37, 7, '', '2025-12-01 13:54:25'),
(11, 8, 38, 7, '', '2025-12-01 13:56:35'),
(12, 8, 39, 7, '', '2025-12-01 13:57:33'),
(14, 8, 41, 7, '', '2025-12-01 13:59:25'),
(15, 8, 42, 7, '', '2025-12-01 14:01:46'),
(16, 8, 43, 7, '', '2025-12-01 14:08:07'),
(19, 8, 46, 7, '', '2025-12-01 14:26:54'),
(20, 8, 47, 7, '', '2025-12-01 15:45:01'),
(21, 8, 49, 7, '', '2025-12-01 16:16:09'),
(27, 6, 55, 7, 'competing_behaviours', '2025-12-02 14:10:01'),
(31, 8, 59, 7, 'pmr_training', '2025-12-02 15:40:32'),
(32, 8, 60, 7, '', '2025-12-02 16:16:34');

ALTER TABLE `patient_resources`
  MODIFY `Patient_Resource_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
COMMIT;


-- --------------------------------------------------------

-- patient_resource_assignments

-- structure for table `patient_resource_assignments`

CREATE TABLE IF NOT EXISTS `patient_resource_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patient_professional_link`(`Patient_ID`) ON DELETE CASCADE,
  FOREIGN KEY (`resource_id`) REFERENCES `resource_hub`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- chat_log


-- structure for table `chat_log`

CREATE TABLE IF NOT EXISTS `chat_log` (
  `Chat_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Link_ID` int(11) NOT NULL,
  `Sender_Type` enum('Patient','Professional') NOT NULL,
  `Sender` int(11) DEFAULT NULL,
  `Receiver` int(11) DEFAULT NULL,
  `Chat_Text` text NOT NULL,
  `Chat_Time` datetime NOT NULL,
  `File_Path` varchar(255) DEFAULT NULL,
  `File_Type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Chat_ID`),
  FOREIGN KEY (`Link_ID`) REFERENCES `patient_professional_link`(`Link_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Sender`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Receiver`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- insert data into `chat_log`





-- Trigger to validate roles in 'chat_log' on INSERT

DELIMITER $$
CREATE TRIGGER `trg_chat_log_before_ins` BEFORE INSERT ON `chat_log` FOR EACH ROW BEGIN
  DECLARE role_s VARCHAR(50);
  DECLARE role_r VARCHAR(50);

  SELECT Role INTO role_s FROM user_profile WHERE User_ID = NEW.Sender;
  SELECT Role INTO role_r FROM user_profile WHERE User_ID = NEW.Receiver;

  IF role_s IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sender user not found in user_profile';
  END IF;
  IF role_r IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Receiver user not found in user_profile';
  END IF;
  IF role_s = role_r THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sender and Receiver must have different roles';
  END IF;
END
$$
DELIMITER ;


-- Trigger to validate roles in 'chat_log' on UPDATE

DELIMITER $$
CREATE TRIGGER `trg_chat_log_before_upd` BEFORE UPDATE ON `chat_log` FOR EACH ROW BEGIN
  DECLARE role_s VARCHAR(50);
  DECLARE role_r VARCHAR(50);

  SELECT Role INTO role_s FROM user_profile WHERE User_ID = NEW.Sender;
  SELECT Role INTO role_r FROM user_profile WHERE User_ID = NEW.Receiver;

  IF role_s IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sender user not found in user_profile';
  END IF;
  IF role_r IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Receiver user not found in user_profile';
  END IF;
  IF role_s = role_r THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sender and Receiver must have different roles';
  END IF;
END
$$
DELIMITER ;


-- --------------------------------------------------------

-- password_resets

-- structure for table `password_resets`

CREATE TABLE IF NOT EXISTS `password_resets` (
  `Password_Resets_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) NOT NULL,
  `Token` varchar(255) NOT NULL,
  `Expires` bigint(20) NOT NULL,
  PRIMARY KEY (`Password_Resets_ID`),
  KEY `Email` (`Email`),
  KEY `Token` (`Token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- insert data into `password_resets`

INSERT IGNORE INTO `password_resets` (`Password_Resets_ID`, `Email`, `Token`, `Expires`) VALUES
(1, 'maria.silva@example.com', 'token_abc123', 1760000000),
(2, 'joao.pereira@example.com', 'token_def456', 1760003600),
(3, 'bhawna.panwar@gmail.com', 'b590988ed6b82342a5fe704d8a675cc84deb0a59af09f48b3582ba592f91bb80', 1763598991),
(4, 'bhawna.panwar@gmail.com', '9812d041f50327d67db6ce5f1925f28890e4ffb707e2727c7c7c8a2cc963ebd7', 1764725940),
(5, 'bhawna.panwar@gmail.com', 'a6b609970fc948ec88e8c397588b0680ea0702faefd957957f4468fce87c78d0', 1764725965),
(6, 'julia.vidal@gmail.com', '82b7476f6c57f404f75c9518277e8a503ded2f247a0f89888be4bdc0964e3178', 1764726084);


-- --------------------------------------------------------

-- notifications

-- structure for table `notifications`

CREATE TABLE IF NOT EXISTS `notifications` (
  `Notification_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Message` text DEFAULT NULL,
  `Type` varchar(50) DEFAULT 'system',
  `Is_Read` tinyint(1) DEFAULT 0,
  `Created_At` datetime DEFAULT current_timestamp(),
  `Read_At` datetime DEFAULT NULL,
  PRIMARY KEY (`Notification_ID`),
  INDEX `idx_user_id` (`User_ID`),
  INDEX `idx_is_read` (`Is_Read`),
  INDEX `idx_created` (`Created_At`),
  FOREIGN KEY (`User_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- insert sample data into `notifications`

INSERT IGNORE INTO `notifications` (`Notification_ID`, `User_ID`, `Title`, `Message`, `Type`, `Is_Read`, `Created_At`) VALUES
(1, 1, 'Welcome to TicTracker!', 'Thank you for joining. Start by logging your first tic entry.', 'system', 0, '2025-11-01 10:00:00'),
(2, 1, 'Medication Reminder', 'Don\'t forget to take your Clonidine at 8:00 PM.', 'medication', 1, '2025-11-13 19:45:00'),
(3, 11, 'New Message', 'You have a new message from Dr. Rodrigo Neves.', 'message', 0, '2025-12-02 14:30:00'),
(4, 12, 'Diary Reminder', 'You haven\'t logged your emotional diary today.', 'diary', 0, '2025-12-03 20:00:00'),
(5, 11, 'Connection Request', 'Dr. Laura Mendes wants to connect with you.', 'connection', 0, '2025-12-02 09:15:00');


-- --------------------------------------------------------

-- ygtss_results

-- structure for table `ygtss_results`
-- YGTSS - Yale Global Tic Severity Scale Results

CREATE TABLE IF NOT EXISTS `ygtss_results` (
    `YGTSS_ID` INT AUTO_INCREMENT PRIMARY KEY,
    `Patient_ID` INT NOT NULL,
    `Submission_Date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- A2 Scores (0-5 scale)
    `Number_Motor` INT DEFAULT 0,
    `Number_Vocal` INT DEFAULT 0,
    `Frequency_Motor` INT DEFAULT 0,
    `Frequency_Vocal` INT DEFAULT 0,
    `Intensity_Motor` INT DEFAULT 0,
    `Intensity_Vocal` INT DEFAULT 0,
    `Complexity_Motor` INT DEFAULT 0,
    `Complexity_Vocal` INT DEFAULT 0,
    `Interference_Motor` INT DEFAULT 0,
    `Interference_Vocal` INT DEFAULT 0,
    
    -- Overall Impairment (0-50 scale)
    `Overall_Impairment` INT DEFAULT 0,
    
    -- Calculated Totals
    `Motor_Tic_Total` INT DEFAULT 0,
    `Vocal_Tic_Total` INT DEFAULT 0,
    `Total_Tic_Score` INT DEFAULT 0,
    `Global_Severity` INT DEFAULT 0,
    
    -- Final Information
    `First_Tic_Age` INT DEFAULT NULL,
    `Bother_Age` INT DEFAULT NULL,
    `Treatment_Age` INT DEFAULT NULL,
    
    -- A1 Data (stored as JSON for flexibility)
    `A1_Symptoms` TEXT DEFAULT NULL,
    `A1_Simultaneous_Tics` ENUM('Yes', 'No') DEFAULT NULL,
    `A1_Simultaneous_Desc` TEXT DEFAULT NULL,
    `A1_Multiple_Groups` ENUM('Yes', 'No') DEFAULT NULL,
    `A1_Multiple_Groups_Desc` TEXT DEFAULT NULL,
    
    INDEX `idx_ygtss_patient` (`Patient_ID`),
    INDEX `idx_ygtss_date` (`Submission_Date`),
    FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;







