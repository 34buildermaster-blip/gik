"use client";

import Image from "next/image";
import Link from "next/link";
import dynamic from "next/dynamic";
import { FormEvent, useState } from "react";
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
import { assetPath } from "@/lib/asset-path";
import { getPublicApiBaseUrl } from "@/lib/public-api-url";
import { BrandLogoImage } from "@/components/brand-logo-image";
import { SiteHeader } from "@/components/site-chrome";
import { CookieSettingsButton } from "@/components/cookie-consent";
import { useSiteSettings } from "@/contexts/site-settings-context";
import ApproachCarousel from "./ApproachCarousel";
import ClientExperienceCarousel from "./ClientExperienceCarousel";
import HeroCarousel from "./HeroCarousel";
import styles from "./page.module.css";

const HomeMotion = dynamic(() => import("./HomeMotion"), { ssr: false });

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
    <div className={styles.sectionHeading} data-gsap-heading>
      <span>{eyebrow}</span>
      <h2>{title}</h2>
      {copy ? <p>{copy}</p> : null}
    </div>
  );
}

export default function HomePreviewPage() {
  const settings = useSiteSettings();
  const [isSubmittingLead, setIsSubmittingLead] = useState(false);
  const [leadFeedback, setLeadFeedback] = useState<{ type: "success" | "error"; message: string } | null>(null);

  async function submitLead(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setLeadFeedback(null);
    setIsSubmittingLead(true);

    const form = event.currentTarget;
    const data = new FormData(form);

    try {
      const response = await fetch(`${getPublicApiBaseUrl()}/api/contact-leads`, {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({
          name: data.get("name"),
          phone: data.get("phone"),
          email: data.get("email") || null,
          service_type: data.get("service_type") || null,
          message: data.get("message") || null,
          source_url: window.location.href,
          website: data.get("website") || null,
        }),
      });
      const payload = (await response.json().catch(() => ({}))) as { message?: string; errors?: Record<string, string[]> };

      if (!response.ok) {
        const firstError = payload.errors ? Object.values(payload.errors)[0]?.[0] : null;
        throw new Error(firstError || payload.message || "ไม่สามารถส่งข้อมูลได้ กรุณาลองใหม่อีกครั้ง");
      }

      form.reset();
      setLeadFeedback({ type: "success", message: payload.message || "ส่งข้อมูลเรียบร้อยแล้ว ทีมงานจะติดต่อกลับโดยเร็วที่สุด" });
    } catch (error) {
      setLeadFeedback({ type: "error", message: error instanceof Error ? error.message : "ไม่สามารถส่งข้อมูลได้ กรุณาลองใหม่อีกครั้ง" });
    } finally {
      setIsSubmittingLead(false);
    }
  }

  return (
    <main className={styles.page} data-home-motion-root>
      <HomeMotion />
      <SiteHeader overlay />

      <HeroCarousel />

      <section className={styles.promiseStrip} aria-label="จุดเด่นบริการ" data-gsap-stagger="promise">
        <span data-gsap-item><Ruler size={19} /> ออกแบบตามการใช้งานจริง</span>
        <span data-gsap-item><ShieldCheck size={19} /> ควบคุมคุณภาพเป็นขั้นตอน</span>
        <span data-gsap-item><Building2 size={19} /> ดูแลครบตั้งแต่ต้นจนจบ</span>
        <span data-gsap-item><MessageCircle size={19} /> ติดตามความคืบหน้าได้</span>
      </section>

      <section className={styles.introSection}>
        <div className={styles.introCopy} data-gsap-copy>
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
          <div className={styles.serviceGrid} data-gsap-stagger="cards">
            {services.map((service) => {
              const ServiceIcon = service.icon;
              return (
                <Link href="/services" className={styles.serviceCard} key={service.number} data-gsap-item>
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
          <div className={styles.projectGrid} data-gsap-stagger="projects">
            <article className={`${styles.projectCard} ${styles.projectMain}`} data-gsap-item>
              <Image src={assetPath("/selected-projects/tropical-japandi-exterior.webp")} alt="บ้าน Tropical Japandi Luxury ยามค่ำพร้อมสระว่ายน้ำ" fill sizes="70vw" />
              <div><span>RESIDENTIAL · CHIANG MAI</span><h3>บ้านพักอาศัยร่วมสมัย</h3><p>ออกแบบและก่อสร้าง</p></div>
            </article>
            <article className={styles.projectCard} data-gsap-item>
              <Image src={assetPath("/selected-projects/tropical-japandi-living.webp")} alt="พื้นที่พักผ่อนภายในสไตล์ Tropical Japandi Luxury" fill sizes="35vw" />
              <div><span>INTERIOR · MATERIAL</span><h3>พื้นที่ภายในและบิวท์อิน</h3><p>ออกแบบรายละเอียดวัสดุ</p></div>
            </article>
            <article className={`${styles.projectCard} ${styles.projectTextCard}`} data-gsap-item>
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
          <div className={styles.processGrid} data-gsap-stagger="process">
            <i className={styles.processLine} data-gsap-line aria-hidden="true" />
            {process.map(([number, title, detail]) => (
              <article key={number} data-gsap-item>
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
          <div className={styles.brandGrid} data-gsap-stagger="brands">
            {materialBrands.map((brand) => (
              <div className={styles.materialBrand} key={brand.name} data-gsap-item>
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
        <div className={styles.contactImage} data-gsap-media data-gsap-parallax>
          <Image src={assetPath("/contact/tropical-japandi-contact.webp")} alt="บ้าน Tropical Japandi Luxury สำหรับเริ่มต้นปรึกษาโครงการกับ 34 Build Master" fill sizes="50vw" />
          <div><span>START YOUR PROJECT</span><h2>เริ่มต้นจากการคุยกัน<br />อย่างเข้าใจ</h2></div>
        </div>
        <form className={styles.contactForm} onSubmit={submitLead} data-gsap-form>
          <p className={styles.eyebrow}>CONTACT US</p>
          <h2>{settings.cta.contact_heading}</h2>
          <p>{settings.cta.contact_description}</p>
          <label>ชื่อ-นามสกุล<input name="name" placeholder="ชื่อของคุณ" minLength={2} maxLength={120} required /></label>
          <div className={styles.formRow}>
            <label>เบอร์โทรศัพท์<input name="phone" type="tel" placeholder="08X-XXX-XXXX" minLength={8} maxLength={30} required /></label>
            <label>ประเภทงาน<select name="service_type" defaultValue=""><option value="" disabled>เลือกประเภทงาน</option><option>ออกแบบบ้าน</option><option>สร้างบ้าน</option><option>รีโนเวท</option><option>บิวท์อิน</option></select></label>
          </div>
          <label>อีเมล (ไม่บังคับ)<input name="email" type="email" placeholder="อีเมลสำหรับรับข้อมูลเพิ่มเติม" maxLength={255} /></label>
          <label>รายละเอียด<textarea name="message" rows={4} maxLength={5000} placeholder="พื้นที่ งบประมาณ หรือสิ่งที่ต้องการปรึกษา" /></label>
          <label hidden aria-hidden="true">เว็บไซต์<input name="website" tabIndex={-1} autoComplete="off" /></label>
          {leadFeedback ? <p className={leadFeedback.type === "success" ? styles.formSuccess : styles.formError} role="status">{leadFeedback.message}</p> : null}
          <button className={styles.primaryButton} type="submit" disabled={isSubmittingLead}>{isSubmittingLead ? "กำลังส่งข้อมูล..." : "ส่งข้อมูลให้ทีมงาน"} <ArrowRight size={18} /></button>
        </form>
      </section> : null}

      <footer className={styles.footer}>
        <div className={styles.footerMain} data-gsap-stagger="footer">
          <div className={styles.footerBrand} data-gsap-item>
            <BrandMark footer />
            <strong className={styles.footerSlogan}>DESIGN WITH PURPOSE.<br />BUILD WITH CARE.</strong>
            <p>สร้างบ้านไม่ใช่เพียงงานก่อสร้าง แต่คือการดูแลทุกรายละเอียด เพื่อส่งมอบพื้นที่ที่เหมาะกับชีวิตของเจ้าของบ้านจริง ๆ</p>
          </div>

          <div className={styles.footerColumn} data-gsap-item>
            <strong>เมนูหลัก</strong>
            <Link href="/">หน้าหลัก</Link>
            <Link href="/about">เกี่ยวกับเรา</Link>
            {settings.display.show_home_projects ? <a href="#projects">ผลงานของเรา</a> : null}
            {settings.navigation.show_blog ? <Link href="/blog">บทความ</Link> : null}
            {settings.display.show_home_contact ? <a href="#contact">ติดต่อเรา</a> : null}
          </div>

          <div className={styles.footerColumn} data-gsap-item>
            <strong>บริการครบวงจร</strong>
            <Link href="/services">ออกแบบบ้าน</Link>
            <Link href="/services">รับเหมาก่อสร้าง</Link>
            <Link href="/services">รีโนเวทและปรับปรุง</Link>
            <Link href="/services">ตกแต่งภายในและบิวท์อิน</Link>
            <Link href="/services">ปรึกษาและวางแผนโครงการ</Link>
          </div>

          <div className={styles.footerColumn} data-gsap-item>
            <strong>ข้อมูลสำคัญ</strong>
            {settings.navigation.show_updates ? <Link href="/updates">อัปเดตหน้างาน</Link> : null}
            {settings.navigation.show_faq ? <Link href="/faq">คำถามที่พบบ่อย</Link> : null}
            {settings.navigation.show_blog ? <Link href="/blog">ความรู้เรื่องบ้าน</Link> : null}
            <Link href="/login">ระบบติดตามโครงการ</Link>
          </div>

          <div className={`${styles.footerColumn} ${styles.footerContact}`} data-gsap-item>
            <strong>ติดต่อเรา</strong>
            <span><MapPin size={17} />{settings.general.address}</span>
            <a href={settings.general.phone_href}><Phone size={17} />{settings.general.phone_display}</a>
            <a href={`mailto:${settings.general.email}`}><Mail size={17} />{settings.general.email}</a>
            <span><Clock3 size={17} />{settings.general.business_hours}</span>
          </div>
        </div>

        <div className={styles.footerBottom}>
          <nav aria-label="ข้อมูลด้านกฎหมาย"><Link href="/privacy-policy">นโยบายความเป็นส่วนตัว</Link><Link href="/terms-of-service">ข้อกำหนดการใช้งาน</Link><Link href="/cookie-policy">นโยบายคุกกี้</Link><CookieSettingsButton className={styles.footerLegalButton} />{settings.navigation.show_faq ? <Link href="/faq">คำถามที่พบบ่อย</Link> : null}</nav>
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
