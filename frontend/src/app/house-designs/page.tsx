import type { Metadata } from "next";
import Image from "next/image";
import { ContactBand, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { assetPath } from "@/lib/asset-path";
import { getIntegratedHouseDesigns } from "@/lib/house-design-api";
import { HouseDesignBrowser } from "./house-design-browser";

export const metadata: Metadata = {
  title: "แบบบ้าน | 34 Build Master Construction",
  description: "ค้นหาแบบบ้านโมเดิร์น มินิมอล ร่วมสมัย และคลาสสิก พร้อมข้อมูลพื้นที่ใช้สอย จำนวนห้อง และงบประมาณเบื้องต้น",
};

export default async function HouseDesignsPage() {
  const designs = await getIntegratedHouseDesigns();

  return (
    <main className="modern-inner-page min-h-screen bg-[#f4f6f5] text-[#17211c]">
      <SiteHeader />

      <section className="relative isolate grid min-h-[300px] place-items-center overflow-hidden px-5 py-14 text-center text-white sm:py-16 lg:px-8">
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
          <p className="text-xs font-semibold uppercase tracking-[0.16em] text-white/72 sm:text-[13px]">House collection</p>
          <h1 className="mt-2.5 text-3xl font-semibold leading-tight sm:text-4xl">แบบบ้าน</h1>
          <p className="mx-auto mt-3.5 max-w-2xl text-[15px] leading-7 text-white/82 sm:text-base">
            สำรวจแนวทางบ้านที่เหมาะกับพื้นที่ งบประมาณ และรูปแบบชีวิตของคุณ ทุกแบบสามารถนำไปปรับฟังก์ชันและรายละเอียดให้เข้ากับหน้างานจริงได้
          </p>
        </div>
      </section>

      <HouseDesignBrowser designs={designs} />
      <ContactBand />
      <SiteFooter />
    </main>
  );
}
