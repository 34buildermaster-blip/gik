import type { MetadataRoute } from "next";
import { blogPosts } from "@/data/blog";
import { getIntegratedBlogPosts } from "@/lib/blog-api";
import { siteConfig } from "@/lib/site-config";

export const dynamic = "force-static";

const staticPages = [
  "",
  "/about",
  "/services",
  "/house-designs",
  "/updates",
  "/blog",
  "/faq",
  "/contact",
  "/privacy-policy",
  "/terms-of-service",
  "/cookie-policy",
];

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const now = new Date();
  const pages = staticPages.map((path) => ({
    url: `${siteConfig.siteUrl}${path}`,
    lastModified: now,
    changeFrequency: path === "" ? ("weekly" as const) : ("monthly" as const),
    priority: path === "" ? 1 : 0.8,
  }));

  const integratedPosts = await getIntegratedBlogPosts();
  const postsBySlug = new Map([
    ...blogPosts.map((post) => [post.slug, post] as const),
    ...integratedPosts.map((post) => [post.slug, post] as const),
  ]);

  const posts = Array.from(postsBySlug.values()).map((post) => ({
    url: `${siteConfig.siteUrl}/blog/${post.slug}`,
    lastModified: now,
    changeFrequency: "monthly" as const,
    priority: 0.72,
  }));

  return [...pages, ...posts] satisfies MetadataRoute.Sitemap;
}
