import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ContactBand, SiteFooter, SiteHeader } from "@/components/site-chrome";

export const metadata: Metadata = {
  title: "เกี่ยวกับเรา | 34 Build Master Construction",
  description:
    "รู้จัก 34 Build Master Construction ทีมรับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินในเชียงใหม่ ที่เน้นคุณภาพ ความน่าเชื่อถือ และการดูแลงานเป็นขั้นตอน",
};

const stats = [
  { value: "4", label: "บริการหลัก", detail: "ออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน" },
  { value: "1", label: "ทีมดูแลงานครบ", detail: "ประสานงานตั้งแต่รับโจทย์จนส่งมอบ" },
  { value: "360", label: "วางแผนรอบด้าน", detail: "ดูฟังก์ชัน งบประมาณ วัสดุ และหน้างานจริง" },
];

const principles = [
  {
    title: "ออกแบบให้ใช้งานจริง",
    detail: "ทุกแนวทางต้องตอบโจทย์การอยู่จริง ไม่ใช่แค่ภาพสวย แต่ต้องดูแลรักษาได้และอยู่ได้นาน",
  },
  {
    title: "คุมคุณภาพเป็นขั้นตอน",
    detail: "เราให้ความสำคัญกับการตรวจหน้างาน ลำดับงาน วัสดุ และรายละเอียดเล็ก ๆ ก่อนส่งมอบ",
  },
  {
    title: "สื่อสารให้เจ้าของบ้านมั่นใจ",
    detail: "ลูกค้าควรเห็นภาพรวมของงาน งบประมาณ และสิ่งที่ต้องตัดสินใจก่อนเริ่มลงมือจริง",
  },
];

const process = [
  "รับโจทย์และทำความเข้าใจพื้นที่",
  "ประเมินแนวทาง งบ และขอบเขตงาน",
  "วางแผนวัสดุ รายละเอียด และลำดับงาน",
  "ตรวจคุณภาพก่อนส่งมอบ",
];

const brandValues = ["Modern", "Luxury", "Premium", "Trustworthy", "Professional"];

