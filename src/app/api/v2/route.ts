import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { generateOrderId, generateTransactionId } from "@/lib/utils";

async function authenticateApiKey(request: Request) {
  const body = await request.json();
  const key = body.key;
  if (!key) return { error: "API key required", body };
  const user = await prisma.user.findUnique({ where: { apiKey: key } });
  if (!user || user.status !== "active") return { error: "Invalid API key", body };
  return { user, body };
}

export async function POST(request: Request) {
  try {
    const { user, body, error } = await authenticateApiKey(request);
    if (error || !user) {
      return NextResponse.json({ error: error || "Invalid key" }, { status: 401 });
    }

    const { action } = body;

    if (action === "services") {
      const services = await prisma.service.findMany({
        where: { status: "active" },
        include: { category: { select: { name: true } } },
      });
      return NextResponse.json(services.map((s) => ({
        service: s.id,
        name: s.name,
        category: s.category.name,
        type: s.type,
        rate: s.pricePer1000,
        min: s.minQuantity,
        max: s.maxQuantity,
        dripfeed: s.dripFeed,
        refill: s.refill,
        cancel: s.cancel,
      })));
    }

    if (action === "add") {
      const { service: serviceId, link, quantity } = body;
      const service = await prisma.service.findUnique({ where: { id: serviceId } });
      if (!service) return NextResponse.json({ error: "Service not found" }, { status: 404 });

      const charge = (service.pricePer1000 / 1000) * quantity;
      if (user.balance < charge) {
        return NextResponse.json({ error: "Insufficient balance" }, { status: 400 });
      }

      const orderId = generateOrderId();
      const newBalance = user.balance - charge;

      const [order] = await prisma.$transaction([
        prisma.order.create({
          data: { orderId, userId: user.id, serviceId, link, quantity, charge, status: "pending", refill: service.refill, cancel: service.cancel },
        }),
        prisma.user.update({ where: { id: user.id }, data: { balance: newBalance, totalSpent: user.totalSpent + charge } }),
        prisma.transaction.create({
          data: { transactionId: generateTransactionId(), userId: user.id, type: "charge", amount: -charge, balanceAfter: newBalance, description: `API Order ${orderId}` },
        }),
      ]);

      return NextResponse.json({ order: order.id });
    }

    if (action === "status") {
      const { order: orderId } = body;
      const order = await prisma.order.findFirst({
        where: { id: orderId, userId: user.id },
      });
      if (!order) return NextResponse.json({ error: "Order not found" }, { status: 404 });
      return NextResponse.json({
        charge: order.charge,
        start_count: order.startCount,
        status: order.status,
        remains: order.remains,
        currency: "USD",
      });
    }

    if (action === "balance") {
      return NextResponse.json({ balance: user.balance, currency: "USD" });
    }

    return NextResponse.json({ error: "Invalid action" }, { status: 400 });
  } catch {
    return NextResponse.json({ error: "API error" }, { status: 500 });
  }
}
