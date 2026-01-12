# Configuator toml [![PHP Composer](https://github.com/EdmondDantes/configuator-toml/actions/workflows/php.yml/badge.svg)](https://github.com/EdmondDantes/configuator-toml/actions/workflows/php.yml)

Configurator for the IfCastle framework in [TOML format](https://toml.io/).

## Installation

> This package must be installed within the **IfCastle** application environment,
> i.e., in `ifcastle/package-installer`,
> if you want the dependencies to be correctly defined.


```bash
composer require ifcastle/configuator-toml
```

## Usage

The package defines two toml files:

* the main application configuration file: `main.toml`
* the service registry: `services.toml`

All files must be located in the root directory of the project.

## Syntax

Automatic replacement for `ENV` variables will also work, i.e., syntax with "%":

```toml
[database]
host = "%DB_HOST%"
port = "%DB_PORT%"
user = "%DB_USER%"
password = "%DB_PASSWORD%"
```

See also the [toml](https://toml.io/).