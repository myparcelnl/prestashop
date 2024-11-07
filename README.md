# PrestaShop MyParcel Module

[![Latest stable release](https://img.shields.io/github/v/release/myparcelnl/prestashop?labelColor=white&label=Latest%20release)](https://github.com/myparcelnl/prestashop/releases/latest)
[![Latest release candidate release](https://img.shields.io/github/v/release/myparcelnl/prestashop?filter=*-rc.*)](https://myparcelnl.github.io/github-release-linker?repo=myparcelnl/prestashop&tag=beta)
![Supported PrestaShop Version](https://img.shields.io/badge/Prestashop-%3E%3D1.7.8.0-gray?labelColor=DF0067&logo=prestashop)
![Supported PHP Version](https://img.shields.io/badge/PHP-%3E=7.4-B0B3D6?labelColor=white&logo=php)

## Introduction

This module allows you to seamlessly integrate [the MyParcel services] into your PrestaShop webshop.

## Requirements

- PrestaShop 1.7.8.0 or higher, including 8.0 and up
- PHP 7.4 or higher

> :warning: Older 1.7.x versions may work, but are not officially tested or supported.

## Manual

For the manual and more information, check out our [PrestaShop module guide] on the [MyParcel Developer Portal].

### Upgrading to v4.x

When upgrading to v4.x it may be necessary to first clear the cache of Prestashop. To do so, go to `Advanced Parameters` -> `Performance` and click on `Clear cache`, then upload the plugin as normal.

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md).

## Versions

The 1.7 module was initially considered a completely standalone module from the old 1.6 version, and released as version 1.0. This has caused some confusion among users as well as our developers and support team. This will also be able to cause issues in the future when we release new versions that include migrations based on the previously installed version of the module.

To avoid this, we've decided to update the versioning of the module. The new versioning is as follows:

| PrestaShop version | Module version | New module version |
| ------------------ | -------------- | ------------------ |
| 1.6                | 1.x            | 1.x (unchanged)    |
| 1.6                | 2.x            | 2.x (unchanged)    |
| 1.7                | 1.x            | 3.x                |
| 1.7.x & 8.x        | 2.x            | 4.x                |

We chose to only update [the changelog](./CHANGELOG.md), the repository tags and not touch anything else, to avoid any potential issues with the module's users and further confusion. We've also merged the history of the old 1.6 module into this repository. This way, anyone interested can see the full history of the module in one place. We've been using semantic version since version 3.2.0 (previously 1.2.0) and we will continue to do so, just with the updated versioning.

[Bug report for v4.0.0-beta.x form]: https://github.com/myparcelnl/prestashop/issues/new?labels=pdk&template=ZZ-BUG-REPORT-NEW.yml
[MyParcel Developer Portal]: https://developer.myparcel.nl
[MyParcel Plugin Development Kit]: https://developer.myparcel.nl/documentation/52.pdk/
[PrestaShop module guide]: https://developer.myparcel.nl/nl/documentatie/11.prestashop.html
[pinned issue]: https://github.com/myparcelnl/prestashop/issues/226
[releases]: https://github.com/myparcelnl/prestashop/releases
[support@myparcel.nl]: mailto:support@myparcel.nl
[the MyParcel services]: https://www.myparcel.nl/en/
