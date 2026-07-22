"use client";

import Image from "next/image";
import { useCallback, useEffect, useRef, useState } from "react";
import { assetPath } from "@/lib/asset-path";
import styles from "./page.module.css";

const homes = [
  { src: "/approach-homes/modern.jpg", style: "Modern Residence", alt: "บ้านสไตล์โมเดิร์นเส้นสายเรียบคม" },
  { src: "/approach-homes/natural-modern.jpg", style: "Natural Modern", alt: "บ้านโมเดิร์นที่ผสมวัสดุไม้ธรรมชาติ" },
  { src: "/approach-homes/contemporary.jpg", style: "Modern Farmhouse", alt: "บ้านสไตล์โมเดิร์นฟาร์มเฮาส์" },
  { src: "/approach-homes/minimal.jpg", style: "Rustic Contemporary", alt: "บ้านร่วมสมัยที่ใช้หินและวัสดุธรรมชาติ" },
  { src: "/approach-homes/natural.jpg", style: "Nordic Bungalow", alt: "บ้านชั้นเดียวสไตล์นอร์ดิกท่ามกลางสวน" },
  { src: "/approach-homes/coastal-villa.jpg", style: "Coastal Villa", alt: "บ้านพักตากอากาศสไตล์วิลลาริมน้ำ" },
  { src: "/approach-homes/classic.jpg", style: "Resort Villa", alt: "บ้านพักสไตล์รีสอร์ตพร้อมพื้นที่สระว่ายน้ำ" },
  { src: "/approach-homes/villa.jpg", style: "Classic Residence", alt: "บ้านพักอาศัยสไตล์คลาสสิกร่วมสมัย" },
];

const AUTO_SLIDE_MS = 4200;

export default function ApproachCarousel() {
  const trackRef = useRef<HTMLDivElement>(null);
  const [activeIndex, setActiveIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  const showSlide = useCallback((index: number, behavior: ScrollBehavior = "smooth") => {
    const track = trackRef.current;
    const slide = track?.children.item(index) as HTMLElement | null;

    if (!track || !slide) return;

    track.scrollTo({ left: slide.offsetLeft, behavior });
    setActiveIndex(index);
  }, []);

  useEffect(() => {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion || isPaused) return;

    const timer = window.setInterval(() => {
      setActiveIndex((current) => {
        const next = (current + 1) % homes.length;
        const track = trackRef.current;
        const slide = track?.children.item(next) as HTMLElement | null;

        if (track && slide) {
          track.scrollTo({ left: slide.offsetLeft, behavior: "smooth" });
        }

        return next;
      });
    }, AUTO_SLIDE_MS);

    return () => window.clearInterval(timer);
  }, [isPaused]);

  useEffect(() => {
    const handleResize = () => showSlide(activeIndex, "auto");
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, [activeIndex, showSlide]);

  return (
    <div
      className={styles.approachCarousel}
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onFocusCapture={() => setIsPaused(true)}
      onBlurCapture={() => setIsPaused(false)}
      aria-label="ตัวอย่างแบบบ้านหลากหลายสไตล์"
    >
      <div className={styles.carouselTrack} ref={trackRef} aria-live="off">
        {homes.map((home, index) => (
          <figure className={styles.approachSlide} key={home.src}>
            <Image
              src={assetPath(home.src)}
              alt={home.alt}
              fill
              sizes="(max-width: 620px) 86vw, (max-width: 900px) 48vw, 32vw"
            />
            <figcaption>
              <span>{String(index + 1).padStart(2, "0")} / {String(homes.length).padStart(2, "0")}</span>
              <strong>{home.style}</strong>
            </figcaption>
          </figure>
        ))}
      </div>

      <div className={styles.carouselDots} aria-label="เลือกภาพแบบบ้าน">
        {homes.map((home, index) => (
          <button
            className={index === activeIndex ? styles.activeDot : undefined}
            type="button"
            key={home.src}
            aria-label={`ดูภาพ ${home.style}`}
            aria-current={index === activeIndex ? "true" : undefined}
            onClick={() => showSlide(index)}
          />
        ))}
      </div>
    </div>
  );
}
