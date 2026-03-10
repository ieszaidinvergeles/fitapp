CREATE TABLE `users` (
  `id` integer PRIMARY KEY,
  `username` varchar(255),
  `email` varchar(255) UNIQUE,
  `password_hash` varchar(255),
  `role` ENUM ('admin', 'manager', 'staff', 'client', 'user_online'),
  `full_name` varchar(255),
  `dni` varchar(255),
  `birth_date` date,
  `profile_photo_url` varchar(255),
  `theme_preference` varchar(255) COMMENT 'light or dark',
  `language_preference` varchar(255),
  `current_gym_id` integer COMMENT 'Home base gym. Null for online users',
  `membership_plan_id` integer,
  `membership_status` varchar(255) COMMENT 'active, paused, expired',
  `cancellation_strikes` integer DEFAULT 0 COMMENT 'Count of late cancellations',
  `is_blocked_from_booking` boolean DEFAULT false,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `membership_plans` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `type` varchar(255) COMMENT 'physical, online, duo',
  `allow_partner_link` boolean DEFAULT false,
  `price` decimal
);

CREATE TABLE `user_partners` (
  `id` integer PRIMARY KEY,
  `primary_user_id` integer,
  `partner_user_id` integer,
  `linked_at` timestamp
);

CREATE TABLE `privacy_settings` (
  `user_id` integer PRIMARY KEY,
  `share_workout_stats` boolean DEFAULT true COMMENT 'Share weights lifted/reps',
  `share_body_metrics` boolean DEFAULT false COMMENT 'Share weight/fat %',
  `share_attendance` boolean DEFAULT true
);

CREATE TABLE `gyms` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `manager_id` integer,
  `address` varchar(255),
  `city` varchar(255),
  `location_coords` varchar(255),
  `phone` varchar(255)
);

CREATE TABLE `rooms` (
  `id` integer PRIMARY KEY,
  `gym_id` integer,
  `name` varchar(255),
  `capacity` integer
);

CREATE TABLE `staff_attendance` (
  `id` integer PRIMARY KEY,
  `staff_id` integer,
  `gym_id` integer,
  `clock_in` timestamp,
  `clock_out` timestamp,
  `date` date
);

CREATE TABLE `notifications` (
  `id` integer PRIMARY KEY,
  `sender_id` integer COMMENT 'System or Admin/Manager',
  `title` varchar(255),
  `body` text,
  `target_audience` varchar(255) COMMENT 'global, staff_only, specific_gym, specific_user',
  `related_gym_id` integer,
  `created_at` timestamp
);

CREATE TABLE `user_notification_feed` (
  `id` integer PRIMARY KEY,
  `user_id` integer,
  `notification_id` integer,
  `is_read` boolean
);

CREATE TABLE `equipment` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `description` text,
  `is_home_accessible` boolean COMMENT 'True for mats, dumbbells, bands'
);

CREATE TABLE `gym_inventory` (
  `gym_id` integer,
  `equipment_id` integer,
  `quantity` integer,
  `status` varchar(255) COMMENT 'operational, maintenance'
);

CREATE TABLE `exercises` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `description` text,
  `image_url` varchar(255),
  `video_url` varchar(255),
  `target_muscle_group` varchar(255)
);

CREATE TABLE `exercise_requirements` (
  `exercise_id` integer,
  `equipment_id` integer
);

CREATE TABLE `routines` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `description` text,
  `creator_id` integer,
  `difficulty_level` varchar(255),
  `estimated_duration_min` integer,
  `associated_diet_plan_id` integer COMMENT 'Optional link'
);

CREATE TABLE `routine_exercises` (
  `routine_id` integer,
  `exercise_id` integer,
  `order_index` integer,
  `recommended_sets` integer,
  `recommended_reps` integer,
  `rest_seconds` integer
);

CREATE TABLE `user_active_routines` (
  `user_id` integer,
  `routine_id` integer,
  `is_active` boolean,
  `start_date` date
);

CREATE TABLE `workout_logs` (
  `id` integer PRIMARY KEY,
  `user_id` integer,
  `routine_id` integer,
  `exercise_id` integer,
  `date` timestamp,
  `weight_lifted` decimal,
  `reps_done` integer,
  `rpe` integer COMMENT 'Rate of Perceived Exertion 1-10'
);

