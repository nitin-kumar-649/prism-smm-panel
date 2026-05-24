import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAuth } from "@/lib/auth";
import { generateTransactionId } from "@/lib/utils";

export async function GET() {
  try {
    const user = await requireAuth();
    const transactions = await prisma.transaction.findMany({
      where: { userId: user.id },
      orderBy: { createdAt: "desc" },
      take: 50,
    });
    const currentUser = await prisma.user.findUnique({
      where: { id: user.id },
      select: { balance: true },
    });
    return NextResponse.json({ balance: currentUser?.balance || 0, transactions });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const user = await requireAuth();
    const { amount, gateway } = await request.json();

    if (!amount || amount < 5) {
      return NextResponse.json({ error: "Minimum deposit is $5" }, { status: 400 });
    }

    const currentUser = await prisma.user.findUnique({ where: { id: user.id } });
    if (!currentUser) return NextResponse.json({ error: "User not found" }, { status: 404 });

    const transactionId = generateTransactionId();
    const newBalance = currentUser.balance + amount;

    await prisma.$transaction([
      prisma.user.update({
        where: { id: user.id },
        data: { balance: newBalance },
      }),
      prisma.transaction.create({
        data: {
          transactionId,
          userId: user.id,
          type: "deposit",
          amount,
          balanceAfter: newBalance,
          description: `Deposit via ${gateway || "Manual"}`,
        },
      }),
    ]);

    return NextResponse.json({ balance: newBalance, transactionId });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
