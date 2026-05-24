"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import {
  LayoutDashboard, ShoppingCart, PlusCircle, Wallet, MessageSquare,
  Code, User, X, ChevronLeft, Zap
} from "lucide-react";

const userNav = [
  { href: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { href: "/new-order", label: "New Order", icon: PlusCircle },
  { href: "/orders", label: "Orders", icon: ShoppingCart },
  { href: "/wallet", label: "Wallet", icon: Wallet },
  { href: "/tickets", label: "Tickets", icon: MessageSquare },
  { href: "/api-docs", label: "API", icon: Code },
  { href: "/profile", label: "Profile", icon: User },
];

interface SidebarProps {
  open: boolean;
  onClose: () => void;
}

export default function Sidebar({ open, onClose }: SidebarProps) {
  const pathname = usePathname();

  return (
    <>
      {open && (
        <div className="fixed inset-0 bg-black/50 z-40 lg:hidden" onClick={onClose} />
      )}
      <aside
        className={cn(
          "fixed top-0 left-0 z-50 h-full w-64 glass-strong flex flex-col transition-transform duration-300 lg:translate-x-0",
          open ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <div className="flex items-center justify-between p-5 border-b border-border">
          <Link href="/dashboard" className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg gradient-primary flex items-center justify-center">
              <Zap className="w-5 h-5 text-white" />
            </div>
            <span className="text-lg font-bold bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-transparent">
              Prism SMM
            </span>
          </Link>
          <button onClick={onClose} className="lg:hidden text-muted hover:text-foreground">
            <X className="w-5 h-5" />
          </button>
        </div>

        <nav className="flex-1 p-3 space-y-1 overflow-y-auto">
          {userNav.map((item) => {
            const isActive = pathname === item.href;
            return (
              <Link
                key={item.href}
                href={item.href}
                onClick={onClose}
                className={cn(
                  "flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200",
                  isActive
                    ? "gradient-primary text-white shadow-lg shadow-indigo-500/25"
                    : "text-muted hover:text-foreground hover:bg-surface-hover"
                )}
              >
                <item.icon className="w-4.5 h-4.5" />
                {item.label}
              </Link>
            );
          })}
        </nav>

        <div className="p-3 border-t border-border">
          <button
            onClick={onClose}
            className="hidden lg:flex items-center gap-2 text-xs text-muted hover:text-foreground w-full px-3 py-2"
          >
            <ChevronLeft className="w-4 h-4" />
            Collapse
          </button>
        </div>
      </aside>
    </>
  );
}
