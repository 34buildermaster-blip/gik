"use client";

import Image from "next/image";
import Link from "next/link";
import {
  ArrowRight,
  ArrowUpRight,
  Building2,
  ClipboardCheck,
  Clock3,
  Hammer,
  Handshake,
  House,
  Layers3,
  Mail,
  MapPin,
  MessageCircle,
  Paintbrush,
  Phone,
  Ruler,
  ShieldCheck,
} from "lucide-react";
import { FaFacebookF, FaInstagram, FaLine, FaTiktok } from "react-icons/fa6";
import { assetPath, sitePath } from "@/lib/asset-path";
import { BrandLogoImage } from "@/components/brand-logo-image";
import { SiteHeader } from "@/components/site-chrome";
import { useSiteSettings } from "@/contexts/site-settings-context";
import ApproachCarousel from "./ApproachCarousel";
import ClientExperienceCarousel from "./ClientExperienceCarousel";
import HeroCarousel from "./HeroCarousel";
import styles from "./page.module.css";

const services = [
  {
    number: "01",
    title: "ออกแบบบ้าน",
    description: "วางฟังก์ชัน รูปแบบ และงบประมาณให้สอดคล้องกับชีวิตจริงก่อนเริ่มก่อสร้าง",
    icon: Ruler,
  },
  {
    number: "02",
    title: "สร้างบ้าน",
    description: "บริหารงานก่อสร้างเป็นขั้นตอน พร้อมควบคุมคุณภาพและอัปเดตความคืบหน้า",
    icon: House,
  },
  {
    number: "03",
    title: "รีโนเวท",
    description: "ปรับโครงสร้าง ระบบ และพื้นที่เดิมให้กลับมาใช้งานได้ดีและเหมาะกับปัจจุบัน",
    icon: Hammer,
  },
  {
    number: "04",
    title: "บิวท์อิน",
    description: "ออกแบบงานภายในและเฟอร์นิเจอร์ให้ต่อเนื่องกับพื้นที่ วัสดุ และตัวตนของบ้าน",
    icon: Layers3,
  },
  {
    number: "05",
    title: "ควบคุมงานก่อสร้าง",
    description: "ตรวจคุณภาพ วางลำดับงาน และประสานทีมช่างให้ทุกขั้นตอนเดินตามแผนที่กำหนด",
    icon: ClipboardCheck,
  },
  {
    number: "06",
    title: "ที่ปรึกษาโครงการ",
    description: "ช่วยประเมินแนวทาง งบประมาณ และความเป็นไปได้ก่อนตัดสินใจเริ่มโครงการจริง",
    icon: Handshake,
  },
];

const process = [
  ["01", "รับโจทย์", "คุยความต้องการ งบประมาณ และบริบทของพื้นที่"],
  ["02", "สำรวจและออกแบบ", "วัดพื้นที่ วางแนวทาง และสรุปรายละเอียดงาน"],
  ["03", "วางแผนและก่อสร้าง", "จัดลำดับงาน คุมทีมช่าง และตรวจคุณภาพ"],
  ["04", "ตรวจรับและส่งมอบ", "ตรวจความเรียบร้อย แก้ไข และส่งมอบอย่างเป็นระบบ"],
];

type MaterialBrand = {
  name: string;
  category: string;
  logo: string;
  fit?: "square" | "conwood";
};

const materialBrands: MaterialBrand[] = [
  { name: "SCG", category: "วัสดุก่อสร้าง", logo: assetPath("/brands/scg.svg") },
  { name: "TOA", category: "สีและสารเคลือบ", logo: assetPath("/brands/toa.png") },
  { name: "HAFELE", category: "ฮาร์ดแวร์และฟิตติ้ง", logo: assetPath("/brands/hafele.svg") },
  { name: "CPAC", category: "คอนกรีตและหลังคา", logo: assetPath("/brands/cpac.svg") },
  { name: "SHERA", category: "ไฟเบอร์ซีเมนต์", logo: assetPath("/brands/shera.svg") },
  { name: "CONWOOD", category: "วัสดุทดแทนไม้", logo: assetPath("/brands/conwood.png"), fit: "conwood" },
  { name: "COTTO", category: "สุขภัณฑ์และกระเบื้อง", logo: assetPath("/brands/cotto.png"), fit: "square" },
  { name: "AMERICAN STANDARD", category: "สุขภัณฑ์", logo: assetPath("/brands/american-standard.webp"), fit: "square" },
  { name: "BEGER", category: "สีและผลิตภัณฑ์ปกป้องพื้นผิว", logo: assetPath("/brands/beger.webp") },
  { name: "TOTO", category: "สุขภัณฑ์", logo: assetPath("/brands/toto.svg?v=2") },
];

