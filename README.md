# PHP Retrier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/s-patompong/php-retrier.svg?style=flat-square)](https://packagist.org/packages/s-patompong/php-retrier)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/s-patompong/php-retrier/Tests?label=tests)](https://github.com/s-patompong/php-retrier/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/s-patompong/php-retrier/Check%20&%20fix%20styling?label=code%20style)](https://github.com/s-patompong/php-retrier/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/s-patompong/php-retrier.svg?style=flat-square)](https://packagist.org/packages/s-patompong/php-retrier)

Retrier can help you retry your logic easily.
```php
<?php

// Your own API class
use App\Api\ApiConnector;
use SPatompong\Retrier\Retrier;

$api = new ApiConnector();

// By default, Retrier use RetryThrowableStrategy which will retry if the result is an instance of \Throwable
$result = (new Retrier())
    ->setLogic(function() use($api) {
        return $api->get();
    })
    ->execute();
```

## Installation

You can install the package via composer:

```bash
composer require s-patompong/php-retrier
```

## Usage

Fields and their default values:

| Field           | Description                                    | Setter                                                                                                              | Default |
|-----------------|------------------------------------------------|---------------------------------------------------------------------------------------------------------------------|---------|
| delay           | Wait time between each retry                   | setDelay(int $delay): Retrier                                                                                       | 3       |
| retryTimes      | Number of retry                                | setRetryTimes(int $retryTimes): Retrier                                                                             | 3       |
| onRetryListener | A closure that will be called on retry         | setOnRetryListener(function(int $currentTryTimes, ?mixed $returnedValue, ?\Throwable $throwable): void {}): Retrier | null    |
| retryStrategy   | A class that implement RetryStrategy interface | setRetryStrategy(RetryStrategy $retryStrategy): Retrier                                                             | null    |

Minimal configuration example:
```php
<?php

// Your own API class
use App\Api\ApiConnector;
use SPatompong\Retrier\Retrier;

$api = new ApiConnector();

$retrier = (new Retrier())
    ->setLogic(function() {
        return 0;
    });

// After 3 retries, it's possible that the code still gets the \Throwable
// Thus, we still need to put it in a try/catch block
try {
    $value = $retrier->execute();
} catch(\Throwable $t) {
    echo "Still gets throwable after 3 retries.\n";
}
```

Full configuration example:
```php
<?php

// Your own API class
use App\Api\ApiConnector;
use SPatompong\Retrier\Retrier;
use SPatompong\Retrier\Presets\Strategies\RetryNullStrategy;

$api = new ApiConnector();

// Keep track of retry count, useful for logging or echoing to the terminal
$retryCount = 0;

$value = (new Retrier())
    // Change the stragegy to RetryNullStrategy to keep retrying if the Logic returns null
    ->setRetryStrategy(new RetryNullStrategy())
    
    // Set the wait time for each retry to 10 seconds
    ->setDelay(10)
    
    // Let the code retry 5 times
    ->setRetryTimes(5)
    
    // Set the onRetryListener to print out some useful log
    ->setOnRetryListener(function ($currentTryCount, $value, $throwable) use (&$retryCount) {
        $retryCount++;
        echo "Failed to get API data, retry count: $retryCount\n";
    })
    
    // Set the logic
    ->setLogic(fn () => $api->get())
    
    // Execute it
    ->execute();
    
// At this point, value could still be null if after 5 times the code still couldn't get the API data
echo $value;
```

It's also possible to use callable array syntax when set the logic or retryListener:
```php
<?php

use SPatompong\Retrier\Retrier;
use SPatompong\Retrier\Tests\Helpers\FakeClass;

$fakeClass = new FakeClass();

$publicMethodResult = (new Retrier())
    ->setLogic([$fakeClass, 'fakePublicMethod'])
    ->execute();
    
$staticMethodResult = (new Retrier())
    ->setLogic([FakeClass::class, 'fakeStaticMethod'])
    ->execute();
```

## Retry Strategy

RetryStrategy is a class that implement RetryStrategy interface. The Retrier class uses it to determine if it should retry or not (given the return value from the logic).

```php
<?php

namespace SPatompong\Retrier\Contracts;

interface RetryStrategy
{
    /**
     * Add a logic to check if the retrier should retry
     *
     * @param mixed $value
     * @return bool
     */
    public function shouldRetry(mixed $value): bool;
}
```

This library provides two presets strategy:
1. `RetryThrowableStrategy` - This is a default strategy that will retry any \Throwable response.
2. `RetryNullStrategy` - This strategy will keep retry if the response is NULL.

If you want to have a custom `shouldRetry()` logic, you can create your own RetryStrategy class and implement this RetryStrategy interface.

```php
<?php

namespace App\RetryStrategies;

use SPatompong\Retrier\Contracts\RetryStrategy;
use GuzzleHttp\Exception\ClientException;

class RetryGuzzleClientExceptionStrategy implements RetryStrategy
{
    public function shouldRetry(mixed $value): bool
    {
        return $value instanceof ClientException;
    }
}
```

Then, set it as a retry strategy of the Retrier:

```php
<?php

use SPatompong\Retrier\Retrier;
use App\RetryStrategies\RetryGuzzleClientExceptionStrategy;

$retrier = (new Retrier())
    ->setRetryStrategy(new RetryGuzzleClientExceptionStrategy())
    
try {
    $retrier->execute();
} catch(\Throwable $t) {
    // Still gets ClientException after retry or other type of Throwable
}
```


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Patompong Savaengsuk](https://github.com/s-patompong)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
