


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table app_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_categories`;

CREATE TABLE `app_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tab_id` int(11) NOT NULL,
  `name` varchar(96) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_custom` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tab_name_owner` (`tab_id`,`name`,`user_id`),
  CONSTRAINT `app_categories_ibfk_1` FOREIGN KEY (`tab_id`) REFERENCES `app_tabs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `app_categories` WRITE;
/*!40000 ALTER TABLE `app_categories` DISABLE KEYS */;

INSERT INTO `app_categories` (`id`, `tab_id`, `name`, `is_custom`, `user_id`, `is_active`) VALUES
	(1, 1, 'Car purchase/loan', 0, 0, 1),
	(2, 1, 'Car insurance', 0, 0, 1),
	(3, 1, 'Fuel (gas/electric/hybrid)', 0, 0, 1),
	(4, 1, 'Maintenance (oil, tires, inspections)', 0, 0, 1),
	(5, 1, 'Repairs', 0, 0, 1),
	(6, 1, 'Parts & accessories', 0, 0, 1),
	(7, 1, 'Registration/licensing', 0, 0, 1),
	(8, 1, 'Roadside assistance', 0, 0, 1),
	(9, 1, 'Tolls', 0, 0, 1),
	(10, 1, 'Parking (street/garage/monthly)', 0, 0, 1),
	(11, 1, 'Public transit (fares/passes)', 0, 0, 1),
	(12, 1, 'Rideshare/taxi', 0, 0, 1),
	(13, 1, 'Bike/scooter (purchase/maintenance)', 0, 0, 1),
	(14, 1, 'Car wash/detailing', 0, 0, 1),
	(15, 1, 'Car membership/car-share', 0, 0, 1),
	(16, 1, 'Driver\'s license renewals', 0, 0, 1),
	(17, 1, 'Fines', 0, 0, 1),
	(18, 1, 'Other', 0, 0, 1),
	(19, 2, 'Rent', 0, 0, 1),
	(20, 2, 'Mortgage', 0, 0, 1),
	(21, 2, 'Property taxes', 0, 0, 1),
	(22, 2, 'Home/tenant insurance', 0, 0, 1),
	(23, 2, 'Electricity', 0, 0, 1),
	(24, 2, 'Heating/Hydro', 0, 0, 1),
	(25, 2, 'Water/Sewer', 0, 0, 1),
	(26, 2, 'Garbage/Recycling fees', 0, 0, 1),
	(27, 2, 'Internet', 0, 0, 1),
	(28, 2, 'Cable/Streaming for home', 0, 0, 1),
	(29, 2, 'Mobile/landline', 0, 0, 1),
	(30, 2, 'HOA/condo fees', 0, 0, 1),
	(31, 2, 'Security system', 0, 0, 1),
	(32, 2, 'Pest control', 0, 0, 1),
	(33, 2, 'Cleaning services', 0, 0, 1),
	(34, 2, 'Repairs/maintenance', 0, 0, 1),
	(35, 2, 'Renovations', 0, 0, 1),
	(36, 2, 'Furniture', 0, 0, 1),
	(37, 2, 'Appliances', 0, 0, 1),
	(38, 2, 'Tools', 0, 0, 1),
	(39, 2, 'Storage unit', 0, 0, 1),
	(40, 2, 'Moving costs', 0, 0, 1),
	(41, 2, 'Landscaping/snow removal', 0, 0, 1),
	(42, 2, 'Other', 0, 0, 1),
	(43, 3, 'Dining out', 0, 0, 1),
	(44, 3, 'Cafes', 0, 0, 1),
	(45, 3, 'Snacks/treats', 0, 0, 1),
	(46, 3, 'Movies', 0, 0, 1),
	(47, 3, 'Concerts', 0, 0, 1),
	(48, 3, 'Theater', 0, 0, 1),
	(49, 3, 'Museums', 0, 0, 1),
	(50, 3, 'Sports events', 0, 0, 1),
	(51, 3, 'Activity fees (bowling, mini-golf, escape rooms)', 0, 0, 1),
	(52, 3, 'Subscriptions (Netflix/Spotify/etc.)', 0, 0, 1),
	(53, 3, 'Gaming (games/DLC/in-app)', 0, 0, 1),
	(54, 3, 'Hobbies/crafts', 0, 0, 1),
	(55, 3, 'Books/e-books/audiobooks', 0, 0, 1),
	(56, 3, 'Streaming rentals/PPV', 0, 0, 1),
	(57, 3, 'Nightlife (bars/clubs)', 0, 0, 1),
	(58, 3, 'Events/festivals', 0, 0, 1),
	(59, 3, 'Photography/gear', 0, 0, 1),
	(60, 3, 'Courses/workshops (non-essential)', 0, 0, 1),
	(61, 3, 'Gifts (non-essential)', 0, 0, 1),
	(62, 3, 'Other', 0, 0, 1),
	(63, 4, 'Gym membership', 0, 0, 1),
	(64, 4, 'Fitness classes (yoga, pilates, martial arts)', 0, 0, 1),
	(65, 4, 'Personal training/coaching', 0, 0, 1),
	(66, 4, 'Home fitness equipment', 0, 0, 1),
	(67, 4, 'Medicines', 0, 0, 1),
	(68, 4, 'Vitamins/supplements', 0, 0, 1),
	(69, 4, 'Prescriptions', 0, 0, 1),
	(70, 4, 'Pharmacy fees', 0, 0, 1),
	(71, 4, 'GP/Family doctor', 0, 0, 1),
	(72, 4, 'Specialists', 0, 0, 1),
	(73, 4, 'Hospital/ER', 0, 0, 1),
	(74, 4, 'Urgent care/clinics', 0, 0, 1),
	(75, 4, 'Dental (cleaning, fillings, ortho)', 0, 0, 1),
	(76, 4, 'Vision (exams, glasses, contacts, surgery)', 0, 0, 1),
	(77, 4, 'Hearing (exams, aids)', 0, 0, 1),
	(78, 4, 'Mental health (therapy/counseling)', 0, 0, 1),
	(79, 4, 'Physiotherapy', 0, 0, 1),
	(80, 4, 'Chiropractor', 0, 0, 1),
	(81, 4, 'Massage therapy', 0, 0, 1),
	(82, 4, 'Acupuncture', 0, 0, 1),
	(83, 4, 'Alternative/naturopathy', 0, 0, 1),
	(84, 4, 'Lab tests', 0, 0, 1),
	(85, 4, 'Medical devices (BP cuff, glucose monitor)', 0, 0, 1),
	(86, 4, 'Health insurance premiums', 0, 0, 1),
	(87, 4, 'Travel vaccines', 0, 0, 1),
	(88, 4, 'Other', 0, 0, 1),
	(89, 5, 'Groceries', 0, 0, 1),
	(90, 5, 'Household supplies', 0, 0, 1),
	(91, 5, 'Toiletries/personal care', 0, 0, 1),
	(92, 5, 'Laundry/dry cleaning', 0, 0, 1),
	(93, 5, 'Baby supplies (diapers, formula)', 0, 0, 1),
	(94, 5, 'School supplies', 0, 0, 1),
	(95, 5, 'Tuition/fees (essential)', 0, 0, 1),
	(96, 5, 'Childcare/babysitting', 0, 0, 1),
	(97, 5, 'Transportation passes required for work/school', 0, 0, 1),
	(98, 5, 'Pet food/basic care', 0, 0, 1),
	(99, 5, 'Basic clothing/shoes', 0, 0, 1),
	(100, 5, 'Work uniforms/tools', 0, 0, 1),
	(101, 5, 'Cloud storage/phone plan essential tier', 0, 0, 1),
	(102, 5, 'Banking fees', 0, 0, 1),
	(103, 5, 'Taxes/filing fees', 0, 0, 1),
	(104, 5, 'Postage/shipping essentials', 0, 0, 1),
	(105, 5, 'Community dues/obligatory fees', 0, 0, 1),
	(106, 5, 'Other', 0, 0, 1),
	(107, 6, 'Memberships: Clubs (golf/social)', 0, 0, 1),
	(108, 6, 'Memberships: Premium streaming', 0, 0, 1),
	(109, 6, 'Memberships: Subscription boxes', 0, 0, 1),
	(110, 6, 'Memberships: Premium apps', 0, 0, 1),
	(111, 6, 'Memberships: Creator memberships/patreon', 0, 0, 1),
	(112, 6, 'Memberships: Magazines/newspapers', 0, 0, 1),
	(113, 6, 'Memberships: Specialty gyms', 0, 0, 1),
	(114, 6, 'Memberships: VIP programs', 0, 0, 1),
	(115, 6, 'Memberships: Game passes', 0, 0, 1),
	(116, 6, 'Non-Memberships: Fashion & accessories', 0, 0, 1),
	(117, 6, 'Non-Memberships: Designer items', 0, 0, 1),
	(118, 6, 'Non-Memberships: Luxury electronics/gadgets', 0, 0, 1),
	(119, 6, 'Non-Memberships: Collectibles', 0, 0, 1),
	(120, 6, 'Non-Memberships: Hobbies/special gear', 0, 0, 1),
	(121, 6, 'Non-Memberships: Décor', 0, 0, 1),
	(122, 6, 'Non-Memberships: Non-essential gifts', 0, 0, 1),
	(123, 6, 'Non-Memberships: Travel splurges', 0, 0, 1),
	(124, 6, 'Non-Memberships: Event splurges', 0, 0, 1),
	(125, 6, 'Non-Memberships: Cosmetics/luxury care', 0, 0, 1),
	(126, 6, 'Non-Memberships: Impulse buys', 0, 0, 1),
	(127, 6, 'Other', 0, 0, 1),
	(128, 101, 'Flights (domestic/international)', 0, 0, 1),
	(129, 101, 'Trains', 0, 0, 1),
	(130, 101, 'Buses/coaches', 0, 0, 1),
	(131, 101, 'Shuttles', 0, 0, 1),
	(132, 101, 'Car rental', 0, 0, 1),
	(133, 101, 'Fuel', 0, 0, 1),
	(134, 101, 'Taxis/rideshare', 0, 0, 1),
	(135, 101, 'Ferries/boats', 0, 0, 1),
	(136, 101, 'Cruises', 0, 0, 1),
	(137, 101, 'City transport passes/cards', 0, 0, 1),
	(138, 101, 'Baggage fees', 0, 0, 1),
	(139, 101, 'Seat/upgrade fees', 0, 0, 1),
	(140, 101, 'Airport parking', 0, 0, 1),
	(141, 101, 'Tolls', 0, 0, 1),
	(142, 101, 'Travel insurance (transport)', 0, 0, 1),
	(143, 101, 'Other', 0, 0, 1),
	(144, 102, 'Hotels', 0, 0, 1),
	(145, 102, 'Hostels', 0, 0, 1),
	(146, 102, 'Guesthouses', 0, 0, 1),
	(147, 102, 'Vacation rentals (Airbnb)', 0, 0, 1),
	(148, 102, 'Resorts', 0, 0, 1),
	(149, 102, 'Motels', 0, 0, 1),
	(150, 102, 'Campsites', 0, 0, 1),
	(151, 102, 'Overnight trains/boats', 0, 0, 1),
	(152, 102, 'Day rooms', 0, 0, 1),
	(153, 102, 'Resort fees', 0, 0, 1),
	(154, 102, 'City/lodging taxes', 0, 0, 1),
	(155, 102, 'Other', 0, 0, 1),
	(156, 103, 'Restaurants', 0, 0, 1),
	(157, 103, 'Cafes', 0, 0, 1),
	(158, 103, 'Street food', 0, 0, 1),
	(159, 103, 'Groceries', 0, 0, 1),
	(160, 103, 'Delivery/apps', 0, 0, 1),
	(161, 103, 'Room service', 0, 0, 1),
	(162, 103, 'Snacks', 0, 0, 1),
	(163, 103, 'Water/beverages', 0, 0, 1),
	(164, 103, 'Specialty dining/experiences', 0, 0, 1),
	(165, 103, 'Other', 0, 0, 1),
	(166, 104, 'Tours (city, cultural, adventure)', 0, 0, 1),
	(167, 104, 'Landmarks/museums', 0, 0, 1),
	(168, 104, 'Theme/amusement parks', 0, 0, 1),
	(169, 104, 'Beaches/pool passes', 0, 0, 1),
	(170, 104, 'Outdoor (hiking, diving, skiing)', 0, 0, 1),
	(171, 104, 'Gear rental', 0, 0, 1),
	(172, 104, 'Shows/concerts/nightlife', 0, 0, 1),
	(173, 104, 'Classes/workshops', 0, 0, 1),
	(174, 104, 'Souvenirs/shopping', 0, 0, 1),
	(175, 104, 'Photography permits', 0, 0, 1),
	(176, 104, 'Local SIM/roaming for apps', 0, 0, 1),
	(177, 104, 'Other', 0, 0, 1),
	(178, 105, 'Visas/passport fees', 0, 0, 1),
	(179, 105, 'Currency exchange/ATM/bank fees', 0, 0, 1),
	(180, 105, 'SIM/eSIM/roaming plans', 0, 0, 1),
	(181, 105, 'Travel health insurance', 0, 0, 1),
	(182, 105, 'Travel meds/vaccines', 0, 0, 1),
	(183, 105, 'Safety gear', 0, 0, 1),
	(184, 105, 'Emergency fund', 0, 0, 1),
	(185, 105, 'Luggage/locks', 0, 0, 1),
	(186, 105, 'Power adapters', 0, 0, 1),
	(187, 105, 'Local transport cards', 0, 0, 1),
	(188, 105, 'Data backups/cloud while traveling', 0, 0, 1),
	(189, 105, 'Other', 0, 0, 1);

