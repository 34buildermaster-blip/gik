import type { Metadata } from "next";
import { Mail, MapPin, MessageCircle, Phone } from "lucide-react";
import { ContactForm, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { siteConfig, socialLinks } from "@/lib/site-config";

export const metadata: Metadata = {
  title: "ติดต่อเรา | 34 Build Master Construction",
  description: "ติดต่อ 34 Build Master Construction เพื่อปรึกษางานออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน",
};

const channels = [
  { label: "โทรปรึกษา", value: siteConfig.phoneDisplay, href: siteConfig.phoneHref, icon: Phone },
  { label: "อีเมล", value: siteConfig.email, href: `mailto:${siteConfig.email}`, icon: Mail },
  { label: "LINE OA", value: "ส่งข้อความถึงทีม", href: socialLinks.find((item) => item.icon === "line")?.href || "#", icon: MessageCircle },
];

export default function ContactPage() {
  return (
    <main className="modern-inner-page min-h-screen bg-white text-[#17211c]">
      <SiteHeader />
      <PageHero title="เริ่มคุยเรื่องบ้านของคุณ" currentLabel="ติดต่อเรา" />

      <section className="px-5 py-20 lg:px-8 lg:py-28">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.82fr_1.18fr]">
          <div>
            <p className="modern-kicker">Contact us</p>
            <h2 className="mt-4 text-4xl font-semibold leading-tight sm:text-5xl">บอกโจทย์ที่คุณมี<br />แล้วค่อยวางแผนไปด้วยกัน</h2>
            <p className="mt-5 max-w-xl text-xl leading-9 text-[#667169]">ส่งข้อมูลพื้นที่ รูปหน้างาน งบประมาณโดยประมาณ และช่วงเวลาที่ต้องการ ทีมจะติดต่อกลับเพื่อช่วยจัดลำดับขั้นแรกให้ชัดเจน</p>

            <div className="mt-10 grid gap-3">
              {channels.map((channel) => {
                const ChannelIcon = channel.icon;
                return (
                  <a key={channel.label} href={channel.href} target={channel.href.startsWith("http") ? "_blank" : undefined} rel={channel.href.startsWith("http") ? "noreferrer" : undefined} className="group grid grid-cols-[48px_1fr] items-center gap-4 rounded-lg border border-[#dfe4e0] p-4 transition hover:border-[#0f6b45] hover:bg-[#edf5f0]">
                    <span className="grid size-12 place-items-center rounded-full bg-[#edf5f0] text-[#0f6b45] group-hover:bg-[#0f6b45] group-hover:text-white"><ChannelIcon className="size-5" /></span>
                    <span className="min-w-0"><small className="block text-sm text-[#7a847d]">{channel.label}</small><strong className="mt-1 block break-all text-lg font-semibold text-[#17211c]">{channel.value}</strong></span>
                  </a>
                );
              })}
            </div>

            <div className="mt-8 flex gap-3 border-t border-[#dfe4e0] pt-7 text-[#667169]"><MapPin className="mt-1 size-5 shrink-0 text-[#0f6b45]" /><p className="leading-8">พื้นที่ให้บริการหลัก<br /><strong className="font-semibold text-[#17211c]">{siteConfig.area}</strong></p></div>
          </div>
          <div><p className="mb-5 text-sm font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">Project enquiry</p><ContactForm /></div>
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
