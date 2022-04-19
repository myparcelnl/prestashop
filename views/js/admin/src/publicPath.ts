/**
 * Because sites can be hosted in any nested amount of subfolders on / as well as in the root itself, we can't simply
 * use a static publicPath configuration in vue.config.js. Using the following variable we can define it dynamically.
 *
 * @see https://webpack.js.org/guides/public-path/#on-the-fly
 */

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
__webpack_public_path__ = `${window.MyParcelConfiguration.modulePathUri}views/dist/js/admin/`;
