"use client";

import Autoplay from "embla-carousel-autoplay";
import useEmblaCarousel from "embla-carousel-react";
import { type FocusEvent, useCallback, useEffect, useState } from "react";

type AutoCarouselOptions = {
  delay: number;
};

export function useAutoCarousel({ delay }: AutoCarouselOptions) {
  const [autoplay] = useState(() =>
    Autoplay({ delay, stopOnInteraction: false, stopOnMouseEnter: true }),
  );
  const [viewportRef, emblaApi] = useEmblaCarousel(
    { loop: true, align: "center", skipSnaps: false },
    [autoplay],
  );
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [scrollSnaps, setScrollSnaps] = useState<number[]>([]);

  const syncCarousel = useCallback(() => {
    if (!emblaApi) return;

    setSelectedIndex(emblaApi.selectedScrollSnap());
    setScrollSnaps(emblaApi.scrollSnapList());
  }, [emblaApi]);

  const play = useCallback(() => {
    if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      autoplay.play();
    }
  }, [autoplay]);

  const stop = useCallback(() => autoplay.stop(), [autoplay]);

  const resumeAfterFocus = useCallback(
    (event: FocusEvent<HTMLElement>) => {
      if (!event.currentTarget.contains(event.relatedTarget)) play();
    },
    [play],
  );

  useEffect(() => {
    if (!emblaApi) return;

    const frame = requestAnimationFrame(syncCarousel);
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const updateMotion = () => (reducedMotion.matches ? stop() : play());

    emblaApi.on("select", syncCarousel).on("reInit", syncCarousel);
    reducedMotion.addEventListener("change", updateMotion);
    updateMotion();

    return () => {
      emblaApi.off("select", syncCarousel).off("reInit", syncCarousel);
      reducedMotion.removeEventListener("change", updateMotion);
      cancelAnimationFrame(frame);
    };
  }, [emblaApi, play, stop, syncCarousel]);

  return {
    emblaApi,
    play,
    resumeAfterFocus,
    scrollSnaps,
    selectedIndex,
    stop,
    viewportRef,
  };
}
