"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Bell, Menu, LogOut, Shield } from "lucide-react";
import { cn } from "@/lib/utils";
import { formatCurrency } from "@/lib/utils";

interface HeaderProps {
  user: { username: string; email: string; role: string; balance: number };
  onMenuClick: () => void;
}

export default function Header({ user, onMenuClick }: HeaderProps) {
  const router = useRouter();
  const [showDropdown, setShowDropdown] = useState(false);

  const handleLogout = async () => {
    await fetch("/api/auth/logout", { method: "POST" });
    router.push("/login");
    router.refresh();
  };

  return (
    <header className="sticky top-0 z-30 glass-strong">
      <div className="flex items-center justify-between px-4 py-3 lg:px-6">
        <button onClick={onMenuClick} className="lg:hidden text-muted hover:text-foreground">
          <Menu className="w-6 h-6" />
        </button>

        <div className="hidden lg:block" />

        <div className="flex items-center gap-3">
          <div className="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
            <span className="text-xs text-emerald-400">Balance:</span>
            <span className="text-sm font-semibold text-emerald-400">{formatCurrency(user.balance)}</span>
          </div>

          <button className="relative p-2 rounded-xl hover:bg-surface-hover text-muted hover:text-foreground transition-colors">
            <Bell className="w-5 h-5" />
          </button>

          <div className="relative">
            <button
              onClick={() => setShowDropdown(!showDropdown)}
              className="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-surface-hover transition-colors"
            >
              <div className="w-8 h-8 rounded-full gradient-primary flex items-center justify-center text-white text-sm font-semibold">
                {user.username[0].toUpperCase()}
              </div>
              <span className="hidden sm:block text-sm font-medium">{user.username}</span>
            </button>

            {showDropdown && (
              <div className="absolute right-0 top-full mt-2 w-48 glass-strong rounded-xl py-2 shadow-2xl">
                <div className="px-3 py-2 border-b border-border">
                  <p className="text-sm font-medium">{user.username}</p>
                  <p className="text-xs text-muted">{user.email}</p>
                </div>
                {user.role === "admin" && (
                  <button
                    onClick={() => { setShowDropdown(false); router.push("/admin"); }}
                    className="w-full flex items-center gap-2 px-3 py-2 text-sm text-muted hover:text-foreground hover:bg-surface-hover transition-colors"
                  >
                    <Shield className="w-4 h-4" /> Admin Panel
                  </button>
                )}
                <button
                  onClick={handleLogout}
                  className={cn(
                    "w-full flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors"
                  )}
                >
                  <LogOut className="w-4 h-4" /> Logout
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  );
}
