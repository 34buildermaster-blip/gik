"use client";

import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, Mail, MapPin, Menu, Phone, Send } from "lucide-react";
import { FaFacebookF, FaInstagram, FaLine, FaTiktok } from "react-icons/fa6";
import { BrandLogoImage } from "@/components/brand-logo-image";
import { useSiteSettings } from "@/contexts/site-settings-context";
import { assetPath } from "@/lib/asset-path";

const navLinks = [
  { href: "/", label: "หน้าหลัก" },
  { href: "/about", label: "เกี่ยวกับเรา" },
  { href: "/services", label: "บริการ" },
  { href: "/house-designs", label: "แบบบ้าน" },
  { href: "/updates", label: "อัปเดตงาน" },
  { href: "/blog", label: "บทความ" },
  { href: "/faq", label: "FAQ" },
  { href: "/contact", label: "ติดต่อ" },
];

const serviceLinks = ["ออกแบบบ้าน", "รีโนเวทบ้าน", "สร้างบ้าน", "บิวท์อิน"];

const socialIconMap = {
  facebook: FaFacebookF,
  instagram: FaInstagram,
  line: FaLine,
  tiktok: FaTiktok,
};

function SiteLogo({ footer = false, className }: { footer?: boolean; className: string }) {
  const settings = useSiteSettings();
  const customLogo = (footer ? settings.branding.footer_logo_url : null) || settings.branding.logo_url;

  if (customLogo) {
    // The logo URL is managed by the Laravel media library and can use a deployment-specific host.
    // eslint-disable-next-line @next/next/no-img-element
    return <img className={className} src={customLogo} alt={`โลโก้ ${settings.general.company_name_en}`} />;
  }

  return <BrandLogoImage className={className} sizes={footer ? "56px" : "44px"} priority={!footer} />;
}

function SocialLinks({ dark = false }: { dark?: boolean }) {
  const settings = useSiteSettings();
  const links = [
    { label: "Facebook", href: settings.social.facebook_url, icon: "facebook" as const },
    { label: "Instagram", href: settings.social.instagram_url, icon: "instagram" as const },
    { label: "Line", href: settings.social.line_url, icon: "line" as const },
    { label: "TikTok", href: settings.social.tiktok_url, icon: "tiktok" as const },
  ].filter((social) => social.href);

  return (
    <div className="flex items-center gap-2">
      {links.map((social) => {
        const SocialIcon = socialIconMap[social.icon];
        return (
          <a
            key={social.label}
            href={social.href}
            aria-label={social.label}
            target="_blank"
            rel="noreferrer"
            className={`grid size-10 place-items-center rounded-full border transition duration-200 hover:-translate-y-0.5 ${
              dark
                ? "border-white/18 text-white/72 hover:border-white/45 hover:text-white"
                : "border-[#d8ded9] text-[#334139] hover:border-[#0f6b45] hover:bg-[#0f6b45] hover:text-white"
            }`}
          >
            <SocialIcon className="size-4" />
          </a>
        );
      })}
    </div>
  );
}

export function PageHero({
  title,
  currentLabel,
  parentLabel = "หน้าหลัก",
  parentHref = "/",
  size = "default",
}: {
  title: string;
  currentLabel: string;
  parentLabel?: string;
  parentHref?: string;
  size?: "default" | "compact";
}) {
  return (
    <section className="relative isolate min-h-[310px] overflow-hidden border-b border-[#dfe4e0] bg-[#f1f3f1] px-5 py-16 lg:px-8">
      <div className="absolute inset-y-0 right-0 -z-20 w-full sm:w-[62%] lg:w-[54%]">
        <Image
          src={assetPath("/approach-homes/contemporary.jpg")}
          alt="ผลงานบ้านสมัยใหม่ของ 34 Build Master Construction"
          fill
          priority
          sizes="(min-width: 1024px) 54vw, 100vw"
          className="object-cover object-center"
        />
      </div>
      <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,#f1f3f1_0%,rgba(241,243,241,0.98)_42%,rgba(241,243,241,0.7)_68%,rgba(241,243,241,0.22)_100%)]" />
      <div className="mx-auto flex min-h-[180px] max-w-7xl flex-col justify-center">
        <p className="text-sm font-semibold uppercase tracking-[0.18em] text-[#0f6b45]">34 Build Master Construction</p>
        <h1 className={`${size === "compact" ? "max-w-4xl text-4xl sm:text-5xl" : "text-5xl sm:text-6xl"} mt-4 font-semibold leading-[1.15] text-[#17211c]`}>
          {title}
        </h1>
        <div className="mt-6 flex flex-wrap items-center gap-2 text-base font-medium text-[#667169]">
          <Link href={parentHref} className="transition hover:text-[#0f6b45]">{parentLabel}</Link>
          <span aria-hidden="true">/</span>
          <span className="text-[#0f6b45]">{currentLabel}</span>
        </div>
      </div>
    </section>
  );
}

