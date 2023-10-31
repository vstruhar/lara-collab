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
import remove from "lodash/remove";
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
    },
    {
      label: "Projects",
      icon: IconListDetails,
      link: route("projects.index"),
      active: route().current("projects.*"),
    },
    {
      label: "My Work",
      icon: IconLayoutList,
      active: route().current("my-work.*"),
      links: [
        {
          label: "Tasks",
          link: route("my-work.tasks.index"),
          active: route().current("my-work.tasks.*"),
        },
        {
          label: "Activity",
          link: route("my-work.activity.index"),
          active: route().current("my-work.activity.*"),
        },
      ],
    },
    {
      label: "Users",
      icon: IconUsers,
      link: route("users.index"),
      active: route().current("users.*"),
    },
    {
      label: "Invoices",
      icon: IconFileDollar,
      link: route("invoices.index"),
      active: route().current("invoices.*"),
    },
    {
      label: "Reports",
      icon: IconReportAnalytics,
      link: route("reports.index"),
      active: route().current("reports.*"),
    },
    {
      label: "Settings",
      icon: IconSettings,
      active: route().current("settings.*"),
      links: [
        {
          label: "Company",
          link: route("settings.company.index"),
          active: route().current("settings.company.*"),
        },
        {
          label: "Roles",
          link: route("settings.roles.index"),
          active: route().current("settings.roles.*"),
        },
        {
          label: "Labels",
          link: route("settings.labels.index"),
          active: route().current("settings.labels.*"),
        },
      ],
    },
  ];

  if (!can("view users")) remove(items, (i) => i.label === "Users");

  const links = items.map((item) => (
    <NavbarLinksGroup {...item} key={item.label} />
  ));

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
