import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { assetPath } from "@/lib/asset-path";
import { TestimonialsCarousel } from "./testimonials-carousel";

export const metadata: Metadata = {
  title: "เกี่ยวกับเรา",
  description:
    "รู้จัก 34 Build Master Construction ทีมออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินในเชียงใหม่ ที่ดูแลงานอย่างเป็นระบบตั้งแต่รับโจทย์จนส่งมอบ",
};

type IconName = "brief" | "plan" | "build" | "check" | "diamond" | "crown" | "shield" | "people" | "quote" | "arrow";

function AboutIcon({ name }: { name: IconName }) {
  const paths: Record<IconName, React.ReactNode> = {
    brief: <><path d="M9 6V4h6v2" /><rect x="3" y="6" width="18" height="14" rx="2" /><path d="M3 11h18M9 11v2h6v-2" /></>,
    plan: <><path d="M4 19V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" /><path d="M14 3v6h6M8 13h8M8 17h6" /></>,
    build: <><path d="m14 6 4-4 4 4-4 4" /><path d="m16 8-9.5 9.5a2.1 2.1 0 0 1-3-3L13 5" /><path d="m11 13 5 5" /></>,
    check: <><path d="M20 7 9 18l-5-5" /><path d="M15 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7" /></>,
    diamond: <><path d="m12 21 9-11-4-6H7l-4 6 9 11Z" /><path d="m3 10 9 11 9-11M7 4l5 17 5-17M3 10h18" /></>,
    crown: <><path d="m3 7 4 4 5-7 5 7 4-4-2 11H5L3 7Z" /><path d="M5 21h14" /></>,
    shield: <><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" /><path d="m9 12 2 2 4-4" /></>,
    people: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></>,
    quote: <><path d="M10 11H5a4 4 0 0 0 4 4v2a4 4 0 0 1-4 4" /><path d="M22 11h-5a4 4 0 0 0 4 4v2a4 4 0 0 1-4 4" /></>,
    arrow: <><path d="M5 12h14" /><path d="m13 6 6 6-6 6" /></>,
  };

  return <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">{paths[name]}</svg>;
}

const stats = [
  { value: "4", suffix: " บริการ", label: "ดูแลครบทุกความต้องการเรื่องบ้าน" },
  { value: "360°", suffix: "", label: "วางแผนครบทั้งแบบ งบ และหน้างาน" },
  { value: "1", suffix: " ทีม", label: "ประสานงานตั้งแต่เริ่มจนส่งมอบ" },
  { value: "100%", suffix: "", label: "ตรวจคุณภาพก่อนส่งมอบทุกโครงการ" },
];

const workSteps = [
  { icon: "brief" as const, title: "รับโจทย์", text: "ฟังความต้องการ รูปแบบการใช้ชีวิต และกรอบงบประมาณ" },
  { icon: "plan" as const, title: "ออกแบบและวางแผน", text: "สำรวจพื้นที่ สรุปแนวทาง วัสดุ และลำดับการทำงาน" },
  { icon: "build" as const, title: "ลงมือก่อสร้าง", text: "ควบคุมงานตามแผน พร้อมสื่อสารความคืบหน้าเป็นระยะ" },
  { icon: "check" as const, title: "ตรวจและส่งมอบ", text: "เก็บรายละเอียด ตรวจคุณภาพ และส่งมอบอย่างเป็นระบบ" },
];

const values = [
  { icon: "diamond" as const, title: "คุณภาพที่ตรวจสอบได้", text: "เลือกวัสดุและควบคุมรายละเอียดให้เหมาะกับการใช้งานจริง" },
  { icon: "crown" as const, title: "ดีไซน์เหนือกาลเวลา", text: "ออกแบบให้สวยร่วมสมัย ใช้งานง่าย และอยู่ได้นาน" },
  { icon: "shield" as const, title: "สื่อสารอย่างตรงไปตรงมา", text: "อธิบายขอบเขต งบ และผลกระทบก่อนตัดสินใจทุกครั้ง" },
  { icon: "people" as const, title: "ทีมเดียวดูแลต่อเนื่อง", text: "ลดความซับซ้อนในการประสานงานตั้งแต่วันแรกจนจบโครงการ" },
];

const milestones = [
  { year: "01", title: "เริ่มจากการฟัง", text: "ทำความเข้าใจบ้าน เจ้าของบ้าน และเป้าหมายที่ต้องการให้ชัด" },
  { year: "02", title: "เปลี่ยนโจทย์เป็นแผน", text: "เชื่อมแบบ วัสดุ งบประมาณ และระยะเวลาให้เป็นภาพเดียวกัน" },
  { year: "03", title: "ควบคุมทุกขั้นตอน", text: "ตรวจหน้างานและสื่อสารข้อมูลสำคัญตลอดการดำเนินงาน" },
  { year: "04", title: "ส่งมอบความมั่นใจ", text: "ตรวจรายละเอียดสุดท้ายพร้อมดูแลคำแนะนำหลังส่งมอบ" },
];

