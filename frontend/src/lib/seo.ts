export function stripSiteNameSuffix(title: string, siteName: string) {
  const suffix = ` | ${siteName}`;

  return title.endsWith(suffix) ? title.slice(0, -suffix.length) : title;
}
