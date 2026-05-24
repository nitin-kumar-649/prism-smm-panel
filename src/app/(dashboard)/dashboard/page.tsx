import { redirect } from "next/navigation";
import { getCurrentUser } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { formatCurrency, formatDate, getStatusColor } from "@/lib/utils";
import { ShoppingCart, DollarSign, Clock, TrendingUp } from "lucide-react";

export default async function DashboardPage() {
  const user = await getCurrentUser();
  if (!user) redirect("/login");

  const [orderCount, totalSpent, pendingOrders, recentOrders] = await Promise.all([
    prisma.order.count({ where: { userId: user.id } }),
    prisma.order.aggregate({ where: { userId: user.id }, _sum: { charge: true } }),
    prisma.order.count({ where: { userId: user.id, status: { in: ["pending", "processing"] } } }),
    prisma.order.findMany({
      where: { userId: user.id },
      include: { service: { select: { name: true } } },
      orderBy: { createdAt: "desc" },
      take: 5,
    }),
  ]);

  const stats = [
    { label: "Balance", value: formatCurrency(user.balance), icon: DollarSign, color: "from-emerald-500 to-emerald-600" },
    { label: "Total Orders", value: orderCount.toString(), icon: ShoppingCart, color: "from-indigo-500 to-indigo-600" },
    { label: "Total Spent", value: formatCurrency(totalSpent._sum.charge || 0), icon: TrendingUp, color: "from-purple-500 to-purple-600" },
    { label: "Pending", value: pendingOrders.toString(), icon: Clock, color: "from-amber-500 to-amber-600" },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Dashboard</h1>
        <p className="text-muted">Welcome back, {user.username}!</p>
      </div>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {stats.map((stat) => (
          <div key={stat.label} className="glass rounded-2xl p-5 stat-card transition-all duration-300">
            <div className="flex items-center justify-between mb-3">
              <span className="text-sm text-muted">{stat.label}</span>
              <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${stat.color} flex items-center justify-center shadow-lg`}>
                <stat.icon className="w-5 h-5 text-white" />
              </div>
            </div>
            <p className="text-2xl font-bold">{stat.value}</p>
          </div>
        ))}
      </div>

      <div className="glass rounded-2xl p-6">
        <h2 className="text-lg font-semibold mb-4">Recent Orders</h2>
        {recentOrders.length === 0 ? (
          <p className="text-muted text-sm py-8 text-center">No orders yet. Place your first order!</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-left text-muted border-b border-border">
                  <th className="pb-3 font-medium">Order ID</th>
                  <th className="pb-3 font-medium">Service</th>
                  <th className="pb-3 font-medium">Quantity</th>
                  <th className="pb-3 font-medium">Charge</th>
                  <th className="pb-3 font-medium">Status</th>
                  <th className="pb-3 font-medium">Date</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {recentOrders.map((order) => (
                  <tr key={order.id} className="hover:bg-surface-hover transition-colors">
                    <td className="py-3 font-mono text-xs">{order.orderId}</td>
                    <td className="py-3 max-w-[200px] truncate">{order.service.name}</td>
                    <td className="py-3">{order.quantity.toLocaleString()}</td>
                    <td className="py-3">{formatCurrency(order.charge)}</td>
                    <td className="py-3">
                      <span className={`inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border ${getStatusColor(order.status)}`}>
                        {order.status}
                      </span>
                    </td>
                    <td className="py-3 text-muted text-xs">{formatDate(order.createdAt)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
