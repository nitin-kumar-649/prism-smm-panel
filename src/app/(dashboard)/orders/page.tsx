"use client";

import { useState, useEffect } from "react";
import { formatCurrency, formatDate, getStatusColor } from "@/lib/utils";
import { Search, Filter } from "lucide-react";

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
  service: { name: string };
}

export default function OrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");

  useEffect(() => {
    fetch("/api/orders")
      .then((r) => r.json())
      .then(setOrders);
  }, []);

  const filtered = orders.filter((o) => {
    const matchSearch = o.orderId.toLowerCase().includes(search.toLowerCase()) ||
      o.service.name.toLowerCase().includes(search.toLowerCase()) ||
      o.link.toLowerCase().includes(search.toLowerCase());
    const matchStatus = statusFilter === "all" || o.status === statusFilter;
    return matchSearch && matchStatus;
  });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Orders</h1>
        <p className="text-muted">Track all your orders</p>
      </div>

      <div className="flex flex-col sm:flex-row gap-3">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-border text-foreground placeholder:text-muted focus:outline-none focus:border-primary text-sm"
            placeholder="Search orders..."
          />
        </div>
        <div className="relative">
          <Filter className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" />
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="pl-10 pr-8 py-2.5 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary text-sm appearance-none"
          >
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="partial">Partial</option>
            <option value="cancelled">Cancelled</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>
      </div>

      <div className="glass rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-muted border-b border-border">
                <th className="px-4 py-3 font-medium">ID</th>
                <th className="px-4 py-3 font-medium">Service</th>
                <th className="px-4 py-3 font-medium">Link</th>
                <th className="px-4 py-3 font-medium">Qty</th>
                <th className="px-4 py-3 font-medium">Charge</th>
                <th className="px-4 py-3 font-medium">Start</th>
                <th className="px-4 py-3 font-medium">Remains</th>
                <th className="px-4 py-3 font-medium">Status</th>
                <th className="px-4 py-3 font-medium">Date</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={9} className="px-4 py-12 text-center text-muted">No orders found</td>
                </tr>
              ) : filtered.map((o) => (
                <tr key={o.id} className="hover:bg-surface-hover transition-colors">
                  <td className="px-4 py-3 font-mono text-xs">{o.orderId}</td>
                  <td className="px-4 py-3 max-w-[200px] truncate">{o.service.name}</td>
                  <td className="px-4 py-3 max-w-[150px] truncate text-xs text-muted">{o.link}</td>
                  <td className="px-4 py-3">{o.quantity.toLocaleString()}</td>
                  <td className="px-4 py-3">{formatCurrency(o.charge)}</td>
                  <td className="px-4 py-3">{o.startCount ?? "—"}</td>
                  <td className="px-4 py-3">{o.remains ?? "—"}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border ${getStatusColor(o.status)}`}>
                      {o.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-xs text-muted whitespace-nowrap">{formatDate(o.createdAt)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