export function SiteHeader() {
  const settings = useSiteSettings();
  const visibleNavLinks = navLinks.filter((item) => {
    if (item.href === "/house-designs") return settings.navigation.show_house_designs;
    if (item.href === "/updates") return settings.navigation.show_updates;
    if (item.href === "/blog") return settings.navigation.show_blog;
    if (item.href === "/faq") return settings.navigation.show_faq;
    return true;
  });

  return (
    <header className="sticky top-0 z-50 border-b border-black/6 bg-white/88 text-[#17211c] shadow-[0_8px_32px_rgba(18,34,25,0.05)] backdrop-blur-xl">
      <div className="mx-auto flex min-h-[76px] max-w-7xl items-center justify-between gap-4 px-5 lg:px-8">
        <Link href="/" className="flex min-w-0 items-center gap-3" aria-label={settings.general.company_name_en}>
          <SiteLogo className="size-11 shrink-0 rounded-md object-cover" />
          <span className="min-w-0 leading-tight">
            <span className="block truncate text-sm font-semibold uppercase tracking-[0.1em] sm:text-base">Build Master</span>
            <span className="block truncate text-[10px] font-medium uppercase tracking-[0.18em] text-[#0f6b45] sm:text-[11px]">Construction</span>
          </span>
        </Link>

        <nav className="hidden items-center gap-5 text-[15px] font-medium text-[#4f5b53] lg:flex">
          {visibleNavLinks.map((item) => (
            <Link key={item.href} href={item.href} className="transition hover:text-[#0f6b45]">{item.label}</Link>
          ))}
        </nav>

        <div className="hidden items-center gap-3 xl:flex">
          <SocialLinks />
          <a href={settings.general.phone_href} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-full bg-[#0f6b45] px-5 text-sm font-semibold text-white transition hover:bg-[#0a5335]">
            <Phone className="size-4" />
            {settings.cta.consultation_label}
          </a>
        </div>

        <details className="relative lg:hidden">
          <summary className="grid size-11 cursor-pointer list-none place-items-center rounded-full border border-[#d8ded9] bg-white text-[#17211c] outline-none focus-visible:border-[#0f6b45] focus-visible:ring-2 focus-visible:ring-[#0f6b45]/20 [&::-webkit-details-marker]:hidden">
            <span className="sr-only">เปิดเมนู</span>
            <Menu className="size-5" />
          </summary>
          <div className="absolute right-0 top-14 w-[min(88vw,340px)] overflow-hidden rounded-lg border border-[#dfe4e0] bg-white p-3 shadow-[0_24px_70px_rgba(18,34,25,0.16)]">
            <nav className="grid text-base font-medium text-[#334139]">
              {visibleNavLinks.map((item) => (
                <Link key={item.href} href={item.href} className="rounded-md px-4 py-3 transition hover:bg-[#edf5f0] hover:text-[#0f6b45]">{item.label}</Link>
              ))}
            </nav>
            <div className="mt-3 flex items-center justify-between gap-3 border-t border-[#e4e8e5] pt-3">
              <SocialLinks />
              <a href={settings.general.phone_href} className="grid size-10 place-items-center rounded-full bg-[#0f6b45] text-white" aria-label={settings.cta.consultation_label}>
                <Phone className="size-4" />
              </a>
            </div>
          </div>
        </details>
      </div>
    </header>
  );
}

export function ContactForm() {
  const settings = useSiteSettings();
  return (
    <form className="grid gap-5 rounded-lg border border-[#dfe4e0] bg-white p-6 shadow-[0_20px_70px_rgba(18,34,25,0.07)] md:p-8" action={`mailto:${settings.general.email}`} method="post" encType="text/plain">
      <div className="grid gap-5 sm:grid-cols-2">
        <label className="modern-form-field"><span>ชื่อผู้ติดต่อ</span><input name="name" type="text" placeholder="ชื่อของคุณ" /></label>
        <label className="modern-form-field"><span>เบอร์โทร</span><input name="phone" type="tel" placeholder="เบอร์ที่สะดวกให้ติดต่อกลับ" /></label>
      </div>
      <label className="modern-form-field">
        <span>ประเภทงาน</span>
        <select name="service" defaultValue="">
          <option value="" disabled>เลือกประเภทงาน</option>
          {serviceLinks.map((service) => <option key={service}>{service}</option>)}
        </select>
      </label>
      <label className="modern-form-field"><span>รายละเอียดเบื้องต้น</span><textarea name="detail" placeholder="เล่าพื้นที่ งบประมาณคร่าว ๆ หรือสิ่งที่อยากทำ" /></label>
      <button className="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-[#0f6b45] px-7 font-semibold text-white transition hover:bg-[#0a5335]" type="submit">
        <Send className="size-5" />
        ส่งรายละเอียด
      </button>
    </form>
  );
}

export function ContactBand() {
  const settings = useSiteSettings();
  return (
    <section className="border-y border-[#dfe4e0] bg-[#f1f3f1] px-5 py-20 lg:px-8 lg:py-24">
      <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-stretch">
        <div className="relative min-h-[430px] overflow-hidden rounded-lg bg-[#173427]">
          <Image src={assetPath("/approach-homes/natural-modern.jpg")} alt="พูดคุยวางแผนโครงการกับ 34 Build Master" fill sizes="(min-width: 1024px) 46vw, 100vw" className="object-cover" />
          <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(10,34,23,0.06),rgba(10,34,23,0.86))]" />
          <div className="absolute inset-x-0 bottom-0 p-7 text-white md:p-9">
            <p className="text-sm font-semibold uppercase tracking-[0.18em] text-white/70">Contact us</p>
            <h2 className="mt-3 max-w-xl text-4xl font-semibold leading-tight sm:text-5xl">{settings.cta.contact_heading}</h2>
            <p className="mt-4 max-w-xl text-lg leading-8 text-white/72">{settings.cta.contact_description}</p>
            <div className="mt-6 flex flex-wrap gap-3">
              <a href={settings.general.phone_href} className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 font-semibold text-[#17211c]"><Phone className="size-4" />{settings.general.phone_display}</a>
              <a href={`mailto:${settings.general.email}`} className="inline-flex items-center gap-2 rounded-full border border-white/30 bg-black/15 px-5 py-3 font-semibold text-white backdrop-blur"><Mail className="size-4" />อีเมล</a>
            </div>
          </div>
        </div>
        <ContactForm />
      </div>
    </section>
  );
}

