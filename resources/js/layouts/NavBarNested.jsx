import { Code, Group, ScrollArea, rem } from "@mantine/core";
import {
  IconBuildingSkyscraper,
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
      visible: can("view projects"),
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
      label: "Clients",
      icon: IconBuildingSkyscraper,
      active: route().current("clients.*"),
      visible: can("view client users") || can("view client companies"),
      links: [
        {
          label: "Users",
          link: route("clients.users.index"),
          active: route().current("clients.users.*"),
          visible: can("view client users"),
        },
        {
          label: "Companies",
          link: route("clients.companies.index"),
          active: route().current("clients.companies.*"),
          visible: can("view client companies"),
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
      visible:
        can("view owner company") || can("view roles") || can("view labels"),
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
