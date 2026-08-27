import { assetPath } from "@/lib/asset-path";

export type HomeSlide = {
  id: number | string;
  image: string;
  alt: string;
  eyebrow?: string | null;
  title: string;
  titleLine2?: string | null;
  description?: string | null;
  label?: string | null;
};

export type HomeSlidesPayload = {
  hero: HomeSlide[];
  approach: HomeSlide[];
};

let homeSlidesRequest: Promise<HomeSlidesPayload | null> | null = null;

export function homeSlideImage(path: string) {
  if (/^(https?:)?\/\//i.test(path) || path.startsWith("data:")) {
    return path;
  }

  return assetPath(path);
}

async function loadHomeSlides(): Promise<HomeSlidesPayload | null> {
  const configuredApiUrl = process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, "");
  const isLocalDevelopment =
    typeof window !== "undefined" &&
    ["127.0.0.1", "localhost"].includes(window.location.hostname);

  if (!configuredApiUrl && !isLocalDevelopment) {
    return null;
  }

  const endpoint = configuredApiUrl
    ? `${configuredApiUrl}/api/home-slides`
    : "/backend-api/home-slides";

  try {
    const response = await fetch(endpoint, {
      headers: { Accept: "application/json" },
    });

    if (!response.ok) return null;

    const payload = (await response.json()) as { data?: Partial<HomeSlidesPayload> };

    return {
      hero: Array.isArray(payload.data?.hero) ? payload.data.hero : [],
      approach: Array.isArray(payload.data?.approach) ? payload.data.approach : [],
    };
  } catch {
    return null;
  }
}

export function fetchHomeSlides(signal?: AbortSignal): Promise<HomeSlidesPayload | null> {
  homeSlidesRequest ??= loadHomeSlides();

  if (!signal) return homeSlidesRequest;
  if (signal.aborted) return Promise.resolve(null);

  return new Promise((resolve) => {
    const handleAbort = () => resolve(null);

    signal.addEventListener("abort", handleAbort, { once: true });
    homeSlidesRequest?.then((payload) => {
      signal.removeEventListener("abort", handleAbort);
      if (!signal.aborted) resolve(payload);
    });
  });
}
