# Filament Kanban Board

A beautiful, interactive Kanban Board for **Filament Panels**. Manage your tasks with drag-and-drop, checklists, attachments, comments, and activity logs.

## Requirements

This package is **not** a standalone Kanban UI. It is built specifically for applications that already use **[Filament](https://filamentphp.com/) Panels** (v3 or v4) on **Laravel** (PHP 8.2+).

You must:

- Have a Filament panel configured in your app
- Register `FilamentKanbanPlugin` on that panel (see [Usage](#usage))
- Run the package migrations and include the package views in your Tailwind build (see [Styling](#styling))

It will **not** work in a plain Laravel app without Filament, nor as a generic Livewire component outside a Filament panel.

## Features

- 🚀 **Interactive Kanban Board**: Drag and drop lists and cards (HTML5 + Alpine + Livewire).
- 📋 **Checklists**: Add, edit, reorder checklists and items within cards.
- 📎 **Attachments**: Upload and manage files (configurable disk and directory).
- 💬 **Comments**: Team collaboration with card-level commenting.
- 🕒 **Activity logs**: Track everything that happens on a card.
- 👥 **Member Management**: Assign members to boards and cards.
- 🏷️ **Tags**: Categorize cards with tags defined in your application (see [Tags](#tags)).
- 📅 **Due Dates**: Set and track deadlines.
- 📦 **Archiving**: Archive lists and cards; show/hide archived items; bulk archive or unarchive all lists.
- 🔒 **Privacy**: Private or public boards. Private boards remain visible in the board list for everyone; only members can open them—other users see a clear restricted-access message instead of the board content.

## Installation

Install only in a **Laravel + Filament Panel** project:

```bash
composer require tales-virtualy/filament-kanban-board
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --tag="filament-kanban-board-config"
php artisan vendor:publish --tag="filament-kanban-board-migrations"
php artisan migrate
```

For attachments on the `public` disk, run `php artisan storage:link` if you have not already.

## Configuration

The configuration file `config/kanban.php` allows you to customize:

- `user_model`: The model used for users (default: `App\Models\User`).
- `system_user_id`: ID used for system-generated activity logs.
- `storage_disk`: Filesystem disk for attachments (default: `public`).
- `storage_directory`: Base path for card attachments (default: `kanban/card-attachments`).
- `tables`: Customize database table names (`boards`, `lists`, `cards`, `tags`, `taggables`, etc.).

## Tags

The package does **not** ship a screen to create or manage tags. Tags are stored in the `tags` table (model `FilamentKanban\Models\CustomTag`) and linked to cards via `taggables`.

**Your application** is responsible for creating tags (Filament Resource, seeder, admin UI, etc.). The card modal lists every tag in that table so users can attach them to cards.

Example seeder in your app:

```php
use FilamentKanban\Models\CustomTag;

CustomTag::query()->updateOrCreate(
    ['slug' => 'urgent'],
    [
        'name' => 'Urgent',
        'badge_color' => '#ef4444',
        'text_color' => '#ffffff',
        'type' => 'Global',
        'order_column' => 1,
    ],
);
```

## Styling

This package uses Tailwind CSS for styling. To ensure the board looks correct, you must include the package's views in your project's `tailwind.config.js` or `resources/css/app.css` (for Tailwind v4).

### For Tailwind v4 (`app.css`):

Add the `@source` directive to your `resources/css/app.css`:

```css
@import 'tailwindcss';

@source '../../vendor/tales-virtualy/filament-kanban-board/resources/views/**/*.blade.php';
/* ... other imports */
```

### For Tailwind v3 (`tailwind.config.js`):

Add the views path to the `content` array:

```js
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './vendor/tales-virtualy/filament-kanban-board/resources/views/**/*.blade.php',
    ],
    // ...
}
```

The card modal also includes scoped CSS so Cancel/Save and the two-column layout work even when some utility classes are not compiled in the host app.

## Usage

### Registering the plugin

Add the plugin to your **Filament Panel** provider (for example `app/Providers/Filament/AdminPanelProvider.php`):

```php
use FilamentKanban\FilamentKanbanPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentKanbanPlugin::make(),
        ]);
}
```

### Accessing the boards

Once registered, open the Kanban boards from your panel navigation, typically at `/admin/boards` (or your panel's path prefix).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## Credits

- [Tales](https://github.com/tales-virtualy)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
