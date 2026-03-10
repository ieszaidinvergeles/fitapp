CREATE TABLE `activities` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255),
  `description` text,
  `intensity_level` varchar(255)
);

CREATE TABLE `body_metrics` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11),
  `date` date,
  `weight_kg` decimal(10,0),
  `height_cm` decimal(10,0),
  `body_fat_pct` decimal(10,0),
  `muscle_mass_pct` decimal(10,0)
);

CREATE TABLE `bookings` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `class_id` int(11),
  `user_id` int(11),
  `status` ENUM ('active', 'cancelled', 'attended', 'no_show'),
  `booked_at` timestamp NOT NULL DEFAULT (current_timestamp()),
  `cancelled_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
);

CREATE TABLE `classes` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `gym_id` int(11),
  `activity_id` int(11),
  `instructor_id` int(11),
  `room_id` int(11),
  `start_time` timestamp NOT NULL DEFAULT (current_timestamp()),
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `capacity_limit` int(11),
  `is_cancelled` tinyint(1) DEFAULT 0
);

CREATE TABLE `diet_plans` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255) COMMENT 'e.g. Definition, Bulk, Keto',
  `goal_description` text
);

CREATE TABLE `equipment` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255),
  `description` text,
  `is_home_accessible` tinyint(1) COMMENT 'True for mats, dumbbells, bands'
);

CREATE TABLE `exercises` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255),
  `description` text,
  `image_url` varchar(255),
  `video_url` varchar(255),
  `target_muscle_group` varchar(255)
);

CREATE TABLE `gyms` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255),
  `manager_id` int(11),
  `address` varchar(255),
  `city` varchar(255),
  `location_coords` varchar(255),
  `phone` varchar(255)
);

CREATE TABLE `gym_inventory` (
  `gym_id` int(11),
  `equipment_id` int(11),
  `quantity` int(11),
  `status` varchar(255) COMMENT 'operational, maintenance'
);

CREATE TABLE `logs_payo_no_se_usa` (
  `id` int(11) PRIMARY KEY NOT NULL
);

CREATE TABLE `membership_plans` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255),
  `type` varchar(255) COMMENT 'physical, online, duo',
  `allow_partner_link` tinyint(1) DEFAULT 0,
  `price` decimal(10,0)
);

CREATE TABLE `notifications` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `sender_id` int(11) COMMENT 'System or Admin/Manager',
  `title` varchar(255),
  `body` text,
  `target_audience` varchar(255) COMMENT 'global, staff_only, specific_gym, specific_user',
  `related_gym_id` int(11),
  `created_at` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `recipes` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `description` text,
  `ingredients` text,
  `preparation_steps` text,
  `calories` integer,
  `macros_json` json COMMENT '{protein: 30, carbs: 50, fat: 10}',
  `type` ENUM ('breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout'),
  `image_url` varchar(255)
);

CREATE TABLE `rooms` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `gym_id` int(11),
  `name` varchar(255),
  `capacity` int(11)
);

CREATE TABLE `routines` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `name` varchar(255),
  `description` text,
  `creator_id` int(11),
  `difficulty_level` varchar(255),
  `estimated_duration_min` int(11),
  `associated_diet_plan_id` int(11) COMMENT 'Optional link'
);

CREATE TABLE `routine_exercises` (
  `routine_id` int(11),
  `exercise_id` int(11),
  `order_index` int(11),
  `recommended_sets` int(11),
  `recommended_reps` int(11),
  `rest_seconds` int(11)
);

CREATE TABLE `settings` (
  `user_id` int(11) PRIMARY KEY NOT NULL,
  `share_workout_stats` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Share weights lifted/reps',
  `share_body_metrics` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Share weight/fat %',
  `share_attendance` tinyint(1) NOT NULL DEFAULT 1,
  `theme_preference` varchar(255) COMMENT 'light or dark',
  `language_preference` varchar(255)
);

CREATE TABLE `staff_attendance` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `staff_id` int(11),
  `gym_id` int(11),
  `clock_in` timestamp NOT NULL DEFAULT (current_timestamp()),
  `clock_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date` date
);

CREATE TABLE `users` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `username` varchar(255),
  `email` varchar(255),
  `password_hash` varchar(255),
  `role` ENUM ('admin', 'manager', 'staff', 'client', 'user_online'),
  `full_name` varchar(255),
  `dni` varchar(255),
  `birth_date` date,
  `profile_photo_url` varchar(255),
  `current_gym_id` int(11) COMMENT 'Home base gym. Null for online users',
  `membership_plan_id` int(11),
  `membership_status` varchar(255) COMMENT 'active, paused, expired',
  `cancellation_strikes` int(11) DEFAULT 0 COMMENT 'Count of late cancellations',
  `is_blocked_from_booking` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT (current_timestamp()),
  `updated_at` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `user_active_routines` (
  `user_id` int(11),
  `routine_id` int(11),
  `is_active` tinyint(1),
  `start_date` date
);

CREATE TABLE `user_favorites` (
  `user_id` int(11),
  `entity_type` varchar(255) COMMENT 'gym, activity, routine',
  `entity_id` int(11)
);

CREATE TABLE `user_meal_schedule` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `user_id` int(11),
  `date` date,
  `meal_type` ENUM ('breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout'),
  `recipe_id` int(11),
  `is_consumed` tinyint(1)
);

CREATE TABLE `user_partners` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `primary_user_id` int(11),
  `partner_user_id` int(11),
  `linked_at` timestamp NOT NULL DEFAULT (current_timestamp())
);

ALTER TABLE `body_metrics` ADD CONSTRAINT `body_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `bookings` ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

ALTER TABLE `bookings` ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `classes` ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `classes` ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`);

ALTER TABLE `classes` ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`);

ALTER TABLE `classes` ADD CONSTRAINT `classes_ibfk_4` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

ALTER TABLE `gyms` ADD CONSTRAINT `gyms_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

ALTER TABLE `gym_inventory` ADD CONSTRAINT `gym_inventory_ibfk_1` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `gym_inventory` ADD CONSTRAINT `gym_inventory_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

ALTER TABLE `notifications` ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

ALTER TABLE `notifications` ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `rooms` ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `routines` ADD CONSTRAINT `routines_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`);

ALTER TABLE `routines` ADD CONSTRAINT `routines_ibfk_2` FOREIGN KEY (`associated_diet_plan_id`) REFERENCES `diet_plans` (`id`);

ALTER TABLE `routine_exercises` ADD CONSTRAINT `routine_exercises_ibfk_1` FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`);

ALTER TABLE `routine_exercises` ADD CONSTRAINT `routine_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

ALTER TABLE `settings` ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `staff_attendance` ADD CONSTRAINT `staff_attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

ALTER TABLE `staff_attendance` ADD CONSTRAINT `staff_attendance_ibfk_2` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`current_gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plans` (`id`);

ALTER TABLE `user_active_routines` ADD CONSTRAINT `user_active_routines_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_active_routines` ADD CONSTRAINT `user_active_routines_ibfk_2` FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`);

ALTER TABLE `user_favorites` ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_meal_schedule` ADD CONSTRAINT `user_meal_schedule_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_meal_schedule` ADD CONSTRAINT `user_meal_schedule_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

ALTER TABLE `user_partners` ADD CONSTRAINT `user_partners_ibfk_1` FOREIGN KEY (`primary_user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_partners` ADD CONSTRAINT `user_partners_ibfk_2` FOREIGN KEY (`partner_user_id`) REFERENCES `users` (`id`);