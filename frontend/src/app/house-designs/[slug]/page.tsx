import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, Bath, BedDouble, CarFront, Check, Layers3, Maximize2 } from "lucide-react";
import { ContactBand, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { fallbackHouseDesigns } from "@/data/house-designs";
import { getIntegratedHouseDesign, getIntegratedHouseDesigns } from "@/lib/house-design-api";
import { siteConfig } from "@/lib/site-config";
import { HouseDesignLightbox } from "./house-design-lightbox";

type HouseDesignDetailProps = {
  params: Promise<{ slug: string }>;
};

function absoluteImageUrl(src: string) {
  if (/^https?:\/\//.test(src)) {
    return src;
  }

  const cleanPath = src.replace(/^\/gik(?=\/)/, "").replace(/^\//, "");
  return `${siteConfig.siteUrl}/${cleanPath}`;
}

export async function generateStaticParams() {
  const designs = await getIntegratedHouseDesigns();
  const slugs = new Set([...fallbackHouseDesigns.map((design) => design.slug), ...designs.map((design) => design.slug)]);
  return Array.from(slugs).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: HouseDesignDetailProps): Promise<Metadata> {
  const { slug } = await params;
  const design = await getIntegratedHouseDesign(slug);

  if (!design) {
    return { title: "ไม่พบแบบบ้าน | 34 Build Master Construction" };
  }

  const description = design.seo?.description || design.description;
  const image = absoluteImageUrl(design.coverImage);

  return {
    title: design.seo?.title || `${design.title} | 34 Build Master Construction`,
    description,
    alternates: { canonical: `${siteConfig.siteUrl}/house-designs/${design.slug}` },
    openGraph: {
      title: design.title,
      description,
      url: `${siteConfig.siteUrl}/house-designs/${design.slug}`,
      images: [image],
      type: "website",
      locale: "th_TH",
    },
    twitter: {
      card: "summary_large_image",
      title: design.title,
      description,
      images: [image],
    },
  };
}

export default async function HouseDesignDetailPage({ params }: HouseDesignDetailProps) {
  const { slug } = await params;
  const design = await getIntegratedHouseDesign(slug);

  if (!design) {
    notFound();
  }

  const gallery = design.gallery.length
    ? design.gallery
    : [{ id: "cover", image: design.coverImage, alt: design.coverAlt, caption: null }];
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "CreativeWork",
    name: design.title,
    alternateName: design.name,
    description: design.description,
    image: gallery.map((image) => absoluteImageUrl(image.image)),
    creator: {
      "@type": "Organization",
      name: "34 Build Master Construction",
    },
    url: `${siteConfig.siteUrl}/house-designs/${design.slug}`,
  };

  const specs = [
    { label: "พื้นที่ใช้สอย", value: `${design.area} ตร.ม.`, icon: Maximize2 },
    { label: "ห้องนอน", value: `${design.bedrooms} ห้อง`, icon: BedDouble },
    { label: "ห้องน้ำ", value: `${design.bathrooms} ห้อง`, icon: Bath },
    { label: "จำนวนชั้น", value: `${design.floors} ชั้น`, icon: Layers3 },
    { label: "ที่จอดรถ", value: `${design.parkingSpaces} คัน`, icon: CarFront },
  ];

  return (
    <main className="modern-inner-page min-h-screen bg-white text-[#17211c]">
      <SiteHeader />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />

      <section className="border-b border-[#dfe4e0] bg-[#f4f6f5] px-5 py-10 lg:px-8 lg:py-14">
        <div className="mx-auto grid max-w-7xl items-center gap-9 lg:grid-cols-[.82fr_1.18fr]">
          <div>
            <Link href="/house-designs" className="inline-flex items-center gap-2 text-sm font-semibold text-[#0f6b45]">
              <ArrowLeft className="size-4" aria-hidden="true" /> กลับไปหน้าแบบบ้าน
            </Link>
            <p className="mt-8 text-xs font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">{design.styleLabel} · {design.name}</p>
            <h1 className="mt-3 text-4xl font-semibold leading-tight text-[#17211c] sm:text-5xl">{design.title}</h1>
            <p className="mt-5 max-w-xl text-base leading-8 text-[#667169]">{design.description}</p>
            <div className="mt-7">
              <p className="text-sm text-[#7a847e]">งบก่อสร้างโดยประมาณ</p>
              <p className="mt-1 text-2xl font-semibold text-[#0f6b45]">{design.budgetLabel}</p>
            </div>
            <Link href={`/contact?design=${encodeURIComponent(design.name)}`} className="mt-7 inline-flex min-h-12 items-center justify-center rounded-full bg-[#0f6b45] px-7 font-semibold text-white transition hover:bg-[#0b5839]">
              ปรึกษาและปรับแบบให้เหมาะกับคุณ
            </Link>
          </div>
          <div className="relative aspect-[16/11] overflow-hidden rounded-lg bg-[#e6ebe8]">
            <Image
              src={design.coverImage}
              alt={design.coverAlt}
              fill
              loading="eager"
              fetchPriority="high"
              sizes="(min-width: 1024px) 58vw, 100vw"
              className="object-cover"
            />
          </div>
        </div>
      </section>

      <section className="border-b border-[#dfe4e0] bg-white px-5 py-8 lg:px-8">
        <dl className="mx-auto grid max-w-7xl grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-5">
          {specs.map(({ label, value, icon: Icon }) => (
            <div key={label} className="flex items-center gap-3 lg:border-r lg:border-[#dfe4e0] lg:last:border-r-0">
              <span className="grid size-11 shrink-0 place-items-center rounded-full bg-[#edf5f0] text-[#0f6b45]"><Icon className="size-5" aria-hidden="true" /></span>
              <div><dt className="text-sm text-[#7a847e]">{label}</dt><dd className="mt-1 font-semibold text-[#17211c]">{value}</dd></div>
            </div>
          ))}
        </dl>
      </section>

      <section className="px-5 py-16 lg:px-8 lg:py-24">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[1.1fr_.9fr]">
          <div>
            <p className="section-kicker">Design concept</p>
            <h2 className="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">แนวคิดที่อยู่เบื้องหลังแบบบ้าน</h2>
            <p className="mt-6 whitespace-pre-line text-base leading-8 text-[#667169]">{design.concept || design.description}</p>
          </div>
          <div className="border-l border-[#dfe4e0] pl-0 lg:pl-10">
            <p className="section-kicker">Highlights</p>
            <h2 className="mt-3 text-2xl font-semibold">จุดเด่นของแบบ</h2>
            <ul className="mt-6 grid gap-4">
              {(design.features.length ? design.features : ["ปรับฟังก์ชันตามพื้นที่จริง", "เลือกวัสดุได้ตามงบประมาณ"]).map((feature) => (
                <li key={feature} className="flex items-start gap-3 text-base leading-7 text-[#455149]">
                  <span className="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-[#edf5f0] text-[#0f6b45]"><Check className="size-4" aria-hidden="true" /></span>
                  {feature}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </section>

      <section className="bg-[#f4f6f5] px-5 py-16 lg:px-8 lg:py-24">
        <div className="mx-auto max-w-7xl">
          <p className="section-kicker">Design gallery</p>
          <div className="mt-3 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <h2 className="text-3xl font-semibold leading-tight sm:text-4xl">มุมมองและบรรยากาศของบ้าน</h2>
            <p className="text-sm text-[#7a847e]">{gallery.length} รูปภาพ</p>
          </div>
          <HouseDesignLightbox images={gallery} />
        </div>
      </section>

      <ContactBand />
      <SiteFooter />
    </main>
  );
}
