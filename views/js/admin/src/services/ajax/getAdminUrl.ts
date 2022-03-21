/**
 * Get the admin url with a path.
 */
export function getAdminUrl(path: string): string {
  return window.MyParcelActions.adminUrl.replace(/\/$/, '') + path;
}
