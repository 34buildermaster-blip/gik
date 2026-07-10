import type { Metadata } from "next";
import Image from "next/image";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { projectUpdates } from "@/data/project-updates";

export const metadata: Metadata = {
  title: "อัปเดตงาน",
  description:
    "ติดตามความคืบหน้าหน้างาน การเลือกวัสดุ และทิศทางงานออกแบบจาก 34 Build Master Construction",
  alternates: {
    canonical: "/updates",
  },
};

const updateTopics = [
  {
    number: "01",
    title: "ความคืบหน้าหน้างาน",
    detail: "เห็นลำดับงานและจุดสำคัญที่ทีมกำลังตรวจสอบในแต่ละช่วง",
  },
  {
    number: "02",
    title: "วัสดุและรายละเอียด",
    detail: "ดูแนวทางเลือกสี ผิวสัมผัส และวัสดุให้เข้ากับภาพรวมของบ้าน",
  },
  {
    number: "03",
    title: "ทิศทางงานออกแบบ",
    detail: "ติดตามแนวคิดที่ถูกพัฒนาจากโจทย์จนพร้อมนำไปทำงานจริง",
  },
];

export default function UpdatesPage() {
  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <SiteHeader />
      <PageHero title="อัปเดตงาน" currentLabel="อัปเดตงาน" />

      <section className="relative overflow-hidden bg-[#fffaf0] px-5 py-20 lg:px-8 lg:py-24">
        <div className="absolute inset-0 pointer-events-none bg-[radial-gradient(circle_at_15%_18%,rgba(246,217,123,0.24),transparent_28%),linear-gradient(90deg,rgba(170,116,38,0.05)_1px,transparent_1px)] bg-[length:auto,88px_88px]" />
        <div className="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-end">
          <div>
            <p className="section-kicker">Project Journal</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">
              เห็นวิธีทำงานจริง ก่อนเริ่มโปรเจกต์ของคุณ
            </h2>
          </div>
          <div>
            <p className="max-w-3xl text-xl leading-9 text-[#4d5b50]">
              รวมภาพและรายละเอียดจากแต่ละช่วงของงาน เพื่อให้คุณเห็นมาตรฐานการวางแผน การเลือกวัสดุ และการดูแลหน้างานของทีมได้ชัดเจนขึ้น
            </p>
            <div className="mt-8 grid gap-4 sm:grid-cols-3">
              {updateTopics.map((topic) => (
                <div key={topic.number} className="border-l-2 border-[#aa7426] pl-4">
                  <p className="text-sm font-extrabold tracking-[0.16em] text-[#aa7426]">{topic.number}</p>
                  <h3 className="mt-2 text-xl font-bold text-[#053920]">{topic.title}</h3>
                  <p className="mt-2 text-base leading-7 text-[#4d5b50]">{topic.detail}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="live-updates-section px-5 py-20 text-white lg:px-8 lg:py-24">
        <div className="relative z-10 mx-auto max-w-7xl">
          <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div className="max-w-3xl">
              <p className="section-kicker text-[#f6d97b]">Latest Updates</p>
              <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
                ความเคลื่อนไหวล่าสุดจากทีมงาน
              </h2>
            </div>
            <p className="max-w-xl text-xl leading-9 text-white/72">
              แต่ละรายการสะท้อนกระบวนการทำงานคนละช่วง ตั้งแต่สำรวจพื้นที่ ไปจนถึงสรุปวัสดุและทิศทางดีไซน์
            </p>
          </div>

          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {projectUpdates.map((update) => (
              <article key={update.title} className="project-update-card section-card group">
                <div className="project-update-image">
                  <Image
                    src={update.image}
                    alt={update.title}
                    fill
                    sizes="(min-width: 768px) 33vw, 100vw"
                    className="object-cover transition duration-500 group-hover:scale-105"
                  />
                </div>
                <div className="p-6">
                  <p className="text-base font-extrabold uppercase tracking-[0.14em] text-[#f6d97b]">
                    {update.stage}
                  </p>
                  <h3 className="mt-3 text-2xl font-extrabold leading-snug text-white">{update.title}</h3>
                  <p className="mt-3 text-lg leading-8 text-white/68">{update.detail}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <ContactBand />
      <SiteFooter />
    </main>
  );
}
