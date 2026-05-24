import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

export async function GET() {
  try {
    const categories = await prisma.category.findMany({
      where: { status: "active" },
      include: {
        services: {
          where: { status: "active" },
          orderBy: { sortOrder: "asc" },
        },
      },
      orderBy: { sortOrder: "asc" },
    });
    return NextResponse.json(categories);
  } catch {
    return NextResponse.json({ error: "Failed to fetch services" }, { status: 500 });
  }
}
