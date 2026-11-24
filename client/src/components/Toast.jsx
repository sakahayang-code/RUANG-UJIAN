import React from 'react';
import { useApp } from '../context/AppContext';
import { CheckCircle2, AlertTriangle, AlertCircle, Info, X } from 'lucide-react';

export default function Toast() {
  const { toast } = useApp();

  if (!toast) return null;

  const icons = {
    success: <CheckCircle2 className="w-5 h-5 text-emerald-500 shrink-0" />,
    error: <AlertCircle className="w-5 h-5 text-rose-500 shrink-0" />,
    warning: <AlertTriangle className="w-5 h-5 text-amber-500 shrink-0" />,
    info: <Info className="w-5 h-5 text-blue-500 shrink-0" />
  };

  const bgColors = {
    success: 'bg-white border-emerald-200 text-slate-800 shadow-emerald-500/10',
    error: 'bg-white border-rose-200 text-slate-800 shadow-rose-500/10',
    warning: 'bg-white border-amber-200 text-slate-800 shadow-amber-500/10',
    info: 'bg-white border-blue-200 text-slate-800 shadow-blue-500/10'
  };

  return (
    <div className="fixed bottom-20 md:bottom-6 right-6 z-50 animate-bounce-in max-w-sm w-full no-print">
      <div className={`flex items-center gap-3 p-4 rounded-xl border shadow-xl ${bgColors[toast.type] || bgColors.info} transition-all`}>
        {icons[toast.type] || icons.info}
        <p className="text-sm font-medium flex-1 leading-snug">{toast.message}</p>
      </div>
    </div>
  );
}
