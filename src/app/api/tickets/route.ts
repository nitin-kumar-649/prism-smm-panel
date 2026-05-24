import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAuth } from "@/lib/auth";
import { generateTicketId } from "@/lib/utils";

export async function GET() {
  try {
    const user = await requireAuth();
    const tickets = await prisma.ticket.findMany({
      where: { userId: user.id },
      include: { messages: { orderBy: { createdAt: "asc" } } },
      orderBy: { updatedAt: "desc" },
    });
    return NextResponse.json(tickets);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const user = await requireAuth();
    const { subject, message, priority } = await request.json();

    if (!subject || !message) {
      return NextResponse.json({ error: "Subject and message required" }, { status: 400 });
    }

    const ticket = await prisma.ticket.create({
      data: {
        ticketId: generateTicketId(),
        userId: user.id,
        subject,
        priority: priority || "medium",
        messages: {
          create: { userId: user.id, message, isAdmin: false },
        },
      },
      include: { messages: true },
    });

    return NextResponse.json(ticket, { status: 201 });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  try {
    const user = await requireAuth();
    const { ticketId, message } = await request.json();

    if (!ticketId || !message) {
      return NextResponse.json({ error: "Ticket ID and message required" }, { status: 400 });
    }

    const ticket = await prisma.ticket.findFirst({
      where: { id: ticketId, userId: user.id },
    });
    if (!ticket) return NextResponse.json({ error: "Ticket not found" }, { status: 404 });

    await prisma.ticketMessage.create({
      data: { ticketId, userId: user.id, message, isAdmin: false },
    });
    await prisma.ticket.update({
      where: { id: ticketId },
      data: { status: "open" },
    });

    return NextResponse.json({ success: true });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
