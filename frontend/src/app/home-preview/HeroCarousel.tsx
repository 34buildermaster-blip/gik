"use client";

import Image from "next/image";
import { type FocusEvent, useEffect, useState } from "react";
import { ArrowRight, ArrowUpRight, ChevronLeft, ChevronRight } from "lucide-react";
import {
  fetchHomeSlides,
  homeSlideImage,
  type HomeSlide,
} from "@/lib/home-slides";
import styles from "./page.module.css";

const fallbackSlides: HomeSlide[] = [
  {
    id: "fallback-hero-1",
    image: "/hero-construction.png",
    alt: "ทีมงาน 34 Build Master Construction ตรวจแบบบ้านก่อนเริ่มงาน",
    eyebrow: "DESIGN · BUILD · RENOVATE",
    title: "สร้างพื้นที่ที่ดี",
    titleLine2: "ให้ชีวิตเดินหน้าได้จริง",
    description:
      "ออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน พร้อมดูแลรายละเอียดตั้งแต่แนวคิดจนถึงวันส่งมอบ",
    label: "BUILD WITH CLARITY",
  },
  {
    id: "fallback-hero-2",
    image: "/managed-home-slides/hero-02.webp",
    alt: "บ้านพักอาศัยสไตล์โมเดิร์น",
    eyebrow: "ARCHITECTURAL DESIGN",
    title: "บ้านที่สะท้อนตัวตน",
    titleLine2: "และตอบโจทย์ทุกวัน",
    description:
      "เริ่มจากการเข้าใจชีวิตจริง วางฟังก์ชัน และพัฒนาแบบให้สมดุลทั้งความสวยงาม งบประมาณ และการใช้งาน",
    label: "MODERN RESIDENCE",
  },
  {
    id: "fallback-hero-3",
    image: "/managed-home-slides/hero-03.webp",
    alt: "บ้านร่วมสมัยที่ออกแบบอย่างพิถีพิถัน",
    eyebrow: "CONSTRUCTION MANAGEMENT",
    title: "ทุกขั้นตอนชัดเจน",
    titleLine2: "ตั้งแต่แบบถึงส่งมอบ",
    description:
      "วางแผนงานก่อสร้าง ควบคุมคุณภาพ และสื่อสารความคืบหน้าอย่างเป็นระบบ เพื่อให้ทุกการตัดสินใจมั่นใจขึ้น",
    label: "QUALITY IN DETAIL",
  },
  {
    id: "fallback-hero-4",
    image: "/managed-home-slides/hero-04.webp",
    alt: "บ้านโมเดิร์นที่เลือกใช้วัสดุธรรมชาติ",
    eyebrow: "RENOVATION · INTERIOR",
    title: "เปลี่ยนพื้นที่เดิม",
    titleLine2: "ให้กลับมาน่าอยู่กว่าเดิม",
    description:
      "ปรับโครงสร้าง พื้นที่ และบรรยากาศภายในอย่างเข้าใจข้อจำกัดเดิม พร้อมต่อยอดให้เหมาะกับชีวิตในปัจจุบัน",
    label: "REIMAGINE YOUR SPACE",
  },
];

export default function HeroCarousel() {
  const [slides, setSlides] = useState<HomeSlide[]>(fallbackSlides);
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    const controller = new AbortController();

    fetchHomeSlides(controller.signal).then((payload) => {
      if (payload?.hero.length) {
        setSlides(payload.hero);
        setSelectedIndex(0);
      }
    });

    return () => controller.abort();
  }, []);

  useEffect(() => {
    if (isPaused || slides.length < 2 || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      return;
    }

    const timer = window.setTimeout(() => {
      setSelectedIndex((current) => (current + 1) % slides.length);
    }, 6200);

    return () => window.clearTimeout(timer);
  }, [isPaused, selectedIndex, slides.length]);

  const showPrevious = () =>
    setSelectedIndex((current) => (current - 1 + slides.length) % slides.length);
  const showNext = () =>
    setSelectedIndex((current) => (current + 1) % slides.length);
  const resumeAfterFocus = (event: FocusEvent<HTMLElement>) => {
    if (!event.currentTarget.contains(event.relatedTarget)) setIsPaused(false);
  };

  return (
    <section
      className={styles.hero}
      aria-roledescription="carousel"
      aria-label="บริการและผลงานของ 34 Build Master Construction"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onFocusCapture={() => setIsPaused(true)}
      onBlurCapture={resumeAfterFocus}
    >
      <div className={styles.heroViewport}>
        <div className={styles.heroTrack}>
          {slides.map((slide, index) => (
            <article
              className={`${styles.heroSlide} ${index === selectedIndex ? styles.activeHeroSlide : ""}`}
              key={slide.id}
              aria-hidden={index !== selectedIndex}
            >
              <Image
                src={homeSlideImage(slide.image)}
                alt={slide.alt}
                fill
                priority={index === 0}
                sizes="100vw"
                className={styles.heroImage}
              />
              <div className={styles.heroShade} />
              <div className={styles.heroContent}>
                {slide.eyebrow && <p className={styles.heroEyebrow}>{slide.eyebrow}</p>}
                <h1>
                  {slide.title}
                  {slide.titleLine2 && (
                    <>
                      <br /> {slide.titleLine2}
                    </>
                  )}
                </h1>
                {slide.description && <p>{slide.description}</p>}
                <div className={styles.heroButtons}>
                  <a className={styles.primaryButton} href="#projects">
                    ดูผลงาน <ArrowRight size={18} />
                  </a>
                  <a className={styles.ghostButton} href="#contact">
                    คุยกับทีมงาน <ArrowUpRight size={18} />
                  </a>
                </div>
              </div>
              <div className={styles.heroMeta}>
                <span>CHIANG MAI</span>
                {slide.label && <span>{slide.label}</span>}
              </div>
            </article>
          ))}
        </div>
      </div>

      {slides.length > 1 && (
        <div className={styles.heroControls}>
          <div className={styles.heroPagination} aria-label="เลือกภาพ Hero">
            {slides.map((slide, index) => (
              <button
                className={index === selectedIndex ? styles.activeHeroDot : undefined}
                key={slide.id}
                type="button"
                onClick={() => setSelectedIndex(index)}
                aria-label={`แสดงสไลด์ที่ ${index + 1}`}
                aria-current={index === selectedIndex ? "true" : undefined}
              >
                <span>{String(index + 1).padStart(2, "0")}</span>
                <i />
              </button>
            ))}
          </div>
          <div className={styles.heroArrows}>
            <button type="button" onClick={showPrevious} aria-label="สไลด์ก่อนหน้า">
              <ChevronLeft size={20} />
            </button>
            <button type="button" onClick={showNext} aria-label="สไลด์ถัดไป">
              <ChevronRight size={20} />
            </button>
          </div>
        </div>
      )}
    </section>
  );
}
