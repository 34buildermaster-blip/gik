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
    <main className="modern-inner-page min-h-screen bg-white text-lg text-[#17211c]">
      <SiteHeader />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />
      <PageHero title={post.title} currentLabel="รายละเอียดบทความ" parentLabel="บทความ" parentHref="/blog" size="compact" />

      <article className="relative overflow-hidden bg-white px-5 py-20 lg:px-8">
        <div className="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
          <aside className="lg:sticky lg:top-28">
            <div className="rounded-lg border border-[#dfe4e0] bg-[#f7f8f7] p-6">
              <p className="section-kicker">Key Points</p>
              <ul className="mt-6 grid gap-3">
                {post.highlights.map((item, index) => (
                  <li key={item} className="flex items-start gap-3 rounded-md border border-[#dfe4e0] bg-white p-4">
                    <span className="grid size-9 shrink-0 place-items-center rounded-full bg-[#edf5f0] text-sm font-semibold text-[#0f6b45]">0{index + 1}</span>
                    <span className="pt-1 text-lg font-medium leading-7 text-[#17211c]">{item}</span>
                  </li>
                ))}
              </ul>
            </div>
          </aside>

          <div className="rounded-lg border border-[#dfe4e0] bg-white p-6 shadow-[0_20px_70px_rgba(18,34,25,0.06)] md:p-10">
            <div className="relative mb-10 min-h-[360px] overflow-hidden rounded-lg bg-[#e8ece9]">
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
                    <h2 className="text-3xl font-semibold leading-tight text-[#17211c] md:text-4xl">{section.heading}</h2>
                    <p className="mt-4 text-xl leading-9 text-[#667169]">{section.body}</p>
                  </section>
                ))}
              </div>
            )}

            <div className="mt-12 rounded-lg bg-[#173427] p-6 text-white md:p-8">
              <p className="text-sm font-semibold uppercase tracking-[0.18em] text-[#b8e3ca]">Next Step</p>
              <h2 className="mt-3 text-3xl font-semibold leading-tight">อยากเริ่มวางแผนโปรเจกต์ของคุณ?</h2>
              <p className="mt-3 text-lg leading-8 text-white/72">
                ส่งรายละเอียดพื้นที่ ไอเดีย หรืองบประมาณเบื้องต้นให้ทีม 34 Build Master Construction ช่วยประเมินแนวทางก่อนเริ่มงานจริงได้เลย
              </p>
              <Link href="/contact" className="mt-6 inline-flex min-h-12 items-center justify-center rounded-full bg-white px-7 font-semibold text-[#173427] transition hover:bg-[#edf5f0]">
                ติดต่อปรึกษา
              </Link>
            </div>
          </div>
        </div>
      </article>

      <section className="bg-material-section px-5 py-20 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <p className="section-kicker">Related Articles</p>
          <h2 className="mt-4 text-4xl font-semibold leading-tight text-[#17211c] sm:text-6xl">บทความที่เกี่ยวข้อง</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-2">
            {relatedPosts.map((item) => (
              <Link key={item.slug} href={`/blog/${item.slug}`} className="group grid gap-5 rounded-lg border border-[#dfe4e0] bg-white p-5 transition duration-300 hover:-translate-y-1 hover:border-[#0f6b45] hover:shadow-[0_18px_55px_rgba(18,34,25,0.08)] md:grid-cols-[180px_1fr]">
                <div className="relative min-h-[180px] overflow-hidden rounded-md bg-[#e8ece9]">
                  <Image
                    src={item.image}
                    alt={item.coverAlt}
                    fill
                    sizes="180px"
                    className="object-cover opacity-82 transition duration-700 group-hover:scale-105"
                  />
                </div>
                <div className="flex flex-col justify-center">
                  <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">{item.category}</p>
                  <h3 className="mt-3 text-3xl font-semibold leading-tight text-[#17211c]">{item.title}</h3>
                  <p className="mt-3 text-lg leading-8 text-[#667169]">{item.excerpt}</p>
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
