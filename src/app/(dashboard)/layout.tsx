import { redirect } from "next/navigation";
import { getCurrentUser } from "@/lib/auth";
import DashboardShell from "./DashboardShell";

export default async function DashboardLayout({ children }: { children: React.ReactNode }) {
  const user = await getCurrentUser();
  if (!user) redirect("/login");

  return (
    <DashboardShell user={{ username: user.username, email: user.email, role: user.role, balance: user.balance }}>
      {children}
    </DashboardShell>
  );
}
