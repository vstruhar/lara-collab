import Logo from "@/components/Logo";
import useNavigationStore from "@/hooks/store/useNavigationStore";
import useSidebarStore from "@/hooks/store/useSidebarStore";
import { usePage } from "@inertiajs/react";
import {
  IconBuildingSkyscraper,
  IconFileDollar,
  IconGauge,
  IconLayoutList,
  IconListDetails,
  IconReportAnalytics,
  IconSettings,
  IconUsers,
  IconChevronLeft,
  IconChevronRight
} from "@tabler/icons-react";
import { useEffect } from "react";

import NavbarLinksGroup from "./NavbarLinksGroup";
import UserButton from "./UserButton";
import classes from "./css/NavBarNested.module.css";
import { Group, ScrollArea, Text, rem, ActionIcon, Transition } from "@mantine/core";

export default function Sidebar() {
  const { version } = usePage().props;
  const { items, setItems } = useNavigationStore();
  const { collapsed, toggle } = useSidebarStore();

  useEffect(() => {
    setItems([
      {
        label: "Dashboard",
        icon: IconGauge,
        link: route("dashboard"),
        active: route().current("dashboard"),
        visible: true,
      },
      {
        label: "Projects",
        icon: IconListDetails,
        link: route("projects.index"),
        active: route().current("projects.*"),
        visible: can("view projects"),
      },
      {
        label: "My Work",
        icon: IconLayoutList,
        active: route().current("my-work.*"),
        opened: route().current("my-work.*"),
        visible: can("view tasks") || can("view activities"),
        links: [
          {
            label: "Tasks",
            link: route("my-work.tasks.index"),
            active: route().current("my-work.tasks.*"),
            visible: can("view tasks"),
          },
          {
            label: "Activity",
            link: route("my-work.activity.index"),
            active: route().current("my-work.activity.*"),
            visible: can("view activities"),
          },
        ],
      },
      {
        label: "Users",
        icon: IconUsers,
        link: route("users.index"),
        active: route().current("users.*"),
        visible: can("view users"),
      },
      {
        label: "Settings",
        icon: IconSettings,
        active: route().current("settings.*"),
        opened: route().current("settings.*"),
        visible: can("view owner company") || can("view roles") || can("view labels") || can("view task priority"),
        links: [
          {
            label: "Company",
            link: route("settings.company.edit"),
            active: route().current("settings.company.*"),
            visible: can("view owner company"),
          },
          {
            label: "Roles",
            link: route("settings.roles.index"),
            active: route().current("settings.roles.*"),
            visible: can("view roles"),
          },
          {
            label: "Labels",
            link: route("settings.labels.index"),
            active: route().current("settings.labels.*"),
            visible: can("view labels"),
          },
          {
            label: "Priorities",
            link: route("settings.task-priorities.index"),
            active: route().current("settings.task-priorities.*"),
            visible: can("view task priority"),
          },
        ],
      },
    ]);
  }, []);

  return (
    <nav
      className={classes.navbar}
        style={{ width: collapsed ? 60 : 260, transition: "width 0.2s cubic-bezier(0.4, 0.0, 0.2, 1)" }}
    >
      <div className={classes.header}>
        <Group justify="space-between">
          {!collapsed && <Logo style={{ width: rem(120) }} />}
          {!collapsed && (
            <Text size="xs" className={classes.version}>
              v{version}
            </Text>
          )}
          <ActionIcon variant="light" onClick={toggle} size={32} aria-label="Toggle sidebar">
            {collapsed ? <IconChevronRight size={20} /> : <IconChevronLeft size={20} />}
          </ActionIcon>
        </Group>
      </div>

      {!collapsed && (
        <ScrollArea className={classes.links}>
          <div className={classes.linksInner}>
            {items
              .filter((i) => i.visible)
              .map((item) => (
                <NavbarLinksGroup key={item.label} item={item} />
              ))}
          </div>
        </ScrollArea>
      )}

      <div className={classes.footer}>
        {!collapsed && <UserButton />}
      </div>
    </nav>
  );
}
