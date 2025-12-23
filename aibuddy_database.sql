-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 23, 2025 lúc 09:04 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `aibuddy_database`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `AdminName` varchar(100) DEFAULT NULL,
  `AdminEmail` varchar(100) DEFAULT NULL,
  `AdminPassword` varchar(255) DEFAULT NULL,
  `Role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin`
--

INSERT INTO `admin` (`AdminID`, `AdminName`, `AdminEmail`, `AdminPassword`, `Role`) VALUES
(2, 'admin', 'admin@email.com', '123456', 'SuperAdmin');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `badge`
--

CREATE TABLE `badge` (
  `BadgeID` int(11) NOT NULL,
  `BadgeName` varchar(100) DEFAULT NULL,
  `BadgeSymbol` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `badgecondition`
--

CREATE TABLE `badgecondition` (
  `ConditionID` int(11) NOT NULL,
  `BadgeID` int(11) NOT NULL,
  `ConditionTypeID` int(11) NOT NULL,
  `ConditionValue` varchar(100) DEFAULT NULL,
  `BadgeDescription` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chathistory`
--

CREATE TABLE `chathistory` (
  `ChatID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PersonaID` int(11) DEFAULT NULL,
  `TopicID` int(11) DEFAULT NULL,
  `ChatTime` datetime DEFAULT current_timestamp(),
  `MessageContent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chathistory`
--

INSERT INTO `chathistory` (`ChatID`, `UserID`, `PersonaID`, `TopicID`, `ChatTime`, `MessageContent`) VALUES
(1, 101, 2, 2, '2025-12-13 12:07:15', 'Làm sao để sửa lỗi kết nối database trong PHP vậy?'),
(2, 101, 2, 2, '2025-12-13 13:07:15', 'Cảm ơn, mình đã cấu hình lại file config và chạy ổn rồi.'),
(3, 102, 1, 6, '2025-12-12 17:07:15', 'Gợi ý cho mình thực đơn eat clean trong 7 ngày để giảm cân nhé.'),
(4, 102, 1, 6, '2025-12-12 18:07:15', 'Mình bị dị ứng hải sản, bạn đổi món khác được không?'),
(5, 103, 3, 5, '2025-12-13 16:37:15', 'Viết giúp mình một đoạn caption thả thính hài hước đăng Facebook.'),
(6, 104, 1, 3, '2025-12-11 17:07:15', 'Mình đang buồn vì cãi nhau với người yêu, hãy cho mình lời khuyên.'),
(7, 101, 2, 4, '2025-12-13 16:57:15', 'Giải thích ngắn gọn về nguyên lý hoạt động của Blockchain.'),
(8, 103, 3, 1, '2025-12-13 17:07:15', 'Kể cho tôi nghe một câu chuyện cười ngắn đi.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatmessages`
--

CREATE TABLE `chatmessages` (
  `MessageID` int(11) NOT NULL,
  `SessionID` int(11) NOT NULL,
  `Sender` enum('User','AI') NOT NULL,
  `Content` text NOT NULL,
  `ImagePath` varchar(255) DEFAULT NULL,
  `AudioUrl` varchar(255) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chatmessages`
--

INSERT INTO `chatmessages` (`MessageID`, `SessionID`, `Sender`, `Content`, `ImagePath`, `AudioUrl`, `CreatedAt`) VALUES
(100, 66, 'User', 'hello', NULL, NULL, '2025-12-22 21:52:06'),
(101, 66, 'AI', 'Hello there. It\'s good to connect with you today for your daily reflection.\n\nI\'m here to help you explore your thoughts and feelings from the day in a calm, objective way, using principles of Cognitive Behavioral Therapy.\n\nTo begin, perhaps you could share a little about what\'s been on your mind today, or any particular experience or thought that stands out to you from your day so far?', NULL, NULL, '2025-12-22 21:52:08'),
(102, 67, 'AI', 'Hello! I\'m ready to chat about Creative Writing.', NULL, NULL, '2025-12-22 21:53:43'),
(103, 68, 'AI', 'Hello. Academic life often comes with its unique set of pressures, whether it\'s navigating exams, managing deadlines, or preparing for presentations. I\'m here to listen. What\'s been on your mind recently regarding your academic journey?', NULL, NULL, '2025-12-22 21:53:50'),
(104, 68, 'User', 'Hello', NULL, NULL, '2025-12-22 21:53:56'),
(105, 68, 'AI', 'Hello again. I\'m here and ready to listen. As I mentioned, academic life can certainly bring its share of pressures. What thoughts or feelings have been particularly present for you recently regarding your academic journey, or perhaps any specific situations that have felt challenging?', NULL, NULL, '2025-12-22 21:53:59'),
(106, 69, 'AI', 'Hello. It\'s a valuable practice to take a moment and check in with ourselves. To start our daily reflection, I wonder, what\'s one thought or feeling that has been most present for you today?', NULL, NULL, '2025-12-22 22:13:21'),
(107, 70, 'User', 'hello', NULL, NULL, '2025-12-23 14:41:46'),
(108, 71, 'User', 'humanoid', NULL, NULL, '2025-12-23 14:42:14'),
(109, 72, 'User', 'I\'m happy', NULL, NULL, '2025-12-23 14:42:19'),
(110, 73, 'AI', 'Hello! I\'m ready to chat about Academic Stress.', NULL, NULL, '2025-12-23 14:45:59'),
(111, 74, 'AI', 'Hello! I\'m ready to chat about Tài chính.', NULL, NULL, '2025-12-23 14:55:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatsessions`
--

CREATE TABLE `chatsessions` (
  `SessionID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PersonaID` int(11) NOT NULL,
  `TopicID` int(11) DEFAULT NULL,
  `Title` varchar(100) DEFAULT 'New Conversation',
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chatsessions`
--

INSERT INTO `chatsessions` (`SessionID`, `UserID`, `PersonaID`, `TopicID`, `Title`, `CreatedAt`) VALUES
(66, 121, 2, 1, 'Daily Reflection', '2025-12-22 21:52:06'),
(67, 121, 2, 5, 'Topic: Creative Writing', '2025-12-22 21:53:41'),
(68, 121, 2, 4, 'Topic: Academic Stress', '2025-12-22 21:53:46'),
(69, 122, 2, 1, 'Topic: Daily Reflection', '2025-12-22 22:13:17'),
(70, 129, 1, 1, 'Daily Reflection', '2025-12-23 14:41:46'),
(71, 129, 1, 1, 'Daily Reflection', '2025-12-23 14:42:14'),
(72, 129, 1, 1, 'Daily Reflection', '2025-12-23 14:42:19'),
(73, 129, 2, 4, 'Topic: Academic Stress', '2025-12-23 14:45:59'),
(74, 129, 1, 9, 'Topic: Tài chính', '2025-12-23 14:55:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `conditiontype`
--

CREATE TABLE `conditiontype` (
  `ConditionTypeID` int(11) NOT NULL,
  `ConditionName` varchar(100) DEFAULT NULL,
  `ConditionDescription` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `emotionentry`
--

CREATE TABLE `emotionentry` (
  `EntryID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `IconID` int(11) DEFAULT NULL,
  `EntryTime` datetime DEFAULT current_timestamp(),
  `EmotionDescription` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `emotionentry`
--

INSERT INTO `emotionentry` (`EntryID`, `UserID`, `IconID`, `EntryTime`, `EmotionDescription`) VALUES
(2, 111, 5, '2025-12-20 08:40:08', 'ádsa'),
(3, 111, 1, '2025-12-20 10:02:08', 'đâsd'),
(4, 112, 4, '2025-12-20 10:05:18', 'hôm nay tao buồn'),
(5, 115, 1, '2025-12-20 12:14:22', 'hôm nay t đ vui'),
(6, 116, 8, '2025-12-20 15:37:43', 'jshdajshdjahsd'),
(7, 116, 3, '2025-12-20 15:38:23', 'hádhashd'),
(8, 116, 1, '2025-12-20 15:38:27', 'jksjdsjd'),
(9, 116, 2, '2025-12-18 15:38:55', 'sạdjashd'),
(10, 116, 2, '2025-12-22 15:39:02', ''),
(11, 116, 4, '2025-12-21 15:39:11', ''),
(12, 118, 4, '2025-12-20 18:27:21', 'sdasdasd'),
(13, 118, 4, '2025-12-21 18:27:27', ''),
(14, 118, 2, '2025-12-21 18:27:32', ''),
(15, 118, 8, '2025-12-21 18:27:38', ''),
(16, 118, 8, '2025-12-19 18:27:43', ''),
(17, 124, 2, '2025-12-22 17:30:14', 'hello'),
(18, 124, 4, '2025-12-23 17:30:26', 'fun'),
(19, 124, 1, '2025-12-24 17:30:40', 'im angry'),
(20, 129, 3, '2025-12-23 08:44:26', 'tôi mệt'),
(21, 129, 8, '2025-12-24 08:44:44', 'tôi vui'),
(22, 129, 4, '2025-12-25 08:45:02', 'ok'),
(23, 129, 6, '2025-12-26 08:45:13', 'vui');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `form`
--

CREATE TABLE `form` (
  `FormID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `AdminID` int(11) DEFAULT NULL,
  `FormTopic` varchar(100) DEFAULT NULL,
  `FormContent` text DEFAULT NULL,
  `FormStatus` varchar(20) DEFAULT NULL,
  `FormCreationTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `form`
--

INSERT INTO `form` (`FormID`, `UserID`, `AdminID`, `FormTopic`, `FormContent`, `FormStatus`, `FormCreationTime`) VALUES
(1, 110, NULL, 'Technical Support', 'máy bị lag', 'Pending', '2025-12-20 12:59:22');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `icon`
--

CREATE TABLE `icon` (
  `IconID` int(11) NOT NULL,
  `IconName` varchar(50) DEFAULT NULL,
  `IconSymbol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `icon`
--

INSERT INTO `icon` (`IconID`, `IconName`, `IconSymbol`) VALUES
(1, 'Angry', '😠'),
(2, 'Sad', '😔'),
(3, 'Tired', '🥱'),
(4, 'Okay', '🙂'),
(5, 'Calm', '😌'),
(6, 'Joyful', '😊'),
(8, 'Happy', '😄');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `meditationsession`
--

CREATE TABLE `meditationsession` (
  `SessionID` int(11) NOT NULL,
  `PlanID` int(11) NOT NULL,
  `SessionName` varchar(100) DEFAULT NULL,
  `SessionType` varchar(50) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `membership`
--

CREATE TABLE `membership` (
  `MembershipID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PlanID` int(11) NOT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `MembershipStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `persona`
--

CREATE TABLE `persona` (
  `PersonaID` int(11) NOT NULL,
  `PersonaName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `SystemPrompt` text DEFAULT NULL,
  `Icon` varchar(50) DEFAULT '?',
  `IsPremium` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `persona`
--

INSERT INTO `persona` (`PersonaID`, `PersonaName`, `Description`, `SystemPrompt`, `Icon`, `IsPremium`) VALUES
(1, 'Bestie', 'Always by your side', 'You are a supportive, empathetic best friend. You use casual language, emojis, and slang appropriate for Gen Z. Your goal is to listen and validate the user\'s feelings without being too clinical. Always reply in English.', '👯', 0),
(2, 'Therapist', 'Professional CBT support', 'You are a professional psychologist using Cognitive Behavioral Therapy (CBT) techniques. Help the user analyze their thoughts objectively. Be calm, professional, and ask guiding questions. Always reply in English.', '🧠', 1),
(8, 'Beloved Family', 'Where you belong to ...', 'bạn là gia đình của tôi', '👪', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `plan`
--

CREATE TABLE `plan` (
  `PlanID` int(11) NOT NULL,
  `PlanName` varchar(100) NOT NULL,
  `PlanDescription` text DEFAULT NULL,
  `PlanPrice` decimal(10,2) DEFAULT NULL,
  `BillingCycle` varchar(20) DEFAULT NULL,
  `PlanVideoURL` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `plan`
--

INSERT INTO `plan` (`PlanID`, `PlanName`, `PlanDescription`, `PlanPrice`, `BillingCycle`, `PlanVideoURL`) VALUES
(1, 'Free', 'Basic features available (2 trials)', 0.00, 'Daily', 'https://www.youtube.com/watch?v=bXyPSlZPDiY'),
(2, 'Essential', 'Unlock advanced featured.', 99000.00, 'Monthly', 'https://www.youtube.com/watch?v=hpomJDXnHZE'),
(3, 'Premium Vip', 'Unlocked exclusive feature. ???', 980000.00, 'Monthly', ''),
(8, 'Gói cao cấp', 'cao cấp', 10000000.00, 'Daily', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `planfeature`
--

CREATE TABLE `planfeature` (
  `FeatureID` int(11) NOT NULL,
  `PlanID` int(11) NOT NULL,
  `FeatureDescription` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `planfeature`
--

INSERT INTO `planfeature` (`FeatureID`, `PlanID`, `FeatureDescription`) VALUES
(1, 1, '2 short sessions (13 minutes)'),
(2, 1, 'Breathing Animation'),
(3, 1, 'Basic Chatbot'),
(4, 2, '5 sessions'),
(5, 2, 'Emotion Chat + Mood Chart'),
(6, 2, 'Select multiple persona + topic (counselor, parents...)'),
(7, 3, '30 diverse sessions'),
(8, 3, 'Select multiple persona + topic (psychologists...)'),
(9, 3, 'Automatic focus reminders'),
(10, 3, 'Analyze emotional trends'),
(11, 3, 'Deeper Emotional Insights');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `refundrequest`
--

CREATE TABLE `refundrequest` (
  `RefundID` int(11) NOT NULL,
  `TransactionID` int(11) NOT NULL,
  `RefundType` varchar(20) DEFAULT NULL,
  `RefundAmount` decimal(10,2) DEFAULT NULL,
  `RefundDetails` text DEFAULT NULL,
  `EvidencePath` varchar(255) DEFAULT NULL,
  `RefundStatus` varchar(20) DEFAULT NULL,
  `AdminResponse` text DEFAULT NULL,
  `RequestDate` datetime DEFAULT current_timestamp(),
  `UpdatedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `refundrequest`
--

INSERT INTO `refundrequest` (`RefundID`, `TransactionID`, `RefundType`, `RefundAmount`, `RefundDetails`, `EvidencePath`, `RefundStatus`, `AdminResponse`, `RequestDate`, `UpdatedDate`) VALUES
(3, 1, 'Sai gói dịch vụ', 500000.00, 'Tôi mua nhầm gói doanh nghiệp thay vì cá nhân', 'images/evidence/ev1.jpg', 'Pending', NULL, '2025-12-13 15:39:47', NULL),
(8, 17, 'Service Not Working', 90000.00, 'quá lag', NULL, 'Pending', NULL, '2025-12-20 21:43:38', NULL),
(9, 24, 'Service Not Working', 990000.00, 'ádasd', NULL, 'Pending', NULL, '2025-12-21 00:07:13', NULL),
(10, 59, 'Accidental Purchase', 99000.00, 'tôi mua nhầm', NULL, 'Pending', NULL, '2025-12-23 14:47:03', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `report`
--

CREATE TABLE `report` (
  `ReportID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `AdminID` int(11) DEFAULT NULL,
  `ReportType` varchar(50) DEFAULT NULL,
  `ReportContent` text DEFAULT NULL,
  `ReportStartTime` datetime DEFAULT NULL,
  `ReportEndTime` datetime DEFAULT NULL,
  `ReportTime` datetime DEFAULT current_timestamp(),
  `Status` varchar(20) DEFAULT 'Pending',
  `AdminResponse` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `report`
--

INSERT INTO `report` (`ReportID`, `UserID`, `AdminID`, `ReportType`, `ReportContent`, `ReportStartTime`, `ReportEndTime`, `ReportTime`, `Status`, `AdminResponse`) VALUES
(1, 101, NULL, 'Lỗi kỹ thuật', 'Tôi không thể đăng nhập vào tài khoản premium.', '2025-12-13 15:28:40', '2025-12-13 15:28:40', '2025-12-13 15:28:40', 'Pending', 'im đi gobi'),
(2, 102, NULL, 'Nội dung xấu', 'Có người dùng đăng tải nội dung không phù hợp.', '2025-12-13 15:28:40', '2025-12-13 15:28:40', '2025-12-13 15:28:40', 'Processed', 'kệ nó đi'),
(3, 103, NULL, 'Thanh toán', 'Tôi đã thanh toán nhưng chưa được nâng cấp gói.', '2025-12-13 15:28:40', '2025-12-13 15:28:40', '2025-12-13 15:28:40', 'Resolved', NULL),
(4, 110, NULL, 'Technical Support', 'máy bị lag', '2025-12-20 13:05:41', '2025-12-20 13:05:41', '2025-12-20 13:05:41', 'Pending', 'kệ m'),
(5, 115, NULL, 'Lỗi kỹ thuật', 'lag', '2025-12-20 18:25:05', '2025-12-20 18:25:05', '2025-12-20 18:25:05', 'Pending', 'ok'),
(6, 116, NULL, 'Nội dung xấu', 'lag', '2025-12-20 21:45:19', '2025-12-20 21:45:19', '2025-12-20 21:45:19', 'Resolved', 'ok'),
(7, 116, NULL, 'Nội dung xấu', 'lag', '2025-12-20 21:45:44', '2025-12-20 21:45:44', '2025-12-20 21:45:44', 'Pending', NULL),
(8, 116, NULL, 'Nội dung xấu', 'lag', '2025-12-20 21:45:55', '2025-12-20 21:45:55', '2025-12-20 21:45:55', 'Pending', NULL),
(9, 129, NULL, 'Nội dung xấu', 'tôi thấy không phù hợp', '2025-12-23 14:46:33', '2025-12-23 14:46:33', '2025-12-23 14:46:33', 'Resolved', 'ok');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subscriptioncancel`
--

CREATE TABLE `subscriptioncancel` (
  `CancelID` int(11) NOT NULL,
  `MembershipID` int(11) NOT NULL,
  `CancellationType` varchar(20) DEFAULT NULL,
  `CancellationReason` text DEFAULT NULL,
  `CancellationTime` datetime DEFAULT current_timestamp(),
  `CancellationStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `subscriptioncancel`
--

INSERT INTO `subscriptioncancel` (`CancelID`, `MembershipID`, `CancellationType`, `CancellationReason`, `CancellationTime`, `CancellationStatus`) VALUES
(17, 33, 'Immediate', 'I don’t use the service much', '2025-12-21 08:11:16', 'Pending'),
(18, 35, 'Immediate', '', '2025-12-22 21:52:30', 'Pending'),
(19, 48, 'Immediate', '', '2025-12-22 23:39:13', 'Pending'),
(20, 53, 'Immediate', '', '2025-12-23 00:08:24', 'Pending'),
(21, 54, 'Immediate', '', '2025-12-23 00:10:08', 'Pending'),
(22, 55, 'Immediate', '', '2025-12-23 14:06:49', 'Pending'),
(23, 57, 'Immediate', '', '2025-12-23 14:07:53', 'Pending');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `topic`
--

CREATE TABLE `topic` (
  `TopicID` int(11) NOT NULL,
  `TopicName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `topic`
--

INSERT INTO `topic` (`TopicID`, `TopicName`, `Description`) VALUES
(1, 'Daily Reflection', 'Check in with yourself today'),
(3, 'Relationship Issues', 'Advice on friends, family, and partners'),
(4, 'Academic Stress', 'Exams, deadlines, and study tips'),
(5, 'Creative Writing', 'Brainstorming and content ideas'),
(6, 'Health & Lifestyle', 'Wellness and healthy living tips'),
(8, 'Nói đủ thứ trên đời', 'Với chủ đề này, mày quậy lên cho tao. Nói trên trời dưới đất'),
(9, 'Tài chính', 'về các vấn đề tài chính');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `TransactionID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `PaymentStatus` varchar(20) DEFAULT NULL,
  `PaymentTime` datetime DEFAULT current_timestamp(),
  `Amount` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`TransactionID`, `OrderID`, `PaymentMethod`, `PaymentStatus`, `PaymentTime`, `Amount`) VALUES
(1, 1, 'Momo', 'Completed', '2023-10-25 09:35:00', NULL),
(2, 3, 'Ví nội bộ', 'Completed', '2023-10-27 10:05:00', NULL),
(3, 5, 'ZaloPay', 'Completed', '2023-10-29 08:25:00', NULL),
(4, 23, 'Credit Card', 'Completed', '2025-12-20 18:05:52', 99000.00),
(5, 24, 'Momo', 'Completed', '2025-12-20 18:07:02', 990000.00),
(6, 26, 'Bank Transfer', 'Completed', '2025-12-20 18:19:24', 990000.00),
(7, 27, 'Momo', 'Completed', '2025-12-20 18:26:55', 99000.00),
(8, 29, 'Momo', 'Completed', '2025-12-21 01:37:03', 99000.00),
(9, 30, 'Bank Transfer', 'Completed', '2025-12-21 01:54:21', 990000.00),
(10, 31, 'Credit Card', 'Completed', '2025-12-21 01:55:10', 99000.00),
(11, 32, 'Momo', 'Completed', '2025-12-21 01:56:46', 99000.00),
(13, 34, 'Credit Card', 'Completed', '2025-12-21 02:12:00', 99000.00),
(14, 35, 'Credit Card', 'Completed', '2025-12-22 15:25:23', 99000.00),
(15, 36, 'Credit Card', 'Completed', '2025-12-22 15:53:25', 99000.00),
(16, 37, 'Credit Card', 'Completed', '2025-12-22 16:13:02', 99000.00),
(17, 38, 'Credit Card', 'Completed', '2025-12-22 17:21:27', 990000.00),
(18, 39, 'Credit Card', 'Completed', '2025-12-22 17:25:08', 990000.00),
(19, 41, 'Credit Card', 'Completed', '2025-12-22 17:29:20', 99000.00),
(20, 42, 'Momo', 'Completed', '2025-12-22 17:31:17', 990000.00),
(21, 43, 'Credit Card', 'Completed', '2025-12-22 17:31:56', 99000.00),
(22, 44, 'Credit Card', 'Completed', '2025-12-22 17:34:40', 990000.00),
(23, 45, 'Credit Card', 'Completed', '2025-12-22 17:35:49', 99000.00),
(24, 46, 'Credit Card', 'Completed', '2025-12-22 17:37:55', 990000.00),
(25, 48, 'Credit Card', 'Completed', '2025-12-22 17:38:38', 99000.00),
(26, 49, 'Credit Card', 'Completed', '2025-12-22 17:39:21', 99000.00),
(27, 50, 'Credit Card', 'Completed', '2025-12-22 17:43:58', 99000.00),
(28, 51, 'Credit Card', 'Completed', '2025-12-22 17:45:53', 99000.00),
(29, 53, 'Credit Card', 'Completed', '2025-12-22 17:48:13', 99000.00),
(30, 55, 'Credit Card', 'Completed', '2025-12-22 18:10:20', 990000.00),
(31, 57, 'Credit Card', 'Completed', '2025-12-23 08:07:38', 99000.00),
(32, 59, 'Credit Card', 'Completed', '2025-12-23 08:42:58', 99000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `userbadge`
--

CREATE TABLE `userbadge` (
  `UserBadgeID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `BadgeID` int(11) NOT NULL,
  `ReceiveTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `userorder`
--

CREATE TABLE `userorder` (
  `OrderID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PlanID` int(11) NOT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `OrderStatus` varchar(20) DEFAULT NULL,
  `PurchaseTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `userorder`
--

INSERT INTO `userorder` (`OrderID`, `UserID`, `PlanID`, `TotalAmount`, `OrderStatus`, `PurchaseTime`) VALUES
(1, 101, 2, 99000.00, 'Completed', '2023-10-25 09:30:00'),
(3, 103, 1, 0.00, 'Completed', '2023-10-27 10:00:00'),
(5, 104, 2, 99000.00, 'Completed', '2023-10-29 08:20:00'),
(6, 114, 1, 0.00, 'Completed', '2025-12-20 10:31:59'),
(7, 114, 2, 99000.00, 'Completed', '2025-12-20 10:35:35'),
(8, 114, 3, 990000.00, 'Cancelled', '2025-12-20 10:40:10'),
(11, 115, 1, 0.00, 'Completed', '2025-12-20 12:17:14'),
(12, 115, 2, 99000.00, 'Completed', '2025-12-20 12:18:11'),
(15, 116, 1, 0.00, 'Completed', '2025-12-20 15:40:31'),
(16, 116, 2, 99000.00, 'Cancelled', '2025-12-20 15:41:17'),
(17, 116, 3, 90000.00, 'Completed', '2025-12-20 15:43:16'),
(18, 117, 1, 0.00, 'Cancelled', '2025-12-20 23:39:28'),
(23, 118, 2, 99000.00, 'Cancelled', '2025-12-20 18:05:52'),
(24, 118, 3, 990000.00, 'Cancelled', '2025-12-20 18:07:02'),
(25, 118, 1, 0.00, 'Completed', '2025-12-21 00:08:00'),
(26, 118, 3, 990000.00, 'Cancelled', '2025-12-20 18:19:24'),
(27, 118, 2, 99000.00, 'Completed', '2025-12-20 18:26:55'),
(28, 119, 1, 0.00, 'Completed', '2025-12-21 07:28:29'),
(29, 119, 2, 99000.00, 'Cancelled', '2025-12-21 01:37:03'),
(30, 119, 3, 990000.00, 'Cancelled', '2025-12-21 01:54:21'),
(31, 119, 2, 99000.00, 'Cancelled', '2025-12-21 01:55:10'),
(32, 119, 2, 99000.00, 'Cancelled', '2025-12-21 01:56:46'),
(34, 120, 2, 99000.00, 'Completed', '2025-12-21 02:12:00'),
(35, 121, 2, 99000.00, 'Cancelled', '2025-12-22 15:25:23'),
(36, 121, 2, 99000.00, 'Completed', '2025-12-22 15:53:25'),
(37, 122, 2, 99000.00, 'Completed', '2025-12-22 16:13:02'),
(38, 118, 3, 990000.00, 'Completed', '2025-12-22 17:21:27'),
(39, 123, 3, 990000.00, 'Completed', '2025-12-22 17:25:08'),
(40, 124, 1, 0.00, 'Completed', '2025-12-22 23:28:05'),
(41, 124, 2, 99000.00, 'Completed', '2025-12-22 17:29:20'),
(42, 124, 3, 990000.00, 'Completed', '2025-12-22 17:31:17'),
(43, 124, 2, 99000.00, 'Completed', '2025-12-22 17:31:56'),
(44, 124, 3, 990000.00, 'Completed', '2025-12-22 17:34:40'),
(45, 124, 2, 99000.00, 'Completed', '2025-12-22 17:35:49'),
(46, 124, 3, 990000.00, 'Completed', '2025-12-22 17:37:55'),
(47, 123, 1, 0.00, 'Completed', '2025-12-22 23:38:27'),
(48, 123, 2, 99000.00, 'Cancelled', '2025-12-22 17:38:38'),
(49, 123, 2, 99000.00, 'Completed', '2025-12-22 17:39:21'),
(50, 125, 2, 99000.00, 'Completed', '2025-12-22 17:43:58'),
(51, 126, 2, 99000.00, 'Completed', '2025-12-22 17:45:53'),
(52, 127, 1, 0.00, 'Completed', '2025-12-22 23:47:24'),
(53, 127, 2, 99000.00, 'Cancelled', '2025-12-22 17:48:13'),
(54, 128, 1, 0.00, 'Cancelled', '2025-12-23 00:09:34'),
(55, 128, 3, 990000.00, 'Cancelled', '2025-12-22 18:10:20'),
(56, 128, 1, 0.00, 'Completed', '2025-12-23 14:07:01'),
(57, 128, 2, 99000.00, 'Cancelled', '2025-12-23 08:07:38'),
(58, 129, 1, 0.00, 'Completed', '2025-12-23 14:40:43'),
(59, 129, 2, 99000.00, 'Completed', '2025-12-23 08:42:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `UserEmail` varchar(100) NOT NULL,
  `UserPassword` varchar(255) NOT NULL,
  `BirthDate` date DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `UsageLeft` int(11) DEFAULT 0 COMMENT 'Số lần sử dụng còn lại',
  `IsTrialActive` tinyint(1) DEFAULT 0 COMMENT 'Đang dùng thử hay không',
  `secret_answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`UserID`, `UserName`, `UserEmail`, `UserPassword`, `BirthDate`, `PhoneNumber`, `Gender`, `UsageLeft`, `IsTrialActive`, `secret_answer`) VALUES
(101, 'Nguyễn Văn An', 'an.nguyen@example.com', '123456', '1995-05-15', '0901112222', 'Nam', 0, 0, ''),
(102, 'Trần Thị Bích', 'bich.tran@example.com', '123456', '1998-10-20', '0912333444', 'Nữ', 0, 0, ''),
(103, 'Lê Hoàng Nam', 'nam.le@example.com', '123456', '2000-01-01', '0988777666', 'Nam', 0, 0, ''),
(104, 'Phạm Thu Hà', 'ha.pham@example.com', '123456', '1992-12-12', '0905555888', 'Nữ', 0, 0, ''),
(106, 'nguyendeptrai', 'phamtrongnguyen04@gmail.com', '$2y$10$KyCfdmGqnH2RuqAehgYyBemEvXxKZXNqAPShWT3aESkVlPL0q.5/S', '2005-03-11', '0396043816', 'Other', 0, 2, ''),
(107, 'tnguyen', '123@gmail.com', '$2y$10$PfAexkL0EWHQZqhhk8QJ2.mlNVol7hzCOMqObeQxpjefNXIf7j2Lm', '2008-03-11', '1234567890', 'Male', 0, 0, ''),
(108, 'tuibidien', '113@gmail.com', '$2y$10$yXU/EGXB5Tc7By7IqbOOo.0RKsm3ul1GF9fJGk53IGRy1PtxF4IE.', '2005-03-11', '0396043816', 'Other', 0, 0, ''),
(110, 'tuibidien', 'tbd@gmail.com', '$2y$10$.Y3YDfmMShjJopPLFE1YIOP1Ra80Xl8Rcma.CRah05TTWgUA/PzCi', '2005-03-11', '0396043816', 'Other', 2, 1, ''),
(111, 'edward', 'edward@gmail.com', '$2y$10$DBj9DaY1/J9lbb9TDHpzvuNQWc7UMHE.o//ZW59PY/5DDrrdYRDRa', '2011-11-22', '0396043816', 'Male', 1, 1, 'cat'),
(112, 'deptrai', 'eddy@gmail.com', '$2y$10$haJu8WP1ceCXgWPyywbXZO5vYBTUY6/dmjyEdSN8UPUt6dUWjtL8e', '2005-03-11', '0396043816', 'Male', 2, 1, 'dog'),
(113, 'eddi', 'eddi@gmai.com', '$2y$10$3BW8ImopVEmUq2dqMTwUVe.87aIoaFVtqF.RcaY4NYkXQbItrFQKm', '2004-02-11', '1234567890', 'Male', 2, 1, 'frog'),
(114, 'edward1', 'edward1@gmail.com', '$2y$10$wqRn2B8bjxfO2p146A6oo.xWRzxKl3WvrhYvbb0hbtm8MgpdfrYfO', NULL, '32482374823784', NULL, 9999, 1, 'cat'),
(115, 'nguyen321', 'nguyen123@gmail.com', '$2y$10$IdZffX688mUhvDBZ3M1RyuJBKzzJAtUryITMhBI71B5gkExkk14Gq', '2005-03-11', '1234567890', 'Male', 9999, 1, 'cat'),
(116, 'nguyen3213', 'nguyen321@gmail.com', '$2y$10$O49FfXc4lpd3NeH.Zs8dPusN6ZRo9buulByLh7Vg3EyKJIgIA9cGK', '2005-03-11', '1234567890', 'Male', 9999, 1, 'chicken'),
(117, 'chu', 'chu@gmail.com', '$2y$10$iqK4mcr3usFPfxDYnl8X9.KTefcEQd8tPOsfQNsKxE05YqFyGs9A.', '2006-02-25', '1234567890', 'Female', 2, 1, 'cat'),
(118, 'chuchu', 'chuchu@gmail.com', '$2y$10$PgRhbpbhuqp7Hycv/A8CX.BcGr9nKM3QnajXgJGYdBIrl2XsuFs4G', '2006-02-25', '1234567890', 'Female', 9999, 1, 'cat'),
(119, 'phamnguyen', 'phamnguyen@gmail.com', '$2y$10$f114MZaEwS4CgJw8b8rhNeODdHwAytEqhfKsRRd6P.aM1Oujq/7Mu', '2005-03-11', '0396043816', 'Male', 9999, 1, 'cat'),
(120, 'MAI', 'dinhhothienbao1312@gmail.com', '$2y$10$tHSJ11IPpQEoFGU9F/tGDuw4TF4KHcxgF.JJdccrmCLIOMXjfbsyK', '2005-06-07', '0826360672', 'Female', 9999, 1, 'dog'),
(121, 'Dacien Nguyen', 'daciennguyeen1904@gmail.com', '$2y$10$C5P//RCN9dGa2ITAJF5ACeN.g/QS.QCROZOsxijV0a9.ooISmwq6i', '2005-04-19', '0901144599', 'Male', 9999, 1, 'UEH'),
(122, 'Dacien Ng', 'daciennguyen@gmail.com', '$2y$10$823IlfY73qL.mH1Q3KjH4.tzxw8ZNojggIewEf6hjiCtyC/xjB.ee', '2005-04-19', '0901144599', 'Male', 9999, 1, 'Dog'),
(123, 'hezo', 'hezo@gmail.com', '$2y$10$oocEGkLbf6sTe2Eybv.gZu1vXH2O6ryyeMmWbdoVHPIlOZzwQLYYe', '2005-02-11', '0396043816', 'Female', 9999, 1, 'cat'),
(124, 'phamtrongnguyen03', 'phamtrongnguyen03@gmail.com', '$2y$10$JnHfjApi760GdGg80sCOmeSXcrrrU9U73FSguQhmCQPHfFugA5ILu', '2005-03-11', '0396043816', 'Male', 9999, 1, 'cat'),
(125, 'pvk', 'pvk@gmail.com', '$2y$10$LIXlGV8ptqGPxQ8qr.Ec5.LAfDBqhhjnPvIpRODvOegp6U16k1VvC', '2005-03-11', '0396043816', 'Male', 9999, 1, 'pig'),
(126, 'ntd', 'ntd@gmail.com', '$2y$10$DLnqYgcCIFaKP1ZNhtsCZO4WKyyUUx5u3OX7w7Elnrx1XIK0tTNPW', '2004-02-11', '0396043816', 'Male', 9999, 1, 'hog'),
(127, 'ba', 'ba@gmail.com', '$2y$10$csx5Lbky6PX2oUMqoXI8VuRcnvBDf/8Y15JT4WPKqKONrl6mOxrhi', '2004-02-11', NULL, 'Male', 9999, 1, 'chó'),
(128, 'ptn', 'ptn@gmail.com', '$2y$10$Wtimn8bE3thsS14aUGObJulLg5LP8ebBQvtAeayBAQq.ZkzHnr09u', '2005-03-11', '0396043816', 'Male', 9999, 1, 'heo'),
(129, 'nguyen1234', 'nguyen1234@gmail.com', '$2y$10$fuM67P2bJuO3.kiMbXpT8uvgmiuTH4cU2fQjPFYqkih7Y9L0ukLaS', '2005-03-11', '0396043816', 'Male', 9999, 1, 'cat');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Chỉ mục cho bảng `badge`
--
ALTER TABLE `badge`
  ADD PRIMARY KEY (`BadgeID`);

--
-- Chỉ mục cho bảng `badgecondition`
--
ALTER TABLE `badgecondition`
  ADD PRIMARY KEY (`ConditionID`),
  ADD KEY `BadgeID` (`BadgeID`),
  ADD KEY `ConditionTypeID` (`ConditionTypeID`);

--
-- Chỉ mục cho bảng `chatmessages`
--
ALTER TABLE `chatmessages`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `SessionID` (`SessionID`);

--
-- Chỉ mục cho bảng `chatsessions`
--
ALTER TABLE `chatsessions`
  ADD PRIMARY KEY (`SessionID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PersonaID` (`PersonaID`),
  ADD KEY `TopicID` (`TopicID`);

--
-- Chỉ mục cho bảng `conditiontype`
--
ALTER TABLE `conditiontype`
  ADD PRIMARY KEY (`ConditionTypeID`);

--
-- Chỉ mục cho bảng `emotionentry`
--
ALTER TABLE `emotionentry`
  ADD PRIMARY KEY (`EntryID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `IconID` (`IconID`);

--
-- Chỉ mục cho bảng `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`FormID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Chỉ mục cho bảng `icon`
--
ALTER TABLE `icon`
  ADD PRIMARY KEY (`IconID`);

--
-- Chỉ mục cho bảng `meditationsession`
--
ALTER TABLE `meditationsession`
  ADD PRIMARY KEY (`SessionID`),
  ADD KEY `PlanID` (`PlanID`);

--
-- Chỉ mục cho bảng `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`MembershipID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PlanID` (`PlanID`);

--
-- Chỉ mục cho bảng `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`PersonaID`);

--
-- Chỉ mục cho bảng `plan`
--
ALTER TABLE `plan`
  ADD PRIMARY KEY (`PlanID`);

--
-- Chỉ mục cho bảng `planfeature`
--
ALTER TABLE `planfeature`
  ADD PRIMARY KEY (`FeatureID`),
  ADD KEY `PlanID` (`PlanID`);

--
-- Chỉ mục cho bảng `refundrequest`
--
ALTER TABLE `refundrequest`
  ADD PRIMARY KEY (`RefundID`),
  ADD KEY `TransactionID` (`TransactionID`);

--
-- Chỉ mục cho bảng `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Chỉ mục cho bảng `subscriptioncancel`
--
ALTER TABLE `subscriptioncancel`
  ADD PRIMARY KEY (`CancelID`),
  ADD KEY `fk_cancel_membership` (`MembershipID`);

--
-- Chỉ mục cho bảng `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`TopicID`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Chỉ mục cho bảng `userbadge`
--
ALTER TABLE `userbadge`
  ADD PRIMARY KEY (`UserBadgeID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `BadgeID` (`BadgeID`);

--
-- Chỉ mục cho bảng `userorder`
--
ALTER TABLE `userorder`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `PlanID` (`PlanID`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `UserEmail` (`UserEmail`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `badge`
--
ALTER TABLE `badge`
  MODIFY `BadgeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `badgecondition`
--
ALTER TABLE `badgecondition`
  MODIFY `ConditionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `chatmessages`
--
ALTER TABLE `chatmessages`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT cho bảng `chatsessions`
--
ALTER TABLE `chatsessions`
  MODIFY `SessionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT cho bảng `conditiontype`
--
ALTER TABLE `conditiontype`
  MODIFY `ConditionTypeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `emotionentry`
--
ALTER TABLE `emotionentry`
  MODIFY `EntryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `form`
--
ALTER TABLE `form`
  MODIFY `FormID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `icon`
--
ALTER TABLE `icon`
  MODIFY `IconID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `meditationsession`
--
ALTER TABLE `meditationsession`
  MODIFY `SessionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `membership`
--
ALTER TABLE `membership`
  MODIFY `MembershipID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `persona`
--
ALTER TABLE `persona`
  MODIFY `PersonaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `plan`
--
ALTER TABLE `plan`
  MODIFY `PlanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `planfeature`
--
ALTER TABLE `planfeature`
  MODIFY `FeatureID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `refundrequest`
--
ALTER TABLE `refundrequest`
  MODIFY `RefundID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `report`
--
ALTER TABLE `report`
  MODIFY `ReportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `subscriptioncancel`
--
ALTER TABLE `subscriptioncancel`
  MODIFY `CancelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `topic`
--
ALTER TABLE `topic`
  MODIFY `TopicID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `userbadge`
--
ALTER TABLE `userbadge`
  MODIFY `UserBadgeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `userorder`
--
ALTER TABLE `userorder`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `badgecondition`
--
ALTER TABLE `badgecondition`
  ADD CONSTRAINT `badgecondition_ibfk_1` FOREIGN KEY (`BadgeID`) REFERENCES `badge` (`BadgeID`),
  ADD CONSTRAINT `badgecondition_ibfk_2` FOREIGN KEY (`ConditionTypeID`) REFERENCES `conditiontype` (`ConditionTypeID`);

--
-- Các ràng buộc cho bảng `chatmessages`
--
ALTER TABLE `chatmessages`
  ADD CONSTRAINT `chatmessages_ibfk_1` FOREIGN KEY (`SessionID`) REFERENCES `chatsessions` (`SessionID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chatsessions`
--
ALTER TABLE `chatsessions`
  ADD CONSTRAINT `chatsessions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `chatsessions_ibfk_2` FOREIGN KEY (`PersonaID`) REFERENCES `persona` (`PersonaID`),
  ADD CONSTRAINT `chatsessions_ibfk_3` FOREIGN KEY (`TopicID`) REFERENCES `topic` (`TopicID`);

--
-- Các ràng buộc cho bảng `emotionentry`
--
ALTER TABLE `emotionentry`
  ADD CONSTRAINT `emotionentry_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `emotionentry_ibfk_2` FOREIGN KEY (`IconID`) REFERENCES `icon` (`IconID`);

--
-- Các ràng buộc cho bảng `form`
--
ALTER TABLE `form`
  ADD CONSTRAINT `form_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `form_ibfk_2` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`);

--
-- Các ràng buộc cho bảng `meditationsession`
--
ALTER TABLE `meditationsession`
  ADD CONSTRAINT `meditationsession_ibfk_1` FOREIGN KEY (`PlanID`) REFERENCES `plan` (`PlanID`);

--
-- Các ràng buộc cho bảng `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`PlanID`) REFERENCES `plan` (`PlanID`);

--
-- Các ràng buộc cho bảng `planfeature`
--
ALTER TABLE `planfeature`
  ADD CONSTRAINT `planfeature_ibfk_1` FOREIGN KEY (`PlanID`) REFERENCES `plan` (`PlanID`);

--
-- Các ràng buộc cho bảng `refundrequest`
--
ALTER TABLE `refundrequest`
  ADD CONSTRAINT `fk_refund_userorder` FOREIGN KEY (`TransactionID`) REFERENCES `userorder` (`OrderID`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`);

--
-- Các ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `userorder` (`OrderID`);

--
-- Các ràng buộc cho bảng `userbadge`
--
ALTER TABLE `userbadge`
  ADD CONSTRAINT `userbadge_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `userbadge_ibfk_2` FOREIGN KEY (`BadgeID`) REFERENCES `badge` (`BadgeID`);

--
-- Các ràng buộc cho bảng `userorder`
--
ALTER TABLE `userorder`
  ADD CONSTRAINT `userorder_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `userorder_ibfk_2` FOREIGN KEY (`PlanID`) REFERENCES `plan` (`PlanID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
