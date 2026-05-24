import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAdmin } from "@/lib/auth";

export async function GET() {
  try {
    await requireAdmin();

    const [totalUsers, totalOrders, totalRevenue, activeOrders, recentOrders, ordersByDay] = await Promise.all([
      prisma.user.count(),
      prisma.order.count(),
      prisma.order.aggregate({ _sum: { charge: true } }),
      prisma.order.count({ where: { status: { in: ["pending", "processing", "in-progress"] } } }),
      prisma.order.findMany({
        take: 10,
        orderBy: { createdAt: "desc" },
        include: { user: { select: { username: true } }, service: { select: { name: true } } },
      }),
      prisma.$queryRawUnsafe<Array<{ date: string; count: bigint; revenue: number }>>(
        `SELECT date(created_at) as date, COUNT(*) as count, COALESCE(SUM(charge), 0) as revenue
         FROM orders WHERE created_at >= date('now', '-30 days')
         GROUP BY date(created_at) ORDER BY date ASC`
      ),
    ]);

    const chartData = ordersByDay.map((d) => ({
      date: d.date,
      orders: Number(d.count),
      revenue: Number(d.revenue),
    }));

    return NextResponse.json({
      totalUsers,
      totalOrders,
      totalRevenue: totalRevenue._sum.charge || 0,
      activeOrders,
      recentOrders,
      chartData,
    });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
