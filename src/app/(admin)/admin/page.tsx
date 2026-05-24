"use client";

import { useState, useEffect } from "react";
import { formatCurrency, formatDate } from "@/lib/utils";
import { Users, ShoppingCart, DollarSign, Clock } from "lucide-react";
import AdminChart from "./AdminChart";

interface Stats {
  totalUsers: number;
  totalOrders: number;
  totalRevenue: number;
  activeOrders: number;
  recentOrders: Array<{
    id: number;
    orderId: string;
    charge: number;
    status: string;
    createdAt: string;
    user: { username: string };
    service: { name: string };
  }>;
  chartData: Array<{ date: string; orders: number; revenue: number }>;
}

export default function AdminDashboard() {
  const [stats, setStats] = useState<Stats | null>(null);

  useEffect(() => {
    fetch("/api/admin/stats").then((r) => r.json()).then(setStats);
  }, []);

  if (!stats) return <div className="flex items-center justify-center h-64 text-muted">Loading...</div>;

  const cards = [
    { label: "Total Users", value: stats.totalUsers, icon: Users, color: "from-blue-500 to-blue-600" },
    { label: "Total Orders", value: stats.totalOrders, icon: ShoppingCart, color: "from-indigo-500 to-indigo-600" },
    { label: "Revenue", value: formatCurrency(stats.totalRevenue), icon: DollarSign, color: "from-emerald-500 to-emerald-600" },
    { label: "Active Orders", value: stats.activeOrders, icon: Clock, color: "from-amber-500 to-amber-600" },
  ];

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Admin Dashboard</h1>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {cards.map((c) => (
          <div key={c.label} className="glass rounded-2xl p-5 stat-card transition-all duration-300">
            <div className="flex items-center justify-between mb-3">
              <span className="text-sm text-muted">{c.label}</span>
              <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${c.color} flex items-center justify-center shadow-lg`}>
                <c.icon className="w-5 h-5 text-white" />
              </div>
            </div>
            <p className="text-2xl font-bold">{c.value}</p>
          </div>
        ))}
      </div>

      {stats.chartData.length > 0 && (
        <div className="glass rounded-2xl p-6">
          <h2 className="text-lg font-semibold mb-4">Orders & Revenue (30 days)</h2>
          <AdminChart data={stats.chartData} />
        </div>
      )}

      <div className="glass rounded-2xl p-6">
        <h2 className="text-lg font-semibold mb-4">Recent Orders</h2>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-muted border-b border-border">
                <th className="pb-3 font-medium">ID</th>
                <th className="pb-3 font-medium">User</th>
                <th className="pb-3 font-medium">Service</th>
                <th className="pb-3 font-medium">Charge</th>
                <th className="pb-3 font-medium">Status</th>
                <th className="pb-3 font-medium">Date</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {stats.recentOrders.map((o) => (
                <tr key={o.id} className="hover:bg-surface-hover transition-colors">
                  <td className="py-3 font-mono text-xs">{o.orderId}</td>
                  <td className="py-3">{o.user.username}</td>
                  <td className="py-3 max-w-[200px] truncate">{o.service.name}</td>
                  <td className="py-3">{formatCurrency(o.charge)}</td>
                  <td className="py-3">
                    <span className="px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                      {o.status}
                    </span>
                  </td>
                  <td className="py-3 text-xs text-muted">{formatDate(o.createdAt)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
