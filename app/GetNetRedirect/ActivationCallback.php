<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;
use App\Models\RequestLog;
use App\Models\Transaction;

class ActivationCallback
{
    /**
     * Creates tables
     *
     * @return void
     */
    public function createTables()
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();
        $tableName = RequestLog::tableName();

        $sql = "CREATE TABLE $tableName (
            id bigint unsigned not null auto_increment primary key,
            url varchar(512) null,
            req_headers longtext null,
            req_body longtext null,
            res_code int null,
            res_headers longtext null,
            res_body longtext null,
            order_id bigint unsigned null,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $tableName = Transaction::tableName();
        $sql = "CREATE TABLE $tableName (
            id bigint unsigned not null auto_increment primary key,
            order_id bigint unsigned null,
            transaction_id varchar(128) default null,
            payment_method varchar(32) default null,
            last_transaction_type varchar(32) default null,
            last_transaction_res_body longtext null,
            last_transaction_id varchar(128) default null,
            last_payment_res_body longtext null,
            last_refund_request_id varchar(128) default null,
            last_refund_merchant_account_id varchar(128) default null,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charsetCollate;";
        dbDelta($sql);
    }

    public static function execute()
    {
        $act = new ActivationCallback();
        return $act->processActivation();
    }

    /**
     * Process the activation
     *
     * @return void
     */
    public function processActivation()
    {
        $this->createTables();
    }
}
