-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 05:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sharmino_donorhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `beneficiaries`
--

CREATE TABLE `beneficiaries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `required_support` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `beneficiaries`
--

INSERT INTO `beneficiaries` (`id`, `name`, `email`, `phone`, `address`, `required_support`, `created_at`, `user_id`, `status`) VALUES
(1, 'johnny', 'johnny@gmail.com', '021457454', '4/78 los angle,usa', 'Fresh Water', '2025-08-13 06:32:32', 11, 'Active'),
(2, 'john', 'john@gmail.com', '01545858555', '4/78 los angle,usa', 'Fresh Water', '2025-08-13 06:32:43', 12, '0'),
(5, 'Charlie Brown', 'charlie.b@email.com', '0148586356', '4/78 New York,, USA', 'Emergency relief', '2025-08-20 04:02:47', 7, 'Inactive'),
(6, 'Charlie Brown', 'charlie.b@email.com', '0148586356', '4/78 New York,, USA', 'Emergency relief', '2025-08-20 04:20:27', 7, 'Inactive'),
(7, 'john', 'john@gmail.com', '021457454', '4/78 los angle,usa', 'Aid', '2025-08-24 06:07:44', 12, 'Active'),
(8, 'Lima Akter', 'lima@gmail.com', '01325875456', '32/A LA, USA', 'Health Checkup Camp', '2025-09-02 02:39:26', 17, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `descriptions` varchar(400) NOT NULL,
  `goal_amount` int(11) DEFAULT NULL,
  `total_raised` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_date` date DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `event_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `name`, `descriptions`, `goal_amount`, `total_raised`, `start_date`, `end_date`, `status`, `event_id`, `file_path`) VALUES
(1, 'Charity Run 2025', 'A marathon to raise funds for community development.', 20000, 30000, '2025-08-30 03:19:24', '2025-08-31', 'Active', 1, 'uploads/cause-3.jpg'),
(4, 'Emergency Relief', 'Immediate assistance for families affected by natural disasters.', 30000, 25000, '2025-08-27 04:09:49', '2025-09-09', 'Active', 4, 'uploads/cause-3.jpg'),
(5, 'Health Checkup Camp', 'Free medical checkups and awareness sessions for the community.', 10000, 8200, '2025-09-02 05:41:51', '2025-11-01', 'Active', 5, 'uploads/cause-3.jpg'),
(6, 'Fresh Water ', 'Freshwater camping area provides shady, sheltered sites set among scribbly gum woodland about 500m inland from the beach.\r\n', 20000, 20000, '2025-08-30 03:16:19', '2025-09-12', 'Active', 1, 'uploads/cause-3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `campaign_management`
--

CREATE TABLE `campaign_management` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `fund_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `beneficiary_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaign_management`
--

INSERT INTO `campaign_management` (`id`, `name`, `fund_id`, `user_id`, `beneficiary_id`) VALUES
(1, 'programmer diva\r\n', 3, 4, 2),
(2, '\r\nAlice Johnson\r\n', 1, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_id` int(11) DEFAULT NULL,
  `fund_id` int(11) DEFAULT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `pledge_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `name`, `amount`, `date`, `payment_id`, `fund_id`, `donor_id`, `pledge_id`, `campaign_id`, `payment_reference`, `status`) VALUES
(3, 'Rayaan Mohammad', 5000, '2025-08-29 16:27:33', 2, 5, 14, NULL, 6, NULL, 'verified'),
(5, 'Bob Smith', 6000, '2025-08-29 16:28:01', 5, 4, 11, NULL, 5, NULL, 'verified'),
(11, 'Ayaan Mohammad', 25000, '2025-08-29 22:50:46', 4, 3, 9, NULL, 4, NULL, 'verified'),
(12, 'Ayaan Mohammad', 10000, '2025-08-31 03:41:32', 2, 1, 9, NULL, 1, NULL, 'verified'),
(13, 'Ayaan Mohammad', 10000, '2025-08-30 03:19:24', 2, 1, 9, NULL, 1, NULL, 'verified'),
(14, 'Ayaan Mohammad', 5000, '2025-08-29 23:08:52', 5, 5, 9, NULL, 6, NULL, 'verified'),
(15, 'Ayaan Mohammad', 5000, '2025-08-29 23:16:19', 5, 5, 9, NULL, 6, NULL, 'verified'),
(16, 'Ayaan Mohammad', 1000, '2025-09-01 05:57:13', 2, 4, 9, NULL, 5, NULL, 'verified'),
(17, 'Rayaan Mohammad', 200, '2025-09-02 04:34:29', 2, 4, 14, NULL, 5, NULL, 'verified'),
(18, 'Ayaan Mohammad', 500, '2025-09-02 05:42:30', 2, 4, 9, NULL, 5, NULL, 'verified');

