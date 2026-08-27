"use client";

import Image from "next/image";
import { type CSSProperties } from "react";
import { assetPath } from "@/lib/asset-path";
import { testimonials } from "@/data/testimonials";
import { useAutoCarousel } from "@/hooks/use-auto-carousel";

export function TestimonialsCarousel() {
  const { emblaApi, resumeAfterFocus, scrollSnaps, selectedIndex, stop, play, viewportRef } =
    useAutoCarousel({ delay: 5000 });

  return (
    <div
      className="about-v2-carousel"
      onMouseEnter={stop}
      onMouseLeave={play}
      onFocusCapture={stop}
      onBlurCapture={resumeAfterFocus}
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
