"use client";

import Image from "next/image";
import Autoplay from "embla-carousel-autoplay";
import useEmblaCarousel from "embla-carousel-react";
import { type CSSProperties, type FocusEvent, useCallback, useEffect, useState } from "react";
import { testimonials } from "@/data/testimonials";
import { assetPath } from "@/lib/asset-path";
import styles from "./page.module.css";

export default function ClientExperienceCarousel() {
  const [autoplay] = useState(() => Autoplay({ delay: 4600, stopOnInteraction: false, stopOnMouseEnter: true }));
  const [viewportRef, emblaApi] = useEmblaCarousel({ loop: true, align: "center", skipSnaps: false }, [autoplay]);
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [scrollSnaps, setScrollSnaps] = useState<number[]>([]);

  const syncCarousel = useCallback(() => {
    if (!emblaApi) return;
    setSelectedIndex(emblaApi.selectedScrollSnap());
    setScrollSnaps(emblaApi.scrollSnapList());
  }, [emblaApi]);

  useEffect(() => {
    if (!emblaApi) return;

    const frame = requestAnimationFrame(syncCarousel);
    emblaApi.on("select", syncCarousel).on("reInit", syncCarousel);

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const updateMotion = () => reducedMotion.matches ? autoplay.stop() : autoplay.play();
    updateMotion();
    reducedMotion.addEventListener("change", updateMotion);

    return () => {
      emblaApi.off("select", syncCarousel).off("reInit", syncCarousel);
      reducedMotion.removeEventListener("change", updateMotion);
      cancelAnimationFrame(frame);
    };
  }, [autoplay, emblaApi, syncCarousel]);

  const resumeAutoplay = (event: FocusEvent<HTMLDivElement>) => {
    if (!event.currentTarget.contains(event.relatedTarget) && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      autoplay.play();
    }
  };

  return (
    <div
      className={styles.reviewCarousel}
      onMouseEnter={() => autoplay.stop()}
      onMouseLeave={() => !window.matchMedia("(prefers-reduced-motion: reduce)").matches && autoplay.play()}
      onFocusCapture={() => autoplay.stop()}
      onBlurCapture={resumeAutoplay}
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
