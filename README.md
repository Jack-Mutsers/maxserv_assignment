# MaxServ B.V. Assignment

This project acts as a foundational implementation for the MaxServ B.V. assignment. You are welcome to modify, expand, and refactor the code as needed to meet the assignment criteria. Please note that while PHP packages are allowed, the use of PHP frameworks is not permitted.

## Getting Started
To execute the project, you will need to install the following prerequisites:

- `Docker` - A platform for developing, shipping, and running applications. [Learn more](https://www.docker.com/get-started).
- `Composer` - A dependency manager for PHP. [Learn more](https://getcomposer.org/).
- `Git` - A distributed version control system. (Only required if your code is managed in a Git repository) [Learn more](https://git-scm.com/).

Now that all prerequisites are installed, we can initiate the project by following the steps below:

1. Execute the `composer install` command to install the project dependencies.
2. Execute the `docker-compose up -d` command to launch the application.

The project should now be operational and accessible at `http://localhost:8080`.
You can also access phpMyAdmin at `http://localhost:8081`.

### Application flow
The application begins from a single entry point, `public/index.php`, which ensures composer autoloading and initiates a bootstrap process.
The bootstrap process sets up the application. It creates a `Container` to facilitate dependency injection and a `Router` to manage incoming requests.

A default route is specified in `config/routes.yaml`, which links the `/` route to the `IndexController` and the `index` method.
The `IndexController` renders the `index.html.twig` template, located in the `templates` directory.
The `IndexController` also includes the `ProductRepository` as an example of dependency injection and a convenient way to interact with the database.

## Project Structure

The project is structured as follows:

### Git
This project employs Git for version control, and basic Git configuration is included.

- `.gitignore` - Lists files and directories that should be ignored by Git. [Learn more](https://git-scm.com/docs/gitignore).

### Docker
The project can be run within a Docker container, with the necessary Docker configuration provided.

- `Dockerfile` - Contains instructions for building the Docker image. [Learn more](https://docs.docker.com/engine/reference/builder/).
- `docker-compose.yml` - Defines the services, networks, and volumes for running the Docker container. [Learn more](https://docs.docker.com/compose/compose-file/).
- `.dockerignore` - Lists files and directories to be ignored by Docker. [Learn more](https://docs.docker.com/engine/reference/builder/#dockerignore-file).

### Composer
This project utilizes Composer for dependency management, with the relevant configuration included.

- `composer.json` - The file used to manage and install project dependencies. [Learn more](https://getcomposer.org/doc/04-schema.md).

### Configuration
The project includes configuration files for various tools and services.

- `config/routes.yaml` - Contains the routing configuration for the project. [Learn more](https://symfony.com/doc/current/routing.html#creating-routes-in-yaml-xml-or-php-files).

### Packages
The `packages` directory contains the code for the project.

- `packages/core` - Contains the core functionality of the application.
- `packages/app` - The package where the assignment requirements can be implemented.

### Public
The `public` directory contains the entry point for the application. It is advisable to keep this directory as clean as possible and not alter the logic in the `index.php` file.

### Templates
The `templates` directory contains the Twig templates for the application.

- `templates/index.html.twig` - The example template provided with the project.

## Core Package
The `core` package encompasses the core functionality of the application. The `core` offers some default functionality that can be utilized while constructing your implementation.

### Database Connection
The `core` package provides a database connection using PDO. The database connection is configured through environment variables, which can be set in the `docker-compose.yaml` file.

### Template Rendering
The `core` package includes a `TemplateRenderer` class that can be used to render Twig templates. The `TemplateRenderer` class is initialized with the `Twig` environment and can be employed to render templates. All templates should be defined in the `templates` directory.

## Provided PHP packages
With the foundation, we provide you with several PHP packages out-of-the-box. These packages are included in the `composer.json` file and can be utilized to enhance the functionality of the project.

- `guzzlehttp/guzzle` - A PHP HTTP client that simplifies sending HTTP requests. [Learn more](https://docs.guzzlephp.org/en/stable/).
- `symfony/routing` - Offers tools for routing requests to PHP code. [Learn more](https://symfony.com/doc/current/routing.html).
- `symfony/config` - Assists in finding, loading, combining, autofilling, and validating configuration values of any kind, regardless of their source (YAML, XML, INI files, or even a database). [Learn more](https://symfony.com/doc/current/components/config.html).
- `symfony/yaml` - Provides tools for handling YAML files. [Learn more](https://symfony.com/doc/current/components/yaml.html).
- `symfony/http-foundation` - Defines an object-oriented layer for the HTTP specification. [Learn more](https://symfony.com/doc/current/components/http_foundation.html).
- `symfony/dependency-injection` - Enables you to standardize and centralize the way objects are constructed in your application. [Learn more](https://symfony.com/doc/current/components/dependency_injection.html).
- `symfony/finder` - Offers tools for locating files and directories. [Learn more](https://symfony.com/doc/current/components/finder.html).
- `ext-pdo` - The PHP Data Objects (PDO) extension provides a lightweight, consistent interface for accessing databases in PHP. [Learn more](https://www.php.net/manual/en/book.pdo.php).
- `twig/twig` - A flexible, fast, and secure template engine for PHP. [Learn more](https://twig.symfony.com/).
