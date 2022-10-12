<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\Models;

use App\Constants;

class Model
{
    const TABLE_NAME = 'model';

    /**
     * Retrieve all rows from the given request
     *
     * @param array $columns
     * @return mixed
     */
    public static function all($columns = [])
    {
        global $wpdb;
        $tableName = static::tableName();
        $selectedColumns = '*';
        if (count($columns) > 0) {
            $selectedColumns = implode(',', $columns);
        }
        $results = $wpdb->get_results(
            "SELECT {$selectedColumns}
            FROM {$tableName}
            WHERE 1
            ORDER BY id DESC"
        );
        return $results;
    }

    /**
     * Transaction find element
     *
     * @param integer $id
     * @param array $columns
     * @return stdClass
     */
    public static function find($id, $columns = [])
    {
        global $wpdb;
        $tableName = static::tableName();
        $selectedColumns = '*';
        if (count($columns) > 0) {
            $selectedColumns = implode(',', $columns);
        }
        $results = $wpdb->get_row(
            "SELECT {$selectedColumns}
            FROM {$tableName}
            WHERE 1
            AND id = {$id}
            ORDER BY id DESC
            LIMIT 1"
        );
        return $results;
    }


    /**
     * Returns table name
     *
     * @return string
     */
    public static function tableName()
    {
        global $wpdb;
        return $wpdb->prefix . static::TABLE_NAME;
    }

    /**
     * Create request log based on the given data
     *
     * @param array $data
     * @return mixed
     */
    public static function create($data)
    {
        global $wpdb;
        $wpdb->insert(static::tableName(), $data);
        return $wpdb->insert_id;
    }

    /**
     * Updates the given id with the defined data
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public static function update($id, $data)
    {
        global $wpdb;
        $wpdb->update(static::tableName(), $data, ['id' => $id]);
    }
}