/*!40000 ALTER TABLE `app_categories` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table app_entries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_entries`;

CREATE TABLE `app_entries` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `row_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ts_utc` datetime NOT NULL,
  `local_date` date NOT NULL,
  `amount_cents` int(11) NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idempotency_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_idem` (`idempotency_key`),
  KEY `idx_row_month` (`row_id`,`local_date`),
  KEY `idx_user_month` (`user_id`,`local_date`),
  CONSTRAINT `app_entries_ibfk_1` FOREIGN KEY (`row_id`) REFERENCES `app_user_category_rows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table app_monthly_snapshot_subtotals
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_monthly_snapshot_subtotals`;

CREATE TABLE `app_monthly_snapshot_subtotals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint(20) NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_cents` bigint(20) NOT NULL DEFAULT '0',
  `entry_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_snap_cur` (`snapshot_id`,`currency`),
  CONSTRAINT `app_monthly_snapshot_subtotals_ibfk_1` FOREIGN KEY (`snapshot_id`) REFERENCES `app_monthly_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table app_monthly_snapshots
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_monthly_snapshots`;

CREATE TABLE `app_monthly_snapshots` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `row_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mode` enum('normal','travel') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tab_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `month_start` date NOT NULL,
  `month_end` date NOT NULL,
  `total_cents` bigint(20) NOT NULL DEFAULT '0',
  `predominant_currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_row_month` (`row_id`,`month_start`),
  KEY `idx_user_month` (`user_id`,`month_start`),
  CONSTRAINT `app_monthly_snapshots_ibfk_1` FOREIGN KEY (`row_id`) REFERENCES `app_user_category_rows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table app_tabs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_tabs`;

CREATE TABLE `app_tabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mode` enum('normal','travel') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort` smallint(6) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_mode_name` (`mode`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `app_tabs` WRITE;
/*!40000 ALTER TABLE `app_tabs` DISABLE KEYS */;

