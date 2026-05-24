"use client";

import { useState, useEffect } from "react";
import { Plus, Pencil, Trash2, Loader2 } from "lucide-react";
import { formatCurrency } from "@/lib/utils";
import { toast } from "@/components/Toast";

interface Service {
  id: number;
  name: string;
  pricePer1000: number;
  minQuantity: number;
  maxQuantity: number;
  status: string;
  dripFeed: boolean;
  refill: boolean;
  cancel: boolean;
  categoryId: number;
  category: { name: string };
}

interface Category {
  id: number;
  name: string;
}

export default function AdminServicesPage() {
  const [services, setServices] = useState<Service[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [editing, setEditing] = useState<Service | null>(null);
  const [loading, setLoading] = useState(false);

  const load = () => {
    fetch("/api/admin/services").then((r) => r.json()).then(setServices);
    fetch("/api/admin/categories").then((r) => r.json()).then(setCategories);
  };

  useEffect(() => { load(); }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editing) return;
    setLoading(true);
    try {
      const method = editing.id ? "PUT" : "POST";
      const res = await fetch("/api/admin/services", {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(editing),
      });
      if (res.ok) {
        toast("success", editing.id ? "Service updated" : "Service created");
        setEditing(null);
        load();
      }
    } catch {
      toast("error", "Failed to save");
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm("Delete this service?")) return;
    const res = await fetch("/api/admin/services", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id }),
    });
    if (res.ok) { toast("success", "Deleted"); load(); }
  };

  const newService = (): Service => ({
    id: 0, name: "", pricePer1000: 0, minQuantity: 100, maxQuantity: 10000,
    status: "active", dripFeed: false, refill: false, cancel: false,
    categoryId: categories[0]?.id || 1, category: { name: "" },
  });

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Services</h1>
        <button
          onClick={() => setEditing(newService())}
          className="inline-flex items-center gap-2 px-4 py-2 rounded-xl gradient-primary text-white text-sm font-medium hover:opacity-90 shadow-lg shadow-indigo-500/25"
        >
          <Plus className="w-4 h-4" /> Add Service
        </button>
      </div>

      {editing && (
        <form onSubmit={handleSave} className="glass rounded-2xl p-6 space-y-4">
          <h3 className="font-semibold">{editing.id ? "Edit" : "New"} Service</h3>
          <div className="grid sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs text-muted mb-1">Name</label>
              <input value={editing.name} onChange={(e) => setEditing({ ...editing, name: e.target.value })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" required />
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">Category</label>
              <select value={editing.categoryId} onChange={(e) => setEditing({ ...editing, categoryId: Number(e.target.value) })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary">
                {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">Price per 1000</label>
              <input type="number" step="0.01" value={editing.pricePer1000} onChange={(e) => setEditing({ ...editing, pricePer1000: Number(e.target.value) })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" required />
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">Status</label>
              <select value={editing.status} onChange={(e) => setEditing({ ...editing, status: e.target.value })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">Min Quantity</label>
              <input type="number" value={editing.minQuantity} onChange={(e) => setEditing({ ...editing, minQuantity: Number(e.target.value) })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" />
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">Max Quantity</label>
              <input type="number" value={editing.maxQuantity} onChange={(e) => setEditing({ ...editing, maxQuantity: Number(e.target.value) })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" />
            </div>
          </div>
          <div className="flex flex-wrap gap-4">
            <label className="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" checked={editing.dripFeed} onChange={(e) => setEditing({ ...editing, dripFeed: e.target.checked })} className="accent-primary" /> Drip-feed</label>
            <label className="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" checked={editing.refill} onChange={(e) => setEditing({ ...editing, refill: e.target.checked })} className="accent-primary" /> Refill</label>
            <label className="flex items-center gap-2 text-sm cursor-pointer"><input type="checkbox" checked={editing.cancel} onChange={(e) => setEditing({ ...editing, cancel: e.target.checked })} className="accent-primary" /> Cancel</label>
          </div>
          <div className="flex gap-3">
            <button type="submit" disabled={loading} className="px-6 py-2.5 rounded-xl gradient-primary text-white text-sm font-medium disabled:opacity-50 flex items-center gap-2">
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : "Save"}
            </button>
            <button type="button" onClick={() => setEditing(null)} className="px-6 py-2.5 rounded-xl border border-border text-sm hover:bg-surface-hover">Cancel</button>
          </div>
        </form>
      )}

      <div className="glass rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-muted border-b border-border">
                <th className="px-4 py-3 font-medium">ID</th>
                <th className="px-4 py-3 font-medium">Name</th>
                <th className="px-4 py-3 font-medium">Category</th>
                <th className="px-4 py-3 font-medium">Price/1K</th>
                <th className="px-4 py-3 font-medium">Min</th>
                <th className="px-4 py-3 font-medium">Max</th>
                <th className="px-4 py-3 font-medium">Status</th>
                <th className="px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {services.map((s) => (
                <tr key={s.id} className="hover:bg-surface-hover transition-colors">
                  <td className="px-4 py-3">{s.id}</td>
                  <td className="px-4 py-3 max-w-[200px] truncate">{s.name}</td>
                  <td className="px-4 py-3 text-xs">{s.category.name}</td>
                  <td className="px-4 py-3">{formatCurrency(s.pricePer1000)}</td>
                  <td className="px-4 py-3">{s.minQuantity.toLocaleString()}</td>
                  <td className="px-4 py-3">{s.maxQuantity.toLocaleString()}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-0.5 rounded-lg text-xs font-medium border ${s.status === "active" ? "bg-emerald-500/20 text-emerald-400 border-emerald-500/30" : "bg-gray-500/20 text-gray-400 border-gray-500/30"}`}>
                      {s.status}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2">
                      <button onClick={() => setEditing(s)} className="p-1.5 rounded-lg hover:bg-surface-hover text-muted hover:text-foreground"><Pencil className="w-3.5 h-3.5" /></button>
                      <button onClick={() => handleDelete(s.id)} className="p-1.5 rounded-lg hover:bg-red-500/10 text-muted hover:text-red-400"><Trash2 className="w-3.5 h-3.5" /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
