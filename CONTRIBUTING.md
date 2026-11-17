# Contributing

## Prerequisites

- [Docker]
- [Node] 24, version 24.21 or later
- [pnpm] 12, version 12.11 or later
- No Volta on your machine. Its `yarn` does not go through corepack, so the `packageManager` field in `package.json` does not stop it.

## Steps

### Install dependencies

Install composer dependencies with Docker:

```shell
docker compose up php
```

Install pnpm dependencies:

```shell
pnpm install
```

### Build the module

```shell
pnpm build
```

This will build the module and output a version for each platform to the `dist` folder.

### Make your changes

Follow our [Developer Guide for contributing to MyParcel repositories].

### Test your changes

#### Automated tests

You should always run the automated tests.

To run the frontend tests:

```shell
pnpm test:run
```

The JavaScript packages in `views/js/` share the nx targets `build`, `build:dev`, `test`, `test:coverage` and `test:run`. The command of each target is in `targetDefaults` in `nx.json`. A package lists the targets it uses under `nx.targets` in its `package.json`. To run a target for one package:

```shell
pnpm nx test:run @myparcel-prestashop/frontend-checkout
```

To run the PHP tests:

```shell
docker compose run php composer test
```

These will also be run automatically on GitHub when you create a pull request and must pass before your changes can be merged.

#### Manual testing

##### Using a local PrestaShop instance

This is only sufficient if you're running PrestaShop locally and your source directory is inside your `modules` folder. If this is not the case, continue to [the next section](#using-a-remote-prestashop-instance).

Run this after every change:

```shell
pnpm build
```

Or run this to monitor your changes and rebuild automatically:

```shell
pnpm watch
```

##### Using a remote PrestaShop instance

Build module files:

```shell
pnpm build
```

The folder structure should look like this:

```
dist
├── myparcelbe
└── myparcelnl
```

Now zip the module folder you want to use, then upload the zip file on the modules page of your PrestaShop website to install it.

You can also upload the module folder manually using FTP.

[Developer Guide for contributing to MyParcel repositories ]: https://github.com/myparcelnl/developer/blob/main/DEVELOPERS.md#developer-guide-for-contributing-to-myparcel-repositories
[conventional commits]: https://www.conventionalcommits.org/
[docker]: https://www.docker.com/
[eslint]: https://eslint.org/
[pnpm]: https://pnpm.io/
[node]: https://nodejs.org/
