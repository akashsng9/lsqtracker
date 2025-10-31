<?php

use Illuminate\Support\Facades\DB;

/**
 * Get data from another DB table with flexible conditions.
 *
 * @param string $table
 * @param array $conditions Mixed array of where clauses:
 *     - ['status' => 1]
 *     - ['status' => [0, 1]]
 *     - [['created_at', '>', '2025-04-01']]
 *     - [['price', '>=', 100]]
 * @param string $connectionName
 * @return \Illuminate\Support\Collection
 */
function getDataFromOtherDb(string $table, array $conditions = [], string $connectionName = 'mysql_secondary')
{
    $query = DB::connection($connectionName)->table($table);

    foreach ($conditions as $key => $condition) {
        if (is_int($key) && is_array($condition) && count($condition) === 3) {
            // Example: ['created_at', '>', '2025-04-01']
            [$column, $operator, $value] = $condition;
            $query->where($column, $operator, $value);
        } elseif (is_array($condition)) {
            // Example: ['status' => [0, 1]] => whereIn
            $query->whereIn($key, $condition);
        } else {
            // Example: ['status' => 1]
            $query->where($key, $condition);
        }
    }

    return $query->get();
}
