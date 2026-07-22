"use client";

import Link from "next/link";
import { useState } from "react";
import { siteConfig } from "@/lib/site-config";

const categories = [
  ["general", "ข้อมูลทั่วไป"],
  ["planning", "การวางแผนโครงการ"],
  ["design", "แบบและวัสดุ"],
  ["construction", "ขั้นตอนก่อสร้าง"],
  ["payment", "ค่าใช้จ่ายและการชำระเงิน"],
] as const;

type CategoryId = (typeof categories)[number][0];
type Question = { question: string; answer: string };

const questions: Record<CategoryId, Question[]> = {
  general: [
    { question: "34 Build Master ให้บริการอะไรบ้าง?", answer: "เราให้บริการออกแบบบ้าน รีโนเวท สร้างบ้าน และบิวท์อินแบบครบวงจร ตั้งแต่รับโจทย์ สำรวจพื้นที่ วางแผน ไปจนถึงตรวจงานและส่งมอบ" },
    { question: "ให้บริการในพื้นที่ใดบ้าง?", answer: `พื้นที่หลักของเราคือ${siteConfig.area} ส่วนโครงการนอกพื้นที่สามารถส่งรายละเอียดมาให้ทีมประเมินก่อนได้` },
    { question: "มีบริการให้คำปรึกษาเบื้องต้นหรือไม่?", answer: "มีครับ ส่งข้อมูลพื้นที่ ความต้องการ และงบประมาณคร่าว ๆ เพื่อให้ทีมช่วยประเมินแนวทางก่อนนัดสำรวจจริงได้" },
    { question: "นัดดูผลงานหรือคุยกับทีมได้อย่างไร?", answer: `ติดต่อผ่าน Facebook, LINE OA หรือโทร ${siteConfig.phoneDisplay} เพื่อแจ้งประเภทงานและช่วงเวลาที่สะดวกได้เลย` },
  ],
  planning: [
    { question: "ก่อนเริ่มโครงการต้องเตรียมข้อมูลอะไรบ้าง?", answer: "เตรียมขนาดพื้นที่ รูปหน้างาน ความต้องการใช้งาน งบประมาณคร่าว ๆ และช่วงเวลาที่ต้องการเริ่มงาน" },
    { question: "ใช้เวลาประเมินราคาเบื้องต้นนานเท่าไร?", answer: "ขึ้นอยู่กับความครบถ้วนของข้อมูลและขนาดโครงการ ทีมจะแจ้งกรอบเวลาหลังรับข้อมูลและสำรวจพื้นที่แล้ว" },
    { question: "กำหนดระยะเวลาก่อสร้างอย่างไร?", answer: "ประเมินจากขอบเขตงาน แบบ วัสดุ สภาพพื้นที่ และลำดับงาน ก่อนจัดทำแผนดำเนินงานแต่ละช่วง" },
    { question: "เปลี่ยนรายละเอียดหลังเริ่มงานได้หรือไม่?", answer: "ทำได้ โดยทีมจะประเมินผลต่อราคา ระยะเวลา และงานส่วนอื่นให้ทราบก่อนยืนยันทุกครั้ง" },
  ],
  design: [
    { question: "มีบริการออกแบบก่อนก่อสร้างหรือไม่?", answer: "มีบริการวางผัง ออกแบบแนวทาง และจัดทำรายละเอียดที่จำเป็นตามประเภทโครงการก่อนลงมือจริง" },
    { question: "ลูกค้าสามารถเลือกวัสดุเองได้หรือไม่?", answer: "ได้ครับ ทีมช่วยแนะนำตัวเลือกที่เหมาะกับงบ การใช้งาน และภาพรวมของบ้านได้" },
    { question: "มีภาพ 3D หรือแบบตัวอย่างให้ดูก่อนไหม?", answer: "งานที่มีขอบเขตการออกแบบสามารถจัดทำภาพหรือแบบประกอบตามรายละเอียดในข้อเสนอได้" },
    { question: "ใช้วัสดุที่ลูกค้าจัดหาเองได้หรือไม่?", answer: "ทำได้บางรายการ โดยต้องตรวจสอบสเปก จำนวน ระยะเวลาจัดส่ง และเงื่อนไขการรับประกันก่อน" },
  ],
  construction: [
    { question: "ติดตามความคืบหน้าของโครงการได้อย่างไร?", answer: "ลูกค้าที่ได้รับบัญชีเปิดหน้า “งานของฉัน” เพื่อดูเปอร์เซ็นต์ Timeline รูปหน้างาน วันที่ และขั้นตอนล่าสุดได้ตลอดเวลา" },
    { question: "มีการตรวจคุณภาพระหว่างก่อสร้างหรือไม่?", answer: "ทีมควบคุมงานตรวจตามลำดับและจุดสำคัญก่อนเดินหน้าขั้นถัดไป พร้อมบันทึกรายละเอียดโครงการ" },
    { question: "สามารถเข้าตรวจหน้างานด้วยตัวเองได้ไหม?", answer: "ได้ครับ ควรนัดผู้ดูแลล่วงหน้าเพื่อความปลอดภัยและให้ทีมเตรียมอธิบายงานได้ครบถ้วน" },
    { question: "หากหน้างานล่าช้าจะมีการแจ้งอย่างไร?", answer: "ทีมจะแจ้งสาเหตุ ผลกระทบ และแผนปรับงานผ่านผู้ดูแล รวมถึงอัปเดตในระบบติดตามงาน" },
  ],
  payment: [
    { question: "ราคาของโครงการคำนวณจากอะไร?", answer: "พิจารณาจากขอบเขตงาน พื้นที่ แบบ วัสดุ สภาพหน้างาน ระบบประกอบอาคาร และระยะเวลาดำเนินงาน" },
    { question: "มีค่าใช้จ่ายแอบแฝงหรือไม่?", answer: "รายการจะระบุในข้อเสนอ หากมีงานเพิ่มหรือเปลี่ยนแปลง ทีมจะเสนอราคาและผลกระทบให้อนุมัติก่อน" },
    { question: "แบ่งชำระเงินเป็นงวดได้หรือไม่?", answer: "โดยทั่วไปแบ่งชำระตามงวดงานและความคืบหน้าที่กำหนดในสัญญา" },
    { question: "ควบคุมงบประมาณควรเริ่มอย่างไร?", answer: "แจ้งกรอบงบและจัดลำดับสิ่งจำเป็นตั้งแต่ต้น ทีมจะช่วยเปรียบเทียบแนวทางและวัสดุก่อนสรุปแบบ" },
  ],
};

