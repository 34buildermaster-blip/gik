export type HouseDesign = {
  slug: string;
  name: string;
  title: string;
  style: "modern" | "minimal" | "contemporary" | "classic";
  styleLabel: string;
  budgetCategory: "under-5" | "5-10" | "over-10";
  budgetLabel: string;
  area: number;
  bedrooms: number;
  bathrooms: number;
  floors: number;
  parkingSpaces: number;
  description: string;
  concept: string;
  features: string[];
  coverImage: string;
  coverAlt: string;
  gallery: Array<{
    id: number | string;
    image: string;
    alt: string;
    caption: string | null;
  }>;
  seo?: {
    title?: string | null;
    description?: string | null;
  };
  source?: "api" | "fallback";
};

const galleryPool = [
  "/approach-homes/modern.jpg",
  "/approach-homes/natural-modern.jpg",
  "/approach-homes/contemporary.jpg",
  "/approach-homes/minimal.jpg",
  "/approach-homes/natural.jpg",
  "/approach-homes/coastal-villa.jpg",
  "/approach-homes/classic.jpg",
  "/approach-homes/villa.jpg",
  "/approach-homes/urban.jpg",
  "/approach-homes/warm-modern.jpg",
];

const baseDesigns = [
  ["bm-courtyard", "BM Courtyard", "บ้านโมเดิร์นคอร์ทยาร์ด", "modern", "โมเดิร์น", "5-10", "5.8 - 7.2 ล้านบาท", 285, 4, 4, 2, 2, "/approach-homes/modern.jpg"],
  ["nordic-retreat", "Nordic Retreat", "บ้านนอร์ดิกอบอุ่น", "contemporary", "ร่วมสมัย", "under-5", "3.9 - 4.8 ล้านบาท", 210, 3, 3, 2, 2, "/approach-homes/natural.jpg"],
  ["warm-minimal", "Warm Minimal", "บ้านมินิมอลแสงธรรมชาติ", "minimal", "มินิมอล", "under-5", "3.4 - 4.3 ล้านบาท", 185, 3, 2, 2, 2, "/approach-homes/minimal.jpg"],
  ["urban-frame", "Urban Frame", "บ้านโมเดิร์นสำหรับครอบครัว", "modern", "โมเดิร์น", "5-10", "6.5 - 8.2 ล้านบาท", 330, 4, 5, 2, 3, "/approach-homes/urban.jpg"],
  ["tropical-villa", "Tropical Villa", "พูลวิลล่าร่วมสมัย", "contemporary", "ร่วมสมัย", "over-10", "10.8 - 13.5 ล้านบาท", 465, 5, 6, 2, 3, "/approach-homes/villa.jpg"],
  ["classic-residence", "Classic Residence", "บ้านคลาสสิกเหนือกาลเวลา", "classic", "คลาสสิก", "over-10", "12.5 - 16 ล้านบาท", 520, 5, 6, 2, 4, "/approach-homes/classic.jpg"],
  ["coastal-living", "Coastal Living", "บ้านพักผ่อนรับวิว", "contemporary", "ร่วมสมัย", "5-10", "7.2 - 9.5 ล้านบาท", 350, 4, 4, 2, 3, "/approach-homes/coastal-villa.jpg"],
  ["natural-house", "Natural House", "บ้านธรรมชาติสมัยใหม่", "minimal", "มินิมอล", "5-10", "5.1 - 6.4 ล้านบาท", 245, 3, 3, 2, 2, "/approach-homes/natural-modern.jpg"],
  ["modern-farmhouse", "Modern Farmhouse", "บ้านฟาร์มเฮาส์ร่วมสมัย", "contemporary", "ร่วมสมัย", "5-10", "5.6 - 7 ล้านบาท", 275, 4, 3, 2, 2, "/approach-homes/contemporary.jpg"],
  ["quiet-luxury", "Quiet Luxury", "บ้านหรูเส้นสายเรียบ", "modern", "โมเดิร์น", "over-10", "11 - 14.5 ล้านบาท", 490, 5, 6, 2, 4, "/approach-homes/warm-modern.jpg"],
] as const;

export const fallbackHouseDesigns: HouseDesign[] = baseDesigns.map((item, index) => {
  const [slug, name, title, style, styleLabel, budgetCategory, budgetLabel, area, bedrooms, bathrooms, floors, parkingSpaces, coverImage] = item;

  return {
    slug,
    name,
    title,
    style,
    styleLabel,
    budgetCategory,
    budgetLabel,
    area,
    bedrooms,
    bathrooms,
    floors,
    parkingSpaces,
    coverImage,
    coverAlt: `${title} โดย 34 Build Master Construction`,
    description:
      "แบบบ้านที่ออกแบบให้สมดุลระหว่างภาพลักษณ์ ฟังก์ชัน และการใช้ชีวิตจริง สามารถปรับผังพื้นที่ วัสดุ และรายละเอียดให้เหมาะกับที่ดินและงบประมาณของแต่ละครอบครัว",
    concept:
      "วางพื้นที่ส่วนกลางให้เชื่อมต่อกันอย่างเป็นธรรมชาติ รับแสงและลมได้ดี พร้อมแยกพื้นที่พักผ่อนส่วนตัวอย่างชัดเจน เพื่อให้บ้านใช้งานสะดวกในทุกช่วงเวลา",
    features: [
      "พื้นที่ส่วนกลางโปร่งและเชื่อมต่อสวน",
      "วางช่องเปิดเพื่อรับแสงธรรมชาติ",
      "ปรับฟังก์ชันให้เหมาะกับขนาดครอบครัว",
      "เลือกวัสดุและงบประมาณได้หลายระดับ",
    ],
    gallery: [0, 1, 2].map((offset) => ({
      id: `${slug}-${offset}`,
      image: offset === 0 ? coverImage : galleryPool[(index + offset) % galleryPool.length],
      alt: `${title} มุมมองที่ ${offset + 1}`,
      caption: offset === 0 ? "ภาพรวมด้านหน้าแบบบ้าน" : null,
    })),
    seo: {
      title: `${title} | 34 Build Master Construction`,
      description: `${title} พื้นที่ใช้สอย ${area} ตารางเมตร ${bedrooms} ห้องนอน ${bathrooms} ห้องน้ำ ปรับแบบได้ตามพื้นที่และงบประมาณ`,
    },
    source: "fallback",
  };
});
