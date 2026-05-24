"use client";

import { useState, useEffect } from "react";
import { Plus, Pencil, Loader2 } from "lucide-react";
import { toast } from "@/components/Toast";

interface Provider {
  id: number;
  name: string;
  url: string;
  apiKey: string;
  status: string;
  balance: number;
  _count: { services: number };
}

export default function AdminProvidersPage() {
  const [providers, setProviders] = useState<Provider[]>([]);
  const [editing, setEditing] = useState<Partial<Provider> | null>(null);
  const [loading, setLoading] = useState(false);

  const load = () => {
    fetch("/api/admin/providers").then((r) => r.json()).then(setProviders);
  };

  useEffect(() => { load(); }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editing) return;
    setLoading(true);
    try {
      const method = editing.id ? "PUT" : "POST";
      const res = await fetch("/api/admin/providers", {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(editing),
      });
      if (res.ok) {
        toast("success", editing.id ? "Updated" : "Created");
        setEditing(null);
        load();
      }
    } catch {
      toast("error", "Failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">API Providers</h1>
        <button
          onClick={() => setEditing({ name: "", url: "", apiKey: "", status: "active" })}
          className="inline-flex items-center gap-2 px-4 py-2 rounded-xl gradient-primary text-white text-sm font-medium hover:opacity-90 shadow-lg shadow-indigo-500/25"
        >
          <Plus className="w-4 h-4" /> Add Provider
        </button>
      </div>

      {editing && (
        <form onSubmit={handleSave} className="glass rounded-2xl p-6 space-y-4">
          <h3 className="font-semibold">{editing.id ? "Edit" : "New"} Provider</h3>
          <div className="grid sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs text-muted mb-1">Name</label>
              <input value={editing.name || ""} onChange={(e) => setEditing({ ...editing, name: e.target.value })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" required />
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">URL</label>
              <input value={editing.url || ""} onChange={(e) => setEditing({ ...editing, url: e.target.value })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" required />
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">API Key</label>
              <input value={editing.apiKey || ""} onChange={(e) => setEditing({ ...editing, apiKey: e.target.value })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary" required />
            </div>
            <div>
              <label className="block text-xs text-muted mb-1">Status</label>
              <select value={editing.status || "active"} onChange={(e) => setEditing({ ...editing, status: e.target.value })} className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div className="flex gap-3">
            <button type="submit" disabled={loading} className="px-6 py-2.5 rounded-xl gradient-primary text-white text-sm font-medium disabled:opacity-50 flex items-center gap-2">
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : "Save"}
            </button>
            <button type="button" onClick={() => setEditing(null)} className="px-6 py-2.5 rounded-xl border border-border text-sm hover:bg-surface-hover">Cancel</button>
          </div>
        </form>
      )}

      <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {providers.map((p) => (
          <div key={p.id} className="glass rounded-2xl p-5">
            <div className="flex items-center justify-between mb-3">
              <h3 className="font-semibold">{p.name}</h3>
              <button onClick={() => setEditing(p)} className="p-1.5 rounded-lg hover:bg-surface-hover text-muted hover:text-foreground">
                <Pencil className="w-3.5 h-3.5" />
              </button>
            </div>
            <p className="text-xs text-muted truncate mb-2">{p.url}</p>
            <div className="flex items-center gap-3 text-xs">
              <span className={`px-2 py-0.5 rounded-lg ${p.status === "active" ? "bg-emerald-500/20 text-emerald-400" : "bg-gray-500/20 text-gray-400"}`}>
                {p.status}
              </span>
              <span className="text-muted">{p._count.services} services</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
