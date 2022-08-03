# Contributing

1. Clone or download the [source code]
2. If you're planning to change JavaScript or CSS code, see below section for
   details.
3. Make your changes
4. Create a pull request!

## Prerequisites

* [Docker]

## Steps

### Build and run the image

```shell
docker build . -t myparcelnl/prestashop-module
docker run --rm -v $(pwd):/app myparcelnl/prestashop-module
```

This will install Node and Composer dependencies and build the plugin. The
plugin will be available in the `dist` folder and as a zip file in the root.

### Make your changes

* Please try to conform to our existing code style.

### Test your changes

**Easiest method**

This is only sufficient if you're running WordPress locally and your source
directory is inside your `wp-content` folder. If this is not the case, continue
to the next section.

Run this after every change:

```shell
docker run --rm -v $(pwd):/app myparcelnl/prestashop-module
```

Or run this to monitor your changes and rebuild automatically:

```shell
docker run --rm -v $(pwd):/app myparcelnl/prestashop-module yarn serve
```
