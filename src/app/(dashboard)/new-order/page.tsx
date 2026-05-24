"use client";

import { useState, useEffect } from "react";
import { ShoppingCart, Loader2, Info } from "lucide-react";
import { toast } from "@/components/Toast";
import { formatCurrency } from "@/lib/utils";

interface Service {
  id: number;
  name: string;
  pricePer1000: number;
  minQuantity: number;
  maxQuantity: number;
  dripFeed: boolean;
  refill: boolean;
  cancel: boolean;
  description: string | null;
}

interface Category {
  id: number;
  name: string;
  services: Service[];
}

export default function NewOrderPage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedService, setSelectedService] = useState<Service | null>(null);
  const [link, setLink] = useState("");
  const [quantity, setQuantity] = useState("");
  const [dripFeed, setDripFeed] = useState(false);
  const [dripFeedInterval, setDripFeedInterval] = useState("");
  const [dripFeedQuantity, setDripFeedQuantity] = useState("");
  const [dripFeedRuns, setDripFeedRuns] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetch("/api/services")
      .then((r) => r.json())
      .then(setCategories)
      .catch(() => toast("error", "Failed to load services"));
  }, []);

  const filteredServices = selectedCategory
    ? categories.find((c) => c.id === selectedCategory)?.services || []
    : categories.flatMap((c) => c.services);

  const charge = selectedService && quantity
    ? (selectedService.pricePer1000 / 1000) * Number(quantity)
    : 0;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedService) return toast("error", "Select a service");
    setLoading(true);
    try {
      const res = await fetch("/api/orders", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          serviceId: selectedService.id,
          link,
          quantity: Number(quantity),
          dripFeed,
          dripFeedInterval: dripFeedInterval ? Number(dripFeedInterval) : null,
          dripFeedQuantity: dripFeedQuantity ? Number(dripFeedQuantity) : null,
          dripFeedRuns: dripFeedRuns ? Number(dripFeedRuns) : null,
        }),
      });
      const data = await res.json();
      if (!res.ok) return toast("error", data.error);
      toast("success", `Order placed! ${data.orderId}`);
      setLink("");
      setQuantity("");
      setDripFeed(false);
    } catch {
      toast("error", "Order failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      <div>
        <h1 className="text-2xl font-bold">New Order</h1>
        <p className="text-muted">Place a new social media marketing order</p>
      </div>

      <form onSubmit={handleSubmit} className="glass rounded-2xl p-6 space-y-5">
        <div>
          <label className="block text-sm font-medium mb-2">Category</label>
          <select
            value={selectedCategory || ""}
            onChange={(e) => {
              setSelectedCategory(e.target.value ? Number(e.target.value) : null);
              setSelectedService(null);
            }}
            className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary transition-colors"
          >
            <option value="">All Categories</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">Service</label>
          <select
            value={selectedService?.id || ""}
            onChange={(e) => {
              const svc = filteredServices.find((s) => s.id === Number(e.target.value));
              setSelectedService(svc || null);
              if (svc) setQuantity(svc.minQuantity.toString());
            }}
            className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary transition-colors"
            required
          >
            <option value="">Select a service</option>
            {filteredServices.map((s) => (
              <option key={s.id} value={s.id}>
                {s.name} — ${s.pricePer1000}/1K
              </option>
            ))}
          </select>
        </div>

        {selectedService && (
          <div className="flex flex-wrap gap-3 text-xs">
            <span className="px-2.5 py-1 rounded-lg bg-indigo-500/15 text-indigo-300 border border-indigo-500/20">
              Min: {selectedService.minQuantity.toLocaleString()}
            </span>
            <span className="px-2.5 py-1 rounded-lg bg-indigo-500/15 text-indigo-300 border border-indigo-500/20">
              Max: {selectedService.maxQuantity.toLocaleString()}
            </span>
            <span className="px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-300 border border-emerald-500/20">
              ${selectedService.pricePer1000}/1K
            </span>
            {selectedService.refill && (
              <span className="px-2.5 py-1 rounded-lg bg-cyan-500/15 text-cyan-300 border border-cyan-500/20">Refill</span>
            )}
            {selectedService.dripFeed && (
              <span className="px-2.5 py-1 rounded-lg bg-purple-500/15 text-purple-300 border border-purple-500/20">Drip-feed</span>
            )}
          </div>
        )}

        <div>
          <label className="block text-sm font-medium mb-2">Link</label>
          <input
            type="url"
            value={link}
            onChange={(e) => setLink(e.target.value)}
            className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground placeholder:text-muted focus:outline-none focus:border-primary transition-colors"
            placeholder="https://instagram.com/username"
            required
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">Quantity</label>
          <input
            type="number"
            value={quantity}
            onChange={(e) => setQuantity(e.target.value)}
            className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground placeholder:text-muted focus:outline-none focus:border-primary transition-colors"
            min={selectedService?.minQuantity || 1}
            max={selectedService?.maxQuantity || 1000000}
            required
          />
        </div>

        {selectedService?.dripFeed && (
          <div className="space-y-3">
            <label className="flex items-center gap-3 cursor-pointer">
              <input
                type="checkbox"
                checked={dripFeed}
                onChange={(e) => setDripFeed(e.target.checked)}
                className="w-4 h-4 rounded accent-primary"
              />
              <span className="text-sm">Enable Drip-feed</span>
            </label>
            {dripFeed && (
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-xs text-muted mb-1">Runs</label>
                  <input type="number" value={dripFeedRuns} onChange={(e) => setDripFeedRuns(e.target.value)} className="w-full px-3 py-2 rounded-lg bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" min={2} />
                </div>
                <div>
                  <label className="block text-xs text-muted mb-1">Interval (min)</label>
                  <input type="number" value={dripFeedInterval} onChange={(e) => setDripFeedInterval(e.target.value)} className="w-full px-3 py-2 rounded-lg bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" min={1} />
                </div>
                <div>
                  <label className="block text-xs text-muted mb-1">Qty per run</label>
                  <input type="number" value={dripFeedQuantity} onChange={(e) => setDripFeedQuantity(e.target.value)} className="w-full px-3 py-2 rounded-lg bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" min={1} />
                </div>
              </div>
            )}
          </div>
        )}

        {charge > 0 && (
          <div className="flex items-center gap-2 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20">
            <Info className="w-5 h-5 text-indigo-400 shrink-0" />
            <span className="text-sm">
              Total charge: <strong className="text-indigo-300">{formatCurrency(charge)}</strong>
            </span>
          </div>
        )}

        <button
          type="submit"
          disabled={loading || !selectedService}
          className="w-full py-3.5 rounded-xl gradient-primary text-white font-semibold hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/25"
        >
          {loading ? <Loader2 className="w-5 h-5 animate-spin" /> : (
            <>
              <ShoppingCart className="w-5 h-5" />
              Place Order
            </>
          )}
        </button>
      </form>
    </div>
  );
}
