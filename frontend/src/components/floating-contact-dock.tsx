"use client";

import { Phone } from "lucide-react";
import { FaFacebookF, FaLine } from "react-icons/fa6";
import { useSiteSettings } from "@/contexts/site-settings-context";

const buttonClass =
  "group relative grid size-11 place-items-center rounded-full border border-white/70 bg-white text-[#0f6b45] shadow-[0_8px_22px_rgba(8,50,33,0.16)] transition duration-200 hover:-translate-y-0.5 hover:border-[#0f6b45] hover:bg-[#0f6b45] hover:text-white focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#78b397]/30 sm:size-12";

function Tooltip({ children }: { children: React.ReactNode }) {
  return (
    <span className="pointer-events-none absolute right-[calc(100%+12px)] top-1/2 hidden -translate-y-1/2 whitespace-nowrap rounded-md bg-[#17211c] px-3 py-2 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 group-focus-visible:opacity-100 sm:block">
      {children}
    </span>
  );
}

export function FloatingContactDock() {
  const settings = useSiteSettings();

  return (
    <aside
      className="fixed bottom-20 right-3 z-[70] flex flex-col gap-2 rounded-full border border-white/18 bg-[#123c2c]/94 p-2 shadow-[0_20px_48px_rgba(8,42,28,0.28)] backdrop-blur-xl sm:bottom-[28%] sm:right-5"
      aria-label="ช่องทางติดต่อด่วน"
    >
      <a className={buttonClass} href={settings.general.phone_href} aria-label={`โทร ${settings.general.phone_display}`}>
        <Phone className="size-5" strokeWidth={1.8} />
        <Tooltip>โทร {settings.general.phone_display}</Tooltip>
      </a>

      {settings.social.line_url ? (
        <a className={buttonClass} href={settings.social.line_url} target="_blank" rel="noreferrer" aria-label="ติดต่อผ่าน LINE OA">
          <FaLine className="size-5" />
          <Tooltip>LINE OA</Tooltip>
        </a>
      ) : null}

      {settings.social.facebook_url ? (
        <a className={buttonClass} href={settings.social.facebook_url} target="_blank" rel="noreferrer" aria-label="ติดต่อผ่าน Facebook">
          <FaFacebookF className="size-[18px]" />
          <Tooltip>Facebook</Tooltip>
        </a>
      ) : null}
    </aside>
  );
}
