import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { getIntegratedBlogPosts } from "@/lib/blog-api";

export const metadata: Metadata = {
  title: "บทความ | 34 Build Master Construction",
  description:
    "บทความความรู้เรื่องออกแบบบ้าน รีโนเวท สร้างบ้าน และบิวท์อิน จาก 34 Build Master Construction",
};

export default async function BlogPage() {
  const posts = await getIntegratedBlogPosts();
  const featuredPost = posts[0];
  const otherPosts = posts.slice(1, 4);

  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <SiteHeader />
      <PageHero title="บทความ" currentLabel="บทความ" />

      <section className="relative overflow-hidden bg-[#fffaf0] px-5 py-20 lg:px-8">
        <div className="absolute inset-0 pointer-events-none bg-[radial-gradient(circle_at_15%_18%,rgba(246,217,123,0.22),transparent_28%),linear-gradient(90deg,rgba(170,116,38,0.05)_1px,transparent_1px)] bg-[length:auto,88px_88px]" />
        <div className="relative mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
          <Link href={`/blog/${featuredPost.slug}`} className="group relative min-h-[430px] overflow-hidden rounded-[2rem] border border-[#aa7426]/24 bg-[#112416] shadow-[0_32px_100px_rgba(17,36,22,0.16)]">
            <Image
              src={featuredPost.image}
              alt={featuredPost.coverAlt}
              fill
              sizes="(min-width: 1024px) 50vw, 100vw"
              className="object-cover opacity-78 transition duration-700 group-hover:scale-105"
            />
            <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,57,32,0.08),rgba(5,57,32,0.88))]" />
            <div className="absolute inset-x-0 bottom-0 p-7 text-white md:p-9">
              <span className="rounded-full bg-[#f6d97b] px-4 py-2 text-sm font-extrabold uppercase tracking-[0.18em] text-[#112416]">
                Featured
              </span>
              <h2 className="mt-5 text-4xl font-extrabold leading-tight md:text-5xl">{featuredPost.title}</h2>
              <p className="mt-4 max-w-2xl text-xl leading-8 text-white/72">{featuredPost.excerpt}</p>
            </div>
          </Link>

          <div className="grid gap-5">
            {otherPosts.map((post) => (
              <Link key={post.slug} href={`/blog/${post.slug}`} className="group grid gap-5 rounded-[1.7rem] border border-[#aa7426]/24 bg-white/82 p-5 shadow-[0_24px_80px_rgba(17,36,22,0.08)] transition duration-300 hover:-translate-y-1 hover:border-[#aa7426]/55 md:grid-cols-[170px_1fr]">
                <div className="relative min-h-[170px] overflow-hidden rounded-[1.25rem] bg-[#112416]">
                  <Image
                    src={post.image}
                    alt={post.coverAlt}
                    fill
                    sizes="170px"
                    className="object-cover opacity-82 transition duration-700 group-hover:scale-105"
                  />
                </div>
                <div className="flex flex-col justify-center">
                  <p className="text-sm font-extrabold uppercase tracking-[0.18em] text-[#aa7426]">{post.category}</p>
                  <h2 className="mt-3 text-3xl font-extrabold leading-tight text-[#053920]">{post.title}</h2>
                  <p className="mt-3 text-lg leading-8 text-[#4d5b50]">{post.excerpt}</p>
                  <p className="mt-4 text-base font-bold text-[#aa7426]">{post.date} · อ่าน {post.readTime}</p>
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
              <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">บทความทั้งหมด</h2>
            </div>
            <Link href="/contact" className="gold-button inline-flex min-h-12 items-center justify-center px-7 font-extrabold text-[#112416]">
              ปรึกษาโปรเจกต์ของคุณ
            </Link>
          </div>

          <div className="grid gap-6 md:grid-cols-3">
            {posts.map((post) => (
              <article key={post.slug} className="luxe-card overflow-hidden p-0">
                <Link href={`/blog/${post.slug}`} className="group block">
                  <div className="relative min-h-[230px] overflow-hidden bg-[#112416]">
                    <Image
                      src={post.image}
                      alt={post.coverAlt}
                      fill
                      sizes="(min-width: 768px) 33vw, 100vw"
                      className="object-cover opacity-82 transition duration-700 group-hover:scale-105"
                    />
                  </div>
                  <div className="p-6">
                    <p className="text-sm font-extrabold uppercase tracking-[0.18em] text-[#aa7426]">{post.category}</p>
                    <h3 className="mt-3 text-3xl font-extrabold leading-tight text-[#053920]">{post.title}</h3>
                    <p className="mt-3 text-lg leading-8 text-[#4d5b50]">{post.excerpt}</p>
                    <span className="mt-5 inline-flex font-extrabold text-[#aa7426]">อ่านบทความ</span>
                  </div>
                </Link>
              </article>
            ))}
          </div>
        </div>
      </section>

      <ContactBand />
      <SiteFooter />
    </main>
  );
}
