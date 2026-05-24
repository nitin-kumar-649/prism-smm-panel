import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAdmin } from "@/lib/auth";

export async function GET() {
  try {
    await requireAdmin();
    const tickets = await prisma.ticket.findMany({
      include: {
        user: { select: { username: true, email: true } },
        messages: { orderBy: { createdAt: "asc" } },
      },
      orderBy: { updatedAt: "desc" },
    });
    return NextResponse.json(tickets);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  try {
    const admin = await requireAdmin();
    const { ticketId, message, status } = await request.json();

    if (message) {
      await prisma.ticketMessage.create({
        data: { ticketId, userId: admin.id, message, isAdmin: true },
      });
      await prisma.ticket.update({
        where: { id: ticketId },
        data: { status: "answered" },
      });
    }

    if (status) {
      await prisma.ticket.update({
        where: { id: ticketId },
        data: { status },
      });
    }

    return NextResponse.json({ success: true });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