function BrandMark({ footer = false }: { footer?: boolean }) {
  const settings = useSiteSettings();
  const customLogo = (footer ? settings.branding.footer_logo_url : null) || settings.branding.logo_url;

  return (
    <span className={styles.brand}>
      {customLogo ? (
        // The media host is configured by the Laravel backend at runtime.
        // eslint-disable-next-line @next/next/no-img-element
        <img className={styles.brandLogoImage} src={customLogo} alt={`โลโก้ ${settings.general.company_name_en}`} />
      ) : (
        <BrandLogoImage className={styles.brandLogoImage} sizes="48px" priority={!footer} />
      )}
      <span>
        <strong>BUILD MASTER</strong>
        <small>CONSTRUCTION</small>
      </span>
    </span>
  );
}

function SectionHeading({ eyebrow, title, copy }: { eyebrow: string; title: string; copy?: string }) {
  return (
    <div className={styles.sectionHeading}>
      <span>{eyebrow}</span>
      <h2>{title}</h2>
      {copy ? <p>{copy}</p> : null}
    </div>
  );
}

export default function HomePreviewPage() {
  const settings = useSiteSettings();

  return (
    <main className={styles.page}>
      <SiteHeader overlay />

      <HeroCarousel />

      <section className={styles.promiseStrip} aria-label="จุดเด่นบริการ">
        <span><Ruler size={19} /> ออกแบบตามการใช้งานจริง</span>
        <span><ShieldCheck size={19} /> ควบคุมคุณภาพเป็นขั้นตอน</span>
        <span><Building2 size={19} /> ดูแลครบตั้งแต่ต้นจนจบ</span>
        <span><MessageCircle size={19} /> ติดตามความคืบหน้าได้</span>
      </section>

      <section className={styles.introSection}>
        <div className={styles.introCopy}>
          <p className={styles.eyebrow}>ABOUT OUR APPROACH</p>
          <h2>บ้านที่ดีเริ่มจากการเข้าใจ<br />สิ่งที่เจ้าของบ้านต้องการจริง ๆ</h2>
          <div>
            <p>เราเริ่มทุกโครงการด้วยการฟัง วางแผน และจัดลำดับความสำคัญ เพื่อให้งบประมาณ รูปแบบ และการใช้งานเดินไปในทิศทางเดียวกัน</p>
            <Link href="/about">รู้จักแนวทางของเรา <ArrowRight size={17} /></Link>
          </div>
        </div>
        <ApproachCarousel />
      </section>

      {settings.display.show_home_services ? <section className={styles.servicesSection} id="services">
        <div className={styles.contentWidth}>
          <div className={styles.servicesHeading}>
            <SectionHeading
              eyebrow="OUR SERVICES"
              title="บริการครบทุกขั้นตอน เพื่อสร้างพื้นที่ในแบบของคุณ"
              copy="ลดความซับซ้อนของงานก่อสร้างด้วยทีมที่ดูแลทั้งภาพรวมและรายละเอียด ตั้งแต่คำปรึกษาแรกจนถึงวันส่งมอบ"
            />
            <a className={styles.servicesCta} href="#contact">เริ่มต้นปรึกษา <ArrowUpRight size={17} /></a>
          </div>
          <div className={styles.serviceGrid}>
            {services.map((service) => {
              const ServiceIcon = service.icon;
              return (
                <Link href="/services" className={styles.serviceCard} key={service.number}>
                  <div className={styles.serviceCardTop}>
                    <span className={styles.serviceIcon}><ServiceIcon size={23} /></span>
                    <span className={styles.serviceNumber}>{service.number}</span>
                  </div>
                  <h3>{service.title}</h3>
                  <p>{service.description}</p>
                  <span className={styles.serviceCardLink}>ดูรายละเอียด <ArrowUpRight size={17} /></span>
                </Link>
              );
            })}
          </div>
        </div>
      </section> : null}

      {settings.display.show_home_projects ? <section className={styles.projectsSection} id="projects">
        <div className={styles.contentWidth}>
          <div className={styles.projectsHeading}>
            <SectionHeading eyebrow="SELECTED PROJECTS" title="ผลงานที่สะท้อนวิธีคิดและมาตรฐานของเรา" />
            <Link href="/updates">ดูผลงานและอัปเดตทั้งหมด <ArrowRight size={18} /></Link>
          </div>
          <div className={styles.projectGrid}>
            <article className={`${styles.projectCard} ${styles.projectMain}`}>
              <Image src={assetPath("/selected-projects/tropical-japandi-exterior.webp")} alt="บ้าน Tropical Japandi Luxury ยามค่ำพร้อมสระว่ายน้ำ" fill sizes="70vw" />
              <div><span>RESIDENTIAL · CHIANG MAI</span><h3>บ้านพักอาศัยร่วมสมัย</h3><p>ออกแบบและก่อสร้าง</p></div>
            </article>
            <article className={styles.projectCard}>
              <Image src={assetPath("/selected-projects/tropical-japandi-living.webp")} alt="พื้นที่พักผ่อนภายในสไตล์ Tropical Japandi Luxury" fill sizes="35vw" />
              <div><span>INTERIOR · MATERIAL</span><h3>พื้นที่ภายในและบิวท์อิน</h3><p>ออกแบบรายละเอียดวัสดุ</p></div>
            </article>
            <article className={`${styles.projectCard} ${styles.projectTextCard}`}>
              <Paintbrush size={30} />
              <span>RENOVATION</span>
              <h3>เปลี่ยนพื้นที่เดิม<br />ให้กลับมาตอบโจทย์อีกครั้ง</h3>
              <Link href="/services">ดูบริการรีโนเวท <ArrowRight size={18} /></Link>
            </article>
          </div>
        </div>
      </section> : null}

      {settings.display.show_home_process ? <section className={styles.processSection}>
        <div className={styles.contentWidth}>
          <SectionHeading eyebrow="WORK PROCESS" title="ขั้นตอนชัดเจน เพื่อให้ทุกการตัดสินใจง่ายขึ้น" />
          <div className={styles.processGrid}>
            {process.map(([number, title, detail]) => (
              <article key={number}>
                <span>{number}</span>
                <h3>{title}</h3>
                <p>{detail}</p>
              </article>
            ))}
          </div>
        </div>
      </section> : null}

      {settings.display.show_home_partners ? <section className={styles.brandsSection}>
        <div className={styles.contentWidth}>
          <SectionHeading
            eyebrow="MATERIAL BRANDS"
            title="เลือกใช้วัสดุจากแบรนด์ที่เป็นที่ยอมรับ"
            copy="ตัวอย่างแบรนด์วัสดุและระบบที่สามารถนำมาพิจารณาให้เหมาะกับงบประมาณและรายละเอียดของแต่ละโครงการ"
          />
          <div className={styles.brandGrid}>
            {materialBrands.map((brand) => (
              <div className={styles.materialBrand} key={brand.name}>
                <div className={`${styles.brandLogo} ${brand.fit === "square" ? styles.squareLogo : ""} ${brand.fit === "conwood" ? styles.conwoodLogo : ""}`}>
                  <Image src={brand.logo} alt={`โลโก้ ${brand.name}`} fill unoptimized sizes="(max-width: 620px) 38vw, (max-width: 1120px) 30vw, 160px" />
                </div>
                <span>{brand.category}</span>
              </div>
            ))}
          </div>
          <p className={styles.brandNote}>* รายชื่อใช้สำหรับแสดงแนวทาง UI เบื้องต้น การเลือกใช้จริงขึ้นอยู่กับสเปกของแต่ละโครงการ</p>
        </div>
      </section> : null}

      {settings.display.show_home_reviews ? <section className={styles.reviewSection}>
        <div className={styles.contentWidth}>
          <SectionHeading eyebrow="CLIENT EXPERIENCE" title="ความมั่นใจที่เกิดจากการสื่อสารอย่างตรงไปตรงมา" />
        </div>
        <ClientExperienceCarousel />
      </section> : null}

      {settings.display.show_home_contact ? <section className={styles.contactSection} id="contact">
        <div className={styles.contactImage}>
          <Image src={assetPath("/contact/tropical-japandi-contact.webp")} alt="บ้าน Tropical Japandi Luxury สำหรับเริ่มต้นปรึกษาโครงการกับ 34 Build Master" fill sizes="50vw" />
          <div><span>START YOUR PROJECT</span><h2>เริ่มต้นจากการคุยกัน<br />อย่างเข้าใจ</h2></div>
        </div>
        <form className={styles.contactForm} action={sitePath("/contact")} method="get">
          <p className={styles.eyebrow}>CONTACT US</p>
          <h2>{settings.cta.contact_heading}</h2>
          <p>{settings.cta.contact_description}</p>
          <label>ชื่อ-นามสกุล<input name="name" placeholder="ชื่อของคุณ" required /></label>
          <div className={styles.formRow}>
            <label>เบอร์โทรศัพท์<input name="phone" placeholder="08X-XXX-XXXX" required /></label>
            <label>ประเภทงาน<select name="service" defaultValue=""><option value="" disabled>เลือกประเภทงาน</option><option>ออกแบบบ้าน</option><option>สร้างบ้าน</option><option>รีโนเวท</option><option>บิวท์อิน</option></select></label>
          </div>
          <label>รายละเอียด<textarea name="detail" rows={4} placeholder="พื้นที่ งบประมาณ หรือสิ่งที่ต้องการปรึกษา" /></label>
          <button className={styles.primaryButton} type="submit">ส่งข้อมูลให้ทีมงาน <ArrowRight size={18} /></button>
        </form>
      </section> : null}

      <footer className={styles.footer}>
        <div className={styles.footerMain}>
          <div className={styles.footerBrand}>
            <BrandMark footer />
            <strong className={styles.footerSlogan}>DESIGN WITH PURPOSE.<br />BUILD WITH CARE.</strong>
            <p>สร้างบ้านไม่ใช่เพียงงานก่อสร้าง แต่คือการดูแลทุกรายละเอียด เพื่อส่งมอบพื้นที่ที่เหมาะกับชีวิตของเจ้าของบ้านจริง ๆ</p>
          </div>

          <div className={styles.footerColumn}>
            <strong>เมนูหลัก</strong>
            <Link href="/">หน้าหลัก</Link>
            <Link href="/about">เกี่ยวกับเรา</Link>
            {settings.display.show_home_projects ? <a href="#projects">ผลงานของเรา</a> : null}
            {settings.navigation.show_blog ? <Link href="/blog">บทความ</Link> : null}
            {settings.display.show_home_contact ? <a href="#contact">ติดต่อเรา</a> : null}
          </div>

          <div className={styles.footerColumn}>
            <strong>บริการครบวงจร</strong>
            <Link href="/services">ออกแบบบ้าน</Link>
            <Link href="/services">รับเหมาก่อสร้าง</Link>
            <Link href="/services">รีโนเวทและปรับปรุง</Link>
            <Link href="/services">ตกแต่งภายในและบิวท์อิน</Link>
            <Link href="/services">ปรึกษาและวางแผนโครงการ</Link>
          </div>

          <div className={styles.footerColumn}>
            <strong>ข้อมูลสำคัญ</strong>
            {settings.navigation.show_updates ? <Link href="/updates">อัปเดตหน้างาน</Link> : null}
            {settings.navigation.show_faq ? <Link href="/faq">คำถามที่พบบ่อย</Link> : null}
            {settings.navigation.show_blog ? <Link href="/blog">ความรู้เรื่องบ้าน</Link> : null}
            <Link href="/login">ระบบติดตามโครงการ</Link>
          </div>

          <div className={`${styles.footerColumn} ${styles.footerContact}`}>
            <strong>ติดต่อเรา</strong>
            <span><MapPin size={17} />{settings.general.address}</span>
            <a href={settings.general.phone_href}><Phone size={17} />{settings.general.phone_display}</a>
            <a href={`mailto:${settings.general.email}`}><Mail size={17} />{settings.general.email}</a>
            <span><Clock3 size={17} />{settings.general.business_hours}</span>
          </div>
        </div>

        <div className={styles.footerBottom}>
          <nav aria-label="ข้อมูลด้านกฎหมาย"><Link href="/contact">นโยบายความเป็นส่วนตัว</Link><Link href="/contact">ข้อกำหนดการใช้งาน</Link>{settings.navigation.show_faq ? <Link href="/faq">คำถามที่พบบ่อย</Link> : null}</nav>
          <span>{settings.general.copyright}</span>
          <div className={styles.socials}>
            {settings.social.facebook_url ? <a href={settings.social.facebook_url} aria-label="Facebook" title="Facebook" target="_blank" rel="noreferrer"><FaFacebookF /></a> : null}
            {settings.social.line_url ? <a href={settings.social.line_url} aria-label="LINE OA" title="LINE OA" target="_blank" rel="noreferrer"><FaLine /></a> : null}
            {settings.social.tiktok_url ? <a href={settings.social.tiktok_url} aria-label="TikTok" title="TikTok" target="_blank" rel="noreferrer"><FaTiktok /></a> : null}
            {settings.social.instagram_url ? <a href={settings.social.instagram_url} aria-label="Instagram" title="Instagram" target="_blank" rel="noreferrer"><FaInstagram /></a> : null}
          </div>
        </div>
      </footer>
    </main>
  );
}
