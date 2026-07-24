"use client";

import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, Bath, BedDouble, Expand, House, RotateCcw, Search } from "lucide-react";
import { useMemo, useState } from "react";
import type { HouseDesign } from "@/data/house-designs";

type HouseDesignBrowserProps = {
  designs: HouseDesign[];
};

export function HouseDesignBrowser({ designs }: HouseDesignBrowserProps) {
  const [query, setQuery] = useState("");
  const [style, setStyle] = useState("all");
  const [budget, setBudget] = useState("all");
  const [bedrooms, setBedrooms] = useState("all");

  const bedroomOptions = useMemo(
    () => Array.from(new Set(designs.map((design) => design.bedrooms))).sort((a, b) => a - b),
    [designs],
  );

  const filteredDesigns = useMemo(() => {
    const searchTerm = query.trim().toLocaleLowerCase("th");
    return designs.filter((design) => {
      const matchesSearch = !searchTerm || `${design.name} ${design.title} ${design.styleLabel}`.toLocaleLowerCase("th").includes(searchTerm);
      const matchesStyle = style === "all" || design.style === style;
      const matchesBudget = budget === "all" || design.budgetCategory === budget;
      const matchesBedrooms = bedrooms === "all" || design.bedrooms === Number(bedrooms);
      return matchesSearch && matchesStyle && matchesBudget && matchesBedrooms;
    });
  }, [designs, query, style, budget, bedrooms]);

  const resetFilters = () => {
    setQuery("");
    setStyle("all");
    setBudget("all");
    setBedrooms("all");
  };

  return (
    <section className="px-5 py-16 lg:px-8 lg:py-24">
      <div className="mx-auto max-w-7xl">
        <div className="rounded-lg border border-[#dfe4e0] bg-white p-5 shadow-[0_16px_55px_rgba(18,34,25,0.06)] md:p-7">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p className="modern-kicker">Find your home</p>
              <h2 className="mt-2 text-2xl font-semibold sm:text-3xl">ค้นหาแบบบ้าน</h2>
            </div>
            <span className="w-fit rounded-full bg-[#edf5f0] px-4 py-2 text-sm font-semibold text-[#0f6b45]">{filteredDesigns.length} แบบบ้าน</span>
          </div>

          <div className="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-[1.45fr_repeat(3,1fr)_auto]">
            <label className="grid gap-2 text-sm font-medium text-[#4f5b53]">
              ค้นหา
              <span className="relative">
                <Search className="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-[#7a847d]" aria-hidden="true" />
                <input
                  value={query}
                  onChange={(event) => setQuery(event.target.value)}
                  className="min-h-12 w-full rounded-lg border border-[#d8ded9] bg-[#f8f9f8] py-3 pl-11 pr-4 outline-none transition focus:border-[#0f6b45] focus:bg-white focus:ring-2 focus:ring-[#0f6b45]/10"
                  placeholder="ชื่อแบบบ้าน หรือสไตล์"
                />
              </span>
            </label>
            <label className="grid gap-2 text-sm font-medium text-[#4f5b53]">
              สไตล์
              <select value={style} onChange={(event) => setStyle(event.target.value)} className="min-h-12 rounded-lg border border-[#d8ded9] bg-[#f8f9f8] px-4 outline-none focus:border-[#0f6b45]">
                <option value="all">ทุกสไตล์</option>
                <option value="modern">โมเดิร์น</option>
                <option value="minimal">มินิมอล</option>
                <option value="contemporary">ร่วมสมัย</option>
                <option value="classic">คลาสสิก</option>
              </select>
            </label>
            <label className="grid gap-2 text-sm font-medium text-[#4f5b53]">
              งบประมาณ
              <select value={budget} onChange={(event) => setBudget(event.target.value)} className="min-h-12 rounded-lg border border-[#d8ded9] bg-[#f8f9f8] px-4 outline-none focus:border-[#0f6b45]">
                <option value="all">ทุกช่วงงบ</option>
                <option value="under-5">ต่ำกว่า 5 ล้านบาท</option>
                <option value="5-10">5 - 10 ล้านบาท</option>
                <option value="over-10">มากกว่า 10 ล้านบาท</option>
              </select>
            </label>
            <label className="grid gap-2 text-sm font-medium text-[#4f5b53]">
              ห้องนอน
              <select value={bedrooms} onChange={(event) => setBedrooms(event.target.value)} className="min-h-12 rounded-lg border border-[#d8ded9] bg-[#f8f9f8] px-4 outline-none focus:border-[#0f6b45]">
                <option value="all">ทุกจำนวน</option>
                {bedroomOptions.map((option) => <option key={option} value={option}>{option} ห้อง</option>)}
              </select>
            </label>
            <button type="button" onClick={resetFilters} className="mt-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-[#cfd6d1] px-5 font-medium text-[#4f5b53] transition hover:border-[#0f6b45] hover:text-[#0f6b45]">
              <RotateCcw className="size-4" aria-hidden="true" /> ล้าง
            </button>
          </div>
        </div>

        {filteredDesigns.length ? (
          <div className="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            {filteredDesigns.map((design) => (
              <article key={design.slug} className="group overflow-hidden rounded-lg border border-[#dfe4e0] bg-white transition duration-300 hover:-translate-y-1 hover:border-[#b8c8be] hover:shadow-[0_22px_65px_rgba(18,34,25,0.1)]">
                <Link href={`/house-designs/${design.slug}`} className="block">
                  <div className="relative aspect-[4/3] overflow-hidden bg-[#e8ece9]">
                    <Image src={design.coverImage} alt={design.coverAlt} fill sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw" className="object-cover transition duration-700 group-hover:scale-[1.04]" />
                    <span className="absolute left-4 top-4 rounded-full bg-white/92 px-3 py-2 text-xs font-semibold text-[#0f6b45] shadow-sm backdrop-blur">{design.styleLabel}</span>
                  </div>
                </Link>
                <div className="p-6">
                  <p className="text-sm font-medium uppercase tracking-[0.12em] text-[#0f6b45]">{design.name}</p>
                  <h3 className="mt-2 text-xl font-semibold text-[#17211c]">{design.title}</h3>
                  <p className="mt-4 text-sm text-[#7a847d]">งบก่อสร้างโดยประมาณ</p>
                  <p className="mt-1 text-lg font-semibold text-[#17211c]">{design.budgetLabel}</p>
                  <dl className="mt-5 grid grid-cols-3 gap-2 border-y border-[#e4e8e5] py-4 text-sm text-[#667169]">
                    <div><dt className="flex items-center gap-1.5"><Expand className="size-4 text-[#0f6b45]" aria-hidden="true" /> พื้นที่</dt><dd className="mt-1 font-semibold text-[#17211c]">{design.area} ตร.ม.</dd></div>
                    <div><dt className="flex items-center gap-1.5"><BedDouble className="size-4 text-[#0f6b45]" aria-hidden="true" /> ห้องนอน</dt><dd className="mt-1 font-semibold text-[#17211c]">{design.bedrooms} ห้อง</dd></div>
                    <div><dt className="flex items-center gap-1.5"><Bath className="size-4 text-[#0f6b45]" aria-hidden="true" /> ห้องน้ำ</dt><dd className="mt-1 font-semibold text-[#17211c]">{design.bathrooms} ห้อง</dd></div>
                  </dl>
                  <Link href={`/house-designs/${design.slug}`} className="mt-5 inline-flex items-center gap-2 font-semibold text-[#0f6b45]">
                    ดูรายละเอียดแบบบ้าน <ArrowUpRight className="size-4" aria-hidden="true" />
                  </Link>
                </div>
              </article>
            ))}
          </div>
        ) : (
          <div className="mt-10 grid min-h-[320px] place-items-center rounded-lg border border-dashed border-[#cfd6d1] bg-white p-8 text-center">
            <div>
              <span className="mx-auto grid size-14 place-items-center rounded-full bg-[#edf5f0] text-[#0f6b45]"><House className="size-6" aria-hidden="true" /></span>
              <h3 className="mt-4 text-xl font-semibold">ยังไม่พบแบบบ้านที่ตรงกับตัวกรอง</h3>
              <p className="mt-2 text-[#667169]">ลองเปลี่ยนช่วงงบ สไตล์ หรือจำนวนห้องนอน</p>
              <button type="button" onClick={resetFilters} className="mt-5 rounded-full bg-[#0f6b45] px-6 py-3 font-semibold text-white">ดูแบบบ้านทั้งหมด</button>
            </div>
          </div>
        )}

        <p className="mt-8 text-sm leading-7 text-[#7a847d]">* งบประมาณเป็นการประเมินเบื้องต้นจากขนาดและระดับวัสดุ อาจเปลี่ยนแปลงตามพื้นที่ก่อสร้าง สเปก และรายละเอียดแบบจริง</p>
      </div>
    </section>
  );
}