--
-- Triggers `donations`
--
DELIMITER $$
CREATE TRIGGER `update_campaign_total_on_delete` AFTER DELETE ON `donations` FOR EACH ROW BEGIN
    UPDATE `campaigns`
    SET `total_raised` = `total_raised` - OLD.amount
    WHERE `id` = OLD.campaign_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_campaign_total_raised_after_insert` AFTER INSERT ON `donations` FOR EACH ROW BEGIN
    UPDATE campaigns
    SET total_raised = (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE campaign_id = NEW.campaign_id)
    WHERE id = NEW.campaign_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pledge_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`id`, `name`, `contact`, `type`, `user_id`, `pledge_id`) VALUES
(9, 'Ayaan Mohammad', 'ayaan@gmail.com', 'Individual', 9, NULL),
(11, 'Bob Smith\r\n', 'bob.s@email.com', 'Individual', 6, 2),
(14, 'Rayaan Mohammad', 'rayaan@gmail.com', 'Individual', 14, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `descriptions` varchar(255) NOT NULL,
  `date` date DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `location`, `descriptions`, `date`, `image_url`, `status`) VALUES
(1, 'Charity Run 2025', 'Central Park, New York', 'Whether you\'re keen to walk, run, cycle or swim take a look below and sign up today to fundraise for Save the Children. We\'ll support you every step of the', '2025-09-30', 'uploads/event3.jpg', 'Active'),
(3, 'School Fundraising Gala', 'Hilton Hotel, Chicago', 'A high school fundraising gala serves as a platform for high schools to gather funds and resources while unifying the community in support of a common cause ...\r\n', '2025-09-20', 'uploads/event.png', 'Active'),
(4, 'Emergency Relife  ', ' Gaza, Palestine', 'Emergency relief means providing immediate assistance to the victims of conflicts or disaster situations. For more than 50 years, the World Food Programme ...', '2025-09-30', 'uploads/event-4.jpg', 'Active'),
(5, 'Health Checkup Camp', 'Community Center, Dallas', 'Held from 9am to 2pm, the event offered free healthcare services including specialist doctor consultation and blood sugar Body Mass Index (BMI) ', '2025-09-27', 'uploads/event2.jpg\r\n', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `funds`
--

CREATE TABLE `funds` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `collected_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `funds`
--

