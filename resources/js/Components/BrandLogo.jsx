export default function BrandLogo({
  className = "h-14 w-auto"
}) {
  return (<svg viewBox="0 0 96 96" role="img" aria-label="Logo Printer POS" className={className}>
    <title>Logo Printer POS</title>

    {/* CSS animation langsung di dalam SVG */}
    <style>
      {
        ` @keyframes hueShift {
          0% {
            filter: hue-rotate(0deg) saturate(1);
          }
          50% {
            filter: hue-rotate(40deg) saturate(1.1);
          }
          100% {
            filter: hue-rotate(0deg) saturate(1);
          }
        }
        @keyframes breathe {
          0%,
          100% {
            transform: translateY(0px);
          }
          50% {
            transform: translateY(1.6px);
          }
        }
        @keyframes shimmer {
          0% {
            transform: translateX(-24px);
          }
          100% {
            transform: translateX(60px);
          }
        }
         `
      }</style>

    <defs>
      <linearGradient id="gg" x1="24" y1="26" x2="72" y2="70" gradientUnits="userSpaceOnUse">
        <stop offset="0" stopColor="#38BDF8"/>
        <stop offset="1" stopColor="#8B5CF6"/>
      </linearGradient>

      <linearGradient id="ss" x1="22" y1="24" x2="74" y2="72" gradientUnits="userSpaceOnUse">
        <stop stopColor="#FFFFFF" stopOpacity=".9"/>
        <stop offset="1" stopColor="#FFFFFF" stopOpacity=".2"/>
      </linearGradient>

      <filter id="sh" x="-20%" y="-20%" width="140%" height="160%" colorInterpolationFilters="sRGB">
        <feDropShadow dx="0" dy="6" stdDeviation="8" floodColor="#0B1220" floodOpacity=".35"/>
      </filter>

      <linearGradient id="gloss" x1="0" x2="1">
        <stop offset="0" stopColor="#FFFFFF" stopOpacity="0"/>
        <stop offset=".5" stopColor="#FFFFFF" stopOpacity=".45"/>
        <stop offset="1" stopColor="#FFFFFF" stopOpacity="0"/>
      </linearGradient>

      <clipPath id="slot-clip">
        <rect x="30" y="40" width="36" height="6" rx="3"/>
      </clipPath>
    </defs>

    {/* Grup utama: hue shift pelan */}
    <g style={{
        animation: "hueShift 14s linear infinite"
      }}>
      {/* Body printer */}
      <g filter="url(#sh)">
        <rect x="22" y="32" width="52" height="30" rx="10" fill="url(#gg)"/>
        <rect x="22" y="32" width="52" height="30" rx="10" fill="none" stroke="url(#ss)" strokeWidth="1.6"/> {/* Slot + shimmer */}
        <g clipPath="url(#slot-clip)">
          <rect x="30" y="40" width="36" height="6" rx="3" fill="#FFFFFF" fillOpacity=".85"/>
          <rect x="-24" y="40" width="24" height="6" rx="3" fill="url(#gloss)" style={{
              animation: "shimmer 5.5s linear infinite"
            }}/>
        </g>
      </g>

      {/* Kertas struk (breathe) */}
      <g filter="url(#sh)" style={{
          animation: "breathe 6s ease-in-out infinite"
        }}>
        <rect x="30" y="50" width="36" height="18" rx="4" fill="url(#gg)"/>
        <rect x="30" y="50" width="36" height="18" rx="4" fill="none" stroke="url(#ss)" strokeWidth="1.6"/>
        <rect x="34" y="54" width="20" height="2.6" rx="1.3" fill="white" opacity=".95"/>
        <rect x="34" y="59" width="16" height="2.6" rx="1.3" fill="white" opacity=".9"/>
        <rect x="56" y="54" width="1.5" height="8" fill="white"/>
        <rect x="58.2" y="54" width="2.2" height="8" fill="white" opacity=".9"/>
        <rect x="61.4" y="54" width="1.2" height="8" fill="white"/>
      </g>

      {/* Bayangan dasar */}
      <ellipse cx="48" cy="78" rx="20" ry="5" fill="#0B1220" fillOpacity=".16"/>
    </g>
  </svg>);
}
