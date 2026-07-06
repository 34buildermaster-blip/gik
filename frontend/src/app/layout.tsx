import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL("https://34bmconstruction.com"),
  title: "34 Build Master Construction | รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน",
  description:
    "34 Build Master Construction รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร สร้างสรรค์คุณภาพ มุ่งมั่นในทุกงานก่อสร้าง",
  keywords: [
    "34 BM Construction",
    "34 Build Master Construction",
    "Build Master Construction",
    "รับสร้างบ้าน",
    "รับรีโนเวทบ้าน",
    "รับออกแบบบ้าน",
    "รับทำบิวท์อิน",
    "บริษัทรับเหมาก่อสร้าง",
  ],
  openGraph: {
    title: "34 Build Master Construction",
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
    <html lang="th" className="h-full antialiased">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link
          href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
