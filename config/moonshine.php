<?php

use MoonShine\Exceptions\MoonShineNotFoundException;
use MoonShine\Forms\LoginForm;
use MoonShine\Http\Middleware\Authenticate;
use MoonShine\Layouts\AppLayout;
use MoonShine\Layouts\CompactLayout;
use MoonShine\Models\MoonshineUser;
use MoonShine\Pages\ErrorPage;
use MoonShine\Pages\LoginPage;
use MoonShine\Pages\ProfilePage;

return [
    'dir' => 'app/MoonShine',
    'namespace' => 'App\MoonShine',

    'title' => env('MOONSHINE_TITLE', 'MoonShine'),

    'route' => [
        'domain' => env('MOONSHINE_DOMAIN', ''),
        'prefix' => env('MOONSHINE_ROUTE_PREFIX', 'admin'),
        'single_page_prefix' => 'page',
        'index' => 'moonshine.index',
        'middlewares' => [],
        'notFoundHandler' => MoonShineNotFoundException::class,
    ],

    'use_migrations' => true,

    'layout' => AppLayout::class, // or CompactLayout::class

    'disk' => 'public',

    'disk_options' => [],

    'cache' => 'file',

    'forms' => [
        'login' => LoginForm::class
    ],

    'pages' => [
        'dashboard' => '',
        'profile' => ProfilePage::class,
        'login' => LoginPage::class,
        'error' => ErrorPage::class,
    ],

    'model_resources' => [
        'default_with_import' => true,
        'default_with_export' => true,
    ],

    'auth' => [
        'enable' => true,
        'middleware' => Authenticate::class,
        'fields' => [
            'username' => 'email',
            'password' => 'password',
            'name' => 'name',
            'avatar' => 'avatar',
        ],
        'guard' => 'moonshine',
        'guards' => [
            'moonshine' => [
                'driver' => 'session',
                'provider' => 'moonshine',
            ],
        ],
        'providers' => [
            'moonshine' => [
                'driver' => 'eloquent',
                'model' => MoonshineUser::class,
            ],
        ],
        'pipelines' => [],
    ],

    'locales' => [
        'en',
        'ru',
    ],

    'global_search' => [
        // User::class
    ],

    'tinymce' => [
        'file_manager' => false, // or 'laravel-filemanager' prefix for lfm
        'token' => env('MOONSHINE_TINYMCE_TOKEN', ''),
        'version' => env('MOONSHINE_TINYMCE_VERSION', '6'),
    ],

    'socialite' => [
        // 'driver' => 'path_to_image_for_button'
    ],
];
