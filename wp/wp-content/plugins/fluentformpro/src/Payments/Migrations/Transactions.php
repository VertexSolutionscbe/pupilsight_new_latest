<?php

namespace FluentFormPro\Payments\Migrations;

class Transactions
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . 'fluentform_transactions';

        $cols = $wpdb->get_col("DESC {$table}", 0);

        $sql = "CREATE TABLE $table (
			    id int(11) NOT NULL AUTO_INCREMENT,
			    transaction_hash varchar(255) NULL,
			    payer_name varchar(255) NULL,
			    payer_email varchar(255) NULL,
			    billing_address varchar(255) NULL,
			    shipping_address varchar(255) NULL,
				form_id int(11) NOT NULL,
				user_id int(11) DEFAULT NULL,
				submission_id int(11) NULL,
				subscription_id int(11) NULL,
				transaction_type varchar(255) DEFAULT 'onetime',
				payment_method varchar(255),
				card_last_4 int(4),
				card_brand varchar(255),
				charge_id varchar(255),
				payment_total int(11) DEFAULT 1,
				status varchar(255),
				currency varchar(255),
				payment_mode varchar(255),
				payment_note longtext,
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }
}