import Image from "next/image";

const services = [
  {
    title: "ออกแบบบ้าน",
    description:
      "วางแนวคิด ฟังก์ชัน และภาพรวมงานก่อสร้างให้เหมาะกับงบประมาณและการใช้งานจริง",
    tag: "Design",
  },
  {
    title: "รีโนเวทบ้าน",
    description:
      "ปรับบ้านเดิมให้ใช้งานดีขึ้น ตั้งแต่งานโครงสร้าง งานระบบ ไปจนถึงงานตกแต่ง",
    tag: "Renovation",
  },
  {
    title: "สร้างบ้าน",
    description:
      "ดูแลงานสร้างบ้านครบขั้นตอน พร้อมประสานงานหน้างานและตรวจคุณภาพเป็นระบบ",
    tag: "Build",
  },
  {
    title: "บิวท์อิน",
    description:
      "ออกแบบและผลิตเฟอร์นิเจอร์บิวท์อินให้พอดีกับพื้นที่ ใช้วัสดุเหมาะกับสไตล์บ้าน",
    tag: "Built-in",
  },
];

const projects = [
  "บ้านพักอาศัยสไตล์โมเดิร์น",
  "รีโนเวททาวน์โฮม",
  "ครัวและตู้บิวท์อิน",
];

const process = [
  "คุยโจทย์และสำรวจพื้นที่",
  "เสนอแบบและประเมินงบ",
  "วางแผนงานและเริ่มก่อสร้าง",
  "ตรวจรับและส่งมอบงาน",
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
    <main className="min-h-screen bg-[#f7f5f0] text-[#20201d]">
      <header className="sticky top-0 z-30 border-b border-black/10 bg-[#f7f5f0]/90 backdrop-blur">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
          <a href="#" className="flex items-center gap-3" aria-label="34 BM Construction">
            <span className="grid size-11 place-items-center bg-[#20201d] text-sm font-bold text-white">
              34
            </span>
            <span className="leading-tight">
              <span className="block text-base font-bold uppercase tracking-wide">
                BM Construction
              </span>
              <span className="block text-xs text-[#6e6a5f]">
                Design Renovation Build Built-in
              </span>
            </span>
          </a>

          <nav className="hidden items-center gap-7 text-sm font-medium text-[#4c4a43] md:flex">
            <a href="#services" className="hover:text-[#c86428]">
              บริการ
            </a>
            <a href="#projects" className="hover:text-[#c86428]">
              ผลงาน
            </a>
            <a href="#process" className="hover:text-[#c86428]">
              ขั้นตอน
            </a>
            <a href="#contact" className="hover:text-[#c86428]">
              ติดต่อ
            </a>
          </nav>

          <a
            href="tel:+66000000000"
            className="inline-flex min-h-11 items-center justify-center bg-[#c86428] px-4 text-sm font-semibold text-white transition hover:bg-[#a94f1b]"
          >
            โทรปรึกษา
          </a>
        </div>
      </header>

      <section className="relative min-h-[calc(100vh-77px)] overflow-hidden">
        <Image
          src="/hero-construction.png"
          alt="ทีมงาน 34 BM Construction ตรวจแบบหน้าบ้านโมเดิร์น"
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
        <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(24,24,21,0.88)_0%,rgba(24,24,21,0.68)_34%,rgba(24,24,21,0.14)_72%)]" />

        <div className="relative mx-auto flex min-h-[calc(100vh-77px)] max-w-7xl items-center px-5 py-16 lg:px-8">
          <div className="max-w-3xl text-white">
            <p className="mb-5 inline-flex bg-white/14 px-3 py-2 text-sm font-semibold text-[#f2c77a] backdrop-blur">
              รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร
            </p>
            <h1 className="text-4xl font-bold leading-tight sm:text-5xl lg:text-7xl">
              34 BM Construction
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-white/86">
              ทีมงานก่อสร้างและตกแต่งภายในที่ช่วยเปลี่ยนไอเดียบ้านให้เป็นงานจริง
              วางแผนชัดเจน คุมงานเป็นระบบ และสื่อสารตรงไปตรงมาตั้งแต่เริ่มจนส่งมอบ
            </p>

            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <a
                href="#contact"
                className="inline-flex min-h-12 items-center justify-center bg-[#f2b84b] px-6 text-base font-bold text-[#20201d] transition hover:bg-[#ffd07a]"
              >
                ขอประเมินราคาฟรี
              </a>
              <a
                href="#projects"
                className="inline-flex min-h-12 items-center justify-center border border-white/55 px-6 text-base font-semibold text-white transition hover:bg-white hover:text-[#20201d]"
              >
                ดูผลงานตัวอย่าง
              </a>
            </div>

            <dl className="mt-12 grid max-w-2xl grid-cols-3 gap-4 border-t border-white/24 pt-6">
              <div>
                <dt className="text-2xl font-bold">4</dt>
                <dd className="mt-1 text-sm text-white/72">บริการหลัก</dd>
              </div>
              <div>
                <dt className="text-2xl font-bold">1</dt>
                <dd className="mt-1 text-sm text-white/72">ทีมดูแลครบงาน</dd>
              </div>
              <div>
                <dt className="text-2xl font-bold">SEO</dt>
                <dd className="mt-1 text-sm text-white/72">พร้อมต่อยอด</dd>
              </div>
            </dl>
          </div>
        </div>
      </section>

      <section id="services" className="px-5 py-20 lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="max-w-2xl">
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#c86428]">
              Services
            </p>
            <h2 className="mt-3 text-3xl font-bold sm:text-5xl">
              บริการสำหรับบ้านที่ต้องการทั้งดีไซน์และความเรียบร้อยหน้างาน
            </h2>
          </div>

          <div className="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {services.map((service) => (
              <article
                key={service.title}
                className="border border-black/10 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
              >
                <p className="text-xs font-bold uppercase tracking-[0.18em] text-[#6b8a5a]">
                  {service.tag}
                </p>
                <h3 className="mt-5 text-2xl font-bold">{service.title}</h3>
                <p className="mt-4 leading-7 text-[#5e5a50]">{service.description}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="projects" className="bg-white px-5 py-20 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#c86428]">
              Selected Work
            </p>
            <h2 className="mt-3 text-3xl font-bold sm:text-5xl">
              วางพื้นที่โชว์ผลงานก่อน-หลัง เพื่อสร้างความมั่นใจก่อนลูกค้าทักหา
            </h2>
            <p className="mt-5 leading-8 text-[#5e5a50]">
              ตอนนี้ใช้ตัวอย่าง UX ก่อน เมื่อมีรูปงานจริง เราจะเปลี่ยนเป็น gallery,
              case study และหน้าแยกสำหรับแต่ละโปรเจกต์เพื่อทำ SEO ได้ละเอียดขึ้น
            </p>
          </div>

          <div className="grid gap-4 sm:grid-cols-3">
            {projects.map((project, index) => (
              <article
                key={project}
                className="flex min-h-64 flex-col justify-between bg-[#20201d] p-5 text-white"
              >
                <span className="text-sm text-white/56">0{index + 1}</span>
                <div>
                  <p className="text-sm font-semibold text-[#f2b84b]">
                    Project type
                  </p>
                  <h3 className="mt-2 text-2xl font-bold">{project}</h3>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="px-5 py-20 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-3">
          <div className="lg:col-span-1">
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#c86428]">
              Why Us
            </p>
            <h2 className="mt-3 text-3xl font-bold">จุดที่ลูกค้าควรรู้ตั้งแต่หน้าแรก</h2>
          </div>

          <div className="grid gap-4 md:grid-cols-3 lg:col-span-2">
            {["ประเมินงานตามขอบเขตจริง", "สื่อสารงานเป็นขั้นตอน", "ออกแบบเพื่อการใช้งานระยะยาว"].map(
              (item) => (
                <div key={item} className="border-l-4 border-[#c86428] bg-white p-6">
                  <p className="text-xl font-bold">{item}</p>
                  <p className="mt-3 leading-7 text-[#5e5a50]">
                    ช่วยให้เจ้าของบ้านตัดสินใจง่าย เห็นภาพรวมก่อนเริ่ม และลดความเสี่ยงระหว่างงาน
                  </p>
                </div>
              ),
            )}
          </div>
        </div>
      </section>

      <section id="process" className="bg-[#20201d] px-5 py-20 text-white lg:px-8">
        <div className="mx-auto max-w-7xl">
          <div className="max-w-2xl">
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#f2b84b]">
              Process
            </p>
            <h2 className="mt-3 text-3xl font-bold sm:text-5xl">
              ขั้นตอนเรียบง่าย แต่ทำให้ลูกค้าเห็นความคืบหน้าชัด
            </h2>
          </div>

          <ol className="mt-10 grid gap-4 md:grid-cols-4">
            {process.map((step, index) => (
              <li key={step} className="border border-white/18 p-6">
                <span className="text-sm font-bold text-[#f2b84b]">0{index + 1}</span>
                <p className="mt-8 text-xl font-bold">{step}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="grid bg-white lg:grid-cols-2">
        <div className="px-5 py-20 lg:px-16">
          <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#c86428]">
            FAQ
          </p>
          <h2 className="mt-3 text-3xl font-bold sm:text-5xl">คำถามที่ลูกค้าถามบ่อย</h2>
        </div>
        <div className="divide-y divide-black/10 border-t border-black/10 lg:border-l lg:border-t-0">
          {faqs.map((faq) => (
            <div key={faq.question} className="px-5 py-8 lg:px-10">
              <h3 className="text-xl font-bold">{faq.question}</h3>
              <p className="mt-3 leading-7 text-[#5e5a50]">{faq.answer}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="contact" className="px-5 py-20 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-8 bg-[#ded7c8] p-6 md:p-10 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#7a4b1f]">
              Contact
            </p>
            <h2 className="mt-3 text-3xl font-bold sm:text-5xl">
              พร้อมเริ่มคุยแบบ รีโนเวท หรือบิวท์อินบ้านของคุณ
            </h2>
            <p className="mt-4 max-w-2xl leading-8 text-[#5b5548]">
              ส่งรูปพื้นที่และรายละเอียดคร่าว ๆ ให้ทีมงานประเมินเบื้องต้นได้ทันที
              จากนั้นค่อยนัดสำรวจและสรุปขอบเขตงานให้ชัดเจน
            </p>
          </div>
          <div className="flex flex-col gap-3 sm:flex-row lg:flex-col">
            <a
              href="tel:+66000000000"
              className="inline-flex min-h-12 items-center justify-center bg-[#20201d] px-6 font-bold text-white transition hover:bg-[#3b3a34]"
            >
              โทรหาเรา
            </a>
            <a
              href="https://line.me"
              className="inline-flex min-h-12 items-center justify-center border border-[#20201d] px-6 font-bold text-[#20201d] transition hover:bg-white"
            >
              แอด Line
            </a>
          </div>
        </div>
      </section>
    </main>
  );
}
