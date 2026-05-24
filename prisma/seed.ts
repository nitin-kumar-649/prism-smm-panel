import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  const adminPassword = await bcrypt.hash("admin123", 12);
  const userPassword = await bcrypt.hash("user123", 12);

  const admin = await prisma.user.upsert({
    where: { email: "admin@prismsmm.com" },
    update: {},
    create: {
      username: "admin",
      email: "admin@prismsmm.com",
      password: adminPassword,
      role: "admin",
      status: "active",
      balance: 1000,
      apiKey: "pk_admin_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    },
  });

  const user = await prisma.user.upsert({
    where: { email: "user@prismsmm.com" },
    update: {},
    create: {
      username: "demo_user",
      email: "user@prismsmm.com",
      password: userPassword,
      role: "user",
      status: "active",
      balance: 100,
      apiKey: "pk_user_x1y2z3a4b5c6d7e8f9g0h1i2j3k4l5m6",
    },
  });

  const categories = await Promise.all([
    prisma.category.upsert({
      where: { slug: "instagram" },
      update: {},
      create: { name: "Instagram", slug: "instagram", sortOrder: 1 },
    }),
    prisma.category.upsert({
      where: { slug: "youtube" },
      update: {},
      create: { name: "YouTube", slug: "youtube", sortOrder: 2 },
    }),
    prisma.category.upsert({
      where: { slug: "tiktok" },
      update: {},
      create: { name: "TikTok", slug: "tiktok", sortOrder: 3 },
    }),
    prisma.category.upsert({
      where: { slug: "twitter" },
      update: {},
      create: { name: "Twitter / X", slug: "twitter", sortOrder: 4 },
    }),
    prisma.category.upsert({
      where: { slug: "facebook" },
      update: {},
      create: { name: "Facebook", slug: "facebook", sortOrder: 5 },
    }),
    prisma.category.upsert({
      where: { slug: "telegram" },
      update: {},
      create: { name: "Telegram", slug: "telegram", sortOrder: 6 },
    }),
    prisma.category.upsert({
      where: { slug: "spotify" },
      update: {},
      create: { name: "Spotify", slug: "spotify", sortOrder: 7 },
    }),
  ]);

  const services = [
    { categoryId: categories[0].id, name: "Instagram Followers [Real]", pricePer1000: 2.5, minQuantity: 100, maxQuantity: 100000, refill: true, dripFeed: true },
    { categoryId: categories[0].id, name: "Instagram Likes [Premium]", pricePer1000: 1.5, minQuantity: 50, maxQuantity: 50000, refill: false, dripFeed: true },
    { categoryId: categories[0].id, name: "Instagram Views [Fast]", pricePer1000: 0.5, minQuantity: 100, maxQuantity: 500000, refill: false, dripFeed: false },
    { categoryId: categories[0].id, name: "Instagram Comments [Custom]", pricePer1000: 15.0, minQuantity: 10, maxQuantity: 5000, refill: false, dripFeed: false },
    { categoryId: categories[1].id, name: "YouTube Views [Real]", pricePer1000: 3.0, minQuantity: 500, maxQuantity: 1000000, refill: false, dripFeed: true },
    { categoryId: categories[1].id, name: "YouTube Subscribers", pricePer1000: 12.0, minQuantity: 100, maxQuantity: 50000, refill: true, dripFeed: true },
    { categoryId: categories[1].id, name: "YouTube Likes", pricePer1000: 2.0, minQuantity: 50, maxQuantity: 100000, refill: false, dripFeed: false },
    { categoryId: categories[2].id, name: "TikTok Followers", pricePer1000: 3.5, minQuantity: 100, maxQuantity: 100000, refill: true, dripFeed: true },
    { categoryId: categories[2].id, name: "TikTok Likes", pricePer1000: 1.0, minQuantity: 100, maxQuantity: 200000, refill: false, dripFeed: false },
    { categoryId: categories[2].id, name: "TikTok Views", pricePer1000: 0.3, minQuantity: 500, maxQuantity: 1000000, refill: false, dripFeed: false },
    { categoryId: categories[3].id, name: "Twitter Followers [HQ]", pricePer1000: 5.0, minQuantity: 100, maxQuantity: 50000, refill: true, dripFeed: true },
    { categoryId: categories[3].id, name: "Twitter Likes", pricePer1000: 2.5, minQuantity: 50, maxQuantity: 100000, refill: false, dripFeed: false },
    { categoryId: categories[4].id, name: "Facebook Page Likes", pricePer1000: 4.0, minQuantity: 100, maxQuantity: 100000, refill: true, dripFeed: true },
    { categoryId: categories[5].id, name: "Telegram Members [Real]", pricePer1000: 3.0, minQuantity: 100, maxQuantity: 100000, refill: false, dripFeed: false },
    { categoryId: categories[6].id, name: "Spotify Plays", pricePer1000: 1.5, minQuantity: 1000, maxQuantity: 1000000, refill: false, dripFeed: true },
  ];

  for (const service of services) {
    await prisma.service.create({ data: { ...service, cancel: true } });
  }

  await prisma.order.create({
    data: {
      orderId: "ORD-DEMO-001",
      userId: user.id,
      serviceId: 1,
      link: "https://instagram.com/example",
      quantity: 1000,
      charge: 2.5,
      status: "completed",
      startCount: 5420,
      remains: 0,
    },
  });

  await prisma.transaction.create({
    data: {
      transactionId: "TXN-DEMO-001",
      userId: user.id,
      type: "deposit",
      amount: 100,
      balanceAfter: 100,
      description: "Initial deposit",
    },
  });

  await prisma.transaction.create({
    data: {
      transactionId: "TXN-DEMO-002",
      userId: user.id,
      type: "charge",
      amount: -2.5,
      balanceAfter: 97.5,
      description: "Order ORD-DEMO-001 - Instagram Followers",
    },
  });

  const settings = [
    { key: "site_name", value: "Prism SMM", group: "general" },
    { key: "site_description", value: "Premium Social Media Marketing Panel", group: "general" },
    { key: "currency", value: "USD", group: "general" },
    { key: "currency_symbol", value: "$", group: "general" },
    { key: "min_deposit", value: "5", group: "payment" },
    { key: "max_deposit", value: "10000", group: "payment" },
  ];
  for (const s of settings) {
    await prisma.setting.upsert({
      where: { key: s.key },
      update: {},
      create: s,
    });
  }

  console.log("Seed complete:", { admin: admin.email, user: user.email });
}

main()
  .then(() => prisma.$disconnect())
  .catch((e) => {
    console.error(e);
    prisma.$disconnect();
    process.exit(1);
  });
