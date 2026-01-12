# Creating an Application

This section will cover the process of creating an application based on this component.

## Application and Engine

`Engine` is a set of modules that manage low-level components such as timers, workers, processes, 
and asynchronous operations. 
This allows different applications to be built on the same `Engine`.

The role of the `Application` is to initialize the runtime environment, 
load all dependencies, and ultimately start the `Engine`.

In essence, the `Application` is the strategy for launching the application, 
while the `Engine` is its low-level core. The `ServiceManager` represents the business logic, 
and the `SystemEnvironment` is the lifeblood that ties everything together.

Thus, you can have multiple "applications" for the same project. 
Essentially, you can have multiple running processes-entry points-each of which can operate slightly differently. 
This approach can be extremely useful: having a single codebase with different ways to use it.

## Application class

The `Application` is responsible for the process of starting the `Engine` and the `Service manager`.
From this perspective, the `Application` class is the strategy for launching the `Engine`.

Below is an example of a Console application.

Creating the main application class might look like this:

```php
<?php
declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineInterface;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;

class ConsoleApplication            extends ApplicationAbstract
{
    #[\Override]
    protected function engineStartAfter(): void
    {
        (new SymfonyApplication(
            $this->systemEnvironment,
            $this->systemEnvironment->resolveDependency(DescriptorRepositoryInterface::class)
        ))->run();
    }
    
    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }        
}
```

At least two methods will need to be implemented:

1. A method that **starts** the application `engineStartAfter`. 
2. A method that defines the default Role `defineEngineRole`.

The `engineStartAfter` method is called after the application has been initialized.
At this point, all dependencies and components are available, 
including `SystemEnvironment`, `Engine`, and the `Service Manager`. 

In example of a console application that will map console commands to service calls.

For Web-server applications, the server itself should be the `Engine`, 
and the `Application` class is responsible for starting it.

## Runner

The `Runner` class is responsible for the application's startup logic. 
It performs the `bootloader process` and builds the `SystemEnvironment` for the application.

## What Do Applications Do?

All applications share the common characteristic that they generally invoke services in some way. 
For example, in a console application, services are invoked through console commands. 
A web server uses a dispatcher to invoke services. 
You can also create an application that invokes services based on an event queue.

Essentially, an application is a strategy that determines how service methods will be called 
and how their parameters and results will be handled.