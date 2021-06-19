<?php

return [
    'db_connection' => env('DB_CONNECTION', 'pgsql'),
    'jwt_name' => env('JWT_NAME', 'token'),
    'jwt_token_type' => env('JWT_TOKEN_TYPE', 'bearer'),
];
