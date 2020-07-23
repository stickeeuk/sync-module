# Stickee Sync

This a composer module for loading affiliate information from the Sync API.

## Installation

`composer require stickee/sync`

This module ships with a Laravel service provider which will be automatically registered for Laravel 5.5+.

### Manual registration

The module can be manually registered by adding this to the `providers` array in `config/app.php`:

```
Stickee\Sync\ServiceProvider::class,
```

# Usage for Websites

Add the routes to your routes/api.php. You will usually want to add some form of authentication, like this:
```
Route::middleware('auth:api')->group(function () {
    \Stickee\Sync\ServiceProvider::routes();
});
```

## Developing

The easiest way to make changes is to make the project you're importing the module in to load the module from your filesystem instead of the composer repository, like this:

1. `composer remove stickee/sync`
2. Edit `composer.json` and add
    ```
    "repositories": [
            {
                "type": "path",
                "url": "../sync"
            }
        ]
    ```
    where "../sync" is the path to where you have this project checked out
3. `composer require stickee/sync`

**NOTE:** Do not check in your `composer.json` like this!
