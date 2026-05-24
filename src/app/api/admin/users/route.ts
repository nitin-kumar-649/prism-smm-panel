import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAdmin } from "@/lib/auth";
import { generateTransactionId } from "@/lib/utils";

export async function GET() {
  try {
    await requireAdmin();
    const users = await prisma.user.findMany({
      select: {
        id: true,
        username: true,
        email: true,
        role: true,
        status: true,
        balance: true,
        totalSpent: true,
        createdAt: true,
        _count: { select: { orders: true } },
      },
      orderBy: { createdAt: "desc" },
    });
    return NextResponse.json(users);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  try {
    await requireAdmin();
    const { userId, action, amount } = await request.json();

    if (action === "addFunds" && amount > 0) {
      const user = await prisma.user.findUnique({ where: { id: userId } });
      if (!user) return NextResponse.json({ error: "User not found" }, { status: 404 });

      const newBalance = user.balance + amount;
      await prisma.$transaction([
        prisma.user.update({ where: { id: userId }, data: { balance: newBalance } }),
        prisma.transaction.create({
          data: {
            transactionId: generateTransactionId(),
            userId,
            type: "deposit",
            amount,
            balanceAfter: newBalance,
            description: "Admin added funds",
          },
        }),
      ]);
      return NextResponse.json({ balance: newBalance });
    }

    if (action === "suspend") {
      await prisma.user.update({ where: { id: userId }, data: { status: "suspended" } });
      return NextResponse.json({ success: true });
    }

    if (action === "activate") {
      await prisma.user.update({ where: { id: userId }, data: { status: "active" } });
      return NextResponse.json({ success: true });
    }

    return NextResponse.json({ error: "Invalid action" }, { status: 400 });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
