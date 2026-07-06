import type { Metadata } from "next";
import { ContactForm, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";

export const metadata: Metadata = {
  title: "ติดต่อเรา | 34 Build Master Construction",
  description:
    "ติดต่อ 34 Build Master Construction เพื่อปรึกษางานออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน",
};

export default function ContactPage() {
  return (
    <main className="min-h-screen bg-[#fbf7ec] text-lg text-[#112416]">
      <SiteHeader />
      <PageHero title="ติดต่อเรา" currentLabel="ติดต่อเรา" />

      <section className="bg-material-section px-5 py-20 lg:px-8">
        <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.8fr_1.2fr]">
          <aside className="luxe-card p-7">
            <h2 className="text-3xl font-extrabold text-[#053920]">ช่องทางติดต่อ</h2>
            <div className="mt-7 space-y-5 text-xl leading-9 text-[#4d5b50]">
              <p>
                โทร:{" "}
                <a className="font-extrabold text-[#053920]" href="tel:+66819512297">
                  081-9512-297
                </a>
              </p>
              <p>
                อีเมล:{" "}
                <a className="font-extrabold text-[#053920]" href="mailto:34buildmaster@gmail.com">
                  34buildmaster@gmail.com
                </a>
              </p>
              <p>พื้นที่ให้บริการ: เชียงใหม่ และพื้นที่ใกล้เคียง</p>
            </div>
          </aside>

          <ContactForm />
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
