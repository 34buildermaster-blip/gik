"use client";

import Link from "next/link";
import { BarChart3, Check, Cookie, Megaphone, ShieldCheck, X } from "lucide-react";
import { useCallback, useEffect, useState } from "react";

const CONSENT_COOKIE = "bm_cookie_consent";
const CONSENT_VERSION = "1.0";
const CONSENT_MAX_AGE = 60 * 60 * 24 * 180;
const OPEN_SETTINGS_EVENT = "bm:open-cookie-settings";
const CONSENT_CHANGED_EVENT = "bm:cookie-consent";

export type CookieConsentPreferences = {
  version: string;
  essential: true;
  analytics: boolean;
  marketing: boolean;
  updatedAt: string;
};

function readConsent(): CookieConsentPreferences | null {
  const cookie = document.cookie
    .split("; ")
    .find((item) => item.startsWith(`${CONSENT_COOKIE}=`));

  if (!cookie) return null;

  try {
    const value = JSON.parse(decodeURIComponent(cookie.split("=").slice(1).join("="))) as CookieConsentPreferences;
    return value.version === CONSENT_VERSION && value.essential === true ? value : null;
  } catch {
    return null;
  }
}

function writeConsent(preferences: CookieConsentPreferences) {
  const secure = window.location.protocol === "https:" ? "; Secure" : "";
  document.cookie = `${CONSENT_COOKIE}=${encodeURIComponent(JSON.stringify(preferences))}; Path=/; Max-Age=${CONSENT_MAX_AGE}; SameSite=Lax${secure}`;
  window.dispatchEvent(new CustomEvent(CONSENT_CHANGED_EVENT, { detail: preferences }));
}

export function canUseCookieCategory(category: "analytics" | "marketing") {
  if (typeof document === "undefined") return false;
  return readConsent()?.[category] === true;
}

export function CookieSettingsButton({ className = "" }: { className?: string }) {
  return (
    <button
      className={className}
      type="button"
      onClick={() => window.dispatchEvent(new Event(OPEN_SETTINGS_EVENT))}
    >
      ตั้งค่าคุกกี้
    </button>
  );
}

