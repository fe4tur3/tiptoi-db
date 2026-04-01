-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 01. Apr 2026 um 10:27
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `tiptoi`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buch`
--

CREATE TABLE `buch` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `seitenanzahl` int(11) DEFAULT NULL,
  `ausgeliehen` tinyint(1) NOT NULL,
  `ausgeliehen_von` enum('privat','Bücherei') DEFAULT NULL,
  `vielfaeltigkeit` tinyint(4) DEFAULT NULL,
  `bewertung_eltern` enum('super','ganz gut','okay','nicht so gut','Das ist echt nix','falsche Altersgruppe') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stammdaten der TipToi-Medien';

--
-- Daten für Tabelle `buch`
--

INSERT INTO `buch` (`id`, `name`, `seitenanzahl`, `ausgeliehen`, `ausgeliehen_von`, `vielfaeltigkeit`, `bewertung_eltern`) VALUES
(1, 'WWW Tiere', 24, 0, NULL, 8, 'super'),
(2, 'Wieso Weshalb Warum - Feuerwehr', 24, 1, 'Bücherei', 6, 'ganz gut'),
(3, 'Weltatlas', 48, 0, NULL, 9, 'super');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buch_thema`
--

CREATE TABLE `buch_thema` (
  `buch_id` int(11) NOT NULL,
  `thema_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Hilfstabelle zwischen Medien und Themen';

--
-- Daten für Tabelle `buch_thema`
--

INSERT INTO `buch_thema` (`buch_id`, `thema_id`) VALUES
(1, 2),
(1, 5),
(2, 1),
(3, 12);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kind_bewertung`
--

CREATE TABLE `kind_bewertung` (
  `id` int(11) NOT NULL,
  `buch_id` int(11) NOT NULL,
  `bewertung` enum('1 - Nochmal! Nochmal!','2 - Ich mag das!','3 - Ganz okay','4 - Na ja...','5 - Nicht so meins') DEFAULT NULL,
  `zeitstempel` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kind_bewertung`
--

INSERT INTO `kind_bewertung` (`id`, `buch_id`, `bewertung`, `zeitstempel`) VALUES
(1, 1, '1 - Nochmal! Nochmal!', '2026-04-01 10:26:50'),
(2, 2, '3 - Ganz okay', '2026-04-01 10:26:50'),
(3, 3, '2 - Ich mag das!', '2026-04-01 10:26:50');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `thema`
--

CREATE TABLE `thema` (
  `ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Themenfelder der TipToi-Medien';

--
-- Daten für Tabelle `thema`
--

INSERT INTO `thema` (`ID`, `name`) VALUES
(1, 'Einsatzteams'),
(2, 'Tiere'),
(3, 'Alltag'),
(4, 'Fahrzeuge'),
(5, 'Wissenschaften'),
(6, 'Lesen lernen'),
(7, 'Fantasy'),
(8, 'Geschichte'),
(9, 'Musik'),
(10, 'Sport'),
(11, 'Kochen & Basteln'),
(12, 'Länder & Kulturen'),
(13, 'Zahlen & Rechnen');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `buch`
--
ALTER TABLE `buch`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indizes für die Tabelle `buch_thema`
--
ALTER TABLE `buch_thema`
  ADD PRIMARY KEY (`buch_id`,`thema_id`),
  ADD KEY `thema_id` (`thema_id`);

--
-- Indizes für die Tabelle `kind_bewertung`
--
ALTER TABLE `kind_bewertung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buch_id` (`buch_id`);

--
-- Indizes für die Tabelle `thema`
--
ALTER TABLE `thema`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `buch`
--
ALTER TABLE `buch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `kind_bewertung`
--
ALTER TABLE `kind_bewertung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `thema`
--
ALTER TABLE `thema`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `buch_thema`
--
ALTER TABLE `buch_thema`
  ADD CONSTRAINT `buch_thema_ibfk_1` FOREIGN KEY (`buch_id`) REFERENCES `buch` (`id`),
  ADD CONSTRAINT `buch_thema_ibfk_2` FOREIGN KEY (`thema_id`) REFERENCES `thema` (`ID`);

--
-- Constraints der Tabelle `kind_bewertung`
--
ALTER TABLE `kind_bewertung`
  ADD CONSTRAINT `kind_bewertung_ibfk_1` FOREIGN KEY (`buch_id`) REFERENCES `buch` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
