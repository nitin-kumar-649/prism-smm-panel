import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireAdmin } from "@/lib/auth";

export async function GET() {
  try {
    await requireAdmin();
    const categories = await prisma.category.findMany({
      include: { _count: { select: { services: true } } },
      orderBy: { sortOrder: "asc" },
    });
    return NextResponse.json(categories);
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    await requireAdmin();
    const { name, slug, description, sortOrder } = await request.json();
    const category = await prisma.category.create({
      data: { name, slug, description, sortOrder: sortOrder || 0 },
    });
    return NextResponse.json(category, { status: 201 });
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Failed";
    if (msg === "Unauthorized" || msg === "Forbidden") return NextResponse.json({ error: msg }, { status: 403 });
    return NextResponse.json({ error: msg }, { status: 500 });
  }
}
