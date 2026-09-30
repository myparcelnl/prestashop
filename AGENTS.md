# MyParcel PrestaShop Module (myparcelnl)

The PrestaShop module for MyParcel. It is a thin adapter layer on top of the PDK: it implements the PrestaShop-specific storage, hooks, rendering and controllers.

## MyParcel stack

This repository is one part of the MyParcel plugin stack:

| Repository | Role |
| --- | --- |
| [myparcelnl/sdk](https://github.com/myparcelnl/sdk) | PHP client generated from the MyParcel API OpenAPI spec. Source of carriers, delivery types, package types and API types. |
| [myparcelnl/pdk](https://github.com/myparcelnl/pdk) | PHP Plugin Development Kit. Business logic, models, settings, migrations and API calls shared by all plugins. |
| [myparcelnl/js-pdk](https://github.com/myparcelnl/js-pdk) | JS Plugin Development Kit. Admin UI and checkout scripts that the plugins build on. |
| [myparcelnl/delivery-options](https://github.com/myparcelnl/delivery-options) | Checkout widget in which the consumer picks a delivery or pickup option. |
| [myparcelnl/woocommerce](https://github.com/myparcelnl/woocommerce) | WooCommerce plugin. Thin adapter on top of the PDK. |
| [myparcelnl/prestashop](https://github.com/myparcelnl/prestashop) **(this repository)** | PrestaShop module. Thin adapter on top of the PDK. |

How they connect:

- A plugin bootstraps the PDK and implements the platform adapters (storage, hooks, rendering, cron). Behaviour that all plugins share goes in the PDK, not in one plugin.
- The PDK renders its context as JSON in an HTML attribute (`data-pdk-context` for the admin). The js-pdk apps read it and call PDK endpoint actions (registered in the PDK's `config/actions.php`) for more data.
- The delivery options widget is not bundled into the plugins. The PDK builds jsdelivr CDN URLs for `myparcel.js`, `myparcel.lib.js` and `style.css` (the PDK's `config/pdk-dependencies.php`), and the plugin loads them at runtime. In js-pdk, the `@myparcel-dev/delivery-options` npm dependency supplies only types and constants.
- The widget and the js-pdk admin get carrier capabilities by POSTing to the PDK `proxyCapabilities` action (`PdkCapabilitiesActions`).
- Carriers, delivery types and package types come from the SDK. Do not add definitions of them to the PDK or the plugins.

For local development, `pdk-dev-on` links local checkouts of the PDK and js-pdk into a plugin. It adds a composer `path` repository to `composer.json` and yarn `portal:` links to `package.json`. Never commit these changes. Suggest it only when the task needs changes in the PDK or js-pdk, or needs to test such changes in a plugin. MyParcel employees can find the setup in [Local PDK in PDK plugin](https://myparcelnl.atlassian.net/wiki/spaces/IG/pages/319422473).

The delivery options widget has no link command. To test a local build, serve `apps/delivery-options/dist` from a delivery-options checkout and override `deliveryOptionsCdnUrlJs`, `deliveryOptionsCdnUrlJsLib` and `deliveryOptionsCdnUrlCss` in the plugin's `config/pdk.php`. Never commit these overrides. This works only locally: there is no way to give a tester a plugin build with a custom widget version.

To find the local checkouts of the other repositories, read `.claude/int-branch.json` in a parent directory. Its `repos` list holds their paths. If there is none and the task needs another repository, tell the user once that the `int-branch` skill of the `external-integrations-basekit` plugin offers to write this file on its first run.

## MyParcel employees: agent plugin

If no skills from the `external-integrations-basekit` plugin are available in this session, tell the user once, at the start, that the plugin is missing and give these commands:

```
/plugin marketplace add mypadev/ai-plugins
/plugin install external-integrations-basekit@myparcel-claude-code-marketplace
```

The marketplace is private, so only MyParcel employees can install it.

## Data storage

Order, cart and product data live in the module's own `myparcelnl_*` database tables (created by the migrations in `src/Database/`), not in PrestaShop order meta.

## Development environment

The module is developed inside [myparcelnl/docker-prestashop](https://github.com/myparcelnl/docker-prestashop). Clone this repository into its `modules/myparcelnl` folder. `docker/init-modules.sh` in docker-prestashop symlinks it into the container's `modules` folder.

## Running Tests

Tests run inside Docker using the `test` service defined in `docker-compose.yml`:

```sh
docker compose run --rm test
```

Do not run `composer test` or `vendor/bin/pest` directly on the host — the container provides the correct PHP version and environment.

## Building Frontend Assets

The admin and checkout UIs are built with Vite/Vue via the js-pdk. If the admin page renders but shows empty content (empty `data-pdk-context`, no Vue components), the JS assets need to be (re)built:

```sh
yarn install
yarn translations:import
yarn build
```

- `yarn translations:import` generates `config/pdk/translations/en.json` (required by the PDK context service)
- `yarn build` compiles all JS workspaces under `views/js/` into their `dist/` directories

## PrestaShop JS/CSS Cache

PrestaShop bundles and caches JS/CSS into combined files (e.g. `bottom-*.js` in the theme assets). After rebuilding frontend assets, the old cached bundle will still be served until the cache is cleared:

```sh
# Inside the container:
rm -rf /var/www/html/var/cache/* /var/www/html/themes/<active theme>/assets/cache/*
```

Then hard-refresh the browser. Without this step, rebuilt JS will not take effect — PrestaShop will keep serving the stale combined bundle.
