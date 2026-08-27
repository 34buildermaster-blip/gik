"use client";

import Image from "next/image";
import { type CSSProperties } from "react";
import { testimonials } from "@/data/testimonials";
import { useAutoCarousel } from "@/hooks/use-auto-carousel";
import { assetPath } from "@/lib/asset-path";
import styles from "./page.module.css";

export default function ClientExperienceCarousel() {
  const { emblaApi, resumeAfterFocus, scrollSnaps, selectedIndex, stop, play, viewportRef } =
    useAutoCarousel({ delay: 4600 });

  return (
    <div
      className={styles.reviewCarousel}
      data-gsap-media
      onMouseEnter={stop}
      onMouseLeave={play}
      onFocusCapture={stop}
      onBlurCapture={resumeAfterFocus}
      aria-label="รีวิวประสบการณ์จากลูกค้า"
    >
      <div className={styles.reviewViewport} ref={viewportRef}>
        <div className={styles.reviewContainer}>
          {testimonials.map((item, index) => (
            <div className={`${styles.reviewSlide} ${selectedIndex === index ? styles.selectedReview : ""}`} key={item.name}>
              <article className={styles.reviewCard}>
                <div className={styles.reviewProfile}>
                  <span
                    className={styles.reviewAvatar}
                    style={{ "--avatar-x": `${item.avatarIndex * -20}%` } as CSSProperties}
                  >
                    <Image src={assetPath("/testimonial-avatars.png")} alt="" width={1984} height={1024} sizes="58px" />
                  </span>
                  <span>
                    <strong>{item.name}</strong>
                    <small>{item.project}</small>
                  </span>
                  <i>{String(index + 1).padStart(2, "0")}</i>
                </div>

                <div className={styles.reviewStars} aria-label={`${item.rating} ดาว`}>{"★".repeat(item.rating)}</div>
                <blockquote>“{item.text}”</blockquote>
                <footer>
                  <span>โครงการ</span>
                  <strong>{item.location}</strong>
                </footer>
              </article>
            </div>
          ))}
        </div>
      </div>

      <div className={styles.reviewDots} aria-label="เลือกรีวิว">
        {scrollSnaps.map((_, index) => (
          <button
            type="button"
            className={selectedIndex === index ? styles.selectedReviewDot : undefined}
            key={index}
            onClick={() => emblaApi?.scrollTo(index)}
            aria-label={`แสดงรีวิวที่ ${index + 1}`}
            aria-current={selectedIndex === index ? "true" : undefined}
          />
        ))}
      </div>
    </div>
  );
}