function FaqIcon({ name }: { name: "chat" | "phone" | "clock" | "arrow" }) {
  const paths = {
    chat: <><path d="M5 18.5 6.2 15A7 7 0 1 1 12 18H8.5z" /><path d="M8 11h.01M12 11h.01M16 11h.01" /></>,
    phone: <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.7a16 16 0 0 0 6.2 6.2l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z" />,
    clock: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></>,
    arrow: <><path d="M5 12h14" /><path d="m13 6 6 6-6 6" /></>,
  }[name];
  return <svg aria-hidden="true" fill="none" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" viewBox="0 0 24 24">{paths}</svg>;
}

export function FaqContent() {
  const [category, setCategory] = useState<CategoryId>("general");
  const [openIndex, setOpenIndex] = useState(1);

  const chooseCategory = (id: CategoryId) => {
    setCategory(id);
    setOpenIndex(0);
  };

  return (
    <>
      <section className="faq-page-section px-5 py-20 lg:px-8 lg:py-28">
        <div className="mx-auto max-w-7xl">
          <div className="faq-page-title-row">
            <div>
              <p className="section-kicker">FAQs</p>
              <h2>มีคำถาม? <span>ดูคำตอบที่นี่</span></h2>
              <p>เลือกหัวข้อที่สนใจ แล้วเปิดดูคำตอบเกี่ยวกับการออกแบบ ก่อสร้าง และการดูแลโครงการ</p>
            </div>
            <div className="faq-crane-mark" aria-hidden="true"><span /><i /></div>
          </div>

          <div className="faq-page-layout">
            <aside className="faq-category-column" aria-label="หมวดคำถาม">
              <div className="faq-category-list">
                {categories.map(([id, label]) => (
                  <button key={id} type="button" className={category === id ? "is-active" : ""} onClick={() => chooseCategory(id)}>
                    {label}
                  </button>
                ))}
              </div>

              <div className="faq-help-card">
                <span className="faq-help-icon"><FaqIcon name="chat" /></span>
                <h3>มีคำถามอื่นเพิ่มเติม?</h3>
                <p>ส่งรายละเอียดให้ทีมของเรา แล้วเราจะช่วยแนะนำแนวทางเริ่มต้น</p>
                <Link href="/contact" className="gold-button inline-flex min-h-11 items-center justify-center px-6 text-sm font-extrabold text-[#112416]">ติดต่อเรา</Link>
              </div>

              <a className="faq-service-card" href={siteConfig.phoneHref}>
                <span><FaqIcon name="phone" /></span>
                <span>
                  <small>พร้อมให้คำปรึกษา</small>
                  <strong>{siteConfig.phoneDisplay}</strong>
                  <em><FaqIcon name="clock" /> นัดหมายเวลาที่สะดวก</em>
                </span>
              </a>
            </aside>

            <div className="faq-page-accordion" aria-live="polite">
              {questions[category].map((item, index) => {
                const isOpen = openIndex === index;
                return (
                  <article key={item.question} className={isOpen ? "faq-page-item is-open" : "faq-page-item"}>
                    <button type="button" aria-expanded={isOpen} onClick={() => setOpenIndex(isOpen ? -1 : index)}>
                      <span>{item.question}</span><i aria-hidden="true" />
                    </button>
                    <div className="faq-page-answer"><div><p>{item.answer}</p></div></div>
                  </article>
                );
              })}
            </div>
          </div>
        </div>
      </section>

      <section className="faq-connect-band px-5 py-14 text-white lg:px-8">
        <div className="mx-auto flex max-w-7xl flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
          <div><p className="section-kicker text-[#f6d97b]">Let&apos;s Connect</p><h2>เริ่มคุยเรื่องบ้านของคุณกับทีมเรา</h2></div>
          <Link href="/contact" className="gold-button inline-flex min-h-12 items-center justify-center gap-2 px-7 font-extrabold text-[#112416]">
            ติดต่อเรา <span className="size-5"><FaqIcon name="arrow" /></span>
          </Link>
        </div>
      </section>
    </>
  );
}
