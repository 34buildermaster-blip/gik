"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import type { IntegratedBlogPost } from "@/lib/blog-api";

type BlogListProps = {
  initialPosts: IntegratedBlogPost[];
};

type ApiBlogPost = Omit<IntegratedBlogPost, "image" | "source"> & {
  image: string | null;
};

const apiBaseUrl = (process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000").replace(/\/$/, "");

function normalizeApiPost(post: ApiBlogPost): IntegratedBlogPost {
  return {
    ...post,
    image: post.image || "/bg-material-board.png",
    source: "api",
  };
}

export function BlogList({ initialPosts }: BlogListProps) {
  const [posts, setPosts] = useState(initialPosts);
  const featuredPost = posts[0];
  const otherPosts = posts.slice(1, 4);

  useEffect(() => {
    let isMounted = true;

    async function syncArticles() {
      try {
        const response = await fetch(`${apiBaseUrl}/api/articles`, {
          headers: { Accept: "application/json" },
        });

        if (!response.ok) {
          return;
        }

        const payload = (await response.json()) as { data?: ApiBlogPost[] };

        if (isMounted && Array.isArray(payload.data) && payload.data.length > 0) {
          setPosts(payload.data.map(normalizeApiPost));
        }
      } catch {
        // Keep the prerendered fallback when the backend is not reachable.
      }
    }

    syncArticles();

    return () => {
      isMounted = false;
    };
  }, []);

  if (!featuredPost) {
    return (
      <section className="relative overflow-hidden bg-white px-5 py-24 lg:px-8">
        <div className="relative mx-auto max-w-4xl rounded-lg border border-[#dfe4e0] bg-white p-8 text-center shadow-[0_20px_70px_rgba(18,34,25,0.07)] md:p-12">
          <p className="section-kicker">Articles</p>
          <h2 className="mt-3 text-3xl font-semibold leading-tight text-[#17211c] md:text-4xl">ยังไม่มีบทความเผยแพร่</h2>
          <p className="mx-auto mt-4 max-w-2xl text-lg leading-8 text-[#667169]">
            เมื่อเพิ่มบทความจากหลังบ้านและตั้งสถานะเป็นเผยแพร่ บทความจะแสดงบนหน้านี้โดยอัตโนมัติ
          </p>
          <Link href="/contact" className="gold-button mt-8 inline-flex min-h-12 items-center justify-center rounded-full px-7 font-semibold text-white">
            ปรึกษาโปรเจกต์ของคุณ
          </Link>
        </div>
      </section>
    );
  }

  return (
    <>
      <section className="relative overflow-hidden bg-white px-5 py-20 lg:px-8">
        <div className="relative mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
          <Link href={`/blog/${featuredPost.slug}`} className="group relative min-h-[430px] overflow-hidden rounded-lg bg-[#173427] shadow-[0_24px_70px_rgba(18,34,25,0.12)]">
            <Image
              src={featuredPost.image}
              alt={featuredPost.coverAlt}
              fill
              sizes="(min-width: 1024px) 50vw, 100vw"
              className="object-cover opacity-78 transition duration-700 group-hover:scale-105"
            />
            <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(10,34,23,0.03),rgba(10,34,23,0.88))]" />
            <div className="absolute inset-x-0 bottom-0 p-7 text-white md:p-9">
              <span className="rounded-full bg-white px-4 py-2 text-sm font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">
                Featured
              </span>
              <h2 className="mt-4 text-3xl font-semibold leading-tight md:text-4xl">{featuredPost.title}</h2>
              <p className="mt-3 max-w-2xl text-lg leading-8 text-white/72">{featuredPost.excerpt}</p>
            </div>
          </Link>

          <div className="grid gap-5">
            {otherPosts.map((post) => (
              <Link key={post.slug} href={`/blog/${post.slug}`} className="group grid gap-5 rounded-lg border border-[#dfe4e0] bg-white p-5 transition duration-300 hover:-translate-y-1 hover:border-[#0f6b45] hover:shadow-[0_18px_55px_rgba(18,34,25,0.08)] md:grid-cols-[170px_1fr]">
                <div className="relative min-h-[170px] overflow-hidden rounded-md bg-[#e8ece9]">
                  <Image
                    src={post.image}
                    alt={post.coverAlt}
                    fill
                    sizes="170px"
                    className="object-cover opacity-82 transition duration-700 group-hover:scale-105"
                  />
                </div>
                <div className="flex flex-col justify-center">
                  <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">{post.category}</p>
                  <h2 className="mt-3 text-2xl font-semibold leading-tight text-[#17211c]">{post.title}</h2>
                  <p className="mt-3 text-base leading-7 text-[#667169]">{post.excerpt}</p>
                  <p className="mt-4 text-base font-medium text-[#0f6b45]">{post.date} · อ่าน {post.readTime}</p>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-material-section px-5 py-20 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="mb-10 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
              <p className="section-kicker">Latest Articles</p>
              <h2 className="mt-3 text-3xl font-semibold leading-tight text-[#17211c] sm:text-4xl">บทความทั้งหมด</h2>
            </div>
            <Link href="/contact" className="gold-button inline-flex min-h-12 items-center justify-center rounded-full px-7 font-semibold text-white">
              ปรึกษาโปรเจกต์ของคุณ
            </Link>
          </div>

          <div className="grid gap-6 md:grid-cols-3">
            {posts.map((post) => (
              <article key={post.slug} className="luxe-card overflow-hidden rounded-lg p-0">
                <Link href={`/blog/${post.slug}`} className="group block">
                  <div className="relative min-h-[230px] overflow-hidden bg-[#e8ece9]">
                    <Image
                      src={post.image}
                      alt={post.coverAlt}
                      fill
                      sizes="(min-width: 768px) 33vw, 100vw"
                      className="object-cover opacity-82 transition duration-700 group-hover:scale-105"
                    />
                  </div>
                  <div className="p-6">
                    <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">{post.category}</p>
                    <h3 className="mt-3 text-2xl font-semibold leading-tight text-[#17211c]">{post.title}</h3>
                    <p className="mt-3 text-base leading-7 text-[#667169]">{post.excerpt}</p>
                    <span className="mt-5 inline-flex font-semibold text-[#0f6b45]">อ่านบทความ</span>
                  </div>
                </Link>
              </article>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
