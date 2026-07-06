const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "";

export function assetPath(path: string) {
  if (!path.startsWith("/")) {
    return `${basePath}/${path}`;
  }

  return `${basePath}${path}`;
}

export function sitePath(path: string) {
  if (
    path.startsWith("http") ||
    path.startsWith("mailto:") ||
    path.startsWith("tel:") ||
    path.startsWith("#")
  ) {
    return path;
  }

  if (path === "/") {
    return basePath || "/";
  }

  if (path.startsWith("/")) {
    return `${basePath}${path}`;
  }

  return `${basePath}/${path}`;
}
