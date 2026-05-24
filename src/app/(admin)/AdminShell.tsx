"use client";

import { useState } from "react";
import AdminSidebar from "@/components/AdminSidebar";
import Header from "@/components/Header";

interface AdminShellProps {
  user: { username: string; email: string; role: string; balance: number };
  children: React.ReactNode;
}

export default function AdminShell({ user, children }: AdminShellProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="min-h-screen bg-[#08081a]">
      <AdminSidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      <div className="lg:ml-64">
        <Header user={user} onMenuClick={() => setSidebarOpen(true)} />
        <main className="p-4 lg:p-6">{children}</main>
      </div>
    </div>
  );
}
