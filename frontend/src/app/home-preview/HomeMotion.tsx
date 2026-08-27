"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(useGSAP);

const REVEAL_EASE = "power3.out";

export default function HomeMotion() {
  useGSAP(() => {
    const root = document.querySelector<HTMLElement>("[data-home-motion-root]");
    if (!root || window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    gsap.registerPlugin(ScrollTrigger);

    const media = gsap.matchMedia();

    media.add(
      {
        desktop: "(min-width: 901px)",
        compact: "(max-width: 900px)",
      },
      (context) => {
        const desktop = Boolean(context.conditions?.desktop);
        const revealDistance = desktop ? 52 : 30;

        root.querySelectorAll<HTMLElement>("[data-gsap-heading]").forEach((heading) => {
          gsap.from(Array.from(heading.children), {
            autoAlpha: 0,
            y: revealDistance,
            duration: desktop ? 1.05 : 0.78,
            ease: REVEAL_EASE,
            stagger: 0.1,
            scrollTrigger: {
              trigger: heading,
              start: desktop ? "top 82%" : "top 88%",
              once: true,
            },
          });
        });

        root.querySelectorAll<HTMLElement>("[data-gsap-copy]").forEach((copy) => {
          const targets = Array.from(copy.children);
          if (!targets.length) return;

          gsap.from(targets, {
            autoAlpha: 0,
            y: revealDistance * 0.72,
            duration: 0.9,
            ease: REVEAL_EASE,
            stagger: 0.11,
            scrollTrigger: {
              trigger: copy,
              start: "top 86%",
              once: true,
            },
          });
        });

        root.querySelectorAll<HTMLElement>("[data-gsap-stagger]").forEach((group) => {
          const items = Array.from(group.querySelectorAll<HTMLElement>("[data-gsap-item]"));
          if (!items.length) return;

          const preset = group.dataset.gsapStagger;
          const fromVars: gsap.TweenVars = {
            autoAlpha: 0,
            duration: desktop ? 0.9 : 0.7,
            ease: REVEAL_EASE,
            stagger: desktop ? 0.09 : 0.07,
            scrollTrigger: {
              trigger: group,
              start: desktop ? "top 80%" : "top 88%",
              once: true,
            },
          };

          if (preset === "projects") {
            Object.assign(fromVars, {
              y: desktop ? 58 : 32,
              clipPath: "inset(0 0 100% 0 round 10px)",
              stagger: desktop ? 0.16 : 0.1,
              duration: desktop ? 1.15 : 0.82,
            });
          } else if (preset === "brands") {
            Object.assign(fromVars, {
              y: 22,
              scale: 0.92,
              stagger: desktop ? { each: 0.055, from: "center" } : 0.045,
            });
          } else if (preset === "process") {
            Object.assign(fromVars, {
              x: desktop ? -28 : 0,
              y: desktop ? 24 : 30,
              stagger: desktop ? 0.14 : 0.09,
            });
          } else if (preset === "promise") {
            Object.assign(fromVars, {
              y: 20,
              stagger: 0.08,
              duration: 0.72,
            });
          } else {
            Object.assign(fromVars, {
              y: revealDistance,
              rotateX: desktop ? 7 : 0,
              transformPerspective: 900,
            });
          }

          gsap.from(items, fromVars);
        });

        root.querySelectorAll<HTMLElement>("[data-gsap-media]").forEach((mediaBlock) => {
          gsap.from(mediaBlock, {
            autoAlpha: 0,
            y: desktop ? 36 : 24,
            clipPath: "inset(0 0 18% 0 round 10px)",
            duration: desktop ? 1.2 : 0.85,
            ease: "expo.out",
            scrollTrigger: {
              trigger: mediaBlock,
              start: desktop ? "top 84%" : "top 90%",
              once: true,
            },
          });
        });

        root.querySelectorAll<HTMLElement>("[data-gsap-form]").forEach((form) => {
          gsap.from(Array.from(form.children), {
            autoAlpha: 0,
            x: desktop ? 42 : 0,
            y: desktop ? 0 : 24,
            duration: 0.82,
            ease: REVEAL_EASE,
            stagger: 0.075,
            scrollTrigger: {
              trigger: form,
              start: "top 78%",
              once: true,
            },
          });
        });

        root.querySelectorAll<HTMLElement>("[data-gsap-line]").forEach((line) => {
          gsap.from(line, {
            scaleX: 0,
            duration: desktop ? 1.35 : 0.9,
            ease: "power2.inOut",
            transformOrigin: "left center",
            scrollTrigger: {
              trigger: line,
              start: "top 86%",
              once: true,
            },
          });
        });

        if (desktop) {
          root.querySelectorAll<HTMLElement>("[data-gsap-parallax]").forEach((container) => {
            const image = container.querySelector<HTMLElement>("img");
            if (!image) return;

            gsap.fromTo(
              image,
              { yPercent: -5, scale: 1.08 },
              {
                yPercent: 5,
                scale: 1.08,
                ease: "none",
                scrollTrigger: {
                  trigger: container,
                  start: "top bottom",
                  end: "bottom top",
                  scrub: 0.7,
                },
              },
            );
          });
        }
      },
    );

    return () => media.revert();
  }, []);

  return null;
}
