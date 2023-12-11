import { redirectTo } from "@/utils/route";
import { getInitials } from "@/utils/user";
import { router, usePage } from "@inertiajs/react";
import {
  Avatar,
  Group,
  Menu,
  Text,
  UnstyledButton,
  darken,
  rem,
  useComputedColorScheme,
  useMantineColorScheme,
  useMantineTheme,
} from "@mantine/core";
import { upperFirst } from "@mantine/hooks";
import {
  IconBell,
  IconChevronRight,
  IconLogout,
  IconMoon,
  IconSun,
  IconUser,
} from "@tabler/icons-react";
import classes from "./css/UserButton.module.css";

export default function UserButton() {
  const { user } = usePage().props.auth;
  const { setColorScheme } = useMantineColorScheme({ keepTransition: true });
  const computedColorScheme = useComputedColorScheme();
  const { colors } = useMantineTheme();

  const logout = () => {
    router.delete(route("logout"), {
      onSuccess: redirectTo("auth.login.form"),
    });
  };

  return (
    <Menu
      position="right"
      offset={10}
      withArrow
      width={200}
      shadow="md"
      styles={{ dropdown: { translate: "0 -12px" } }}
    >
      <Menu.Target>
        <UnstyledButton
          className={classes.user}
          bg={
            computedColorScheme === "light"
              ? darken(colors.blue[8], 0.15)
              : "var(--mantine-color-dark-7)"
          }
        >
          <Group>
            <Avatar
              src={user.avatar}
              radius="xl"
              color={computedColorScheme === "light" ? "white" : "blue"}
              alt={user.name}
            >
              {getInitials(user.name)}
            </Avatar>

            <div style={{ flex: 1 }}>
              <Text size="sm" fw={500}>
                {user.name}
              </Text>

              <Text c={computedColorScheme === "light" ? "blue.4" : "dimmed"} size="xs">
                {user.job_title}
              </Text>
            </div>

            <IconChevronRight style={{ width: rem(14), height: rem(14) }} stroke={1.5} />
          </Group>
        </UnstyledButton>
      </Menu.Target>

      <Menu.Dropdown>
        <Menu.Label>Account</Menu.Label>
        <Menu.Item
          leftSection={<IconUser style={{ width: rem(14), height: rem(14) }} />}
          onClick={() => redirectTo("account.profile.edit")}
        >
          My Profile
        </Menu.Item>
        <Menu.Item
          leftSection={<IconBell style={{ width: rem(14), height: rem(14) }} />}
          onClick={() => redirectTo("notifications")}
        >
          Notifications
        </Menu.Item>

        <Menu.Divider />

        <Menu.Item
          leftSection={
            computedColorScheme === "light" ? (
              <IconSun style={{ width: rem(14), height: rem(14) }} />
            ) : (
              <IconMoon style={{ width: rem(14), height: rem(14) }} />
            )
          }
          onClick={() => setColorScheme(computedColorScheme === "light" ? "dark" : "light")}
        >
          {upperFirst(computedColorScheme)} mode
        </Menu.Item>

        <Menu.Divider />

        <Menu.Item
          color="red"
          leftSection={<IconLogout style={{ width: rem(14), height: rem(14) }} />}
          onClick={logout}
        >
          Logout
        </Menu.Item>
      </Menu.Dropdown>
    </Menu>
  );
}
