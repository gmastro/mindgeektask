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
      - [Automate Seeding](#automate-seeding)
    - [Processes/Queues](#processesqueues)
  - [Developement](#developement)
    - [Testing](#testing)
  - [Run](#run)
  - [Conclusions](#conclusions)
    - [Vendor Documentation](#vendor-documentation)
    - [Vendor Source Code](#vendor-source-code)
    - [Artisan Digging](#artisan-digging)
    - [Eloquent](#eloquent)
      - [Relations](#relations)
      - [Upserts](#upserts)
      - [Transactions](#transactions)
      - [Incosistencies](#incosistencies)
    - [Debugging](#debugging)
    - [Queues, Events, Listeners](#queues-events-listeners)
    - [Websockets](#websockets)
  - [Epilogue](#epilogue)

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
>           This is where `font-size:1px` `register` is located!

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### Migrations

```bash
$ # automatically add a migration
$ ./vendor/bin/sail artisan make:migration
$ # tweak generated file and
$ ./vendor/bin/sail artisan migrate
$ # revert any changes from the last migration
$ ./vendor/bin/sail artisan migrate:rollback --step=1
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

#### Automate Seeding

To create a default seeding for all models.
Usable when you start a fresh migration.
After that you can trigger either the scheduler or `RemoteFeedsJob`

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

### Processes/Queues

Always keep a track on which jobs are running and when.
So far there is only a single working queue (the default one).
More jobs and on different queues are on the way.

To track all jobs

```sh
$ ./vendor/bin/sail artisan queue:info --timeout=N --queue=default,job1,..jobN
```

-----

Back to [Home](#guide) - [Contents](#contents) - [Config](#config)

-----

## Developement

This is the fun part, where the short story becomes long.

Most of the work done is within

`./app/Customizations/`

All added classes have been added as part of a design pattern.
Pick your design pattern, a mochito and when necessary start pointing the finger.

To be continued.....

And we are back! Didn't took long did it?

The structure is
- Components
- Adapters
- Composites
- Traits

Starting with some decoupled components.
- One that utilizes Curl for examining and downloading sources
- One that utilizes fopen for creating stream resources on filesystem.
  This one is used for storing downloaded content

Adapters
- Using a combination of fopen and curl to download content
- Using Redis Facade to store content

Composites
- Usable by jobs especially to chain various tasks using the smallest possible payload, yet, able to communicate by acquiring and sharing content

Traits
- Some horizontal magic happens here
- Tracking errors and unwanted conditions
- Sharing content between composites
- Generating and retrieving filenames from url sources if no-name is provided.

### Testing

Couple of places to check content
- Unit
- Featured

Within Unit all Customization classes have been thoroughly tested (in case that few are missing they will soon be added).
Within Featured you may find tests for structured Eloquent Models, Listeners, Queues and events.

-----

Back to [Home](#guide) - [Contents](#contents) - [Development](#developement)

-----

## Run

All the effort is time to come into fruition. To start the whole process

```bash
$ ./vendor/bin/sail up -d \
  & ./vendor/bin/sail artisan migrate:fresh \
  & ./vendor/bin/sail artisan db:seed \
  & ./vendor/bin/sail artisan schedule:run \
  & ./vendor/bin/sail npm run dev
```

Open your preferable browser and type `localhost` into url path

The job should have already have started via scheduler.
In case you wish to re-examine the results you may click on link `force-scheduler`

-----

Back to [Home](#guide) - [Contents](#contents)

-----

## Conclusions

Since this became a Research and Development project, rather than a mere job application task, the following `out of scope` tasks took place.

- Instead of examining and downloading a single file, I've added a table able to hold various url sources including stats
- Instead of using Cache and Storage to display images, all images are stored into Redis
- Instead of using Blade templating system, added Inertia with React
- Separated the logic into various design patterns, possible thinking of reusability, mostly decoupled (excluding composite components).

Thus, practitioning with provided vendor tools and assets, the following conclusions have been reached;

-----

Back to [Home](#guide) - [Contents](#contents)

-----

### Vendor Documentation

Just one word. **AWFUL**

- You need a subscription to be able to read something that makes sence.
- Lack of examples
- Bits of sparced code without a proper explanation

Yes, software is not free and services do cost.
But the way the framework attempts to monetize as a merchandise is deterrent.

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

### Vendor Source Code

Yes it does what is supposed to do, provides what is supposed to provide.
Unfortunately, it throws errors here and there.
My guess is you probably have to have `PHPStorm` in order to use the framework to its full potential! (?)

Lets try the following example

```php
$path = Storage::disk('whatever')->path('');
dump($path); // will output absolute path
```

Assuming that you have software which tracks and displays documentation per instance, method variable etc., so;
- `Storage` derives from `Illuminate\Support\Facades\Storage`
- `Storage` extends to `Facade`... so far so good, nothing to argue with here
- `Storage::disk()` ? Where is that?
- Lets try the annotations
- Declaration says, `disk()` is part of `\Illuminate\Contracts\Filesystem\Filesystem` interface
- When opening the interface there is no such method implemented.

Lets try again through `abstract Illuminate\Support\Facades\Facade`
- Nope neither here, and why should it be?
- We only emulate the state of some called class or dependency, yet somewhere along the way the dependency exists

Lets try again through the annotations
- `{@see Illuminate\Filesystem\FilesystemManager}`
- This looks promising, yet again nope!
- No such method here either

Lets try the non standard annotations `@mixin` within `Illuminate\Filesystem\FilesystemManager`
- yes non-standard, for more information _please read [DocBlock](https://docs.phpdoc.org/guide/references/phpdoc/index.html)_
- and found `public function path(string $path);` at `Illuminate\Filesystem\FilesystemAdapter`

So time to declare the variable and get the absolute location of a disk (or relative if it is `s3`), without the warning

```php
/**
 * @var Illuminate\Filesystem\FilesystemManager $storage
 */
$storage = Storage::disk('whatever')
$storage->path('');
```

Dark sorcery? In a way it is.
- `$storage` is an instance `Illuminate\Filesystem\FilesystemManager`
- it uses macro Trait to generate the `static public function path()` via magic method __callStatic
- Still this does neither explain how `path()` obtained, neither how it is called via `$storage->path()`.
- Another source that roses more questions from own [Laravel Documentor](https://laravel.com/api/10.x/Illuminate/Support/Facades/Storage.html#method_disk)

Plenty more digging has to be performed

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

### Artisan Digging

Just scratched the surface.

> Not a pretty fan of `tinker`. Still tinker's best command is `wtf`

As a tool, provides a fast way to see what is within each model per defined Relational or NoSQL storage medium.
In some cases it might accept changes per Eloquent model and displays them on the spot.
But 8/10 you need to restart so the changes take effect.

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

### Eloquent

A heavy duty memory draining modeling system.
Unless, you run Eloquent model instance structuring through memcaching, any chance of getting at least 10 relational records will most probably timeout.

> Probably this is why they say either the `Laravel` or `Eloquent` way.

Honestly... give me a break.

Another shady aspect of `Eloquent` is it's inconcistencies on manipulating records via its own instance of Collection Iterator.
You have to keep a cheat-sheet at your side to remember when the same method will
- require or not arguments and if it does will they be accepted or ignored
- return a certain data type, an instance or a Collection

The latter inconsistency is time consuming.

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

#### Relations

Tested one-to-many and many-to-many relations.
I haven't tested polymorphism, as there was no need.

> **Note**: Polymorphism opposes the normalization concepts of relational database design.
>           If you are working with relational databases, you **MAY** consider `ssid` structures, i.e. fetch the data, cast it when and if needed without the overhead of implementing new classes, methods and discovery bootstraps!

One-to-many is pretty straightforward and easy to implement.
The tricky bit stars with a many-to-many relation, which, has to be defined in reverse order via `Illuminate\Database\Eloquent\Relations\BelongsToMany`.

This is some unnecessary _time consuming_ complexity, just to understand the concept.

In comparison, other frameworks, have a more straightforward approach on models and relations.

Moreover, the schema used has the following tables and relations

- `remote_feeds`
  - one-to-many `pornstars`
  - one-to-many `thumbnails`
- `pornstars`
  - many-to-many `thumbnails` via pivot `pornstars-thumbnails`
- `thumbnails`
  - many-to-many `pornstars` via pivot `pornstars-thumbnails`
- `pornstars_thumbnails` _pivot_
- `downloaded_files`
  - one-to-many `remote_feeds`
  - one-to-many `thumbnails`

What the framework cannot do with all those build in classes is to get the following relation **WITHOUT** eagerly loading it.

```
`pornstars` 
  -> via pivot `pornstars_thumbnails`
  -> via `thumbnails`
  -> `downloaded_files`
```

You **MAY** create an attribute for this purpose with couple of options
- memory consuming calls from existing relations and possible map iterations (since it is a one-to-many)
- eagerly load the content, after calling invoking a lazy loaded relation.

Or, you **MAY** create a relational database `view` via migrations, but oops, no such thing in `Illuminate\Database\Schema\Blueprint`.

If chose the latter, you will have to;
- `hardcode` the view,
- capture as many possible supported cases per database engines used 
- try to prevent inconsistences when calling the model

> **Comment**:  Such a headache!

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions) - [Eloquent](#eloquent)

-----

#### Upserts

Way the best way to reduce time for inserting content.

Anything converted to Eloquent is an _overkill_ especially when eagerly loaded, has relations and is not cached.

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions) - [Eloquent](#eloquent)

-----

#### Transactions

Love the part with the closures and repeat via `DB::transaction()`, but there is no error handling.

Only try/catch cases with `DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions) - [Eloquent](#eloquent)

-----

#### Incosistencies

You have to remember when the content is an object, when it is an array, when it is Eloquent Collection and when Support Collection.

Thus, you have to have `artisan tinker` for testing all the time, plus, restart it with every change.

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions) - [Eloquent](#eloquent)

-----

### Debugging

Another shady aspect of the framework.

I.e. I got the error `Target class [config] does not exist`.
Somewhere along the way there is
- a class missing?
- a configuration parameter missing?
- an error during runtime compilation within where?
- in which configuration this `whatever` thingy missing?
- Oh it is a "macro"... we need to reduce the explanation within the "macros"

Reminds me of [Iznogood](https://en.wikipedia.org/wiki/Iznogoud)

Even Clojure, which honestly has the worst possible to read backtrace doesn't omit a thing.
At least it points and explains what had happened.

The only solution is to keep `laravel.log` file open all the time.
Even so, you might never get the exact error, only `Target class [config] does not exist`

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

### Queues, Events, Listeners

After examining and experimenting with `Jobs` and`Listeners` the differences are;
- Two entirely different concepts merged and presented together within the documentation.
- `Listeners` need and require `Event` instances, `Jobs` do not.

Also experimented and utilized Chain and Batch `Jobs`;
- You cannot use `Bus::chain()` and `Bus::batch()` on `Listeners`.
  But you can emulate a chain by dispatching new `Jobs` (or batch with multiple dispatches)
- The payload is one and the same until a job finishes ... more dots... and more dots ...
  If by any chance your payload should progress to something else tough luck, you shouldn't use chains, neither batches.
  Why?
  You get more overhead produced serializing all Jobs rather than instantly handling them on the spot.
  Do so if you have no other option, but keep in mind that the system will require resources, either way.

Reduced payload will do the difference.
It's one of those few cases where Documentation explicitely states it.
The problem is that you need to figure it out yourself the hard way.

> **Note**: `artisan queue` is not for production. Good for testing and this is all about it.

Now about timeout cases and racing conditions on resources per `Job` or `Listener` a multitude of jobs is on my Todo list. Most probably a knapsack problem with additional prediction algos.

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

### Websockets

This is on my TODO list

-----

Back to [Home](#guide) - [Contents](#contents) - [Conclusions](#conclusions)

-----

## Epilogue

Like all systems you need some time to discover them.
Ideally, discovery is faster with the proper documentation.
This framework will only provide documentation if you are willing to pay for it.

Moreover, as a framework it can do a lot in a sort time.
It provides ready environment build via docker (only tested it there)

Major concerns when using the framewor are;
- memory consumption, especially through it's MVC part, ok I'll name it `Eloquent`, 
- various tools usability inconsistencies which arily relate to flexibility.
- Magic Methods without proper documentation
- Lack of proper explanation during `Throwables`

