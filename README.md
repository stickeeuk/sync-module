# Stickee Sync

This a composer module for synchronising files or database tables between two servers.

> NOTE: Foreign key checks are disabled during import

## Installation

`composer require stickee/sync`

This module ships with a Laravel service provider which will be automatically registered.

### Manual registration

The module can be manually registered by adding this to the `providers` array in `config/app.php`:

```
\Stickee\Sync\ServiceProvider::class,
```

# Usage for Servers (Sending Data)

Add the routes to your `routes/api.php` by calling `\Stickee\Sync\ServiceProvider::routes();`.
You will usually want to prefix the URL and add some form of authentication, for example:
```
Route::middleware('auth:api')
    ->prefix('sync')
    ->name('sync.')
    ->group(function () {
        \Stickee\Sync\ServiceProvider::routes();
    });
```

## Configuration

Run `php artisan vendor:publish --provider="Stickee\Sync\ServiceProvider"` to publish the configuration files,
then fill in `tables` and `directories` in `sync-server.php`.

# Usage for Clients (Receiving Data)

## Configuration

Run `php artisan vendor:publish --provider="Stickee\Sync\ServiceProvider"` to publish the configuration files,
then fill in `tables` and `directories` in `sync-client.php`.
Set the following in your .env file:

 - `SYNC_API_URL`: The server URL, e.g. `https://example.com/api/sync`

If you have added authentication, then you will need to make the Guzzle client authenticate.
For api-token authenticaion, add this to you `config/sync-client.php`
```
'api_key' => env('SYNC_API_KEY'),
```
and add `SYNC_API_KEY=XXXXXXX` to your `.env` file.

Then in `AppServiceProvider::register()` add:

```
app()->when(\Stickee\Sync\Client::class)
    ->needs(\GuzzleHttp\Client::class)
    ->give(function () {
        $config = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('sync-client.api_key'),
                'Accept' => 'application/json',
            ],
        ];

        return new \GuzzleHttp\Client($config);
    });
```

# Commands

The package supplies `php artisan sync:sync` to run a synchronisation from the command line.

# Developing

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
3. `composer require stickee/sync@dev`

**NOTE:** Do not check in your `composer.json` like this!

### Database tables

Method 1:
 - Client hashes its local copy of the table
 - Client sends a getTableHash request to the server
 - If the hashes are the same, there is nothing to do so stop
 - Client sends a getTable request
 - Client imports the response, which is merged in to the existing table

Method 2:
 - Client hashes its local copy of the table
 - Client sends a getTable request to the server, including the hash
 - If the hashes are the same, the server will respond 304 Not Modified
 - Client imports the response, which is merged in to the existing table

Tables can be hashed using a class that implements \Stickee\Sync\Interfaces\TableHasherInterface

### Files

 - Client hashes its local copy of the files
 - Client sends a getFileHashes request to the server
 - Client compares the hashes and builds a list of files it needs to delete / download
 - Client deletes extraneous files
 - Client splits the list of files to fetch into chunks
 - For each chunk, the client sends a getFiles request
 - Files are saved to the disk

 ### Testing

First copy `phpunit.xml.dist` to `phpunit.xml` and fill in your MySQL database details.
Run unit tests with the following command:

 ` ./vendor/bin/phpunit -v`
