<?php

return [
    'enabled' => env('INSTALLER_ENABLED', env('APP_ENV', 'production') !== 'production'),
];
