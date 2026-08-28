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
              source: "/gik/bg-luxury-green.png",
              destination: "/bg-luxury-green.png",
            },
            {
              source: "/gik/bg-material-board.png",
              destination: "/bg-material-board.png",
            },
            {
              source: "/gik/hero-construction.png",
              destination: "/hero-construction.png",
            },
            {
              source: "/backend-api/:path*",
              destination: `${backendUrl}/api/:path*`,
            },
            {
              source: "/api/:path*",
              destination: `${backendUrl}/api/:path*`,
            },
            {
              source: "/admin/:path*",
              destination: `${backendUrl}/admin/:path*`,
            },
            {
              source: "/login/:path*",
              destination: `${backendUrl}/login/:path*`,
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
              source: "/change-password",
              destination: `${backendUrl}/change-password`,
            },
            {
              source: "/forgot-password",
              destination: `${backendUrl}/forgot-password`,
            },
            {
              source: "/reset-password/:path*",
              destination: `${backendUrl}/reset-password/:path*`,
            },
            {
              source: "/two-factor-challenge",
              destination: `${backendUrl}/two-factor-challenge`,
            },
            {
              source: "/my-projects/:path*",
              destination: `${backendUrl}/my-projects/:path*`,
            },
            {
              source: "/notifications/:path*",
              destination: `${backendUrl}/notifications/:path*`,
            },
            {
              source: "/media/:path*",
              destination: `${backendUrl}/media/:path*`,
            },
            {
              source: "/project-documents/:path*",
              destination: `${backendUrl}/project-documents/:path*`,
            },
            {
              source: "/project-issue-media/:path*",
              destination: `${backendUrl}/project-issue-media/:path*`,
            },
            {
              source: "/project-media/:path*",
              destination: `${backendUrl}/project-media/:path*`,
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
