"use client";

import { createContext, useContext, useEffect, useState } from "react";
import { siteConfig, socialLinks } from "@/lib/site-config";

export type SiteSettings = {
  general: {
    company_name_th: string;
    company_name_en: string;
    tagline: string;
    phone_display: string;
    phone_href: string;
    email: string;
    address: string;
    service_area: string;
    business_hours: string;
    copyright: string;
  };
  branding: {
    logo_url: string | null;
    footer_logo_url: string | null;
    favicon_url: string | null;
  };
  social: {
    facebook_url: string;
    instagram_url: string;
    line_url: string;
    tiktok_url: string;
  };
  cta: {
    consultation_label: string;
    tracking_label: string;
    contact_heading: string;
    contact_description: string;
  };
  navigation: {
    show_house_designs: boolean;
    show_updates: boolean;
    show_blog: boolean;
    show_faq: boolean;
  };
  display: {
    show_home_services: boolean;
    show_home_projects: boolean;
    show_home_process: boolean;
    show_home_partners: boolean;
    show_home_reviews: boolean;
    show_home_contact: boolean;
  };
  seo: {
    default_title: string;
    default_description: string;
    og_image_url: string | null;
  };
};

const socialByIcon = Object.fromEntries(socialLinks.map((item) => [item.icon, item.href]));

export const defaultSiteSettings: SiteSettings = {
  general: {
    company_name_th: "34 บิลด์ มาสเตอร์ คอนสตรัคชั่น",
    company_name_en: siteConfig.name,
    tagline: "รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร",
    phone_display: siteConfig.phoneDisplay,
    phone_href: siteConfig.phoneHref,
    email: siteConfig.email,
    address: siteConfig.address,
    service_area: siteConfig.area,
    business_hours: "จันทร์–เสาร์ 08:30–17:30 น.",
    copyright: "© 2026 34 Build Master Construction.",
  },
  branding: {
    logo_url: null,
    footer_logo_url: null,
    favicon_url: null,
  },
  social: {
    facebook_url: socialByIcon.facebook,
    instagram_url: socialByIcon.instagram,
    line_url: socialByIcon.line,
    tiktok_url: socialByIcon.tiktok,
  },
  cta: {
    consultation_label: "ปรึกษาโครงการ",
    tracking_label: "ติดตามความคืบหน้า",
    contact_heading: "เริ่มต้นบ้านที่ใช่ ด้วยการวางแผนที่ชัดเจน",
    contact_description: "เล่าไอเดีย พื้นที่ และงบประมาณเบื้องต้นให้เรา ทีมงานจะติดต่อกลับเพื่อช่วยประเมินแนวทางที่เหมาะกับโครงการ",
  },
  navigation: {
    show_house_designs: true,
    show_updates: true,
    show_blog: true,
    show_faq: true,
  },
  display: {
    show_home_services: true,
    show_home_projects: true,
    show_home_process: true,
    show_home_partners: true,
    show_home_reviews: true,
    show_home_contact: true,
  },
  seo: {
    default_title: "34 Build Master | รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน",
    default_description: siteConfig.description,
    og_image_url: null,
  },
};

const SiteSettingsContext = createContext<SiteSettings>(defaultSiteSettings);

function mergeSettings(remote: Partial<SiteSettings>): SiteSettings {
  return {
    general: { ...defaultSiteSettings.general, ...remote.general },
    branding: { ...defaultSiteSettings.branding, ...remote.branding },
    social: { ...defaultSiteSettings.social, ...remote.social },
    cta: { ...defaultSiteSettings.cta, ...remote.cta },
    navigation: { ...defaultSiteSettings.navigation, ...remote.navigation },
    display: { ...defaultSiteSettings.display, ...remote.display },
    seo: { ...defaultSiteSettings.seo, ...remote.seo },
  };
}

export function SiteSettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState(defaultSiteSettings);

  useEffect(() => {
    const controller = new AbortController();
    const configuredApiUrl = process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, "");
    const isStaticPreview = Boolean(process.env.NEXT_PUBLIC_BASE_PATH);

    if (!configuredApiUrl && isStaticPreview) {
      document.documentElement.dataset.siteSettings = "fallback";
      return () => controller.abort();
    }

    const settingsEndpoint = configuredApiUrl
      ? `${configuredApiUrl}/api/site-settings`
      : "/api/site-settings";

    fetch(settingsEndpoint, {
      headers: { Accept: "application/json" },
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) throw new Error("Unable to load site settings");
        return response.json() as Promise<{ data?: Partial<SiteSettings> }>;
      })
      .then((payload) => {
        setSettings(mergeSettings(payload.data || {}));
        document.documentElement.dataset.siteSettings = "remote";
      })
      .catch((error: unknown) => {
        if (!(error instanceof DOMException && error.name === "AbortError")) {
          setSettings(defaultSiteSettings);
          document.documentElement.dataset.siteSettings = "fallback";
        }
      });

    return () => controller.abort();
  }, []);

  useEffect(() => {
    const basePath = process.env.NEXT_PUBLIC_BASE_PATH || "";
    const routePath = window.location.pathname.replace(basePath, "").replace(/\/+$/, "") || "/";

    if (routePath === "/" || routePath === "/home-preview") {
      document.title = settings.seo.default_title;
      document.querySelector('meta[name="description"]')?.setAttribute("content", settings.seo.default_description);
    }

    if (settings.branding.favicon_url) {
      let favicon = document.querySelector<HTMLLinkElement>('link[rel~="icon"]');
      if (!favicon) {
        favicon = document.createElement("link");
        favicon.rel = "icon";
        document.head.appendChild(favicon);
      }
      favicon.href = settings.branding.favicon_url;
    }
  }, [settings.branding.favicon_url, settings.seo.default_description, settings.seo.default_title]);

  return <SiteSettingsContext.Provider value={settings}>{children}</SiteSettingsContext.Provider>;
}

export function useSiteSettings() {
  return useContext(SiteSettingsContext);
}
