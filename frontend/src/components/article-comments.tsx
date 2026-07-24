"use client";

import { FormEvent, useEffect, useMemo, useState } from "react";
import { MessageCircle, Send, ShieldCheck, UserRound } from "lucide-react";

type PublicComment = {
  id: number;
  authorName: string;
  body: string;
  createdAt: string | null;
  adminReply: string | null;
  repliedAt: string | null;
};

type ArticleCommentsProps = {
  slug: string;
  title: string;
};

type FormState = {
  authorName: string;
  authorEmail: string;
  body: string;
  website: string;
};

const initialForm: FormState = {
  authorName: "",
  authorEmail: "",
  body: "",
  website: "",
};

function getApiBaseUrl() {
  const configured = process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, "");

  if (configured) {
    return configured;
  }

  if (typeof window === "undefined") {
    return "http://127.0.0.1:8000";
  }

  const { hostname, protocol } = window.location;
  const isLocal = hostname === "localhost" || hostname === "127.0.0.1" || /^192\.168\./.test(hostname);

  return isLocal ? `${protocol}//${hostname}:8000` : "http://127.0.0.1:8000";
}

function formatCommentDate(value: string | null) {
  if (!value) {
    return "";
  }

  return new Intl.DateTimeFormat("th-TH", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

function authorInitial(name: string) {
  return Array.from(name.trim())[0]?.toUpperCase() || "34";
}

export function ArticleComments({ slug, title }: ArticleCommentsProps) {
  const [comments, setComments] = useState<PublicComment[]>([]);
  const [form, setForm] = useState<FormState>(initialForm);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isAvailable, setIsAvailable] = useState(true);
  const [feedback, setFeedback] = useState<{ type: "success" | "error"; message: string } | null>(null);
  const endpoint = useMemo(() => {
    const baseUrl = getApiBaseUrl();
    return `${baseUrl}/api/articles/${encodeURIComponent(slug)}/comments`;
  }, [slug]);

  useEffect(() => {
    const controller = new AbortController();

    async function loadComments() {
      try {
        const response = await fetch(endpoint, {
          headers: { Accept: "application/json" },
          signal: controller.signal,
        });

        if (!response.ok) {
          throw new Error("comments-unavailable");
        }

        const payload = (await response.json()) as { data?: PublicComment[] };
        setComments(Array.isArray(payload.data) ? payload.data : []);
      } catch (error) {
        if (error instanceof DOMException && error.name === "AbortError") {
          return;
        }

        setIsAvailable(false);
      } finally {
        setIsLoading(false);
      }
    }

    void loadComments();

    return () => controller.abort();
  }, [endpoint]);

  function updateField(field: keyof FormState, value: string) {
    setForm((current) => ({ ...current, [field]: value }));
  }

  async function submitComment(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setFeedback(null);

    setIsSubmitting(true);

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          article_title: title,
          author_name: form.authorName,
          author_email: form.authorEmail || null,
          body: form.body,
          website: form.website,
        }),
      });

      const payload = (await response.json()) as {
        message?: string;
        errors?: Record<string, string[]>;
      };

      if (!response.ok) {
        const firstError = payload.errors ? Object.values(payload.errors).flat()[0] : null;
        throw new Error(firstError || payload.message || "ส่งความคิดเห็นไม่สำเร็จ");
      }

      setForm(initialForm);
      setFeedback({
        type: "success",
        message: payload.message || "ส่งความคิดเห็นแล้ว ทีมงานจะตรวจสอบก่อนเผยแพร่",
      });
    } catch (error) {
      setFeedback({
        type: "error",
        message: error instanceof Error ? error.message : "ส่งความคิดเห็นไม่สำเร็จ กรุณาลองอีกครั้ง",
      });
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <section className="border-y border-[#dfe4e0] bg-[#f4f6f5] px-5 py-12 lg:px-8 lg:py-14" aria-labelledby="article-comments-title">
      <div className="mx-auto max-w-7xl lg:grid lg:grid-cols-[minmax(220px,.7fr)_minmax(0,2.3fr)] lg:items-start lg:gap-10">
        <div className="flex flex-col gap-5 lg:pt-2">
          <div>
            <p className="section-kicker">Discussion</p>
            <h2 id="article-comments-title" className="mt-3 text-2xl font-semibold leading-tight text-[#17211c] sm:text-3xl">
              ร่วมพูดคุยเกี่ยวกับบทความ
            </h2>
            <p className="mt-3 max-w-md text-[15px] leading-7 text-[#667169]">
              แบ่งปันคำถามหรือประสบการณ์ของคุณ ทีมงานจะตรวจสอบข้อความก่อนนำขึ้นแสดง
            </p>
          </div>
          <div className="inline-flex w-fit items-center gap-2 rounded-full border border-[#cad8d0] bg-white px-4 py-2 text-sm font-semibold text-[#315241]">
            <MessageCircle aria-hidden="true" size={17} />
            {comments.length} ความคิดเห็น
          </div>
        </div>

        <div className="mt-8 grid items-start gap-6 md:grid-cols-2 lg:mt-0 lg:grid-cols-[minmax(0,1fr)_minmax(320px,1fr)]">
          <div className="grid gap-4 lg:max-h-[610px] lg:overflow-y-auto lg:pr-2" aria-live="polite">
            {isLoading ? (
              <div className="rounded-lg border border-[#dfe4e0] bg-white p-7 text-[#667169]">กำลังโหลดความคิดเห็น...</div>
            ) : comments.length > 0 ? (
              comments.map((comment) => (
                <article key={comment.id} className="rounded-lg border border-[#dfe4e0] bg-white p-5 sm:p-6">
                  <header className="flex items-center gap-3">
                    <span className="grid size-11 shrink-0 place-items-center rounded-full bg-[#e7f1ec] font-semibold text-[#0f6b45]" aria-hidden="true">
                      {authorInitial(comment.authorName)}
                    </span>
                    <div>
                      <h3 className="text-lg font-semibold text-[#17211c]">{comment.authorName}</h3>
                      <p className="mt-0.5 text-sm text-[#7a847e]">{formatCommentDate(comment.createdAt)}</p>
                    </div>
                  </header>
                  <p className="mt-5 whitespace-pre-wrap text-base leading-8 text-[#455149]">{comment.body}</p>

                  {comment.adminReply ? (
                    <div className="mt-5 border-l-2 border-[#0f6b45] bg-[#f2f7f4] px-4 py-4">
                      <div className="flex items-center gap-2 text-sm font-semibold text-[#0f6b45]">
                        <ShieldCheck aria-hidden="true" size={17} />
                        คำตอบจาก 34 Build Master
                      </div>
                      <p className="mt-2 whitespace-pre-wrap text-sm leading-7 text-[#455149]">{comment.adminReply}</p>
                      {comment.repliedAt ? <p className="mt-2 text-xs text-[#7a847e]">{formatCommentDate(comment.repliedAt)}</p> : null}
                    </div>
                  ) : null}
                </article>
              ))
            ) : (
              <div className="rounded-lg border border-dashed border-[#cad8d0] bg-white/70 px-6 py-8 text-center">
                <MessageCircle className="mx-auto text-[#0f6b45]" aria-hidden="true" size={28} />
                <h3 className="mt-4 text-xl font-semibold text-[#17211c]">เริ่มบทสนทนาเป็นคนแรก</h3>
                <p className="mt-2 text-sm leading-6 text-[#667169]">ยังไม่มีความคิดเห็นที่เผยแพร่สำหรับบทความนี้</p>
              </div>
            )}

            {!isAvailable && !isLoading ? (
              <p className="text-sm leading-6 text-[#7a847e]">
                ระบบความคิดเห็นกำลังรอการเชื่อมต่อกับเซิร์ฟเวอร์ ขณะนี้ยังไม่สามารถแสดงรายการล่าสุดได้
              </p>
            ) : null}
          </div>

          <form className="rounded-lg border border-[#d7dfda] bg-white p-5 shadow-[0_18px_50px_rgba(18,34,25,0.07)]" onSubmit={submitComment}>
            <div className="flex items-center gap-3">
              <span className="grid size-10 place-items-center rounded-full bg-[#173427] text-white">
                <UserRound aria-hidden="true" size={19} />
              </span>
              <div>
                <h3 className="text-xl font-semibold text-[#17211c]">แสดงความคิดเห็น</h3>
                <p className="mt-0.5 text-sm text-[#7a847e]">ช่องที่มี * จำเป็นต้องกรอก</p>
              </div>
            </div>

            <div className="mt-5 grid gap-3.5">
              <label className="grid gap-2 text-sm font-semibold text-[#315241]">
                ชื่อ *
                <input
                  type="text"
                  value={form.authorName}
                  onChange={(event) => updateField("authorName", event.target.value)}
                  minLength={2}
                  maxLength={100}
                  required
                  autoComplete="name"
                  className="min-h-11 rounded-md border border-[#cdd8d1] bg-white px-4 font-normal text-[#17211c] outline-none transition focus:border-[#0f6b45] focus:ring-4 focus:ring-[#0f6b45]/10"
                />
              </label>

              <label className="grid gap-2 text-sm font-semibold text-[#315241]">
                อีเมล
                <input
                  type="email"
                  value={form.authorEmail}
                  onChange={(event) => updateField("authorEmail", event.target.value)}
                  maxLength={255}
                  autoComplete="email"
                  className="min-h-11 rounded-md border border-[#cdd8d1] bg-white px-4 font-normal text-[#17211c] outline-none transition focus:border-[#0f6b45] focus:ring-4 focus:ring-[#0f6b45]/10"
                />
                <span className="font-normal text-[#7a847e]">ใช้สำหรับติดต่อกลับเท่านั้น และจะไม่แสดงบนเว็บไซต์</span>
              </label>

              <label className="grid gap-2 text-sm font-semibold text-[#315241]">
                ความคิดเห็น *
                <textarea
                  value={form.body}
                  onChange={(event) => updateField("body", event.target.value)}
                  minLength={3}
                  maxLength={2000}
                  required
                  rows={4}
                  className="resize-y rounded-md border border-[#cdd8d1] bg-white px-4 py-3 font-normal leading-7 text-[#17211c] outline-none transition focus:border-[#0f6b45] focus:ring-4 focus:ring-[#0f6b45]/10"
                />
                <span className="text-right font-normal text-[#7a847e]">{form.body.length}/2,000</span>
              </label>

              <label className="sr-only" aria-hidden="true">
                เว็บไซต์
                <input
                  type="text"
                  value={form.website}
                  onChange={(event) => updateField("website", event.target.value)}
                  tabIndex={-1}
                  autoComplete="off"
                />
              </label>
            </div>

            {feedback ? (
              <p
                className={`mt-4 rounded-md px-4 py-3 text-sm leading-6 ${
                  feedback.type === "success" ? "bg-[#e7f4ec] text-[#0f6b45]" : "bg-[#fff0f0] text-[#973d3d]"
                }`}
                role="status"
              >
                {feedback.message}
              </p>
            ) : null}

            <button
              type="submit"
              disabled={isSubmitting || !isAvailable}
              className="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-[#0f6b45] px-6 font-semibold text-white transition hover:bg-[#0b5839] disabled:cursor-not-allowed disabled:opacity-50"
            >
              <Send aria-hidden="true" size={18} />
              {isSubmitting ? "กำลังส่ง..." : "ส่งความคิดเห็น"}
            </button>

            <p className="mt-4 flex items-start gap-2 text-xs leading-5 text-[#7a847e]">
              <ShieldCheck className="mt-0.5 shrink-0 text-[#0f6b45]" aria-hidden="true" size={15} />
              ทุกความคิดเห็นจะผ่านการตรวจสอบก่อนเผยแพร่ เพื่อรักษาพื้นที่สนทนาที่สุภาพและเป็นประโยชน์
            </p>
          </form>
        </div>
      </div>
    </section>
  );
}