const team = [
  { role: "ทีมออกแบบ", detail: "วางฟังก์ชันและภาพรวมให้ตรงกับชีวิตจริง", position: "66% center" },
  { role: "ทีมควบคุมงาน", detail: "ดูแลแผน คุณภาพ และการประสานงานหน้างาน", position: "76% center" },
  { role: "ทีมผู้เชี่ยวชาญ", detail: "เชื่อมทุกฝ่ายให้ทำงานไปในมาตรฐานเดียวกัน", position: "center center" },
];

const faqs = [
  ["34 Build Master ให้บริการอะไรบ้าง?", "เราให้บริการออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินแบบครบวงจร ตั้งแต่สำรวจพื้นที่จนถึงตรวจรับและส่งมอบ"],
  ["เริ่มต้นคุยงานต้องเตรียมอะไร?", "เตรียมรูปพื้นที่ ขนาดโดยประมาณ ความต้องการใช้งาน และกรอบงบคร่าว ๆ เพื่อช่วยให้ทีมประเมินแนวทางได้เร็วขึ้น"],
  ["สามารถประเมินงบเบื้องต้นได้ไหม?", "ได้ครับ ทีมจะพิจารณาจากขอบเขตงาน แบบ วัสดุ สภาพพื้นที่ และช่วงเวลาที่ต้องการดำเนินงาน"],
];

