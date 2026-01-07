# Filament Kanban Board

A beautiful, interactive Kanban Board for Filament Panels. Manage your tasks with drag-and-drop, checklists, attachments, comments, and activity logs.

## Features

- 🚀 **Interactive Kanban Board**: Drag and drop cards between lists.
- 📋 **Checklists**: Add and manage checklists within cards.
- 📎 **Attachments**: Upload and manage files attached to cards.
- 💬 **Comments**: Team collaboration with card-level commenting.
- 🕒 **Activity logs**: Track everything that happens on a card.
- 👥 **Member Management**: Assign members to boards and cards.
- 🏷️ **Tags**: Categorize cards with custom tags.
- 📅 **Due Dates**: Set and track deadlines.
- 🔒 **Privacy**: Private or public boards.

## Installation

You can install the package via composer:

```bash
composer require tales-virtualy/filament-kanban-board
```

Publish the configuration, migrations, and assets:

```bash
php artisan vendor:publish --tag="filament-kanban-board-config"
php artisan vendor:publish --tag="filament-kanban-board-migrations"
php artisan migrate
```

## Configuration

The configuration file `config/kanban.php` allows you to customize:
- `user_model`: The model used for users (default: `App\Models\User`).
- `system_user_id`: ID used for system-generated activity logs.
- `storage_disk`: Disk used for attachments (default: `public`).
- `table_names`: Customize database table names.

## Usage

### Registering the Plugin

Add the plugin to your Filament Panel Provider:

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

### Accessing the Boards

Once registered, you can access the Kanban boards at `/admin/boards` (or your panel's equivalent).

## Credits

- [Tales](https://github.com/tales-virtualy)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
