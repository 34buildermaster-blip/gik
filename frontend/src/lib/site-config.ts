export const siteConfig = {
  name: "34 Build Master Construction",
  shortName: "34 Build Master",
  siteUrl: process.env.NEXT_PUBLIC_SITE_URL ?? "https://34buildermaster-blip.github.io/gik",
  description:
    "รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจรในเชียงใหม่ วางแผนงานชัดเจน ดูแลคุณภาพ และสื่อสารกับเจ้าของบ้านทุกขั้นตอน",
  phoneDisplay: "081-9512-297",
  phoneHref: "tel:+66819512297",
  email: "34buildmaster@gmail.com",
  area: "เชียงใหม่ และพื้นที่ใกล้เคียง",
  address: "161/26 หมู่ 4 ตำบลหนองป่าครั่ง อำเภอเมืองเชียงใหม่ จังหวัดเชียงใหม่ 50000",
};

export const socialLinks = [
  { label: "Facebook", href: "https://www.facebook.com/34BuildMasterConstruction", icon: "facebook" as const },
  { label: "Instagram", href: "https://www.instagram.com/34buildmaster", icon: "instagram" as const },
  { label: "Line", href: "https://line.me/R/ti/p/@34buildmaster", icon: "line" as const },
  { label: "TikTok", href: "https://www.tiktok.com/@34buildmaster", icon: "tiktok" as const },
];

export const primaryPages = [
  { path: "/", label: "หน้าหลัก" },
  { path: "/about", label: "เกี่ยวกับเรา" },
  { path: "/services", label: "บริการ" },
  { path: "/blog", label: "บทความ" },
  { path: "/faq", label: "คำถามพบบ่อย" },
  { path: "/contact", label: "ติดต่อ" },
];
