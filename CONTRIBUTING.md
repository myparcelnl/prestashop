# Contributing

## Prerequisites

- [Docker]
- [Volta] or [Node]

> If you don't want to use Volta, make sure to use the Node version in the `volta.node` key in [./package.json].

## Steps

### Install dependencies

Install composer dependencies with Docker:

```shell
docker compose up --rm -it php
```

Install yarn dependencies:

```shell
yarn
```

### Build the module

```shell
yarn build
```

This will build the module and output a version for each platform to the `dist` folder.

### Make your changes

Follow our [Developer Guide for contributing to MyParcel repositories].

### Test your changes

#### Easiest method

This is only sufficient if you're running PrestaShop locally and your source directory is inside your `modules` folder. If this is not the case, continue to [the next section](#using-a-remote-prestashop-instance).

Run this after every change:

```shell
yarn build
```

Or run this to monitor your changes and rebuild automatically:

```shell
yarn watch
```

#### Using a remote PrestaShop instance

Build module files:

```shell
yarn build
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
[volta]: https://volta.sh/
[node]: https://nodejs.org/
