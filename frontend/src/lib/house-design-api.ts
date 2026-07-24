import { assetPath } from "@/lib/asset-path";
import { fallbackHouseDesigns, type HouseDesign } from "@/data/house-designs";

type ApiHouseDesign = Omit<HouseDesign, "coverImage" | "gallery" | "concept" | "features"> & {
  coverImage: string | null;
  concept?: string | null;
  features?: string[];
  gallery?: HouseDesign["gallery"];
};

const apiBaseUrl = (process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000").replace(/\/$/, "");

function mediaUrl(value: string | null | undefined) {
  if (!value) {
    return assetPath("/approach-homes/modern.jpg");
  }

  return /^https?:\/\//.test(value) ? value : assetPath(value);
}

function normalizeDesign(design: ApiHouseDesign): HouseDesign {
  return {
    ...design,
    coverImage: mediaUrl(design.coverImage),
    concept: design.concept || "",
    features: Array.isArray(design.features) ? design.features : [],
    gallery: Array.isArray(design.gallery)
      ? design.gallery.map((image) => ({ ...image, image: mediaUrl(image.image) }))
      : [],
    source: "api",
  };
}

function normalizeFallback(design: HouseDesign): HouseDesign {
  return {
    ...design,
    coverImage: mediaUrl(design.coverImage),
    gallery: design.gallery.map((image) => ({ ...image, image: mediaUrl(image.image) })),
  };
}

async function fetchJson<T>(path: string): Promise<T | null> {
  try {
    const response = await fetch(`${apiBaseUrl}${path}`, {
      cache: "no-store",
      headers: { Accept: "application/json" },
    });

    if (!response.ok) {
      return null;
    }

    return (await response.json()) as T;
  } catch {
    return null;
  }
}

export async function getIntegratedHouseDesigns(): Promise<HouseDesign[]> {
  const payload = await fetchJson<{ data?: ApiHouseDesign[] }>("/api/house-designs");

  if (!Array.isArray(payload?.data) || payload.data.length === 0) {
    return fallbackHouseDesigns.map(normalizeFallback);
  }

  return payload.data.map(normalizeDesign);
}

export async function getIntegratedHouseDesign(slug: string): Promise<HouseDesign | null> {
  const payload = await fetchJson<{ data?: ApiHouseDesign }>(`/api/house-designs/${encodeURIComponent(slug)}`);

  if (payload?.data) {
    return normalizeDesign(payload.data);
  }

  const fallback = fallbackHouseDesigns.find((design) => design.slug === slug);
  return fallback ? normalizeFallback(fallback) : null;
}
