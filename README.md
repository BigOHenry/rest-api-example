# Rest API Example

## Technology
- PHP 8.2
- PostgreSQL 17.5
- Symfony 7.2
- Doctrine ORM

## Installation

### Step 1: Execute Installation script

#### For Windows:

```sh
resources/scripts/install.bat
```

#### For Linux/macOS:

```sh
resources/scripts/install.sh
```

### Step 2: Go to application

```
http://localhost:8080
```

The Swagger documentation page should appear (redirected to /api/doc).

## Documentation

- Full REST API documentation is available at `http://localhost:8080/api/doc` with all endpoints
- The documentation contains complete call and response examples, as well as error responses with examples 


## CS + PHPStan + PHPUnit

- (before executing commands bellow go into the app container with `docker exec -it rest_api bash`)
- Static code analysis is performed by PHPStan `vendor/bin/phpstan analyse`
- Code quality is checked according to PSR-12 and other rules using CodeSniffer `vendor/bin/phpcs` or automatic fix `vendor/bin/php-cs-fixer fix`
- Unit tests are solved using PHPUnit `php bin/phpunit`

## API Usage Examples

### User Registration <span style="color:green">POST /api/auth/register</span>

- The registration endpoint is primarily used to create the first admin user in the system. After that, additional admins must be created by existing administrators using the /api/users endpoint.
- Users with roles ROLE_READER and ROLE_AUTHOR can be created without any limitations
- The user's email must be unique.
- The password must be at least 8 characters long, contain upper and lower case letters, a number and a special character.

#### Request body:
```json
{
    "email": "admin@example.com",
    "password": "Password1234.",
    "name": "System Administrator",
    "role": "ROLE_ADMIN"
}
```

#### Response body (HTTP 200):

```json
{
  "message": "User created successfully",
  "user": {
    "id": 1
  }
}
```

### User Login <span style="color:green">POST /api/auth/login</span>

- After successfully authenticate the user, a JWT/Bearer token is returned in the response, which is then used in the request header during further communication `Authorization: Bearer <token>` 

#### Request body:
```json
{
    "email": "admin@example.com",
    "password": "Password1234."
}
```

#### Response body (HTTP 200):

```json
{
  "token": "Here is JWT token"
}
```


### Article creation <span style="color:green">POST /api/articles</span>

- To use this endpoint (and all the others), it is first necessary to authenticate `/api/auth/login`
- Article name must be unique and longer than 2 characters.
- Content article must be at least 50 characters.

#### Request body:
```json
{
  "name": "What is Lorem Ipsum?",
  "content": "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
}
```

#### Response body (HTTP 200):

```json
{
  "message": "Article created successfully",
  "article": {
    "id": 1
  }
}
```