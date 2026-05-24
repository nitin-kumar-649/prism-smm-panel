"use client";

import { useEffect, useState, useCallback } from "react";
import { X, CheckCircle, AlertCircle, Info } from "lucide-react";
import { cn } from "@/lib/utils";

export interface ToastData {
  id: string;
  type: "success" | "error" | "info";
  message: string;
}

let addToastFn: ((toast: Omit<ToastData, "id">) => void) | null = null;

export function toast(type: ToastData["type"], message: string) {
  addToastFn?.({ type, message });
}

export default function ToastContainer() {
  const [toasts, setToasts] = useState<ToastData[]>([]);

  const addToast = useCallback((t: Omit<ToastData, "id">) => {
    const id = Math.random().toString(36).slice(2);
    setToasts((prev) => [...prev, { ...t, id }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((x) => x.id !== id));
    }, 4000);
  }, []);

  useEffect(() => {
    addToastFn = addToast;
    return () => { addToastFn = null; };
  }, [addToast]);

  const icons = { success: CheckCircle, error: AlertCircle, info: Info };
  const colors = {
    success: "border-emerald-500/30 bg-emerald-500/10",
    error: "border-red-500/30 bg-red-500/10",
    info: "border-blue-500/30 bg-blue-500/10",
  };

  return (
    <div className="fixed top-4 right-4 z-[100] space-y-2 max-w-sm">
      {toasts.map((t) => {
        const Icon = icons[t.type];
        return (
          <div
            key={t.id}
            className={cn("flex items-center gap-3 px-4 py-3 rounded-xl border backdrop-blur-xl shadow-2xl animate-in slide-in-from-right", colors[t.type])}
          >
            <Icon className="w-5 h-5 shrink-0" />
            <p className="text-sm flex-1">{t.message}</p>
            <button onClick={() => setToasts((prev) => prev.filter((x) => x.id !== t.id))}>
              <X className="w-4 h-4 text-muted" />
            </button>
          </div>
        );
      })}
    </div>
  );
}
