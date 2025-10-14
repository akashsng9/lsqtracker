<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getDataFromOtherDb')) {
    /**
     * Get data from a specific table in another database connection.
     *
     * @param string $table
     * @param array $conditions
     * @param string $connectionName
     * @return \Illuminate\Support\Collection
     */
    function getDataFromOtherDb(string $table, array $conditions = [], string $connectionName = 'mysql_secondary')
    {
        $query = DB::connection($connectionName)->table($table);

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get();
    }
}
