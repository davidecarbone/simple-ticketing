# Simple ticketing API

An API layer providing basic functionalities of a ticketing system.

## Getting Started

### Installing

You can use docker-compose to build a container network with all requirements:
```
$ docker-compose up -d
```

This will run three containers with:
 - the main web application
 - a MySQL database
 - a swagger-ui container for documentation
 - an Adminer UI for mysql database
 
The web container will also execute migrations to provide some test tickets, along with some test users.

NOTE: the very first build of containers with all requirements may take a while depending on your connection.

### Documentation
Available endpoints are fully documented with OpenAPI 3.0.

Swagger UI is available at http://localhost:8082

### Adminer
Adminer UI to administrate the database is available at http://localhost:8081

## Running the tests

To run unit tests simply run from the project root:
```
$ vendor/bin/phpunit
```

Integration and End2end tests are not run by default. To run them:
```
$ vendor/bin/phpunit --testsuite end2end
```

## Disclaimer
This is a case study and it's not meant for production! Even though basic security and validation is provided, this code is not suitable for a production environment.

### Todos and omissions
- Pagination and navigability/hateoas have been omitted
- Expose an endpoint to allow an admin to assign a ticket to another admin
- Expose an endpoint to allow an admin to request to be assigned on a ticket

## Built With
* PHP 7.3
* [Symfony 5](http://www.symfony.com/)
* [MySQL](https://www.mysql.com/)

## Authors
* **Davide Carbone** - *Initial work* - [Davide Carbone](https://github.com/davidecarbone)

## License
This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
