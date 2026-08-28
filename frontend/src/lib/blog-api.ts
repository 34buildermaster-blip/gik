import { blogPosts, type BlogPost } from "@/data/blog";

type ApiBlogPost = Omit<BlogPost, "image"> & {
  image: string | null;
  contentHtml?: string | null;
  seo?: {
    title?: string | null;
    description?: string | null;
    keywords?: string | null;
  };
};

export type IntegratedBlogPost = BlogPost & {
  contentHtml?: string | null;
  seo?: ApiBlogPost["seo"];
  source?: "api" | "fallback";
};

const apiBaseUrl = (
  process.env.BACKEND_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  "http://127.0.0.1:8000"
).replace(/\/$/, "");

function normalizePost(post: ApiBlogPost): IntegratedBlogPost {
  return {
    ...post,
    image: post.image || "/bg-material-board.png",
    source: "api",
  };
}

type ApiResult<T> = {
  ok: boolean;
  status: number;
  data?: T;
};

async function fetchJson<T>(path: string): Promise<ApiResult<T> | null> {
  try {
    const response = await fetch(`${apiBaseUrl}${path}`, {
      cache: "no-store",
      headers: { Accept: "application/json" },
    });

    if (!response.ok) {
      return {
        ok: false,
        status: response.status,
      };
    }

    return {
      ok: true,
      status: response.status,
      data: (await response.json()) as T,
    };
  } catch {
    return null;
  }
}

export async function getIntegratedBlogPosts(): Promise<IntegratedBlogPost[]> {
  const result = await fetchJson<{ data: ApiBlogPost[] }>("/api/articles");

  if (result === null || !result.ok || !Array.isArray(result.data?.data)) {
    return blogPosts.map((post) => ({ ...post, source: "fallback" as const }));
  }

  return result.data.data.length
    ? result.data.data.map(normalizePost)
    : blogPosts.map((post) => ({ ...post, source: "fallback" as const }));
}

export async function getIntegratedBlogPost(slug: string): Promise<IntegratedBlogPost | null> {
  const result = await fetchJson<{ data: ApiBlogPost }>(`/api/articles/${slug}`);

  if (result?.ok && result.data?.data) {
    return normalizePost(result.data.data);
  }

  const fallback = blogPosts.find((post) => post.slug === slug);
  return fallback ? { ...fallback, source: "fallback" } : null;
}
