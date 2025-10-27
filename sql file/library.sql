-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 01:59 PM
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
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `id` int(11) NOT NULL,
  `AdminUserName` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `updationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`id`, `AdminUserName`, `Password`, `updationDate`) VALUES
(1, 'admin', 'admin', '2025-10-22 07:01:22');

-- --------------------------------------------------------

--
-- Table structure for table `tblauthors`
--

CREATE TABLE `tblauthors` (
  `id` int(11) NOT NULL,
  `AuthorName` varchar(150) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblauthors`
--

INSERT INTO `tblauthors` (`id`, `AuthorName`, `creationDate`, `updationDate`) VALUES
(1, 'Dennis M. Ritchie', '2025-10-22 07:01:22', '2025-10-24 14:41:09'),
(2, 'Thomas H. Cormen', '2025-10-22 07:01:22', NULL),
(4, 'D. Ravichandran', '2025-10-22 14:40:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblbookrequests`
--

CREATE TABLE `tblbookrequests` (
  `id` int(11) NOT NULL,
  `studentID` int(100) NOT NULL,
  `bookTitle` varchar(255) NOT NULL,
  `bookAuthor` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `bookISBN` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `requestDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` int(1) NOT NULL DEFAULT 0,
  `actionDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblbookrequests`
--

INSERT INTO `tblbookrequests` (`id`, `studentID`, `bookTitle`, `bookAuthor`, `publisher`, `bookISBN`, `reason`, `requestDate`, `status`, `actionDate`) VALUES
(1, 1, 'Designing Data-Intensive Applications', 'Martin Kleppmann', 'O\'Reilly Media', '9781449373320', 'Highly recommended for final-year students.', '2025-10-22 07:01:22', 1, '2025-10-25 12:21:37');

-- --------------------------------------------------------

--
-- Table structure for table `tblbooks`
--

CREATE TABLE `tblbooks` (
  `id` int(11) NOT NULL,
  `BookName` varchar(255) DEFAULT NULL,
  `CatId` int(11) DEFAULT NULL,
  `AuthorId` int(11) DEFAULT NULL,
  `PublisherId` int(11) DEFAULT NULL,
  `ISBNNumber` varchar(20) DEFAULT NULL,
  `BookPrice` decimal(10,2) DEFAULT NULL,
  `bookCopies` int(11) NOT NULL DEFAULT 10,
  `bookImage` longblob DEFAULT NULL,
  `regDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `isIssued` int(1) NOT NULL DEFAULT 0 COMMENT '0=Available, 1=Issued',
  `bookEdition` varchar(50) DEFAULT NULL COMMENT 'The edition of the book',
  `BookDescription` text DEFAULT NULL COMMENT 'Summary or detailed description of the book'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblbooks`
--

INSERT INTO `tblbooks` (`id`, `BookName`, `CatId`, `AuthorId`, `PublisherId`, `ISBNNumber`, `BookPrice`, `bookCopies`, `bookImage`, `regDate`, `updationDate`, `isIssued`, `bookEdition`, `BookDescription`) VALUES
(1, 'The C Programming Language', 1, 1, 1, '9780131103627', 450.00, 12, 0x635f6c616e675f636f7665722e6a7067, '2025-10-22 07:34:02', '2025-10-23 11:54:54', 0, '2nd Edition', 'Practical implementation of the C language. It defines the language\'s syntax, structure, standard library, and low-level concepts like pointers, memory management, and arrays.'),
(2, 'Introduction to Algorithms (CLRS)', 1, 2, 2, '9780262033848', 1200.00, 8, 0x616c676f726974686d735f636c72732e6a7067, '2025-10-22 07:34:02', '2025-10-25 13:38:08', 0, '3rd Edition', 'Theoretical foundation of algorithms, covering design techniques (divide-and-conquer, dynamic programming), mathematical analysis of running time (using Big O notation), and data structures.'),
(11, 'Programming with C++', 1, 4, 9, '978-0070681897', 850.00, 7, 0x626f6f6b5f363866636136376130313736322e6a7067, '2025-10-25 10:29:14', NULL, 0, '3rd Edition', 'This book introduces the syntax and features of C++ programming languages in a simple and easy-to-understand manner. The concepts are very well exemplified with program codes containing the inputs and outputs of the sample programs. The new edition has been thoroughly revised, updated and revamped as per the ANSI /ISO C++ standard. Key Features 5 new chapters Introduction to Object Oriented Programming Building ANSI C++ Programs STL - Containers STL - Iterators STL - Algorithms and Function Objects Enhanced coverage for topics like Datatypes, Arithmetic Operators, IO Streams, Functions and Program Structures, Special Member Functions, Exception Handling Pictorial representation in the form of syntax diagrams, flowcharts and Object Modeling Technique (OMT) class notation diagrams given Refreshed and Enhanced Pedagogy Programming Examples: 359 Concept Review Questions: 38 Review Questions: 439 Programming Exercises: 197 Table of Content 1. Introduction to Object Oriented Programming 2. Building ANSI C++ Program 3. Data Types, Operators and Expressions 4. Input and Output Streams 5. Control Statements 6. Functions and Program Structures 7. Arrays 8. Pointers and Strings 9. Structures, Unions and Bit Fields 10. Classes and Objects 11. Special Member Functions 12. Single and Multiple Inheritance 13. Overloading Functions and Operators 14. Polymorphism and Virtual Functions 15. Templates, Namespace and Exception Handling 16. Data File Operations ');

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL,
  `CategoryName` varchar(150) DEFAULT NULL,
  `Status` int(1) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`id`, `CategoryName`, `Status`, `CreationDate`, `UpdationDate`) VALUES
(1, 'Computer Science', 1, '2025-10-22 07:01:22', '2025-10-25 04:15:47'),
(2, 'Mechanical Engineering', 1, '2025-10-22 07:01:22', NULL),
(3, 'Science & Technology', 1, '2025-10-22 13:01:12', NULL),
(5, 'Business & Finance', 1, '2025-10-22 13:01:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblcontactmessages`
--

CREATE TABLE `tblcontactmessages` (
  `id` int(11) NOT NULL,
  `studentID` int(100) NOT NULL,
  `messageTitle` varchar(255) NOT NULL,
  `messageDetails` text DEFAULT NULL,
  `messageDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcontactmessages`
--

INSERT INTO `tblcontactmessages` (`id`, `studentID`, `messageTitle`, `messageDetails`, `messageDate`, `status`) VALUES
(1, 1, 'Missing Book from Shelf', 'I believe book ID 2 is misfiled in the wrong section.', '2025-10-22 07:01:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblissuedbookdetails`
--

CREATE TABLE `tblissuedbookdetails` (
  `id` int(11) NOT NULL,
  `BookID` int(11) DEFAULT NULL,
  `StudentID` int(150) DEFAULT NULL,
  `IssuesDate` timestamp NULL DEFAULT current_timestamp(),
  `ReturnDate` timestamp NULL DEFAULT NULL,
  `RetrunStatus` int(1) DEFAULT NULL,
  `fine` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblissuedbookdetails`
--

INSERT INTO `tblissuedbookdetails` (`id`, `BookID`, `StudentID`, `IssuesDate`, `ReturnDate`, `RetrunStatus`, `fine`) VALUES
(3, 1, 101, '2025-10-24 10:03:32', '2025-10-24 09:27:43', 1, 0.00),
(4, 2, 103, '2025-10-24 12:54:55', '2025-10-25 04:55:23', 1, 0.00),
(7, 2, 101, '2025-10-25 13:33:26', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbllibrarysettings`
--

CREATE TABLE `tbllibrarysettings` (
  `id` int(11) NOT NULL,
  `SettingName` varchar(100) DEFAULT NULL,
  `SettingValue` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbllibrarysettings`
--

INSERT INTO `tbllibrarysettings` (`id`, `SettingName`, `SettingValue`) VALUES
(1, 'AppName', 'Modern LMS'),
(2, 'FineRate', '10.00'),
(3, 'IssueDurationDays', '15'),
(4, 'LibraryContactEmail', 'library@collegename.edu'),
(5, 'hours_mon_fri', '9:00 AM to 6:00 PM'),
(6, 'hours_saturday', '9:00 AM to 2:00 PM'),
(7, 'closed_note', 'The Library remains Closed on Sundays and all public holidays.');

-- --------------------------------------------------------

--
-- Table structure for table `tblpublishers`
--

CREATE TABLE `tblpublishers` (
  `id` int(11) NOT NULL,
  `PublisherName` varchar(150) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblpublishers`
--

INSERT INTO `tblpublishers` (`id`, `PublisherName`, `creationDate`, `updationDate`) VALUES
(1, 'Prentice Hall', '2025-10-22 07:52:58', NULL),
(2, 'MIT Press', '2025-10-22 07:52:58', NULL),
(3, 'O’Reilly Media', '2025-10-22 07:52:58', NULL),
(4, 'Pearson', '2025-10-22 07:54:26', NULL),
(5, 'Ocean Publication', '2025-10-22 14:31:43', NULL),
(7, 'S. Chand & Company', '2025-10-22 14:31:43', NULL),
(8, 'Laxmi Publications', '2025-10-22 14:31:43', NULL),
(9, 'McGraw Hill Education', '2025-10-25 10:03:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblrequests`
--

CREATE TABLE `tblrequests` (
  `id` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL COMMENT 'References the Primary Key (id) in tblstudents',
  `BookID` int(11) NOT NULL COMMENT 'References the ID in tblbooks',
  `RequestDate` datetime NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending' COMMENT 'Possible values: Pending, Approved, Rejected',
  `ApprovalDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblrequests`
--

INSERT INTO `tblrequests` (`id`, `StudentID`, `BookID`, `RequestDate`, `Status`, `ApprovalDate`) VALUES
(6, 1, 2, '2025-10-22 18:59:53', 'Approved', '2025-10-25 19:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `tblstudents`
--

CREATE TABLE `tblstudents` (
  `id` int(11) NOT NULL,
  `StudentId` int(100) NOT NULL,
  `FullName` varchar(120) NOT NULL,
  `EmailId` varchar(120) NOT NULL,
  `MobileNumber` char(11) NOT NULL,
  `Password` varchar(120) NOT NULL,
  `Status` int(1) NOT NULL DEFAULT 1,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblstudents`
--

INSERT INTO `tblstudents` (`id`, `StudentId`, `FullName`, `EmailId`, `MobileNumber`, `Password`, `Status`, `RegDate`, `UpdationDate`) VALUES
(1, 101, 'Test', 'test@gmail.com', '9876453210', '123', 1, '2025-10-22 07:01:22', '2025-10-24 14:02:04'),
(6, 103, 'mad', 'mad@mad.com', '1234589760', '123', 1, '2025-10-22 10:33:02', '2025-10-24 06:07:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblauthors`
--
ALTER TABLE `tblauthors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblbookrequests`
--
ALTER TABLE `tblbookrequests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblbooks`
--
ALTER TABLE `tblbooks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcontactmessages`
--
ALTER TABLE `tblcontactmessages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblissuedbookdetails`
--
ALTER TABLE `tblissuedbookdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbllibrarysettings`
--
ALTER TABLE `tbllibrarysettings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblpublishers`
--
ALTER TABLE `tblpublishers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblrequests`
--
ALTER TABLE `tblrequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student_request` (`StudentID`),
  ADD KEY `fk_book_request` (`BookID`);

--
-- Indexes for table `tblstudents`
--
ALTER TABLE `tblstudents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `StudentId` (`StudentId`),
  ADD UNIQUE KEY `EmailId` (`EmailId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblauthors`
--
ALTER TABLE `tblauthors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblbookrequests`
--
ALTER TABLE `tblbookrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblbooks`
--
ALTER TABLE `tblbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblcontactmessages`
--
ALTER TABLE `tblcontactmessages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblissuedbookdetails`
--
ALTER TABLE `tblissuedbookdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbllibrarysettings`
--
ALTER TABLE `tbllibrarysettings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblpublishers`
--
ALTER TABLE `tblpublishers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tblrequests`
--
ALTER TABLE `tblrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblstudents`
--
ALTER TABLE `tblstudents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblrequests`
--
ALTER TABLE `tblrequests`
  ADD CONSTRAINT `fk_book_request` FOREIGN KEY (`BookID`) REFERENCES `tblbooks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_request` FOREIGN KEY (`StudentID`) REFERENCES `tblstudents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
