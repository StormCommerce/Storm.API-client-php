STORM API PHP
============

Storm API Client is a client library for easy integration against the Storm API. This is used when building applications that calls Storm API. It wraps calls to Storm API.
This is a PHP Client library for communication and integrating the [Storm](https://storm.io/storm-commerce/) E-commerce platform. 
It's the equivalent of the official .NET Client from Storm Commerce that can be found [here](https://github.com/StormCommerce/Storm.API-client-dotnet). 

============

### Prerequisites

To be able to use the client to communicate with Storm you need to have an **account** set up by Storm Commerce and a client **certificate** sent to you. The certificate is unique for each application in Storm. 

From Storm Commerce:

1. Client certificate (ClientName.pfx normally)
2. Password for the certificate

A UNIX-like system is highly recommended. The README is written based on that assumption.
If you are running on a Windows system you can use [Vagrant](https://www.vagrantup.com/) to run a virtual machine.

Name | Version
------------ | -------------
PHP | min 5.6+
Curl | min 7.35
Redis | min 3.0.7
Composer | min 1.2.1

PHP packages:

* curl
* intl
* json
* libxml
* mbstring
* mcrypt
* Reflection
* SimpleXML
* soap
* session

### Supported methods

All operations returns an instance of StormModel or an extension of StormModel. These models support the toJson() -method which can be used to debug the returned data from Storm. 

All operations available in the [StormApi](https://stormstage.enferno.se/api/21/docs/index2.html) are available through the proxy classes. 

Example: 

```
$client->application();
$client->customers();
$client->orders();
$client->products();
$client->shopping();
```
Method calls supports both normal parameters passing or an array:

```
$client->products()->ListParametricValues(1,2);
// Or
$client->products()->ListParametricValues([
    'id' => 1,
    'type' => 2
]);
```


### Installation


#### 1. Start by converting the .pfx certificate obtained from Enferno to a .pem format supported by curl. 

```
$ openssl pkcs12 -in cert.pfx -out cert.pem -nodes
```

#### 2. Add this client to your composer.json: 

```
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/StormCommerce/Storm.API-client-php.git"
    }
  ],
```

#### 3. Install the package

Run this command in the project root

```
$ composer require iveo/storm-client
```

#### 4. Generate application encryption key
Storm saves the state of each visitor in a cookie. In this client the cookie is encrypted with a private encryption key. A script to generate the key is included in the storm-client root. This should be invoked from the storm-client root directory. 

```
$ cd vendor/iveo/storm-client
$ php generate-key.php
```

#### 5. Move the key to your project root
The key should be moved to the root of your project and later referenced in the configuration. Example: 

```
$ mv key ../../../
```

#### 6. Move the certificate to your project root. 

#### 7. Start Redis if not already running


### Configuration

The following needs to be configured to run the Storm Client: 

```
$dirname = dirname(__FILE__);
/*
 * application_name: The name of the application is used in various cache keys and if multiple instances are running you need different names
 * certification_path: The path to the certificate file obtained and converted from Enferno
 * certification_password: The password to the certificate obtained from Enferno
 * redis_path: Your address to redis server, e.g. tcp://127.0.0.1:6379
 * app_path: path to application encryption key
 * base_url: The base URL to the Storm API
 * image_url: The URL to the image bucket, often on azure. Obtained from Storm
 * expose_path: Path to your expose directory, this is used for entity resolving, operation mapping (GET/POST) and auto completion in IDE (standard PHP doc format)
 * middlewares: If any middlewares are used, configure them here. 
 */
$config =  [
    
    'application_name' => 'storm_test',
    'certification_path' => "$dirname/cert.pem",
    'certification_password' => 'password',
    'redis_path' => 'tcp://127.0.0.1:6379',
    'app_path' => "$dirname/key",
    'base_url' => 'https://servicesstage.enferno.se/api/1.1/',
    'image_url' => 'http://az666937.vo.msecnd.net/42/',
    'expose_path' => "$dirname/expose",
    'middlewares' => [
        'parameters' => [
            //ContextParameterMiddleware::class
        ]
    ]
];
```

### Client instantiation
Instantiate the client: 

```
$client = StormClient::self($config);
$client->context(); // Boots context and saves StormContext encrypted cookie

$client->expose()->generateClasses(); // Generate expose, only needs to run once if Enferno updates the StormApi
```




### Examples

All operations returns an instance of StormModel or and extension of StormModel. These models support the toJson() -method which can be used to debug the returned data from Storm. 

All operations available in the [StormApi](https://stormstage.enferno.se/api/21/docs/index2.html) are available through the proxy classes. 

Example: 

```
$client->application();
$client->customers();
$client->orders();
$client->products();
$client->shopping();
```
Method calls supports both normal parameters passing or an array:

```
$client->products()->ListParametricValues(1,2);
// Or
$client->products()->ListParametricValues([
    'id' => 1,
    'type' => 2
]);
```
**StormContext**

The StormContext provides default session handling functionallity. It is used to track customer basketid, customerid and various storm tracking.

You can access it like this:

```
$client->context()->get('BasketId');
```


**Get Application**

```
$application = $client->application()->GetApplication();

// You can use toJson() to see raw json data from Application object
echo $application->toJson();
```

**Get Categories**

```
$categories =  $client->products()->ListCategories();
```

**Get Products**

```
$client->products()->ListProducts2();
```

**Get Basket** 

```
$client->shopping()->GetBasket($id);
```

**Get Checkout**

```
$client->shopping()->GetCheckout2($basketId);
```

**Cancel Payment**

Some methods require a Storm model to be sent. You simply pass the model object to the method and Storm Client will handle the rest.

```
$client->shopping()->PaymentCancel($basket);
```

**Cache**

The Storm Client currently only supports redis but will release update for multiple cache providers in a future release

You can get an instance of the cache client:

```
$client->cache()->put('test', 'Hello World', 5); // Key, Value, TTL

echo $client->cache()->get('test', ':/'); // Echoes Hello World if still exist otherwise prints default string :/
```

To send a request without getting a cache hit you can append a 'noCache => true'
to the method call

```
$client->products->GetProduct(['id' => $id, 'noCache' => true])
```