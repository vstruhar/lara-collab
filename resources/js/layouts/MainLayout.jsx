import FlashNotification from "@/components/FlashNotification";
import useNotificationsStore from "@/hooks/store/useNotificationsStore";
import useAuthorization from "@/hooks/useAuthorization";
import useWebSockets from "@/hooks/useWebSockets";
import NavBarNested from "@/layouts/NavBarNested";
import Notifications from "@/layouts/Notifications";
import { Head, usePage } from "@inertiajs/react";
import { AppShell } from "@mantine/core";
import { useEffect } from "react";

export default function MainLayout({ children, title }) {
  window.can = useAuthorization().can;

  const { initUserWebSocket } = useWebSockets();
  const { notifications } = usePage().props.auth;
  const { setNotifications } = useNotificationsStore();

  useEffect(() => {
    initUserWebSocket();
    setNotifications(notifications);
  }, []);

  return (
    <AppShell
      navbar={{ width: 300, breakpoint: "sm", collapsed: { mobile: false } }}
      padding="4rem"
    >
      <Head title={title} />

      <FlashNotification />

      <Notifications />

      <AppShell.Navbar>
        <NavBarNested></NavBarNested>
      </AppShell.Navbar>

      <AppShell.Main>{children}</AppShell.Main>
    </AppShell>
  );
}
