-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2024 at 02:03 PM
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
-- Database: `agrimatch`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `question_id`, `user_id`, `comment`, `created_at`) VALUES
(89, 23, 9, 'Increasing vegetable crop yield involves implementing several key practices to optimize growth conditions. First, selecting high-quality seeds or seedlings and choosing crop varieties suited to the local climate can significantly enhance productivity. Proper soil preparation, including testing and amending the soil with organic matter or fertilizers, ensures that plants receive essential nutrients. Regular irrigation, especially during dry spells, is critical, but overwatering should be avoided to prevent root diseases. Additionally, applying mulch helps conserve moisture, suppress weeds, and regulate soil temperature. Effective pest and disease management, through integrated pest management (IPM) techniques or organic alternatives, minimizes damage and loss. Crop rotation also helps maintain soil fertility and prevents the build-up of pests and diseases. Finally, timely harvesting ensures that vegetables are picked at their peak, reducing waste and ensuring higher yields over time.', '2024-10-26 10:05:42'),
(90, 24, 9, 'In the Philippines, several rice harvesters are recognized for their efficiency, durability, and suitability for local farming conditions. The Kubota DC-70 is one of the most popular models due to its powerful engine, high threshing capacity, and suitability for wet or dry fields, making it ideal for the Philippines\' varied terrain. Another excellent option is the Yanmar AW82, known for its efficient fuel consumption and easy-to-operate controls, providing farmers with both cost savings and productivity. The DAEDONG combine harvester also offers impressive performance, particularly in smaller and less accessible fields due to its compact design. Additionally, the PhilRice-developed mechanical rice harvesters are widely used because they are tailored specifically for Filipino farmers, with an emphasis on affordability and ease of maintenance. These harvesters help reduce manual labor, speed up harvesting processes, and minimize post-harvest losses, which is crucial for improving productivity in rice farming.', '2024-10-26 10:11:58'),
(91, 24, 10, 'gello', '2024-11-19 13:58:38'),
(92, 23, 9, 'yesyes', '2024-11-24 03:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `user1_id` int(11) DEFAULT NULL,
  `user2_id` int(11) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `question_id`, `user1_id`, `user2_id`, `started_at`) VALUES
