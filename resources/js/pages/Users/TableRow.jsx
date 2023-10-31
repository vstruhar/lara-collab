import { openConfirmModal } from "@/components/ConfirmModal";
import RoleBadge from "@/components/RoleBadge";
import UserService from "@/services/UserService";
import { money } from "@/utils/formatCurrency";
import { redirectTo } from "@/utils/route";
import {
  ActionIcon,
  Avatar,
  Flex,
  Group,
  Menu,
  Table,
  Text,
  rem,
} from "@mantine/core";
import {
  IconArchive,
  IconArchiveOff,
  IconDots,
  IconPencil,
} from "@tabler/icons-react";
import { useForm } from "laravel-precognition-react-inertia";

export default function TableRow({ user }) {
  const service = new UserService(user);
  const archiveUser = useForm("delete", route("users.destroy", user.id));
  const unArchiveUser = useForm("post", route("users.restore", user.id));

  const openArchiveModal = () =>
    openConfirmModal({
      type: "danger",
      title: "Archive user",
      content: `Are you sure you want to archive this user? This action will prevent
          the user from logging in, while all other aspects related to the
          user's actions will remain unaffected.`,
      confirmLabel: "Archive",
      confirmProps: { color: "red" },
      action: () => archiveUser.submit(),
    });

  const openUnArchiveModal = () =>
    openConfirmModal({
      type: "info",
      title: "Restore user",
      content: `Are you sure you want to restore this user? This action will again allow the user to logging in.`,
      confirmLabel: "Restore",
      confirmProps: { color: "blue" },
      action: () => unArchiveUser.submit(),
    });

  return (
    <Table.Tr key={user.id}>
      <Table.Td>
        <Group gap="sm">
          <Avatar
            src={user.avatar}
            size={40}
            radius={40}
            color="blue"
            alt={user.name}
          >
            {service.getInitials()}
          </Avatar>
          <div>
            <Text fz="sm" fw={500}>
              {user.name}
            </Text>
            <Text fz="xs" c="dimmed">
              {user.job_title}
            </Text>
          </div>
        </Group>
      </Table.Td>
      <Table.Td maw={200}>
        <Flex gap="sm" align="center" direction="row" wrap="wrap">
          {user.roles.map((role) => (
            <RoleBadge role={role.name} key={role.id} />
          ))}
        </Flex>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{user.email}</Text>
        <Text fz="xs" c="dimmed">
          Email
        </Text>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{money(user.rate)} / hr</Text>
        <Text fz="xs" c="dimmed">
          Rate
        </Text>
      </Table.Td>
      <Table.Td>
        <Group gap={0} justify="flex-end">
          {!route().params.archived && (
            <ActionIcon
              variant="subtle"
              color="blue"
              onClick={redirectTo("users.edit", user.id)}
            >
              <IconPencil
                style={{ width: rem(16), height: rem(16) }}
                stroke={1.5}
              />
            </ActionIcon>
          )}
          <Menu
            transitionProps={{ transition: "pop" }}
            withArrow
            position="bottom-end"
            withinPortal
          >
            <Menu.Target>
              <ActionIcon variant="subtle" color="gray">
                <IconDots
                  style={{ width: rem(16), height: rem(16) }}
                  stroke={1.5}
                />
              </ActionIcon>
            </Menu.Target>
            <Menu.Dropdown>
              {route().params.archived ? (
                <Menu.Item
                  leftSection={
                    <IconArchiveOff
                      style={{ width: rem(16), height: rem(16) }}
                      stroke={1.5}
                    />
                  }
                  color="blue"
                  onClick={openUnArchiveModal}
                >
                  Restore
                </Menu.Item>
              ) : (
                <Menu.Item
                  leftSection={
                    <IconArchive
                      style={{ width: rem(16), height: rem(16) }}
                      stroke={1.5}
                    />
                  }
                  color="red"
                  onClick={openArchiveModal}
                >
                  Archive
                </Menu.Item>
              )}
            </Menu.Dropdown>
          </Menu>
        </Group>
      </Table.Td>
    </Table.Tr>
  );
}
