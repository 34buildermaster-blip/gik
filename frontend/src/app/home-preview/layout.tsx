import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "34 Build Master | รับออกแบบ รีโนเวท สร้างบ้านและบิวท์อิน",
  description: "34 Build Master Construction รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจรในเชียงใหม่ ดูแลตั้งแต่แนวคิดจนถึงวันส่งมอบ",
};

export default function HomePreviewLayout({ children }: { children: React.ReactNode }) {
  return children;
}
