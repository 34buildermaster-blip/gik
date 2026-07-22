"use client";

import Image from "next/image";
import Autoplay from "embla-carousel-autoplay";
import useEmblaCarousel from "embla-carousel-react";
import { type CSSProperties, useCallback, useEffect, useState } from "react";
import { assetPath } from "@/lib/asset-path";
import { testimonials } from "@/data/testimonials";

export function TestimonialsCarousel() {
  const [autoplay] = useState(() => Autoplay({ delay: 5000, stopOnInteraction: false, stopOnMouseEnter: true }));
  const [viewportRef, emblaApi] = useEmblaCarousel({ loop: true, align: "center", skipSnaps: false }, [autoplay]);
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [scrollSnaps, setScrollSnaps] = useState<number[]>([]);

  const onSelect = useCallback(() => {
    if (!emblaApi) return;
    setSelectedIndex(emblaApi.selectedScrollSnap());
  }, [emblaApi]);

  const onInit = useCallback(() => {
    if (!emblaApi) return;
    setScrollSnaps(emblaApi.scrollSnapList());
    setSelectedIndex(emblaApi.selectedScrollSnap());
  }, [emblaApi]);

  useEffect(() => {
    if (!emblaApi) return;

    const frame = requestAnimationFrame(onInit);
    emblaApi.on("select", onSelect).on("reInit", onInit);

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const updateMotion = () => reducedMotion.matches ? autoplay.stop() : autoplay.play();
    updateMotion();
    reducedMotion.addEventListener("change", updateMotion);

    return () => {
      emblaApi.off("select", onSelect).off("reInit", onInit);
      reducedMotion.removeEventListener("change", updateMotion);
      cancelAnimationFrame(frame);
    };
  }, [autoplay, emblaApi, onInit, onSelect]);

  const resumeAutoplay = (event: React.FocusEvent<HTMLDivElement>) => {
    if (!event.currentTarget.contains(event.relatedTarget) && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      autoplay.play();
    }
  };

  return (
    <div
      className="about-v2-carousel"
      onMouseEnter={() => autoplay.stop()}
      onMouseLeave={() => !window.matchMedia("(prefers-reduced-motion: reduce)").matches && autoplay.play()}
      onFocusCapture={() => autoplay.stop()}
      onBlurCapture={resumeAutoplay}
      aria-label="รีวิวจากลูกค้า"
    >
      <div className="about-v2-carousel-viewport" ref={viewportRef}>
        <div className="about-v2-carousel-container">
          {testimonials.map((item, index) => (
            <div className={`about-v2-carousel-slide${selectedIndex === index ? " is-selected" : ""}`} key={item.name}>
              <article className="about-v2-review-card">
                <div className="about-v2-review-topline">
                  <span className="about-v2-review-quote">“</span>
                  <span className="about-v2-review-count">0{index + 1}</span>
                </div>
                <div className="about-v2-stars" aria-label={`${item.rating} ดาว`}>{"★".repeat(item.rating)}</div>
                <blockquote>“{item.text}”</blockquote>
                <div className="about-v2-review-profile">
                  <span className="about-v2-review-avatar" style={{ "--avatar-x": `${item.avatarIndex * -20}%` } as CSSProperties}>
                    <Image src={assetPath("/testimonial-avatars.png")} alt="" width={1984} height={1024} sizes="260px" />
                  </span>
                  <span><strong>{item.name}</strong><small>{item.project}</small><em>{item.location}</em></span>
                </div>
              </article>
            </div>
          ))}
        </div>
      </div>

      <div className="about-v2-carousel-controls">
        <div className="about-v2-carousel-dots" aria-label="เลือกรีวิว">
          {scrollSnaps.map((_, index) => (
            <button key={index} type="button" className={selectedIndex === index ? "is-selected" : ""} onClick={() => emblaApi?.scrollTo(index)} aria-label={`แสดงรีวิวที่ ${index + 1}`} />
          ))}
        </div>
      </div>
    </div>
  );
}
