import type { Metadata } from "next";
import { ContactBand, PageHero, SiteFooter, SiteHeader } from "@/components/site-chrome";
import { BlogList } from "@/components/blog-list";
import { getIntegratedBlogPosts } from "@/lib/blog-api";

export const metadata: Metadata = {
  title: "บทความ | 34 Build Master Construction",
  description:
    "บทความความรู้เรื่องออกแบบบ้าน รีโนเวท สร้างบ้าน และบิวท์อิน จาก 34 Build Master Construction",
};

export default async function BlogPage() {
  const posts = await getIntegratedBlogPosts();

  return (
    <main className="modern-inner-page min-h-screen bg-white text-[#17211c]">
      <SiteHeader />
      <PageHero title="บทความ" currentLabel="บทความ" />
      <BlogList initialPosts={posts} />
      <ContactBand />
      <SiteFooter />
    </main>
  );
}
