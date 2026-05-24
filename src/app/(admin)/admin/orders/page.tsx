"use client";

import { useState, useEffect, useCallback } from "react";
import { formatCurrency, formatDate, getStatusColor } from "@/lib/utils";
import { toast } from "@/components/Toast";

interface Order {
  id: number;
  orderId: string;
  link: string;
  quantity: number;
  charge: number;
  startCount: number | null;
  remains: number | null;
  status: string;
  createdAt: string;
  user: { username: string; email: string };
  service: { name: string };
}

const statuses = ["all", "pending", "processing", "in-progress", "completed", "partial", "cancelled", "refunded"];

export default function AdminOrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [status, setStatus] = useState("all");
  const [page, setPage] = useState(1);
  const [pages, setPages] = useState(1);

  const loadOrders = useCallback(() => {
    fetch(`/api/admin/orders?status=${status}&page=${page}`)
      .then((r) => r.json())
      .then((data) => { setOrders(data.orders); setPages(data.pages); });
  }, [status, page]);

  useEffect(() => { loadOrders(); }, [loadOrders]);

  const updateStatus = async (orderId: number, newStatus: string) => {
    const res = await fetch("/api/admin/orders", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ orderId, status: newStatus }),
    });
    if (res.ok) {
      toast("success", "Order updated");
      loadOrders();
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Manage Orders</h1>

      <div className="flex flex-wrap gap-2">
        {statuses.map((s) => (
          <button
            key={s}
            onClick={() => { setStatus(s); setPage(1); }}
            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
              status === s ? "gradient-primary text-white" : "glass text-muted hover:text-foreground"
            }`}
          >
            {s.charAt(0).toUpperCase() + s.slice(1)}
          </button>
        ))}
      </div>

      <div className="glass rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-muted border-b border-border">
                <th className="px-4 py-3 font-medium">ID</th>
                <th className="px-4 py-3 font-medium">User</th>
                <th className="px-4 py-3 font-medium">Service</th>
                <th className="px-4 py-3 font-medium">Qty</th>
                <th className="px-4 py-3 font-medium">Charge</th>
                <th className="px-4 py-3 font-medium">Status</th>
                <th className="px-4 py-3 font-medium">Date</th>
                <th className="px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {orders.map((o) => (
                <tr key={o.id} className="hover:bg-surface-hover transition-colors">
                  <td className="px-4 py-3 font-mono text-xs">{o.orderId}</td>
                  <td className="px-4 py-3 text-xs">{o.user.username}</td>
                  <td className="px-4 py-3 max-w-[150px] truncate text-xs">{o.service.name}</td>
                  <td className="px-4 py-3">{o.quantity.toLocaleString()}</td>
                  <td className="px-4 py-3">{formatCurrency(o.charge)}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-0.5 rounded-lg text-xs font-medium border ${getStatusColor(o.status)}`}>{o.status}</span>
                  </td>
                  <td className="px-4 py-3 text-xs text-muted whitespace-nowrap">{formatDate(o.createdAt)}</td>
                  <td className="px-4 py-3">
                    <select
                      value={o.status}
                      onChange={(e) => updateStatus(o.id, e.target.value)}
                      className="px-2 py-1 rounded-lg bg-white/5 border border-border text-xs focus:outline-none"
                    >
                      {statuses.filter((s) => s !== "all").map((s) => (
                        <option key={s} value={s}>{s}</option>
                      ))}
                    </select>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {pages > 1 && (
          <div className="flex items-center justify-center gap-2 p-4 border-t border-border">
            {Array.from({ length: pages }, (_, i) => (
              <button
                key={i}
                onClick={() => setPage(i + 1)}
                className={`w-8 h-8 rounded-lg text-xs font-medium transition-colors ${
                  page === i + 1 ? "gradient-primary text-white" : "glass text-muted hover:text-foreground"
                }`}
              >
                {i + 1}
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
