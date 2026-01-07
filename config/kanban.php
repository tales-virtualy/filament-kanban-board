<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the model that represents the users in your application.
    | It will be used for relationships and authorization.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | System User ID
    |--------------------------------------------------------------------------
    |
    | This is the ID of the user that will be used for system-generated
    | activity logs. If null, logs will not be associated with a user
    | when triggered by the system.
    |
    */
    'system_user_id' => null,

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | If you want to customize the table names used by the package.
    |
    */
    'tables' => [
        'boards' => 'boards',
        'lists' => 'lists',
        'cards' => 'cards',
        'card_has_users' => 'card_has_users',
        'card_activity_logs' => 'card_activity_logs',
        'card_comments' => 'card_comments',
        'card_checklists' => 'card_checklists',
        'card_checklist_items' => 'card_checklist_items',
        'card_attachments' => 'card_attachments',
        'board_has_users' => 'board_has_users',
        'tags' => 'tags',
        'taggables' => 'taggables',
    ],
];
