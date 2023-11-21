import FlashNotification from "@/components/FlashNotification";
import useAuthorization from "@/hooks/useAuthorization";
import useWebSocketsNotifications from "@/hooks/useWebSocketsNotifications";
import NavBarNested from "@/layouts/NavBarNested";
import { Head } from "@inertiajs/react";
import { AppShell } from "@mantine/core";
import { useEffect } from "react";

export default function MainLayout({ children, title }) {
  window.can = useAuthorization().can;

  const { init } = useWebSocketsNotifications();

  useEffect(() => {
    init();
  }, []);

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
