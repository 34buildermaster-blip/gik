import Image from "next/image";
import { assetPath } from "@/lib/asset-path";

type BrandLogoImageProps = {
  className?: string;
  sizes?: string;
  priority?: boolean;
};

export function BrandLogoImage({ className, sizes = "56px", priority = false }: BrandLogoImageProps) {
  return (
    <Image
      src={assetPath("/brand-logo.png")}
      alt=""
      width={1182}
      height={1208}
      sizes={sizes}
      priority={priority}
      className={className}
    />
  );
}
