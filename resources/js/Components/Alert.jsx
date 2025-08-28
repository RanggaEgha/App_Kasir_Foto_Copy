export default function Alert({
  variant = 'info',
  title,
  messages,
  className = '',
  children,
  ...props
}) {
  const v = {
    info: {
      border: 'border-sky-300/40 dark:border-sky-300/20',
      iconBg: 'bg-sky-500/10',
      iconColor: 'text-sky-600 dark:text-sky-300',
      title: 'text-slate-900 dark:text-slate-100',
      body: 'text-slate-700 dark:text-slate-200',
    },
    success: {
      border: 'border-emerald-300/40 dark:border-emerald-300/20',
      iconBg: 'bg-emerald-500/10',
      iconColor: 'text-emerald-600 dark:text-emerald-300',
      title: 'text-slate-900 dark:text-slate-100',
      body: 'text-slate-700 dark:text-slate-200',
    },
    warning: {
      border: 'border-amber-300/40 dark:border-amber-300/20',
      iconBg: 'bg-amber-500/10',
      iconColor: 'text-amber-600 dark:text-amber-300',
      title: 'text-slate-900 dark:text-slate-100',
      body: 'text-slate-700 dark:text-slate-200',
    },
    danger: {
      border: 'border-rose-300/45 dark:border-rose-300/25',
      iconBg: 'bg-rose-500/10',
      iconColor: 'text-rose-600 dark:text-rose-300',
      title: 'text-slate-900 dark:text-slate-100',
      body: 'text-slate-700 dark:text-slate-200',
    },
  }[variant] || {};

  const renderIcon = () => {
    switch (variant) {
      case 'success':
        return (
          <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">
            <path d="M20 6 9 17l-5-5" />
          </svg>
        );
      case 'warning':
        return (
          <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
            <path d="m10.29 3.86-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.71-3.14l-8-14a2 2 0 0 0-3.42 0Z" />
          </svg>
        );
      case 'danger':
        return (
          <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
            <circle cx="12" cy="12" r="9" />
          </svg>
        );
      default:
        return (
          <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v4m0 4h.01" />
          </svg>
        );
    }
  };

  const list = Array.isArray(messages) ? messages : (messages ? [messages] : []);

  return (
    <div
      role="alert"
      aria-live="assertive"
      className={
        `relative overflow-hidden ${className} ` +
        `rounded-2xl border ${v.border} bg-white/20 dark:bg-slate-900/30 backdrop-blur-xl shadow-[0_6px_24px_-6px_rgba(2,6,23,0.25)]`
      }
      {...props}
    >
      <div className="flex items-start gap-3 px-4 py-3">
        <div className={`mt-0.5 flex h-8 w-8 items-center justify-center rounded-full ${v.iconBg} ${v.iconColor}`}>
          {renderIcon()}
        </div>
        <div className="min-w-0">
          {title && <div className={`font-medium leading-6 ${v.title}`}>{title}</div>}
          {children}
          {list.length > 0 && (
            <ul className={`mt-1 space-y-0.5 text-sm ${v.body}`}>
              {list.map((m, i) => (
                <li key={i} className="list-disc list-inside marker:text-slate-400/80 dark:marker:text-slate-400/60">{m}</li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </div>
  );
}
