import {useEffect, useState} from "react";
import Checkbox from "@/Components/Checkbox";
import GuestLayout from "@/Layouts/GuestLayout";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import {Head, Link, useForm} from "@inertiajs/react";

export default function Login({
  status,
  canResetPassword = false
}) {
  const {
    data,
    setData,
    post,
    processing,
    errors,
    reset
  } = useForm({email: "", password: "", remember: false});
  const [showPassword, setShowPassword] = useState(false);

  useEffect(() => () => reset("password"), []);
  const submit = e => {
    e.preventDefault();
    post("/login", {
      replace: true,
      onFinish: () => reset("password")
    });
  };

  return (<GuestLayout>
    <Head title="Masuk"/> {/* Keyframes untuk tombol (khusus halaman ini) */}
    <style>
      {
        ` @keyframes btnShine {
          0% {
            transform: translateX(-120%);
          }
          100% {
            transform: translateX(120%);
          }
        }
        @keyframes btnPulse {
          0%,
          100% {
            box-shadow: 0 8px 24px rgba(2,132,199,0.25);
          }
          50% {
            box-shadow: 0 10px 28px rgba(2,132,199,0.38);
          }
        }
         `
      }</style>

    {/* Header form */}
    <div className="mb-5">
      <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">
        Masuk
      </h1>
      <p className="text-sm text-slate-600 dark:text-slate-400">
        Silakan autentikasi.
      </p>
    </div>

    {
      status && (<div className="mb-4 rounded-xl border border-emerald-300/50 bg-emerald-50/70 px-3 py-2 text-sm text-emerald-700
                        dark:border-emerald-400/30 dark:bg-emerald-900/30 dark:text-emerald-200">
        {status}
      </div>)
    }

    <form method="post" action="/login" onSubmit={submit} className="space-y-4">
      <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") ?? ""
}/> {/* Email */}
      <div>
        <InputLabel htmlFor="email" value="Email"/>
        <div className="relative mt-1">
          <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg viewBox="0 0 24 24" className="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" strokeWidth="1.6">
              <path d="M4 4h16v16H4z"/>
              <path d="m22 6-10 7L2 6"/>
            </svg>
          </div>
          <TextInput id="email" type="email" name="email" value={data.email} className="pl-10 pr-3 h-11 block w-full rounded-2xl
                         bg-white/60 dark:bg-slate-800/50
                         border-white/40 dark:border-white/10
                         placeholder-slate-500/80
                         focus:border-sky-400 focus:ring-sky-400/40
                         text-slate-900 dark:text-slate-100" autoComplete="username" isFocused={true} onChange={e => setData("email", e.target.value)} disabled={processing}/>
        </div>
        <InputError message={errors.email} className="mt-2"/>
      </div>

      {/* Password */}
      <div>
        <InputLabel htmlFor="password" value="Password"/>
        <div className="relative mt-1">
          <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg viewBox="0 0 24 24" className="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" strokeWidth="1.6">
              <rect x="3" y="11" width="18" height="10" rx="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>

          <button type="button" onClick={() => setShowPassword(v => !v)} className="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" aria-label={showPassword
              ? "Sembunyikan password"
              : "Tampilkan password"
} tabIndex={-1}>
            {
              showPassword
                ? (<svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.6">
                  <path d="M1 1l22 22"/>
                  <path d="M17.94 17.94C16.23 18.96 14.21 19.6 12 19.6 6 19.6 2 12 2 12a20.1 20.1 0 0 1 5.06-6.06m4.26-1.53A9.9 9.9 0 0 1 12 4.4C18 4.4 22 12 22 12a20.5 20.5 0 0 1-2.84 3.94"/>
                </svg>)
                : (<svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.6">
                  <path d="M2 12s4-7.6 10-7.6S22 12 22 12s-4 7.6-10 7.6S2 12 2 12Z"/>
                  <circle cx="12" cy="12" r="3.2"/>
                </svg>)
            }
          </button>

          <TextInput id="password" type={showPassword
              ? "text"
              : "password"} name="password" value={data.password} className="pl-10 pr-10 h-11 block w-full rounded-2xl
                         bg-white/60 dark:bg-slate-800/50
                         border-white/40 dark:border-white/10
                         placeholder-slate-500/80
                         focus:border-sky-400 focus:ring-sky-400/40
                         text-slate-900 dark:text-slate-100" autoComplete="current-password" onChange={e => setData("password", e.target.value)} disabled={processing}/>
        </div>
        <InputError message={errors.password} className="mt-2"/>
      </div>

      {/* Remember + Forgot */}
      <div className="flex items-center justify-between pt-1">
        <label className="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
          <Checkbox name="remember" checked={data.remember} onChange={e => setData("remember", e.target.checked)} disabled={processing}/>
          Ingat saya
        </label>

        {
          canResetPassword && (<Link href="/forgot-password" className="text-sm text-sky-700 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-300 underline-offset-4 hover:underline">
            Lupa password?
          </Link>)
        }
      </div>

      {/* Tombol MASUK (liquid shine + pulse) */}
      <PrimaryButton className="relative overflow-hidden group
                     mt-1 w-full justify-center rounded-2xl px-4 py-3
                     text-base font-semibold normal-case
                     bg-sky-600 hover:bg-sky-700 focus:ring-sky-400/50
                     text-white shadow-md transition active:scale-[0.99]" disabled={processing} type="submit">
        <span className="relative z-10">
          {
            processing
              ? "Memproses..."
              : "Masuk"
          }
        </span>
        {/* shine strip */}
        <span aria-hidden="true" className="pointer-events-none absolute inset-y-0 left-0 w-1/3
                       bg-gradient-to-r from-transparent via-white/40 to-transparent
                       opacity-70 group-hover:opacity-100" style={{
            animation: "btnShine 2.6s ease-in-out infinite"
          }}/>
      </PrimaryButton>
    </form>
  </GuestLayout>);
}
