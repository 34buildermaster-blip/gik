import { blogPosts, type BlogPost } from "@/data/blog";

type ApiBlogPost = Omit<BlogPost, "image"> & {
  image: string | null;
  seo?: {
    title?: string | null;
    description?: string | null;
    keywords?: string | null;
  };
};

export type IntegratedBlogPost = BlogPost & {
  seo?: ApiBlogPost["seo"];
  source?: "api" | "fallback";
};

const apiBaseUrl = (process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000").replace(/\/$/, "");

function normalizePost(post: ApiBlogPost): IntegratedBlogPost {
  return {
    ...post,
    image: post.image || "/bg-material-board.png",
    source: "api",
  };
}

async function fetchJson<T>(path: string): Promise<T | null> {
  try {
    const response = await fetch(`${apiBaseUrl}${path}`, {
      next: { revalidate: 60 },
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

export async function getIntegratedBlogPosts(): Promise<IntegratedBlogPost[]> {
  const payload = await fetchJson<{ data: ApiBlogPost[] }>("/api/articles");

  if (!payload?.data?.length) {
    return blogPosts.map((post) => ({ ...post, source: "fallback" }));
  }

  return payload.data.map(normalizePost);
}

export async function getIntegratedBlogPost(slug: string): Promise<IntegratedBlogPost | null> {
  const payload = await fetchJson<{ data: ApiBlogPost }>(`/api/articles/${slug}`);

  if (payload?.data) {
    return normalizePost(payload.data);
  }

  const fallback = blogPosts.find((post) => post.slug === slug);
  return fallback ? { ...fallback, source: "fallback" } : null;
}
