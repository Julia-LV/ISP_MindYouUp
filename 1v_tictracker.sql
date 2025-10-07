-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01-Out-2025 às 20:02
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `tictracker`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `emotional_diary`
--

CREATE TABLE `emotional_diary` (
  `Emotional_Diary_ID` int(11) NOT NULL,
  `Patient_ID` int(11) NOT NULL,
  `Ocurrence` datetime NOT NULL,
  `Stress` int(10) NOT NULL,
  `Anxiety` int(10) NOT NULL,
  `Sleep` int(10) NOT NULL,
  `Notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `patient_profile`
--

CREATE TABLE `patient_profile` (
  `Patient_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `E-mail` varchar(50) NOT NULL,
  `Password` varchar(50) NOT NULL,
  `Treatment_Type` varchar(13) NOT NULL,
  `Self-reported` tinyint(1) NOT NULL,
  `Professional_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `professional_notes`
--

CREATE TABLE `professional_notes` (
  `Note_ID` int(11) NOT NULL,
  `Professional_ID` int(11) NOT NULL,
  `Note_Text` text NOT NULL,
  `Appointment` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `professional_profile`
--

CREATE TABLE `professional_profile` (
  `Professional_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Surname` varchar(50) NOT NULL,
  `E-mail` varchar(50) NOT NULL,
  `Password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `resource_hub`
--

CREATE TABLE `resource_hub` (
  `Resource_ID` int(11) NOT NULL,
  `Patient_ID` int(11) NOT NULL,
  `Professional_ID` int(11) NOT NULL,
  `Resource_Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tic_log`
--

CREATE TABLE `tic_log` (
  `Tic_ID` int(11) NOT NULL,
  `Patient_ID` int(11) NOT NULL,
  `Type_Description` varchar(50) NOT NULL,
  `Duration` int(11) NOT NULL,
  `Intensity` int(11) NOT NULL,
  `Self-reported` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `track_medicine`
--

CREATE TABLE `track_medicine` (
  `Medication_ID` int(11) NOT NULL,
  `Patient_ID` int(11) NOT NULL,
  `Medication_Name` varchar(50) NOT NULL,
  `Status` tinyint(1) NOT NULL,
  `Dosage` int(11) NOT NULL,
  `Remind` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `emotional_diary`
--
ALTER TABLE `emotional_diary`
  ADD PRIMARY KEY (`Emotional_Diary_ID`);

--
-- Índices para tabela `patient_profile`
--
ALTER TABLE `patient_profile`
  ADD PRIMARY KEY (`Patient_ID`);

--
-- Índices para tabela `professional_notes`
--
ALTER TABLE `professional_notes`
  ADD PRIMARY KEY (`Note_ID`);

--
-- Índices para tabela `resource_hub`
--
ALTER TABLE `resource_hub`
  ADD PRIMARY KEY (`Resource_ID`);

--
-- Índices para tabela `tic_log`
--
ALTER TABLE `tic_log`
  ADD PRIMARY KEY (`Tic_ID`);

--
-- Índices para tabela `track_medicine`
--
ALTER TABLE `track_medicine`
  ADD PRIMARY KEY (`Medication_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
