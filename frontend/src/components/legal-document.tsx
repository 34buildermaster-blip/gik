import type { ReactNode } from "react";
import { SiteFooter, SiteHeader } from "@/components/site-chrome";

export function LegalDocument({
  eyebrow,
  title,
  description,
  children,
}: {
  eyebrow: string;
  title: string;
  description: string;
  children: ReactNode;
}) {
  return (
    <main className="modern-inner-page min-h-screen bg-[#f4f6f5] text-[#17211c]">
      <SiteHeader />
      <header className="border-b border-[#dfe4e0] bg-white px-5 py-14 lg:px-8 lg:py-18">
        <div className="mx-auto max-w-5xl">
          <p className="text-xs font-semibold uppercase tracking-[0.16em] text-[#0f6b45]">{eyebrow}</p>
          <h1 className="mt-3 max-w-4xl text-4xl font-semibold leading-tight sm:text-5xl">{title}</h1>
          <p className="mt-5 max-w-3xl text-base leading-8 text-[#667169]">{description}</p>
          <div className="mt-6 flex flex-wrap gap-2 text-xs text-[#667169]">
            <span className="rounded-full border border-[#dfe4e0] px-3 py-1.5">เวอร์ชัน 1.0</span>
            <span className="rounded-full border border-[#dfe4e0] px-3 py-1.5">ปรับปรุงล่าสุด 5 สิงหาคม 2569</span>
          </div>
        </div>
      </header>
      <article className="legal-copy mx-auto my-8 max-w-5xl border border-[#dfe4e0] bg-white px-6 py-4 shadow-[0_16px_50px_rgba(18,34,25,0.06)] sm:px-10 lg:my-12 lg:px-14">
        {children}
      </article>
      <SiteFooter />
    </main>
  );
}
