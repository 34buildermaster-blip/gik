"use client";

import { X } from "lucide-react";
import { useCallback, useEffect, useRef, useState } from "react";
import { sitePath } from "@/lib/asset-path";
import { fetchWelcomePopup, type WelcomePopupData } from "@/lib/welcome-popup";

const COOKIE_CHANGED_EVENT = "bm:cookie-consent";

function popupSeenKey(popup: WelcomePopupData) {
  return `bm_welcome_popup_seen_${popup.id}_${popup.updatedAt ?? "1"}`;
}

export function WelcomePopup() {
  const [popup, setPopup] = useState<WelcomePopupData | null>(null);
  const [visible, setVisible] = useState(false);
  const closeButtonRef = useRef<HTMLButtonElement>(null);

  const dismiss = useCallback(() => {
    if (popup) sessionStorage.setItem(popupSeenKey(popup), "1");
    setVisible(false);
  }, [popup]);

  useEffect(() => {
    const controller = new AbortController();
    let revealTimer: number | undefined;

    const reveal = (data: WelcomePopupData) => {
      if (sessionStorage.getItem(popupSeenKey(data))) return;
      revealTimer = window.setTimeout(() => {
        setPopup(data);
        setVisible(true);
      }, 700);
    };

    void fetchWelcomePopup(controller.signal).then((data) => {
      if (!data || controller.signal.aborted) return;

      if (document.querySelector(".cookie-banner")) {
        const afterCookieChoice = () => reveal(data);
        window.addEventListener(COOKIE_CHANGED_EVENT, afterCookieChoice, { once: true });
        controller.signal.addEventListener("abort", () => {
          window.removeEventListener(COOKIE_CHANGED_EVENT, afterCookieChoice);
        }, { once: true });
        return;
      }

      reveal(data);
    });

    return () => {
      controller.abort();
      if (revealTimer) window.clearTimeout(revealTimer);
    };
  }, []);

  useEffect(() => {
    if (!visible) return;
    closeButtonRef.current?.focus();
    document.body.style.overflow = "hidden";

    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") dismiss();
    };
    window.addEventListener("keydown", closeOnEscape);
    return () => {
      document.body.style.overflow = "";
      window.removeEventListener("keydown", closeOnEscape);
    };
  }, [dismiss, visible]);

  if (!popup || !visible) return null;

  const image = (
    <picture>
      {popup.mobileImage ? <source media="(max-width: 640px)" srcSet={popup.mobileImage} /> : null}
      {/* URL is returned by the managed Laravel media API and can be hosted on Google Drive. */}
      <img src={popup.desktopImage} alt={popup.alt} />
    </picture>
  );
  const isExternal = Boolean(popup.linkUrl && /^(https?:)?\/\//i.test(popup.linkUrl));

  return (
    <div className="welcome-popup-backdrop" role="presentation" onMouseDown={(event) => {
      if (event.target === event.currentTarget) dismiss();
    }}>
      <section className="welcome-popup" role="dialog" aria-modal="true" aria-label={popup.alt}>
        <button ref={closeButtonRef} className="welcome-popup-close" type="button" onClick={dismiss} aria-label="ปิดประกาศ">
          <X />
        </button>
        {popup.linkUrl ? (
          <a
            className="welcome-popup-media"
            href={isExternal ? popup.linkUrl : sitePath(popup.linkUrl)}
            target={isExternal ? "_blank" : undefined}
            rel={isExternal ? "noreferrer" : undefined}
            onClick={dismiss}
            aria-label={`${popup.alt} เปิดรายละเอียด`}
          >
            {image}
          </a>
        ) : (
          <div className="welcome-popup-media">{image}</div>
        )}
      </section>
    </div>
  );
}
