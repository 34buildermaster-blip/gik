import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  metadataBase: new URL("https://34bmconstruction.com"),
  title: "34 BM Construction | รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน",
  description:
    "34 BM Construction รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร วางแผนงานชัดเจน ดูแลหน้างานเป็นระบบ และพร้อมให้คำปรึกษาเบื้องต้น",
  keywords: [
    "34 BM Construction",
    "รับสร้างบ้าน",
    "รับรีโนเวทบ้าน",
    "รับออกแบบบ้าน",
    "รับทำบิวท์อิน",
    "บริษัทรับเหมาก่อสร้าง",
  ],
  openGraph: {
    title: "34 BM Construction",
    description: "รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร",
    images: ["/hero-construction.png"],
    type: "website",
    locale: "th_TH",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="th"
      className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