INSERT INTO `app_tabs` (`id`, `mode`, `name`, `sort`, `is_active`) VALUES
	(1, 'normal', 'Transportation', 10, 1),
	(2, 'normal', 'Accommodation / Housing', 20, 1),
	(3, 'normal', 'Entertainment & Leisure', 30, 1),
	(4, 'normal', 'Health & Wellness', 40, 1),
	(5, 'normal', 'Essentials', 50, 1),
	(6, 'normal', 'Non-Essentials', 60, 1),
	(101, 'travel', 'Transportation (Travel)', 10, 1),
	(102, 'travel', 'Accommodation (Travel)', 20, 1),
	(103, 'travel', 'Food & Dining (Travel)', 30, 1),
	(104, 'travel', 'Entertainment & Activities (Travel)', 40, 1),
	(105, 'travel', 'Essentials (Travel)', 50, 1);

/*!40000 ALTER TABLE `app_tabs` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table app_user_category_rows
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_user_category_rows`;

CREATE TABLE `app_user_category_rows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mode` enum('normal','travel') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tab_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `open_month_ym` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_total_cents` bigint(20) NOT NULL DEFAULT '0',
  `current_entry_count` int(11) NOT NULL DEFAULT '0',
  `lifetime_total_cents` bigint(20) NOT NULL DEFAULT '0',
  `lifetime_entry_count` bigint(20) NOT NULL DEFAULT '0',
  `current_currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_entry_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_mode_tab_cat` (`user_id`,`mode`,`tab_id`,`category_id`),
  KEY `idx_user_month` (`user_id`,`open_month_ym`),
  KEY `tab_id` (`tab_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `app_user_category_rows_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_user_category_rows_ibfk_2` FOREIGN KEY (`tab_id`) REFERENCES `app_tabs` (`id`),
  CONSTRAINT `app_user_category_rows_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table app_user_currency_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `app_user_currency_history`;

CREATE TABLE `app_user_currency_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `effective_from_utc` datetime NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_from` (`user_id`,`effective_from_utc`),
  CONSTRAINT `app_user_currency_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(48) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;

INSERT INTO `categories` (`id`, `name`, `active`) VALUES
	(1, 'Transportation', 1),
	(2, 'Accommodation', 1),
	(3, 'Travel & Entertainment', 1),
	(4, 'Health', 1);

/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table expense_tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `expense_tags`;

CREATE TABLE `expense_tags` (
  `expense_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`expense_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `expense_tags_ibfk_1` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





# Dump of table expenses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `expenses`;

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount_cents` int(11) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'CAD',
  `category_id` tinyint(3) unsigned NOT NULL,
  `subcategory_id` smallint(5) unsigned DEFAULT NULL,
  `payment_method_id` tinyint(3) unsigned DEFAULT NULL,
  `merchant` varchar(64) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_date` (`tenant_id`,`date`),
  KEY `idx_tenant_cat_date` (`tenant_id`,`category_id`,`date`),
  KEY `idx_tenant_user_date` (`tenant_id`,`user_id`,`date`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `subcategory_id` (`subcategory_id`),
  KEY `payment_method_id` (`payment_method_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`),
  CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `expenses_ibfk_4` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`),
  CONSTRAINT `expenses_ibfk_5` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;

INSERT INTO `expenses` (`id`, `tenant_id`, `user_id`, `date`, `amount_cents`, `currency`, `category_id`, `subcategory_id`, `payment_method_id`, `merchant`, `note`, `created_at`, `updated_at`) VALUES
	(1, 2, 2, '2025-08-27', 4500, 'CAD', 3, 12, 3, '', '', '2025-08-27 19:45:51', '2025-08-27 19:45:51');

/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table monthly_expense_totals
# ------------------------------------------------------------

DROP TABLE IF EXISTS `monthly_expense_totals`;

CREATE TABLE `monthly_expense_totals` (
  `tenant_id` int(11) NOT NULL,
  `year_month` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` tinyint(3) unsigned NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_cents` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tenant_id`,`year_month`,`category_id`,`currency`),
  KEY `idx_met_tenant` (`tenant_id`),
  KEY `idx_met_category` (`category_id`),
  CONSTRAINT `fk_met_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_met_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table payment_methods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `payment_methods`;

CREATE TABLE `payment_methods` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;

INSERT INTO `payment_methods` (`id`, `name`, `active`) VALUES
	(1, 'Cash', 1),
	(2, 'Debit', 1),
	(3, 'Credit', 1),
	(4, 'E-Transfer', 1),
	(5, 'Other', 1);

/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table subcategories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `subcategories`;

CREATE TABLE `subcategories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(48) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;

LOCK TABLES `subcategories` WRITE;
/*!40000 ALTER TABLE `subcategories` DISABLE KEYS */;

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `active`) VALUES
	(1, 1, 'Bus', 1),
	(2, 1, 'Train', 1),
	(3, 1, 'Uber', 1),
	(4, 1, 'Fuel', 1),
	(5, 1, 'Parking', 1),
	(6, 2, 'Rent', 1),
	(7, 2, 'Utilities', 1),
	(8, 2, 'Internet', 1),
	(9, 3, 'Flights', 1),
	(10, 3, 'Hotels', 1),
	(11, 3, 'Movies', 1),
	(12, 3, 'Restaurants', 1),
	(13, 4, 'Pharmacy', 1),
	(14, 4, 'Clinic', 1),
	(15, 4, 'Insurance', 1),
	(16, 1, 'Bus', 1),
	(17, 1, 'Train', 1),
	(18, 1, 'Uber', 1),
	(19, 1, 'Fuel', 1),
	(20, 1, 'Parking', 1),
	(21, 2, 'Rent', 1),
	(22, 2, 'Utilities', 1),
	(23, 2, 'Internet', 1),
	(24, 3, 'Flights', 1),
	(25, 3, 'Hotels', 1),
	(26, 3, 'Movies', 1),
	(27, 3, 'Restaurants', 1),
	(28, 4, 'Pharmacy', 1),
	(29, 4, 'Clinic', 1),
	(30, 4, 'Insurance', 1);

