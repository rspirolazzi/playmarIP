CREATE TABLE `ip_service_amounts` (
  `service_amount_id`      INT(11)        NOT NULL AUTO_INCREMENT,
  `service_id`             INT(11)        NOT NULL,
  `service_item_subtotal`  DECIMAL(20, 2) NOT NULL DEFAULT '0.00',
  `service_item_tax_total` DECIMAL(20, 2) NOT NULL DEFAULT '0.00',
  `service_tax_total`      DECIMAL(20, 2) NOT NULL DEFAULT '0.00',
  `service_total`          DECIMAL(20, 2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`service_amount_id`),
  KEY `service_id` (`service_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

CREATE TABLE `ip_service_custom` (
  `service_custom_id` INT(11) NOT NULL AUTO_INCREMENT,
  `service_id`        INT(11) NOT NULL,
  `service_custom_fieldid` INT NOT NULL,
  `service_custom_fieldvalue` TEXT NULL ,
  PRIMARY KEY (`service_custom_id`),
  KEY `service_id` (`service_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

CREATE TABLE `ip_service_item_amounts` (
  `item_amount_id` INT(11)        NOT NULL AUTO_INCREMENT,
  `item_id`        INT(11)        NOT NULL,
  `item_subtotal`  DECIMAL(20, 2) NOT NULL DEFAULT '0.00',
  `item_tax_total` DECIMAL(20, 2) NOT NULL,
  `item_total`     DECIMAL(20, 2) NOT NULL,
  PRIMARY KEY (`item_amount_id`),
  KEY `item_id` (`item_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

CREATE TABLE `ip_service_items` (
  `item_id`          INT(11)        NOT NULL AUTO_INCREMENT,
  `service_id`         INT(11)        NOT NULL,
  `item_tax_rate_id` INT(11)        NOT NULL,
  `item_date_added`  DATE           NOT NULL,
  `item_name`        VARCHAR(100)   ,
  `item_description` LONGTEXT       ,
  `item_quantity`    DECIMAL(20, 2) NOT NULL,
  `item_price`       DECIMAL(20, 2) NOT NULL,
  `item_order`       INT(2)         NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`),
  KEY `service_id` (`service_id`, `item_date_added`, `item_order`),
  KEY `item_tax_rate_id` (`item_tax_rate_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

CREATE TABLE `ip_service_tax_rates` (
  `service_tax_rate_id`     INT(11)        NOT NULL AUTO_INCREMENT,
  `service_id`              INT(11)        NOT NULL,
  `tax_rate_id`           INT(11)        NOT NULL,
  `include_item_tax`      INT(1)         NOT NULL DEFAULT '0',
  `service_tax_rate_amount` DECIMAL(20, 2) NOT NULL,
  PRIMARY KEY (`service_tax_rate_id`),
  KEY `service_id` (`service_id`),
  KEY `tax_rate_id` (`tax_rate_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

CREATE TABLE `ip_services` (
  `service_id`            INT(11)     NOT NULL AUTO_INCREMENT,
  `invoice_id`          INT(11)     NOT NULL DEFAULT '0',
  `user_id`             INT(11)     NOT NULL,
  `client_id`           INT(11)     NOT NULL,
  `invoice_group_id`    INT(11)     NOT NULL,
  `service_status_id`     TINYINT(2)  NOT NULL DEFAULT '1',
  `service_date_created`  DATE        NOT NULL,
  `service_date_modified` DATETIME    NOT NULL,
  `service_date_expires`  DATE        NOT NULL,
  `service_number`        VARCHAR(100) NOT NULL,
  `service_url_key`       CHAR(32)    NOT NULL,
  `notes`                 LONGTEXT,
  PRIMARY KEY (`service_id`),
  KEY `user_id` (`user_id`, `client_id`, `invoice_group_id`, `service_date_created`, `service_date_expires`, `service_number`),
  KEY `invoice_id` (`invoice_id`),
  KEY `service_status_id` (`service_status_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;
  
  
  ALTER TABLE `ip_services`
  ADD COLUMN `service_discount_amount` DECIMAL(20, 2) NOT NULL DEFAULT 0
  AFTER `service_number`,
  ADD COLUMN `service_discount_percent` DECIMAL(20, 2) NOT NULL DEFAULT 0
  AFTER `service_discount_amount`;

ALTER TABLE `ip_service_item_amounts`
  ADD COLUMN `item_discount` DECIMAL(20, 2) NOT NULL DEFAULT 0
  AFTER `item_tax_total`;
ALTER TABLE `ip_service_items`
  ADD COLUMN `item_discount_amount` DECIMAL(20, 2) NOT NULL DEFAULT 0
  AFTER `item_price`;
  
  
  ALTER TABLE ip_service_items
  ADD COLUMN item_product_unit VARCHAR(50) DEFAULT NULL,
  ADD COLUMN item_product_unit_id INT(11);

  ALTER TABLE `ip_service_items`
  ADD COLUMN `item_product_id` INT(11) DEFAULT NULL
  AFTER `item_tax_rate_id`;

  ALTER TABLE `ip_service_items`
	CHANGE COLUMN `item_discount_amount` `item_discount_amount` DECIMAL(20,2) NULL DEFAULT '0.00' AFTER `item_price`;

  INSERT INTO `ip_invoice_groups` (`invoice_group_name`, `invoice_group_identifier_format`, `invoice_group_next_id`) VALUES ('Service Default', 'SER{{{id}}}', '1');
