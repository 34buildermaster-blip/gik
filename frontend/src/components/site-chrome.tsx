import Image from "next/image";
import Link from "next/link";
import { assetPath, sitePath } from "@/lib/asset-path";

type IconName = "phone" | "mail" | "location" | "send" | "facebook" | "instagram" | "line" | "tiktok";

function Icon({ name, className = "" }: { name: IconName; className?: string }) {
  const icon = {
    phone: (
      <>
        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.7a16 16 0 0 0 6.2 6.2l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z" />
      </>
    ),
    mail: (
      <>
        <path d="M4 6h16v12H4z" />
        <path d="m4 7 8 6 8-6" />
      </>
    ),
    location: (
      <>
        <path d="M12 21s7-5.1 7-11a7 7 0 0 0-14 0c0 5.9 7 11 7 11z" />
        <path d="M12 10.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
      </>
    ),
    send: (
      <>
        <path d="M22 2 11 13" />
        <path d="m22 2-7 20-4-9-9-4z" />
      </>
    ),
    facebook: (
      <>
        <path d="M14 8h3V4h-3a5 5 0 0 0-5 5v3H6v4h3v6h4v-6h3l1-4h-4V9a1 1 0 0 1 1-1z" />
      </>
    ),
    instagram: (
      <>
        <rect height="16" rx="4" width="16" x="4" y="4" />
        <path d="M16 11.4A4 4 0 1 1 12.6 8" />
        <path d="M17.5 6.5h.01" />
      </>
    ),
    line: (
      <>
        <path d="M5 18.5 6.2 15A7 7 0 1 1 12 18H8.5z" />
        <path d="M8 11h.01" />
        <path d="M12 11h.01" />
        <path d="M16 11h.01" />
      </>
    ),
    tiktok: (
      <>
        <path d="M14 4v10.2a3.8 3.8 0 1 1-3-3.7" />
        <path d="M14 4c.7 2.5 2.4 4 5 4" />
      </>
    ),
  }[name];

  return (
    <svg
      aria-hidden="true"
      className={className}
      fill="none"
      stroke="currentColor"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth="1.7"
      viewBox="0 0 24 24"
    >
      {icon}
    </svg>
  );
}

const socialLinks = [
  { label: "Facebook", href: "https://facebook.com", icon: "facebook" as const },
  { label: "Instagram", href: "https://instagram.com", icon: "instagram" as const },
  { label: "Line", href: "https://line.me", icon: "line" as const },
  { label: "TikTok", href: "https://tiktok.com", icon: "tiktok" as const },
];

const serviceLinks = ["ออกแบบบ้าน", "รีโนเวทบ้าน", "สร้างบ้าน", "บิวท์อิน"];

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
  const titleSize =
    size === "compact"
      ? "text-4xl sm:text-6xl"
      : "text-5xl sm:text-7xl";

  return (
    <section className="relative grid min-h-[320px] place-items-center overflow-hidden bg-[#053920] px-5 py-16 text-white lg:px-8">
      <Image
        src={assetPath("/bg-luxury-green.png")}
        alt="พื้นหลัง 34 Build Master Construction"
        fill
        priority
        sizes="100vw"
        className="z-0 object-cover opacity-55"
      />
      <div className="absolute inset-0 z-10 bg-[linear-gradient(90deg,rgba(5,57,32,0.96),rgba(17,36,22,0.88)),radial-gradient(circle_at_50%_8%,rgba(246,217,123,0.2),transparent_30%)]" />
      <div className="relative z-20 mx-auto max-w-6xl text-center">
        <h1 className={`${titleSize} font-extrabold leading-tight drop-shadow-[0_18px_46px_rgba(0,0,0,0.35)]`}>
          {title}
        </h1>
        <div className="mt-5 flex flex-wrap items-center justify-center gap-3 text-lg font-bold">
          <Link href={sitePath(parentHref)} className="text-white/76 transition hover:text-[#f6d97b]">
            {parentLabel}
          </Link>
          <span className="text-[#f6d97b]">/</span>
          <span className="text-[#f6d97b]">{currentLabel}</span>
        </div>
      </div>
      <span aria-hidden="true" className="absolute inset-x-0 bottom-0 z-20 h-1 bg-gradient-to-r from-transparent via-[#f6d97b] to-transparent" />
    </section>
  );
}

