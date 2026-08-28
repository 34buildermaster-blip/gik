export type WelcomePopupData = {
  id: number;
  desktopImage: string;
  mobileImage?: string | null;
  alt: string;
  linkUrl?: string | null;
  updatedAt?: string | null;
};

export async function fetchWelcomePopup(signal?: AbortSignal): Promise<WelcomePopupData | null> {
  const configuredApiUrl = process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, "");
  const isStaticPreview = Boolean(process.env.NEXT_PUBLIC_BASE_PATH);

  if (!configuredApiUrl && isStaticPreview) return null;

  const endpoint = configuredApiUrl
    ? `${configuredApiUrl}/api/welcome-popup`
    : "/api/welcome-popup";

  try {
    const response = await fetch(endpoint, {
      headers: { Accept: "application/json" },
      cache: "no-store",
      signal,
    });
    if (!response.ok) return null;

    const payload = (await response.json()) as { data?: WelcomePopupData | null };
    if (!payload.data?.desktopImage) return null;

    const apiBase = configuredApiUrl ?? window.location.origin;
    const normalizeImageUrl = (value?: string | null) => {
      if (!value) return value;
      if (value.startsWith("/")) return `${apiBase}${value}`;

      try {
        const url = new URL(value);
        if (isLocalDevelopment && ["localhost", "127.0.0.1"].includes(url.hostname) && !url.port) {
          return `${apiBase}${url.pathname}${url.search}`;
        }
      } catch {
        return value;
      }

      return value;
    };

    return {
      ...payload.data,
      desktopImage: normalizeImageUrl(payload.data.desktopImage) ?? payload.data.desktopImage,
      mobileImage: normalizeImageUrl(payload.data.mobileImage),
    };
  } catch {
    return null;
  }
}
