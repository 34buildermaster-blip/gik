import Image from "next/image";

const services = [
  {
    title: "ออกแบบบ้าน",
    description:
      "ออกแบบแนวคิด ฟังก์ชัน และภาพรวมงานก่อสร้างให้สอดคล้องกับงบประมาณ ไลฟ์สไตล์ และคุณภาพระยะยาว",
    tag: "Design",
    number: "01",
  },
  {
    title: "รีโนเวทบ้าน",
    description:
      "ปรับบ้านเดิมให้กลับมาสวย ใช้งานดี และมีระบบงานที่ชัดเจน ตั้งแต่งานโครงสร้างถึงงานตกแต่ง",
    tag: "Renovation",
    number: "02",
  },
  {
    title: "สร้างบ้าน",
    description:
      "ดูแลงานสร้างบ้านด้วยแผนงานเป็นขั้นตอน คุมคุณภาพหน้างาน และสื่อสารกับเจ้าของบ้านอย่างสม่ำเสมอ",
    tag: "Build",
    number: "03",
  },
  {
    title: "บิวท์อิน",
    description:
      "ออกแบบและผลิตเฟอร์นิเจอร์บิวท์อินให้ลงตัวกับพื้นที่จริง เลือกวัสดุและรายละเอียดให้เข้ากับบ้าน",
    tag: "Built-in",
    number: "04",
  },
];

const projects = [
  {
    type: "Residential",
    title: "บ้านพักอาศัยสไตล์โมเดิร์น",
    detail: "ออกแบบภาพรวมพื้นที่อยู่อาศัยให้สะอาด โปร่ง และดูแลรักษาง่าย",
  },
  {
    type: "Renovation",
    title: "รีโนเวทบ้านเดิม",
    detail: "จัดลำดับงานระบบ โครงสร้าง และผิวจบ เพื่อให้บ้านกลับมาใช้งานได้ดี",
  },
  {
    type: "Interior",
    title: "ครัวและตู้บิวท์อิน",
    detail: "เพิ่มพื้นที่เก็บของและความเรียบร้อย โดยคุมโทนวัสดุให้ต่อเนื่องทั้งบ้าน",
  },
];

const process = [
  "รับโจทย์และสำรวจพื้นที่",
  "เสนอแนวทางพร้อมงบประมาณ",
  "วางแผนงานและคุมหน้างาน",
  "ตรวจคุณภาพและส่งมอบ",
];

