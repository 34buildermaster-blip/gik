import type { Metadata } from "next";
import Link from "next/link";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { sitePath } from "@/lib/asset-path";

export const metadata: Metadata = {
  title: "บริการ | 34 Build Master Construction",
  description:
    "บริการออกแบบบ้าน รีโนเวท สร้างบ้าน และบิวท์อินครบวงจรโดย 34 Build Master Construction",
};

const services = [
  {
    title: "ออกแบบบ้าน",
    detail: "วางภาพรวม ฟังก์ชัน และทิศทางวัสดุให้เหมาะกับงบประมาณ ไลฟ์สไตล์ และพื้นที่จริง",
  },
  {
    title: "รีโนเวทบ้าน",
    detail: "ปรับบ้านเดิมให้สวยขึ้น ใช้งานดีขึ้น พร้อมวางลำดับงานเพื่อควบคุมเวลาและคุณภาพ",
  },
  {
    title: "สร้างบ้าน",
    detail: "ดูแลการสร้างบ้านเป็นขั้นตอน ตั้งแต่วางแผนหน้างาน คุมงาน ไปจนถึงตรวจรับ",
  },
  {
    title: "บิวท์อิน",
    detail: "ออกแบบและผลิตงานเฟอร์นิเจอร์บิวท์อินให้เข้ากับพื้นที่จริงและ mood ของบ้าน",
  },
];

export default function ServicesPage() {
  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <SiteHeader />
      <PageHero title="บริการ" currentLabel="บริการ" />

      <section className="bg-material-section px-5 py-20 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-6 md:grid-cols-2">
          {services.map((service, index) => (
            <article key={service.title} className="luxe-card p-7">
              <p className="gold-text text-2xl font-extrabold">0{index + 1}</p>
              <h2 className="mt-5 text-4xl font-extrabold text-[#053920]">{service.title}</h2>
              <p className="mt-4 text-xl leading-9 text-[#4d5b50]">{service.detail}</p>
              <Link
                href={sitePath("/contact")}
                className="mt-7 inline-flex min-h-12 items-center rounded-full border border-[#aa7426]/35 px-6 font-extrabold text-[#053920] transition hover:bg-[#053920] hover:text-[#fdf0a3]"
              >
                ปรึกษางานนี้
              </Link>
            </article>
          ))}
        </div>
      </section>
      <ContactBand />
      <SiteFooter />
    </main>
  );
}
