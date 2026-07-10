import { assetPath } from "@/lib/asset-path";

export type ProjectUpdate = {
  title: string;
  stage: string;
  detail: string;
  image: string;
};

export const projectUpdates: ProjectUpdate[] = [
  {
    title: "ตรวจหน้างานรีโนเวทบ้านพักอาศัย",
    stage: "Site Survey",
    detail: "อัปเดตพื้นที่จริง วัดระยะ และเช็กจุดสำคัญก่อนจัดแผนงาน",
    image: assetPath("/hero-construction.png"),
  },
  {
    title: "เลือกวัสดุและโทนงานบิวท์อิน",
    stage: "Material Review",
    detail: "คุมโทนสี วัสดุ และรายละเอียดผิวให้ตรงกับภาพรวมบ้าน",
    image: assetPath("/bg-material-board.png"),
  },
  {
    title: "สรุป mood งาน luxury modern",
    stage: "Design Direction",
    detail: "จัดทิศทางดีไซน์ให้หรู เรียบ และต่อยอดเป็นงานจริงได้",
    image: assetPath("/bg-luxury-green.png"),
  },
];