/*!40000 ALTER TABLE `subcategories` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tenant_tag` (`tenant_id`,`name`),
  CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





# Dump of table tenants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tenants`;

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;

INSERT INTO `tenants` (`id`, `name`, `created_at`) VALUES
	(1, 'Default', '2025-08-27 19:03:47'),
	(2, 'Moussa Badr', '2025-08-27 19:22:11');

/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT '1',
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `tz` varchar(64) NOT NULL DEFAULT 'UTC',
  `active_currency` char(3) NOT NULL DEFAULT 'CAD',
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `tenant_id`, `name`, `email`, `tz`, `active_currency`, `password_hash`, `role`, `created_at`) VALUES
	(1, 1, 'Admin', 'admin@example.com', 'UTC', 'CAD', '$2y$10$c35Yr5TmX9GMAUrdzAJOs.yfweUNqkt9.On1E9LUqHP2pUoej2rf.', 'admin', '2025-08-27 19:03:47'),
	(2, 2, 'Moussa Badr', 'moussabadr2020@gmail.com', 'UTC', 'CAD', '$2y$10$C2auJJZlg3/GoX9ZCdw4KOehg6XPaPwiWbZOFUY5hz6la29JKd0Iq', 'user', '2025-08-27 19:22:11');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of views
# ------------------------------------------------------------

DROP VIEW IF EXISTS `vw_expenses_with_names`;
CREATE ALGORITHM=UNDEFINED DEFINER=`%` SQL SECURITY DEFINER VIEW `vw_expenses_with_names` AS select `e`.`id` AS `id`,`e`.`tenant_id` AS `tenant_id`,`e`.`user_id` AS `user_id`,`e`.`date` AS `date`,`e`.`amount_cents` AS `amount_cents`,`e`.`currency` AS `currency`,`e`.`category_id` AS `category_id`,`c`.`name` AS `category_name`,`e`.`subcategory_id` AS `subcategory_id`,`sc`.`name` AS `subcategory_name`,`e`.`payment_method_id` AS `payment_method_id`,`pm`.`name` AS `payment_method_name`,`e`.`merchant` AS `merchant`,`e`.`note` AS `note`,`e`.`created_at` AS `created_at`,`e`.`updated_at` AS `updated_at` from (((`expenses` `e` left join `categories` `c` on((`c`.`id` = `e`.`category_id`))) left join `subcategories` `sc` on((`sc`.`id` = `e`.`subcategory_id`))) left join `payment_methods` `pm` on((`pm`.`id` = `e`.`payment_method_id`)));


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