(138, NULL, 9, 10, '2024-11-23 14:39:29'),
(139, NULL, 9, 13, '2024-11-23 15:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_end`
--

CREATE TABLE `conversation_end` (
  `conversation_id` int(11) DEFAULT NULL,
  `ended_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_end`
--

INSERT INTO `conversation_end` (`conversation_id`, `ended_at`, `id`) VALUES
(138, '2024-11-24 13:01:25', 23);

-- --------------------------------------------------------

--
-- Table structure for table `cryptocurrencies`
--

CREATE TABLE `cryptocurrencies` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `current_value` decimal(10,4) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `change_percentage` decimal(5,2) DEFAULT NULL,
  `starting_value` decimal(10,4) NOT NULL DEFAULT 0.1500
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cryptocurrencies`
--

INSERT INTO `cryptocurrencies` (`id`, `name`, `symbol`, `current_value`, `last_updated`, `change_percentage`, `starting_value`) VALUES
(1, 'Stars', 'STAR', 0.1410, '2024-11-24 12:48:41', -6.03, 0.1500);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `caption` text DEFAULT NULL,
  `media_type` enum('photo','video','none') DEFAULT 'none',
  `media_name` varchar(255) DEFAULT NULL,
  `date_posted` datetime DEFAULT current_timestamp(),
  `hearts_received` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `caption`, `media_type`, `media_name`, `date_posted`, `hearts_received`) VALUES
(13, 9, '1. Climate Change and Crop Resilience\r\n•	Developing drought-resistant crops:\r\no	Example: Scientists are using genetic engineering to introduce genes from drought-tolerant plants (like desert succulents) into crops like maize and wheat. This can enhance their ability to survive with less water.\r\no	Another Example: Researchers are selecting and breeding varieties of rice that have deeper root systems, allowing them to access water deeper in the soil during dry periods.\r\n•	Improving heat tolerance:\r\no	Example: Researchers are studying the genes that control heat shock proteins in crops like soybeans. These proteins protect plants from damage during heat waves, and identifying the genes involved can help breed more heat-tolerant varieties.\r\no	Another Example: Scientists are developing crop varieties with altered photosynthetic pathways (like C4 photosynthesis) that are more efficient in high temperatures, leading to better yields in hot climates.\r\n•	Developing saline agriculture:\r\no	Example: Researchers are investigating salt-tolerant plants like quinoa and barley to identify the genes responsible for their salt tolerance. These genes can potentially be introduced into other crops.\r\no	Another Example: Scientists are exploring the use of halophytes (salt-loving plants) as crops in saline-affected areas. These plants can be used for food, fodder, or biofuel production.\r\n2. Sustainable Agriculture and Crop Production\r\n•	Improving soil health:\r\no	Example: Studies are evaluating the benefits of cover cropping (planting non-cash crops between main crops) on soil health. Cover crops can help prevent erosion, improve soil structure, and increase nutrient content.\r\no	Another Example: Research is ongoing to optimize no-till farming practices, which involve planting crops without disturbing the soil. This can improve soil health, reduce water loss, and sequester carbon.\r\n•	Integrated pest management:\r\no	Example: Scientists are developing biological control methods using natural predators (like ladybugs for aphid control) to reduce reliance on chemical pesticides.\r\no	Another Example: Researchers are exploring the use of pheromone traps to disrupt insect mating and reduce pest populations without harming beneficial insects.\r\n•	Precision agriculture:\r\no	Example: Farmers are using drones equipped with sensors to monitor crop health, identify areas needing irrigation or fertilizer, and optimize resource use.\r\no	Another Example: GPS-guided tractors are being used for precise planting and application of inputs, minimizing waste and environmental impact.\r\n3. Crop Improvement and Biotechnology\r\n•	Genetic engineering and gene editing:\r\no	Example: Scientists have developed genetically modified (GM) crops like Bt cotton, which produces its own insecticide, reducing the need for chemical sprays.\r\no	Another Example: CRISPR-Cas9 gene editing technology is being used to develop disease-resistant varieties of crops like bananas and potatoes.\r\n•	Marker-assisted selection:\r\no	Example: Researchers are using DNA markers to identify genes for disease resistance in wheat. This allows breeders to select and crossbreed varieties with improved resistance more efficiently.\r\no	Another Example: Marker-assisted selection is being used to improve the nutritional content of crops like rice by identifying genes responsible for higher vitamin A content.\r\n•	Plant phenotyping:\r\no	Example: High-throughput phenotyping platforms use automated imaging and data analysis to assess plant traits like growth rate, leaf area, and root architecture. This helps identify superior varieties for breeding programs.\r\no	Another Example: Researchers are using thermal imaging to detect early signs of water stress in crops, allowing for timely irrigation and preventing yield loss.\r\n4. Crop Diversification and Food Security\r\n•	Exploring underutilized crops:\r\no	Example: Scientists are studying the potential of neglected crops like fonio (a drought-tolerant grain) and moringa (a nutrient-rich leafy vegetable) to improve food security in arid regions.\r\no	Another Example: Research is ongoing to evaluate the nutritional and agronomic properties of underutilized legumes and grains to diversify diets and promote sustainable agriculture.\r\n•	Developing climate-resilient and nutritious varieties:\r\no	Example: Plant breeders are developing new varieties of traditional crops like sorghum and millet with improved drought tolerance, pest resistance, and nutritional content.\r\no	Another Example: Scientists are working to enhance the bioavailability of essential nutrients in staple crops like cassava and sweet potato through biofortification.\r\n•	Promoting sustainable cropping systems:\r\no	Example: Research is focused on promoting intercropping systems (growing multiple crops together) to enhance biodiversity, improve soil health, and reduce pest pressure.\r\no	Another Example: Agroforestry systems, which integrate trees with crops, are being studied for their potential to provide shade, improve soil fertility, and enhance carbon sequestration.\r\nI hope these examples provide a clearer picture of the exciting research happening in each of these crop science areas!', 'photo', '1732416545_Gemini_Generated_Image_8bhgfr8bhgfr8bhg.jpg', '2024-11-24 10:49:05', 2);

-- --------------------------------------------------------

--
-- Table structure for table `post_hearts`
--

CREATE TABLE `post_hearts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_hearts`
--

INSERT INTO `post_hearts` (`id`, `user_id`, `post_id`) VALUES
(30, 9, 13),
(32, 10, 13);

-- --------------------------------------------------------

--
-- Table structure for table `private_messages`
--

CREATE TABLE `private_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `question_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `private_messages`
--

INSERT INTO `private_messages` (`id`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `sent_at`, `question_id`) VALUES
(126, 132, 9, 10, 'hedd', '2024-11-23 14:26:22', NULL),
(127, 133, 9, 10, 'asdf', '2024-11-23 14:27:09', NULL),
(128, 134, 9, 10, 'asdf', '2024-11-23 14:27:19', NULL),
(129, 135, 9, 10, 'asdfasdf', '2024-11-23 14:29:56', NULL),
(130, 136, 9, 10, 'asdf', '2024-11-23 14:31:09', NULL),
(131, 137, 9, 10, 'asdf', '2024-11-23 14:32:36', NULL),
(132, 138, 9, 10, 'gago', '2024-11-23 14:39:29', NULL),
(133, 138, 10, 9, 'haha', '2024-11-23 14:39:44', NULL),
(134, 139, 9, 13, 'hello po', '2024-11-23 15:13:53', NULL),
(135, 138, 10, 9, 'low', '2024-11-24 03:12:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `topic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `user_id`, `question`, `created_at`, `topic`) VALUES
(23, 10, 'How to increase vegetables crop yield?', '2024-10-26 10:05:20', 'Crop Science'),
(24, 11, 'What are the best rice harvesters in the Philippines?', '2024-10-26 10:09:55', 'Agricultural Extension'),
(49, 10, 'What are the best soil for vegetables', '2024-11-24 03:06:25', 'Soils'),
(50, 10, 'How to get rid of black bugs', '2024-11-24 03:13:07', 'Crop Protection'),
(51, 10, 'How to increase chicken egg yield', '2024-11-24 03:13:27', 'Animal Science');

-- --------------------------------------------------------

--
-- Table structure for table `stars_given`
--

CREATE TABLE `stars_given` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `stars_given` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stars_given`
--

INSERT INTO `stars_given` (`id`, `user_id`, `comment_id`, `stars_given`) VALUES
(33, 10, 89, 21),
(38, 10, 90, 5);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `expiry_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan`, `price`, `expiry_date`) VALUES
(21, 11, 'basic', 49, '2025-10-26 00:00:00'),
(22, 9, 'premium', 1416, '2025-11-02 00:00:00'),
(24, 13, 'premium', 1416, '2025-11-23 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `verified` varchar(255) NOT NULL DEFAULT 'not_verified',
  `topics` varchar(255) NOT NULL DEFAULT 'None',
  `question_asked` int(11) DEFAULT 0,
  `last_question_time` datetime DEFAULT NULL,
  `date_verified` date DEFAULT NULL,
  `ads_watched` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `profile_picture`, `role`, `verified`, `topics`, `question_asked`, `last_question_time`, `date_verified`, `ads_watched`) VALUES
(9, 'Expert', '$2y$10$9TsrYC0yxPAQgSxzBqAtLOHtgnvjsiCSE6YTQ/ggZzVDeg4fZtoU2', NULL, '2024-10-26 09:39:52', '9_671cb9bc4457d0.26937736.png', 'user', 'verified', 'Soils', 0, '2024-11-23 15:04:10', '2022-11-23', 0),
(10, 'user', '$2y$10$0s4SmcEZtNnCe0u2m.4wd.OrOtrhMHUMneN60wqujPVCfKLFne0qe', NULL, '2024-10-26 09:44:54', '10_6725811d2891f0.27793472.jpg', 'user', 'not_verified', 'crop science', 2, '2024-11-24 11:13:27', NULL, 1),
(11, 'Roberto', '$2y$10$JfqfpPO9rgW3NKLJIbOWYe11UK0eji.5/Ba8Gn5L3eCjaTARObq1G', NULL, '2024-10-26 10:08:04', '11_671cc051489062.53911391.png', 'user', 'not_verified', '', 0, '2024-11-23 15:04:10', NULL, 0),
(12, 'asdfasdf', '$2y$10$kV4in1PEVKsnfln/qwElmOXpvcgQBnXySQ4r3HPmZpTKXxkJbvSBm', NULL, '2024-11-02 11:13:37', 'default.jpg', 'user', 'not_verified', '', 0, '2024-11-23 15:04:10', NULL, 0),
(13, 'Admin', '$2y$10$WZoeoYLzBcTrSbr7WzxSg.Wp8N4wf5VSWVGG6.rWeU/kr6J7jqoZC', NULL, '2024-11-19 13:46:21', 'logo.jpg', 'admin', 'verified', '', 0, '2024-11-23 15:04:10', NULL, 0),
(15, 'zxcv', '$2y$10$nsJSF8hRML20yeNE3yqhpuGt9bRiBq/3MALmZu6wSKvUOHbZSHjBK', NULL, '2024-11-23 15:06:53', 'default.jpg', 'user', 'not_verified', 'None', 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_stars`
--

CREATE TABLE `user_stars` (
  `user_id` int(11) NOT NULL,
  `stars` int(11) NOT NULL DEFAULT 0,
  `stars_received` int(11) DEFAULT 0,
  `Income` decimal(12,2) DEFAULT 0.00,
  `badge` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_stars`
--

INSERT INTO `user_stars` (`user_id`, `stars`, `stars_received`, `Income`, `badge`) VALUES
(9, 0, 240, 1.71, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_end`
--
ALTER TABLE `conversation_end`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cryptocurrencies`
--
ALTER TABLE `cryptocurrencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_hearts`
--
ALTER TABLE `post_hearts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `private_messages`
--
ALTER TABLE `private_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stars_given`
--
ALTER TABLE `stars_given`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_stars`
--
ALTER TABLE `user_stars`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `conversation_end`
--
ALTER TABLE `conversation_end`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cryptocurrencies`
--
ALTER TABLE `cryptocurrencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `post_hearts`
--
ALTER TABLE `post_hearts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `private_messages`
--
ALTER TABLE `private_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `stars_given`
--
ALTER TABLE `stars_given`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `post_hearts`
--
ALTER TABLE `post_hearts`
  ADD CONSTRAINT `post_hearts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_hearts_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stars_given`
--
ALTER TABLE `stars_given`
  ADD CONSTRAINT `stars_given_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stars_given_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_stars`
--
ALTER TABLE `user_stars`
  ADD CONSTRAINT `user_stars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
