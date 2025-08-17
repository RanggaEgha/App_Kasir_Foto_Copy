import {Link} from "@inertiajs/react";
import BrandLogo from "@/Components/BrandLogo";
import BlurEffect from "react-progressive-blur";

export default function GuestLayout({children}) {
  return (<div className="relative min-h-screen overflow-hidden">
    {/* ==== KEYFRAMES ==== */}
    <style>
      {
        ` @keyframes float-a {
          0% {
            transform: translate(-10%,0) scale(1);
          }
          100% {
            transform: translate(10%,-6%) scale(1.15);
          }
        }
        @keyframes float-b {
          0% {
            transform: translate(20%,10%) scale(.9);
          }
          100% {
            transform: translate(-5%,20%) scale(1.05);
          }
        }
        @keyframes float-c {
          0% {
            transform: translate(0,10%) scale(1.1);
          }
          100% {
            transform: translate(0,-10%) scale(.95);
          }
        }
        @keyframes hue {
          0% {
            filter:hue-rotate(0deg) saturate(1)
          }
          50% {
            filter:hue-rotate(180deg) saturate(1.08)
          }
          100% {
            filter:hue-rotate(360deg) saturate(1)
          }
        }
        @keyframes shimmer {
          0% {
            transform: translateX(-30%);
          }
          100% {
            transform: translateX(130%);
          }
        }
        @keyframes textShine {
          0% {
            background-position: 0 50%;
          }
          100% {
            background-position: 200% 50%;
          }
        }
        @keyframes subPulse {
          0%,
          100% {
            opacity: 0.70;
          }
          50% {
            opacity: 0.90;
          }
        }
         `
      }</style>
    {/* Base gradient */}
    <div className="absolute inset-0 -z-30 bg-gradient-to-br from-slate-50 via-sky-50 to-blue-100
                      dark:from-slate-950 dark:via-slate-900 dark:to-slate-900"/>{" "}
    {/* Aurora layer */}
    <div className="pointer-events-none absolute inset-0 -z-20 overflow-hidden">
      <div className="absolute left-1/2 top-1/2 h...[140vmax] w-[140vmax]
                        -translate-x-1/2 -translate-y-1/2 rounded-full opacity-25 blur-3xl mix-blend-overlay
                        motion-reduce:animate-none animate-[spin_90s_linear_infinite]
                        bg-[conic-gradient(from_0deg_at_50%_50%,rgba(56,189,248,0.18),rgba(124,58,237,0.16),rgba(14,165,233,0.18),rgba(56,189,248,0.18))]"/>
    </div>
    {/* Liquid blobs bergerak + hue cycling */}
    <div className="pointer-events-none absolute inset-0 -z-10 motion-reduce:animate-none animate-[hue_60s_linear_infinite]">
      <div className="absolute -top-24 -left-24 h-[38rem] w-[38rem] rounded-full
                        bg-[radial-gradient(circle_at_30%_30%,rgba(59,130,246,0.55),rgba(99,102,241,0.35)_40%,transparent_60%)]
                        blur-3xl opacity-80 animate-[float-a_20s_ease-in-out_infinite_alternate]"/>
      <div className="absolute top-1/2 -right-28 h-[34rem] w-[34rem] rounded-full
                        bg-[radial-gradient(circle_at_60%_40%,rgba(14,165,233,0.45),rgba(99,102,241,0.35)_45%,transparent_65%)]
                        blur-3xl opacity-70 animate-[float-b_24s_ease-in-out_infinite_alternate]"/>
      <div className="absolute -bottom-24 left-1/3 h-[30rem] w-[30rem] rounded-full
                        bg-[radial-gradient(circle_at_50%_50%,rgba(56,189,248,0.35),rgba(14,165,233,0.25)_50%,transparent_70%)]
                        blur-3xl opacity-70 animate-[float-c_28s_ease-in-out_infinite_alternate]"/>
    </div>
    {/* === Progressive Blur Edges ===
          z-0 sehingga berada di bawah kartu (z-10) tapi di atas background (-z-10 dst) */
    }
    <div className="pointer-events-none absolute inset-0 z-0">
      {/* Atas & bawah: haluskan pertemuan background dengan konten */}
      <BlurEffect position="top" intensity={48} className="h-28"/>
      <BlurEffect position="bottom" intensity={36} className="h-24"/>{" "}
      {/* Kiri/kanan: aktifkan hanya di md+ agar tidak makan ruang pada layar kecil */}
      <div className="hidden md:block h-full">
        <BlurEffect position="left" intensity={28} className="h-full w-16"/>
        <BlurEffect position="right" intensity={28} className="h-full w-16"/>
      </div>
    </div>
    {/* Centered card */}
    <div className="relative z-10 mx-auto flex min-h-screen max-w-7xl items-center justify-center p-6">
      <div className="w-full max-w-md">
        {/* === Logo + Wordmark (sejajar) === */}
        <Link href="/" className="mb-2 flex justify-center select-none">
          <div className="inline-flex items-center gap-1">
            {/* Ikon animated */}
            <BrandLogo className="h-[90px] w-[90px] shrink-0 drop-shadow"/>{" "}
            {/* Teks + animasi */}
            <div className="leading-none">
              <div className="font-semibold tracking-wide bg-clip-text text-transparent drop-shadow
                             text-[clamp(26px,2.3vw,32px)]" style={{
                  backgroundImage: "linear-gradient(90deg, #38bdf8, #22d3ee, #8b5cf6, #38bdf8)",
                  backgroundSize: "200% 100%",
                  animation: "textShine 12s linear infinite"
                }}>
                Foto Copy App
              </div>
              <div className="text-[10px] md:text-[11px] uppercase tracking-[0.22em]
                             text-slate-600/80 dark:text-white/65" style={{
                  animation: "subPulse 6s ease-in-out infinite"
                }}>
                Melayani lebih cepat, mengelola lebih mudah.
              </div>
            </div>
          </div>
        </Link>

        {/* Liquid Glass card */}
        <div className="relative overflow-hidden rounded-3xl
                       bg-white/20 dark:bg-slate-900/30 backdrop-blur-2xl
                       border border-white/35 dark:border-white/10
                       shadow-[0_10px_40px_-5px_rgba(2,6,23,0.25)]">
          {/* glossy highlight */}
          <div className="pointer-events-none absolute -top-24 -left-24 h-64 w-64 rounded-full bg-white/30 blur-2xl"/>{" "}
          {/* inner gradient + shimmer */}
          <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.25),transparent_40%)]"/>
          <div className="pointer-events-none absolute inset-y-0 -left-1/3 w-1/3 skew-x-12
                            bg-white/10 blur-md animate-[shimmer_6s_linear_infinite]"/>
          <div className="relative p-6">{children}</div>
        </div>
      </div>
    </div>
  </div>);
}
