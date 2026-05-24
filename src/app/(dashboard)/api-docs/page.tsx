"use client";

import { useState, useEffect } from "react";
import { Copy, Check, Code, Key } from "lucide-react";
import { toast } from "@/components/Toast";

export default function ApiDocsPage() {
  const [apiKey, setApiKey] = useState("");
  const [copied, setCopied] = useState("");

  useEffect(() => {
    fetch("/api/profile").then((r) => r.json()).then((data) => setApiKey(data.apiKey || ""));
  }, []);

  const copyToClipboard = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    setCopied(label);
    setTimeout(() => setCopied(""), 2000);
  };

  const regenerateKey = async () => {
    const res = await fetch("/api/profile", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ regenerateApiKey: true }),
    });
    const data = await res.json();
    if (res.ok) {
      setApiKey(data.apiKey);
      toast("success", "API key regenerated");
    }
  };

  const endpoints = [
    {
      title: "Get Services",
      code: `{ "key": "YOUR_API_KEY", "action": "services" }`,
    },
    {
      title: "Place Order",
      code: `{ "key": "YOUR_API_KEY", "action": "add", "service": 1, "link": "https://instagram.com/user", "quantity": 1000 }`,
    },
    {
      title: "Order Status",
      code: `{ "key": "YOUR_API_KEY", "action": "status", "order": 1 }`,
    },
    {
      title: "Check Balance",
      code: `{ "key": "YOUR_API_KEY", "action": "balance" }`,
    },
  ];

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div>
        <h1 className="text-2xl font-bold">API Documentation</h1>
        <p className="text-muted">Integrate Prism SMM into your applications</p>
      </div>

      <div className="glass rounded-2xl p-6">
        <div className="flex items-center gap-3 mb-4">
          <Key className="w-5 h-5 text-indigo-400" />
          <h2 className="font-semibold">Your API Key</h2>
        </div>
        <div className="flex items-center gap-3">
          <code className="flex-1 px-4 py-3 rounded-xl bg-black/30 text-sm font-mono text-emerald-400 overflow-x-auto">
            {apiKey || "Loading..."}
          </code>
          <button
            onClick={() => copyToClipboard(apiKey, "key")}
            className="p-2.5 rounded-xl border border-border hover:bg-surface-hover transition-colors"
          >
            {copied === "key" ? <Check className="w-4 h-4 text-emerald-400" /> : <Copy className="w-4 h-4" />}
          </button>
          <button
            onClick={regenerateKey}
            className="px-4 py-2.5 rounded-xl gradient-primary text-white text-sm font-medium hover:opacity-90"
          >
            Regenerate
          </button>
        </div>
      </div>

      <div className="glass rounded-2xl p-6">
        <div className="flex items-center gap-3 mb-4">
          <Code className="w-5 h-5 text-indigo-400" />
          <h2 className="font-semibold">API Endpoint</h2>
        </div>
        <div className="flex items-center gap-3">
          <code className="flex-1 px-4 py-3 rounded-xl bg-black/30 text-sm font-mono">
            <span className="text-cyan-400">POST</span>{" "}
            <span className="text-foreground">{typeof window !== "undefined" ? window.location.origin : ""}/api/v2</span>
          </code>
        </div>
        <p className="text-xs text-muted mt-2">All requests must use POST with Content-Type: application/json</p>
      </div>

      <div className="space-y-4">
        {endpoints.map((ep) => (
          <div key={ep.title} className="glass rounded-2xl p-6">
            <div className="flex items-center justify-between mb-3">
              <h3 className="font-semibold">{ep.title}</h3>
              <button
                onClick={() => copyToClipboard(ep.code, ep.title)}
                className="p-1.5 rounded-lg hover:bg-surface-hover"
              >
                {copied === ep.title ? <Check className="w-4 h-4 text-emerald-400" /> : <Copy className="w-4 h-4 text-muted" />}
              </button>
            </div>
            <pre className="px-4 py-3 rounded-xl bg-black/30 text-sm font-mono text-emerald-400 overflow-x-auto whitespace-pre-wrap">
              {ep.code}
            </pre>
          </div>
        ))}
      </div>
    </div>
  );
}