export default function AboutPage() {
  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <SiteHeader />

      <section className="about-page-title relative grid min-h-[320px] place-items-center overflow-hidden bg-[#053920] px-5 py-16 text-white lg:px-8">
        <Image
          src="/bg-luxury-green.png"
          alt="พื้นหลังแบรนด์ 34 Build Master Construction"
          fill
          priority
          sizes="100vw"
          className="z-0 object-cover opacity-55"
        />
        <div className="about-title-overlay absolute inset-0 z-10" />
        <div className="relative z-20 mx-auto max-w-7xl text-center">
          <h1 className="text-5xl font-extrabold leading-tight drop-shadow-[0_18px_46px_rgba(0,0,0,0.35)] sm:text-7xl">เกี่ยวกับเรา</h1>
          <div className="mt-5 flex items-center justify-center gap-3 text-lg font-bold">
            <Link href="/" className="text-white/76 transition hover:text-[#f6d97b]">
              หน้าหลัก
            </Link>
            <span className="text-[#f6d97b]">/</span>
            <span className="text-[#f6d97b]">เกี่ยวกับเรา</span>
          </div>
        </div>
        <span aria-hidden="true" className="absolute inset-x-0 bottom-0 z-20 h-1 bg-gradient-to-r from-transparent via-[#f6d97b] to-transparent" />
      </section>

      <section className="about-intro-section relative overflow-hidden bg-[#fffaf0] px-5 py-24 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.9fr_1fr] lg:items-center">
          <div>
            <p className="section-kicker">About 34 BM</p>
            <h2 className="mt-4 max-w-4xl text-[2.25rem] font-extrabold leading-tight text-[#053920] sm:text-[2.9rem] lg:text-5xl xl:text-6xl">
              แบรนด์ที่ให้คุณค่ากับบ้านของคุณ
            </h2>
            <p className="mt-5 text-xl leading-9 text-[#4d5b50]">
              34 Build Master Construction รับออกแบบ รีโนเวท สร้างบ้าน และงานบิวท์อิน
              สำหรับเจ้าของบ้านที่ต้องการงานเรียบร้อย ดูดี ใช้งานได้จริง และมีทีมช่วยดูภาพรวมตั้งแต่เริ่มคิดจนส่งมอบงาน
            </p>
            <p className="mt-4 text-xl leading-9 text-[#4d5b50]">
              เราเชื่อว่างานบ้านที่ดีเริ่มจากการฟังโจทย์ให้ละเอียด วางแผนให้ชัด และทำงานด้วยมาตรฐานที่ตรวจสอบได้
            </p>
            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
              <Link
                href="/contact"
                className="gold-button inline-flex min-h-12 items-center justify-center px-7 text-base font-extrabold text-[#112416]"
              >
                คุยกับทีมของเรา
              </Link>
              <Link
                href="/services"
                className="inline-flex min-h-12 items-center justify-center rounded-full border border-[#aa7426]/45 px-7 text-base font-bold text-[#053920] transition hover:bg-[#053920] hover:text-[#fdf0a3]"
              >
                ดูบริการทั้งหมด
              </Link>
            </div>
          </div>

          <div className="about-intro-image relative min-h-[420px] overflow-hidden rounded-lg border border-[#aa7426]/25 bg-[#112416] shadow-[0_28px_86px_rgba(17,36,22,0.14)]">
            <Image
              src="/hero-construction.png"
              alt="ทีมงาน 34 Build Master Construction สำรวจหน้างาน"
              fill
              sizes="(min-width: 1024px) 50vw, 100vw"
              className="object-cover"
            />
          </div>
        </div>
      </section>

      <section className="about-stats-section px-5 py-18 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-4 md:grid-cols-3">
          {stats.map((item) => (
            <article key={item.label} className="about-stat-card">
              <p className="gold-text text-5xl font-extrabold">{item.value}</p>
              <h3 className="mt-3 text-2xl font-extrabold text-[#053920]">{item.label}</h3>
              <p className="mt-2 text-lg leading-8 text-[#4d5b50]">{item.detail}</p>
            </article>
          ))}
        </div>
      </section>

      <section className="about-story-section px-5 py-24 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.84fr_1.16fr] lg:items-center">
          <div>
            <p className="section-kicker">Our Approach</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">
              เราไม่ได้เริ่มจากแบบ แต่เริ่มจากวิธีใช้ชีวิตของเจ้าของบ้าน
            </h2>
          </div>
          <div className="about-story-panel">
            <p>
              บ้านแต่ละหลังมีเงื่อนไขไม่เหมือนกัน ทั้งพื้นที่ งบประมาณ สมาชิกในบ้าน วัสดุที่ชอบ
              และภาพงานที่เจ้าของบ้านอยากเห็น เราจึงเริ่มจากการทำความเข้าใจโจทย์จริงก่อนค่อยสรุปแนวทางงาน
            </p>
            <p>
              สิ่งที่ทีมให้ความสำคัญคือการทำให้งานก่อสร้างเข้าใจง่ายขึ้น ตั้งแต่การประเมินเบื้องต้น
              การเลือกวัสดุ การจัดลำดับงาน ไปจนถึงการตรวจรายละเอียดก่อนส่งมอบ
            </p>
          </div>
        </div>
      </section>

      <section className="about-principles-section px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="mx-auto max-w-4xl text-center">
            <p className="section-kicker process-kicker mx-auto text-[#f6d97b]">What We Care</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight text-white sm:text-6xl">
              สิ่งที่เราใส่ใจในทุกโปรเจกต์
            </h2>
          </div>
          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {principles.map((item, index) => (
              <article key={item.title} className="about-principle-card">
                <span className="about-principle-number">0{index + 1}</span>
                <h3 className="mt-6 text-3xl font-extrabold leading-tight">{item.title}</h3>
                <p className="mt-4 text-lg leading-8 text-white/68">{item.detail}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="about-process-section px-5 py-24 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
          <div>
            <p className="section-kicker">How We Work</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">
              ขั้นตอนทำงานที่ช่วยให้เจ้าของบ้านเห็นภาพตั้งแต่ต้น
            </h2>
          </div>
          <ol className="about-process-list">
            {process.map((step, index) => (
              <li key={step}>
                <span>{index + 1}</span>
                <p>{step}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="about-brand-band px-5 py-20 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-6 md:grid-cols-5">
          {brandValues.map((value) => (
            <div key={value} className="about-brand-chip">
              {value}
            </div>
          ))}
        </div>
      </section>

      <ContactBand />
      <SiteFooter />
    </main>
  );
}