const values = [
  "Modern",
  "Luxury",
  "Premium",
  "Trustworthy",
  "Professional",
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
    <main className="min-h-screen bg-[#fbf7ec] text-[#112416]">
      <header className="sticky top-0 z-40 border-b border-[#f6d97b]/20 bg-[#053920]/95 text-white shadow-[0_18px_60px_rgba(0,0,0,0.2)] backdrop-blur">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
          <a href="#" className="group flex items-center gap-3" aria-label="34 Build Master Construction">
            <span className="relative grid size-12 place-items-center overflow-hidden bg-[#112416] text-lg font-black text-[#fdf0a3] ring-1 ring-[#f6d97b]/40">
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
            className="gold-button inline-flex min-h-11 items-center justify-center px-4 text-sm font-black text-[#112416]"
          >
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
              <p className="mt-6 max-w-2xl text-lg leading-8 text-white/90 drop-shadow-[0_4px_18px_rgba(0,0,0,0.62)]">
                รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร ด้วยภาพลักษณ์ที่ทันสมัย
                งานจบเรียบร้อย และการดูแลโครงการแบบมืออาชีพตั้งแต่ต้นจนส่งมอบ
              </p>
            </div>

            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <a
                href="#contact"
                className="gold-button inline-flex min-h-12 items-center justify-center px-7 text-base font-black text-[#112416]"
              >
                ขอประเมินราคาฟรี
              </a>
              <a
                href="#projects"
                className="inline-flex min-h-12 items-center justify-center border border-[#f6d97b]/50 px-7 text-base font-bold text-[#fdf0a3] transition hover:bg-[#f6d97b] hover:text-[#112416]"
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

      <section id="services" className="relative overflow-hidden px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-10 lg:grid-cols-[0.78fr_1.22fr] lg:items-end">
            <div>
              <p className="section-kicker">Services</p>
              <h2 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
                งานก่อสร้างที่ดูพรีเมียม เริ่มจากระบบคิดที่ชัดเจน
              </h2>
            </div>
            <p className="max-w-2xl leading-8 text-[#4d5b50] lg:ml-auto">
              เราวางโครงหน้าเว็บให้สื่อสารแบบแบรนด์หรู: ใช้พื้นที่หายใจมากขึ้น
              สีเขียวเข้มให้ความมั่นคง และทองเป็น accent เพื่อเน้นคุณภาพกับความเชื่อถือ
            </p>
          </div>

          <div className="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            {services.map((service) => (
              <article key={service.title} className="luxe-card group p-6">
                <div className="flex items-start justify-between gap-4">
                  <p className="text-xs font-black uppercase tracking-[0.22em] text-[#aa7426]">
                    {service.tag}
                  </p>
                  <span className="text-sm font-black text-[#053920]/25">
                    {service.number}
                  </span>
                </div>
                <h3 className="mt-8 text-2xl font-black text-[#053920]">{service.title}</h3>
                <p className="mt-4 leading-7 text-[#4d5b50]">{service.description}</p>
                <div className="mt-7 h-px w-full bg-gradient-to-r from-[#aa7426] via-[#f6d97b] to-transparent transition group-hover:w-4/5" />
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="projects" className="bg-[#053920] px-5 py-24 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.86fr_1.14fr]">
          <div>
            <p className="section-kicker text-[#f6d97b]">Selected Work</p>
            <h2 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
              ผลงานต้องเล่าเรื่องความละเอียด ไม่ใช่แค่โชว์รูปสวย
            </h2>
            <p className="mt-5 leading-8 text-white/72">
              พื้นที่นี้เตรียมไว้สำหรับ case study, ภาพ before-after และรายละเอียดวัสดุ
              เพื่อให้ลูกค้าเห็นคุณภาพก่อนติดต่อ และช่วยทำ SEO ของแต่ละประเภทงาน
            </p>
          </div>

          <div className="grid gap-5">
            {projects.map((project, index) => (
              <article
                key={project.title}
                className="group grid gap-5 border border-[#f6d97b]/18 bg-[#112416]/62 p-5 shadow-[0_30px_90px_rgba(0,0,0,0.24)] transition hover:-translate-y-1 hover:border-[#f6d97b]/48 md:grid-cols-[120px_1fr]"
              >
                <div className="grid min-h-28 place-items-center bg-gradient-to-br from-[#aa7426] via-[#f6d97b] to-[#fdf0a3] text-4xl font-black text-[#053920]">
                  0{index + 1}
                </div>
                <div>
                  <p className="text-sm font-black uppercase tracking-[0.2em] text-[#f6d97b]">
                    {project.type}
                  </p>
                  <h3 className="mt-3 text-2xl font-black">{project.title}</h3>
                  <p className="mt-3 leading-7 text-white/68">{project.detail}</p>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="px-5 py-24 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="grid gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
            <div>
              <p className="section-kicker">Brand Personality</p>
              <h2 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
                บุคลิกแบรนด์จาก guideline ถูกแปลงเป็นประสบการณ์บนเว็บ
              </h2>
            </div>
            <div className="grid gap-4 sm:grid-cols-5">
              {values.map((value) => (
                <div
                  key={value}
                  className="border border-[#aa7426]/24 bg-white px-4 py-6 text-center shadow-sm"
                >
                  <div className="mx-auto mb-4 grid size-12 place-items-center border border-[#aa7426]/35 text-lg font-black text-[#aa7426]">
                    {value.slice(0, 1)}
                  </div>
                  <p className="text-sm font-black uppercase tracking-[0.08em] text-[#053920]">
                    {value}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section id="process" className="bg-[#112416] px-5 py-24 text-white lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="max-w-3xl">
            <p className="section-kicker text-[#f6d97b]">Process</p>
            <h2 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
              ขั้นตอนที่คุมความคาดหวังได้ตั้งแต่วันแรก
            </h2>
          </div>

          <ol className="mt-12 grid gap-4 md:grid-cols-4">
            {process.map((step, index) => (
              <li
                key={step}
                className="relative overflow-hidden border border-[#f6d97b]/18 p-6 transition hover:border-[#f6d97b]/50 hover:bg-white/5"
              >
                <span className="gold-text text-sm font-black">0{index + 1}</span>
                <p className="mt-10 text-xl font-black">{step}</p>
                <span className="absolute bottom-0 left-0 h-1 w-16 bg-gradient-to-r from-[#aa7426] to-[#fdf0a3]" />
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="grid bg-white lg:grid-cols-2">
        <div className="bg-[#fbf7ec] px-5 py-24 lg:px-16">
          <p className="section-kicker">FAQ</p>
          <h2 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
            คำถามที่ช่วยให้ลูกค้าตัดสินใจง่ายขึ้น
          </h2>
        </div>
        <div className="divide-y divide-[#112416]/10 border-t border-[#112416]/10 lg:border-l lg:border-t-0">
          {faqs.map((faq) => (
            <div key={faq.question} className="px-5 py-8 transition hover:bg-[#fbf7ec] lg:px-10">
              <h3 className="text-xl font-black text-[#053920]">{faq.question}</h3>
              <p className="mt-3 leading-7 text-[#4d5b50]">{faq.answer}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="contact" className="bg-[#053920] px-5 py-24 text-white lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-8 border border-[#f6d97b]/28 bg-[#112416]/70 p-6 shadow-[0_36px_120px_rgba(0,0,0,0.28)] md:p-10 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <p className="section-kicker text-[#f6d97b]">Contact</p>
            <h2 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
              พร้อมเริ่มคุยแบบ รีโนเวท หรือบิวท์อินบ้านของคุณ
            </h2>
            <p className="mt-5 max-w-2xl leading-8 text-white/72">
              ส่งรูปพื้นที่และรายละเอียดคร่าว ๆ ให้ทีมงานประเมินเบื้องต้น
              จากนั้นนัดสำรวจและสรุปขอบเขตงานให้ชัดเจนก่อนเริ่มจริง
            </p>
          </div>
          <div className="flex flex-col gap-3 sm:flex-row lg:flex-col">
            <a
              href="tel:+66819512297"
              className="gold-button inline-flex min-h-12 items-center justify-center px-7 font-black text-[#112416]"
            >
              โทร 081-9512-297
            </a>
            <a
              href="mailto:34buildmaster@gmail.com"
              className="inline-flex min-h-12 items-center justify-center border border-[#f6d97b]/50 px-7 font-black text-[#fdf0a3] transition hover:bg-[#f6d97b] hover:text-[#112416]"
            >
              ส่งอีเมล
            </a>
          </div>
        </div>
      </section>
    </main>
  );
}
