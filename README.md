# Ignition for Magento2

A beautiful error page and error tracking service for Magento
powered by [spatie/ignition](https://github.com/spatie/ignition).

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="./media/dark.webp">
    <img alt="Screenshot of the error page powered by Ignition" src="./media/light.webp" width="738">
</picture>

## Installation

```bash
composer require swissup/module-ignition
bin/magento module:enable Swissup_Ignition
```

## Configuration

This module will show the error page with detailed information
about the error and stack trace only in developer mode:

```
bin/magento deploy:mode:set developer
```

You can also use it in production mode to log the errors using [Flare](https://flareapp.io/)
error tracking service. To do so, add this section to the `env.php` file:

```php
'system' => [
    'default' => [
        'swissup_ignition' => [
            'general' => [
                'api_key' => 'API_KEY'
            ]
        ]
    ]
],
```

## Docker

When using Ignition with Docker the `Open in Editor` links will show incorrect paths.
To fix this issue open `~/.ignition.json` file and add `remote_sites_path` and
`local_sites_path` settings.

**Example:**

```
{
    "remote_sites_path": "/var/www/public",
    "local_sites_path": "/Users/username/Sites/mysite/public"
}
```
