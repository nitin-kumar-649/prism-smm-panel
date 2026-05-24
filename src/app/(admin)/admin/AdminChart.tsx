"use client";

import { AreaChart, Area, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from "recharts";

interface AdminChartProps {
  data: Array<{ date: string; orders: number; revenue: number }>;
}

export default function AdminChart({ data }: AdminChartProps) {
  return (
    <div className="h-72">
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data} margin={{ top: 5, right: 5, left: 0, bottom: 5 }}>
          <defs>
            <linearGradient id="colorOrders" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3} />
              <stop offset="95%" stopColor="#6366f1" stopOpacity={0} />
            </linearGradient>
            <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#22d3ee" stopOpacity={0.3} />
              <stop offset="95%" stopColor="#22d3ee" stopOpacity={0} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
          <XAxis
            dataKey="date"
            stroke="rgba(255,255,255,0.3)"
            fontSize={11}
            tickFormatter={(v) => new Date(v).toLocaleDateString("en", { month: "short", day: "numeric" })}
          />
          <YAxis stroke="rgba(255,255,255,0.3)" fontSize={11} />
          <Tooltip
            contentStyle={{
              background: "rgba(10,10,26,0.95)",
              border: "1px solid rgba(255,255,255,0.1)",
              borderRadius: "12px",
              fontSize: "12px",
            }}
          />
          <Area type="monotone" dataKey="orders" stroke="#6366f1" fill="url(#colorOrders)" strokeWidth={2} name="Orders" />
          <Area type="monotone" dataKey="revenue" stroke="#22d3ee" fill="url(#colorRevenue)" strokeWidth={2} name="Revenue ($)" />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  );
}
