import Image from "next/image";
import { assetPath, sitePath } from "@/lib/asset-path";
import { siteConfig, socialLinks } from "@/lib/site-config";

type IconName =
  | "drafting"
  | "renovation"
  | "home"
  | "cabinet"
  | "diamond"
  | "crown"
  | "badge"
  | "shield"
  | "professional"
  | "phone"
  | "mail"
  | "location"
  | "clock"
  | "send"
  | "facebook"
  | "instagram"
  | "line"
  | "tiktok"
  | "check"
  | "plan"
  | "tools"
  | "consult"
  | "measure"
  | "clipboard"
  | "handover";

function Icon({ name, className = "" }: { name: IconName; className?: string }) {
  const icon = {
    drafting: (
      <>
        <path d="M4 19h16" />
        <path d="M7 16 17 6l3 3-10 10H7z" />
        <path d="m14 9 3 3" />
      </>
    ),
    renovation: (
      <>
        <path d="M14 4 4 14l6 6L20 10z" />
        <path d="m8 10 6 6" />
        <path d="m12 6 6 6" />
      </>
    ),
    home: (
      <>
        <path d="M3 11 12 4l9 7" />
        <path d="M5 10v10h14V10" />
        <path d="M9 20v-6h6v6" />
      </>
    ),
    cabinet: (
      <>
        <path d="M5 4h14v16H5z" />
        <path d="M12 4v16" />
        <path d="M9 12h1" />
        <path d="M14 12h1" />
      </>
    ),
    diamond: (
      <>
        <path d="M6 4h12l4 6-10 10L2 10z" />
        <path d="M2 10h20" />
        <path d="m7 4 5 16 5-16" />
      </>
    ),
    crown: (
      <>
        <path d="m3 8 4 4 5-7 5 7 4-4v11H3z" />
        <path d="M3 19h18" />
      </>
    ),
    badge: (
      <>
        <path d="M12 3 8 5 4 5v7c0 4 3 7 8 9 5-2 8-5 8-9V5h-4z" />
        <path d="m9 12 2 2 4-5" />
      </>
    ),
    shield: (
      <>
        <path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z" />
        <path d="m9 12 2 2 4-5" />
      </>
    ),
    professional: (
      <>
        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" />
        <path d="M5 21a7 7 0 0 1 14 0" />
        <path d="M10 15h4" />
      </>
    ),
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
    clock: (
      <>
        <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z" />
        <path d="M12 6v6l4 2" />
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
    check: (
      <>
        <path d="m4 12 5 5L20 6" />
      </>
    ),
    plan: (
      <>
        <path d="M5 4h14v16H5z" />
        <path d="M8 8h8" />
        <path d="M8 12h4" />
        <path d="M14 15h2" />
      </>
    ),
    tools: (
      <>
        <path d="M14 6a4 4 0 0 0 4 4l-8 8a3 3 0 0 1-4-4l8-8z" />
        <path d="m14 6 4-4 4 4-4 4" />
      </>
    ),
    consult: (
      <>
        <path d="M7 11.5a5 5 0 1 1 3.5 4.8L6 18l1.4-3.7A4.9 4.9 0 0 1 7 11.5z" />
        <path d="M13 8.5h.01" />
        <path d="M16 8.5h.01" />
        <path d="M10 8.5h.01" />
      </>
    ),
    measure: (
      <>
        <path d="M4 17 17 4l3 3L7 20H4z" />
        <path d="m13 8 3 3" />
        <path d="m10 11 2 2" />
        <path d="m7 14 3 3" />
      </>
    ),
    clipboard: (
      <>
        <path d="M9 4h6l1 2h3v15H5V6h3z" />
        <path d="M9 4v3h6V4" />
        <path d="M8 12h8" />
        <path d="M8 16h5" />
      </>
    ),
    handover: (
      <>
        <path d="M4 12h7l2 3h7" />
        <path d="M4 16h5l2 3h5" />
        <path d="m15 8 2 2 4-5" />
        <path d="M4 8h6" />
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

const services = [
  {
    title: "ออกแบบบ้าน",
    description:
      "ออกแบบแนวคิด ฟังก์ชัน และภาพรวมงานก่อสร้างให้สอดคล้องกับงบประมาณ ไลฟ์สไตล์ และคุณภาพระยะยาว",
    tag: "Design",
    number: "01",
    icon: "drafting" as const,
  },
  {
    title: "รีโนเวทบ้าน",
    description:
      "ปรับบ้านเดิมให้กลับมาสวย ใช้งานดี และมีระบบงานที่ชัดเจน ตั้งแต่งานโครงสร้างถึงงานตกแต่ง",
    tag: "Renovation",
    number: "02",
    icon: "renovation" as const,
  },
  {
    title: "สร้างบ้าน",
    description:
      "ดูแลงานสร้างบ้านด้วยแผนงานเป็นขั้นตอน คุมคุณภาพหน้างาน และสื่อสารกับเจ้าของบ้านอย่างสม่ำเสมอ",
    tag: "Build",
    number: "03",
    icon: "home" as const,
  },
  {
    title: "บิวท์อิน",
    description:
      "ออกแบบและผลิตเฟอร์นิเจอร์บิวท์อินให้ลงตัวกับพื้นที่จริง เลือกวัสดุและรายละเอียดให้เข้ากับบ้าน",
    tag: "Built-in",
    number: "04",
    icon: "cabinet" as const,
  },
];

const projects = [
  {
    type: "Residential",
    title: "บ้านพักอาศัยสไตล์โมเดิร์น",
    detail: "ออกแบบภาพรวมพื้นที่อยู่อาศัยให้สะอาด โปร่ง และดูแลรักษาง่าย",
    icon: "home" as const,
  },
  {
    type: "Renovation",
    title: "รีโนเวทบ้านเดิม",
    detail: "จัดลำดับงานระบบ โครงสร้าง และผิวจบ เพื่อให้บ้านกลับมาใช้งานได้ดี",
    icon: "tools" as const,
  },
  {
    type: "Interior",
    title: "ครัวและตู้บิวท์อิน",
    detail: "เพิ่มพื้นที่เก็บของและความเรียบร้อย โดยคุมโทนวัสดุให้ต่อเนื่องทั้งบ้าน",
    icon: "cabinet" as const,
  },
];

const process = [
  "รับโจทย์และสำรวจพื้นที่",
  "เสนอแนวทางพร้อมงบประมาณ",
  "วางแผนงานและคุมหน้างาน",
  "ตรวจคุณภาพและส่งมอบ",
];

const values = [
  { label: "Modern", icon: "home" as const },
  { label: "Luxury", icon: "crown" as const },
  { label: "Premium", icon: "diamond" as const },
  { label: "Trustworthy", icon: "shield" as const },
  { label: "Professional", icon: "professional" as const },
];

const faqs = [
  {
    question: "รับงานประเภทไหนบ้าง?",
    answer:
      "รับออกแบบ รีโนเวท สร้างบ้าน และงานบิวท์อินสำหรับบ้านพักอาศัยหรือพื้นที่ใช้งานส่วนตัว",
  },
  {
    question: "เริ่มต้นต้องเตรียมอะไร?",
    answer:
      "เตรียมรูปพื้นที่ ขนาดโดยประมาณ งบประมาณคร่าว ๆ และความต้องการหลักของบ้านหรือห้องนั้น",
  },
  {
    question: "สามารถประเมินงบก่อนเริ่มงานได้ไหม?",
    answer:
      "ได้ ทีมงานสามารถช่วยประเมินเบื้องต้นจากข้อมูลพื้นที่และขอบเขตงานก่อนนัดสำรวจจริง",
  },
  {
    question: "งบประมาณเริ่มต้นควรเตรียมไว้ประมาณเท่าไร?",
    answer:
      "งบขึ้นอยู่กับขนาดพื้นที่ ประเภทงาน วัสดุ และรายละเอียดการตกแต่ง ทีมสามารถช่วยประเมินเบื้องต้นจากรูปพื้นที่ ขนาดคร่าว ๆ และความต้องการหลักก่อนนัดสำรวจจริงได้",
  },
  {
    question: "โดยทั่วไปใช้เวลาทำงานนานแค่ไหน?",
    answer:
      "ระยะเวลาขึ้นอยู่กับขอบเขตงาน หากเป็นงานออกแบบหรือบิวท์อินบางส่วนอาจใช้เวลาสั้นกว่า ส่วนงานรีโนเวทหรือสร้างบ้านต้องดูหน้างานจริง แผนวัสดุ และลำดับงานก่อนสรุปไทม์ไลน์",
  },
];

const projectUpdates = [
  {
    title: "ตรวจหน้างานรีโนเวทบ้านพักอาศัย",
    stage: "Site Survey",
    detail: "อัปเดตพื้นที่จริง วัดระยะ และเช็กจุดสำคัญก่อนจัดแผนงาน",
    image: assetPath("/hero-construction.png"),
  },
  {
    title: "เลือกวัสดุและโทนงานบิวท์อิน",
    stage: "Material Review",
    detail: "คุมโทนสี วัสดุ และรายละเอียดผิวให้ตรงกับภาพรวมบ้าน",
    image: assetPath("/bg-material-board.png"),
  },
  {
    title: "สรุป mood งาน luxury modern",
    stage: "Design Direction",
    detail: "จัดทิศทางดีไซน์ให้หรู เรียบ และต่อยอดเป็นงานจริงได้",
    image: assetPath("/bg-luxury-green.png"),
  },
];

const beforeAfterCases = [
  {
    title: "รีโนเวทพื้นที่พักอาศัยให้โปร่งและใช้งานดีขึ้น",
    category: "Renovation",
    before: assetPath("/bg-material-board.png"),
    after: assetPath("/hero-construction.png"),
    detail: "จัด mood วัสดุ แสง และพื้นที่ใช้งานใหม่ เพื่อให้บ้านดูทันสมัยและดูแลรักษาง่ายขึ้น",
  },
  {
    title: "ปรับภาพรวมงานบิวท์อินให้ต่อเนื่องกับตัวบ้าน",
    category: "Built-in",
    before: assetPath("/bg-luxury-green.png"),
    after: assetPath("/bg-material-board.png"),
    detail: "วางโทนสีและรายละเอียดผิววัสดุให้กลมกลืนกับบ้าน พร้อมเพิ่มพื้นที่เก็บของอย่างเป็นระบบ",
  },
];

const testimonials = [
  {
    quote:
      "ทีมช่วยเรียบเรียงความต้องการและแนะนำวัสดุได้ดีมาก งานออกมาดูเรียบร้อยและตรงโทนที่อยากได้",
    name: "คุณอร",
    project: "รีโนเวทบ้านพักอาศัย",
  },
  {
    quote:
      "คุยงานง่าย มีการอัปเดตเป็นระยะ ทำให้เห็นภาพรวมงบและขั้นตอนก่อนตัดสินใจลงงานจริง",
    name: "คุณณัฐ",
    project: "ออกแบบและบิวท์อิน",
  },
  {
    quote:
      "รายละเอียดงานดูพรีเมียมขึ้น บ้านใช้งานได้ดีขึ้น และทีมให้คำแนะนำเรื่องการดูแลหลังจบงานด้วย",
    name: "คุณแพร",
    project: "ปรับปรุงพื้นที่ภายใน",
  },
];

const serviceAreas = ["เมืองเชียงใหม่", "หางดง", "สันทราย", "แม่ริม", "สารภี", "สันกำแพง"];
const homeNavLinks = [
  { href: sitePath("/"), label: "หน้าหลัก" },
  { href: sitePath("/about"), label: "เกี่ยวกับเรา" },
  { href: sitePath("/services"), label: "บริการ" },
  { href: sitePath("/blog"), label: "บทความ" },
  { href: "#updates", label: "อัปเดตงาน" },
  { href: sitePath("/contact"), label: "ติดต่อ" },
];

export default function Home() {
  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <header className="sticky top-0 z-40 border-b border-[#f6d97b]/20 bg-[#053920]/95 text-white shadow-[0_18px_60px_rgba(0,0,0,0.2)] backdrop-blur">
        <div className="mx-auto grid max-w-7xl grid-cols-[minmax(0,1fr)_auto] items-center gap-3 px-5 py-4 md:flex md:justify-between lg:px-8">
          <a href={sitePath("/")} className="group flex min-w-0 items-center gap-3" aria-label="34 Build Master Construction">
            <span className="relative grid size-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#112416] text-lg font-extrabold text-[#fdf0a3] ring-1 ring-[#f6d97b]/40">
              <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#fdf0a3] to-transparent" />
              34
            </span>
            <span className="min-w-0 leading-tight">
              <span className="block truncate text-sm font-extrabold uppercase tracking-[0.12em] sm:text-base sm:tracking-[0.16em]">
                Build Master
              </span>
              <span className="block truncate text-[10px] uppercase tracking-[0.18em] text-[#f6d97b] sm:text-[11px] sm:tracking-[0.22em]">
                Construction
              </span>
            </span>
          </a>

          <nav className="hidden items-center gap-6 text-base font-semibold text-white/78 md:flex">
            {homeNavLinks.map((item) => (
              <a key={item.href} href={item.href} className="transition hover:text-[#f6d97b]">
                {item.label}
              </a>
            ))}
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
            href={siteConfig.phoneHref}
            className="gold-button hidden min-h-11 items-center justify-center gap-2 px-4 text-base font-extrabold text-[#112416] sm:inline-flex"
          >
            <Icon name="phone" className="size-4" />
            โทรปรึกษา
          </a>

          <details className="relative shrink-0 md:hidden">
            <summary className="grid size-11 cursor-pointer list-none place-items-center rounded-full border border-[#f6d97b]/45 bg-[#112416]/72 text-[#fdf0a3] shadow-[0_18px_48px_rgba(0,0,0,0.24)] [&::-webkit-details-marker]:hidden">
              <span className="sr-only">เปิดเมนู</span>
              <span className="flex w-5 flex-col gap-1.5">
                <span className="h-0.5 rounded-full bg-current" />
                <span className="h-0.5 rounded-full bg-current" />
                <span className="h-0.5 rounded-full bg-current" />
              </span>
            </summary>
            <div className="absolute right-0 top-14 w-[min(82vw,320px)] overflow-hidden rounded-[1.5rem] border border-[#f6d97b]/28 bg-[#053920]/98 p-3 shadow-[0_28px_80px_rgba(0,0,0,0.34)] backdrop-blur">
              <nav className="grid gap-1 text-base font-bold text-white/82">
                {homeNavLinks.map((item) => (
                  <a key={item.href} href={item.href} className="rounded-2xl px-4 py-3 transition hover:bg-white/8 hover:text-[#f6d97b]">
                    {item.label}
                  </a>
                ))}
              </nav>
              <div className="mt-3 flex items-center justify-between gap-2 border-t border-[#f6d97b]/16 pt-3">
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
                <a href={siteConfig.phoneHref} className="gold-button inline-flex min-h-10 items-center justify-center gap-2 px-4 text-sm font-extrabold text-[#112416]">
                  <Icon name="phone" className="size-4" />
                  โทร
                </a>
              </div>
            </div>
          </details>
        </div>
      </header>

      <section className="relative min-h-[calc(100vh-81px)] overflow-hidden bg-[#053920]">
        <Image
          src={assetPath("/hero-construction.png")}
          alt="ทีมงาน 34 Build Master Construction ตรวจแบบหน้าบ้านโมเดิร์น"
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
        <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,57,32,0.96)_0%,rgba(5,57,32,0.82)_38%,rgba(17,36,22,0.35)_78%)]" />
        <div className="luxury-rings absolute -right-36 -top-40 hidden h-[560px] w-[560px] rounded-full lg:block" />
        <div className="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-[#053920] to-transparent" />

        <div className="relative mx-auto flex min-h-[calc(100vh-81px)] max-w-7xl items-center px-5 py-16 lg:px-8">
          <div className="max-w-3xl text-white">
            <div className="reveal-up">
              <p className="mb-6 inline-flex text-sm font-bold text-[#fff3b8] drop-shadow-[0_8px_24px_rgba(0,0,0,0.58)]">
                สร้างสรรค์คุณภาพ มุ่งมั่นในทุกงานก่อสร้าง
              </p>
              <h1 className="text-5xl font-extrabold leading-[0.95] text-[#fffaf0] drop-shadow-[0_8px_30px_rgba(0,0,0,0.72)] sm:text-6xl lg:text-8xl">
                34 Build
                <span className="hero-gold-text block">Master</span>
              </h1>
              <p className="mt-6 max-w-2xl text-xl leading-9 text-white/90 drop-shadow-[0_4px_18px_rgba(0,0,0,0.62)]">
                รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร ด้วยภาพลักษณ์ที่ทันสมัย
                งานจบเรียบร้อย และการดูแลโครงการแบบมืออาชีพตั้งแต่ต้นจนส่งมอบ
              </p>
            </div>

            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <a
                href="#contact"
                className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 text-base font-extrabold text-[#112416]"
              >
                <Icon name="phone" className="size-5" />
                ขอประเมินราคาฟรี
              </a>
              <a
                href="#projects"
                className="inline-flex min-h-12 items-center justify-center rounded-full border border-[#f6d97b]/50 px-7 text-base font-bold text-[#fdf0a3] transition hover:bg-[#f6d97b] hover:text-[#112416]"
              >
                ดูผลงานตัวอย่าง
              </a>
            </div>

            <dl className="mt-12 grid max-w-2xl grid-cols-3 gap-4 border-t border-[#f6d97b]/28 pt-7">
              <div className="luxury-stat">
                <dt className="gold-text text-3xl font-extrabold">4</dt>
                <dd className="mt-1 text-sm text-white/68">บริการหลัก</dd>
              </div>
              <div className="luxury-stat">
                <dt className="gold-text text-3xl font-extrabold">1</dt>
                <dd className="mt-1 text-sm text-white/68">ทีมดูแลครบงาน</dd>
              </div>
              <div className="luxury-stat">
                <dt className="gold-text text-3xl font-extrabold">360</dt>
                <dd className="mt-1 text-sm text-white/68">วางแผนครบมุม</dd>
              </div>
            </dl>
          </div>
        </div>
      </section>

      <section id="services" className="section-reveal bg-material-section relative overflow-hidden px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-10 lg:grid-cols-[0.78fr_1.22fr] lg:items-end">
            <div >
              <p className="section-kicker">Services</p>
              <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
                 งานก่อสร้างเริ่มต้นจากความใส่ใจในทุกรายละเอียด
              </h2>
            </div>
            <p className="max-w-2xl text-lg leading-9 text-[#4d5b50] lg:ml-auto">
              เราวางโครงหน้าเว็บให้สื่อสารแบบแบรนด์หรู: ใช้พื้นที่หายใจมากขึ้น
              สีเขียวเข้มให้ความมั่นคง และทองเป็น accent เพื่อเน้นคุณภาพกับความเชื่อถือ
            </p>
          </div>

          <div className="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            {services.map((service) => (
              <article key={service.title} className="luxe-card section-card group p-6">
                <div className="flex items-start justify-between gap-4">
                  <span className="icon-medallion">
                    <Icon name={service.icon} className="size-7" />
                  </span>
                  <span className="text-sm font-extrabold text-[#053920]/25">
                    {service.number}
                  </span>
                </div>
                <p className="mt-7 text-xs font-extrabold uppercase tracking-[0.22em] text-[#aa7426]">
                  {service.tag}
                </p>
                <h3 className="mt-3 text-3xl font-extrabold text-[#053920]">{service.title}</h3>
                <p className="mt-4 text-lg leading-8 text-[#4d5b50]">{service.description}</p>
                <div className="mt-7 h-px w-full bg-gradient-to-r from-[#aa7426] via-[#f6d97b] to-transparent transition group-hover:w-4/5" />
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="projects" className="section-reveal bg-luxury-section px-5 py-24 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.86fr_1.14fr]">
          <div>
            <p className="section-kicker text-[#f6d97b]">Selected Work</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
              ผลงานต้องเล่าเรื่องความละเอียด ไม่ใช่แค่โชว์รูปสวย
            </h2>
            <p className="mt-5 text-lg leading-9 text-white/72">
              พื้นที่นี้เตรียมไว้สำหรับ case study, ภาพ before-after และรายละเอียดวัสดุ
              เพื่อให้ลูกค้าเห็นคุณภาพก่อนติดต่อ และช่วยทำ SEO ของแต่ละประเภทงาน
            </p>
          </div>

          <div className="grid gap-5">
            {projects.map((project) => (
              <article
                key={project.title}
                className="section-card group grid gap-5 border border-[#f6d97b]/18 bg-[#112416]/62 p-5 shadow-[0_30px_90px_rgba(0,0,0,0.24)] transition hover:-translate-y-1 hover:border-[#f6d97b]/48 md:grid-cols-[120px_1fr]"
              >
                <div className="grid min-h-28 place-items-center rounded-3xl bg-gradient-to-br from-[#aa7426] via-[#f6d97b] to-[#fdf0a3] text-[#053920]">
                  <Icon name={project.icon} className="size-12" />
                </div>
                <div>
                  <p className="text-sm font-extrabold uppercase tracking-[0.2em] text-[#f6d97b]">
                    {project.type}
                  </p>
                  <h3 className="mt-3 text-3xl font-extrabold">{project.title}</h3>
                  <p className="mt-3 text-lg leading-8 text-white/68">{project.detail}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section-reveal before-after-section px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-end">
            <div>
              <p className="section-kicker">Before / After</p>
              <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">
                เห็นภาพการเปลี่ยนแปลงก่อนตัดสินใจเริ่มงาน
              </h2>
            </div>
            <p className="max-w-2xl text-xl leading-9 text-[#4d5b50] lg:ml-auto">
              พื้นที่นี้เตรียมไว้สำหรับรูปงานจริง เปรียบเทียบก่อนและหลัง เพื่อให้ลูกค้าเห็นคุณภาพงาน
              รายละเอียดวัสดุ และความคุ้มค่าของการปรับพื้นที่อย่างชัดเจน
            </p>
          </div>

          <div className="mt-12 grid gap-6 lg:grid-cols-2">
            {beforeAfterCases.map((item) => (
              <article key={item.title} className="before-after-card">
                <div className="grid gap-3 sm:grid-cols-2">
                  <div className="comparison-frame">
                    <Image
                      src={item.before}
                      alt={`${item.title} before`}
                      fill
                      sizes="(min-width: 1024px) 25vw, 50vw"
                      className="object-cover"
                    />
                    <span className="comparison-label">Before</span>
                  </div>
                  <div className="comparison-frame">
                    <Image
                      src={item.after}
                      alt={`${item.title} after`}
                      fill
                      sizes="(min-width: 1024px) 25vw, 50vw"
                      className="object-cover"
                    />
                    <span className="comparison-label">After</span>
                  </div>
                </div>
                <div className="mt-6">
                  <p className="text-sm font-extrabold uppercase tracking-[0.18em] text-[#aa7426]">
                    {item.category}
                  </p>
                  <h3 className="mt-3 text-3xl font-extrabold leading-tight text-[#053920]">{item.title}</h3>
                  <p className="mt-3 text-lg leading-8 text-[#4d5b50]">{item.detail}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section
        id="updates"
        className="live-updates-section bg-[#053920] px-5 py-24 text-white lg:px-8"
      >
        <div className="relative z-10 mx-auto max-w-7xl">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div className="max-w-3xl">
              <p className="section-kicker text-[#f6d97b]">Live Project Updates</p>
              <h2 className="mt-4 text-4xl font-extrabold leading-tight text-white sm:text-6xl">
                อัปเดตโปรเจกต์หน้างานแบบเรียลไทม์
              </h2>
              <p className="mt-5 text-xl leading-9 text-white/72">
                วางพื้นที่นี้ไว้สำหรับรูปโปรเจกต์จริง ความคืบหน้าหน้างาน และภาพวัสดุที่ทีมกำลังดูแลอยู่
                เพื่อให้ลูกค้าเห็นมาตรฐานการทำงานตั้งแต่ก่อนเริ่มคุยรายละเอียด
              </p>
            </div>
            <a
              href={sitePath("/contact")}
              className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 text-base font-extrabold text-[#112416]"
            >
              <Icon name="send" className="size-5" />
              ส่งรูปงานให้ประเมิน
            </a>
          </div>

          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {projectUpdates.map((update) => (
              <article
                key={update.title}
                className="project-update-card section-card group border border-[#f6d97b]/25 bg-[#112416]/85 shadow-[0_30px_100px_rgba(0,0,0,0.28)]"
                style={{
                  background:
                    "linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03)), rgba(17,36,22,0.86)",
                  borderColor: "rgba(246,217,123,0.28)",
                }}
              >
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
                  <h3 className="mt-3 text-2xl font-extrabold leading-snug text-white">
                    {update.title}
                  </h3>
                  <p className="mt-3 text-lg leading-8 text-white/68">{update.detail}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section-reveal brand-personality-section bg-[#fbf7ec] px-5 py-24 text-[#112416] lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="brand-personality-panel grid gap-10 rounded-[2rem] border border-[#aa7426]/20 bg-white/75 p-5 shadow-[0_30px_100px_rgba(17,36,22,0.12)] lg:grid-cols-[0.82fr_1.18fr] lg:items-center lg:p-9">
            <div className="brand-copy-panel rounded-[1.6rem] border border-[#053920]/10 bg-white/70 p-5 lg:p-8">
              <p className="section-kicker">Brand Personality</p>
              <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">
                บุคลิกแบรนด์จาก guideline ถูกแปลงเป็นประสบการณ์บนเว็บ
              </h2>
            </div>
            <div className="grid gap-4 sm:grid-cols-5">
              {values.map((value) => (
                <div
                  key={value.label}
                  className="brand-value-card section-card border border-[#aa7426]/25 bg-white/95 px-4 py-6 text-center shadow-[0_18px_54px_rgba(17,36,22,0.1)] transition hover:-translate-y-1"
                >
                  <div className="icon-medallion mx-auto mb-4">
                    <Icon name={value.icon} className="size-7" />
                  </div>
                  <p className="text-sm font-extrabold uppercase tracking-[0.08em] text-[#053920]">
                    {value.label}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section id="process" className="section-reveal bg-luxury-section px-5 py-24 text-white lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="mx-auto max-w-4xl text-center">
            <p className="section-kicker process-kicker mx-auto text-[#f6d97b]">Process</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
              <span className="block">ขั้นตอนที่คุมความคาดหวัง</span>
              <span className="block">ได้ตั้งแต่วันแรก</span>
            </h2>
          </div>

          <ol className="process-flow mt-16 grid gap-10 md:grid-cols-4">
            {process.map((step, index) => (
              <li
                key={step}
                className="process-step relative text-center"
              >
                <div className="process-icon mx-auto">
                  <Icon
                    name={index === 0 ? "consult" : index === 1 ? "measure" : index === 2 ? "clipboard" : "handover"}
                    className="size-8"
                  />
                </div>
                <p className="mt-5 text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">
                  Step {index + 1}
                </p>
                <p className="mx-auto mt-4 max-w-56 text-xl font-extrabold leading-8 text-white/92">{step}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="section-reveal testimonials-section px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="mx-auto max-w-4xl text-center">
            <p className="section-kicker process-kicker mx-auto">Testimonials</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-6xl">
              เสียงจากลูกค้าที่เริ่มงานอย่างมั่นใจ
            </h2>
            <p className="mx-auto mt-5 max-w-3xl text-xl leading-9 text-[#4d5b50]">
              รีวิวส่วนนี้เตรียมไว้สำหรับใส่คำชมจากลูกค้าจริง เพื่อเพิ่มความน่าเชื่อถือและช่วยให้ผู้ชมตัดสินใจติดต่อได้ง่ายขึ้น
            </p>
          </div>

          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {testimonials.map((item) => (
              <article key={item.name} className="testimonial-card">
                <div className="text-5xl font-extrabold leading-none text-[#aa7426]/28">“</div>
                <p className="mt-2 text-xl font-semibold leading-9 text-[#112416]">{item.quote}</p>
                <div className="mt-8 border-t border-[#aa7426]/18 pt-5">
                  <p className="text-lg font-extrabold text-[#053920]">{item.name}</p>
                  <p className="mt-1 text-base font-semibold text-[#aa7426]">{item.project}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section-reveal faq-luxe-section px-5 py-24 lg:px-8">
        <div className="relative mx-auto max-w-7xl">
          <div className="mx-auto max-w-6xl text-center">
            <p className="section-kicker mx-auto">FAQ</p>
            <h2 className="faq-heading mt-4 text-4xl font-extrabold leading-tight text-[#053920] sm:text-5xl xl:text-6xl">
              คำถามสำคัญ ก่อนเริ่มคุยเรื่องบ้าน
            </h2>
            <p className="mt-5 text-xl leading-9 text-[#4d5b50]">
              รวมคำตอบที่ช่วยให้เจ้าของบ้านเห็นภาพขั้นตอน งบประมาณ และการเตรียมข้อมูลเบื้องต้นได้ชัดขึ้น
            </p>
          </div>

          <div className="mt-12 grid gap-6 lg:grid-cols-[0.42fr_0.58fr] lg:items-start">
            <div className="faq-intro-panel section-card">
              <p className="text-base font-semibold uppercase tracking-[0.18em] text-[#f6d97b]">
                Quick Guide
              </p>
              <h3 className="mt-4 text-3xl font-extrabold leading-tight text-white">
                เตรียมข้อมูลให้พร้อม คุยงานได้เร็วขึ้น
              </h3>
              <p className="mt-4 text-lg leading-8 text-white/68">
                ถ้ามีรูปพื้นที่ ขนาดคร่าว ๆ และงบประมาณที่ตั้งใจไว้ ทีมจะช่วยประเมินแนวทางให้เห็นภาพได้ไวขึ้น
              </p>
              <div className="mt-8 grid gap-3">
                <div className="faq-mini-stat">
                  <span>01</span>
                  ประเภทงาน
                </div>
                <div className="faq-mini-stat">
                  <span>02</span>
                  ข้อมูลเริ่มต้น
                </div>
                <div className="faq-mini-stat">
                  <span>03</span>
                  ประเมินงบ
                </div>
              </div>
            </div>

            <div className="grid gap-4">
              {faqs.map((faq, index) => (
                <details key={faq.question} className="faq-accordion section-card group" open={index === 0}>
                  <summary>
                    <span className="faq-count">0{index + 1}</span>
                    <span className="faq-question">{faq.question}</span>
                    <span className="faq-toggle" aria-hidden="true" />
                  </summary>
                  <div className="faq-answer">
                    <p>{faq.answer}</p>
                  </div>
                </details>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="section-reveal service-area-section px-5 py-24 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
          <div>
            <p className="section-kicker text-[#f6d97b]">Service Area</p>
            <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
              รับงานออกแบบ รีโนเวท และก่อสร้างในเชียงใหม่
            </h2>
            <p className="mt-5 max-w-2xl text-xl leading-9 text-white/72">
              ทีมพร้อมนัดสำรวจพื้นที่จริง ประเมินขอบเขตงาน และวางแผนเบื้องต้นสำหรับบ้านพักอาศัยและพื้นที่ใช้งานส่วนตัว
              ในเชียงใหม่และโซนใกล้เคียง
            </p>
          </div>

          <div className="service-area-panel">
            <div className="flex items-center gap-4">
              <span className="service-area-icon">
                <Icon name="location" className="size-8" />
              </span>
              <div>
                <p className="text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">
                  Chiang Mai
                </p>
                <h3 className="mt-1 text-3xl font-extrabold">พื้นที่ให้บริการหลัก</h3>
              </div>
            </div>
            <div className="mt-8 flex flex-wrap gap-3">
              {serviceAreas.map((area) => (
                <span key={area} className="service-area-chip">
                  {area}
                </span>
              ))}
            </div>
            <p className="mt-6 text-lg leading-8 text-white/68">
              พื้นที่นอกเหนือจากนี้สามารถส่งโลเคชันหรือรูปหน้างานให้ทีมช่วยประเมินเบื้องต้นก่อนได้
            </p>
          </div>
        </div>
      </section>

      <section className="section-reveal cta-hero-banner px-5 py-24 text-white lg:px-8">
        <div className="cta-hero-content mx-auto max-w-5xl text-center">
          <p className="text-lg font-extrabold text-[#f6d97b] md:text-2xl">
            “ความฝันเรื่องบ้านของคุณ คือหน้าที่ของเรา”
          </p>
          <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
            <span className="block">เริ่มปรึกษาและวางแผนงานกับ</span>
            <span className="block whitespace-nowrap text-[0.9em] sm:text-[0.95em]">
              34 Build Master Construction
            </span>
          </h2>
          <p className="mx-auto mt-5 max-w-3xl text-xl leading-9 text-white/78">
            ส่งรูปพื้นที่ ไอเดีย หรือรายละเอียดเบื้องต้นให้ทีมช่วยประเมินก่อนเริ่มงานจริง ผ่านช่องทางที่คุณสะดวก
          </p>

          <div className="cta-banner-actions">
              <a
                href={socialLinks[0].href}
                className="cta-banner-button"
                target="_blank"
                rel="noreferrer"
              >
                <span className="cta-banner-icon">
                  <Icon name="facebook" className="size-6" />
                </span>
                <span>
                  <span className="block text-xs font-bold uppercase tracking-[0.16em]">Facebook</span>
                  <span className="block text-lg font-extrabold">ส่งข้อความหาเรา</span>
                </span>
              </a>

              <a
                href={socialLinks[2].href}
                className="cta-banner-button"
                target="_blank"
                rel="noreferrer"
              >
                <span className="cta-banner-icon">
                  <Icon name="line" className="size-6" />
                </span>
                <span>
                  <span className="block text-xs font-bold uppercase tracking-[0.16em]">LINE OA</span>
                  <span className="block text-lg font-extrabold">แอดไลน์ปรึกษางาน</span>
                </span>
              </a>

              <a href={siteConfig.phoneHref} className="cta-banner-button">
                <span className="cta-banner-icon">
                  <Icon name="phone" className="size-6" />
                </span>
                <span>
                  <span className="block text-xs font-bold uppercase tracking-[0.16em]">Phone</span>
                  <span className="block text-lg font-extrabold">081-9512-297</span>
                </span>
              </a>
          </div>
        </div>
      </section>

      <section id="contact" className="bg-luxury-section px-5 py-20 text-white lg:px-8 lg:py-24">
        <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-stretch">
          <div className="relative overflow-hidden rounded-[2rem] border border-[#f6d97b]/28 bg-[#112416]/72 p-6 shadow-[0_36px_120px_rgba(0,0,0,0.28)] md:p-10">
            <div className="luxury-rings absolute -right-56 -top-56 h-[420px] w-[420px] rounded-full opacity-60" />
            <div className="relative">
              <p className="section-kicker text-[#f6d97b]">Contact</p>
              <h2 className="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">
                เล่าไอเดียบ้านของคุณ แล้วให้ทีมช่วยประเมินขั้นแรก
              </h2>
              <p className="mt-5 max-w-2xl text-lg leading-9 text-white/72">
                ส่งข้อมูลพื้นที่ งบประมาณคร่าว ๆ และความต้องการหลักไว้ก่อน
                ทีมงานจะใช้เป็นข้อมูลตั้งต้นสำหรับการนัดสำรวจและสรุปขอบเขตงานจริง
              </p>

              <div className="mt-10 grid gap-4">
                <a href={siteConfig.phoneHref} className="contact-info-row group">
                  <span className="icon-medallion shrink-0">
                    <Icon name="phone" className="size-6" />
                  </span>
                  <span>
                    <span className="block text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">
                      Phone
                    </span>
                    <span className="mt-1 block text-2xl font-extrabold">{siteConfig.phoneDisplay}</span>
                  </span>
                </a>
                <a href={`mailto:${siteConfig.email}`} className="contact-info-row group">
                  <span className="icon-medallion shrink-0">
                    <Icon name="mail" className="size-6" />
                  </span>
                  <span>
                    <span className="block text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">
                      Email
                    </span>
                    <span className="mt-1 block break-all text-xl font-extrabold">
                      {siteConfig.email}
                    </span>
                  </span>
                </a>
                <div className="contact-info-row">
                  <span className="icon-medallion shrink-0">
                    <Icon name="location" className="size-6" />
                  </span>
                  <span>
                    <span className="block text-sm font-extrabold uppercase tracking-[0.18em] text-[#f6d97b]">
                      Service Area
                    </span>
                    <span className="mt-1 block text-xl font-extrabold">
                      เชียงใหม่ และพื้นที่ใกล้เคียง
                    </span>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <form
          action={`mailto:${siteConfig.email}`}
            className="section-card border border-[#f6d97b]/30 bg-[#fbf7ec]/95 p-6 text-[#112416] shadow-[0_36px_120px_rgba(0,0,0,0.18)] backdrop-blur md:p-8"
            encType="text/plain"
            method="post"
          >
            <div className="flex items-start justify-between gap-4">
              <div>
                <p className="section-kicker">Request Quote</p>
                <h3 className="mt-4 text-3xl font-extrabold leading-tight sm:text-4xl">
                  ฝากรายละเอียดเพื่อให้ทีมติดต่อกลับ
                </h3>
              </div>
              <span className="icon-medallion hidden sm:grid">
                <Icon name="send" className="size-7" />
              </span>
            </div>

            <div className="mt-8 grid gap-5 sm:grid-cols-2">
              <label className="form-field">
                <span>ชื่อผู้ติดต่อ</span>
                <input name="name" placeholder="ชื่อ-นามสกุล" type="text" />
              </label>
              <label className="form-field">
                <span>เบอร์โทร</span>
                <input name="phone" placeholder="08x-xxx-xxxx" type="tel" />
              </label>
              <label className="form-field sm:col-span-2">
                <span>ประเภทงาน</span>
                <select defaultValue="" name="service">
                  <option disabled value="">
                    เลือกประเภทงานที่สนใจ
                  </option>
                  <option>ออกแบบบ้าน</option>
                  <option>รีโนเวทบ้าน</option>
                  <option>สร้างบ้าน</option>
                  <option>บิวท์อิน</option>
                </select>
              </label>
              <label className="form-field">
                <span>พื้นที่โครงการ</span>
                <input name="location" placeholder="อำเภอ / จังหวัด" type="text" />
              </label>
              <label className="form-field">
                <span>งบประมาณคร่าว ๆ</span>
                <input name="budget" placeholder="เช่น 800,000 - 1,500,000" type="text" />
              </label>
              <label className="form-field sm:col-span-2">
                <span>รายละเอียดเพิ่มเติม</span>
                <textarea
                  name="message"
                  placeholder="เล่าขนาดพื้นที่ สไตล์ที่ชอบ หรือสิ่งที่อยากปรับปรุง"
                  rows={5}
                />
              </label>
            </div>

            <div className="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
              <button
                className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 font-extrabold text-[#112416]"
                type="submit"
              >
                <Icon name="send" className="size-5" />
                ส่งรายละเอียด
              </button>
              <p className="text-sm leading-6 text-[#4d5b50]">
                ตอนนี้ฟอร์มจะเปิดอีเมลของเครื่องก่อน ระบบบันทึกเข้าหลังบ้านจะทำในขั้นต่อไป
              </p>
            </div>
          </form>
        </div>
      </section>

      <div className="floating-contact" aria-label="ช่องทางติดต่อด่วน">
        <a href={siteConfig.phoneHref} aria-label="โทรหา 34 Build Master Construction">
          <Icon name="phone" className="size-5" />
        </a>
        <a href={socialLinks[2].href} aria-label="ติดต่อผ่าน LINE OA" target="_blank" rel="noreferrer">
          <Icon name="line" className="size-5" />
        </a>
        <a href={socialLinks[0].href} aria-label="ติดต่อผ่าน Facebook" target="_blank" rel="noreferrer">
          <Icon name="facebook" className="size-5" />
        </a>
      </div>

      <footer className="bg-[#112416] px-5 py-14 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-10 border-t border-[#f6d97b]/20 pt-10 lg:grid-cols-[1.2fr_0.7fr_0.7fr_1fr]">
          <div>
            <a href={sitePath("/")} className="flex items-center gap-3" aria-label="34 Build Master Construction">
              <span className="grid size-14 place-items-center rounded-2xl border border-[#f6d97b]/40 bg-[#053920] text-xl font-extrabold text-[#fdf0a3]">
                34
              </span>
              <span className="leading-tight">
                <span className="block text-lg font-extrabold uppercase tracking-[0.16em]">
                  Build Master
                </span>
                <span className="block text-xs uppercase tracking-[0.24em] text-[#f6d97b]">
                  Construction
                </span>
              </span>
            </a>
            <p className="mt-5 max-w-sm text-base leading-8 text-white/62">
              สร้างสรรค์คุณภาพ มุ่งมั่นในทุกงานก่อสร้าง
              สำหรับงานออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน
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
              {services.map((service) => (
                <li key={service.title}>
                  <a className="footer-link" href={sitePath("/services")}>
                    {service.title}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="footer-heading">เมนู</h3>
            <ul className="mt-4 space-y-3 text-white/66">
              <li>
                <a className="footer-link" href={sitePath("/about")}>
                  เกี่ยวกับเรา
                </a>
              </li>
              <li>
                <a className="footer-link" href="#updates">
                  อัปเดตงาน
                </a>
              </li>
              <li>
                <a className="footer-link" href={sitePath("/services")}>
                  บริการ
                </a>
              </li>
              <li>
                <a className="footer-link" href={sitePath("/contact")}>
                  ติดต่อเรา
                </a>
              </li>
            </ul>
          </div>

          <div>
            <h3 className="footer-heading">ติดต่อ</h3>
            <ul className="mt-4 space-y-4 text-white/72">
              <li className="flex gap-3">
                <Icon name="phone" className="mt-1 size-5 shrink-0 text-[#f6d97b]" />
                <a className="footer-link" href={siteConfig.phoneHref}>
                  {siteConfig.phoneDisplay}
                </a>
              </li>
              <li className="flex gap-3">
                <Icon name="mail" className="mt-1 size-5 shrink-0 text-[#f6d97b]" />
                <a className="footer-link break-all" href={`mailto:${siteConfig.email}`}>
                  {siteConfig.email}
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
    </main>
  );
}
