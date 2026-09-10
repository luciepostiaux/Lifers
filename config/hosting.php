<?php

return [
    'bootstrap_enabled' => filter_var(
        env('LIFERS_HOSTING_BOOTSTRAP_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
