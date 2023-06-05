# Guide

This guide contains a list of bin commands to perform/check and utilize the framework.
Additionally what am I supposed to do here, some muttering, whining and mumbling.

> **Note**: This is a ready set example from the framework, and I am really grateful for it.
>           But since it is my workspace, I'll get a bit balistic without any offence meant! :D

Since it is the first time installing it from scratch, without guidance from a protected working environment I would
demand the following compensations;
- A bottle of tsipouro
- 4 tabacco packs

**Have fun reading!**

## Contents

- [Guide](#guide)
  - [Contents](#contents)
  - [Task](#task)
  - [Config](#config)
    - [Docker](#docker)
    - [Update composer](#update-composer)
    - [React](#react)
    - [Localhost](#localhost)
    - [Migrations](#migrations)
    - [Tests](#tests)
    - [Processes/Queues](#processesqueues)
  - [Developement](#developement)

## Task

Long version short

- Download a file
- Don't spam the server containing the file, if you do not wish to be cut out
- Extract it's contents
- Store the contents and possible download additional resources
- Display the contents
- Any tests provided is an asset!

-----

Back to [Home](#guide) - [Contents](#contents) 

-----

## Config

For this task I've followed and used [Laravel Bootcamp tutorial](https://bootcamp.laravel.com/introduction)

Only minor tweaks where performed.

### Docker

> **Note**: Assuming that you have already installed docker and it is up to date.
>           If you are a first timer, bare sometime and read, otherwise skip this chapter.

Instead of using directly docker command which would be;

```bash
$ docker up -d
```

Initialize it with;

```bash
$ # added -d to run it on the background
$ ./vendor/bin/sail up -d
```

To terminate it, it is quite straightforward.
What goes up, should go down. Thus;

```bash
$ ./vendor/bin/sail down
```

Few extra commands in case you need them

```bash
$ # get all containers
$ docker ps
$ # or with sail script
$ ./vendor/bin/sail ps


$ # mariadb, mysql usually if you downloaded chirper that should be `chirper-mysql-1`
$ docker exec -it ${NAME_OF_DB} -usail -p -A chirper
```

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### Update composer

This will update composer to the latest version.
Will only need to perform this one if there is a solid installation in your workstation.

```bash
$ composer self-update
```

But, there is a catch.
By running this command, it will result to update composer to the latest version for all possible setup's you have.

In case you are happy with latest version, no need to keep reading.
To confine, this update, it to given docker image:

```bash
$ ./vendor/bin/sail root-shell
$ composer self-update
```

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### React

This one is kinda mindblowing.
It is one of the `RTFM` cases, where although not skipped, I **SHOULD** re-`RTFM`
So few key installation key concepts in case that are not present within `./package.json` file

```bash
$ # add Inertia support and files
$ composer require laravel/breeze --dev
$ # add Inertia jsx parser
$ ./vendor/bin/sail artisan breeze:install react
```
Why did I do that for?
I wanted some examples for a trial by error development approach.

Now, add some thrash within the database installation and you are just a step before getting results.
You have 2 alternatives to do that:

```bash
$ php artisan migrate
$ # or
$ ./vendor/bin/sail artisan migrate
```

Preferable go with the second one.

Why?

Cause if you have installed PHP `cli` already in your workstation `artisan` will not be able to read and expose environment variables located into `.env` file used by docker containers via `sail`.

There is a workaround to do that so, but this is a different story, with more pain than gain!

Finally, in case that you've decided to use routing via `ziggy` you might need to update it once in a while

```bash
$ ./vendor/bin/sail artisan ziggy:generate
```

That is all from react.

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### Localhost

In order to get display everything into your browser start with

```bash
./vendor/bin/sail npm run dev
```

To expose this site to the entire local network (and who know's where else)

```bash
./vendor/bin/sail npm run dev -- --host
```

> **Note**: In case that you are following bootcamp tutorial you **MUST**, once you've started `npm`, check top right corner of your screen!
>           This is where `font-size:4px` `register` is located!

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### Migrations

```bash
$ # automatically add a migration
$ ./vendor/bin/sail artisan make:migration
$ # tweak generated file and
$ ./vendor/bin/sail migrate
$ # revert any changes from the last migration
$ ./vendor/bin/sail migrate:rollback --step=1
```

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### Tests

Tests do use PHPUnit. Yet, just like migration there is a catch.
i.e. starting with the first already provided test.

```php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}

```

This will run fine, as long as there are no Laravel fakers and fake Facades in it.
In case though there are this will probably result to a `Runtime Exception`.

To fix this problem you need to replace `PHPUnit\Framework\TestCase` with `Tests\TestCase`;

The file will become

```php
namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}

```

To run those tests

```bash
$ # batch testing
$ ./vendor/bin/sail test
$ # single file testing
$ ./vendor/bin/sail test ./tests/Unit/ExampleTest.php
$ # coverage
$ ./vendor/bin/sail test --coverage
```

For the latter command you need to configure `./phpunit.xml` with coverage tag.

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----


### Processes/Queues

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

## Developement

This is the fun part, where the short story becomes long.

Most of the work done is within

`./app/Customizations/`

Pick your design pattern, a mochito and when necessary start pointing the finger.

To be continued.....

-----

Back to [Home](#guide) - [Contents](#contents) - [Development](#developement)

-----

