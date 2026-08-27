export function getPublicApiBaseUrl() {
  const configured = process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, "");

  if (configured) {
    return configured;
  }

  if (typeof window === "undefined") {
    return "http://127.0.0.1:8000";
  }

  const { hostname, origin, protocol } = window.location;
  const isLocal = hostname === "localhost" || hostname === "127.0.0.1" || /^192\.168\./.test(hostname);

  return isLocal ? `${protocol}//${hostname}:8000` : origin;
}
