# Prism SMM Panel

A modern, production-level Social Media Marketing (SMM) panel built with **Next.js 16**, **TypeScript**, **Tailwind CSS v4**, and **Prisma** with SQLite.

## Features

- **Modern Glassmorphism UI** — Dark theme with gradient accents and smooth animations
- **User Panel** — Dashboard, New Order, Orders, Wallet, Tickets, API Docs, Profile
- **Admin Panel** — Dashboard with charts, Orders, Services, Users, Tickets, Providers, Settings
- **Wallet System** — Deposit funds via PayPal, Stripe, Razorpay, Crypto
- **Order Management** — Place orders, bulk orders, drip-feed, refill/cancel support
- **Ticket System** — Support tickets with real-time messaging
- **REST API (v2)** — Full API for third-party integration
- **Authentication** — JWT-based auth with bcrypt password hashing
- **Responsive** — Desktop, tablet, and mobile support
- **Analytics** — Charts and statistics with Recharts
- **Security** — CSRF protection, input validation, httpOnly cookies

## Tech Stack

- **Framework**: Next.js 16 (App Router)
- **Language**: TypeScript
- **Styling**: Tailwind CSS v4
- **Database**: Prisma ORM + SQLite
- **Auth**: JWT + bcrypt
- **Charts**: Recharts
- **Icons**: Lucide React

## Getting Started

### Prerequisites

- Node.js 20+
- npm

### Installation

```bash
git clone https://github.com/nitin-kumar-649/prism-smm-panel.git
cd prism-smm-panel
npm install
```

### Setup Database

```bash
cp .env.example .env
npx prisma migrate dev --name init
npx tsx prisma/seed.ts
```

### Run Development Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000)

### Demo Credentials

- **Admin**: admin@prismsmm.com / admin123
- **User**: user@prismsmm.com / user123

## Project Structure

```
src/
├── app/
│   ├── (admin)/          # Admin panel pages
│   ├── (auth)/           # Login & Register
│   ├── (dashboard)/      # User panel pages
│   ├── api/              # API routes
│   └── page.tsx          # Homepage
├── components/           # Shared UI components
└── lib/                  # Utilities, auth, Prisma client
prisma/
├── schema.prisma         # Database schema
├── seed.ts               # Seed data
└── migrations/           # Database migrations
```

## API Documentation

All API requests use `POST` to `/api/v2` with JSON body:

```json
{ "key": "YOUR_API_KEY", "action": "services" }
{ "key": "YOUR_API_KEY", "action": "add", "service": 1, "link": "https://...", "quantity": 1000 }
{ "key": "YOUR_API_KEY", "action": "status", "order": 1 }
{ "key": "YOUR_API_KEY", "action": "balance" }
```

## Deployment

### Hostinger Node.js Hosting

1. **Connect GitHub repo** in Hostinger dashboard → Websites → Node.js
2. **Set Entry point** to: `server.js`
3. **Set environment variables** in Hostinger:
   ```
   NODE_ENV=production
   DATABASE_URL=file:./prisma/dev.db
   JWT_SECRET=your-secret-key-here
   ```
4. **Build command** (runs automatically): `npm run build`
5. **Initialize database** (run once via Hostinger terminal):
   ```bash
   npx prisma migrate deploy
   npx tsx prisma/seed.ts
   ```
6. **Restart** the Node.js app from Hostinger dashboard

The `output: 'standalone'` config in `next.config.ts` produces a self-contained server at `.next/standalone/` that includes only the necessary files. The build script automatically copies `public/` and `.next/static/` into the standalone folder.

### Other Platforms

```bash
npm run build
npm start
```

Compatible with Vercel, Netlify, Railway, and any Node.js hosting.

## License

MIT
