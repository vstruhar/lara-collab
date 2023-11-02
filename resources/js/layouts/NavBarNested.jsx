import { Code, Group, ScrollArea, rem } from "@mantine/core";
import {
  IconFileDollar,
  IconGauge,
  IconLayoutList,
  IconListDetails,
  IconReportAnalytics,
  IconSettings,
  IconUsers,
} from "@tabler/icons-react";
import Logo from "../components/Logo";
import NavbarLinksGroup from "./NavbarLinksGroup";
import UserButton from "./UserButton";
import classes from "./css/NavbarNested.module.css";

export default function Sidebar() {
  const items = [
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
      visible: true,
    },
    {
      label: "My Work",
      icon: IconLayoutList,
      active: route().current("my-work.*"),
      visible: true,
      links: [
        {
          label: "Tasks",
          link: route("my-work.tasks.index"),
          active: route().current("my-work.tasks.*"),
          visible: true,
        },
        {
          label: "Activity",
          link: route("my-work.activity.index"),
          active: route().current("my-work.activity.*"),
          visible: true,
        },
      ],
    },
    {
      label: "Users",
      icon: IconUsers,
      link: route("users.index"),
      active: route().current("users.*"),
      visible: true,
    },
    {
      label: "Invoices",
      icon: IconFileDollar,
      link: route("invoices.index"),
      active: route().current("invoices.*"),
      visible: true,
    },
    {
      label: "Reports",
      icon: IconReportAnalytics,
      link: route("reports.index"),
      active: route().current("reports.*"),
      visible: true,
    },
    {
      label: "Settings",
      icon: IconSettings,
      active: route().current("settings.*"),
      visible: true,
      links: [
        {
          label: "Company",
          link: route("settings.company.index"),
          active: route().current("settings.company.*"),
          visible: true,
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
      ],
    },
  ];

  const links = items
    .filter((i) => i.visible)
    .map((item) => <NavbarLinksGroup {...item} key={item.label} />);

  return (
    <nav className={classes.navbar}>
      <div className={classes.header}>
        <Group justify="space-between">
          <Logo style={{ width: rem(120) }} />
          <Code fw={700}>v1.0.0</Code>
        </Group>
      </div>

      <ScrollArea className={classes.links}>
        <div className={classes.linksInner}>{links}</div>
      </ScrollArea>

      <div className={classes.footer}>
        <UserButton />
      </div>
    </nav>
  );
}
