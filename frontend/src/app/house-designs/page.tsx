import type { Metadata } from "next";
import Image from "next/image";
import { ContactBand, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { assetPath } from "@/lib/asset-path";
import { HouseDesignBrowser } from "./house-design-browser";

export const metadata: Metadata = {
  title: "แบบบ้าน | 34 Build Master Construction",
  description: "ค้นหาแบบบ้านโมเดิร์น มินิมอล ร่วมสมัย และคลาสสิก พร้อมข้อมูลพื้นที่ใช้สอย จำนวนห้อง และงบประมาณเบื้องต้น",
};

export default function HouseDesignsPage() {
  return (
    <main className="modern-inner-page min-h-screen bg-[#f4f6f5] text-[#17211c]">
      <SiteHeader />

      <section className="relative isolate grid min-h-[430px] place-items-center overflow-hidden px-5 py-24 text-center text-white lg:px-8">
        <Image
          src={assetPath("/approach-homes/modern.jpg")}
          alt="ตัวอย่างแบบบ้านสมัยใหม่โดย 34 Build Master Construction"
          fill
          priority
          sizes="100vw"
          className="-z-20 object-cover object-center"
        />
        <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(12,29,20,0.78),rgba(12,29,20,0.48)),linear-gradient(0deg,rgba(12,29,20,0.54),transparent_58%)]" />
        <div className="mx-auto max-w-3xl">
          <p className="text-sm font-semibold uppercase tracking-[0.18em] text-white/72">House collection</p>
          <h1 className="mt-4 text-5xl font-semibold leading-tight sm:text-7xl">แบบบ้าน</h1>
          <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-white/82 sm:text-xl">
            สำรวจแนวทางบ้านที่เหมาะกับพื้นที่ งบประมาณ และรูปแบบชีวิตของคุณ ทุกแบบสามารถนำไปปรับฟังก์ชันและรายละเอียดให้เข้ากับหน้างานจริงได้
          </p>
        </div>
      </section>

      <HouseDesignBrowser />
      <ContactBand />
      <SiteFooter />
    </main>
  );
}
