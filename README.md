# reservepay-php

PHP clients to allow usage of the Reservepay APIs - [Merchant](https://developer.reservepay.com/api/merchants), [SDK](https://developer.reservepay.com/api/sdk)
, and [Terminal](https://developer.reservepay.com/api/terminals).

The clients (found in `src/`) were generated using the [WebFunction CLI tool](https://github.com/webfunction-protocol/webfunction-cli).

## Usage

After `composer install reservepay/reservepay-php`:

```php
require "vendor/autoload.php";

define('API_KEY', 'my_api_key');

$client = new Reservepay\Merchant\Client(bearerAuth: API_KEY);
$versions = $client->availableApiVersions();

var_dump($versions);

```
