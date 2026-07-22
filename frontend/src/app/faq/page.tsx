import type { Metadata } from "next";
import { PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { FaqContent } from "./faq-content";

export const metadata: Metadata = {
  title: "คำถามที่พบบ่อย",
  description:
    "รวมคำตอบเรื่องบริการ การวางแผนโครงการ แบบและวัสดุ ขั้นตอนก่อสร้าง ค่าใช้จ่าย และการติดตามงานกับ 34 Build Master Construction",
};

export default function FaqPage() {
  return (
    <main className="modern-inner-page min-h-screen bg-white text-[#17211c]">
      <SiteHeader />
      <PageHero title="คำถามที่พบบ่อย" currentLabel="FAQs" />
      <FaqContent />
      <SiteFooter />
    </main>
  );
}
