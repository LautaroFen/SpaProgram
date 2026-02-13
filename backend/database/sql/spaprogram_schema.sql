-- SpaProgram schema (MySQL 8+)
-- Run (example):
--   mysql -u root -p < spaprogram_schema.sql
--
-- Notes:
-- - `roles` intentionally has NO timestamps (as requested).
-- - `clients.balance_cents` tracks total debt (recommended single source of truth).
-- - `appointments.price_cents` stores the appointment price at the time it was booked.
-- - `appointments.deposit_cents` stores the prepayment (advance payment) for that appointment.
-- - `payments` allows partial payments and links optionally to an appointment.
-- - `discounts` + `discount_redemptions` allow tracking benefits/discount usage per client.

CREATE DATABASE IF NOT EXISTS spaprogram
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE spaprogram;

-- -----------------------------
-- Roles
-- -----------------------------
CREATE TABLE IF NOT EXISTS roles (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL,
	PRIMARY KEY (id),
	UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Users (employees)
-- -----------------------------
CREATE TABLE IF NOT EXISTS users (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	role_id BIGINT UNSIGNED NOT NULL,

	first_name VARCHAR(100) NOT NULL,
	last_name  VARCHAR(100) NOT NULL,

	-- Optional for professionals (enforce in application logic)
	job_title VARCHAR(120) NULL,

	-- Optional; unique allows multiple NULLs in MySQL
	email VARCHAR(255) NULL,

	-- Optional; required only for users that can log in
	password VARCHAR(255) NULL,

	-- Optional schedule data, e.g. {"mon":["09:00","18:00"],"tue":["10:00","16:00"]}
	work_schedule JSON NULL,

	remember_token VARCHAR(100) NULL,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	UNIQUE KEY uq_users_email (email),
	KEY idx_users_role_id (role_id),
	KEY idx_users_name (last_name, first_name),

	CONSTRAINT fk_users_role
		FOREIGN KEY (role_id) REFERENCES roles(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Clients
-- -----------------------------
CREATE TABLE IF NOT EXISTS clients (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	dni VARCHAR(32) NULL,

	first_name VARCHAR(100) NOT NULL,
	last_name  VARCHAR(100) NOT NULL,

	email VARCHAR(255) NULL,
	phone VARCHAR(30) NOT NULL,

	-- Total outstanding balance for the client across appointments.
	balance_cents BIGINT UNSIGNED NOT NULL DEFAULT 0,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	UNIQUE KEY uq_clients_dni (dni),
	KEY idx_clients_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Services
-- -----------------------------
CREATE TABLE IF NOT EXISTS services (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(120) NOT NULL,

	duration_minutes INT UNSIGNED NOT NULL,
	price_cents INT UNSIGNED NOT NULL,

	is_active TINYINT(1) NOT NULL DEFAULT 1,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	UNIQUE KEY uq_services_name (name),
	KEY idx_services_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Appointments (turnos)
-- -----------------------------
CREATE TABLE IF NOT EXISTS appointments (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	client_id BIGINT UNSIGNED NOT NULL,
	user_id BIGINT UNSIGNED NOT NULL,
	service_id BIGINT UNSIGNED NOT NULL,

	start_at DATETIME NOT NULL,
	end_at   DATETIME NOT NULL,

	-- Appointment price at booking time
	price_cents INT UNSIGNED NOT NULL,

	-- Advance payment/prepayment
	deposit_cents INT UNSIGNED NOT NULL DEFAULT 0,

	status ENUM('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
	notes TEXT NULL,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	KEY idx_appointments_start_at (start_at),
	KEY idx_appointments_user_start (user_id, start_at),
	KEY idx_appointments_client_start (client_id, start_at),
	KEY idx_appointments_status (status),

	CONSTRAINT fk_appointments_client
		FOREIGN KEY (client_id) REFERENCES clients(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,

	CONSTRAINT fk_appointments_user
		FOREIGN KEY (user_id) REFERENCES users(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,

	CONSTRAINT fk_appointments_service
		FOREIGN KEY (service_id) REFERENCES services(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Payments (seguimiento de pagos)
-- -----------------------------
CREATE TABLE IF NOT EXISTS payments (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	client_id BIGINT UNSIGNED NOT NULL,
	appointment_id BIGINT UNSIGNED NULL,

	-- Positive payment amounts only. Refunds/chargebacks could be handled via status or a separate table.
	amount_cents INT UNSIGNED NOT NULL,

	method ENUM('cash','card','transfer','other') NOT NULL DEFAULT 'cash',
	status ENUM('pending','paid','void') NOT NULL DEFAULT 'paid',

	paid_at DATETIME NULL,
	reference VARCHAR(120) NULL,
	notes TEXT NULL,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	KEY idx_payments_client (client_id, created_at),
	KEY idx_payments_appointment (appointment_id),
	KEY idx_payments_status (status),

	CONSTRAINT fk_payments_client
		FOREIGN KEY (client_id) REFERENCES clients(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,

	CONSTRAINT fk_payments_appointment
		FOREIGN KEY (appointment_id) REFERENCES appointments(id)
		ON UPDATE CASCADE
		ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Expenses (gastos de la empresa)
-- -----------------------------
-- Historical log of business expenses such as materials, taxes, services, etc.
CREATE TABLE IF NOT EXISTS expenses (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	-- Category of the expense (e.g. "materiales", "impuestos", "servicios", "otros")
	category VARCHAR(80) NOT NULL,

	-- Provider or payee (supplier / service being paid)
	payee VARCHAR(160) NOT NULL,

	-- Amounts in cents to avoid floating point issues
	amount_due_cents BIGINT UNSIGNED NOT NULL,
	amount_paid_cents BIGINT UNSIGNED NOT NULL DEFAULT 0,

	-- Date when the expense was incurred / scheduled
	performed_at DATETIME NOT NULL,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	KEY idx_expenses_performed_at (performed_at),
	KEY idx_expenses_category (category),
	KEY idx_expenses_payee (payee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Discounts (cupones / beneficios)
-- -----------------------------
CREATE TABLE IF NOT EXISTS discounts (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	code VARCHAR(50) NOT NULL,
	type ENUM('percent','fixed') NOT NULL,

	-- percent: 1..100, fixed: cents
	value INT UNSIGNED NOT NULL,

	starts_at DATETIME NULL,
	ends_at   DATETIME NULL,

	usage_limit INT UNSIGNED NULL,
	per_client_limit INT UNSIGNED NULL,

	is_active TINYINT(1) NOT NULL DEFAULT 1,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	UNIQUE KEY uq_discounts_code (code),
	KEY idx_discounts_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Each time a discount is used, we log it.
CREATE TABLE IF NOT EXISTS discount_redemptions (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	discount_id BIGINT UNSIGNED NOT NULL,
	client_id BIGINT UNSIGNED NOT NULL,
	appointment_id BIGINT UNSIGNED NULL,

	-- How much was discounted for this redemption (in cents)
	amount_cents INT UNSIGNED NOT NULL,

	redeemed_at DATETIME NOT NULL,
	notes TEXT NULL,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	KEY idx_redemptions_client (client_id, redeemed_at),
	KEY idx_redemptions_discount (discount_id, redeemed_at),

	CONSTRAINT fk_redemptions_discount
		FOREIGN KEY (discount_id) REFERENCES discounts(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,

	CONSTRAINT fk_redemptions_client
		FOREIGN KEY (client_id) REFERENCES clients(id)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,

	CONSTRAINT fk_redemptions_appointment
		FOREIGN KEY (appointment_id) REFERENCES appointments(id)
		ON UPDATE CASCADE
		ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Optional: Audit log (quién cambió qué)
-- -----------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

	actor_user_id BIGINT UNSIGNED NULL,

	action VARCHAR(80) NOT NULL,
	entity_type VARCHAR(120) NOT NULL,
	entity_id BIGINT UNSIGNED NULL,

	metadata JSON NULL,
	ip_address VARCHAR(45) NULL,

	created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

	PRIMARY KEY (id),
	KEY idx_audit_actor (actor_user_id, created_at),
	KEY idx_audit_entity (entity_type, entity_id),

	CONSTRAINT fk_audit_actor
		FOREIGN KEY (actor_user_id) REFERENCES users(id)
		ON UPDATE CASCADE
		ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;