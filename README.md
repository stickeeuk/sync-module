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

# Data Models

 - `\Stickee\Sync\Models\Affiliate` - The individual or company who will receive commission
 - `\Stickee\Sync\Models\Vertical` - Types of sites, e.g. Broadband or Mobile Phones
 - `\Stickee\Sync\Models\AffiliateVertical` - Link between Affiliate and Vertical
 - `\Stickee\Sync\Models\Property` - A trackable website or API
 - `\Stickee\Sync\Models\PropertyConfigurations\*` - Non-Eloquent - Different types of property will have different configuration options (implementing `\Stickee\Sync\Interfaces\PropertyConfigurationInterface`)
 - `\Stickee\Sync\Models\Commissions\*` - Polymorph - The commission structure (implementing `\Stickee\Sync\Interfaces\CommissionInterface`)
 - `\Stickee\Sync\Models\Theme` - Default theme variables

# Usage for Websites

The `\Stickee\Sync\ServiceProvider` will register `\Stickee\Sync\Models\Affiliate` and `\Stickee\Sync\Models\Property` with the Service Container.

The website can switch on the configuration class to implement custom logic for the type of property

```
$property = app(\Stickee\Sync\Models\Property);
$configuration = $property->configuration;

// You can get the property type like this ...
if ($configuration instanceof \Stickee\Sync\Models\PropertyConfigurations\MyPropertyConfiguration) {
    // Display widget A with setting $configuration->B
}

// ... or like this
elseif ($property->isA(\Stickee\Sync\Models\PropertyConfigurations\OtherPropertyConfiguration::class)) {
    // Display widget C with setting $configuration->D
}

// Best to throw an exception if the property type isn't recognised
else {
    throw new \Exception('Unknown property type');
}

$commission = $property->getCommission();
$theme = $property->getTheme();

<img src="{{ $theme->logo_url }}">
```

This will look up the property based on the current URL.
If you wish to override this, it's best to register a new factory with the Service Container like this:

```
app()->bind(\Stickee\Sync\Models\Property::class, function () {
    $property = ... // Load property - consider using \Stickee\Sync\PropertyService

    return $property;
});
```
**NOTE:** Be sure to use `$property->getCommission()` instead of `$property->commission` and `$property->getTheme()` instead of `$property->theme` to inherit values from parent records.

## Unknown Properties

If the property cannot be found, then a `Stickee\Sync\Exceptions\PropertyNotFoundException` exception will be thrown.
It is recommended to catch this exception and display a friendly error page.

## Configuration

For sites that need to synchronise with the live Comparison Platform system, the following variables must be set:

 - `AFFILIATES_API_URL` The API URL - usually https://TODO/... or your local copy, e.g. https://comparison-platform.test/...
 - `AFFILIATES_API_KEY` The API key - must be unique to your project

If you wish to edit the configuration file directly, you can publish it by running:
```
php artisan vendor:publish --provider="Stickee\Sync\ServiceProvider"
```

## Creating a Property

When setting up a dev machine, you will need to create the `property` and associated records in the database.
Run `php artisan sync:create-property` to go through this process on the command line, or run your own instance of Comparison Platform.

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
