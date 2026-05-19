# Filament Kanban Board

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tales-virtualy/filament-kanban-board.svg?style=flat-square)](https://packagist.org/packages/tales-virtualy/filament-kanban-board)
[![Total Downloads](https://img.shields.io/packagist/dt/tales-virtualy/filament-kanban-board.svg?style=flat-square)](https://packagist.org/packages/tales-virtualy/filament-kanban-board)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/tales-virtualy/filament-kanban-board.svg?style=flat-square)](https://packagist.org/packages/tales-virtualy/filament-kanban-board)
[![Filament](https://img.shields.io/badge/Filament-3%20%7C%204%20%7C%205-amber?style=flat-square)](https://filamentphp.com/)

A Kanban board package for [Filament](https://filamentphp.com/) Panels on Laravel. Organize work in boards, lists, and cards with drag-and-drop, checklists, attachments, comments, activity history, members, tags, due dates, and archiving.

> **Scope:** This is a Filament panel plugin—not a standalone Kanban widget. It requires Laravel (PHP 8.2+), a configured Filament panel (v3, v4, or v5), and a Tailwind build that includes the package views.

---

## Features

- **Boards** — Public or private; member management; bulk archive and restore of lists
- **Lists & cards** — HTML5 drag-and-drop with Alpine.js and Livewire; move cards across lists
- **Card workspace** — Description, members, tags, due dates, attachments, checklists (with reorder)
- **Collaboration** — Comments with edit/delete; activity log per card
- **Archiving** — Archive lists and cards; show or hide archived items; unarchive from the board or card modal
- **Access control** — Laravel policies for boards, lists, and cards; private boards show a clear restricted-access screen for non-members

---

## Requirements

| Requirement | Notes |
|-------------|--------|
| PHP | `^8.2` |
| Laravel | Compatible with current LTS releases used by Filament 5 |
| Filament | `^3.0`, `^4.0`, or `^5.0` (Panels) |
| Livewire | `^3.0` or `^4.0` (pulled in by Filament) |
| Tailwind CSS | v3 or v4; must compile classes used in package views (see [Panel theme](#4-panel-theme-required)) |
| Database | MySQL, PostgreSQL, SQLite, etc. |

---

## Installation checklist

Complete these steps in order. Skipping the panel theme step is the most common cause of a “broken” UI (unstyled board names, layout glitches).

1. [Install via Composer](#1-install-via-composer)
2. [Register the plugin](#2-register-the-plugin)
3. [Run migrations](#3-run-migrations)
4. [Configure the Filament panel theme](#4-panel-theme-required) — **required for correct rendering**
5. [Build frontend assets](#5-build-frontend-assets)
6. [Optional: config, storage, tags](#optional-configuration)

---

## 1. Install via Composer

```bash
composer require tales-virtualy/filament-kanban-board:^1.0.10 -W
```

Use `-W` (`--with-all-dependencies`) when your application pins Filament 5, so Composer can resolve compatible versions.

---

## 2. Register the plugin

In your panel provider (for example `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Filament\Panel;
use FilamentKanban\FilamentKanbanPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentKanbanPlugin::make(),
        ])
        // ...discoverResources(), pages(), middleware(), etc.
    ;
}
```

Without this registration, Kanban pages and navigation will not be available.

---

## 3. Run migrations

The package ships runnable migrations and loads them automatically (`runsMigrations()`):

```bash
php artisan migrate
```

You should see migrations named `create_kanban_tables` and `create_kanban_tag_tables`.

**If migrate reports “Nothing to migrate” but you get `relation "boards" does not exist`:**

- Ensure you are on **v1.0.10+**, then run `php artisan migrate` again, **or**
- Publish migrations into your application and migrate:

```bash
php artisan vendor:publish --tag="filament-kanban-board-migrations"
php artisan migrate
```

On a **fresh** database, use either automatic vendor migrations **or** published files—not both.

---

## 4. Panel theme (required)

Filament panels load CSS through a **Vite theme**, not through `resources/css/app.css` unless you explicitly wire that file into the panel. The Kanban views rely on Tailwind utility classes (`grid`, `rounded-lg`, `border`, avatar sizing, etc.). If those classes are not compiled into the panel theme, the UI will look broken even though data and routes work correctly.

### Step A — Create the theme file

Create `resources/css/filament/admin/theme.css` (adjust the path if your panel uses another name):

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/**/*.blade.php';
@source '../../../../vendor/tales-virtualy/filament-kanban-board/resources/views/**/*.blade.php';
@source '../../../../storage/framework/views/*.php';
```

Paths are relative to `resources/css/filament/admin/theme.css`. IDE warnings on `@source` are expected; it is a Tailwind CSS v4 directive.

### Step B — Register the theme on the panel

In the same panel provider:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->viteTheme('resources/css/filament/admin/theme.css')
        ->plugins([
            FilamentKanbanPlugin::make(),
        ]);
}
```

### Step C — Register the theme in Vite

In `vite.config.js`, include the theme in the **`laravel-vite-plugin` `input` array** (a root-level `input` key is not used by the Laravel plugin):

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

The card modal includes scoped CSS for layout and the Cancel/Save footer, but **board cards, lists, and the full board view still require** the theme setup above.

### Tailwind v3 (legacy)

If your application does not use a Filament Vite theme, add the package views to Tailwind’s `content` paths instead:

```js
// tailwind.config.js
content: [
    './resources/**/*.blade.php',
    './vendor/tales-virtualy/filament-kanban-board/resources/views/**/*.blade.php',
],
```

Ensure the resulting CSS is actually loaded by your Filament panel.

---

## 5. Build frontend assets

After creating or updating the theme file:

```bash
npm install
npm run build
```

For local development you may use `npm run dev` instead. Rebuild whenever you change `@source` paths or upgrade the package.

If styles still look stale after a production build:

```bash
php artisan filament:assets
php artisan view:clear
```

---

## Optional configuration

### Publish config

```bash
php artisan vendor:publish --tag="filament-kanban-board-config"
```

Options in `config/kanban.php`:

| Key | Description | Default |
|-----|-------------|---------|
| `user_model` | User model for relationships and policies | `App\Models\User` |
| `system_user_id` | User ID for system-generated activity logs | `null` |
| `storage_disk` | Filesystem disk for attachments | `public` |
| `storage_directory` | Base path for card uploads | `kanban/card-attachments` |
| `tables` | Database table names | `boards`, `lists`, `cards`, `tags`, … |

### Storage link (attachments on `public`)

```bash
php artisan storage:link
```

### Tags

The package does **not** include a tag management UI. Tags live in the `tags` table (`FilamentKanban\Models\CustomTag`) and are attached to cards via `taggables`. Your application must create tags (seeders, Filament Resource, import, etc.).

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

---

## Usage

Open the panel and use the **Kanban** navigation item (label comes from package translations). The default route slug is `board-list-page`; the exact URL depends on your panel path (for example `/admin/board-list-page` or `/board-list-page` when the panel is mounted at `/`).

From the board list you can create boards, open a board, manage lists and cards, and use the card modal for details and collaboration.

---

## Troubleshooting

### `relation "boards" does not exist`

Migrations have not been applied. Run `php artisan migrate`. If nothing runs, upgrade to **v1.0.10+** or publish migrations (see [Run migrations](#3-run-migrations)).

### Kanban menu appears but pages error on database tables

Same as above—complete migrations before using the UI.

### Board list shows only plain text / large black shapes

The plugin is working; **panel CSS is missing**. The board title is rendered without card layout styles, and avatar markup may appear as unstyled blocks.

**Fix:** Complete [Panel theme (required)](#4-panel-theme-required) and [Build frontend assets](#5-build-frontend-assets). Confirm `theme.css` is listed inside `laravel({ input: [...] })` in `vite.config.js`, not only as a separate root `input` property.

### `php artisan migrate` → “Nothing to migrate” (v1.0.9 or older)

Older releases registered migrations as `.stub` files, which Laravel does not load automatically. Upgrade to **v1.0.10+** or publish migrations manually.

### Tags popover is empty

Expected until your app creates rows in the `tags` table. See [Tags](#tags).

### Composer: package conflicts with `filament/filament ^5.0`

Use **v1.0.8+** (prefer **v1.0.10+**) and install with:

```bash
composer require tales-virtualy/filament-kanban-board:^1.0.10 -W
```

### Private board visible but cannot open

By design: private boards appear in the list for all users; only members see the board content. Others see a restricted-access message.

---

## Uninstalling

Removing the package is straightforward. There is no hidden coupling beyond the steps you added during installation.

### 1. Remove the Composer dependency

```bash
composer remove tales-virtualy/filament-kanban-board
```

### 2. Undo application wiring

- Remove `FilamentKanbanPlugin::make()` from your panel provider’s `->plugins([...])` array.
- Remove the Kanban `@source` line from `resources/css/filament/admin/theme.css` (or your panel theme file).
- Run `npm run build` again so Tailwind drops unused classes.

### 3. Remove published files (if you published them)

```bash
# Only if you ran vendor:publish earlier
rm config/kanban.php
# Remove published migrations from database/migrations/ if you copied them
```

### 4. Database and storage (optional)

Kanban tables are **not** dropped automatically when you remove the package.

To remove schema and data (irreversible):

```bash
php artisan migrate:rollback
# Or roll back only the Kanban migrations you published, by batch/name
```

Delete uploaded card files from your disk if you stored attachments (default path: `storage/app/kanban/card-attachments/` on the `public` disk, or whatever you set in `config/kanban.php`).

After rollback, your app returns to its pre-Kanban database state. Back up production data before rolling back.

---

## Contributing

Pull requests are welcome. For larger changes, open an issue first to discuss the approach.

```bash
git checkout -b feature/my-feature
# make your changes
git commit -m "feat: describe your change"
git push origin feature/my-feature
```

Please keep changes focused and match existing code style. See [CHANGELOG.md](CHANGELOG.md) for release history.

---

## Changelog

Release notes: [CHANGELOG.md](CHANGELOG.md).

---

## License

MIT. See [LICENSE.md](LICENSE.md).

---

## Credits

Made by **[Tales Colver](https://github.com/tales-virtualy)**.

- [GitHub repository](https://github.com/tales-virtualy/filament-kanban-board)
- [Packagist package](https://packagist.org/packages/tales-virtualy/filament-kanban-board)
- [Contributors](../../contributors)

If this package helped your project, a star on GitHub is appreciated.
