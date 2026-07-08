import type { Metadata } from "next";
import { siteConfig, socialLinks } from "@/lib/site-config";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL(siteConfig.siteUrl),
  title: {
    default: `${siteConfig.name} | รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน`,
    template: `%s | ${siteConfig.name}`,
  },
  description: siteConfig.description,
  keywords: [
    "34 BM Construction",
    "34 Build Master Construction",
    "รับสร้างบ้านเชียงใหม่",
    "รับรีโนเวทบ้านเชียงใหม่",
    "รับออกแบบบ้านเชียงใหม่",
    "รับทำบิวท์อินเชียงใหม่",
    "บริษัทรับเหมาก่อสร้างเชียงใหม่",
  ],
  alternates: {
    canonical: "/",
  },
  openGraph: {
    title: siteConfig.name,
    description: siteConfig.description,
    url: "/",
    siteName: siteConfig.name,
    images: [
      {
        url: "/hero-construction.png",
        width: 1727,
        height: 911,
        alt: `${siteConfig.name} รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน`,
      },
    ],
    type: "website",
    locale: "th_TH",
  },
  twitter: {
    card: "summary_large_image",
    title: siteConfig.name,
    description: siteConfig.description,
    images: ["/hero-construction.png"],
  },
};

const localBusinessJsonLd = {
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  name: siteConfig.name,
  description: siteConfig.description,
  url: siteConfig.siteUrl,
  telephone: siteConfig.phoneDisplay,
  email: siteConfig.email,
  image: `${siteConfig.siteUrl}/hero-construction.png`,
  address: {
    "@type": "PostalAddress",
    streetAddress: "161/26 หมู่ 4",
    addressLocality: "ตำบลหนองป่าครั่ง อำเภอเมืองเชียงใหม่",
    addressRegion: "เชียงใหม่",
    postalCode: "50000",
    addressCountry: "TH",
  },
  areaServed: siteConfig.area,
  sameAs: socialLinks.map((item) => item.href),
  makesOffer: [
    { "@type": "Offer", itemOffered: { "@type": "Service", name: "ออกแบบบ้าน" } },
    { "@type": "Offer", itemOffered: { "@type": "Service", name: "รีโนเวทบ้าน" } },
    { "@type": "Offer", itemOffered: { "@type": "Service", name: "สร้างบ้าน" } },
    { "@type": "Offer", itemOffered: { "@type": "Service", name: "บิวท์อิน" } },
  ],
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="th" className="h-full antialiased">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link
          href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=Noto+Serif+Thai:wght@500;600;700&display=swap"
          rel="stylesheet"
        />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(localBusinessJsonLd) }}
        />
      </head>
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
