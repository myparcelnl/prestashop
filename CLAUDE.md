# PrestaShop Module (myparcelnl)

## Building Frontend Assets

The admin and checkout UIs are built with Vite/Vue via the js-pdk. If the admin page renders but shows empty content (empty `data-pdk-context`, no Vue components), the JS assets need to be (re)built:

```sh
yarn install
yarn translations:import
yarn build
```

- `yarn translations:import` generates `config/pdk/translations/en.json` (required by the PDK context service)
- `yarn build` compiles all JS workspaces under `views/js/` into their `dist/` directories

The js-pdk packages are linked via `portal:` resolutions in `package.json`. When using a workspace layout (like ps9-upgrade), ensure the js-pdk worktree exists at `../js-pdk` relative to this repo.

## PrestaShop JS/CSS Cache

PrestaShop bundles and caches JS/CSS into combined files (e.g. `bottom-*.js` in the theme assets). After rebuilding frontend assets, the old cached bundle will still be served until the cache is cleared:

```sh
# Inside the container:
rm -rf /var/www/html/var/cache/* /var/www/html/themes/hummingbird/assets/cache/*
```

Then hard-refresh the browser. Without this step, rebuilt JS will not take effect — PrestaShop will keep serving the stale combined bundle.
