<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;

/**
 * PostgreSQL connection that binds PHP booleans as 'true' / 'false'.
 *
 * The pgsql connection uses emulated prepares (config/database.php), so PDO
 * writes bindings straight into the SQL text. Laravel turns booleans into
 * 0 / 1 first, which produces `"read" = 0`, and PostgreSQL rejects that with
 * "operator does not exist: boolean = integer". A quoted 'true' / 'false'
 * literal is coerced to boolean, so queries and writes both work.
 */
class PostgresConnection extends BasePostgresConnection
{
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return parent::prepareBindings($bindings);
    }
}
