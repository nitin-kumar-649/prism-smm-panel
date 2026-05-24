import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAuth } from "@/lib/auth";
import { generateOrderId, generateTransactionId } from "@/lib/utils";

export async function GET() {
  try {
    const user = await requireAuth();
    const orders = await prisma.order.findMany({
      where: { userId: user.id },
      include: { service: { select: { name: true } } },
      orderBy: { createdAt: "desc" },
    });
    return NextResponse.json(orders);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const user = await requireAuth();
    const { serviceId, link, quantity, dripFeed, dripFeedInterval, dripFeedQuantity, dripFeedRuns } = await request.json();

    if (!serviceId || !link || !quantity) {
      return NextResponse.json({ error: "Service, link, and quantity required" }, { status: 400 });
    }

    const service = await prisma.service.findUnique({ where: { id: serviceId } });
    if (!service || service.status !== "active") {
      return NextResponse.json({ error: "Service not found" }, { status: 404 });
    }

    if (quantity < service.minQuantity || quantity > service.maxQuantity) {
      return NextResponse.json({ error: `Quantity must be between ${service.minQuantity} and ${service.maxQuantity}` }, { status: 400 });
    }

    const charge = (service.pricePer1000 / 1000) * quantity;
    const currentUser = await prisma.user.findUnique({ where: { id: user.id } });
    if (!currentUser || currentUser.balance < charge) {
      return NextResponse.json({ error: "Insufficient balance" }, { status: 400 });
    }

    const orderId = generateOrderId();
    const transactionId = generateTransactionId();
    const newBalance = currentUser.balance - charge;

    const [order] = await prisma.$transaction([
      prisma.order.create({
        data: {
          orderId,
          userId: user.id,
          serviceId,
          link,
          quantity,
          charge,
          status: "pending",
          dripFeed: dripFeed || false,
          dripFeedInterval: dripFeedInterval || null,
          dripFeedQuantity: dripFeedQuantity || null,
          dripFeedRuns: dripFeedRuns || null,
          refill: service.refill,
          cancel: service.cancel,
        },
      }),
      prisma.user.update({
        where: { id: user.id },
        data: { balance: newBalance, totalSpent: currentUser.totalSpent + charge },
      }),
      prisma.transaction.create({
        data: {
          transactionId,
          userId: user.id,
          type: "charge",
          amount: -charge,
          balanceAfter: newBalance,
          description: `Order ${orderId} - ${service.name}`,
        },
      }),
    ]);

    return NextResponse.json(order, { status: 201 });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