export default function AboutPage() {
  return (
    <main className="modern-inner-page min-h-screen bg-white text-[#17211c]">
      <SiteHeader />
      <PageHero title="เกี่ยวกับเรา" currentLabel="เกี่ยวกับเรา" />

      <section className="about-v2-intro">
        <div className="about-v2-shell about-v2-intro-grid">
          <div className="about-v2-copy">
            <p className="section-kicker">About 34 Build Master</p>
            <h2>สร้างคุณภาพ<br /><span>ในทุกพื้นที่ของชีวิต</span></h2>
            <p>เราเป็นทีมออกแบบและก่อสร้างที่ดูแลบ้านอย่างเป็นระบบ เชื่อมความต้องการของเจ้าของบ้านเข้ากับแบบ งบประมาณ วัสดุ และการทำงานหน้างานจริง</p>
            <p>ทุกโครงการเริ่มจากการฟังให้ชัด วางแผนให้เห็นภาพ และลงมือด้วยมาตรฐานที่ตรวจสอบได้ เพื่อให้ผลลัพธ์สวย ใช้งานได้จริง และอยู่กับคุณไปได้นาน</p>
            <Link href="/contact" className="gold-button about-v2-button">เริ่มคุยกับทีม <AboutIcon name="arrow" /></Link>
          </div>
          <div className="about-v2-intro-visual">
            <div className="about-v2-image-main"><Image src={assetPath("/hero-construction.png")} alt="ทีม 34 Build Master ตรวจแบบหน้างาน" fill priority sizes="(min-width: 1024px) 46vw, 100vw" /></div>
            <div className="about-v2-image-detail"><Image src={assetPath("/hero-construction.png")} alt="รายละเอียดบ้านสมัยใหม่" fill sizes="260px" /></div>
            <div className="about-v2-note"><strong>34 BM</strong><span>คิดครบ ทำจริง<br />ดูแลทุกขั้นตอน</span></div>
          </div>
        </div>
      </section>

      <section className="about-v2-showcase">
        <div className="about-v2-shell">
          <div className="about-v2-heading centered">
            <p className="section-kicker">Our Work</p>
            <h2>เราเปลี่ยนแผนงาน<br /><span>ให้กลายเป็นพื้นที่จริง</span></h2>
          </div>
          <div className="about-v2-showcase-image">
            <Image src={assetPath("/hero-construction.png")} alt="ผลงานบ้านและทีมก่อสร้างของ 34 Build Master" fill sizes="(min-width: 1280px) 1180px, 94vw" />
            <div className="about-v2-play"><span>34</span><p>งานที่ดี เริ่มจากแผนที่ชัดเจน</p></div>
          </div>
          <div className="about-v2-stats">
            {stats.map((item) => <div key={item.label}><strong>{item.value}<small>{item.suffix}</small></strong><span>{item.label}</span></div>)}
          </div>
        </div>
      </section>

      <section className="about-v2-process">
        <div className="about-v2-shell">
          <div className="about-v2-heading centered">
            <p className="section-kicker">How We Get It Done</p>
            <h2>ขั้นตอนที่ทำให้ทุกฝ่าย<br /><span>เห็นภาพเดียวกัน</span></h2>
          </div>
          <div className="about-v2-steps">
            {workSteps.map((step, index) => (
              <article key={step.title}>
                <div className="about-v2-step-icon"><AboutIcon name={step.icon} /></div>
                <small>STEP 0{index + 1}</small><h3>{step.title}</h3><p>{step.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="about-v2-values">
        <div className="about-v2-shell">
          <div className="about-v2-heading centered light">
            <p className="section-kicker">Our Standards</p>
            <h2>มาตรฐานที่พาโครงการ<br /><span>ไปถึงผลลัพธ์ที่ดี</span></h2>
          </div>
          <div className="about-v2-value-grid">
            {values.map((item) => <article key={item.title}><span><AboutIcon name={item.icon} /></span><h3>{item.title}</h3><p>{item.text}</p></article>)}
          </div>
        </div>
      </section>

      <section className="about-v2-trust">
        <div className="about-v2-shell about-v2-trust-grid">
          <div className="about-v2-trust-photo"><Image src={assetPath("/hero-construction.png")} alt="ทีมงานตรวจสอบรายละเอียดก่อนก่อสร้าง" fill sizes="(min-width: 1024px) 48vw, 100vw" /></div>
          <div className="about-v2-trust-copy">
            <p className="section-kicker">Building Trust</p>
            <h2>ความไว้ใจเกิดจาก<br /><span>รายละเอียดที่เราดูแล</span></h2>
            <p>เราไม่ได้วัดคุณภาพจากภาพตอนส่งมอบเพียงอย่างเดียว แต่ให้ความสำคัญกับวิธีทำงานระหว่างทางที่เจ้าของบ้านเข้าใจและติดตามได้</p>
            <ul>
              <li><AboutIcon name="check" /><span><strong>ขอบเขตชัดเจน</strong> สรุปสิ่งที่ทำและสิ่งที่ต้องตัดสินใจก่อนเริ่ม</span></li>
              <li><AboutIcon name="check" /><span><strong>วางแผนงบประมาณ</strong> มองผลกระทบของแบบและวัสดุร่วมกัน</span></li>
              <li><AboutIcon name="check" /><span><strong>ตรวจคุณภาพเป็นช่วง</strong> เก็บรายละเอียดก่อนเดินหน้าขั้นตอนถัดไป</span></li>
            </ul>
            <Link href="/services" className="about-v2-outline-button">ดูบริการของเรา <AboutIcon name="arrow" /></Link>
          </div>
        </div>
      </section>

      <section className="about-v2-milestones">
        <div className="about-v2-shell">
          <div className="about-v2-heading centered">
            <p className="section-kicker">Our Milestones</p>
            <h2>หมุดหมายที่กำหนด<br /><span>วิธีทำงานของเรา</span></h2>
          </div>
          <div className="about-v2-timeline">
            {milestones.map((item, index) => (
              <article key={item.year} className={index % 2 ? "reverse" : ""}>
                <div className="about-v2-timeline-image"><Image src={assetPath("/hero-construction.png")} alt="ขั้นตอนการทำงานของ 34 Build Master" fill sizes="360px" /></div>
                <span className="about-v2-timeline-dot">{item.year}</span>
                <div className="about-v2-timeline-copy"><h3>{item.title}</h3><p>{item.text}</p></div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="team" className="about-v2-team">
        <div className="about-v2-shell">
          <div className="about-v2-heading split">
            <div><p className="section-kicker">Meet Our Team</p><h2>ทีมที่อยู่เบื้องหลัง<br /><span>ทุกพื้นที่คุณภาพ</span></h2></div>
            <p>ความสำเร็จของโครงการเกิดจากคนหลายฝ่ายที่เข้าใจเป้าหมายเดียวกัน และสื่อสารต่อเนื่องตลอดทาง</p>
          </div>
          <div className="about-v2-team-grid">
            {team.map((member) => (
              <article key={member.role}>
                <div><Image src={assetPath("/hero-construction.png")} alt={member.role} fill sizes="(min-width: 768px) 31vw, 100vw" style={{ objectPosition: member.position }} /></div>
                <h3>{member.role}</h3><p>{member.detail}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="reviews" className="about-v2-testimonials">
        <div className="about-v2-shell">
          <div className="about-v2-heading centered light"><p className="section-kicker">Client Stories</p><h2>ประสบการณ์ที่ลูกค้า<br /><span>ส่งต่อถึงเรา</span></h2></div>
          <TestimonialsCarousel />
        </div>
      </section>

      <section className="about-v2-faq">
        <div className="about-v2-shell about-v2-faq-grid">
          <div className="about-v2-heading"><p className="section-kicker">Questions?</p><h2>คำถามก่อนเริ่ม<br /><span>คุยเรื่องบ้าน</span></h2><p>รวมคำตอบเบื้องต้นที่จะช่วยให้คุณเตรียมข้อมูลและเห็นขั้นตอนชัดขึ้น</p><Link href="/faq" className="about-v2-outline-button">ดูคำถามทั้งหมด <AboutIcon name="arrow" /></Link></div>
          <div className="about-v2-faq-list">
            {faqs.map(([question, answer], index) => <details key={question} open={index === 0}><summary>{question}<i /></summary><p>{answer}</p></details>)}
          </div>
        </div>
      </section>

      <ContactBand />
      <SiteFooter />
    </main>
  );
}