INSERT INTO `funds` (`id`, `name`, `status`, `collected_amount`) VALUES
(1, 'Charity Run 2025', 'Active', 10000),
(2, 'Food Donation Drive', 'Inactive', 200000),
(3, 'Emergency Relief', 'Active', 50000),
(4, 'Health Checkup Camp', 'Active', 150000),
(5, 'Fresh Water ', 'Active', 100000);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `type`) VALUES
(1, 'Credit Card'),
(2, 'PayPal'),
(3, 'Bank Transfer'),
(4, 'Check'),
(5, 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `pledges`
--

CREATE TABLE `pledges` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pledge_amount` int(11) DEFAULT NULL,
  `pledge_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `donor_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pledges`
--

INSERT INTO `pledges` (`id`, `name`, `pledge_amount`, `pledge_date`, `status`, `donor_id`, `campaign_id`) VALUES
(2, 'Pledge for Education', 500, '2025-08-16 07:19:06', 'Pending', 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(5, 'beneficiary'),
(4, 'campaign_manager'),
(2, 'donor'),
(3, 'volunteer');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `amount` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `status`, `date`, `amount`, `payment_id`, `donor_id`, `campaign_id`) VALUES
(3, 'complete', '2025-08-16 07:25:46', 500, 2, 11, 4),
(13, 'complete', '2025-08-30 03:19:24', 10000, 2, 9, 1),
(14, 'complete', '2025-08-29 23:08:52', 5000, 5, 9, 6),
(15, 'complete', '2025-08-29 23:16:19', 5000, 5, 9, 6),
(16, 'complete', '2025-09-01 05:57:13', 1000, 2, 9, 5),
(17, 'complete', '2025-09-02 04:34:29', 200, 2, 14, 5),
(18, 'complete', '2025-09-02 05:42:30', 500, 2, 9, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `requested_role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role_id`, `created_at`, `requested_role_id`) VALUES
(3, 'Sharmin', 'Akter', 'sharminakter@gmail.com', '$2y$10$uzHD1Z4lXppoB/Rck.pnIOImOQryJpFIu4XgPjVfmGrF0qHqJ4/w6', 1, '2025-08-15 18:16:30', NULL),
(4, 'programmer', 'diva', 'programmer@gmail.com', '$2y$10$FDLcmWXz1IdOum52bD1OiO6SFVJ97Z2g1WgZlQsAdogeAJ4Lcg5Pe', 4, '2025-08-15 18:18:11', NULL),
(5, 'Alice', 'Johnson', 'alice.j@email.com', 'password123', 3, '2025-08-16 06:57:21', NULL),
(6, 'Bob', 'Smith', 'bob.s@email.com', 'password456', 3, '2025-08-16 06:57:21', NULL),
(7, 'Charlie', 'Brown', 'charlie.b@email.com', 'password789', 5, '2025-08-16 06:57:21', NULL),
(8, 'Diana', 'Miller', 'diana.m@email.com', 'passwordabc', 3, '2025-08-16 06:57:21', NULL),
(9, 'Ayaan', 'Mohammad', 'ayaan@gmail.com', '$2y$10$JryyXYRfPOi50gP9asod3uJDcim3CpJMkKfz36K54QeH74iJojE2O', 2, '2025-08-20 03:18:34', NULL),
(11, 'johnny', 'jonh', 'johnny@gmail.com', '$2y$10$N.PTawvJGtDua0OCtc6GDO2gnqqNpjh6Y8lME0bBYo7unLwOxg8bG', 5, '2025-08-20 03:53:04', NULL),
(12, 'john', 'doe', 'john@gmail.com', '$2y$10$wWbdTZSK1gJqQ0sK4aXDTeFlbo8jnRGOUmG2pWBr/1dllyHjAc2Ei', 5, '2025-08-20 03:53:57', NULL),
(13, 'Charlie', 'Brown', 'charlie@email.com', '$2y$10$uGnCIrpDti80H8ei5iEBkeEPuEk9eWMGPYp4CwG1I4E.4yAIsrDe2', 5, '2025-08-25 04:25:16', NULL),
(14, 'Rayaan', 'Mohammad', 'rayaan@gmail.com', '$2y$10$SFOOq4rXStYELlPKeBoy7Orhm.RrNB2TrfpFc1eSbiFCR8uydV/5.', 2, '2025-08-27 04:01:51', NULL),
(16, 'Alisha', 'Mohammad', 'alisha@gmail.com', '$2y$10$Dcpo/TfT3oHbsVT3UqtqYu6Pk9gtTWJHJPOTNqfWblrwpVAxVHEWO', 3, '2025-08-30 05:39:11', NULL),
(17, 'Lima', 'Akter', 'lima@gmail.com', '$2y$10$jbDGTbj.qj/sgj59kpMBkuihDXdxO1gK9FylD0Blxh13esc7HJb7m', 5, '2025-08-31 02:56:37', NULL),
(18, 'Asha', 'Akter', 'asha@gmail.com', '$2y$10$vbrhL6Vh0DOL35zPHmq2vOVmqWAtoorz35E8/lL6tUFjzuxK/GZKm', 2, '2025-09-02 04:26:36', 3);

-- --------------------------------------------------------

--
-- Table structure for table `volunteer`
--

CREATE TABLE `volunteer` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `task` varchar(255) DEFAULT NULL,
  `availability_status` enum('Available','Unavailable') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteer`
--

INSERT INTO `volunteer` (`id`, `name`, `contact`, `task`, `availability_status`, `created_at`, `event_id`, `user_id`) VALUES
(3, 'Alice Johnson', 'alice.j@email.com', 'Event Management', 'Unavailable', '2025-08-16 06:59:59', 1, 5),
(5, 'Charlie Brown', 'charlie.b@email.com', 'Community Outreach', 'Available', '2025-08-16 06:59:59', 1, 5),
(6, 'Diana Miller', 'diana.m@email.com', 'Fundraising', 'Available', '2025-08-16 06:59:59', 3, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `campaign_management`
--
ALTER TABLE `campaign_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fund_id` (`fund_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `beneficiary_id` (`beneficiary_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `fund_id` (`fund_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `pledge_id` (`pledge_id`),
  ADD KEY `fk_campaign` (`campaign_id`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_donors_pledges` (`pledge_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `funds`
--
ALTER TABLE `funds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pledges`
--
ALTER TABLE `pledges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `fk_pledges_donors` (`donor_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `volunteer`
--
ALTER TABLE `volunteer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `campaign_management`
--
ALTER TABLE `campaign_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `funds`
--
ALTER TABLE `funds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pledges`
--
ALTER TABLE `pledges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `volunteer`
--
ALTER TABLE `volunteer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `beneficiaries`
--
ALTER TABLE `beneficiaries`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `campaign_management`
--
ALTER TABLE `campaign_management`
  ADD CONSTRAINT `campaign_management_ibfk_1` FOREIGN KEY (`fund_id`) REFERENCES `funds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `campaign_management_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `campaign_management_ibfk_3` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment_methods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`fund_id`) REFERENCES `funds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `donations_ibfk_4` FOREIGN KEY (`pledge_id`) REFERENCES `pledges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donors`
--
ALTER TABLE `donors`
  ADD CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_donors_pledges` FOREIGN KEY (`pledge_id`) REFERENCES `pledges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pledges`
--
ALTER TABLE `pledges`
  ADD CONSTRAINT `fk_pledges_donors` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pledges_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment_methods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `volunteer`
--
ALTER TABLE `volunteer`
  ADD CONSTRAINT `volunteer_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `volunteer_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