export function SiteHeader() {
  return (
    <header className="sticky top-0 z-40 border-b border-[#f6d97b]/20 bg-[#053920]/95 text-white shadow-[0_18px_60px_rgba(0,0,0,0.2)] backdrop-blur">
      <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 lg:px-8">
        <Link href={sitePath("/")} className="group flex items-center gap-3" aria-label="34 Build Master Construction">
          <span className="relative grid size-12 place-items-center overflow-hidden rounded-2xl bg-[#112416] text-lg font-extrabold text-[#fdf0a3] ring-1 ring-[#f6d97b]/40">
            <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#fdf0a3] to-transparent" />
            34
          </span>
          <span className="leading-tight">
            <span className="block text-base font-extrabold uppercase tracking-[0.16em]">Build Master</span>
            <span className="block text-[11px] uppercase tracking-[0.22em] text-[#f6d97b]">Construction</span>
          </span>
        </Link>

        <nav className="hidden items-center gap-6 text-base font-semibold text-white/78 md:flex">
          <Link href={sitePath("/")} className="transition hover:text-[#f6d97b]">
            หน้าหลัก
          </Link>
          <Link href={sitePath("/about")} className="transition hover:text-[#f6d97b]">
            เกี่ยวกับเรา
          </Link>
          <Link href={sitePath("/services")} className="transition hover:text-[#f6d97b]">
            บริการ
          </Link>
          <Link href={sitePath("/blog")} className="transition hover:text-[#f6d97b]">
            บทความ
          </Link>
          <Link href={sitePath("/#updates")} className="transition hover:text-[#f6d97b]">
            อัปเดตงาน
          </Link>
          <Link href={sitePath("/contact")} className="transition hover:text-[#f6d97b]">
            ติดต่อ
          </Link>
        </nav>

        <div className="hidden items-center gap-2 xl:flex">
          {socialLinks.map((social) => (
            <a
              key={social.label}
              href={social.href}
              className="social-icon-link"
              aria-label={social.label}
              target="_blank"
              rel="noreferrer"
            >
              <Icon name={social.icon} className="size-4" />
            </a>
          ))}
        </div>

        <a
          href="tel:+66819512297"
          className="gold-button inline-flex min-h-11 items-center justify-center gap-2 px-4 text-base font-extrabold text-[#112416]"
        >
          <Icon name="phone" className="size-4" />
          โทรปรึกษา
        </a>
      </div>
    </header>
  );
}

