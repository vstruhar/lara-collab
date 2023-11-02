import FlashNotification from "@/components/FlashNotification";
import useAuthorization from "@/hooks/useAuthorization";
import NavBarNested from "@/layouts/NavBarNested";
import { Head } from "@inertiajs/react";
import { AppShell } from "@mantine/core";

export default function MainLayout({ children, title }) {
  window.can = useAuthorization().can;

  return (
    <AppShell
      navbar={{ width: 300, breakpoint: "sm", collapsed: { mobile: false } }}
      style={{ padding: "3rem 5rem" }}
    >
      <Head title={title} />

      <FlashNotification />

      <AppShell.Navbar>
        <NavBarNested></NavBarNested>
      </AppShell.Navbar>

      <AppShell.Main>{children}</AppShell.Main>
    </AppShell>
  );
}
