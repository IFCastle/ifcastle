# Type definitions [![PHP Composer](https://github.com/EdmondDantes/type-definitions/actions/workflows/php.yml/badge.svg)](https://github.com/EdmondDantes/amphp-pool/actions/workflows/php.yml)

A metadata library for describing the types of procedures, services, and objects.

## Why is this needed?

Information about data types and method prototypes can be used for code generation, 
forming `DataTransferObjects`, and remote calls. 
This library provides an independent infrastructure for forming metadata 
about data types that is not tied to a specific implementation.

## Features

* Classes for describing data types
* Forming metadata through PHP Reflection and attributes.
* Serialization and deserialization of data into a `JSON`-like structure (`JSON-array`).
* `ValueContainer` pattern: a container for storing values with a type descriptor.
* A PHP code generator for serialization, validation, and deserialization for better performance.

## Installation

```bash
composer require ifcastle/type-definitions
```

## Example

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

// TODO: Add example

```