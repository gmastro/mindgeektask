# Readme

Downloads and processes content from [https://pornhub.com](https://pornhub.com). That means **adult content, nudity**

Content selection was not deliberate, it was part of a task.

As an implementation challenge, it floods the source with enough requests for retrieving content, locally processing it and finally displaying it.

> **Note**: This is a **Sample** Work-In-Progress (WIP)

## Contents

- [Readme](#readme)
  - [Contents](#contents)
  - [Environment](#environment)
    - [Feed Mechanism](#feed-mechanism)
  - [Installation](#installation)
  - [Start](#start)
    - [Scheduler](#scheduler)
  - [Stop](#stop)
  - [Disclaimer](#disclaimer)

## Environment

Uses Laravel framework with;
- completed `chirper` tutorial implementation
- [Feed Mechanism](#feed-mechanism)

For the frontend uses;
- `React`

For the backend uses;
- `MySQL` (including testing, batch-bus jobs logs) located under schema `chirper`,
- `Redis` (queues, image caching)

-----
Go to: [Top](#readme) - [Contents](#contents)

-----
### Feed Mechanism

Database wise; all feed contents are located in schema `chirper.remote_feeds`

Rationalizing feeds process;
- Holds a list of remote file sources (a.k.a. `source(s)`), one per feed.
- Each `source` is a background job, which, is initialized via the scheduler (_TODO_) or forcefully dispatched via `http://localhost/dashboard` **Force Job** button
- Once a job is initialized, it contains in a chain the following actions;
  - Sends a CURL header request to examine when the `source` last updated
  - Downloads and stores locally the `source` -only and if is newer
  - **MAY** process downloaded file

If any of the chained actions fails, i.e. `source` returns status code 404, the job will terminate.

Apart from examining and downloading a `source` a list of `handles` is used for further processing.

-----
Go to: [Top](#readme) - [Contents](#contents) - [Environment](#environment)

-----
## Installation

> **Prerequisites**:    Your system **SHOULD** have 
> - [Git](https://git-scm.com/) 
> - [Docker](https://www.docker.com/) and 
> - [Composer](https://getcomposer.org/)

On the following lines replace `{yourdirectory}` with the installation path

```bash
$ git clone git@github.com:gmastro/mindgeektask.git {yourdirectory} \
  && cd {yourdirectory} \
  && composer update \
  && cp .env.example .env \
  && ./vendor/bin/sail key:generate \
  && ./vendor/bin/sail up -d --build \
  && ./vendor/bin/sail artisan storage:link \
  && ./vendor/bin/sail artisan migrate:fresh --seed \
  && ./vendor/bin/sail npm install \
  && ./vendor/bin/sail down
```

-----
Go to: [Top](#readme) - [Contents](#contents)

-----

## Start

Start with the docker container (on the background)

```bash
$ ./vendor/bin/sail up -d \
```

Then

```bash
$ ./vendor/bin/sail npm run dev
```

Open a browser window and type `localhost` into addresses tab.

-----
Go to: [Top](#readme) - [Contents](#contents)

-----

### Scheduler

> **Note**: This part is still under development, thus it will not perform as is described. Added into this `README` for future reference

To start the scheduler

```bash
$ ./vendor/bin/sail artisan schedule:run
```

Once the scheduler is up, it will start sending requests towards all feed sources.

-----
Go to: [Top](#readme) - [Contents](#contents) - [Start](#start)

-----
## Stop

To stop `Docker`

```bash
$ ./vendor/bin/sail down
```

By stopping docker, it will forcefully terminate `scheduler`, `npm` or any other connections

To stop `npm`, press `Ctrl+C` (or the key combination that your OS supports to send a SIGTERM)

-----
Go to: [Top](#readme) - [Contents](#contents)

-----

## Disclaimer

This WIP is not meant for production.
Feel free to fork it and Buy Me A Beer!
