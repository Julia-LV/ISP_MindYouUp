-- phpMyAdmin SQL Dump (reorganized)
-- Database: `tictracker_v6`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Table structure and inserts
-- --------------------------------------------------------

-- 1. user_profile
CREATE TABLE `user_profile` (
  `User_ID` int(11) NOT NULL AUTO_INCREMENT,
  `User_Image` varchar(250) NOT NULL COMMENT 'meter link na imagem',
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(250) NOT NULL,
  `Age` int(3) NOT NULL,
  `Role` enum('Professional','Patient') NOT NULL,
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_profile` (`User_ID`,`User_Image`,`First_Name`,`Last_Name`,`E-mail`,`Password`,`Age`,`Role`) VALUES
(1,  '/images/users/1.jpg', 'Maria',  'Silva',   'maria.silva@example.com',   'pass_placeholder_1', 28, 'Patient'),
(2,  '/images/users/2.jpg', 'João',   'Pereira', 'joao.pereira@example.com',  'pass_placeholder_2', 34, 'Patient'),
(3,  '/images/users/3.jpg', 'Ana',    'Costa',   'ana.costa@example.com',     'pass_placeholder_3', 19, 'Patient'),
(4,  '/images/users/4.jpg', 'Lucas',  'Oliveira','lucas.oliveira@example.com','pass_placeholder_4', 42, 'Patient'),
(5,  '/images/users/5.jpg', 'Sofia',  'Martins', 'sofia.martins@example.com', 'pass_placeholder_5', 16, 'Patient'),
(6,  '/images/users/6.jpg', 'Ana',    'Almeida', 'ana.almeida@clinic.example', 'pass_placeholder_6', 45, 'Professional'),
(7,  '/images/users/7.jpg', 'Roberto','Sousa',   'roberto.sousa@clinic.example','pass_placeholder_7', 50, 'Professional'),
(8,  '/images/users/8.jpg', 'Laura',  'Mendes',  'laura.mendes@clinic.example', 'pass_placeholder_8', 38, 'Professional'),
(9,  '/images/users/9.jpg', 'Helena', 'Ribeiro', 'helena.ribeiro@clinic.example','pass_placeholder_9', 41, 'Professional'),
(10, '/images/users/10.jpg','Miguel', 'Santos',  'miguel.santos@clinic.example',  'pass_placeholder_10',37, 'Professional');


-- 2. patient_profile
CREATE TABLE `patient_profile` (
  `User_ID` int(11) NOT NULL,
  `Patient_Status` enum('Drop_Out','Followed','Discharged') NOT NULL,
  `Treatment_Type` enum('Psychological','Medical','Both') NOT NULL,
  `Start_Date` date NOT NULL,
  `Link_ID` int(11) NOT NULL,
  PRIMARY KEY (`User_ID`),
  FOREIGN KEY (`Link_ID`) REFERENCES `patient_professional_link`(`Link_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`User_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `patient_profile` (`User_ID`,`Patient_Status`,`Treatment_Type`,`Start_Date`,`Link_ID`) VALUES
(1, 'Followed',  'Both', '2025-02-10', 1),
(2, 'Drop_Out',  'Psychological', '2024-11-05', 2),
(3, 'Followed',  'Medical', '2025-06-01', 3),
(4, 'Discharged','Both', '2023-09-20', 4),
(5, 'Followed',  'Psychological', '2025-08-15', 5);


-- 3. professional_profile
CREATE TABLE `professional_profile` (
  `User_ID` int(11) NOT NULL,
  `Link_ID` int(11) NOT NULL,
  PRIMARY KEY (`User_ID`),
  FOREIGN KEY (`Link_ID`) REFERENCES `patient_professional_link`(`Link_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`User_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `professional_profile` (`User_ID`,`Link_ID`) VALUES
(6, 1),
(7, 2),
(8, 3),
(9, 4),
(10,5);


-- 4. patient_professional_link
CREATE TABLE `patient_professional_link` (
  `Link_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Professional_ID` int(11) NOT NULL,
  `Assigned_Date` date NOT NULL,
  PRIMARY KEY (`Link_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Professional_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `patient_professional_link` (`Link_ID`,`Patient_ID`,`Professional_ID`,`Assigned_Date`) VALUES
(1, 1, 6, '2025-02-10'),
(2, 2, 7, '2024-11-05'),
(3, 3, 8, '2025-06-01'),
(4, 4, 9, '2023-09-20'),
(5, 5, 10,'2025-08-15');


-- 5. emotional_diary
CREATE TABLE `emotional_diary` (
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

INSERT INTO `emotional_diary` (`Patient_ID`,`Occurrence`,`Emotion`,`Stress`,`Anxiety`,`Sleep`,`Notes`) VALUES
(1, '2025-10-12 08:30:00', 'Frustration', 6, 5, 6, 'Woken up tense after a vivid dream. Practiced breathing for 10 minutes.'),
(1, '2025-11-01 20:10:00', 'Hopeful', 3, 2, 7, 'Good session today; felt motivated to try exercises.'),
(2, '2025-09-28 21:15:00', 'Relief', 3, 2, 7, 'Medication adjustment helped; slept better.'),
(3, '2025-07-05 14:45:00', 'Anxious', 8, 8, 4, 'Felt panic when leaving home; noted heartbeat and sweating.'),
(4, '2025-04-02 10:00:00', 'Calm', 2, 1, 8, 'Finished program and feeling improved; family supportive.'),
(5, '2025-11-10 09:00:00', 'Sad', 6, 5, 5, 'Struggled with school stress today; practiced grounding.');


-- 6. tic_log
CREATE TABLE `tic_log` (
  `Tic_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Type_Description` varchar(50) NOT NULL,
  `Muscle_Group` varchar(50) NOT NULL,
  `Duration` varchar(50) NOT NULL,
  `Intensity` int(11) NOT NULL,
  `Describe_Text` text NOT NULL,
  `Self-reported` tinyint(1) NOT NULL,
  PRIMARY KEY (`Tic_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tic_log` (`Patient_ID`,`Type_Description`,`Muscle_Group`,`Duration`,`Intensity`,`Describe_Text`,`Self-reported`) VALUES
(1, 'Motor Tic', 'Face', '5s', 3, 'Blinking rapidly during conversation.', 1),
(2, 'Vocal Tic', 'Throat', '10s', 2, 'Sudden throat clearing in public.', 1),
(3, 'Motor Tic', 'Shoulder','7s', 4, 'Shrugging shoulder repeatedly when stressed.', 0),
(1, 'Motor Tic', 'Hand', '3s', 2, 'Tapping fingers on desk rhythmically.', 1),
(4, 'Vocal Tic', 'Mouth', '5s', 3, 'Making unusual noises unintentionally.', 0),
(2, 'Motor Tic', 'Face', '8s', 5, 'Rapid eye blinking and lip movement.', 1),
(3, 'Vocal Tic', 'Throat','6s', 3, 'Occasional grunting during intense focus.', 0),
(5, 'Motor Tic', 'Neck', '4s', 2, 'Quick head movements when anxious.', 1),
(1, 'Vocal Tic', 'Voice', '12s', 4, 'Repeating words unconsciously during stress.', 1),
(4, 'Motor Tic', 'Arm', '3s', 2, 'Brief twitching in right arm while writing.', 0);


-- 7. track_medication
CREATE TABLE `track_medication` (
  `Track_Medication_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Patient_ID` int(11) NOT NULL,
  `Medication_Name` varchar(50) NOT NULL,
  `Medication_Time` datetime NOT NULL,
  `Medication_Status` tinyint(1) NOT NULL,
  PRIMARY KEY (`Track_Medication_ID`),
  FOREIGN KEY (`Patient_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `track_medication` (`Patient_ID`,`Medication_Name`,`Medication_Time`,`Medication_Status`) VALUES
(1, 'Clonidine',  '2025-11-13 08:00:00', 1),
(2, 'Aripiprazole','2025-11-13 09:00:00', 1),
(3, 'Risperidone','2025-11-13 08:30:00', 0),
(1, 'Clonidine',  '2025-11-13 20:00:00', 0),
(4, 'Guanfacine', '2025-11-13 07:45:00', 1),
(2, 'Aripiprazole','2025-11-13 21:00:00', 0),
(3, 'Risperidone','2025-11-13 19:00:00', 1),
(5, 'Clonazepam','2025-11-13 08:15:00', 1),
(1, 'Clonidine',  '2025-11-14 08:00:00', 1),
(4, 'Guanfacine', '2025-11-14 20:00:00', 0);


-- 8. professional_notes
CREATE TABLE `professional_notes` (
  `Note_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Professional_ID` int(11) NOT NULL,
  `Note_Title` varchar(250) NOT NULL,
  `Note_Text` text NOT NULL,
  PRIMARY KEY (`Note_ID`),
  FOREIGN KEY (`Professional_ID`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `professional_notes` (`Professional_ID`,`Note_Title`,`Note_Text`) VALUES
(6, 'Initial Consultation', 'Discussed goals and challenges with the client. Agreed weekly check-ins.'),
(7, 'Follow-up Session', 'Reviewed progress and adjusted the action plan accordingly.'),
(8, 'Assessment Results', 'Client showed improvement in stress management after med change.'),
(6, 'Therapy Notes', 'Introduced mindfulness and breathing strategies.'),
(9, 'Meeting Summary', 'Outlined next steps for vocational support.'),
(7, 'Client Feedback', 'Client reported better sleep with new routine.'),
(8, 'Progress Evaluation', 'Improvements in social confidence noted.'),
(10,'Observation Notes', 'Observed interactions in group session; positive engagement.'),
(6, 'Plan Update', 'Updated treatment plan to include CBT exercises.'),
(7, 'Session Reflection', 'Client reflected on personal achievements and challenges.');


-- 9. resource_hub
CREATE TABLE `resource_hub` (
  `Resource_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Link_ID` int(11) NOT NULL COMMENT 'ID of the patient_professional_link',
  `Resource_PDF` blob NOT NULL,
  PRIMARY KEY (`Resource_ID`),
  FOREIGN KEY (`Link_ID`) REFERENCES `patient_professional_link`(`Link_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `resource_hub` (`Link_ID`,`Resource_PDF`) VALUES
(1, x'00'),
(2, x'00'),
(3, x'00'),
(4, x'00'),
(5, x'00');


-- 10. chat_log
CREATE TABLE `chat_log` (
  `Chat_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Sender` int(11) NOT NULL,
  `Receiver` int(11) NOT NULL,
  `Chat_Text` text NOT NULL,
  `Chat_Time` datetime NOT NULL,
  PRIMARY KEY (`Chat_ID`),
  FOREIGN KEY (`Sender`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`Receiver`) REFERENCES `user_profile`(`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `chat_log` (`Sender`,`Receiver`,`Chat_Text`,`Chat_Time`) VALUES
(1, 6, 'Olá Dra. Ana, tenho tido mais tiques esta semana. Posso agendar uma breve chamada?', '2025-10-10 09:12:00'),
(6, 1, 'Olá Maria — claro. Tenho disponibilidade quinta-feira às 11h. Serve?', '2025-10-10 09:20:00'),
(2, 7, 'Bom dia Dr. Roberto, a medicação parece funcionar, mas tenho problemas para dormir.', '2025-09-29 08:05:00'),
(7, 2, 'Obrigado por avisar. Vamos rever a dose na próxima consulta.', '2025-09-29 08:18:00'),
(3, 8, 'Senti ansiedade ontem à noite; procurei as técnicas combinadas.', '2025-11-01 21:00:00'),
(8, 3, 'Bom trabalho. Continua o plano e fala comigo se piorar.', '2025-11-01 21:10:00'),
(5,10, 'Olá Miguel, tenho dúvida sobre o horário da medicação noturna.', '2025-11-12 07:50:00'),
(10,5, 'Podemos ajustar. Marca 10 minutos para falarmos hoje.', '2025-11-12 08:05:00'),
(4,9, 'Dr. Helena, já terminei o programa e queria agendar encerramento.', '2025-04-02 10:15:00'),
(9,4, 'Parabéns pelos progressos. Vou marcar a última sessão.', '2025-04-02 10:30:00');


-- 11. password_resets
CREATE TABLE `password_resets` (
  `Password_Resets_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) NOT NULL,
  `Token` varchar(255) NOT NULL,
  `Expires` bigint(20) NOT NULL,
  PRIMARY KEY (`Password_Resets_ID`),
  KEY `Email` (`Email`),
  KEY `Token` (`Token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `password_resets` (`E-mail`,`Token`,`Expires`) VALUES
('maria.silva@example.com','token_abc123', 1760000000),
('joao.pereira@example.com','token_def456', 1760003600);


-- --------------------------------------------------------
-- Data inserts (keep your original inserts, unchanged)
-- --------------------------------------------------------
-- [Include all your INSERT statements here as in your dump]

COMMIT;

-- ================================================
-- Triggers: enforce role constraints (chat_log & patient_professional_link)
-- Added by automation to ensure Sender/Receiver have different roles
-- and Patient/Professional IDs have the correct roles.
-- Run as part of the dump import: these are safe to include here.
-- ================================================

DELIMITER $$

DROP TRIGGER IF EXISTS trg_chat_log_before_ins$$
DROP TRIGGER IF EXISTS trg_chat_log_before_upd$$
DROP TRIGGER IF EXISTS trg_ppl_before_ins$$
DROP TRIGGER IF EXISTS trg_ppl_before_upd$$

CREATE TRIGGER trg_chat_log_before_ins
BEFORE INSERT ON chat_log
FOR EACH ROW
BEGIN
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
END$$

CREATE TRIGGER trg_chat_log_before_upd
BEFORE UPDATE ON chat_log
FOR EACH ROW
BEGIN
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
END$$

CREATE TRIGGER trg_ppl_before_ins
BEFORE INSERT ON patient_professional_link
FOR EACH ROW
BEGIN
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
END$$

CREATE TRIGGER trg_ppl_before_upd
BEFORE UPDATE ON patient_professional_link
FOR EACH ROW
BEGIN
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
END$$

DELIMITER ;

