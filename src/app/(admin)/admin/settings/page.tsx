"use client";

import { useState, useEffect } from "react";
import { Save, Loader2 } from "lucide-react";
import { toast } from "@/components/Toast";

export default function AdminSettingsPage() {
  const [settings, setSettings] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetch("/api/admin/settings").then((r) => r.json()).then(setSettings);
  }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await fetch("/api/admin/settings", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(settings),
      });
      if (res.ok) toast("success", "Settings saved");
    } catch {
      toast("error", "Failed to save");
    } finally {
      setLoading(false);
    }
  };

  const fields = [
    { key: "site_name", label: "Site Name", type: "text" },
    { key: "site_description", label: "Site Description", type: "text" },
    { key: "currency", label: "Currency Code", type: "text" },
    { key: "currency_symbol", label: "Currency Symbol", type: "text" },
    { key: "min_deposit", label: "Minimum Deposit", type: "number" },
    { key: "max_deposit", label: "Maximum Deposit", type: "number" },
  ];

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Settings</h1>

      <form onSubmit={handleSave} className="glass rounded-2xl p-6 space-y-5">
        {fields.map((f) => (
          <div key={f.key}>
            <label className="block text-sm font-medium mb-2">{f.label}</label>
            <input
              type={f.type}
              value={settings[f.key] || ""}
              onChange={(e) => setSettings({ ...settings, [f.key]: e.target.value })}
              className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary"
            />
          </div>
        ))}

        <button
          type="submit"
          disabled={loading}
          className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl gradient-primary text-white text-sm font-medium hover:opacity-90 disabled:opacity-50 shadow-lg shadow-indigo-500/25"
        >
          {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
          Save Settings
        </button>
      </form>
    </div>
  );
}
