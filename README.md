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

## Usage for Websites

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
 - If the hashes are the same, there server will respond 304 Not Modified
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
