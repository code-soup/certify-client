## Certify Client

A license management and verification solution for commercial WordPress plugins.

## Description

The Certify Client is a composer package that provides a license activation and verification solution for custom WordPress plugins.
In order to use Certify Client, you must have the Certify Server installed on your server.

Certify Server is a free WordPress plugin which enables you to manage licenses for your custom WordPress plugins and provides custom update repository to handle plugin updates.

## Features

-   Verify license keys issued by the Certify Server
-   Integrate license verification into your plugin with ease, using our simple and intuitive API
-   Limit number of activations per license
-   Generate license for multiple plugins
-   Integrated with [Paddle.com](https://www.paddle.com) subscriptions

## Requirements

-   PHP >= 8.2
-   Composer
-   [Certify Server](https://github.com/code-soup/certify) installed on your server

## Setup

1. Install composer package

```bash
composer require code-soup/certify-client
```

2. Initialise Certify Client class

```php
$certify = \CodeSoup\CertifyClient\Init::get_instance();
$certify->init([
    'plugin_id'             => 'my-plugin-folder-name',
    'plugin_version'        => '0.0.1',
    'cache_allowed'         => true,
    'certify_server_origin' => 'https://my.website.com',
    'license_key'           => '12345-12345-12345-12345-12345'
]);
```

### Configuration Options

---

The `init` method takes an array of configuration options, which are used to configure the plugin. The following options are available:

-   `plugin_id`: The folder name of your plugin (e.g., `my-plugin-folder-name`).
-   `plugin_version`: The version of your plugin (e.g., `0.0.1`).
-   `cache_allowed`: A boolean indicating whether caching is allowed (default: `true`). This saves certify server response in transient which expires each day. This way only 1 request per day is made to your server
-   `certify_server_origin`: The origin URL of the Certify server (e.g., `https://my.website.com`).
-   `license_key`: The license key for your plugin (e.g., `12345-12345-12345-12345-12345`).

## Validate License Key

---

You can then simply validate license key against certify server:

```php
$certify->validate();
```

Response:

```json
{
	"valid": true,
	"expiry": 1748995200
}
```

-   `valid`: Days left to expire > 0 AND activations limit is not yet reached. This would also mean that user is allowed to install plugin update.
-   `expiry`: Timestamp when key is about to expire

In case of any error following is returned:

```json
{
	"valid": false,
	"expiry": 0
}
```

## Issues

Please use [Github issues](https://github.com/code-soup/certify-client/issues) to submit any bugs you may find.

## License

This project is licensed under the [GPL license](http://www.gnu.org/licenses/gpl-3.0.txt).
