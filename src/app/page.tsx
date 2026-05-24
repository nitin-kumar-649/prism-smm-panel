import Link from "next/link";
import { Zap, Shield, Clock, Globe, TrendingUp, Users, ArrowRight, Star, Sparkles } from "lucide-react";

const features = [
  { icon: Zap, title: "Instant Delivery", desc: "Orders start processing within seconds of placement" },
  { icon: Shield, title: "Secure Platform", desc: "Enterprise-grade security with encrypted transactions" },
  { icon: Clock, title: "24/7 Support", desc: "Round-the-clock customer support via ticket system" },
  { icon: Globe, title: "All Platforms", desc: "Instagram, YouTube, TikTok, Twitter, Facebook & more" },
  { icon: TrendingUp, title: "Best Prices", desc: "Most competitive prices in the market with bulk discounts" },
  { icon: Users, title: "API Access", desc: "Full API integration for automated order management" },
];

const stats = [
  { value: "10M+", label: "Orders Delivered" },
  { value: "50K+", label: "Happy Customers" },
  { value: "99.9%", label: "Uptime" },
  { value: "24/7", label: "Support" },
];

const platforms = ["Instagram", "YouTube", "TikTok", "Twitter/X", "Facebook", "Telegram", "Spotify"];

export default function Home() {
  return (
    <div className="min-h-screen gradient-mesh">
      <header className="glass sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
          <Link href="/" className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg gradient-primary flex items-center justify-center animate-glow">
              <Zap className="w-5 h-5 text-white" />
            </div>
            <span className="text-xl font-bold bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-transparent">
              Prism SMM
            </span>
          </Link>
          <div className="flex items-center gap-3">
            <Link
              href="/login"
              className="px-4 py-2 text-sm font-medium text-muted hover:text-foreground transition-colors"
            >
              Sign In
            </Link>
            <Link
              href="/register"
              className="px-5 py-2 text-sm font-medium text-white gradient-primary rounded-xl hover:opacity-90 transition-opacity shadow-lg shadow-indigo-500/25"
            >
              Get Started
            </Link>
          </div>
        </div>
      </header>

      <main>
        <section className="relative overflow-hidden py-24 sm:py-32">
          <div className="absolute inset-0 overflow-hidden">
            <div className="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl animate-float" />
            <div className="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-cyan-500/10 blur-3xl animate-float" style={{ animationDelay: "3s" }} />
          </div>
          <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-sm text-indigo-300 mb-8">
              <Sparkles className="w-4 h-4" />
              #1 SMM Panel Platform
            </div>
            <h1 className="text-4xl sm:text-5xl lg:text-7xl font-bold tracking-tight mb-6">
              <span className="bg-gradient-to-r from-white via-indigo-200 to-white bg-clip-text text-transparent">
                Grow Your Social
              </span>
              <br />
              <span className="bg-gradient-to-r from-indigo-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent">
                Media Presence
              </span>
            </h1>
            <p className="max-w-2xl mx-auto text-lg text-muted mb-10">
              The most powerful SMM panel for Instagram, YouTube, TikTok, and more.
              Premium quality services with instant delivery and the best prices.
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <Link
                href="/register"
                className="group inline-flex items-center gap-2 px-8 py-3.5 text-base font-semibold text-white gradient-primary rounded-2xl hover:opacity-90 transition-opacity shadow-2xl shadow-indigo-500/30"
              >
                Start Growing Now
                <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </Link>
              <Link
                href="/login"
                className="inline-flex items-center gap-2 px-8 py-3.5 text-base font-medium glass rounded-2xl hover:bg-surface-hover transition-colors"
              >
                View Services
              </Link>
            </div>
          </div>
        </section>

        <section className="py-12 border-y border-border">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
              {stats.map((s) => (
                <div key={s.label} className="text-center">
                  <p className="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-transparent">
                    {s.value}
                  </p>
                  <p className="text-sm text-muted mt-1">{s.label}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section className="py-20 sm:py-24">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-center mb-16">
              <h2 className="text-3xl sm:text-4xl font-bold mb-4">Why Choose Prism SMM?</h2>
              <p className="text-muted max-w-xl mx-auto">Everything you need to boost your social media growth</p>
            </div>
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {features.map((f) => (
                <div key={f.title} className="group glass rounded-2xl p-6 hover:bg-surface-hover transition-all duration-300 stat-card">
                  <div className="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                    <f.icon className="w-6 h-6 text-white" />
                  </div>
                  <h3 className="text-lg font-semibold mb-2">{f.title}</h3>
                  <p className="text-sm text-muted">{f.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section className="py-20 sm:py-24 border-t border-border">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 className="text-3xl sm:text-4xl font-bold mb-6">Supported Platforms</h2>
            <div className="flex flex-wrap justify-center gap-4">
              {platforms.map((p) => (
                <div key={p} className="glass rounded-2xl px-6 py-3 text-sm font-medium hover:bg-surface-hover transition-colors">
                  {p}
                </div>
              ))}
            </div>
          </div>
        </section>

        <section className="py-20 sm:py-24">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 className="text-3xl sm:text-4xl font-bold mb-4">Trusted by Thousands</h2>
            <div className="flex items-center justify-center gap-1 mb-8">
              {[...Array(5)].map((_, i) => (
                <Star key={i} className="w-6 h-6 text-yellow-400 fill-yellow-400" />
              ))}
              <span className="ml-2 text-muted">4.9/5 rating</span>
            </div>
          </div>
        </section>

        <section className="py-20 sm:py-24 border-t border-border">
          <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 className="text-3xl sm:text-4xl font-bold mb-4">Ready to Get Started?</h2>
            <p className="text-muted mb-8">
              Join thousands of satisfied customers and start growing your social media today.
            </p>
            <Link
              href="/register"
              className="inline-flex items-center gap-2 px-10 py-4 text-lg font-semibold text-white gradient-primary rounded-2xl hover:opacity-90 transition-opacity shadow-2xl shadow-indigo-500/30"
            >
              Create Free Account
              <ArrowRight className="w-5 h-5" />
            </Link>
          </div>
        </section>
      </main>

      <footer className="border-t border-border py-8">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <Zap className="w-5 h-5 text-indigo-400" />
            <span className="font-semibold">Prism SMM</span>
          </div>
          <p className="text-sm text-muted">&copy; {new Date().getFullYear()} Prism SMM. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
}
