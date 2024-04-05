# PrestaShop MyParcel Module [BETA]

[![Latest stable release](https://img.shields.io/github/v/release/myparcelnl/prestashop?labelColor=white&label=Latest%20release)](https://github.com/myparcelnl/prestashop/releases/latest)
[![Latest beta release](https://img.shields.io/github/v/release/myparcelnl/prestashop?filter=*-beta.*)](https://myparcelnl.github.io/github-release-linker?repo=myparcelnl/prestashop&tag=beta)
![Supported PrestaShop Version](https://img.shields.io/badge/Prestashop-%3E%3D1.7.8.0-gray?labelColor=DF0067&logo=prestashop)
![Supported PHP Version](https://img.shields.io/badge/PHP-%3E=7.4-B0B3D6?labelColor=white&logo=php)

## ⚠️ Preface️ ⚠️

This is the `beta` branch, which contains the source code of the upcoming MyParcel PrestaShop v4.0.0 release. We've rewritten the entire module from scratch, using the [MyParcel Plugin Development Kit]. This module supports PrestaShop 8 and Php 7.4 through 8.2 (and onwards). See the [pinned issue] for more information on the changes.

> To view the readme and source code of the current stable version of the module, please switch to the [`main` branch](https://github.com/myparcelnl/prestashop/tree/main).

For a less bug-prone experience, we recommend you use the stable or release candidate versions of the module instead. You can find the stable and release candidates in the [releases] section of this repository. The release candidates are versioned with a `-rc` suffix.

If you do choose to install this version, we would love to hear your feedback. Please report any issues you encounter using the [Bug report for v4.0.0-beta.x form] or by sending an email to [support@myparcel.nl]. Use in production is at your own risk.

## Introduction

This module allows you to seamlessly integrate [the MyParcel services] into your PrestaShop webshop.

## Requirements

- PrestaShop 1.7.8.0 or higher (including 8.0 and up)
- PHP 7.4 or higher

For the manual and more information, check out our [PrestaShop module guide] on the [MyParcel Developer Portal]. Do note this is the manual for the stable version of the module. We are working on a new manual for the v4.0.0 release.

> :warning: Older 1.7.x versions may work, but are not officially tested or supported.

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
