<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'test' => [
            'driver' => 'single',
            'path' => storage_path('logs/test.log'),
            'level' => 'info'
        ],

        // Logs
        'general-cache-cleared-info' => [
            'driver' => 'single',
            'path' => storage_path('logs/cacheCleared/general-cache-cleared-info.log'),
            'level' => 'info'
        ],

        'sms-shablons-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/sms-shablons-errors.log'),
            'level' => 'error'
        ],
        'users-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/users-errors.log'),
            'level' => 'error'
        ],

        'doctors-final-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/doctors-final-errors.log'),
            'level' => 'error'
        ],
        'extended-prices-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/extended-prices-errors.log'),
            'level' => 'error'
        ],
        'clinics-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/clinics-errors.log'),
            'level' => 'error'
        ],
        'sms-bazas-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/sms-bazas-errors.log'),
            'level' => 'error'
        ],
        'outgoings-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/outgoings-errors.log'),
            'level' => 'error'
        ],
        'subscribes-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/subscribes-errors.log'),
            'level' => 'error'
        ],
        'recommendations-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/recommendations-errors.log'),
            'level' => 'error'
        ],
        'incomings-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/incomings-errors.log'),
            'level' => 'error'
        ],
        'hospitals-bases-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/hospitals-bases-errors.log'),
            'level' => 'error'
        ],
        'notes-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/notes-errors.log'),
            'level' => 'error'
        ],

        'erp-general-errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/erp-general-errors.log'),
            'level' => 'error'
        ],
    ],

];
