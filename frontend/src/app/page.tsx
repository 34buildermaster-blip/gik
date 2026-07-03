import Image from "next/image";

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
  | "check"
  | "plan"
  | "tools";

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
];

export default function Home() {
  return (
    <main className="min-h-screen bg-[#fbf7ec] text-[17px] text-[#112416] md:text-lg">
      <header className="sticky top-0 z-40 border-b border-[#f6d97b]/20 bg-[#053920]/95 text-white shadow-[0_18px_60px_rgba(0,0,0,0.2)] backdrop-blur">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
          <a href="#" className="group flex items-center gap-3" aria-label="34 Build Master Construction">
            <span className="relative grid size-12 place-items-center overflow-hidden rounded-2xl bg-[#112416] text-lg font-black text-[#fdf0a3] ring-1 ring-[#f6d97b]/40">
              <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#fdf0a3] to-transparent" />
              34
            </span>
            <span className="leading-tight">
              <span className="block text-base font-black uppercase tracking-[0.16em]">
                Build Master
              </span>
              <span className="block text-[11px] uppercase tracking-[0.22em] text-[#f6d97b]">
                Construction
              </span>
            </span>
          </a>

          <nav className="hidden items-center gap-7 text-sm font-semibold text-white/78 md:flex">
            <a href="#services" className="transition hover:text-[#f6d97b]">
              บริการ
            </a>
            <a href="#projects" className="transition hover:text-[#f6d97b]">
              ผลงาน
            </a>
            <a href="#process" className="transition hover:text-[#f6d97b]">
              ขั้นตอน
            </a>
            <a href="#contact" className="transition hover:text-[#f6d97b]">
              ติดต่อ
            </a>
          </nav>

          <a
            href="tel:+66819512297"
            className="gold-button inline-flex min-h-11 items-center justify-center gap-2 px-4 text-sm font-black text-[#112416]"
          >
            <Icon name="phone" className="size-4" />
            โทรปรึกษา
          </a>
        </div>
      </header>

      <section className="relative min-h-[calc(100vh-81px)] overflow-hidden bg-[#053920]">
        <Image
          src="/hero-construction.png"
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
              <p className="mb-6 inline-flex border border-[#f6d97b]/40 bg-[#112416]/78 px-4 py-2 text-sm font-bold text-[#fff3b8] shadow-[0_20px_60px_rgba(0,0,0,0.34)] backdrop-blur">
                สร้างสรรค์คุณภาพ มุ่งมั่นในทุกงานก่อสร้าง
              </p>
              <h1 className="text-5xl font-black leading-[0.95] text-[#fffaf0] drop-shadow-[0_8px_30px_rgba(0,0,0,0.72)] sm:text-6xl lg:text-8xl">
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
                className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 text-base font-black text-[#112416]"
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
                <dt className="gold-text text-3xl font-black">4</dt>
                <dd className="mt-1 text-sm text-white/68">บริการหลัก</dd>
              </div>
              <div className="luxury-stat">
                <dt className="gold-text text-3xl font-black">1</dt>
                <dd className="mt-1 text-sm text-white/68">ทีมดูแลครบงาน</dd>
              </div>
              <div className="luxury-stat">
                <dt className="gold-text text-3xl font-black">360</dt>
                <dd className="mt-1 text-sm text-white/68">วางแผนครบมุม</dd>
              </div>
            </dl>
          </div>
        </div>
      </section>

      <section id="services" className="section-reveal bg-material-section relative overflow-hidden px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-10 lg:grid-cols-[0.78fr_1.22fr] lg:items-end">
            <div>
              <p className="section-kicker">Services</p>
              <h2 className="mt-4 text-4xl font-black leading-tight sm:text-6xl">
                งานก่อสร้างที่ดูพรีเมียม เริ่มจากระบบคิดที่ชัดเจน
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
                  <span className="text-sm font-black text-[#053920]/25">
                    {service.number}
                  </span>
                </div>
                <p className="mt-7 text-xs font-black uppercase tracking-[0.22em] text-[#aa7426]">
                  {service.tag}
                </p>
                <h3 className="mt-3 text-3xl font-black text-[#053920]">{service.title}</h3>
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
            <h2 className="mt-4 text-4xl font-black leading-tight sm:text-6xl">
              ผลงานต้องเล่าเรื่องความละเอียด ไม่ใช่แค่โชว์รูปสวย
            </h2>
            <p className="mt-5 text-lg leading-9 text-white/72">
              พื้นที่นี้เตรียมไว้สำหรับ case study, ภาพ before-after และรายละเอียดวัสดุ
              เพื่อให้ลูกค้าเห็นคุณภาพก่อนติดต่อ และช่วยทำ SEO ของแต่ละประเภทงาน
            </p>
          </div>

          <div className="grid gap-5">
            {projects.map((project, index) => (
              <article
                key={project.title}
                className="section-card group grid gap-5 border border-[#f6d97b]/18 bg-[#112416]/62 p-5 shadow-[0_30px_90px_rgba(0,0,0,0.24)] transition hover:-translate-y-1 hover:border-[#f6d97b]/48 md:grid-cols-[120px_1fr]"
              >
                <div className="grid min-h-28 place-items-center rounded-3xl bg-gradient-to-br from-[#aa7426] via-[#f6d97b] to-[#fdf0a3] text-[#053920]">
                  <Icon name={project.icon} className="size-12" />
                </div>
                <div>
                  <p className="text-sm font-black uppercase tracking-[0.2em] text-[#f6d97b]">
                    {project.type}
                  </p>
                  <h3 className="mt-3 text-3xl font-black">{project.title}</h3>
                  <p className="mt-3 text-lg leading-8 text-white/68">{project.detail}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="section-reveal bg-material-section px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
            <div>
              <p className="section-kicker">Brand Personality</p>
              <h2 className="mt-4 text-4xl font-black leading-tight sm:text-6xl">
                บุคลิกแบรนด์จาก guideline ถูกแปลงเป็นประสบการณ์บนเว็บ
              </h2>
            </div>
            <div className="grid gap-4 sm:grid-cols-5">
              {values.map((value) => (
                <div
                  key={value.label}
                  className="section-card border border-[#aa7426]/24 bg-white/92 px-4 py-6 text-center shadow-sm backdrop-blur transition hover:-translate-y-1 hover:border-[#aa7426]/50 hover:shadow-xl"
                >
                  <div className="icon-medallion mx-auto mb-4">
                    <Icon name={value.icon} className="size-7" />
                  </div>
                  <p className="text-sm font-black uppercase tracking-[0.08em] text-[#053920]">
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
          <div className="max-w-3xl">
            <p className="section-kicker text-[#f6d97b]">Process</p>
            <h2 className="mt-4 text-4xl font-black leading-tight sm:text-6xl">
              ขั้นตอนที่คุมความคาดหวังได้ตั้งแต่วันแรก
            </h2>
          </div>

          <ol className="mt-12 grid gap-4 md:grid-cols-4">
            {process.map((step, index) => (
              <li
                key={step}
                className="section-card relative overflow-hidden border border-[#f6d97b]/18 p-6 transition hover:border-[#f6d97b]/50 hover:bg-white/5"
              >
                <div className="flex items-center justify-between gap-4">
                  <span className="gold-text text-sm font-black">0{index + 1}</span>
                  <Icon name={index === 0 ? "plan" : index === 1 ? "drafting" : index === 2 ? "tools" : "check"} className="size-7 text-[#f6d97b]" />
                </div>
                <p className="mt-10 text-2xl font-black leading-snug">{step}</p>
                <span className="absolute bottom-0 left-0 h-1 w-16 bg-gradient-to-r from-[#aa7426] to-[#fdf0a3]" />
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="section-reveal grid overflow-hidden bg-white lg:grid-cols-2">
        <div className="bg-[#fbf7ec] px-5 py-24 lg:px-16">
          <p className="section-kicker">FAQ</p>
          <h2 className="mt-4 text-4xl font-black leading-tight sm:text-6xl">
            คำถามที่ช่วยให้ลูกค้าตัดสินใจง่ายขึ้น
          </h2>
        </div>
        <div className="divide-y divide-[#112416]/10 border-t border-[#112416]/10 lg:border-l lg:border-t-0">
          {faqs.map((faq) => (
            <div key={faq.question} className="px-5 py-8 transition hover:bg-[#fbf7ec] lg:px-10">
              <h3 className="text-2xl font-black text-[#053920]">{faq.question}</h3>
              <p className="mt-3 text-lg leading-8 text-[#4d5b50]">{faq.answer}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="contact" className="section-reveal bg-luxury-section px-5 py-24 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-stretch">
          <div className="relative overflow-hidden rounded-[2rem] border border-[#f6d97b]/28 bg-[#112416]/72 p-6 shadow-[0_36px_120px_rgba(0,0,0,0.28)] md:p-10">
            <div className="luxury-rings absolute -right-56 -top-56 h-[420px] w-[420px] rounded-full opacity-60" />
            <div className="relative">
              <p className="section-kicker text-[#f6d97b]">Contact</p>
              <h2 className="mt-4 text-4xl font-black leading-tight sm:text-6xl">
                เล่าไอเดียบ้านของคุณ แล้วให้ทีมช่วยประเมินขั้นแรก
              </h2>
              <p className="mt-5 max-w-2xl text-lg leading-9 text-white/72">
                ส่งข้อมูลพื้นที่ งบประมาณคร่าว ๆ และความต้องการหลักไว้ก่อน
                ทีมงานจะใช้เป็นข้อมูลตั้งต้นสำหรับการนัดสำรวจและสรุปขอบเขตงานจริง
              </p>

              <div className="mt-10 grid gap-4">
                <a href="tel:+66819512297" className="contact-info-row group">
                  <span className="icon-medallion shrink-0">
                    <Icon name="phone" className="size-6" />
                  </span>
                  <span>
                    <span className="block text-sm font-black uppercase tracking-[0.18em] text-[#f6d97b]">
                      Phone
                    </span>
                    <span className="mt-1 block text-2xl font-black">081-9512-297</span>
                  </span>
                </a>
                <a href="mailto:34buildmaster@gmail.com" className="contact-info-row group">
                  <span className="icon-medallion shrink-0">
                    <Icon name="mail" className="size-6" />
                  </span>
                  <span>
                    <span className="block text-sm font-black uppercase tracking-[0.18em] text-[#f6d97b]">
                      Email
                    </span>
                    <span className="mt-1 block break-all text-xl font-black">
                      34buildmaster@gmail.com
                    </span>
                  </span>
                </a>
                <div className="contact-info-row">
                  <span className="icon-medallion shrink-0">
                    <Icon name="location" className="size-6" />
                  </span>
                  <span>
                    <span className="block text-sm font-black uppercase tracking-[0.18em] text-[#f6d97b]">
                      Service Area
                    </span>
                    <span className="mt-1 block text-xl font-black">
                      เชียงใหม่ และพื้นที่ใกล้เคียง
                    </span>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <form
            action="mailto:34buildmaster@gmail.com"
            className="section-card border border-[#f6d97b]/30 bg-[#fbf7ec]/95 p-6 text-[#112416] shadow-[0_36px_120px_rgba(0,0,0,0.18)] backdrop-blur md:p-8"
            encType="text/plain"
            method="post"
          >
            <div className="flex items-start justify-between gap-4">
              <div>
                <p className="section-kicker">Request Quote</p>
                <h3 className="mt-4 text-3xl font-black leading-tight sm:text-4xl">
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
                className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 font-black text-[#112416]"
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

      <footer className="bg-[#112416] px-5 py-14 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-10 border-t border-[#f6d97b]/20 pt-10 lg:grid-cols-[1.2fr_0.7fr_0.7fr_1fr]">
          <div>
            <a href="#" className="flex items-center gap-3" aria-label="34 Build Master Construction">
              <span className="grid size-14 place-items-center rounded-2xl border border-[#f6d97b]/40 bg-[#053920] text-xl font-black text-[#fdf0a3]">
                34
              </span>
              <span className="leading-tight">
                <span className="block text-lg font-black uppercase tracking-[0.16em]">
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
          </div>

          <div>
            <h3 className="footer-heading">บริการ</h3>
            <ul className="mt-4 space-y-3 text-white/66">
              {services.map((service) => (
                <li key={service.title}>
                  <a className="footer-link" href="#services">
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
                <a className="footer-link" href="#projects">
                  ผลงาน
                </a>
              </li>
              <li>
                <a className="footer-link" href="#process">
                  ขั้นตอน
                </a>
              </li>
              <li>
                <a className="footer-link" href="#contact">
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
        <div className="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-[#f6d97b]/14 pt-6 text-sm text-white/46 sm:flex-row sm:items-center sm:justify-between">
          <p>© 2026 34 Build Master Construction. All rights reserved.</p>
          <p>Modern Luxury Premium Construction</p>
        </div>
      </footer>
    </main>
  );
}