export function CookieConsent() {
  const [ready, setReady] = useState(false);
  const [showBanner, setShowBanner] = useState(false);
  const [showSettings, setShowSettings] = useState(false);
  const [analytics, setAnalytics] = useState(false);
  const [marketing, setMarketing] = useState(false);

  const openSettings = useCallback(() => {
    const saved = readConsent();
    setAnalytics(saved?.analytics ?? false);
    setMarketing(saved?.marketing ?? false);
    setShowSettings(true);
  }, []);

  useEffect(() => {
    window.addEventListener(OPEN_SETTINGS_EVENT, openSettings);
    const frame = window.requestAnimationFrame(() => {
      const saved = readConsent();
      setAnalytics(saved?.analytics ?? false);
      setMarketing(saved?.marketing ?? false);
      setShowBanner(!saved);
      setReady(true);
    });

    return () => {
      window.cancelAnimationFrame(frame);
      window.removeEventListener(OPEN_SETTINGS_EVENT, openSettings);
    };
  }, [openSettings]);

  useEffect(() => {
    if (!showSettings) return;
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") setShowSettings(false);
    };
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", closeOnEscape);
    return () => {
      document.body.style.overflow = "";
      window.removeEventListener("keydown", closeOnEscape);
    };
  }, [showSettings]);

  const save = (nextAnalytics: boolean, nextMarketing: boolean) => {
    const preferences: CookieConsentPreferences = {
      version: CONSENT_VERSION,
      essential: true,
      analytics: nextAnalytics,
      marketing: nextMarketing,
      updatedAt: new Date().toISOString(),
    };
    writeConsent(preferences);
    setAnalytics(nextAnalytics);
    setMarketing(nextMarketing);
    setShowBanner(false);
    setShowSettings(false);
  };

  if (!ready) return null;

  return (
    <>
      {showBanner ? (
        <section className="cookie-banner" aria-label="การตั้งค่าคุกกี้">
          <div className="cookie-banner-icon" aria-hidden="true"><Cookie /></div>
          <div className="cookie-banner-copy">
            <strong>เราให้ความสำคัญกับความเป็นส่วนตัวของคุณ</strong>
            <p>
              เว็บไซต์ใช้คุกกี้ที่จำเป็นเพื่อให้ระบบทำงาน และจะใช้คุกกี้วิเคราะห์หรือการตลาดเมื่อคุณอนุญาตเท่านั้น
              อ่านรายละเอียดได้ที่ <Link href="/cookie-policy">นโยบายคุกกี้</Link>
            </p>
          </div>
          <div className="cookie-banner-actions">
            <button type="button" className="cookie-button cookie-button--ghost" onClick={openSettings}>ตั้งค่ารายละเอียด</button>
            <button type="button" className="cookie-button cookie-button--secondary" onClick={() => save(false, false)}>ปฏิเสธที่ไม่จำเป็น</button>
            <button type="button" className="cookie-button cookie-button--primary" onClick={() => save(true, true)}>ยอมรับทั้งหมด</button>
          </div>
        </section>
      ) : null}

      {showSettings ? (
        <div className="cookie-modal-backdrop" role="presentation" onMouseDown={(event) => {
          if (event.target === event.currentTarget) setShowSettings(false);
        }}>
          <section className="cookie-modal" role="dialog" aria-modal="true" aria-labelledby="cookie-settings-title">
            <header className="cookie-modal-header">
              <div>
                <span>Privacy preferences</span>
                <h2 id="cookie-settings-title">ศูนย์ตั้งค่าคุกกี้</h2>
              </div>
              <button className="cookie-close" type="button" onClick={() => setShowSettings(false)} aria-label="ปิดหน้าต่าง"><X /></button>
            </header>

            <p className="cookie-modal-intro">เลือกประเภทข้อมูลที่อนุญาตให้เว็บไซต์จัดเก็บได้ คุกกี้ที่จำเป็นไม่สามารถปิดได้เพราะใช้เพื่อความปลอดภัยและจดจำการตั้งค่านี้</p>

            <div className="cookie-category-list">
              <article className="cookie-category">
                <div className="cookie-category-icon"><ShieldCheck /></div>
                <div><h3>คุกกี้ที่จำเป็น</h3><p>ใช้สำหรับความปลอดภัย การทำงานพื้นฐานของเว็บไซต์ และบันทึกตัวเลือกคุกกี้</p></div>
                <span className="cookie-always-on"><Check />เปิดเสมอ</span>
              </article>

              <label className="cookie-category">
                <div className="cookie-category-icon"><BarChart3 /></div>
                <div><h3>คุกกี้วิเคราะห์</h3><p>ช่วยให้เข้าใจการใช้งานเว็บไซต์แบบภาพรวม เพื่อนำไปปรับปรุงเนื้อหาและประสบการณ์</p></div>
                <span className="cookie-switch"><input type="checkbox" aria-label="อนุญาตคุกกี้วิเคราะห์" checked={analytics} onChange={(event) => setAnalytics(event.target.checked)} /><i aria-hidden="true" /></span>
              </label>

              <label className="cookie-category">
                <div className="cookie-category-icon"><Megaphone /></div>
                <div><h3>คุกกี้การตลาด</h3><p>ใช้วัดผลโฆษณาหรือแสดงเนื้อหาที่เกี่ยวข้อง ปัจจุบันเว็บไซต์ยังไม่เปิดใช้คุกกี้ประเภทนี้</p></div>
                <span className="cookie-switch"><input type="checkbox" aria-label="อนุญาตคุกกี้การตลาด" checked={marketing} onChange={(event) => setMarketing(event.target.checked)} /><i aria-hidden="true" /></span>
              </label>
            </div>

            <footer className="cookie-modal-actions">
              <button type="button" className="cookie-button cookie-button--secondary" onClick={() => save(false, false)}>ปฏิเสธที่ไม่จำเป็น</button>
              <button type="button" className="cookie-button cookie-button--primary" onClick={() => save(analytics, marketing)}>บันทึกการตั้งค่า</button>
            </footer>
          </section>
        </div>
      ) : null}
    </>
  );
}