CREATE TABLE `body_metrics` (
  `id` integer PRIMARY KEY,
  `user_id` integer,
  `date` date,
  `weight_kg` decimal,
  `height_cm` decimal,
  `body_fat_pct` decimal,
  `muscle_mass_pct` decimal
);

CREATE TABLE `activities` (
  `id` integer PRIMARY KEY,
  `name` varchar(255),
  `description` text,
  `intensity_level` varchar(255)
);

CREATE TABLE `classes` (
  `id` integer PRIMARY KEY,
  `gym_id` integer,
  `activity_id` integer,
  `instructor_id` integer,
  `room_id` integer,
  `start_time` timestamp,
  `end_time` timestamp,
  `capacity_limit` integer,
  `is_cancelled` boolean DEFAULT false
);

CREATE TABLE `bookings` (
  `id` integer PRIMARY KEY,
  `class_id` integer,
  `user_id` integer,
  `status` ENUM ('active', 'cancelled', 'attended', 'no_show'),
  `booked_at` timestamp,
  `cancelled_at` timestamp
);

CREATE TABLE `cancellation_logs` (
  `id` integer PRIMARY KEY,
  `booking_id` integer,
  `minutes_before_class` integer,
  `penalty_applied` boolean
);

CREATE TABLE `diet_plans` (
  `id` integer PRIMARY KEY,
  `name` varchar(255) COMMENT 'e.g. Definition, Bulk, Keto',
  `goal_description` text
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

CREATE TABLE `diet_plan_recipe_pool` (
  `diet_plan_id` integer,
  `recipe_id` integer
);

CREATE TABLE `user_meal_schedule` (
  `id` integer PRIMARY KEY,
  `user_id` integer,
  `date` date,
  `meal_type` ENUM ('breakfast', 'lunch', 'dinner', 'snack', 'pre_workout', 'post_workout'),
  `recipe_id` integer,
  `is_consumed` boolean
);

CREATE TABLE `user_favorites` (
  `user_id` integer,
  `entity_type` varchar(255) COMMENT 'gym, activity, routine',
  `entity_id` integer
);

ALTER TABLE `users` ADD FOREIGN KEY (`current_gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plans` (`id`);

ALTER TABLE `user_partners` ADD FOREIGN KEY (`primary_user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_partners` ADD FOREIGN KEY (`partner_user_id`) REFERENCES `users` (`id`);

ALTER TABLE `privacy_settings` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `gyms` ADD FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

ALTER TABLE `rooms` ADD FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `staff_attendance` ADD FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

ALTER TABLE `staff_attendance` ADD FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`related_gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `user_notification_feed` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_notification_feed` ADD FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`);

ALTER TABLE `gym_inventory` ADD FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `gym_inventory` ADD FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

ALTER TABLE `exercise_requirements` ADD FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

ALTER TABLE `exercise_requirements` ADD FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

ALTER TABLE `routines` ADD FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`);

ALTER TABLE `routines` ADD FOREIGN KEY (`associated_diet_plan_id`) REFERENCES `diet_plans` (`id`);

ALTER TABLE `routine_exercises` ADD FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`);

ALTER TABLE `routine_exercises` ADD FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

ALTER TABLE `user_active_routines` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_active_routines` ADD FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`);

ALTER TABLE `workout_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `workout_logs` ADD FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`);

ALTER TABLE `workout_logs` ADD FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`);

ALTER TABLE `body_metrics` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `classes` ADD FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`);

ALTER TABLE `classes` ADD FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`);

ALTER TABLE `classes` ADD FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`);

ALTER TABLE `classes` ADD FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

ALTER TABLE `bookings` ADD FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

ALTER TABLE `bookings` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `cancellation_logs` ADD FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

ALTER TABLE `diet_plan_recipe_pool` ADD FOREIGN KEY (`diet_plan_id`) REFERENCES `diet_plans` (`id`);

ALTER TABLE `diet_plan_recipe_pool` ADD FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

ALTER TABLE `user_meal_schedule` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_meal_schedule` ADD FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

ALTER TABLE `user_favorites` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);