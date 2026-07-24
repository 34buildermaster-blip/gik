import type { NextConfig } from "next";

const isGithubPages = process.env.GITHUB_PAGES === "true";
const isStaticExport = isGithubPages || process.env.NEXT_OUTPUT === "export";
const repoName = "gik";
const backendUrl = process.env.BACKEND_URL || "http://127.0.0.1:8000";

const nextConfig: NextConfig = {
  allowedDevOrigins: ["127.0.0.1", "localhost"],
  env: {
    NEXT_PUBLIC_BASE_PATH: isGithubPages ? `/${repoName}` : "",
  },
  ...(isStaticExport
    ? {
        output: "export" as const,
      }
    : {
        async rewrites() {
          return [
            {
              source: "/backend-api/:path*",
              destination: `${backendUrl}/api/:path*`,
            },
            {
              source: "/admin/:path*",
              destination: `${backendUrl}/admin/:path*`,
            },
            {
              source: "/login",
              destination: `${backendUrl}/login`,
            },
            {
              source: "/register",
              destination: `${backendUrl}/register`,
            },
            {
              source: "/logout",
              destination: `${backendUrl}/logout`,
            },
            {
              source: "/uploads/:path*",
              destination: `${backendUrl}/uploads/:path*`,
            },
          ];
        },
      }),
  images: {
    unoptimized: true,
    remotePatterns: [
      {
        protocol: "https",
        hostname: "**",
      },
      {
        protocol: "http",
        hostname: "**",
      },
    ],
  },
  trailingSlash: true,
  ...(isGithubPages
    ? {
        basePath: `/${repoName}`,
        assetPrefix: `/${repoName}/`,
      }
    : {}),
};

export default nextConfig;
