import type { Metadata } from "next";
import { Kanit } from "next/font/google";
import { assetPath } from "@/lib/asset-path";
import { siteConfig, socialLinks } from "@/lib/site-config";
import { SiteSettingsProvider } from "@/contexts/site-settings-context";
import { FloatingContactDock } from "@/components/floating-contact-dock";
import { CookieConsent } from "@/components/cookie-consent";
import { WelcomePopup } from "@/components/welcome-popup";
import "./globals.css";

const kanit = Kanit({
  subsets: ["thai", "latin"],
  weight: ["300", "400", "500", "600", "700"],
  variable: "--font-kanit",
  display: "swap",
});

export const metadata: Metadata = {
  metadataBase: new URL(siteConfig.siteUrl),
  title: {
    default: `${siteConfig.name} | รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน`,
    template: `%s | ${siteConfig.name}`,
  },
  description: siteConfig.description,
  icons: {
    icon: [{ url: assetPath("/brand-logo.png"), type: "image/png", sizes: "any" }],
    shortcut: assetPath("/brand-logo.png"),
    apple: [{ url: assetPath("/brand-logo.png"), type: "image/png" }],
  },
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
    <html lang="th" className={`${kanit.variable} h-full antialiased`}>
      <head>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(localBusinessJsonLd) }}
        />
      </head>
      <body className="min-h-full flex flex-col">
        <SiteSettingsProvider>
          {children}
          <FloatingContactDock />
          <WelcomePopup />
          <CookieConsent />
        </SiteSettingsProvider>
      </body>
    </html>
  );
}
