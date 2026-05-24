import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAdmin } from "@/lib/auth";

export async function GET() {
  try {
    await requireAdmin();
    const providers = await prisma.provider.findMany({
      include: { _count: { select: { services: true } } },
      orderBy: { createdAt: "desc" },
    });
    return NextResponse.json(providers);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    await requireAdmin();
    const { name, url, apiKey } = await request.json();
    const provider = await prisma.provider.create({ data: { name, url, apiKey } });
    return NextResponse.json(provider, { status: 201 });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  try {
    await requireAdmin();
    const { id, ...data } = await request.json();
    const provider = await prisma.provider.update({ where: { id }, data });
    return NextResponse.json(provider);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
