import useUser from "@/hooks/useUser";
import { redirectTo } from "@/utils/route";
import { router, usePage } from "@inertiajs/react";
import { Avatar, Group, Menu, Text, UnstyledButton, rem } from "@mantine/core";
import {
  IconBell,
  IconChevronRight,
  IconLogout,
  IconUser,
} from "@tabler/icons-react";
import classes from "./css/UserButton.module.css";

export default function UserButton() {
  const { user } = usePage().props.auth;
  const { getInitials } = useUser(user);

  const logout = () => {
    router.delete(route("logout"), {
      onSuccess: redirectTo("auth.login.form"),
    });
  };

  return (
    <Menu position="right" offset={10} withArrow width={200}>
      <Menu.Target>
        <UnstyledButton className={classes.user}>
          <Group>
            <Avatar src={user.avatar} radius="xl" color="blue" alt={user.name}>
              {getInitials()}
            </Avatar>

            <div style={{ flex: 1 }}>
              <Text size="sm" fw={500}>
                {user.name}
              </Text>

              <Text c="dimmed" size="xs">
                {user.job_title}
              </Text>
            </div>

            <IconChevronRight
              style={{ width: rem(14), height: rem(14) }}
              stroke={1.5}
            />
          </Group>
        </UnstyledButton>
      </Menu.Target>

      <Menu.Dropdown>
        <Menu.Label>Account</Menu.Label>
        <Menu.Item
          leftSection={<IconUser style={{ width: rem(14), height: rem(14) }} />}
        >
          My Profile
        </Menu.Item>
        <Menu.Item
          leftSection={<IconBell style={{ width: rem(14), height: rem(14) }} />}
        >
          Notifications
        </Menu.Item>

        <Menu.Divider />

        <Menu.Item
          color="red"
          leftSection={
            <IconLogout style={{ width: rem(14), height: rem(14) }} />
          }
          onClick={logout}
        >
          Logout
        </Menu.Item>
      </Menu.Dropdown>
    </Menu>
  );
}
