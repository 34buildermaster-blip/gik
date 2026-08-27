import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, Hammer, House, Layers3, Ruler } from "lucide-react";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { assetPath } from "@/lib/asset-path";

export const metadata: Metadata = {
  title: "บริการ",
  description: "บริการออกแบบบ้าน รีโนเวท สร้างบ้าน และบิวท์อินครบวงจรโดย 34 Build Master Construction",
};

const services = [
  { number: "01", title: "ออกแบบบ้าน", detail: "วางภาพรวม ฟังก์ชัน และทิศทางวัสดุให้เหมาะกับงบประมาณ ไลฟ์สไตล์ และพื้นที่จริง", icon: Ruler, image: "/approach-homes/minimal.jpg" },
  { number: "02", title: "สร้างบ้าน", detail: "ดูแลการสร้างบ้านเป็นขั้นตอน ตั้งแต่วางแผนหน้างาน ควบคุมคุณภาพ ไปจนถึงตรวจรับ", icon: House, image: "/approach-homes/contemporary.jpg" },
  { number: "03", title: "รีโนเวทบ้าน", detail: "ปรับบ้านเดิมให้สวยและใช้งานดีขึ้น พร้อมวางลำดับงานเพื่อควบคุมเวลาและงบประมาณ", icon: Hammer, image: "/approach-homes/urban.jpg" },
  { number: "04", title: "บิวท์อิน", detail: "ออกแบบและผลิตเฟอร์นิเจอร์บิวท์อินให้พอดีกับพื้นที่จริง วัสดุ และบรรยากาศของบ้าน", icon: Layers3, image: "/approach-homes/warm-modern.jpg" },
];

export default function ServicesPage() {
  return (
    <main className="modern-inner-page min-h-screen bg-white text-[#17211c]">
      <SiteHeader />
      <PageHero title="บริการของเรา" currentLabel="บริการ" />

      <section className="px-5 py-20 lg:px-8 lg:py-28">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
            <div>
              <p className="modern-kicker">Our services</p>
              <h2 className="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">ครบตั้งแต่แนวคิด<br />จนถึงพื้นที่จริง</h2>
            </div>
            <p className="max-w-3xl text-lg leading-8 text-[#667169]">เลือกบริการที่ตรงกับโครงการของคุณ ทุกงานเริ่มจากการสำรวจโจทย์จริง วางแผนให้เห็นภาพ และควบคุมรายละเอียดตลอดกระบวนการ</p>
          </div>

          <div className="mt-12 grid gap-6 md:grid-cols-2">
            {services.map((service) => {
              const ServiceIcon = service.icon;
              return (
                <article key={service.title} className="group overflow-hidden rounded-lg border border-[#dfe4e0] bg-white transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(18,34,25,0.1)]">
                  <div className="relative aspect-[16/9] overflow-hidden bg-[#e8ece9]">
                    <Image src={assetPath(service.image)} alt={service.title} fill sizes="(min-width: 768px) 50vw, 100vw" className="object-cover transition duration-700 group-hover:scale-[1.04]" />
                    <span className="absolute left-5 top-5 grid size-12 place-items-center rounded-full bg-white text-[#0f6b45] shadow-lg"><ServiceIcon className="size-5" /></span>
                  </div>
                  <div className="grid gap-5 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-start md:p-8">
                    <span className="text-sm font-semibold text-[#0f6b45]">{service.number}</span>
                    <div><h3 className="text-2xl font-semibold text-[#17211c]">{service.title}</h3><p className="mt-3 text-base leading-7 text-[#667169]">{service.detail}</p></div>
                    <Link href="/contact" aria-label={`ปรึกษางาน${service.title}`} className="grid size-11 place-items-center rounded-full border border-[#cfd6d1] text-[#17211c] transition group-hover:border-[#0f6b45] group-hover:bg-[#0f6b45] group-hover:text-white"><ArrowUpRight className="size-5" /></Link>
                  </div>
                </article>
              );
            })}
          </div>
        </div>
      </section>

      <section className="border-y border-[#dfe4e0] bg-[#f1f3f1] px-5 py-16 lg:px-8">
        <div className="mx-auto flex max-w-7xl flex-col gap-6 md:flex-row md:items-center md:justify-between">
          <div><p className="modern-kicker">Not sure where to start?</p><h2 className="mt-3 text-2xl font-semibold sm:text-3xl">ส่งข้อมูลเบื้องต้นให้ทีมช่วยประเมินประเภทงาน</h2></div>
          <Link href="/contact" className="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-full bg-[#0f6b45] px-7 font-semibold text-white transition hover:bg-[#0a5335]">เริ่มปรึกษา <ArrowUpRight className="size-5" /></Link>
        </div>
      </section>
      <ContactBand />
      <SiteFooter />
    </main>
  );
}
