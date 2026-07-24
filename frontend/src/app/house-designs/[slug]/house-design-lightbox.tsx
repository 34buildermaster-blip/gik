"use client";

import Image from "next/image";
import { ChevronLeft, ChevronRight, Expand, X } from "lucide-react";
import { useCallback, useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";

type GalleryImage = {
  id: number | string;
  image: string;
  alt: string;
  caption: string | null;
};

type HouseDesignLightboxProps = {
  images: GalleryImage[];
};

export function HouseDesignLightbox({ images }: HouseDesignLightboxProps) {
  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const closeButtonRef = useRef<HTMLButtonElement>(null);
  const previousFocusRef = useRef<HTMLElement | null>(null);
  const touchStartX = useRef<number | null>(null);

  const close = useCallback(() => setActiveIndex(null), []);
  const showPrevious = useCallback(() => {
    setActiveIndex((current) => current === null ? null : (current - 1 + images.length) % images.length);
  }, [images.length]);
  const showNext = useCallback(() => {
    setActiveIndex((current) => current === null ? null : (current + 1) % images.length);
  }, [images.length]);

  useEffect(() => {
    if (activeIndex === null) {
      return;
    }

    previousFocusRef.current = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    closeButtonRef.current?.focus();

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        close();
      } else if (event.key === "ArrowLeft" && images.length > 1) {
        showPrevious();
      } else if (event.key === "ArrowRight" && images.length > 1) {
        showNext();
      }
    };

    window.addEventListener("keydown", handleKeyDown);

    return () => {
      window.removeEventListener("keydown", handleKeyDown);
      document.body.style.overflow = previousOverflow;
      previousFocusRef.current?.focus();
    };
  }, [activeIndex, close, images.length, showNext, showPrevious]);

  useEffect(() => {
    if (activeIndex === null || images.length < 2) {
      return;
    }

    const nextIndex = (activeIndex + 1) % images.length;
    const previousIndex = (activeIndex - 1 + images.length) % images.length;
    [images[nextIndex], images[previousIndex]].forEach((item) => {
      const preload = new window.Image();
      preload.src = item.image;
    });
  }, [activeIndex, images]);

  const open = (index: number) => setActiveIndex(index);
  const currentIndex = activeIndex ?? 0;
  const activeImage = activeIndex === null ? null : images[activeIndex];

  const lightbox = activeImage && typeof document !== "undefined"
    ? createPortal(
        <div
          className="fixed inset-0 z-[100] flex bg-[#07100c]/95 p-3 backdrop-blur-sm sm:p-6"
          role="dialog"
          aria-modal="true"
          aria-label={`ภาพที่ ${currentIndex + 1} จาก ${images.length}`}
          onClick={(event) => {
            if (event.currentTarget === event.target) {
              close();
            }
          }}
          onTouchStart={(event) => {
            touchStartX.current = event.changedTouches[0]?.clientX ?? null;
          }}
          onTouchEnd={(event) => {
            if (touchStartX.current === null || images.length < 2) {
              return;
            }

            const distance = (event.changedTouches[0]?.clientX ?? touchStartX.current) - touchStartX.current;
            touchStartX.current = null;
            if (Math.abs(distance) < 45) {
              return;
            }

            if (distance > 0) {
              showPrevious();
            } else {
              showNext();
            }
          }}
        >
          <div className="relative mx-auto flex h-full w-full max-w-[1500px] flex-col">
            <header className="flex h-12 shrink-0 items-center justify-between gap-4 text-white">
              <p className="text-sm font-medium text-white/75">
                {currentIndex + 1} / {images.length}
              </p>
              <button
                ref={closeButtonRef}
                type="button"
                className="grid size-11 place-items-center rounded-full border border-white/20 bg-black/25 text-white transition hover:bg-white hover:text-[#17211c] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                onClick={close}
                aria-label="ปิดแกลเลอรี"
                title="ปิด"
              >
                <X aria-hidden="true" size={22} />
              </button>
            </header>

            <div className="relative min-h-0 flex-1">
              <Image
                src={activeImage.image}
                alt={activeImage.alt}
                fill
                priority
                sizes="100vw"
                className="select-none object-contain"
                draggable={false}
              />

              {images.length > 1 ? (
                <>
                  <button
                    type="button"
                    className="absolute left-1 top-1/2 grid size-11 -translate-y-1/2 place-items-center rounded-full border border-white/20 bg-black/35 text-white transition hover:bg-white hover:text-[#17211c] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:left-4 sm:size-12"
                    onClick={showPrevious}
                    aria-label="ดูรูปก่อนหน้า"
                    title="รูปก่อนหน้า"
                  >
                    <ChevronLeft aria-hidden="true" size={25} />
                  </button>
                  <button
                    type="button"
                    className="absolute right-1 top-1/2 grid size-11 -translate-y-1/2 place-items-center rounded-full border border-white/20 bg-black/35 text-white transition hover:bg-white hover:text-[#17211c] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:right-4 sm:size-12"
                    onClick={showNext}
                    aria-label="ดูรูปถัดไป"
                    title="รูปถัดไป"
                  >
                    <ChevronRight aria-hidden="true" size={25} />
                  </button>
                </>
              ) : null}
            </div>

            <footer className="shrink-0 pt-3 text-center text-white">
              {activeImage.caption ? <p className="text-sm text-white/80">{activeImage.caption}</p> : null}
              {images.length > 1 ? (
                <div className="mt-3 hidden justify-center gap-2 sm:flex">
                  {images.map((image, index) => (
                    <button
                      key={image.id}
                      type="button"
                      className={`relative h-14 w-20 overflow-hidden rounded-md border-2 transition ${
                        index === activeIndex ? "border-white opacity-100" : "border-transparent opacity-55 hover:opacity-100"
                      }`}
                      onClick={() => setActiveIndex(index)}
                      aria-label={`เปิดภาพที่ ${index + 1}`}
                      aria-current={index === activeIndex ? "true" : undefined}
                    >
                      <Image src={image.image} alt="" fill sizes="80px" className="object-cover" />
                    </button>
                  ))}
                </div>
              ) : null}
            </footer>
          </div>
        </div>,
        document.body,
      )
    : null;

  return (
    <>
      <div className="mt-9 grid gap-5 md:grid-cols-2">
        {images.map((image, index) => (
          <figure key={image.id} className={index === 0 && images.length > 2 ? "md:col-span-2" : ""}>
            <button
              type="button"
              className={`group relative block w-full cursor-zoom-in overflow-hidden rounded-lg bg-[#e4e9e6] text-left focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#0f6b45] ${
                index === 0 && images.length > 2 ? "aspect-[16/8]" : "aspect-[4/3]"
              }`}
              onClick={() => open(index)}
              aria-label={`ขยายภาพ ${image.alt}`}
            >
              <Image
                src={image.image}
                alt={image.alt}
                fill
                sizes={index === 0 ? "100vw" : "(min-width: 768px) 50vw, 100vw"}
                className="object-cover transition duration-500 group-hover:scale-[1.025]"
              />
              <span className="absolute right-4 top-4 grid size-11 place-items-center rounded-full bg-white/90 text-[#173427] opacity-0 shadow-sm transition group-hover:opacity-100 group-focus-visible:opacity-100">
                <Expand aria-hidden="true" size={19} />
              </span>
            </button>
            {image.caption ? <figcaption className="mt-3 text-sm text-[#667169]">{image.caption}</figcaption> : null}
          </figure>
        ))}
      </div>
      {lightbox}
    </>
  );
}
