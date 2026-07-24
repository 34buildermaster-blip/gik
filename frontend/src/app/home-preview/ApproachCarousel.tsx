"use client";

import Image from "next/image";
import { useCallback, useEffect, useRef, useState } from "react";
import {
  fetchHomeSlides,
  homeSlideImage,
  type HomeSlide,
} from "@/lib/home-slides";
import styles from "./page.module.css";

const fallbackHomes: HomeSlide[] = [
  { id: "fallback-home-1", image: "/approach-homes/modern.jpg", title: "Modern Residence", alt: "บ้านสไตล์โมเดิร์นเส้นสายเรียบคม" },
  { id: "fallback-home-2", image: "/approach-homes/natural-modern.jpg", title: "Natural Modern", alt: "บ้านโมเดิร์นที่ผสมวัสดุไม้ธรรมชาติ" },
  { id: "fallback-home-3", image: "/approach-homes/contemporary.jpg", title: "Modern Farmhouse", alt: "บ้านสไตล์โมเดิร์นฟาร์มเฮาส์" },
  { id: "fallback-home-4", image: "/approach-homes/minimal.jpg", title: "Rustic Contemporary", alt: "บ้านร่วมสมัยที่ใช้หินและวัสดุธรรมชาติ" },
  { id: "fallback-home-5", image: "/approach-homes/natural.jpg", title: "Nordic Bungalow", alt: "บ้านชั้นเดียวสไตล์นอร์ดิกท่ามกลางสวน" },
  { id: "fallback-home-6", image: "/approach-homes/coastal-villa.jpg", title: "Coastal Villa", alt: "บ้านพักตากอากาศสไตล์วิลลาริมน้ำ" },
  { id: "fallback-home-7", image: "/approach-homes/classic.jpg", title: "Resort Villa", alt: "บ้านพักสไตล์รีสอร์ตพร้อมพื้นที่สระว่ายน้ำ" },
  { id: "fallback-home-8", image: "/approach-homes/villa.jpg", title: "Classic Residence", alt: "บ้านพักอาศัยสไตล์คลาสสิกร่วมสมัย" },
];

const AUTO_SLIDE_MS = 4200;

export default function ApproachCarousel() {
  const trackRef = useRef<HTMLDivElement>(null);
  const [homes, setHomes] = useState<HomeSlide[]>(fallbackHomes);
  const [activeIndex, setActiveIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    const controller = new AbortController();

    fetchHomeSlides(controller.signal).then((payload) => {
      if (payload?.approach.length) {
        setHomes(payload.approach);
        setActiveIndex(0);
      }
    });

    return () => controller.abort();
  }, []);

  const showSlide = useCallback((index: number, behavior: ScrollBehavior = "smooth") => {
    const track = trackRef.current;
    const slide = track?.children.item(index) as HTMLElement | null;

    if (!track || !slide) return;

    track.scrollTo({ left: slide.offsetLeft, behavior });
    setActiveIndex(index);
  }, []);

  useEffect(() => {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion || isPaused || homes.length < 2) return;

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
  }, [homes.length, isPaused]);

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
          <figure className={styles.approachSlide} key={home.id}>
            <Image
              src={homeSlideImage(home.image)}
              alt={home.alt}
              fill
              sizes="(max-width: 620px) 86vw, (max-width: 900px) 48vw, 32vw"
            />
            <figcaption>
              <span>
                {String(index + 1).padStart(2, "0")} / {String(homes.length).padStart(2, "0")}
              </span>
              <strong>{home.title}</strong>
            </figcaption>
          </figure>
        ))}
      </div>

      {homes.length > 1 && (
        <div className={styles.carouselDots} aria-label="เลือกภาพแบบบ้าน">
          {homes.map((home, index) => (
            <button
              className={index === activeIndex ? styles.activeDot : undefined}
              type="button"
              key={home.id}
              aria-label={`ดูภาพ ${home.title}`}
              aria-current={index === activeIndex ? "true" : undefined}
              onClick={() => showSlide(index)}
            />
          ))}
        </div>
      )}
    </div>
  );
}