export function ContactForm() {
  return (
    <form
      className="luxe-card grid gap-5 p-7 md:p-8"
      action="mailto:34buildmaster@gmail.com"
      method="post"
      encType="text/plain"
    >
      <label className="form-field">
        <span>ชื่อผู้ติดต่อ</span>
        <input name="name" type="text" placeholder="ชื่อของคุณ" />
      </label>
      <label className="form-field">
        <span>เบอร์โทร</span>
        <input name="phone" type="tel" placeholder="เบอร์ที่สะดวกให้ติดต่อกลับ" />
      </label>
      <label className="form-field">
        <span>ประเภทงาน</span>
        <select name="service" defaultValue="">
          <option value="" disabled>
            เลือกประเภทงาน
          </option>
          <option>ออกแบบบ้าน</option>
          <option>รีโนเวทบ้าน</option>
          <option>สร้างบ้าน</option>
          <option>บิวท์อิน</option>
        </select>
      </label>
      <label className="form-field">
        <span>รายละเอียดเบื้องต้น</span>
        <textarea name="detail" placeholder="เล่าพื้นที่ งบประมาณคร่าว ๆ หรือสิ่งที่อยากทำ" />
      </label>
      <button className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 font-extrabold text-[#112416]" type="submit">
        <Icon name="send" className="size-5" />
        ส่งรายละเอียด
      </button>
    </form>
  );
}

export function ContactBand() {
  return (
    <section className="section-reveal bg-luxury-section px-5 py-24 text-white lg:px-8">
      <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-stretch">
        <div className="relative overflow-hidden rounded-[2rem] border border-[#f6d97b]/28 bg-[#112416]/72 p-6 shadow-[0_36px_120px_rgba(0,0,0,0.28)] md:p-10">
          <div className="luxury-rings absolute -right-56 -top-56 h-[420px] w-[420px] rounded-full opacity-60" />
          <div className="relative">
            <p className="section-kicker text-[#f6d97b]">Contact</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
              เล่าไอเดียบ้านของคุณ แล้วให้ทีมช่วยประเมินขั้นแรก
            </h2>
            <p className="mt-5 max-w-2xl text-xl leading-9 text-white/72">
              ส่งข้อมูลพื้นที่ งบประมาณคร่าว ๆ และความต้องการหลักไว้ก่อน ทีมงานจะใช้เป็นข้อมูลตั้งต้นสำหรับการนัดสำรวจและสรุปขอบเขตงานจริง
            </p>

            <div className="mt-10 grid gap-4">
              <a href="tel:+66819512297" className="contact-info-row group">
                <span className="icon-medallion shrink-0">
                  <Icon name="phone" className="size-6" />
                </span>
                <span>
                  <span className="block text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">Phone</span>
                  <span className="mt-1 block text-2xl font-extrabold">081-9512-297</span>
                </span>
              </a>
              <a href="mailto:34buildmaster@gmail.com" className="contact-info-row group">
                <span className="icon-medallion shrink-0">
                  <Icon name="mail" className="size-6" />
                </span>
                <span>
                  <span className="block text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">Email</span>
                  <span className="mt-1 block break-all text-xl font-extrabold">34buildmaster@gmail.com</span>
                </span>
              </a>
            </div>
          </div>
        </div>

        <ContactForm />
      </div>
    </section>
  );
}

export function SiteFooter() {
  return (
    <footer className="bg-[#112416] px-5 py-14 text-white lg:px-8">
      <div className="mx-auto grid max-w-7xl gap-10 border-t border-[#f6d97b]/20 pt-10 lg:grid-cols-[1.2fr_0.7fr_0.7fr_1fr]">
        <div>
          <Link href={sitePath("/")} className="flex items-center gap-3" aria-label="34 Build Master Construction">
            <span className="grid size-14 place-items-center rounded-2xl border border-[#f6d97b]/40 bg-[#053920] text-xl font-extrabold text-[#fdf0a3]">
              34
            </span>
            <span className="leading-tight">
              <span className="block text-lg font-extrabold uppercase tracking-[0.16em]">Build Master</span>
              <span className="block text-xs uppercase tracking-[0.24em] text-[#f6d97b]">Construction</span>
            </span>
          </Link>
          <p className="mt-5 max-w-sm text-base leading-8 text-white/62">
            สร้างสรรค์คุณภาพ มุ่งมั่นในทุกงานก่อสร้าง สำหรับงานออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน
          </p>
          <div className="mt-6 flex items-center gap-2">
            {socialLinks.map((social) => (
              <a
                key={social.label}
                href={social.href}
                className="social-icon-link"
                aria-label={social.label}
                target="_blank"
                rel="noreferrer"
              >
                <Icon name={social.icon} className="size-4" />
              </a>
            ))}
          </div>
        </div>

        <div>
          <h3 className="footer-heading">บริการ</h3>
          <ul className="mt-4 space-y-3 text-white/66">
            {serviceLinks.map((service) => (
              <li key={service}>
                <Link className="footer-link" href={sitePath("/services")}>
                  {service}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <div>
          <h3 className="footer-heading">เมนู</h3>
          <ul className="mt-4 space-y-3 text-white/66">
            <li>
              <Link className="footer-link" href={sitePath("/about")}>
                เกี่ยวกับเรา
              </Link>
            </li>
            <li>
              <Link className="footer-link" href={sitePath("/#updates")}>
                อัปเดตงาน
              </Link>
            </li>
            <li>
              <Link className="footer-link" href={sitePath("/services")}>
                บริการ
              </Link>
            </li>
            <li>
              <Link className="footer-link" href={sitePath("/blog")}>
                บทความ
              </Link>
            </li>
            <li>
              <Link className="footer-link" href={sitePath("/contact")}>
                ติดต่อเรา
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <h3 className="footer-heading">ติดต่อ</h3>
          <ul className="mt-4 space-y-4 text-white/72">
            <li className="flex gap-3">
              <Icon name="phone" className="mt-1 size-5 shrink-0 text-[#f6d97b]" />
              <a className="footer-link" href="tel:+66819512297">
                081-9512-297
              </a>
            </li>
            <li className="flex gap-3">
              <Icon name="mail" className="mt-1 size-5 shrink-0 text-[#f6d97b]" />
              <a className="footer-link break-all" href="mailto:34buildmaster@gmail.com">
                34buildmaster@gmail.com
              </a>
            </li>
            <li className="flex gap-3">
              <Icon name="location" className="mt-1 size-5 shrink-0 text-[#f6d97b]" />
              <span>เชียงใหม่ และพื้นที่ใกล้เคียง</span>
            </li>
          </ul>
        </div>
      </div>
      <div className="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-[#f6d97b]/14 pt-6 text-base text-white/46 sm:flex-row sm:items-center sm:justify-between">
        <p>© 2026 34 Build Master Construction. All rights reserved.</p>
        <p>Modern Luxury Premium Construction</p>
      </div>
    </footer>
  );
}
