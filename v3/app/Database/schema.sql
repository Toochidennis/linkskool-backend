-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 01:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
    time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `linkskoo_practice`
--
-- --------------------------------------------------------
--
-- Table structure for table `account_chart`
--
CREATE TABLE
    IF NOT EXISTS `account_chart` (
        `typeid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `account_id` varchar(50) DEFAULT NULL,
        `account_type` int (11) DEFAULT NULL,
        `account_name` varchar(255) DEFAULT NULL,
        `inactive` varchar(10) DEFAULT NULL,
        PRIMARY KEY (`typeid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `account_type`
--
CREATE TABLE
    IF NOT EXISTS `account_type` (
        `typeid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `aid` int (11) DEFAULT NULL,
        PRIMARY KEY (`typeid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `admission_criteria`
--
CREATE TABLE
    IF NOT EXISTS `admission_criteria` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `courses` varchar(150) DEFAULT NULL,
        `lowest1` varchar(10) DEFAULT NULL,
        `lowest2` varchar(10) DEFAULT NULL,
        `others` tinyint (3) UNSIGNED DEFAULT NULL,
        `type` tinyint (3) UNSIGNED DEFAULT NULL,
        `pass_mark` tinyint (3) UNSIGNED DEFAULT NULL,
        `success_msg` text DEFAULT NULL,
        `failure_msg` text DEFAULT NULL,
        `exam_date` datetime DEFAULT NULL,
        `exam_venue` text DEFAULT NULL,
        `exam_comment` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `admission_form_table`
--
CREATE TABLE
    IF NOT EXISTS `admission_form_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        `amount` varchar(50) DEFAULT NULL,
        `start_date` datetime DEFAULT NULL,
        `process` int (11) DEFAULT 0,
        `description` text DEFAULT NULL,
        `form` text DEFAULT NULL,
        `free` varchar(50) DEFAULT NULL,
        `end_date` datetime DEFAULT NULL,
        `courses` text DEFAULT NULL,
        `exam_date` datetime DEFAULT NULL,
        `exam_time` varchar(50) DEFAULT NULL,
        `exam_venue` varchar(255) DEFAULT NULL,
        `comment` text DEFAULT NULL,
        `success_msg` text DEFAULT NULL,
        `failure_msg` text DEFAULT NULL,
        `course_pass_mark` int (11) DEFAULT NULL,
        `min_avg` int (11) DEFAULT NULL,
        `min_num_pass` int (11) DEFAULT NULL,
        `course1` varchar(255) DEFAULT NULL,
        `course2` varchar(255) DEFAULT NULL,
        `min_score_course1` varchar(255) DEFAULT NULL,
        `min_score_course2` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `admission_table`
--
CREATE TABLE
    IF NOT EXISTS `admission_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `exam_no` int (10) UNSIGNED NOT NULL,
        `registration_no` varchar(10) DEFAULT NULL,
        `payment_pin` bigint (20) UNSIGNED DEFAULT NULL,
        `payment_amount` double UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `allowance`
--
CREATE TABLE
    IF NOT EXISTS `allowance` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `include` text DEFAULT NULL,
        `percent` float (10, 2) DEFAULT NULL,
        `dummy` float (10, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `allowance_table`
--
CREATE TABLE
    IF NOT EXISTS `allowance_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `alumni_record`
--
CREATE TABLE
    IF NOT EXISTS `alumni_record` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `picture_ref` varchar(100) DEFAULT NULL,
        `title` int (11) DEFAULT NULL,
        `surname` varchar(50) DEFAULT NULL,
        `first_name` varchar(50) DEFAULT NULL,
        `middle` varchar(50) DEFAULT NULL,
        `sex` varchar(50) DEFAULT NULL,
        `birthdate` date DEFAULT NULL,
        `address` varchar(200) DEFAULT NULL,
        `city` int (11) DEFAULT NULL,
        `state` int (11) DEFAULT NULL,
        `country` int (11) DEFAULT NULL,
        `email` varchar(70) DEFAULT NULL,
        `website` varchar(255) DEFAULT NULL,
        `phone` varchar(70) DEFAULT NULL,
        `registration_no` varchar(50) DEFAULT NULL,
        `religion` varchar(30) DEFAULT NULL,
        `guardian_name` varchar(50) DEFAULT NULL,
        `guardian_address` text DEFAULT NULL,
        `guardian_email` varchar(50) DEFAULT NULL,
        `guardian_phone_no` varchar(55) DEFAULT NULL,
        `local_government_origin` varchar(25) DEFAULT NULL,
        `state_origin` varchar(25) DEFAULT NULL,
        `nationality` varchar(25) DEFAULT NULL,
        `health_status` text DEFAULT NULL,
        `date_admitted` date DEFAULT NULL,
        `status` varchar(100) DEFAULT NULL,
        `past_record` text DEFAULT NULL,
        `result` text DEFAULT NULL,
        `occupation` varchar(100) DEFAULT NULL,
        `graduation_year` year (4) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `assessment_table`
--
CREATE TABLE
    IF NOT EXISTS `assessment_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `assesment_name` char(25) DEFAULT NULL,
        `max_score` int (11) DEFAULT NULL,
        `level` int (11) NOT NULL DEFAULT 0,
        `type` int (11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `attendance`
--
CREATE TABLE
    IF NOT EXISTS `attendance` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `term` tinyint (4) DEFAULT NULL,
        `staff_id` int (11) DEFAULT NULL,
        `count` int (11) DEFAULT NULL,
        `class` int (11) DEFAULT NULL,
        `course` int (11) DEFAULT NULL,
        `register` text DEFAULT NULL,
        `date` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `card_data`
--
CREATE TABLE
    IF NOT EXISTS `card_data` (
        `id` bigint (20) NOT NULL AUTO_INCREMENT,
        `pin` varchar(17) NOT NULL,
        `serial` varchar(20) NOT NULL,
        `barcode` varchar(70) NOT NULL,
        `status` bigint (2) NOT NULL DEFAULT 0,
        `datetime` datetime NOT NULL,
        `user` int (11) DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `category` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `chatroom_table`
--
CREATE TABLE
    IF NOT EXISTS `chatroom_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `chatroom` int (10) UNSIGNED DEFAULT NULL,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `class_table`
--
CREATE TABLE
    IF NOT EXISTS `class_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `class_name` varchar(10) DEFAULT NULL,
        `level` tinyint (3) UNSIGNED DEFAULT NULL,
        `form_teacher` varchar(100) DEFAULT NULL,
        `result_template` varchar(255) DEFAULT NULL,
        `monday_timetable` varchar(100) DEFAULT NULL,
        `tuesday_timetable` varchar(100) DEFAULT NULL,
        `wednesday_timetable` varchar(100) DEFAULT NULL,
        `thursday_timetable` varchar(100) DEFAULT NULL,
        `friday_timetable` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `comment_table`
--
CREATE TABLE
    IF NOT EXISTS `comment_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `term` tinyint (4) DEFAULT NULL,
        `reg_no` int (10) UNSIGNED NOT NULL,
        `pass` varchar(4) DEFAULT NULL,
        `form_teacher` varchar(200) DEFAULT NULL,
        `principal` varchar(200) DEFAULT NULL,
        `no_absent` varchar(255) DEFAULT NULL,
        `no_present` varchar(255) DEFAULT NULL,
        `no_school_opened` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `country`
--
CREATE TABLE
    IF NOT EXISTS `country` (
        `countryId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(200) DEFAULT NULL,
        PRIMARY KEY (`countryId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `course_table`
--
CREATE TABLE
    IF NOT EXISTS `course_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `course_name` varchar(50) DEFAULT NULL,
        `course_code` char(5) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `customer`
--
CREATE TABLE
    IF NOT EXISTS `customer` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `customerid` varchar(100) DEFAULT NULL,
        `customername` varchar(255) DEFAULT NULL,
        `contact` varchar(255) DEFAULT NULL,
        `address` varchar(255) DEFAULT NULL,
        `address2` varchar(255) DEFAULT NULL,
        `city` varchar(255) DEFAULT NULL,
        `state` varchar(255) DEFAULT NULL,
        `zipcode` varchar(255) DEFAULT NULL,
        `country` varchar(255) DEFAULT NULL,
        `telephone` varchar(50) DEFAULT NULL,
        `telephone1` varchar(50) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `website` varchar(255) DEFAULT NULL,
        `fax` varchar(255) DEFAULT NULL,
        `customer_since` date DEFAULT NULL,
        `type` varchar(100) DEFAULT NULL,
        `customer_type` varchar(100) DEFAULT NULL,
        `inactive` varchar(10) DEFAULT NULL,
        `prospect` varchar(10) DEFAULT NULL,
        `picture` varchar(255) DEFAULT NULL,
        `referal` varchar(255) DEFAULT NULL,
        `current_balance` float (10, 2) DEFAULT NULL,
        `created_by` int (11) DEFAULT NULL,
        `clientid` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `default_account_settings`
--
CREATE TABLE
    IF NOT EXISTS `default_account_settings` (
        `sid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `account_receivable` varchar(255) DEFAULT NULL,
        `account_payable` varchar(255) DEFAULT NULL,
        `cash` varchar(255) DEFAULT NULL,
        `sales` varchar(255) DEFAULT NULL,
        `cost_of_sales` varchar(255) DEFAULT NULL,
        `fixed_assets` varchar(255) DEFAULT NULL,
        `other_assets` varchar(255) DEFAULT NULL,
        `expenses` varchar(255) DEFAULT NULL,
        `current_assets` varchar(255) DEFAULT NULL,
        `current_liabilities` varchar(255) DEFAULT NULL,
        `long_term_liabilities` varchar(255) DEFAULT NULL,
        `equity_open` varchar(255) DEFAULT NULL,
        `equity_closed` varchar(255) DEFAULT NULL,
        `equity_retained` varchar(255) DEFAULT NULL,
        `inventory` varchar(255) DEFAULT NULL,
        `accumulated_depreciation` varchar(255) DEFAULT NULL,
        `payroll` varchar(255) DEFAULT NULL,
        `discount` varchar(255) DEFAULT NULL,
        `pur_discount` varchar(255) DEFAULT NULL,
        `production` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`sid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `department`
--
CREATE TABLE
    IF NOT EXISTS `department` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(120) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `department_table`
--
CREATE TABLE
    IF NOT EXISTS `department_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `designation`
--
CREATE TABLE
    IF NOT EXISTS `designation` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `designation_table`
--
CREATE TABLE
    IF NOT EXISTS `designation_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `employee`
--
CREATE TABLE
    IF NOT EXISTS `employee` (
        `EmpID` int (11) NOT NULL,
        `LastName` varchar(50) DEFAULT NULL,
        `FirstName` varchar(50) DEFAULT NULL,
        `Country` varchar(50) DEFAULT NULL,
        PRIMARY KEY (`EmpID`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `entrance_form`
--
CREATE TABLE
    IF NOT EXISTS `entrance_form` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `number` varchar(50) DEFAULT NULL,
        `score` varchar(255) DEFAULT NULL,
        `admission_status` tinyint (1) DEFAULT NULL,
        `registration_status` tinyint (1) DEFAULT NULL,
        `date` datetime DEFAULT NULL,
        `col1` text DEFAULT NULL,
        `col2` text DEFAULT NULL,
        `col3` text DEFAULT NULL,
        `col4` text DEFAULT NULL,
        `col5` text DEFAULT NULL,
        `col6` text DEFAULT NULL,
        `col7` text DEFAULT NULL,
        `col8` text DEFAULT NULL,
        `col9` text DEFAULT NULL,
        `col10` text DEFAULT NULL,
        `col11` text DEFAULT NULL,
        `col12` text DEFAULT NULL,
        `col13` text DEFAULT NULL,
        `col14` text DEFAULT NULL,
        `col15` text DEFAULT NULL,
        `col16` text DEFAULT NULL,
        `col17` text DEFAULT NULL,
        `col18` text DEFAULT NULL,
        `col19` text DEFAULT NULL,
        `col20` text DEFAULT NULL,
        `col21` text DEFAULT NULL,
        `col22` text DEFAULT NULL,
        `col23` text DEFAULT NULL,
        `col24` text DEFAULT NULL,
        `col25` text DEFAULT NULL,
        `col26` text DEFAULT NULL,
        `col27` text DEFAULT NULL,
        `col28` text DEFAULT NULL,
        `col29` text DEFAULT NULL,
        `col30` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `fees_table`
--
CREATE TABLE
    IF NOT EXISTS `fees_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `amount` float DEFAULT NULL,
        `level` int (11) DEFAULT NULL,
        `mandatory` tinyint (4) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `file_count`
--
CREATE TABLE
    IF NOT EXISTS `file_count` (
        `countid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `type` varchar(20) DEFAULT NULL,
        `number` int (11) DEFAULT NULL,
        PRIMARY KEY (`countid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `gender`
--
CREATE TABLE
    IF NOT EXISTS `gender` (
        `genderId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(20) DEFAULT NULL,
        PRIMARY KEY (`genderId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `grade_allowance`
--
CREATE TABLE
    IF NOT EXISTS `grade_allowance` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `grade` int (11) DEFAULT NULL,
        `allowance` int (11) DEFAULT NULL,
        `amount` float (10, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `grade_level`
--
CREATE TABLE
    IF NOT EXISTS `grade_level` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `hall_of_fame`
--
CREATE TABLE
    IF NOT EXISTS `hall_of_fame` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `room` int (11) DEFAULT NULL,
        `alumni` int (11) DEFAULT NULL,
        `title` varchar(255) DEFAULT NULL,
        `biography` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `hall_of_fame_room`
--
CREATE TABLE
    IF NOT EXISTS `hall_of_fame_room` (
        `roomId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(200) DEFAULT NULL,
        PRIMARY KEY (`roomId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `hostel_table`
--
CREATE TABLE
    IF NOT EXISTS `hostel_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `capacity` int (11) DEFAULT NULL,
        `state` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `insurable_earning`
--
CREATE TABLE
    IF NOT EXISTS `insurable_earning` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `allowance` int (11) DEFAULT NULL,
        `status` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `item`
--
CREATE TABLE
    IF NOT EXISTS `item` (
        `tid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `transcid` varchar(50) DEFAULT NULL,
        `itemID` varchar(100) DEFAULT NULL,
        `description` varchar(100) DEFAULT NULL,
        `sales_desc` varchar(200) DEFAULT NULL,
        `purchase_desc` varchar(200) DEFAULT NULL,
        `upc` varchar(200) DEFAULT NULL,
        `item_type` varchar(200) DEFAULT NULL,
        `location` varchar(200) DEFAULT NULL,
        `unit` varchar(200) DEFAULT NULL,
        `weight` float (10, 2) DEFAULT NULL,
        `unit_cost` float (10, 2) DEFAULT NULL,
        `taxable` varchar(10) DEFAULT NULL,
        `price` float (10, 2) DEFAULT NULL,
        `inactive` varchar(10) DEFAULT NULL,
        `commission` varchar(10) DEFAULT NULL,
        `costing_method` varchar(10) DEFAULT NULL,
        `gl_sales_account` varchar(50) DEFAULT NULL,
        `gl_inventory_account` varchar(50) DEFAULT NULL,
        `gl_cos_account` varchar(50) DEFAULT NULL,
        `minimum_stock` varchar(50) DEFAULT NULL,
        `quantity_on_hand` varchar(50) DEFAULT NULL,
        `date_created` date DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `mandatory` int (11) DEFAULT NULL,
        `created_by` int (11) DEFAULT NULL,
        PRIMARY KEY (`tid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `leave_table`
--
CREATE TABLE
    IF NOT EXISTS `leave_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_ref` int (10) UNSIGNED NOT NULL,
        `startdate` date DEFAULT NULL,
        `enddate` date DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `leave_type`
--
CREATE TABLE
    IF NOT EXISTS `leave_type` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `level_table`
--
CREATE TABLE
    IF NOT EXISTS `level_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `level_name` varchar(10) DEFAULT NULL,
        `school_type` tinyint (3) UNSIGNED DEFAULT NULL,
        `rank` int (11) DEFAULT NULL,
        `result_template` varchar(255) DEFAULT NULL,
        `admit` int (2) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `lga`
--
CREATE TABLE
    IF NOT EXISTS `lga` (
        `lgaId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(20) DEFAULT NULL,
        `State` int (11) DEFAULT NULL,
        PRIMARY KEY (`lgaId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `link`
--
CREATE TABLE
    IF NOT EXISTS `link` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(250) DEFAULT NULL,
        `category` varchar(250) DEFAULT NULL,
        `description` varchar(250) DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `rank` int (11) DEFAULT NULL,
        `parent` int (11) DEFAULT NULL,
        `outline` int (11) DEFAULT NULL,
        `url` text DEFAULT NULL,
        `body` text DEFAULT NULL,
        `start_date` datetime DEFAULT NULL,
        `end_date` datetime DEFAULT NULL,
        `date` datetime DEFAULT NULL,
        `picref` text DEFAULT NULL,
        `no_of_views` int (11) DEFAULT NULL,
        `author_name` varchar(255) DEFAULT NULL,
        `publish` int (11) DEFAULT NULL,
        `author_id` int (11) DEFAULT NULL,
        `path_label` text DEFAULT NULL,
        `upload_date` datetime DEFAULT NULL,
        `course_id` int (11) DEFAULT NULL,
        `course_name` varchar(255) DEFAULT NULL,
        `level` varchar(255) DEFAULT NULL,
        `term` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `marital_status`
--
CREATE TABLE
    IF NOT EXISTS `marital_status` (
        `statusId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(20) DEFAULT NULL,
        PRIMARY KEY (`statusId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `monthly_assessment_table`
--
CREATE TABLE
    IF NOT EXISTS `monthly_assessment_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `term` tinyint (4) DEFAULT NULL,
        `reg_no` int (11) DEFAULT NULL,
        `class` int (11) DEFAULT NULL,
        `course` int (11) DEFAULT NULL,
        `month` int (11) DEFAULT NULL,
        `result` varchar(50) DEFAULT NULL,
        `date_modified` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `nametitle`
--
CREATE TABLE
    IF NOT EXISTS `nametitle` (
        `titleId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `titleName` varchar(20) DEFAULT NULL,
        PRIMARY KEY (`titleId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `new_student_table`
--
CREATE TABLE
    IF NOT EXISTS `new_student_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `email` varchar(255) DEFAULT NULL,
        `password` varchar(50) DEFAULT NULL,
        `phone` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `next_term_fees`
--
CREATE TABLE
    IF NOT EXISTS `next_term_fees` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `term` int (11) DEFAULT NULL,
        `fee` int (11) DEFAULT NULL,
        `fee_name` varchar(100) DEFAULT NULL,
        `amount` float (8, 2) DEFAULT NULL,
        `level` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `next_term_table`
--
CREATE TABLE
    IF NOT EXISTS `next_term_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `term_name` varchar(20) DEFAULT NULL,
        `term_start` date DEFAULT NULL,
        `term_end` date DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `paid_table`
--
CREATE TABLE
    IF NOT EXISTS `paid_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `payment_ref` int (10) UNSIGNED NOT NULL,
        `fees_ref` int (10) UNSIGNED NOT NULL,
        `year` year (4) DEFAULT NULL,
        `term` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `payment_table`
--
CREATE TABLE
    IF NOT EXISTS `payment_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `bank_ref` int (10) UNSIGNED NOT NULL,
        `fees_ref` int (10) UNSIGNED NOT NULL,
        `teller_no` int (11) DEFAULT NULL,
        `name` varchar(150) DEFAULT NULL,
        `amount` float DEFAULT NULL,
        `amount_used` float DEFAULT NULL,
        `teller_date` date DEFAULT NULL,
        `date_used` datetime DEFAULT NULL,
        `registration_no` int (10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `productline`
--
CREATE TABLE
    IF NOT EXISTS `productline` (
        `productlineid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `transcid` varchar(50) DEFAULT NULL,
        `shortcode` varchar(255) DEFAULT NULL,
        `Name` varchar(255) DEFAULT NULL,
        `parent` int (11) DEFAULT NULL,
        PRIMARY KEY (`productlineid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `promoted_table`
--
CREATE TABLE
    IF NOT EXISTS `promoted_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `reg_no` int (10) UNSIGNED NOT NULL,
        `promoted` tinyint (4) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `prospective_staff_table`
--
CREATE TABLE
    IF NOT EXISTS `prospective_staff_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `surname` varchar(50) DEFAULT NULL,
        `first_name` varchar(50) DEFAULT NULL,
        `middle` varchar(50) DEFAULT NULL,
        `sex` char(6) DEFAULT NULL,
        `birthdate` date DEFAULT NULL,
        `ref_no` varchar(15) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `picture_ref` varchar(100) DEFAULT NULL,
        `email` varchar(70) DEFAULT NULL,
        `local_government_origin` varchar(25) DEFAULT NULL,
        `state_origin` varchar(25) DEFAULT NULL,
        `nationality` varchar(25) DEFAULT NULL,
        `religion` varchar(30) DEFAULT NULL,
        `qualification_primary` text DEFAULT NULL,
        `qualification_secondary` text DEFAULT NULL,
        `qualification_tertiary` text DEFAULT NULL,
        `qualification_postgraduate` text DEFAULT NULL,
        `referees` text DEFAULT NULL,
        `marital_status` varchar(10) DEFAULT NULL,
        `health_status` text DEFAULT NULL,
        `phone_no` varchar(55) DEFAULT NULL,
        `score` int (10) UNSIGNED DEFAULT NULL,
        `employment_status` tinyint (1) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `prospective_students_table`
--
CREATE TABLE
    IF NOT EXISTS `prospective_students_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `surname` varchar(50) DEFAULT NULL,
        `first_name` varchar(50) DEFAULT NULL,
        `middle` varchar(50) DEFAULT NULL,
        `sex` char(6) DEFAULT NULL,
        `birthdate` date DEFAULT NULL,
        `address` text DEFAULT NULL,
        `exam_no` varchar(255) DEFAULT NULL,
        `guardian_name` varchar(50) DEFAULT NULL,
        `guardian_address` text DEFAULT NULL,
        `guardian_email` varchar(50) DEFAULT NULL,
        `guardian_phone_no` varchar(55) DEFAULT NULL,
        `picture_ref` varchar(100) DEFAULT NULL,
        `email` varchar(70) DEFAULT NULL,
        `local_government_origin` varchar(25) DEFAULT NULL,
        `state_origin` varchar(25) DEFAULT NULL,
        `nationality` varchar(25) DEFAULT NULL,
        `religion` varchar(30) DEFAULT NULL,
        `score` varchar(255) DEFAULT NULL,
        `past_record` text DEFAULT NULL,
        `admission_status` tinyint (1) DEFAULT NULL,
        `health_status` text DEFAULT NULL,
        `date_admitted` date DEFAULT NULL,
        `date_applied` date DEFAULT NULL,
        `level_applied` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `question_table`
--
CREATE TABLE
    IF NOT EXISTS `question_table` (
        `question_id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `parent` int (11) DEFAULT NULL,
        `content` text DEFAULT NULL,
        `title` varchar(255) DEFAULT NULL,
        `type` varchar(10) DEFAULT NULL,
        `answer` text DEFAULT NULL,
        `correct` text DEFAULT NULL,
        `course_id` int (11) DEFAULT NULL,
        `course_name` varchar(255) DEFAULT NULL,
        `year` year (4) DEFAULT NULL,
        `term` int (11) DEFAULT NULL,
        PRIMARY KEY (`question_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `relief`
--
CREATE TABLE
    IF NOT EXISTS `relief` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `parameter1` float (10, 2) DEFAULT NULL,
        `parameter2` float (10, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `responses`
--
CREATE TABLE
    IF NOT EXISTS `responses` (
        `response_id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `student` int (11) DEFAULT NULL,
        `student_name` varchar(100) DEFAULT NULL,
        `exam` int (11) DEFAULT NULL,
        `response` text DEFAULT NULL,
        `score` decimal(10, 2) DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `date` datetime DEFAULT NULL,
        `unmarked` int (11) DEFAULT NULL,
        `marking` text DEFAULT NULL,
        `marking_score` decimal(10, 2) DEFAULT NULL,
        `total_score` decimal(10, 2) DEFAULT NULL,
        `exam_total` decimal(10, 2) DEFAULT NULL,
        `exam_count` int (11) DEFAULT NULL,
        `attempted` int (11) DEFAULT NULL,
        `converted` int (11) DEFAULT NULL,
        `course_id` int (11) NOT NULL,
        `course_name` varchar(100) DEFAULT NULL,
        `level` int (11) NOT NULL,
        `class` int (11) DEFAULT NULL,
        `class_name` varchar(100) DEFAULT NULL,
        `year` year (4) DEFAULT NULL,
        `term` int (11) DEFAULT NULL,
        PRIMARY KEY (`response_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `response_table`
--
CREATE TABLE
    IF NOT EXISTS `response_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `reference` varchar(255) DEFAULT NULL,
        `picture_ref` varchar(255) DEFAULT NULL,
        `term` varchar(255) DEFAULT NULL,
        `year` varchar(50) DEFAULT NULL,
        `form_id` int (11) DEFAULT NULL,
        `response` text DEFAULT NULL,
        `date` datetime DEFAULT NULL,
        `result` text DEFAULT NULL,
        `exam_number` varchar(255) DEFAULT NULL,
        `class` int (11) DEFAULT NULL,
        `admit` int (11) DEFAULT NULL,
        `register` int (11) DEFAULT NULL,
        `new_student_id` int (11) DEFAULT NULL,
        `exam_start` datetime DEFAULT NULL,
        `exam_end` datetime DEFAULT NULL,
        `amount_paid` float (12, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `result_table`
--
CREATE TABLE
    IF NOT EXISTS `result_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `term` tinyint (4) DEFAULT NULL,
        `reg_no` int (10) UNSIGNED NOT NULL,
        `class` int (10) UNSIGNED NOT NULL,
        `course` int (10) UNSIGNED NOT NULL,
        `result` varchar(50) DEFAULT NULL,
        `new_result` varchar(255) DEFAULT NULL,
        `total` decimal(10, 2) DEFAULT NULL,
        `grade` varchar(10) DEFAULT NULL,
        `remark` varchar(50) DEFAULT NULL,
        `comment` varchar(255) DEFAULT NULL,
        `passed` tinyint (1) DEFAULT NULL,
        `date_modified` datetime DEFAULT NULL,
        `modified_by` int (11) DEFAULT NULL,
        `approved` int (11) DEFAULT NULL,
        `date_approved` datetime DEFAULT NULL,
        `approved_by` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `saved_report`
--
CREATE TABLE
    IF NOT EXISTS `saved_report` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(100) DEFAULT NULL,
        `itype` varchar(50) DEFAULT NULL,
        `itable` varchar(50) DEFAULT NULL,
        `idcol` varchar(50) DEFAULT NULL,
        `datecol` varchar(50) DEFAULT NULL,
        `dateTitle` varchar(50) DEFAULT NULL,
        `others` text DEFAULT NULL,
        `group_others` text DEFAULT NULL,
        `graph` varchar(50) DEFAULT NULL,
        `param` text DEFAULT NULL,
        `isource` varchar(255) DEFAULT NULL,
        `idummy` varchar(255) DEFAULT NULL,
        `igroup` text DEFAULT NULL,
        `datacol` varchar(255) DEFAULT NULL,
        `datacol_label` text DEFAULT NULL,
        `icondition` text DEFAULT NULL,
        `idetail` text DEFAULT NULL,
        `ifilter` text DEFAULT NULL,
        `icombine` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_alumni_table`
--
CREATE TABLE
    IF NOT EXISTS `school_alumni_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `username` varchar(75) DEFAULT NULL,
        `password` varchar(25) DEFAULT NULL,
        `registration_no` int (10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_calender`
--
CREATE TABLE
    IF NOT EXISTS `school_calender` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `subject` varchar(250) DEFAULT NULL,
        `date` date DEFAULT NULL,
        `timeFrom` time DEFAULT NULL,
        `timeTo` time DEFAULT NULL,
        `venue` varchar(250) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `reminder` int (11) DEFAULT NULL,
        `reminderTime` datetime DEFAULT NULL,
        `ReminderAudience` int (11) DEFAULT NULL,
        `ReminderSelect` text DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `personWith` int (11) DEFAULT NULL,
        `confirmed` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_chat_table`
--
CREATE TABLE
    IF NOT EXISTS `school_chat_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `message` text DEFAULT NULL,
        `username` varchar(75) DEFAULT NULL,
        `chatroom` int (10) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_forum_table`
--
CREATE TABLE
    IF NOT EXISTS `school_forum_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `subject` varchar(150) DEFAULT NULL,
        `content` text DEFAULT NULL,
        `type` tinyint (3) UNSIGNED DEFAULT NULL,
        `parent` text DEFAULT NULL,
        `username` varchar(75) DEFAULT NULL,
        `date_posted` date DEFAULT NULL,
        `number_replies` int (10) UNSIGNED DEFAULT NULL,
        `number_views` int (10) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_news_table`
--
CREATE TABLE
    IF NOT EXISTS `school_news_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `subject` varchar(250) DEFAULT NULL,
        `content` text DEFAULT NULL,
        `date_posted` date DEFAULT NULL,
        `user` int (11) DEFAULT NULL,
        `publish` int (11) DEFAULT NULL,
        `audience` int (11) DEFAULT NULL,
        `views` int (11) DEFAULT NULL,
        `pic_ref` varchar(250) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_pictures`
--
CREATE TABLE
    IF NOT EXISTS `school_pictures` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `pic` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_prefix`
--
CREATE TABLE
    IF NOT EXISTS `school_prefix` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `student` varchar(255) DEFAULT NULL,
        `staff` varchar(255) DEFAULT NULL,
        `alumni` varchar(255) DEFAULT NULL,
        `regstart` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_settings_table`
--
CREATE TABLE
    IF NOT EXISTS `school_settings_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(155) DEFAULT NULL,
        `year` year (4) DEFAULT NULL,
        `term` tinyint (4) DEFAULT NULL,
        `remote_server` varchar(155) DEFAULT NULL,
        `website` varchar(155) DEFAULT NULL,
        `short_name` varchar(155) DEFAULT NULL,
        `username` varchar(155) DEFAULT NULL,
        `password` varchar(155) DEFAULT NULL,
        `initialize` tinyint (4) DEFAULT NULL,
        `conform` text DEFAULT NULL,
        `formdetails` text DEFAULT NULL,
        `dummy1` text DEFAULT NULL,
        `address` varchar(255) DEFAULT NULL,
        `city` varchar(255) DEFAULT NULL,
        `state` varchar(255) DEFAULT NULL,
        `country` varchar(255) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `student_prefix` varchar(255) DEFAULT NULL,
        `staff_prefix` varchar(255) DEFAULT NULL,
        `alumni_prefix` varchar(255) DEFAULT NULL,
        `last_reg` varchar(255) DEFAULT NULL,
        `school_logo` varchar(255) DEFAULT NULL,
        `coverImage` varchar(255) DEFAULT NULL,
        `result_template` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `school_timetable`
--
CREATE TABLE
    IF NOT EXISTS `school_timetable` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `school_start_time` time DEFAULT NULL,
        `school_end_time` time DEFAULT NULL,
        `brake_start_time` time DEFAULT NULL,
        `brake_end_time` time DEFAULT NULL,
        `duration_course_period` tinyint (3) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `score_grade_table`
--
CREATE TABLE
    IF NOT EXISTS `score_grade_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `grade_symbol` char(2) DEFAULT NULL,
        `start` tinyint (4) DEFAULT NULL,
        `remark` varchar(15) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `section`
--
CREATE TABLE
    IF NOT EXISTS `section` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `department` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `section_table`
--
CREATE TABLE
    IF NOT EXISTS `section_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `sent_messages`
--
CREATE TABLE
    IF NOT EXISTS `sent_messages` (
        `sent_messageId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `type` varchar(20) DEFAULT NULL,
        `sent_to` text DEFAULT NULL,
        `message` text DEFAULT NULL,
        `userid` int (11) DEFAULT NULL,
        `Date` datetime DEFAULT NULL,
        PRIMARY KEY (`sent_messageId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `skill_table`
--
CREATE TABLE
    IF NOT EXISTS `skill_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `skill_name` char(45) DEFAULT NULL,
        `type` int (11) DEFAULT NULL,
        `level` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_access_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_access_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `username` varchar(100) DEFAULT NULL,
        `password` varchar(30) DEFAULT NULL,
        `ref_no` int (10) UNSIGNED NOT NULL,
        `access_level` tinyint (4) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_account_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_account_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff` int (11) DEFAULT NULL,
        `bank` int (11) DEFAULT NULL,
        `account_no` varchar(150) DEFAULT NULL,
        `branch` varchar(150) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_allowance`
--
CREATE TABLE
    IF NOT EXISTS `staff_allowance` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_ref` int (11) DEFAULT NULL,
        `allowance` int (11) DEFAULT NULL,
        `status` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_anual_leave_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_anual_leave_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `ref_no` int (10) UNSIGNED NOT NULL,
        `begining_date` date DEFAULT NULL,
        `ending_date` date DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_bank_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_bank_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_course_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_course_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `ref_no` int (10) UNSIGNED NOT NULL,
        `course` int (11) DEFAULT NULL,
        `class` int (11) DEFAULT NULL,
        `year` int (11) DEFAULT NULL,
        `term` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_grade_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_grade_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `grade_name` varchar(15) DEFAULT NULL,
        `grade_number` tinyint (4) DEFAULT NULL,
        `basic_salary` double UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_payroll`
--
CREATE TABLE
    IF NOT EXISTS `staff_payroll` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_ref` int (11) DEFAULT NULL,
        `allowance` int (11) DEFAULT NULL,
        `year` int (11) DEFAULT NULL,
        `month` int (11) DEFAULT NULL,
        `amount` float (10, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_query_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_query_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `query_subject` varchar(150) DEFAULT NULL,
        `query_body` text DEFAULT NULL,
        `ref_no` int (10) UNSIGNED NOT NULL,
        `date_issued` date DEFAULT NULL,
        `comment` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_record`
--
CREATE TABLE
    IF NOT EXISTS `staff_record` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `picture_ref` varchar(100) DEFAULT NULL,
        `surname` varchar(50) DEFAULT NULL,
        `first_name` varchar(50) DEFAULT NULL,
        `middle` varchar(50) DEFAULT NULL,
        `sex` varchar(50) DEFAULT NULL,
        `birthdate` date DEFAULT NULL,
        `address` varchar(200) DEFAULT NULL,
        `city` varchar(255) DEFAULT NULL,
        `state` int (11) DEFAULT NULL,
        `country` varchar(255) DEFAULT NULL,
        `phone` varchar(70) DEFAULT NULL,
        `email` varchar(70) DEFAULT NULL,
        `staff_no` varchar(50) DEFAULT NULL,
        `religion` varchar(30) DEFAULT NULL,
        `marital_status` varchar(10) DEFAULT NULL,
        `local_government_origin` varchar(25) DEFAULT NULL,
        `state_origin` varchar(25) DEFAULT NULL,
        `nationality` varchar(25) DEFAULT NULL,
        `town` varchar(25) DEFAULT NULL,
        `health_status` varchar(100) DEFAULT NULL,
        `past_record` text DEFAULT NULL,
        `past_record2` text DEFAULT NULL,
        `p_record` text DEFAULT NULL,
        `work_record` text DEFAULT NULL,
        `referees` text DEFAULT NULL,
        `additional` text DEFAULT NULL,
        `registrationtime` datetime DEFAULT NULL,
        `kin_name` varchar(50) DEFAULT NULL,
        `kin_address` text DEFAULT NULL,
        `kin_email` varchar(50) DEFAULT NULL,
        `kin_phone_no` varchar(55) DEFAULT NULL,
        `date_employed` date DEFAULT NULL,
        `status` varchar(100) DEFAULT NULL,
        `health_appraisal` text DEFAULT NULL,
        `appraisal` text DEFAULT NULL,
        `grade` int (11) DEFAULT NULL,
        `department` int (11) DEFAULT NULL,
        `section` int (11) DEFAULT NULL,
        `designation` int (11) DEFAULT NULL,
        `password` varchar(50) DEFAULT NULL,
        `access_level` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_relief`
--
CREATE TABLE
    IF NOT EXISTS `staff_relief` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_ref` int (11) DEFAULT NULL,
        `relief` int (11) DEFAULT NULL,
        `status` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_sack_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_sack_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `ref_no` int (10) UNSIGNED NOT NULL,
        `date_sacked` date DEFAULT NULL,
        `reason` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `staff_suspension_table`
--
CREATE TABLE
    IF NOT EXISTS `staff_suspension_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `ref_no` int (10) UNSIGNED NOT NULL,
        `date_suspended` date DEFAULT NULL,
        `recall_date` date DEFAULT NULL,
        `reason` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `state`
--
CREATE TABLE
    IF NOT EXISTS `state` (
        `stateId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(200) DEFAULT NULL,
        `country` int (11) DEFAULT NULL,
        PRIMARY KEY (`stateId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `statutory_allowance`
--
CREATE TABLE
    IF NOT EXISTS `statutory_allowance` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `allowance` int (11) DEFAULT NULL,
        `status` int (11) DEFAULT NULL,
        `amount` float (10, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_drop_table`
--
CREATE TABLE
    IF NOT EXISTS `students_drop_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `date_dropped` date DEFAULT NULL,
        `reason` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_expulsion_table`
--
CREATE TABLE
    IF NOT EXISTS `students_expulsion_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `date_expelled` date DEFAULT NULL,
        `reason` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_hostel_table`
--
CREATE TABLE
    IF NOT EXISTS `students_hostel_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `hostel` int (10) UNSIGNED NOT NULL,
        `term` tinyint (3) UNSIGNED DEFAULT NULL,
        `year` year (4) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_payment_record`
--
CREATE TABLE
    IF NOT EXISTS `students_payment_record` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `payment_record` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_promotion_criteria_table`
--
CREATE TABLE
    IF NOT EXISTS `students_promotion_criteria_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `courses_min` text DEFAULT NULL,
        `no_passed` int (11) DEFAULT NULL,
        `strict` int (11) DEFAULT NULL,
        `type` int (10) UNSIGNED DEFAULT NULL,
        `pass_mark` tinyint (3) UNSIGNED DEFAULT NULL,
        `level` int (11) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_record`
--
CREATE TABLE
    IF NOT EXISTS `students_record` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `picture_ref` varchar(100) DEFAULT NULL,
        `surname` varchar(50) DEFAULT NULL,
        `first_name` varchar(50) DEFAULT NULL,
        `middle` varchar(50) DEFAULT NULL,
        `sex` varchar(50) DEFAULT NULL,
        `birthdate` date DEFAULT NULL,
        `address` varchar(200) DEFAULT NULL,
        `city` int (11) DEFAULT NULL,
        `state` int (11) DEFAULT NULL,
        `country` int (11) DEFAULT NULL,
        `email` varchar(70) DEFAULT NULL,
        `registration_no` varchar(50) DEFAULT NULL,
        `religion` varchar(30) DEFAULT NULL,
        `guardian_name` varchar(50) DEFAULT NULL,
        `guardian_address` text DEFAULT NULL,
        `guardian_email` varchar(50) DEFAULT NULL,
        `guardian_phone_no` varchar(55) DEFAULT NULL,
        `local_government_origin` varchar(25) DEFAULT NULL,
        `state_origin` varchar(25) DEFAULT NULL,
        `nationality` varchar(25) DEFAULT NULL,
        `health_status` text DEFAULT NULL,
        `date_admitted` year (4) DEFAULT NULL,
        `status` varchar(100) DEFAULT NULL,
        `past_record` text DEFAULT NULL,
        `result` text DEFAULT NULL,
        `student_class` int (11) DEFAULT NULL,
        `student_level` int (11) DEFAULT NULL,
        `password` varchar(50) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_registration_table`
--
CREATE TABLE
    IF NOT EXISTS `students_registration_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `course_signature` text DEFAULT NULL,
        `class` int (10) UNSIGNED NOT NULL,
        `level` tinyint (3) UNSIGNED DEFAULT NULL,
        `term` tinyint (3) UNSIGNED DEFAULT NULL,
        `year` year (4) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_skill_table`
--
CREATE TABLE
    IF NOT EXISTS `students_skill_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `year` year (4) DEFAULT NULL,
        `term` tinyint (4) DEFAULT NULL,
        `reg_no` int (10) UNSIGNED NOT NULL,
        `skill` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_suspension_table`
--
CREATE TABLE
    IF NOT EXISTS `students_suspension_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `date_suspended` date DEFAULT NULL,
        `recall_date` date DEFAULT NULL,
        `reason` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `students_transfer_table`
--
CREATE TABLE
    IF NOT EXISTS `students_transfer_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `registration_no` int (10) UNSIGNED NOT NULL,
        `date_transfered` date DEFAULT NULL,
        `school` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `student_access_table`
--
CREATE TABLE
    IF NOT EXISTS `student_access_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `username` varchar(100) DEFAULT NULL,
        `password` varchar(30) DEFAULT NULL,
        `ref_no` int (10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `sync_table`
--
CREATE TABLE
    IF NOT EXISTS `sync_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `query` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `tax_rates`
--
CREATE TABLE
    IF NOT EXISTS `tax_rates` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `rate` float (10, 2) DEFAULT NULL,
        `start_limit` float (10, 2) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `transactions`
--
CREATE TABLE
    IF NOT EXISTS `transactions` (
        `tid` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `memo` varchar(255) DEFAULT NULL,
        `ref` varchar(100) DEFAULT NULL,
        `method` varchar(100) DEFAULT NULL,
        `date` date DEFAULT NULL,
        `year` year (4) DEFAULT NULL,
        `term` int (11) DEFAULT NULL,
        `c_type` int (11) DEFAULT NULL,
        `cid` varchar(100) DEFAULT NULL,
        `cref` varchar(100) DEFAULT NULL,
        `name` varchar(255) DEFAULT NULL,
        `level` int (11) DEFAULT NULL,
        `class` int (11) DEFAULT NULL,
        `quantity` float (12, 2) DEFAULT NULL,
        `it_id` varchar(100) DEFAULT NULL,
        `it_type` varchar(100) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `unit_price` float (12, 2) DEFAULT NULL,
        `amount_due` float (10, 2) DEFAULT NULL,
        `amount` float (10, 2) DEFAULT NULL,
        `net_due` float (10, 2) DEFAULT NULL,
        `account` varchar(100) DEFAULT NULL,
        `account_name` varchar(255) DEFAULT NULL,
        `glaccount` varchar(100) DEFAULT NULL,
        `discount_account` varchar(100) DEFAULT NULL,
        `discount_amount` float (10, 2) DEFAULT NULL,
        `trans_no` varchar(255) DEFAULT NULL,
        `s_no` varchar(255) DEFAULT NULL,
        `trans_type` varchar(50) DEFAULT NULL,
        `sub` int (11) DEFAULT NULL,
        `approved` int (11) DEFAULT NULL,
        `status` int (11) NOT NULL,
        `user` int (11) DEFAULT NULL,
        PRIMARY KEY (`tid`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `voucher_bonus_table`
--
CREATE TABLE
    IF NOT EXISTS `voucher_bonus_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `percentage` double (6, 3) UNSIGNED DEFAULT NULL,
        `amount` double DEFAULT NULL,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `voucher_deduction_table`
--
CREATE TABLE
    IF NOT EXISTS `voucher_deduction_table` (
        `id` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `percentage` double (6, 3) UNSIGNED DEFAULT NULL,
        `amount` double DEFAULT NULL,
        `name` varchar(100) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

-- --------------------------------------------------------
--
-- Table structure for table `ward`
--
CREATE TABLE
    IF NOT EXISTS `ward` (
        `wardId` int (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `Name` varchar(20) DEFAULT NULL,
        `lga` int (11) DEFAULT NULL,
        PRIMARY KEY (`wardId`)
    ) ENGINE = InnoDB DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;