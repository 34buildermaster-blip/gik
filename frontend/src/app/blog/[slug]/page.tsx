import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { blogPosts } from "@/data/blog";
import { getIntegratedBlogPost, getIntegratedBlogPosts } from "@/lib/blog-api";
import { siteConfig } from "@/lib/site-config";

type BlogDetailProps = {
  params: Promise<{
    slug: string;
  }>;
};

function absoluteAssetUrl(src: string) {
  if (/^https?:\/\//.test(src)) {
    return src;
  }

  const assetWithoutBasePath = src.replace(/^\/gik(?=\/)/, "").replace(/^\//, "");
  return `${siteConfig.siteUrl}/${assetWithoutBasePath}`;
}

export async function generateStaticParams() {
  const posts = await getIntegratedBlogPosts();
  const slugs = new Set([...blogPosts.map((post) => post.slug), ...posts.map((post) => post.slug)]);

  return Array.from(slugs).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: BlogDetailProps): Promise<Metadata> {
  const { slug } = await params;
  const post = await getIntegratedBlogPost(slug);

  if (!post) {
    return {
      title: "ไม่พบบทความ | 34 Build Master Construction",
    };
  }

  const imageUrl = absoluteAssetUrl(post.image);

  return {
    title: post.seo?.title || post.title,
    description: post.seo?.description || post.excerpt,
    alternates: {
      canonical: `${siteConfig.siteUrl}/blog/${post.slug}`,
    },
    openGraph: {
      title: post.title,
      description: post.excerpt,
      url: `${siteConfig.siteUrl}/blog/${post.slug}`,
      images: [imageUrl],
      type: "article",
      locale: "th_TH",
    },
    twitter: {
      card: "summary_large_image",
      title: post.title,
      description: post.seo?.description || post.excerpt,
      images: [imageUrl],
    },
  };
}

export default async function BlogDetailPage({ params }: BlogDetailProps) {
  const { slug } = await params;
  const post = await getIntegratedBlogPost(slug);

  if (!post) {
    notFound();
  }

  const allPosts = await getIntegratedBlogPosts();
  const relatedPosts = allPosts.filter((item) => item.slug !== post.slug).slice(0, 2);
  const imageUrl = absoluteAssetUrl(post.image);
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: post.title,
    description: post.excerpt,
    image: imageUrl,
    mainEntityOfPage: `${siteConfig.siteUrl}/blog/${post.slug}`,
    author: {
      "@type": "Organization",
      name: "34 Build Master Construction",
    },
    publisher: {
      "@type": "Organization",
      name: "34 Build Master Construction",
    },
  };

  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <SiteHeader />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />
      <PageHero title={post.title} currentLabel="รายละเอียดบทความ" parentLabel="บทความ" parentHref="/blog" size="compact" />

      <article className="relative overflow-hidden bg-[#fffaf0] px-5 py-20 lg:px-8">
        <div className="absolute inset-0 pointer-events-none bg-[radial-gradient(circle_at_15%_12%,rgba(246,217,123,0.2),transparent_24%),linear-gradient(90deg,rgba(170,116,38,0.05)_1px,transparent_1px)] bg-[length:auto,92px_92px]" />
        <div className="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
          <aside className="lg:sticky lg:top-28">
            <div className="rounded-[1.75rem] border border-[#aa7426]/24 bg-white/82 p-6 shadow-[0_24px_86px_rgba(17,36,22,0.1)]">
              <p className="section-kicker">Key Points</p>
              <ul className="mt-6 grid gap-3">
                {post.highlights.map((item, index) => (
                  <li key={item} className="flex items-start gap-3 rounded-2xl border border-[#aa7426]/16 bg-[#fbf7ec] p-4">
                    <span className="grid size-9 shrink-0 place-items-center rounded-full bg-[#f6d97b] text-sm font-extrabold text-[#112416]">0{index + 1}</span>
                    <span className="pt-1 text-lg font-bold leading-7 text-[#053920]">{item}</span>
                  </li>
                ))}
              </ul>
            </div>
          </aside>

          <div className="rounded-[2rem] border border-[#aa7426]/18 bg-white/86 p-6 shadow-[0_30px_110px_rgba(17,36,22,0.1)] md:p-10">
            <div className="relative mb-10 min-h-[360px] overflow-hidden rounded-[1.5rem] bg-[#112416]">
              <Image
                src={post.image}
                alt={post.coverAlt}
                fill
                sizes="(min-width: 1024px) 760px, 100vw"
                className="object-cover opacity-86"
              />
            </div>

            {post.contentHtml ? (
              <div className="article-content" dangerouslySetInnerHTML={{ __html: post.contentHtml }} />
            ) : (
              <div className="grid gap-10">
                {post.content.map((section) => (
                  <section key={section.heading}>
                    <h2 className="text-3xl font-extrabold leading-tight text-[#053920] md:text-4xl">{section.heading}</h2>
                    <p className="mt-4 text-xl leading-9 text-[#4d5b50]">{section.body}</p>
                  </section>
                ))}
              </div>
            )}

            <div className="mt-12 rounded-[1.5rem] bg-[#053920] p-6 text-white md:p-8">
              <p className="text-sm font-extrabold uppercase tracking-[0.2em] text-[#f6d97b]">Next Step</p>
              <h2 className="mt-3 text-3xl font-extrabold leading-tight">อยากเริ่มวางแผนโปรเจกต์ของคุณ?</h2>
              <p className="mt-3 text-lg leading-8 text-white/72">
                ส่งรายละเอียดพื้นที่ ไอเดีย หรืองบประมาณเบื้องต้นให้ทีม 34 Build Master Construction ช่วยประเมินแนวทางก่อนเริ่มงานจริงได้เลย
              </p>
              <Link href="/contact" className="gold-button mt-6 inline-flex min-h-12 items-center justify-center px-7 font-extrabold text-[#112416]">
                ติดต่อปรึกษา
              </Link>
            </div>
          </div>
        </div>
      </article>

      <section className="bg-material-section px-5 py-20 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <p className="section-kicker">Related Articles</p>
          <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">บทความที่เกี่ยวข้อง</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-2">
            {relatedPosts.map((item) => (
              <Link key={item.slug} href={`/blog/${item.slug}`} className="group grid gap-5 rounded-[1.7rem] border border-[#aa7426]/24 bg-white/82 p-5 shadow-[0_24px_80px_rgba(17,36,22,0.08)] transition duration-300 hover:-translate-y-1 hover:border-[#aa7426]/55 md:grid-cols-[180px_1fr]">
                <div className="relative min-h-[180px] overflow-hidden rounded-[1.2rem] bg-[#112416]">
                  <Image
                    src={item.image}
                    alt={item.coverAlt}
                    fill
                    sizes="180px"
                    className="object-cover opacity-82 transition duration-700 group-hover:scale-105"
                  />
                </div>
                <div className="flex flex-col justify-center">
                  <p className="text-sm font-extrabold uppercase tracking-[0.18em] text-[#aa7426]">{item.category}</p>
                  <h3 className="mt-3 text-3xl font-extrabold leading-tight text-[#053920]">{item.title}</h3>
                  <p className="mt-3 text-lg leading-8 text-[#4d5b50]">{item.excerpt}</p>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <ContactBand />
      <SiteFooter />
    </main>
  );
}
