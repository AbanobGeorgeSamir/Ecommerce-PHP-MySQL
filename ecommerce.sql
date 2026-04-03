
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";






CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Abanob George', 'samirabanob848@gmail.com', 'hello', '2026-03-30 12:47:23'),
(2, 'Abanob George', 'abanobgeorge662@gmail.com', 'hello world', '2026-04-01 20:21:53'),
(3, 'Abanob George', 'abanobgeorge662@gmail.com', 'hello from web', '2026-04-01 21:32:46'),
(4, 'Abanob George', 'abanobgeorge662@gmail.com', 'hello from web', '2026-04-01 22:13:06'),
(5, 'abanobasdfsaffsa', 'abanobgeorge662@gmail.com', 'fsasfafsd', '2026-04-01 22:22:16');



CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `total` decimal(10,2) NOT NULL CHECK (`total` >= 0),
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `email`, `address`, `total`, `status`, `created_at`) VALUES
(1, NULL, 'Abanob George', 'fjksdfklsdjflksj@gmail.com', 'street', 0.00, 'pending', '2026-03-30 12:22:54'),
(2, NULL, 'Abanob George', 'samirabanob848@gmail.com', '1021', 0.00, 'pending', '2026-03-30 12:24:43'),
(3, NULL, 'Abanob George', 'abanob@gmail.com', '102', 0.00, 'pending', '2026-03-30 12:27:36'),
(4, NULL, 'Abanob', 'abanob@gmail.com', '102', 3600.00, 'pending', '2026-03-30 12:30:08'),
(5, 4, 'abnaob george samir', 'samirabanob848@gmail.com', 'egypt, eltor', 120.00, 'pending', '2026-04-01 21:20:52'),
(6, 4, 'Abanob George', 'abanobgeorge662@gmail.com', 'egypt, eltor', 120.00, 'pending', '2026-04-01 21:25:53'),
(7, NULL, 'abanob george samir', 'abanobgeorge332@gmail.com', 'egypt, eltor', 120.00, 'pending', '2026-04-01 22:21:46');



CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(2, 5, 11, 1, 120.00),
(3, 6, 11, 1, 120.00),
(4, 7, 11, 1, 120.00);

DELIMITER $$
CREATE TRIGGER `reduce_stock_after_order` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    UPDATE products
    SET stock = stock - NEW.quantity
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;


CREATE TABLE `order_summary` (
`order_id` int(11)
,`customer_name` varchar(150)
,`total` decimal(10,2)
,`status` enum('pending','processing','shipped','delivered','cancelled')
,`product_name` varchar(255)
,`quantity` int(11)
);



CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0),
  `stock` int(11) DEFAULT 0 CHECK (`stock` >= 0),
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `created_at`) VALUES
(11, 'RGB Keyboard', 'this is a keyboard', 120.00, 8, 'uploads/1775078398_12570554c603.webp', '2026-04-01 21:19:58');



CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(4, 'Abanob George', 'samirabanob848@gmail.com', '$2y$12$z.nJyReZ9aosDydHiJ.ebO9sX3v7zRydslwEMPhDKCw3At.1dUifi', 'admin', '2026-04-01 21:14:10'),
(5, 'Abanob George', 'abanobgeorge662@gmail.com', '$2y$12$3ngw6I7sqjD1e0d6eB4ICuY3H5xacYMtfd5Ot66n6B0N3aRTgkFJy', 'user', '2026-04-01 21:32:22');


ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`);

ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_name` (`name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);


ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


DROP TABLE IF EXISTS `order_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_summary`  AS SELECT `o`.`id` AS `order_id`, `o`.`customer_name` AS `customer_name`, `o`.`total` AS `total`, `o`.`status` AS `status`, `p`.`name` AS `product_name`, `oi`.`quantity` AS `quantity` FROM ((`orders` `o` join `order_items` `oi` on(`o`.`id` = `oi`.`order_id`)) join `products` `p` on(`oi`.`product_id` = `p`.`id`)) ;


ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