export function SiteFooter() {
  const settings = useSiteSettings();
  const visibleNavLinks = navLinks.filter((item) => {
    if (item.href === "/house-designs") return settings.navigation.show_house_designs;
    if (item.href === "/updates") return settings.navigation.show_updates;
    if (item.href === "/blog") return settings.navigation.show_blog;
    if (item.href === "/faq") return settings.navigation.show_faq;
    return true;
  });

  return (
    <footer className="border-t border-[#dfe4e0] bg-white px-5 py-16 text-[#17211c] lg:px-8">
      <div className="mx-auto grid max-w-7xl gap-10 md:grid-cols-2 lg:grid-cols-[1.35fr_0.75fr_0.75fr_1fr]">
        <div>
          <Link href="/" className="flex items-center gap-3" aria-label={settings.general.company_name_en}>
            <SiteLogo footer className="size-14 shrink-0 rounded-md object-cover" />
            <span className="leading-tight"><span className="block text-lg font-semibold uppercase tracking-[0.1em]">Build Master</span><span className="block text-xs font-medium uppercase tracking-[0.18em] text-[#0f6b45]">Construction</span></span>
          </Link>
          <p className="mt-5 max-w-sm text-base leading-8 text-[#667169]">{settings.general.tagline}</p>
          <div className="mt-6"><SocialLinks /></div>
        </div>
        <div>
          <h3 className="text-sm font-semibold uppercase tracking-[0.16em] text-[#17211c]">บริการ</h3>
          <ul className="mt-5 grid gap-3 text-[#667169]">{serviceLinks.map((service) => <li key={service}><Link href="/services" className="transition hover:text-[#0f6b45]">{service}</Link></li>)}</ul>
        </div>
        <div>
          <h3 className="text-sm font-semibold uppercase tracking-[0.16em] text-[#17211c]">เมนู</h3>
          <ul className="mt-5 grid gap-3 text-[#667169]">{visibleNavLinks.slice(1).map((item) => <li key={item.href}><Link href={item.href} className="transition hover:text-[#0f6b45]">{item.label}</Link></li>)}</ul>
        </div>
        <div>
          <h3 className="text-sm font-semibold uppercase tracking-[0.16em] text-[#17211c]">ติดต่อ</h3>
          <ul className="mt-5 grid gap-4 text-[#667169]">
            <li><a href={settings.general.phone_href} className="flex gap-3 transition hover:text-[#0f6b45]"><Phone className="mt-1 size-5 shrink-0" />{settings.general.phone_display}</a></li>
            <li><a href={`mailto:${settings.general.email}`} className="flex gap-3 break-all transition hover:text-[#0f6b45]"><Mail className="mt-1 size-5 shrink-0" />{settings.general.email}</a></li>
            <li className="flex gap-3"><MapPin className="mt-1 size-5 shrink-0" />{settings.general.service_area}</li>
          </ul>
          <Link href="/contact" className="mt-6 inline-flex items-center gap-2 font-semibold text-[#0f6b45]">เริ่มปรึกษาโครงการ <ArrowUpRight className="size-4" /></Link>
        </div>
      </div>
      <div className="mx-auto mt-12 flex max-w-7xl flex-col gap-2 border-t border-[#e4e8e5] pt-6 text-sm text-[#7a847d] sm:flex-row sm:items-center sm:justify-between">
        <p>{settings.general.copyright}</p><p>{settings.general.service_area}</p>
      </div>
    </footer>
  );
}
