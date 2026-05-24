import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAuth, hashPassword } from "@/lib/auth";
import { generateApiKey } from "@/lib/utils";

export async function GET() {
  try {
    const user = await requireAuth();
    return NextResponse.json(user);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  try {
    const user = await requireAuth();
    const { username, phone, password, regenerateApiKey } = await request.json();

    const data: Record<string, unknown> = {};
    if (username) data.username = username;
    if (phone !== undefined) data.phone = phone;
    if (password) data.password = await hashPassword(password);
    if (regenerateApiKey) data.apiKey = generateApiKey();

    const updated = await prisma.user.update({
      where: { id: user.id },
      data,
      select: {
        id: true,
        username: true,
        email: true,
        role: true,
        phone: true,
        apiKey: true,
        balance: true,
        createdAt: true,
      },
    });

    return NextResponse.json(updated);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized") return NextResponse.json({ error: msg }, { status: 401 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
